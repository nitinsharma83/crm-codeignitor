<?php
/**
 * receive_leads_from_ads.php  (fixed for MySQL strict mode / AWS)
 *
 * - No database password in this file: it uses db_config.php (the same file webhook.php uses,
 *   which stays out of Git).
 * - Fills every NOT NULL tbl_lead column that has no default, so the insert also works when
 *   MySQL strict mode is on (the old Hostinger MariaDB silently filled these with '' / 0).
 * - Every failure is written to logs_private/ads_receiver.log with the real reason.
 *
 * Accepts GET, POST (form) or JSON.  Required: mobile_no.
 */
date_default_timezone_set('Asia/Kolkata');
header('Content-Type: application/json');
mysqli_report(MYSQLI_REPORT_OFF);   // return errors instead of throwing exceptions

function ads_log($msg)
{
    $dir = is_dir(__DIR__ . '/logs_private') ? __DIR__ . '/logs_private' : sys_get_temp_dir();
    @file_put_contents($dir . '/ads_receiver.log', '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n", FILE_APPEND | LOCK_EX);
}
function respond($code, $arr)
{
    http_response_code($code);
    echo json_encode($arr);
    exit;
}
// tbl_lead text columns are utf8 (3-byte): drop emoji / 4-byte characters so strict mode doesn't reject them
function clean($v)
{
    $v = trim((string) $v);
    return preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $v) ?? $v;
}

require_once __DIR__ . '/db_config.php';   // defines $mysqli
if (!isset($mysqli) || $mysqli->connect_error) {
    ads_log('DB connect failed: ' . (isset($mysqli) ? $mysqli->connect_error : 'no connection object'));
    respond(500, ['status' => 'error', 'message' => 'Database connection failed']);
}
$conn = $mysqli;
$conn->set_charset('utf8');

// ---- read input (GET, JSON body, or form POST) ----
$lead_data = ($_SERVER['REQUEST_METHOD'] === 'GET') ? $_GET : json_decode(file_get_contents('php://input'), true);
if (!is_array($lead_data) || empty($lead_data)) {
    $lead_data = $_POST;
}
ads_log('Received: ' . json_encode($lead_data));

$allowed_fields = [
    'lead_id', 'customer_name', 'company_name', 'city', 'state', 'mobile_no', 'secondary_mobile_no',
    'email_id', 'requirement', 'description', 'lead_source', 'status', 'userid', 'assigned_by',
    'assigned_to', 'created_date', 'tarik', 'today', 'team_head', 'api',
];

// Defaults. userid = 0 means "unassigned" (the round-robin cron picks these up).
$defaults = [
    'lead_id'             => 'WEB' . time(),
    'customer_name'       => '',
    'company_name'        => '',
    'city'                => '',
    'state'               => '',
    'secondary_mobile_no' => '',
    'email_id'            => '',
    'requirement'         => '',
    'description'         => '',
    'lead_source'         => 'GautamLanding',
    'status'              => 'Not Contacted',
    'userid'              => 0,
    'assigned_by'         => '',
    'assigned_to'         => '',
    'created_date'        => date('Y-m-d H:i:s'),
    'tarik'               => date('Y-m-d'),
    'today'               => date('Y-m-d'),
    'team_head'           => '',
    'api'                 => 'API',
    'whatsapp_greetings'  => 0,
];

// Column sizes in tbl_lead: strict MySQL rejects text that is too long, so cut it to fit
$max_len = [
    'lead_id' => 200, 'customer_name' => 500, 'company_name' => 200, 'city' => 200, 'state' => 200,
    'mobile_no' => 200, 'secondary_mobile_no' => 30, 'email_id' => 200, 'requirement' => 1000,
    'description' => 200, 'lead_source' => 500, 'status' => 200, 'assigned_by' => 200,
    'assigned_to' => 200, 'today' => 35, 'team_head' => 35,
];

$row = $defaults;
foreach ($allowed_fields as $field) {
    if (isset($lead_data[$field]) && !is_array($lead_data[$field])) {
        $row[$field] = clean($lead_data[$field]);
        if (isset($max_len[$field])) {
            $row[$field] = mb_substr($row[$field], 0, $max_len[$field], 'UTF-8');
        }
    }
}
$row['userid'] = (int) $row['userid'];
$row['whatsapp_greetings'] = (int) $row['whatsapp_greetings'];

if (empty($row['mobile_no'])) {
    ads_log('Rejected: mobile_no missing');
    respond(400, ['status' => 'error', 'message' => 'mobile_no is required']);
}

// ---- ignore an exact repeat of the same lead_id (double submit / retry) ----
$chk = $conn->prepare('SELECT id FROM tbl_lead WHERE lead_id = ? LIMIT 1');
if ($chk) {
    $chk->bind_param('s', $row['lead_id']);
    $chk->execute();
    $chk->store_result();
    $dup = $chk->num_rows > 0;
    $chk->close();
    if ($dup) {
        respond(200, ['status' => 'success', 'message' => 'Duplicate ignored']);
    }
}

// ---- insert ----
$fields = array_keys($row);
$sql = 'INSERT INTO `tbl_lead` (`' . implode('`,`', $fields) . '`) VALUES (' . implode(',', array_fill(0, count($fields), '?')) . ')';
$stmt = $conn->prepare($sql);
if (!$stmt) {
    ads_log('Prepare failed: ' . $conn->error);
    respond(500, ['status' => 'error', 'message' => 'Could not save lead']);
}

$types = '';
foreach ($row as $v) {
    $types .= is_int($v) ? 'i' : 's';
}
$values = array_values($row);
$stmt->bind_param($types, ...$values);

if ($stmt->execute()) {
    $stmt->close();
    respond(200, ['status' => 'success', 'message' => 'Received']);
}

ads_log('Insert failed: ' . $stmt->error . ' | lead_id=' . $row['lead_id'] . ' mobile=' . $row['mobile_no']);
$stmt->close();
respond(500, ['status' => 'error', 'message' => 'Could not save lead']);