<?php
class Charge_type_model extends CI_Model {

	function __construct()
	{
		 parent::__construct();
	}
	
	function get_default_charge_type_id($company_id)
	{
		$this->db->select('id as charge_type_id');
		$this->db->from('charge_type');
		$this->db->where("is_default_room_charge_type = '1'");
		$this->db->where('company_id', $company_id);
		
		$query = $this->db->get();
		
		if (isset($query->num_rows)) {
			if($query->num_rows() >= 1)
			{		
				$result = $query->result();
				return $result[0]->charge_type_id;
			}
		}
		return NULL;
	}
	function get_charge_type_by_id($charge_type_id)
	{			
		$this->db->where('id', $charge_type_id);
		$this->db->where('is_deleted', 0);
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
	
    
    // get taxes that belong to a charge type
  //   function get_taxes($charge_type_id) {	
		// $this->db->select('t.tax_type, t.tax_type_id, t.tax_rate, t.is_percentage');
		// $this->db->from('tax_type as t, charge_type_tax_list as cttl');
		// $this->db->where('cttl.tax_type_id = t.tax_type_id');
		// $this->db->where('cttl.charge_type_id' , $charge_type_id);		
		// $this->db->where('t.is_deleted = "0"');		
		// $this->db->order_by("t.tax_type", "asc");
  //       $query = $this->db->get();
		// if ($query->num_rows() > 0)
  //       {
		// 	return $query->result_array();
  //       }        
  //   }

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
}

/* End of file - rate_model.php */