<?php

date_default_timezone_set('Asia/Kolkata');

// DB Connection
$conn = new mysqli("localhost", "u457158986_team", "#Team@123", "u457158986_team");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get Today Report Data
$sql = "
SELECT 
    COUNT(l.id) AS total_leads,
    COUNT(DISTINCT c.lead_id) AS worked_leads,
    (COUNT(l.id) - COUNT(DISTINCT c.lead_id)) AS pending_leads
FROM tbl_lead l
LEFT JOIN tbl_comment c 
    ON l.id = c.lead_id 
    AND DATE(c.created_date) = CURDATE()
WHERE DATE(l.created_date) = CURDATE()
";

$result = $conn->query($sql);
$row = $result->fetch_assoc();

$total   = $row['total_leads'];
$worked  = $row['worked_leads'];
$pending = $row['pending_leads'];

$today_date = date("d-m-Y");

// WhatsApp Number (with country code, no +)
$phone = "919873446622";

// WATI API
$url = "https://live-mt-server.wati.io/1034642/api/v2/sendTemplateMessage?whatsappNumber={$phone}";

$data = [
    "template_name" => "reportingsss",
    "broadcast_name" => "reporting",
    "parameters" => [
        ["name" => "variable_1", "value" => $today_date],
        ["name" => "variable_2", "value" => $total],
        ["name" => "variable_3", "value" => $worked],
        ["name" => "variable_4", "value" => $pending]
    ]
];

$headers = [
    "Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1bmlxdWVfbmFtZSI6ImdhdXRhbWF5dXJ2ZWRhc3RvcmVAZ21haWwuY29tIiwibmFtZWlkIjoiZ2F1dGFtYXl1cnZlZGFzdG9yZUBnbWFpbC5jb20iLCJlbWFpbCI6ImdhdXRhbWF5dXJ2ZWRhc3RvcmVAZ21haWwuY29tIiwiYXV0aF90aW1lIjoiMDIvMTIvMjAyNiAxMjowNjoxNiIsInRlbmFudF9pZCI6IjEwMzQ2NDIiLCJkYl9uYW1lIjoibXQtcHJvZC1UZW5hbnRzIiwiaHR0cDovL3NjaGVtYXMubWljcm9zb2Z0LmNvbS93cy8yMDA4LzA2L2lkZW50aXR5L2NsYWltcy9yb2xlIjoiQURNSU5JU1RSQVRPUiIsImV4cCI6MjUzNDAyMzAwODAwLCJpc3MiOiJDbGFyZV9BSSIsImF1ZCI6IkNsYXJlX0FJIn0.xw8mdb2vYurA1PP6G2YbF1Xw2QkDW9lyqALq-TEWQcE",
    "Content-Type: application/json"
];

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);


$status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if(curl_errno($ch)){
    echo "<h3 style='color:red;'>CURL Error:</h3>";
    echo curl_error($ch);
} else {
    echo "<h3>Cron Execution Report</h3>";
    echo "<strong>Date:</strong> " . date("d-m-Y H:i:s") . "<br><br>";
    
    echo "<strong>Total Leads:</strong> " . $total . "<br>";
    echo "<strong>Worked Leads:</strong> " . $worked . "<br>";
    echo "<strong>Pending Leads:</strong> " . $pending . "<br><br>";
    
    echo "<strong>API Status Code:</strong> " . $status . "<br><br>";
    
    echo "<strong>API Response:</strong><br>";
    echo "<pre>";
    print_r($response);
    echo "</pre>";
}
?>
