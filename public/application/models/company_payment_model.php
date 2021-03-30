<?php

class Company_payment_model extends CI_Model {

    function __construct()
    {        
        parent::__construct();
    }		
	
	function insert_company_payment($payments)
    {
	   $this->db->insert_batch("company_payment", $payments);
		
		if ($this->db->_error_message()) 
		{
			show_error($this->db->_error_message());
		}		
    }
		
	function get_company_payments($company_id)
    {		
        $sql = "
			SELECT * 
			FROM company_payment
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
	
	function get_company_payment_total($company_id)
    {		
        $sql = "
			SELECT SUM(amount) as payment_total
			FROM company_payment
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
			return $result[0]['payment_total'];
		}
		return 0;
    }	
	
	function does_transaction_exist($company_id, $chargify_transaction_id)
	{
		$this->db->from('company_payment');
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
	
	function delete_company_payment($company_payment_id) {
		$data['is_deleted'] = '1';
        $this->db->where('company_payment_id', $company_payment_id);
        $this->db->update("company_payment", $data);
		//echo $this->db->last_query();
	}


}