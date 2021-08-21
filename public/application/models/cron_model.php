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

	function handle_session_overflow()
    {
        $SQL = "
            DELETE FROM `sessions` 
            WHERE 
            (
            `user_agent` like '%UptimeRobot%' or 
            `user_agent` like '%Wget%' or 
            `user_agent` IS NULL or
            `user_agent` = '' or
            `ip_address` = '66.42.82.40'
            )
            AND `session_id` NOT IN (
              SELECT `session_id`
              FROM (
                SELECT `session_id`
                FROM `sessions` 
                ORDER BY `last_activity` DESC
                LIMIT 200 -- keep this many records
              ) foo
            );
        ";
        $this->db->query($SQL);
    }

}