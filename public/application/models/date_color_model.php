<?php

class Date_color_model extends CI_Model {

	function __construct()
    {
        // Call the Model constructor
        
		$this->load->library('PHPRequests');	
        parent::__construct();
    }
    
    function insert($data)
    {
        $this->db->insert('date_color', $data);
    }
    
    function get_date_color($id = null)
    {
        $this->db->select('*');
        $this->db->from('date_color');
        $this->db->where('is_deleted', 0);
        $this->db->where('company_id', $this->company_id);
        $this->db->where('user_id', $this->user_id);
        if($id != null)
        {
        $this->db->where('id', $id);
        }
        $query = $this->db->get();
        $row = $query->result_array();
        return $row;
    }
    
    function update_color($id, $data)
    {
        $data = (object) $data;
		$this->db->where('id', $id);
        $this->db->update("date_color", $data);
        
    }
}