<?php

class Minical_export_model extends CI_Model {

	function __construct()
	{
		// Call the Model constructor
		parent::__construct();
	}

	function get_company($company_id, $filter = null)
	{
        if(isset($filter['get_last_action']) && $filter['get_last_action'])
        {
            $this->db->select('(DATEDIFF(NOW(), IF(la.last_action IS NULL, capi.creation_date, la.last_action))) as idle',FALSE);
            $this->db->join("(SELECT
                            b.company_id,
                            MAX(bl.date_time) as last_action
                        FROM
                            booking as b, booking_log as bl
                        WHERE
                            b.booking_id = bl.booking_id
                        GROUP BY b.company_id) as la "
                        , "la.company_id = c.company_id", "left");
        }
		$this->db->select('c.*,cpg.*, capi.*, up.*, cs.subscription_level, cs.limit_feature, cs.subscription_state, cs.payment_method, cs.subscription_id, cs.balance, u.email as owner_email, p.*, count(DISTINCT r.room_id) as number_of_rooms_actual,c.partner_id,IFNULL(wp.username,"Minical") as partner_name',FALSE);
		$this->db->from('company as c');
		$this->db->join('company_admin_panel_info as capi', 'c.company_id = capi.company_id', 'left');
		$this->db->join('company_subscription as cs', 'c.company_id = cs.company_id', 'left');
        $this->db->join('company_payment_gateway as cpg', 'cpg.company_id = c.company_id', 'left');
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

    function get_user_list($company_id, $except_admins = false)
	{
		$this->db->from("users");
		$this->db->from("user_permissions");
		$this->db->from("user_profiles");

		if ($except_admins) {
			$this->db->where('users.is_admin <> 1');
		}

		$this->db->where('users.id = user_profiles.user_id');
		$this->db->where('user_permissions.user_id = user_profiles.user_id');
		$this->db->where('user_permissions.company_id', $company_id);
		$this->db->where_in('permission', array('is_employee', 'is_manager', 'is_owner', 'is_admin', 'is_housekeeping'));
		$this->db->order_by('user_permissions.user_id');

		$query = $this->db->get();
		//echo $this->db->last_query();
		if ($query->num_rows >= 1)
		{
			return $query->result_array();
		}

		return NULL;
	}

	// Called from Employee Settings to display all users and permissions that belong to this company
	function get_all_user_permissions($company_id)
	{
		$this->db->from('user_permissions');

		$this->db->where('company_id', $company_id);
		$this->db->where_in('permission', array('is_employee', 'is_manager', 'is_owner', 'is_admin', 'is_housekeeping'));

		$this->db->order_by('user_id');

		$query = $this->db->get();

		$base_roles = $query->result_array();
	    $user_ids = array_column($base_roles, 'user_id');

	    // Step 2: For users who are employees, get their extra permissions
	    $employee_user_ids = array_column(
	        array_filter($base_roles, function ($item) {
	            return $item['permission'] === 'is_employee';
	        }),
	        'user_id'
	    );

	    $extra_permissions = [];
	    if (!empty($employee_user_ids)) {
	        // $this->db->select('user_id, permission');
	        $this->db->from('user_permissions');
	        $this->db->where('company_id', $company_id);
	        $this->db->where_in('user_id', $employee_user_ids);
	        $this->db->where_not_in('permission', ['is_employee', 'is_manager', 'is_owner', 'is_admin']);

	        $extra_permissions = $this->db->get()->result_array();
	    }

	    // Step 3: Combine all permissions
	    $all_permissions = array_merge($base_roles, $extra_permissions);

		if ($all_permissions >= 1)
		{
			return $all_permissions;
		}

		return NULL;
	}

	function get_booking_fields($booking_id)
    {
        $this->db->where('company_id', $this->company_id);
        $this->db->where('show_on_booking_form', 1);
        $this->db->where('is_deleted', 0);
        $this->db->from('booking_field as bf');
        $this->db->join('booking_x_booking_field as bxbf', "bxbf.booking_field_id = bf.id and bxbf.booking_id = '$booking_id'", 'left');
        $query = $this->db->get();
        $booking_fields_result = $query->result_array();

        $booking_fields = array();
        foreach($booking_fields_result as $field)
        {
            $booking_fields[$field['id']] = $field['value'];
        }
        return $booking_fields;
    }

