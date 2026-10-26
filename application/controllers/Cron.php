<?php
defined('BASEPATH') OR exit('No direct script access allowed');
function normalize_mobile($num)
{
    $num = preg_replace('/\D+/', '', (string) $num);
    if (strlen($num) > 10) {
        $num = substr($num, -10);
    }
    return trim($num);
}
class Cron extends CI_Controller {
private $openAiKey;
public function __construct(){
    
    parent::__construct();
    $this->load->database();
     $this->config->load('openai');

    // get api key
    $this->openAiKey = $this->config->item('openai_api_key');
     // Only runnable from CLI (php index.php cron lead_transfer) - never over HTTP
        if (!$this->input->is_cli_request()) {
            show_404();
        }
 

        $this->load->model('Lead_transfer_model', 'leadModel');
        set_time_limit(120);
}


 private function logmsg($msg)
    {
        $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg;
        log_message('info', $line);
        echo $line . "\n";
    }


public function lead_transfer()
{
    $this->logmsg("=========== CRON STARTED ===========");

    $users = $this->leadModel->get_employees_by_mobile();
    $this->logmsg("Employees Loaded: " . count($users));

    $logs = $this->leadModel->get_pending_call_logs(500);
    $this->logmsg("Pending Logs: " . count($logs));

    $leadSourceMap = [
        '7574' => '7574 X 7574',
        '7575' => '7575 X 7575',
        '7570' => '9266 X 7570',
        '7566' => '9289 X 7566',
        '7567' => '7567 X 7567',
        '7573' => '9873 X 7573',
    ];

    $inserted   = 0;
    $reassigned = 0;
    $sameAgent  = 0;
    $errors     = 0;

    // IMPORTANT: transaction is per-ROW, not around the whole loop.
    // Wrapping all 500 rows in one transaction means a single bad row
    // rolls back every successful update in the batch — that was the
    // main reason nothing seemed to persist.
    foreach ($logs as $log) {

        $logId = (int) $log['id'];

        $customerMobile = normalize_mobile(
            !empty($log['caller_id_number'])
                ? $log['caller_id_number']
                : $log['customer_no_with_prefix']
        );

        $agentMobile = normalize_mobile($log['answer_agent_number']);
        $callTo      = normalize_mobile($log['call_to_number']);

        $this->logmsg("------------------------------------------------");
        $this->logmsg("Processing Log ID: {$logId} | Customer: {$customerMobile} | Agent: {$agentMobile}");

        if (empty($customerMobile)) {
            $this->leadModel->mark_call_log($logId, 'error');
            $this->logmsg("Customer mobile empty");
            $errors++;
            continue;
        }

        // Last-4-digit mapping, same scheme as the missed-call cron.
        $callToNumber = preg_replace('/\D+/', '', (string) $log['call_to_number']);
        $last4Digits  = substr($callToNumber, -4);
        $mappedDigits = $leadSourceMap[$last4Digits] ?? $last4Digits;
        $lead_source  = $mappedDigits;

        if (!empty($agentMobile) && isset($users[$agentMobile])) {
            $matchedUser = $users[$agentMobile];
            $userid      = (int) $matchedUser['id'];
            $assigned_to = $matchedUser['full_name'];
            $team_head   = $matchedUser['agent_id'];
            $api         = $matchedUser['location'];
            $this->logmsg("Matched Agent: {$assigned_to}");
        } else {
            $userid      = 0;
            $assigned_to = 'Unassigned';
            $team_head   = 'NA';
            $api         = 'NA';
            $this->logmsg("No Agent Match Found");
        }

        $lead_id_text = (string) random_int(100000, 999999);

        // Real call time (for records) vs real "now" (for the bump-to-top
        // field). Using the call log's own created_at for the bump was
        // the second bug — if the cron ever runs behind, that timestamp
        // is stale and the lead never actually rises to the top.
        $callTime = !empty($log['created_at']) ? $log['created_at'] : date('Y-m-d H:i:s');
        $now      = date('Y-m-d H:i:s');
        $today    = date('Y-m-d');
        $tarik1   = date('Y-m-d\TH:i');

        $existing = $this->leadModel->find_latest_lead_by_mobile($customerMobile);

        // ---------- CASE 1: lead already exists -> ALWAYS reassign + bump, never fork ----------
        if ($existing) {

            $leadPrimaryId = (int) $existing['id'];
            $wasSameAgent  = ((int) $existing['userid'] === $userid && $userid !== 0);

            $ok = $this->leadModel->reassign_and_touch($leadPrimaryId, [
                'userid'       => $userid,
                'assigned_to'  => $assigned_to,
                'team_head'    => $team_head,
                'api'          => $api,
                'created_date' => $now,
                'tarik'        => $today,
                'lead_source'  => $lead_source,
            ]);

            if (!$ok) {
                $this->logmsg("Reassign FAILED for Lead ID: {$leadPrimaryId} — leaving log as-is, will retry next run");
                $errors++;
                continue; // don't mark_call_log as transferred, don't insert comment
            }

            $comment = $wasSameAgent
                ? 'Customer called again, same agent'
                : "Customer called again — reassigned to {$assigned_to}";

            $this->db->trans_start();

            $this->leadModel->insert_comment([
                'lead_id'        => $leadPrimaryId,
                'userid'         => $userid,
                'comment'        => "[Call] " . $comment,
                'username'       => $assigned_to,
                'user_mobile_no' => $customerMobile,
                'status'         => 'Contacted',
                'created_date'   => $callTime,
                'description'    => 'Answered call cron',
                'tarik'          => $today,
                'team_leader'    => $team_head,
                'tarik1'         => $tarik1,
            ]);

            $this->leadModel->mark_call_log($logId, 'transferred');

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $this->logmsg("Comment/mark_call_log FAILED for Log ID: {$logId}, will retry next run");
                $errors++;
                continue;
            }

            $this->logmsg("Lead {$leadPrimaryId} reassigned to {$assigned_to} and bumped to top");
            $wasSameAgent ? $sameAgent++ : $reassigned++;
            continue;
        }

        // ---------- CASE 2: no lead exists yet for this number -> insert new ----------
        $this->db->trans_start();

        $leadPrimaryId = $this->leadModel->insert_lead([
            'lead_id'             => $lead_id_text,
            'customer_name'       => '',
            'company_name'        => '',
            'city'                => '',
            'state'               => '',
            'mobile_no'           => $customerMobile,
            'secondary_mobile_no' => '',
            'email_id'            => '',
            'requirement'         => '',
            'description'         => 'Lead auto created from answered call cron',
            'lead_source'         => $lead_source,
            'status'              => 'Contacted',
            'userid'              => $userid,
            'assigned_by'         => 'Direct',
            'assigned_to'         => $assigned_to,
            'created_date'        => $now,
            'tarik'               => $today,
            'today'               => $today,
            'team_head'           => $team_head,
            'api'                 => $api,
        ]);

        if (!$leadPrimaryId) {
            $this->db->trans_complete();
            $this->logmsg("Insert Failed for Log ID: {$logId}: " . json_encode($this->db->error()));
            $this->leadModel->mark_call_log($logId, 'error');
            $errors++;
            continue;
        }

        $this->leadModel->insert_comment([
            'lead_id'        => $leadPrimaryId,
            'userid'         => $userid,
            'comment'        => '[Call] New answered call lead created',
            'username'       => $assigned_to,
            'user_mobile_no' => $customerMobile,
            'status'         => 'Contacted',
            'created_date'   => $callTime,
            'description'    => 'Lead auto created from answered call cron',
            'tarik'          => $today,
            'team_leader'    => $team_head,
            'tarik1'         => $tarik1,
        ]);

        $this->leadModel->mark_call_log($logId, 'transferred');

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->logmsg("Insert/comment FAILED for Log ID: {$logId}, will retry next run");
            $errors++;
            continue;
        }

