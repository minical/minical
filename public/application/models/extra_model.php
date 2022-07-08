<?php

class Extra_model extends CI_Model {

	function __construct()
    {
        parent::__construct();
    }

	function get_extra($extra_id)
	{
		$this->db->where('extra_id', $extra_id);
		$this->db->where('is_deleted', 0);
		$this->db->order_by("extra_id", "asc");
		
		$query = $this->db->get('extra');
		
		if ($query->num_rows >= 1)
		{
			$result = $query->result_array();
			return $result[0];
		}
		
		return NULL;
	}	
	
	// temporary method to retrieve default rate.
	// This isn't a good way of handling this, because extra_rates are date dependent.
	// Eventually, I'll have to implement Rate_Type & Rate style to extra_rates.
	function get_extra_default_rate($extra_id)
	{
		$this->db->where('extra_id', $extra_id);
		$this->db->order_by("extra_rate_id", "desc");
		
		$query = $this->db->get('extra_rate');
		
		if ($query->num_rows >= 1)
		{
			$result = $query->result_array();
			return $result[0]['rate'];
		}
		
		return NULL;
	}

	function get_extras($company_id, $is_pos = false)
	{
		$this->db->from('extra as e, extra_rate as er');
		$this->db->where('e.extra_id = er.extra_id');
		$this->db->where('e.company_id', $company_id);
		$this->db->where('e.is_deleted', 0);
		if($is_pos)
			$this->db->where('e.show_on_pos', 1);
		$this->db->order_by("e.extra_id", "asc");
		$this->db->group_by("e.extra_id");
		
		$query = $this->db->get();
		
		if ($query->num_rows >= 1)
		{
			return $result = $query->result_array();
		}
		
		return NULL;
	}

	function get_pos_extra_items($company_id, $item_name)
	{
		$this->db->from('extra as e, extra_rate as er, charge_type as ct');
		$this->db->where('e.extra_id = er.extra_id');
		$this->db->where('e.charge_type_id = ct.id');
		$this->db->where('e.company_id', $company_id);
		$this->db->where('e.is_deleted', 0);
		$this->db->where('e.show_on_pos', 1);
		$this->db->like('e.extra_name', $item_name);
		$this->db->order_by("e.extra_id", "asc");
		$this->db->group_by("e.extra_id");
		
		$query = $this->db->get();
		
		if ($query->num_rows >= 1)
		{
			return $result = $query->result_array();
		}
		
		return NULL;
	}	
	

	function create_extra($company_id, $extra_name)
	{
		$data = array (
			'extra_name' => $extra_name,
			'company_id' => $company_id
		);
		$this->db->insert('extra', $data);		
		
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
	
	function update_extra($extra_id, $data, $company_id = null)
	{
		$data = (object) $data;
		
		if ($company_id != null) 
		{
			$this->db->where('company_id', $company_id);
		}
		
		$this->db->where('extra_id', $extra_id);
		$this->db->update('extra', $data);
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		return TRUE;
	}

	function delete_rate_plan_etras($rate_plan_id, $room_type_id, $extra_id){
		$this->db->where('rate_plan_id', $rate_plan_id);
		$this->db->where('room_type_id', $room_type_id);
		$this->db->where('extra_id', $extra_id);
        $this->db->delete('rate_plan_x_extra');
	}

    function delete_extras($company_id){

        $data = Array('is_deleted' => 1);
        $this->db->where('company_id', $company_id);
        $this->db->update("extra", $data);

        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }

    }

	function create_rate_plan_extras($rate_plan_id, $extra_id, $room_type_id)
	{
		$data = array (
			'rate_plan_id' => $rate_plan_id,
			'room_type_id' => $room_type_id,
			'extra_id' => $extra_id
		);
		$this->db->insert('rate_plan_x_extra', $data);		
		
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

	function get_rate_plan_extras($rate_plan_id, $room_type_id = null)
	{
		$where_condition = "";
		if($room_type_id)
		{
			$where_condition = " AND rpxe.room_type_id = '$room_type_id'";
		}

		$sql = "
			SELECT
				*
			FROM
				extra as e
			LEFT JOIN rate_plan_x_extra as rpxe ON e.extra_id = rpxe.extra_id
			LEFT JOIN extra_rate as er ON e.extra_id = er.extra_id
			WHERE
				rpxe.rate_plan_id = '$rate_plan_id'
				AND e.is_deleted = 0
				$where_condition
			GROUP BY e.extra_id
		";
		
        $query = $this->db->query($sql);
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
					
		if ($query->num_rows >= 1) 
		{
			return $query->result_array();
		}
		return NULL;
	}

    function create_all_extras($data)
    {

        $this->db->insert('extra', $data);

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

    function get_rate_plan_extra($rate_plan_id, $extra_id, $room_type_id){

        $this->db->from('rate_plan_x_extra');
        $this->db->where('rate_plan_id', $rate_plan_id);
        $this->db->where('room_type_id', $room_type_id);
        $this->db->where('extra_id', $extra_id);
        $query = $this->db->get();

        if ($query->num_rows >= 1)
        {
            return $result = $query->result_array();
        }

        return NULL;
    }
	
}

/* End of file - extra_model.php */