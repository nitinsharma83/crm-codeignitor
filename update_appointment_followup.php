<?php
date_default_timezone_set('Asia/Kolkata');

// ✅ TODAY DATE
$today   = date('Y-m-d');
$nextDay = date('Y-m-d', strtotime('+1 day'));

// Counters
$totalFound    = 0;
$totalVisited  = 0;
$totalUpdated  = 0; // rescheduled
$totalSkipped  = 0;

/*
|--------------------------------------------------------------------------
| DB 1: APPOINTMENT DATABASE
|--------------------------------------------------------------------------
*/
try {
    $appointmentDB = new PDO(
        "mysql:host=srv834.hstgr.io;dbname=u457158986_gautamayurveda;charset=utf8",
        "u457158986_gautamayurveda",
        "#Gautam@123"
    );
    $appointmentDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Appointment DB Error: " . $e->getMessage());
}

/*
|--------------------------------------------------------------------------
| DB 2: LEAD DATABASE
|--------------------------------------------------------------------------
*/
try {
    $leadDB = new PDO(
        "mysql:host=localhost;dbname=u457158986_team;charset=utf8",
        "u457158986_team",
        "#Team@123"
    );
    $leadDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Lead DB Error: " . $e->getMessage());
}

/*
|--------------------------------------------------------------------------
| STEP 1: GET UNPROCESSED PAST APPOINTMENTS
| (both added_as_patient = 0 and = 1, we branch below)
|--------------------------------------------------------------------------
*/
$stmt = $appointmentDB->prepare("
    SELECT id, mobile, appointment_date, added_as_patient
    FROM tbl_appointment
    WHERE appointment_date < :today
      AND followup_updated = 0
    LIMIT 200
");

$stmt->execute([':today' => $today]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalFound = count($appointments);

echo "============================\n";
echo "CRON STARTED\n";
echo "Date: $today\n";
echo "Found: $totalFound appointment(s)\n";
echo "============================\n";

/*
|--------------------------------------------------------------------------
| STEP 2: PROCESS
|--------------------------------------------------------------------------
*/
foreach ($appointments as $app) {

    $appId            = $app['id'];
    $mobile           = substr($app['mobile'], -10);
    $appDate          = $app['appointment_date'];
    $addedAsPatient   = (int) $app['added_as_patient'];

    echo "\nProcessing Appointment ID: $appId | Mobile: $mobile | Date: $appDate | AddedAsPatient: $addedAsPatient\n";

    // Find matching lead
    $checkLead = $leadDB->prepare("
        SELECT id, status, tarik
        FROM tbl_lead
        WHERE RIGHT(mobile_no,10) = :mobile
        LIMIT 1
    ");
    $checkLead->execute([':mobile' => $mobile]);
    $lead = $checkLead->fetch(PDO::FETCH_ASSOC);

    if ($lead) {

        if ($addedAsPatient === 1) {
            // ✅ CASE 1: Patient visited -> mark lead as Visited
            if ($lead['status'] === 'Appointment Visited') {
                echo "→ Skipped (Already marked as Visited)\n";
                $totalSkipped++;
            } else {
                $updateLead = $leadDB->prepare("
                    UPDATE tbl_lead
                    SET status = 'Appointment Visited'
                    WHERE id = :id
                ");
                $updateLead->execute([':id' => $lead['id']]);

                echo "→ Lead Updated to 'Appointment Visited' (ID: {$lead['id']})\n";
                $totalVisited++;
            }

        } else {
            // ❌ CASE 2: Not visited -> reschedule
            if ($lead['tarik'] == $nextDay) {
                echo "→ Skipped (Already updated for next day)\n";
                $totalSkipped++;
            } else {
                $updateLead = $leadDB->prepare("
                    UPDATE tbl_lead
                    SET
                        status = 'Appointment Reschedule',
                        tarik  = :nextDay
                    WHERE id = :id
                ");
                $updateLead->execute([
                    ':nextDay' => $nextDay,
                    ':id'      => $lead['id']
                ]);

                echo "→ Lead Updated to 'Appointment Reschedule' (ID: {$lead['id']})\n";
                $totalUpdated++;
            }
        }

    } else {
        echo "→ No matching lead found\n";
        $totalSkipped++;
    }

    // ✅ Mark appointment as processed either way
    $updateApp = $appointmentDB->prepare("
        UPDATE tbl_appointment
        SET followup_updated = 1
        WHERE id = :id
    ");
    $updateApp->execute([':id' => $appId]);
}

/*
|--------------------------------------------------------------------------
| FINAL SUMMARY
|--------------------------------------------------------------------------
*/
echo "\n============================\n";
echo "CRON SUMMARY\n";
echo "Total Found      : $totalFound\n";
echo "Marked Visited   : $totalVisited\n";
echo "Marked Reschedule: $totalUpdated\n";
echo "Total Skipped    : $totalSkipped\n";
echo "============================\n";
echo "CRON COMPLETED\n";