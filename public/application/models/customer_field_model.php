<?php

class Customer_field_model extends CI_Model {

	function __construct()
    {        
        parent::__construct(); // Call the Model constructor
    }
	
	function get_customer_fields($company_id, $show_deleted = false, $field = false)
	{
		$this->db->where('company_id', $company_id);
		if (!$show_deleted)
			$this->db->where('is_deleted', 0);
		$this->db->order_by("id", "asc");

		if ($field)
            $this->db->where($field, 1);
		
		$query = $this->db->get('customer_field');
		
		if ($query->num_rows >= 1) 
			return $query->result_array();
		return NULL;
	}

	function get_customer_fields_for_registration_card($company_id)
	{
		$this->db->where('company_id', $company_id);
		$this->db->where('is_deleted', 0);
		$this->db->where('show_on_registration_card', 1);
		$this->db->order_by("id", "asc");
		
		$query = $this->db->get('customer_field');
		
		if ($query->num_rows >= 1) 
			return $query->result_array();
		return NULL;
	}

	function get_common_customer_fields_for_registration_card($company_id)
	{
		$this->db->where('company_id', $company_id);
		$this->db->where('is_deleted', 0);
		$this->db->where('show_on_registration_card', 1);
		
		$query = $this->db->get('common_customer_fields_setting');
		
		if ($query->num_rows >= 1) 
			return $query->result_array();
		return NULL;
	}
	
	function get_customer_fields_for_customer_form($company_id, $is_common = null)
	{
		$this->db->where('company_id', $company_id);
		$this->db->where('is_deleted', 0);
		
        if($is_common)
        {
            $this->db->order_by("customer_field_id", "desc");
            $query = $this->db->get('common_customer_fields_setting');
        }
        else
        {
            $this->db->where('show_on_customer_form', 1);
            $this->db->order_by("id", "asc");
            $query = $this->db->get('customer_field');
        }
		
		
		if ($query->num_rows >= 1) 
			return $query->result_array();
		return NULL;
	}

	function create_customer_field($company_id, $name)
	{		
		$data = array(
			'name' => $name,
			'company_id' => $company_id
		);
		
		$this->db->insert('customer_field', $data);		
		//echo $this->db->last_query();
		return $this->db->insert_id();
	}
	
	function update_customer_field($id, $data)
	{
		$data = (object) $data;
		$this->db->where('id', $id);
		$this->db->update('customer_field', $data);
		return TRUE;
	}
    
    function update_common_customer_fields_settings($company_id, $customer_field_id, $data)
    {
        $this->db->where('customer_field_id', $customer_field_id);
        $this->db->where('company_id', $company_id);
		$this->db->delete('common_customer_fields_setting');
        
        return $this->create_common_customer_fields_settings($data);
    }

    function get_customer_field_by_name($company_id, $name){
        $this->db->where('company_id', $company_id);
        $this->db->where('name', $name);
        $this->db->where('is_deleted', 0);

        $query = $this->db->get('customer_field');

        if ($query->num_rows >= 1)
            return $query->result_array();
        return NULL;
    }
    
    function create_common_customer_fields_settings($data){
        $this->db->insert('common_customer_fields_setting', $data);		
		return $this->db->insert_id();
    }

    function get_common_customer_fields_settings($company_id){
        $this->db->where('company_id', $company_id);
        $this->db->order_by('customer_field_id', 'ASC');
        $query = $this->db->get('common_customer_fields_setting');
        $response = array();
        if ($query->num_rows >= 1) {
            $result = $query->result_array();
            foreach($result as $setting)
            {
                $response[$setting['customer_field_id']] = $setting;
            }
        }
        return $response;
    }


    function customer_field($data)
    {
        $this->db->insert('customer_x_customer_field', $data);
        //echo $this->db->last_query();
        return $this->db->insert_id();
    }

    function delete_customer_fields($company_id)
    {
        $data = Array('is_deleted' => 1);

        $this->db->where('company_id', $company_id);
        $this->db->update("customer_field", $data);

        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
    }
	
}

/* End of file customer_model.php */