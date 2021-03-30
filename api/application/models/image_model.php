<?php

class Image_model extends CI_Model {
	
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    function create_image_group($image_type_id)
    {
        $this->db->insert("image_group", Array("image_type_id" => $image_type_id));
		$image_group_id = $this->db->insert_id();

		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		return $image_group_id;
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
		return NULL;
    }

    function insert_image($data)
	{
		$data = (object) $data;        
        $this->db->insert("image", $data);
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

	}

	// temporarily, we'll require company_id parameter, because not all filesnames are unique.
	// later on though, once all filenames are unique, we can get rid of the company_id condition.
    function delete_image($filename, $image_group_id)
	{
		$this->db->where("image_group_id", $image_group_id);
		$this->db->where("filename", $filename);
        $this->db->delete("image");
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

	}

	function get_dimensions($image_group_id)
    {
    	$this->db->from('image_group as ig');
    	$this->db->from('image_type as it');
    	$this->db->where('ig.image_type_id = it.id');
		$this->db->where('ig.id', $image_group_id);
		
		$query = $this->db->get();
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		if ($query->num_rows >= 1)
		{
			$result = $query->result_array();
			return $result[0];
		}
		return NULL;
    }


}
?>