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
    // insert_id function won't work as it converts id(bigint) to int, results in incorrect value
//		return $this->db->insert_id();
		
    }



    function insert_logs($data)
    {
	    $this->db->insert_batch("booking_log", $data);
		
		if ($this->db->_error_message()) 
		{
			show_error($this->db->_error_message());
		}		
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
    // insert_id function won't work as it converts id(bigint) to int, results in incorrect value
//		return $this->db->insert_id();
		
    }
		
	function get_booking_logs($booking_id)
    {		
        $sql = "
			SELECT bl.*, u.id, up.first_name, up.last_name, u.email
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

	function get_action_report($company_id, $from_date, $to_date, $user_id='', $start_time = '', $end_time = '', $include_sibling_dates = null)
    {		
        $employee_query = '';
        if ($user_id != '')
		{
			$employee_query = " AND bl.user_id = '$user_id'";
		}
        $start_time_sql = "";
        if($start_time){
            $start_time_sql = "AND DATE_FORMAT(bl.date_time,'%H:%i') >= '$start_time'";
        }
        $end_time_sql = "";
        if($end_time){
            $end_time_sql = "AND DATE_FORMAT(bl.date_time,'%H:%i') <= '$end_time'";
        }
        
        // $date_sql = "DATE(bl.date_time) = '$date'";
        $date_sql = "DATE(bl.selling_date) >= '$from_date' AND DATE(bl.selling_date) <= '$to_date' ";
        if($include_sibling_dates && $from_date && $to_date){
            $prev_date = date("Y-m-d", strtotime($from_date));
            $next_date = date("Y-m-d", strtotime($to_date));
            $date_sql = "DATE(bl.selling_date) >= '$prev_date' AND DATE(bl.selling_date) <= '$next_date' ";
        }
        $sql = "
			SELECT bl.date_time, up.first_name, up.last_name, bl.log, b.booking_id, bl.log_type
			FROM booking_log as bl, users as u, user_profiles as up, booking as b
			WHERE 
				b.company_id = '$company_id' AND
				b.booking_id = bl.booking_id AND
				bl.user_id = u.id AND
				u.id = up.user_id AND
                $date_sql
				$employee_query $start_time_sql $end_time_sql
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

    function get_booking_createdate($booking_id)
    {		
        $sql = "
			SELECT bl.*
			FROM booking_log as bl
			WHERE 
				bl.booking_id = '$booking_id' AND
            bl.log in('OTA Booking created','Booking created')
			ORDER BY date_time ASC limit 0,1
		";
		
		$q = $this->db->query($sql);
		
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
		$result = $q->result_array();

		return isset($result[0]) && $result[0] ? $result[0] : null;
    }	
}