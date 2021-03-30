<?php

class Card_model extends CI_Model {

	function __construct()
    {
        parent::__construct();
    }
    
    function create_customer_card($data)
    {   
		$this->db->insert("customer_card_detail", $data);		
		
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
    }
    function update_customer_card($data) 
    {		
		
        $this->db->where('is_primary', 1);
		$this->db->where('customer_id',$data['customer_id']);
        $this->db->update("customer_card_detail", $data);
        //echo $this->db->last_query();
        if($this->db->affected_rows() > 0){
            return "success";
        }else{
            return "fail";
        }
    }
}
