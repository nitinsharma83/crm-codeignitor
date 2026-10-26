
        <?php
// DB Connection
$servername = "localhost";
$username   = "u457158986_team";
$password   = "#Team@123";
$dbname     = "u457158986_team";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

// Session values
$usertype   = $this->session->userdata('USERTYPE');
$loginUser  = $this->session->userdata('USERNAME');

// Fetch agents (only for Admin)
$agents = [];
if ($usertype === 'Admin') {
    $res = $conn->query("SELECT DISTINCT username FROM tbl_orders WHERE username <> '' ORDER BY username ASC");
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $agents[] = $row['username'];
        }
    }
}

// Fetch shipment_status for dropdown
$statuses = [];
$res2 = $conn->query("SELECT DISTINCT shipment_status FROM tbl_orders WHERE shipment_status <> '' ORDER BY shipment_status ASC");
if ($res2 && $res2->num_rows > 0) {
    while ($row = $res2->fetch_assoc()) {
        $statuses[] = $row['shipment_status'];
    }
}

// Handle filters
$where = " WHERE 1=1 ";

// If not Admin, force username condition (restrict employee data)
if ($usertype !== 'Admin') {
    $where .= " AND username = '".$conn->real_escape_string($loginUser)."' ";
}

// If Admin selects an agent
if (!empty($_POST['agent']) && $usertype === 'Admin') {
    $agent = $conn->real_escape_string($_POST['agent']);
    $where .= " AND username = '$agent' ";
}

// Shipment status filter
if (!empty($_POST['shipment_status'])) {
    $status = $conn->real_escape_string($_POST['shipment_status']);
    $where .= " AND shipment_status = '$status' ";
}

// Customer name filter
if (!empty($_POST['customer_name'])) {
    $cname = $conn->real_escape_string($_POST['customer_name']);
    $where .= " AND customer_name LIKE '%$cname%' ";
}

// Mobile number filter
if (!empty($_POST['mobile_no'])) {
    $mobile = $conn->real_escape_string($_POST['mobile_no']);
    $where .= " AND (phone LIKE '%$mobile%' OR alt_number LIKE '%$mobile%') ";
}

// Fetch orders
$sql = "SELECT id, lead_id, customer_name, phone, alt_number, state, address, post_office, district, landmark, 
               pin_code, medicine, price, totalprice, discount_type, discount_percent, comment, status, order_date, 
               userid, username, waybill_no, shipment_status, api_response
        FROM tbl_orders $where ORDER BY id DESC";

