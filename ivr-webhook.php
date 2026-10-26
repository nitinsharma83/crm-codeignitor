<?php
/**
 * ivr-webhook.php - IVR call webhook (hardened)
 * Same hardening as api/call_webhook.php: no public error output, shared DB config,
 * private rotating log, real HTTP status codes.
 */
date_default_timezone_set('Asia/Kolkata');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Report DB errors as return values (so they get logged) instead of throwing uncaught exceptions
mysqli_report(MYSQLI_REPORT_OFF);

require_once __DIR__ . '/webhook_common.php';
require_once __DIR__ . '/db_config.php';   // provides $mysqli

if (!isset($mysqli) || $mysqli->connect_error) {
    wh_log('ivr_webhook.log', 'DB connect failed: ' . ($mysqli->connect_error ?? 'no connection object'));
    wh_respond(500, ["status" => "error", "msg" => "Service unavailable"]);
}
$conn = $mysqli;

// Convert UTC -> IST
function convertToIST($timestamp) {
    if (empty($timestamp)) return null;
    try {
        $dt = new DateTime($timestamp, new DateTimeZone('UTC'));
        $dt->setTimezone(new DateTimeZone('Asia/Kolkata'));
        return $dt->format('Y-m-d H:i:s');
    } catch (Exception $e) {
        return null;
    }
}

// nested objects were always stored as "Array" by the old code - kept identical (see call_webhook.php note)
function flat($v) { return is_array($v) ? 'Array' : $v; }

$raw  = file_get_contents('php://input');
wh_log('ivr_webhook.log', $raw);
$data = json_decode($raw, true);

if (!is_array($data)) {
    wh_respond(400, ["status" => "error", "msg" => "Invalid JSON"]);
}

$uuid             = flat($data['uuid'] ?? '');
$call_to_number   = flat($data['call_to_number'] ?? '');
$caller_id_number = flat($data['caller_id_number'] ?? '');
$start_stamp      = convertToIST($data['start_stamp'] ?? null);
$answer_stamp     = convertToIST($data['answer_stamp'] ?? null);
$end_stamp        = convertToIST($data['end_stamp'] ?? null);
$billsec          = $data['billsec'] ?? 0;
$digits_dialed    = flat($data['digits_dialed'] ?? '');
$direction        = flat($data['direction'] ?? '');
$duration         = $data['duration'] ?? 0;
$answered_agent   = flat($data['answered_agent'] ?? '');
$missed_agent     = flat($data['missed_agent'] ?? '');
$call_flow        = flat($data['call_flow'] ?? '');
$broadcast_lead_fields = flat($data['broadcast_lead_fields'] ?? '');
$recording_url    = flat($data['recording_url'] ?? '');
$call_id          = flat($data['call_id'] ?? '');
$outbound_sec     = $data['outbound_sec'] ?? 0;
$agent_ring_time  = $data['agent_ring_time'] ?? 0;
$agent_transfer_ring_time = $data['agent_transfer_ring_time'] ?? 0;
$billing_circle   = flat($data['billing_circle'] ?? '');
$aws_call_recording_identifier = flat($data['aws_call_recording_identifier'] ?? '');
$customer_number_with_prefix   = flat($data['customer_no_with_prefix'] ?? ($data['customer_no_with_prefix '] ?? ''));
$campaign_name    = flat($data['campaign_name'] ?? '');
$campaign_id      = flat($data['campaign_id'] ?? '');
$hangup_cause_description = flat($data['hangup_cause_description'] ?? '');
$hangup_cause_code = flat($data['hangup_cause_code'] ?? '');
$hangup_cause_key  = flat($data['hangup_cause_key'] ?? '');

$stmt = $conn->prepare("INSERT INTO ivr_call_logs (
    uuid, call_to_number, caller_id_number, start_stamp, answer_stamp, end_stamp,
    billsec, digits_dialed, direction, duration, answered_agent, missed_agent,
    call_flow, broadcast_lead_fields, recording_url, call_id, outbound_sec,
    agent_ring_time, agent_transfer_ring_time, billing_circle,
    aws_call_recording_identifier, customer_number_with_prefix,
    campaign_name, campaign_id, hangup_cause_description,
    hangup_cause_code, hangup_cause_key
) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

if (!$stmt) {
    wh_log('ivr_webhook.log', 'Prepare failed: ' . $conn->error);
    wh_respond(500, ["status" => "error", "msg" => "Could not save call"]);
}

$stmt->bind_param(
    "ssssssississssssiiiisssssss",
    $uuid, $call_to_number, $caller_id_number, $start_stamp, $answer_stamp, $end_stamp,
    $billsec, $digits_dialed, $direction, $duration, $answered_agent, $missed_agent,
    $call_flow, $broadcast_lead_fields, $recording_url, $call_id, $outbound_sec,
    $agent_ring_time, $agent_transfer_ring_time, $billing_circle,
    $aws_call_recording_identifier, $customer_number_with_prefix,
    $campaign_name, $campaign_id, $hangup_cause_description,
    $hangup_cause_code, $hangup_cause_key
);

if ($stmt->execute()) {
    $stmt->close();
    wh_respond(200, ["status" => "success"]);
}

wh_log('ivr_webhook.log', 'Insert failed: ' . $stmt->error . ' | call_id=' . $call_id);
$stmt->close();
wh_respond(500, ["status" => "error", "msg" => "Could not save call"]);
