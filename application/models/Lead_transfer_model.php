<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lead_transfer_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * mobile_no (normalized) => employee row
     */
    public function get_employees_by_mobile()
    {
        $rows = $this->db
            ->select('id, full_name, agent_id, location, mobile_no, status')
            ->from('tbl_registration')
            ->where('user_type', 'Employee')
            ->get()
            ->result_array();

        $users = [];
        foreach ($rows as $row) {
            $key = normalize_mobile($row['mobile_no']);
            if (!empty($key)) {
                $users[$key] = $row;
            }
        }
        return $users;
    }

    /**
     * Pull a batch of unprocessed call logs. LIMIT keeps each cron run fast
     * and predictable - process backlog over several runs, not one huge run.
     */
    public function get_pending_call_logs($limit = 500)
    {
        return $this->db
            ->select('id, call_to_number, caller_id_number, answer_agent_number,
                      customer_no_with_prefix, created_at, status')
            ->from('tbl_call_logs')
            ->group_start()
                ->where('status', NULL)
                ->or_where('status', '')
                ->or_where('status', 'Answered')
            ->group_end()
            ->order_by('id', 'ASC')
            ->limit($limit)
            ->get()
            ->result_array();
    }
public function get_employees_by_email()
{
    $rows = $this->db
        ->select('id, full_name, agent_id, location, email_address')
        ->where('email_address IS NOT NULL', null, false)
        ->where('email_address !=', '')
        ->get('tbl_registration')
        ->result_array();

    $map = [];
    foreach ($rows as $row) {
        $key = trim(strtolower($row['email_address']));
        if ($key !== '') {
            $map[$key] = $row;
        }
    }
    return $map;
}

public function get_pending_wati_logs($limit = 500)
{
    return $this->db
        ->where('processed', 0)
        ->order_by('id', 'ASC')
        ->limit($limit)
        ->get('tbl_wati_webhook')
        ->result_array();
}

public function mark_wati_log($webhookId, $status)
{
    return $this->db
        ->where('id', $webhookId)
        ->update('tbl_wati_webhook', [
            'processed'      => $status,
            'processed_date' => date('Y-m-d H:i:s'),
        ]);
}
    /**
     * Most recent lead for a given 10-digit mobile, if any.
     * NOTE: RIGHT(mobile_no,10)=? cannot use an index — this is a full table
     * scan on every call. Fine for now; fix with a normalized generated
     * column + unique index once you're ready for that migration.
     */
    public function find_latest_lead_by_mobile($mobile)
    {
        $sql = "SELECT id, userid, assigned_to, api, team_head, created_date
                FROM tbl_lead
                WHERE RIGHT(mobile_no, 10) = ?
                ORDER BY created_date DESC, id DESC
                LIMIT 1";

        $query = $this->db->query($sql, [$mobile]);

        return $query->num_rows() > 0
            ? $query->row_array()
            : null;
    }

    /**
     * Reassign an existing lead to whoever just answered/called, and bump
     * it to the top of the list by stamping created_date with the REAL
     * current time (caller must pass now, not the call log's timestamp).
     * Own mini-transaction so a bad row here can't poison the batch.
     */
    public function reassign_and_touch($leadId, array $fields)
    {
        if (empty($fields['created_date'])) {
            $fields['created_date'] = date('Y-m-d H:i:s');
        }

        $this->db->trans_start();

        $this->db->where('id', $leadId)->update('tbl_lead', $fields);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            log_message('error', 'REASSIGN FAILED | Lead ID=' . $leadId
                . ' | db_error=' . json_encode($this->db->error()));
            return false;
        }

        log_message('info', 'REASSIGN OK | Lead ID=' . $leadId
            . ' | affected_rows=' . $this->db->affected_rows());

        return true;
    }
/**
     * Active employees, split out FB05 for the special Missed9266 routing.
     */
    public function get_active_employees_for_missed()
    {
        $rows = $this->db
            ->select('id, full_name, agent_id, location, mobile_no')
            ->from('tbl_registration')
            ->where('status', 'active')
            ->where('user_type', 'Employee')
            ->order_by('id', 'ASC')
            ->get()
            ->result_array();

        $employees = [];
        $fb05 = null;

        foreach ($rows as $row) {
            if (strtoupper(trim($row['full_name'])) === 'FB05') {
                $fb05 = $row;
            } else {
                $employees[] = $row;
            }
        }

        return ['employees' => $employees, 'fb05' => $fb05];
    }

    public function get_pending_missed_logs($limit = 500)
    {
        return $this->db
            ->select('id, call_to_number, caller_id_number')
            ->from('tbl_missed_call_logs')
            ->where('status', 'pending')
            ->order_by('id', 'ASC')
            ->limit($limit)
            ->get()
            ->result_array();
    }

    public function mark_missed_log($logId, $status)
    {
        $this->db->where('id', $logId)->update('tbl_missed_call_logs', ['status' => $status]);
    }
    /**
     * Kept for backward compatibility / anywhere else that still calls it
     * directly. Prefer reassign_and_touch() going forward — it also updates
     * agent assignment, not just the timestamp.
     */
    public function touch_lead($leadId, $now, $today)
    {
        $oldLead = $this->db
            ->select('id, created_date')
            ->where('id', $leadId)
            ->get('tbl_lead')
            ->row_array();

        log_message(
            'error',
            'TOUCH LEAD | Lead ID=' . $leadId .
            ' | OLD created_date=' . ($oldLead['created_date'] ?? 'NULL') .
            ' | NEW created_date=' . $now
        );

        $this->db
            ->where('id', $leadId)
            ->update('tbl_lead', [
                'created_date' => $now,
                'tarik'        => $today,
            ]);

        log_message(
            'error',
            'TOUCH LEAD RESULT | Lead ID=' . $leadId .
            ' | affected_rows=' . $this->db->affected_rows() .
            ' | db_error=' . json_encode($this->db->error())
        );

        return !$this->db->error()['code'];
    }

    public function insert_lead(array $data)
    {
        $this->db->insert('tbl_lead', $data);
        return $this->db->insert_id();
    }

    public function insert_comment(array $data)
    {
        $this->db->insert('tbl_comment', $data);
    }

    public function mark_call_log($logId, $status)
    {
        $this->db->where('id', $logId)->update('tbl_call_logs', ['status' => $status]);
    }
}