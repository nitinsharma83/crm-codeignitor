<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
         $this->config->load('wati'); 
    }

    // 🔐 LOGIN API
    public function login_api()
    {
        
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit; // Preflight request
    }

        $username = trim($this->input->post('username'));
        $password = md5(trim($this->input->post('password')));

        $user = $this->db->where([
            'email_address' => $username,
            'password' => $password
        ])->get('tbl_registration')->row();

        if ($user) {

            // Generate secure token
            $token = bin2hex(random_bytes(32));

            // Save token
            $this->db->where('id', $user->id)
                     ->update('tbl_registration', [
                         'login_token' => $token,
                         'status' => 'active'
                     ]);

            echo json_encode([
                'code' => 200,
                'message' => 'Login successful',
                'redirect_url' => site_url('api/token_login?token='.$token)
            ]);

        } else {
            echo json_encode([
                'code' => 400,
                'message' => 'Invalid Email or Password'
            ]);
        }
    }
public function send_wati_message()
{
    $lead_id   = $this->input->post('lead_id');
    $mobile_no = $this->input->post('mobile_no');
    $message   = trim($this->input->post('message'));

    if (empty($mobile_no) || empty($message)) {
        echo json_encode(['status' => 'error', 'message' => 'Mobile number and message are required']);
        return;
    }

    $phone = '91' . substr(preg_replace('/\D/', '', $mobile_no), -10);

    $url = "https://live-mt-server.wati.io/1034642/api/v1/sendSessionMessage/{$phone}?messageText=" . urlencode($message);

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . $this->config->item('wati_api_key'), // move token to config, don't hardcode
            "Content-Type: application/json"
        ],
    ]);
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($httpCode >= 200 && $httpCode < 300) {
        $this->db->insert('lead_whatsapp_messages', [
            'lead_id'       => $lead_id,
            'direction'     => 'outbound',
            'message'       => $message,
            'template_name' => null,
            'sent_at'       => date('Y-m-d H:i:s'),
        ]);
        echo json_encode(['status' => 'success']);
    } else {
        error_log("WATI send_wati_message failed: " . $response);
        echo json_encode(['status' => 'error', 'message' => 'WATI API request failed']);
    }
}
    // 🔑 TOKEN LOGIN
