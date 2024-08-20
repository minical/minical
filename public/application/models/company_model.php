<?php

class Company_model extends CI_Model {

	function __construct()
	{
		// Call the Model constructor
		parent::__construct();
	}

	function create_company($data)
	{
		$data = (object) $data;
		$this->db->insert("company", $data);

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
    
    function get_all_companies($is_ota_connected = false, $ota_key = null)
	{
		$subscription_state = array('trialing','active');

		$this->db->select('c.company_id, c.name');
		$this->db->from('company as c');
		$this->db->join('company_subscription as cs','cs.company_id = c.company_id','left');

		if($is_ota_connected){
			$this->db->join('ota_x_company as oxc','oxc.company_id=c.company_id','left');
			$this->db->where('oxc.ota_manager_id IS NOT NULL');

			if($ota_key){
	            $this->db->join('ota_manager as om','om.id = oxc.ota_manager_id', 'left');
	            $this->db->join('otas as o','o.id = om.ota_id', 'left');
	            $this->db->where('o.key', $ota_key);
	        }
		}

		$this->db->where('c.is_deleted', 0);
		$this->db->where_in('cs.subscription_state', $subscription_state);
		$this->db->group_by('c.company_id');
        
		$query = $this->db->get();
        
		if($query->num_rows() >= 1)
		{
			return $query->result_array();
		}
        
		return NULL;
	}

function get_total_companies($extension_name = null, $is_extension_active = false){

		$this->db->select('c.company_id, c.name');
		$this->db->from('company as c');

		if($is_extension_active){
			$this->db->join('extensions_x_company as exc','exc.company_id=c.company_id','left');
			$this->db->where('exc.extension_name', $extension_name);
			$this->db->where('exc.is_active', 1);
		}
		
		$this->db->where('c.is_deleted', 0);
		$this->db->group_by('c.company_id');
        
		$query = $this->db->get();
        
		if($query->num_rows() >= 1)
		{
			return $query->result_array();
		}
        
		return NULL;

	}
	
	function get_ota_x_company_data($company_ids)
	{
		$this->db->from('ota_x_company as oxc');
		$this->db->where_in('oxc.company_id', $company_ids);
        
		$query = $this->db->get();
        
		if($query->num_rows() >= 1)
		{
			return $query->result_array();
		}
        
		return NULL;
	}

	function set_pricing_mode_rate_plan($data){

		$update_data = array('rate_update_type' => $data['rate_update_type']);

		$this->db->where('company_id', $data['company_id']);
		$this->db->where('ota_x_company_id', $data['ota_x_company_id']);
		$this->db->update("ota_rate_plans", $update_data);

		$this->db->where('company_id', $data['company_id']);
		$this->db->where('ota_x_company_id', $data['ota_x_company_id']);
		$this->db->update("ota_x_company", array('rate_update_type' => null));
	}
    
    function get_data_of_companies()
    {
        $sql = $this->db->query("SELECT * FROM `company` WHERE logo_image_group_id IS NULL");
        
        return $sql->result_array();
    }


    //Returns array of objects where each object represents a row from query
	function get_companies($user_id, $subscription_states = null)
	{
        $company_subscription_join = $where = '';
        $companies = array();
        if($subscription_states)
        {
            $company_subscription_join = 'LEFT JOIN company_subscription as cs ON cs.company_id = c.company_id';
            $subscription_state_sql = array();
            foreach($subscription_states as $subscription_state){
                $subscription_state_sql[] = 'cs.subscription_state = "'.$subscription_state.'"';
            }
            $where = 'AND ('.implode(" OR ", $subscription_state_sql).')';
        }
        else
        {
            $this->db->select('c.name, c.company_id, c.country');
            $this->db->from('company as c, user_permissions as up');
            $this->db->where('up.user_id', $user_id);
            $this->db->where('c.company_id = up.company_id');
            $this->db->where('c.is_deleted','0');
            $this->db->where_in('up.permission', array('is_employee', 'is_manager', 'is_owner', 'is_admin', 'is_housekeeping'));
            $this->db->group_by('c.company_id');

            $query = $this->db->get();

            if($query->num_rows() >= 1)
            {
                foreach($query->result_array() as $company)
                {
                    $companies[$company['company_id']] = $company;
                }
            }
        }
        
        // also fetch whitelabel partner's companies if current user is a whitelabel partner 
        $sql = "SELECT 
                    c.name, c.company_id, c.country
                FROM company as c
                $company_subscription_join
                LEFT JOIN 
                    user_permissions as up ON up.company_id = c.company_id
                LEFT JOIN 
                    whitelabel_partner as wp on wp.id = c.partner_id
                LEFT JOIN 
                    whitelabel_partner_x_admin as wpxa on wp.id = wpxa.partner_id
                WHERE 
                    c.is_deleted = '0' AND
                    up.permission = 'is_owner' AND
                    wpxa.admin_id = '$user_id'
                    $where
                GROUP BY c.company_id";
        
        $query = $this->db->query($sql);
        
		if($query->num_rows() >= 1)
		{
            foreach($query->result_array() as $company)
            {
                $companies[$company['company_id']] = $company;
            }
		}
        return $companies;
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
		$this->db->select('c.*, capi.*, up.*, cs.subscription_level, cs.limit_feature, cs.subscription_state, cs.payment_method, cs.subscription_id, cs.balance, u.email as owner_email, p.*, count(DISTINCT r.room_id) as number_of_rooms_actual,c.partner_id,IFNULL(wp.username,"Minical") as partner_name, cpg.selected_payment_gateway',FALSE);
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

	function update_company($company_id, $data)
	{
		$data = (object) $data;
		$this->db->where('company_id', $company_id);
		$this->db->update("company", $data);
	}
    
	function update_company_admin_panel_info($company_id, $data)
	{
		$data = (object) $data;
		$this->db->where('company_id', $company_id);
		$this->db->update("company_admin_panel_info", $data);
	}

	// increment selling_date by 1 (Called from night audit controller)
	function increment_selling_date_by_one($company_id) {
		$SQL = "UPDATE company SET selling_date = DATE_ADD(selling_date, INTERVAL 1 DAY) WHERE company_id = '$company_id'";
		$this->db->query($SQL);
	}

	// decrement selling_date by 1 (Called from night audit controller)
	function decrement_selling_date_by_one($company_id) {
		$SQL = "UPDATE company SET selling_date = DATE_SUB(selling_date, INTERVAL 1 DAY) WHERE company_id = '$company_id'";
		$this->db->query($SQL);
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

	//return current company's set time zone
	//* eventually we want to add company_id to parameter (Jaeyun 12-15-2011)
	function get_time_zone($company_id)
	{
		$this->db->where('company_id', $company_id);
		$this->db->select('time_zone');
		$query = $this->db->get('company');

		if ($query->num_rows >= 1)
		{
			$result = $query->result_array();
			return $result[0]['time_zone'];
		}

		return NULL;
	}

	function is_rate_including_tax($company_id) {
		$is_rate_including_tax = null;
		$this->db->select("is_rate_including_tax");
		$this->db->where('company_id', $company_id);
		$q = $this->db->get("company");
		foreach ($q->result() as $row) {
			$is_rate_including_tax = $row->is_rate_including_tax;
		}
		//echo $this->db->last_query();
		return $is_rate_including_tax;
	}

	// called from monthly summary report
	// returns number of company's rooms 
	function get_number_of_rooms($company_id) {
		$number_of_rooms = null;

		$this->db->where('company_id', $company_id);
		$this->db->select('number_of_rooms');
		$q = $this->db->get('company');

		foreach ($q->result() as $row) {
			$number_of_rooms = $row->number_of_rooms;
		}
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		return $number_of_rooms;
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
		$this->db->where_not_in('permission', array('is_employee', 'is_manager', 'is_owner', 'is_admin'));

		$this->db->order_by('user_id');

		$query = $this->db->get();

		if ($query->num_rows >= 1)
		{
			return $query->result_array();
		}

		return NULL;
	}

	// returns company's housekeeping setting information - called from Housekeeping settings
	function get_housekeeping_settings($company_id, $is_room_dirty = false) {
		if($is_room_dirty)
		{
			$this->db->select("housekeeping_auto_dirty_is_enabled");
			$this->db->select("housekeeping_auto_dirty_time");
		}
		else
		{
			$this->db->select("housekeeping_auto_clean_is_enabled");
			$this->db->select("housekeeping_auto_clean_time");
		}
		$this->db->select("housekeeping_day_interval_for_full_cleaning");

		$this->db->where('company_id', $company_id);

		$q = $this->db->get("company");

		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		$result = $q->result();

		if (isset( $result[0]))
		{
			return $result[0]; // log out
		}
		return null;
	}


	function get_policies($company_id) {
		$this->db->select("reservation_policies");
		$this->db->select("check_in_policies");
		$this->db->where('company_id', $company_id);

		$q = $this->db->get("company");

		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		$result = $q->result();
		return $result[0];
	}

	function get_check_in_policies($company_id) {
		$this->db->select("check_in_policies");
		$this->db->where('company_id', $company_id);

		$q = $this->db->get("company");

		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		$result = $q->result();
		return $result[0]->check_in_policies;
	}


	function get_reservation_policies($company_id) {
		$this->db->select("reservation_policies");
		$this->db->where('company_id', $company_id);

		$q = $this->db->get("company");

		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		$result = $q->result();
		return $result[0]->reservation_policies;
	}

	function company_contains_user($company_id, $user_id) {
		$this->db->select('user_id');
		$this->db->where('company_id', $company_id);
		$this->db->where('user_id', $user_id);
		$query = $this->db->get('user_permissions');
		if($query->num_rows > 0) {
			return true;
		}
		return false;
	}

	/* ######################### OTA Stuff ############################*/


	function get_expedia_account($company_id) {
		$this->db->select("username, password");
		$this->db->where('company_id', $company_id);

		$q = $this->db->get("OTA_expedia_accounts");

		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		$result = $q->result();
		$account = Array();

		if (isset($result[0]->username) && isset($result[0]->password))
		{
			$account['username'] = $result[0]->username;
			$account['password'] = $result[0]->password;
			return $account;
		}

		return null;

	}
	/* ************************************************************************** */
	// obtain last IP that accessed the company under "owner" permission
	function get_last_IP($company_id) {
		$this->db->select("u.last_ip");
		$this->db->where("c.company_id", $company_id);
		$this->db->where("up.company_id = c.company_id");
		$this->db->where("up.permission = 'is_owner'");
		$this->db->where("up.user_id = u.id");
		$q = $this->db->get("company as c, user_permissions as up, users as u");

		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		$result = $q->result();
		return $result[0]->last_ip;
	}


	function get_housekeeping_day_interval_for_full_cleaning($company_id) {

		$this->db->select("housekeeping_day_interval_for_full_cleaning");
		$this->db->where('company_id', $company_id);

		$q = $this->db->get("company");

		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		$housekeeping_day_interval_for_full_cleaning = '';

		foreach ($q->result() as $row) {
			$housekeeping_day_interval_for_full_cleaning = $row->housekeeping_day_interval_for_full_cleaning;
		}
		//echo $this->db->last_query();
		return $housekeeping_day_interval_for_full_cleaning;
	}

	function get_companies_that_signed_up_within_last_month($today)
	{
		$month_ago = Date("Y-m-d", strtotime("-3  months", strtotime($today)));
		$this->db->select("c.company_id, c.selling_date, capi.creation_date, c.name, ut.last_login");
		$this->db->from("company as c, company_admin_panel_info as capi");
		$this->db->from('
			(
				SELECT up.company_id, MAX(u.last_login) as last_login
				FROM 
					users as u,
					user_permissions as up
				WHERE 
					u.id = up.user_id AND
					u.email != "jaeyun@minical.io"
				GROUP BY up.company_id
			) as ut
			');
		$this->db->where("c.company_id = capi.company_id");
		$this->db->where("c.company_id = ut.company_id");
		$this->db->where("capi.creation_date > '$month_ago'");

		$query = $this->db->get();
		//echo $this->db->last_query();
		if ($query->num_rows >= 1)
		{
			return $query->result_array();
		}

		return NULL;
	}

	function get_cron_email_log($company_id)
	{
		$this->db->select('email_type');
		$this->db->from('cron_email_log');
		$this->db->where('company_id', $company_id);

		$query = $this->db->get();

		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		$result_array = $query->result_array();
		$result = array();
		foreach($result_array as $r => $email_type)
		{
			$result[] = $email_type['email_type'];
		}
		return $result;
	}

	function insert_cron_email_log($company_id, $email_type, $email, $date_sent)
	{
		$data = array (
			'company_id' => $company_id,
			'email_to' => $email,
			'email_type' => $email_type,
			'date_sent' => $date_sent
		);

		$this->db->insert('cron_email_log', $data);

		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

	}

	function get_auto_housekeeping_enabled_companies($is_room_dirty = false, $company_batch = null)
	{
		$this->db->distinct();
		$this->db->group_by('c.company_id');

		$this->db->select('c.company_id');
		$this->db->select('c.name as company_name');
		if($is_room_dirty)
			$this->db->select('c.housekeeping_auto_dirty_time');
		else
			$this->db->select('c.housekeeping_auto_clean_time');
		$this->db->select('c.time_zone');
		$this->db->select('c.selling_date');
		$this->db->from('company as c');
		$this->db->join( 'company_x_tag as cxt',"cxt.company_id = c.company_id and cxt.tag = 'DISABLED'", 'left');
		$this->db->join( 'company_subscription as cs',"cs.company_id = c.company_id", 'left');
		
		if($is_room_dirty)
			$this->db->where("c.housekeeping_auto_dirty_is_enabled = '1'");
		else
			$this->db->where("c.housekeeping_auto_clean_is_enabled = '1'");
		$this->db->where("cxt.tag IS NULL");
		
		$this->db->where("c.is_deleted = 0");
		$this->db->where("cs.subscription_state != 'canceled'");
		$this->db->where("cs.subscription_state != 'trial_ended'");
		$this->db->where("cs.subscription_state != 'deleted'");
		
		if($company_batch && count($company_batch) > 0) {
			$this->db->where_in("c.company_id", $company_batch);
		}
		
		$query = $this->db->get();
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		return $query->result_array();

	}
	/*

	function get_active_companies()
	{
		$this->db->select('c.company_id');
		$this->db->select('c.name as company_name');
		$this->db->from('company as c');
		$this->db->from('company_x_tag as cxt');
		$this->db->where('c.company_id = cxt.company_id');
		$this->db->where("cxt.tag != 'DISABLED'");
		
		$query = $this->db->get();
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		return $query->result_array();
	}
	*/

	function get_company_last_logins()
	{

		$this->db->select('company_id');
		$this->db->select('MAX(u.last_login) as last_login');
		$this->db->from('user_permissions as up');
		$this->db->from('users as u');
		$this->db->where('u.id = up.user_id');
		$this->db->where("u.email != 'jaeyun@minical.io'");
		$this->db->group_by("up.company_id");

		$query = $this->db->get();

		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		return $query->result_array();
	}

	//  get email of users belong to company for cronjob ( Email Invoice )
	function get_company_users($company_id, $selling_date)
	{
		$sql = "SELECT
				  b.invoice_hash,
				  c.email
				FROM booking AS b,
				  customer AS c,
				  booking_block AS brh
				WHERE b.company_id = ".$company_id."
				    AND b.booking_customer_id = c.customer_id
				    AND brh.booking_id = b.booking_id
				    AND brh.check_out_date = '".$selling_date."'";
		$q = $this->db->query($sql);

		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}

		$result = $q->result();

		return $result;
	}

	function get_company_id_by_booking_id($booking_id)
	{
		$this->db->select('company_id');
		$this->db->from('booking as b');
		$this->db->where('b.booking_id', $booking_id);

		$query = $this->db->get();
		//echo $this->db->last_query();

		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}

		$q = $query->row_array(0);

		return $q['company_id'];

	}

	function has_tag($company_id, $tag)
	{
		$this->db->select('company_id');
		$this->db->where('tag', $tag);
		$this->db->where('company_id', $company_id);
		$query = $this->db->get('company_x_tag');

		if(isset($query->num_rows))
		{
			if($query->num_rows > 0) {
				return 1;
			}
		}
		return 0;
	}

	function add_tag($company_id, $tag)
	{
		$sql ="
			INSERT IGNORE INTO company_x_tag SELECT '$company_id', '$tag'
		";

		$query = $this->db->query($sql);
		//echo $this->db->last_query();

		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
	}

	// returns true if there's another hotel than the current_company_id that's currently using $uri
	function uri_exists($uri, $current_company_id)
	{
		$this->db->select('company_id');
		$this->db->where('website_uri', $uri);
		$this->db->where("company_id != '$current_company_id'");
		$query = $this->db->get('company');

		if(isset($query->num_rows))
		{
			if($query->num_rows > 0) {
				return TRUE;
			}
		}
		return FALSE;
	}

	// use only absolutely necessary!
	// after migrating all users from v1 to v2, 
	// stop deleting from users table (but delete user permissions), as users can be assigned to different properties
	function delete_company($company_id)
	{

		$queries = array(
			"
				DELETE u, up, ua, upro
				FROM  
					user_permissions as up
				LEFT JOIN users as u ON u.id = up.user_id AND u.email != 'support@minical.io'
				LEFT JOIN user_autologin as ua ON u.id = ua.user_id
				LEFT JOIN user_profiles as upro ON u.id = upro.user_id
				WHERE  
					up.company_id = $company_id
			",
			"
				DELETE
					rt, 
					r,
					rp,
					dr,
					drxr
				FROM  
					room_type as rt
				LEFT JOIN rate_plan as rp ON rt.id = rp.room_type_id
				LEFT JOIN rate as r ON r.rate_plan_id = rp.rate_plan_id
				LEFT JOIN date_range_x_rate as drxr ON r.rate_id = drxr.rate_id
				LEFT JOIN date_range as dr ON dr.date_range_id = drxr.date_range_id
				WHERE
					rt.company_id = $company_id
			",
			"
				DELETE
					bxe, 
					bxi,
					i,
					b,
					brh,
					bl,
					c,
					p,
					bscl
				FROM 
					booking as b
				LEFT JOIN booking_block as brh ON b.booking_id = brh.booking_id
				LEFT JOIN booking_staying_customer_list as bscl ON b.booking_id = bscl.booking_id
				LEFT JOIN charge as c ON c.booking_id = b.booking_id
				LEFT JOIN payment as p ON p.booking_id = b.booking_id	
				LEFT JOIN booking_x_extra as bxe ON bxe.booking_id = b.booking_id
				LEFT JOIN booking_x_invoice as bxi ON bxi.booking_id = b.booking_id
				LEFT JOIN invoice as i ON i.invoice_id = bxi.invoice_id
				LEFT JOIN booking_log as bl ON b.booking_id = bl.booking_id
					
				WHERE 
					b.company_id = $company_id;
			",
			"
				DELETE 
					e, 
					er
				FROM 
					extra as e
				LEFT JOIN extra_rate as er ON e.extra_id = er.extra_id
				LEFT JOIN date_range_x_extra_rate as drxer ON drxer.extra_rate_id = er.extra_rate_id
				LEFT JOIN date_range as dr ON dr.date_range_id = drxer.date_range_id
				WHERE 
					e.company_id = $company_id;
			",
			"
				DELETE FROM customer
				WHERE 
					company_id = $company_id;
			",
			"
				DELETE FROM room 
				WHERE
					company_id = $company_id;
			",
			"
				DELETE 
					rt,
					drxrt,
					drxrxc,
					dr
				FROM 
					room_type as rt
				LEFT JOIN date_range_x_room_type AS drxrt ON rt.id = drxrt.room_type_id
				LEFT JOIN date_range as dr ON dr.date_range_id = drxrt.date_range_id
				LEFT JOIN date_range_x_room_type_x_channel AS drxrxc ON drxrxc.date_range_x_room_type_id = drxrt.date_range_x_room_type_id
				WHERE 
					rt.company_id = '$company_id';
			",
			"
				DELETE FROM payment_type WHERE company_id = '$company_id';
			",
			"
				DELETE FROM tax_type WHERE company_id = $company_id;
			",
			"
				DELETE 
					charge_type,
					charge_type_tax_list,
					tax_type
				FROM   
					charge_type
				LEFT JOIN charge_type_tax_list ON charge_type.id = charge_type_tax_list.charge_type_id
				LEFT JOIN tax_type ON tax_type.tax_type_id = charge_type_tax_list.tax_type_id
				WHERE  charge_type.company_id = $company_id;
			",
			"
				DELETE 
				FROM   payment_type
				WHERE
					company_id = $company_id;
			",
			"
				DELETE FROM company WHERE company_id = $company_id;				
			",
			"
				DELETE FROM company_subscription WHERE company_id = $company_id;				
			",
			"
				DELETE FROM company_admin_panel_info WHERE company_id = $company_id;				
			"
		);

		foreach ($queries as $query) {
			$this->db->query($query);

			if ($this->db->_error_message())
				show_error($this->db->_error_message());
		}


	}

	function get_company_country_code($country_name){
		$this->db->select('countries.country_code');
		$this->db->from('company');
		$this->db->join("countries","company.country = countries.name");
		$this->db->where('company.country',$country_name);
		$this->db->limit(1);
		$query = $this->db->get();
		if ($query->num_rows >= 1)
		{
			$result = $query->result_array();
			return   $result[0]['country_code'];
		}
		return NULL;
	}

	function get_rate_plans_grouped_by_room_type($company_id)
	{
		$this->db->select("rt.id as room_type_id, rt.name as room_type_name,rt.max_adults as room_type_max_occupency, rp.rate_plan_id, rp.rate_plan_name");
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
                $result_array[$row['room_type_id']]["room_type_max_occupency"] = $row['room_type_max_occupency'];
				$result_array[$row['room_type_id']]["rate_plans"][] = array(
					"rate_plan_id" => $row['rate_plan_id'],
					"rate_plan_name" => $row['rate_plan_name']
				);
			}
			return $result_array;
		}
		return NULL;
	}
	
