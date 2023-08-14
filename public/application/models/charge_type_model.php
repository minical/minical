<?php
class Charge_type_model extends CI_Model {

	function __construct()
	{
		 parent::__construct();
	}
	
	function get_charge_types($company_id)
	{		
		$this->db->where('company_id', $company_id);
		$this->db->where('is_deleted', 0);
		$this->db->order_by("id", "asc");
		$charge_type_query = $this->db->get('charge_type');
		if ($charge_type_query->num_rows >= 1) 
			return $charge_type_query->result_array();
		return NULL;
	}

	// get taxes that belong to a charge type
    function get_taxes($charge_type_id, $amount = null) {
		
		$select_tax_rate = "t.tax_rate,";
		$join_tax_brackets = "";
		// fetch tax rate from price brackets according to charge amount
		if ($amount) {
			$select_tax_rate = "IF(t.is_brackets_active, tpb.tax_rate, t.tax_rate) as tax_rate,";
			$join_tax_brackets = "LEFT JOIN tax_price_bracket as tpb 
					ON tpb.tax_type_id = t.tax_type_id AND '$amount' BETWEEN tpb.start_range AND tpb.end_range";
		}
		
		$sql = "SELECT 
					$select_tax_rate
					t.tax_type, 
					t.tax_type_id, 
					t.is_percentage,
					t.is_tax_inclusive 
				FROM tax_type as t 
				LEFT JOIN charge_type_tax_list as cttl ON cttl.tax_type_id = t.tax_type_id
				$join_tax_brackets
				WHERE 
					cttl.charge_type_id = '$charge_type_id' AND 
					t.is_deleted = '0' 
				ORDER BY t.tax_type asc";
		
        $query = $this->db->query($sql);
				
		if ($query->num_rows() > 0)
        {
        	return $query->result_array();
        }        
    }
	
	function get_charge_type_tax_list($charge_type_id)
	{		
		$this->db->where('charge_type_id', $charge_type_id);
		$this->db->order_by("tax_type_id", "asc");
		$query = $this->db->get('charge_type_tax_list');
		
		return $query->result_array();
	}

    function get_charge_tax($charge_type_id,$tax_type_id)
    {
        $this->db->where('charge_type_id', $charge_type_id);
        $this->db->where('tax_type_id', $tax_type_id);
        $this->db->order_by("tax_type_id", "asc");
        $query = $this->db->get('charge_type_tax_list');

        return $query->result_array();
    }
	
	function create_charge_type($company_id, $name)
	{
		//Add charge_type
		$data = array (
			'name' => $name,
			'company_id' => $company_id
		);
		$this->db->insert('charge_type', $data);
		
		//get newly created charge_type_id
		//$charge_type_id = $this->db->insert_id();
    $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
		$result = $query->result_array();
    if(isset($result[0]))
    {  
      $charge_type_id = $result[0]['last_id'];
    }
		else
    {  
      $charge_type_id = null;
    }
		
		//TO DO: Error Checking.
		return $charge_type_id;
	}
	
	function create_room_charge_type($company_id, $is_default_room_charge_type = 0)
	{
		//Add charge_type
		$data = array (
			'name' => 'Room Charge',
			'company_id' => $company_id,
			'is_room_charge_type' => '1',
			'is_default_room_charge_type' => $is_default_room_charge_type
		);
		$this->db->insert('charge_type', $data);
		
		//get newly created charge_type_id
		//$charge_type_id = $this->db->insert_id();
    $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
		$result = $query->result_array();
    if(isset($result[0]))
    {  
      $charge_type_id = $result[0]['last_id'];
    }
		else
    {  
      $charge_type_id = null;
    }
		
		//TO DO: Error Checking.
		return $charge_type_id;
	}
	
	function add_charge_type_tax($charge_type_id, $tax_type_id)
	{
		$data = array (			
			'charge_type_id' => $charge_type_id,
			'tax_type_id' => $tax_type_id
		);
		
		$this->db->insert('charge_type_tax_list', $data);
	}
	
	function delete_charge_type_tax($charge_type_id, $tax_type_id)
	{		
		$this->db->where('charge_type_id', $charge_type_id);
		$this->db->where('tax_type_id', $tax_type_id);
		
		$this->db->delete('charge_type_tax_list');
	}
	
	function update_charge_type($charge_type_id, $data, $company_id = null)
	{	
		$data = (object) $data;
		
		if ($company_id != null) {
			$this->db->where('company_id', $company_id);
		}
		
		$this->db->where('id', $charge_type_id);
		$this->db->update('charge_type', $data);
		
		return TRUE;
	}

