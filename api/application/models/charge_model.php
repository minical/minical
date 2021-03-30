<?php

class Charge_model extends CI_Model {

	function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }	
    
    function get_last_applied_charge($booking_id, $charge_type_id, $end_date = null, $only_night_audit_charge = false)
	{
		$this->db->where('charge_type_id', $charge_type_id);		
        $this->db->where('booking_id', $booking_id);		
        $this->db->where('is_deleted', 0);		
        if($only_night_audit_charge) {
            $this->db->where('is_night_audit_charge', 1);	
        }
        
        if($end_date){
            $this->db->where('selling_date <', $end_date);	
        }
        
        $this->db->order_by('selling_date', 'DESC');		
        $this->db->limit(1);		
		$query = $this->db->get('charge');
		
		if ($query->num_rows() > 0)
		{
			return $query->result_array()[0];
		}
		else
		{
			return null;
		}
	}
    function get_charges($booking_id, $exclude_deleted_bookings = false, $company_id = null)
    {
		$this->db->select('c.*');
		
		if($exclude_deleted_bookings) {
			$this->db->join('booking as b', 'b.booking_id = c.booking_id', 'left');
			$this->db->where('b.is_deleted', '0');
		}

        if($company_id) {
            $this->db->join('booking as b', 'b.booking_id = c.booking_id', 'left');
            $this->db->where('b.company_id', $company_id);
        }
		
        $this->db->where('c.booking_id', $booking_id);

        $this->db->where('c.is_deleted', '0');
		
        $query = $this->db->get("charge as c");
				
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        else{
            return null;
        }
    }	
    
    function insert_charges($charge_data)
    {
        $this->db->insert_batch('charge', $charge_data); 
        
        if ($this->db->_error_message())
		{
			return show_error($this->db->_error_message());
		}
    }
}