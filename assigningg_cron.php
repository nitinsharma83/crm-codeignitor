<?php
// Round-robin lead assignment cron - AWS-safe version (same assignment logic as before)
date_default_timezone_set("Asia/Kolkata");
mysqli_report(MYSQLI_REPORT_OFF);          // return errors instead of throwing (PHP 8.1+)

function logline($msg) { echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n"; }

// ---- Prevent two runs overlapping (would assign the same lead twice) ----
$lockFile = fopen(sys_get_temp_dir() . '/assigningg_cron.lock', 'c');
if (!$lockFile || !flock($lockFile, LOCK_EX | LOCK_NB)) {
    logline('Another run is still in progress - exiting.');
    exit;
}

// ---- Database: re-use the CRM's db_config.php (defines $mysqli) ----
require_once __DIR__ . '/db_config.php';
if (!isset($mysqli) || $mysqli->connect_errno) {
    logline('ERROR: DB connection failed: ' . (isset($mysqli) ? $mysqli->connect_error : '$mysqli not defined in db_config.php'));
    exit(1);
}
$conn = $mysqli;
$conn->query("SET time_zone = '+05:30'");

function q($conn, $sql) {
    $res = $conn->query($sql);
    if ($res === false) {
        logline('ERROR: query failed: ' . $conn->error . ' | SQL: ' . preg_replace('/\s+/', ' ', $sql));
        exit(1);
    }
    return $res;
}

// ---- Fetch one unassigned lead (userid = 0) ----
$leadQuery = q($conn, "SELECT id, lead_source FROM tbl_lead WHERE userid = 0 ORDER BY id ASC LIMIT 1");
if ($leadQuery->num_rows === 0) {
    logline('No unassigned leads (userid = 0) found.');
    exit;
}
$lead    = $leadQuery->fetch_assoc();
$lead_id = (int)$lead['id'];

// ---- Round robin: next active employee after the last assigned one ----
$lastUserResult = q($conn, "SELECT last_userid FROM tbl_round_robin ORDER BY id DESC LIMIT 1");
$lastUserId     = ($lastUserResult->num_rows > 0) ? (int)$lastUserResult->fetch_assoc()['last_userid'] : 0;

$userQuery = q($conn, "
    SELECT id, full_name, location
    FROM tbl_registration
    WHERE id > $lastUserId
      AND user_type = 'Employee'
      AND status = 'active'
      AND id <> 123
    ORDER BY id ASC
    LIMIT 1
");

// End of list -> loop back to first active employee
if ($userQuery->num_rows === 0) {
    $userQuery = q($conn, "
        SELECT id, full_name, location
        FROM tbl_registration
        WHERE user_type = 'Employee'
          AND status = 'active'
          AND id <> 123
          AND full_name <> 'FB05'
        ORDER BY id ASC
        LIMIT 1
    ");
}

if ($userQuery->num_rows === 0) {
    logline('No active employees found in tbl_registration.');
    exit;
}
$user    = $userQuery->fetch_assoc();
$user_id = (int)$user['id'];

$location    = !empty($user['location']) ? trim($user['location']) : 'NA';
$api         = $location;
$assigned_to = $user['full_name'];
$assigned_by = $user['full_name'];

logline("Assigning Lead ID $lead_id to Employee ID $user_id ($assigned_to) [API: $api]");

// ---- Assign the lead ----
$updateQuery = $conn->prepare("UPDATE tbl_lead SET userid = ?, assigned_to = ?, assigned_by = ?, api = ? WHERE id = ?");
if (!$updateQuery) {
    logline('ERROR: prepare (update lead) failed: ' . $conn->error);
    exit(1);
}
$updateQuery->bind_param("isssi", $user_id, $assigned_to, $assigned_by, $api, $lead_id);

if (!$updateQuery->execute()) {
    logline('ERROR: updating lead failed: ' . $updateQuery->error);
    exit(1);
}
$updateQuery->close();
logline("Lead ID $lead_id assigned successfully.");

// Advance round-robin pointer only AFTER the lead was really assigned
$conn->query("UPDATE tbl_round_robin SET last_userid = $user_id");
if ($conn->error) { logline('WARNING: round robin update failed: ' . $conn->error); }

// ---- Notification ----
$from_username     = "System";
$to_username       = $user['full_name'];
$from_userid       = 0;
$to_userid         = $user_id;
$message           = "New Lead assigned to you";
$view              = 0;
$created_date      = date("Y-m-d H:i:s");
$status            = 'active';
$notification_type = "Lead";

$notificationQuery = $conn->prepare("
    INSERT INTO tbl_notification
    (from_username, to_username, from_userid, to_userid, message, view, created_date, status, lead_id, Notification_type)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
if (!$notificationQuery) {
    logline('WARNING: prepare (notification) failed: ' . $conn->error);
} else {
    $notificationQuery->bind_param("ssiisissis",
        $from_username, $to_username, $from_userid, $to_userid,
        $message, $view, $created_date, $status, $lead_id, $notification_type);
    if ($notificationQuery->execute()) { logline('Notification inserted successfully.'); }
    else { logline('WARNING: inserting notification failed: ' . $notificationQuery->error); }
    $notificationQuery->close();
}

$conn->close();
logline('=== Round Robin Assignment Completed ===');