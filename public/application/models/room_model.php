<?php

class Room_model extends CI_Model {

	function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     * Get rooms
     * @param integer $company_id
     * @param string  $sort_by
     * @return array
     */
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
	
		
	// return number of rooms that are actually entered on room table
	function get_number_of_rooms($company_id) {
		$sql = "SELECT 
					COUNT(r.room_name) as total
					FROM 
						room as r
					WHERE 
						r.company_id = '$company_id' AND 
						r.is_deleted = 0					
					";
		
		$q = $this->db->query($sql);
		//echo $this->db->last_query();		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
		
		$result = $q->row_array(0);
		
		return $result['total'];	
	}
	
	
	// Rooms main page.
	function get_room_inventory($date, $include_checkedout_state = true)
	{
		$sql = $this->_get_filtered_query($date, $include_checkedout_state);
		
		$q = $this->db->query($sql);
		//echo $this->db->last_query();
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		$result = $q->result();
		
		return $result;
	}
	
	// New housekeeping report - 2012-10-07 by Jaeyun
	// Report displays 7 days in seperate columns. Indicating which rooms need to be cleaned with what kind of method

    function _get_filtered_query($date, $include_checkedout_state = true)
    {
        $where_state = ""; $user_wise ='';
        if($include_checkedout_state){
            $where_state = " OR b.state = '".CHECKOUT."'";
        }
        
        if($this->is_housekeeper_manage_enabled == 1){
        	 $order = "ORDER BY room_info.state DESC";

          if($this->user_permission == 'is_housekeeping'){
              $user_wise =' AND r.group_id ='.$this->user_id;
          }

        }else {
        	 $order = "ORDER BY room_name ASC";
        }
        
        $company_id = $this->session->userdata('current_company_id');
        $sql        = "
			SELECT
			    booking_status,
				r.room_id,
				r.room_name as room_name,
				room_info.customer_name,
				status,
				r.notes,
				r.score,
				r.instructions,
				rt.name as room_type_name,
				acronym,
				booking_id,
				#room_info.booking_id,
				room_info.check_in_date,
				room_info.check_out_date,
				room_info.state
			FROM room_type as rt, room as r
			LEFT JOIN (
				SELECT
				    b.state as booking_status,
                    b.booking_id as booking_id,
					c.customer_name,
					state,
					#b.booking_id,
					brh.room_id,
					#min(brh2.check_in_date) as check_in_date,
					brh2.check_in_date as check_in_date,
					brh.check_out_date
				FROM
					booking_block as brh,
					customer as c,
					booking as b
				LEFT JOIN booking_block as brh2 ON brh2.booking_id = b.booking_id
				WHERE
					b.company_id = '$company_id' AND
					brh.booking_id = b.booking_id AND
					(
						b.state = '".INHOUSE."' OR b.state = '".OUT_OF_ORDER."' 
                        $where_state
					) AND

					b.booking_customer_id = c.customer_id AND
					c.is_deleted = '0' AND
					b.is_deleted = '0' AND
					brh.check_out_date = (SELECT max(brh.check_out_date) FROM booking_block WHERE booking_id = b.booking_id) AND
					brh.check_in_date <= '$date' AND '$date' <= brh.check_out_date
				#GROUP BY booking_id
				)room_info ON r.room_id = room_info.room_id
			WHERE
				r.company_id = '$company_id' AND
				r.is_deleted = '0' AND
				r.room_type_id = rt.id
				$user_wise
			GROUP BY room_id
			$order
		";
        
        return $sql;

	}

