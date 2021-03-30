<?php

class Room_location_model extends CI_Model {

	function __construct()
    {        
        parent::__construct(); // Call the Model constructor

        $this->load->model('Image_model');
        $this->load->model('Room_model');
		$this->load->library('PHPRequests');		
    }

	// return company's room_types
    function insert($data)
    {
        $this->db->insert('room_location', $data);
    }
    
    function get_room_location($company_id, $limit = NULL)
    {
        $this->db->select('*');
        $this->db->from('room_location');
        $this->db->where('company_id',$company_id);
        $this->db->where('is_deleted','0');
        if($limit)
        {
            $this->db->order_by("id", "DESC");
            $this->db->limit($limit);
        }
        $query = $this->db->get();
        return $query->result_array();
    }
    
    function update_location($location_id, $data)
    {
        $this->db->where('id', $location_id);
        return $this->db->update('room_location', $data);
    }
    
}
