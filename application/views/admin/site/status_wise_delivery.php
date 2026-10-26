
        <div class="content-wrapper">
          <div class="page-header">
            <h3 class="page-title">
              <span class="page-title-icon bg-gradient-primary text-white mr-2">
                <i class="fa fa-users"></i>                 
              </span>
           <?php echo $_GET['shipment_status']; ?> Lead
            </h3> 
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
               $q=$this->db->query("select  *from tbl_lead where id='".$id."'")->result();
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
                <input type="text" class="form-control" value="<?php echo $q[0]->lead_owner; ?>" name="lead_owner" id="lead_owner" placeholder="Lead Owner">
                </div>
                 <div class="form-group col-md-6">
                <label>Name </label>  
                <input type="text" class="form-control" value="<?php echo $q[0]->name; ?>" name="name" id="name" placeholder="Name">
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
               $q=$this->db->query("select  *from tbl_lead where id='".$id."'")->result();
                ?>  
               <div class="row">
                <div class="col-md-6">
             <div class="card">
            <div class="card-header">
                <i class="fa fa-eye"></i> View Lead Details <a href="<?php echo site_url(); ?>detail_order_details?status=<?php echo $_GET['shipment_status']; ?>" class="btn btn-gradient-danger btn-rounded btn-fw btn-sm"> Back</a>
            </div>
                 
              <div class="card-body">
                  <div class="table-responsive">
                <table class="table table-bordered">
                  <tbody>
                      <tr>
                        <th>Lead ID</th>
                       <td><?php echo $q[0]->lead_id; ?></td>
                        </tr> 
                        <tr>
                        <th>Customer Name</th>
                       <td><?php echo $q[0]->customer_name; ?></td>
                        </tr>
                        <tr>
                        <th>Age/Height</th>
                       <td><?php echo $q[0]->company_name; ?></td>
                        </tr>
                        <tr>
                        <th>Customer Mobile No</th>
                       <td><?php echo $q[0]->mobile_no; ?> <?php if(!empty($q[0]->secondary_mobile_no)){ echo ', '.$q[0]->secondary_mobile_no; } ?></td>
                        </tr>
                          <tr>
                        <th>Email ID</th>
                       <td><?php echo $q[0]->email_id; ?></td>
                        </tr>
                         <tr>
                        <th>Address</th>
                       <td><?php echo $q[0]->city; if(!empty($q[0]->state)){ echo ','.  $q[0]->state; } ?></td>
                        </tr>
                         <tr>
                        <th>Lead Source</th>
                       <td><?php echo $q[0]->lead_source; ?></td>
                        </tr>
                         <tr>
                        <th>Lead Status</th>
                       <td><?php echo $q[0]->status; ?></td>
                        </tr>
                         <tr>
                        <th>Assigned By</th>
                       <td><?php echo $q[0]->assigned_by; ?></td>
                        </tr>
                        <tr>
                        <th>Assigned To</th>
                       <td><?php echo $q[0]->assigned_to; ?></td>
                        </tr>
                        <tr>
                        <th>Follow Up Date</th>
                       <td><?php echo $q[0]->tarik; ?></td>
                        </tr>
                         <tr>
                        <th>Lead Requirement</th>
                       <td><?php echo $q[0]->requirement; ?></td>
                        </tr>
                       
                      <tr><th>Description</th>
                          <td colspan="3"><?php echo $q[0]->description; ?></td>
                      </tr>
                  </tbody>   
              </table>
                  </div>
                 </div>  
             </div> 
                </div>   
                   <div class="col-md-6">
                    <div class="card">
            <div class="card-header">
                <i class="fa fa-comment"></i> Lead Comment <a href="<?php echo site_url(); ?>detail_order_details?status=<?php echo $_GET['shipment_status']; ?>" class="btn btn-gradient-danger btn-rounded btn-fw btn-sm"> Back</a>
            </div>
                 
              <div class="card-body">
                 <form method="post" id="submited_comment_form">
                     <input type="hidden" name="lead_id" id="lead_id" value="<?php echo base64_decode($_GET['view']); ?>"/>
                     <input type="hidden" name="lead_title" value="<?php echo $q[0]->title; ?>"/>
                      <input type="hidden" name="userid" value="<?php echo $q[0]->userid; ?>"/>
                       <input type="hidden" name="lead_id_ID" value="<?php echo $q[0]->lead_id; ?>"/>
                      
                  <div class="alert alert-danger" id="error_message_change" style="display:none;"></div> 
                <div class="alert alert-success" id="success_message_submit" style="display:none;"></div> 
                    <br>
                 <div class="row">
                 <div class="col-md-6">
                 <textarea type="text" id="description" class="form-control" name="description"><?php echo $q[0]->description; ?></textarea>
                 </div>
                 <div class="col-md-6">
                   <input type="date" id="tarik" name="tarik" class="form-control"  />
                 </div>
                     <div class="col-md-12">
                         <textarea id="comment" name="comment" placeholder="Comment" class="form-control"></textarea>   
                     </div> 

                 </div>
                    <div class="row">
                     <div class="col-md-6">
                         <label>Select Lead Status</label>
                         <select id="lead_statuss" name="lead_statuss" placeholder="Comment" class="form-control">
                             <option value="<?php echo $q[0]->status; ?>"><?php echo $q[0]->status; ?></option>   
                             <?php foreach($status as $row){ if($q[0]->status!=$row->status){ ?>
                             <option value="<?php echo $row->status; ?>"><?php echo $row->status; ?></option>     
                             <?php } } ?>
                         </select>   
                     </div>
                        <div class="col-md-6"><br>
                             <button type="submit" id="submit_button" class="btn btn-gradient-primary mr-2">Submit Comment</button>
                        </div>   
                 </div>
                </form>
                  
                  
         <div class="row" id="get_recent_comment">
              <?php if(count($lead_data)>=1){ ?>
            <div class="card-body panel-default widget">
            <div class="panel-heading">
                <span class="glyphicon glyphicon-comment"></span>
                <h3 class="panel-title">
                    Recent Comments</h3>
                <!--<span class="label label-info">78</span>-->
            </div>
            <div class="panel-body">
                <ul class="list-group">
                  <?php foreach($lead_data as $row){ ?>  
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-xs-10 col-md-11">
                                <div>
                                    <a href="#">At that time status was: <b><?php echo $row->status; ?></b></a>
                                    <div class="mic-info">
                                        By: <a href="#"><?php echo $row->username; ?></a> on <?php echo $row->created_date; ?>
                                    </div>
                                </div>
                                <div class="comment-text">
                                   <?php echo $row->comment; ?>
                                </div>
                               
                            </div>
                        </div>
                    </li>
                  <?php } ?>
                </ul>
            </div>
        </div>
<?php } ?>
    </div>
              </div></div></div>
                 </div>
                 
                 <div class="row">
                     <div class="col-md-6"><?php