    function get_housekeeping_report($company_id, $date, $display_all_rooms = false, $start_date = null, $end_date = null, $selling_date = null)
	{
		$result = array();
        $this->load->model("Company_model");
        $housekeeping_day_interval_for_full_cleaning = $this->Company_model->get_housekeeping_day_interval_for_full_cleaning($company_id);

        if ($date == "") {
            $date = date("Y-m-d");
        }
        
        // Yesterday, Today, and Tomorrow are displayed on housekeeping report
        $date_start           = Date("Y-m-d", strtotime("-1 days", strtotime($date)));
        $date_end             = Date("Y-m-d", strtotime("+1 days", strtotime($date)));
        $current_selling_date = $this->Company_model->get_selling_date($company_id);
        
        
        $display_all_rooms_sql = "";
        if($display_all_rooms)
        {
            $display_all_rooms_sql = "OR b.booking_id IS NULL";
            // don't consider Yesterday while showing all rooms (removed yesterday column).
            $date_start = $date;
        }
		/* if($start_date && $end_date){
			
		} */
        $charge = $start_end_date = '';
		if($start_date && $end_date) {
			$charge = "LEFT JOIN charge as ch ON ch.booking_id = b.booking_id";
		    $start_end_date = "AND ch.selling_date >= '$start_date' AND ch.selling_date <= '$end_date'";
		}
        $dates = array();
        // get columns of the days to display
        for ($i = $date; $i <= $date; $i = Date("Y-m-d", strtotime("+1 days", strtotime($i)))) {
            $dates[] = $i;
        }

        $str_array = Array();

        // todo refactor
        foreach ($dates as $day) {
            // 3 REPRESENTS CHECK OUT CLEANING
            // 2 REPRESENTS FULL CLEANING
            // 1 REPRESENTS STAY CLEAN

            $str_array[] = "
				MAX(
					IF ( brh.check_out_date = '".$day."' AND ( b.state = ".CHECKOUT." OR b.state = ".INHOUSE." ),
						'Checkout',
						IF ( brh.check_in_date <= '".$day."' AND '".$day."' < brh.check_out_date AND ( b.state = ".CHECKOUT." OR b.state = ".INHOUSE." ) ,
								IF(('$day' != brh.check_in_date) AND MOD(DATEDIFF('$day', brh.check_in_date), '$housekeeping_day_interval_for_full_cleaning') = 0,
									'Change Sheets',
									'Stay'
								),
								''
							)
					)
				) AS '".$day."'";
        }
        
        $columns_str = implode(", ", $str_array);
        
        $sql = "
            SELECT	
				MAX(b.booking_customer_id) AS booking_customer_id, 
				MAX(b.state) as booking_state, 
				blg.id as customer_group_id, blg.name as group_name, 
				MAX(brh.check_in_date) AS check_in_date, 
				MAX(brh.check_out_date) AS check_out_date, 
				r.room_name, r.status, $columns_str, r.notes, b.housekeeping_notes, r.group_id, r.room_id, count(DISTINCT r.group_id) as total_groups,
                    CONCAT('<b>', 
                        IFNULL((
                            SELECT c2.customer_name FROM customer as c2
                            LEFT JOIN booking as b2 ON c2.customer_id = b2.booking_customer_id
                            LEFT JOIN booking_block as brh2 ON b2.booking_id = brh2.booking_id 
                            WHERE 
                                brh2.check_out_date >= '$date' AND 
                                brh2.check_in_date <= '$date' AND
                                brh2.room_id = r.room_id AND 
                                b2.company_id = '$company_id' AND
                                (
                                    b2.state = ".CHECKOUT." OR
                                    b2.state = ".INHOUSE." 
                                    
                                ) AND
                                b2.is_deleted = '0'
                            
                            ORDER BY brh2.check_in_date ASC 
                            LIMIT 1
                        ), 'Empty'), 
                    '</b>') as occupied,
                    CONCAT('<b>', 
                        IFNULL((
                            SELECT c2.customer_name FROM customer as c2
                            LEFT JOIN booking as b2 ON c2.customer_id = b2.booking_customer_id
                            LEFT JOIN booking_block as brh2 ON b2.booking_id = brh2.booking_id 
                            WHERE 
                                brh2.check_out_date >= '$date_end' AND 
                                brh2.check_in_date <= '$date_end' AND 
                                brh2.room_id = r.room_id AND 
                                b2.company_id = '$company_id' AND
                                (
                                    b2.state = ".CHECKOUT." OR
                                    b2.state = ".INHOUSE." 
                                    
                                ) AND
                                b2.is_deleted = '0'
                            
                            ORDER BY brh2.check_in_date ASC 
                            LIMIT 1
                        ), 'Empty'),
                    '</b>') as second_occupied
		FROM
                    room as r  
                LEFT JOIN booking_block as brh2
                    ON (brh2.room_id = r.room_id AND brh2.check_out_date >= '$date' AND brh2.check_in_date <= '$date' )
                LEFT JOIN booking as b 
					ON (b.booking_id = brh2.booking_id AND b.is_deleted = 0 
						AND (b.state = ".CHECKOUT." OR b.state = ".INHOUSE." OR b.state = ".OUT_OF_ORDER."))
				LEFT JOIN booking_block as brh
                    ON (brh.booking_id = b.booking_id)
                LEFT JOIN booking_x_booking_linked_group as bblg ON b.booking_id = bblg.booking_id
                LEFT JOIN booking_linked_group as blg ON blg.id = bblg.booking_group_id
                LEFT JOIN customer as c ON c.customer_id = b.booking_customer_id
				$charge
		WHERE
			r.company_id = '$company_id' AND
			r.is_deleted != '1'  AND
			(
                            (
                                (
                                    b.state = ".CHECKOUT." OR
                                    b.state = ".INHOUSE." OR
                                    b.state = ".RESERVATION." OR
                                    b.state = ".OUT_OF_ORDER."
                                ) AND
                                b.is_deleted = '0' AND
                                b.company_id = '$company_id' AND
                                (
                                        brh.check_out_date >= '$date' AND brh.check_in_date <= '$date'
                                )
                            ) 
                            $display_all_rooms_sql
                        )
			$start_end_date
 		GROUP BY room_name
		#ORDER BY r.group_id ASC, r.room_name ASC, brh.check_in_date ASC, r.sort_order ASC
		ORDER BY r.sort_order ASC
		;
		";
        
        $q = $this->db->query($sql);

        //echo $this->db->last_query();
        if ($this->db->_error_message()) {
            show_error($this->db->_error_message());
        }


        if ($q) {
            $result = $q->result();
        }

		return $result;
	}
	
