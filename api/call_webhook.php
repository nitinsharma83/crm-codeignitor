<?php
/**
 * api/call_webhook.php  - TataTele call-completed webhook (hardened)
 *
 * Changes vs. the old version:
 *  - no display_errors on a public endpoint (errors go to the private log instead)
 *  - DB credentials come from ../db_config.php (one place to rotate the password)
 *  - raw payload logged to /logs_private with a 5 MB rotation (no more 58 MB public txt files)
 *  - removed the date_debug.txt print_r dump (it grew to 89 MB)
 *  - proper HTTP status codes (200 on success, 400 bad payload, 500 DB failure)
 *    so TataTele can see real failures and retry
 *  - DB errors are logged, not echoed back to the caller
 */
date_default_timezone_set('Asia/Kolkata');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Report DB errors as return values (so they get logged) instead of throwing uncaught exceptions
mysqli_report(MYSQLI_REPORT_OFF);

require_once __DIR__ . '/../webhook_common.php';
require_once __DIR__ . '/../db_config.php';   // provides $mysqli

if (!isset($mysqli) || $mysqli->connect_error) {
    wh_log('call_webhook.log', 'DB connect failed: ' . ($mysqli->connect_error ?? 'no connection object'));
    wh_respond(500, ['status' => 'error', 'message' => 'Service unavailable']);
}
$conn = $mysqli;

// ---- read + log payload ----
$input = file_get_contents('php://input');
wh_log('call_webhook.log', $input);

$data = json_decode($input, true);
if (!is_array($data)) {
    wh_respond(400, ['status' => 'error', 'message' => 'Invalid JSON']);
}

function formatDate($d) {
    if (empty($d)) return null;
    $d = str_replace(['T', 'Z'], [' ', ''], $d);
    $d = preg_replace('/\.\d+/', '', $d);
    return trim($d);
}

// TataTele sometimes sends this key with a trailing space / different name
if (isset($data['customer_no_with_prefix '])) {
    $data['customer_no_with_prefix'] = $data['customer_no_with_prefix '];
}
if (isset($data['hangupcause_desc'])) {
    $data['hangup_cause_description'] = $data['hangupcause_desc'];
}

// JSON key => DB column
$map = [
    "uuid" => "uuid", "call_to_number" => "call_to_number", "caller_id_number" => "caller_id_number",
    "start_stamp" => "start_stamp", "answer_stamp" => "answer_stamp", "end_stamp" => "end_stamp",
    "billsec" => "billsec", "digits_dialed" => "digits_dialed", "direction" => "direction",
    "duration" => "duration", "answered_agent" => "answered_agent", "missed_agent" => "missed_agent",
    "call_flow" => "call_flow", "broadcast_lead_fields" => "broadcast_lead_fields",
    "recording_url" => "recording_url", "call_id" => "call_id", "outbound_sec" => "outbound_sec",
    "agent_ring_time" => "agent_ring_time", "agent_transfer_ring_time" => "agent_transfer_ring_time",
    "billing_circle" => "billing_circle", "aws_call_recording_identifier" => "aws_call_recording_identifier",
    "customer_no_with_prefix" => "customer_number_with_prefix", "campaign_name" => "campaign_name",
    "campaign_id" => "campaign_id", "hangup_cause_description" => "hangup_cause_description",
    "hangup_cause_code" => "hangup_cause_code", "hangup_cause_key" => "hangup_cause_key",
    "answered_agent_name" => "answered_agent_name", "answered_agent_number" => "answered_agent_number",
    "call_status" => "call_status", "call_connected" => "call_connected",
    "customer_ring_time" => "customer_ring_time", "reason_key" => "reason_key", "ref_id" => "ref_id",
];

$columns = $placeholders = $values = [];
foreach ($map as $jsonKey => $dbColumn) {
    if (!isset($data[$jsonKey])) continue;
    $value = $data[$jsonKey];
    if (in_array($jsonKey, ["start_stamp", "answer_stamp", "end_stamp"], true)) {
        $value = formatDate($value);
    }
    // NOTE: nested objects (answered_agent, call_flow, billing_circle) have always been stored as the
    // text "Array" by the old code. Kept identical on purpose because Api_Controller filters on
    // answered_agent. Change this deliberately later if you want real JSON stored.
    if (is_array($value)) $value = 'Array';
    // strict SQL mode: '' is not a valid INT / DATETIME value
    if (in_array($jsonKey, ['billsec','duration','outbound_sec','agent_ring_time','agent_transfer_ring_time','customer_ring_time'], true) && ($value === '' || !is_numeric($value))) $value = 0;
    if (in_array($jsonKey, ['start_stamp','answer_stamp','end_stamp'], true) && $value === '') $value = null;
    $columns[] = $dbColumn;
    $placeholders[] = '?';
    $values[] = $value;
}

if (empty($columns)) {
    wh_respond(400, ['status' => 'error', 'message' => 'No valid fields']);
}

$sql  = "INSERT INTO ivr_call_logs (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    wh_log('call_webhook.log', 'Prepare failed: ' . $conn->error);
    wh_respond(500, ['status' => 'error', 'message' => 'Could not save call']);
}

$stmt->bind_param(str_repeat('s', count($values)), ...$values);

if ($stmt->execute()) {
    $id = $conn->insert_id;
    $stmt->close();
    wh_respond(200, ['status' => 'success', 'insert_id' => $id]);
}

wh_log('call_webhook.log', 'Insert failed: ' . $stmt->error . ' | call_id=' . ($data['call_id'] ?? ''));
$stmt->close();
wh_respond(500, ['status' => 'error', 'message' => 'Could not save call']);
