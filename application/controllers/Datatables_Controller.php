<?php
class Datatables_Controller  extends MY_Controller{
    public function __construct() {
    parent::__construct();
   }
      // Job Requirement
      public function employee_listing_paging(){  
      
        $list = $this->Datatables_Model->get_datatables_employee();
        $data = array();
        $no = $_POST['start'];
		$status='';
		
        foreach ($list as $rows) {
            $status='';
            if($rows->status=='active'){
            $status='<a  class="btn btn-gradient-success btn-rounded btn-fw btn-sm" href="#" onclick="update_employee_status('.$rows->id.',1)"><i class="mdi mdi-check-circle"></i> Active</a>';
            }else{
            $status='<a class="btn btn-gradient-danger btn-rounded btn-fw btn-sm" href="#" onclick="update_employee_status('.$rows->id.',2)"><i class="mdi mdi-window-close"></i> Inactive</a>';    
            }
            $edit='<a class="btn btn-gradient-primary btn-rounded btn-fw btn-sm" href="'. site_url().'employee?update='.base64_encode($rows->id).'" title="Edit"><i class="fa fa-edit"></i></a>';
             $view='<a class="btn btn-gradient-info btn-rounded btn-fw btn-sm" href="'. site_url().'employee?view='.base64_encode($rows->id).'" title="View"><i class="fa fa-eye"></i></a>';
             $no++;
            $row = array();
            $row[] = $no;
            $row[] = $rows->full_name;
            $row[] = $rows->mobile_no;
            $row[] = $rows->email_address;
            $row[] = $rows->location;
            $row[] = $rows->role;
            $row[] = $status.'&nbsp;'.$edit.'&nbsp;'.$view;
            $data[] = $row;
        }
  $output = array("draw" => $_POST['draw'],"recordsTotal" => $this->Datatables_Model->count_all_employee(),"recordsFiltered" => $this->Datatables_Model->count_filtered_employee(),"data" => $data,);
        //output to json format
        echo json_encode($output);
    }
    
