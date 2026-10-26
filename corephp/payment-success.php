<?php

$host = "localhost";
$db   = "u457158986_team";
$user = "u457158986_team";
$pass = "#Team@123";

try {
    $dbh = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("DB Error");
}

$order_id = $_GET['order_id'];

// Call Cashfree verify API OR webhook better

mysqli_query($conn, "
    UPDATE tbl_lead 
    SET payment_status='SUCCESS' 
    WHERE order_id='".$order_id."'
");

echo "Payment Successful";
?>