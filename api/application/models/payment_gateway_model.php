<?php

class Payment_gateway_model extends CI_Model {

	function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

	// called from invoice_model... and somewhere else...
	function update_payment_gateway_settings($data)
    {	
    	$this->db->on_duplicate("company_payment_gateway", $data);
    }
	
    // called from invoice_model... and somewhere else...
	function get_payment_gateway_settings($company_id)
    {
        $result = null;

    	$this->db->where('c.company_id', $company_id);
    	$this->db->from('company as c');
        $this->db->join('company_payment_gateway as cpg', 'cpg.company_id = c.company_id', 'left');
        
        $query = $this->db->get();
        $result = $query->result_array();

        if ($this->db->_error_message()) {
			show_error($this->db->_error_message());
		}

        if ($query->num_rows >= 1) {
            $result = $result[0];
		}

        return $result;
    }
}