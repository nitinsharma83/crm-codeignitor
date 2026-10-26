<?php
ob_start();

require_once __DIR__ . '/db.php';

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {

    $added_by         = trim($_POST['added_by'] ?? '');
    $lead_id          = trim($_POST['lead_id'] ?? '');
    $customer_name    = trim($_POST['customer_name'] ?? '');
    $mobile           = trim($_POST['mobile'] ?? '');
    $branch_id        = trim($_POST['branch_id'] ?? '');
    $appointment_date = trim($_POST['appointment_date'] ?? '');
    $appointment_time = trim($_POST['appointment_time'] ?? '');
    $doctor_id        = trim($_POST['doctor_id'] ?? '');
    $lead_source      = trim($_POST['lead_source'] ?? '');

    // ---- Pin code gate ----
    // tbl_lead lives in u457158986_team, a different DB than whatever
    // db.php's $dbh points to (tbl_appointment lives in
    // u457158986_gautamayurveda per the lead view), so this opens its own
    // connection rather than assuming $dbh can see tbl_lead.
    if (empty($lead_id) || !ctype_digit($lead_id)) {
        ob_end_clean();
        echo json_encode([
            'status'  => 'error',
            'message' => 'Missing lead reference — cannot verify pincode. Please reload the lead page and try again.'
        ]);
        exit;
    }

    try {
               // CRM database (tbl_lead): same login the CRM and webhooks use on this server
        require_once __DIR__ . '/../db_config.php';
        $leadDbh = new PDO(
            "mysql:host=$dbHost;dbname=$dbDatabase;charset=utf8",
            $dbUser,
            $dbPasswrod,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $stmtPin = $leadDbh->prepare("SELECT pincode FROM tbl_lead WHERE id = :id LIMIT 1");
        $stmtPin->execute([':id' => $lead_id]);
        $leadPin = $stmtPin->fetchColumn();
    } catch (PDOException $e) {
        ob_end_clean();
        echo json_encode(['status' => 'error', 'message' => 'Could not verify lead pincode.']);
        exit;
    }

    if (empty($leadPin)) {
        ob_end_clean();
        echo json_encode([
            'status'  => 'error',
            'message' => 'This lead has no pincode on file. Please add the pincode before booking an appointment.'
        ]);
        exit;
    }

    if (strpos($appointment_time, '|') !== false) {
        $parts = explode('|', $appointment_time);
        $appointment_time = end($parts);
    }

    $timestamp = strtotime($appointment_time);
    if ($timestamp === false) {
        ob_end_clean();
        echo json_encode([
            'status'  => 'error',
            'message' => 'Invalid appointment time received.',
            'time'    => $appointment_time
        ]);
        exit;
    }
    $appointment_time = date('H:i:s', $timestamp);

    $whatsapp_number  = $mobile;
    $whatsapp_message = 1;

    if (
        empty($customer_name) ||
        empty($mobile) ||
        empty($branch_id) ||
        empty($appointment_date) ||
        empty($appointment_time) ||
        empty($doctor_id)
    ) {
        ob_end_clean();
        echo json_encode([
            'status'  => 'error',
            'message' => 'All required fields are mandatory.'
        ]);
        exit;
    }

    if (!isset($dbh) || !($dbh instanceof PDO)) {
        throw new Exception('Database handle ($dbh) is not available from db.php');
    }

    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ---- Resolve lead_source -> tbl_enquiry.id (matched via `type` column) ----
    $customer_source = ''; // column is NOT NULL, so never leave this as null
    if (!empty($lead_source)) {
        $stmt_src = $dbh->prepare("SELECT id FROM tbl_enquiry WHERE type = :type LIMIT 1");
        $stmt_src->execute([':type' => $lead_source]);
        $src_row = $stmt_src->fetch(PDO::FETCH_ASSOC);
        if ($src_row) {
            $customer_source = (string) $src_row['id'];
        }
    }

    $stmt = $dbh->prepare("
        INSERT INTO tbl_appointment
        SET
            customer_name      = :customer_name,
            mobile             = :mobile,
            whatsapp_number    = :whatsapp_number,
            whatsapp_message   = :whatsapp_message,
            appointment_date   = :appointment_date,
            appointment_time   = :appointment_time,
            doctor_id          = :doctor_id,
            branch_id          = :branch_id,
            added_by           = :added_by,
            customer_source    = :customer_source,
            post_date          = CURDATE(),
            added_as_customer  = 1,
            status             = 0
    ");
    $stmt->execute([
        ':customer_name'    => $customer_name,
        ':mobile'           => $mobile,
        ':whatsapp_number'  => $whatsapp_number,
        ':whatsapp_message' => $whatsapp_message,
        ':appointment_date' => $appointment_date,
        ':appointment_time' => $appointment_time,
        ':doctor_id'        => $doctor_id,
        ':branch_id'        => $branch_id,
        ':added_by'         => $added_by,
        ':customer_source'  => $customer_source
    ]);

    ob_end_clean();
    echo json_encode([
        'status'  => 'success',
        'message' => 'Appointment booked successfully.'
    ]);

} catch (Throwable $e) {
    error_log('save_appointment.php error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

    if (ob_get_length()) { ob_end_clean(); }

    echo json_encode([
        'status'  => 'error',
        'message' => 'Something went wrong while saving the appointment.',
        'debug'   => $e->getMessage()
    ]);
}