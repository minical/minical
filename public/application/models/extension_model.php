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

	function get_installed_extensions($company_id, $vendor_id)
	{
		$this->db->select('*');
		$this->db->from('extensions_x_vendor');

		if($vendor_id)
			$this->db->where('vendor_id', $vendor_id);
		else
			$this->db->where('vendor_id', 0);

		$this->db->where('is_installed', 1);
		
		$query = $this->db->get();
		
		if ($query->num_rows >= 1)
		{
			return $result = $query->result_array();
		}
		
		return NULL;
	}

	function get_uninstalled_extensions($company_id, $vendor_id)
	{
		$this->db->select('*');
		$this->db->from('extensions_x_vendor');

		if($vendor_id)
			$this->db->where('vendor_id', $vendor_id);
		else
			$this->db->where('vendor_id', 0);

		$this->db->where('is_installed', 0);
		
		$query = $this->db->get();
		
		if ($query->num_rows >= 1)
		{
			return $result = $query->result_array();
		}
		
		return NULL;
	}

	function update_extension($data, $is_vendor = false)
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
			if(!$is_vendor)
				$data['is_active'] = 1;
			
			$this->db->insert('extensions_x_company', $data);
		}

		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		return true;
	}

	function update_extension_status($data)
	{
		
		if(isset($data['vendor_companies']) && $data['vendor_companies']){

			$is_activated_extension = $this->get_active_extensions($data['vendor_companies'], $data['extension_name']);
			if(
				isset($is_activated_extension[0]) && 
				$is_activated_extension[0] && 
				isset($is_activated_extension[0]['is_active']) &&
				$is_activated_extension[0]['is_active'] == 1
			){
				return $is_activated_extension;
			} else {
				return null;
			}
		} else {

			unset($data['vendor_companies']);

			$this->db->select('*');
			$this->db->from('extensions_x_vendor');
			$this->db->where('extension_name', $data['extension_name']);
			$this->db->where('vendor_id', $data['vendor_id']);

			$query = $this->db->get();
			
			if ($query->num_rows >= 1)
			{
				$result = $query->result_array();
			}

			$company_id = $data['company_id'];

			if(isset($result) && $result){
				unset($data['company_id']);
				$this->db->where('extension_name', $data['extension_name']);
				$this->db->where('vendor_id', $data['vendor_id']);
				$this->db->update('extensions_x_vendor', $data);
			} else {
				unset($data['company_id']);
				$this->db->insert('extensions_x_vendor', $data);
			}

			unset($data['is_installed']);
			unset($data['vendor_id']);
			$data['company_id'] = $company_id;
			$data['is_active'] = 0;
			$this->update_extension($data, true);

			if ($this->db->_error_message()) // error checking
				show_error($this->db->_error_message());

			return true;
		}
	}

	function get_active_extensions($company_id = null, $module_name = null, $is_active = true)
	{
		$this->db->select('*');
		$this->db->from('extensions_x_company');

		if($company_id){
			if(is_array($company_id)){
				$this->db->where_in('company_id', $company_id);
			} else {
				$this->db->where('company_id', $company_id);
			}
		}

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

	function insert_vendors_extension($data){
		
		for ($i = 0, $total = count($data); $i < $total; $i = $i + 50)
        {
            $batch = array_slice($data, $i, 50);

            $this->db->insert_batch("extensions_x_vendor", $batch);

            if ($this->db->_error_message())
            {
                show_error($this->db->_error_message());
            }
        }
	}

	function get_vendor_extension_status($extension,$status,$vendor_id){
		$this->db->select('*');
		$this->db->from('extensions_x_vendor');
		$this->db->where('extension_name', $extension);
		$this->db->where('vendor_id', $vendor_id);
		$this->db->where('is_installed', $status);
		
		$query = $this->db->get();
		
		if ($query->num_rows >= 1)
		{
			return $result = $query->result_array();
		}
		
		return NULL;
	}

	function add_vendors_extension($data){
		$data = (object) $data;        
        $this->db->insert("extensions_x_vendor", $data);
		
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
}

/* End of file - extra_model.php */