
        <div class="content-wrapper">
          <div class="page-header">
            <h3 class="page-title">
              <span class="page-title-icon bg-gradient-primary text-white mr-2">
                <i class="fa fa-clock-o"></i>                 
              </span>
            Duplicates <?php $username = $this->session->userdata('USERNAME'); ?>
            </h3>
          </div>
          <div class="row">
           <div class="col-md-12">
                <div class="card">
            <div class="card-header">
            <?php $username = $this->session->userdata('USERNAME'); ?>
                <i class="mdi mdi-watch"></i> Duplicates  
                    </div>
            </div>
            <?php
include_once 'db_config.php';
if(isset($_POST['submit']))
{    
     $username = $_POST['username'];
     $hh = $_POST['hh'];
     $mm = $_POST['mm'];
     $ss = $_POST['ss'];
     $ms = $_POST['ms'];
     $tarik = date("Y-m-d");
     $reason = $_POST['reason'];
     $sql = "INSERT INTO timer (username,breaktime,reason,tarik)
     VALUES ('$username','$hh:$mm:$ss:$ms','$reason','$tarik')";
     if (mysqli_query($mysqli, $sql)) {
     } else {
        echo "Error: " . $sql . ":-" . mysqli_error($mysqli);
     }
}
?>
              <div class="card-body">
<?php

$username = $this->session->userdata('USERNAME');

if($username == 'Admin')
{

$query = "
SELECT 
    t1.id,
    t1.customer_name,
    t1.company_name,
    t1.mobile_no,
    t1.status,
    t1.assigned_to

FROM tbl_lead t1

INNER JOIN (

    SELECT 
        assigned_to,

        RIGHT(
            REPLACE(
                REPLACE(mobile_no, '+', ''),
            ' ', ''),
        10
        ) as normalized_mobile

    FROM tbl_lead

    GROUP BY assigned_to, normalized_mobile

    HAVING COUNT(*) > 1

) t2

ON t1.assigned_to = t2.assigned_to

AND RIGHT(
        REPLACE(
            REPLACE(t1.mobile_no, '+', ''),
        ' ', ''),
    10
) = t2.normalized_mobile

ORDER BY t1.assigned_to ASC, t1.id ASC
";

}
else
{

$query = "
SELECT 
    id,
    customer_name,
    company_name,
    mobile_no,
    status,
    assigned_to

FROM tbl_lead

WHERE assigned_to = '$username'

AND RIGHT(
        REPLACE(
            REPLACE(mobile_no, '+', ''),
        ' ', ''),
    10
) IN (

    SELECT RIGHT(
                REPLACE(
                    REPLACE(mobile_no, '+', ''),
                ' ', ''),
            10
           )

    FROM tbl_lead

    WHERE assigned_to = '$username'

    GROUP BY RIGHT(
                    REPLACE(
                        REPLACE(mobile_no, '+', ''),
                    ' ', ''),
                10
             )

    HAVING COUNT(*) > 1
)

ORDER BY id ASC
";

}

$result = $this->db->query($query);

?>
<table border="2" cellspacing="0" cellpadding="10" width="100%">
  <tr>
    <th width="10%">S.N </th>
    <th width="20%">Customer Name</th>
    <th width="20%">Company Name</th>
    <th width="20%">Mobile Number</th>
    <th width="20%">Status</th>
    <th width="15%">Assigned To</th>
    <th width="20%">Action</th>
  </tr>
<?php
if ($result->num_rows() > 0) {
    $sn = 1;

    foreach($result->result_array() as $data) {
 ?>
 <tr>
   <td><?php echo $sn; ?> </td>
   <td><?php echo $data['customer_name']; ?> </td>
   <td><?php echo $data['company_name']; ?> </td>
<td>
<?php 
echo $data['mobile_no']; 
echo "<br><small>Normalized: ".substr(preg_replace('/\D/', '', $data['mobile_no']), -10)."</small>";
?>
</td>
   <td><?php echo $data['status']; ?> </td>
   <td><?php echo $data['assigned_to']; ?></td>
      <td>
     <form method="post" action="<?php echo base_url('delete_duplicate_lead'); ?>" onsubmit="return confirm('Delete this lead?');">
       <input type="hidden" name="id" value="<?php echo (int) $data['id']; ?>">
       <button type="submit" class="btn btn-danger btn-sm">Delete</button>
     </form>
   </td>
 <tr>
 <?php
  $sn++;}} else { ?>
    <tr>
     <td colspan="8">No data found</td>
    </tr>
 <?php } ?>
  </table>    
              </div>
            </div>
           </div>
          </div>   
        </div>
         
  <script>
$(document).ready(function(){
    var table = $('#order_creation_tables').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "orders.php",
            "type": "POST",
            "data": function(d){
                d.agents = $('#agent_filter').val();
                d.shipment_status = $('#status_filter').val();
            }
        },
        "pageLength": 50,
        "bLengthChange": false,
        "order": []
    });

    $('#apply_filter').click(function(){
        table.ajax.reload();
    });

    $('#reset_filter').click(function(){
        $('#agent_filter').val([]);
        $('#status_filter').val([]);
        table.ajax.reload();
    });
});
</script>