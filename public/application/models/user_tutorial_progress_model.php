<?php

class User_tutorial_progress_model extends CI_Model {
	
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
	
	function get_progresses($user_id)
	{
		$this->db->select('progress_name');
		$this->db->from('user_tutorial_progress');
		$this->db->where('user-id', $user_id);
		
		$query = $this->db->get();
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		$result_array = $query->result_array();
		$result = array();
		foreach($result_array as $r => $progress_name)
		{
			$result[] = $progress_name['progress_name'];
		}
		return $result;
	}
	
	function record_progress($user_id, $progress_name, $datetime)
	{
		$data = array (
			'user_id' => $user_id,
			'progress_name' => $progress_name,
			'datetime' => $datetime
		);
		
		$this->db->insert('user_tutorial_progress', $data);
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
			
	}
	
}
?>