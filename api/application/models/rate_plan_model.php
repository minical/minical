<?php
class Rate_plan_model extends CI_Model {

	function __construct()
	{
		 parent::__construct();
	}
	
	function get_rate_plan($rate_plan_id, $company_id = null)
	{

		$this->db->from("rate_plan as rp");
		$this->db->join("rate as r","rp.base_rate_id = r.rate_id", "left");
		if($company_id)
		{
			$this->db->join("room_type as rt","rp.room_type_id = rt.id", "left");
		}
		$this->db->where("rp.is_deleted != '1'");
		$this->db->where("rp.rate_plan_id", $rate_plan_id);
		if($company_id)
		{
			$this->db->where("rt.company_id", $company_id);
		}
		
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
	
	function create_rate_plan($data)
    {
		$this->db->insert('rate_plan', $data);
		//$rate_plan_id = $this->db->insert_id();
        $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
		$result = $query->result_array();
        if(isset($result[0]))
        {  
          $rate_plan_id = $result[0]['last_id'];
        }
            else
        {  
          $rate_plan_id = null;
        }
		if ($this->db->_error_message()) // error checking
		{
			show_error($this->db->_error_message());
		}
		//$new_rate_plan_id = $this->db->insert_id();
        $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
		$result = $query->result_array();
        if(isset($result[0]))
        {  
          $new_rate_plan_id = $result[0]['last_id'];
        }
            else
        {  
          $new_rate_plan_id = null;
        }
		
		return $new_rate_plan_id;
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
	
		$room_type_id = $this->get_room_type_id_by_rate_plan_id($rate_plan_id);
		
	}
	
	function update_rate_plan($data)
	{
		$this->db->where('rate_plan_id', $data['rate_plan_id']);  
		$this->db->update("rate_plan", $data);
		if ($this->db->_error_message()) // error checking
		{
			show_error($this->db->_error_message());
		}
		
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
	
}

/* End of file - rate_model.php */