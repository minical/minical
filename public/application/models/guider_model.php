<?php

class Guider_model extends CI_Model {
	
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
	
	function get_guiders($user_id, $location)
	{
		$sql = "
			SELECT *
			FROM guider as g
			WHERE 
				g.location = '$location' AND
				(
					IFNULL(g.prerequisite_guider_id, 0) <= (
						SELECT IFNULL(MAX(uxg2.guider_id), 0)
						FROM user_x_guider as uxg2
						WHERE uxg2.user_id = '$user_id'
					) AND
					g.guider_id NOT IN (
						SELECT uxg.guider_id
						FROM user_x_guider as uxg
						WHERE uxg.user_id = '$user_id'
					)
				)
			ORDER BY g.guider_id ASC;
		";
		
        $query = $this->db->query($sql);
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		$result_array = $query->result_array();
		
		return $result_array;
	}
	
	// when a guider is viewed, then this function is called
	function record_progress($user_id, $guider_id, $date_time)
	{
		$data = array (
			'user_id' => $user_id,
			'guider_id' => $guider_id,
			'completed_at' => $date_time
		);
		
		$this->db->insert('user_x_guider', $data);
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
			
	}
	
	function get_last_guider_viewed_by_user($user_id)
	{
		$this->db->select("g.*");
		$this->db->from("users as u, guider as g, user_x_guider as uxg");
		$this->db->where("u.id = uxg.user_id");
		$this->db->where("g.guider_id = uxg.guider_id");
		$this->db->where("u.id", $user_id);
		
		
        $query = $this->db->query($sql);
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		$result_array = $query->result_array();
		
		return $result_array;
	}
	
	function get_guider_progress_report($start_date, $end_date)
	{
		$sql = "
		
			select g2.guider_id, COUNT(max_table.id) as user_count, g2.description
			FROM guider as g2
			join
			(
				select u.id, u.created, max(g.guider_id) as guider_id
				from
				users as u, guider as g, user_x_guider as uxg
				where 
					u.id = uxg.user_id AND 
					g.guider_id = uxg.guider_id AND
					u.created > '$start_date' AND u.created < '$end_date'
				group by u.id 
			) max_table ON max_table.guider_id = g2.guider_id
			GROUP BY g2.guider_id
		";
		  
		$query = $this->db->query($sql);
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		$result_array = $query->result_array();
		
		return $result_array;
	}
	
}
?>