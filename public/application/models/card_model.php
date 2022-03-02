<?php
class Card_model extends CI_Model {

	function __construct()
    {
        parent::__construct();
    }
    
    function get_customer_cards($customer_id)
	{
		$this->db->select('cc.*, c.stripe_customer_id');
		$this->db->where('cc.customer_id', $customer_id);
		$this->db->where('cc.is_card_deleted', 0);
    	$this->db->from('customer_card_detail as cc');
		$this->db->join('customer as c', 'cc.customer_id = c.customer_id', 'left');
        $query = $this->db->get();		
		
        if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
		if ($query->num_rows >= 1)
		{
			return $query->result_array();
		}
        return null;
	}
	
	function get_token_cards($token)
	{
		$this->db->select('id');
		$this->db->where('cc_tokenex_token', $token);
    	$this->db->from('customer_card_detail');
        $query = $this->db->get();		
		
        if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
		if ($query->num_rows >= 1)
		{
			return $query->result_array();
		}
        return null;
	}
    
    function get_customer_card_detail($customer_id)
	{
        $sql = "
			SELECT 
				customer.customer_id, customer.customer_name, customer.company_id, 
				customer_card_detail.id, customer_card_detail.is_primary, customer_card_detail.evc_card_status, 
				customer_card_detail.card_name, customer_card_detail.cc_number, customer_card_detail.cc_expiry_month,
				customer_card_detail.cc_expiry_year, customer_card_detail.cc_tokenex_token, customer_card_detail.cc_cvc_encrypted, customer_card_detail.is_card_deleted
			FROM customer 
			LEFT JOIN customer_card_detail ON customer.customer_id = customer_card_detail.customer_id
			WHERE 
				customer.customer_id = '$customer_id' ";
		
        $query = $this->db->query($sql);
        $result = $query->result_array();
		$customer = null;
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
		if ($query->num_rows >= 1)
		{
			$customer = $result;
		}

        return $customer;
	}
    
    function get_customer_primary_card($customer_id)
	{
		$this->db->where('customer_id', $customer_id);
        $this->db->where('is_primary', 1);
        $this->db->where('is_card_deleted', 0);
    	$this->db->from('customer_card_detail');
        $query = $this->db->get();
        $result = $query->result_array();
		
		$customer = null;
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
		if ($query->num_rows >= 1)
		{
			$customer = $result[0];
		}

        return $customer;
	}
    
    function get_customer_card_by_id($customer_id, $customer_card_id)
	{
		$this->db->where('customer_id', $customer_id);
        $this->db->where('id', $customer_card_id);
    	$this->db->from('customer_card_detail');
        $query = $this->db->get();
        $result = $query->result_array();
		
		$customer = null;
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
		if ($query->num_rows >= 1)
		{
			$customer = $result;
		}

        return $customer;
	}
    function delete_customer_card($customer_id, $card_id, $company_id = null)
    {
       // $data['is_card_deleted'] = 1;
        $this->db->where('customer_id', $customer_id);
        $this->db->where('id', $card_id);
        $this->db->delete("customer_card_detail");
        //$this->db->update("customer_card_detail", $data);
        if ($this->db->_error_message())
		{
            show_error($this->db->_error_message());
		}
        if($this->db->affected_rows() > 0){
            return true;
        }else{
            return false;
        }
    }
    
    function create_customer_card_info($data)
    {
        $data = (object)$data;
        $this->db->insert("customer_card_detail", $data);
        if ($this->db->_error_message())
		{
            show_error($this->db->_error_message());
		}
		
		return $this->db->insert_id();
    }
    
    function update_customer_card($cardId, $customer_id, $data)
    {   
		$data = (object) $data;
        $this->db->where('id', $cardId);
		$this->db->where('customer_id', $customer_id);
        $this->db->update("customer_card_detail", $data);
        if($this->db->affected_rows() > 0){
            return true;
        }else{
            return false;
        }
    }
    
    function update_customer_primary_card($customer_id, $data)
    {   
		$data = (object) $data;
        $this->db->where('is_primary', 1);
		$this->db->where('customer_id', $customer_id);
        $this->db->update("customer_card_detail", $data);
        if($this->db->affected_rows() > 0){
            return true;
        }else{
            return false;
        }
    }
    
    function update_customer_card_is_primary_card_table($customer_id, $card_id, $active, $company_id = null)
    {
		if($active == "active"){
			$data['is_primary'] = 0;
            $this->db->where('customer_id', $customer_id);
            $this->db->where('company_id', $company_id);
            $this->db->update("customer_card_detail", $data);
            
            $data['is_primary'] = 1;
            $this->db->where('id', $card_id);
            $this->db->where('customer_id', $customer_id);
            $this->db->where('company_id', $company_id);
            $this->db->update("customer_card_detail", $data);
        }else{
		    $data['is_primary'] = 0;
            $this->db->where('id', $card_id);
            $this->db->where('customer_id', $customer_id);
            $this->db->where('company_id', $company_id);
            $this->db->update("customer_card_detail", $data);
            //echo $this->db->last_query();
        }
		if($this->db->affected_rows() > 0){
			return true;
		}else{
			return false;
		}
    }
    
