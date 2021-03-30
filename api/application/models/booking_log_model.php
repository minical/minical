<?php

class Booking_log_model extends CI_Model {

    function __construct()
    {        
        parent::__construct();
    }		
	
	function insert_log($data)
    {
	    $data = (object) $data;        
	    $this->db->insert("booking_log", $data);
		
		if ($this->db->_error_message()) 
		{
			show_error($this->db->_error_message());
		}		
		//return $this->db->insert_id();
        $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
		$result = $query->result_array();
        if(isset($result[0]))
        {  
          return $result[0]['last_id'];
        }
            else
        {  
          return null;
        }
		
    }


	function insert_logs($data)
    {
	    $this->db->insert_batch("booking_log", $data);
		
		if ($this->db->_error_message()) 
		{
			show_error($this->db->_error_message());
		}		
		//return $this->db->insert_id();
        $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
		$result = $query->result_array();
        if(isset($result[0]))
        {  
          return $result[0]['last_id'];
        }
            else
        {  
          return null;
        }
		
    }
		
	function get_booking_logs($booking_id)
    {		
        $sql = "
			SELECT bl.*, u.id, up.first_name, up.last_name
			FROM booking_log as bl
			LEFT JOIN users as u ON bl.user_id = u.id
			LEFT JOIN user_profiles as up ON u.id = up.user_id
			WHERE 
				bl.booking_id = '$booking_id'
			ORDER BY date_time ASC
		";
		
		$q = $this->db->query($sql);
		
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
		$result = $q->result_array();

		return $result;
    }	

	function get_action_report($company_id, $date, $user_id='')
    {		
		$employee_query = '';
		if ($user_id != '')
		{
			$employee_query = " AND bl.user_id = '$user_id'";
		}
        $sql = "
			SELECT bl.date_time, up.first_name, up.last_name, bl.log, b.booking_id
			FROM booking_log as bl, users as u, user_profiles as up, booking as b
			WHERE 
				b.company_id = '$company_id' AND
				b.booking_id = bl.booking_id AND
				bl.user_id = u.id AND
				u.id = up.user_id AND
				DATE(bl.date_time) = '$date' $employee_query
			ORDER BY bl.date_time ASC
		";
		$q = $this->db->query($sql);
		
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
		$result = $q->result();
		
		return $result;
    }	

	
}