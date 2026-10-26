<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ==========================
   DATABASE CONNECTION
========================== */
class db {
    private static $con = null;

    public static function con() {
        if (self::$con === null) {
            try {
                self::$con = new PDO(
                    "mysql:host=srv834.hstgr.io;dbname=u457158986_gautamayurveda;charset=utf8",
                    "u457158986_gautamayurveda",
                    "#Gautam@123",
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (PDOException $e) {
                die("Database connection failed");
            }
        }
        return self::$con;
    }
}

/* ==========================
   AUTH HELPERS
========================== */
function validate_admin() {
    if (empty($_SESSION['sess_admin_id'])) {
        header("Location: index.php");
        exit;
    }
}

function validate_users() {
    if (empty($_SESSION['sess_userid'])) {
        header("Location: index.php");
        exit;
    }
}

/* ==========================
   DATE HELPERS
========================== */
function mysql_date($dd) {
    return $dd ? implode('-', array_reverse(explode('/', $dd))) : null;
}

function user_date($dd, $full = 0, $sep = '/') {
    if (!$dd || $dd == '0000-00-00') return '';
    return $full
        ? date("d{$sep}m{$sep}Y h:i A", strtotime($dd))
        : date("d{$sep}m{$sep}Y", strtotime($dd));
}

/* ==========================
   REDIRECT
========================== */
function ms_redirect($url) {
    header("Location: $url");
    exit;
}

/* ==========================
   SECURITY
========================== */
function clean($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/* ==========================
   SMS (UNCHANGED)
========================== */
function sendSMS($message, $mobile) {
    if (strlen(trim($mobile)) == 10) {
        file_get_contents(
            "http://c2c.swigcom.com/PushSMS.php?CCID=30811&ServiceID=30811&CustomerNumber=" .
            trim($mobile) . "&SmsText=" . urlencode($message) . "&SmsType=0"
        );
    }
}

/* ==========================
   CONSTANTS
========================== */
if ($_SERVER['HTTP_HOST'] == 'localhost') {
    define('SITE_URL', 'http://localhost/oldcrm/admin');
} else {
    define('SITE_URL', 'https://gautamclinics.com/admin/');
}

define('SITE_TITLE', 'Gautam Clinic');
define('SITE_ADMIN_TITLE', 'Gautam Clinic - Secured Administration Suite');
