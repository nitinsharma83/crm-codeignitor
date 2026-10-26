<?php 
 class Datatables_Model extends CI_Model {
    var $table_payment = 'tbl_registration';
    var $column_order_payment = array(null,'full_name','id','mobile_no','email_address','location','role','user_type','status','created_date'); 
    var $column_search_payment = array('full_name','id','mobile_no','email_address','location','role','user_type','status','created_date');  
    var $order_payment = array('id' => 'desc');
    
    var $table_lead = 'tbl_lead';
    var $column_order_lead = array(null,'customer_name','id','company_name','state','city','mobile_no','requirement','description','lead_source','tarik','userid','status'); 
    var $column_search_lead = array('customer_name','id','company_name','state','city','mobile_no','requirement','description','lead_source','tarik','userid','status');  
    var $order_lead = array('id' => 'desc');
    
    
    var $table_order = 'tbl_orders';
    var $column_order_order = array(null,'id', 'lead_id', 'customer_name', 'phone', 'alt_number', 'state', 'address', 'post_office', 'district', 'landmark', 'pin_code', 'medicine', 'price', 'totalprice', 'discount_type', 'discount_percent', 'comment', 'status', 'order_date', 'userid', 'username','waybill_no'); 
    var $column_search_order = array('id', 'lead_id', 'customer_name', 'phone', 'alt_number', 'state', 'address', 'post_office', 'district', 'landmark', 'pin_code', 'medicine', 'price', 'totalprice', 'discount_type', 'discount_percent', 'comment', 'status', 'order_date', 'userid', 'username','waybill_no');  
    var $order_order = array('id' => 'desc');
    
     var $table_delivery = 'tbl_orders';
    var $column_order_delivery = array(null,'id', 'lead_id', 'customer_name', 'phone', 'alt_number', 'state', 'address', 'post_office', 'district', 'landmark', 'pin_code', 'medicine', 'price', 'totalprice', 'discount_type', 'discount_percent', 'comment', 'status', 'order_date', 'userid', 'username','waybill_no','shipment_status'); 
    var $column_search_delivery = array('id', 'lead_id', 'customer_name', 'phone', 'alt_number', 'state', 'address', 'post_office', 'district', 'landmark', 'pin_code', 'medicine', 'price', 'totalprice', 'discount_type', 'discount_percent', 'comment', 'status', 'order_date', 'userid', 'username','waybill_no','shipment_status');  
    var $order_delivery = array('id' => 'desc');
    
    
    
    
    
   	public function __construct() {
        parent::__construct();
        $this->load->helper('date');
    }
    
       private function geting_datatables_emplyee(){
        $this->db->select($this->column_order_payment);	
        $this->db->from($this->table_payment);
       
        $i = 0;
        foreach ($this->column_search_payment as $item) // loop column 
        {
            if($_POST['search']['value']) // if datatable send POST for search
            {   
                if($i===0) // first loop
                {
                    $this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
                    $this->db->like($item, $_POST['search']['value']);
                }
                else
                {
                    $this->db->or_like($item, $_POST['search']['value']);
                }
                if(count($this->column_search_payment) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
            }
            $i++;
        }
        if(isset($_POST['order'])) // here order processing
        {
            $this->db->order_by($this->column_order_payment[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } 
        else if(isset($this->order_payment)){
            $order = $this->order_payment;
            $this->db->order_by(key($order), $order[key($order)]);
        }
      }
    function get_datatables_employee() {
        $this->geting_datatables_emplyee();
        if($_POST['length'] != -1)
        $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        return $query->result();
    }
    function count_filtered_employee(){
        $this->geting_datatables_emplyee();
        $query = $this->db->get();
        return $query->num_rows();
    }
     public function count_all_employee(){
        $this->db->from($this->table_payment);
         $this->db->where('user_type','Employee');
        return $this->db->count_all_results();
    } 
     
  
  
  
  
       private function geting_datatables_lead($usertype,$userid){
        $this->db->select($this->column_order_lead);	
       if($usertype=='Admin'){  
        $this->db->from($this->table_lead);
       }else{
      $this->db->from($this->table_lead);
      $this->db->where('userid',$userid);
       }
        $i = 0;
        foreach ($this->column_search_lead as $item) // loop column 
        {
            if($_POST['search']['value']) // if datatable send POST for search
            {   
                if($i===0) // first loop
                {
                    $this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
                    $this->db->like($item, $_POST['search']['value']);
                }
                else
                {
                    $this->db->or_like($item, $_POST['search']['value']);
                }
                if(count($this->column_search_lead) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
            }
            $i++;
        }
        if(isset($_POST['order'])) // here order processing
        {
            $this->db->order_by($this->column_order_lead[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } 
        else if(isset($this->order_lead)){
            $order = $this->order_lead;
            $this->db->order_by(key($order), $order[key($order)]);
        }
      }
      
      private function geting_datatables_order($usertype,$userid){
        $this->db->select($this->column_order_order);	
       if($usertype=='Admin'){  
        $this->db->from($this->table_order);
       }else{
      $this->db->from($this->table_order);
      $this->db->where('userid',$userid);
       }
        $i = 0;
        foreach ($this->column_search_order as $item) // loop column 
        {
            if($_POST['search']['value']) // if datatable send POST for search
            {   
                if($i===0) // first loop
                {
                    $this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
                    $this->db->like($item, $_POST['search']['value']);
                }
                else
                {
                    $this->db->or_like($item, $_POST['search']['value']);
                }
                if(count($this->column_search_order) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
            }
            $i++;
        }
        if(isset($_POST['order'])) // here order processing
        {
            $this->db->order_by($this->column_order_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } 
        else if(isset($this->order_order)){
            $order = $this->order_order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
      }
      
      
      
      
      
      
    function get_datatables_lead($usertype,$userid) {
        $this->geting_datatables_lead($usertype,$userid);
        if($_POST['length'] != -1)
        $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        return $query->result();
    }
    function count_filtered_lead($usertype,$userid){
        $this->geting_datatables_lead($usertype,$userid);
        $query = $this->db->get();
        return $query->num_rows();
    }
     public function count_all_lead($usertype,$userid){
       if($usertype=='Admin'){  
        $this->db->from($this->table_lead);
       }else{
      $this->db->from($this->table_lead);
      $this->db->where('userid',$userid);
       }
        return $this->db->count_all_results();
    } 
    
    
    function get_datatables_order($usertype,$userid) {
        $this->geting_datatables_order($usertype,$userid);
        if($_POST['length'] != -1)
        $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        return $query->result();
    }
    function count_filtered_order($usertype,$userid){
        $this->geting_datatables_order($usertype,$userid);
        $query = $this->db->get();
        return $query->num_rows();
    }
     public function count_all_order($usertype,$userid){
       if($usertype=='Admin'){  
        $this->db->from($this->table_order);
       }else{
      $this->db->from($this->table_order);
      $this->db->where('userid',$userid);
       }
        return $this->db->count_all_results();
    } 
    
    
    
/* ================= MAIN QUERY ================= */
private function geting_datatables_lead_details($types, $userid)
{
    $this->db->select($this->column_order_lead);
    $this->db->from($this->table_lead);

    /* ================= STATUS FILTER ================= */
    if (!empty($types)) {
        $this->db->where('status', $types);
    }

    /* ================= USER FILTER ================= */
    if (!empty($userid)) {
        $this->db->where('userid', $userid);
    }

    /* ================= DATE FILTER (tarik DATETIME) ================= */
    if (!empty($_GET['from_date']) && !empty($_GET['to_date'])) {

        $from_date = $_GET['from_date'] . ' 00:00:00';
        $to_date   = $_GET['to_date'] . ' 23:59:59';

        $this->db->where('tarik >=', $from_date);
        $this->db->where('tarik <=', $to_date);
    }

    /* ================= SEARCH ================= */
    $i = 0;
    if (!empty($_POST['search']['value'])) {
        foreach ($this->column_search_lead as $item) {
            if ($i === 0) {
                $this->db->group_start();
                $this->db->like($item, $_POST['search']['value']);
            } else {
                $this->db->or_like($item, $_POST['search']['value']);
            }
            $i++;
        }
        $this->db->group_end();
    }

    /* ================= ORDER ================= */
    if (isset($_POST['order'])) {
        $this->db->order_by(
            $this->column_order_lead[$_POST['order'][0]['column']],
            $_POST['order'][0]['dir']
        );
    } else if (isset($this->order_lead)) {
        $order = $this->order_lead;
        $this->db->order_by(key($order), $order[key($order)]);
    }
}


/* ================= DATA ================= */
function get_datatables_lead_details($types, $userid, $from_date = null, $to_date = null)
{
    $this->db->select('*');
    $this->geting_datatables_lead_details($types, $userid, $from_date, $to_date);

    if ($_POST['length'] != -1) {
        $this->db->limit($_POST['length'], $_POST['start']);
    }

    return $this->db->get()->result();
}

/* ================= FILTERED COUNT ================= */
function count_filtered_lead_details($types, $userid, $from_date = null, $to_date = null)
{
    $this->geting_datatables_lead_details($types, $userid, $from_date, $to_date);
    return $this->db->count_all_results();
}

/* ================= TOTAL COUNT (FIXED) ================= */
public function count_all_lead_details($types, $userid, $from_date = null, $to_date = null)
{
    $this->db->from($this->table_lead);

    if (!empty($types)) {
        $this->db->where('status', $types);
    }

    if (!empty($userid)) {
        $this->db->where('userid', $userid);
    }

    if (!empty($from_date) && !empty($to_date)) {
        $this->db->where('tarik >=', $from_date . ' 00:00:00');
        $this->db->where('tarik <=', $to_date . ' 23:59:59');
    }

    return $this->db->count_all_results();
}


      
  private function geting_datatables_order_details($status, $userid) {
    $this->db->select($this->column_order_order);
    $this->db->from($this->table_order);

    // ✅ Filter by status if provided
    if (!empty($status) && $status !== 'All') {
        $this->db->where('status', $status);
    }

    // ✅ Filter by userid if employee
    if (!empty($userid)) {
        $this->db->where('userid', $userid);     
    }

    // ✅ Global search (safe)
    $searchValue = $_POST['search']['value'] ?? '';
    if (!empty($searchValue)) {
        $this->db->group_start();
        foreach ($this->column_search_order as $i => $item) {
            if ($i === 0) {
                $this->db->like($item, $searchValue);
            } else {
                $this->db->or_like($item, $searchValue);
            }
        }
        $this->db->group_end();
    }

    // ✅ Order
    if (isset($_POST['order'][0]['column'])) {
        $columnIndex = $_POST['order'][0]['column'];
        $dir         = $_POST['order'][0]['dir'] ?? 'asc';

        if (isset($this->column_order_order[$columnIndex])) {
            $this->db->order_by($this->column_order_order[$columnIndex], $dir);
        }
    } else if (!empty($this->order_order)) {
        $order = $this->order_order;
        $this->db->order_by(key($order), $order[key($order)]);
    }
}
private function geting_datatables_delivery_details($shipment_status, $userid){
    $this->db->select($this->column_order_delivery);	
    $this->db->from($this->table_delivery);

    // ✅ Filter by status if provided
    if (!empty($shipment_status)) {
        $this->db->where('shipment_status', $shipment_status);
    }

    // ✅ Filter by userid if employee
    if (!empty($userid)) {
        $this->db->where('userid', $userid);     
    }

    $i = 0;
    foreach ($this->column_search_delivery as $item) {
        if ($_POST['search']['value']) {
            if ($i === 0) {
                $this->db->group_start();
                $this->db->like($item, $_POST['search']['value']);
            } else {
                $this->db->or_like($item, $_POST['search']['value']);
            }
            if (count($this->column_search_delivery) - 1 == $i) {
                $this->db->group_end();
            }
        }
        $i++;
    }

    if (isset($_POST['order'])) {
        $this->db->order_by(
            $this->column_order_delivery[$_POST['order']['0']['column']], 
            $_POST['order']['0']['dir']
        );
    } else if (isset($this->order_delivery)) {
        $order = $this->order_delivery;
        $this->db->order_by(key($order), $order[key($order)]);
    }
}
      
      





    
    
    public function get_datatables_order_details($status, $userid) {
    $this->geting_datatables_order_details($status, $userid);

    $length = $_POST['length'] ?? 10;
    $start  = $_POST['start'] ?? 0;

    if ($length != -1) {
        $this->db->limit((int)$length, (int)$start);
    }

    $query = $this->db->get();
    return $query->result();
}
public function count_filtered_order_details($status, $userid) {
    $this->geting_datatables_order_details($status, $userid);
    $query = $this->db->get();
    return $query->num_rows();
}

   public function count_all_order_details($status, $userid) {
    $this->db->from($this->table_order);

    if (!empty($status) && $status !== 'All') {
        $this->db->where('status', $status);
    }

    if (!empty($userid)) {
        $this->db->where('userid', $userid);
    }

    return $this->db->count_all_results();
}
function get_datatables_delivery_details($usertype,$userid) {
        $this->geting_datatables_delivery_details($usertype,$userid);
        if($_POST['length'] != -1)
        $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        return $query->result();
    }
    function count_filtered_delivery_details($shipment_status, $userid){
    $this->geting_datatables_delivery_details($shipment_status, $userid);
    $query = $this->db->get();
    return $query->num_rows();
}
    public function count_all_delivery_details($shipment_status, $userid){
    $this->db->from($this->table_delivery);

    if (!empty($shipment_status)) {
        $this->db->where('shipment_status', $shipment_status);
    }
    if (!empty($userid)) {
        $this->db->where('userid', $userid);
    }

    return $this->db->count_all_results();
}
    
  }