        $this->logmsg("Lead Inserted Successfully ID: {$leadPrimaryId}");
        $inserted++;
    }

    $this->logmsg("=========== CRON COMPLETED ===========");
    $this->logmsg("New={$inserted}, Reassigned(diff-agent)={$reassigned}, SameAgent={$sameAgent}, Errors={$errors}");
}
public function transfer_missed_calls()
    {
        set_time_limit(120);
        $this->logmsg("=========== MISSED-CALL CRON STARTED ===========");

        $emp = $this->leadModel->get_active_employees_for_missed();
        $employees = $emp['employees'];
        $fb05      = $emp['fb05'];

        if (empty($employees) && empty($fb05)) {
            $this->logmsg("No active employees found.");
            return;
        }

        // Round-robin pointer, persisted between runs. Separate file from
        // the call cron so the two round robins don't interfere.
        $rrFile  = APPPATH . 'cache/rr_index_missed.txt';
        $rrIndex = file_exists($rrFile) ? (int) file_get_contents($rrFile) : 0;

        $logs = $this->leadModel->get_pending_missed_logs(500);
        $this->logmsg("Pending Missed Logs: " . count($logs));

        $leadSourceMap = [
            '7574' => '7574 X 7574',
            '7575' => '7575 X 7575',
            '7570' => '9266 X 7570',
            '7566' => '9289 X 7566',
            '7567' => '7567 X 7567',
            '7573' => '9873 X 7573',
        ];

        $inserted   = 0;
        $reassigned = 0;
        $errors     = 0;

        foreach ($logs as $log) {

            $logId  = (int) $log['id'];
            $mobile = normalize_mobile($log['caller_id_number']);

            if ($mobile === '') {
                $this->leadModel->mark_missed_log($logId, 'error');
                $this->logmsg("MissedLog {$logId}: empty mobile");
                $errors++;
                continue;
            }

            $callToNumber = preg_replace('/\D+/', '', (string) $log['call_to_number']);
            $last4Digits  = substr($callToNumber, -4);
            $mappedDigits = $leadSourceMap[$last4Digits] ?? $last4Digits;
            $lead_source  = $mappedDigits;

            // FB05 special routing for Missed9266, else round robin
            if ($lead_source === 'Missed9266' && !empty($fb05)) {
                $employee = $fb05;
                $this->logmsg("Special Routing -> Missed9266 assigned to FB05");
            } else {
                if (empty($employees)) {
                    $this->logmsg("MissedLog {$logId}: no round-robin employees available, skipping");
                    $errors++;
                    continue;
                }
                $employee = $employees[$rrIndex % count($employees)];
                $rrIndex++;
                file_put_contents($rrFile, $rrIndex, LOCK_EX);

                if ($lead_source === 'Missed9266') {
                    $this->logmsg("FB05 not active. Missed9266 assigned by Round Robin to {$employee['full_name']}");
                }
            }

            $userid      = (int) $employee['id'];
            $assigned_to = $employee['full_name'];
            $team_head   = $employee['agent_id'];
            $api         = $employee['location'];

            $now    = date('Y-m-d H:i:s');
            $today  = date('Y-m-d');
            $tarik1 = date('Y-m-d\TH:i');

            $existing = $this->leadModel->find_latest_lead_by_mobile($mobile);

            // ---------- CASE 1: lead already exists -> reassign + bump, never fork ----------
            if ($existing) {

                $leadPrimaryId = (int) $existing['id'];

                $ok = $this->leadModel->reassign_and_touch($leadPrimaryId, [
                    'userid'       => $userid,
                    'assigned_to'  => $assigned_to,
                    'team_head'    => $team_head,
                    'api'          => $api,
                    'created_date' => $now,
                    'tarik'        => $today,
                    'lead_source'  => $lead_source,
                ]);

                if (!$ok) {
                    $this->logmsg("Reassign FAILED for Lead ID: {$leadPrimaryId}, will retry next run");
                    $errors++;
                    continue;
                }

                $this->db->trans_start();

                $this->leadModel->insert_comment([
                    'lead_id'        => $leadPrimaryId,
                    'userid'         => $userid,
                    'comment'        => "[Missed Call] Customer called again (missed) — reassigned to {$assigned_to}",
                    'username'       => $assigned_to,
                    'user_mobile_no' => $mobile,
                    'status'         => 'Not Contacted',
                    'created_date'   => $now,
                    'description'    => 'Missed call cron',
                    'tarik'          => $today,
                    'team_leader'    => $team_head,
                    'tarik1'         => $tarik1,
                ]);

                $this->leadModel->mark_missed_log($logId, 'transferred');

                $this->db->trans_complete();

                if ($this->db->trans_status() === FALSE) {
                    $this->logmsg("Comment/mark_missed_log FAILED for Log ID: {$logId}, will retry next run");
                    $errors++;
                    continue;
                }

                $this->logmsg("Lead {$leadPrimaryId} reassigned to {$assigned_to} and bumped to top (missed call)");
                $reassigned++;
                continue;
            }

            // ---------- CASE 2: no lead exists yet for this number -> insert new ----------
            $this->db->trans_start();

            $leadPrimaryId = $this->leadModel->insert_lead([
                'lead_id'             => (string) random_int(100000, 999999),
                'customer_name'       => 'No Name',
                'company_name'        => 'No Data Available',
                'city'                => 'No Data Available',
                'state'               => 'No Data Available',
                'mobile_no'           => $mobile,
                'secondary_mobile_no' => 'No Data Available',
                'email_id'            => 'No Data Available',
                'requirement'         => 'No Data Available',
                'description'         => 'Lead auto created from missed call cron',
                'lead_source'         => $lead_source,
                'status'              => 'Missed',
                'userid'              => $userid,
                'assigned_by'         => $assigned_to,
                'assigned_to'         => $assigned_to,
                'created_date'        => $now,
                'tarik'               => $today,
                'today'               => $today,
                'team_head'           => $team_head,
                'api'                 => $api,
            ]);

            if (!$leadPrimaryId) {
                $this->db->trans_complete();
                $this->logmsg("Insert Failed for MissedLog {$logId}: " . json_encode($this->db->error()));
                $this->leadModel->mark_missed_log($logId, 'error');
                $errors++;
                continue;
            }

            $this->leadModel->insert_comment([
                'lead_id'        => $leadPrimaryId,
                'userid'         => $userid,
                'comment'        => '[Missed Call] New missed call lead created',
                'username'       => $assigned_to,
                'user_mobile_no' => $mobile,
                'status'         => 'Missed',
                'created_date'   => $now,
                'description'    => 'Lead auto created from missed call cron',
                'tarik'          => $today,
                'team_leader'    => $team_head,
                'tarik1'         => $tarik1,
            ]);

            $this->leadModel->mark_missed_log($logId, 'transferred');

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $this->logmsg("Insert/comment FAILED for MissedLog {$logId}, will retry next run");
                $errors++;
                continue;
            }

            $this->logmsg("MissedLog {$logId} ({$mobile}) -> lead assigned to {$assigned_to}");
            $inserted++;
        }

        $this->logmsg("=========== MISSED-CALL CRON COMPLETED ===========");
        $this->logmsg("Inserted={$inserted}, Reassigned={$reassigned}, Errors={$errors}");
    }
    /* ==============================
   WATI LEAD TRANSFER CRON
   processed codes on tbl_wati_webhook:
   0 = pending
   1 = new lead inserted
   2 = existing lead reassigned/updated
   3 = skipped - no operator_email yet (not an agent reply)
   4 = skipped - operator_email present but no matching agent, or missing mobile
   5 = error - insert/reassign failed, will retry next run
============================== */
public function wati_lead_transfer()
{
    $this->logmsg("=========== WATI CRON STARTED ===========");

    $users = $this->leadModel->get_employees_by_email();
    $this->logmsg("Employees Loaded (by email): " . count($users));

    $logs = $this->leadModel->get_pending_wati_logs(500);
    $this->logmsg("Pending WATI Logs: " . count($logs));

    $inserted   = 0;
    $reassigned = 0;
    $sameAgent  = 0;
    $skipped    = 0;
    $errors     = 0;

    foreach ($logs as $log) {

        $webhookId = (int) $log['id'];

        $customerMobile = normalize_mobile($log['mobile_no']);
        $operatorEmail  = trim(strtolower($log['operator_email']));

        $this->logmsg("------------------------------------------------");
        $this->logmsg("Processing Webhook ID: {$webhookId} | Mobile: {$customerMobile} | Email: {$operatorEmail}");

        // Rule 1: no operator email yet = customer messaged, agent hasn't replied. Leave pending, don't touch tbl_lead.
        if (empty($operatorEmail)) {
            $this->leadModel->mark_wati_log($webhookId, 3);
            $this->logmsg("No operator_email yet, skipping (waiting for agent reply)");
            $skipped++;
            continue;
        }

        // Rule 2: operator email present but must resolve to a real agent, and mobile must be usable
        if (empty($customerMobile) || !isset($users[$operatorEmail])) {
            $this->leadModel->mark_wati_log($webhookId, 4);
            $this->logmsg(empty($customerMobile) ? "Empty mobile, skipping" : "No agent match for {$operatorEmail}, skipping");
            $skipped++;
            continue;
        }

        $matchedUser = $users[$operatorEmail];
        $userid      = (int) $matchedUser['id'];
        $assigned_to = $matchedUser['full_name'];
        $team_head   = $matchedUser['agent_id'];
        $api         = $matchedUser['location'];

        $lead_id_text = $log['wati_id'] ?: (string) random_int(100000, 999999);
        $customer_name = $log['customer_name'] ?: 'Unknown';
        $requirement   = $log['requirement'];
        $description   = $log['description'];

        $callTime = !empty($log['created_date']) ? $log['created_date'] : date('Y-m-d H:i:s');
        $now      = date('Y-m-d H:i:s');
        $today    = date('Y-m-d');
        $tarik1   = date('Y-m-d\TH:i');

        $existing = $this->leadModel->find_latest_lead_by_mobile($customerMobile);

        // ---------- CASE 1: lead already exists -> reassign + bump, never fork ----------
        if ($existing) {

            $leadPrimaryId = (int) $existing['id'];
            $wasSameAgent  = ((int) $existing['userid'] === $userid);

            $ok = $this->leadModel->reassign_and_touch($leadPrimaryId, [
                'userid'       => $userid,
                'assigned_to'  => $assigned_to,
                'team_head'    => $team_head,
                'api'          => $api,
                'created_date' => $now,
                'tarik'        => $today,
            ]);

            if (!$ok) {
                $this->logmsg("Reassign FAILED for Lead ID: {$leadPrimaryId}, will retry next run");
                $errors++;
                continue; // leave processed = 0, retry next run
            }

            $comment = $wasSameAgent
                ? 'Customer messaged again on WhatsApp, same agent'
                : "Customer messaged again on WhatsApp — reassigned to {$assigned_to}";

            $this->db->trans_start();

            $this->leadModel->insert_comment([
                'lead_id'        => $leadPrimaryId,
                'userid'         => $userid,
                'comment'        => "[WATI] " . $comment . (!empty($description) ? " — \"{$description}\"" : ""),
                'username'       => $assigned_to,
                'user_mobile_no' => $customerMobile,
                'status'         => 'Contacted',
                'created_date'   => $callTime,
                'description'    => 'WATI webhook cron',
                'tarik'          => $today,
                'team_leader'    => $team_head,
                'tarik1'         => $tarik1,
            ]);

            $this->leadModel->mark_wati_log($webhookId, 2);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $this->logmsg("Comment/mark_wati_log FAILED for Webhook ID: {$webhookId}, will retry next run");
                $errors++;
                continue;
            }

            $this->logmsg("Lead {$leadPrimaryId} reassigned to {$assigned_to} and bumped to top");
            $wasSameAgent ? $sameAgent++ : $reassigned++;
            continue;
        }

        // ---------- CASE 2: no lead exists yet for this number -> insert new ----------
        $this->db->trans_start();

        $leadPrimaryId = $this->leadModel->insert_lead([
            'lead_id'             => $lead_id_text,
            'customer_name'       => $customer_name,
            'company_name'        => '',
            'city'                => '',
            'state'               => '',
            'mobile_no'           => $customerMobile,
            'secondary_mobile_no' => '',
            'email_id'            => $operatorEmail,
            'requirement'         => $requirement,
            'description'         => $description,
            'lead_source'         => 'Whatsapp Lead',
            'status'              => 'Contacted',
            'userid'              => $userid,
            'assigned_by'         => 'WATI',
            'assigned_to'         => $assigned_to,
            'created_date'        => $now,
            'tarik'               => $today,
            'today'               => $today,
            'team_head'           => $team_head,
            'api'                 => $api,
        ]);

        if (!$leadPrimaryId) {
            $this->db->trans_complete();
            $this->logmsg("Insert Failed for Webhook ID: {$webhookId}: " . json_encode($this->db->error()));
            $errors++;
            continue; // leave processed = 0, retry next run
        }

        $this->leadModel->insert_comment([
            'lead_id'        => $leadPrimaryId,
            'userid'         => $userid,
            'comment'        => "[WATI] New lead created from WhatsApp" . (!empty($description) ? " — \"{$description}\"" : ""),
            'username'       => $assigned_to,
            'user_mobile_no' => $customerMobile,
            'status'         => 'Contacted',
            'created_date'   => $callTime,
            'description'    => 'Lead auto created from WATI webhook cron',
            'tarik'          => $today,
            'team_leader'    => $team_head,
            'tarik1'         => $tarik1,
        ]);

        $this->leadModel->mark_wati_log($webhookId, 1);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->logmsg("Insert/comment FAILED for Webhook ID: {$webhookId}, will retry next run");
            $errors++;
            continue;
        }

        $this->logmsg("Lead Inserted Successfully ID: {$leadPrimaryId}, assigned to {$assigned_to}");
        $inserted++;
    }

    $this->logmsg("=========== WATI CRON COMPLETED ===========");
    $this->logmsg("New={$inserted}, Reassigned(diff-agent)={$reassigned}, SameAgent={$sameAgent}, Skipped={$skipped}, Errors={$errors}");
}
/* COMMON FUNCTION TO SEND WHATSAPP */
private function send_whatsapp($phone, $template_name, $parameters){

    $url = "https://live-mt-server.wati.io/1034642/api/v2/sendTemplateMessage?whatsappNumber={$phone}";

    $payload = [
        "parameters" => $parameters,
        "template_name"  => $template_name,
        "broadcast_name" => "CRM Alert"
    ];

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1bmlxdWVfbmFtZSI6ImdhdXRhbWF5dXJ2ZWRhc3RvcmVAZ21haWwuY29tIiwibmFtZWlkIjoiZ2F1dGFtYXl1cnZlZGFzdG9yZUBnbWFpbC5jb20iLCJlbWFpbCI6ImdhdXRhbWF5dXJ2ZWRhc3RvcmVAZ21haWwuY29tIiwiYXV0aF90aW1lIjoiMDcvMjQvMjAyNiAxMTozMDoxMiIsInRlbmFudF9pZCI6IjEwMzQ2NDIiLCJkYl9uYW1lIjoibXQtcHJvZC1UZW5hbnRzIiwiaHR0cDovL3NjaGVtYXMubWljcm9zb2Z0LmNvbS93cy8yMDA4LzA2L2lkZW50aXR5L2NsYWltcy9yb2xlIjoiQURNSU5JU1RSQVRPUiIsImV4cCI6MjUzNDAyMzAwODAwLCJpc3MiOiJDbGFyZV9BSSIsImF1ZCI6IkNsYXJlX0FJIn0.nu8xInahYqv5ID4tsEhUROZPcHhkt1LQztq4lIUSWMc",
            "Content-Type: application/json-patch+json"
        ],
    ]);

    curl_exec($curl);
    curl_close($curl);

    // Prevent server/API overload
    usleep(300000); // 0.3 sec
}


