<?php
require_once "db.php";

$slot      = $_POST['str'] ?? '';
$branch_id = $_POST['branch_id'] ?? '';
$date      = $_POST['appointment_date'] ?? '';

echo '<option value="">Select Doctor</option>';

if (!$slot || !$branch_id || !$date) {
    exit;
}

// If slot contains schedule_id|time, extract only the time
$parts = explode('|', $slot);
$time = end($parts);

// Find all doctors available for this time
$q = $dbh->prepare("
    SELECT DISTINCT
        a.id,
        a.name
    FROM tbl_admin a
    INNER JOIN tbl_day_schedule d
        ON d.docter_id = a.id
    WHERE d.branch_id = :branch_id
      AND d.date = :date
      AND :time >= d.time_from
      AND :time < d.time_to
      AND NOT EXISTS (
            SELECT 1
            FROM tbl_appointment ap
            WHERE ap.doctor_id = a.id
              AND ap.branch_id = :branch_id
              AND ap.appointment_date = :date
              AND ap.appointment_time = :time
      )
    ORDER BY a.name
");

$q->execute([
    ':branch_id' => $branch_id,
    ':date'      => $date,
    ':time'      => $time
]);

while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
    echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
}