public function auto_login()
{
    $key = $this->input->get('key');

    if (!$key) {
        show_error('Unauthorized', 401);
    }

    $user = $this->db->where('auto_login_key', $key)
                     ->get('tbl_registration')
                     ->row();

    if (!$user) {
        show_error('Unauthorized', 401);
    }

    // Create session (every time)
    $this->session->set_userdata([
        'ADMIN_ID'  => $user->id,
        'USERNAME'  => $user->full_name,
        'EMAIL'     => $user->email_address,
        'USERTYPE'  => $user->user_type,
        'LOCATION'  => $user->location,
        'AGENT_ID'  => $user->agent_id,
        'ROLE'     => $user->role,
        'logged_in' => true
    ]);

    // ❌ DO NOT DELETE KEY
    // ❌ DO NOT EXPIRE KEY

    redirect('/');
}
public function send_wati_template()
{
    $mobile_no = trim($this->input->post('mobile_no'));
    $lead_id   = trim($this->input->post('lead_id'));

    if (empty($mobile_no)) {
        echo json_encode(['status' => 'error', 'message' => 'Mobile number missing.']);
        return;
    }

    // WATI expects number with country code, no +, no spaces
    $mobile_no = preg_replace('/[^0-9]/', '', $mobile_no);

    // ==== WATI CONFIG — fill these in ====
    $wati_tenant_id = '1034642';
    $wati_api_key   = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1bmlxdWVfbmFtZSI6ImdhdXRhbWF5dXJ2ZWRhc3RvcmVAZ21haWwuY29tIiwibmFtZWlkIjoiZ2F1dGFtYXl1cnZlZGFzdG9yZUBnbWFpbC5jb20iLCJlbWFpbCI6ImdhdXRhbWF5dXJ2ZWRhc3RvcmVAZ21haWwuY29tIiwiYXV0aF90aW1lIjoiMDcvMjkvMjAyNiAxMzozNTo1MiIsInRlbmFudF9pZCI6IjEwMzQ2NDIiLCJkYl9uYW1lIjoibXQtcHJvZC1UZW5hbnRzIiwiaHR0cDovL3NjaGVtYXMubWljcm9zb2Z0LmNvbS93cy8yMDA4LzA2L2lkZW50aXR5L2NsYWltcy9yb2xlIjoiQURNSU5JU1RSQVRPUiIsImV4cCI6MjUzNDAyMzAwODAwLCJpc3MiOiJDbGFyZV9BSSIsImF1ZCI6IkNsYXJlX0FJIn0.Rw4N6Ie7cd5IKokcswqoiuRzXJz-S55mqKe8EJu4vwo';
    $template_name  = 'sales_followup_contact_v1';
    $broadcast_name = 'consultation_confirmation';

    $full_name = $this->session->userdata('USERNAME');

$agent_name = 'Team';

if (!empty($full_name)) {
    $row = $this->db
        ->select('username')
        ->from('tbl_registration')
        ->where('full_name', $full_name)
        ->limit(1)
        ->get()
        ->row();

    if ($row && !empty($row->username)) {
        $agent_name = $row->username;
    }
}

    $url = "https://live-mt-server.wati.io/{$wati_tenant_id}/api/v1/sendTemplateMessage?whatsappNumber={$mobile_no}";

    $payload = [
        "template_name"  => $template_name,
        "broadcast_name" => $broadcast_name,
        "parameters" => [
            ["name" => "agent_name", "value" => $agent_name]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $wati_api_key
    ]);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($http_code == 200 && isset($result['result']) && $result['result'] == true) {
        echo json_encode(['status' => 'success', 'message' => 'Template sent.']);
    } else {
        echo json_encode([
            'status'  => 'error',
            'message' => $result['info'] ?? 'WATI API error.'
        ]);
    }
}
public function send_wati_cron()
{
    // Optional: restrict this endpoint to CLI or a secret token if hit via URL cron
    // if (!$this->input->is_cli_request()) {
    //     $token = $this->input->get('token');
    //     if ($token !== 'YOUR_SECRET_CRON_TOKEN') {
    //         echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    //         return;
    //     }
    // }

    $this->load->database();

    // 1. Get all leads assigned to someone, AND not yet greeted
    $this->db->where('assigned_to !=', '');
    $this->db->where('(whatsapp_greetings IS NULL OR whatsapp_greetings != 1)', null, false);
    $leads = $this->db->get('tbl_lead')->result();

    if (empty($leads)) {
        echo json_encode([
            'status' => 'success',
            'message' => 'No leads to process.'
        ]);
        return;
    }

    // ==== WATI CONFIG ====
    $wati_tenant_id = '1034642';

    $wati_api_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1bmlxdWVfbmFtZSI6ImdhdXRhbWF5dXJ2ZWRhc3RvcmVAZ21haWwuY29tIiwibmFtZWlkIjoiZ2F1dGFtYXl1cnZlZGFzdG9yZUBnbWFpbC5jb20iLCJlbWFpbCI6ImdhdXRhbWF5dXJ2ZWRhc3RvcmVAZ21haWwuY29tIiwiYXV0aF90aW1lIjoiMDcvMjkvMjAyNiAxMzozNTo1MiIsInRlbmFudF9pZCI6IjEwMzQ2NDIiLCJkYl9uYW1lIjoibXQtcHJvZC1UZW5hbnRzIiwiaHR0cDovL3NjaGVtYXMubWljcm9zb2Z0LmNvbS93cy8yMDA4LzA2L2lkZW50aXR5L2NsYWltcy9yb2xlIjoiQURNSU5JU1RSQVRPUiIsImV4cCI6MjUzNDAyMzAwODAwLCJpc3MiOiJDbGFyZV9BSSIsImF1ZCI6IkNsYXJlX0FJIn0.Rw4N6Ie7cd5IKokcswqoiuRzXJz-S55mqKe8EJu4vwo';

    $template_name  = 'sales_first_contact_v4';
    $broadcast_name = 'consultation_followup';

    $results = [];

    foreach ($leads as $lead) {

        if (empty($lead->mobile_no)) {
            $results[] = "Lead {$lead->id}: skipped, no customer mobile.";
            continue;
        }

        // 2. Get assigned agent
        // assigned_to contains the tbl_registration ID
       // 2. Get assigned agent by full_name
$agent = $this->db
    ->where('full_name', $lead->assigned_to)
    ->get('tbl_registration')
    ->row();

if (!$agent) {
    $results[] = "Lead {$lead->id}: skipped, agent not found. assigned_to = {$lead->assigned_to}";
    continue;
}

// REAL AGENT USERNAME
$agent_name = !empty($agent->username)
    ? $agent->username
    : $agent->full_name;

// Agent phone / IVR number
$ivr_number = !empty($agent->ivr_number)
    ? $agent->ivr_number
    : $agent->mobile_no;

        $customer_no = preg_replace('/[^0-9]/', '', $lead->mobile_no);

        $url = "https://live-mt-server.wati.io/{$wati_tenant_id}/api/v1/sendTemplateMessage?whatsappNumber={$customer_no}";

        $payload = [
            "template_name"  => $template_name,
            "broadcast_name" => $broadcast_name,
            "parameters" => [
                [
                    "name"  => "agent_name",
                    "value" => $agent_name
                ],
                [
                    "name"  => "ivr_number",
                    "value" => $ivr_number
                ]
            ]
        ];

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $wati_api_key
        ]);

        $response  = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $result = json_decode($response, true);

        if (
            $http_code == 200 &&
            isset($result['result']) &&
            $result['result'] == true
        ) {

            // 3. Mark as greeted
            $this->db->where('id', $lead->id);

            $this->db->update('tbl_lead', [
                'whatsapp_greetings' => 1
            ]);

            $results[] =
                "Lead {$lead->id}: sent to {$lead->mobile_no} "
                . "(Agent: {$agent_name}, IVR: {$ivr_number}).";

        } else {

            // Don't mark as greeted if WATI failed
            $results[] =
                "Lead {$lead->id}: FAILED - "
                . ($result['info'] ?? 'Unknown WATI error.');
        }

        // Small delay between API calls
        usleep(300000);
    }

    echo json_encode([
        'status'  => 'success',
        'results' => $results
    ]);
}