	// called from housekeeping report & Rooms main page

    function get_room_report($company_id)
    {
        $this->db->where('company_id', $company_id);
        $this->db->where('is_deleted', '0');
        $this->db->from("room");
        $this->db->select("room_name, notes");

        $q = $this->db->get();

        if ($this->db->_error_message()) // error checking
        {
            show_error($this->db->_error_message());
        }

        $result = $q->result_array();

        return $result;

	}
	
    // get available rooms based on date and roomtype
    // include room already selected for booking ($booking_id)
	// ignore rooms that have state lower than 3. (not reservation, checkin, nor checkout)

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

		if($this->enable_hourly_booking){

			$check_in_date = $check_in_date.' '.date("H:i:s", strtotime($this->default_checkin_time));
			$check_out_date = $check_out_date.' '.date("H:i:s", strtotime($this->default_checkout_time));

			$date_condition = " brh.check_out_date > '$check_in_date' AND '$check_out_date' > brh.check_in_date ";
		} else {
			$date_condition = " DATE(brh.check_out_date) > '$check_in_date' AND '$check_out_date' > DATE(brh.check_in_date) ";
		}
		
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
    
    function get_rooms_for_reservations($check_in_date, $check_out_date, $company_id)
    {
        $sql = "SELECT * FROM `booking_block`
                LEFT JOIN booking ON (booking.booking_id = booking_block.booking_id)
                WHERE `check_in_date` > '$check_in_date'  AND `check_out_date` < '$check_out_date' AND booking.company_id = '$company_id' ";
        
        $q = $this->db->query($sql);
		//echo $this->db->last_query(); // enabling this line will disable the functionality of getting available rooms
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

        return $q->result_array();
    }
	
	// update if room status as dirty or clean
	function update_room_status($room_id, $room_status)
	{		
		$this->db->where('company_id', $this->session->userdata('current_company_id'));
		$this->db->where('room_id', $room_id);
		$data = array ( 'status' => $room_status );
		$this->db->update('room', $data);
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
	}

	function update_room_score($room_id, $room_score)
	{		
		$this->db->where('company_id', $this->session->userdata('current_company_id'));
		$this->db->where('room_id', $room_id);
		$data = array ( 'score' => $room_score );
		$this->db->update('room', $data);
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
	}

	function get_room_rating($company_id)
	{
		$room_sql = "SELECT room_id FROM room WHERE is_deleted = '0' AND company_id = $company_id";

		$q = $this->db->query($room_sql);
		$room_ids_array = $q->result_array();
		$room_ids_str = "";

		foreach ($room_ids_array as $key => $value) {
			$room_ids_str .= $value['room_id'] ? $value['room_id'].',' : $value['room_id'];
		}
		$room_ids_str = rtrim($room_ids_str, ',');

		$sql ="SELECT
			    brh.room_id,
			    COUNT(brh.room_id) as total_ratings,
			    (
			    	SELECT 
			    		COUNT(br.comment) 
		    		FROM 
		    			booking_review AS br 
		    			WHERE 
		    				br.comment != ''
				) AS total_reviews,
			    ROUND(SUM(br.rating) / COUNT(br.rating) * 2) / 2 AS average_rating
			FROM
			    booking_review AS br
			LEFT JOIN booking AS b
			ON
			    b.booking_id = br.booking_id
			LEFT JOIN booking_block AS brh
			ON
			    brh.booking_id = b.booking_id
			WHERE
			    brh.room_id IN($room_ids_str)
			GROUP BY
			    brh.room_id";

	    $query = $this->db->query($sql);
		return $query->result_array();
	}

