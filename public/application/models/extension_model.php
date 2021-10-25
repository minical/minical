<?php

class Extension_model extends CI_Model {

	function __construct()
    {
        parent::__construct();
    }

	function get_extensions($modules_name, $company_id)
	{
		$this->db->select('*');
		$this->db->from('extensions_x_company');
		$this->db->where('company_id', $company_id);
		if($modules_name)
			$this->db->where_in('extension_name', $modules_name);
		
		$query = $this->db->get();
		
		if ($query->num_rows >= 1)
		{
			return $result = $query->result_array();
		}
		
		return NULL;
	}

	function update_extension($data)
	{
		$this->db->select('*');
		$this->db->from('extensions_x_company');
		$this->db->where('extension_name', $data['extension_name']);
		$this->db->where('company_id', $data['company_id']);

		$query = $this->db->get();
		
		if ($query->num_rows >= 1)
		{
			$result = $query->result_array();
		}

		if(isset($result) && $result){
			$this->db->where('extension_name', $data['extension_name']);
			$this->db->where('company_id', $data['company_id']);
			$this->db->update('extensions_x_company', $data);
		} else {
			$data['is_active'] = 1;
			$this->db->insert('extensions_x_company', $data);
		}

		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		return true;
	}

	function get_active_extensions($company_id = null, $module_name = null, $is_active = true)
	{
		$this->db->select('*');
		$this->db->from('extensions_x_company');

		if($company_id)
			$this->db->where('company_id', $company_id);

		if($module_name)
			$this->db->where('extension_name', $module_name);

		if($is_active)
			$this->db->where('is_active', 1);

		$query = $this->db->get();
		
		if ($query->num_rows >= 1)
		{
			return $result = $query->result_array();
		}
		
		return NULL;
	}

	function add_extension($data){
		$data = (object) $data;        
        $this->db->insert("extensions_x_company", $data);
		
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

	function get_filter_extension($status,$company_id){
		$this->db->select('*');
		$this->db->from('extensions_x_company');
		$this->db->where('company_id', $company_id);
		$this->db->where('is_active', $status);
		
		$query = $this->db->get();
		
		if ($query->num_rows >= 1)
		{
			return $result = $query->result_array();
		}
		
		return NULL;
	}

	function get_favourite_extension($status,$company_id){
		$this->db->select('*');
		$this->db->from('extensions_x_company');
		$this->db->where('company_id', $company_id);
		$this->db->where('is_favourite', $status);
		
		$query = $this->db->get();
		
		if ($query->num_rows >= 1)
		{
			return $result = $query->result_array();
		}
		
		return NULL;
	}
}

/* End of file - extra_model.php */