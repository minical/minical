<?php

class Night_audit_model extends CI_Model {

	function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
	
	function log_night_audit($company_id, $selling_date, $user_id)
	{		
		$this->load->model('Company_model');
		$this->load->helper('timezone');
		$company_time_zone = $this->Company_model->get_time_zone($company_id);
		$local_time =  convert_to_local_time(new DateTime(), $company_time_zone)->format("Y-m-d H:i:s");
		
		$data = Array("company_id" => $company_id, "selling_date" => $selling_date, "user_id" => $user_id, "local_time" => $local_time);
		$this->db->insert("night_audit_log", $data);
        if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
	}	

	function delete_night_audit_log()
	{
		$this->load->model('Company_model');
		$this->db->empty_table("night_audit_log");
        if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
	}
	
	function get_night_audit_logs()
	{
		
		$this->db->order_by('local_time', 'desc');
		$this->db->limit(500);
		$query = $this->db->get('night_audit_log');
		
		
		 if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
		else
		{
			return null;
		}
	}
	
	
}