     // Job Requirement
      public function lead_details_pagination(){   
    $usertype = $this->session->userdata('USERTYPE');
    $userid   = $this->session->userdata('ADMIN_ID');
    $list     = $this->Datatables_Model->get_datatables_lead($usertype,$userid);
    $data     = array();
    $no       = $_POST['start'];
    $status   = '';
                
    if($usertype == 'Admin'){    
        foreach ($list as $rows) {
            // Mask mobile number (hide last 4 digits)
            
            $status = '<a class="btn btn-gradient-primary btn-rounded btn-fw btn-sm" href="#">'.$rows->status.'</a>';    
            $edit   = '<a class="btn btn-gradient-primary btn-rounded btn-fw btn-sm" href="'. site_url().'create_lead?update='.base64_encode($rows->id).'" title="Edit"><i class="fa fa-edit"></i></a>';
            $delete = '<a class="btn btn-gradient-danger btn-rounded btn-fw btn-sm" href="#" onclick="return delete_lead_creation('.$rows->id.')" title="Delete"><i class="fa fa-trash"></i></a>';
            $view   = '<a class="btn btn-gradient-info btn-rounded btn-fw btn-sm" href="'. site_url().'detail_lead_details?view='.base64_encode($rows->id).'" title="View"><i class="fa fa-eye"></i></a>';
            
            $no++;
            $row   = array();
            $row[] = $no;
            $row[] = $rows->customer_name;
            $row[] = $rows->mobile_no;
            $row[] = $rows->city.','.$rows->state;
            $row[] = $rows->requirement; 
            $row[] = $rows->description;
            $row[] = $rows->lead_source;
            $row[] = $rows->tarik;
            $row[] = $status;
            $row[] = $edit.'&nbsp;'.$delete.'&nbsp;'.$view;
            $data[] = $row;
        }
    }else{
        foreach ($list as $rows) {
            // Mask mobile number (hide last 4 digits)
            $mobile_no = substr($rows->mobile_no, 0, -4) . 'XXXX';

            $status = '<a class="btn btn-gradient-primary btn-rounded btn-fw btn-sm" href="#">'.$rows->status.'</a>';    
            $edit   = '<a class="btn btn-gradient-primary btn-rounded btn-fw btn-sm" href="'. site_url().'employee_created_leads?update='.base64_encode($rows->id).'" title="Edit"><i class="fa fa-edit"></i></a>';
            $view   = '<a class="btn btn-gradient-info btn-rounded btn-fw btn-sm" href="'. site_url().'detail_lead_details?view='.base64_encode($rows->id).'" title="View"><i class="fa fa-eye"></i></a>';
            
            $no++;
            $row   = array();
            $row[] = $no;
            $row[] = $rows->customer_name;
            $row[] = $mobile_no;
            $row[] = $rows->city.','.$rows->state;
            $row[] = $rows->requirement; 
            $row[] = $rows->description;
            $row[] = $rows->lead_source;
            $row[] = date("d-m-Y", strtotime($rows->tarik));
            $row[] = $status;
            $row[] = $edit.'&nbsp;'.$view;
            $data[] = $row;
        }    
    }

    $output = array(
        "draw"            => $_POST['draw'],
        "recordsTotal"    => $this->Datatables_Model->count_all_lead($usertype,$userid),
        "recordsFiltered" => $this->Datatables_Model->count_filtered_lead($usertype,$userid),
        "data"            => $data,
    );

    //output to json format
    echo json_encode($output);
}






public function lead_details_pagination_details()
{
    $draw  = intval($this->input->post_get('draw') ?? 1);
$start = intval($this->input->post_get('start') ?? 0);

   $types     = $this->input->get_post('lead_status');
$from_date = $this->input->get_post('from_date');
$to_date   = $this->input->get_post('to_date');



    $ADMIN_ID = $this->session->userdata('ADMIN_ID');

$userid = ($this->session->userdata('USERTYPE') == 'Employee') ? $ADMIN_ID : null;

$list = $this->Datatables_Model
    ->get_datatables_lead_details($types, $userid, $from_date, $to_date);


    $data = array();
    $no   = $_POST['start'];

    foreach ($list as $rows) {

        $mobile_no = substr($rows->mobile_no, 0, -4) . 'XXXX';
        $statusBtn = '<span class="btn btn-sm btn-primary">'.$rows->status.'</span>';

        $view = '<a class="btn btn-sm btn-info" href="'.site_url().'detail_lead_details?view='.base64_encode($rows->id).'&lead_status='.$types.'"><i class="fa fa-eye"></i></a>';

        $edit = '<a class="btn btn-sm btn-primary" href="'.site_url().'create_lead?update='.base64_encode($rows->id).'"><i class="fa fa-edit"></i></a>';

        $no++;
        $row = array();
        $row[] = $no;
        $row[] = $rows->customer_name;
        $row[] = $mobile_no;
        $row[] = $rows->city . ',' . $rows->state;
        $row[] = $rows->requirement;
        $row[] = $rows->description;
        $row[] = $rows->lead_source;
        $row[] = $rows->tarik;
        $row[] = $statusBtn;
        $row[] = $edit . ' ' . $view;

        $data[] = $row;
    }

    echo json_encode([
    "draw"            => intval($_POST['draw']),
    "recordsTotal"    => $this->Datatables_Model
        ->count_all_lead_details($types, $userid, $from_date, $to_date),
    "recordsFiltered" => $this->Datatables_Model
        ->count_filtered_lead_details($types, $userid, $from_date, $to_date),
    "data"            => $data
]);

}



    
    
    
public function order_details_pagination_details() {
    // ✅ Accept both POST & GET safely
    $types = $this->input->post_get('status') ?? 'All';
    $ADMIN_ID = $this->session->userdata('ADMIN_ID');

    // ✅ Fetch list based on user type
    if ($this->session->userdata('USERTYPE') == 'Employee') {
        $list = $this->Datatables_Model->get_datatables_order_details($types, $ADMIN_ID);
    } else {
        $list = $this->Datatables_Model->get_datatables_order_details($types, '');
    }

    $data = array();

    // ✅ Safely handle draw & start
    $draw = intval($this->input->post_get('draw') ?? 1);
    $no   = intval($this->input->post_get('start') ?? 0);

    foreach ($list as $rows) {
        // 🔹 Edit button
        $edit = '<a href="'.site_url('create_order?update='.base64_encode($rows->id)).'" 
                    class="btn btn-sm btn-primary">Edit</a>';

        // 🔹 Row data
        $row = array();
        $row[] = htmlspecialchars($rows->status, ENT_QUOTES, 'UTF-8');
        $row[] = htmlspecialchars($rows->username, ENT_QUOTES, 'UTF-8');
        $row[] = htmlspecialchars($rows->customer_name, ENT_QUOTES, 'UTF-8');
        $row[] = htmlspecialchars($rows->phone, ENT_QUOTES, 'UTF-8');
        $row[] = htmlspecialchars($rows->alt_number, ENT_QUOTES, 'UTF-8');
        $row[] = htmlspecialchars($rows->pin_code, ENT_QUOTES, 'UTF-8');
        $row[] = htmlspecialchars($rows->address, ENT_QUOTES, 'UTF-8');
        $row[] = htmlspecialchars($rows->landmark, ENT_QUOTES, 'UTF-8');
        $row[] = htmlspecialchars($rows->state, ENT_QUOTES, 'UTF-8');
        $row[] = htmlspecialchars($rows->post_office, ENT_QUOTES, 'UTF-8');
        $row[] = htmlspecialchars($rows->district, ENT_QUOTES, 'UTF-8');
        $row[] = htmlspecialchars($rows->medicine, ENT_QUOTES, 'UTF-8');
        $row[] = htmlspecialchars($rows->price, ENT_QUOTES, 'UTF-8');
        $row[] = htmlspecialchars($rows->totalprice, ENT_QUOTES, 'UTF-8');
        $row[] = htmlspecialchars($rows->discount_type, ENT_QUOTES, 'UTF-8');
        $row[] = htmlspecialchars($rows->discount_percent, ENT_QUOTES, 'UTF-8');
        $row[] = htmlspecialchars($rows->order_date, ENT_QUOTES, 'UTF-8');
        $row[] = $edit;

        $data[] = $row;
    }

    // ✅ Prepare output
    $output = array(
        "draw"            => $draw,
        "recordsTotal"    => $this->Datatables_Model->count_all_order_details($types, $ADMIN_ID),
        "recordsFiltered" => $this->Datatables_Model->count_filtered_order_details($types, $ADMIN_ID),
        "data"            => $data,
    );

    // ✅ Return JSON safely
    echo json_encode($output, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}








    
    
}
?>