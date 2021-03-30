<?php

class Extra_model extends CI_Model {

	function __construct()
    {
        parent::__construct();
    }

	function create_extra($data)
	{
		$this->db->insert('extra', $data);		
		
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		else
		{
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
	
	function update_extra($extra_id, $data, $company_id = null)
	{
		$data = (object) $data;
		
		if ($company_id != null) 
		{
			$this->db->where('company_id', $company_id);
		}
		
		$this->db->where('extra_id', $extra_id);
		$this->db->update('extra', $data);
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		return TRUE;
	}
	
}

/* End of file - extra_model.php */