<?php
Defined('BASEPATH') OR exit('No direct script access allowed');

class Lead extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
    }

    /**
     * Server-side truth for "does this lead already have a pincode saved".
     * Every action that requires a pincode (bulk_update here, and the
     * comment/appointment/payment endpoints in their own files) should
     * go through the same check so a bypassed client-side check can't
     * slip a lead through without one.
     */
    private function lead_has_pincode($lead_id)
    {
        $row = $this->db->select('pincode')->where('id', $lead_id)->get('tbl_lead')->row();
        return $row && trim((string) $row->pincode) !== '';
    }

    public function get_popup_lead()
    {
        $username = $this->session->userdata('USERNAME');

$query = $this->db->query("
    SELECT * FROM tbl_lead
    WHERE lead_source = 'Call'
      AND (tarik IS NULL OR tarik < '1000-01-01')
      AND assigned_by = ?
    ORDER BY id DESC
    LIMIT 1", [$username]);

if ($query->num_rows() > 0) {
    $lead = $query->row_array();
    $this->db->query("UPDATE tbl_lead SET tarik = NOW() WHERE id = ?", [$lead['id']]);
    
            echo json_encode([
                'status' => true,
                'data' => $lead
            ]);
        } else {
            echo json_encode([
                'status' => false
            ]);
        }
    }

    /**
     * NEW: called by the pincode field on the lead view as soon as the
     * agent types 6 digits. Looks the pincode up against India Post's
     * free public API (server-side, so there's no CORS issue and no key
     * to manage) and returns district/state so the front end can
     * auto-fill City. If the pincode isn't found, 'found' comes back
     * false and the front end just lets the agent type the city in
     * manually — pincode itself is still saved either way.
     */
    public function lookup_pincode()
    {
        $pincode = trim($this->input->post('pincode'));

        if (!preg_match('/^\d{6}$/', $pincode)) {
            echo json_encode(['status' => false, 'message' => 'Enter a valid 6-digit pincode']);
            return;
        }

        $found = false;
        $city = $state = $area = '';

        $ch = curl_init('https://api.postalpincode.in/pincode/' . $pincode);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        $response = curl_exec($ch);
        $curl_err = curl_error($ch);
        curl_close($ch);

        if ($response !== false) {
            $data = json_decode($response, true);
            if (!empty($data[0]['Status']) && $data[0]['Status'] === 'Success' && !empty($data[0]['PostOffice'][0])) {
                $po = $data[0]['PostOffice'][0];
                $found = true;
                $city  = $po['District'];
                $state = $po['State'];
                $area  = $po['Name'];
            }
        }

        echo json_encode([
            'status'  => true, // the lookup itself succeeded, regardless of whether a match was found
            'found'   => $found,
            'city'    => $city,
            'state'   => $state,
            'area'    => $area,
            'message' => $found ? 'Location found' : 'No location found for this pincode — please enter the city manually',
        ]);
    }

    public function bulk_update()
    {
        $lead_id = $this->input->post('lead_id');

        if (empty($lead_id)) {
            echo json_encode(['status' => false, 'message' => 'Invalid lead']);
            return;
        }

        // ---- Pincode gate: compulsory before ANY field on this lead can be saved ----
        $posted_pincode = $this->input->post('pincode');
        $posted_pincode = $posted_pincode !== null ? trim($posted_pincode) : null;

        if ($posted_pincode !== null && $posted_pincode !== '' && !preg_match('/^\d{6}$/', $posted_pincode)) {
            echo json_encode(['status' => false, 'message' => 'Enter a valid 6-digit pincode']);
            return;
        }

        $already_has_pincode = $this->lead_has_pincode($lead_id);

        if (!$already_has_pincode && empty($posted_pincode)) {
            echo json_encode(['status' => false, 'message' => 'Please enter the pincode before saving lead details']);
            return;
        }

        $update = [];

        if (!empty($posted_pincode)) {
            $update['pincode'] = $posted_pincode;
        }

        // Simple text fields
        foreach (['customer_name', 'company_name', 'requirement'] as $f) {
            if ($this->input->post($f) !== null) {
                $update[$f] = $this->input->post($f);
            }
        }

        // Priority
        $priority = $this->input->post('lead_priority');
        if (!empty($priority)) {
            if (!in_array($priority, ['HOT', 'WARM', 'COLD', 'DEAD'])) {
                echo json_encode(['status' => false, 'message' => 'Invalid priority']);
                return;
            }
            $update['lead_priority'] = $priority;
        }

        // Address — city and state are updated independently now. Previously this set
        // BOTH fields together whenever either was posted, which meant every "Save Changes"
        // (the front-end only ever sends 'city') silently wiped out the 'state' column.
        if ($this->input->post('city') !== null) {
            $update['city'] = $this->input->post('city');
        }
        if ($this->input->post('state') !== null) {
            $update['state'] = $this->input->post('state');
        }
if ($this->input->post('description') !== null) {
    $update['description'] = $this->input->post('description', false);
}
        // Age — sent by the "Age / Gender" row on the Overview tab, but this method never
        // picked it up before, so editing age here had no effect.
        $age = $this->input->post('age');
        if ($age !== null && $age !== '') {
            if (!is_numeric($age) || $age < 0 || $age > 120) {
                echo json_encode(['status' => false, 'message' => 'Enter a valid age (0-120)']);
                return;
            }
            $update['age'] = (int) $age;
        }

        // Gender — same row, same problem: posted but never saved.
        $gender = $this->input->post('gender');
        if (!empty($gender)) {
            if (!in_array($gender, ['Male', 'Female', 'Other'])) {
                echo json_encode(['status' => false, 'message' => 'Invalid gender']);
                return;
            }
            $update['gender'] = $gender;
        }

        // Follow-up date (tarik) — sent by the inline "Follow Up Date" editor on the lead
        // view, but this method never actually picked it up before, so the save button
        // only updated the on-screen text and never touched the database.
        $tarik = $this->input->post('tarik');
        if (!empty($tarik)) {
            $ts = strtotime($tarik);
            if ($ts === false) {
                echo json_encode(['status' => false, 'message' => 'Invalid follow-up date/time']);
                return;
            }
            $update['tarik'] = date('Y-m-d H:i:s', $ts);
        }

        // WhatsApp number — editable via its own inline editor (masked display, blank input on
        // open, so the real number is never re-exposed just by clicking edit). Validated as a
        // real 10-digit number server-side too, not just "doesn't look like the masked xxxx".
        $secondary = $this->input->post('secondary_mobile_no');
        if ($secondary !== null && $secondary !== '') {
            $secondary_digits = preg_replace('/\D/', '', $secondary);
            if (strlen($secondary_digits) !== 10) {
                echo json_encode(['status' => false, 'message' => 'Enter a valid 10-digit WhatsApp number']);
                return;
            }
            $update['secondary_mobile_no'] = $secondary_digits;
        }

        // Assigned To — admin-only, only applied if actually sent
        $employee_id = $this->input->post('employee_id');
        if (!empty($employee_id)) {

            $update['userid']      = $employee_id;
            $update['assigned_to'] = $this->input->post('assigned_to');
            $update['api']         = $this->input->post('api');
        }

        if (empty($update)) {
            echo json_encode(['status' => false, 'message' => 'Nothing to update']);
            return;
        }

        $this->db->trans_start();
        $this->db->where('id', $lead_id)->update('tbl_lead', $update);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $db_error = $this->db->error(); // surfaced so a silent save failure (e.g. follow-up date) is diagnosable from the browser console instead of just "didn't work"
            echo json_encode([
                'status'   => false,
                'message'  => 'Save failed, please try again',
                'db_error' => $db_error['message'] ?? null,
            ]);
            return;
        }

        echo json_encode([
            'status'  => true,
            'message' => 'Lead updated successfully',
            'updated' => $update, // the actual values written, so the UI reflects what was saved rather than what it optimistically assumed
            'reload'  => isset($update['assigned_to']), // only reload if agent changed
        ]);
    }

    public function update_assigned_to()
    {

        $lead_id      = $this->input->post('lead_id');

        $employee_id  = $this->input->post('employee_id');

        $assigned_to  = $this->input->post('assigned_to');

        $api          = $this->input->post('api');

        if(empty($lead_id) || empty($employee_id)){

            echo json_encode([
                'status' => false,
                'message' => 'Invalid request'
            ]);

            exit;
        }

        $update = [

            'userid'      => $employee_id,

            'assigned_to' => $assigned_to,

            'api'         => $api
        ];

        $this->db->where('id', $lead_id);

        $done = $this->db->update('tbl_lead', $update);

        if($done){

            echo json_encode([
                'status' => true,
                'message' => 'Lead reassigned successfully'
            ]);

        }else{

            echo json_encode([
                'status' => false,
                'message' => 'Database error'
            ]);
        }
    }
    public function update_priority()
    {
        $lead_id = $this->input->post('lead_id');
        $priority = $this->input->post('lead_priority');

        if (empty($lead_id) || empty($priority)) {
            echo json_encode(['status' => false, 'message' => 'Invalid data']);
            return;
        }

        $allowed = ['HOT', 'WARM', 'COLD', 'DEAD'];

        if (!in_array($priority, $allowed)) {
            echo json_encode(['status' => false, 'message' => 'Invalid priority']);
            return;
        }

        // UPDATE
        $this->db->where('id', $lead_id);
        $this->db->set('lead_priority', $priority);
        $this->db->update('tbl_lead');

        // 🔥 REAL CHECK (IMPORTANT)
        if ($this->db->affected_rows() > 0) {
            echo json_encode(['status' => true, 'message' => 'Updated successfully']);
        } else {
            echo json_encode(['status' => false, 'message' => 'No change detected / already same value']);
        }
    }
    public function update_lead_field()
    {
        $lead_id = $this->input->post('lead_id');
        $field   = $this->input->post('field');
        $value   = $this->input->post('value');

        if(empty($lead_id) || empty($field)){

            echo json_encode([
                'status' => false,
                'message' => 'Invalid request'
            ]);
            return;
        }

        $allowed_fields = [

            'customer_name',
            'company_name',
            'secondary_mobile_no',
            'lead_priority',
            'email_id',
            'requirement',
            'pincode'

        ];

        // Address update
        if($field == 'address'){

            $update = [

                'state' => $value['state'],
                'city'  => $value['city']

            ];

        } else {

            if(!in_array($field, $allowed_fields)){

                echo json_encode([
                    'status' => false,
                    'message' => 'Field not allowed'
                ]);
                return;
            }

            if ($field === 'pincode' && !preg_match('/^\d{6}$/', (string) $value)) {
                echo json_encode(['status' => false, 'message' => 'Enter a valid 6-digit pincode']);
                return;
            }

            $update = [
                $field => $value
            ];
        }

        $this->db->where('id', $lead_id);

        $result = $this->db->update('tbl_lead', $update);

        if($result){

            echo json_encode([
                'status' => true,
                'message' => 'Updated Successfully'
            ]);

        } else {

            echo json_encode([
                'status' => false,
                'message' => 'Update Failed'
            ]);
        }
    }

    // Called by the Tags widget's "+ Add Tag" dropdown on the lead view
    // (views/admin/site/status_wise_lead.php). Backed by lead_tag_map + tbl_tags.
    public function add_tag()
    {
        $lead_id = $this->input->post('lead_id');
        $tag_id  = $this->input->post('tag_id');

        if (empty($lead_id) || empty($tag_id)) {
            echo json_encode(['status' => false, 'message' => 'Invalid request']);
            return;
        }

        // avoid duplicate mapping if the same tag is somehow submitted twice
        $existing = $this->db->query(
            "SELECT id FROM lead_tag_map WHERE lead_id = ? AND tag_id = ?",
            [$lead_id, $tag_id]
        )->result();

        if (!empty($existing)) {
            echo json_encode(['status' => true, 'message' => 'Tag already added']);
            return;
        }

        $done = $this->db->insert('lead_tag_map', [
            'lead_id'    => $lead_id,
            'tag_id'     => $tag_id,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if ($done) {
            echo json_encode(['status' => true, 'message' => 'Tag added']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Could not add tag']);
        }
    }

    // Called by each tag chip's "x" remove link on the lead view.
    public function remove_tag()
    {
        $lead_id = $this->input->post('lead_id');
        $tag_id  = $this->input->post('tag_id');

        if (empty($lead_id) || empty($tag_id)) {
            echo json_encode(['status' => false, 'message' => 'Invalid request']);
            return;
        }

        $this->db->where('lead_id', $lead_id)->where('tag_id', $tag_id)->delete('lead_tag_map');

        echo json_encode(['status' => true, 'message' => 'Tag removed']);
    }

    // Called by the upload control on the new Files tab. Backed by lead_files.
    public function upload_file()
    {
        $lead_id = $this->input->post('lead_id');

        if (empty($lead_id)) {
            echo json_encode(['status' => false, 'message' => 'Invalid lead']);
            return;
        }

        if (empty($_FILES['lead_file']['name'])) {
            echo json_encode(['status' => false, 'message' => 'No file selected']);
            return;
        }

        $upload_dir = FCPATH . 'uploads/lead_files/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $config['upload_path']   = $upload_dir;
        $config['allowed_types'] = 'jpg|jpeg|png|gif|pdf|doc|docx|xls|xlsx|txt';
        $config['max_size']      = 10240; // 10 MB
        $config['encrypt_name']  = true;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('lead_file')) {
            echo json_encode(['status' => false, 'message' => strip_tags($this->upload->display_errors())]);
            return;
        }

        $file_data = $this->upload->data();

        $done = $this->db->insert('lead_files', [
            'lead_id'     => $lead_id,
            'file_name'   => $file_data['client_name'] ?? $file_data['orig_name'],
            'file_path'   => 'uploads/lead_files/' . $file_data['file_name'],
            'uploaded_by' => $this->session->userdata('ADMIN_ID'),
            'uploaded_at' => date('Y-m-d H:i:s'),
        ]);

        if ($done) {
            echo json_encode(['status' => true, 'message' => 'File uploaded successfully']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Could not save file record']);
        }
    }
}
