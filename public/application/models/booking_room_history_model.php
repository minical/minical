<?php

// The purpose of Booking_room_history table is to keep on track of 
// different rooms and the duration of stay in the room per booking.
// Because the guest may not necessarily have stayed in a same room throughout their stay.
// For example, a guest may have stayed in room 101 between March 1 to March 5, then moved to room 102 between March 6 to March 9.

class Booking_room_history_model extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    // Create a new booking_room_history
    function create_booking_room_history($data) {
        $data = (object) $data;
        $this->db->insert("booking_block", $data);
        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());

        $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
        $result = $query->result_array();
        if (isset($result[0])) {
            return $result[0]['last_id'];
        } else {
            return null;
        }
        // insert_id function won't work as it converts id(bigint) to int, results in incorrect value
//		return $this->db->insert_id();
    }

    function insert_booking_room_history_batch ($booking_history_batch) {
        if ($booking_history_batch && count($booking_history_batch) > 0) {
            // just to be safe, only insert in batch of 50, sometimes it doesn't insert more than 100 at once
            for ($i = 0, $total = count($booking_history_batch); $i < $total; $i = $i + 50) {
                $booking_batch = array_slice($booking_history_batch, $i, 50);
                $this->db->insert_batch("booking_block", $booking_batch);
            }
        }
    }


    // get earliest check in date, and the latest check out date that belongs to the booking
    // also grab latest booking_room_history's room_id
    function get_booking_detail($booking_id) {

        $sql = "
                SELECT brh2.booking_id, brh2.booking_room_history_id, room_id, room_type_id, brh2.check_in_date, brh2.check_out_date, brh3.booking_room_history_id
                FROM booking_block as brh3
                LEFT JOIN
                    (
                    SELECT booking_room_history_id , booking_id, MIN(brh.check_in_date) as check_in_date, MAX(brh.check_out_date) as check_out_date
                    FROM booking_block as brh
                    WHERE brh.booking_id = '$booking_id'
                    )brh2 ON brh2.booking_id = brh3.booking_id
                WHERE brh3.booking_id = '$booking_id'
                order by brh3.check_out_date DESC
                LIMIT 1
            ";
        // }

        $q = $this->db->query($sql);

        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());

        $result = $q->row_array(0);

        return $result;
    }

    // Update room_id of a corresponding booking_room_history_row
    function update_room_id($booking_room_history_row, $new_room_id, $new_room_type_id = null) {
		$update_room_type_id = '';
		if(isset($new_room_type_id) && $new_room_type_id)
		{
			$update_room_type_id = ", room_type_id = '$new_room_type_id'";
		}
        $query = "	UPDATE booking_block 
					SET room_id = '$new_room_id' $update_room_type_id
					WHERE 	booking_room_history_id  = '" . $booking_room_history_row['booking_room_history_id'] . "' AND
							check_in_date = '" . $booking_room_history_row['check_in_date'] . "' AND 
							check_out_date = '" . $booking_room_history_row['check_out_date'] . "';
				 ";

        $this->db->query($query);

        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());
    }

    // find the exact matching booking_room_history (using $before), and update as $after
    function update_booking_room_history($before, $after) {
        $this->db->where('booking_id', $before['booking_id']);
        $this->db->where('room_id', $before['room_id']);

        // don't use where condition unless $after variable is used
        if (isset($after['check_in_date'])) {
            $this->db->where('check_in_date', $before['check_in_date']);
        }

        if (isset($after['check_out_date'])) {
            $this->db->where('check_out_date', $before['check_out_date']);
        }

        $this->db->update("booking_block", $after);

        //echo $this->db->last_query();

        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());
    }

    // Update check_in_date of a corresponding booking_room_history_row
    // Used for reservations only
    function update_check_in_date($brh, $check_in_date) {
        if (isset($brh['booking_room_history_id']) && $brh['booking_room_history_id']) {
            $this->db->where('booking_room_history_id', $brh['booking_room_history_id']);
            $this->db->update("booking_block", Array('check_in_date' => $check_in_date));
        } else {
            $this->db->where('booking_id', $brh['booking_id']);
            $this->db->where('room_id', $brh['room_id']);

            $this->db->update("booking_block", Array('check_in_date' => $check_in_date));
        }

        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());
    }

    // Update check_out_date of a corresponding booking_room_history_row
    function update_check_out_date($booking_room_history_row, $check_out_date) {
        if (isset($booking_room_history_row['booking_room_history_id']) && $booking_room_history_row['booking_room_history_id']) {
            $query = "UPDATE booking_block 
                        SET check_out_date = '$check_out_date'
                        WHERE booking_room_history_id = '" . $booking_room_history_row['booking_room_history_id'] . "' 
                ";
        } else {
            $where = '';

            if (isset($booking_room_history_row['check_in_date']) && $booking_room_history_row['check_in_date'] != '') {
                $where = " AND check_in_date = '" . $booking_room_history_row['check_in_date'] . "'";
            }
            $query = "UPDATE booking_block
                            SET check_out_date = '$check_out_date'
                            WHERE 	booking_id = '" . $booking_room_history_row['booking_id'] . "' AND
                            room_id = '" . $booking_room_history_row['room_id'] . "' 
                        $where
                     ";
        }
     
        $this->db->query($query);

        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());
    }
    
    function update_noshow_booking($booking_id, $checkin_date, $checkout_date)
    {                
		
		$this->db->where('booking_id', $booking_id);
        $q = $this->db->get("booking_block");

        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());
		
		$booking_block_ids = [];
		$booking_block_id = null;
        $booking_blocks = $q->result();
        if (sizeof($booking_blocks) >= 2) {
            foreach ($booking_blocks as $block) {
				$booking_block_ids[] = $block->booking_room_history_id;
			}
			$booking_block_id = min($booking_block_ids);
		}
		
		if ($booking_block_id) {
			
			$this->db->where('booking_id', $booking_id);
			$this->db->where("booking_room_history_id != '$booking_block_id'");

			$this->db->delete('booking_block');
		}
		
        $data = array(
               'check_in_date' => $checkin_date,
               'check_out_date' => $checkout_date
            );

        $this->db->where('booking_id', $booking_id);
        $this->db->update('booking_block', $data); 
        
        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());
    }

    // return the latest (most recent check-out-date) row that has corresponding booking_id
    function get_latest_booking_room_history($booking_id, $is_booking_details = false) {

        if($is_booking_details) {

            $sql = "
                SELECT *
                FROM booking_block as bb
                LEFT JOIN booking as b on b.booking_id = bb.booking_id 
                WHERE bb.booking_id = '$booking_id'
                order by bb.check_in_date DESC
                LIMIT 1
            ";
        } else {
            $sql = "
                SELECT *
                FROM booking_block
                WHERE booking_id = '$booking_id'
                order by check_in_date DESC
                LIMIT 1
            ";
        }
       
        $q = $this->db->query($sql);

        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());

        $result = $q->row_array(0);

        return $result;
    }

    /**
     * A function that returns true if the room is not vacant between two dates. Otherwise, returns false
     * @param string $booking_id Booking ID of a booking that is being moved. Hence, this booking must NOT be considered as a block
     * @param string $room_id
     * @param string $start_date 
     * @param string $end_date 
     */
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
								b.state = '" . RESERVATION . "'	OR
								b.state = '" . INHOUSE . "'	OR
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

    // Combine any booking blocks that are unnecessarily separated
    // For example, if there are two bookings with same room and one booking's checkout date is same as other's check in date, combine them into one
    function check_and_combine_booking_blocks($booking_id) {
        $this->db->where('booking_id', $booking_id);
        $q = $this->db->get("booking_block");

        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());

        $booking_blocks = $q->result();
        if (sizeof($booking_blocks) >= 2) {
            foreach ($booking_blocks as $block_a) {
                foreach ($booking_blocks as $block_b) {
                    // if block a's check out date is same as block b's check in date, and both block's room are the same
                    // change block a's check out date to block b's check out date, and delete block b
                    if ($block_a->check_out_date == $block_b->check_in_date && $block_a->room_id == $block_b->room_id) {
                        $data = array(
                            'booking_id' => $block_a->booking_id,
                            'check_in_date' => $block_a->check_in_date,
                            'check_out_date' => $block_b->check_out_date
                        );

                        $this->db->where('booking_id', $block_a->booking_id);
                        $this->db->where('check_in_date', $block_a->check_in_date);
                        $this->db->where('check_out_date', $block_a->check_out_date);
                        $this->db->where('room_id', $block_a->room_id);

                        $this->db->update('booking_block', $data);

                        $this->db->where('booking_id', $block_b->booking_id);
                        $this->db->where('check_in_date', $block_b->check_in_date);
                        $this->db->where('check_out_date', $block_b->check_out_date);

                        $this->db->delete('booking_block');
                    }
                }
            }
        }
    }

    // delete any booking blocks that have checkin and chekout date greater than booking's final checkout date
	// delete any booking blocks that have checkin and chekout date lesser than booking's final checkin date
    function delete_extra_booking_blocks($booking_id, $check_out_date = null, $check_in_date = null) {
        
        $sql = "
            SELECT MIN(booking_room_history_id) as first_block_id, COUNT(booking_room_history_id) as block_count
            FROM booking_block
            WHERE booking_id = '$booking_id'
            order by check_in_date DESC
            LIMIT 1";
       
        $q = $this->db->query($sql);

        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());
        
        $result = $q->row_array(0);
        
        if ($booking_id && $check_out_date && $result['block_count'] > 1) {
            
            $this->db->where('booking_id', $booking_id);
            $this->db->where("check_in_date >= '$check_out_date'");
            $this->db->where("check_out_date > '$check_out_date'");
            $this->db->where("booking_room_history_id != '{$result['first_block_id']}'");
            $this->db->delete('booking_block');
            
            //echo $this->db->last_query();
            
            if ($this->db->_error_message()) // error checking
                show_error($this->db->_error_message());
            
        }
		
		
		$sql = "
            SELECT MAX(booking_room_history_id) as last_block_id, COUNT(booking_room_history_id) as block_count
            FROM booking_block
            WHERE booking_id = '$booking_id'
            order by check_in_date DESC
            LIMIT 1";
       
        $q = $this->db->query($sql);

        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());
        
        $result = $q->row_array(0);
        
        if ($booking_id && $check_in_date && $result['block_count'] > 1) {
            
            $this->db->where('booking_id', $booking_id);
            $this->db->where("check_in_date < '$check_in_date'");
            $this->db->where("check_out_date <= '$check_in_date'");
            $this->db->where("booking_room_history_id != '{$result['last_block_id']}'");
            $this->db->delete('booking_block');
            
            //echo $this->db->last_query();
            
            if ($this->db->_error_message()) // error checking
                show_error($this->db->_error_message());
            
        }
    }

    
    function get_booking_block_count($booking_id) {
        $this->db->from('booking_block');
        $this->db->where("booking_id", $booking_id);
        $query = $this->db->get();

        if ($this->db->_error_message()) {
            show_error($this->db->_error_message());
        }

        return $query->num_rows();
    }

    /* Better approach 2015-03-09 Jaeyun */

    function get_booking_blocks($booking_id) {
        $this->db->from('booking_block');
        $this->db->where("booking_id", $booking_id);
        $this->db->order_by("check_in_date");
        $query = $this->db->get();

        if ($this->db->_error_message()) {
            show_error($this->db->_error_message());
        }

        return $query->result_array();
    }

    function get_block($booking_id) {
        $sql = "
            SELECT brh2.booking_id, room_id, brh2.check_in_date, brh2.check_out_date
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

    function get_last_inserted_booking_room_history_id($booking_id) {
        $sql = "
                SELECT MAX(booking_room_history_id) as booking_room_history_id from booking_block
                WHERE booking_id = '$booking_id'
            ";

        $query = $this->db->query($sql);
        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());

        $result = $query->row_array(0);

        return $result['booking_room_history_id'];
    }
	
    }
