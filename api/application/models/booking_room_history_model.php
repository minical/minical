<?php

// The purpose of Booking_room_history table is to keep on track of 
// different rooms and the duration of stay in the room per booking.
// Because the guest may not necessarily have stayed in a same room throughout their stay.
// For example, a guest may have stayed in room 101 between March 1 to March 5, then moved to room 102 between March 6 to March 9.

class Booking_room_history_model extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
		
	// Create a new booking_room_history
	function create_booking_room_history($data)
    {
	    $data = $data;        
	    $this->db->insert("booking_block", $data);
        if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
		//return $this->db->insert_id();
        $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
		$result = $query->result_array();
        if(isset($result[0]))
        {  
          return $result[0]['last_id'];
        }
            else
        {  
          return null;
        }
    }

    function get_block($booking_id)
    {   
        $sql = "
            SELECT brh2.booking_id, room_id, room_type_id, brh2.check_in_date, brh2.check_out_date
            FROM booking_block as brh3
            LEFT JOIN
				(
					SELECT booking_id, MIN(brh.check_in_date) as check_in_date, MAX(brh.check_out_date) as check_out_date
					FROM booking_block as brh
					WHERE brh.booking_id = '$booking_id'
				)brh2 ON brh2.booking_id = brh3.booking_id
            WHERE brh3.booking_id = '$booking_id'
            order by brh3.check_out_date DESC
            LIMIT 1
        ";
        
        $q = $this->db->query($sql);
        
        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());
        
        $result = $q->row_array(0);
        
        return $result;
    }
    
	function delete_booking_room_history ($booking_id) {
		$this->db->where('booking_id', $booking_id);
		$this->db->delete('booking_block');
	}

    function check_if_booking_exists_between_two_dates($room_id, $start_date, $end_date, $booking_id = 0, $consider_unconfirmed_reservations = true) {
        // Consider Unconfirmed Reservation as  normal Booking block (reservation, in-house, check-out) 
        // Hence, if there is an unconfirmed reservation block within given parameters, this function will return false
        $unconfirmed_reservation_sql = "";
        if ($consider_unconfirmed_reservations) {
            $unconfirmed_reservation_sql = " AND b.state != '" . UNCONFIRMED_RESERVATION . "'";
        }

        // this query ignores CANCELLED, NO_SHOW, DELETED BOOKINGS (Hence, bookings can be dragged on top of those bookings even if they exist within the date range)
        $sql = "SELECT *                
                    FROM booking_block as brh, booking as b
                    WHERE 
                        brh.room_id = '$room_id' AND 
                        '$start_date' < brh.check_out_date AND brh.check_in_date < '$end_date' AND 
                        brh.booking_id != '$booking_id' AND
                        brh.booking_id = b.booking_id AND
                        (
                            (
                                b.state = '" . RESERVATION . "' OR
                                b.state = '" . INHOUSE . "' OR
                                b.state = '" . CHECKOUT . "'
                            ) AND
                            b.is_deleted != '1'
                        )
                        $unconfirmed_reservation_sql
                        ";

        $q = $this->db->query($sql);

        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());

        if ($q->num_rows() > 0) {
            return true; // room is not vacant  
        }
        return false; // room is vacant
    }
}