<?php 
if(!defined('BASEPATH')) EXIT('No direct script access allowed');
error_reporting(0);
class SupportController extends MY_Controller {
    public function __construct() {
        
        
        
    parent::__construct();
    $this->load->model('Support_Model');
    $this->load->model("GenericModel");
    $this->load->library('user_agent'); // ADD THIS
    if ($this->session->userdata('ADMIN_ID')) {

    $admin_id = $this->session->userdata('ADMIN_ID');

    $checkUser = $this->db
        ->where('id', $admin_id)
        ->get('tbl_registration')
        ->row();

    // USER INACTIVE OR DELETED
    if (!$checkUser || $checkUser->status != 'active') {

        $this->session->sess_destroy();

        redirect('admin-login');
        exit;
    }
}
}
    public function index(){
    $data['template']='index';
    if($this->session->userdata('USERTYPE')=='Employee'){
    $userid=$this->session->userdata('ADMIN_ID');   
    $data['leads']=$this->GenericModel->get_record_with_condition("tbl_lead","SUM(CASE WHEN status = 'Contacted' THEN 1 ELSE 0 END) AS 'contacted',SUM(CASE WHEN status = 'Not Contacted' THEN 1 ELSE 0 END) AS 'notcontacted',SUM(CASE WHEN status = 'Ringing' THEN 1 ELSE 0 END) AS 'ringing',SUM(CASE WHEN status = 'Follow Up' THEN 1 ELSE 0 END) AS 'followup',SUM(CASE WHEN status = 'Call Cut' THEN 1 ELSE 0 END) AS 'callcut',SUM(CASE WHEN status = 'Not Interested' THEN 1 ELSE 0 END) AS 'notinterested',SUM(CASE WHEN status = 'Clash' THEN 1 ELSE 0 END) AS 'clash',SUM(CASE WHEN status = 'Negotiation' THEN 1 ELSE 0 END) AS 'negotiation',SUM(CASE WHEN status = 'Deal Done' THEN 1 ELSE 0 END) AS 'dealdone',SUM(CASE WHEN status = 'Quotation Sending' THEN 1 ELSE 0 END) AS 'quotationsending',SUM(CASE WHEN status = 'Appointment Done' THEN 1 ELSE 0 END) AS 'appointmentdone',SUM(CASE WHEN status = 'Switch Off' THEN 1 ELSE 0 END) AS 'switchoff',SUM(CASE WHEN status = 'Invalid' THEN 1 ELSE 0 END) AS 'invalid',SUM(CASE WHEN status = 'Call Back' THEN 1 ELSE 0 END) AS 'callback'","userid='".$userid."'");
    }else{
    $data['total_staff']=$this->GenericModel->get_record_with_condition("tbl_registration","count(id) as total_count","user_type='Employee'");    
    $data['leads']=$this->GenericModel->get_record("tbl_lead","SUM(CASE WHEN status = 'Contacted' THEN 1 ELSE 0 END) AS 'contacted',SUM(CASE WHEN status = 'Not Contacted' THEN 1 ELSE 0 END) AS 'notcontacted',SUM(CASE WHEN status = 'Ringing' THEN 1 ELSE 0 END) AS 'ringing',SUM(CASE WHEN status = 'Follow Up' THEN 1 ELSE 0 END) AS 'followup',SUM(CASE WHEN status = 'Call Cut' THEN 1 ELSE 0 END) AS 'callcut',SUM(CASE WHEN status = 'Not Interested' THEN 1 ELSE 0 END) AS 'notinterested',SUM(CASE WHEN status = 'Clash' THEN 1 ELSE 0 END) AS 'clash',SUM(CASE WHEN status = 'Negotiation' THEN 1 ELSE 0 END) AS 'negotiation',SUM(CASE WHEN status = 'Deal Done' THEN 1 ELSE 0 END) AS 'dealdone',SUM(CASE WHEN status = 'Quotation Sending' THEN 1 ELSE 0 END) AS 'quotationsending',SUM(CASE WHEN status = 'Appointment Done' THEN 1 ELSE 0 END) AS 'appointmentdone',SUM(CASE WHEN status = 'Switch Off' THEN 1 ELSE 0 END) AS 'switchoff',SUM(CASE WHEN status = 'Invalid' THEN 1 ELSE 0 END) AS 'invalid',SUM(CASE WHEN status = 'Call Back' THEN 1 ELSE 0 END) AS 'callback'");
    }
    $data['title'] ='Home';
    $data['page']='Home';
    $this->layout_admin($data);
     }
   public function login_admin(){
      $this->load->view('admin/site/login');  
    }
public function support_login(){

$username = trim($this->input->post('username'));
$password = trim($this->input->post('password'));
$password = md5($password);

$ip = $this->input->ip_address();

$table='tbl_registration';
$field = 'id,full_name,mobile_no,email_address,user_type,role,team_leader,location,agent_id';
$condition="email_address='$username' and password='".$password."'"; 

$query=$this->GenericModel->get_record_with_condition($table,$field,$condition);

$array=array();

if(count($query)>=1){

    // Update login status
    $this->db->where('id', $query[0]->id);
    $this->db->update('tbl_registration', ['status' => 'active']);

    // SESSION DATA
    $session_data['ADMIN_ID']=$query[0]->id;
    $session_data['USERNAME']=$query[0]->full_name;
    $session_data['MOBILE']=$query[0]->mobile_no;
    $session_data['EMAIL']=$query[0]->email_address;
    $session_data['USERTYPE']=$query[0]->user_type;
    $session_data['ROLE']=$query[0]->role;
    $session_data['TEAM']=$query[0]->team_leader;
    $session_data['LOCATION']=$query[0]->location;
    $session_data['AGENT_ID']=$query[0]->agent_id;

    $this->session->set_userdata($session_data);

    // =============================
    // GET DEVICE / BROWSER DETAILS
    // =============================

    $browser = $this->agent->browser().' '.$this->agent->version();
    $platform = $this->agent->platform();
    $user_agent = $this->agent->agent_string();

    if ($this->agent->is_mobile()){
        $device = 'Mobile';
    } elseif ($this->agent->is_browser()){
        $device = 'Desktop';
    } else {
        $device = 'Unknown';
    }

    // =============================
    // GET LOCATION FROM IP
    // =============================

    $location = '';

    $ipdata = @json_decode(file_get_contents("http://ip-api.com/json/".$ip));

    if($ipdata && $ipdata->status == 'success'){
        $location = $ipdata->city.', '.$ipdata->regionName.', '.$ipdata->country;
    }

    // =============================
    // INSERT LOGIN LOG
    // =============================

    $log_data = array(
    'user_id' => $query[0]->id,
    'email' => $query[0]->email_address,
    'ip_address' => $ip,
    'browser' => $browser,
    'platform' => $platform,
    'device' => $device,
    'user_agent' => $user_agent,
    'location' => $location,
    'login_time' => date('Y-m-d H:i:s'),
    'status' => 'Success'
    );
    
    $this->db->insert('tbl_login_logs', $log_data);
    
    $login_log_id = $this->db->insert_id();
    
    // store log id in session
    $this->session->set_userdata('LOGIN_LOG_ID', $login_log_id);

    $array=array('code'=>200,'message'=>'Redirecting Login Successfull...');

}else{

    // FAILED LOGIN LOG

    $log_data = array(
        'user_id' => 0,
        'email' => $username,
        'ip_address' => $ip,
        'browser' => $this->agent->browser(),
        'platform' => $this->agent->platform(),
        'device' => 'Unknown',
        'user_agent' => $this->agent->agent_string(),
        'location' => '',
        'login_time' => date('Y-m-d H:i:s'),
        'status' => 'Failed'
    );

    $this->db->insert('tbl_login_logs', $log_data);

    $array=array('code'=>400,'message'=>'Email ID No or Password Incorrect');    

}

echo json_encode($array);
}






public function login_support_process(){
  echo $this->Support_Model->login_support_process($_POST);
  }
public function support_user_logout() {

    $user_id = $this->session->userdata('ADMIN_ID');
    $login_log_id = $this->session->userdata('LOGIN_LOG_ID');

    if($user_id){

        // update user status
        $this->db->where('id', $user_id);
        $this->db->update('tbl_registration', ['status' => 'inactive']);

        // update logout time
        if($login_log_id){

            $logout_time = date('Y-m-d H:i:s');

            // get login time
            $log = $this->db->get_where('tbl_login_logs', ['id'=>$login_log_id])->row();

            if($log){

                $login_time = strtotime($log->login_time);
                $logout_timestamp = strtotime($logout_time);

                $duration = $logout_timestamp - $login_time;

                $session_duration = gmdate("H:i:s", $duration);

                $this->db->where('id', $login_log_id);
                $this->db->update('tbl_login_logs', [
                    'logout_time' => $logout_time,
                    'session_duration' => $session_duration
                ]);
            }
        }
    }

    $this->session->sess_destroy();

    redirect('admin-login');
}


public function login_logs(){

$from_date = $this->input->get('from_date');
$to_date = $this->input->get('to_date');
$user_ids = $this->input->get('user_ids');

$page = $this->input->get('page');
$page = ($page) ? $page : 1;

$limit = 5;
$offset = ($page - 1) * $limit;

/* COUNT TOTAL RECORDS */

$this->db->from('tbl_login_logs');
$this->db->join('tbl_registration','tbl_registration.id = tbl_login_logs.user_id','left');

if(!empty($from_date)){
$this->db->where('DATE(tbl_login_logs.login_time) >=', $from_date);
}

if(!empty($to_date)){
$this->db->where('DATE(tbl_login_logs.login_time) <=', $to_date);
}

if(!empty($user_ids)){
$this->db->where_in('tbl_login_logs.user_id', $user_ids);
}

$total_rows = $this->db->count_all_results();


/* FETCH DATA */

$this->db->select('tbl_login_logs.*, tbl_registration.full_name');
$this->db->from('tbl_login_logs');
$this->db->join('tbl_registration','tbl_registration.id = tbl_login_logs.user_id','left');

if(!empty($from_date)){
$this->db->where('DATE(tbl_login_logs.login_time) >=', $from_date);
}

if(!empty($to_date)){
$this->db->where('DATE(tbl_login_logs.login_time) <=', $to_date);
}

if(!empty($user_ids)){
$this->db->where_in('tbl_login_logs.user_id', $user_ids);
}

$this->db->order_by('tbl_login_logs.id','DESC');
$this->db->limit($limit,$offset);

$data['logs'] = $this->db->get()->result();


/* PAGINATION */

$data['totalPages'] = ceil($total_rows / $limit);
$data['page'] = $page;


/* EMPLOYEE LIST */

$data['employees']=$this->db
->select('id, full_name')
->where('user_type','Employee')
->get('tbl_registration')
->result();


$data['template']='login_logs';
$data['title']='Login Logs';
$data['page_title']='Login Logs';

$this->layout_admin($data);
}







public function employee(){
$data['template']='employee';
$data['title'] ='Gautam Ayurveda Employee';
$data['role']=$this->GenericModel->get_record('tbl_role',"*");
$data['page']='Gautam Ayurveda Employee';
$this->layout_admin($data);
 }
 public function change_password(){
    $data['template']='change_password';
    $data['title'] ='Change Password';
    $data['page']='Gautam Ayurveda Employee';
    $this->layout_admin($data);
 }
 public function detail_lead_details(){
$data['template']='status_wise_lead';
$data['title'] =$_GET['lead_status'].' Lead';
$data['role']=$this->GenericModel->get_record('tbl_role',"*");
$data['status']=$this->GenericModel->get_record('tbl_status',"*");
if(isset($_GET['view'])){
$data['lead_data']=$this->GenericModel->get_record_with_condition("tbl_comment","*","lead_id='". base64_decode($_GET['view'])."' order by id desc");
}
$data['page']=$_GET['lead_status'].' Lead';
$this->layout_admin($data);
 }
 public function create_lead(){
$data['template']='lead';
$data['title'] ='Create Lead';
$data['status']=$this->GenericModel->get_record('tbl_status',"*");
$data['employee']=$this->GenericModel->get_record_with_condition("tbl_registration","full_name,id","user_type='Employee'"); 
$data['page']='Create Lead';
$this->layout_admin($data);
 }
  public function employee_created_leads(){
$data['template']='employee_all_leads';
$data['title'] ='Employee Lead';
$data['status']=$this->GenericModel->get_record('tbl_status',"*");
//$data['employee']=$this->GenericModel->get_record_with_condition("tbl_registration","full_name,id","user_type='Employee'"); 
$data['page']='Employee Lead';
$this->layout_admin($data);
 }
 public function create_order(){
$data['template']='order';
$data['title'] ='Create Order';
$data['status']=$this->GenericModel->get_record('tbl_orders',"*");
$data['employee']=$this->GenericModel->get_record_with_condition("tbl_registration","full_name,id","user_type='Employee'"); 
$data['page']='Create Order';
$this->layout_admin($data);
 }
  public function employee_created_orders(){
$data['template']='employee_all_leads';
$data['title'] ='Employee Lead';
$data['status']=$this->GenericModel->get_record('tbl_orders',"*");
//$data['employee']=$this->GenericModel->get_record_with_condition("tbl_registration","full_name,id","user_type='Employee'"); 
$data['page']='Employee Lead';
$this->layout_admin($data);
 }

