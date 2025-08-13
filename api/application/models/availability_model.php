<?php

class Availability_model extends CI_Model {

    function __construct()
    {        
        parent::__construct();
        $this->load->helper('date');
    }	

	// different from get_availability. Returns the max number of rooms allowed to be sold per room type
	// as set by the hotel (does not subract those that are sold). May be more or less than the actual
	// number of rooms available to be sold.
    function get_inventory($start_date, $end_date, $room_types, $ota_id, $filter_can_be_sold_online=FALSE)
    {
		$room_types_string = implode(', ', $room_types);
		$can_be_sold_online_filter = $filter_can_be_sold_online ? 'AND r.can_be_sold_online = 1' : '';
    	
    	$query = $this->db->query("
			SELECT 
				di.date, 
				rt.id as room_type_id, 
				rt.name,

				(
					SELECT
						IFNULL((
							SELECT
								drxrtxc.availability
							from 
								date_range as dr,
								date_range_x_room_type as drxrt,
								date_range_x_room_type_x_channel as drxrtxc
							where 
								drxrtxc.date_range_x_room_type_id = drxrt.date_range_x_room_type_id AND
								drxrtxc.channel_id = ".$ota_id." AND
								rt.id = drxrt.room_type_id AND
								dr.date_range_id = drxrt.date_range_id AND
								dr.date_start <= di.date AND 
								di.date <= dr.date_end AND
								(
									(dr.sunday = '1' AND DAYOFWEEK(di.date) = '".SUNDAY."') OR
									(dr.monday = '1' AND DAYOFWEEK(di.date) = '".MONDAY."') OR
									(dr.tuesday = '1' AND DAYOFWEEK(di.date) = '".TUESDAY."') OR
									(dr.wednesday = '1' AND DAYOFWEEK(di.date) = '".WEDNESDAY."') OR
									(dr.thursday = '1' AND DAYOFWEEK(di.date) = '".THURSDAY."') OR
									(dr.friday = '1' AND DAYOFWEEK(di.date) = '".FRIDAY."') OR
									(dr.saturday = '1' AND DAYOFWEEK(di.date) = '".SATURDAY."')
								)
							order by drxrt.date_range_x_room_type_id DESC
							LIMIT 0,1
						), 999) - count(b.booking_id)
					FROM
						booking as b, 
						booking_block as brh,
						room as r
					WHERE
						b.source IN (".SOURCE_BOOKING_DOT_COM.",".SOURCE_EXPEDIA.",".SOURCE_AGODA.",".SOURCE_MYALLOCATOR.",".SOURCE_SITEMINDER.") AND
						r.room_type_id = rt.id AND
						brh.room_id = r.room_id AND
						brh.check_out_date > di.date AND 
						di.date >= brh.check_in_date AND
						b.booking_id = brh.booking_id AND
						b.state < 4 AND
						b.is_deleted = '0' AND
						r.is_deleted = '0'
				) as availability
			FROM
				room_type as rt,
				date_interval as di,
				room as r
			WHERE
				rt.id IN (".$room_types_string.") AND
				r.room_type_id = rt.id AND
				di.date >= '$start_date' AND 
				'$end_date' >= di.date AND
				rt.is_deleted = '0' AND
				r.is_deleted = '0'
				".$can_be_sold_online_filter."

			GROUP BY rt.id, di.date
			ORDER BY di.date ASC
		");

		$result_array = Array();
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		} else  {// otherwise, return insert_id;
			$result_array = $query->result_array();
		}

		$grouped_by_room_type = array();
		// organize the array into room_types
		foreach ($result_array as $availability)
		{
			$grouped_by_room_type[$availability['room_type_id']][] = Array(
				'date' => $availability['date'],
				'availability' => $availability['availability']
				);
		}

		$date_ranged_array = array();

		foreach ($grouped_by_room_type as $key => $array_of_room_type)
		{
			$date_ranged_array[$key] = get_array_with_range_of_dates(
																$array_of_room_type,
																$ota_id);
		};
      
		//return $grouped_by_room_type;
		return $date_ranged_array;
    }