if (isset($_POST['pincode'])) {
    $pincode = $_POST['pincode'];

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://track.delhivery.com/c/api/pin-codes/json/?filter_codes=" . $pincode,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Authorization: Token c0a7e816bb48dc87a944629f6ed1a7951422d023"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        $result = "cURL Error #:" . $err;
    } else {
        $data = json_decode($response, true);

        if (isset($data['delivery_codes'][0]['postal_code']['pin'])) {
            $result = "✅Hurreeyyyyy! Pincode <b>{$pincode}</b> is serviceable.";
        } else {
            $result = "❌ Pincode <b>{$pincode}</b> is NOT serviceable.";
        }
    }
}
?>
                          <div class="container mt-5">
    <div class="card p-4 shadow">
      <h3 class="mb-3">Check Pincode Serviceability</h3>
      <form method="post">
        <div class="mb-3">
          <label for="pincode" class="form-label">Enter Pincode</label>
          <input type="text" class="form-control" id="pincode" name="pincode" required>
        </div>
        <button type="submit" class="btn btn-primary">Check</button>
      </form>
      <div class="mt-3">
        <?php if (!empty($result)) { echo "<div class='alert alert-info'>$result</div>"; } ?>
      </div>
    </div>
  </div>
                     </div>
                      <div class="col-md-6">
                           
                    <div class="card">
            <div class="card-header">
                <i class="fa fa-comment"></i> Submit Order<a href="<?php echo site_url(); ?>detail_order_details?status=<?php echo $_GET['shipment_status']; ?>" class="btn btn-gradient-danger btn-rounded btn-fw btn-sm"> Back</a>
            </div>
                 
              <div class="card-body">
                 <form method="post" action="submit_order.php">
    <input type="hidden" name="lead_id" id="lead_id" value="<?php echo base64_decode($_GET['view']); ?>"/>
    <input type="hidden" name="userid" value="<?php echo $q[0]->userid; ?>"/>
    <input type="hidden" name="username" value="<?php echo $q[0]->assigned_by; ?>"/>
    <input type="hidden" name="lead_id_ID" value="<?php echo $q[0]->lead_id; ?>"/>

    <div class="alert alert-danger" id="error_message_change" style="display:none;"></div> 
    <div class="alert alert-success" id="success_message_submit" style="display:none;"></div> 

    <br>
    <div class="row">
        <!-- Name -->
        <div class="col-md-6">
            <label>Customer Name</label>
            <input type="text" class="form-control" name="customer_name" value="<?php echo $q[0]->customer_name; ?>" required />
        </div>

        <!-- Phone -->
        <div class="col-md-6">
            <label>Phone</label>
            <input type="text" class="form-control" name="phone" value="<?php echo $q[0]->mobile_no; ?>" required />
        </div>

        <!-- Alt Number -->
        <div class="col-md-6">
            <label>Alternate Number</label>
            <input type="text" class="form-control" name="alt_number" value="<?php echo $q[0]->secondary_mobile_no; ?>" />
        </div>

        <!-- Medicine -->
        <div class="col-md-6">
            <label>Medicine</label>
           <input type="text" class="form-control" name="medicine" required />
        </div>

        <!-- Price -->
        <div class="col-md-6">
            <label>Price</label>
            <input type="number" class="form-control" name="price" step="0.01" required />
        </div>

        <!-- Discount Yes/No -->
        <div class="col-md-6">
            <label>Discount</label>
            <select class="form-control" name="discount_type" id="discount_type">
                <option value="No">No</option>
                <option value="Yes">Yes</option>
            </select>
        </div>

        <!-- Discount Percent -->
        <div class="col-md-6" id="discount_percent_box" style="display:none;">
            <label>Discount (%)</label>
            <input type="number" class="form-control" name="discount_percent" min="0" max="100" />
        </div>

        <!-- State -->
        <div class="col-md-6">
            <label>State</label>
            <input type="text" class="form-control" name="state" value="<?php echo $q[0]->state; ?>" />
        </div>

        <!-- Address -->
        <div class="col-md-12">
            <label>Address</label>
            <textarea class="form-control" name="address"><?php echo $q[0]->city; ?></textarea>
        </div>

        <!-- Post Office -->
        <div class="col-md-6">
            <label>Post Office</label>
            <input type="text" class="form-control" name="post_office" />
        </div>

        <!-- District -->
        <div class="col-md-6">
            <label>District</label>
            <input type="text" class="form-control" name="district" value="<?php echo $q[0]->city; ?>" />
        </div>

        <!-- Landmark -->
        <div class="col-md-6">
            <label>Landmark</label>
            <input type="text" class="form-control" name="landmark" />
        </div>

        <!-- Pincode -->
        <div class="col-md-6">
            <label>Pin Code</label>
            <input type="text" class="form-control" name="pin_code" />
        </div>

        <!-- Comment -->
        <div class="col-md-12">
            <label>Comment</label>
            <textarea class="form-control" name="comment"></textarea>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mt-3">
            <input type="submit" id="submit" class="btn btn-gradient-primary" />
        </div>
    </div>
