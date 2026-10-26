<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Call_lead extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->load->database();
        $this->load->library('session');
    }

    public function index()
    {

        $lead_id = base64_decode($this->input->get('call'));

        $lead_id = intval($lead_id);

        $lead = $this->db
            ->where('id', $lead_id)
            ->get('tbl_lead')
            ->row_array();

        if(!$lead){

            die("Lead Not Found");
        }

        $mobile = trim($lead['mobile_no']);

// Keep only digits
$cleanedNumber = preg_replace('/\D/', '', $mobile);

// Remove leading zeros
$cleanedNumber = ltrim($cleanedNumber, '0');

// Remove 91 country code if present
if (strlen($cleanedNumber) > 10 && substr($cleanedNumber, 0, 2) == '91') {
    $cleanedNumber = substr($cleanedNumber, 2);
}

// Keep only last 10 digits
$cleanedNumber = substr($cleanedNumber, -10);

// Validate
if (!preg_match('/^[6-9][0-9]{9}$/', $cleanedNumber)) {
    die("Invalid Mobile Number: " . $mobile);
}

$customerNumber = '91' . $cleanedNumber;

        // LOGGED IN USER
        $username = $this->session->userdata('USERNAME');

        if(empty($username)){

            die("Session Expired");
        }

        // USER API
        $user = $this->db
            ->where('full_name', $username)
            ->get('tbl_registration')
            ->row_array();

        if(!$user){

            die("User Not Found");
        }

        $apiKey = trim($user['location']);

        if(empty($apiKey)){

            die("API Key Missing");
        }

        // CURL
                // CURL
        $apiToken = $this->config->item('smartflo_token');

        if (empty($apiToken)) {
            die("Smartflo API Token Missing");
        }

        $postData = [
            'api_key'               => $apiKey,
            'customer_number'       => $cleanedNumber,   // try $customerNumber ('91'.xxx) if this fails
            'async'                 => 1,
            'customer_ring_timeout' => 30,
            'call_timeout'          => 300,
            'custom_identifier'     => [
                'lead_id' => (string)$lead_id
            ],
            // 'caller_id' => '9180694XXXXX',  // add if your account requires it
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => "https://api-smartflo.tatateleservices.com/v1/click_to_call_support",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($postData),
            CURLOPT_HTTPHEADER     => [
                "Accept: application/json",
                "Content-Type: application/json",
                "Authorization: Bearer " . $apiToken
            ],
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $response = curl_exec($curl);
        $err      = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        // Log the real response so you can see exactly what Smartflo says
        log_message('error', "Smartflo HTTP {$httpCode}: " . $response);

        if ($err || $response === false) {

            echo "<script>
                alert('Call Failed: Unable to reach calling service. Please try again.');
                history.back();
            </script>";
            return;
        }

        $responseData = json_decode($response, true);

        // Find any message-like field
        $message = '';
        foreach (['message', 'error', 'reason'] as $key) {
            if (!empty($responseData[$key]) && is_string($responseData[$key])) {
                $message = $responseData[$key];
                break;
            }
        }
        if ($message === '') {
            $message = "HTTP {$httpCode}";
        }

        if (!empty($responseData['success'])) {

            echo "<script>
                alert('Call Initiated Successfully');
                history.back();
            </script>";

        } elseif (stripos($message, 'Originate failed') !== false) {

            echo "<script>
                alert('Patient is busy or Switched off');
                history.back();
            </script>";

        } else {

            echo "<script>
                alert('Call Failed: " . addslashes($message) . "');
                history.back();
            </script>";
        }
    }
}