	// net availability
    function get_availability($start_date, $end_date, $room_types, $ota_id, $filter_can_be_sold_online=FALSE, $adult_count = null, $children_count = null, $get_inventory = false, $get_max_availability = true, $get_inventorysold = true, $get_closeout_status = true, $is_overbooking = false, $company_id = null, $ota_key = null)
    {
		$room_types_string = implode(', ', array_map('intval', $room_types));
		$can_be_sold_online_filter = $filter_can_be_sold_online ? 'AND r.can_be_sold_online = 1' : '';
		$adult_count_sql = $adult_count ? "rt.max_adults >= ".$this->db->escape_str((int)$adult_count)." AND " : "";
		$children_count_sql = $children_count ? "rt.max_children >= ".$this->db->escape_str((int)$children_count)." AND " : "";
		$max_occupancy_sql = $min_occupancy_sql = "";
		if ($adult_count || $children_count) {
			$total_occupants = (int)$adult_count + (int)$children_count;
			$max_occupancy_sql = "rt.max_occupancy >= ".$this->db->escape_str($total_occupants)." AND ";
			$min_occupancy_sql = "rt.min_occupancy <= ".$this->db->escape_str($total_occupants)." AND ";
		}
		$company_filter_assigned = $company_id ? " AND c.company_id = ".$this->db->escape_str((int)$company_id)." AND b.company_id = ".$this->db->escape_str((int)$company_id)."" : "";
		$company_filter_unassigned = $company_id ? " AND c.company_id = ".$this->db->escape_str((int)$company_id)." AND b.company_id = ".$this->db->escape_str((int)$company_id)."" : "";
		$ota_sources_condition = ($ota_id == -1) ? "1=1" : ("b.source IN (".$ota_id.")");

		$sql = "
			SELECT
				di.date,
				rt.id AS room_type_id,
				rt.name AS room_type_name,
				rt.name AS name,
				-- derived metrics
				(IFNULL(bk_ota_assigned.cnt_assigned_ota,0) + IFNULL(bk_ota_unassigned.cnt_unassigned_ota,0)) AS inventory_sold,
				IF(
					LEAST(IFNULL(cap.availability, 999), IFNULL(rooms.total_rooms, 0)) < 0,
					0,
					LEAST(IFNULL(cap.availability, 999), IFNULL(rooms.total_rooms, 0))
				) AS max_availability,
				IFNULL(st.can_be_sold_online, 1) AS closeout_status,
				GREATEST(
					0,
					LEAST(
						IFNULL(rooms.total_rooms, 0) - (IFNULL(bk_all_assigned.cnt_assigned_all,0) + IFNULL(bk_all_unassigned.cnt_unassigned_all,0)),
						IFNULL(cap.availability, 999) - (IFNULL(bk_ota_assigned.cnt_assigned_ota,0) + IFNULL(bk_ota_unassigned.cnt_unassigned_ota,0))
					)
					- IF(rt.ota_close_out_threshold AND " . (is_numeric($ota_id) ? (int)$ota_id : 0) . " > 0 AND '".$this->db->escape_str((string)$ota_key)."' != 'obe', rt.ota_close_out_threshold, 0)
				) AS availability
			FROM
				date_interval di
			JOIN room_type rt
				ON rt.id IN ($room_types_string)
				AND rt.is_deleted = '0'
			LEFT JOIN (
				SELECT r.room_type_id, COUNT(r.room_id) AS total_rooms
				FROM room r
				JOIN room_type rt2 ON rt2.id = r.room_type_id AND rt2.is_deleted = '0'
				WHERE r.is_deleted = '0'
				  AND r.room_type_id IN ($room_types_string)
				GROUP BY r.room_type_id
			) rooms ON rooms.room_type_id = rt.id
			-- assigned bookings (all sources)
			LEFT JOIN (
				SELECT
					di2.date,
					rt2.id AS room_type_id,
					COUNT(DISTINCT CASE WHEN rt2.prevent_inline_booking = 1 THEN r.room_id ELSE b.booking_id END) AS cnt_assigned_all
				FROM date_interval di2
				JOIN room_type rt2 ON rt2.id IN ($room_types_string) AND rt2.is_deleted = '0'
				JOIN room r ON r.room_type_id = rt2.id AND r.is_deleted = '0' $can_be_sold_online_filter
				JOIN booking_block brh ON brh.room_id = r.room_id
				JOIN booking b ON b.booking_id = brh.booking_id AND b.is_deleted = '0'
				JOIN company c ON c.company_id = b.company_id
				WHERE
					IF(rt2.prevent_inline_booking = 1, DATE(brh.check_out_date) + INTERVAL 1 DAY, DATE(brh.check_out_date)) > di2.date AND
					di2.date >= IF(rt2.prevent_inline_booking = 1, DATE(brh.check_in_date) - INTERVAL 1 DAY, DATE(brh.check_in_date)) AND
					IF(c.book_over_unconfirmed_reservations = 1, b.state < 4, (b.state < 4 OR b.state = 7))
					$company_filter_assigned
					AND di2.date BETWEEN '$start_date' AND '$end_date'
				GROUP BY di2.date, rt2.id
			) bk_all_assigned ON bk_all_assigned.date = di.date AND bk_all_assigned.room_type_id = rt.id
			-- unassigned bookings (all sources)
			LEFT JOIN (
				SELECT
					di2.date,
					rt2.id AS room_type_id,
					COUNT(DISTINCT b.booking_id) AS cnt_unassigned_all
				FROM date_interval di2
				JOIN room_type rt2 ON rt2.id IN ($room_types_string) AND rt2.is_deleted = '0'
				JOIN booking_block brh ON brh.room_type_id = rt2.id AND (brh.room_id IS NULL OR brh.room_id = 0)
				JOIN booking b ON b.booking_id = brh.booking_id AND b.is_deleted = '0'
				JOIN company c ON c.company_id = b.company_id
				WHERE
					DATE(brh.check_out_date) > di2.date AND di2.date >= DATE(brh.check_in_date) AND
					IF(c.book_over_unconfirmed_reservations = 1, b.state < 4, (b.state < 4 OR b.state = 7))
					$company_filter_unassigned
					AND di2.date BETWEEN '$start_date' AND '$end_date'
				GROUP BY di2.date, rt2.id
			) bk_all_unassigned ON bk_all_unassigned.date = di.date AND bk_all_unassigned.room_type_id = rt.id
			-- assigned OTA bookings
			LEFT JOIN (
				SELECT di2.date, rt2.id AS room_type_id,
					COUNT(DISTINCT CASE WHEN rt2.prevent_inline_booking = 1 THEN r.room_id ELSE b.booking_id END) AS cnt_assigned_ota
				FROM date_interval di2
				JOIN room_type rt2 ON rt2.id IN ($room_types_string) AND rt2.is_deleted = '0'
				JOIN room r ON r.room_type_id = rt2.id AND r.is_deleted = '0'
				JOIN booking_block brh ON brh.room_id = r.room_id
				JOIN booking b ON b.booking_id = brh.booking_id AND b.is_deleted = '0'
				JOIN company c ON c.company_id = b.company_id
				WHERE
					IF(rt2.prevent_inline_booking = 1, DATE(brh.check_out_date) + INTERVAL 1 DAY, DATE(brh.check_out_date)) > di2.date AND
					di2.date >= IF(rt2.prevent_inline_booking = 1, DATE(brh.check_in_date) - INTERVAL 1 DAY, DATE(brh.check_in_date)) AND
					IF(c.book_over_unconfirmed_reservations = 1, b.state < 4, (b.state < 4 OR b.state = 7)) AND
					$ota_sources_condition
					$company_filter_assigned
					AND di2.date BETWEEN '$start_date' AND '$end_date'
				GROUP BY di2.date, rt2.id
			) bk_ota_assigned ON bk_ota_assigned.date = di.date AND bk_ota_assigned.room_type_id = rt.id
			-- unassigned OTA bookings
			LEFT JOIN (
				SELECT di2.date, rt2.id AS room_type_id, COUNT(DISTINCT b.booking_id) AS cnt_unassigned_ota
				FROM date_interval di2
				JOIN room_type rt2 ON rt2.id IN ($room_types_string) AND rt2.is_deleted = '0'
				JOIN booking_block brh ON brh.room_type_id = rt2.id AND (brh.room_id IS NULL OR brh.room_id = 0)
				JOIN booking b ON b.booking_id = brh.booking_id AND b.is_deleted = '0'
				JOIN company c ON c.company_id = b.company_id
				WHERE
					DATE(brh.check_out_date) > di2.date AND di2.date >= DATE(brh.check_in_date) AND
					IF(c.book_over_unconfirmed_reservations = 1, b.state < 4, (b.state < 4 OR b.state = 7)) AND
					$ota_sources_condition
					$company_filter_unassigned
					AND di2.date BETWEEN '$start_date' AND '$end_date'
				GROUP BY di2.date, rt2.id
			) bk_ota_unassigned ON bk_ota_unassigned.date = di.date AND bk_ota_unassigned.room_type_id = rt.id
			-- latest channel cap per date and room_type
			LEFT JOIN (
				SELECT
					m.date,
					m.room_type_id,
					COALESCE(drxrtxc.availability, 999) AS availability
				FROM (
					SELECT
						di3.date,
						rt3.id AS room_type_id,
						MAX(drxrt.date_range_x_room_type_id) AS latest_drxrt_id
					FROM date_interval di3
					JOIN room_type rt3 ON rt3.id IN ($room_types_string) AND rt3.is_deleted = '0'
					JOIN date_range dr ON
						dr.date_start <= di3.date AND di3.date <= dr.date_end AND
						(
							(dr.sunday = '1' AND DAYOFWEEK(di3.date) = '".SUNDAY."') OR
							(dr.monday = '1' AND DAYOFWEEK(di3.date) = '".MONDAY."') OR
							(dr.tuesday = '1' AND DAYOFWEEK(di3.date) = '".TUESDAY."') OR
							(dr.wednesday = '1' AND DAYOFWEEK(di3.date) = '".WEDNESDAY."') OR
							(dr.thursday = '1' AND DAYOFWEEK(di3.date) = '".THURSDAY."') OR
							(dr.friday = '1' AND DAYOFWEEK(di3.date) = '".FRIDAY."') OR
							(dr.saturday = '1' AND DAYOFWEEK(di3.date) = '".SATURDAY."')
						)
					JOIN date_range_x_room_type drxrt ON drxrt.date_range_id = dr.date_range_id
						AND drxrt.room_type_id = rt3.id
					WHERE di3.date >= '$start_date' AND di3.date <= '$end_date'
					GROUP BY di3.date, rt3.id
				) m
				LEFT JOIN date_range_x_room_type_x_channel drxrtxc
					ON drxrtxc.date_range_x_room_type_id = m.latest_drxrt_id
					AND drxrtxc.channel_id = ".$ota_id.
			") cap ON cap.date = di.date AND cap.room_type_id = rt.id
			-- latest status (can_be_sold_online) per date and room_type
			LEFT JOIN (
				SELECT
					m2.date,
					m2.room_type_id,
					COALESCE(drxrtxs.can_be_sold_online, 1) AS can_be_sold_online
				FROM (
					SELECT
						di4.date,
						rt4.id AS room_type_id,
						MAX(drxrt4.date_range_x_room_type_id) AS latest_drxrt_id
					FROM date_interval di4
					JOIN room_type rt4 ON rt4.id IN ($room_types_string) AND rt4.is_deleted = '0'
					JOIN date_range dr4 ON
						dr4.date_start <= di4.date AND di4.date <= dr4.date_end AND
						(
							(dr4.sunday = '1' AND DAYOFWEEK(di4.date) = '".SUNDAY."') OR
							(dr4.monday = '1' AND DAYOFWEEK(di4.date) = '".MONDAY."') OR
							(dr4.tuesday = '1' AND DAYOFWEEK(di4.date) = '".TUESDAY."') OR
							(dr4.wednesday = '1' AND DAYOFWEEK(di4.date) = '".WEDNESDAY."') OR
							(dr4.thursday = '1' AND DAYOFWEEK(di4.date) = '".THURSDAY."') OR
							(dr4.friday = '1' AND DAYOFWEEK(di4.date) = '".FRIDAY."') OR
							(dr4.saturday = '1' AND DAYOFWEEK(di4.date) = '".SATURDAY."')
						)
					JOIN date_range_x_room_type drxrt4 ON drxrt4.date_range_id = dr4.date_range_id
						AND drxrt4.room_type_id = rt4.id
					WHERE di4.date >= '$start_date' AND di4.date <= '$end_date'
					GROUP BY di4.date, rt4.id
				) m2
				LEFT JOIN date_range_x_room_type_x_status drxrtxs
					ON drxrtxs.date_range_x_room_type_id = m2.latest_drxrt_id
					AND drxrtxs.channel_id = ".$ota_id.
			") st ON st.date = di.date AND st.room_type_id = rt.id
		WHERE
			$adult_count_sql
			$children_count_sql
			$max_occupancy_sql
			$min_occupancy_sql
			di.date >= '$start_date' AND di.date <= '$end_date'
		ORDER BY di.date ASC
		";
		
		$query = $this->db->query($sql);

		$result_array = Array();
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		} else  {// otherwise, return insert_id;
			$result_array = $query->result_array();
		}

		if($is_overbooking)
		{
			return $result_array;
		}

		$grouped_by_room_type = array();
		foreach ($result_array as $availability)
		{
			if ($get_inventory) {
				$grouped_by_room_type[$availability['room_type_id']][] = Array(
					'date' => $availability['date'],
					'room_type_name' => isset($availability['room_type_name']) ? $availability['room_type_name'] : (isset($availability['name']) ? $availability['name'] : null),
					'name' => isset($availability['name']) ? $availability['name'] : (isset($availability['room_type_name']) ? $availability['room_type_name'] : null),
					'availability' => isset($availability['availability']) ? $availability['availability'] : null,
					'max_availability' => isset($availability['max_availability']) ? $availability['max_availability'] : null,
					'inventory_sold' => isset($availability['inventory_sold']) ? $availability['inventory_sold'] : null,
					'closeout_status' => isset($availability['closeout_status']) ? $availability['closeout_status'] : null
				);
			} else {
				$grouped_by_room_type[$availability['room_type_id']][] = Array(
					'date' => $availability['date'],
					'availability' => isset($availability['availability']) ? $availability['availability'] : null,
					'closeout_status' => isset($availability['closeout_status']) ? $availability['closeout_status'] : null
				);
			}
		}

		$date_ranged_array = array();
		foreach ($grouped_by_room_type as $key => $array_of_room_type)
		{
			$date_ranged_array[$key] = get_array_with_range_of_dates(
												$array_of_room_type,
												$ota_id);
		}
		return $date_ranged_array;
	}


	// max availability set by hotels
    function get_max_availability($start_date, $end_date, $room_types, $channel, $filter_can_be_sold_online=FALSE)
    {
    	$room_types_string = implode(', ', $room_types);
		$can_be_sold_online_filter = $filter_can_be_sold_online ? 'AND r.can_be_sold_online = 1' : '';
    	
    	$query = $this->db->query("
			SELECT 
				di.date, 
				rt.id as room_type_id, 
				rt.name,
				 
				(
					(
					SELECT
						(IFNULL((
							SELECT
								drxrtxc.availability
							from 
								date_range as dr,
								date_range_x_room_type as drxrt,
								date_range_x_room_type_x_channel as drxrtxc
							where 
								drxrtxc.date_range_x_room_type_id = drxrt.date_range_x_room_type_id AND
								drxrtxc.channel_id = ".$channel." AND
								rt.id = drxrt.room_type_id AND
								dr.date_range_id = drxrt.date_range_id AND
								dr.date_start <= di.date AND 
								di.date <= dr.date_end AND
								(
									(dr.sunday = '1' AND DAYOFWEEK(di.date) = '".SUNDAY."') OR
									(dr.monday = '1' AND DAYOFWEEK(di.date) = '".MONDAY."') OR
									(dr.tuesday = '1' AND DAYOFWEEK(di.date) = '".TUESDAY."') OR
									(dr.wednesday = '1' AND DAYOFWEEK(di.date) = '".WEDNESDAY."') OR
									(dr.thursday = '1' AND DAYOFWEEK(di.date) = '".THURSDAY."') OR
									(dr.friday = '1' AND DAYOFWEEK(di.date) = '".FRIDAY."') OR
									(dr.saturday = '1' AND DAYOFWEEK(di.date) = '".SATURDAY."')
								)
							order by drxrt.date_range_x_room_type_id DESC
							LIMIT 0,1
						), 999)) - count(DITINCT b.booking_id)
						FROM
							booking as b, 
							booking_block as brh,
							room as r
						WHERE
							b.source IN (".SOURCE_BOOKING_DOT_COM.",".SOURCE_EXPEDIA.",".SOURCE_AGODA.",".SOURCE_SITEMINDER.") AND
							r.room_type_id = rt.id AND
							brh.room_id = r.room_id AND
							brh.check_out_date > di.date AND 
							di.date >= brh.check_in_date AND
							b.booking_id = brh.booking_id AND
							b.state < 4 AND
							b.is_deleted = '0' AND
							r.is_deleted = '0'
					) - (
						SELECT
							count(DITINCT b.booking_id)
						FROM
							booking as b, 
							booking_block as brh
						WHERE
							b.source IN (".SOURCE_BOOKING_DOT_COM.",".SOURCE_EXPEDIA.",".SOURCE_AGODA.",".SOURCE_SITEMINDER.") AND
							brh.room_type_id = rt.id AND
							(brh.room_id IS NULL OR brh.room_id = 0) AND
							brh.check_out_date > di.date AND 
							di.date >= brh.check_in_date AND
							b.booking_id = brh.booking_id AND
							b.state < 4 AND
							b.is_deleted = '0'
					)
				)
				as availability
			FROM
				date_interval as di,
				room_type as rt
			LEFT JOIN room as r ON r.room_type_id = rt.id AND r.is_deleted = '0' ".$can_be_sold_online_filter."
			WHERE
				rt.id IN (".$room_types_string.") AND
				
				di.date >= '$start_date' AND 
				'$end_date' >= di.date AND
				rt.is_deleted = '0'
				

			GROUP BY rt.id, di.date
			ORDER BY di.date ASC
		");

		$result_array = Array();
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		} else  {// otherwise, return insert_id;
			$result_array = $query->result_array();
		}
		
		//echo $this->db->last_query();

		$grouped_by_room_type = array();
		// organize the array into room_types
		foreach ($result_array as $availability)
		{
			$grouped_by_room_type[$availability['room_type_id']][] = Array(
				'date' => $availability['date'],
				'availability' => $availability['availability']
				);
		}

		$date_ranged_array = array();

		foreach ($grouped_by_room_type as $key => $array_of_room_type)
		{
			$date_ranged_array[$key] = get_array_with_range_of_dates(
																$array_of_room_type,
																$channel);
		};

		return $date_ranged_array;

    }

	// called from get_availability_by_room_type
	// return number of occupancies grouped by room type
	function get_overall_occupancy($company_id, $start_date, $end_date)
	{
		$query = $this->db->query("			
			SELECT indexes.room_type_id, indexes.selling_date, count(occupancy.booking_id) as occupancies
			FROM 
			(
				SELECT d.date as selling_date, rt.id as room_type_id
				FROM 
					date_interval as d, room_type as rt
				WHERE 	
					rt.company_id = '$company_id' AND
					d.date >= '$start_date' AND 
					'$end_date' >= d.date AND
					rt.is_deleted = '0'
			) indexes
			LEFT JOIN
			(
				SELECT
					rt2.id as room_type_id, b.booking_id, 
					GREATEST(brh.check_in_date, '$start_date') as check_in_date, 
					LEAST(brh.check_out_date, '$end_date') as check_out_date
				FROM
					booking as b, 
					room as r,
					room_type as rt2,
					booking_block as brh
				WHERE
					brh.check_out_date > '$start_date' AND '$end_date' > brh.check_in_date AND
					b.booking_id = brh.booking_id AND
					b.state < 4 AND
					b.is_deleted = '0' AND
					brh.check_in_date < brh.check_out_date AND
					brh.room_id = r.room_id AND
					r.room_type_id = rt2.id AND
					rt2.is_deleted = '0' AND
					rt2.company_id = '$company_id'
			) occupancy ON 
				occupancy.check_in_date <= indexes.selling_date AND 
				occupancy.check_out_date > indexes.selling_date AND
				occupancy.room_type_id = indexes.room_type_id
			GROUP BY indexes.room_type_id, indexes.selling_date
		");
		
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		} else  {// otherwise, return insert_id;
			return $query->result_array();
		}
	}
	
	/**
	*	Returns array of availabilities grouped by Room Type
	*	@return array
	*/
	function get_room_count_per_room_type($company_id)
	{
		// First, get the total number of rooms per room type
		$query = $this->db->query("			
			SELECT rt.id, count(r.room_id) as room_count
			FROM room_type as rt
			LEFT JOIN room as r ON r.room_type_id = rt.id AND r.is_deleted = '0'
			WHERE
				rt.company_id = '$company_id' AND
				rt.is_deleted = '0'
			GROUP BY rt.id
		");
		
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
		$result = $query->result_array();

		return $result;
	}
	
}
