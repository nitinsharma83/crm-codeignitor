<?php
if (!function_exists('clean_mobile')) {
    function clean_mobile($number){
        return substr(preg_replace('/\D/', '', $number), -10);
    }
}

if (!function_exists('hide_mobile')) {
    function hide_mobile($number){

        $number = clean_mobile($number);

        if(strlen($number) < 4){
            return $number;
        }

        // Show first 4 digits only
        return substr($number,0,4).'xxxxxx';
    }
}

if (!function_exists('lead_field')) {
    // Safe getter: returns fallback text if the property is missing/empty
    // instead of throwing a notice. Use this anywhere a DB column may not exist.
    function lead_field($obj, $prop, $fallback = 'No Data Available'){
        return (isset($obj->$prop) && $obj->$prop !== '' && $obj->$prop !== null) ? $obj->$prop : $fallback;
    }
}

if (!function_exists('time_ago')) {
    function time_ago($datetime){
        if(empty($datetime)) return '-';
        $diff = time() - strtotime($datetime);
        if($diff < 0) $diff = 0;
        if($diff < 60) return 'Just now';
        if($diff < 3600) return floor($diff/60).' min ago';
        if($diff < 86400) { $h = floor($diff/3600); return $h.' hr'.($h>1?'s':'').' ago'; }
        $d = floor($diff/86400);
        return $d.' day'.($d>1?'s':'').' ago';
    }
}

if (!function_exists('getStatusMeta')) {
    function getStatusMeta($status){
        $map = [
            "Contacted" => ["color"=>"#28a745", "icon"=>"fa-phone"],
            "Not Contacted" => ["color"=>"#6c757d", "icon"=>"fa-user-slash"],
            "Ringing" => ["color"=>"#17a2b8", "icon"=>"fa-bell"],
            "Follow Up" => ["color"=>"#ffc107", "icon"=>"fa-clock"],
            "Missed" => ["color"=>"#fd7e14", "icon"=>"fa-phone-volume"],
            "Invalid" => ["color"=>"#dc3545", "icon"=>"fa-times"],
            "Appointment Done" => ["color"=>"#28a745", "icon"=>"fa-check"],
            "Not Interested" => ["color"=>"#343a40", "icon"=>"fa-thumbs-down"],
            "Switch Off" => ["color"=>"#6f42c1", "icon"=>"fa-power-off"],
            "Appointment Pending" => ["color"=>"#007bff", "icon"=>"fa-calendar"]
        ];
        return isset($map[$status]) ? $map[$status] : ["color"=>"#999", "icon"=>"fa-info"];
    }
}
?>
<style>

.edit-icon-btn{

    width:32px;
    height:32px;

    border:none;
    border-radius:6px;

    background:#0d6efd;
    color:#fff;

    display:flex;
    align-items:center;
    justify-content:center;

    cursor:pointer;

    transition:0.2s;
}

.edit-icon-btn:hover{

    background:#0b5ed7;
    transform:scale(1.05);
}

.edit-icon-btn i{

    font-size:13px;
}
.call-timeline {
    position: relative;
    padding: 20px 0;
}

.call-timeline::before {
    content: '';
    position: absolute;
    left: 50%;
    width: 3px;
    height: 100%;
    background: #ddd;
    transform: translateX(-50%);
}

/* STEP */
.timeline-step {
    text-align: center;
    margin-bottom: 40px;
    position: relative;
}

/* 🔺 TOP ARROW */
.timeline-step::before {
    content: '\f062'; /* FontAwesome up arrow */
    font-family: FontAwesome;
    position: absolute;
    top: -25px;
    left: 50%;
    transform: translateX(-50%);
    color: #999;
    font-size: 14px;
}

/* ❌ remove arrow from first item */
.timeline-step:first-child::before {
    display: none;
}

/* ICON */
.timeline-icon {
    width: 55px;
    height: 55px;
    border-radius: 50%;
    color: #fff;
    line-height: 55px;
    font-size: 20px;
    margin: 0 auto;
    position: relative;
    z-index: 2;
}

