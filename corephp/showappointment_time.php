<?php
require_once __DIR__ . '/db.php';

$branch_id = $_POST['branch_id'] ?? '';
$date      = $_POST['appointment_date'] ?? '';

echo '<option value="">Select Time</option>';

if (!$branch_id || !$date) exit;

$stmt = $dbh->prepare("
    SELECT d.id as schedule_id, d.docter_id, d.time_from, d.time_to
    FROM tbl_day_schedule d
    WHERE d.branch_id = :branch
      AND d.date = :date
    ORDER BY d.time_from
");

$stmt->execute([
    ':branch' => $branch_id,
    ':date'   => $date
]);

$slots = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $start = strtotime($row['time_from']);
    $end   = strtotime($row['time_to']);

    for ($t = $start; $t < $end; $t += 15 * 60) {

        $time = date('H:i', $t);

        // Keep only one entry per time
        if (!isset($slots[$time])) {
            $slots[$time] = $row['schedule_id']; // first schedule id
        }
    }
}

ksort($slots);

foreach ($slots as $time => $schedule_id) {

    echo '<option value="'.$schedule_id.'|'.$time.'">'
        . date('h:i A', strtotime($time))
        . '</option>';
}