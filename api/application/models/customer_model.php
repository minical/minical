<?php

class Customer_model extends CI_Model {

	function __construct()
    {
        parent::__construct();
    }

	function update_customer($data) 
    {		
		$this->db->where('customer_id', $data['customer_id']);
        $this->db->update("customer", $data);		
    }
	
	function create_customer($data)
    {
		$this->db->insert("customer", $data);		
		
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
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

    function add_staying_customer_to_booking($data)
    {
        $this->db->insert("booking_staying_customer_list", $data);      
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
    }

    function delete_staying_customer_to_booking($data)
    {
    	$this->db->where($data);
        $this->db->delete("booking_staying_customer_list");
    }

    function get_customer($customer_id)
    {	
        $sql = "SELECT *
				FROM customer as c
				WHERE c.customer_id = '$customer_id'
			";
		
        $q = $this->db->query($sql);
		
        // return result set as an associative array
        $result = $q->row_array(0);
		
		return $result;
    }

    function get_customer_by_email($email, $company_id)
    {	
    	if ($email == '')
    	{
    		return null;
    	}

        $sql = "SELECT *
				FROM customer as c
				WHERE c.email = '$email' AND c.company_id = '$company_id'
			";
		
        $q = $this->db->query($sql);
		// return result set as an associative array
        // $result = $q->row_array(0);
		
        return $q->result_array();
    }

    function get_customer_id_by_email($email, $company_id)
    {
        $this->db->from("customer");
        $this->db->where('email', $email);
        $this->db->where('company_id', $company_id);
        $query = $this->db->get();

        //echo $this->db->last_query();
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }

        $result = $query->row_array(0);
        if (isset($result['customer_id']))
        {
            return $result['customer_id'];
        }
        else
        {
            return 0;
        }
    }
	
}