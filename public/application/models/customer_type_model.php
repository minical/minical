<?php

class Customer_type_model extends CI_Model {

	function __construct()
    {        
        parent::__construct(); // Call the Model constructor
    }
	
	function get_customer_types($company_id, $show_deleted = false)
	{
		$this->db->where('company_id', $company_id);
		if (!$show_deleted)
			$this->db->where('is_deleted', 0);
		$this->db->order_by("sort_order", "asc");
		
		$query = $this->db->get('customer_type');
		
		if ($query->num_rows >= 1) 
			return $query->result_array();
		return NULL;
	}
	
	function create_customer_type($company_id, $name)
	{		
		$data = array(
			'name' => $name,
			'company_id' => $company_id
		);
		
		$this->db->insert('customer_type', $data);		
		//echo $this->db->last_query();
		return $this->db->insert_id();
	}
	
	function update_customer_type($id, $data)
	{
		$data = (object) $data;
		$this->db->where('id', $id);
		$this->db->update('customer_type', $data);
                
		return TRUE;
	}
        
        function get_customer_type($customer_type_id)
	{
            $this->db->where('id', $customer_type_id);		
            $this->db->where('is_deleted', 0);
            $this->db->order_by("id", "asc");

            $query = $this->db->get('customer_type');

            if ($query->num_rows >= 1) 
                    return $query->result_array();
            return NULL;
	}
        
        function get_last_customer_type()
        { 
            $this->db->select("MAX(id) as max");

            $query = $this->db->get('customer_type');

            if ($query->num_rows >= 1) 
                    return $query->result_array();
            return NULL;
        }
        
        function change_customer_type($id, $new_customer_type__id)
	{
            $sql = "UPDATE `customer_type` SET `id` = '$new_customer_type__id' WHERE `customer_type`.`id` = $id";
            $this->db->query($sql);
	}  
        
        function get_customers($customer_type_id)
        {
            $this->db->where('customer_type_id', $customer_type_id);
            $query = $this->db->get('customer');

            if ($query->num_rows >= 1) 
                    return $query->result_array();
            return NULL;
        }
    
    function create_common_customer_type_setting($data){
        $this->db->insert('common_customer_type_setting', $data);		
		return $this->db->insert_id();
    }
    
    function get_common_customer_type_settings($company_id){
        $this->db->where('company_id', $company_id);		
        $this->db->order_by('sort_order', 'ASC');		
		$query = $this->db->get('common_customer_type_setting');
		$response = array();
		if ($query->num_rows >= 1) {
			$result = $query->result_array();
            foreach($result as $setting)
            {
                $response[$setting['customer_type_id']] = $setting;
            }
        }
        return $response;
    }
    function update_common_customer_type_settings($company_id, $customer_type_id, $data)
    {
        $this->db->where('customer_type_id', $customer_type_id);
        $this->db->where('company_id', $company_id);
		$this->db->delete('common_customer_type_setting');
        
        return $this->create_common_customer_type_setting($data);
    }

      function get_customer_type_by_name($company_id, $name)
    {
            $this->db->where('name', $name);      
            $this->db->where('company_id', $company_id);      
            $this->db->where('is_deleted', 0);
            $this->db->order_by("id", "asc");

            $query = $this->db->get('customer_type');

            if ($query->num_rows >= 1) 
                    return $query->result_array();
            return NULL;
    }

    function delete_customer_types($company_id)
    {
        $data = Array('is_deleted' => 1);

        $this->db->where('company_id', $company_id);
        $this->db->update("customer_type", $data);

        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }

    }


}

/* End of file customer_model.php */