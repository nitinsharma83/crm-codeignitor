<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cron: Assign leads to their agents inside WATI Team Inbox.
 *
 * Logic:
 * 1. Pull a small BATCH of leads (not all at once) that are already assigned
 *    to an agent (assigned_to is set) but not yet confirmed assigned in WATI
 *    (wati_assign_status = 0).
 * 2. For each lead: try to move the WATI chat from Broadcast -> Open, then
 *    assign the operator (agent's email).
 * 3. If WATI confirms {"result": true} -> mark wati_assign_status = 1 (done).
 *    If it fails (most commonly because the lead never replied on WhatsApp,
 *    so the chat is still stuck in "Broadcast" status) -> leave it at 0.
 *    We do NOT force an assignment in that case; the cron will simply pick
 *    that lead up again on its next run (e.g. tomorrow).
 * 4. A wall-clock time budget stops the script cleanly before PHP/cron
 *    timeouts hit, so large tables never cause a hang — remaining leads
 *    are picked up by the next scheduled run.
 *
 * Suggested crontab (every 10 minutes, keeps working through backlog safely):
 *   *\/10 * * * * php /path/to/index.php cron assign_wati_leads >> /path/to/logs/wati_assign.log 2>&1
 */
class Assign_Cron extends CI_Controller
{
    // ===== Tunables =====
    const BATCH_SIZE          = 100; // leads processed per cron run
    const TIME_BUDGET_SECONDS = 240; // stop cleanly after 4 minutes, let next run continue
    const CURL_TIMEOUT        = 15;  // seconds per WATI API call
    const CURL_CONNECT_TIMEOUT = 5;
    const SLEEP_BETWEEN_CALLS_US = 250000; // 0.25s between leads, avoid rate limits

    private $watiTenantId;
    private $watiToken;
    private $watiChannelNumber;
    private $startTime;

    public function __construct()
    {
        parent::__construct();
        $this->load->database();

        // TODO: move these to a config file / environment variables instead of hardcoding
        $this->watiTenantId      = '1034642';
        $this->watiToken         = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1bmlxdWVfbmFtZSI6ImdhdXRhbWF5dXJ2ZWRhc3RvcmVAZ21haWwuY29tIiwibmFtZWlkIjoiZ2F1dGFtYXl1cnZlZGFzdG9yZUBnbWFpbC5jb20iLCJlbWFpbCI6ImdhdXRhbWF5dXJ2ZWRhc3RvcmVAZ21haWwuY29tIiwiYXV0aF90aW1lIjoiMDcvMjQvMjAyNiAxMjo1MDozMSIsInRlbmFudF9pZCI6IjEwMzQ2NDIiLCJkYl9uYW1lIjoibXQtcHJvZC1UZW5hbnRzIiwiaHR0cDovL3NjaGVtYXMubWljcm9zb2Z0LmNvbS93cy8yMDA4LzA2L2lkZW50aXR5L2NsYWltcy9yb2xlIjoiQURNSU5JU1RSQVRPUiIsImV4cCI6MjUzNDAyMzAwODAwLCJpc3MiOiJDbGFyZV9BSSIsImF1ZCI6IkNsYXJlX0FJIn0.sk3oFHZNFQuLxKgiodAEPa9_zJPxgI7ziYVfeXvZSOc';
        $this->watiChannelNumber = '919289911911'; // number the leads' chats live under

        $this->startTime = microtime(true);

        // Allow enough execution time for a batch, but we still self-limit via TIME_BUDGET_SECONDS
        set_time_limit(self::TIME_BUDGET_SECONDS + 60);
    }

    public function assign_wati_leads()
    {
        log_message('info', 'WATI assign cron started');
        echo "Started at " . date('Y-m-d H:i:s') . "\n";

        // ✅ Build agent lookup once: full_name -> email
        // tbl_lead.assigned_to stores the agent's full_name (as text), so we
        // match against tbl_registration.full_name.
        $agents = $this->db
            ->select('full_name, email_address')
            ->from('tbl_registration')
            ->where('status', 1) // only active agents; drop this if not applicable
            ->get()
            ->result_array();

        $agentEmailByName = [];
        foreach ($agents as $a) {
            // normalize (trim + lowercase) so minor casing/whitespace differences
            // between tbl_lead.assigned_to and tbl_registration.full_name still match
            $key = $this->normalizeName($a['full_name']);
            $agentEmailByName[$key] = $a['email_address'];
        }

        // ✅ Pull one batch of unresolved, assigned leads
        $leads = $this->db
            ->select('id, lead_id, customer_name, mobile_no, assigned_to')
            ->from('tbl_lead')
            ->where('wati_assign_status', 0)
            ->where('assigned_to IS NOT NULL', null, false)
            ->where('assigned_to !=', '')
            ->where('mobile_no IS NOT NULL', null, false)
            ->where('mobile_no !=', '')
            ->order_by('id', 'ASC')
            ->limit(self::BATCH_SIZE)
            ->get()
            ->result_array();

        if (empty($leads)) {
            echo "No pending leads to assign.\n";
            log_message('info', 'WATI assign cron: nothing to do');
            return;
        }

        $processed = 0;
        $assigned  = 0;
        $skipped   = 0;

        foreach ($leads as $lead) {
            // ✅ Time budget check — stop cleanly, next cron run resumes from here
            if ((microtime(true) - $this->startTime) > self::TIME_BUDGET_SECONDS) {
                echo "Time budget reached, stopping this run at lead id {$lead['id']}. "
                   . "Remaining leads will be picked up next run.\n";
                break;
            }

            $processed++;

            // Resolve agent email by matching assigned_to (a name) against full_name
            $assignedToKey = $this->normalizeName($lead['assigned_to']);
            $agentEmail    = $agentEmailByName[$assignedToKey] ?? null;

            if (!$agentEmail) {
                echo "⚠️ Lead {$lead['id']}: no agent found with full_name matching "
                   . "'{$lead['assigned_to']}', skipping.\n";
                $skipped++;
                continue;
            }

            $whatsappNumber = $this->normalizeMobile($lead['mobile_no']);
            if (!$whatsappNumber) {
                echo "⚠️ Lead {$lead['id']}: invalid mobile number, skipping.\n";
                $skipped++;
                continue;
            }

            $result = $this->tryAssignLeadInWati($whatsappNumber, $agentEmail);

            // Always stamp when we last checked, whether success or not
            $updateData = [
                'wati_last_checked_at' => date('Y-m-d H:i:s'),
            ];

            if ($result['success']) {
                $updateData['wati_assign_status'] = 1;
                $assigned++;
                echo "✅ Lead {$lead['id']} ({$lead['customer_name']}) assigned to $agentEmail\n";
            } else {
                // Leave wati_assign_status = 0 — most likely the lead hasn't
                // messaged/replied on WhatsApp yet (chat still in Broadcast
                // status), so we don't force it. Cron retries next run.
                $updateData['wati_assign_status'] = 0;
                $skipped++;
                echo "⏭️  Lead {$lead['id']} ({$lead['customer_name']}) not yet assignable "
                   . "(likely no WhatsApp reply yet). Will retry later. Reason: {$result['reason']}\n";
            }

            $this->db->where('id', $lead['id'])->update('tbl_lead', $updateData);

            usleep(self::SLEEP_BETWEEN_CALLS_US);
        }

        echo "Done. Processed: $processed | Assigned: $assigned | Skipped/retry-later: $skipped\n";
        log_message('info', "WATI assign cron finished. Processed=$processed Assigned=$assigned Skipped=$skipped");
    }

    /**
     * Attempts: open the chat, then assign the operator.
     * Returns ['success' => bool, 'reason' => string]
     */
    private function tryAssignLeadInWati($whatsappNumber, $agentEmail)
    {
        // Step 1: move chat out of Broadcast status
        $statusResp = $this->updateChatStatus($whatsappNumber, 'OPEN');
        $statusJson = json_decode($statusResp, true);

        if (empty($statusJson['result'])) {
            return ['success' => false, 'reason' => 'updateChatStatus failed: ' . $statusResp];
        }

        // Step 2: assign operator
        $assignResp = $this->assignOperator($whatsappNumber, $agentEmail);
        $assignJson = json_decode($assignResp, true);

        if (!empty($assignJson['result'])) {
            return ['success' => true, 'reason' => 'ok'];
        }

        return ['success' => false, 'reason' => 'assignOperator failed: ' . $assignResp];
    }

    private function updateChatStatus($whatsappNumber, $status)
    {
        $url = "https://live-mt-server.wati.io/{$this->watiTenantId}/api/v1/updateChatStatus";
        $payload = [
            'ticketStatus'       => $status, // OPEN / SOLVED / PENDING / BLOCK
            'whatsappNumber'     => $whatsappNumber,
            'channelPhoneNumber' => $this->watiChannelNumber,
        ];
        return $this->watiPost($url, $payload);
    }

    private function assignOperator($whatsappNumber, $agentEmail)
    {
        $url = "https://live-mt-server.wati.io/{$this->watiTenantId}/api/v1/assignOperator"
             . '?email=' . urlencode($agentEmail)
             . '&whatsappNumber=' . urlencode($whatsappNumber);
        return $this->watiPost($url, null);
    }

    /**
     * Shared cURL helper. Bounded timeouts so a slow/hanging WATI response
     * can never stall the whole cron run indefinitely.
     */
    private function watiPost($url, $payload)
    {
        $curl = curl_init();
        $headers = [
            "Authorization: Bearer {$this->watiToken}",
            'accept: application/json',
        ];

        $opts = [
            CURLOPT_URL             => $url,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_ENCODING        => '',
            CURLOPT_MAXREDIRS       => 5,
            CURLOPT_TIMEOUT         => self::CURL_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT  => self::CURL_CONNECT_TIMEOUT,
            CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST   => 'POST',
        ];

        if ($payload !== null) {
            $headers[] = 'Content-Type: application/json';
            $opts[CURLOPT_POSTFIELDS] = json_encode($payload);
        }

        $opts[CURLOPT_HTTPHEADER] = $headers;
        curl_setopt_array($curl, $opts);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $response = json_encode([
                'result' => false,
                'info'   => curl_error($curl),
            ]);
        }

        curl_close($curl);
        return $response;
    }

    /**
     * Normalizes a name for comparison — trims whitespace and lowercases,
     * so "Ramesh Kumar", " ramesh kumar ", "RAMESH KUMAR" all match the
     * same agent.
     */
    private function normalizeName($name)
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', (string) $name)));
    }

    /**
     * Normalizes a stored mobile number into WATI's expected format
     * (country code + number, no +, no spaces/dashes).
     * Adjust this if your numbers are stored differently.
     */
    private function normalizeMobile($mobile)
    {
        $mobile = preg_replace('/\D/', '', trim($mobile)); // strip non-digits
        if (empty($mobile)) {
            return null;
        }
        // If it's a 10-digit Indian number without country code, prefix 91
        if (strlen($mobile) == 10) {
            $mobile = '91' . $mobile;
        }
        return $mobile;
    }
}