/* MAIN CRON FUNCTION */
public function send_login_alert(){

    $phones = ["919873446622","919873653786"];

    /* ================= LOGIN ALERT ================= */
    $login_logs = $this->db
        ->select('tbl_login_logs.*, tbl_registration.full_name')
        ->from('tbl_login_logs')
        ->join('tbl_registration','tbl_registration.id = tbl_login_logs.user_id','left')
        ->where('login_alert_sent',0)
        ->limit(10)
        ->get()
        ->result();

    foreach($login_logs as $log){

        $parameters = [
            ["name"=>"employee_name","value"=>$log->full_name],
            ["name"=>"device","value"=>$log->device],
            ["name"=>"browser","value"=>$log->browser],
            ["name"=>"platform","value"=>$log->platform],
            ["name"=>"location","value"=>$log->location],
            ["name"=>"ip_address","value"=>$log->ip_address],
            ["name"=>"login_time","value"=>$log->login_time]
        ];

        foreach($phones as $phone){
            $this->send_whatsapp($phone, "crm_alertsssss", $parameters);
        }

        // Update flag
        $this->db->where('id',$log->id);
        $this->db->update('tbl_login_logs',[
            'login_alert_sent'=>1
        ]);
    }


    /* ================= LOGOUT ALERT ================= */
    $logout_logs = $this->db
        ->select('tbl_login_logs.*, tbl_registration.full_name')
        ->from('tbl_login_logs')
        ->join('tbl_registration','tbl_registration.id = tbl_login_logs.user_id','left')
        ->where('login_alert_sent',1)
        ->where('logout_alert_sent',0)
        ->where('logout_time IS NOT NULL', NULL, FALSE)
        ->limit(10)
        ->get()
        ->result();

    foreach($logout_logs as $log){

        $parameters = [
            ["name"=>"employee_name","value"=>$log->full_name],
            ["name"=>"login_time","value"=>$log->login_time],
            ["name"=>"logout_time","value"=>$log->logout_time],
            ["name"=>"duration","value"=>$log->session_duration]
        ];

        foreach($phones as $phone){
            $this->send_whatsapp($phone, "crm_logout_alertsssss", $parameters);
        }

        // Update flag
        $this->db->where('id',$log->id);
        $this->db->update('tbl_login_logs',[
            'logout_alert_sent'=>1
        ]);
    }

    echo "Login & Logout alerts processed";
}


