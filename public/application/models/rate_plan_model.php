<?php
class Rate_plan_model extends CI_Model {

	function __construct()
	{
		 parent::__construct();
	}
	
	// return an array of rate types grouped by Room Type
	function get_rate_plans($company_id)
	{
		$this->db->select('
						rp.rate_plan_id, 
						r.adult_1_rate, 
						rp.rate_plan_name, 
						rp.description,
						cu.currency_code, 
						rp.charge_type_id,
						cu.currency_id, 
						rt.id as room_type_id, 
						rt.name as room_type_name, 
						rp.image_group_id');
		$this->db->from('rate_plan as rp, room_type as rt, currency as cu');		
		$this->db->join('rate as r', 'r.rate_id = rp.base_rate_id', 'left');

		$this->db->where('rt.company_id', $company_id);
		$this->db->where('rp.room_type_id = rt.id');
		$this->db->where('IF(rp.currency_id, rp.currency_id, '.DEFAULT_CURRENCY.') = cu.currency_id');
		$this->db->where('rp.is_deleted != "1"');
		$this->db->where('rp.is_selectable = "1"');
		$this->db->where('rt.is_deleted != "1"');
		
		
		$query = $this->db->get();
		
		if ($this->db->_error_message()) // error checking
					show_error($this->db->_error_message());
					
		//echo $this->db->last_query();
		if ($query->num_rows >= 1) 
		{
			$result =  $query->result_array();
			return $result;
		}
		return NULL;
	}
		
	function get_rate_plan($rate_plan_id, $company_id = null)
	{
        $this->db->select("rp.*, r.*");
		$this->db->from("rate_plan as rp");
		$this->db->join("rate as r","rp.base_rate_id = r.rate_id", "left");
		$this->db->where("rp.is_deleted != '1'");
		$this->db->where("rp.rate_plan_id", $rate_plan_id);

		if($company_id)
			$this->db->where("rp.company_id", $company_id);
		
		$query = $this->db->get();
		//echo $this->db->last_query();
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
		if ($query->num_rows >= 1) 
		{
			$q = $query->result_array();
			return $q[0];
		}
		return NULL;
	}
    function get_rate_plan_by_name($rate_plan_id, $company_id)
    {
        $this->db->select("rp.*");
        $this->db->from("rate_plan as rp");
        $this->db->where("rp.is_deleted != '1'");
        $this->db->where("rp.rate_plan_name", $rate_plan_id);
        $this->db->where("rp.company_id", $company_id);

        $query = $this->db->get();

        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());
        if ($query->num_rows >= 1)
        {
            $q = $query->result_array();
            return $q[0];
        }
        return NULL;
    }

    function get_rate_plans_by_room_type_id($room_type_id, $previous_rate_plan_id=null, $get_all_rate_plans = false, $extra_ids = null)
	{
		$where_condition = " rp.room_type_id = '$room_type_id' OR ";
		if($get_all_rate_plans)
		{
			$where_condition = "rp.room_type_id IN ($room_type_id) OR ";
		}

		$extra_where_condition = $join_rate_plan_extras = $select_condition = "";
		if($extra_ids)
		{
			$select_condition = ", e.extra_id";
			$join_rate_plan_extras = ", rate_plan_x_extra as rpxe, extra as e ";
			$extra_where_condition = "e.extra_id IN ($extra_ids) AND rpxe.rate_plan_id = rp.rate_plan_id AND rpxe.extra_id = e.extra_id AND ";
		}

		$sql = "
			SELECT
				rp.rate_plan_id,
				rp.parent_rate_plan_id,
				rp.rate_plan_name,
				rp.room_type_id,
				rp.description, 
				rp.charge_type_id,
				cu.currency_code,
				cu.currency_id,
				rt.name as room_type_name,
				rt.image_group_id as room_type_image_group_id,
				rt.company_id,
				rt.max_adults,
				rp.image_group_id,
				rp.is_shown_in_online_booking_engine
				$select_condition
			FROM
				rate_plan as rp, currency as cu, room_type as rt $join_rate_plan_extras
			WHERE
				$extra_where_condition
				(
					$where_condition
					rp.rate_plan_id = '$previous_rate_plan_id'
				) AND
				(
					rp.is_selectable = '1' OR
					rp.rate_plan_id = '$previous_rate_plan_id'
				) AND
				rp.is_deleted != '1' AND
				cu.currency_id = IF(rp.currency_id, rp.currency_id, ".DEFAULT_CURRENCY.") AND 
				rp.room_type_id = rt.id
            GROUP BY rp.rate_plan_id
		";
		
        $query = $this->db->query($sql);
		//echo $this->db->last_query();
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
					
		if ($query->num_rows >= 1) 
		{
			return $query->result_array();
		}
		return NULL;

	}
	
