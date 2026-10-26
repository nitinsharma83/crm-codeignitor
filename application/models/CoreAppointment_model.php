<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CoreAppointment_model extends CI_Model
{
    private $core_db;

    public function __construct()
    {
        parent::__construct();

        // Load second database
        $this->core_db = $this->load->database('corephp', TRUE);
    }

    /**
     * Apply all filters
     */
    private function applyFilters($filter = array())
    {
        // Appointment Date From
        if (!empty($filter['sfrom_date'])) {

            $date = date('Y-m-d', strtotime(str_replace('/', '-', $filter['sfrom_date'])));

            $this->core_db->where('tbl_appointment.appointment_date >=', $date);
        }

        // Appointment Date To
        if (!empty($filter['sto_date'])) {

            $date = date('Y-m-d', strtotime(str_replace('/', '-', $filter['sto_date'])));

            $this->core_db->where('tbl_appointment.appointment_date <=', $date);
        }

        // Created Date From
        if (!empty($filter['cfrom_date'])) {

            $date = date('Y-m-d', strtotime(str_replace('/', '-', $filter['cfrom_date'])));

            $this->core_db->where('DATE(tbl_appointment.post_date) >=', $date, FALSE);
        }

        // Created Date To
        if (!empty($filter['cto_date'])) {

            $date = date('Y-m-d', strtotime(str_replace('/', '-', $filter['cto_date'])));

            $this->core_db->where('DATE(tbl_appointment.post_date) <=', $date, FALSE);
        }

        // Customer Name
        if (!empty($filter['name'])) {

            $this->core_db->like(
                'tbl_appointment.customer_name',
                trim($filter['name'])
            );
        }

        // Mobile
        if (!empty($filter['mobile'])) {

            $this->core_db->like(
                'tbl_appointment.mobile',
                trim($filter['mobile'])
            );
        }

        // Doctor
        if (!empty($filter['doctor'])) {

            $this->core_db->where(
                'tbl_appointment.doctor_id',
                $filter['doctor']
            );
        }

        // Branch
        if (!empty($filter['branch'])) {

            $this->core_db->where(
                'tbl_appointment.branch_id',
                $filter['branch']
            );
        }

        // Caller
        if (!empty($filter['added_by'])) {

            $this->core_db->where(
                'tbl_appointment.added_by',
                $filter['added_by']
            );
        }

        // Customer Source
        if (!empty($filter['customer_source'])) {

            $this->core_db->where(
                'tbl_appointment.customer_source',
                $filter['customer_source']
            );
        }

        // Status
        if ($filter['status'] !== '' && $filter['status'] !== null) {

            $this->core_db->where(
                'tbl_appointment.status',
                $filter['status']
            );
        }
    }

    /**
     * Count total records
     */
    public function countAppointments($filter = array())
    {
        $this->core_db->from('tbl_appointment');

        $this->core_db->join(
            'tbl_admin doctor',
            'doctor.id = tbl_appointment.doctor_id',
            'left'
        );

        $this->core_db->join(
            'tbl_branch',
            'tbl_branch.id = tbl_appointment.branch_id',
            'left'
        );

        $this->core_db->join(
            'tbl_admin caller',
            'caller.id = tbl_appointment.added_by',
            'left'
        );

        $this->core_db->join(
            'tbl_enquiry',
            'tbl_enquiry.id = tbl_appointment.customer_source',
            'left'
        );

        $this->applyFilters($filter);

        return $this->core_db->count_all_results();
    }

    /**
     * Get Appointment List
     */
    public function getAppointments($filter = array(), $limit = 100, $offset = 0)
    {
        $this->core_db->select("
            tbl_appointment.*,

            doctor.name as doctor_name,

            caller.username as caller_name,

            tbl_branch.branch,

            tbl_enquiry.type as source_name
        ");

        $this->core_db->from('tbl_appointment');

        $this->core_db->join(
            'tbl_admin doctor',
            'doctor.id = tbl_appointment.doctor_id',
            'left'
        );

        $this->core_db->join(
            'tbl_admin caller',
            'caller.id = tbl_appointment.added_by',
            'left'
        );

        $this->core_db->join(
            'tbl_branch',
            'tbl_branch.id = tbl_appointment.branch_id',
            'left'
        );

        $this->core_db->join(
            'tbl_enquiry',
            'tbl_enquiry.id = tbl_appointment.customer_source',
            'left'
        );

        $this->applyFilters($filter);

        $this->core_db->order_by(
            'tbl_appointment.id',
            'DESC'
        );

        $this->core_db->limit(
            $limit,
            $offset
        );

        return $this->core_db->get()->result();
    }
}