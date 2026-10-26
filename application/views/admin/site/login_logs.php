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
    --crm-danger:#e0483e;
    --crm-danger-bg:#fdecea;
    --crm-radius:14px;
    --crm-radius-sm:8px;
    --crm-shadow:0 2px 10px rgba(30,40,80,0.06);
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
.crm-stats { display:grid; grid-template-columns: repeat(3, 1fr); gap:16px; margin-bottom: 22px; }
.crm-stat-card {
    background: var(--crm-card); border-radius: var(--crm-radius);
    padding: 16px 18px; box-shadow: var(--crm-shadow);
    display:flex; align-items:center; gap:14px; border: 1px solid var(--crm-border);
}
.crm-stat-icon {
    width:44px; height:44px; border-radius: 12px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center; font-size:18px;
}
.crm-stat-total .crm-stat-icon { background: var(--crm-primary-light); color: var(--crm-primary); }
.crm-stat-success .crm-stat-icon { background: var(--crm-success-bg); color: var(--crm-success); }
.crm-stat-failed .crm-stat-icon { background: var(--crm-danger-bg); color: var(--crm-danger); }
.crm-stat-value { font-size: 20px; font-weight:700; line-height:1; }
.crm-stat-label { font-size: 12px; color: var(--crm-text-muted); margin-top:4px; }

