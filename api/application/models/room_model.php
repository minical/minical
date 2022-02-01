<?php

class Room_model extends CI_Model {

	function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    // get available rooms based on date and roomtype
    // include room already selected for booking ($booking_id)
	// ignore rooms that have state lower than 3. (not reservation, checkin, nor checkout)
    function get_available_room_id(
								$check_in_date, 
								$check_out_date, 
								$room_type_id = 'undefined', 
								$booking_id = "undefined", 
								$company_id = "undefined",
								$can_be_sold_online = 0,
								$adults_count = "undefined",
								$children_count = "undefined",
								$subscription_level = null,
                                $book_over_unconfirmed_reservations = 0
                            )
	{
		$order_by = "";
		if($subscription_level == ELITE)			
		{
			$order_by = " r.score DESC,";
		}

		$room_list ="";
		
		$room_type_sql= '';
		if ($room_type_id != 'undefined'){
			$room_type_sql = "r.room_type_id = '$room_type_id' AND";
		}
		
		if ($company_id == "undefined") {
			$company_id = $this->session->userdata('current_company_id');
		}
		
		$can_be_sold_online_sql = '';
		if ($can_be_sold_online == 1) {
			$can_be_sold_online_sql = "r.can_be_sold_online = '1' AND";
		}
		
		$max_adult_sql = '';
		$max_children_sql = '';
		if ($adults_count != 'undefined' && $children_count != 'undefined') {
			//for each room type
			$max_adult_sql = "rt.max_adults >= '$adults_count' AND";				
			$max_children_sql = "rt.max_children + (rt.max_adults - '$adults_count') >= '$children_count' AND";			
		}

		$check_out_date_label = "IF(rt2.prevent_inline_booking = 1, DATE(brh.check_out_date) + INTERVAL 1 DAY, DATE(brh.check_out_date))";
        $check_in_date_label = "IF(rt2.prevent_inline_booking = 1, DATE(brh.check_in_date) - INTERVAL 1 DAY, DATE(brh.check_in_date))";

        $booking_state_sql = $book_over_unconfirmed_reservations ? "b.state < 4 AND" : "(b.state < 4 OR b.state = 7) AND";

        $sql = "SELECT DISTINCT
				r.room_id
				FROM room as r
				LEFT JOIN 
				(
					#occupied rooms
					SELECT  brh.room_id, brh.booking_id
					FROM booking as b, booking_block as brh
					LEFT JOIN room_type rt2 ON rt2.id = brh.room_id
					WHERE
					(
						($check_out_date_label > '$check_in_date' AND '$check_out_date' > $check_in_date_label) AND 
						brh.booking_id != '$booking_id'
					) AND #include currently selected room in the available room list
					b.booking_id = brh.booking_id AND
					$booking_state_sql
					b.is_deleted != '1' AND
					b.company_id = '$company_id' AND
					$check_in_date_label < $check_out_date_label
				)ot ON r.room_id = ot.room_id
				LEFT JOIN room_type rt
				ON
				rt.id = r.room_type_id
				WHERE
				r.company_id = '$company_id' AND
				r.is_deleted = '0' AND
				$room_type_sql
				$can_be_sold_online_sql
				$max_adult_sql
				$max_children_sql
				ot.booking_id IS NULL
				ORDER BY $order_by r.room_name
				LIMIT 0,1
				";
		
		$data = array();
        $q = $this->db->query($sql);
		//echo $this->db->last_query(); // enabling this line will disable the functionality of getting available rooms
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

        $array_result = $q->result_array();
        if (isset($array_result[0]['room_id']))
        {
        	return $array_result[0]['room_id'];
        }
        return null;
    }

    function get_rooms_by_room_type_id($room_type_id, $subscription_level = null)
	{
		$order_by = "";
		if($subscription_level == ELITE)			
		{
			$order_by = "ORDER BY r.score DESC";
		}

		$sql = "SELECT
				r.room_id, r.room_name as room_name, r.status, r.room_type_id
				FROM room as r
				WHERE 
					r.room_type_id = '$room_type_id' AND
					r.is_deleted = '0' AND
					r.can_be_sold_online = '1'
				$order_by
				";
		
		$data = array();
        $q = $this->db->query($sql);
		//echo $this->db->last_query(); // enabling this line will disable the functionality of getting available rooms
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

        return $q->result();
	}
	
	function get_room($room_id) {
	
		$sql ="
		SELECT * FROM room as r
		LEFT JOIN room_type as rt ON rt.id = r.room_type_id		
		WHERE r.room_id = '$room_id';
		";		
		
        $q = $this->db->query($sql);
		//echo $this->db->last_query();
        // return result set as an associative array
        $result = $q->row_array(0);
		
		return $result;

	}
    
