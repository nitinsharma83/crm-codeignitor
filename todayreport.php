<?php
date_default_timezone_set('Asia/Kolkata');

// DB Connection
$conn = new mysqli("localhost", "u457158986_team", "#Team@123", "u457158986_team");


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Agent Wise Daily Report (Only Employees)
$sql = "
SELECT 
    r.full_name AS agent_name,
    COUNT(l.id) AS total_leads,
    COUNT(DISTINCT c.lead_id) AS worked_leads,
    (COUNT(l.id) - COUNT(DISTINCT c.lead_id)) AS pending_leads
FROM tbl_registration r
LEFT JOIN tbl_lead l 
    ON r.full_name = l.assigned_to
    AND DATE(l.created_date) = CURDATE()
LEFT JOIN tbl_comment c 
    ON l.id = c.lead_id
    AND DATE(c.created_date) = CURDATE()
WHERE r.user_type = 'Employee'
GROUP BY r.full_name
ORDER BY total_leads DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Agent Daily Report</title>
    <style>
        body { font-family: Arial; background:#f4f6f9; padding:20px; }
        h2 { margin-bottom:20px; }
        table { width:100%; border-collapse: collapse; background:#fff; }
        th, td { padding:10px; border:1px solid #ddd; text-align:center; }
        th { background:#2c3e50; color:#fff; }
        tr:nth-child(even){ background:#f2f2f2; }
        .total { background:#27ae60; color:#fff; }
        .worked { background:#2980b9; color:#fff; }
        .pending { background:#c0392b; color:#fff; }
    </style>
</head>
<body>

<h2>📊 Agent Wise Daily Lead Report (<?php echo date("d-m-Y"); ?>)</h2>

<table>
    <tr>
        <th>Agent Name</th>
        <th class="total">Total Leads</th>
        <th class="worked">Worked Leads</th>
        <th class="pending">Pending Leads</th>
        <th>Work %</th>
    </tr>

<?php
while($row = $result->fetch_assoc()){

    $total = $row['total_leads'];
    $worked = $row['worked_leads'];
    $pending = $row['pending_leads'];

    $percentage = ($total > 0) 
        ? round(($worked / $total) * 100, 2) 
        : 0;

    echo "<tr>
            <td>{$row['agent_name']}</td>
            <td>{$total}</td>
            <td>{$worked}</td>
            <td>{$pending}</td>
            <td>{$percentage}%</td>
          </tr>";
}
?>

</table>

</body>
</html>
