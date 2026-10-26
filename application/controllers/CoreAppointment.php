<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CoreAppointment extends MY_Controller
{
    private $core_db;

    public function __construct()
    {
        parent::__construct();

        // Login check
        if (!$this->session->userdata('ADMIN_ID')) {
            redirect(base_url());
        }

        date_default_timezone_set('Asia/Kolkata');

        // Load second database
        $this->core_db = $this->load->database('corephp', TRUE);

        // Load model & library
        $this->load->model('CoreAppointment_model');
        $this->load->library('pagination');
    }

    public function index()
    {
        $filter = array(
            'name'            => trim($this->input->get('name')),
            'mobile'          => trim($this->input->get('mobile')),
            'doctor'          => trim($this->input->get('doctor')),
            'branch'          => trim($this->input->get('branch')),
            'status'          => trim($this->input->get('status')),
            'added_by'        => trim($this->input->get('added_by')),
            'customer_source' => trim($this->input->get('customer_source')),
            'sfrom_date'      => trim($this->input->get('sfrom_date')),
            'sto_date'        => trim($this->input->get('sto_date')),
            'cfrom_date'      => trim($this->input->get('cfrom_date')),
            'cto_date'        => trim($this->input->get('cto_date'))
        );

        $limit  = 100;
        $offset = (int)$this->uri->segment(3);

        if ($offset < 0) {
            $offset = 0;
        }

        /*
        |--------------------------------------------------------------------------
        | Total Records
        |--------------------------------------------------------------------------
        */

        $total = $this->CoreAppointment_model->countAppointments($filter);

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $config['base_url'] = site_url('CoreAppointment/index');
        $config['total_rows'] = $total;
        $config['per_page'] = $limit;
        $config['uri_segment'] = 3;
        $config['reuse_query_string'] = TRUE;

        $config['full_tag_open'] = '<ul class="pagination justify-content-center">';
        $config['full_tag_close'] = '</ul>';

        $config['first_tag_open'] = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';

        $config['last_tag_open'] = '<li class="page-item">';
        $config['last_tag_close'] = '</li>';

        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';

        $config['prev_tag_open'] = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';

        $config['cur_tag_open'] = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close'] = '</span></li>';

        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';

        $config['attributes'] = array(
            'class' => 'page-link'
        );

        $this->pagination->initialize($config);

        /*
        |--------------------------------------------------------------------------
        | Appointment List
        |--------------------------------------------------------------------------
        */

        $data['appointments'] = $this->CoreAppointment_model->getAppointments(
            $filter,
            $limit,
            $offset
        );

        $data['pagination'] = $this->pagination->create_links();
        $data['filter'] = $filter;

        /*
        |--------------------------------------------------------------------------
        | Doctors
        |--------------------------------------------------------------------------
        */

        $data['doctors'] = $this->core_db
            ->where('type', 'Doctor')
            ->where('status', 1)
            ->order_by('name', 'ASC')
            ->get('tbl_admin')
            ->result();

        /*
        |--------------------------------------------------------------------------
        | Branches
        |--------------------------------------------------------------------------
        */

        $data['branches'] = $this->core_db
            ->order_by('branch', 'ASC')
            ->get('tbl_branch')
            ->result();

        /*
        |--------------------------------------------------------------------------
        | Customer Sources
        |--------------------------------------------------------------------------
        */

        $data['sources'] = $this->core_db
            ->order_by('type', 'ASC')
            ->get('tbl_enquiry')
            ->result();

        /*
        |--------------------------------------------------------------------------
        | Callers
        |--------------------------------------------------------------------------
        */

        $data['callers'] = $this->core_db
            ->where('type', 'Caller')
            ->where('status', 1)
            ->order_by('username', 'ASC')
            ->get('tbl_admin')
            ->result();

        /*
        |--------------------------------------------------------------------------
        | Layout
        |--------------------------------------------------------------------------
        */

        $data['title'] = 'Core Appointments';

        // This tells layout.php which page to load
        $data['template'] = 'core_appointment_list';

        // Load with header, sidebar & footer
     $data['data'] = $data;

$this->load->view('admin/layout/layout', $data);
    }
}