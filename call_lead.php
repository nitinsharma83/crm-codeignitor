<?php

date_default_timezone_set('Asia/Kolkata');

$servername = "localhost";
$username   = "u457158986_team";
$password   = "#Team@123";
$dbname     = "u457158986_team";

$mysqli = new mysqli($servername, $username, $password, $dbname);

if ($mysqli->connect_error) {
    die("DB Connection failed: " . $mysqli->connect_error);
}

if (isset($_GET['call']) && !empty($_GET['call'])) {

    $lead_id = intval(base64_decode($_GET['call']));

    $leadRes = $mysqli->query("
        SELECT *
        FROM tbl_lead
        WHERE id = '{$lead_id}'
        LIMIT 1
    ");

    if (!$leadRes || $leadRes->num_rows == 0) {
        die("Lead not found");
    }

    $lead = $leadRes->fetch_assoc();

    // ===================================================
    // MOBILE NORMALIZATION
    // ===================================================

    // Original number from database
    $mobile = trim($lead['mobile_no']);

    // Keep only digits
    $cleanedNumber = preg_replace('/\D/', '', $mobile);

    // Remove all leading zeros
    $cleanedNumber = ltrim($cleanedNumber, '0');

    // Remove country code 91 if present
    if (strlen($cleanedNumber) > 10 && substr($cleanedNumber, 0, 2) == '91') {
        $cleanedNumber = substr($cleanedNumber, 2);
    }

    // Keep only last 10 digits
    if (strlen($cleanedNumber) > 10) {
        $cleanedNumber = substr($cleanedNumber, -10);
    }

    // Validate Indian mobile number
    if (!preg_match('/^[6-9][0-9]{9}$/', $cleanedNumber)) {
        die("Invalid Mobile Number: " . $mobile);
    }

    // Final number to send to Smartflo
    $customerNumber = "91" . $cleanedNumber;

    // ===================================================
    // USER SESSION
    // ===================================================

    session_start();

    $loggedUser = isset($_SESSION['USERNAME'])
        ? trim($_SESSION['USERNAME'])
        : '';

    if (empty($loggedUser)) {
        die("Session Username Missing");
    }

    $loggedUser = $mysqli->real_escape_string($loggedUser);

    $userRes = $mysqli->query("
        SELECT location
        FROM tbl_registration
        WHERE full_name = '$loggedUser'
        LIMIT 1
    ");

    if (!$userRes || $userRes->num_rows == 0) {
        die("User Not Found In tbl_registration");
    }

    $userData = $userRes->fetch_assoc();

    $apiKey = trim($userData['location']);

    if (empty($apiKey)) {
        die("API Key Missing In location Column");
    }

    // ===================================================
    // SMARTFLO API
    // ===================================================

    // ===================================================
// SMARTFLO API
// ===================================================

// Bearer token from Smartflo dashboard (API / Developer section).
// Better: store it in a config file outside public_html.
$apiToken = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiI1ODU1NjQiLCJjciI6ZmFsc2UsImlzcyI6Imh0dHBzOi8vY2xvdWRwaG9uZS50YXRhdGVsZXNlcnZpY2VzLmNvbS90b2tlbi9nZW5lcmF0ZSIsImlhdCI6MTc1NzUwOTczMiwiZXhwIjoyMDU3NTA5NzMyLCJuYmYiOjE3NTc1MDk3MzIsImp0aSI6IlJOOEJYWmtvREZpUEhLMUgifQ.SNkmrkCfiEpg5kJk4hGTol9sCZ2dwSiERrXpMh_KYgM";

$payload = [
    "api_key"         => $apiKey,
    "customer_number" => $cleanedNumber,   // see note 1 below
    "async"           => 1,                // replaces get_call_id per new docs
    "customer_ring_timeout" => 30,
    "call_timeout"    => 300,
    "custom_identifier" => [
        "lead_id" => (string)$lead_id
    ]
    // "caller_id" => "9180694XXXXX",      // add if your account requires it
];

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://api-smartflo.tatateleservices.com/v1/click_to_call_support",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        "Accept: application/json",
        "Content-Type: application/json",
        "Authorization: Bearer " . $apiToken
    ],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
]);

    $response  = curl_exec($curl);
    $curlError = curl_error($curl);
    $httpCode  = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    // ===================================================
    // HANDLE cURL LEVEL FAILURE
    // ===================================================

    if ($curlError || $response === false) {
        echo "<script>
            alert('Call Failed: Unable to reach calling service. Please try again.');
            window.history.back();
        </script>";
        exit;
    }

    // ===================================================
    // PARSE RESPONSE
    // ===================================================

    $responseData = json_decode($response, true);

    $status  = !empty($responseData['success']);
    $message = isset($responseData['message']) ? $responseData['message'] : '';

    // Grab whatever text field is available to check for "Originate failed"
    $checkText = '';
    if (!empty($responseData['message'])) {
        $checkText = $responseData['message'];
    } elseif (!empty($responseData['error'])) {
        $checkText = $responseData['error'];
    } elseif (!empty($responseData['reason'])) {
        $checkText = $responseData['reason'];
    } else {
        $checkText = $response; // fallback: raw response text
    }

    // ===================================================
    // RESULT HANDLING
    // ===================================================

    if ($status) {

        echo "<script>
            alert('Call Connected Successfully');
            window.history.back();
        </script>";

    } elseif (stripos($checkText, 'Originate failed') !== false) {

        echo "<script>
            alert('Patient is busy or Switched off');
            window.history.back();
        </script>";

    } else {

        $safeMessage = addslashes($message ?: 'Unknown error');
        echo "<script>
            alert('Call Failed: " . $safeMessage . "');
            window.history.back();
        </script>";
    }

    exit;
}
?>