</form>

                  
                
              </div></div></div>
                     
                 </div>
                 
                 
                 
              
            <div class="row">
                 <div class="col-md-12">
                 <?php
date_default_timezone_set("Asia/Kolkata");

// DB connection
$servername = "localhost";
$username   = "u457158986_team";
$password   = "#Team@123";
$dbname     = "u457158986_team";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check DB connection
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}
// ---------------- Handle Cancel Shipment ----------------
if (isset($_POST['cancel_order_id']) && isset($_POST['waybill_no'])) {
    $order_id   = intval($_POST['cancel_order_id']);
    $waybill_no = $_POST['waybill_no'];

    $payload = [
        "waybill"      => $waybill_no,
        "cancellation" => "true"
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://track.delhivery.com/api/p/edit",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            "Accept: application/json",
            "Authorization: Token c0a7e816bb48dc87a944629f6ed1a7951422d023", // replace with production token
            "Content-Type: application/json"
        ],
    ]);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        $conn->query("UPDATE tbl_orders SET shipment_status='Cancel API Error', api_response='" . $conn->real_escape_string($err) . "' WHERE id=$order_id");
        echo "<div class='alert alert-danger'>❌ Cancel API Error: $err</div>";
    } else {
        $result = json_decode($response, true);

        if ((isset($result['status']) && $result['status'] == true) || 
    (isset($result['success']) && $result['success'] == true)) {
    
    $conn->query("UPDATE tbl_orders SET shipment_status='Cancelled', api_response='" . $conn->real_escape_string($response) . "' WHERE id=$order_id");
    echo "<div class='alert alert-success'>✅ Shipment cancelled successfully. Remark: " . $result['remark'] . " (Waybill: " . $result['waybill'] . ")</div>";
} else {
    $conn->query("UPDATE tbl_orders SET shipment_status='Cancel Failed', api_response='" . $conn->real_escape_string($response) . "' WHERE id=$order_id");
    echo "<div class='alert alert-warning'>❌ Cancel shipment failed. Response: " . htmlspecialchars($response) . "</div>";
}

    }
}