    function get_availability_for_room_by_date_range($start_date, $end_date, $room_type_id, $room_id, $subscription_level = null, $book_over_unconfirmed_reservations = 0)
    {
    	$order_by = "";
		if($subscription_level == ELITE)			
		{
			$order_by = "ORDER BY r.score DESC";
		}

		$booking_state_sql = $book_over_unconfirmed_reservations ? "(b.state < 4 OR b.state = '5') AND" : "(b.state < 4 OR b.state = '5' OR b.state = 7) AND";

        $sql = "
                SELECT  
                    di.date,
                    r.room_name,
                    r.room_id,
                    (
                        SELECT
                            b.booking_id 
                        FROM 
                            booking as b, 
                            booking_block as brh,
                            room as r 
                        WHERE 
                            DATE(brh.check_out_date) > di.date AND 
                            di.date >= DATE(brh.check_in_date) AND 
                            b.booking_id = brh.booking_id AND 
                            (b.state < 4  || b.state = '5' ) AND 
                            $booking_state_sql
                            b.is_deleted = '0' AND
                            r.room_type_id = rt.id AND
                            brh.room_id = r.room_id AND
                            r.is_deleted = '0' AND 
                            r.room_id = '$room_id'
                            LIMIT 0, 1
                    ) as booking 
                FROM 
                    date_interval as di, 
                    room_type as rt
                LEFT JOIN 
                    room as r ON r.room_type_id = rt.id AND r.is_deleted = '0'
                WHERE 
                    rt.id  = '$room_type_id' AND
                    di.date >= '$start_date' AND
                    di.date <= '$end_date' AND
                    rt.is_deleted = '0' AND
                    r.room_id = '$room_id'
                $order_by
            ";

        $query = $this->db->query($sql);
      
        if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
        
        $result_array = array();
        
        if($query->num_rows() > 0)
        {
            $results = $query->result_array();

            foreach($results as $result)
            {
                if($result['booking'] == "" )
                    $result_array[$result['date']] = $result['room_id'];
                
            }
           
            return $result_array; 
        }
        return NULL; 
    }

    function get_rooms($company_id, $sort_by = 'sort_order', $where = '',  $get_hidden_room = null)
    {
        $result = array();
        $include_hidden_room_condition = " AND r.is_deleted = 0 AND r.is_hidden = 0";
        if($get_hidden_room)
        {
            $include_hidden_room_condition = "AND (r.is_deleted = 0 OR r.is_hidden = 1)";
        }
        $sql = "	SELECT
                        r.room_name,
                        r.room_id,
                        r.can_be_sold_online,
                        r.room_type_id,
                        r.group_id,
                        r.status,
                        r.sort_order,
                        r.floor_id,
                        r.location_id,
                        r.is_hidden,
                        rt.acronym,
                        rt.name as room_type_name,
                        rt.description as room_type_description,
                        rt.id					
					FROM room as r
					LEFT JOIN 
                        room_type as rt ON r.room_type_id = rt.id $include_hidden_room_condition
					WHERE r.company_id = '$company_id'  					
                        $include_hidden_room_condition		
                        $where
					ORDER BY $sort_by ASC, r.room_name ASC
					";

        $q = $this->db->query($sql);
        //ECHO $this->db->last_query();
        // error checking
        if ($this->db->_error_message()) {
            show_error($this->db->_error_message());
        }

        if ($q->num_rows() > 0) {
            $result = $q->result_array();
        }

        return $result;
    }

    function get_available_rooms(
								$check_in_date = null, 
								$check_out_date = null, 
								$room_type_id = null, 
								$booking_id = null, 
								$company_id = null,
								$can_be_sold_online = 0,
								$adults_count = null,
								$children_count = null,
								$room_id = null,
								$company_group_id = null
								)
	{			

		$room_list = "";
		
		$room_type_sql = '';
		if ($room_type_id){
				$room_type_sql = "r.room_type_id = '".$room_type_id."' AND";	
			}
		
		if (!$company_id) {
			$company_id = $this->session->userdata('current_company_id');
		}

		$company_sql_where_condition = " r.company_id = '$company_id' AND ";
		$company_select_sql = "";
		if($company_group_id)
		{
			$company_select_sql = "company_groups_x_company as cgxc,";
			$company_sql_where_condition = " cgxc.company_id = rt.company_id AND cgxc.company_group_id = $company_group_id AND ";
		}
		
		$can_be_sold_online_sql = '';
		if ($can_be_sold_online == 1) {
			$can_be_sold_online_sql = "r.can_be_sold_online = '1' AND";
		}
		
		$room_id_sql = "";
		if ($room_id) {
			$room_id_sql = "brh.room_id != '$room_id' AND";
		}
		
		$max_adult_sql = '';
		$max_children_sql = '';
		if ($adults_count && $children_count) {
			//for each room type
			$max_adult_sql = "rt.max_adults >= '$adults_count' AND";				
			$max_children_sql = "rt.max_children + (rt.max_adults - '$adults_count') >= '$children_count' AND";			
		}

		// if($this->enable_hourly_booking){
		// 	$date_condition = " brh.check_out_date > '$check_in_date' AND '$check_out_date' > brh.check_in_date ";
		// } else {
			$date_condition = " DATE(brh.check_out_date) > '$check_in_date' AND '$check_out_date' > DATE(brh.check_in_date) ";
		// }
		
        $sql = "SELECT 
					DISTINCT r.room_id, r.room_name as room_name, r.status, r.room_type_id, rt.acronym
				FROM room_type as rt, $company_select_sql room as r
				LEFT JOIN 
				(
					SELECT  brh.room_id, brh.booking_id
					FROM booking as b, booking_block as brh
					WHERE
					(
						$date_condition
					) AND #include currently selected room in the available room list
					b.booking_id = brh.booking_id AND
					b.booking_id != '$booking_id' AND
					$room_id_sql
					(b.state < 4 OR b.state = 7) AND
					b.is_deleted != '1' AND
					b.company_id = '$company_id' AND
					brh.check_in_date < brh.check_out_date
				)ot
				ON
					r.room_id = ot.room_id
				
				WHERE
				$company_sql_where_condition
				r.is_deleted = '0' AND
				rt.id = r.room_type_id AND
				$room_type_sql
				$can_be_sold_online_sql
				$max_adult_sql
				$max_children_sql
				ot.booking_id IS NULL
				ORDER BY r.room_name
				";
		
		$data = array();
        
        $q = $this->db->query($sql);
		//echo $this->db->last_query(); // enabling this line will disable the functionality of getting available rooms
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

        return $q->result_array();

    }

}