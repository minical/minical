<?php

class Booking_source_model extends CI_Model {

	function __construct()
    {        
        parent::__construct(); // Call the Model constructor
    }
	
	function get_booking_source($company_id, $show_deleted = false, $show_hidden = false)
	{
		$this->db->where('company_id', $company_id);
		if (!$show_deleted)        
			$this->db->where('is_deleted', 0);
        
        if (!$show_hidden)
            $this->db->where('is_hidden', 0);
        
		$this->db->order_by("sort_order, name", "asc");
		
		$query = $this->db->get('booking_source');
		
		if ($query->num_rows >= 1) 
			return $query->result_array();
		return NULL;
	}
	
	function create_booking_source($company_id, $name, $is_hidden = NULL, $id = NULL, $sort_order = 0)
	{	
        if (!$is_hidden) 
        {
            $data = array(
                'name' => $name,
                'company_id' => $company_id,
                'sort_order' => $sort_order
            );
        }
		else
        {
            $data = array(
                'name' => $name,
                'company_id' => $company_id,
                'is_hidden' => $is_hidden,
                'id' => $id,
                'sort_order' => $sort_order
            );
        }
		
		$this->db->insert('booking_source', $data);		
		//echo $this->db->last_query();
		return $this->db->insert_id();
	}
	
	function update_booking_source($id, $data)
	{
		$data = (object) $data;
		$this->db->where('id', $id);
		$this->db->update('booking_source', $data);
		return TRUE;
	}
    
    function get_booking_source_detail($id, $company_id)
    {
        $this->db->where('id', $id);
        
        if($company_id)
            $this->db->where('company_id', $company_id);
        
        $this->db->from('booking_source');
        $result = $this->db->get()->result_array();
        return $result;
    }
    
    function update_booking_source_for_company($old_id, $new_id, $company_id)
	{
		$data = ['source' => $new_id];
		$this->db->where('source', $old_id);
        $this->db->where('company_id', $company_id);
		$this->db->update('booking', $data);
		return TRUE;
	}
    
    function delete_booking_sources_by_company($company_id, $booking_source_id = null)
	{
        if($booking_source_id) {
            $this->db->where('id', $booking_source_id);
        }
        $this->db->where('company_id', $company_id);
		$this->db->delete('booking_source');
	}
    
    function get_booking_source_by_company($company_id, $name)
	{
		$this->db->where('company_id', $company_id);
        $this->db->where('name', $name);
        $this->db->where('is_deleted', 0);
		
		$query = $this->db->get('booking_source');
		
		if ($query->num_rows >= 1) {
			$result = $query->result_array();
            return isset($result[0]) ? $result[0]['id'] : null;
        }
		return NULL;
	}
    
    function create_common_booking_source_setting($data){
        $this->db->insert('common_booking_source_setting', $data);		
		return $this->db->insert_id();
    }
    
    function get_common_booking_sources_settings($company_id){
        $this->db->where('company_id', $company_id);		
        $this->db->order_by('sort_order', 'ASC');		
		$query = $this->db->get('common_booking_source_setting');
		$response = array();
		if ($query->num_rows >= 1) {
			$result = $query->result_array();
            foreach($result as $setting)
            {
                $response[$setting['booking_source_id']] = $setting;
            }
        }
        return $response;
    }
    
    function update_common_booking_sources_settings($company_id, $booking_source_id, $data)
    {
        $this->db->where('booking_source_id', $booking_source_id);
        $this->db->where('company_id', $company_id);
		$this->db->delete('common_booking_source_setting');
        
        return $this->create_common_booking_source_setting($data);
    }

    function delete_booking_sources($company_id)
    {
        $data = Array('is_deleted' => 1);

        $this->db->where('company_id', $company_id);
        $this->db->update("booking_source", $data);

        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
    }
}
