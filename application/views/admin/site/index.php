
<style>
    #customers {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

#customers td, #customers th {
  border: 1px solid #ddd;
  padding: 8px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #04AA6D;
  color: white;
}

body{
    background:#f8f9fa;
    font-family: 'Poppins', sans-serif;
    color:#333;
}

/* Title */
.dashboard-title{
    font-weight:700;
    letter-spacing:1px;
    color:#723421;
}

/* Card Base */
.card-glass{
    background:#ffffff;
    border-radius:16px;
    box-shadow:0 8px 25px rgba(114,52,33,0.08);
    transition:0.3s ease;
    border:1px solid rgba(114,52,33,0.08);
    margin-bottom:20px;   /* 👈 Add this */
}


.card-glass:hover{
    transform: translateY(-5px);
    box-shadow:0 12px 35px rgba(114,52,33,0.15);
}

/* Stat Numbers */
.stat-number{
    font-size:28px;
    font-weight:700;
    color:#723421;
}
.stat-number1{
    font-size:28px;
    font-weight:700;
    color:white;
}
/* Total Card */
.gradient-total{
    background: linear-gradient(135deg,#723421,#a0522d);
    color:#fff;
}

/* Status Cards */
.gradient-status{
    background: linear-gradient(135deg,#ffffff,#f4ebe8);
    border-left:5px solid #723421;
}

/* Status title inside card */
.gradient-status h6{
    color:#723421;
    font-weight:600;
}

/* Form Styling */
.form-control{
    border-radius:10px;
    border:1px solid #ddd;
}

.form-control:focus{
    border-color:#723421;
    box-shadow:0 0 0 0.15rem rgba(114,52,33,0.15);
}

/* Primary Button */
.btn-primary{
    background-color:#723421 !important;
    border-color:#723421 !important;
    color:#fff !important;
    font-weight:600;
    border-radius:10px;
}

.btn-primary:hover{
    background-color:#5c2a1a !important;
    border-color:#5c2a1a !important;
}

/* Outline Button */
.btn-outline-primary{
    border:2px solid #723421 !important;
    color:#723421 !important;
    font-weight:600;
    border-radius:10px;
}

.btn-outline-primary:hover{
    background-color:#723421 !important;
    color:#fff !important;
}


/* Chart Container */
.chart-container{
    background:#ffffff;
    padding:25px;
    border-radius:18px;
    box-shadow:0 8px 25px rgba(114,52,33,0.08);
}

/* Labels */
label{
    font-weight:500;
    color:#555;
}

</style>
<?php
$username  = $this->session->userdata('USERNAME');
$usertype  = $this->session->userdata('USERTYPE');

$usertype   = $this->session->userdata('USERTYPE');

// ===== DATE FILTER (GLOBAL) =====
$from_date = isset($_GET['from_date']) && $_GET['from_date'] != ''
    ? $_GET['from_date']
    : date('Y-m-d');

$to_date = isset($_GET['to_date']) && $_GET['to_date'] != ''
    ? $_GET['to_date']
    : date('Y-m-d');

// reusable SQL condition
$whereDate = " AND tarik BETWEEN '$from_date' AND '$to_date'";
?>



<?php
$conn = new mysqli("localhost", "u457158986_team", "#Team@123", "u457158986_team");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$where = "";

// Role Based Filter
if ($usertype != 'Admin') {
    $username_safe = $conn->real_escape_string($username);
    $where .= " AND assigned_to = '$username_safe'";
}

// Date Filter
// ===== DATE DEFAULT =====
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to   = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// ===== QUICK FILTER =====
if(isset($_GET['quick_filter'])) {

    if($_GET['quick_filter'] == 'today'){
        $date_from = date('Y-m-d');
        $date_to   = date('Y-m-d');
    }

    elseif($_GET['quick_filter'] == 'yesterday'){
        $date_from = date('Y-m-d', strtotime('-1 day'));
        $date_to   = date('Y-m-d', strtotime('-1 day'));
    }

    elseif($_GET['quick_filter'] == 'week'){
        $date_from = date('Y-m-d', strtotime('-6 days'));
        $date_to   = date('Y-m-d');
    }
}

// ===== APPLY DATE FILTER TO QUERY =====
if (!empty($date_from) && !empty($date_to)) {
    $where .= " AND DATE(created_date) BETWEEN '$date_from' AND '$date_to'";
}


$totalLeads = $conn->query("SELECT COUNT(*) as total FROM tbl_lead WHERE 1 $where")
                   ->fetch_assoc()['total'];

$statusList = [];
$statusQuery = $conn->query("SELECT id, status FROM tbl_status ORDER BY status ASC");
while ($row = $statusQuery->fetch_assoc()) { $statusList[] = $row; }

$statusCounts = [];
foreach ($statusList as $status) {
    $name = $status['status'];
    $count = $conn->query("SELECT COUNT(*) as total FROM tbl_lead 
                           WHERE status='$name' $where")
                  ->fetch_assoc()['total'];
    $statusCounts[$name] = $count;
}

// ===== TOTAL APPOINTMENTS (Booked + Scheduled + Visited) =====
// ===== TOTAL APPOINTMENTS (Booked + Reschedule + Visited) =====
$appointmentStatuses = ['Appointment Booked', 'Appointment Reschedule', 'Appointment Visited'];
$totalAppointments = 0;
foreach ($appointmentStatuses as $apptStatus) {
    if (isset($statusCounts[$apptStatus])) {
        $totalAppointments += $statusCounts[$apptStatus];
    }
}
/* ==============================
   TAG COUNTS
============================== */

$tagCounts = [];

$tagQuery = $conn->query("
    SELECT tag, COUNT(*) as total
    FROM tbl_lead
    WHERE tag IS NOT NULL
    AND tag != ''
    $where
    GROUP BY tag
    ORDER BY total DESC
");

while ($row = $tagQuery->fetch_assoc()) {

    $tagName = $row['tag'];

    $tagCounts[$tagName] = $row['total'];
}
// Quick Date Filters
$leadSourceCounts = [];

$sourceQuery = $conn->query("
    SELECT lead_source, COUNT(*) as total 
    FROM tbl_lead 
    WHERE 1 $where 
    GROUP BY lead_source
");

while ($row = $sourceQuery->fetch_assoc()) {
    $sourceName = $row['lead_source'] ?: 'Unknown';
    $leadSourceCounts[$sourceName] = $row['total'];
}
?>
 <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div class="content-wrapper">
  <div class="conainer py-5">

   

    <!-- Filter -->
 <div class="card-glass p-4 mb-4">
    <form method="GET" class="row g-3 align-items-end">

        <div class="col-md-3">
            <label class="form-label">From Date</label>
            <input type="date" name="date_from" class="form-control"
                   value="<?php echo $date_from; ?>">
        </div>

        <div class="col-md-3">
            <label class="form-label">To Date</label>
            <input type="date" name="date_to" class="form-control"
                   value="<?php echo $date_to; ?>">
        </div>

        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">
                🔍 Apply
            </button>
        </div>

        <div class="col-md-2">
            <a href="/" class="btn btn-outline-primary w-100">
                Reset
            </a>
        </div>

    </form>

    <!-- Quick Filters -->
    <div class="mt-3 d-flex gap-2 flex-wrap">
        <a href="?quick_filter=today" class="btn btn-sm btn-outline-primary">Today</a>
        <a href="?quick_filter=yesterday" class="btn btn-sm btn-outline-primary">Yesterday</a>
        <a href="?quick_filter=week" class="btn btn-sm btn-outline-primary">Last 7 Days</a>
    </div>
</div>


    <!-- Cards -->
   <?php
$usertype  = $this->session->userdata('USERTYPE');

if ($usertype == 'Employee') {
    $base_url = "https://team.gautamclinics.com/employee_created_leads?";
} else {
    $base_url = "https://team.gautamclinics.com/create_lead?";
}
?>

<div class="row g-4 mb-5">

    <!-- LEFT SIDE → STATUS -->
    <div class="col-md-6">
        <div class="card-glass p-3">
            <h5 class="mb-3 text-center">📊 Status Overview</h5>
            <div class="row g-3">
<!-- Total -->
<?php
$totalRedirectUrl = $base_url . http_build_query([
    'date_from' => $date_from,
    'date_to'   => $date_to
]);
?>
<div class="col-md-6">
    <a href="<?= $totalRedirectUrl; ?>" class="card-link">
        <div class="card-glass gradient-total p-3 text-center clickable-card">
            <h6>Total Leads</h6>
            <div class="stat-number1"><?= $totalLeads; ?></div>
        </div>
    </a>
</div>

<!-- Total Appointments -->
<?php
$totalApptRedirectUrl = $base_url . http_build_query([
    'date_from' => $date_from,
    'date_to'   => $date_to
]);
?>
<div class="col-md-6">
    <a href="<?= $totalApptRedirectUrl; ?>" class="card-link">
        <div class="card-glass p-3 text-center clickable-card" style="border-left:5px solid #04AA6D; background: linear-gradient(135deg,#04AA6D,#037a4e); color:#fff;">
            <h6 style="color:#fff;">Total Appointments</h6>
            <div class="stat-number1"><?= $totalAppointments; ?></div>
        </div>
    </a>
</div>
                <!-- Status Loop -->
                <?php foreach($statusCounts as $status => $count): 
                    $redirectUrl = $base_url . http_build_query([
                        'status' => $status,
                        'date_from' => $date_from,
                        'date_to' => $date_to
                    ]);
                ?>
                <div class="col-md-6">
                    <a href="<?= $redirectUrl; ?>" class="card-link">
                        <div class="card-glass gradient-status p-3 text-center clickable-card">
                            <h6><?= $status; ?></h6>
                            <div class="stat-number"><?= $count; ?></div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>

            </div>
        </div>
    </div>


    <!-- RIGHT SIDE → LEAD SOURCE -->
    <div class="col-md-6">
        <div class="card-glass p-3">
            <h5 class="mb-3 text-center">📞 Lead Source</h5>
            <div class="row g-3">

                <?php foreach($leadSourceCounts as $source => $count): 
                    
                    $redirectUrl = $base_url . http_build_query([
                        'source' => $source,
                        'date_from' => $date_from,
                        'date_to' => $date_to
                    ]);
                ?>

                <div class="col-md-6">
                    <a href="<?= $redirectUrl; ?>" class="card-link">
                        <div class="card-glass p-3 text-center clickable-card"
                             style="border-left:5px solid #047edf;">
                            <h6><?= ucfirst($source); ?></h6>
                            <div class="stat-number"><?= $count; ?></div>
                        </div>
                    </a>
                </div>

                <?php endforeach; ?>

            </div>
        </div>
    </div>
<!-- TAGS -->
<div class="col-md-6">
    <div class="card-glass p-3">
        <h5 class="mb-3 text-center">🏷️ Campaign Tags</h5>

        <div class="row g-3">

            <?php if(!empty($tagCounts)) { ?>

                <?php foreach($tagCounts as $tag => $count): 

                    $redirectUrl = $base_url . http_build_query([
                        'tag' => $tag,
                        'date_from' => $date_from,
                        'date_to' => $date_to
                    ]);
                ?>

                <div class="col-md-6">
                    <a href="<?= $redirectUrl; ?>" class="card-link">

                        <div class="card-glass p-3 text-center clickable-card"
                             style="border-left:5px solid #ff9800;">

                            <h6><?= htmlspecialchars($tag); ?></h6>

                            <div class="stat-number">
                                <?= $count; ?>
                            </div>

                        </div>

                    </a>
                </div>

                <?php endforeach; ?>

            <?php } else { ?>

                <div class="col-md-12">
                    <div class="alert alert-warning text-center mb-0">
                        No Tags Found
                    </div>
                </div>

            <?php } ?>

        </div>
    </div>
</div>
</div>

<?php
$usertype = $this->session->userdata('USERTYPE');
$role     = $this->session->userdata('ROLE');

if($usertype == 'Admin' || $role == 'Sub Admin' || $role == 'Manager'){

    $userStatusData = []; // ✅ IMPORTANT INIT

    $query = "
    SELECT 
        assigned_to,
        status,
        COUNT(*) as total
    FROM tbl_lead
    WHERE 1 $where
    GROUP BY assigned_to, status
    ORDER BY assigned_to ASC
    ";

    $result = $conn->query($query);

    while($row = $result->fetch_assoc()){
        $user = !empty($row['assigned_to']) ? $row['assigned_to'] : 'Unassigned';
        $status = $row['status'];

        $userStatusData[$user][$status] = $row['total'];
    }
}
?>
<?php if(!empty($userStatusData)) { ?>

<div class="card-glass p-4 mt-5">
    <h5 class="mb-4 text-center">👨‍💼 Agent Performance (Status Wise)</h5>

    <div class="table-responsive">
        <table class="table table-bordered table-hover text-center">

            <thead class="bg-dark text-white">
                <tr>
                    <th>User</th>

                    <?php foreach($statusList as $s){ ?>
                        <th><?php echo $s['status']; ?></th>
                    <?php } ?>

                    <th>Total</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach($userStatusData as $user => $statuses){ 
                    $rowTotal = 0;
                ?>

                <tr>
                    <td><b><?php echo $user; ?></b></td>

                    <?php foreach($statusList as $s){ 
                        $count = isset($statuses[$s['status']]) ? $statuses[$s['status']] : 0;
                        $rowTotal += $count;
                    ?>
                        <td><?php echo $count; ?></td>
                    <?php } ?>

                    <td><b><?php echo $rowTotal; ?></b></td>
                </tr>

                <?php } ?>

            </tbody>

        </table>
    </div>
</div>

<?php } ?>
    <!-- Chart -->
  <div class="row g-4">

    <!-- STATUS CHART -->
    <div class="col-md-6">
        <div class="chart-container">
            <h5 class="mb-4 text-center">Status Wise Lead Distribution</h5>
            <canvas id="statusChart" height="120"></canvas>
        </div>
    </div>

    <!-- LEAD SOURCE CHART -->
    <div class="col-md-6">
        <div class="chart-container">
            <h5 class="mb-4 text-center">Lead Source Distribution</h5>
            <canvas id="sourceChart" height="120"></canvas>
        </div>
    </div>

</div>

</div>

<script>

/* STATUS CHART */
const statusCtx = document.getElementById('statusChart');

new Chart(statusCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_keys($statusCounts)); ?>,
        datasets: [{
            label: 'Leads Count',
            data: <?php echo json_encode(array_values($statusCounts)); ?>,
            backgroundColor: [
                '#ff9a9e','#fad0c4','#a18cd1','#fbc2eb','#84fab0',
                '#8fd3f4','#fccb90','#e0c3fc','#fddb92','#d1fdff'
            ],
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display:false } },
        scales: {
            y: { beginAtZero:true, ticks:{ color:"#333" } },
            x: { ticks:{ color:"#333" } }
        }
    }
});


/* LEAD SOURCE CHART */
const sourceCtx = document.getElementById('sourceChart');

new Chart(sourceCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_keys($leadSourceCounts)); ?>,
        datasets: [{
            data: <?php echo json_encode(array_values($leadSourceCounts)); ?>,
            backgroundColor: [
                '#07cdae',  // website
                '#047edf',  // call
                '#fe7096',  // missed
                '#ffc107',
                '#6f42c1'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>



       
            
         
          
          <style>
        table {
          width: 100%;
            margin: 0 auto;
            font-size: large;
            border: 2px solid gray;
        }
  
        h1 {
            text-align: center;
            color: #006600;
            font-size: xx-large;
            font-family: 'Gill Sans', 'Gill Sans MT', 
            ' Calibri', 'Trebuchet MS', 'sans-serif';
        }
  
        td {
            background-color: white;
            border: 2px solid gray;
        }
  
        th,
        td {
            font-weight: bold;
            border: 1px solid black;
            padding: 10px;
            text-align: center;
        }
  
        td {
            font-weight: lighter;
        }
    </style>
    <?php $fetchdate = date('Y-m-d'); ?>
          <?php
  
  // Username is root
  $user = 'u457158986_team';
  $password = '#Team@123'; 
    
  $database = 'u457158986_team'; 
    
  $servername='localhost';
  $mysqli = new mysqli($servername, $user, 
                  $password, $database);
    
  // Checking for connections
  if ($mysqli->connect_error) {
      die('Connect Error (' . 
      $mysqli->connect_errno . ') '. 
      $mysqli->connect_error);
  }

$username = $this->session->userdata('USERNAME');

// SQL query with LEFT JOIN to fetch username from tbl_orders if mobile matches
$sql = "
SELECT l.*, o.username AS order_by_user
FROM tbl_lead l
LEFT JOIN tbl_orders o 
    ON l.mobile_no = o.phone
WHERE l.assigned_by = '$username'
  AND DATE(l.tarik) = '$fetchdate'
ORDER BY l.tarik DESC
";

$result = $mysqli->query($sql);

// Handle Call Request (when button clicked)
if (isset($_GET['call']) && !empty($_GET['call'])) {
    $lead_id = base64_decode($_GET['call']);
    // basic validation
    $lead_id = intval($lead_id);

    // Fetch that lead
    $leadRes = $mysqli->query("SELECT * FROM tbl_lead WHERE id = '{$lead_id}' LIMIT 1");
    $lead = $leadRes->fetch_assoc();

    if ($lead) {
        // Clean and format number
        $cleanedNumber = preg_replace('/\D/', '', $lead['mobile_no']);
        if (strpos($cleanedNumber, '91') === 0) {
            $customerNumber = $cleanedNumber;
        } else {
            $customerNumber = '91' . $cleanedNumber;
        }

        $apiKey = $lead['api'];

        // API Call with CURL
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api-smartflo.tatateleservices.com/v1/click_to_call_support",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'api_key' => $apiKey,
                'customer_number' => $customerNumber,
                'get_call_id' => 1
            ]),
            CURLOPT_HTTPHEADER => ["accept: application/json", "content-type: application/json"],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            echo "<script>
                alert('Call Failed: " . addslashes($err) . "');
                window.location.href = 'admin-dashboard';
            </script>";
            exit;
        } else {
            $responseData = json_decode($response, true);
            $msg = isset($responseData['message']) ? $responseData['message'] : "Unknown response";
            echo "<script>
                alert('Call Status: " . addslashes($msg) . "');
                window.location.href = 'admin-dashboard';
            </script>";
            exit;
        }
    }
}
?>

<!-- Styles to force the row colors (use !important so bootstrap doesn't override) -->
<style>
/* table-custom is added to the table class below */
.table-custom tbody tr.website td { background-color: #07cdae !important; color: #fff !important; }
.table-custom tbody tr.call td    { background-color: #047edf !important; color: #fff !important; }
.table-custom tbody tr.missed td  { background-color: #fe7096 !important; color: #fff !important; }

/* Make sure header stays readable */
.table-custom thead th { color: #fff; }

/* Ensure action buttons remain visible on colored rows */
.table-custom tbody tr.website a.btn,
.table-custom tbody tr.call a.btn,
.table-custom tbody tr.missed a.btn {
    color: #000 !important;
    background: #fff !important;
    border: 1px solid rgba(0,0,0,0.12) !important;
}

/* Optional: keep links readable */
.table-custom tbody tr.website a,
.table-custom tbody tr.call a,
.table-custom tbody tr.missed a {
    color: #fff !important;
    text-decoration: none;
}
</style>

<div class="table-responsive">
    <table class="table table-hover table-bordered align-middle table-custom">
        <thead class="table-dark text-center">
            <tr>
                <th>Customer Name</th>
                <th>Age/Height</th>
                <th>Mobile Number</th>
                <th>Requirement</th>
                <th>Description</th>
                <th>Lead Status</th>
                <th>Date</th>
                
                <th>Lead Source</th>
                
                <th>Action</th>
                <th>Call</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($rows = $result->fetch_assoc()) {
            $id = base64_encode($rows['id']);
            $status = $rows['status'];

            // Raw value (for debugging in page source). Remove this line later if not needed.
            $leadSourceRaw = isset($rows['lead_source']) ? $rows['lead_source'] : '';

            // Normalize: trim, collapse whitespace, lowercase
            $leadSource = strtolower(trim(preg_replace('/\s+/', ' ', $leadSourceRaw)));

            // Fuzzy mapping (accept common variations)
            $leadSourceNormalized = '';
            if ($leadSource === 'website' || strpos($leadSource, 'web') !== false) {
                $leadSourceNormalized = 'website';
            } elseif ($leadSource === 'call' || strpos($leadSource, 'phone') !== false || strpos($leadSource, 'tele') !== false) {
                $leadSourceNormalized = 'call';
            } elseif ($leadSource === 'missed' || strpos($leadSource, 'miss') !== false) {
                $leadSourceNormalized = 'missed';
            }

            // Choose CSS class for the row
            $rowClass = '';
            if ($leadSourceNormalized === 'website') $rowClass = 'website';
            elseif ($leadSourceNormalized === 'call') $rowClass = 'call';
            elseif ($leadSourceNormalized === 'missed') $rowClass = 'missed';
        ?>
        <!-- Debug comment: raw / normalized (visible in page source) -->
        <!-- LeadSourceRaw: <?php echo htmlspecialchars($leadSourceRaw); ?> | Normalized: <?php echo $leadSourceNormalized; ?> -->
        <tr class="<?php echo $rowClass; ?>">
            <td><?php echo htmlspecialchars($rows['customer_name']); ?></td>
            <td><?php echo htmlspecialchars($rows['company_name']); ?></td>
           <td>
    <?php $mobile = $rows['mobile_no'];
echo htmlspecialchars(substr($mobile, 0, 6) . "XXXX"); ?></td>

            <td><?php echo htmlspecialchars($rows['requirement']); ?></td>
            <td><?php echo htmlspecialchars($rows['description']); ?></td>
            <td><?php echo htmlspecialchars($rows['status']); ?></td>
            <td><?php echo htmlspecialchars($rows['tarik']); ?></td>
            
            <td><?php echo htmlspecialchars($rows['lead_source']); ?></td>
           
            <td>
                <a href="detail_lead_details?view=<?php echo $id ?>&lead_status=<?php echo urlencode($status) ?>" class="btn btn-sm">View</a>
            </td>
            <td>
                <a href="?call=<?php echo $id; ?>" class="btn btn-sm">Call</a>
            </td>
        </tr>
        <?php } // end while ?>
        </tbody>
    </table>
</div>


        <hr>
        <hr>
    

</div>


