<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
    --crm-bad-bg:#fdeceb;
    --crm-bad-border:#e0483e;
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
.crm-stats { display:grid; grid-template-columns: repeat(4, 1fr); gap:16px; margin-bottom: 22px; }
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
.crm-stat-good .crm-stat-icon { background: var(--crm-success-bg); color: var(--crm-success); }
.crm-stat-bad .crm-stat-icon { background: var(--crm-bad-bg); color: var(--crm-bad-border); }
.crm-stat-training .crm-stat-icon { background: var(--crm-warning-bg); color: var(--crm-warning); }
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
table.crm-table { width:100%; border-collapse: collapse; min-width: 1150px; }
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

/* Bad-flag row highlight */
table.crm-table tbody tr.row-flag-bad {
    background: var(--crm-bad-bg) !important;
    box-shadow: inset 3px 0 0 var(--crm-bad-border);
}
table.crm-table tbody tr.row-flag-bad:hover { background: #fbdedb !important; }

.crm-feedback-input {
    width:100%; min-width:160px; padding:8px 10px; border-radius: var(--crm-radius-sm);
    border:1px solid var(--crm-border); background:#fbfcff; font-size:12px; resize:vertical;
}
.crm-feedback-input:focus { outline:none; border-color: var(--crm-primary); box-shadow: 0 0 0 3px rgba(79,110,247,0.15); }

.crm-flag-select {
    width:100%; min-width:120px; padding:8px 10px; border-radius: var(--crm-radius-sm);
    border:1px solid var(--crm-border); background:#fbfcff; font-size:12px; font-weight:600;
}
.crm-flag-select.flag-good { color: var(--crm-success); border-color: var(--crm-success-bg); background: var(--crm-success-bg); }
.crm-flag-select.flag-bad { color: var(--crm-bad-border); border-color: var(--crm-bad-bg); background: var(--crm-bad-bg); }
.crm-flag-select.flag-training { color: var(--crm-warning); border-color: var(--crm-warning-bg); background: var(--crm-warning-bg); }

.crm-save-indicator { font-size:11px; margin-top:4px; min-height:14px; }
.crm-save-indicator.saved { color: var(--crm-success); }
.crm-save-indicator.saving { color: var(--crm-text-muted); }
.crm-save-indicator.error { color: var(--crm-danger); }

audio.crm-audio { width:170px; height:32px; }

.crm-empty { text-align:center; padding: 40px 20px; color: var(--crm-text-muted); font-size:14px; }

/* Pagination (windowed so it stays short even with many pages) */
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
    .crm-stats { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
    .crm-wrap { padding: 14px; }
    .filter-box { grid-template-columns: 1fr; padding: 16px; }
    .crm-stats { grid-template-columns: repeat(2, 1fr); gap:10px; }
    .crm-page-header h3 { font-size:18px; }

    table.crm-table { min-width: 0; }
    table.crm-table thead { display:none; }
    table.crm-table, table.crm-table tbody, table.crm-table tr, table.crm-table td { display:block; width:100%; }
    table.crm-table tbody tr {
        margin-bottom: 14px; border: 1px solid var(--crm-border); border-radius: var(--crm-radius-sm);
        padding: 10px 12px; box-shadow: var(--crm-shadow);
    }
    table.crm-table tbody tr.row-flag-bad { box-shadow: 0 0 0 1px var(--crm-bad-border), var(--crm-shadow); }
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
        <span class="icon-badge"><i class="fa fa-headset"></i></span>
        Call Quality Review
      </h3>
      <span style="display:flex; align-items:center; gap:10px;">
        <button type="button" id="filterToggleBtn" class="crm-back-btn crm-filter-toggle" onclick="crmToggleFilters()">
          <i class="fa fa-sliders-h"></i> Filters
        </button>
      </span>
    </div>

    <?php
    $goodCount = 0; $badCount = 0; $trainingCount = 0;
    if (!empty($calls)) {
        foreach ($calls as $c) {
            if ($c->tl_flag == 'good') { $goodCount++; }
            elseif ($c->tl_flag == 'bad') { $badCount++; }
            elseif ($c->tl_flag == 'training') { $trainingCount++; }
        }
    }
    ?>

    <!-- Summary strip -->
    <div class="crm-stats">
      <div class="crm-stat-card crm-stat-total">
        <div class="crm-stat-icon"><i class="fa fa-list"></i></div>
        <div>
          <div class="crm-stat-value"><?php echo $total; ?></div>
          <div class="crm-stat-label">Total Records</div>
        </div>
      </div>
      <div class="crm-stat-card crm-stat-good">
        <div class="crm-stat-icon"><i class="fa fa-thumbs-up"></i></div>
        <div>
          <div class="crm-stat-value"><?php echo $goodCount; ?></div>
          <div class="crm-stat-label">Good (this page)</div>
        </div>
      </div>
      <div class="crm-stat-card crm-stat-bad">
        <div class="crm-stat-icon"><i class="fa fa-thumbs-down"></i></div>
        <div>
          <div class="crm-stat-value"><?php echo $badCount; ?></div>
          <div class="crm-stat-label">Bad (this page)</div>
        </div>
      </div>
      <div class="crm-stat-card crm-stat-training">
        <div class="crm-stat-icon"><i class="fa fa-graduation-cap"></i></div>
        <div>
          <div class="crm-stat-value"><?php echo $trainingCount; ?></div>
          <div class="crm-stat-label">Training (this page)</div>
        </div>
      </div>
    </div>

    <!-- Filters (hidden by default; toggled via the Filters button above) -->
    <?php
    $hasActiveFilter = !empty($this->input->get('from_date')) || !empty($this->input->get('to_date'))
        || !empty($this->input->get('mobile')) || !empty($this->input->get('flag'));
    ?>
    <form method="GET" class="filter-box" id="filterPanel" style="display:<?php echo $hasActiveFilter ? 'grid' : 'none'; ?>;">

        <div class="f-group">
            <label>From Date</label>
            <input type="date" name="from_date" value="<?php echo $this->input->get('from_date'); ?>">
        </div>

        <div class="f-group">
            <label>To Date</label>
            <input type="date" name="to_date" value="<?php echo $this->input->get('to_date'); ?>">
        </div>

        <div class="f-group">
            <label>Mobile</label>
            <input type="text" name="mobile" placeholder="Search mobile" value="<?php echo $this->input->get('mobile'); ?>">
        </div>

        <div class="f-group">
            <label>Flag</label>
            <select name="flag">
                <option value="">All Flags</option>
                <option value="good" <?php if ($this->input->get('flag') == 'good') echo 'selected'; ?>>Good</option>
                <option value="bad" <?php if ($this->input->get('flag') == 'bad') echo 'selected'; ?>>Bad</option>
                <option value="training" <?php if ($this->input->get('flag') == 'training') echo 'selected'; ?>>Training</option>
            </select>
        </div>

        <div class="f-actions">
            <a href="?" class="crm-back-btn f-reset">Reset</a>
            <button type="submit"><i class="fa fa-search"></i> Filter</button>
        </div>
    </form>

    <div class="crm-card">
      <div class="card-header">
        <span><i class="fa fa-phone"></i> Call Log</span>
        <span class="crm-count-pill"><?php echo $total; ?> total</span>
      </div>
      <div class="card-body">

        <div class="table-responsive">
          <table class="crm-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Agent</th>
                <th>Duration</th>
                <th>Recording</th>
                <th>Feedback</th>
                <th>Flag</th>
              </tr>
            </thead>

            <tbody>

              <?php if (!empty($calls)) {
                  $i = ($page_no - 1) * $limit + 1;

                  foreach ($calls as $row):
                      $rowClass = ($row->tl_flag == 'bad') ? 'row-flag-bad' : '';
                  ?>

                <tr class="<?php echo $rowClass; ?>">

                  <td data-label="#"><?php echo $i++; ?></td>

                  <td data-label="Date">
                    <?php
                    echo !empty($row->created_at)
                        ? date("d-m-Y h:i A", strtotime($row->created_at . ' UTC'))
                        : '-';
                    ?>
                  </td>

                  <td data-label="Customer">
                    <?php
                    $role = trim($this->session->userdata('ROLE'));
                    $allowedRoles = ['Manager', 'Sub Admin'];

                    if (in_array($role, $allowedRoles)) {
                        // Show full number (digits only)
                        echo preg_replace('/\D/', '', $row->caller_id_number);
                    } else {
                        // Show first 6 digits and mask the rest
                        echo substr(
                            preg_replace('/\D/', '', $row->caller_id_number),
                            0,
                            6
                        ) . 'XXXX';
                    }
                    ?>
                  </td>

                  <td data-label="Agent"><?php echo htmlspecialchars($row->answered_agent_name); ?></td>

                  <td data-label="Duration"><?php echo $row->duration ? $row->duration . ' sec' : '-'; ?></td>

                  <td data-label="Recording">
                    <?php if (!empty($row->recording_url)) { ?>
                      <audio controls class="crm-audio">
                        <source src="<?php echo $row->recording_url; ?>">
                      </audio>
                    <?php } else { ?>
                      <span class="text-muted">No File</span>
                    <?php } ?>
                  </td>

                  <td data-label="Feedback">
                    <textarea class="crm-feedback-input feedback" data-id="<?php echo $row->id; ?>" rows="2"><?php echo htmlspecialchars($row->tl_feedback); ?></textarea>
                    <div class="crm-save-indicator" data-indicator-for="<?php echo $row->id; ?>"></div>
                  </td>

                  <td data-label="Flag">
                    <select class="crm-flag-select flag flag-<?php echo htmlspecialchars($row->tl_flag); ?>" data-id="<?php echo $row->id; ?>">
                        <option value="none" <?php if ($row->tl_flag == 'none') echo 'selected'; ?>>None</option>
                        <option value="good" <?php if ($row->tl_flag == 'good') echo 'selected'; ?>>Good</option>
                        <option value="bad" <?php if ($row->tl_flag == 'bad') echo 'selected'; ?>>Bad</option>
                        <option value="training" <?php if ($row->tl_flag == 'training') echo 'selected'; ?>>Training</option>
                    </select>
                  </td>

                </tr>

              <?php endforeach; } else { ?>

                <tr>
                  <td colspan="8" class="crm-empty">
                    <i class="fa fa-inbox" style="font-size:22px; display:block; margin-bottom:8px; opacity:.5;"></i>
                    No records found
                  </td>
                </tr>

              <?php } ?>

            </tbody>
          </table>
        </div>

        <!-- Pagination: windowed so it stays short even with many pages -->
        <?php
        $total_pages = ceil($total / $limit);
        if ($total_pages > 1):
        ?>
        <nav>
          <ul class="crm-pagination">
            <?php
            $range = 2;
            $queryParams = $_GET;

            // PREV
            if ($page_no > 1) {
                $queryParams['page'] = $page_no - 1;
                echo '<li><a href="?' . http_build_query($queryParams) . '">&laquo; Prev</a></li>';
            }

            // FIRST PAGE
            if ($page_no > ($range + 1)) {
                $queryParams['page'] = 1;
                echo '<li><a href="?' . http_build_query($queryParams) . '">1</a></li>';
                echo '<li class="disabled"><span>...</span></li>';
            }

            // PAGE NUMBERS (only the ones near the current page)
            for ($i = max(1, $page_no - $range); $i <= min($total_pages, $page_no + $range); $i++) {
                $queryParams['page'] = $i;
                $active = ($i == $page_no) ? 'class="active"' : '';
                echo "<li $active><a href=\"?" . http_build_query($queryParams) . "\">$i</a></li>";
            }

            // LAST PAGE
            if ($page_no < ($total_pages - $range)) {
                echo '<li class="disabled"><span>...</span></li>';
                $queryParams['page'] = $total_pages;
                echo '<li><a href="?' . http_build_query($queryParams) . '">' . $total_pages . '</a></li>';
            }

            // NEXT
            if ($page_no < $total_pages) {
                $queryParams['page'] = $page_no + 1;
                echo '<li><a href="?' . http_build_query($queryParams) . '">Next &raquo;</a></li>';
            }
            ?>
          </ul>
        </nav>
        <?php endif; ?>

      </div>
    </div>

  </div>
</div>

<!-- ⚡ AJAX SAVE -->
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

$(document).on('change', '.feedback, .flag', function () {

    var row = $(this).closest('tr');

    var id = row.find('.feedback').data('id');
    var feedback = row.find('.feedback').val();
    var flagSelect = row.find('.flag');
    var flag = flagSelect.val();
    var indicator = row.find('[data-indicator-for="' + id + '"]');

    // Re-color the flag select to match the chosen value
    flagSelect.removeClass('flag-none flag-good flag-bad flag-training').addClass('flag-' + flag);

    // Toggle the bad-call row highlight live, without a full reload
    row.toggleClass('row-flag-bad', flag === 'bad');

    indicator.removeClass('saved error').addClass('saving').text('Saving...');

    $.post("<?php echo base_url('Api_Controller/update_call_feedback'); ?>", {
        id: id,
        feedback: feedback,
        flag: flag
    }, function (res) {
        indicator.removeClass('saving error').addClass('saved').text('Saved');
        setTimeout(function () { indicator.text(''); }, 2000);
        console.log(res);
    }).fail(function () {
        indicator.removeClass('saving saved').addClass('error').text('Failed to save');
    });

});
</script>