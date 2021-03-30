<?php

class Payment_model extends CI_Model {

	function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
    
    function get_payments($booking_id, $exclude_deleted_bookings = false)
    {
		$this->db->select('p.*');
		if($exclude_deleted_bookings) {
			$this->db->join('booking as b', 'b.booking_id = p.booking_id', 'left');
			$this->db->where('b.is_deleted', '0');
		}
		
        $this->db->where('p.booking_id', $booking_id);
        $this->db->where('p.is_deleted', 0);
        $query = $this->db->get('payment as p');
		
        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return null;
        }
    }
    
    function insert_payments($payment_data)
    {
        $this->db->insert_batch('payment', $payment_data); 
        
        if ($this->db->_error_message())
		{
			return show_error($this->db->_error_message());
		}

    }
}

