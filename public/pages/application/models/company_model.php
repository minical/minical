<?php

class Company_model extends CI_Model {
	
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    // called by pages.innGrid.net
    function get_company($uri)
    {
    	$this->db->where('website_uri', $uri);
		$this->db->from('company');
		$query = $this->db->get();
		
		if ($this->db->_error_message()) // error checking
					show_error($this->db->_error_message());
					
		if ($query->num_rows >= 1) 
		{
			$result = $query->result_array();
			return $result[0];
		}
		return NULL;
    }

    // created on Dec-29-2012 used in admin panel
	// Hopefully this functionw ill eventually replace get_company (which returns object instead of array
	function get_company_data($company_id)
	{
		$this->db->where('company_id', $company_id);
		$query = $this->db->get('company');		
		
		//echo $this->db->last_query();
		if ($query->num_rows >= 1)
		{
			$result = $query->result_array();
			return $result[0];
		}
		
		return NULL;
	}
	
	function get_company_id_from_api_key($api_key)
	{
		$sql = "SELECT 
                    k.*, kxc.*,c.name
                FROM 
                    `key` as k
                LEFT JOIN 
                    key_x_company as kxc ON kxc.key_id = k.id
                LEFT JOIN 
                    company as c ON c.company_id = kxc.company_id
                WHERE 
                    k.key = '$api_key'";
        
        $query = $this->db->query($sql);
        
        if ($query->num_rows >= 1)
		{
			$result = $query->result_array();
			return array('company_id' =>$result[0]['company_id'], 'company_name' =>$result[0]['name']);
		}
		
		return NULL;
	}
    
    function get_selling_date($company_id) {
		$selling_date = null;
		$this->db->select("selling_date");
		$this->db->where('company_id', $company_id);

		$q = $this->db->get("company");

		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		foreach ($q->result() as $row) {
			$selling_date = $row->selling_date;
		}
		//echo $this->db->last_query();
		return $selling_date;
	}
	
	// return an array of rate types grouped by Room Type
	function get_rate_plans_grouped_by_room_type($company_id)
	{
		$this->db->select("rt.id as room_type_id, rt.name as room_type_name, rp.rate_plan_id, rp.rate_plan_name");
		$this->db->from('room_type as rt');
		$this->db->join('rate_plan as rp', 'rp.room_type_id = rt.id AND rp.is_deleted != "1"', 'left');
		
		$this->db->where('rt.company_id', $company_id);
		$this->db->where('rt.is_deleted = "0"');
		$this->db->where('rp.is_selectable = "1"');
		
		$query = $this->db->get();
		
		if ($this->db->_error_message()) // error checking
					show_error($this->db->_error_message());
					
		if ($query->num_rows >= 1) 
		{
			$rows =  $query->result_array();
			$result_array = Array();
			foreach ($rows as $row)
			{
				$result_array[$row['room_type_id']]["room_type_name"] = $row['room_type_name'];
				$result_array[$row['room_type_id']]["room_type_id"] = $row['room_type_id'];
				$result_array[$row['room_type_id']]["rate_plans"][] = array(
															"rate_plan_id" => $row['rate_plan_id'],
															"rate_plan_name" => $row['rate_plan_name']
														);
			}
			return $result_array;
		}
		return NULL;
	}

	function get_company_detail($company_id)
	{
		$this->db->select('c.*, capi.*, up.*, cs.subscription_level, cs.subscription_state, cs.payment_method, cs.subscription_id, cs.balance, u.email as owner_email, p.*, count(r.room_id) as number_of_rooms_actual,c.partner_id,IFNULL(wp.username,"Minical") as partner_name',FALSE);
		$this->db->from('company as c');
		$this->db->join('company_admin_panel_info as capi', 'c.company_id = capi.company_id', 'left');
		$this->db->join('company_subscription as cs', 'c.company_id = cs.company_id', 'left');
		$this->db->join('user_permissions as up', "c.company_id = up.company_id and up.permission = 'is_owner'", 'left');
		$this->db->join('room as r', "r.company_id = c.company_id AND r.is_deleted != 1", 'left');
		$this->db->join('users as u', "up.user_id = u.id", 'left');
		$this->db->join('whitelabel_partner wp',"c.partner_id = wp.id",'left');
		$this->db->join('user_profiles as p', 'up.user_id = p.user_id', 'left');
        $this->db->where('c.company_id', $company_id);
		$query = $this->db->get();

		//echo $this->db->last_query();
		if ($query->num_rows >= 1)
		{
			$result = $query->result_array();
			$result[0]['company_id'] = $company_id;
			return $result[0];
		}

		return NULL;
	}

	function get_common_booking_engine_fields($company_id){
        $this->db->where('company_id', $company_id);		
        $this->db->order_by('id', 'DESC');		
		$query = $this->db->get('online_booking_engine_field');
		$response = array();
		if ($query->num_rows >= 1) {
			$result = $query->result_array();
            foreach($result as $setting)
            {
                $response[$setting['id']] = $setting;
            }
        }
        return $response;
    }

    function get_company_api_permission($company_id, $key = null)
    {
        $is_key_avail = $key ? "k.key = '$key' AND" : '';

        $sql = "SELECT  k.*, kxc.* FROM  `key` as k
                LEFT JOIN key_x_company as kxc ON kxc.key_id = k.id
                WHERE 
                    $is_key_avail 
                    kxc.company_id = '$company_id' ";

        $query = $this->db->query($sql);
        $result = $query->result_array();

        return count($result) > 0 ? $result : NULL;
    }

    function get_ota_id($ota_key){
        $this->db->from('otas');
        $this->db->where('key', $ota_key);

        $query = $this->db->get();

        $result = $query->row_array();
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        
        if ($query->num_rows >= 1)
        {
            return $result['id'];
        }
        return null;
    }
		
}
?>
