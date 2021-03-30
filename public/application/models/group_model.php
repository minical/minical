<?php

class Group_model extends CI_Model {

	function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }	

	function get_groups(){
        $this->db->select('*');
        $this->db->from('group');
        $query = $this->db->get();
        return $query->result_array();
    }
    function get_booking_group($booking_id){
        $this->db->select('group_id');
        $this->db->from('booking_x_group');
        $this->db->where('booking_id',$booking_id);
        $this->db->order_by("booking_id", "DESC");
        $this->db->limit('1');
        $query = $this->db->get();
        return $query->result_array();
    }
    function create_booking_group($data){
        // delete previous associations
        $this->db->where('booking_id', $data['booking_ids']);
        $this->db->delete("booking_x_group");
        
        $insert_data = array( 'booking_id' => $data['booking_ids'],
                              'group_id' => $data['group_id'] 
                        );
        $this->db->insert('booking_x_group', $insert_data);
    }
    
    function update_customer_booking_group($customer_id)
    {
        $sql = "DELETE FROM
                    booking_x_group
                WHERE
                    booking_id IN(
                        SELECT
                            booking_id
                        FROM
                            booking
                        WHERE
                            booking_customer_id = '$customer_id'
                    )";
        $this->db->query($sql);
    }
    
}