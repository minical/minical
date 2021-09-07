<?php
class Room_type_model extends CI_Model {

	function __construct()
	{
		 parent::__construct();
	}
    
	// return company's room_types
	function get_room_types($company_id)
	{
		$this->db->where('company_id', $company_id);
		$this->db->where('is_deleted', 0);
		$this->db->order_by("id", "asc"); // needs to be in asc order, so newly added room types are in the bottom of the list
		
		$query = $this->db->get('room_type');
		
		if ($query->num_rows >= 1)
		{
			return $query->result_array();
		}
		
		return NULL;
	}
	
	function get_room_type($room_type_id)
	{
		$this->db->where('id', $room_type_id);
		$query = $this->db->get('room_type');
		
		if ($query->num_rows() == 1)
		{
			return $query->row_array(0);
		}
		else
		{
			return NULL;
		}
	}

	function get_room_type_availability($company_id, $ota_id, $start_date, $end_date, $adult_count = null, $children_count = null, $filter_can_be_sold_online = true, $company_group_id = null, $get_max_availability = true, $get_inventorysold = true, $get_closeout_status = true, $get_inventory = true)
	{
        $inventory = array();
		
        $room_types = $this->get_room_types($company_id, $adult_count, $children_count, $company_group_id);
		if(count($room_types)!=0){
            foreach ($room_types as $room_type)
            {
                $inventory[$room_type['id']] = $room_type;
            }
        }
        
        $query = http_build_query(
            array(
                'start_date'                => $start_date,
                'ota_id'                    => $ota_id,
                'max_adults'                => $adult_count,
                'max_children'              => $children_count,
                'end_date'                  => $end_date,
                'room_types'                => array_keys($inventory),
                'filter_can_be_sold_online' => $filter_can_be_sold_online ? true : false,
                'get_max_availability'		=> $get_max_availability,
                'get_inventorysold'			=> $get_inventorysold,
                'get_closeout_status'		=> $get_closeout_status,
                'get_inventory'             => $get_inventory,
                'company_id'                => $company_id,
                'X-API-KEY'                 => $this->config->item('api_key')
            )
        );
        $req = Requests::get($this->config->item('api_url').'/v1/inventory/availability?'.$query);
        // if response empty aka no availability, ensure actual array is created anyway
		if(isset($_GET['dev_mode']) && $_GET['dev_mode'] === getenv('DEVMODE_PASS')){
			echo $this->config->item('api_url').'/v1/inventory/availability?'.$query;
		}
//		print_r($req);die;
		
		$avail_array = $req->body ? json_decode($req->body, true) : null;
        $avail_array = empty($avail_array) ? array() : $avail_array;

        foreach ($avail_array as $room_type_id => $room_type_availability) {
            $inventory[$room_type_id]['availability'] = $room_type_availability;
        }
		
        return $inventory;
	}
    
    // get net availability

    function get_room_types_data($company_id, $adult_count = null, $children_count = null, $company_group_id = null)
	{
		$this->db->select("rt.*, count(rp.rate_plan_id) as rate_plan_count");
		$this->db->from("room_type as rt");
		$this->db->join("rate_plan as rp", "rp.room_type_id = rt.id and rp.is_deleted != '1' and rp.is_selectable = '1'", "left");
		if($company_group_id)
		{
			$this->db->join("company_groups_x_company as cgxc", "cgxc.company_id = rt.company_id", "left");
			$this->db->where('cgxc.company_group_id', $company_group_id);
		}
		else
		{
	        $this->db->where('rt.company_id', $company_id);
		}
        if($adult_count){
            $this->db->where("rt.max_adults >= $adult_count");
        }
        if($children_count){
            $this->db->where("rt.max_children >= $children_count");
        }
        if($adult_count || $children_count){
            $total_occupants = (int)$adult_count + (int)$children_count;
            $this->db->where("rt.max_occupancy >= $total_occupants");
            $this->db->where("rt.min_occupancy <= $total_occupants");
        }
        $this->db->where('rt.is_deleted', 0);
        $this->db->group_by("rt.id"); // needs to be in asc order, so newly added room types are in the bottom of the list
		$this->db->order_by("rt.sort", "asc"); // needs to be in asc order, so newly added room types are in the bottom of the list

        $query = $this->db->get();

        if ($query->num_rows >= 1) {
            return $query->result_array();
        }

        return null;
    }

    function get_room_type_by_room_id($room_id)
	{
		$this->db->where('r.room_id', $room_id);
		$this->db->where('r.room_type_id = rt.id');
		$query = $this->db->get('room as r, room_type as rt');
		
		
		if ($query->num_rows() > 0)
		{
			$result = $query->row_array(0);			
			return $result;
		}
		else
		{
			return NULL;
		}
	}
	
	
}
/* End of file - rate_model.php */