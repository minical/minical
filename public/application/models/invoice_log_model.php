<?php

class Invoice_log_model extends CI_Model {

    function __construct()
    {        
        parent::__construct();
    }
    
    function insert_log($data)
    {
        $data = (object) $data;        
        $this->db->insert("invoice_log", $data);
		
        if ($this->db->_error_message()) 
        {
                show_error($this->db->_error_message());
        }		

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
	
    function get_invoice_logs($booking_id)
    {		
        $sql = "
			SELECT il.*, u.id, up.first_name, up.last_name, u.email
			FROM invoice_log as il
			LEFT JOIN users as u ON il.user_id = u.id
			LEFT JOIN user_profiles as up ON u.id = up.user_id
			WHERE 
				il.booking_id IN ($booking_id)
			ORDER BY date_time ASC
		";
		$q = $this->db->query($sql);
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		$result = $q->result_array();
        return $result;
    }	
}