	function create_rate_plan($data)
    {
		$data['image_group_id'] = $this->Image_model->create_image_group(RATE_PLAN_IMAGE_TYPE_ID);
		$this->db->insert('rate_plan', $data);

		if ($this->db->_error_message()) // error checking
		{
			show_error($this->db->_error_message());
		}
		
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
	
// get room type id by rate plan
	function get_room_type_id_by_rate_plan_id($rate_plan_id)
	{
		$this->db->select('room_type_id');
		$this->db->from('rate_plan');
		$this->db->where('rate_plan_id', $rate_plan_id);
		
		$query = $this->db->get();
		if ($this->db->_error_message()) // error checking
		{
			show_error($this->db->_error_message());
		}
		
	    if ($query->num_rows >= 1) 
		{	
			$q = $query->result();
			return $q[0]->room_type_id;
		}
		
		return NULL;
	}

	// get room type id by rate plan
	function get_room_type_id_by_rate_plan_name($name)
	{
		$this->db->from('rate_plan');
		$this->db->where('rate_plan_name', $name);
		
		$query = $this->db->get();
		if ($this->db->_error_message()) // error checking
		{
			show_error($this->db->_error_message());
		}
		
	    if ($query->num_rows >= 1) 
		{	
			$q = $query->result();
			return $q[0]->room_type_id;
		}
		
		return NULL;
	}
	
	
	function check_if_rate_plan_belongs_to_company($rate_plan_id, $company_id)
	{
		$this->db->from('rate_plan as rp, room_type as rt');
		$this->db->where('rt.id = rp.room_type_id');
		$this->db->where('rt.company_id', $company_id);
		$this->db->where("rp.rate_plan_id", $rate_plan_id);		
		$query = $this->db->get();
		
		if ($this->db->_error_message()) // error checking
					show_error($this->db->_error_message());
		if ($query->num_rows >= 1) 
		{
			return true;
		}
		return false;
	}
	
	function delete_rate_plan($rate_plan_id)
	{
		$data = array (
			'is_deleted' => '1'
		);
		$this->db->where('rate_plan_id', $rate_plan_id);
		$this->db->update("rate_plan", $data);  
		if ($this->db->_error_message()) // error checking
					show_error($this->db->_error_message());
		return TRUE;
	}
	
	function get_restrictions($date_start, $date_end, $rate_plan_id)
	{
		$this->db->from('date_range as dr, date_range_x_rate_plan as restriction');
		$this->db->where("restriction.rate_plan_id", $rate_plan_id);
		$this->db->where("restriction.date_range_id = dr.date_range_id");
		$this->db->where("dr.date_start <= ", $date_end);
		$this->db->where("dr.date_end >= ", $date_start);
		$query = $this->db->get();
		
		if ($this->db->_error_message()) // error checking
		{
			show_error($this->db->_error_message());
		}
		
	    if ($query->num_rows >= 1) 
		{	
			$rows = $query->result_array();
			$restriction_array = array();
			for ($date = $date_start; $date <= $date_end; $date = Date("Y-m-d", strtotime("+1 day", strtotime($date))) 	)
			{
				foreach($rows as $index => $row)
				{
					if ($row['date_start'] <= $date && $date <= $row['date_end'])
					{
						$restriction_array[$date] = $row;
					}
				}
			}
			return $restriction_array;
			
		}
		
		return NULL;
	}
	
	function update_rate_plan($data, $rate_plan_id = "")
	{
		$this->db->where('rate_plan_id', $rate_plan_id);  
		$this->db->update("rate_plan", $data);
		if ($this->db->_error_message()) // error checking
		{
			show_error($this->db->_error_message());
			return false;
		}
		return true;
	}

    function update_room_rate_plan($rate_plan_id, $old_id){

        $data = array(
            'default_room_charge' => $rate_plan_id
        );
        $this->db->where('default_room_charge', $old_id);
        $this->db->update("room_type", $data);
        if ($this->db->_error_message()) // error checking
        {
            show_error($this->db->_error_message());
            return false;
        }
        return true;
    }

    function update_rate_plan_room_types($old_rate_plan_ids, $new_rate_plan_ids){

        $i = 0; $data = array();
    	foreach ($old_rate_plan_ids as $key => $value) {
    		if($value && isset($new_rate_plan_ids[$i]) && $new_rate_plan_ids[$i]) {
    			$data[] = " WHEN default_room_charge = $value THEN $new_rate_plan_ids[$i] ";
    		}
			$i++;
    	}

    	$sql = "UPDATE `room_type` SET `default_room_charge` = CASE ";
    	foreach ($data as $k => $val) {
    		$sql .= $val;
    	}

		$sql .= " ELSE `default_room_charge`
				    END";

		$q = $this->db->query($sql);
    }
        
    function delete_custom_rate_plan($rate_plan_id)
    {
        $this->db->where("rate_plan_id", $rate_plan_id);
        $this->db->delete("rate_plan");
    }
    
    function get_rate_plan_descriptions($rate_plan_ids)
	{
        if(!$rate_plan_ids || count($rate_plan_ids) == 0){
            return null;
        }
		$this->db->select('rp.rate_plan_id, rp.description');
		$this->db->from('rate_plan as rp');
		$this->db->where_in("rp.rate_plan_id", $rate_plan_ids);
		$query = $this->db->get();
		
		if ($this->db->_error_message()) // error checking
		{
			show_error($this->db->_error_message());
		}
        if ($query->num_rows >= 1) 
		{
			$result = $query->result_array();
            $descriptions = array();
            foreach($result as $row){
                $descriptions[$row['rate_plan_id']] = $row['description'];
            }
            return $descriptions;
		}
		return NULL;
    }

    function delete_rate_plans($company_id){

        $data = Array('is_deleted' => 1);

        $this->db->where('company_id', $company_id);
        $this->db->update("rate_plan", $data);

        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
    }
    function get_policy($option_name,$company_id){

        $this->db->select('*');
        $this->db->where('option_name', $option_name);
        $this->db->where('company_id', $company_id);
        
        $query = $this->db->get('options');
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        $result = $query->result_array();

        return $result;

    }

    function get_parent_rateplan_data($roomtype_id , $rateplan_id, $date_range = null)
    {
		$limit_condition = "LIMIT 7";
    	if($date_range){
    		$limit_condition = "";
    	}
        $q = "SELECT rt.rate_plan_name ,r.*,drr.rate_id,drr.date_range_id,dr.* FROM `rate_plan` as rt 
			INNER JOIN rate as r ON rt.rate_plan_id = r.rate_plan_id
			INNER JOIN date_range_x_rate as drr ON drr.rate_id = r.rate_id
			INNER JOIN date_range as dr ON dr.date_range_id = drr.date_range_id 
			where rt.rate_plan_id = $rateplan_id and rt.room_type_id = $roomtype_id and dr.date_start !='2000-01-01' and dr.date_end !='2030-01-01' ORDER BY r.rate_id DESC $limit_condition";
        $query = $this->db->query($q);
        
        return $query->result_array();
    }
}

/* End of file - rate_model.php */