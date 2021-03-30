<?php
class Rate_model extends CI_Model {

	function __construct()
	{
		 parent::__construct();
	}
	
	function create_rate($data)
	{
		
		$this->db->insert('rate', $data);		
		
		if ($this->db->_error_message()) 
		{
			show_error($this->db->_error_message());
		}
		else 
		{
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
	}
	
	function update_rate($data) {	
		
		if ($rate_id != null) {
			$this->db->where('rate_id', $data['rate_id']);
		}
		else
		{
			$this->db->where('rate_id', $data['rate_id']);
        }
		
		$this->db->update("rate", $data);
		//echo $this->db->last_query();
	}	
	
	function delete_rate($rate_id)
	{
		$this->db->where('rate_id', $rate_id);
		$this->db->delete('rate');
		//echo $this->db->last_query()."\n";
		
		if ($this->db->_error_message()) 
		{
			show_error($this->db->_error_message());
		}
		
	}

	/**
	* 
	* Returns array of rates
	*
	* @param int $rate_plan_id
	* @param string $date_start
	* @param string $date_end
	* @return array
	*/		
	// get rates between dates *DAILY*
	
	// get rates between dates
	function get_rates($rate_plan_id, $ota_id, $date_start = "1970-01-01", $date_end = "2050-01-01")
	{
		// Fetch rate POST variables
		$rate_variables = array(
									"base_rate",
									"adult_1_rate",
									"adult_2_rate",
									"adult_3_rate",
									"adult_4_rate",
									"additional_adult_rate",
									"additional_child_rate",
									'minimum_length_of_stay',
									'maximum_length_of_stay',
									'minimum_length_of_stay_arrival',
									'maximum_length_of_stay_arrival',
									'closed_to_arrival',
									'closed_to_departure',
									'can_be_sold_online'
			);

		$rate_sql_string = "";
		foreach ($rate_variables as $var)
		{
			$rate_sql_string = $rate_sql_string."
				,
				(
					SELECT 
						r.$var
					FROM 
						rate as r, 
						date_range as dr, 
						date_range_x_rate as drxr
					WHERE 
						r.rate_plan_id = '$rate_plan_id' AND
						r.rate_id = drxr.rate_id AND
						r.$var IS NOT NULL AND
						dr.date_range_id = drxr.date_range_id AND
						dr.date_start <= di.date AND 
						di.date <= dr.date_end AND
						#check for day of week
						(
							(dr.sunday = '1' AND DAYOFWEEK(di.date) = '".SUNDAY."') OR
							(dr.monday = '1' AND DAYOFWEEK(di.date) = '".MONDAY."') OR
							(dr.tuesday = '1' AND DAYOFWEEK(di.date) = '".TUESDAY."') OR
							(dr.wednesday = '1' AND DAYOFWEEK(di.date) = '".WEDNESDAY."') OR
							(dr.thursday = '1' AND DAYOFWEEK(di.date) = '".THURSDAY."') OR
							(dr.friday = '1' AND DAYOFWEEK(di.date) = '".FRIDAY."') OR
							(dr.saturday = '1' AND DAYOFWEEK(di.date) = '".SATURDAY."')
						)
					ORDER BY r.rate_id DESC
					LIMIT 0, 1
				) as ".$var."
			";
		}

		$sql = "
			select
				di.date 
				$rate_sql_string,
				rp.room_type_id,
				rp.description,
                rt.max_adults as room_max_capacity,
				rp.rate_plan_id,
				rp.charge_type_id,
				c.currency_code,
				rp.rate_plan_name
			from 
				date_interval as di,
                rate_plan as rp,
				currency as c,
                room_type as rt
			where 
				(
					(di.date >= '$date_start' AND di.date <= '$date_end') OR
					(di.date = '$date_start' AND '$date_start' = '$date_end')
				) AND
				rp.rate_plan_id = '$rate_plan_id' AND
				rp.currency_id = c.currency_id AND
                rt.id = rp.room_type_id
			group by di.date
		";

		$query = $this->db->query($sql);
		//echo  $this->db->last_query();
		if ($query->num_rows >= 1) 
		{
			$result_array = $query->result_array();
			// organize the array into room_types
			
			$date_ranged_array = get_array_with_range_of_dates($result_array, $ota_id);
	
			//print_r($date_ranged_array);	

			return $date_ranged_array;

		}
		
		return null;
	}


	// get rates between dates
	function get_daily_rates($rate_plan_id, $date_start = "1970-01-01", $date_end = "2050-01-01")
	{
		// Fetch rate POST variables
		$rate_variables = array(
									"base_rate",
									"adult_1_rate",
									"adult_2_rate",
									"adult_3_rate",
									"adult_4_rate",
									"additional_adult_rate",
									"additional_child_rate",
									'minimum_length_of_stay',
									'maximum_length_of_stay',
									'minimum_length_of_stay_arrival',
									'maximum_length_of_stay_arrival',
									'closed_to_arrival',
									'closed_to_departure',
									'can_be_sold_online'
			);

		$rate_sql_string = "";
		foreach ($rate_variables as $var)
		{
			$rate_sql_string = $rate_sql_string."
				,
				(
					SELECT 
						r.$var
					FROM 
						rate as r, 
						date_range as dr, 
						date_range_x_rate as drxr
					WHERE 
						r.rate_plan_id = '$rate_plan_id' AND
						r.rate_id = drxr.rate_id AND
						r.$var IS NOT NULL AND
						dr.date_range_id = drxr.date_range_id AND
						dr.date_start <= di.date AND 
						di.date <= dr.date_end AND
						#check for day of week
						(
							(dr.sunday = '1' AND DAYOFWEEK(di.date) = '".SUNDAY."') OR
							(dr.monday = '1' AND DAYOFWEEK(di.date) = '".MONDAY."') OR
							(dr.tuesday = '1' AND DAYOFWEEK(di.date) = '".TUESDAY."') OR
							(dr.wednesday = '1' AND DAYOFWEEK(di.date) = '".WEDNESDAY."') OR
							(dr.thursday = '1' AND DAYOFWEEK(di.date) = '".THURSDAY."') OR
							(dr.friday = '1' AND DAYOFWEEK(di.date) = '".FRIDAY."') OR
							(dr.saturday = '1' AND DAYOFWEEK(di.date) = '".SATURDAY."')
						)
					ORDER BY r.rate_id DESC
					LIMIT 0, 1
				) as ".$var."
			";
		}

		$sql = "
			select
				di.date 
				$rate_sql_string,
				rp.room_type_id,
				rp.rate_plan_id,
				rp.charge_type_id,
				rp.rate_plan_name
			from 
				date_interval as di,
				rate_plan as rp
			where 
				(
					(di.date >= '$date_start' AND di.date < '$date_end') OR
					(di.date = '$date_start' AND '$date_start' = '$date_end')
				) AND
				rp.rate_plan_id = '$rate_plan_id'
			group by di.date
		";

		$query = $this->db->query($sql);
		//echo  $this->db->last_query();
	    if ($query->num_rows >= 1) 
		{
			return $query->result_array();
		}

		return array();
	}
	
	
	

	
}
/* End of file - rate_model.php */