// 🎬 REELS FOLLOWUP — single lead, called from the "Send" button on the leads list
public function send_reels_followup()
{
    header('Content-Type: application/json');

    $lead_id = $this->input->post('lead_id');

    if (empty($lead_id)) {
        echo json_encode(['status' => 'error', 'message' => 'lead_id is required']);
        return;
    }

    $lead = $this->db->where('id', $lead_id)->get('tbl_lead')->row();

    if (!$lead) {
        echo json_encode(['status' => 'error', 'message' => 'Lead not found']);
        return;
    }

    // Prefer WhatsApp number if the lead has one, else fall back to primary mobile
    $mobile_no = !empty($lead->secondary_mobile_no) ? $lead->secondary_mobile_no : $lead->mobile_no;
    $mobile_no = preg_replace('/[^0-9]/', '', $mobile_no);

    if (empty($mobile_no)) {
        echo json_encode(['status' => 'error', 'message' => 'No valid mobile/whatsapp number on file']);
        return;
    }

    $mobile_no = '91' . substr($mobile_no, -10);

    // ==== WATI CONFIG (same tenant/token as your other WATI methods) ====
    $wati_tenant_id = '1034642';
    $wati_api_key   = $this->config->item('wati_api_key');
    $template_name  = 'reels_followups';
    $broadcast_name = 'reels_followup';

    // ⚠️ Adjust this parameter list to match whatever variables the
    // approved "reels_followup" template actually has.
    $payload = [
        "template_name"  => $template_name,
        "broadcast_name" => $broadcast_name,
        "parameters" => [
            ["name" => "name", "value" => $lead->customer_name]
        ]
    ];

    $url = "https://live-mt-server.wati.io/{$wati_tenant_id}/api/v1/sendTemplateMessage?whatsappNumber={$mobile_no}";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $wati_api_key
    ]);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($http_code == 200 && isset($result['result']) && $result['result'] == true) {

        // Optional: log it the same way send_wati_message() does.
        // Requires a lead_whatsapp_messages table (already used elsewhere in this controller).
        $this->db->insert('lead_whatsapp_messages', [
            'lead_id'       => $lead_id,
            'direction'     => 'outbound',
            'message'       => null,
            'template_name' => $template_name,
            'sent_at'       => date('Y-m-d H:i:s'),
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Reels followup sent.']);
    } else {
        error_log("WATI send_reels_followup failed: " . $response);
        echo json_encode([
            'status'  => 'error',
            'message' => $result['info'] ?? 'WATI API error.'
        ]);
    }
}
}