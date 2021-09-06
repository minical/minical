<?php

class Tax_price_bracket_model extends CI_Model {

	function __construct()
    {        
        parent::__construct(); // Call the Model constructor
    }
	
	function add_price_brakets($data)
	{
		$this->db->insert_batch('tax_price_bracket', $data);		
	}

    function delete_price_brakets($price_braket_id, $tax_type_id)
    {
		if($price_braket_id)
			$this->db->where('id', $price_braket_id);
		
		if($tax_type_id)
			$this->db->where('tax_type_id', $tax_type_id);
		
    	$this->db->delete('tax_price_bracket'); 	
    }
  
    function get_price_brackets($price_braket_id, $tax_type_id)
    {
		if($price_braket_id)
			$this->db->where('id', $price_braket_id);
		
		if($tax_type_id)
			$this->db->where('tax_type_id', $tax_type_id);
		
    	$query = $this->db->get('tax_price_bracket');
    	return $query->result();	
    }

    function create_price_bracket($data){
        $this->db->insert('tax_price_bracket', $data);
    }
	
}