	function get_company_groups($company_group_id = null)
	{
		$where = "";
		if($company_group_id)
		{
			$where = " AND cg.id = $company_group_id ";
		}
		$sql = "SELECT * FROM company_groups as cg
				WHERE is_deleted = 0 $where GROUP BY cg.id ORDER BY cg.id";
		
		$query = $this->db->query($sql);
		$company_groups = $query->result_array();
		
		$sql1 = "SELECT c.company_id,
						c.name, 
						c.city, 
						c.region, 
						c.selling_date,
						c.booking_engine_booking_status,
						c.allow_free_bookings,
						cgxc.* FROM company_groups_x_company as cgxc 
				LEFT JOIN company as c ON cgxc.company_id = c.company_id";
		
		$query1 = $this->db->query($sql1);
		$company_groups_x_company = $query1->result_array();
		
		foreach($company_groups as $key => $groups)
		{
			foreach($company_groups_x_company as $groups_x_company)
			{
				if($groups['id'] == $groups_x_company['company_group_id'])
				{
					$company_groups[$key]['companies'][$groups_x_company['company_id']] = $groups_x_company;
				}
			}
		}
		
		return count($company_groups) > 0 ? $company_groups : NULL;
	}

	function get_all_review_links($company_id)
	{
		$this->db->from('review_management');
		$this->db->where('company_id', $company_id);
		$query = $this->db->get();
        
		if($query->num_rows() >= 1)
		{
			return $query->result_array();
		}
        
		return NULL;
	}

