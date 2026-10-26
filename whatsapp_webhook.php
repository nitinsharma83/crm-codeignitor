<?php
header("Content-Type: text/plain");
http_response_code(200);
echo "OK";
ignore_user_abort(true);
ob_flush();
flush();
date_default_timezone_set('Asia/Kolkata');

function clean_mobile($number){
    return substr(preg_replace('/\D/', '', $number), -10);
}

$input = file_get_contents("php://input");
$data  = json_decode($input, true);
if (!$data || !isset($data['eventType']) || $data['eventType'] !== 'message') {
    exit;
}

$conn = new mysqli("localhost", "u457158986_team", "#Team@123", "u457158986_team");
if ($conn->connect_error) {
    error_log("DB Error: " . $conn->connect_error);
    exit;
}

$lead_id       = $data['id'] ?? '';
$customer_name = $data['senderName'] ?? 'Unknown';
$mobile_no     = clean_mobile($data['waId'] ?? '');
$email_id      = $data['operatorEmail'] ?? '';
$operator_name = $data['operatorName'] ?? '';
$requirement   = $data['type'] ?? '';
$description   = $data['text'] ?? '';
$created_date  = !empty($data['created'])
    ? date('Y-m-d H:i:s', strtotime($data['created']))
    : date('Y-m-d H:i:s');

/* ==============================
   DUPLICATE CHECK
   WATI can redeliver the same event on retry — skip if we've
   already logged this exact wati_id.
============================== */
if (!empty($lead_id)) {
    $dupCheck = $conn->prepare("SELECT id FROM tbl_wati_webhook WHERE wati_id = ? LIMIT 1");
    $dupCheck->bind_param("s", $lead_id);
    $dupCheck->execute();
    $dupCheck->store_result();
    $isDuplicate = $dupCheck->num_rows > 0;
    $dupCheck->close();

    if ($isDuplicate) {
        $conn->close();
        exit; // already logged this event, nothing to do
    }
}

$logStmt = $conn->prepare("
    INSERT INTO tbl_wati_webhook
    (wati_id, customer_name, mobile_no, operator_email, operator_name, requirement, description, created_date, payload)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$logStmt->bind_param(
    "sssssssss",
    $lead_id,
    $customer_name,
    $mobile_no,
    $email_id,
    $operator_name,
    $requirement,
    $description,
    $created_date,
    $input
);
if (!$logStmt->execute()) {
    error_log("Webhook Log Insert Error: " . $logStmt->error);
}
$logStmt->close();
$conn->close();
exit;
?>