    function delete_charge_type($company_id = null)
    {
        $this->db->where('company_id', $company_id);

        $this->db->delete('charge_type');
    }
	
	function get_charge_type_by_id($charge_type_id)
	{			
		$this->db->where('id', $charge_type_id);
		// $this->db->where('is_deleted', 0);
		$charge_type_query = $this->db->get('charge_type');
		if ($charge_type_query->num_rows >= 1) 
		{
			return $charge_type_query->row_array();
		}
		return NULL;
	}
	

	function get_room_charge_type_id($booking_id)
	{
		$this->db->select('booking.charge_type_id as booking_charge_type_id, rate_plan.charge_type_id as rate_plan_charge_type_id, booking.use_rate_plan');
		$this->db->where('booking_id', $booking_id);
		$this->db->join('rate_plan', 'booking.rate_plan_id = rate_plan.rate_plan_id', 'left');
		
		$charge_type_query = $this->db->get('booking');
		if ($charge_type_query->num_rows >= 1) 
		{
			$booking = $charge_type_query->row_array();
			
			if ($booking['use_rate_plan'] == 1)
			{
				$room_charge_type_id = $booking['rate_plan_charge_type_id'];
			}
			else
			{
				$room_charge_type_id = $booking['booking_charge_type_id'];
			}

			return $room_charge_type_id;
		}
		return NULL;
	}

	function get_default_room_charge_type($company_id)
	{
		$this->db->where('company_id', $company_id);
		$this->db->where('is_default_room_charge_type', 1);
		$charge_type_query = $this->db->get('charge_type');
		if ($charge_type_query->num_rows >= 1) 
		{
			$charge_type = $charge_type_query->row_array();
			return $charge_type;
		}
		return NULL;
	}
	
	function set_default_room_charge_type($company_id, $charge_type_id) 
	{
		$this->db->where('company_id', $company_id);
		$data = array ( 'is_default_room_charge_type' => '0' );
		$this->db->update('charge_type', $data);

		$this->db->where('id', $charge_type_id);
		$data = array ( 'is_default_room_charge_type' => '1' );
		$this->db->update('charge_type', $data);

	}
	


	// This will be deprecated soon (replaced by get_room_charge_type_id) -- Jaeyun 01/08/2014
	function get_room_charge_type($company_id)
	{
		$this->db->where('company_id', $company_id);
		$this->db->where('is_room_charge_type', 1);
		$this->db->where('is_deleted', 0);
		$charge_type_query = $this->db->get('charge_type');
		if ($charge_type_query->num_rows >= 1) 
		{
			return $charge_type_query->row_array();
		}
		return NULL;
	}
	
	function get_room_charge_types($company_id){
		$this->db->where('company_id', $company_id);
		$this->db->where('is_deleted', 0);
		$this->db->where('is_room_charge_type', 1);
		$this->db->order_by("is_default_room_charge_type", "DESC");
		$charge_type_query = $this->db->get('charge_type');
		if ($charge_type_query->num_rows >= 1) 
			return $charge_type_query->result_array();
		return NULL;
	}

	// To be deleted?
	function get_charge_type_by_name($name, $company_id)
	{			
		$this->db->where('company_id', $company_id);
		$this->db->where('name', $name);
		$this->db->where('is_deleted', 0);
		$charge_type_query = $this->db->get('charge_type');
		if ($charge_type_query->num_rows >= 1) 
		{
			return $charge_type_query->row_array();
		}
		return NULL;
	}


    function create_charge_types($data)
    {
        //Add charge_type
        $this->db->insert('charge_type', $data);

        $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
        $result = $query->result_array();
        if(isset($result[0]))
        {
            $charge_type_id = $result[0]['last_id'];
        }
        else
        {
            $charge_type_id = null;
        }

        return $charge_type_id;
    }

    function delete_charge_types($company_id){

        $data = Array('is_deleted' => 1);

        $this->db->where('company_id', $company_id);
        $this->db->update("charge_type", $data);

        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
    }

    function get_deleted_charge_types($company_id)
	{		
		$this->db->from('charge as c');
		$this->db->join('charge_type as ct', 'ct.id = c.charge_type_id', 'left');
		$this->db->where('ct.company_id', $company_id);
		$this->db->where('c.is_deleted', 0);
		$this->db->where('ct.is_deleted', 1);
		$this->db->order_by("id", "asc");
		$charge_type_query = $this->db->get();
		if ($charge_type_query->num_rows >= 1) 
			return $charge_type_query->result_array();
		return NULL;
	}
}

/* End of file - charge_type_model.php */