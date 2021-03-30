<?php

class Company_charge_model extends CI_Model {

    function __construct()
    {        
        parent::__construct();
    }		
	
	function insert_company_charge($charges)
    {
		$this->db->insert_batch("company_charge", $charges);
		
		if ($this->db->_error_message()) 
		{
			show_error($this->db->_error_message());
		}		
    }
		
	function get_company_charges($company_id)
    {		
        $sql = "
			SELECT * 
			FROM company_charge
			WHERE 
				company_id = '$company_id'
			ORDER BY date ASC
		";
		
		$q = $this->db->query($sql);
		
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
		$result = $q->result_array();
		
		return $result;
    }	
	
	function get_company_charge_total($company_id)
    {		
        $sql = "
			SELECT SUM(amount) as charge_total
			FROM company_charge
			WHERE 
				company_id = '$company_id' AND
				is_deleted = '0'
		";
		
		$q = $this->db->query($sql);
		
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
		if($q->num_rows() >= 1)
		{
			$result = $q->result_array();
			return $result[0]['charge_total'];
		}
		return 0;
    }	
		
	function get_last_charge($company_id)
	{
		$this->db->from('company_charge');
		$this->db->where('company_id', $company_id);
		$this->db->where('name', 'Subscription Fee');
		$this->db->where('is_deleted', '0');
		$this->db->order_by('date', 'desc');
		
		$q = $this->db->get();
		
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
		if($q->num_rows() >= 1)
		{
			$result = $q->result_array();
			return $result[0];
		}
		return null;
	}
	
	function does_transaction_exist($company_id, $chargify_transaction_id)
	{
		$this->db->from('company_charge');
		$this->db->where('company_id', $company_id);
		$this->db->where('description', $chargify_transaction_id);
		
		$q = $this->db->get();
		
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
		if($q->num_rows() >= 1)
		{
			return true;
		}
		return false;
	}

	function delete_company_charge($company_charge_id) {
		$data['is_deleted'] = '1';
        $this->db->where('company_charge_id', $company_charge_id);
        $this->db->update("company_charge", $data);
		//echo $this->db->last_query();
	}
	
}