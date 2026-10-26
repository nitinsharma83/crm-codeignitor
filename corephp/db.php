<?php
$host = "srv834.hstgr.io";
$db   = "u457158986_gautamayurveda";
$user = "u457158986_gautamayurveda";
$pass = "#Gautam@123";

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