    function get_customer_fields($company_id, $show_deleted = false, $field = false)
	{
		$this->db->where('company_id', $company_id);
		if (!$show_deleted)
			$this->db->where('is_deleted', 0);
		$this->db->order_by("id", "asc");

		if ($field)
            $this->db->where($field, 1);
		
		$query = $this->db->get('customer_field');
		
		if ($query->num_rows >= 1) 
			return $query->result_array();
		return NULL;
	}

	function get_payment_types($company_id)
    {       
        $this->db->where('company_id', $company_id);
		$this->db->where('is_deleted', 0);
		$this->db->where('is_read_only', 0); // read_only payment types are not shown
		
		$query = $this->db->get('payment_type');
		
	    if ($query->num_rows >= 1) 
		{	
			return $query->result();
		}
		
		return NULL;
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

	function get_customer_types($company_id, $show_deleted = false)
	{
		$this->db->where('company_id', $company_id);
		if (!$show_deleted)
			$this->db->where('is_deleted', 0);
		$this->db->order_by("sort_order", "asc");
		
		$query = $this->db->get('customer_type');
		
		if ($query->num_rows >= 1) 
			return $query->result_array();
		return NULL;
	}

	function get_booking_source($company_id, $show_deleted = false, $show_hidden = false)
	{
		$this->db->where('company_id', $company_id);
		if (!$show_deleted)        
			$this->db->where('is_deleted', 0);
        
        if (!$show_hidden)
            $this->db->where('is_hidden', 0);
        
		$this->db->order_by("sort_order, name", "asc");
		
		$query = $this->db->get('booking_source');
		
		if ($query->num_rows >= 1) 
			return $query->result_array();
		return NULL;
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

	function get_export_room_types($company_id)
    {
        $this->db->where('rt.company_id', $company_id);
        $this->db->select('rt.*,r.*,rl.*,f.*,rt.can_be_sold_online as room_type_sold_online,r.can_be_sold_online as room_sold_online');
        $this->db->from('room_type as rt');
        $this->db->join('room as r' , 'rt.id = r.room_type_id', 'left');
        $this->db->join('room_location as rl' , 'rl.id = r.location_id', 'left');
        $this->db->join('floor as f' , 'f.id = r.floor_id', 'left');
        $this->db->where('r.is_deleted',0);
        $this->db->where('r.is_hidden',0);
        $this->db->order_by("r.room_id","asc");
        $query = $this->db->get();

        if ($query->num_rows() > 0)
        {
            $result = $query->result_array(0);
            return $result;
        }
        else
        {
            return NULL;
        }
    }

     function get_all_rate_plans($company_id){

	    $sql = '
                select 
                    rp.rate_plan_name,
                    r.rate_plan_id as rate_planid,
                    rp.charge_type_id,
                    rp.room_type_id as roomtype_id,
                    rt.name as room_type_name,
                    r.rate_id,
                    r.base_rate,
                    dr.date_start,
                    dr.date_end,
                    "" as description,
                    rp.currency_id,
                    rp.policy_code,
                    dr.monday,
                    dr.tuesday,
                    dr.wednesday,
                    dr.thursday,
                    dr.friday,
                    dr.saturday,
                    dr.sunday,
                    r.adult_1_rate,
                    r.adult_2_rate,
                    r.adult_3_rate,
                    r.adult_4_rate,
                    r.additional_adult_rate,
                    r.additional_child_rate,
                    r.minimum_length_of_stay as minimumlength,
                    r.maximum_length_of_stay as maximumlength, 
                    IF(r.closed_to_departure = 1, "true", "false") as cls_to_departure,
                    IF(r.closed_to_arrival = 1, "true", "false") as cls_to_arrival, 
                    IF(r.can_be_sold_online = 1, "true", "false") as can_sold_online,
                    IF(rp.is_selectable = 1, "true", "false") as is_selectable,
                    
                    IFNULL((
                        SELECT
                            GROUP_CONCAT(e.extra_id)
                        FROM
                            extra as e
                        LEFT JOIN rate_plan_x_extra as rpxe ON e.extra_id = rpxe.extra_id
                        LEFT JOIN extra_rate as er ON e.extra_id = er.extra_id
                        WHERE
                            rpxe.rate_plan_id = rp.rate_plan_id
                            AND e.is_deleted = 0
                            AND rpxe.room_type_id = rt.id
                    ), "") as rate_plan_extras
                from rate_plan as rp
                left join room_type as rt on rt.id = rp.room_type_id
                left join rate as r on r.rate_plan_id = rp.rate_plan_id
                left join date_range_x_rate as drxr on drxr.rate_id = r.rate_id
                left join date_range as dr on dr.date_range_id = drxr.date_range_id
              where 
              rt.company_id =  '.$company_id.' and
              rp.is_deleted = 0
	    ';

        $query = $this->db->query($sql);
        
        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());

        if ($query->num_rows >= 1)
        {
            return $query->result_array();
        }
        return NULL;
    }

     function get_all_rate_plans_custom($company_id) {
	    $sql = 'select 
					rp.rate_plan_name,
                    r.rate_plan_id as rate_planid,
                    rp.charge_type_id,
                    rp.room_type_id as roomtype_id,
                    rt.name as room_type_name,
                    r.rate_id,
                    r.base_rate,
                    dr.date_start,
                    dr.date_end,
                    "" as description,
                    rp.currency_id,
                    dr.monday,
                    dr.tuesday,
                    dr.wednesday,
                    dr.thursday,
                    dr.friday,
                    dr.saturday,
                    dr.sunday,
                    r.adult_1_rate,
                    r.adult_2_rate,
                    r.adult_3_rate,
                    r.adult_4_rate,
                    r.additional_adult_rate,
                    r.additional_child_rate,
                    r.minimum_length_of_stay as minimumlength,
                    r.maximum_length_of_stay as maximumlength, 
                    IF(r.closed_to_departure = 1, "true", "false") as cls_to_departure,
                    IF(r.closed_to_arrival = 1, "true", "false") as cls_to_arrival, 
                    IF(r.can_be_sold_online = 1, "true", "false") as can_sold_online,
                    IF(rp.is_selectable = 1, "true", "false") as is_selectable,
                    
                    IFNULL((
                        SELECT
                            GROUP_CONCAT(e.extra_id)
                        FROM
                            extra as e
                        LEFT JOIN rate_plan_x_extra as rpxe ON e.extra_id = rpxe.extra_id
                        LEFT JOIN extra_rate as er ON e.extra_id = er.extra_id
                        WHERE
                            rpxe.rate_plan_id = rp.rate_plan_id
                            AND e.is_deleted = 0
                            AND rpxe.room_type_id = rt.id
                    ), "") as rate_plan_extras
                from rate_plan as rp
                left join booking as b on b.rate_plan_id = rp.rate_plan_id
                left join booking_room_history as brh on brh.booking_id = b.booking_id
                left join room_type as rt on rt.id = rp.room_type_id
                left join rate as r on r.rate_plan_id = rp.rate_plan_id
                left join date_range_x_rate as drxr on drxr.rate_id = r.rate_id
                left join date_range as dr on dr.date_range_id = drxr.date_range_id
              where 
              rt.company_id =  '.$company_id.' and
              rp.is_deleted = 0 and
              (
                rp.is_selectable = 1 OR
				  (
					b.state != 4 and 
					b.is_deleted = 0 and
                    b.booking_id NOT IN (8492948427,8492948428,8492948474,8492948478,8492948485,8492949352,8492979844,8492983280,8492986150,8493008050,8492289743,8493146071,8493146073,8493196981,8493281113,8493310884,8493310895,8493310898,8493446869,8493447329,8493512369,8493551076,8493559229,8493064176,8492589790,8493215965,8493215958,8493591882,8494395175,8494395243,8492908634,8492908624,8492908628,8492908621,8494360574,8494549310,8494550860,8494585989,8494604583,8494612343,8494623220,8494641853,8494645549,8494654250,8494657531,8494657683,8494661746,8494674490,8494691340,8494697194)
				  )
              )
              group by r.rate_id';

        $query = $this->db->query($sql);

        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());

        if ($query->num_rows >= 1)
        {
            return $query->result_array();
        }
        return NULL;
    }

