<?php

class Shift_model extends CI_Model {

	function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

	// insert new shift_id, and return it.
	function create_new_shift($user_id) 
	{
		$currentDateTime = date("Y-m-d G:i:s");
		$data = array('user_id' => $user_id, 'start_time'=> $currentDateTime);      
        $this->db->insert("shift", $data);		
	}
	
	function get_last_shift_id($user_id) 	{		
		$sql = "SELECT 
		shift_id
		FROM shift 
		WHERE start_time =(SELECT MAX(start_time) FROM shift WHERE user_id='$user_id');";
		$query = $this->db->query($sql);
		$q = $query->result();		
		return $q[0]->shift_id;
	}
	function get_shifts($user_id) 
	{
		$sql = "SELECT
		shift_id, start_time
		FROM shift
		WHERE user_id = '$user_id'
		ORDER BY start_time DESC";
		$q = $this->db->query($sql);
		$data = array();
		foreach ($q->result() as $row) {
			$data['start_time'][] = $row->start_time;
			$data['shift_id'][] = $row->shift_id;
		}
		return $data;
	}	
	
	function get_user_id($shift_id) 
	{		
		$this->db->select('user_id');
        $this->db->where('shift_id', $shift_id);
        $query = $this->db->get('shift');
        $q = $query->result();
        return $q[0]->user_id;
	}	
	
}