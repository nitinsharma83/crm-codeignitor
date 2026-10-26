<?php
session_start();

// Example session values
$usertype  = isset($_SESSION['USERTYPE']) ? $_SESSION['USERTYPE'] : 'User';
$loginUser = isset($_SESSION['USERNAME']) ? $_SESSION['USERNAME'] : 'demo_user';

// DB connection
$servername = "localhost";
$username   = "u457158986_team";
$password   = "#Team@123";
$dbname     = "u457158986_team";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

// Pagination settings
$limit  = 20;
$page   = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch Status List
$statuses = [];
$statusResult = $conn->query("SELECT id, status FROM tbl_status ORDER BY status ASC");
if ($statusResult && $statusResult->num_rows > 0) {
    while ($s = $statusResult->fetch_assoc()) {
        $statuses[] = $s;
    }
}

// Fetch sources
$sources = [];
$srcResult = $conn->query("SELECT id, name FROM tbl_source ORDER BY name ASC");
if ($srcResult && $srcResult->num_rows > 0) {
    while ($row = $srcResult->fetch_assoc()) {
        $sources[] = $row;
    }
}

// Fetch agents (Sales executives only — adjust column/value if yours differ)
$agents = [];
$agentQuery = "SELECT id, full_name FROM tbl_registration 
                WHERE full_name != '' AND role = 'Sales executive' 
                ORDER BY full_name ASC";
$agentResult = $conn->query($agentQuery);
if ($agentResult && $agentResult->num_rows > 0) {
    while ($a = $agentResult->fetch_assoc()) {
        $agents[] = $a;
    }
}

// Build filter conditions
$where = [];

// Customer name
if (!empty($_GET['customer_name'])) {
    $customer_name = $conn->real_escape_string($_GET['customer_name']);
    $where[] = "customer_name LIKE '%$customer_name%'";
}

// Mobile number
if (!empty($_GET['mobile_no'])) {
    $mobile_no = $conn->real_escape_string($_GET['mobile_no']);
    $where[] = "(mobile_no LIKE '%$mobile_no%' OR secondary_mobile_no LIKE '%$mobile_no%')";
}

// Source
if (!empty($_GET['source'])) {
    $source = $conn->real_escape_string($_GET['source']);
    $where[] = "lead_source = '$source'";
}

// Status filter
if (!empty($_GET['status'])) {
    $status = $conn->real_escape_string($_GET['status']);
    $where[] = "status = '$status'";
}

// Compare assigned_to with full_name
if (!empty($_GET['agent_name'])) {
    $agent_name = $conn->real_escape_string($_GET['agent_name']);
    $where[] = "assigned_to = '$agent_name'";
}

// Date range
if (!empty($_GET['date_from']) && !empty($_GET['date_to'])) {
    $date_from = $conn->real_escape_string($_GET['date_from']);
    $date_to   = $conn->real_escape_string($_GET['date_to']);
    $where[] = "DATE(created_date) BETWEEN '$date_from' AND '$date_to'";
}
// Follow Up Date range
if (!empty($_GET['followup_from']) && !empty($_GET['followup_to'])) {
    $followup_from = $conn->real_escape_string($_GET['followup_from']);
    $followup_to   = $conn->real_escape_string($_GET['followup_to']);
    $where[] = "DATE(tarik) BETWEEN '$followup_from' AND '$followup_to'";
}

// Build main SQL
$sqlBase = "FROM tbl_lead WHERE 1";
if (!empty($where)) {
    $sqlBase .= " AND " . implode(" AND ", $where);
}

// Count total records (also used as the "filtered" count shown above the table)
$countSql = "SELECT COUNT(*) AS total " . $sqlBase;
$countResult = $conn->query($countSql);
$totalRecords = ($countResult) ? (int)$countResult->fetch_assoc()['total'] : 0;
$totalPages = max(1, ceil($totalRecords / $limit));

// Quick counts for the summary strip (missed / new / old) within the current filter scope
$missedSql = "SELECT COUNT(*) AS total " . $sqlBase . " AND status = 'Missed'";
$missedResult = $conn->query($missedSql);
$missedCount = ($missedResult) ? (int)$missedResult->fetch_assoc()['total'] : 0;

$newSql = "SELECT COUNT(*) AS total " . $sqlBase . " AND (patient_type IS NULL OR patient_type != 'Old Patient')";
$newResult = $conn->query($newSql);
$newCount = ($newResult) ? (int)$newResult->fetch_assoc()['total'] : 0;

$oldCount = max(0, $totalRecords - $newCount);

// Fetch paginated records
$sql = "SELECT id, lead_id, customer_name, city, mobile_no, requirement, description, lead_source, status, created_date, tarik, assigned_to, patient_type, has_appointment, has_prescription, tag
        $sqlBase
        ORDER BY created_date DESC, id DESC
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// Helper: pick a badge color family based on the status text
function statusBadgeClass($statusText) {
    $s = strtolower(trim($statusText));
    if ($s === 'missed') return 'badge-missed';
    if (in_array($s, ['closed', 'converted', 'won'])) return 'badge-success';
    if (in_array($s, ['follow up', 'follow-up', 'pending', 'in progress'])) return 'badge-warning';
    if (in_array($s, ['lost', 'cancelled', 'rejected'])) return 'badge-danger';
    return 'badge-neutral';
}
?>
<style>
:root{
    --crm-primary:#4f6ef7;
    --crm-primary-dark:#3b53d1;
    --crm-primary-light:#eef1ff;
    --crm-bg:#f4f6fb;
    --crm-card:#ffffff;
    --crm-border:#e7eaf3;
    --crm-text:#2a2f45;
    --crm-text-muted:#7c8194;
    --crm-success:#1fae63;
    --crm-success-bg:#e7f8ee;
    --crm-warning:#e2a300;
    --crm-warning-bg:#fff6e0;
    --crm-danger:#e0483e;
    --crm-danger-bg:#fdecea;
    --crm-missed-bg:#fdeceb;
    --crm-missed-border:#e0483e;
    --crm-radius:14px;
    --crm-radius-sm:8px;
    --crm-shadow:0 2px 10px rgba(30,40,80,0.06);
    --crm-shadow-hover:0 6px 18px rgba(30,40,80,0.10);
}

