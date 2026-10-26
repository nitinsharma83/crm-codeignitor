<?php
/**
 * missed_call_webhook.php - missed-call webhook (hardened)
 * Same table, same columns, same values as before. Changes: no public error output,
 * DB config from db_config.php, private rotating log, real HTTP status codes.
 */
date_default_timezone_set('Asia/Kolkata');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Report DB errors as return values (so they get logged) instead of throwing uncaught exceptions
mysqli_report(MYSQLI_REPORT_OFF);

require_once __DIR__ . '/webhook_common.php';
require_once __DIR__ . '/db_config.php';   // provides $mysqli

if (!isset($mysqli) || $mysqli->connect_error) {
    wh_log('missed_call_webhook.log', 'DB connect failed: ' . ($mysqli->connect_error ?? 'no connection object'));
    wh_respond(500, ["status" => "error", "message" => "Service unavailable"]);
}
$conn = $mysqli;

$raw  = file_get_contents('php://input');
wh_log('missed_call_webhook.log', $raw);
$data = json_decode($raw, true);

if (!is_array($data) || empty($data)) {
    wh_respond(400, ["status" => "error", "message" => "No data received or invalid JSON"]);
}

// nested objects were always stored as "Array" by the old code - kept identical
function flat($v) { return is_array($v) ? 'Array' : $v; }
// Strict SQL mode rejects '' in INT / DATETIME columns (old server silently stored 0). Normalise them.
function int_or_zero($v) { return ($v === '' || $v === null || !is_numeric($v)) ? 0 : (int)$v; }
function dt_or_null($v)  { return ($v === '' || $v === null || strpos((string)$v, '0000-00-00') === 0) ? null : $v; }

$status = flat($data['call_status'] ?? "pending");

$stmt = $conn->prepare("
    INSERT INTO tbl_missed_call_logs (
        uuid, call_to_number, caller_id_number, start_stamp, answer_stamp, end_stamp,
        hangup_cause, billsec, digits_dialed, direction, duration, answered_agent,
        missed_agent, call_flow, broadcast_lead_fields, recording_url, call_id,
        outbound_sec, agent_ring_time, billing_circle, aws_call_recording_identifier,
        customer_no_with_prefix, campaign_name, campaign_id, status, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
");
if (!$stmt) {
    wh_log('missed_call_webhook.log', 'Prepare failed: ' . $conn->error);
    wh_respond(500, ["status" => "error", "message" => "Could not save call"]);
}

$uuid = flat($data['uuid'] ?? null);
$call_to_number = flat($data['call_to_number'] ?? null);
$caller_id_number = flat($data['caller_id_number'] ?? null);
$start_stamp = dt_or_null(flat($data['start_stamp'] ?? null));
$answer_stamp = dt_or_null(flat($data['answer_stamp'] ?? null));
$end_stamp = dt_or_null(flat($data['end_stamp'] ?? null));
$hangup_cause = flat($data['hangup_cause'] ?? null);
$billsec = int_or_zero(flat($data['billsec'] ?? null));
$digits_dialed = flat($data['digits_dialed'] ?? null);
$direction = flat($data['direction'] ?? null);
$duration = int_or_zero(flat($data['duration'] ?? null));
$answered_agent = flat($data['answered_agent'] ?? null);
$missed_agent = flat($data['missed_agent'] ?? null);
$call_flow = flat($data['call_flow'] ?? null);
$broadcast_lead_fields = flat($data['broadcast_lead_fields'] ?? null);
$recording_url = flat($data['recording_url'] ?? null);
$call_id = flat($data['call_id'] ?? null);
$outbound_sec = int_or_zero(flat($data['outbound_sec'] ?? null));
$agent_ring_time = int_or_zero(flat($data['agent_ring_time'] ?? null));
$billing_circle = flat($data['billing_circle'] ?? null);
$aws_call_recording_identifier = flat($data['aws_call_recording_identifier'] ?? null);
$customer_no_with_prefix = flat($data['customer_no_with_prefix'] ?? ($data['customer_no_with_prefix '] ?? null));
$campaign_name = flat($data['campaign_name'] ?? null);
$campaign_id = flat($data['campaign_id'] ?? null);

$stmt->bind_param(
    "sssssssssssssssssssssssss",
    $uuid, $call_to_number, $caller_id_number, $start_stamp, $answer_stamp, $end_stamp,
    $hangup_cause, $billsec, $digits_dialed, $direction, $duration, $answered_agent,
    $missed_agent, $call_flow, $broadcast_lead_fields, $recording_url, $call_id,
    $outbound_sec, $agent_ring_time, $billing_circle, $aws_call_recording_identifier,
    $customer_no_with_prefix, $campaign_name, $campaign_id, $status
);

if ($stmt->execute()) {
    $stmt->close();
    wh_respond(200, ["status" => "success", "message" => "Data inserted successfully"]);
}

wh_log('missed_call_webhook.log', 'Insert failed: ' . $stmt->error . ' | call_id=' . $call_id);
$stmt->close();
wh_respond(500, ["status" => "error", "message" => "Could not save call"]);
