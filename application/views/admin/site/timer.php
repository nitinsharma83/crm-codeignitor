
        <div class="content-wrapper">
          <div class="page-header">
            <h3 class="page-title">
              <span class="page-title-icon bg-gradient-primary text-white mr-2">
                <i class="fa fa-clock-o"></i>                 
              </span>
            Break <?php $username = $this->session->userdata('USERNAME'); ?>
            </h3>
          </div>
          <div class="row">
           <div class="col-md-12">
                <div class="card">
            <div class="card-header">
            <?php $username = $this->session->userdata('USERNAME'); ?>
                <i class="mdi mdi-watch"></i> Timer  
                <form name="form_main" method="post" >
                    <div style="display: none;">
                      <input type="text" id="hh" name="hh" />
                        <input type="text" id="mm" name="mm" />
                        <input type="text" id="ss" name="ss" />
                        <input type="text" id="ms" name="ms" />
                    <input type="text" id="username" name="username" value="<?php echo $username ?>" />

                    </div>

                    <div class="row">
                      <div class="col-md-6">
                        <br>
                      <h4>Select Reason</h4>
                        <select type="select" class="form-control" name="reason" required>
                          <option value="">Select Reason</option>
                          <option value="washroom">Washroom</option>
                          <option value="lunch">Lunch Time</option>
                          
                          <option value="Tea Time">Tea Time</option>
                        </select><br><br><br>
                        <input  class="form-control" style="cursor: pointer;" type="submit" name="submit" id="submit" value="Reset">

                      </div>
                  </form> 

                      <div class="col-md-6">
                        <div style="text-align: center; border:10px solid #293190; padding: 20px; border-radius: 20px; font-size: 50px; background-color: black; color: white;">
                        <span style="padding: 0px 20px;" id="hour">00</span>:<span  style="padding: 0px 20px;" id="minute">00</span>:<span  style="padding: 0px 20px;" id="second">00</span>:<span  style="padding: 0px 20px;" id="millisecond">00</span>
                        </div>
                        <br>
                        <button  class="form-control" type="button" name="start">Start</button>
         
                      </div>
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
              $tarik1 = date("Y-m-d");
$query = "SELECT breaktime, reason FROM timer WHERE username = '$username' AND tarik = '$tarik1' ORDER BY id DESC";
$result = $mysqli->query($query);
?>
<table border="2" cellspacing="0" cellpadding="10" width="100%">
  <tr>
    <th width="10%">S.N </th>
    <th width="40%">Break Time</th>
    <th width="50%">Reason</th>
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
         
        <script>
	"use strict";

let hour = 0;
let minute = 0;
let second = 0;
let millisecond = 0;

let cron;

document.form_main.start.onclick = () => start();
document.form_main.pause.onclick = () => pause();
document.form_main.reset.onclick = () => reset();

function start() {
  pause();
  cron = setInterval(() => { timer(); }, 10);
}

function pause() {
  clearInterval(cron);
}

function reset() {
  hour = 0;
  minute = 0;
  second = 0;
  millisecond = 0;
  document.getElementById('hour').innerText = '00';
  document.getElementById('minute').innerText = '00';
  document.getElementById('second').innerText = '00';
  document.getElementById('millisecond').innerText = '000';
}

function timer() {
  if ((millisecond += 10) == 1000) {
    millisecond = 0;
    second++;
  }
  if (second == 60) {
    second = 0;
    minute++;
  }
  if (minute == 60) {
    minute = 0;
    hour++;
  }
  document.getElementById('hour').innerText = returnData(hour);
  document.getElementById("hh").value = returnData(hour);
  document.getElementById('minute').innerText = returnData(minute);
  document.getElementById("mm").value = returnData(minute);
  document.getElementById('second').innerText = returnData(second);
  document.getElementById("ss").value = returnData(second);
  document.getElementById('millisecond').innerText = returnData(millisecond);
  document.getElementById("ms").value = returnData(millisecond);
}

function returnData(input) {
  return input > 10 ? input : `0${input}`
}
</script>