    function get_all_customer()
	{
		//$this->db->where('customer_id', $customer_id);
       // if($is_deleted)
       // $this->db->where('is_card_deleted', 0);
    	$this->db->from('customer');
        $query = $this->db->get();
        $result = $query->result_array();
		
		$customer = null;
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
		if ($query->num_rows >= 1)
		{
			$customer = $result;
		}

        return $customer;
	}
     function get_all_customer_cards()
	{
		//$this->db->where('customer_id', $customer_id);
       // $this->db->where('is_deleted', 0);
    	$this->db->from('customer_card_detail');
        $query = $this->db->get();
        $result = $query->result_array();
		
		$customer = null;
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
		if ($query->num_rows >= 1)
		{
			$customer = $result;
		}

        return $customer;
	}
    
    function get_active_card($customer_id, $company_id)
	{
		$this->db->where('customer_id', $customer_id);
        $this->db->where('company_id', $company_id);
        $this->db->where('is_primary', 1);
        $this->db->where('is_card_deleted', 0);
    	$this->db->from('customer_card_detail');
        $query = $this->db->get();
        $result = $query->result_array();
		
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		$customer = "";
		if ($query->num_rows >= 1)
		{
			$customer = $result[0];
		}

        return $customer;
	} 

	function get_unused_tokens($limit){

    	$this->db->where('is_deleted', 0);
    	$this->db->from('customer_deleted_token_details');
    	$this->db->limit($limit);

        $query = $this->db->get();
        $result = $query->result_array();
		
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
		if ($query->num_rows >= 1)
		{
			return $result;
		}

        return NULL;
	}

	function get_unused_card_details($limit){

		$sql = "
			SELECT 
			    distinct r.cc_tokenex_token as token,
			    r.cc_cvc_encrypted as cvc,
			    r.id as card_id,
			    r.customer_id, r.company_id,
			    r.booking_id
			FROM
			    (
			    SELECT 
			      c.cc_tokenex_token,
			      c.id, 
			      c.cc_cvc_encrypted, 
			      c.customer_id, 
			      IFNULL(b.company_id, bs.company_id) as company_id,
                  IFNULL(b.booking_id, bs.booking_id) as booking_id,
				  MAX(brh.check_out_date) as cod,
                  MAX(brhs.check_out_date) as cods
			    FROM
			      customer_card_detail as c
			    LEFT JOIN booking as b ON b.booking_customer_id = c.customer_id
			    LEFT JOIN booking_staying_customer_list as bscl ON bscl.customer_id = c.customer_id
                LEFT JOIN booking as bs ON bs.booking_id = bscl.booking_id
			    LEFT JOIN booking_block as brh ON brh.booking_id = b.booking_id
			    LEFT JOIN booking_block as brhs ON brhs.booking_id = bscl.booking_id
			    WHERE
			    	cc_tokenex_token != '' AND 
	 	      		cc_tokenex_token IS NOT NULL AND
		 	        is_card_deleted = 0 
			    GROUP BY c.cc_tokenex_token
			  ) as r
			WHERE
			    (r.cod < NOW() - INTERVAL 3 MONTH OR r.cod IS NULL) AND 
			    (r.cods < NOW() - INTERVAL 3 MONTH OR r.cods IS NULL)
            LIMIT $limit ";
		
        $query = $this->db->query($sql);
        $result = $query->result_array();
		$customer = null;
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
		if ($query->num_rows >= 1)
		{
			$customer = $result;
		}

        return $customer;
	}

	function insert_detokenize_cards($detokenize_card_data)
	{
		for ($i = 0, $total = count($detokenize_card_data); $i < $total; $i = $i + 50)
        {
            $card_batch = array_slice($detokenize_card_data, $i, 50);

            $this->db->insert_batch("customer_deleted_token_details", $card_batch);

            if ($this->db->_error_message())
            {
                show_error($this->db->_error_message());
            }
        }
	}

	function update_detokenize_cards($customer_ids, $card_ids, $update_card_data, $table_name)
	{
        $this->db->where_in('customer_id', $customer_ids);
        
        if($table_name == 'customer_deleted_token_details'){
        	$this->db->where_in('card_id', $card_ids);
        } else {
        	$this->db->where_in('id', $card_ids);
        }
        
        $this->db->update($table_name, $update_card_data);

        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
	}

	function delete_unused_customer_cards($customer_ids, $card_ids)
    {
        $this->db->where_in('customer_id', $customer_ids);
        $this->db->where_in('id', $card_ids);
        $this->db->delete("customer_card_detail");

        if ($this->db->_error_message())
		{
            show_error($this->db->_error_message());
		}
        if($this->db->affected_rows() > 0){
            return true;
        }else{
            return false;
        }
    }

    function get_card_details($customer_id, $company_id)
    {
        $this->db->where('customer_id', $customer_id);
        $this->db->where('company_id', $company_id);
        $this->db->where('is_card_deleted', 0);
        $this->db->from('customer_card_detail');
        $query = $this->db->get();
        $result = $query->result_array();
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        $customer = "";
        if ($query->num_rows >= 1)
        {
            $customer = $result[0];
        }

        return $customer;
    }
}