 public function detail_order_details(){
$data['template']='status_wise_order';
$data['title'] =$_GET['status'].' Order';

$data['status']=$this->GenericModel->get_record('tbl_order_status',"*");

$data['page']=$_GET['status'].' Order';
$this->layout_admin($data);
 }



 public function detail_delivery_details(){
$data['template']='status_wise_delivery';
$data['title'] =$_GET['shipment_status'].' Order';

$data['shipment_status']=$this->GenericModel->get_record('tbl_delivery_status',"*");

$data['page']=$_GET['shipment_status'].' Order';
$this->layout_admin($data);
 }



 public function timer(){
  $data['template']='timer';
  $data['title'] ='Timer';
$data['status']=$this->GenericModel->get_record('tbl_status',"*");
  //$data['employee']=$this->GenericModel->get_record_with_condition("tbl_registration","full_name,id","user_type='Employee'"); 
  $data['page']='Timer';
  $this->layout_admin($data);
   }
   public function dialer(){
  $data['template']='dialer';
  $data['title'] ='Dialer';
$data['status']=$this->GenericModel->get_record('tbl_status',"*");
  //$data['employee']=$this->GenericModel->get_record_with_condition("tbl_registration","full_name,id","user_type='Employee'"); 
  $data['page']='Dialer';
  $this->layout_admin($data);
   }
    public function duplicate(){
    $data['template']='duplicate';
    $data['title'] ='duplicate';
  $data['status']=$this->GenericModel->get_record('tbl_status',"*");
    //$data['employee']=$this->GenericModel->get_record_with_condition("tbl_registration","full_name,id","user_type='Employee'"); 
    $data['page']='Duplicate';
    $this->layout_admin($data);
     }
 
public function update_referral_plans(){
   $amount_array=$_POST['amount'];
    $id_array=$_POST['id'];
   for ($i = 0; $i < count($amount_array); $i++) {
    $amount =$amount_array[$i];
     $id= $id_array[$i];   
     $insert_array=array('amount'=>$amount);
     $where='id='.$id;
     $this->GenericModel->update_generic_record('tbl_payment_type',$insert_array,$where);
   }  
   $this->session->set_userdata('success','Rate changed successfully'); 
   redirect('update_hotel_and_vendor');
  } 
  
