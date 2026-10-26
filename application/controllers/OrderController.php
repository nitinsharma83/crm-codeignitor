<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OrderController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('GenericModel');
    }

    // Load order form
    public function create_order() {
        $this->load->view('order_form');
    }

    // Fetch lead details by mobile number (AJAX)
    public function fetch_lead() {
        $mobile_no = $this->input->post('mobile_no');
        $lead = $this->GenericModel->get_record_with_condition(
            "tbl_lead", 
            "*", 
            "mobile_no='".$mobile_no."'"
        );

        if(!empty($lead)) {
            echo json_encode(array("status"=>200, "data"=>$lead[0]));
        } else {
            echo json_encode(array("status"=>404, "message"=>"No Lead Found"));
        }
    }

    // Insert Order (you already have this)
    public function order_creation_process() {
        $info = $this->input->post();

        $insert_array = array(
            'order_id'      => time(),
            'name'          => trim($info['name']),
            'phone'         => trim($info['phone']),
            'alt_number'    => trim($info['alt_number']),
            'medicine'      => trim($info['medicine']),
            'price'         => trim($info['price']),
            'discount'      => trim($info['discount']),
            'state'         => trim($info['state']),
            'address'       => trim($info['address']),
            'post_office'   => trim($info['post_office']),
            'district'      => trim($info['district']),
            'landmark'      => trim($info['landmark']),
            'pin_code'      => trim($info['pin_code']),
            'comment'       => trim($info['comment']),
            'created_date'  => date('Y-m-d H:i:s'),
            'status'        => 'Pending'
        );

        $con = $this->GenericModel->insert_generic_record('tbl_orders', $insert_array);

        if($con == 'Error'){
            $array = array('code'=>400,'message'=>'Something went wrong');
        } else {
            $array = array('code'=>200,'message'=>'Order Created successfully..');
        }

        echo json_encode($array);
    }
}
