<?php
date_default_timezone_set('Asia/Kolkata');
error_log("Received Data: " . json_encode($lead_data));

// Set response content type to JSON
header("Content-Type: application/json");

// Database connection settings
$servername = "localhost";
$username = "u457158986_team";
$password = "#Team@123";
$dbname = "u457158986_team";

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check database connection
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]));
}

// Get data from GET or POST
$lead_data = $_SERVER['REQUEST_METHOD'] === 'GET' ? $_GET : json_decode(file_get_contents("php://input"), true);
if (!$lead_data) {
    $lead_data = $_POST;
}

// Allowed fields
$allowed_fields = [
    'lead_id', 'customer_name', 'company_name', 'city', 'state', 'mobile_no', 'secondary_mobile_no', 
    'email_id', 'requirement', 'description', 'lead_source', 'status', 'userid', 'assigned_by', 
    'assigned_to', 'created_date', 'tarik', 'today', 'team_head', 'api'
];

// Default values
$defaults = [
    'lead_source' => 'GautamLanding1',
    'status' => 'Not Contacted',
    'created_date' => date('Y-m-d H:i:s'),
    'tarik' => date('Y-m-d'),
    'today' => date('Y-m-d'),
    'api' => 'API'
];

// Prepare data for insertion
$fields = [];
$placeholders = [];
$values = [];
$bind_types = "";

// Populate SQL fields dynamically
foreach ($allowed_fields as $field) {
    if (isset($lead_data[$field])) {
        $fields[] = $field;
        $values[] = $lead_data[$field];
        $placeholders[] = "?";
        $bind_types .= "s"; // All values are treated as strings
    } elseif (isset($defaults[$field])) {
        $fields[] = $field;
        $values[] = $defaults[$field];
        $placeholders[] = "?";
        $bind_types .= "s"; // Default values
    }
}

// Ensure we have data to insert
if (empty($fields)) {
    echo json_encode(["status" => "error", "message" => "No valid fields provided"]);
    exit;
}

// Prepare SQL statement dynamically
$sql = "INSERT INTO `tbl_lead` (" . implode(",", $fields) . ") VALUES (" . implode(",", $placeholders) . ")";
$stmt = $conn->prepare($sql);

// Bind parameters dynamically
if ($stmt) {
    $stmt->bind_param($bind_types, ...$values);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Received"]);
exit();
    } else {
        echo json_encode(["status" => "error", "message" => "Error inserting lead: " . $stmt->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "SQL Prepare failed: " . $conn->error]);
}

// Close connection
$conn->close();
?>