$result = $conn->query($sql);
?>
<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f9f9f9; }
    h2 { margin-bottom: 20px; }
    .filter-box { background: #fff; padding: 15px; margin-bottom: 20px; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .filter-box label { margin-right: 10px; font-weight: bold; }
    .filter-box select, .filter-box input, .filter-box button { padding: 6px 10px; margin-right: 15px; border-radius: 4px; border: 1px solid #ccc; }
    .filter-box button { background: #007bff; color: #fff; border: none; cursor: pointer; }
    .filter-box button:hover { background: #0056b3; }
    table.dataTable thead th { background: #007bff; color: white; }
    table.dataTable tbody tr:nth-child(even) { background: #f2f2f2; }
    table { border: 1px solid grey !important; }
</style>
        <div class="content-wrapper">
          <div class="page-header">
            <h3 class="page-title">
              <span class="page-title-icon bg-gradient-primary text-white mr-2">
                <i class="fa fa-users"></i>                 
              </span>
            Order
            </h3>
          </div>
            <form method="POST" action="" class="filter-box">
        <?php if ($usertype === 'Admin'): ?>
            <label>Agent:</label>
            <select name="agent">
                <option value="">-- All Agents --</option>
                <?php foreach ($agents as $a): ?>
                    <option value="<?php echo htmlspecialchars($a); ?>" 
                        <?php if (!empty($_POST['agent']) && $_POST['agent'] == $a) echo "selected"; ?>>
                        <?php echo htmlspecialchars($a); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <label>Shipment Status:</label>
        <select name="shipment_status">
            <option value="">-- All Statuses --</option>
            <?php foreach ($statuses as $s): ?>
                <option value="<?php echo htmlspecialchars($s); ?>" 
                    <?php if (!empty($_POST['shipment_status']) && $_POST['shipment_status'] == $s) echo "selected"; ?>>
                    <?php echo htmlspecialchars($s); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Customer Name:</label>
        <input type="text" name="customer_name" value="<?php echo !empty($_POST['customer_name']) ? htmlspecialchars($_POST['customer_name']) : ''; ?>" placeholder="Search by name">

        <label>Mobile No:</label>
        <input type="text" name="mobile_no" value="<?php echo !empty($_POST['mobile_no']) ? htmlspecialchars($_POST['mobile_no']) : ''; ?>" placeholder="Search by mobile">

        <button type="submit">Search</button>
    </form>
          <div class="row">
           <div class="col-md-12">

            <?php if(isset($_GET['view'])){ 
                $id= base64_decode($_GET['view']); 
               $q=$this->db->query("select  *from tbl_orders where order_id='".$order_id."'")->result();
                ?>   
             <div class="card">
            <div class="card-header">
                <i class="fa fa-eye"></i> View Lead Details <a href="<?php echo site_url(); ?>create_order" class="btn btn-gradient-danger btn-rounded btn-fw btn-sm"> Back</a>
            </div>
              <div class="card-body">
              <table class="table table-bordered">
                  <tbody>
                      
                        <tr>
                        <th>Customer Name</th>
                       <td><?php echo $q[0]->customer_name; ?></td>
                        </tr>
                        <tr>
                        <th>Company Name</th>
                       <td><?php echo $q[0]->phone; ?></td>
                        </tr>
                       
                       
                     
                  </tbody>   
              </table>
              </div>     
            </div>
            <?php 
}elseif(isset($_GET['update'])){  
    $id = base64_decode($_GET['update']); 
    $q = $this->db->query("SELECT * FROM tbl_orders WHERE id='".$id."'")->result();
?>
<div class="card">
    <div class="card-header">
        <i class="fa fa-shopping-cart"></i> Update Order
    </div>
    <div class="card-body">
        <?php if(isset($_GET['status'])): ?>
    <?php if($_GET['status'] == 'success'): ?>
        <div class="alert alert-success">✅ Order updated successfully!</div>
    <?php elseif($_GET['status'] == 'error'): ?>
        <div class="alert alert-danger">❌ Update failed: <?php echo $_GET['msg'] ?? 'Unknown error'; ?></div>
    <?php endif; ?>
<?php endif; ?>
        <div class="alert alert-danger" id="error_message_change" style="display:none;"></div> 
        <div class="alert alert-success" id="success_message_submit" style="display:none;"></div> 
        <?php
        
        require_once "db.php";
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect POST data
    $id               = mysqli_real_escape_string($conn, $_POST['id']);
    $customer_name    = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $phone            = mysqli_real_escape_string($conn, $_POST['phone']);
    $alt_number       = mysqli_real_escape_string($conn, $_POST['alt_number']);
    $state            = mysqli_real_escape_string($conn, $_POST['state']);
    $address          = mysqli_real_escape_string($conn, $_POST['address']);
    $post_office      = mysqli_real_escape_string($conn, $_POST['post_office']);
    $district         = mysqli_real_escape_string($conn, $_POST['district']);
    $landmark         = mysqli_real_escape_string($conn, $_POST['landmark']);
    $pin_code         = mysqli_real_escape_string($conn, $_POST['pin_code']);
    $medicine         = mysqli_real_escape_string($conn, $_POST['medicine']);
    $price            = mysqli_real_escape_string($conn, $_POST['price']);
    $totalprice            = mysqli_real_escape_string($conn, $_POST['totalprice']);
    $discount_type    = mysqli_real_escape_string($conn, $_POST['discount_type']);
    $discount_percent = mysqli_real_escape_string($conn, $_POST['discount_percent']);
    $comment          = mysqli_real_escape_string($conn, $_POST['comment']);
    $waybill_no       = mysqli_real_escape_string($conn, $_POST['waybill_no']);
    $status       = mysqli_real_escape_string($conn, $_POST['status']);

    // Prepare UPDATE query (not touching status, order_date, userid, username, shipment_status, api_response)
    $sql = "UPDATE tbl_orders SET 
                customer_name    = '$customer_name',
                phone            = '$phone',
                alt_number       = '$alt_number',
                state            = '$state',
                address          = '$address',
                post_office      = '$post_office',
                district         = '$district',
                landmark         = '$landmark',
                pin_code         = '$pin_code',
                medicine         = '$medicine',
                price            = '$price',
                 totalprice            = '$totalprice',
                discount_type    = '$discount_type',
                discount_percent = '$discount_percent',
                comment          = '$comment',
                waybill_no       = '$waybill_no',
                status       = '$status'
            WHERE id = '$id'";

  if ($conn->query($sql) === TRUE) {
    // ✅ Redirect back to create_order with success
    header("Location: create_order?update=" . base64_encode($id) . "&status=success");
    exit();
} else {
    // ✅ Redirect back to create_order with error
    header("Location: create_order?update=" . base64_encode($id) . "&status=error&msg=" . urlencode($conn->error));
    exit();
}


    echo json_encode($response);
}

$conn->close(); ?>
        <form method="post">
            <input type="hidden" value="<?php echo $id; ?>" id="id" name="id"/>
            
            <div class="row">  
                <div class="form-group col-md-6">
                    <label>Customer Name</label>  
                    <input type="text" class="form-control" value="<?php echo $q[0]->customer_name; ?>" name="customer_name" id="customer_name" placeholder="Customer Name">
                </div>
                <div class="form-group col-md-6">
                    <label>Phone</label>  
                    <input type="text" class="form-control" value="<?php echo $q[0]->phone; ?>" name="phone" id="phone" placeholder="Phone Number">
                </div>
            </div>
            
            <div class="row">  
                <div class="form-group col-md-6">
                    <label>Alternate Number</label>  
                    <input type="text" class="form-control" value="<?php echo $q[0]->alt_number; ?>" name="alt_number" id="alt_number" placeholder="Alternate Number">
                </div>
                <div class="form-group col-md-6">
                    <label>State</label>  
                    <input type="text" class="form-control" value="<?php echo $q[0]->state; ?>" name="state" id="state" placeholder="State">
                </div>
            </div>
            
            <div class="row">  
                <div class="form-group col-md-6">
                    <label>Address</label>  
                    <input type="text" class="form-control" value="<?php echo $q[0]->address; ?>" name="address" id="address" placeholder="Address">
                </div>
                <div class="form-group col-md-6">
                    <label>Post Office</label>  
                    <input type="text" class="form-control" value="<?php echo $q[0]->post_office; ?>" name="post_office" id="post_office" placeholder="Post Office">
                </div>
            </div>
            
            <div class="row">  
                <div class="form-group col-md-6">
                    <label>District</label>  
                    <input type="text" class="form-control" value="<?php echo $q[0]->district; ?>" name="district" id="district" placeholder="District">
                </div>
                <div class="form-group col-md-6">
                    <label>Landmark</label>  
                    <input type="text" class="form-control" value="<?php echo $q[0]->landmark; ?>" name="landmark" id="landmark" placeholder="Landmark">
                </div>
            </div>
            
            <div class="row">  
                <div class="form-group col-md-6">
                    <label>Pin Code</label>  
                    <input type="text" class="form-control" value="<?php echo $q[0]->pin_code; ?>" name="pin_code" id="pin_code" placeholder="Pin Code">
                </div>
                <div class="form-group col-md-6">
                    <label>Medicine</label>  
                    <input type="text" class="form-control" value="<?php echo $q[0]->medicine; ?>" name="medicine" id="medicine" placeholder="Medicine">
                </div>
            </div>
            
            <div class="row">  
                <div class="form-group col-md-6">
                    <label>COD</label>  
                    <input type="text" class="form-control" value="<?php echo $q[0]->price; ?>" name="price" id="price" placeholder="Price">
                </div>
                 <div class="form-group col-md-6">
                    <label>Total Ammount</label>  
                    <input type="text" class="form-control" value="<?php echo $q[0]->totalprice; ?>" name="totalprice" id="totalprice" placeholder="Total Amount">
                </div>
                <div class="form-group col-md-6">
                    <label>Discount Type</label>  
                    <input type="text" class="form-control" value="<?php echo $q[0]->discount_type; ?>" name="discount_type" id="discount_type" placeholder="Discount Type">
                </div>
            </div>
            
            <div class="row">  
                <div class="form-group col-md-6">
                    <label>Discount Percent</label>  
                    <input type="text" class="form-control" value="<?php echo $q[0]->discount_percent; ?>" name="discount_percent" id="discount_percent" placeholder="Discount Percent">
                </div>
                <div class="form-group col-md-6">
                    <label>Comment</label>  
                    <textarea class="form-control" name="comment" id="comment" placeholder="Comment"><?php echo $q[0]->comment; ?></textarea>
                </div>
            </div>
            
          
       
            <div class="row">  
                <div class="form-group col-md-6">
                    <label>Waybill No</label>  
                    <input type="text" class="form-control" value="<?php echo $q[0]->waybill_no; ?>" name="waybill_no" id="waybill_no" placeholder="Waybill No" readonly>
                </div>
               <div class="form-group col-md-6">
    <label>Status</label>  
    <select class="form-control" name="status" id="status">
        <option value="Verified" <?php echo ($q[0]->status == "Verified") ? "selected" : ""; ?>>Verified</option>
        <option value="Hold" <?php echo ($q[0]->status == "Hold") ? "selected" : ""; ?>>Hold</option>
        <option value="Unhold" <?php echo ($q[0]->status == "Unhold") ? "selected" : ""; ?>>Unhold</option>
        <option value="Cancel" <?php echo ($q[0]->status == "Cancel") ? "selected" : ""; ?>>Cancel</option>
    </select>
</div>

            </div>
            
          
            
            <div class="form-group col-md-2">    
                <button type="submit" id="submit_button" class="btn btn-gradient-primary mr-2">Update Order</button>
            </div>
        </form>
    </div>
</div>


            <?php }else{ ?>  
                <div class="card">
           
              <div class="card-body">
                  
                 <?php if($this->session->userdata('success')){ ?>
                  <div class="alert alert-success">
                      <?php echo $this->session->userdata('success'); ?>
                  </div>
                 <?php $this->session->unset_userdata('success'); } ?>
                  <div class="table-responsive">
                     <table id="ordersTable" class="table table-hover table-bordered align-middle table-custom">
            <thead>
                <tr>
                    
                    <th>Lead ID</th>
                    <th>Shipment Status</th>
                    <th>Customer Name</th>
                    <th>Phone</th>
                    <th>Alt Phone</th>
                    <th>State</th>
                    <th>Address</th>
                    <th>Post Office</th>
                    <th>District</th>
                    <th>Landmark</th>
                    <th>Pincode</th>
                    <th>Medicine</th>
                    <th>COD</th>
                    <th>Totol Ammount</th>
                    <th>Discount Type</th>
                    <th>Discount %</th>
                    <th>Comment</th>
                    <th>Status</th>
                    <th>Order Date</th>
                    <th>Agent</th>
                    <th>Waybill No</th>
                    
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                          
                            <td><?php echo $row['lead_id']; ?></td>
                             <td>
  <a href="track_order.php?waybill=<?php echo urlencode($row['waybill_no']); ?>">
    <?php echo $row['shipment_status']; ?>
  </a>
</td>
                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                           <td>
    <?php echo $row['phone']; ?>
    <a href="call_lead.php?call=<?php echo base64_encode($row['lead_id']); ?>" 
       class="call-btn" 
       title="Click to Call">
        <i class="fa fa-phone"></i> Call
    </a>
</td>
                            <td><?php echo $row['alt_number']; ?></td>
                            <td><?php echo $row['state']; ?></td>
                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                            <td><?php echo $row['post_office']; ?></td>
                            <td><?php echo $row['district']; ?></td>
                            <td><?php echo $row['landmark']; ?></td>
                            <td><?php echo $row['pin_code']; ?></td>
                            <td><?php echo $row['medicine']; ?></td>
                            <td><?php echo $row['price']; ?></td>
                            <td><?php echo $row['totalprice']; ?></td>
                            <td><?php echo $row['discount_type']; ?></td>
                            <td><?php echo $row['discount_percent']; ?></td>
                            <td><?php echo htmlspecialchars($row['comment']); ?></td>
                            <td><?php echo $row['status']; ?></td>
                            <td><?php echo $row['order_date']; ?></td>
                            <td><?php echo $row['username']; ?></td>
                            <td><?php echo $row['waybill_no']; ?></td>
                           
                             <td>
            <a class="btn btn-gradient-primary btn-rounded btn-fw btn-sm" 
               href="<?php echo site_url().'create_order?update='.base64_encode($row['id']); ?>" 
               title="Edit">
                <i class="fa fa-edit"></i>
            </a>
        </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="21">No records found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
                  </div>
              </div>
            </div>
            <?php } ?>
           </div>
          </div>   
        </div>
     
     <script>
         $(document).ready(function() {
    $('#ordersTable').DataTable({
        "pageLength": 10,
        "lengthChange": false,
        "ordering": true,
        "searching": false, // we use custom filters
        "responsive": true
    });
});
$(document).ready(function(){

    // Search Lead by Mobile
    $("#search_btn").click(function(){
        var mobile = $("#search_mobile").val();
        if(mobile == ""){
            alert("Enter mobile number");
            return;
        }
        $.post("<?= base_url('OrderController/fetch_lead') ?>",{mobile_no:mobile}, function(res){
            var data = JSON.parse(res);
            if(data.status == 200){
                $("#name").val(data.data.customer_name);
                $("#phone").val(data.data.mobile_no);
                $("#alt_number").val(data.data.secondary_mobile_no);
                $("#state").val(data.data.state);
                $("#search_message").text("Lead Found ✅").css("color","green");
            } else {
                $("#search_message").text("No Lead Found ❌").css("color","red");
            }
        });
    });

    // Submit Order
    $("#orderForm").submit(function(e){
        e.preventDefault();
        $.post("<?= base_url('OrderController/order_creation_process') ?>", $(this).serialize(), function(res){
            var data = JSON.parse(res);
            alert(data.message);
            if(data.code == 200){
                $("#orderForm")[0].reset();
            }
        });
    });
});
</script>