	function get_room_reviews($room_id)
	{

		$sql ="SELECT
			    brh.room_id,
			    brh.check_out_date,
			    br.rating,
			    br.comment,
			    br.created,
			    br.booking_id,
			    c.customer_name 
			FROM
			    booking_review AS br
			LEFT JOIN booking AS b
			ON
			    b.booking_id = br.booking_id
		    LEFT JOIN customer AS c
			ON
			    b.booking_customer_id = c.customer_id
			LEFT JOIN booking_block AS brh
			ON
			    brh.booking_id = b.booking_id
			WHERE
			    brh.room_id = $room_id
			ORDER BY
			    (br.comment != '') DESC, (br.comment IS NOT NULL) DESC, br.created DESC";

	    $query = $this->db->query($sql);
		return $query->result_array();
	}
	
	// set all occupied rooms dirty
	function set_occupied_rooms_dirty() 
	{
		$company_id = $this->session->userdata('current_company_id');		
		$sql = "UPDATE booking as b							
				LEFT JOIN booking_block as brh ON brh.booking_id = b.booking_id
				LEFT JOIN room as r on r.room_id = brh.room_id
				SET r.status = 'Dirty'
				WHERE 
					r.company_id = '$company_id' AND 
					b.state = '1' AND 
					b.is_deleted = 0 AND 
					b.booking_id IS NOT NULL AND 
					brh.check_out_date = 
					(
						SELECT max(check_out_date) 
						FROM booking_block as brh2 
						WHERE brh2.booking_id = b.booking_id
					)
				";
		$q = $this->db->query($sql);		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		//echo $this->db->last_query();
	}
	
	// clean all rooms
	function set_rooms_clean($company_id) 
	{
		$this->db->where('company_id', $company_id);
		$data = array ( 'status' => 'Clean' );
		$this->db->update('room', $data);
	}

	// clean all rooms
	function set_rooms_dirty($company_id, $selling_date) 
	{
		$sql = "UPDATE room as r 
			    LEFT JOIN booking_block as brh ON brh.room_id = r.room_id
			    LEFT JOIN booking as b ON brh.booking_id = b.booking_id   
			    SET r.status = 'Dirty'
			    WHERE r.company_id = '$company_id' AND b.company_id = '$company_id' AND check_in_date <= '$selling_date' AND check_out_date >= '$selling_date' AND b.state = '1' AND b.is_deleted = 0";
	    $q = $this->db->query($sql);		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
	}
	
    function update_room($room_id, $data)
    {
		$data = (object) $data;
		$this->db->where('room_id', $room_id);
        $this->db->update("room", $data);
		
		//TODO: Add error if update failed.
		return TRUE;
    }	
	
	function get_current_room_id($company_id, $booking_id) {
		$sql = "
			SELECT room_id, max(brh.check_in_date)
			FROM booking_block as brh, booking as b
			WHERE b.company_id = '$company_id' AND brh.booking_id = b.booking_id AND b.booking_id = '$booking_id'
		";
		
        $q = $this->db->query($sql);
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		$result = $q->row_array(0);
		
		return $result['room_id'];
	}
	
	function get_room($room_id, $room_type_id = null) {
		if($room_id)
		{
			$sql = "
				SELECT *, rt.name as room_type_name 
				FROM room as r
				LEFT JOIN room_type as rt ON rt.id = r.room_type_id		
				WHERE r.room_id = '$room_id'
				";			
		}
		else
		{
			$sql = "
				SELECT rt.name as room_type_name 
				FROM room_type as rt
				WHERE rt.id = '$room_type_id'
				";
		}
        $q = $this->db->query($sql);
		//echo $this->db->last_query();
        // return result set as an associative array
        $result = $q->row_array(0);
		
		return $result;

	}
	
