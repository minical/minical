<?php

class Image_model extends CI_Model {
	
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }


    function get_images($image_group_id)
    {
    	$this->db->from('image');
		$this->db->where('image_group_id', $image_group_id);
		
		$query = $this->db->get();
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		if ($query->num_rows >= 1)
		{
			$result = $query->result_array();
			return $result;
		}
		return array();
    }

   
}
?>