// ---------------- Fetch Orders ----------------
$sql = "SELECT * FROM tbl_orders ORDER BY id DESC LIMIT 20"; // adjust query as needed
$result = $conn->query($sql);

// Get filter values (from $q array)
$phone = $q[0]->mobile_no;
$userid        = $q[0]->userid;

// Prepare statement
$stmt = $conn->prepare("SELECT * FROM tbl_orders 
    WHERE phone = ? 
    ORDER BY order_date DESC");
$stmt->bind_param("s", $phone);
$stmt->execute();
$result = $stmt->get_result();
                  ?>
                  
                 <table class="table table-bordered table-striped mt-3">
    <thead class="table-dark">
        <tr>
            <th>Order ID</th>
            <th>Lead ID</th>
            <th>Customer Name</th>
            <th>Phone</th>
            <th>Medicine</th>
            <th>Price</th>
            <th>Discount</th>
            <th>Comment</th>
            <th>Order Date</th>
            <th>Waybill</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['lead_id']; ?></td>
                    <td><?php echo $row['customer_name']; ?></td>
                    <td><?php echo $row['phone']; ?></td>
                    <td><?php echo $row['medicine']; ?></td>
                    <td><?php echo $row['price']; ?></td>
                    <td>
                        <?php echo ($row['discount_type'] == 'Yes') ? $row['discount_percent']."%" : "No"; ?>
                    </td>
                    <td><?php echo $row['comment']; ?></td>
                    <td><?php echo $row['order_date']; ?></td>
                    <td><?php echo $row['waybill_no']; ?></td>
                    <td>
                        <?php if ($row['shipment_status'] == 'Created'): ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="cancel_order_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="waybill_no" value="<?php echo $row['waybill_no']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Are you sure you want to cancel this shipment?');">
                                    Cancel Shipment
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="badge bg-secondary"><?php echo $row['shipment_status']; ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="11" class="text-center">⚠️ No orders found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php $stmt->close();
$conn->close(); ?>
              </div>
            </div>
            <?php }else{ ?>  
            <input type="hidden" name="shipment_status" id="shipment_status" 
       value="<?php echo isset($_GET['shipment_status']) ? $_GET['shipment_status'] : ''; ?>">


                <div class="card">
            <div class="card-header">
                <i class="mdi mdi-table-large"></i><?php echo $_GET['shipment_status']; ?> Order List <!--<a href="<?php echo site_url() ?>create_lead?add=new" class="btn btn-gradient-info btn-sm"> Add Lead <i class="fa fa-plus"></i></a>-->
            </div>
              <div class="card-body">
                  
                 <?php if($this->session->userdata('success')){ ?>
                  <div class="alert alert-success">
                      <?php echo $this->session->userdata('success'); ?>
                  </div>
                 <?php $this->session->unset_userdata('success'); } ?>
                  <div class="table-responsive">
                      <table class="table table-bordered" id="all_delivery_details">
                          <thead>
  <tr>
   
    <th>Customer Name</th>
    <th>Customer Mobile</th>
  
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
         
     