	function create_room($company_id = null, $room_name = null, $room_type_id = '', $batch = null)
	{
        if ($batch) {
            
            $this->db->insert_batch('room', $batch);
            
        }else{
            
            $data = array(
                    'company_id' => $company_id,
                    'room_name' => $room_name,
                    'room_type_id' => $room_type_id
            );

            $this->db->insert('room', $data);
            //return $this->db->insert_id();
            $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
            $result = $query->result_array();
            if(isset($result[0]))
            {  
              return $result[0]['last_id'];
            }
        }
        return null;
	}
    
	
	// returns an array of room names that are checking out on given date
	function get_checking_out_inhouse_guests($company_id) {
		$current_selling_date = $this->Company_model->get_selling_date($company_id);
		$sql = "SELECT r.room_name
				FROM room as r, booking_block as brh, booking as b
				WHERE 
					r.company_id = '$company_id' AND
					r.room_id = brh.room_id AND
					brh.check_out_date <= '$current_selling_date' AND
					brh.booking_id = b.booking_id AND
					b.state = '".INHOUSE."'
				ORDER BY r.room_name;
				";
		$q = $this->db->query($sql);		
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
		
		$rooms = Array();
		foreach ($q->result() as $row)
		{
			$rooms[] = $row->room_name;
		}	
		
		return $rooms;
	}
	
	function get_rooms_by_room_type_id($room_type_id)
	{
		$sql = "SELECT
				r.room_id, r.room_name as room_name, r.status, r.room_type_id
				FROM room as r
				WHERE r.room_type_id = '$room_type_id'
				";
		
		$data = array();
        $q = $this->db->query($sql);
		//echo $this->db->last_query(); // enabling this line will disable the functionality of getting available rooms
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

        return $q->result();
	}

	function get_notes($room_id)
	{
		$this->db->where('room_id', $room_id);
		$this->db->from("room");
		$this->db->select("notes");
		
		$q = $this->db->get();
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		$result = $q->row_array(0);
		
		return $result['notes'];
	}

	function get_instructions($room_id)
	{
		$this->db->where('room_id', $room_id);
		$this->db->from("room");
		$this->db->select("instructions");
		
		$q = $this->db->get();
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		$result = $q->row_array(0);
		
		return $result['instructions'];
	}

	function delete_room($company_id){
	    $this->db->where('company_id',$company_id);
        $this->db->delete('room');
    }
    
    function get_room_count_by_room_type_id($room_type_id){
        $sql = "SELECT
				count(r.room_type_id) as room_count
				FROM room as r
				WHERE r.room_type_id = '$room_type_id'
				";
		
        $q = $this->db->query($sql);
		//echo $this->db->last_query(); // enabling this line will disable the functionality of getting available rooms
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

        $result =  $q->result_array();
        return $result[0];
    }
    
    function check_future_reservations_for_room($room_id, $selling_date)
    {
        $sql = "
                SELECT 
                    brh.booking_id FROM room as r 
                LEFT JOIN booking_block as brh ON r.room_id = brh.room_id 
                LEFT JOIN booking as b ON brh.booking_id = b.booking_id
                WHERE 
                    brh.room_id = '$room_id' AND
                    brh.check_in_date >= '$selling_date' AND 
                    brh.check_out_date >= '$selling_date'  AND 
                    r.is_deleted = 0 AND 
                    b.is_deleted = 0 AND
                    b.state < 4
            ";  
        
        $query = $this->db->query($sql);
        
        if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
        
        if($query->num_rows() > 0)
            return true;
        else
            return false;      
    }
	
	function get_all_room_types($company_id)
	{
		$this->db->where('rt.company_id', $company_id);
		$this->db->where('r.room_type_id = rt.id');
		$query = $this->db->get('room as r, room_type as rt');
		
		
		if ($query->num_rows() > 0)
		{
			$result = $query->result_array(0);			
			return $result;
		}
		else
		{
			return NULL;
		}
	}

    function get_room_by_name($name , $room_type_id = null){
        if($room_type_id){
            $this->db->where('r.room_type_id',$room_type_id);
        }
        $this->db->where('r.room_name',$name);
        $this->db->where('r.company_id', $this->company_id);
        $this->db->where('r.is_deleted',0);
        $query = $this->db->get('room as r');

        if ($query->num_rows() > 0)
        {
            $result = $query->result_array(0);
            return $result;
        }
        else
        {
            return NULL;
        }

    }



    function delete_rooms($company_id){

        $data = Array('is_deleted' => 1);

        $this->db->where('company_id', $company_id);
        $this->db->update("room", $data);

        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }

    }

    function create_rooms($company_id = null, $room_name = null, $room_type_id = '', $sort_order = 0 , $sold_online, $status = null)
    {
        $data = array(
            'company_id' => $company_id,
            'room_name' => $room_name,
            'room_type_id' => $room_type_id,
            'sort_order' => $sort_order,
            'can_be_sold_online' => $sold_online,
            'status' => $status

        );
        
        $this->db->insert('room', $data);
        //return $this->db->insert_id();
        $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
        $result = $query->result_array();
        if(isset($result[0]))
        {
            return $result[0]['last_id'];
        }

        return null;
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


}