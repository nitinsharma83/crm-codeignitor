
        <div class="content-wrapper">
          <div class="page-header">
            <h3 class="page-title">
              <span class="page-title-icon bg-gradient-primary text-white mr-2">
                <i class="fa fa-mobile"></i>                 
              </span>
            Dialer  <?php  $api = $this->session->userdata('LOCATION'); ?>
            <?php  $agent_id = $this->session->userdata('AGENT_ID'); ?>
            </h3>
          </div>
          <div class="row">
           <div class="col-md-12">
                <div class="card">
            <div class="card-header">
            <?php $username = $this->session->userdata('USERNAME'); ?>
                <i class="fa fa-mobile"></i> Dialer  
                <form name="form_main" method="post" >
                    

                    <div class="row">
                      <div class="col-md-12">
                        <br>
                      <h4>Enter Mobile No* <small>10 Digital Only</small> </h4>
                        <input class="form-control" type="number" name="mobile" id="mobile" placeholder="99999XXXXX" /><br>
                        <input  class="form-control" style="cursor: pointer;" type="submit" name="submit" id="submit" value="Call Now">

                      </div>
                  </form> 

                    
                    </div>
            </div>
            <?php
        include_once 'db_config.php';
        if (isset($_POST['submit'])) {
                $mobile = $_POST['mobile'];
        
                // Ensure the mobile number is valid and prepend +91
                if (!preg_match('/^\d{10}$/', $mobile)) {
                    echo '<div class="alert alert-danger mt-3">Invalid Mobile Number. Please enter a 10-digit number.</div>';
                } else {
                    $fullMobile = "91" . $mobile; // Add country code
        
                    // cURL request
                    $curl = curl_init();
                    curl_setopt_array($curl, [
                        CURLOPT_URL => "https://api-smartflo.tatateleservices.com/v1/click_to_call_support",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => json_encode([
                            'api_key' => $api,
                            'customer_number' => $fullMobile,
                            'get_call_id' => 1
                        ]),
                        CURLOPT_HTTPHEADER => [
                            "accept: application/json",
                            "content-type: application/json"
                        ],
                    ]);
        
                    $response = curl_exec($curl);
                    $err = curl_error($curl);
                    curl_close($curl);
        
                    // Display API response
                    if ($err) {
                        echo '<div class="alert alert-danger mt-3">Error: ' . htmlspecialchars($err) . '</div>';
                    } else {
                        $decodedResponse = json_decode($response, true);
                        $statusMessage = $decodedResponse['message'] ?? "No response message";
        
                        echo '<div class="alert alert-success mt-3">Response: ' . htmlspecialchars($statusMessage) . '</div>';
                    }
                }
            }
?>
<table border="2" cellspacing="0" cellpadding="10" width="100%">
    <thead>
        Call History <hr>
    </thead>
  <tr>
    <th width="10%">S.N </th>
    <th width="40%">Name</th>
    <th width="50%">Mobile</th>
  </tr>
<?php
if ($result->num_rows > 0) {
  $sn=1;
  while($data = $result->fetch_assoc()) {
 ?>
 <tr>
   <td><?php echo $sn; ?> </td>
   <td><?php echo $data['breaktime']; ?> </td>
   <td><?php echo $data['reason']; ?> </td>
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
         
       