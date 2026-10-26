<?php
// Appointment / clinic database for the corephp endpoints.
// Settings live in application/config/clinic_db.php (not in Git).
if (!defined('BASEPATH')) { define('BASEPATH', true); }   // clinic_db.php refuses to load without it
$clinicCfg = include __DIR__ . '/../application/config/clinic_db.php';

try {
    $dbh = new PDO(
        "mysql:host={$clinicCfg['host']};port={$clinicCfg['port']};dbname={$clinicCfg['database']};charset=utf8",
        $clinicCfg['username'],
        $clinicCfg['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]
    );
} catch (PDOException $e) {
    error_log('corephp/db.php connection failed: ' . $e->getMessage());
    die("Appointment database unavailable");
}