<?php
date_default_timezone_set("Asia/Kolkata");

// Database connection
$servername = "localhost";
$username   = "u457158986_team";
$password   = "#Team@123";
$dbname     = "u457158986_team";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}
mysqli_query($conn, "SET time_zone = '+05:30'");

// Fetch one unassigned lead (userid = 0)
$leadQuery = $conn->query("
    SELECT id, lead_source 
    FROM tbl_lead 
    WHERE userid = 0 
    ORDER BY id ASC 
    LIMIT 1
");

if ($leadQuery->num_rows === 0) {
    echo "⚠️ No unassigned leads (userid = 0) found.\n";
    exit;
}

$lead    = $leadQuery->fetch_assoc();
$lead_id = (int)$lead['id'];

// =====================
// ✅ ROUND ROBIN LOGIC
// =====================

// Get last assigned user_id from tbl_round_robin
$lastUserResult = $conn->query("SELECT last_userid FROM tbl_round_robin ORDER BY id DESC LIMIT 1");
$lastUserId     = ($lastUserResult->num_rows > 0) ? (int)$lastUserResult->fetch_assoc()['last_userid'] : 0;

// Find next employee after last assigned user_id
$userQuery = $conn->query("
    SELECT id, full_name, location
    FROM tbl_registration
    WHERE id > $lastUserId
      AND user_type = 'Employee'
      AND status = 'active'
      AND id <> 123
    ORDER BY id ASC
    LIMIT 1
");

// If reached end, loop back to first active employee
if ($userQuery->num_rows === 0) {
    $userQuery = $conn->query("
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
    echo "⚠️ No active employees found in tbl_registration.\n";
    exit;
}

$user = $userQuery->fetch_assoc();

// Update tbl_round_robin with new last_userid
$conn->query("UPDATE tbl_round_robin SET last_userid = {$user['id']}");

// Determine location
$location = !empty($user['location']) ? trim($user['location']) : 'NA';
$api = $location;

echo "✅ Assigning Lead ID $lead_id to Employee ID {$user['id']} ({$user['full_name']}) [API: $api]\n";

// =====================
// ✅ Update Lead Record
// =====================
$updateQuery = $conn->prepare("
    UPDATE tbl_lead 
    SET userid = ?, assigned_to = ?, assigned_by = ?, api = ? 
    WHERE id = ?
");

$assigned_to = $user['full_name'];
$assigned_by = $user['full_name']; // (You can change to "System" if you prefer)

$updateQuery->bind_param("isssi", $user['id'], $assigned_to, $assigned_by, $api, $lead_id);

if ($updateQuery->execute()) {
    echo "✅ Lead ID $lead_id assigned successfully.\n";

    // =====================
    // ✅ Insert Notification
    // =====================
    $from_username      = "System";
    $to_username        = $user['full_name'];
    $from_userid        = 0;
    $to_userid          = $user['id'];
    $message            = "New Lead assigned to you";
    $view               = 0;
    $created_date       = date("Y-m-d H:i:s");
    $status             = 'active';
    $notification_type  = "Lead";

    $notificationQuery = $conn->prepare("
        INSERT INTO tbl_notification 
        (from_username, to_username, from_userid, to_userid, message, view, created_date, status, lead_id, Notification_type)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $notificationQuery->bind_param(
        "ssiisissis", 
        $from_username, $to_username, $from_userid, $to_userid,
        $message, $view, $created_date, $status, $lead_id, $notification_type
    );

    if ($notificationQuery->execute()) {
        echo "📢 Notification inserted successfully!\n";
    } else {
        echo "❌ Error inserting notification: " . $notificationQuery->error . "\n";
    }

    $notificationQuery->close();
} else {
    echo "❌ Error updating lead: " . $updateQuery->error . "\n";
}

$updateQuery->close();
$conn->close();

echo "=== Round Robin Assignment Completed ===\n";
?>
