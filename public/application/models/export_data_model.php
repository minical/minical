<?php

class Export_data_model extends CI_Model {

	function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        
    }

    /**
     * Get rooms
     * @param integer $company_id
     * @param string  $sort_by
     * @return array
     */

    function get_export_room_types($company_id)
    {
    	$db2 = $this->load->database('roomsy_db', TRUE);
        $db2->select('rt.*,r.*,rl.*,f.*,rt.can_be_sold_online as room_type_sold_online,r.can_be_sold_online as room_sold_online');
        $db2->from('room_type as rt');
        $db2->join('room as r' , 'rt.id = r.room_type_id', 'left');
        $db2->join('room_location as rl' , 'rl.id = r.location_id', 'left');
        $db2->join('floor as f' , 'f.id = r.floor_id', 'left');
        $db2->where('rt.company_id', $company_id);
        $db2->where('r.is_deleted',0);
        $db2->where('r.is_hidden',0);
        $db2->order_by("r.room_id","asc");
        $query = $db2->get();

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

    function get_company($company_id, $filter = null)
	{
		$db2 = $this->load->database('roomsy_db', TRUE);
        if(isset($filter['get_last_action']) && $filter['get_last_action'])
        {
            $db2->select('(DATEDIFF(NOW(), IF(la.last_action IS NULL, capi.creation_date, la.last_action))) as idle',FALSE);
            $db2->join("(SELECT
                            b.company_id,
                            MAX(bl.date_time) as last_action
                        FROM
                            booking as b, booking_log as bl
                        WHERE
                            b.booking_id = bl.booking_id
                        GROUP BY b.company_id) as la "
                        , "la.company_id = c.company_id", "left");
        }
		$db2->select('c.*, capi.*, up.*, cs.subscription_level, cs.limit_feature, cs.subscription_state, cs.payment_method, cs.subscription_id, cs.balance, u.email as owner_email, p.*, count(DISTINCT r.room_id) as number_of_rooms_actual,c.partner_id,IFNULL(wp.username,"Roomsy") as partner_name, cpg.selected_payment_gateway, cpg.gateway_meta_data',FALSE);
		$db2->from('company as c');
		$db2->join('company_admin_panel_info as capi', 'c.company_id = capi.company_id', 'left');
		$db2->join('company_subscription as cs', 'c.company_id = cs.company_id', 'left');
        $db2->join('company_payment_gateways as cpg', 'cpg.company_id = c.company_id', 'left');
		$db2->join('user_permissions as up', "c.company_id = up.company_id and up.permission = 'is_owner'", 'left');
		$db2->join('room as r', "r.company_id = c.company_id AND r.is_deleted != 1", 'left');
		$db2->join('users as u', "up.user_id = u.id", 'left');
		$db2->join('whitelabel_partners wp',"c.partner_id = wp.id",'left');
		$db2->join('user_profiles as p', 'up.user_id = p.user_id', 'left');
        $db2->where('c.company_id', $company_id);
		$query = $db2->get();

		//echo $db2->last_query();
		if ($query->num_rows >= 1)
		{
			$result = $query->result_array();
			$result[0]['company_id'] = $company_id;
			return $result[0];
		}

		return NULL;
	}

	function get_common_booking_engine_fields($company_id){
		$db2 = $this->load->database('roomsy_db', TRUE);
        $db2->where('company_id', $company_id);		
        $db2->order_by('id', 'DESC');		
		$query = $db2->get('online_booking_engine_fields');
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
		$db2 = $this->load->database('roomsy_db', TRUE);
		$db2->from("users");
		$db2->from("user_permissions");
		$db2->from("user_profiles");

		if ($except_admins) {
			$db2->where('users.is_admin <> 1');
		}

		$db2->where('users.id = user_profiles.user_id');
		$db2->where('user_permissions.user_id = user_profiles.user_id');
		$db2->where('user_permissions.company_id', $company_id);
		$db2->where_in('permission', array('is_employee', 'is_manager', 'is_owner', 'is_admin', 'is_housekeeping'));
		$db2->order_by('user_permissions.user_id');

		$query = $db2->get();
		//echo $db2->last_query();
		if ($query->num_rows >= 1)
		{
			return $query->result_array();
		}

		return NULL;
	}

	// Called from Employee Settings to display all users and permissions that belong to this company
	function get_all_user_permissions($company_id, $is_permission = true)
	{
		$db2 = $this->load->database('roomsy_db', TRUE);
		$db2->from('user_permissions');

		$db2->where('company_id', $company_id);
		if($is_permission)
		$db2->where_not_in('permission', array('is_employee', 'is_manager', 'is_owner', 'is_admin'));

		$db2->order_by('user_id');

		$query = $db2->get();

		if ($query->num_rows >= 1)
		{
			return $query->result_array();
		}

		return NULL;
	}

	function get_booking_fields($company_id, $where_field = false){
		$db2 = $this->load->database('roomsy_db', TRUE);
        $db2->where('company_id', $company_id);
        $db2->where('is_deleted', 0);

        if ($where_field) {
            $db2->where($where_field, 1);
            $db2->order_by("id", "asc");
        }

        $query = $db2->get('booking_fields');

        if ($query->num_rows >= 1)
            return $query->result_array();
        return NULL;
    }

    function get_customer_fields($company_id, $show_deleted = false, $field = false)
	{
		$db2 = $this->load->database('roomsy_db', TRUE);
		$db2->where('company_id', $company_id);
		if (!$show_deleted)
			$db2->where('is_deleted', 0);
		$db2->order_by("id", "asc");

		if ($field)
            $db2->where($field, 1);
		
		$query = $db2->get('customer_fields');
		
		if ($query->num_rows >= 1) 
			return $query->result_array();
		return NULL;
	}

	function get_payment_types($company_id)
    {   
    	$db2 = $this->load->database('roomsy_db', TRUE);
        $db2->where('company_id', $company_id);
		$db2->where('is_deleted', 0);
		$db2->where('is_read_only', 0); // read_only payment types are not shown
		
		$query = $db2->get('payment_type');
		
	    if ($query->num_rows >= 1) 
		{	
			return $query->result();
		}
		
		return NULL;
    }

    function get_charge_types($company_id)
	{		
		$db2 = $this->load->database('roomsy_db', TRUE);
		$db2->where('company_id', $company_id);
		$db2->where('is_deleted', 0);
		$db2->order_by("id", "asc");
		$charge_type_query = $db2->get('charge_type');
		if ($charge_type_query->num_rows >= 1) 
			return $charge_type_query->result_array();
		return NULL;
	}

	function get_taxes($charge_type_id, $amount = null) {
		
		$db2 = $this->load->database('roomsy_db', TRUE);
		$select_tax_rate = "t.tax_rate,";
		$join_tax_brackets = "";
		// fetch tax rate from price brackets according to charge amount
		if ($amount) {
			$select_tax_rate = "IF(t.is_brackets_active, tpb.tax_rate, t.tax_rate) as tax_rate,";
			$join_tax_brackets = "LEFT JOIN tax_price_brackets as tpb 
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
		
        $query = $db2->query($sql);
				
		if ($query->num_rows() > 0)
        {
        	return $query->result_array();
        }        
    }

    function get_room_types($company_id, $adult_count = null, $children_count = null, $company_group_id = null)
	{
		$db2 = $this->load->database('roomsy_db', TRUE);
		$db2->select("rt.*, count(rp.rate_plan_id) as rate_plan_count");
		$db2->from("room_type as rt");
		$db2->join("rate_plan as rp", "rp.room_type_id = rt.id and rp.is_deleted != '1' and rp.is_selectable = '1'", "left");
		if($company_group_id)
		{
			$db2->join("company_groups_x_company as cgxc", "cgxc.company_id = rt.company_id", "left");
			$db2->where('cgxc.company_group_id', $company_group_id);
		}
		else
		{
	        $db2->where('rt.company_id', $company_id);
		}
        if($adult_count){
            $db2->where("rt.max_adults >= $adult_count");
        }
        if($children_count){
            $db2->where("rt.max_children >= $children_count");
        }
        if($adult_count || $children_count){
            $total_occupants = (int)$adult_count + (int)$children_count;
            $db2->where("rt.max_occupancy >= $total_occupants");
            $db2->where("rt.min_occupancy <= $total_occupants");
        }
        $db2->where('rt.is_deleted', 0);
        $db2->group_by("rt.id"); // needs to be in asc order, so newly added room types are in the bottom of the list
		$db2->order_by("rt.sort", "asc"); // needs to be in asc order, so newly added room types are in the bottom of the list

        $query = $db2->get();

        if ($query->num_rows >= 1) {
            return $query->result_array();
        }

        return null;
    }

    function get_customer_types($company_id, $show_deleted = false)
	{
		$db2 = $this->load->database('roomsy_db', TRUE);
		$db2->where('company_id', $company_id);
		if (!$show_deleted)
			$db2->where('is_deleted', 0);
		$db2->order_by("sort_order", "asc");
		
		$query = $db2->get('customer_types');
		
		if ($query->num_rows >= 1) 
			return $query->result_array();
		return NULL;
	}

	function get_booking_source($company_id, $show_deleted = false, $show_hidden = false)
	{
		$db2 = $this->load->database('roomsy_db', TRUE);
		$db2->where('company_id', $company_id);
		if (!$show_deleted)        
			$db2->where('is_deleted', 0);
        
        if (!$show_hidden)
            $db2->where('is_hidden', 0);
        
		$db2->order_by("sort_order, name", "asc");
		
		$query = $db2->get('booking_source');
		
		if ($query->num_rows >= 1) 
			return $query->result_array();
		return NULL;
	}

	function get_rate_plans($company_id)
	{
		$db2 = $this->load->database('roomsy_db', TRUE);
		$db2->select('
						rp.rate_plan_id, 
						r.adult_1_rate, 
						rp.rate_plan_name, 
						rp.description,
						cu.currency_code, 
						rp.charge_type_id,
						cu.currency_id, 
						rt.id as room_type_id, 
						rp.image_group_id');
		$db2->from('rate_plan as rp, room_type as rt, currency as cu');		
		$db2->join('rate as r', 'r.rate_id = rp.base_rate_id', 'left');

		$db2->where('rt.company_id', $company_id);
		$db2->where('rp.room_type_id = rt.id');
		$db2->where('IF(rp.currency_id, rp.currency_id, '.DEFAULT_CURRENCY.') = cu.currency_id');
		$db2->where('rp.is_deleted != "1"');
		$db2->where('rp.is_selectable = "1"');
		$db2->where('rt.is_deleted != "1"');
		
		
		$query = $db2->get();
		
		if ($db2->_error_message()) // error checking
					show_error($db2->_error_message());
					
		//echo $db2->last_query();
		if ($query->num_rows >= 1) 
		{
			$result =  $query->result_array();
			return $result;
		}
		return NULL;
	}

	function get_all_tax_types($company_id)
    {
    	$db2 = $this->load->database('roomsy_db', TRUE);

        $db2->select('tt.*,tt.tax_rate as taxrate,tt.tax_type_id as taxtype_id,tpb.*,tpb.tax_rate as price_bracket_rate,tpb.is_percentage as is_price_bracket_percentage,tt.is_percentage as tax_percentage');
        $db2->from('tax_type as tt');
        $db2->join('tax_price_brackets as tpb','tpb.tax_type_id = tt.tax_type_id', "left");
        $db2->where('tt.company_id',$company_id);
        $db2->where('tt.is_deleted', 0);
        $query = $db2->get();
        return $query->result_array();
    }

    function get_customer_card_details($company_id)
    {
    	$db2 = $this->load->database('roomsy_db', TRUE);

    	$sql = 
    	"SELECT 
    		c.*,
        	cd.*,
        	GROUP_CONCAT(cf.name) as custom_fields_name,
        	ct.name as customer_type_name,
        	c.customer_id as customers_id,
        	c.customer_name as name_customer,
        	cxcf.value,
            GROUP_CONCAT(IF(cxcf2.value != '', cxcf2.value, ''), ' ') as custom_fields_value,
            GROUP_CONCAT(DISTINCT cxcf2.customer_field_id, ' ') as customer_field_id
        FROM customer as c
        LEFT JOIN customer_fields as cf ON cf.company_id = c.company_id AND cf.is_deleted = 0
        LEFT JOIN customer_x_customer_field as cxcf ON cxcf.customer_field_id = cf.id AND cxcf.customer_id = c.customer_id
        LEFT JOIN customer_x_customer_field as cxcf2 ON cxcf2.customer_field_id = cf.id AND cxcf2.customer_id = c.customer_id
        LEFT JOIN customer_card_details as cd ON c.customer_id = cd.customer_id
        LEFT JOIN customer_types as ct ON c.customer_type_id = ct.id

        WHERE c.company_id = $company_id AND c.is_deleted = 0 AND ct.is_deleted = 0
        GROUP BY c.customer_id";

        $query = $db2->query($sql);

        if ($db2->_error_message()) // error checking
            show_error($db2->_error_message());

        if ($query->num_rows >= 1)
        {
            $result =  $query->result_array();
            return $result;
        }
        return NULL;
    }

    function get_all_the_charge_types($company_id)
    {
    	$db2 = $this->load->database('roomsy_db', TRUE);

    	$db2->select('ct.*,c.*,c.charge_id as chargeid');
        $db2->from('charge_type as ct');
        $db2->join('charge as c', 'c.charge_type_id = ct.id', 'left');
        $db2->where('ct.company_id', $company_id);
        $db2->where('ct.is_deleted', 0);
        $db2->where('c.is_deleted', 0);

        $query = $db2->get();

        if ($db2->_error_message()) // error checking
            show_error($db2->_error_message());
        if ($query->num_rows >= 1)
        {
            $result =  $query->result_array();
            return $result;
        }
        return NULL;
    }

    function get_charge_taxes($charge_type_ids) {
		
		$db2 = $this->load->database('roomsy_db', TRUE);
		
		$db2->select('cttl.charge_type_id,t.tax_rate,t.tax_type,t.tax_type_id,t.is_percentage,t.is_tax_inclusive');
        $db2->from('tax_type as t');
        $db2->join('charge_type_tax_list as cttl', 'cttl.tax_type_id = t.tax_type_id', 'left');
        $db2->where('t.is_deleted', 0);
        $db2->where_in('cttl.charge_type_id', $charge_type_ids);
        $db2->order_by("t.tax_type","asc");
		
        $query = $db2->get();
				
		if ($query->num_rows() > 0)
        {
        	return $query->result_array();
        }        
    }

     function is_company_exist($company_id){
     	$db2 = $this->load->database('roomsy_db', TRUE);

    	$db2->select('company_id');
        $db2->from('company');
        $db2->where('company_id', $company_id);
        $db2->where('is_deleted', 0);

        $query = $db2->get();

        if ($db2->_error_message()) // error checking
            show_error($db2->_error_message());
        if ($query->num_rows >= 1)
        {
            $result =  $query->result_array();
            return $result;
        }
        return NULL;
    }

    function get_all_rate_plans($company_id){
    	$db2 = $this->load->database('roomsy_db', TRUE);

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
                left join rate_supplied as rs on rs.rate_plan_id = rp.rate_plan_id
                left join date_range_x_rate as drxr on drxr.rate_id = r.rate_id
                left join date_range as dr on dr.date_range_id = drxr.date_range_id
              where 
              rt.company_id =  '.$company_id.' and
              rp.is_deleted = 0
	    ';

        $query = $db2->query($sql);
        
        if ($db2->_error_message()) // error checking
            show_error($db2->_error_message());

        if ($query->num_rows >= 1)
        {
            return $query->result_array();
        }
        return NULL;
    }

    function get_booking_details($company_id)
    {
    	$db2 = $this->load->database('roomsy_db', TRUE);

        $db2->select('b.*,bf.*,bxbf.*,b.booking_id as bookingid,GROUP_CONCAT(bf.name) as custom_fields_name, GROUP_CONCAT(bxbf.value) as custom_fields_value,brh.*,bxblg.booking_group_id,blg.*,r.*,rt.name as room_type_name,ct.name as charge_type_name,bs.name as booking_source_name');
        $db2->from('booking as b');
        $db2->join('booking_room_history as brh', 'b.booking_id = brh.booking_id', 'left');
        $db2->join('booking_x_booking_linked_group as bxblg', 'bxblg.booking_id = brh.booking_id', 'left');
        $db2->join('booking_linked_group as blg', 'blg.id = bxblg.booking_group_id', 'left');
        $db2->join('booking_fields as bf', 'bf.company_id = b.company_id AND bf.is_deleted = 0', 'left');
        $db2->join('booking_x_booking_field as bxbf', 'bxbf.booking_id = b.booking_id AND bf.id = bxbf.booking_field_id', 'left');
        $db2->join('room as r', 'r.room_id = brh.room_id', 'left');
        $db2->join('room_type as rt', 'rt.id = brh.room_type_id', 'left');
        $db2->join('charge_type as ct', 'ct.id = b.charge_type_id', 'left');
        $db2->join('booking_source as bs', 'bs.id = b.source', 'left');
        $db2->where('b.is_deleted', 0 );
        $db2->where('b.company_id', $company_id);
        $db2->group_by('brh.booking_room_history_id');
        $db2->group_by('b.booking_id');

        $query = $db2->get();

        if ($db2->_error_message()) // error checking
            show_error($db2->_error_message());

        if ($query->num_rows >= 1)
        {
            $result =  $query->result_array();
            return $result;
        }
        return NULL;
    }

    function get_all_extras($company_id)
    {
    	$db2 = $this->load->database('roomsy_db', TRUE);

        $db2->select('e.*,er.*,ct.*,e.extra_id as extraid,bxe.booking_id,bxe.booking_extra_id,bxe.start_date,bxe.end_date,bxe.rate as defaultrate,bxe.quantity');
        $db2->from('extra as e');
        $db2->join('extra_rate as er','e.extra_id = er.extra_id','left');
        $db2->join('booking_x_extra as bxe',"bxe.extra_id = e.extra_id", 'left');
        $db2->join('charge_type as ct',"ct.id = e.charge_type_id", 'left');
        $db2->where('e.company_id', $company_id);
        $db2->where('e.is_deleted', 0);
        
        $query = $db2->get();

        if ($query->num_rows >= 1)
        {
            return $result = $query->result_array();
        }

        return NULL;
    }

    function get_payment_details($company_id)
    {
    	$db2 = $this->load->database('roomsy_db', TRUE);

        $db2->from('payment_type as pt');
        $db2->join('payment as p', 'p.payment_type_id = pt.payment_type_id', 'left');
        $db2->where('pt.company_id', $company_id);
        $db2->where('pt.is_deleted', 0);
        $db2->where('p.is_deleted', 0);
        $query = $db2->get();

        if ($db2->_error_message()) // error checking
            show_error($db2->_error_message());
        
        if ($query->num_rows >= 1)
        {
            $result =  $query->result_array();
            return $result;
        }
        return NULL;
    }

    function get_statement($company_id)
    {
    	$db2 = $this->load->database('roomsy_db', TRUE);

        $db2->select('b.booking_id,bxs.*,s.*');
        $db2->from('booking_x_statement as bxs');
        $db2->join('booking as b', "bxs.booking_id = b.booking_id", "left");
        $db2->join('statements as s', "bxs.statement_id = s.statement_id", "left");
        $db2->where('b.company_id', $company_id);
        $db2->where('s.is_deleted', 0);
        $db2->where('b.is_deleted', 0);

        $query = $db2->get();

        if ($query->num_rows >= 1) {
            $result = $query->result_array();
            return $result;
        }
        return null;
    }

    function get_booking_staying_customers($company_id)
    {
    	$db2 = $this->load->database('roomsy_db', TRUE);

    	$db2->select('booking_id');
        $db2->from('booking_staying_customer_list');
        $db2->where('company_id', $company_id);

        $query = $db2->get();
        $result = $query->result_array();

        $booking_ids = array();

        foreach ($result as $key => $value) {
        	$booking_ids[] = $value['booking_id'];
        }

    	
        $db2->select('booking_staying_customer_list.customer_id');
        $db2->select('booking_staying_customer_list.booking_id');
        $db2->select('customer_name');
        $db2->from('booking_staying_customer_list');
        $db2->join('customer', 'customer.customer_id = booking_staying_customer_list.customer_id', 'left');
        $db2->where_in('booking_staying_customer_list.booking_id', $booking_ids);
        $db2->where('booking_staying_customer_list.company_id', $company_id);

        $results = $db2->get();
        
        if (empty($results))
        {
            return array();
        }
        else
        {
            return $results->result_array();
        }
    }
}