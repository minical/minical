<?php

class Home_model extends CI_Model {

	function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
	
	function add_email()
	{
		$time_zone = new DateTimeZone('UTC');
		$current_time = new DateTime('now', $time_zone);
		
		$data = array ( 
			'email' => $this->input->post('email'),
			'num_rooms' => $this->input->post('num_rooms'),
			'signup_date' => $current_time->format('Y-m-d G:i:s')
		);
		
		$query = $this->db->insert('email_list', $data);
		
		if($query)
		{
			return true;
		}
		return false;
	}
	
}