    function get_customer_card_details($company_id){

        $this->db->select('c.*,cd.*,cxcf.*,GROUP_CONCAT(cf.name) as custom_fields_name,GROUP_CONCAT(cxcf.value) as custom_fields_value,c.customer_id as customers_id,c.customer_name as name_customer');
        $this->db->from('customer as c');
        $this->db->join('customer_field as cf', 'cf.company_id = c.company_id AND cf.is_deleted = 0', 'left');
        $this->db->join('customer_x_customer_field as cxcf', 'cxcf.customer_field_id = cf.id AND cxcf.customer_id = c.customer_id', 'left');
        $this->db->join('customer_card_detail as cd', 'c.customer_id = cd.customer_id', 'left');
        // $this->db->join('customer_types as ct', 'c.customer_type_id = ct.id', 'left');
        $this->db->where('c.company_id', $company_id);
        $this->db->where('c.is_deleted', 0);
        // $this->db->where('ct.is_deleted', 0);
        $this->db->group_by('c.customer_id');

        $query = $this->db->get(); 

        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());

        if ($query->num_rows >= 1)
        {
            $result =  $query->result_array();
            return $result;
        }
        return NULL;
    }

    function get_customer_type_details($company_id, $customer_type_ids){

        $this->db->select('ct.id, ct.name as customer_type_name, c.customer_id');
        $this->db->from('customer as c');
        $this->db->join('customer_type as ct', 'c.customer_type_id = ct.id', 'left');
        $this->db->where('c.company_id', $company_id);
        $this->db->where('c.is_deleted', 0);
        $this->db->where('ct.is_deleted', 0);
        $this->db->where_in('ct.id', $customer_type_ids);
        $this->db->group_by('c.customer_id');

        $query = $this->db->get(); 

        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());

        if ($query->num_rows >= 1)
        {
            $result =  $query->result_array();
            return $result;
        }
        return NULL;
    }

    function get_booking_details($company_id)
    {
        $this->db->select('b.*,bf.*,bxbf.*,b.booking_id as bookingid,GROUP_CONCAT(bf.name) as custom_fields_name, GROUP_CONCAT(bxbf.value) as custom_fields_value,brh.*,bxblg.booking_group_id,blg.*,r.*,rt.name as room_type_name,ct.name as charge_type_name,bs.name as booking_source_name');
        $this->db->from('booking as b');
        $this->db->join('booking_block as brh', 'b.booking_id = brh.booking_id', 'left');
        $this->db->join('booking_x_booking_linked_group as bxblg', 'bxblg.booking_id = brh.booking_id', 'left');
        $this->db->join('booking_linked_group as blg', 'blg.id = bxblg.booking_group_id', 'left');
        $this->db->join('booking_field as bf', 'bf.company_id = b.company_id AND bf.is_deleted = 0', 'left');
        $this->db->join('booking_x_booking_field as bxbf', 'bxbf.booking_id = b.booking_id AND bf.id = bxbf.booking_field_id', 'left');
        $this->db->join('room as r', 'r.room_id = brh.room_id', 'left');
        $this->db->join('room_type as rt', 'rt.id = brh.room_type_id', 'left');
        $this->db->join('charge_type as ct', 'ct.id = b.charge_type_id', 'left');
        $this->db->join('booking_source as bs', 'bs.id = b.source', 'left');
        // $this->db->where('b.state !=', 4 );
        $this->db->where('b.is_deleted', 0 );
        $this->db->where('b.company_id', $company_id);
        $this->db->group_by('brh.booking_room_history_id');
        $this->db->group_by('b.booking_id');

        $query = $this->db->get();

        // echo $this->db->last_query();die;

        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());

        if ($query->num_rows >= 1)
        {
            $result =  $query->result_array();
            return $result;
        }
        return NULL;
    }

     function get_booking_staying_customers_list($booking_ids, $company_id)
    {
        $this->db->select('booking_staying_customer_list.customer_id');
        $this->db->select('customer_name');
        $this->db->select('booking_staying_customer_list.booking_id');
        $this->db->from('booking_staying_customer_list');
        $this->db->join('customer', 'customer.customer_id = booking_staying_customer_list.customer_id', 'left');
        $this->db->where('booking_staying_customer_list.company_id', $company_id);
        $this->db->where_in('booking_staying_customer_list.booking_id', $booking_ids);
        $results = $this->db->get();
        
        if (empty($results))
        {
            return array();
        }
        else
        {
            return $results->result_array();
        }
    }

    function get_booking_log_details($company_id)
    {
        $this->db->select('bl.*');
        $this->db->from('booking as b');
        $this->db->join('booking_log as bl', 'b.booking_id = bl.booking_id', 'left');
        $this->db->where('b.is_deleted', 0 );
        $this->db->where('b.company_id', $company_id);
        // $this->db->group_by('b.booking_id');

        $query = $this->db->get();

        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());

        if ($query->num_rows >= 1)
        {
            $result =  $query->result_array();
            return $result;
        }
        return NULL;
    }

    function get_all_tax_types($company_id)
    {
        $this->db->select('tt.*,tt.tax_rate as taxrate,tt.tax_type_id as taxtype_id,tpb.*,tpb.tax_rate as price_bracket_rate,tpb.is_percentage as is_price_bracket_percentage,tt.is_percentage as tax_percentage');
        $this->db->from('tax_type as tt');
        $this->db->join('tax_price_bracket as tpb','tpb.tax_type_id = tt.tax_type_id', "left");
        $this->db->where('tt.company_id',$company_id);
        $this->db->where('tt.is_deleted', 0);
        // $this->db->where('tpb.is_deleted', 0);
        $query = $this->db->get();

	    if ($query === false) {
	        // Log the error for debugging
	        log_message('error', 'DB error in get_all_tax_types: ' . $this->db->last_query());
	        return null;
	    }

	    $result = $query->result_array();
	    
	    if (isset($result[0])) {
	        return $result;
	    } else {
	        return null;
	    }
    }

    function get_all_the_charge_types($company_id){

    	$this->db->select('ct.*,c.*,c.charge_id as chargeid');
        $this->db->from('charge_type as ct');
        $this->db->join('charge as c', 'c.charge_type_id = ct.id', 'left');

	    // Manual parentheses since group_start() does not work
	    $this->db->where("(ct.company_id = 0 OR ct.company_id = $company_id)", NULL, FALSE);

        $this->db->where('ct.is_deleted', 0);
        $this->db->where('c.is_deleted', 0);

        $query = $this->db->get();

	    if ($query->num_rows() >= 1) {
	        return $query->result_array();
        }
        return NULL;
    }

    function get_all_extras($company_id){

        $this->db->select('e.*,er.*,ct.*,e.extra_id as extraid,bxe.booking_id,bxe.booking_extra_id,bxe.start_date,bxe.end_date,bxe.rate as defaultrate,bxe.quantity');
        $this->db->from('extra as e');
        $this->db->join('extra_rate as er','e.extra_id = er.extra_id','left');
        $this->db->join('booking_x_extra as bxe',"bxe.extra_id = e.extra_id", 'left');
        $this->db->join('charge_type as ct',"ct.id = e.charge_type_id", 'left');
        $this->db->where('e.company_id', $company_id);
        $this->db->where('e.is_deleted', 0);
        $this->db->where('bxe.is_deleted', 0);
        
        $query = $this->db->get();

        if ($query->num_rows >= 1)
        {
            return $result = $query->result_array();
        }

        return NULL;
    }

    function get_payment_details($company_id){

        $this->db->from('payment_type as pt');
        $this->db->join('payment as p', 'p.payment_type_id = pt.payment_type_id', 'left');
        $this->db->where('pt.company_id', $company_id);
        $this->db->where('pt.is_deleted', 0);
        $this->db->where('p.is_deleted', 0);
        $query = $this->db->get();

        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());
        
        if ($query->num_rows >= 1)
        {
            $result =  $query->result_array();
            return $result;
        }
        return NULL;

    }

    function get_statement($company_id){

        $this->db->select('b.booking_id,bxs.*,s.*');
        $this->db->from('booking_x_statement as bxs');
        $this->db->join('booking as b', "bxs.booking_id = b.booking_id", "left");
        $this->db->join('statements as s', "bxs.statement_id = s.statement_id", "left");
        $this->db->where('b.company_id', $company_id);
        $this->db->where('s.is_deleted', 0);
        $this->db->where('b.is_deleted', 0);

        $query = $this->db->get();

        if ($query && $query->num_rows >= 1) {
            $result = $query->result_array();
            return $result;
        }
        return null;

    }

    function get_options_data($company_id){

        $this->db->select('op.*');
        $this->db->from('options as op');
        $this->db->where('op.company_id', $company_id);
        $this->db->where('op.name NOT LIKE', 'stripe_%');
        
        $query = $this->db->get();

        if ($query->num_rows >= 1) {
            $result = $query->result_array();
            return $result;
        }
        return null;

    }

    function get_posts_data($company_id){

        $this->db->select('p.*');
        $this->db->from('posts as p');
        $this->db->where('p.company_id', $company_id);
        $this->db->where('p.is_deleted', 0);
        $this->db->where('p.post_type NOT LIKE', 'subscription%');

        $query = $this->db->get();

        if ($query->num_rows >= 1) {
            $result = $query->result_array();
            return $result;
        }
        return null;

    }

    function get_postmeta_data($company_id){

        $this->db->select('pm.*');
        $this->db->from('posts as p');
        $this->db->join('postmeta as pm', "pm.post_id = p.post_id", "left");
        $this->db->where('p.company_id', $company_id);
        $this->db->where('p.is_deleted', 0);

        $query = $this->db->get();
        if ($query->num_rows >= 1) {
            $result = $query->result_array();
            return $result;
        }
        return null;

    }
    
    
 }
?>	