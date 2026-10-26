<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller {

    public function create_key()
    {
        $key = bin2hex(random_bytes(32));

$this->db->where('email_address', 'gautamayurveda99@gmail.com')
         ->update('tbl_registration', [
             'auto_login_key' => $key
         ]);

echo site_url('api/auto-login?key=' . $key);
    }
}
