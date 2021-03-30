<?php

class Customer_log_model extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
		
	// Create a new booking_room_history
	function insert_log($data)
    {
	    $data = (object) $data;        
        $this->db->insert("customer_log", $data);
		return $this->db->insert_id();
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
		
		//echo $this->db->last_query();
    }
	
	// what he said
	// return row_limit number of rows. if row_limit is 0, then there is no cap.
	function get_customer_log($customer_id, $row_limit=0)	
    {		
        $sql = "
			SELECT * 
			FROM customer_log as cl, users as u, user_profiles as up
			WHERE 
				cl.customer_id = '$customer_id' AND
				cl.user_id = u.id AND
				u.id = up.user_id
			ORDER BY date_time DESC			
		";
		if ($row_limit > 0)
			$sql = $sql." LIMIT 0, $row_limit";
		
		$q = $this->db->query($sql);
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
		
		$result = $q->result();
		//echo $this->db->last_query();
		
		return $result;
    }
	
	function get_customer_log_count($customer_id)
    {
		$company_id = $this->session->userdata('current_company_id');
		$sql= "SELECT COUNT(*) AS row_count
			FROM customer_log as cl
			WHERE cl.customer_id = '$customer_id' ";
        $q = $this->db->query($sql);
		//echo $this->db->last_query();		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
		
		$result = $q->row_array(0);
		
		return $result['row_count'];	
	}
	
}