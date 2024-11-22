<?php

class einvoice_details extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database(); // Load database
    }


    public function insert_einvoice_data($data) {
        if ($this->db->insert('einvoice_irndetails', $data)) {
            return TRUE; // Successfully inserted
        } else {
            // Log the error for debugging
            log_message('error', 'DB Insert Error: ' . $this->db->last_query());
            return FALSE;
        }
    }
   
    

   
    
    

  
	
}