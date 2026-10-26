<?php
/**
 * webhook.php - call-status webhook (hardened)
 * Same table (tbl_call_logs), same columns, same values (created_at stays UTC_TIMESTAMP()).
 * Changes: no public error output, DB config from db_config.php, private rotating log,
 * real HTTP status codes.
 */
date_default_timezone_set('Asia/Kolkata');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Report DB errors as return values (so they get logged) instead of throwing uncaught exceptions
mysqli_report(MYSQLI_REPORT_OFF);

require_once __DIR__ . '/webhook_common.php';
require_once __DIR__ . '/db_config.php';   // provides $mysqli

if (!isset($mysqli) || $mysqli->connect_error) {
    wh_log('webhook.log', 'DB connect failed: ' . ($mysqli->connect_error ?? 'no connection object'));
    wh_respond(500, ["status" => "error", "message" => "Service unavailable"]);
}
$conn = $mysqli;

$raw  = file_get_contents('php://input');
wh_log('webhook.log', $raw);
$data = json_decode($raw, true);

if (!is_array($data) || empty($data)) {
    wh_respond(400, ["status" => "error", "message" => "No data received or invalid JSON"]);
}

// nested objects were always stored as "Array" by the old code - kept identical
function flat($v) { return is_array($v) ? 'Array' : $v; }

$status = flat($data['call_status'] ?? "pending");

$stmt = $conn->prepare("
    INSERT INTO tbl_call_logs (
        uuid, call_to_number, caller_id_number, start_stamp, answer_agent_number,
        call_id, billing_circle, call_status, direction, customer_no_with_prefix, status, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, UTC_TIMESTAMP())
");
if (!$stmt) {
    wh_log('webhook.log', 'Prepare failed: ' . $conn->error);
    wh_respond(500, ["status" => "error", "message" => "Could not save call"]);
}

$uuid                    = flat($data['uuid'] ?? null);
$call_to_number          = flat($data['call_to_number'] ?? null);
$caller_id_number        = flat($data['caller_id_number'] ?? null);
$start_stamp             = flat($data['start_stamp'] ?? null);
$answer_agent_number     = flat($data['answer_agent_number'] ?? null);
$call_id                 = flat($data['call_id'] ?? null);
$billing_circle          = flat($data['billing_circle'] ?? null);
$call_status_field       = $status;
$direction               = flat($data['direction'] ?? null);
$customer_no_with_prefix = flat($data['customer_no_with_prefix'] ?? ($data['customer_no_with_prefix '] ?? null));

$stmt->bind_param(
    "sssssssssss",
    $uuid, $call_to_number, $caller_id_number, $start_stamp, $answer_agent_number,
    $call_id, $billing_circle, $call_status_field, $direction, $customer_no_with_prefix,
    $call_status_field
);

if ($stmt->execute()) {
    $stmt->close();
    wh_respond(200, ["status" => "success", "message" => "Data inserted successfully"]);
}

wh_log('webhook.log', 'Insert failed: ' . $stmt->error . ' | call_id=' . $call_id);
$stmt->close();
wh_respond(500, ["status" => "error", "message" => "Could not save call"]);
