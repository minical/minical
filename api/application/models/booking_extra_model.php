<?php

class Booking_extra_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }
	
	function create_booking_extra(
							$booking_id, 
							$extra_id, 
							$start_date, 
							$end_date, 
							$quantity, 
							$rate)
	{		
		$data = array (
			'booking_id' => $booking_id,
			'extra_id' => $extra_id,
			'start_date' => $start_date,
			'end_date' => $end_date,
			'quantity' => $quantity,
			'rate' => $rate
		);
		
		$this->db->insert('booking_x_extra', $data);	
		//$booking_extra_id = $this->db->insert_id();
        $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
		$result = $query->result_array();
        if(isset($result[0]))
        {  
          $booking_extra_id = $result[0]['last_id'];
        }
            else
        {  
          $booking_extra_id = null;
        }
		
		if ($this->db->_error_message()) 
		{
			show_error($this->db->_error_message());
		}
		return $booking_extra_id;
	}
	
	function update_booking_extra($booking_extra_id, $data)
	{	
		$data = (object) $data;
		$this->db->where('booking_extra_id', $booking_extra_id);
		$this->db->update('booking_x_extra', $data);

		if ($this->db->_error_message()) 
		{
			show_error($this->db->_error_message());
		}
		return TRUE;
	}
	
    function get_booking_extra($booking_extra_id)
	{
		$this->db->where('booking_extra_id', $booking_extra_id);
    	$this->db->from('booking_x_extra');
        
        $query = $this->db->get();
        $result = $query->result();
		
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
		if ($query->num_rows >= 1)
		{
			return $result[0];
		}
        return null;
	}
    
	function delete_booking_extra($booking_x_extra_id)
	{		
		$this->db->where('booking_x_extra_id', $booking_x_extra_id);
		$this->db->delete('booking_x_extra');
		if ($this->db->_error_message()) 
		{
			show_error($this->db->_error_message());
		}
	}
	
	function get_booking_extras($booking_id)
	{
		$this->db->from('booking_x_extra as bxe, extra as e');
		$this->db->where('bxe.booking_id', $booking_id);
		$this->db->where('bxe.extra_id = e.extra_id');
		$this->db->where('e.is_deleted', 0);
		$results = $this->db->get();
		if ($this->db->_error_message()) 
		{
			show_error($this->db->_error_message());
		}
		if (empty($results))
		{		
			return array();
		}
		else
		{			
			return $results->result_array();
		}		
	}
}