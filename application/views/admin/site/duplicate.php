
         <div class="content-wrapper">
          <div class="page-header">
            <h3 class="page-title">
              <span class="page-title-icon bg-gradient-primary text-white mr-2">
                <i class="fa fa-clock-o"></i>
              </span>
            Duplicates
            </h3>
          </div>
          <div class="row">
           <div class="col-md-12">
                <div class="card">
            <div class="card-header">
                <i class="mdi mdi-watch"></i> Duplicates
                    </div>
            </div>
              <div class="card-body">
<?php

$username = $this->session->userdata('USERNAME');

if ($username == 'Admin')
{
    // Admin: duplicates inside the same agent's own leads, for every agent
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
            RIGHT(REPLACE(REPLACE(mobile_no, '+', ''), ' ', ''), 10) AS normalized_mobile
        FROM tbl_lead
        GROUP BY assigned_to, normalized_mobile
        HAVING COUNT(*) > 1

    ) t2

    ON t1.assigned_to = t2.assigned_to
    AND RIGHT(REPLACE(REPLACE(t1.mobile_no, '+', ''), ' ', ''), 10) = t2.normalized_mobile

    ORDER BY t1.assigned_to ASC, t1.id ASC
    ";
}
else
{
    // Agent: own leads whose phone number appears on more than one lead anywhere in the CRM
    $u = $this->db->escape($username);

    $query = "
    SELECT t1.id, t1.customer_name, t1.company_name, t1.mobile_no, t1.status, t1.assigned_to
    FROM tbl_lead t1
    INNER JOIN (
        SELECT RIGHT(REPLACE(REPLACE(mobile_no, '+', ''), ' ', ''), 10) AS nm
        FROM tbl_lead
        WHERE mobile_no <> ''
        GROUP BY nm
        HAVING COUNT(*) > 1
    ) d ON RIGHT(REPLACE(REPLACE(t1.mobile_no, '+', ''), ' ', ''), 10) = d.nm
    WHERE t1.assigned_to = $u
    ORDER BY t1.id ASC
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
if ($result && $result->num_rows() > 0) {
    $sn = 1;

    foreach ($result->result_array() as $data) {
 ?>
 <tr>
   <td><?php echo $sn; ?> </td>
   <td><?php echo htmlspecialchars($data['customer_name']); ?> </td>
   <td><?php echo htmlspecialchars($data['company_name']); ?> </td>
   <td>
     <?php
     echo htmlspecialchars($data['mobile_no']);
     echo "<br><small>Normalized: " . substr(preg_replace('/\D/', '', $data['mobile_no']), -10) . "</small>";
     ?>
   </td>
   <td><?php echo htmlspecialchars($data['status']); ?> </td>
   <td><?php echo htmlspecialchars($data['assigned_to']); ?></td>
   <td>
     <form method="post" action="<?php echo base_url('delete_duplicate_lead'); ?>" onsubmit="return confirm('Delete this lead?');">
       <input type="hidden" name="id" value="<?php echo (int) $data['id']; ?>">
       <button type="submit" class="btn btn-danger btn-sm">Delete</button>
     </form>
   </td>
 </tr>
 <?php
        $sn++;
    }
} else { ?>
    <tr>
     <td colspan="7">No data found</td>
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