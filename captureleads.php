<?php
// Set content type to plain text for the response
header("Content-Type: text/plain");
date_default_timezone_set('Asia/Kolkata');
// Database connection settings
$servername = "localhost";  
$username = "u457158986_team";  
$password = "#Team@123";  
$dbname = "u457158986_team";  

// Create a new MySQLi connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check request method (GET, POST)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $lead_data = $_GET;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);
    $lead_data = $data ? $data : $_POST;
} else {
    echo "ERROR: Invalid request method.";
    http_response_code(405);
    exit;
}

// Allowed fields for security
$allowed_fields = [
    'leadid', 'leadtype', 'prefix', 'name', 'mobile', 'phone', 'email',
    'date', 'category', 'city', 'area', 'brancharea', 'dncmobile',
    'dncphone', 'company', 'pincode', 'time', 'branchpin', 'parentid'
];

// Validate that no unexpected fields are present
foreach ($lead_data as $key => $value) {
    if (!in_array($key, $allowed_fields)) {
        echo "ERROR: Invalid field '$key' provided.";
        http_response_code(400);
        exit;
    }
}

// Check required fields
$required_fields = ['leadid', 'name', 'mobile', 'date', 'category', 'city'];
foreach ($required_fields as $field) {
    if (empty($lead_data[$field])) {
        echo "ERROR: Missing required field '$field'.";
        http_response_code(400);
        exit;
    }
}

// **First Insert into justdial_leads Table**
$stmt1 = $conn->prepare("
    INSERT INTO justdial_leads 
    (leadid, leadtype, prefix, name, mobile, phone, email, lead_date, category, city, area, brancharea, dncmobile, dncphone, company, pincode, lead_time, branchpin, parentid) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt1->bind_param(
    "ssssssssssssiiissss", 
    $lead_data['leadid'], 
    $lead_data['leadtype'], 
    $lead_data['prefix'], 
    $lead_data['name'], 
    $lead_data['mobile'], 
    $lead_data['phone'], 
    $lead_data['email'], 
    $lead_data['date'], 
    $lead_data['category'], 
    $lead_data['city'], 
    $lead_data['area'], 
    $lead_data['brancharea'], 
    $lead_data['dncmobile'], 
    $lead_data['dncphone'], 
    $lead_data['company'], 
    $lead_data['pincode'], 
    $lead_data['time'], 
    $lead_data['branchpin'], 
    $lead_data['parentid']
);

// **Second Insert into tbl_lead Table**
$stmt2 = $conn->prepare("
    INSERT INTO tbl_lead 
    (lead_id, customer_name, company_name, city, state, mobile_no, secondary_mobile_no, email_id, requirement, description, lead_source, status, userid, assigned_by, assigned_to, created_date, tarik, today, team_head, api) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$state = "";  // No state in the existing data
$secondary_mobile_no = "";  // No secondary mobile number in the existing data
$requirement = "";  // No requirement field in the existing data
$description = "";  // No description field in the existing data
$lead_source = "GautamJD";  // Assuming Justdial as the source
$status = "Not Contacted";  // Default status
$userid = 0;  // Default value
$assigned_by = "";  // Default value
$assigned_to = "";  // Default value
$created_date = date('Y-m-d H:i:s');  // Current timestamp
$tarik =  date('Y-m-d');  // Default value
$today = date('Y-m-d');  // Current date
$team_head = "";  // Default value
$api = "API";  // Default identifier

$stmt2->bind_param(
    "ssssssssssssssssssss", 
    $lead_data['leadid'], 
    $lead_data['name'], 
    $lead_data['company'], 
    $lead_data['city'], 
    $state, 
    $lead_data['mobile'], 
    $secondary_mobile_no, 
    $lead_data['email'], 
    $requirement, 
    $description, 
    $lead_source, 
    $status, 
    $userid, 
    $assigned_by, 
    $assigned_to, 
    $created_date, 
    $tarik, 
    $today, 
    $team_head, 
    $api
);

// Execute both insert queries
$success1 = $stmt1->execute();
$success2 = $stmt2->execute();

if ($success1 && $success2) {
    echo "RECEIVED";
    http_response_code(200);
} else {
    echo "ERROR: Failed to save lead data.";
    http_response_code(500);
}

// Close statements and connection
$stmt1->close();
$stmt2->close();
$conn->close();
?>
