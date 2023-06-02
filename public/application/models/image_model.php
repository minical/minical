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
		//$image_group_id = $this->db->insert_id();
        $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
		$result = $query->result_array();
        if(isset($result[0]))
        {
          $image_group_id = $result[0]['last_id'];
        }
            else
        {
          $image_group_id = null;
        }

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
		return array();
    }

    function insert_image($data)
	{
		$data = (object) $data;        
        $this->db->insert("image", $data);
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		return $this->db->insert_id();

	}

    function update_image($data)
    {
        // $data = (object) $data;        
        $this->db->where("image_group_id", $data['image_group_id']);
        $this->db->update("image", $data);
        
        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());

        // return $this->db->insert_id();

    }

	// temporarily, we'll require company_id parameter, because not all filesnames are unique.
	// later on though, once all filenames are unique, we can get rid of the company_id condition.
    function delete_image($filename, $image_group_id)
	{
        if(!$filename)
            return;

	    if($image_group_id)
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

    /**
     * This function is used to get the company logo
     */
    function get_company_logo_url($companyId, $logo_image_group_id, $is_mail_html = 0)
    {
        $company_logo = $this->get_images($logo_image_group_id);
        if (!empty($company_logo[0]) && !empty($company_logo[0]['filename'])) {
            $url = $this->image_url.$companyId."/". $company_logo[0]['filename'];
            if ($is_mail_html) {
                return "<div style='text-align: center;'>
                        <img src='".$url."' width='160'/></div><br><br>";
            }
            return $url;
        }
        return "";
    }
}
?>