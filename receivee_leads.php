<?php
// Lead receiver - AWS / strict-SQL-mode safe version
date_default_timezone_set('Asia/Kolkata');
ob_start();                                   // swallow stray PHP warnings so they never corrupt the response
mysqli_report(MYSQLI_REPORT_OFF);             // PHP 8.1+: return errors instead of throwing

function respond($code, $arr) {
    if (ob_get_length()) { ob_end_clean(); }
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($arr);
    exit;
}

// ---- 1. Read incoming data (POST form, JSON body, or GET) ----
$lead_data = array();
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $lead_data = $_GET;
} else {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    $lead_data = is_array($json) ? $json : $_POST;
}
error_log('receivee_leads received: ' . json_encode($lead_data));

// ---- 2. Map alternate field names (Laravel website form) to CRM columns ----
$alias = array(
    'email'   => 'email_id',
    'phone'   => 'mobile_no',
    'name'    => 'customer_name',
    'address' => 'city',
    'message' => 'description',
);
foreach ($alias as $from => $to) {
    if (isset($lead_data[$from]) && !isset($lead_data[$to])) {
        $lead_data[$to] = $lead_data[$from];
    }
}

// Build "requirement" from booking details if not sent directly
if (empty($lead_data['requirement'])) {
    $parts = array();
    if (!empty($lead_data['specialist'])) $parts[] = 'Specialist: ' . $lead_data['specialist'];
    if (!empty($lead_data['treatments'])) $parts[] = 'Treatment: ' . $lead_data['treatments'];
    if (!empty($lead_data['date']))       $parts[] = 'Date: ' . $lead_data['date'];
    if (!empty($lead_data['time']))       $parts[] = 'Time: ' . $lead_data['time'];
    if (!empty($lead_data['description'])) $parts[] = 'Message: ' . $lead_data['description'];
    $lead_data['requirement'] = implode(' | ', $parts);
}

// ---- 3. Every column that is NOT NULL without a DB default gets a value ----
$now = date('Y-m-d H:i:s');
$row = array(
    'lead_id'             => 'WEB' . time() . rand(10, 99),
    'customer_name'       => '',
    'company_name'        => '',
    'city'                => '',
    'state'               => '',
    'mobile_no'           => '',
    'secondary_mobile_no' => '',
    'email_id'            => '',
    'requirement'         => '',
    'description'         => '',
    'lead_source'         => 'Gautam .com',
    'status'              => 'Not Contacted',
    'userid'              => 0,
    'assigned_by'         => '',
    'assigned_to'         => '',
    'created_date'        => $now,
    'tarik'               => date('Y-m-d'),
    'today'               => date('Y-m-d'),
    'team_head'           => '',
    'api'                 => 'API',
    'whatsapp_greetings'  => 0,
);
foreach ($row as $field => $default) {
    if (isset($lead_data[$field]) && is_scalar($lead_data[$field]) && trim((string)$lead_data[$field]) !== '') {
        $row[$field] = trim((string)$lead_data[$field]);
    }
}

// Column length limits (strict mode rejects text that is too long)
$limits = array('lead_id'=>200,'customer_name'=>500,'company_name'=>200,'city'=>200,'state'=>200,
                'mobile_no'=>200,'secondary_mobile_no'=>30,'email_id'=>200,'requirement'=>1000,
                'description'=>200,'lead_source'=>500,'status'=>200,'assigned_by'=>200,'assigned_to'=>200,
                'today'=>35,'team_head'=>35);
foreach ($limits as $field => $max) {
    $row[$field] = (string)$row[$field];
    if (function_exists('mb_strcut')) { $row[$field] = mb_strcut($row[$field], 0, $max, 'UTF-8'); }
    else { $row[$field] = substr($row[$field], 0, $max); }
}
$row['userid'] = (int)$row['userid'];
$row['whatsapp_greetings'] = (int)$row['whatsapp_greetings'];

if ($row['mobile_no'] === '' && $row['customer_name'] === '') {
    respond(400, array('status' => 'error', 'message' => 'mobile_no or customer_name is required'));
}

// ---- 4. Database (re-uses the CRM's own db_config.php, no duplicate password here) ----
require_once __DIR__ . '/db_config.php';      // defines $mysqli
if (!isset($mysqli) || $mysqli->connect_errno) {
    error_log('receivee_leads DB connect failed: ' . (isset($mysqli) ? $mysqli->connect_error : 'no $mysqli'));
    respond(500, array('status' => 'error', 'message' => 'Database connection failed'));
}

$fields = array_keys($row);
$sql  = 'INSERT INTO `tbl_lead` (`' . implode('`,`', $fields) . '`) VALUES (' . implode(',', array_fill(0, count($fields), '?')) . ')';
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    error_log('receivee_leads prepare failed: ' . $mysqli->error);
    respond(500, array('status' => 'error', 'message' => 'SQL prepare failed'));
}
$values = array_values($row);
$stmt->bind_param(str_repeat('s', count($values)), ...$values);

if (!$stmt->execute()) {
    error_log('receivee_leads insert failed: ' . $stmt->error);
    respond(500, array('status' => 'error', 'message' => 'Error inserting lead'));
}
$insert_id = $stmt->insert_id;
$stmt->close();
$mysqli->close();

// ---- 5. Respond: browsers (plain HTML form posts) get redirected, API callers get JSON ----
$accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
if (strpos($accept, 'text/html') !== false && strpos($accept, 'json') === false) {
    if (ob_get_length()) { ob_end_clean(); }
    header('Location: https://temp5.gautamayurveda.com/thank-you.php');
    exit;
}
respond(200, array('status' => 'success', 'id' => $insert_id, 'lead_id' => $row['lead_id']));