<?php
class Currency_model extends CI_Model {

	function __construct()
    {
        parent::__construct();
    }
	
	// Sorted alphabetically by Currencies' country name
	// Get currencies that are available to be associated to the company
	function get_available_currency_list($company_id) 
	{
		$this->db->distinct();
		$this->db->select('cu.currency_id, cu.currency_code, cu.currency_name');
		$this->db->from('currency as cu');
		$this->db->join('company_x_currency as cc', 'cc.currency_id = cu.currency_id', 'left');
        $this->db->where("cc.company_id != '$company_id' or cc.company_id IS NULL");
		$this->db->order_by("cu.currency_name", "asc"); 
		$query = $this->db->get();

		if ($this->db->_error_message()) // error checking
		{
			show_error($this->db->_error_message());
		}
	    if ($query->num_rows >= 1) 
		{	
			return $query->result_array();
		}
		
		return NULL;
	}

	function get_currency_id_by_code($currency_code) 
	{
		$this->db->select('currency_id');
		$this->db->where('currency_code', $currency_code);		
		$query = $this->db->get('currency');
		
	    if ($query->num_rows >= 1) 
		{	
			$q = $query->result();
			return $q[0]->currency_id;
		}
		
		return NULL;
	}
		
	function add_currency($company_id, $currency_id)
  {
        $data = array (
			'company_id' => $company_id,
			'currency_id' => $currency_id
		);
		
		$this->db->insert('company_x_currency', $data);
		
		$this->_set_default_currency_if_there_is_no_default_currency($company_id);
		
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
		
	function change_currency($company_id, $currency_id)
    {
        $data = array (
			'currency_id' => $currency_id
		);
		
		$this->db->where('company_id', $company_id);
		$this->db->update('company_x_currency', $data);
		
		$data = array (
			'default_currency_id' => $currency_id
		);
		
		$this->db->where('company_id', $company_id);
		$this->db->update('company', $data);
		
		$this->_set_default_currency_if_there_is_no_default_currency($company_id);
		
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
	
	function set_default_currency($company_id, $currency_id)
	{
		// first, set all currencies as non-default
		$data = array (
			'default_currency_id' => $currency_id
		);
		$this->db->where('company_id', $company_id);
		$this->db->update('company', $data);
	}
	
	function get_default_currency($company_id)
	{	
		$this->db->select('c.default_currency_id, cu.*');
		$this->db->from('company as c, currency as cu');
		$this->db->where('c.company_id', $company_id);
		// must check this condition, because even if the company may have default_currency_id assigned, that currency and the company may not be linked
		$this->db->where('c.default_currency_id = cu.currency_id');
		$query = $this->db->get();
		
		if ($this->db->_error_message()) // error checking
		{
			show_error($this->db->_error_message());
		}
		
	    if ($query->num_rows >= 1) 
		{	
			$result_array = $query->result_array();
			return $result_array[0];
		}
		
		return NULL;
	}

	//Delete currency and all related room rate entries
	function delete_currency($currency_id, $company_id)
	{
		$this->db->where('currency_id', $currency_id);
		$this->db->where('company_id', $company_id);
		$this->db->delete('company_x_currency');
		
		if ($this->db->_error_message()) // error checking
		{
			show_error($this->db->_error_message());
		}
		
		$this->_set_default_currency_if_there_is_no_default_currency($company_id);
		
	}
	
	function _set_default_currency_if_there_is_no_default_currency($company_id)
	{
		// set default currency among the remaining currencies after the delete. (If the deleted currency was the default currency)
		// if there's no default currency. Set a random currency as a default currency
		if (!$this->get_default_currency($company_id))
		{
			$this->set_default_currency($company_id, 151);			
		}
		
		
	}
	
}