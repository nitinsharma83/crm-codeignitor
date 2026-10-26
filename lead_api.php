<?php
date_default_timezone_set('Asia/Kolkata');
header("Content-Type: application/json");

// ================= DB CONNECTION =================
$servername = "localhost";
$username   = "u457158986_team";
$password   = "#Team@123";
$dbname     = "u457158986_team";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["status"=>"error","msg"=>"DB Connection Failed"]);
    exit;
}

// ================= GET DATA =================
$data = $_GET;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
}

$json = json_decode(file_get_contents("php://input"), true);
if (!empty($json)) {
    $data = $json;
}

// ================= VALIDATION =================
if (empty($data['mobile_no'])) {
    echo json_encode(["status"=>"error","msg"=>"mobile_no required"]);
    exit;
}

// ================= SANITIZE =================
$phone = $conn->real_escape_string($data['mobile_no']);
$name  = $conn->real_escape_string($data['customer_name'] ?? '');
$requirement = $conn->real_escape_string($data['requirement'] ?? '');
$order_id = $conn->real_escape_string($data['order_id'] ?? '');
$payment_status = $conn->real_escape_string($data['payment_status'] ?? 'pending');
$status = $conn->real_escape_string($data['status'] ?? 'New');

// ================= CHECK EXISTING =================
$check = $conn->query("SELECT id FROM tbl_lead WHERE mobile_no='$phone' AND lead_source='GAOnlineConsultation' LIMIT 1");

if ($check && $check->num_rows > 0) {

    $row = $check->fetch_assoc();
    $lead_id = $row['id'];

    // UPDATE
    $conn->query("
        UPDATE tbl_lead SET
            customer_name = '$name',
            requirement = CONCAT(IFNULL(requirement,''), '\n\n$requirement'),
            order_id = '$order_id',
            payment_status = '$payment_status',
            status = '$status',
            today = NOW()
        WHERE id = '$lead_id'
    ");

    echo json_encode([
        "status"=>"updated",
        "lead_id"=>$lead_id
    ]);

} else {

    // INSERT
    $conn->query("
        INSERT INTO tbl_lead
        (customer_name, mobile_no, requirement, order_id, payment_status, status, created_date, today, lead_source)
        VALUES
        ('$name','$phone','$requirement','$order_id','$payment_status','$status',NOW(),NOW(),'Website')
    ");

    echo json_encode([
        "status"=>"inserted",
        "lead_id"=>$conn->insert_id
    ]);
}

$conn->close();
?>