public function processCallAI()
{
    $call = $this->db
        ->select('id, recording_url')
        ->where('ai_processed', 0)
        ->where('recording_url IS NOT NULL', null, false)
        ->where('recording_url !=', '')
        ->limit(1)
        ->get('ivr_call_logs')
        ->row();

    if (!$call) {
        echo "No pending calls";
        return;
    }

    echo "<pre>";

    echo "Processing Call ID: ".$call->id."<br><br>";

    // STEP 1: Transcript
    $transcript = $this->speechToText($call->recording_url);

    echo "<b>Transcript:</b><br>";
    echo $transcript."<br><br>";

    // STEP 2: AI analysis
    $analysis = $this->analyzeCall($transcript);

    echo "<b>AI Analysis:</b><br>";
    print_r($analysis);

    // STEP 3: Save DB
    $this->db->where('id', $call->id);
    $this->db->update('ivr_call_logs', [
        'transcript'      => $transcript,
        'tl_feedback'     => $analysis['tl_feedback'] ?? '',
        'tl_flag'         => $analysis['tl_flag'] ?? 'UNKNOWN',
        'ai_processed'    => 1,
        'ai_processed_at' => date('Y-m-d H:i:s')
    ]);

    echo "<br><br>Saved successfully";
}
private function analyzeCall($transcript)
{
    $prompt = "
You are a call quality analyst.

Analyze this customer call transcript.

Return valid JSON only.

Format:
{
    \"tl_feedback\": \"\",
    \"tl_flag\": \"\"
}

Rules:
- tl_feedback = short manager feedback (1 sentence)
- tl_flag must be one of:

GOOD
BAD
ESCALATION
MISSED
SPAM

Examples:
GOOD = agent handled properly
BAD = poor handling
ESCALATION = angry customer / complaint escalation
MISSED = no conversation / unanswered
SPAM = irrelevant call

Transcript:
".$transcript;

    $payload = [
        "model" => "gpt-4.1-mini",
        "messages" => [
            [
                "role" => "user",
                "content" => $prompt
            ]
        ],
        "temperature" => 0.2
    ];

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.openai.com/v1/chat/completions',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->openAiKey
        ],
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);

    $response = curl_exec($curl);

    curl_close($curl);

    $response = json_decode($response, true);

    $content = $response['choices'][0]['message']['content'] ?? '{}';

    return json_decode($content, true);
}
    private function speechToText($recordingUrl)
    {
        // temp file
        $tempFile = FCPATH . 'uploads/temp_call.mp3';

        // download recording
        $audio = file_get_contents($recordingUrl);

        if (!$audio) {
            return 'Unable to download recording';
        }

        file_put_contents($tempFile, $audio);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.openai.com/v1/audio/transcriptions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer '.$this->openAiKey
            ],
            CURLOPT_POSTFIELDS => [
    'file' => new CURLFile($tempFile),
    'model' => 'gpt-4o-mini-transcribe',

    // force language
    'language' => 'hi',

    // optional prompt improves Hinglish accuracy
    'prompt' => 'This is an Indian Hindi-English (Hinglish) customer support call. Transcribe in Roman Hindi and English, not Urdu script.'
]
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        unlink($tempFile);

        $responseArray = json_decode($response, true);

echo "<pre>";
print_r($responseArray);

return $responseArray['text'] ?? 'No transcript found';
    }


}