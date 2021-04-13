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
        $getwayData = $this->db->get_where('company_payment_gateway', array('company_id =' => $this->company_id))->result_array();
        
        if(empty($getwayData))
        {
            $this->db->insert('company_payment_gateway',$data);
        }
        else
        {
            $this->db->where('company_id', $this->company_id);
            $this->db->update('company_payment_gateway', $data);
        }
    	//$this->db->on_duplicate("company_payment_gateway", $data);
    }
	
    // called from invoice_model... and somewhere else...
	function get_payment_gateway_settings($company_id)
    {
        $result = null;

        $this->db->select('c.*, cpg.beanstream_merchant_id, cpg.beanstream_api_access_passcode,cpg.beanstream_profile_api_access_passcode,cpg.paypal_email,cpg.selected_payment_gateway,cpg.stripe_secret_key,cpg.stripe_publishable_key,cpg.gateway_login,cpg.gateway_password,cpg.gateway_meta_data,cpg.store_cc_in_booking_engine');
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