   public function add_new_location(){
  $types=$this->input->post('types');
  
  $table='tbl_location';
  $condtion="locations='$types'";
  $duplication=$this->GenericModel->get_record_with_condition($table,$field,$condtion); 
  if(count($duplication)==0){
  $array=array('locations'=>$types);
  $con=$this->db->insert('tbl_location',$array);
  if($con==true){
      echo json_encode(array('message'=>'Location added successfully','code'=>200));  
      $this->session->set_userdata('success','location added Successfully'); 
  }else{
    echo json_encode(array('message'=>'some thing went wrong','code'=>400));    
  }
  }else{
    echo json_encode(array('message'=>'This Location already added','code'=>400));      
  }
 }
  public function update_new_location(){
  $types=$this->input->post('types');
  $id=$this->input->post('id');
    $table='tbl_location';
  $condtion="locations='$types' and id<>'".$id."'";
  $duplication=$this->GenericModel->get_record_with_condition($table,$field,$condtion); 
  if(count($duplication)==0){
  $array=array('locations'=>$types);
       $this->db->where('id',$id);
  $con=$this->db->update('tbl_location',$array);
  if($con==true){
      echo json_encode(array('message'=>'Location update successfully','code'=>200));  
      $this->session->set_userdata('success','Location update Successfully'); 
  }else{
    echo json_encode(array('message'=>'some thing went wrong','code'=>400));    
  }
 }else{
    echo json_encode(array('message'=>'This Location already added','code'=>400));      
  }
 }
  public function delete_location_masters(){
 $id=$this->input->post('id');    
 $query=$this->db->query("delete from tbl_location  where id='$id'");
 if($query==true){
    $this->session->set_userdata('success','Location Deleted Successfully'); 
    echo json_encode(array('message'=>'Location Deleted Successfully','code'=>200));
  }else{
    echo json_encode(array('message'=>'deleted unsuccessfull','code'=>400));   
  } 
 }
 public function change_password_process(){
   $new_password=trim($this->input->post('new_password'));
   $ADMIN_ID=$this->session->userdata('ADMIN_ID');
   $array=array();  
   if(empty($new_password)){
   $array=array('code'=>400,'message'=>'Please enter new password'); 
   }else{
   $update=array('password'=> md5($new_password));
   $this->db->where('id',$ADMIN_ID);
   $get =$this->db->update('tbl_registration',$update);
  if($get==true){
   $array=array('code'=>200,'message'=>'Password Change Successfully'); 
   $this->session->set_userdata('success','Password Change Successfully');
  }else{
   $array=array('code'=>400,'message'=>'some thing went wrong');     
    }
   }
   echo json_encode($array);
   exit;
  }
}

?>