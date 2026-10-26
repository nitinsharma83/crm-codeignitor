<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="utf-8">

<title>Core Appointment List</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

<style>

body{
    background:#f4f6f9;
    font-size:14px;
}

.card{
    border:none;
    box-shadow:0 2px 8px rgba(0,0,0,.08);
}

.table th{
    white-space:nowrap;
}

.table td{
    vertical-align:middle;
}

.badge{
    padding:6px 10px;
    font-size:12px;
}

.filter-box{
    background:#fff;
    padding:20px;
    border-radius:6px;
    margin-bottom:20px;
}

.pagination{
    margin-top:20px;
}

</style>

</head>

<body>

<div class="container-fluid mt-4">

<div class="card">

<div class="card-header bg-primary text-white">

<div class="d-flex justify-content-between">

<h4 class="mb-0">Appointment List</h4>

<a href="<?= site_url('CoreAppointment'); ?>" class="btn btn-light btn-sm">
Refresh
</a>

</div>

</div>

<div class="card-body">

<form method="get">

<div class="row">

<div class="col-md-3 mb-3">

<label>Appointment From</label>

<input
type="date"
name="sfrom_date"
value="<?= isset($filter['sfrom_date']) ? $filter['sfrom_date'] : '';?>"
class="form-control">

</div>

<div class="col-md-3 mb-3">

<label>Appointment To</label>

<input
type="date"
name="sto_date"
value="<?= isset($filter['sto_date']) ? $filter['sto_date'] : '';?>"
class="form-control">

</div>

<div class="col-md-3 mb-3">

<label>Created From</label>

<input
type="date"
name="cfrom_date"
value="<?= isset($filter['cfrom_date']) ? $filter['cfrom_date'] : '';?>"
class="form-control">

</div>

<div class="col-md-3 mb-3">

<label>Created To</label>

<input
type="date"
name="cto_date"
value="<?= isset($filter['cto_date']) ? $filter['cto_date'] : '';?>"
class="form-control">

</div>

</div>

<div class="row">

<div class="col-md-3 mb-3">

<label>Customer Name</label>

<input
type="text"
name="name"
class="form-control"
value="<?= isset($filter['name']) ? $filter['name'] : '';?>">

</div>

<div class="col-md-3 mb-3">

<label>Mobile</label>

<input
type="text"
name="mobile"
class="form-control"
value="<?= isset($filter['mobile']) ? $filter['mobile'] : '';?>">

</div>

<div class="col-md-3 mb-3">

<label>Doctor</label>

<select name="doctor" class="form-control">

<option value="">All Doctors</option>

<?php foreach($doctors as $doctor){ ?>

<option
value="<?= $doctor->id;?>"
<?= ($filter['doctor']==$doctor->id)?'selected':'';?>>

<?= $doctor->name;?>

</option>

<?php } ?>

</select>

</div>
<div class="col-md-3 mb-3">

<label>Branch</label>

<select name="branch" class="form-control">

<option value="">All Branches</option>

<?php foreach($branches as $branch){ ?>

<option
value="<?= $branch->id;?>"
<?= (isset($filter['branch']) && $filter['branch']==$branch->id)?'selected':'';?>>

<?= $branch->branch;?>

</option>

<?php } ?>

</select>

</div>

</div>

<div class="row">

<div class="col-md-3 mb-3">

<label>Customer Source</label>

<select name="customer_source" class="form-control">

<option value="">All Sources</option>

<?php foreach($sources as $source){ ?>

<option
value="<?= $source->id;?>"
<?= (isset($filter['customer_source']) && $filter['customer_source']==$source->id)?'selected':'';?>>

<?= $source->type;?>

</option>

<?php } ?>

</select>

</div>

<div class="col-md-3 mb-3">

<label>Caller</label>

<select name="added_by" class="form-control">

<option value="">All Callers</option>

<?php foreach($callers as $caller){ ?>

<option
value="<?= $caller->id;?>"
<?= (isset($filter['added_by']) && $filter['added_by']==$caller->id)?'selected':'';?>>

<?= $caller->username;?>

</option>

<?php } ?>

</select>

</div>

<div class="col-md-3 mb-3">

<label>Status</label>

<select name="status" class="form-control">

<option value="">All Status</option>

<option value="1"
<?= (isset($filter['status']) && $filter['status']==1)?'selected':'';?>>

Done

</option>

<option value="0"
<?= (isset($filter['status']) && $filter['status']==='0')?'selected':'';?>>

Pending

</option>

</select>

</div>

<div class="col-md-3 mb-3 d-flex align-items-end">

<button class="btn btn-primary mr-2">

Search

</button>

<a href="<?= site_url('CoreAppointment'); ?>"
class="btn btn-secondary">

Reset

</a>

</div>

</div>

</form>

<hr>

<div class="table-responsive">

<table class="table table-bordered table-hover table-striped">

<thead class="thead-dark">

<tr>

<th width="60">#</th>

<th>Customer Name</th>

<th>Mobile</th>



<th>Appointment Date</th>

<th>Appointment Time</th>

<th>Doctor</th>

<th>Branch</th>

<th>Source</th>

<th>Caller</th>

<th>Created</th>

<th>Status</th>





</tr>

</thead>

<tbody>

<?php

if(!empty($appointments)):

$sn = 1 + $this->uri->segment(3);

foreach($appointments as $row):

?>

<tr>

<td><?= $sn++; ?></td>

<td><?= ucwords(strtolower($row->customer_name)); ?></td>

<td>
<?php
$role = trim($this->session->userdata('ROLE'));

$allowedRoles = ['Manager', 'Sub Admin'];

$mobile = preg_replace('/\D/', '', trim($row->mobile));

if (in_array($role, $allowedRoles)) {
    // Show full mobile number
    echo $mobile;
} else {
    // Show masked mobile number
    if (strlen($mobile) >= 10) {
        echo substr($mobile, 0, 6) . 'XXXX';
    } else {
        echo $mobile;
    }
}
?>
</td>



<td>

<?= date('d-m-Y',strtotime($row->appointment_date)); ?>

</td>

<td>

<?= date('h:i A',strtotime($row->appointment_time)); ?>

</td>

<td>

<?= $row->doctor_name; ?>

</td>
<td>

<?= $row->branch; ?>

</td>

<td>

<?= $row->source_name; ?>

</td>

<td>

<?= $row->caller_name; ?>

</td>

<td>

<?php

if(!empty($row->post_date))
{
    echo date('d-m-Y', strtotime($row->post_date));
}
else
{
    echo '-';
}

?>

</td>

<td>

<?php if($row->status==1){ ?>

<span class="badge badge-success">

Done

</span>

<?php }else{ ?>

<span class="badge badge-warning">

Pending

</span>

<?php } ?>

</td>





</tr>

<?php

endforeach;

else:

?>

<tr>

<td colspan="14" class="text-center">

<div class="alert alert-warning mb-0">

No Appointment Found.

</div>

</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>

<div class="row mt-3">

<div class="col-md-6">

<b>

Total Records :

<?= isset($appointments) ? count($appointments) : 0; ?>

</b>

</div>

<div class="col-md-6 text-right">

<?= $pagination; ?>

</div>

</div>

</div>

</div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>