	function create_review_links($data)
	{
		$data = (object) $data;
		$this->db->insert("review_management", $data);

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

	function update_review_links($company_id, $ota_id, $data)
	{
		$data = (object) $data;
		$this->db->where('company_id', $company_id);
		$this->db->where('ota_id', $ota_id);
		$this->db->update("review_management", $data);
	}

    function get_subscription_restriction($company_subscription_level, $controller_name, $function_name)
    {
        $this->db->from('subscription_restriction');
        $this->db->where('subscription_plan', $company_subscription_level);
        $this->db->where('controller', $controller_name);
        $this->db->where('function', $function_name);
        $query = $this->db->get();
        
        if($query->num_rows() >= 1)
        {
            return $query->result_array();
        }
        
        return NULL;
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

    function insert_company_api_key($company_id, $key)
    {
        $sql = "SELECT kxc.* FROM key_x_company as kxc
                WHERE kxc.company_id = '$company_id' ";

        $que = $this->db->query($sql);
        $row = $que->row_array();

        $result = array();
        if ($row && $row['key_id']) {
            $id = $row['key_id'];
            $sql = "SELECT k.* FROM `key` as k
	                WHERE k.id = $id ";

            $query = $this->db->query($sql);
            $result = $query->row_array();
        }

        if ($result && count($result) > 0) {
            return $result;
        } else {
            $keys_data = array(
                'key' => $key,
                'level' => 10,
                'date_created' => date("Y-m-d")
            );
            $this->db->insert("key", $keys_data);

            $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
            $result = $query->result_array();
            if (isset($result[0])) {
                $last_id = $result[0]['last_id'];

                $this->db->insert("key_x_company", array('key_id' => $last_id, 'company_id' => $company_id));
                return array('key_id' => $last_id, 'key' => $key);
            } else {
                return null;
            }
        }
    }

	function update_common_booking_engine_fields($company_id, $booking_engine_field_id, $data)
    {
        $this->db->where('id', $booking_engine_field_id);
        $this->db->where('company_id', $company_id);
		$this->db->delete('online_booking_engine_field');
        
        return $this->create_common_booking_engine_fields($data);
    }
    
    function create_common_booking_engine_fields($data){
        $this->db->insert('online_booking_engine_field', $data);		
		return $this->db->insert_id();
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

    function get_property_build($property_id){
        $sql = "SELECT * FROM property_build as pb
                WHERE pb.property_id = '$property_id' ";
     	$que = $this->db->query($sql);

          return $que->row_array();
    } 

    function get_company_detail($company_id){
    	$this->db->where('company_id', $company_id);
		$query = $this->db->get('company');		
		
		if ($query->num_rows >= 1)
		{
			$result = $query->result_array();
			return $result[0];
		}
		
		return NULL;
    }

    function get_company_data($company_ids){
    	$this->db->select('company_id, name');
		$this->db->from('company');

		$this->db->where_in('company_id', $company_ids);
		
		$query = $this->db->get();
		
		if ($query->num_rows >= 1)
		{
			return $result = $query->result_array();
		}
		
		return NULL;
    }

    function get_partner_company_data($user_id){
    	$this->db->select('company_id, name');
		$this->db->from('company');

		$this->db->where('partner_id', $user_id);
		$this->db->where('is_deleted', 0);
		
		$query = $this->db->get();
		
		if ($query->num_rows >= 1)
		{
			return $result = $query->result_array();
		}
		
		return NULL;
    }

    function get_companies_by_state($state){
    	$sql = "SELECT c.company_id, c.partner_id, cs.subscription_id, cs.expiration_date FROM company AS c 
    	INNER JOIN company_subscription AS cs ON c.company_id = cs.company_id 
    	WHERE c.is_deleted = 0 AND c.partner_id = 21 AND cs.subscription_state = '$state'";
		$q = $this->db->query($sql);

		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}

		$result = $q->result_array();

		return $result;
    }
}


