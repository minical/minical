<?php
class Currency_model extends CI_Model {

	function __construct()
	{
		 parent::__construct();
	}
	
	function get_currency_id($currency_code)
	{
		$this->db->select('currency_id');
		$this->db->from('currency');
		$this->db->where('currency_code', $currency_code);
		
		$query = $this->db->get();
		
		if (isset($query->num_rows)) {
			if($query->num_rows() >= 1)
			{		
				$result = $query->result();
				return $result[0]->currency_id;
			}
		}
		return NULL;
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
}

/* End of file - rate_model.php */