* { box-sizing: border-box; }

body {
    font-family: 'Segoe UI', Roboto, Arial, sans-serif;
    background: var(--crm-bg);
    color: var(--crm-text);
}

.crm-wrap { padding: 22px; max-width: 1600px; margin: 0 auto; }

.crm-page-header {
    display:flex; align-items:center; justify-content:space-between;
    flex-wrap: wrap; gap: 12px; margin-bottom: 20px;
}
.crm-page-header h3 {
    display:flex; align-items:center; gap:10px;
    font-size: 22px; font-weight: 700; color: var(--crm-text); margin:0;
}
.crm-page-header .icon-badge {
    width: 40px; height:40px; border-radius: 12px;
    display:flex; align-items:center; justify-content:center;
    background: linear-gradient(135deg, var(--crm-primary), #7d95ff);
    color:#fff; font-size:17px;
    box-shadow: 0 4px 10px rgba(79,110,247,0.35);
}
.crm-back-btn {
    background:#fff; border:1px solid var(--crm-border); color: var(--crm-text);
    padding:8px 16px; border-radius: 999px; text-decoration:none; font-size:13px; font-weight:600;
    box-shadow: var(--crm-shadow); transition: all .15s ease; cursor:pointer;
}
.crm-back-btn:hover { background: var(--crm-primary); color:#fff; border-color: var(--crm-primary); }
.crm-filter-toggle.active { background: var(--crm-primary); color:#fff; border-color: var(--crm-primary); }

/* Summary strip */
.crm-stats {
    display:grid; grid-template-columns: repeat(4, 1fr); gap:16px; margin-bottom: 22px;
}
.crm-stat-card {
    background: var(--crm-card); border-radius: var(--crm-radius);
    padding: 16px 18px; box-shadow: var(--crm-shadow);
    display:flex; align-items:center; gap:14px;
    border: 1px solid var(--crm-border);
}
.crm-stat-icon {
    width:44px; height:44px; border-radius: 12px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center; font-size:18px;
}
.crm-stat-total .crm-stat-icon { background: var(--crm-primary-light); color: var(--crm-primary); }
.crm-stat-new .crm-stat-icon { background: var(--crm-danger-bg); color: var(--crm-danger); }
.crm-stat-old .crm-stat-icon { background: var(--crm-success-bg); color: var(--crm-success); }
.crm-stat-missed .crm-stat-icon { background: var(--crm-missed-bg); color: var(--crm-missed-border); }
.crm-stat-value { font-size: 20px; font-weight:700; line-height:1; }
.crm-stat-label { font-size: 12px; color: var(--crm-text-muted); margin-top:4px; }

/* Filter panel */
.filter-box {
    background: var(--crm-card);
    border-radius: var(--crm-radius);
    padding: 20px 22px;
    margin-bottom: 22px;
    box-shadow: var(--crm-shadow);
    border: 1px solid var(--crm-border);
    display:grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px 18px;
    align-items:end;
}
.filter-box .f-group { display:flex; flex-direction:column; gap:6px; }
.filter-box label {
    font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing:.03em;
    color: var(--crm-text-muted); margin:0;
}
.filter-box select, .filter-box input {
    padding: 9px 12px; border-radius: var(--crm-radius-sm);
    border: 1px solid var(--crm-border); background:#fbfcff; font-size:13px;
    color: var(--crm-text); width:100%; transition: border-color .15s ease, box-shadow .15s ease;
}
.filter-box select:focus, .filter-box input:focus {
    outline:none; border-color: var(--crm-primary);
    box-shadow: 0 0 0 3px rgba(79,110,247,0.15);
}
.filter-box .f-actions { grid-column: 1 / -1; display:flex; justify-content:flex-end; gap:10px; margin-top:4px; }
.filter-box button {
    padding: 10px 22px; border-radius: 999px; border:none; cursor:pointer;
    background: linear-gradient(135deg, var(--crm-primary), var(--crm-primary-dark));
    color:#fff; font-weight:600; font-size:13px;
    box-shadow: 0 4px 12px rgba(79,110,247,0.35);
    transition: transform .12s ease, box-shadow .12s ease;
}
.filter-box button:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(79,110,247,0.45); }
.filter-box .f-reset {
    background:#fff; color: var(--crm-text-muted); border:1px solid var(--crm-border); box-shadow:none;
}
.filter-box .f-reset:hover { background:#f2f3f8; transform:none; box-shadow:none; }

/* Card wrapper for table + form sections */
.crm-card {
    background: var(--crm-card);
    border-radius: var(--crm-radius);
    box-shadow: var(--crm-shadow);
    border: 1px solid var(--crm-border);
    overflow:hidden;
    margin-bottom: 24px;
}
.crm-card .card-header {
    display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;
    padding: 16px 20px; border-bottom: 1px solid var(--crm-border);
    font-weight:700; font-size:15px; background: #fafbff;
}
.crm-card .card-header i { color: var(--crm-primary); margin-right:6px; }
.crm-card .card-body { padding: 20px; }

.crm-count-pill {
    font-size:12px; font-weight:600;
    background: var(--crm-primary-light); color: var(--crm-primary);
    padding:4px 12px; border-radius:999px;
}

/* Table */
.table-responsive { width:100%; overflow-x:auto; -webkit-overflow-scrolling: touch; }
table.crm-table { width:100%; border-collapse: collapse; min-width: 1300px; }
table.crm-table thead th {
    background: var(--crm-primary-light); color: var(--crm-primary-dark);
    text-transform: uppercase; font-size: 11px; letter-spacing:.04em;
    padding: 12px 14px; text-align:left; border-bottom: 2px solid var(--crm-border);
    white-space:nowrap;
}
table.crm-table tbody td {
    padding: 13px 14px; font-size: 13px; border-bottom: 1px solid var(--crm-border);
    vertical-align: middle; color: var(--crm-text);
}
table.crm-table tbody tr { transition: background .12s ease; }
table.crm-table tbody tr:hover { background: #f7f9ff; }
table.crm-table tbody tr:nth-child(even) { background: #fbfcfe; }
table.crm-table tbody tr:nth-child(even):hover { background: #f2f5ff; }

/* Truncate long free-text columns so rows stay a single line; full text on hover via title attr */
table.crm-table td.truncate {
    max-width: 220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; cursor: help;
}

/* Missed row highlight */
table.crm-table tbody tr.row-missed {
    background: var(--crm-missed-bg) !important;
    box-shadow: inset 3px 0 0 var(--crm-missed-border);
}
table.crm-table tbody tr.row-missed:hover { background: #fbdedb !important; }

.type-pill {
    display:inline-flex; align-items:center; gap:5px;
    padding: 4px 10px; border-radius: 999px; font-size:11px; font-weight:700; letter-spacing:.02em;
}
.type-old { background: var(--crm-success-bg); color: var(--crm-success); }
.type-new { background: var(--crm-danger-bg); color: var(--crm-danger); }

.badge-status {
    display:inline-block; padding:5px 12px; border-radius:999px; font-size:11px; font-weight:700;
    text-decoration:none; letter-spacing:.02em;
}
.badge-missed { background: var(--crm-missed-border); color:#fff; }
.badge-success { background: var(--crm-success); color:#fff; }
.badge-warning { background: var(--crm-warning); color:#fff; }
.badge-danger { background: var(--crm-danger); color:#fff; }
.badge-neutral { background: #eef0f6; color: var(--crm-text-muted); }

.action-btn {
    display:inline-flex; align-items:center; justify-content:center;
    width:32px; height:32px; border-radius: 9px; margin-right:6px;
    text-decoration:none; font-size:13px; transition: transform .12s ease;
}
.action-btn:hover { transform: translateY(-1px); }
.action-view { background: var(--crm-primary-light); color: var(--crm-primary); }
.action-edit { background: var(--crm-success-bg); color: var(--crm-success); }

.reels-btn {
    display:inline-flex; align-items:center; gap:6px;
    background: var(--crm-success-bg); color: var(--crm-success);
    border:none; padding:7px 14px; border-radius:999px; font-size:12px; font-weight:700;
    text-decoration:none; cursor:pointer; transition: transform .12s ease;
}
.reels-btn:hover { transform: translateY(-1px); }
.reels-btn:disabled { opacity:.6; cursor:not-allowed; transform:none; }
.reels-status { margin-top:4px; font-size:11px; }

.crm-empty { text-align:center; padding: 40px 20px; color: var(--crm-text-muted); font-size:14px; }

/* Pagination */
.crm-pagination { list-style:none; display:flex; flex-wrap:wrap; gap:6px; margin: 18px 0 0; padding:0; }
.crm-pagination li a, .crm-pagination li span {
    display:inline-flex; align-items:center; justify-content:center;
    min-width:34px; height:34px; padding:0 10px; border-radius: var(--crm-radius-sm);
    border:1px solid var(--crm-border); text-decoration:none; color: var(--crm-text);
    font-size:13px; background:#fff; transition: all .12s ease;
}
.crm-pagination li a:hover { background: var(--crm-primary-light); border-color: var(--crm-primary); color: var(--crm-primary-dark); }
.crm-pagination li.active a, .crm-pagination li.active span { background: var(--crm-primary); color:#fff; border-color: var(--crm-primary); }
.crm-pagination li.disabled span { border:none; background:transparent; color: var(--crm-text-muted); }

/* Add / Update / View forms */
.crm-form-grid { display:grid; grid-template-columns: repeat(2, 1fr); gap: 16px 20px; }
.crm-form-grid .form-group { display:flex; flex-direction:column; gap:6px; }
.crm-form-grid label { font-size:12px; font-weight:700; color: var(--crm-text-muted); text-transform:uppercase; letter-spacing:.03em; }
.crm-form-grid .form-control {
    padding:10px 12px; border-radius: var(--crm-radius-sm); border:1px solid var(--crm-border);
    background:#fbfcff; font-size:13px;
}
.crm-form-grid .form-control:focus { outline:none; border-color: var(--crm-primary); box-shadow: 0 0 0 3px rgba(79,110,247,0.15); }
.crm-form-grid textarea.form-control { min-height: 90px; resize: vertical; }
.crm-form-submit {
    background:linear-gradient(135deg,var(--crm-primary),var(--crm-primary-dark)); color:#fff; border:none;
    padding:10px 26px; border-radius:999px; font-weight:600; cursor:pointer; margin-top:18px;
}

.crm-detail-table { width:100%; border-collapse:collapse; }
.crm-detail-table th { width: 220px; background:#fafbff; color: var(--crm-text-muted); font-size:12px; text-transform:uppercase; letter-spacing:.03em; }
.crm-detail-table td, .crm-detail-table th { padding:12px 16px; border:1px solid var(--crm-border); font-size:13px; }

/* Compact mode — denser rows/spacing so more leads fit on screen at once */
.crm-compact .crm-wrap { padding: 14px 22px; }
.crm-compact .crm-page-header { margin-bottom: 14px; }
.crm-compact .crm-stats { gap: 10px; margin-bottom: 14px; }
.crm-compact .crm-stat-card { padding: 10px 12px; gap: 10px; }
.crm-compact .crm-stat-icon { width: 32px; height: 32px; font-size: 13px; border-radius: 9px; }
.crm-compact .crm-stat-value { font-size: 16px; }
.crm-compact .crm-stat-label { font-size: 10px; margin-top: 2px; }
.crm-compact .filter-box { padding: 14px 16px; gap: 10px 14px; margin-bottom: 14px; }
.crm-compact .filter-box input, .crm-compact .filter-box select { padding: 6px 10px; font-size: 12px; }
.crm-compact .filter-box label { font-size: 10px; }
.crm-compact .crm-card { margin-bottom: 14px; }
.crm-compact .crm-card .card-header { padding: 10px 16px; font-size: 13px; }
.crm-compact .crm-card .card-body { padding: 12px; }
.crm-compact table.crm-table thead th { padding: 7px 10px; font-size: 10px; }
.crm-compact table.crm-table tbody td { padding: 5px 10px; font-size: 12px; }
.crm-compact table.crm-table td.truncate { max-width: 160px; }
.crm-compact .type-pill { padding: 2px 8px; font-size: 10px; }
.crm-compact .badge-status { padding: 3px 9px; font-size: 10px; }
.crm-compact .action-btn { width: 26px; height: 26px; font-size: 11px; margin-right: 4px; }
.crm-compact .reels-btn { padding: 4px 10px; font-size: 11px; }
.crm-compact .reels-status { font-size: 10px; margin-top: 2px; }
.crm-compact .crm-pagination li a, .crm-compact .crm-pagination li span { min-width: 28px; height: 28px; font-size: 12px; }
.crm-density-toggle .fa { margin-right: 2px; }

/* Responsive */
@media (max-width: 1100px) {
    .filter-box { grid-template-columns: repeat(2, 1fr); }
    .crm-stats { grid-template-columns: repeat(2, 1fr); }
    .crm-form-grid { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
    .crm-wrap { padding: 14px; }
    .filter-box { grid-template-columns: 1fr; padding: 16px; }
    .crm-stats { grid-template-columns: repeat(2, 1fr); gap:10px; }
    .crm-stat-card { padding:12px; }
    .crm-page-header h3 { font-size:18px; }

    /* Card-style table on small screens */
    table.crm-table { min-width: 0; }
    table.crm-table thead { display:none; }
    table.crm-table, table.crm-table tbody, table.crm-table tr, table.crm-table td { display:block; width:100%; }
    table.crm-table tbody tr {
        margin-bottom: 14px; border: 1px solid var(--crm-border); border-radius: var(--crm-radius-sm);
        padding: 10px 12px; box-shadow: var(--crm-shadow);
    }
    table.crm-table tbody tr.row-missed { box-shadow: 0 0 0 1px var(--crm-missed-border), var(--crm-shadow); }
    table.crm-table tbody td {
        border:none; padding: 8px 4px; display:flex; justify-content:space-between; align-items:center; gap:10px;
        text-align:right;
    }
    table.crm-table tbody td::before {
        content: attr(data-label); font-weight:700; font-size:11px; color: var(--crm-text-muted);
        text-transform:uppercase; letter-spacing:.03em; text-align:left; flex-shrink:0;
    }
}
</style>

<script type="text/javascript">
function sendReelsFollowup(leadId, btn) {
    if (!confirm('Send reels follow-up WhatsApp message to this lead?')) return;

    var statusSpan = document.getElementById('reels_followup_status_' + leadId);
    var originalText = btn.innerText || btn.textContent;
    btn.disabled = true;
    btn.innerText = 'Sending...';
    if (statusSpan) statusSpan.innerHTML = '';

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo base_url("api/send_reels_followup"); ?>', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            btn.disabled = false;
            btn.innerText = originalText;
            try {
                var res = JSON.parse(xhr.responseText);
                if (statusSpan) {
                    if (res.status === 'success') {
                        statusSpan.innerHTML = '<span style="color:green;">Sent &#10004;</span>';
                    } else {
                        statusSpan.innerHTML = '<span style="color:red;">Failed: ' + res.message + '</span>';
                    }
                }
            } catch (e) {
                if (statusSpan) statusSpan.innerHTML = '<span style="color:red;">Error sending request</span>';
            }
        }
    };
    xhr.send('lead_id=' + encodeURIComponent(leadId));
}

function crmToggleFilters() {
    var panel = document.getElementById('filterPanel');
    var btn = document.getElementById('filterToggleBtn');
    var isOpen = panel.style.display !== 'none';
    panel.style.display = isOpen ? 'none' : 'grid';
    btn.classList.toggle('active', !isOpen);
    if (!isOpen) {
        panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function crmToggleDensity() {
    var wrap = document.getElementById('crmWrap');
    var btn = document.getElementById('densityToggleBtn');
    var isCompact = wrap.classList.toggle('crm-compact');
    btn.classList.toggle('active', isCompact);
    btn.innerHTML = isCompact
        ? '<i class="fa fa-expand-alt"></i> Comfortable View'
        : '<i class="fa fa-compress-alt"></i> Compact View';
    try { localStorage.setItem('crm_density', isCompact ? 'compact' : 'comfortable'); } catch (e) {}
}

document.addEventListener('DOMContentLoaded', function () {
    var panel = document.getElementById('filterPanel');
    var filterBtn = document.getElementById('filterToggleBtn');
    if (panel && panel.style.display !== 'none') {
        filterBtn.classList.add('active');
    }

    var wrap = document.getElementById('crmWrap');
    var densityBtn = document.getElementById('densityToggleBtn');
    var saved = null;
    try { saved = localStorage.getItem('crm_density'); } catch (e) {}
    if (saved === 'compact') {
        wrap.classList.add('crm-compact');
        densityBtn.classList.add('active');
        densityBtn.innerHTML = '<i class="fa fa-expand-alt"></i> Comfortable View';
    }
});
</script>

<div class="content-wrapper">
  <div class="crm-wrap" id="crmWrap">

    <div class="crm-page-header">
      <h3>
        <span class="icon-badge"><i class="fa fa-users"></i></span>
        Leads
      </h3>
      <span style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
        <button type="button" id="densityToggleBtn" class="crm-back-btn crm-density-toggle" onclick="crmToggleDensity()">
          <i class="fa fa-compress-alt"></i> Compact View
        </button>
        <button type="button" id="filterToggleBtn" class="crm-back-btn crm-filter-toggle" onclick="crmToggleFilters()">
          <i class="fa fa-sliders-h"></i> Filters
        </button>
        <a href="javascript:history.back()" class="crm-back-btn"><i class="fa fa-arrow-left"></i> Back</a>
      </span>
    </div>

    <!-- Summary strip -->
    <div class="crm-stats">
      <div class="crm-stat-card crm-stat-total">
        <div class="crm-stat-icon"><i class="fa fa-layer-group"></i></div>
        <div>
          <div class="crm-stat-value"><?php echo $totalRecords; ?></div>
          <div class="crm-stat-label">Total Leads</div>
        </div>
      </div>
      <div class="crm-stat-card crm-stat-new">
        <div class="crm-stat-icon"><i class="fa fa-user-plus"></i></div>
        <div>
          <div class="crm-stat-value"><?php echo $newCount; ?></div>
          <div class="crm-stat-label">New Leads</div>
        </div>
      </div>
      <div class="crm-stat-card crm-stat-old">
        <div class="crm-stat-icon"><i class="fa fa-user-check"></i></div>
        <div>
          <div class="crm-stat-value"><?php echo $oldCount; ?></div>
          <div class="crm-stat-label">Old Patients</div>
        </div>
      </div>
      <div class="crm-stat-card crm-stat-missed">
        <div class="crm-stat-icon"><i class="fa fa-phone-slash"></i></div>
        <div>
          <div class="crm-stat-value"><?php echo $missedCount; ?></div>
          <div class="crm-stat-label">Missed</div>
        </div>
      </div>
    </div>

    <!-- Filters (hidden by default; toggled via the Filters button above) -->
    <?php
    $hasActiveFilter = !empty($_GET['customer_name']) || !empty($_GET['mobile_no']) || !empty($_GET['source'])
        || !empty($_GET['status']) || !empty($_GET['agent_name']) || !empty($_GET['date_from']) || !empty($_GET['date_to'])
        || !empty($_GET['followup_from']) || !empty($_GET['followup_to']);
    ?>
    <form method="GET" action="" class="filter-box" id="filterPanel" style="display:<?php echo $hasActiveFilter ? 'grid' : 'none'; ?>;">
        <div class="f-group">
            <label>Customer Name</label>
            <input type="text" name="customer_name" placeholder="Search by name"
                   value="<?php echo !empty($_GET['customer_name']) ? htmlspecialchars($_GET['customer_name']) : ''; ?>">
        </div>

        <div class="f-group">
            <label>Mobile No</label>
            <input type="text" name="mobile_no" placeholder="Search by mobile"
                   value="<?php echo !empty($_GET['mobile_no']) ? htmlspecialchars($_GET['mobile_no']) : ''; ?>">
        </div>

        <div class="f-group">
            <label>Source</label>
            <select name="source">
                <option value="">All Sources</option>
                <?php foreach ($sources as $src): ?>
                    <option value="<?php echo htmlspecialchars($src['name']); ?>"
                        <?php if (!empty($_GET['source']) && $_GET['source'] == $src['name']) echo "selected"; ?>>
                        <?php echo htmlspecialchars($src['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="f-group">
            <label>Lead Status</label>
            <select name="status">
                <option value="">All Statuses</option>
                <?php foreach ($statuses as $s): ?>
                    <option value="<?php echo htmlspecialchars($s['status']); ?>"
                        <?php if (isset($_GET['status']) && $_GET['status'] == $s['status']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($s['status']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="f-group">
            <label>Agent Name</label>
            <select name="agent_name">
                <option value="">All Agents</option>
                <?php foreach ($agents as $ag): ?>
                    <option value="<?php echo htmlspecialchars($ag['full_name']); ?>"
                        <?php if (!empty($_GET['agent_name']) && $_GET['agent_name'] == $ag['full_name']) echo "selected"; ?>>
                        <?php echo htmlspecialchars($ag['full_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="f-group">
            <label>Date From</label>
            <input type="date" name="date_from" value="<?php echo !empty($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : ''; ?>">
        </div>

        <div class="f-group">
            <label>Date To</label>
            <input type="date" name="date_to" value="<?php echo !empty($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : ''; ?>">
        </div>

        <div class="f-group">
            <label>Follow Up From</label>
            <input type="date" name="followup_from" value="<?php echo !empty($_GET['followup_from']) ? htmlspecialchars($_GET['followup_from']) : ''; ?>">
        </div>

        <div class="f-group">
            <label>Follow Up To</label>
            <input type="date" name="followup_to" value="<?php echo !empty($_GET['followup_to']) ? htmlspecialchars($_GET['followup_to']) : ''; ?>">
        </div>

        <div class="f-actions">
            <a href="?" class="crm-back-btn f-reset">Reset</a>
            <button type="submit"><i class="fa fa-search"></i> Search</button>
        </div>
    </form>

    <div class="row">
      <div class="col-md-12">
        <?php
        $tbl_lead_source = $this->GenericModel->get_record('tbl_source', "name");

        if (isset($_GET['add'])) {
        ?>
        <div class="crm-card">
          <div class="card-header">
            <span><i class="fa fa-shopping-bag"></i> Add New Lead</span>
            <a href="javascript:history.back()" class="crm-back-btn">Back</a>
          </div>
          <div class="card-body">
            <div class="alert alert-danger" id="error_message_change" style="display:none;"></div>
            <div class="alert alert-success" id="success_message_submit" style="display:none;"></div>
            <form method="post" id="lead_creation_form">
              <div class="crm-form-grid">
                <div class="form-group">
                  <label>Customer Name</label>
                  <input type="text" class="form-control" value="" name="customer_name" id="customer_name" placeholder="Customer Name">
                </div>
                <div class="form-group">
                  <label>Age / Height</label>
                  <input type="text" class="form-control" value="" name="company_name" id="company_name" placeholder="Company Name">
                </div>
                <div class="form-group">
                  <label>Address</label>
                  <input type="text" class="form-control" value="" name="city" id="city" placeholder="City">
                </div>
                <div class="form-group">
                  <label>Mobile No</label>
                  <input type="text" class="form-control" value="" name="mobile_no" id="mobile_no" placeholder="Mobile No">
                </div>
                <div class="form-group">
                  <label>Whatsapp Number</label>
                  <input type="text" class="form-control" value="" name="secondary_mobile_no" id="secondary_mobile_no" placeholder="Whatsapp Number">
                </div>
                <div class="form-group">
                  <label>Email ID</label>
                  <input type="email" class="form-control" value="" name="email_id" id="email_id" placeholder="Email ID">
                </div>
                <div class="form-group">
                  <label>Date</label>
                  <input type="date" class="form-control" value="" name="tarik" id="tarik">
                </div>
                <div class="form-group">
                  <label>Select Lead Source</label>
                  <select class="form-control" name="lead_source" id="lead_source">
                    <option value="">Select Lead Source</option>
                    <?php foreach ($tbl_lead_source as $row) { ?>
                      <option value="<?php echo $row->name; ?>"><?php echo $row->name; ?></option>
                    <?php } ?>
                  </select>
                </div>
                <div class="form-group">
                  <label>Lead Status</label>
                  <select class="form-control" name="status" id="status">
                    <option value="">Select Lead Status</option>
                    <?php foreach ($status as $row) { ?>
                      <option value="<?php echo $row->status; ?>"><?php echo $row->status; ?></option>
                    <?php } ?>
                  </select>
                </div>
                <div class="form-group">
                  <label>Lead Assigned</label>
                  <select class="form-control" name="userid" id="userid">
                    <option value="">Select Lead Assigned</option>
                    <?php foreach ($employee as $row) { ?>
                      <option value="<?php echo $row->id; ?>"><?php echo $row->full_name; ?></option>
                    <?php } ?>
                  </select>
                </div>
                <div class="form-group">
                  <label>Lead Requirement</label>
                  <textarea id="requirement" name="requirement" class="form-control" placeholder="Lead Requirement"></textarea>
                </div>
                <div class="form-group">
                  <label>Lead Description</label>
                  <textarea id="description" name="description" class="form-control" placeholder="Lead Description"></textarea>
                </div>
              </div>
              <button type="submit" id="submit_button" class="crm-form-submit">Add Lead</button>
            </form>
          </div>
        </div>

        <?php } elseif (isset($_GET['update'])) {
            $id = base64_decode($_GET['update']);
            $q = $this->db->query("select *from tbl_lead where id='" . $id . "'")->result();
        ?>
        <div class="crm-card">
          <div class="card-header">
            <span><i class="fa fa-leanpub"></i> Update Lead</span>
            <a href="javascript:history.back()" class="crm-back-btn">Back</a>
          </div>
          <div class="card-body">
            <div class="alert alert-danger" id="error_message_change" style="display:none;"></div>
            <div class="alert alert-success" id="success_message_submit" style="display:none;"></div>
            <form method="post" id="update_creation_form">
              <input type="hidden" value="<?php echo $id; ?>" id="id" name="id"/>
              <div class="crm-form-grid">
                <div class="form-group">
                  <label>Customer Name</label>
                  <input type="text" class="form-control" value="<?php echo $q[0]->customer_name; ?>" name="customer_name" id="customer_name" placeholder="Customer Name">
                </div>
                <div class="form-group">
                  <label>Age / Height</label>
                  <input type="text" class="form-control" value="<?php echo $q[0]->company_name; ?>" name="company_name" id="company_name" placeholder="Company Name">
                </div>
                <div class="form-group">
                  <label>City</label>
                  <input type="text" class="form-control" value="<?php echo $q[0]->city; ?>" name="city" id="city" placeholder="City">
                </div>
                <div class="form-group">
                  <label>State</label>
                  <input type="text" class="form-control" value="<?php echo $q[0]->state; ?>" name="state" id="state" placeholder="State">
                </div>
                <div class="form-group">
                  <label>Mobile No</label>
                  <input type="text" class="form-control"
                         value="<?php
                         $mobile = $q[0]->mobile_no ?? '';
                         echo htmlspecialchars(substr($mobile, 0, -4) . 'xxxx');
                         ?>"
                         name="mobile_no" id="mobile_no" placeholder="Mobile No">
                </div>
                <div class="form-group">
                  <label>Whatsapp Number</label>
                  <input type="text" class="form-control" value="<?php echo $q[0]->secondary_mobile_no; ?>" name="secondary_mobile_no" id="secondary_mobile_no" placeholder="Whatsapp Number">
                </div>
                <div class="form-group">
                  <label>Email ID</label>
                  <input type="email" class="form-control" value="<?php echo $q[0]->email_id; ?>" name="email_id" id="email_id" placeholder="Email ID">
                </div>
                <div class="form-group">
                  <label>Date</label>
                  <input type="date" class="form-control" value="<?php echo (!empty($q[0]->tarik) && strpos($q[0]->tarik,'0000')!==0) ? date('Y-m-d', strtotime($q[0]->tarik)) : ''; ?>" name="tarik" id="tarik">
                </div>
                <div class="form-group">
                  <label>Select Lead Source</label>
                  <select class="form-control" name="lead_source" id="lead_source" disabled="">
                    <?php if (empty($q[0]->lead_source)) { ?>
                      <option value="">Select Lead Source</option>
                    <?php } else { ?>
                      <option value="<?php echo $q[0]->lead_source; ?>"><?php echo $q[0]->lead_source; ?></option>
                      <?php foreach ($tbl_lead_source as $row) { ?>
                        <option value="<?php echo $row->name; ?>"><?php echo $row->name; ?></option>
                      <?php } ?>
                    <?php } ?>
                  </select>
                </div>
                <div class="form-group">
                  <label>Lead Status</label>
                  <select class="form-control" name="status" id="status">
                    <option value="<?php echo $q[0]->status; ?>"><?php echo $q[0]->status; ?></option>
                    <?php foreach ($status as $row) { if ($q[0]->status != $row->status) { ?>
                      <option value="<?php echo $row->status; ?>"><?php echo $row->status; ?></option>
                    <?php } } ?>
                  </select>
                </div>
                <div class="form-group">
                  <label>Lead Assigned To</label>
                  <select class="form-control" name="userid" id="userid" disabled="">
                    <?php foreach ($employee as $row) { ?>
                      <option <?php if ($q[0]->userid == $row->id) { echo 'selected'; } ?> value="<?php echo $row->id; ?>"><?php echo $row->full_name; ?></option>
                    <?php } ?>
                  </select>
                </div>
                <div class="form-group">
                  <label>Lead Requirement</label>
                  <textarea id="requirement" name="requirement" class="form-control" placeholder="Lead Requirement"><?php echo $q[0]->requirement; ?></textarea>
                </div>
                <div class="form-group">
                  <label>Lead Description</label>
                  <textarea id="description" name="description" class="form-control" placeholder="Lead Description"><?php echo $q[0]->requirement; ?></textarea>
                </div>
              </div>
              <button type="submit" id="submit_button" class="crm-form-submit">Update Lead</button>
            </form>
          </div>
        </div>

        <?php } elseif (isset($_GET['view'])) {
            $id = base64_decode($_GET['view']);
            $q = $this->db->query("select *from tbl_lead where id='" . $id . "'")->result();
        ?>
        <div class="crm-card">
          <div class="card-header">
            <span><i class="fa fa-eye"></i> View Lead Details</span>
            <a href="javascript:history.back()" class="crm-back-btn">Back</a>
          </div>
          <div class="card-body">
            <table class="crm-detail-table">
              <tbody>
                <tr><th>Loan ID</th><td><?php echo $q[0]->lead_id; ?></td></tr>
                <tr><th>Customer Name</th><td><?php echo $q[0]->customer_name; ?></td></tr>
                <tr><th>Age / Height</th><td><?php echo $q[0]->company_name; ?></td></tr>
                <tr><th>Customer Mobile No</th><td><?php echo $q[0]->mobile_no; ?></td></tr>
                <tr><th>Whatsapp No</th><td><?php if (!empty($q[0]->secondary_mobile_no)) { echo $q[0]->secondary_mobile_no; } ?></td></tr>
                <tr><th>Email ID</th><td><?php echo $q[0]->email_id; ?></td></tr>
                <tr><th>Address</th><td><?php echo $q[0]->city; if (!empty($q[0]->state)) { echo ', ' . $q[0]->state; } ?></td></tr>
                <tr><th>Lead Source</th><td><?php echo $q[0]->lead_source; ?></td></tr>
                <tr><th>Lead Status</th><td><?php echo $q[0]->status; ?></td></tr>
                <tr><th>Assigned By</th><td><?php echo $q[0]->assigned_by; ?></td></tr>
                <tr><th>Assigned To</th><td><?php echo $q[0]->assigned_to; ?></td></tr>
                <tr><th>Created Date</th><td><?php echo $q[0]->tarik; ?></td></tr>
                <tr><th>Lead Requirement</th><td><?php echo $q[0]->requirement; ?></td></tr>
                <tr><th>Description</th><td colspan="3"><?php echo $q[0]->description; ?></td></tr>
              </tbody>
            </table>
          </div>
        </div>

        <?php } else { ?>

        <div class="crm-card">
          <div class="card-header">
            <span><i class="mdi mdi-table-large"></i> Lead List</span>
            <span style="display:flex; align-items:center; gap:10px;">
              <span class="crm-count-pill"><?php echo $totalRecords; ?> total</span>
            </span>
          </div>

          <div class="card-body">
            <?php if (isset($_SESSION['success'])) { ?>
              <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
              </div>
            <?php } ?>

            <div class="table-responsive">
              <table class="crm-table">
                <thead>
                  <tr>
                    <th>Patient Type</th>
                    <th>Customer Name</th>
                    <th>Mobile No</th>
                    <th>City</th>
                    <th>Requirement</th>
                    <th>Description</th>
                    <th>Source</th>
                    <th>Received Date</th>
                    <th>Follow Up</th>
                    <th>Agent</th>
                    <th>Status</th>
                    <th>Reels Followup</th>
                    <?php if ($this->session->userdata('ROLE') != 'Analyst') { ?>
                      <th>Action</th>
                    <?php } ?>
                  </tr>
                </thead>
                <tbody>

                <?php if ($result && $result->num_rows > 0): ?>

                  <?php while ($row = $result->fetch_assoc()):

                    $type = ($row['patient_type'] == 'Old Patient') ? 'Old' : 'New';

                    $icons = '';
                    if ($row['has_appointment'] == 1) { $icons .= '📅 '; }
                    if ($row['has_prescription'] == 1) { $icons .= '💊'; }

                    $isMissed = (strtolower(trim($row['status'])) === 'missed');
                    $rowClass = $isMissed ? 'row-missed' : '';
                  ?>

                    <tr class="<?php echo $rowClass; ?>">

                      <td data-label="Patient Type">
                        <?php if ($type == "Old"): ?>
                          <span class="type-pill type-old">OLD <?php echo $icons; ?></span>
                        <?php else: ?>
                          <span class="type-pill type-new">NEW</span>
                        <?php endif; ?>
                      </td>

                      <td data-label="Customer Name"><?php echo htmlspecialchars($row['customer_name']); ?></td>

                      <td data-label="Mobile No">
                        <?php
                        $mobile = preg_replace('/\D/', '', $row['mobile_no']);
                        // Admin can see full number
                        if ($loginUser == 'Admin' || 'Sub Admin') {
                            echo htmlspecialchars($row['mobile_no']);
                        } else {
                            // Others see masked number
                            if (strlen($mobile) >= 10) {
                                echo substr($mobile, 0, 6) . 'xxxx';
                            } else {
                                echo htmlspecialchars($row['mobile_no']);
                            }
                        }
                        ?>
                      </td>

                      <td data-label="City"><?php echo htmlspecialchars($row['city']); ?></td>
                      <td data-label="Requirement" class="truncate" title="<?php echo htmlspecialchars($row['requirement']); ?>"><?php echo htmlspecialchars($row['requirement']); ?></td>
                      <td data-label="Description" class="truncate" title="<?php echo htmlspecialchars($row['description']); ?>"><?php echo htmlspecialchars($row['description']); ?></td>
                      <td data-label="Source"><?php echo htmlspecialchars($row['lead_source']); ?></td>

                      <td data-label="Received Date"><?php echo date("d-m-Y h:i A", strtotime($row['created_date'])); ?></td>

                      <td data-label="Follow Up">
                        <?php
                        $date = $row['tarik'];
                        if ($date && $date != '0000-00-00 00:00:00') {
                            $timestamp = strtotime($date);
                            echo $timestamp ? date("d-m-Y h:i A", $timestamp) : "Invalid";
                        } else {
                            echo "N/A";
                        }
                        ?>
                      </td>

                      <td data-label="Agent"><?php echo htmlspecialchars($row['assigned_to']); ?></td>

                      <td data-label="Status">
                        <a class="badge-status <?php echo statusBadgeClass($row['status']); ?>"
                           href="https://team.gautamclinics.com/detail_lead_details?lead_status=<?php echo urlencode($row['status']); ?>">
                          <?php echo htmlspecialchars($row['status']); ?>
                        </a>
                      </td>

                      <td data-label="Reels Followup">
                        <a href="javascript:void(0);"
                           class="reels-btn"
                           onclick="sendReelsFollowup(<?php echo (int)$row['id']; ?>, this)"
                           title="Send Reels Followup">
                          <i class="fa fa-whatsapp"></i> Send
                        </a>
                        <div id="reels_followup_status_<?php echo (int)$row['id']; ?>" class="reels-status"></div>
                      </td>

                      <?php $role = trim($this->session->userdata('ROLE')); ?>

                      <td data-label="Action">
                        <?php if ($role == 'Manager' || $role == 'Sales executive' || $role == 'Sub Admin') { ?>
                          <a class="action-btn action-view"
                             href="https://team.gautamclinics.com/detail_lead_details?view=<?php echo base64_encode($row['id']); ?>"
                             title="View">
                            <i class="fa fa-eye"></i>
                          </a>
                        <?php } ?>

                        <?php if ($role == 'Manager' || $role == 'Sales executive') { ?>
                          <a class="action-btn action-edit"
                             href="https://team.gautamclinics.com/create_lead?update=<?php echo base64_encode($row['id']); ?>"
                             title="Edit">
                            <i class="fa fa-edit"></i>
                          </a>
                        <?php } ?>
                      </td>

                    </tr>

                  <?php endwhile; ?>

                <?php else: ?>

                  <tr>
                    <td colspan="20" class="crm-empty">
                      <i class="fa fa-inbox" style="font-size:22px; display:block; margin-bottom:8px; opacity:.5;"></i>
                      No records found
                    </td>
                  </tr>

                <?php endif; ?>

                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <nav>
              <ul class="crm-pagination">
                <?php
                $range = 2;
                $queryParams = $_GET;

                // PREV
                if ($page > 1) {
                    $queryParams['page'] = $page - 1;
                    echo '<li><a href="?' . http_build_query($queryParams) . '">&laquo; Prev</a></li>';
                }

                // FIRST PAGE
                if ($page > ($range + 1)) {
                    $queryParams['page'] = 1;
                    echo '<li><a href="?' . http_build_query($queryParams) . '">1</a></li>';
                    echo '<li class="disabled"><span>...</span></li>';
                }

                // PAGE NUMBERS
                for ($i = max(1, $page - $range); $i <= min($totalPages, $page + $range); $i++) {
                    $queryParams['page'] = $i;
                    $active = ($i == $page) ? 'class="active"' : '';
                    echo "<li $active><a href=\"?" . http_build_query($queryParams) . "\">$i</a></li>";
                }

                // LAST PAGE
                if ($page < ($totalPages - $range)) {
                    echo '<li class="disabled"><span>...</span></li>';
                    $queryParams['page'] = $totalPages;
                    echo '<li><a href="?' . http_build_query($queryParams) . '">' . $totalPages . '</a></li>';
                }

                // NEXT
                if ($page < $totalPages) {
                    $queryParams['page'] = $page + 1;
                    echo '<li><a href="?' . http_build_query($queryParams) . '">Next &raquo;</a></li>';
                }
                ?>
              </ul>
            </nav>

          </div>
        </div>

        <?php } ?>
      </div>
    </div>

  </div>
</div>