/* Filter panel */
.filter-box {
    background: var(--crm-card); border-radius: var(--crm-radius);
    padding: 20px 22px; margin-bottom: 22px; box-shadow: var(--crm-shadow);
    border: 1px solid var(--crm-border);
    display:grid; grid-template-columns: repeat(4, 1fr); gap: 16px 18px; align-items:end;
}
.filter-box .f-group { display:flex; flex-direction:column; gap:6px; }
.filter-box .f-group.f-wide { grid-column: span 2; }
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
    outline:none; border-color: var(--crm-primary); box-shadow: 0 0 0 3px rgba(79,110,247,0.15);
}
.filter-box .select2-container { width:100% !important; }
.filter-box .select2-container .select2-selection--multiple {
    min-height:38px; border-radius: var(--crm-radius-sm); border:1px solid var(--crm-border); background:#fbfcff;
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

/* Card wrapper */
.crm-card {
    background: var(--crm-card); border-radius: var(--crm-radius); box-shadow: var(--crm-shadow);
    border: 1px solid var(--crm-border); overflow:hidden; margin-bottom: 24px;
}
.crm-card .card-header {
    display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;
    padding: 16px 20px; border-bottom: 1px solid var(--crm-border);
    font-weight:700; font-size:15px; background: #fafbff;
}
.crm-card .card-header i { color: var(--crm-primary); margin-right:6px; }
.crm-card .card-body { padding: 20px; }
.crm-count-pill {
    font-size:12px; font-weight:600; background: var(--crm-primary-light); color: var(--crm-primary);
    padding:4px 12px; border-radius:999px;
}

/* Table */
.table-responsive { width:100%; overflow-x:auto; -webkit-overflow-scrolling: touch; }
table.crm-table { width:100%; border-collapse: collapse; min-width: 1200px; }
table.crm-table thead th {
    background: var(--crm-primary-light); color: var(--crm-primary-dark);
    text-transform: uppercase; font-size: 11px; letter-spacing:.04em;
    padding: 12px 14px; text-align:left; border-bottom: 2px solid var(--crm-border);
    white-space:nowrap;
}
table.crm-table tbody td {
    padding: 12px 14px; font-size: 13px; border-bottom: 1px solid var(--crm-border);
    vertical-align: middle; color: var(--crm-text);
}
table.crm-table tbody tr { transition: background .12s ease; }
table.crm-table tbody tr:hover { background: #f7f9ff; }
table.crm-table tbody tr:nth-child(even) { background: #fbfcfe; }
table.crm-table tbody tr:nth-child(even):hover { background: #f2f5ff; }

.badge-status {
    display:inline-block; padding:5px 12px; border-radius:999px; font-size:11px; font-weight:700; letter-spacing:.02em;
}
.badge-success { background: var(--crm-success); color:#fff; }
.badge-danger { background: var(--crm-danger); color:#fff; }

.crm-empty { text-align:center; padding: 40px 20px; color: var(--crm-text-muted); font-size:14px; }

/* Pagination (windowed so it never grows unbounded with many pages) */
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

/* Responsive */
@media (max-width: 1100px) {
    .filter-box { grid-template-columns: repeat(2, 1fr); }
    .filter-box .f-group.f-wide { grid-column: span 2; }
    .crm-stats { grid-template-columns: repeat(1, 1fr); }
}
@media (max-width: 768px) {
    .crm-wrap { padding: 14px; }
    .filter-box { grid-template-columns: 1fr; padding: 16px; }
    .filter-box .f-group.f-wide { grid-column: span 1; }
    .crm-page-header h3 { font-size:18px; }

    table.crm-table { min-width: 0; }
    table.crm-table thead { display:none; }
    table.crm-table, table.crm-table tbody, table.crm-table tr, table.crm-table td { display:block; width:100%; }
    table.crm-table tbody tr {
        margin-bottom: 14px; border: 1px solid var(--crm-border); border-radius: var(--crm-radius-sm);
        padding: 10px 12px; box-shadow: var(--crm-shadow);
    }
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

<div class="content-wrapper">
  <div class="crm-wrap">

    <div class="crm-page-header">
      <h3>
        <span class="icon-badge"><i class="fa fa-users"></i></span>
        CRM Login Logs
      </h3>
      <span style="display:flex; align-items:center; gap:10px;">
        <button type="button" id="filterToggleBtn" class="crm-back-btn crm-filter-toggle" onclick="crmToggleFilters()">
          <i class="fa fa-sliders-h"></i> Filters
        </button>
      </span>
    </div>

    <?php
    $totalLogsCount = !empty($logs) ? count($logs) : 0;
    $successCount = 0;
    $failedCount  = 0;
    if (!empty($logs)) {
        foreach ($logs as $logRow) {
            if ($logRow->status == 'Success') { $successCount++; } else { $failedCount++; }
        }
    }
    ?>

    <!-- Summary strip -->
    <div class="crm-stats">
      <div class="crm-stat-card crm-stat-total">
        <div class="crm-stat-icon"><i class="fa fa-list"></i></div>
        <div>
          <div class="crm-stat-value"><?php echo $totalLogsCount; ?></div>
          <div class="crm-stat-label">Logs on this page</div>
        </div>
      </div>
      <div class="crm-stat-card crm-stat-success">
        <div class="crm-stat-icon"><i class="fa fa-check"></i></div>
        <div>
          <div class="crm-stat-value"><?php echo $successCount; ?></div>
          <div class="crm-stat-label">Successful Logins</div>
        </div>
      </div>
      <div class="crm-stat-card crm-stat-failed">
        <div class="crm-stat-icon"><i class="fa fa-times"></i></div>
        <div>
          <div class="crm-stat-value"><?php echo $failedCount; ?></div>
          <div class="crm-stat-label">Failed Logins</div>
        </div>
      </div>
    </div>

    <?php
    $hasActiveFilter = !empty($this->input->get('from_date')) || !empty($this->input->get('to_date')) || !empty($this->input->get('user_ids'));
    ?>
    <form method="get" class="filter-box" id="filterPanel" style="display:<?php echo $hasActiveFilter ? 'grid' : 'none'; ?>;">

        <div class="f-group">
            <label>From Date</label>
            <input type="date" name="from_date" value="<?php echo $this->input->get('from_date'); ?>">
        </div>

        <div class="f-group">
            <label>To Date</label>
            <input type="date" name="to_date" value="<?php echo $this->input->get('to_date'); ?>">
        </div>

        <div class="f-group f-wide">
            <label>Select Employee</label>
            <select name="user_ids[]" multiple class="select2">
                <?php
                $selected_users = $this->input->get('user_ids');
                foreach ($employees as $emp) {
                ?>
                    <option value="<?php echo $emp->id; ?>"
                        <?php if (!empty($selected_users) && in_array($emp->id, $selected_users)) { echo "selected"; } ?>>
                        <?php echo $emp->full_name; ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="f-actions">
            <a href="<?php echo base_url('login-logs'); ?>" class="crm-back-btn f-reset">Reset</a>
            <button type="submit"><i class="fa fa-search"></i> Filter</button>
        </div>
    </form>

    <div class="crm-card">
      <div class="card-header">
        <span><i class="fa fa-table"></i> Login Activity</span>
        <span class="crm-count-pill"><?php echo $totalLogsCount; ?> shown</span>
      </div>
      <div class="card-body">

        <div class="table-responsive">
          <table id="loginTable" class="crm-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Employee</th>
                <th>Email</th>
                <th>IP Address</th>
                <th>Device</th>
                <th>Browser</th>
                <th>Platform</th>
                <th>Location</th>
                <th>Login Time</th>
                <th>Logout Time</th>
                <th>Session Duration</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>

              <?php if (!empty($logs)) { foreach ($logs as $row) { ?>
                <tr>
                  <td data-label="ID"><?php echo $row->id; ?></td>
                  <td data-label="Employee"><?php echo $row->full_name; ?></td>
                  <td data-label="Email"><?php echo $row->email; ?></td>
                  <td data-label="IP Address"><?php echo $row->ip_address; ?></td>
                  <td data-label="Device"><?php echo $row->device; ?></td>
                  <td data-label="Browser"><?php echo $row->browser; ?></td>
                  <td data-label="Platform"><?php echo $row->platform; ?></td>
                  <td data-label="Location"><?php echo $row->location; ?></td>
                  <td data-label="Login Time"><?php echo $row->login_time; ?></td>
                  <td data-label="Logout Time"><?php echo $row->logout_time; ?></td>
                  <td data-label="Session Duration"><?php echo $row->session_duration; ?></td>
                  <td data-label="Status">
                    <?php if ($row->status == 'Success') { ?>
                      <span class="badge-status badge-success">Success</span>
                    <?php } else { ?>
                      <span class="badge-status badge-danger">Failed</span>
                    <?php } ?>
                  </td>
                </tr>
              <?php } } else { ?>
                <tr>
                  <td colspan="12" class="crm-empty">
                    <i class="fa fa-inbox" style="font-size:22px; display:block; margin-bottom:8px; opacity:.5;"></i>
                    No login logs found
                  </td>
                </tr>
              <?php } ?>

            </tbody>
          </table>
        </div>

        <!-- Pagination: windowed so it stays short even with many pages -->
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

            // PAGE NUMBERS (only the ones near the current page)
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

  </div>
</div>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

<script>
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
document.addEventListener('DOMContentLoaded', function () {
    var panel = document.getElementById('filterPanel');
    var btn = document.getElementById('filterToggleBtn');
    if (panel && panel.style.display !== 'none') {
        btn.classList.add('active');
    }
});

$(document).ready(function(){

    $('.select2').select2({
        placeholder: 'Select Employee',
        width: '100%'
    });

    $('#loginTable').DataTable({
        pageLength: 25,
        order: [[0, 'desc']],
        dom: 'Bfrtip',
        buttons: [
            'excel',
            'csv',
            'pdf',
            'print'
        ]
    });

});
</script>