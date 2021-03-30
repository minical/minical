<?php

class Cron_model extends CI_Model {

	function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
	
	function insert_log($system_time, $log)
	{
		$data = Array("system_time" => $system_time, "log" => $log);
		$this->db->insert("cron_log", $data);
        if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
	}
	
	function get_logs()
	{
		
		$this->db->order_by('system_time', 'desc');
		$query = $this->db->get('cron_log');
		
		
		 if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
		if ($query->num_rows() > 0)
		{
			return $query->result_array();
		}
		else
		{
			return null;
		}
	}


	function clear_cron_log()
	{
		$this->db->empty_table("cron_log");
		echo $this->db->last_query();
        if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
		
		return NULL;
	}


}