/* TEXT */
.timeline-text {
    margin-top: 10px;
    font-size: 14px;
}
.crm-flag-good { background:#d4edda; }
.crm-flag-bad { background:#f8d7da; }
.crm-flag-training { background:#fff3cd; }
.crm-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    border: none;
    transition: all 0.3s ease;
    cursor: pointer;
}

/* Primary Brand Button */
.crm-btn-primary {
    background: linear-gradient(135deg, #723421, #8a4a2a);
    color: #fff;
    box-shadow: 0 4px 10px rgba(114, 52, 33, 0.3);
}

.crm-btn-primary:hover {
    background: linear-gradient(135deg, #8a4a2a, #723421);
    transform: translateY(-2px);
}

/* Secondary Button */
.crm-btn-light {
    background: #f5f5f5;
    color: #723421;
    border: 1px solid #723421;
}

.crm-btn-light:hover {
    background: #723421;
    color: #fff;
    transform: translateY(-2px);
}

/* Modal Header Theme */
.crm-modal-header {
    background: linear-gradient(135deg, #723421, #8a4a2a);
    color: #fff;
    border-bottom: none;
}

.crm-modal-header .close {
    color: #fff;
    opacity: 1;
}

/* Modal Footer */
.crm-modal-footer {
    border-top: none;
}

/* Table Header */
.crm-table thead {
    background: #f8f3f1;
    color: #723421;
}

/* Badge Styling */
.crm-badge-inbound {
    background: #28a745;
    color: #fff;
}

.crm-badge-outbound {
    background: #723421;
    color: #fff;
}

/* Button Theme */
.crm-btn-primary {
    background: linear-gradient(135deg, #723421, #8a4a2a);
    color: #fff;
    border: none;
}

.crm-btn-primary:hover {
    background: linear-gradient(135deg, #8a4a2a, #723421);
}

/* Close Button */
.crm-btn-close {
    background: #eee;
    border: none;
    color: #333;
}

.crm-btn-close:hover {
    background: #723421;
    color: #fff;
}
</style>

<!-- ============================================================
     LEAD DETAILS v2 — matches reference design
     ============================================================ -->
<style>
:root{
    --lead-orange:#723421;
    --lead-orange-dark:#5a2919;
    --lead-green:#3a8a52;
    --lead-green-light:#e9f3ea;
    --lead-purple:#a8623f;
    --lead-purple-light:#f4e9e2;
    --lead-blue:#8a4a2a;
    --lead-blue-light:#f8f0ec;
    --lead-red:#b23b3b;
    --lead-red-light:#fbeaea;
    --lead-yellow:#c98a2e;
    --lead-yellow-light:#faf1e2;
    --lead-ink:#22242a;
    --lead-sub:#8b8f98;
    --lead-border:#eee6e2;
    --lead-bg:#f8f3f1;
}

.lead-details-wrap{ background:var(--lead-bg); margin:-24px -24px 0; padding:20px 24px 24px; }
.lead-details-wrap *{ box-sizing:border-box; }
.lead-details-wrap .card{ border:1px solid var(--lead-border); box-shadow:0 2px 10px rgba(20,20,43,.04); border-radius:14px; }

.lead-breadcrumb{ font-size:13px; color:var(--lead-sub); margin-bottom:4px; }
.lead-breadcrumb a{ color:var(--lead-sub); text-decoration:none; }

.lead-title-row{ display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:16px; }
.lead-page-title{ font-size:26px; font-weight:800; color:var(--lead-ink); margin:0; display:flex; align-items:center; gap:10px; }
.lead-id-badge{ font-size:12px; font-weight:600; background:#eceef2; color:#666; padding:4px 10px; border-radius:20px; vertical-align:middle; }

.lead-quick-actions{ display:flex; gap:10px; flex-wrap:wrap; }
.qa-btn{ display:inline-flex; align-items:center; gap:7px; border:none; border-radius:10px; padding:10px 16px; font-size:13px; font-weight:600; cursor:pointer; text-decoration:none; }
.qa-call{ background:var(--lead-green); color:#fff; }
.qa-call:hover{ background:#2e6f41; color:#fff; }
.qa-whatsapp{ background:#0fae66; color:#fff; }
.qa-whatsapp:hover{ background:#0c9457; color:#fff; }
.qa-appt{ background:var(--lead-purple); color:#fff; }
.qa-appt:hover{ background:#8a4e32; color:#fff; }
.qa-more{ background:#fff; color:#444; border:1px solid #e1e3e8 !important; }
.qa-more:hover{ background:#f4f4f6; }

/* ---- hero panel ---- */
.lead-hero-panel{ background:#fff; border:1px solid var(--lead-border); border-radius:14px; padding:22px 24px; display:flex; align-items:center; gap:24px; flex-wrap:wrap; box-shadow:0 2px 10px rgba(20,20,43,.04); }
.lead-hero-left{ display:flex; align-items:center; gap:16px; flex:1 1 320px; }
.lead-avatar{ width:64px; height:64px; border-radius:50%; background:linear-gradient(135deg,var(--lead-orange),var(--lead-orange-dark)); color:#fff; font-weight:700; font-size:22px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.lead-name-row{ display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.lead-name-input{ border:none; background:transparent; font-size:20px; font-weight:700; color:var(--lead-ink); padding:2px 4px; border-radius:6px; max-width:260px; }
.lead-name-input:focus{ outline:none; background:#f5f6fa; }
.verified-badge{ background:var(--lead-green-light); color:var(--lead-green); font-size:11px; font-weight:700; padding:4px 10px; border-radius:20px; }
.lead-meta-row{ display:flex; gap:14px; margin-top:6px; font-size:13px; color:var(--lead-sub); flex-wrap:wrap; }
.lead-meta-row i{ margin-right:4px; }

.lead-hero-mid{ background:var(--lead-bg); border-radius:12px; padding:12px 18px; flex:0 0 auto; min-width:210px; }
.mid-line{ font-size:18px; font-weight:700; color:var(--lead-ink); }
.mid-line i{ color:var(--lead-sub); margin-right:8px; }
.mid-line-sub{ font-size:12px; color:var(--lead-sub); margin-top:4px; }
.mid-line-sub i{ margin-right:6px; }

.lead-hero-right{ text-align:center; flex:0 0 auto; }
.score-label{ font-size:12px; color:var(--lead-sub); margin-bottom:6px; }
.lead-score-ring{ width:78px; height:78px; border-radius:50%; background:conic-gradient(var(--lead-green) calc(var(--score)*1%), #e9ecef 0); display:flex; align-items:center; justify-content:center; margin:0 auto; }
.lead-score-inner{ width:62px; height:62px; border-radius:50%; background:#fff; display:flex; align-items:center; justify-content:center; }
.score-val{ font-weight:800; font-size:16px; color:var(--lead-ink); }
.hot-lead-label{ margin-top:8px; font-size:12px; font-weight:700; }
.priority-text-hot{ color:var(--lead-red); }
.priority-text-warm{ color:var(--lead-yellow); }
.priority-text-cold{ color:var(--lead-blue); }
.priority-text-dead{ color:var(--lead-sub); }

/* ---- quick-glance stat strip ---- */
.lead-stat-strip{ display:grid; grid-template-columns:repeat(6,1fr); gap:12px; margin:14px 0 18px; }
.stat-chip{ background:#fff; border:1px solid var(--lead-border); border-radius:12px; padding:12px 14px; box-shadow:0 2px 10px rgba(20,20,43,.04); }
.stat-chip .sc-label{ font-size:11px; color:var(--lead-sub); font-weight:700; text-transform:uppercase; letter-spacing:.4px; display:flex; align-items:center; gap:6px; margin-bottom:4px; }
.stat-chip .sc-value{ font-size:14.5px; font-weight:800; color:var(--lead-ink); line-height:1.25; }
.stat-chip .sc-sub{ font-size:11px; color:var(--lead-sub); margin-top:2px; }
.stat-chip.warn{ background:var(--lead-yellow-light); border-color:#f1dfb4; }
.stat-chip.danger{ background:var(--lead-red-light); border-color:#f0c6c6; }
.stat-chip.good{ background:var(--lead-green-light); border-color:#c9e6cf; }
.stat-chip .sc-value.c-danger{ color:var(--lead-red); }
.stat-chip .sc-value.c-warn{ color:var(--lead-orange-dark); }
.stat-chip .sc-value.c-good{ color:var(--lead-green); }

/* ---- tabs ---- */
.lead-tabs{ border-bottom:1px solid var(--lead-border); margin-top:18px; }
.lead-tabs .nav-link{ border:none; color:var(--lead-sub); font-weight:600; font-size:14px; padding:10px 16px; }
.lead-tabs .nav-link i{ margin-right:6px; }
.lead-tabs .nav-link.active{ color:var(--lead-orange-dark); border-bottom:2px solid var(--lead-orange-dark); background:transparent; }

/* ---- info card rows ---- */
.info-card .card-header{ font-weight:700; font-size:14px; border-bottom:1px solid var(--lead-border); background:#fff; }
.info-row{ display:flex; align-items:flex-start; justify-content:space-between; gap:10px; padding:13px 20px; border-bottom:1px solid var(--lead-border); }
.info-row:last-child{ border-bottom:none; }
.info-row .info-label{ display:flex; align-items:flex-start; gap:10px; color:var(--lead-sub); font-size:13px; min-width:150px; }
.info-row .info-label i{ margin-top:2px; width:14px; text-align:center; }
.info-row .info-value{ text-align:right; font-size:13.5px; color:var(--lead-ink); font-weight:600; flex:1; }
.info-row .info-value.text-left{ text-align:left; }
.info-row .info-actions{ display:flex; align-items:center; gap:8px; }
.pill{ display:inline-block; padding:3px 12px; border-radius:20px; font-size:12px; font-weight:700; }
.pill-blue{ background:var(--lead-blue-light); color:var(--lead-blue); }
.pill-green{ background:var(--lead-green-light); color:var(--lead-green); }
.pill-red{ background:var(--lead-red-light); color:var(--lead-red); }
.pill-gray{ background:#eef0f4; color:#666; }
.mini-icon-btn{ width:28px; height:28px; border-radius:8px; border:none; background:#f2f3f6; color:#666; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; }
.mini-icon-btn:hover{ background:#e5e7eb; }

/* ---- upcoming appointment ---- */
.appt-card .card-header{ font-weight:700; font-size:14px; background:#fff; border-bottom:1px solid var(--lead-border); }
.appt-body{ display:flex; gap:16px; padding:18px 20px 8px; align-items:center; }
.appt-date-box{ background:var(--lead-yellow-light); border-radius:12px; width:64px; height:64px; flex-shrink:0; display:flex; flex-direction:column; align-items:center; justify-content:center; }
.appt-date-box .dow{ font-size:10px; font-weight:700; color:var(--lead-orange-dark); text-transform:uppercase; }
.appt-date-box .dom{ font-size:20px; font-weight:800; color:var(--lead-ink); line-height:1; }
.appt-date-box .mon{ font-size:10px; font-weight:700; color:var(--lead-orange-dark); text-transform:uppercase; }
.appt-time{ font-size:16px; font-weight:700; color:var(--lead-ink); }
.appt-doc{ font-size:13px; color:var(--lead-sub); margin-top:2px; }
.appt-actions{ padding:14px 20px 18px; display:flex; gap:8px; flex-wrap:wrap; }
.appt-btn{ border-radius:8px; padding:7px 14px; font-size:12.5px; font-weight:600; border:1px solid #e1e3e8; background:#fff; color:#555; cursor:pointer; text-decoration:none; }
.appt-btn.danger{ color:var(--lead-red); border-color:#f4c9c9; }
.appt-btn.link{ border:none; color:var(--lead-blue); background:transparent; padding-left:4px; }
.appt-empty{ padding:22px; text-align:center; color:var(--lead-sub); font-size:13px; }

/* ---- AI recommendations ---- */
.ai-card .card-header{ font-weight:700; font-size:14px; background:#fff; border-bottom:1px solid var(--lead-border); }
.ai-card .card-header i{ color:var(--lead-purple); margin-right:6px; }
.ai-list{ list-style:none; margin:0; padding:14px 20px 6px; }
.ai-list li{ display:flex; gap:8px; font-size:13px; color:var(--lead-ink); margin-bottom:10px; }
.ai-list li i{ color:var(--lead-green); margin-top:3px; }
.ai-conv{ display:flex; justify-content:space-between; align-items:center; padding:12px 20px 18px; border-top:1px solid var(--lead-border); margin-top:4px; }
.ai-conv .lbl{ font-size:13px; color:var(--lead-sub); font-weight:600; }
.ai-conv .val{ font-size:20px; font-weight:800; color:var(--lead-green); }

/* ---- tags ---- */
.tags-card .card-header{ font-weight:700; font-size:14px; background:#fff; border-bottom:1px solid var(--lead-border); }
.tags-wrap{ padding:14px 20px 18px; display:flex; flex-wrap:wrap; gap:8px; align-items:center; }
.tag-chip{ padding:5px 13px; border-radius:20px; font-size:12px; font-weight:700; display:inline-flex; align-items:center; }
.tag-chip.blue{ background:var(--lead-blue-light); color:var(--lead-blue); }
.tag-chip.green{ background:var(--lead-green-light); color:var(--lead-green); }
.tag-chip.red{ background:var(--lead-red-light); color:var(--lead-red); }
.tag-chip .remove-tag{ margin-left:6px; color:inherit; opacity:.6; text-decoration:none; font-weight:700; }
.tag-chip .remove-tag:hover{ opacity:1; }
.tag-add-select{ border:1px dashed #d0d3da; background:transparent; color:var(--lead-sub); border-radius:20px; padding:5px 13px; font-size:12px; font-weight:600; }

/* ---- activity timeline ---- */
.activity-card .card-header{ display:flex; justify-content:space-between; align-items:center; font-weight:700; font-size:14px; background:#fff; border-bottom:1px solid var(--lead-border); }
.activity-card .card-header .filter-link{ font-size:12px; font-weight:600; color:var(--lead-sub); cursor:pointer; }
.activity-group-label{ font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:var(--lead-sub); font-weight:700; padding:14px 20px 4px; }
.activity-item{ display:flex; gap:12px; padding:10px 20px; }
.activity-dot{ width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-size:13px; flex-shrink:0; }
.activity-body{ flex:1; display:flex; justify-content:space-between; gap:8px; }
.activity-title{ font-size:13px; font-weight:700; color:var(--lead-ink); }
.activity-desc{ font-size:12.5px; color:var(--lead-sub); margin-top:1px; }
.activity-time{ font-size:11px; color:var(--lead-sub); white-space:nowrap; }
.activity-view-all{ text-align:right; padding:10px 20px 16px; }
.activity-view-all a{ font-size:12.5px; font-weight:700; color:var(--lead-blue); text-decoration:none; }

/* ---- notes panel ---- */
.notes-card .card-header{ padding:0; border-bottom:1px solid var(--lead-border); background:#fff; }
.notes-tabs{ display:flex; flex-wrap:wrap; }
.notes-tabs .n-tab{ padding:14px 18px; font-size:13px; font-weight:600; color:var(--lead-sub); border-bottom:2px solid transparent; cursor:pointer; }
.notes-tabs .n-tab.active{ color:var(--lead-ink); border-bottom-color:var(--lead-ink); }
.note-editor textarea.note-input{ border:none; resize:none; min-height:60px; font-size:13.5px; }
.note-editor textarea.note-input:focus{ outline:none; box-shadow:none; }
.note-toolbar{ display:flex; justify-content:space-between; align-items:center; padding:10px 16px 14px; }
.note-toolbar .fmt-icons{ display:flex; gap:14px; color:#9a9da4; font-size:14px; }
.note-toolbar .fmt-icons i{ cursor:default; }

/* ---- lead summary ---- */
.summary-card .card-header{ font-weight:700; font-size:14px; background:#fff; border-bottom:1px solid var(--lead-border); }
.summary-stats{ display:flex; padding:16px 10px; }
.summary-stat{ flex:1; text-align:center; }
.summary-stat i{ font-size:16px; margin-bottom:6px; display:block; }
.summary-stat .num{ font-size:18px; font-weight:800; color:var(--lead-ink); }
.summary-stat .lbl{ font-size:11px; color:var(--lead-sub); }
.summary-foot{ display:flex; justify-content:space-between; align-items:center; padding:12px 20px 16px; border-top:1px solid var(--lead-border); font-size:12.5px; }
.summary-foot .owner{ display:flex; align-items:center; gap:8px; font-weight:700; color:var(--lead-ink); }
.summary-foot .owner .dot{ width:26px; height:26px; border-radius:50%; background:var(--lead-orange); color:#fff; font-size:11px; display:flex; align-items:center; justify-content:center; font-weight:700; }
.summary-foot .last-contact{ color:var(--lead-sub); }

@media (max-width: 991px){
    .lead-hero-panel{ flex-direction:column; align-items:flex-start; }
    .lead-hero-right{ align-self:center; }
    .lead-stat-strip{ grid-template-columns:repeat(3,1fr); }
}
@media (max-width: 575px){
    .lead-stat-strip{ grid-template-columns:repeat(2,1fr); }
}
</style>

        <div class="content-wrapper">
          <div class="page-header">
            
          </div>
          <div class="row">
           <div class="col-md-12">
            <?php if(isset($_GET['add'])){
                ?>   
            <div class="card">
            <div class="card-header">
              <i class="fa fa-shopping-bag"></i> Add New Lead
            </div>
              <div class="card-body">
               <div class="alert alert-danger" id="error_message_change" style="display:none;"></div> 
                <div class="alert alert-success" id="success_message_submit" style="display:none;"></div> 
               <form method="post" id="lead_creation_form">
                <div class="row">  
                <div class="form-group col-md-6">
                <label>Lead Owner </label>  
                <input type="text" class="form-control" value="" name="lead_owner" id="lead_owner" placeholder="Lead Owner">
                </div>
                 <div class="form-group col-md-6">
                <label>Name </label>  
                <input type="text" class="form-control" value="" name="name" id="name" placeholder="Name">
                </div>
                </div>     

                <div class="row">
                <div class="form-group col-md-3">
                <label>Age </label>
                <input type="number" min="0" max="120" class="form-control" value="" name="age" id="age" placeholder="Age">
                </div>
                <div class="form-group col-md-3">
                <label>Gender </label>
                <select class="form-control" name="gender" id="gender">
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
                </div>
                </div>
                   
                <div class="row">  
                <div class="form-group col-md-6">
                <label>City</label>  
                <input type="text" class="form-control" value="" name="city" id="city" placeholder="City">
                </div>
                 <div class="form-group col-md-6">
                <label>Mobile No </label>  
                <input type="text" class="form-control" value="" name="mobile_no" id="mobile_no" placeholder="Mobile No">
                </div>
                </div>     
                   
                <div class="row">  
                <div class="form-group col-md-6">
                <label>Lead Title </label>  
                <input type="text" class="form-control" value="" name="title" id="title" placeholder="Lead Title">
                </div>
                 <div class="form-group col-md-6">
                <label>Lead Status </label>  
                <select type="password" class="form-control" value="" name="status" id="status" placeholder="Mobile No">
                    <option value="">Select Lead Status</option>   
                   <?php foreach($status as $row){ ?>
                   <option value="<?php echo $row->status; ?>"><?php echo $row->status; ?></option>    
                   <?php } ?>
                </select>
                </div>
                </div>    
                   <div class="row">
                    <div class="form-group col-md-6">
                <label>Lead Assigned employee </label>  
                <select type="password" class="form-control" value="" name="userid" id="userid" placeholder="Mobile No">
                    <option value="">Select Lead Assigned</option>   
                   <?php foreach($employee as $row){ ?>
                   <option value="<?php echo $row->id; ?>"><?php echo $row->full_name; ?></option>    
                   <?php } ?>
                </select>
                </div>
                         <div class="form-group col-md-6">
                        <label>Lead Description </label>  
                        <textarea id="description" name="description" class="form-control" placeholder="Lead Description"></textarea>
                         </div>
                   </div>
                  <div class="form-group col-md-2">    
                  <button type="submit" id="submit_button" class="btn btn-gradient-primary mr-2">Add Lead</button>
                  </div>
              
                  </form>
              
              </div>     
            </div>
            <?php }elseif(isset($_GET['update'])){  $id= base64_decode($_GET['update']); 
               $q=$this->db->query("select * from tbl_lead where id=?", [$id])->result();
            ?>
           <div class="card">
            <div class="card-header">
              <i class="fa fa-leanpub"></i> Update New Lead
            </div>
              <div class="card-body">
               <div class="alert alert-danger" id="error_message_change" style="display:none;"></div> 
                <div class="alert alert-success" id="success_message_submit" style="display:none;"></div> 
               <form method="post" id="update_creation_form">
                   <input type="hidden" value="<?php echo $id; ?>" id="id" name="id"/>
                <div class="row">  
                <div class="form-group col-md-6">
                <label>Lead Owner </label>  
                <input type="text" class="form-control" value="<?php echo htmlspecialchars(lead_field($q[0],'lead_owner','')); ?>" name="lead_owner" id="lead_owner" placeholder="Lead Owner">
                </div>
                 <div class="form-group col-md-6">
                <label>Name </label>  
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($q[0]->name); ?>" name="name" id="name" placeholder="Name">
                </div>
                </div>     

                <div class="row">
                <div class="form-group col-md-3">
                <label>Age </label>
                <input type="number" min="0" max="120" class="form-control" value="<?php echo htmlspecialchars(lead_field($q[0],'age','')); ?>" name="age" id="age" placeholder="Age">
                </div>
                <div class="form-group col-md-3">
                <label>Gender </label>
                <select class="form-control" name="gender" id="gender">
                    <option value="">Select Gender</option>
                    <?php foreach(['Male','Female','Other'] as $g){ ?>
                    <option value="<?php echo $g; ?>" <?php echo (lead_field($q[0],'gender','')==$g)?'selected':''; ?>><?php echo $g; ?></option>
                    <?php } ?>
                </select>
                </div>
                </div>
                   
                <div class="row">  
                <div class="form-group col-md-6">
                <label>City</label>  
                <input type="text" class="form-control" value="<?php echo $q[0]->city; ?>" name="city" id="city" placeholder="City">
                </div>
                 <div class="form-group col-md-6">
                <label>Mobile No </label>  
                <input type="text" class="form-control" value="<?php echo $q[0]->mobile_no; ?>" name="mobile_no" id="mobile_no" placeholder="Mobile No">
                </div>
                </div>     
                   
                <div class="row">  
                <div class="form-group col-md-6">
                <label>Lead Title </label>  
                <input type="text" class="form-control" value="<?php echo $q[0]->title; ?>" name="title" id="title" placeholder="Lead Title">
                </div>
                 <div class="form-group col-md-6">
                <label>Lead Status </label>  
                <select type="password" class="form-control" value="" name="status" id="status" placeholder="Mobile No">
                    <option value="<?php echo $q[0]->status; ?>"><?php echo $q[0]->status; ?></option>   
                   <?php foreach($status as $row){ if($q[0]->status!=$row->status){?>
                   <option value="<?php echo $row->status; ?>"><?php echo $row->status; ?></option>    
                   <?php } } ?>
                </select>
                </div>
                </div>    
                   <div class="row">
                    <div class="form-group col-md-6">
                <label>Lead Assigned employee </label>  
                <select  class="form-control" value="" name="userid" id="userid" placeholder="Mobile No">
                     
                   <?php foreach($employee as $row){ ?>
                   <option <?php if($q[0]->userid==$row->id){ echo 'selected'; } ?> value="<?php echo $row->id; ?>"><?php echo $row->full_name; ?></option>    
                   <?php } ?>
                </select>
                </div>
                         <div class="form-group col-md-6">
                        <label>Lead Description </label>  
                        <textarea id="description" name="description" class="form-control" placeholder="Lead Description"><?php echo $q[0]->description; ?></textarea>
                         </div>
                   </div>
                  <div class="form-group col-md-2">    
                  <button type="submit" id="submit_button" class="btn btn-gradient-primary mr-2">Update Lead</button>
                  </div>
              
                  </form>
              
              </div>     
            </div>
            <?php }elseif(isset($_GET['view'])){ 
                $id= base64_decode($_GET['view']); 
               $q=$this->db->query("select * from tbl_lead where id=?", [$id])->result();
                ?>  
                <?php
$mobile = $q[0]->mobile_no;
$mobile_clean = clean_mobile($q[0]->mobile_no);

$recordings = $this->db->query("
    SELECT 
        id,
        recording_url,
        start_stamp,
        direction,
        answered_agent,
        duration,
        caller_id_number,
        call_to_number,
        tl_feedback,
        tl_flag,
        created_at
    FROM ivr_call_logs 
    WHERE 
        RIGHT(caller_id_number, 10) = '$mobile_clean'
        OR RIGHT(call_to_number, 10) = '$mobile_clean'
        OR RIGHT(customer_number_with_prefix, 10) = '$mobile_clean'
    ORDER BY id DESC
")->result();

// ---- Appointments (moved up so hero / upcoming-appointment / summary can use it) ----
$appointments = [];
$appointmentDB = null;
try {
    // Clinic database settings live in application/config/clinic_db.php (host/user/password)
    $clinicCfg = include APPPATH . 'config/clinic_db.php';
    $appointmentDB = new PDO(
        "mysql:host={$clinicCfg['host']};port={$clinicCfg['port']};dbname={$clinicCfg['database']};charset=utf8",
        $clinicCfg['username'],
        $clinicCfg['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 3]
    );
} catch (Throwable $e) {
    // Do NOT die(): the rest of the lead page (call logs, recordings, comments) must still render.
    log_message('error', 'Clinic DB connection failed on lead view: ' . $e->getMessage());
    $appointmentDB = null;
}

if ($appointmentDB) {
try {
$stmt = $appointmentDB->prepare("
    SELECT a.*, ad.username AS caller_name, d.name AS doctor_name, b.branch AS branch_name
    FROM tbl_appointment a
    LEFT JOIN tbl_admin ad ON ad.id = a.added_by
    LEFT JOIN tbl_admin d ON d.id = a.doctor_id
    LEFT JOIN tbl_branch b ON b.id = a.branch_id
    WHERE RIGHT(REGEXP_REPLACE(a.mobile, '[^0-9]', ''), 10) = :mobile
    ORDER BY a.id DESC
");
$stmt->execute([':mobile' => $mobile_clean]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    log_message('error', 'Clinic DB appointment query failed on lead view: ' . $e->getMessage());
    $appointments = [];
}
}

// find the nearest upcoming appointment (today or later, not marked done)
$upcoming_appt = null;
foreach ($appointments as $app) {
    $ts = strtotime($app['appointment_date']);
    if ($ts !== false && $ts >= strtotime('today') && (int)$app['status'] !== 1) {
        if ($upcoming_appt === null || $ts < strtotime($upcoming_appt['appointment_date'])) {
            $upcoming_appt = $app;
        }
    }
}

// ---- Payments (moved up so summary + Payments tab can use it) ----
$payments = $this->db->query("
    SELECT link_id, amount, amount_paid, status, created_at, payment_link
    FROM tbl_payments 
    WHERE lead_id = ? 
    ORDER BY id DESC
", [$q[0]->id]);
$payment_list = $payments->result();

$all_agents = $this->db->query("
    SELECT id, full_name, location
    FROM tbl_registration
    WHERE status='active' AND user_type='Employee'
    ORDER BY full_name ASC
")->result();
$total_paid = 0;
foreach ($payment_list as $p) { $total_paid += (float)$p->amount_paid; }

// ---- Tags (now backed by tbl_tags + lead_tag_map) ----
$existing_lead_tags = $this->db->query("
    SELECT t.id, t.tag_name, t.tag_color
    FROM lead_tag_map m
    JOIN tbl_tags t ON t.id = m.tag_id
    WHERE m.lead_id = ?
", [$q[0]->id])->result();

$all_tags = $this->db->query("SELECT id, tag_name, tag_color FROM tbl_tags WHERE status='Active' ORDER BY tag_name ASC")->result();

// ---- Files (lead_files table) ----
$lead_files = $this->db->query("
    SELECT lf.id, lf.file_name, lf.file_path, lf.uploaded_at, r.full_name AS uploaded_by_name
    FROM lead_files lf
    LEFT JOIN tbl_registration r ON r.id = lf.uploaded_by
    WHERE lf.lead_id = ?
    ORDER BY lf.id DESC
", [$q[0]->id])->result();

// ---- WhatsApp messages (lead_whatsapp_messages table) ----
// ---- WhatsApp messages (lead_whatsapp_messages table) ----
$whatsapp_messages = $this->db->query("
    SELECT id, direction, message, template_name, sent_at
    FROM lead_whatsapp_messages
    WHERE lead_id = ?
    ORDER BY id DESC
", [$q[0]->id])->result();

// ---- NEW: inbound customer replies from tbl_wati_webhook (never shown before) ----
$wati_inbound = $this->db->query("
    SELECT id, description AS message, requirement, created_date
    FROM tbl_wati_webhook
    WHERE RIGHT(mobile_no, 10) = ?
    ORDER BY id ASC
", [$mobile_clean])->result();

// ---- Merge inbound + outbound into a single timeline for the chat UI ----
$chat_timeline = [];
foreach ($wati_inbound as $m) {
    $chat_timeline[] = [
        'direction' => 'inbound',
        'message'   => $m->message,
        'template'  => null,
        'ts'        => strtotime($m->created_date),
    ];
}
foreach ($whatsapp_messages as $m) {
    $chat_timeline[] = [
        'direction' => $m->direction, // 'outbound' (or 'inbound' if you ever log those there too)
        'message'   => $m->message,
        'template'  => $m->template_name,
        'ts'        => strtotime($m->sent_at),
    ];
}
usort($chat_timeline, function($a, $b){ return $a['ts'] <=> $b['ts']; });
?>
<style>
.lead-view-redesign .card { border: none; box-shadow: 0 2px 12px rgba(0,0,0,.06); border-radius: 12px; overflow: hidden; }
</style>

<div class="lead-view-redesign lead-details-wrap">

<?php
$role = trim($this->session->userdata('ROLE'));
$allowedRoles = ['Manager', 'Sub Admin', 'Sales executive'];
$mobileDisplay = in_array($role, $allowedRoles)
    ? $q[0]->mobile_no
    : hide_mobile($q[0]->mobile_no);
$priorityClass = strtolower($q[0]->lead_priority ?: 'cold');

$priorityPercent = ['hot'=>92,'warm'=>60,'cold'=>30,'dead'=>10];
$scorePercent = isset($priorityPercent[$priorityClass]) ? $priorityPercent[$priorityClass] : 50;

// initials for the avatar
$nameParts = preg_split('/\s+/', trim($q[0]->customer_name));
$initials = strtoupper(substr($nameParts[0] ?? 'L', 0, 1) . substr($nameParts[1] ?? '', 0, 1));

$statusClass = 'gray';
$statusLower = strtolower($q[0]->status);
if (strpos($statusLower, 'not interested') !== false) $statusClass = 'red';
elseif (strpos($statusLower, 'contact') !== false || strpos($statusLower, 'fresh') !== false) $statusClass = 'green';

// last contact = latest of most recent recording / most recent lead_data comment
$last_contact_ts = 0; $last_contact_via = '';
if (!empty($recordings)) {
    $rt = strtotime($recordings[0]->created_at);
    if ($rt > $last_contact_ts) { $last_contact_ts = $rt; $last_contact_via = 'Call'; }
}
if (!empty($lead_data)) {
    $ct = strtotime($lead_data[0]->created_date);
    if ($ct > $last_contact_ts) { $last_contact_ts = $ct; $last_contact_via = 'Note'; }
}

// ---- Follow-up urgency (drives the color-coded "Next Follow-up" stat chip) ----
$followup_raw = lead_field($q[0], 'tarik', '');
$followup_ts = $followup_raw ? strtotime($followup_raw) : false;
$followup_class = 'gray';
$followup_note = 'Not scheduled';
if ($followup_ts) {
    $today0 = strtotime('today');
    $tomorrow0 = strtotime('tomorrow');
    if ($followup_ts < $today0) { $followup_class = 'danger'; $followup_note = 'Overdue'; }
    elseif ($followup_ts < $tomorrow0) { $followup_class = 'warn'; $followup_note = 'Due today'; }
    else { $followup_class = 'good'; $followup_note = 'Upcoming'; }
}

// ---- Lead age (time since the lead was created) ----
$created_raw = lead_field($q[0], 'created_date', lead_field($q[0], 'created_at', ''));
$lead_age_days = ($created_raw && strtotime($created_raw)) ? floor((time() - strtotime($created_raw)) / 86400) : null;

// ---- Pin code gate ----
// Compulsory before the lead can be updated, commented on, booked, or
// charged. Server-side enforcement lives in Lead::bulk_update(),
// save_appointment.php, create_payment.php and submitted_your_commenting() —
// this is just the UI reflecting the same rule so agents aren't surprised
// by a rejected request after filling out a whole form.
$leadPincode = lead_field($q[0], 'pincode', '');
$hasPincode  = ($leadPincode !== '' && $leadPincode !== 'No Data Available');
$pincodeGateAttr = $hasPincode ? '' : 'disabled title="Add a pincode to this lead first"';
?>

   

    <div class="lead-title-row">
        <h2 class="lead-page-title">Lead Details <span class="lead-id-badge">ID: <?php echo $q[0]->lead_id; ?></span></h2>
        <div class="lead-quick-actions">
            <a href="Call_lead?call=<?php echo base64_encode($q[0]->id); ?>" class="qa-btn qa-call"><i class="fa fa-phone"></i> Call</a>
            <button type="button" class="qa-btn qa-whatsapp" id="sendWatiTemplateBtn"
                    data-number="<?php echo $q[0]->mobile_no; ?>" data-id="<?php echo $q[0]->id; ?>">
                <i class="fa fa-whatsapp"></i> WhatsApp
            </button>
            <button type="button" class="qa-btn qa-appt" data-pincode-gated data-toggle="modal" data-target="#appointmentModal" <?php echo $pincodeGateAttr; ?>>
                <i class="fa fa-calendar"></i> Book Appointment
            </button>
            <div class="dropdown d-inline-block">
                <button class="qa-btn qa-more dropdown-toggle" type="button" data-toggle="dropdown"><i class="fa fa-ellipsis-v"></i> More</button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="https://team.gautamclinics.com/create_lead?update=<?php echo base64_encode($id); ?>"><i class="fa fa-edit mr-2"></i>Edit Lead</a>
                    <a class="dropdown-item" href="javascript:void(0)" data-pincode-gated data-toggle="modal" data-target="#paymentModal" <?php echo $pincodeGateAttr; ?>><i class="fa fa-credit-card mr-2"></i>Payment</a>
                    <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#recordingModal"><i class="fa fa-headphones mr-2"></i>Recordings</a>
                    <a class="dropdown-item" href="javascript:history.back()"><i class="fa fa-arrow-left mr-2"></i>Back</a>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== HERO ===== -->
    <div class="lead-hero-panel">
        <div class="lead-hero-left">
            <div class="lead-avatar"><?php echo htmlspecialchars($initials); ?></div>
            <div>
                <div class="lead-name-row">
                    <input type="text" id="customer_name" class="lead-name-input" value="<?php echo htmlspecialchars($q[0]->customer_name); ?>" placeholder="Customer name">
                    <?php if(!empty(lead_field($q[0], 'whatsapp_verified', 0))){ ?>
                        <span class="verified-badge"><i class="fa fa-check-circle"></i> WhatsApp Verified</span>
                    <?php } ?>
                </div>
                <div class="lead-meta-row">
                    <?php $ageVal = lead_field($q[0], 'age', ''); if ($ageVal !== '') { ?>
                        <span><i class="fa fa-user"></i> <?php echo htmlspecialchars($ageVal); ?></span>
                    <?php } ?>
                    <?php $genderVal = lead_field($q[0], 'gender', ''); if ($genderVal !== '') { ?>
                        <span><i class="fa fa-venus-mars"></i> <?php echo htmlspecialchars($genderVal); ?></span>
                    <?php } ?>
                    <?php if (!empty($q[0]->city)) { ?>
                        <span><i class="fa fa-map-marker"></i> <?php echo htmlspecialchars($q[0]->city); ?></span>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="lead-hero-mid">
            <div class="mid-line"><i class="fa fa-phone"></i><?php echo $mobileDisplay; ?></div>
            <?php if (!empty($q[0]->city)) { ?>
                <div class="mid-line-sub"><i class="fa fa-map-marker"></i><?php echo htmlspecialchars($q[0]->city); ?></div>
            <?php } ?>
        </div>

        <div class="lead-hero-right" title="Derived from lead priority (<?php echo strtoupper($priorityClass); ?>)">
            <div class="score-label">Lead Score</div>
            <div class="lead-score-ring" style="--score: <?php echo $scorePercent; ?>;">
                <div class="lead-score-inner"><span class="score-val"><?php echo $scorePercent; ?>%</span></div>
            </div>
            <div class="hot-lead-label priority-text-<?php echo $priorityClass; ?>"><i class="fa fa-fire"></i> <?php echo strtoupper($priorityClass); ?> Lead</div>
        </div>
    </div>

    <?php if (!$hasPincode) { ?>
    <div class="alert alert-danger" style="margin-top:14px; display:flex; align-items:center; gap:10px;">
        <i class="fa fa-exclamation-triangle"></i>
        <div>
            <b>Pincode required.</b> This lead has no pincode on file — add one below before you can save changes, add a note, book an appointment, or send a payment request.
        </div>
    </div>
    <?php } ?>

    <!-- ===== QUICK-GLANCE STAT STRIP ===== -->
    <div class="lead-stat-strip">

        <div class="stat-chip <?php echo $followup_class; ?>">
            <div class="sc-label"><i class="fa fa-calendar-check"></i> Next Follow-up</div>
            <div class="sc-value c-<?php echo $followup_class; ?>">
                <?php echo $followup_ts ? date('d M, h:i A', $followup_ts) : '—'; ?>
            </div>
            <div class="sc-sub"><?php echo $followup_note; ?></div>
        </div>

        <div class="stat-chip">
            <div class="sc-label"><i class="fa fa-bullhorn"></i> Lead Source</div>
            <div class="sc-value"><?php echo htmlspecialchars(lead_field($q[0], 'lead_source', 'Unknown')); ?></div>
            <div class="sc-sub">ID: <?php echo htmlspecialchars($q[0]->lead_id); ?></div>
        </div>

        <div class="stat-chip">
            <div class="sc-label"><i class="fa fa-user-tie"></i> Assigned To</div>
            <div class="sc-value"><?php echo htmlspecialchars(lead_field($q[0], 'assigned_to', 'Unassigned')); ?></div>
            <div class="sc-sub">Owner: <?php echo htmlspecialchars(lead_field($q[0], 'lead_owner', '—')); ?></div>
        </div>

        <div class="stat-chip">
            <div class="sc-label"><i class="fa fa-hourglass-half"></i> Lead Age</div>
            <div class="sc-value"><?php echo $lead_age_days !== null ? $lead_age_days.' day'.($lead_age_days == 1 ? '' : 's') : '—'; ?></div>
            <div class="sc-sub"><?php echo $created_raw ? date('d M Y', strtotime($created_raw)) : 'Created date unknown'; ?></div>
        </div>

        <div class="stat-chip">
            <div class="sc-label"><i class="fa fa-comments"></i> Last Contact</div>
            <div class="sc-value">
                <?php echo $last_contact_ts ? time_ago(date('Y-m-d H:i:s', $last_contact_ts)) : 'No contact yet'; ?>
            </div>
            <div class="sc-sub"><?php echo $last_contact_ts ? 'via '.$last_contact_via : '—'; ?></div>
        </div>

        <div class="stat-chip <?php echo $upcoming_appt ? 'good' : ''; ?>">
            <div class="sc-label"><i class="fa fa-stethoscope"></i> Next Appointment</div>
            <div class="sc-value <?php echo $upcoming_appt ? 'c-good' : ''; ?>">
                <?php echo $upcoming_appt ? date('d M, h:i A', strtotime($upcoming_appt['appointment_date'].' '.$upcoming_appt['appointment_time'])) : 'None booked'; ?>
            </div>
            <div class="sc-sub"><?php echo $upcoming_appt ? ('Dr. '.htmlspecialchars($upcoming_appt['doctor_name'] ?? '-')) : '—'; ?></div>
        </div>

    </div>

    <!-- ===== TABS ===== -->
    <ul class="nav nav-tabs lead-tabs" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-overview"><i class="fa fa-th-large"></i>Overview</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-activities"><i class="fa fa-history"></i>Activities</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-notes-full"><i class="fa fa-sticky-note"></i>Notes</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-files"><i class="fa fa-folder"></i>Files</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-whatsapp"><i class="fa fa-whatsapp"></i>WhatsApp</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-payments"><i class="fa fa-credit-card"></i>Payments</a></li>
    </ul>

    <div class="tab-content" style="margin-top:18px;">

    <!-- ================= OVERVIEW TAB ================= -->
    <div class="tab-pane fade show active" id="tab-overview">
        <div class="row">
            <!-- LEFT: customer information -->
            <div class="col-lg-6">
                <div class="card info-card">
                    <div class="card-header"><i class="fa fa-id-card mr-1"></i> Customer Information</div>
                    <div class="card-body p-0">

                        <div class="info-row">
                            <div class="info-label"><i class="fa fa-phone"></i> Mobile Number</div>
                            <div class="info-actions">
                                <span class="info-value"><?php echo $mobileDisplay; ?></span>
                                <a class="mini-icon-btn" href="Call_lead?call=<?php echo base64_encode($q[0]->id); ?>" title="Call"><i class="fa fa-phone"></i></a>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-label"><i class="fa fa-whatsapp"></i> WhatsApp Number</div>
                            <div class="info-actions">
                                <?php
                                $whatsapp_no = !empty($q[0]->secondary_mobile_no) ? hide_mobile($q[0]->secondary_mobile_no) : 'Same as Mobile';
                                ?>
                                <span class="info-value" id="secondary_mobile_no_display"><?php echo $whatsapp_no; ?></span>
                                <button type="button" class="mini-icon-btn" id="edit_whatsapp_btn" title="Edit WhatsApp number"><i class="fa fa-pencil"></i></button>
                                <button type="button" class="mini-icon-btn" id="sendWatiTemplateBtn2"
                                        data-number="<?php echo $q[0]->mobile_no; ?>" data-id="<?php echo $q[0]->id; ?>" title="Send WhatsApp">
                                    <i class="fa fa-whatsapp"></i>
                                </button>
                            </div>
                        </div>
                        <div class="info-row" id="whatsapp_edit_row" style="display:none;">
                            <div class="info-label"><i class="fa fa-whatsapp"></i> New WhatsApp Number</div>
                            <div class="info-actions">
                                <!--
                                    Deliberately left blank rather than pre-filled with the masked number:
                                    the field is for entering a *new* number, and never re-displays the
                                    real digits, so an agent without permission to see the full mobile
                                    number can't reconstruct it by editing this field.
                                -->
                                <input type="text" id="whatsapp_input" class="form-control form-control-sm" placeholder="10-digit mobile number" maxlength="10" style="max-width:160px;">
                                <button type="button" class="mini-icon-btn" id="save_whatsapp_btn" title="Save"><i class="fa fa-check"></i></button>
                            </div>
                        </div>

                        

                        <div class="info-row">
                            <div class="info-label"><i class="fa fa-user"></i> Lead Owner</div>
                            <div class="info-value" style="flex:1;">
                                <input type="text" id="lead_owner_field" class="form-control form-control-sm text-right"
                                       style="border:none; background:transparent; padding:0; font-weight:600;"
                                       value="<?php echo htmlspecialchars(lead_field($q[0], 'lead_owner', '')); ?>">
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-label"><i class="fa fa-venus-mars"></i> Age / Gender</div>
                            <div class="info-actions">
                                <input type="number" min="0" max="120" id="age_field" class="form-control form-control-sm text-right"
                                       style="border:none; background:transparent; padding:0; font-weight:600; width:60px;"
                                       placeholder="Age" value="<?php echo htmlspecialchars(lead_field($q[0], 'age', '')); ?>">
                                <select id="gender_field" class="form-control form-control-sm" style="border:none; background:transparent; font-weight:600; width:auto;">
                                    <option value="">-</option>
                                    <?php foreach(['Male','Female','Other'] as $g){ ?>
                                    <option value="<?php echo $g; ?>" <?php echo (lead_field($q[0],'gender','')==$g)?'selected':''; ?>><?php echo $g; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-label"><i class="fa fa-briefcase"></i> Additional Info</div>
                            <div class="info-value" style="flex:1;">
                                <input type="text" id="company_name" class="form-control form-control-sm text-right"
                                       style="border:none; background:transparent; padding:0; font-weight:600;"
                                       value="<?php echo htmlspecialchars(lead_field($q[0], 'company_name', '')); ?>">
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-label"><i class="fa fa-map-marker"></i> Address</div>
                            <div class="info-value" style="flex:1; display:flex; gap:6px; justify-content:flex-end;">
                                <input type="text" id="city" class="form-control form-control-sm text-right"
                                       style="border:none; background:transparent; padding:0; font-weight:600; max-width:140px;"
                                       placeholder="City" value="<?php echo htmlspecialchars($q[0]->city); ?>">
                                <span>
                                <?php if (!empty($q[0]->state)) echo ', '.htmlspecialchars($q[0]->state);
                                $countryVal = lead_field($q[0], 'country', '');
                                if ($countryVal !== '') echo ', '.htmlspecialchars($countryVal); ?>
                                </span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-label"><i class="fa fa-map-pin"></i> Pin Code</div>
                            <div class="info-actions">
                                <span class="info-value" id="pincode_display"><?php echo $hasPincode ? htmlspecialchars($leadPincode) : '<span style="color:#b23b3b;font-weight:700;">Required — not set</span>'; ?></span>
                                <button type="button" class="mini-icon-btn" id="edit_pincode_btn" title="<?php echo $hasPincode ? 'Edit pincode' : 'Add pincode (required)'; ?>"><i class="fa fa-pencil"></i></button>
                            </div>
                        </div>
                        <div class="info-row" id="pincode_edit_row" style="<?php echo $hasPincode ? 'display:none;' : ''; ?>">
                            <div class="info-label"><i class="fa fa-map-pin"></i> New Pin Code</div>
                            <div class="info-actions" style="flex-wrap:wrap; gap:6px;">
                                <input type="text" id="pincode_input" class="form-control form-control-sm" placeholder="6-digit pincode" maxlength="6" style="max-width:110px;" value="<?php echo htmlspecialchars($hasPincode ? $leadPincode : ''); ?>">
                                <input type="text" id="pincode_city_input" class="form-control form-control-sm" placeholder="City (auto)" style="max-width:130px;" value="<?php echo htmlspecialchars($q[0]->city); ?>">
                                <input type="text" id="pincode_state_input" class="form-control form-control-sm" placeholder="State (auto)" style="max-width:130px;" value="<?php echo htmlspecialchars($q[0]->state); ?>">
                                <button type="button" class="mini-icon-btn" id="save_pincode_btn" title="Save"><i class="fa fa-check"></i></button>
                            </div>
                        </div>
                        <?php if (!$hasPincode) { ?>
                        <div class="info-row" style="border-bottom:none; padding-top:0;">
                            <small id="pincode_lookup_msg" style="display:none; font-size:11.5px;"></small>
                        </div>
                        <?php } else { ?>
                        <small id="pincode_lookup_msg" style="display:none; padding:0 20px 8px; font-size:11.5px;"></small>
                        <?php } ?>

                        <div class="info-row">
                            <div class="info-label"><i class="fa fa-bullhorn"></i> Lead Source</div>
                            <div class="info-value"><span class="pill pill-blue"><?php echo htmlspecialchars($q[0]->lead_source); ?></span></div>
                        </div>

                        <div class="info-row">
                            <div class="info-label"><i class="fa fa-fire"></i> Lead Priority</div>
                            <div class="info-value" style="flex:1; max-width:160px; margin-left:auto;">
                                <select id="lead_priority" class="form-control form-control-sm">
                                    <option value="HOT"  <?php echo ($q[0]->lead_priority=='HOT')  ? 'selected' : ''; ?>>HOT</option>
                                    <option value="WARM" <?php echo ($q[0]->lead_priority=='WARM') ? 'selected' : ''; ?>>WARM</option>
                                    <option value="COLD" <?php echo ($q[0]->lead_priority=='COLD') ? 'selected' : ''; ?>>COLD</option>
                                    <option value="DEAD" <?php echo ($q[0]->lead_priority=='DEAD') ? 'selected' : ''; ?>>DEAD</option>
                                </select>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-label"><i class="fa fa-flag"></i> Lead Status</div>
                            <div class="info-value"><span class="pill pill-<?php echo $statusClass; ?>"><?php echo htmlspecialchars($q[0]->status); ?></span></div>
                        </div>

                        <div class="info-row">
                            <div class="info-label"><i class="fa fa-user-tie"></i> Assigned To</div>
                            <div class="info-value" style="flex:1; max-width:200px; margin-left:auto;">
                                <?php if ($this->session->userdata('USERTYPE') == 'Admin' || $this->session->userdata('USERTYPE') == 'Employee') { ?>
                                    <select id="assigned_to" class="form-control form-control-sm">
                                        <option value="">Select Agent</option>
                                        <?php foreach ($all_agents as $agent) { ?>
                                            <option value="<?php echo $agent->id; ?>"
                                                data-location="<?php echo $agent->location; ?>"
                                                data-name="<?php echo $agent->full_name; ?>"
                                                <?php echo ($q[0]->assigned_to == $agent->full_name) ? 'selected' : ''; ?>>
                                                <?php echo $agent->full_name; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                <?php } else { ?>
                                    <?php echo htmlspecialchars($q[0]->assigned_to); ?>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-label"><i class="fa fa-calendar-check"></i> Follow Up Date</div>
                            <div class="info-actions">
                                <span class="info-value" id="follow_up_display"><?php echo htmlspecialchars($q[0]->tarik); ?></span>
                                <button type="button" class="mini-icon-btn" id="edit_followup_btn" title="Edit follow-up date"><i class="fa fa-pencil"></i></button>
                            </div>
                        </div>
                        <div class="info-row" id="follow_up_edit_row" style="display:none;">
                            <div class="info-label"><i class="fa fa-calendar-check"></i> New Follow Up</div>
                            <div class="info-actions">
                                <input type="datetime-local" id="follow_up_input" class="form-control form-control-sm">
                                <button type="button" class="mini-icon-btn" id="save_followup_btn" title="Save"><i class="fa fa-check"></i></button>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-label"><i class="fa fa-clipboard-list"></i> Lead Requirement</div>
                            <div class="info-value" style="flex:1;">
                                <input type="text" id="requirement" class="form-control form-control-sm text-right"
                                       style="border:none; background:transparent; padding:0; font-weight:600;"
                                       value="<?php echo htmlspecialchars(lead_field($q[0], 'requirement', '')); ?>">
                            </div>
                        </div>

                        <div class="info-row" style="display:block;">
                            <div class="info-label" style="margin-bottom:10px;"><i class="fa fa-align-left"></i> Lead Description</div>
                            <div style="width:100%;">
                                <textarea
                                    id="lead_description"
                                    name="description"
                                    class="form-control"
                                    rows="5"
                                    maxlength="10000"
                                    placeholder="Enter lead description..."
                                    style="width:100%; min-height:120px; resize:vertical; text-align:left; font-weight:400;"
                                ><?php echo htmlspecialchars((string)($q[0]->description ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                <small class="text-muted">You can edit this description and save it with the Save Changes button below.</small>
                            </div>
                        </div>

                    </div>
                    <div style="padding:16px 20px;">
                        <button type="button" id="save_all_lead_details" class="crm-btn crm-btn-primary" style="padding:10px 26px;">
                            <i class="fa fa-save"></i> Save Changes
                        </button>
                        <small id="bulk_save_msg" style="display:none; font-weight:600; margin-left:10px;"></small>
                    </div>
                </div>
            </div>

            <!-- MIDDLE: appointment / AI / tags -->
            <div class="col-lg-3">
                <div class="card appt-card mb-3">
                    <div class="card-header"><i class="fa fa-calendar text-purple mr-1"></i> Upcoming Appointment</div>
                    <?php if ($upcoming_appt) {
                        $ts = strtotime($upcoming_appt['appointment_date']);
                    ?>
                        <div class="appt-body">
                            <div class="appt-date-box">
                                <div class="dow"><?php echo date('D', $ts); ?></div>
                                <div class="dom"><?php echo date('d', $ts); ?></div>
                                <div class="mon"><?php echo date('M', $ts); ?></div>
                            </div>
                            <div>
                                <div class="appt-time"><?php echo date('h:i A', strtotime($upcoming_appt['appointment_time'])); ?></div>
                                <div class="appt-doc">Dr. <?php echo htmlspecialchars($upcoming_appt['doctor_name'] ?? '-'); ?></div>
                                <?php if (!empty($upcoming_appt['branch_name'])) { ?>
                                    <div class="appt-doc"><?php echo htmlspecialchars($upcoming_appt['branch_name']); ?></div>
                                <?php } ?>
                                <span class="pill pill-green" style="margin-top:6px; display:inline-block;">Upcoming</span>
                            </div>
                        </div>
                        <div class="appt-actions">
                            <button type="button" class="appt-btn" data-pincode-gated data-toggle="modal" data-target="#appointmentModal" <?php echo $pincodeGateAttr; ?>><i class="fa fa-refresh"></i> Reschedule</button>
                            <button type="button" class="appt-btn danger"><i class="fa fa-times"></i> Cancel</button>
                            <a href="javascript:void(0)" class="appt-btn link" data-toggle="modal" data-target="#appointmentModal">View Details &rarr;</a>
                        </div>
                    <?php } else { ?>
                        <div class="appt-empty">
                            No upcoming appointment.<br>
                            <button type="button" class="crm-btn crm-btn-light mt-2" data-pincode-gated data-toggle="modal" data-target="#appointmentModal" <?php echo $pincodeGateAttr; ?>>
                                <i class="fa fa-plus"></i> Book Appointment
                            </button>
                        </div>
                    <?php } ?>
                </div>

                <div class="card ai-card mb-3">
            <div class="card summary-card">
                <div class="card-header">Lead Summary</div>
                <div class="summary-stats">
                    <div class="summary-stat">
                        <i class="fa fa-phone" style="color:var(--lead-orange);"></i>
                        <div class="num"><?php echo count($recordings); ?></div>
                        <div class="lbl">Total Calls</div>
                    </div>
                    <div class="summary-stat" style="cursor:pointer;" onclick="$('.lead-tabs a[href=\'#tab-whatsapp\']').tab('show');">
                        <i class="fa fa-whatsapp" style="color:var(--lead-green);"></i>
                        <div class="num"><?php echo count($whatsapp_messages); ?></div>
                        <div class="lbl">WhatsApp Chats</div>
                    </div>
                    <div class="summary-stat">
                        <i class="fa fa-calendar" style="color:var(--lead-red);"></i>
                        <div class="num"><?php echo count($appointments); ?></div>
                        <div class="lbl">Appointments</div>
                    </div>
                    <div class="summary-stat">
                        <i class="fa fa-credit-card" style="color:var(--lead-sub);"></i>
                        <div class="num">&#8377;<?php echo number_format($total_paid); ?></div>
                        <div class="lbl">Total Payments</div>
                    </div>
                </div>
                <div class="summary-foot">
                    <div class="owner">
                        <span class="dot"><?php echo htmlspecialchars(substr(lead_field($q[0],'lead_owner','?'),0,1)); ?></span>
                        <?php echo htmlspecialchars(lead_field($q[0], 'lead_owner')); ?>
                    </div>
                    <div class="last-contact">
                        <?php if ($last_contact_ts) { ?>
                            Last contact: <?php echo time_ago(date('Y-m-d H:i:s', $last_contact_ts)); ?> via <?php echo $last_contact_via; ?>
                        <?php } else { ?>
                            No contact yet
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">Appointment History</div>
                <div class="card-body p-2">
                    <div class="table-responsive" style="max-height:260px; overflow-y:auto;">
                        <table class="table table-bordered table-sm mb-0">
                            <thead style="background:#f8f3f1; color:#723421;">
                                <tr><th>#</th><th>Date</th><th>Doctor</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($appointments)) { $i = 1; foreach ($appointments as $app) { ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo date('d M Y', strtotime($app['appointment_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($app['doctor_name'] ?? '-'); ?></td>
                                    <td><?php echo ((int)$app['status'] === 1) ? '<span class="badge badge-success">Done</span>' : '<span class="badge badge-warning">Pending</span>'; ?></td>
                                </tr>
                            <?php } } else { ?>
                                <tr><td colspan="4" class="text-center">No appointments found</td></tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
                </div>

                <div class="card tags-card">
                    <div class="card-header"><i class="fa fa-tags mr-1"></i> Tags</div>
                    <div class="tags-wrap" id="tags_wrap">
                        <?php foreach ($existing_lead_tags as $t) {
                            $tagColor = $t->tag_color ?: '#723421';
                        ?>
                            <span class="tag-chip" style="background:<?php echo htmlspecialchars($tagColor); ?>1a; color:<?php echo htmlspecialchars($tagColor); ?>;">
                                <?php echo htmlspecialchars($t->tag_name); ?>
                                <a href="javascript:void(0)" class="remove-tag" data-tag-id="<?php echo $t->id; ?>">&times;</a>
                            </span>
                        <?php } ?>
                        <select id="add_tag_select" class="tag-add-select">
                            <option value="">+ Add Tag</option>
                            <?php foreach ($all_tags as $tag) { ?>
                                <option value="<?php echo $tag->id; ?>"><?php echo htmlspecialchars($tag->tag_name); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- RIGHT: activity timeline -->
            <div class="col-lg-3">
                <div class="card activity-card">
                    <div class="card-header">
                        <span>Activity Timeline</span>
                        <span class="filter-link"><i class="fa fa-filter"></i> Filter</span>
                    </div>
                    <div style="max-height:520px; overflow-y:auto;">
                    <?php if (!empty($lead_data)) { $shown = 0; foreach ($lead_data as $row) { if ($shown >= 6) break; $shown++;
                        $meta = getStatusMeta($row->status);
                    ?>
                        <div class="activity-item">
                            <div class="activity-dot" style="background:<?php echo $meta['color']; ?>;"><i class="fa <?php echo $meta['icon']; ?>"></i></div>
                            <div class="activity-body">
                                <div>
                                    <div class="activity-title"><?php echo htmlspecialchars($row->status); ?></div>
                                    <div class="activity-desc"><?php echo htmlspecialchars($row->comment); ?></div>
                                </div>
                                <div class="activity-time"><?php echo date('h:i A', strtotime($row->created_date)); ?></div>
                            </div>
                        </div>
                    <?php } } else { ?>
                        <div class="text-center text-muted" style="padding:24px 20px;">No activity yet</div>
                    <?php } ?>
                    </div>
                    <div class="activity-view-all"><a href="javascript:void(0)" onclick="$('.lead-tabs a[href=\'#tab-activities\']').tab('show');">View All Activities &rarr;</a></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= ACTIVITIES TAB ================= -->
    <div class="tab-pane fade" id="tab-activities">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Status &amp; Comment Timeline</div>
                    <div class="call-timeline" style="padding:20px 24px;">
                        <?php if (!empty($lead_data)) { foreach ($lead_data as $row) { $meta = getStatusMeta($row->status); ?>
                            <div class="timeline-step">
                                <div class="timeline-icon" style="background: <?php echo $meta['color']; ?>"><i class="fa <?php echo $meta['icon']; ?>"></i></div>
                                <div class="timeline-text">
                                    <b><?php echo htmlspecialchars($row->status); ?></b><br>
                                    <?php echo htmlspecialchars($row->comment); ?><br>
                                    <small><?php echo date('d M Y, h:i A', strtotime($row->created_date)); ?></small>
                                </div>
                            </div>
                        <?php } } else { ?>
                            <div class="text-center text-muted">No activity yet</div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Patient History</div>
                    <div class="call-timeline" style="padding:20px 24px;">
                    <?php
                    // Joined by lead_id (reliably populated) instead of user_mobile_no
                    // (which is stored as '0' for most historical rows and never matched).
                    // Reuses the existing $this->db connection instead of a second
                    // hardcoded mysqli_connect() with plaintext credentials.
                    $patient_history = $this->db->query("SELECT * FROM tbl_comment WHERE lead_id = ? ORDER BY id DESC", [$q[0]->id])->result_array();
                    if (!empty($patient_history)) {
                        foreach ($patient_history as $row) {
                            $meta = getStatusMeta($row['status']);
                            ?>
                            <div class="timeline-step">
                                <div class="timeline-icon" style="background: <?php echo $meta['color']; ?>"><i class="fa <?php echo $meta['icon']; ?>"></i></div>
                                <div class="timeline-text">
                                    <b><?php echo htmlspecialchars($row['status']); ?></b><br>
                                    <small>By <b><?php echo htmlspecialchars($row['username']); ?></b> on <?php echo date('d M Y, h:i A', strtotime($row['created_date'])); ?></small>
                                    <p><?php echo nl2br(htmlspecialchars($row['comment'])); ?></p>
                                </div>
                            </div>
                            <?php
                        }
                    } else { ?>
                        <div class="text-center text-muted">No patient history found</div>
                    <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= NOTES TAB (full width version of the bottom panel) ================= -->
    <div class="tab-pane fade" id="tab-notes-full">
        <!-- Notes panel is rendered once below (shared with Overview footer) so the comment form only exists once in the DOM -->
        <div class="text-muted" style="padding:8px 4px; font-size:12.5px;">Use the "Notes &amp; Comments" panel at the bottom of the page to add a note &mdash; it's shared across tabs.</div>
    </div>

    <!-- ================= FILES TAB ================= -->
    <div class="tab-pane fade" id="tab-files">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fa fa-folder mr-1"></i> Files</span>
                <form id="lead_file_upload_form" enctype="multipart/form-data" style="margin:0;">
                    <input type="hidden" name="lead_id" value="<?php echo $q[0]->id; ?>">
                    <label class="crm-btn crm-btn-primary mb-0" style="cursor:pointer;">
                        <i class="fa fa-upload"></i> Upload File
                        <input type="file" name="lead_file" id="lead_file_input" style="display:none;">
                    </label>
                </form>
            </div>
            <div class="card-body p-0">
                <div id="lead_file_upload_msg" class="alert" style="display:none; margin:14px 20px 0;"></div>
                <table class="table table-bordered mb-0" id="lead_files_table">
                    <thead class="thead-light">
                        <tr><th>#</th><th>File Name</th><th>Uploaded By</th><th>Uploaded On</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($lead_files)) { $i = 1; foreach ($lead_files as $f) { ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><i class="fa fa-file-o mr-1"></i> <?php echo htmlspecialchars($f->file_name); ?></td>
                            <td><?php echo htmlspecialchars($f->uploaded_by_name ?: '-'); ?></td>
                            <td><?php echo date('d M Y h:i A', strtotime($f->uploaded_at)); ?></td>
                            <td>
                                <a href="<?php echo base_url($f->file_path); ?>" target="_blank" class="mini-icon-btn" title="Download"><i class="fa fa-download"></i></a>
                            </td>
                        </tr>
                    <?php } } else { ?>
                        <tr><td colspan="5" class="text-center text-muted" style="padding:24px;">No files uploaded yet.</td></tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ================= WHATSAPP TAB ================= -->
    <!-- ================= WHATSAPP TAB ================= -->
<div class="tab-pane fade" id="tab-whatsapp">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fa fa-whatsapp mr-1"></i> WhatsApp Conversation</span>
            <small class="text-muted"><?php echo count($chat_timeline); ?> messages</small>
        </div>
        <div class="card-body" id="wati_chat_scroll" style="max-height:440px; overflow-y:auto; background:#e9f3ea22;">
            <?php if (!empty($chat_timeline)) { foreach ($chat_timeline as $m) {
                $isOut = ($m['direction'] == 'outbound');
            ?>
                <div style="display:flex; justify-content:<?php echo $isOut ? 'flex-end' : 'flex-start'; ?>; margin-bottom:10px;">
                    <div style="max-width:70%; padding:10px 14px; border-radius:12px; background:<?php echo $isOut ? '#dcf8c6' : '#fff'; ?>; border:1px solid var(--lead-border);">
                        <?php if (!empty($m['template'])) { ?>
                            <div style="font-size:11px; color:var(--lead-sub); font-weight:700; margin-bottom:3px;"><?php echo htmlspecialchars($m['template']); ?></div>
                        <?php } ?>
                        <div style="font-size:13.5px;"><?php echo nl2br(htmlspecialchars($m['message'])); ?></div>
                        <div style="font-size:10.5px; color:var(--lead-sub); text-align:right; margin-top:4px;">
                            <?php echo $m['ts'] ? date('d M h:i A', $m['ts']) : '-'; ?>
                        </div>
                    </div>
                </div>
            <?php } } else { ?>
                <div class="text-center text-muted" style="padding:30px;">No WhatsApp conversation found for this lead.</div>
            <?php } ?>
        </div>

        <!-- ---- Reply box: sends a free-text session message, not just a template ---- -->
        <div class="card-footer" style="background:#fff; border-top:1px solid var(--lead-border); padding:12px 16px;">
            <div id="wati_reply_msg" class="alert" style="display:none; margin-bottom:8px; padding:8px 12px; font-size:12.5px;"></div>
            <form id="wati_reply_form" style="display:flex; gap:8px;">
                <input type="hidden" name="lead_id" value="<?php echo $q[0]->id; ?>">
                <input type="hidden" name="mobile_no" value="<?php echo $q[0]->mobile_no; ?>">
                <input type="text" name="message" id="wati_reply_input" class="form-control" placeholder="Type a reply..." autocomplete="off">
                <button type="submit" class="crm-btn crm-btn-primary" style="white-space:nowrap;"><i class="fa fa-paper-plane"></i> Send</button>
            </form>
            <small class="text-muted" style="display:block; margin-top:6px;">
                WhatsApp only allows free-text replies within 24 hours of the customer's last message. Outside that window, use the template button above instead.
            </small>
        </div>
    </div>
</div>

    <!-- ================= PAYMENTS TAB ================= -->
    <div class="tab-pane fade" id="tab-payments">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fa fa-credit-card mr-1"></i> Payments</span>
                <button type="button" class="crm-btn crm-btn-primary" data-pincode-gated data-toggle="modal" data-target="#paymentModal" <?php echo $pincodeGateAttr; ?>><i class="fa fa-plus"></i> New Payment</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr><th>Date</th><th>Link ID</th><th>Amount</th><th>Paid</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($payment_list)) { foreach ($payment_list as $p) { ?>
                            <tr>
                                <td><?php echo date('d M Y h:i A', strtotime($p->created_at)); ?></td>
                                <td><?php echo htmlspecialchars($p->link_id); ?></td>
                                <td>&#8377;<?php echo htmlspecialchars($p->amount); ?></td>
                                <td>&#8377;<?php echo htmlspecialchars($p->amount_paid); ?></td>
                                <td>
                                    <?php if($p->status == 'SUCCESS'){ ?><span class="badge badge-success">Paid</span>
                                    <?php } elseif($p->status == 'PENDING'){ ?><span class="badge badge-warning">Pending</span>
                                    <?php } else { ?><span class="badge badge-danger">Failed</span><?php } ?>
                                </td>
                            </tr>
                        <?php } } else { ?>
                            <tr><td colspan="5" class="text-center">No payments found</td></tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    </div><!-- /tab-content -->

    <!-- ===== BOTTOM ROW: notes panel + lead summary ===== -->
    <div class="row" style="margin-top:18px;">
        <div class="col-lg-9">
            <div class="card notes-card">
                <div class="card-header">
                    <div class="notes-tabs">
                        <span class="n-tab active">Notes &amp; Comments</span>
                        <span class="n-tab" data-toggle="modal" data-target="#recordingModal">Call Logs (<?php echo count($recordings); ?>)</span>
                        <span class="n-tab" onclick="$('.lead-tabs a[href=\'#tab-whatsapp\']').tab('show');">WhatsApp</span>
                        <span class="n-tab" onclick="$('.lead-tabs a[href=\'#tab-files\']').tab('show');">Files</span>
                        <span class="n-tab" onclick="$('.lead-tabs a[href=\'#tab-payments\']').tab('show');">Payments (<?php echo count($payment_list); ?>)</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <form method="post" id="submited_comment_form">
                        <input type="hidden" name="lead_id" id="lead_id" value="<?php echo base64_decode($_GET['view']); ?>"/>
                        <input type="hidden" name="lead_title" value="<?php echo $q[0]->title; ?>"/>
                        <input type="hidden" name="userid" value="<?php echo $q[0]->userid; ?>"/>
                        <input type="hidden" name="lead_id_ID" value="<?php echo $q[0]->lead_id; ?>"/>
                        <input type="hidden" name="lead_id_mobile" value="<?php echo $q[0]->mobile_no; ?>"/>

                        <div class="alert alert-danger" id="error_message_change" style="display:none; margin:14px 20px 0;"></div>
                        <div class="alert alert-success" id="success_message_submit" style="display:none; margin:14px 20px 0;"></div>

                        <div class="note-editor" style="padding:16px 20px 0;">
                            <textarea id="comment" name="comment" placeholder="Write your note or comment here..." class="form-control note-input"></textarea>
                        </div>

                        <div class="px-3" style="padding:0 20px;">
                            <div class="row" style="row-gap:10px;">
                                <!--
                                    Follow-up date/time is now edited only via the "Follow Up Date"
                                    quick-edit above (Customer Information card) so there's a single
                                    source of truth instead of two pickers that could disagree.
                                    This hidden field just passes the lead's current tarik value
                                    through unchanged, since submitted_your_commenting also writes
                                    'tarik' back onto tbl_lead — without it, saving a note would
                                    silently blank out the follow-up date.
                                -->
                                <input type="hidden" id="tarik" name="tarik" value="<?php echo htmlspecialchars($q[0]->tarik); ?>">
                                <div class="col-md-6">
                                    <select id="lead_statuss" name="lead_statuss" class="form-control form-control-sm">

    <option value="<?php echo $q[0]->status; ?>">
        <?php echo $q[0]->status; ?>
    </option>

    <?php foreach($status as $row){

        // Do not allow Appointment Visited to be selected manually
        if (trim($row->status) === 'Appointment Visited') {
            continue;
        }

        if ($q[0]->status != $row->status) {
    ?>
        <option value="<?php echo $row->status; ?>">
            <?php echo $row->status; ?>
        </option>
    <?php
        }
    }
    ?>

</select>
                                </div>
                                <div class="col-md-4" style="display:none;">
                                    <textarea id="description" class="form-control" name="description"><?php echo $q[0]->description; ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="note-toolbar">
                           
                            <button type="submit" id="submit_button" class="crm-btn crm-btn-primary" data-pincode-gated <?php echo $pincodeGateAttr; ?>>Add Note</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        
    </div>

</div><!-- /lead-details-wrap -->

<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once $_SERVER['DOCUMENT_ROOT'].'/corephp/db.php';
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>

// ---- Pincode gate state, shared by the handlers below ----
window.LEAD_HAS_PINCODE = <?php echo $hasPincode ? 'true' : 'false'; ?>;

function pincodeGateMessage(){
    return 'Please add a pincode to this lead first.';
}

function unlockPincodeGatedActions(){
    window.LEAD_HAS_PINCODE = true;
    $('[data-pincode-gated]').prop('disabled', false).removeAttr('title');
    $('.lead-details-wrap > .alert-danger').fadeOut();
}

// open the pincode editor
$(document).on('click', '#edit_pincode_btn', function(){
    $('#pincode_edit_row').show();
});

// auto-lookup once 6 digits are typed, via the existing lead/lookup_pincode endpoint
$(document).on('input', '#pincode_input', function(){
    let val = $(this).val().replace(/\D/g,'').slice(0,6);
    $(this).val(val);
    if(val.length !== 6) return;

    let msg = $('#pincode_lookup_msg');
    msg.text('Looking up location...').css('color', '#8b8f98').show();

    $.post("<?php echo base_url('lead/lookup_pincode'); ?>", { pincode: val }, function(res){
        if(res.status && res.found){
            $('#pincode_city_input').val(res.city);
            $('#pincode_state_input').val(res.state);
            msg.text('Location found: ' + res.area + ', ' + res.city).css('color', '#1e8449');
        } else {
            msg.text((res && res.message) || 'Location not found — enter city/state manually').css('color', '#c0392b');
        }
    }, 'json').fail(function(){
        msg.text('Could not look up pincode — enter city/state manually').css('color', '#c0392b');
    });
});

// save pincode (+ city/state) via the existing lead/bulk_update endpoint
$(document).on('click', '#save_pincode_btn', function(){
    let pincode = $('#pincode_input').val().trim();
    if(!/^\d{6}$/.test(pincode)){
        $('#pincode_lookup_msg').text('Enter a valid 6-digit pincode').css('color', '#c0392b').show();
        return;
    }
    let city = $('#pincode_city_input').val().trim();
    let state = $('#pincode_state_input').val().trim();
    if(!city || !state){
        $('#pincode_lookup_msg').text('City and state are required — enter them manually if not auto-filled').css('color', '#c0392b').show();
        return;
    }

    let btn = $(this);
    btn.prop('disabled', true);
    $.ajax({
        url: "<?php echo base_url('lead/bulk_update'); ?>",
        type: "POST",
        dataType: "json",
        data: {
            lead_id: "<?php echo $q[0]->id; ?>",
            pincode: pincode,
            city: city,
            state: state
        },
        success: function(res){
            if (res.status) {
                $('#pincode_display').text(pincode);
                $('#pincode_edit_row').hide();
                $('#pincode_lookup_msg').hide();
                unlockPincodeGatedActions();
                $('#bulk_save_msg').text(res.message || 'Pincode saved').css('color', '#1e8449').fadeIn().delay(2000).fadeOut();
            } else {
                $('#pincode_lookup_msg').text(res.message || 'Could not save pincode').css('color', '#c0392b').show();
            }
        },
        error: function(){
            $('#pincode_lookup_msg').text('Server error while saving pincode, please try again').css('color', '#c0392b').show();
        },
        complete: function(){ btn.prop('disabled', false); }
    });
});

// belt-and-braces: block the three gated forms client-side too, in case a
// disabled attribute got bypassed (devtools, stale cache, etc.) — server
// side (Lead::bulk_update, save_appointment.php, create_payment.php,
// submitted_your_commenting) enforces this regardless.
$(document).on('submit', '#appointmentForm, #paymentForm, #submited_comment_form', function(e){
    if (!window.LEAD_HAS_PINCODE) {
        e.preventDefault();
        alert(pincodeGateMessage());
        $('#edit_pincode_btn').trigger('click');
    }
});

// ✅ Submit Appointment Form (works inside modal)
$(document).on("submit", "#appointmentForm", function (e) {
    e.preventDefault();
    if (!window.LEAD_HAS_PINCODE) { alert(pincodeGateMessage()); return; }
    let form = $(this);
    $.ajax({
        url: "/corephp/save_appointment.php",
        type: "POST",
        data: form.serialize(),
        dataType: "json",
        success: function (res) {
            if (res.status === "success") {
                alert(res.message);
                form[0].reset();
                $("#appointmentModal #appointment_times").html('<option value="">Select Time</option>');
                $("#appointmentModal #appointment_dr").html('<option value="">Select Doctor</option>');
                $("#appointmentModal").modal('hide');
            } else {
    alert(res.message + (res.debug ? "\n\nDEBUG: " + res.debug : ""));
}
        },
        error: function () {
            alert("Server error. Please try again.");
        }
    });
});

// ✅ Load Available Time Slots
function showappointment_time() {
    let branch_id = $("#appointmentModal #branch_id").val();
    let appointment_date = $("#appointmentModal #appointment_date").val();
    if(branch_id === '' || appointment_date === '') return;
    $.post("corephp/showappointment_time.php", {
        branch_id: branch_id,
        appointment_date: appointment_date
    }, function (data) {
        $("#appointmentModal #appointment_times").html(data);
    });
}

// ✅ Load Doctors Based on Time
function show_doctor(time) {
    let branch_id = $("#appointmentModal #branch_id").val();
    let appointment_date = $("#appointmentModal #appointment_date").val();
    if(time === '') return;
    $.post("corephp/show_doctor.php", {
        str: time,
        branch_id: branch_id,
        appointment_date: appointment_date
    }, function (data) {
        $("#appointmentModal #appointment_dr").html(data);
    });
}

$(document).on("change", "#appointmentModal #branch_id, #appointmentModal #appointment_date", function () {
    showappointment_time();
});
$(document).on("change", "#appointmentModal #appointment_times", function () {
    show_doctor(this.value);
});

$(document).on("submit", "#paymentForm", function(e){
    e.preventDefault();
    if (!window.LEAD_HAS_PINCODE) { alert(pincodeGateMessage()); return; }
    $.ajax({
        url: "/corephp/create_payment.php",
        type: "POST",
        data: $(this).serialize(),
        dataType: "json",
        success: function(res){
            if(res.status === "success"){
                alert("Payment link created!");
                $("#paymentModal").modal("hide");
            }else{
                alert(res.message);
            }
        },
        error:function(){
            alert("Payment error!");
        }
    });
});

$(document).on('change', '.tl-feedback, .tl-flag', function(){
    let row = $(this).closest('tr');
    let id = row.find('.tl-feedback').data('id');
    let feedback = row.find('.tl-feedback').val();
    let flag = row.find('.tl-flag').val();
    $.ajax({
        url: "<?php echo base_url('Api_Controller/update_call_feedback'); ?>",
        method: "POST",
        data: { id: id, feedback: feedback, flag: flag },
        success: function(res){ console.log(res); }
    });
});
$('.tl-flag').each(function(){
    let val = $(this).val();
    $(this).addClass('crm-flag-'+val);
});

$.getJSON("support_assets/js/states-cities.json", function(data){
    // kept for parity with the original state/city cascading dropdowns,
    // in case a hidden #state / #city <select> is added back later.
});

$(document).on('change','#same_as_primary',function(){
    if($(this).is(':checked')){
        $('#secondary_mobile_no_display').text("<?php echo hide_mobile($q[0]->mobile_no); ?>");
    } else {
        $('#secondary_mobile_no_display').text("<?php echo hide_mobile($q[0]->secondary_mobile_no); ?>");
    }
});

// ---- inline follow-up date edit ----
$(document).on('click', '#edit_followup_btn', function(){
    $('#follow_up_edit_row').show();
});
$(document).on('click', '#save_followup_btn', function(){
    let val = $('#follow_up_input').val();
    if(!val){ return; }
    let btn = $(this);
    btn.prop('disabled', true);
    $.ajax({
        url: "<?php echo base_url('lead/bulk_update'); ?>",
        type: "POST",
        dataType: "json",
        data: { lead_id: "<?php echo $q[0]->id; ?>", tarik: val },
        success: function(res){
            if (res.status) {
                // Trust what the server actually wrote back, not the raw client value —
                // closes the gap where the UI could look "saved" without matching the DB.
                let saved = (res.updated && res.updated.tarik) ? res.updated.tarik : val.replace('T', ' ');
                $('#follow_up_display').text(saved);
                $('#follow_up_edit_row').hide();
                // Keep the Notes & Comments form's hidden tarik field in sync — it also
                // writes 'tarik' back onto tbl_lead, so without this a note submitted
                // right after a follow-up edit (no page reload in between) would revert
                // the date back to whatever was on the page at load time.
                $('#tarik').val(saved);
                $('#bulk_save_msg').text(res.message || 'Follow-up date updated').css('color', '#1e8449').fadeIn().delay(2000).fadeOut();
            } else {
                let msg = res.message || 'Could not save follow-up date';
                if (res.db_error) { msg += ' (' + res.db_error + ')'; }
                $('#bulk_save_msg').text(msg).css('color', '#c0392b').fadeIn().delay(4000).fadeOut();
            }
        },
        error: function(){
            $('#bulk_save_msg').text('Server error while saving follow-up date, please try again').css('color', '#c0392b').fadeIn().delay(3000).fadeOut();
        },
        complete: function(){
            btn.prop('disabled', false);
        }
    });
});

// ---- inline WhatsApp number edit (masked display; field is always entered blank so the
// real number is never re-exposed just by opening the editor) ----
$(document).on('click', '#edit_whatsapp_btn', function(){
    $('#whatsapp_input').val('');
    $('#whatsapp_edit_row').show();
});
$(document).on('click', '#save_whatsapp_btn', function(){
    let val = $('#whatsapp_input').val().trim();
    let digits = val.replace(/\D/g, '');
    if (digits.length !== 10) {
        $('#bulk_save_msg').text('Enter a valid 10-digit mobile number').css('color', '#c0392b').fadeIn().delay(3000).fadeOut();
        return;
    }
    let btn = $(this);
    btn.prop('disabled', true);
    $.ajax({
        url: "<?php echo base_url('lead/bulk_update'); ?>",
        type: "POST",
        dataType: "json",
        data: { lead_id: "<?php echo $q[0]->id; ?>", secondary_mobile_no: digits },
        success: function(res){
            if (res.status) {
                // re-mask on screen — never show the full number back after saving
                $('#secondary_mobile_no_display').text(digits.substr(0, 4) + 'xxxxxx');
                $('#whatsapp_input').val('');
                $('#whatsapp_edit_row').hide();
                $('#bulk_save_msg').text(res.message || 'WhatsApp number updated').css('color', '#1e8449').fadeIn().delay(2000).fadeOut();
            } else {
                $('#bulk_save_msg').text(res.message || 'Could not save WhatsApp number').css('color', '#c0392b').fadeIn().delay(3000).fadeOut();
            }
        },
        error: function(){
            $('#bulk_save_msg').text('Server error while saving WhatsApp number, please try again').css('color', '#c0392b').fadeIn().delay(3000).fadeOut();
        },
        complete: function(){
            btn.prop('disabled', false);
        }
    });
});

// ---- tags: add/remove now persist against tbl_tags + lead_tag_map ----
$(document).on('change', '#add_tag_select', function(){
    let tag_id = $(this).val();
    if(!tag_id) return;
    $.post("<?php echo base_url('lead/add_tag'); ?>", { lead_id: "<?php echo $q[0]->id; ?>", tag_id: tag_id }, function(){
        location.reload();
    }, 'json');
});
$(document).on('click', '.remove-tag', function(){
    let tag_id = $(this).data('tag-id');
    $.post("<?php echo base_url('lead/remove_tag'); ?>", { lead_id: "<?php echo $q[0]->id; ?>", tag_id: tag_id }, function(){
        location.reload();
    }, 'json');
});

$(document).on('click', '#save_all_lead_details', function () {
    if (!window.LEAD_HAS_PINCODE) {
        $('#bulk_save_msg').text('Please add a pincode to this lead before saving changes').css('color', '#c0392b').fadeIn();
        $('#edit_pincode_btn').trigger('click');
        return;
    }

    let btn = $(this);
    let lead_id = "<?php echo $q[0]->id; ?>";
    let payload = {
        lead_id: lead_id,
        customer_name: $('#customer_name').val(),
        lead_owner: $('#lead_owner_field').val(),
        age: $('#age_field').val(),
        gender: $('#gender_field').val(),
        company_name: $('#company_name').val(),
        lead_priority: $('#lead_priority').val(),
        requirement: $('#requirement').val(),
        city: $('#city').val(),
        description: $('#lead_description').val()
    };

    let assignedSelect = $('#assigned_to');
    if (assignedSelect.length && assignedSelect.val()) {
        let selected = assignedSelect.find('option:selected');
        payload.employee_id  = assignedSelect.val();
        payload.assigned_to  = selected.data('name');
        payload.api          = selected.data('location');
    }

    btn.prop('disabled', true).html('Saving...');
    $.ajax({
        url: "<?php echo base_url('lead/bulk_update'); ?>",
        type: "POST",
        dataType: "json",
        data: payload,
        success: function (res) {
            $('#bulk_save_msg')
                .text(res.message)
                .css('color', res.status ? '#1e8449' : '#c0392b')
                .fadeIn();
            if (res.status && res.reload) {
                setTimeout(function () { location.reload(); }, 700);
            } else {
                setTimeout(function () { $('#bulk_save_msg').fadeOut(); }, 2500);
            }
        },
        error: function () {
            $('#bulk_save_msg').text('Server error, please try again').css('color', '#c0392b').fadeIn();
        },
        complete: function () {
            btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Changes');
        }
    });
});

// ---- Notes & Comments: was a dead <form method="post"> with no handler and no matching
// controller action on this page, so submitting it just reloaded the page and threw the
// comment away. Wire it to the existing submitted_your_commenting endpoint instead. ----
$(document).on('submit', '#submited_comment_form', function (e) {
    e.preventDefault();
    if (!window.LEAD_HAS_PINCODE) { alert(pincodeGateMessage()); return; }
    let form = $(this);
    let btn = form.find('#submit_button');
    let comment = $('#comment').val().trim();
    if (!comment) {
        $('#error_message_change').text('Please write a note before submitting.').show();
        return;
    }
    $('#error_message_change').hide();
    btn.prop('disabled', true).text('Saving...');
    $.ajax({
        url: "<?php echo base_url('submitted_your_commenting'); ?>",
        type: "POST",
        dataType: "json",
        data: form.serialize(),
        success: function (res) {
            if (res.code == 200) {
                $('#success_message_submit').text(res.message).show().delay(2000).fadeOut();
                form[0].reset();
                // keep the hidden fields (lead_id etc.) intact since form.reset() only clears input values
                $('#comment').val('');
                setTimeout(function () { location.reload(); }, 800);
            } else {
                $('#error_message_change').text(res.message || 'Could not save note').show();
            }
        },
        error: function () {
            $('#error_message_change').text('Server error, please try again').show();
        },
        complete: function () {
            btn.prop('disabled', false).text('Add Note');
        }
    });
});
$(document).on('submit', '#wati_reply_form', function (e) {
    e.preventDefault();
    let form = $(this);
    let input = $('#wati_reply_input');
    let msg = input.val().trim();
    if (!msg) return;

    let btn = form.find('button[type="submit"]');
    btn.prop('disabled', true);

    $.ajax({
        url: "<?php echo base_url('Api/send_wati_message'); ?>",
        type: "POST",
        dataType: "json",
        data: form.serialize(),
        success: function (res) {
            if (res.status === 'success') {
                input.val('');
                // Append the sent message to the chat immediately, then reload
                // shortly after so the DB-backed timeline (with correct id/order)
                // takes over — avoids a jarring full reload on every send.
                $('#wati_chat_scroll').append(
                    '<div style="display:flex; justify-content:flex-end; margin-bottom:10px;">' +
                    '<div style="max-width:70%; padding:10px 14px; border-radius:12px; background:#dcf8c6; border:1px solid var(--lead-border);">' +
                    '<div style="font-size:13.5px;">' + $('<div>').text(msg).html() + '</div>' +
                    '<div style="font-size:10.5px; color:var(--lead-sub); text-align:right; margin-top:4px;">Just now</div>' +
                    '</div></div>'
                );
                $('#wati_chat_scroll').scrollTop($('#wati_chat_scroll')[0].scrollHeight);
                setTimeout(function(){ location.reload(); }, 1500);
            } else {
                $('#wati_reply_msg').removeClass('alert-success').addClass('alert-danger')
                    .text(res.message || 'Failed to send message').show();
            }
        },
        error: function () {
            $('#wati_reply_msg').removeClass('alert-success').addClass('alert-danger')
                .text('Server error while sending message').show();
        },
        complete: function () {
            btn.prop('disabled', false);
        }
    });
});

// Auto-scroll chat to bottom on tab open
$(document).on('shown.bs.tab', 'a[href="#tab-whatsapp"]', function () {
    let box = $('#wati_chat_scroll');
    box.scrollTop(box[0].scrollHeight);
});
// ---- Files tab: upload wired to Lead/upload_file (lead_files table) ----
$(document).on('change', '#lead_file_input', function () {
    let fileInput = this;
    if (!fileInput.files.length) return;
    let formData = new FormData($('#lead_file_upload_form')[0]);
    let msg = $('#lead_file_upload_msg');
    msg.removeClass('alert-success alert-danger').text('Uploading...').show();
    $.ajax({
        url: "<?php echo base_url('lead/upload_file'); ?>",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function (res) {
            if (res.status) {
                msg.addClass('alert-success').text(res.message || 'File uploaded');
                setTimeout(function () { location.reload(); }, 800);
            } else {
                msg.addClass('alert-danger').text(res.message || 'Upload failed');
            }
        },
        error: function () {
            msg.addClass('alert-danger').text('Server error, please try again');
        }
    });
});

$(document).on('click', '#sendWatiTemplateBtn, #sendWatiTemplateBtn2', function () {
    var btn = $(this);
    var mobile = btn.data('number');
    var leadId = btn.data('id');
    if (!mobile) { alert('Mobile number not found.'); return; }
    var oldHtml = btn.html();
    btn.prop('disabled', true).html('Sending...');
    $.ajax({
        url: '<?php echo base_url("Api/send_wati_template"); ?>',
        type: 'POST',
        dataType: 'json',
        data: { mobile_no: mobile, lead_id: leadId },
        success: function (response) {
            if (response.status === 'success') {
                alert('Template sent successfully to ' + mobile);
            } else {
                alert('Failed: ' + response.message);
            }
        },
        error: function () {
            alert('Error sending template. Please try again.');
        },
        complete: function () {
            btn.prop('disabled', false).html(oldHtml);
        }
    });
});
</script>

<!-- ================= MODALS ================= -->
<div class="modal fade" id="recordingModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header crm-modal-header">
        <h5 class="modal-title"><i class="fa fa-headphones"></i> Call Recordings</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <?php if(!empty($recordings)) { ?>
            <table class="table table-bordered table-hover crm-table">
                <thead>
                    <tr><th>#</th><th>Type</th><th>Date & Time</th><th>Duration</th><th>Play</th><th>Feedback</th><th>Flag</th></tr>
                </thead>
                <tbody>
                <?php $i=1; foreach($recordings as $rec){ ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><span class="badge <?php echo ($rec->direction=='inbound')?'crm-badge-inbound':'crm-badge-outbound'; ?>"><?php echo ucfirst($rec->direction); ?></span></td>
                        <td><?php echo !empty($rec->created_at) ? date('d M Y h:i A', strtotime($rec->created_at . ' UTC')) : '-'; ?></td>
                        <td><?php echo $rec->duration ? $rec->duration.' sec' : '-'; ?></td>
                        <td>
                            <?php if(!empty($rec->recording_url)){ ?>
                                <audio controls style="width:200px;"><source src="<?php echo $rec->recording_url; ?>" type="audio/mpeg"></audio>
                            <?php } else { ?><span class="text-danger">No File</span><?php } ?>
                        </td>
                        <td><textarea class="form-control tl-feedback" data-id="<?php echo $rec->id; ?>" placeholder="Enter feedback"><?php echo isset($rec->tl_feedback) ? $rec->tl_feedback : ''; ?></textarea></td>
                        <td>
                            <select class="form-control tl-flag" data-id="<?php echo isset($rec->id) ? $rec->id : 0; ?>">
                                <option value="none" <?php if($rec->tl_flag=='none') echo 'selected'; ?>>None</option>
                                <option value="good" <?php if($rec->tl_flag=='good') echo 'selected'; ?>>Good</option>
                                <option value="bad" <?php if($rec->tl_flag=='bad') echo 'selected'; ?>>Bad</option>
                                <option value="training" <?php if($rec->tl_flag=='training') echo 'selected'; ?>>Training</option>
                            </select>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="text-center text-muted"><i class="fa fa-info-circle"></i><br>No call recordings found</div>
        <?php } ?>
      </div>
      <div class="modal-footer crm-modal-footer"><button class="btn crm-btn-close" data-dismiss="modal">Close</button></div>
    </div>
  </div>
</div>

<div class="modal fade" id="appointmentModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header crm-modal-header">
        <h5 class="modal-title"><i class="fa fa-calendar"></i> Book Appointment</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form id="appointmentForm">
          <input type="hidden" name="added_by" value="<?php echo $this->session->userdata('ADMIN_ID'); ?>" />
          <input type="hidden" name="lead_id" value="<?php echo $q[0]->id; ?>" />
            <input type="hidden" name="lead_source" value="<?php echo htmlspecialchars(lead_field($q[0], 'lead_source', '')); ?>">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label>Customer Name</label>
              <input type="text" class="form-control" name="customer_name" value="<?php echo $q[0]->customer_name; ?>" required>
            </div>
            <div class="col-md-6 mb-3" style="display:none">
              <input type="hidden" class="form-control" name="mobile" value="<?php echo $q[0]->mobile_no; ?>" required readonly>
            </div>
            <div class="col-md-6 mb-3">
              <label>Branch</label>
              <select class="form-control" name="branch_id" id="branch_id" required>
                <option value="">Select Branch</option>
                <?php
                $stmt2 = $dbh->prepare("SELECT id, branch FROM tbl_branch");
                $stmt2->execute();
                while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="'.$row['id'].'">'.$row['branch'].'</option>';
                }
                ?>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label>Appointment Date</label>
              <input type="date" class="form-control" name="appointment_date" id="appointment_date" required>
            </div>
            <div class="col-md-6 mb-3">
              <label>Available Time</label>
              <select name="appointment_time" id="appointment_times" class="form-control" required><option value="">Select Time</option></select>
            </div>
            <div class="col-md-6 mb-3">
              <label>Available Doctor</label>
              <select name="doctor_id" id="appointment_dr" class="form-control" required><option value="">Select Doctor</option></select>
            </div>
          </div>
          <div class="text-center mt-3"><button type="submit" class="btn crm-btn-primary px-4">Confirm Appointment</button></div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="paymentModal" tabindex="-1">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header crm-modal-header">
        <h5 class="modal-title"><i class="fa fa-credit-card"></i> Send Payment Request</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form id="paymentForm">
          <input type="hidden" name="lead_id" value="<?php echo $q[0]->id; ?>">
          <div class="form-group">
            <label>Customer Name</label>
            <input type="text" class="form-control" value="<?php echo $q[0]->customer_name; ?>" readonly>
          </div>
          <div class="form-group">
            <label>Mobile</label>
            <input type="text" class="form-control" name="mobile" value="<?php echo hide_mobile($q[0]->mobile_no); ?>" required readonly>
          </div>
          <input type="hidden" name="mobile" value="<?php echo $q[0]->mobile_no; ?>">
          <input type="hidden" name="customer_name" value="<?php echo $q[0]->customer_name; ?>">
          <div class="form-group">
            <label>Amount (₹)</label>
            <input type="number" name="amount" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Remark</label>
            <input type="text" name="remark" class="form-control">
          </div>
          <div class="mt-3">
            <label>Status:</label><br>
            <?php
            // Derived from the latest tbl_payments row instead of the stale tbl_lead.payment_status snapshot.
            $latestPaymentStatus = !empty($payment_list) ? $payment_list[0]->status : null;
            if($latestPaymentStatus == 'SUCCESS'){ ?><span class="badge badge-success">Paid</span>
            <?php } elseif($latestPaymentStatus == 'PENDING'){ ?><span class="badge badge-warning">Pending</span>
            <?php } elseif($latestPaymentStatus == 'FAILED'){ ?><span class="badge badge-danger">Failed</span>
            <?php } else { ?><span class="badge badge-secondary">Not Initiated</span><?php } ?>
          </div>
          <div class="text-center mt-4"><button type="submit" class="btn crm-btn-primary">Send Payment Link</button></div>
        </form>
        <hr>
        <h6 class="mb-3">Payment History</h6>
        <div style="max-height:250px; overflow-y:auto;">
          <table class="table table-sm table-bordered">
            <thead class="thead-light"><tr><th>Date</th><th>Link ID</th><th>Amount</th><th>Paid</th><th>Status</th></tr></thead>
            <tbody>
            <?php if(!empty($payment_list)){ foreach($payment_list as $p){ ?>
                <tr>
                  <td><?php echo date('d M Y h:i A', strtotime($p->created_at)); ?></td>
                  <td><?php echo $p->link_id; ?></td>
                  <td>₹<?php echo $p->amount; ?></td>
                  <td>₹<?php echo $p->amount_paid; ?></td>
                  <td>
                    <?php if($p->status == 'SUCCESS'){ ?><span class="badge badge-success">Paid</span>
                    <?php } elseif($p->status == 'PENDING'){ ?><span class="badge badge-warning">Pending</span>
                    <?php } else { ?><span class="badge badge-danger">Failed</span><?php } ?>
                  </td>
                </tr>
            <?php } } else { ?>
                <tr><td colspan="5" class="text-center">No payments found</td></tr>
            <?php } ?>
            </tbody>
          </table>
        </div>
        <hr>
      </div>
    </div>
  </div>
</div>

            <?php }else{ ?>  
               <input type="hidden" name="lead_status" id="lead_status" value="<?php echo $_GET['lead_status']; ?>">
                <div class="card">
            <div class="card-header">
                <i class="mdi mdi-table-large"></i><?php echo $_GET['lead_status']; ?> Lead List <a href="javascript:history.back()" class="btn btn-gradient-danger btn-rounded btn-fw btn-sm">Back</a>

            </div>
              <div class="card-body">
                  
                 <?php if($this->session->userdata('success')){ ?>
                  <div class="alert alert-success">
                      <?php echo $this->session->userdata('success'); ?>
                  </div>
                 <?php $this->session->unset_userdata('success'); } ?>
                  <div class="table-responsive">
                      <table class="table table-bordered" id="all_tables_details">
                          <thead>
                           <tr>
                            <th>Sr.No</th>
                            <th>Customer Name</th>
                            <th>Customer Mobile</th>
                            <th>Address</th>
                            <th>Requirement</th>
                            <th>Description</th>
                            <th>Source</th>
                            <th>Date</th> 
                            <th>Status</th>
                            <th style="width:17%;">Action</th>
                            </tr>
                          </thead>    
                      </table>   
                  </div>
              </div>
            </div>
            <?php } ?>
           </div>
          </div>   
        </div>