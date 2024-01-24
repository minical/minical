<?php

class Room_type_model extends CI_Model {

    function __construct()
    {        
        parent::__construct(); // Call the Model constructor
      
    }

    // return company's room_types

    function get_room_types($company_id, $adult_count = null, $children_count = null, $company_group_id = null)
    {
        $this->db->select("rt.*, count(rp.rate_plan_id) as rate_plan_count");
        $this->db->from("room_type as rt");
        $this->db->join("rate_plan as rp", "rp.room_type_id = rt.id and rp.is_deleted != '1' and rp.is_selectable = '1'", "left");
        if($company_group_id)
        {
            $this->db->join("company_groups_x_company as cgxc", "cgxc.company_id = rt.company_id", "left");
            $this->db->where('cgxc.company_group_id', $company_group_id);
        }
        else
        {
            $this->db->where('rt.company_id', $company_id);
        }
        if($adult_count){
            $this->db->where("rt.max_adults >= $adult_count");
        }
        if($children_count){
            $this->db->where("rt.max_children >= $children_count");
        }
        if($adult_count || $children_count){
            $total_occupants = (int)$adult_count + (int)$children_count;
            $this->db->where("rt.max_occupancy >= $total_occupants");
            $this->db->where("rt.min_occupancy <= $total_occupants");
        }
        $this->db->where('rt.is_deleted', 0);
        $this->db->group_by("rt.id"); // needs to be in asc order, so newly added room types are in the bottom of the list
        $this->db->order_by("rt.sort", "asc"); // needs to be in asc order, so newly added room types are in the bottom of the list

        $query = $this->db->get();

        if ($query->num_rows >= 1) {
            return $query->result_array();
        }

        return null;
    }
   
        
}
