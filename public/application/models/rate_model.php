<?php
class Rate_model extends CI_Model {

	function __construct()
	{
		 parent::__construct();
	}
	
	function get_rate_by_rate_id($rate_id)
	{
		$this->db->from('rate as r');
		$this->db->where('r.rate_id', $rate_id);
		
		$query = $this->db->get();
		if ($this->db->_error_message()) // error checking
		{
			show_error($this->db->_error_message());
		}
		
	    if ($query->num_rows >= 1) 
		{	
			$q = $query->result_array();
			return $q[0];
		}
		
		return NULL;
	}

	function get_total_rates_by_rate_plan_id ($rate_plan_id) {
	    $this->db->select('rate_id');
	    $this->db->from('rate');
	    $this->db->where("is_deleted IS NULL");
	    $this->db->where("rate_plan_id", $rate_plan_id);
        $query = $this->db->get();
        if ($this->db->_error_message()) // error checking
        {
            show_error($this->db->_error_message());
        }
        return $query->num_rows();
    }

    function get_date_range($date_start, $date_end)
    {
      
        $sql = "
            SELECT
				di.date,
				WEEKDAY(di.date) as day_of_week             
			FROM 
				date_interval as di
            WHERE 
				(
					(di.date >= '$date_start' AND di.date < '$date_end') OR
					(di.date = '$date_start' AND '$date_start' = '$date_end')
				) 
			
		";

		$query = $this->db->query($sql);
      
        if ($query->num_rows >= 1) 
		{
            return $query->result_array();
		}
		
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
	
	function update_rate($data, $rate_id = null) {	
		
		if ($rate_id != null) {
			$this->db->where('rate_id', $rate_id);
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

	// For Replication Rate Plan
	// Used for traversing through all rates & date_ranges
	function get_rate_x_date_ranges($rate_plan_id)
	{
		$this->db->from('rate as r, date_range as dr, date_range_x_rate as drxr');
		$this->db->where('r.rate_plan_id', $rate_plan_id);
		$this->db->where('dr.date_range_id = drxr.date_range_id');
		$this->db->where('r.rate_id = drxr.rate_id');
		$this->db->order_by('r.rate_id', 'ASC');
		
		$query = $this->db->get();
		if ($this->db->_error_message()) // error checking
		{
			show_error($this->db->_error_message());
		}
		
	    if ($query->num_rows >= 1) 
		{	
			$q = $query->result_array();
			return $q;
		}
		
		return NULL;
	}
	
	// get rates between dates
	function get_daily_rates($rate_plan_id, $date_start = "1970-01-01", $date_end = "2050-01-01", $room_type_id = 0)
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
								'can_be_sold_online',
								'rate_id'
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
       
		$rate_sql = "
			select 
				di.date,
				WEEKDAY(di.date) as day_of_week
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
                
           
			if($this->session->userdata('user_role')=="is_admin"){
                    
				// Fetch supplied rate POST variables
                $rate_supplied_variables = array(
                                                "supplied_adult_1_rate",
                                                "supplied_adult_2_rate",
                                                "supplied_adult_3_rate",
                                                "supplied_adult_4_rate"
                                            );
				// Supplied Rate SQL
				$rate_supplied_sql_string = "";
			    foreach ($rate_supplied_variables as $key => $supplied_var)
				{
					$rate_supplied_sql_string = $rate_supplied_sql_string."
						,
						(
							SELECT 
			                    rs.$supplied_var
							FROM 
								date_range as dr, 
			                    rate_supplied as rs,
			                    date_range_x_rate_supplied as drxrs
							WHERE 
								rs.rate_supplied_id = drxrs.rate_supplied_id AND
								rs.rate_plan_id = '$rate_plan_id' AND
								rs.$supplied_var IS NOT NULL AND
								dr.date_range_id = drxrs.date_range_id AND
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
							ORDER BY rs.rate_supplied_id DESC
							LIMIT 0, 1
						) as ".$supplied_var."
					";
				}
	                
				$rate_supplied_sql = "
					select 
						di.date as supplied_date,
						WEEKDAY(di.date) as supplied_day_of_week
						$rate_supplied_sql_string
					from 
						date_interval as di,
						rate_plan as rp
					where 
						(
							(di.date >= '$date_start' AND di.date < '$date_end') OR
							(di.date = '$date_start' AND '$date_start' = '$date_end')
						) AND
						rp.rate_plan_id = '$rate_plan_id'
					group by supplied_date
					";
	               
				
	            $query = $this->db->query($rate_sql);
                    
	            $supplied_query = $this->db->query($rate_supplied_sql);

            
	            if ($query->num_rows >= 1 && $supplied_query->num_rows >= 1) 
				{
	                $rate_result = $query->result_array();
	                $rate_supplied_result = $supplied_query->result_array();
	                $unique_array = array();
	                foreach($rate_result as $rates) 
	                {
	                    foreach($rate_supplied_result as $supplied_rates) 
	                    {
	                        if($rates['date'] == $supplied_rates['supplied_date']){
	                            
	                            $rates['supplied_adult_1_rate'] = $supplied_rates['supplied_adult_1_rate'];
	                            $rates['supplied_adult_2_rate'] = $supplied_rates['supplied_adult_2_rate'];
	                            $rates['supplied_adult_3_rate'] = $supplied_rates['supplied_adult_3_rate'];
	                            $rates['supplied_adult_4_rate'] = $supplied_rates['supplied_adult_4_rate'];
	                        }
	                    }
	                    $unique_array[]=$rates;
                        
	                }
					return $unique_array;
	            }
			}else{
	        	$query = $this->db->query($rate_sql);
            	return $query->result_array();
	        }
		return array();
	}
	
	// get rates between dates - property group online booking engine - very much optimized sql and we will start using it eventually for whole site
    function get_daily_rates_optimized($rate_plan_ids = array(), $date_start = "1970-01-01", $date_end = "2050-01-01", $room_type_id = 0)
	{
		$rate_plan_ids_str = implode(",", $rate_plan_ids);
		
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
								'can_be_sold_online',
								'rate_id'
							);
		 
		$rate_sql = "
			SELECT   
                di.date,  
                rp.room_type_id,  
                rp.rate_plan_id,  
                rp.charge_type_id,  
                rp.rate_plan_name,                
                WEEKDAY(di.date) as day_of_week, 
			    r.*     
            from   
                rate_plan as rp 
            LEFT JOIN date_interval as di ON ((di.date >= '$date_start' AND di.date < '$date_end') OR (di.date = '$date_start' AND '$date_start' = '$date_end')) 
            LEFT JOIN rate as r ON r.rate_plan_id = rp.rate_plan_id 
            LEFT JOIN date_range_x_rate as drxr ON r.rate_id = drxr.rate_id  
            LEFT JOIN date_range as dr ON dr.date_range_id = drxr.date_range_id  
            where   
                (  
                    (di.date >= '$date_start' AND di.date < '$date_end') OR  
                    (di.date = '$date_start' AND '$date_start' = '$date_end')  
                ) AND  
                 
                rp.rate_plan_id IN ($rate_plan_ids_str) AND 
                 
                dr.date_start <= di.date AND   
                di.date <= dr.date_end AND  
                #check for day of week  
                (  
                    (dr.sunday = '1' AND DAYOFWEEK(di.date) = '1') OR  
                    (dr.monday = '1' AND DAYOFWEEK(di.date) = '2') OR  
                    (dr.tuesday = '1' AND DAYOFWEEK(di.date) = '3') OR  
                    (dr.wednesday = '1' AND DAYOFWEEK(di.date) = '4') OR  
                    (dr.thursday = '1' AND DAYOFWEEK(di.date) = '5') OR  
                    (dr.friday = '1' AND DAYOFWEEK(di.date) = '6') OR  
                    (dr.saturday = '1' AND DAYOFWEEK(di.date) = '7')  
                ) AND          
				(rp.is_deleted = 0 OR rp.is_deleted IS NULL) AND
				(r.is_deleted = 0 OR r.is_deleted IS NULL) 
			ORDER BY di.date asc, rp.rate_plan_id asc, r.rate_id desc
		";
		
		
		$query = $this->db->query($rate_sql);
		$rate_array = $query->result_array();
		
		$rates = array();
		foreach ($rate_array as $rate) {
			
			$rate_key = "{$rate['date']}-{$rate['rate_plan_id']}";
			
			if (!isset($rates[$rate_key])) {
				$rates[$rate_key] = $rate;
			}
			else {
				foreach ($rate_variables as $rate_variable) {
					if ($rates[$rate_key][$rate_variable] === NULL && $rate[$rate_variable]) {
						$rates[$rate_key][$rate_variable] = $rate[$rate_variable];
					}
				}
			}
		}
		return $rates;
	}
	            
	function get_rate_by_date_and_rate_plan_id($date, $rate_plan_id)
	{
		$this->db->select('r.rate_id, r.base_rate, r.additional_adult_rate, r.additional_child_rate');
		$this->db->from('rate as r, date_range as dr, date_range_x_rate as drxr');
		$this->db->where('r.rate_plan_id', $rate_plan_id);
		$this->db->where('drxr.rate_id = r.rate_id');
		$this->db->where('drxr.date_range_id = dr.date_range_id');
		$this->db->where('dr.date_start <= ', $date);
		$this->db->where('dr.date_end >= ', $date);
		$query = $this->db->get();
		if ($this->db->_error_message()) 
		{
			show_error($this->db->_error_message());
			return false;
		} 
		
		//echo $this->db->last_query();
		
		if ($query->num_rows >= 1) 
		{
			$result = $query->result_array();
			return $result[0];
		}
		
		return null;
		
	}
	
	/*
	************************************************************************** EXTRA ***************************************************************************
	*/
		
	function get_extra_rates($extra_id)
	{
		$this->db->from('extra as e, extra_rate as er');
		$this->db->where('e.extra_id', $extra_id);
		$this->db->where('e.extra_id = er.extra_id');
		$query = $this->db->get();
		if ($query->num_rows >= 1) 
		{
			return $query->result_array();
		}
		
		return NULL;
	}
	
	
	function create_extra_rate($data)
	{
		$this->db->insert('extra_rate', $data);
		
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
	
	function update_extra_rate($extra_id, $data)
	{
		$data = (object) $data;
		
		$this->db->where('extra_id', $extra_id);
		$this->db->update('extra_rate', $data);
		if ($this->db->_error_message()) 
		{
			show_error($this->db->_error_message());
			return false;
		} 
		else  
		{
			return true;
		}
	}
	
	// return the array of (Rate, Date) for corresponding parameters.
	function get_default_extra_rate($extra_id) 
	{
		$this->db->from('extra as e, extra_rate as er');
		$this->db->where('e.extra_id', $extra_id);
		$this->db->where('e.extra_id = er.extra_id');
		$query = $this->db->get();
		if ($query->num_rows >= 1) 
		{
			$result = $query->result_array();
		}
		return $result[0]['rate'];
	}

        // Insert Supplied Rate
        function create_supplied_rate($data)
	{
		$this->db->insert('rate_supplied', $data);	
                
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
        
        function delete_custom_rates_and_date_range($rate_id, $rate_plan_id, $date_range_id, $date_range_x_rate_id)
        {
            if($rate_id && $rate_plan_id)
            {
                $this->db->where('rate_id', $rate_id);
                $this->db->where('rate_plan_id', $rate_plan_id);
                $this->db->delete('rate');
            }
            if($date_range_id)
            {
                $this->db->where('date_range_id', $date_range_id);
                $this->db->delete('date_range');
            }
            if($date_range_x_rate_id && $rate_id && $date_range_id)
            {
                $this->db->where('rate_id', $rate_id);
                $this->db->where('date_range_id', $date_range_id);
                $this->db->where('date_range_x_rate_id', $date_range_x_rate_id);
                $this->db->delete('date_range_x_rate');
            }

        }

    function delete_rates($company_id){

        $data = Array('is_deleted' => '1');

        $this->db->where('company_id', $company_id);
        $this->db->update("rate", $data);

        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
    }

}
/* End of file - rate_model.php */
