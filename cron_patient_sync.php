<?php
date_default_timezone_set('Asia/Kolkata');

ini_set('display_errors', 1);
error_reporting(E_ALL);
echo "\n";
echo "==================================================\n";
echo "===== LEAD PATIENT SYNC CRON START =====\n";
echo "Start Time : " . date("d-m-Y h:i:s A") . "\n";
echo "==================================================\n\n";

/* =====================================================
   TEAM DB CONNECTION
===================================================== */
$lead_conn = new mysqli(
    "localhost",
    "u457158986_team",
    "#Team@123",
    "u457158986_team"
);

if ($lead_conn->connect_error) {
    die("❌ Lead DB Connection failed: " . $lead_conn->connect_error);
}

echo "✅ Lead DB Connected\n";

/* =====================================================
   PATIENT DB CONNECTION
===================================================== */
$patient_conn = new mysqli(
    "srv834.hstgr.io",
    "u457158986_gautamayurveda",
    "#Gautam@123",
    "u457158986_gautamayurveda"
);

if ($patient_conn->connect_error) {
    die("❌ Patient DB Connection failed: " . $patient_conn->connect_error);
}

echo "✅ Patient DB Connected\n\n";

/* =====================================================
   ARRAYS
===================================================== */
$patientMobiles = [];
$patientWithPrescription = [];
$patientWithAppointment = [];

/* =====================================================
   FETCH PATIENTS
===================================================== */
echo "🔄 Fetching patients...\n";

$pq = "
SELECT 
    id,
    RIGHT(REGEXP_REPLACE(mobile, '[^0-9]', ''), 10) as mobile_clean
FROM tbl_patient
";

$pr = $patient_conn->query($pq);

if ($pr && $pr->num_rows > 0) {

    while ($row = $pr->fetch_assoc()) {

        if (!empty($row['mobile_clean'])) {
            $patientMobiles[$row['mobile_clean']] = $row['id'];
        }
    }
}

echo "✅ Patients Found: " . count($patientMobiles) . "\n\n";

/* =====================================================
   FETCH PRESCRIPTIONS
===================================================== */
echo "🔄 Fetching prescriptions...\n";

$pres = $patient_conn->query("
    SELECT DISTINCT patient_id
    FROM tbl_prescription
    WHERE amount != 0
");

if ($pres && $pres->num_rows > 0) {

    while ($row = $pres->fetch_assoc()) {

        $patientWithPrescription[$row['patient_id']] = true;
    }
}

echo "✅ Prescription Patients: " . count($patientWithPrescription) . "\n\n";

/* =====================================================
   FETCH APPOINTMENTS
===================================================== */
echo "🔄 Fetching appointments...\n";

$app = $patient_conn->query("
    SELECT DISTINCT
    RIGHT(REGEXP_REPLACE(mobile, '[^0-9]', ''), 10) as mobile_clean
    FROM tbl_appointment
    WHERE added_as_patient = 1
");

if ($app && $app->num_rows > 0) {

    while ($row = $app->fetch_assoc()) {

        if (!empty($row['mobile_clean'])) {
            $patientWithAppointment[$row['mobile_clean']] = true;
        }
    }
}

echo "✅ Appointment Mobiles: " . count($patientWithAppointment) . "\n\n";

/* =====================================================
   FETCH NEXT UNSYNCED LEAD
===================================================== */
echo "🔄 Fetching next unsynced lead...\n";

$leadQuery = "
SELECT 
    id,
    mobile_no,
    patient_type,
    patient_sync_done
FROM tbl_lead
WHERE patient_sync_done = 0
ORDER BY id ASC
LIMIT 1
";

$leadResult = $lead_conn->query($leadQuery);

$updated = 0;
$skipped = 0;

if ($leadResult && $leadResult->num_rows > 0) {

    $lead = $leadResult->fetch_assoc();

    $leadId = $lead['id'];

    echo "Lead Picked ID : " . $leadId . "\n";

    /* ============================================
       NORMALIZE MOBILE
    ============================================ */
    $leadMobile = preg_replace('/\D/', '', $lead['mobile_no']);
    $leadMobile = substr($leadMobile, -10);

    $patientType = "New Patient";
    $hasAppointment = 0;
    $hasPrescription = 0;
    $patientId = NULL;

    /* ============================================
       CHECK OLD PATIENT
    ============================================ */
    if (isset($patientMobiles[$leadMobile])) {

        $patientType = "Old Patient";

        $patientId = $patientMobiles[$leadMobile];

        if (isset($patientWithAppointment[$leadMobile])) {
            $hasAppointment = 1;
        }

        if (isset($patientWithPrescription[$patientId])) {
            $hasPrescription = 1;
        }
    }

    echo "------------------------------------------\n";
    echo "Lead ID          : " . $leadId . "\n";
    echo "Mobile           : " . $leadMobile . "\n";
    echo "Patient Type     : " . $patientType . "\n";
    echo "Has Appointment  : " . $hasAppointment . "\n";
    echo "Has Prescription : " . $hasPrescription . "\n";
    echo "Patient ID       : " . ($patientId ?: 'NULL') . "\n";

    /* ============================================
       UPDATE LEAD
    ============================================ */
    $stmt = $lead_conn->prepare("
        UPDATE tbl_lead
        SET
            patient_type = ?,
            has_appointment = ?,
            has_prescription = ?,
            patient_id = ?,
            patient_sync_done = 1
        WHERE id = ?
    ");

    $stmt->bind_param(
        "siiii",
        $patientType,
        $hasAppointment,
        $hasPrescription,
        $patientId,
        $leadId
    );

    if ($stmt->execute()) {

        $updated++;

        echo "✅ Lead Updated Successfully\n";

        /* ========================================
           VERIFY UPDATE
        ======================================== */
        $verify = $lead_conn->query("
            SELECT patient_sync_done
            FROM tbl_lead
            WHERE id = '$leadId'
        ");

        $verifyData = $verify->fetch_assoc();

        echo "patient_sync_done : " . $verifyData['patient_sync_done'] . "\n";

    } else {

        $skipped++;

        echo "❌ Update Failed : " . $stmt->error . "\n";
    }

    $stmt->close();

    echo "------------------------------------------\n\n";

} else {

    echo "⚠️ No Pending Leads Found\n\n";
}

/* =====================================================
   FINAL SUMMARY
===================================================== */
echo "==================================================\n";
echo "TOTAL LEADS UPDATED : " . $updated . "\n";
echo "TOTAL SKIPPED       : " . $skipped . "\n";
echo "CRON END TIME       : " . date("d-m-Y h:i:s A") . "\n";
echo "===== CRON COMPLETED =====\n";
echo "==================================================\n";
?>