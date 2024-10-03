<?php

class User_model extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    function validate($email, $password)
    {
        $this->db->where('email', $email);
        $this->db->where('password', $password);
        $this->db->where('verified', 1);
        $query = $this->db->get('user');

        if($query->num_rows == 1)
        {
            return true;
        }
        return false;
    }

    function validate_employee_account($email)
    {
        $update_data = array (
            'password' => $this->input->post('password'),
            'verified' => 1
        );
        $this->db->where('email', $email);
        $this->db->update('user', $update_data);
        return true;
    }

    function create_user($first_name, $last_name, $email, $password, $company_id)
    {
        //create primary account
        $new_member_insert_data = array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'password' => $password,
            'company_id' => $company_id,
            'verified' => 1
        );
        $insert = $this->db->insert('user', $new_member_insert_data);

        //get newly created uid
        $this->db->select('user_id');
        $this->db->where('email', $email);
        $query = $this->db->get('user');

        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());

        foreach ($query->result() as $row)
        {
            $user_id = $row->user_id;
        }

        return $user_id;
    }

    function add_user_permission($company_id, $user_id, $permission, $add_default_permissions = false)
    {
        $user_permissions_data = array(
            'company_id' => $company_id,
            'user_id' => $user_id,
            'permission' => $permission
        );
        $query = $this->db->insert('user_permissions', $user_permissions_data);

        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());
		
		if($add_default_permissions) {
			// add other default permissions
			$default_permissions = array(
				array("permission" => "access_to_bookings",		"company_id" => $company_id, "user_id" => $user_id),
				array("permission" => "access_to_customers",	"company_id" => $company_id, "user_id" => $user_id),
				array("permission" => "access_to_rooms",		"company_id" => $company_id, "user_id" => $user_id),
				array("permission" => "can_view_reports",		"company_id" => $company_id, "user_id" => $user_id),
				array("permission" => "access_to_ledger_reports","company_id" => $company_id, "user_id" => $user_id),
                array("permission" => "can_edit_invoices",      "company_id" => $company_id, "user_id" => $user_id),
				array("permission" => "can_post_charges_payments",		"company_id" => $company_id, "user_id" => $user_id),
                array("permission" => "can_modify_charges",		"company_id" => $company_id, "user_id" => $user_id),
                array("permission" => "can_modify_charges",     "company_id" => $company_id, "user_id" => $user_id),
				array("permission" => "access_to_extensions",	"company_id" => $company_id, "user_id" => $user_id)
			);
			$this->db->insert_batch('user_permissions', $default_permissions);
		}
        return true;
    }

    function add_teams($company_id, $user_id, $permission){

        if(is_array($permission)){
            $permission_data = array();
            foreach ($permission as $key => $value) {
                $permission_data[] = array(
                    'company_id' => $company_id,
                    'user_id' => $user_id,
                    'permission' => $value
                );
            }

            $this->db->insert_batch('user_permissions', $permission_data);
        }else{
            $user_permissions_data = array(
                'company_id' => $company_id,
                'user_id' => $user_id,
                'permission' => $permission
            );
            $query = $this->db->insert('user_permissions', $user_permissions_data);
        }


        return true;

    }

    function get_company_id($user_id){
        $this->db->select()->from('user_permissions')->where('user_id',$user_id);
        return $this->db->get()->row();
    }

    // function get_latest_company_id($user_id){
    //     $this->db->select()->from('user_permissions')->where('user_id',$user_id)->order_by('company_id', 'desc');
    //     return $this->db->get()->row();
    // }

    function get_latest_company_id($user_id){

        $this->db->select('user_permissions.*');
        $this->db->from('user_permissions');
        $this->db->join('company', 'user_permissions.company_id = company.company_id', 'left');
        $this->db->where('user_permissions.user_id', $user_id);
        $this->db->where('company.is_deleted', 0);
        $this->db->order_by('user_permissions.company_id', 'desc');
        $query = $this->db->get();

        // $this->db->select()->from('user_permissions')->where('user_id',$user_id)->order_by('company_id', 'desc');
        return $query->row();
    }

    function remove_user_permission($company_id, $user_id, $permission)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('company_id', $company_id);
        $this->db->where('permission', $permission);

        $this->db->delete('user_permissions');

        //TODO: report error on failure
        return true;
    }
    
    function remove_other_permissions($company_id, $user_id, $permission)
    {
        $this->db->query("DELETE FROM `user_permissions`
                        WHERE `user_id` =  '$user_id'
                        AND `company_id` =  '$company_id'
                        AND (`permission` =  'access_to_bookings'
                        OR `permission` =  'can_edit_invoices'
                        OR `permission` =  'can_post_charges_payments'
                        OR `permission` =  'can_modify_charges'
                        OR `permission` =  'can_delete_bookings'
                        OR `permission` =  'can_delete_payments')");        
        //TODO: report error on failure
        return true;
    }

    function remove_all_user_permissions($company_id, $user_id)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('company_id', $company_id);

        $this->db->delete('user_permissions');

        //TODO: report error on failure
        return true;
    }
    /**
     * Get user role
     * ie) owner, employee
     *
     * @param	int
     * @return	string
     */
    function get_user_role($user_id, $company_id) {
        $role = null;
        $this->db->where('user_id', $user_id);
        $this->db->where('company_id', $company_id);
        $this->db->where_in('permission', array('is_employee', 'is_manager', 'is_owner', 'is_admin', 'is_housekeeping'));

        $query = $this->db->get('user_permissions');

        if ($query->num_rows >= 1)
        {
            $results = $query->result_array();
            $role = $results[0]['permission']; //roles are stored in the same column as permission
        }
        
        // company's whitelabel partner will have 'is_admin' permissions, this permission is not stored in user_permissions table 
        $sql = "SELECT 
                    'is_admin' as permission
                FROM user_permissions as up
                LEFT JOIN 
                    company as c on c.company_id = up.company_id
                LEFT JOIN 
                    whitelabel_partner as wp on wp.id = c.partner_id
                LEFT JOIN 
                    whitelabel_partner_x_admin as wpxa on wp.id = wpxa.partner_id
                WHERE 
                    up.permission = 'is_owner' AND
					up.company_id = '$company_id' AND
                    (wpxa.admin_id = '$user_id' OR '".SUPER_ADMIN_USER_ID."' = '$user_id')
                GROUP BY up.company_id";
        
        $query = $this->db->query($sql);
		if($query->num_rows() >= 1)
		{
            $results = $query->result_array();
            $role = $results[0]['permission'];
		}
		
        return $role;
    }

    function get_user_by_email($email)
    {
        $this->db->from('users');
        $this->db->where('LOWER(email)', $email);
        $query = $this->db->get();

        //echo $this->db->last_query();
        if ($query->num_rows >= 1)
        {
            $results = $query->result_array();
            return $results[0]; //roles are stored in the same column as permission
        }
        return false;
    }

    function user_exists_in_company($email, $company_id)
    {
        $this->db->from('users as u, user_permissions as up');
        $this->db->where('u.id = up.user_id');
        $this->db->where('LOWER(u.email)', $email);
        $this->db->where('up.company_id', $company_id);
        $query = $this->db->get();

        //echo $this->db->last_query();
        if ($query->num_rows >= 1)
        {
            return true;
        }
        return false;
    }


    function check_for_permission($permission_to_check)
    {
        // load session information of current user's permissions
        // this is very UNSECURE. need to be improved
        $user_permissions = $this->session->userdata('user_permissions');
        if(isset($user_permissions) && $user_permissions)
        {
            foreach ($user_permissions as $permission)
            {
                if (isset($permission['permission']) && $permission['permission'] == $permission_to_check)
                {
                    return 1;
                }
            }
        }

        return 0;
    }

    function get_tos_agreed_date($user_id) {
        $this->db->select('tos_agreed_date');
        $this->db->where('id', $user_id);
        $query = $this->db->get('users');
        $q = $query->result();
        return $q[0]->tos_agreed_date;
    }

    function set_tos_agreed_date($user_id, $date) {
        $this->db->where('id', $user_id);
        return $this->db->update("users", array('tos_agreed_date' => $date));
    }

    function get_owner($company_id) {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->join('user_permissions', 'user_permissions.user_id = users.id', 'left');
        $this->db->join('user_profiles', 'user_profiles.user_id = users.id', 'left');
        $this->db->where('user_permissions.company_id', $company_id);
        $this->db->where('user_permissions.permission', "is_owner");
        $query = $this->db->get();

        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());

        if($query->num_rows() >= 1)
        {
            $result = $query->result_array();
            return $result[0];
        }

        return array();
    }

    function set_owner($email, $company_id)
    {
        // find user_id of the email given first
        $this->db->from('users as u');
        $this->db->where('u.email', $email);
        $query = $this->db->get();

        // if the user exists, assign the user_id to the is_owner account of the company
        if ($query->num_rows >= 1)
        {
            $results = $query->result_array();
            $user_id = $results[0]['id'];

			// delete all non-owner association of the user with the company
			$this->db->where('company_id', $company_id);
			$this->db->where('user_id', $user_id);
			$this->db->where("permission != 'is_owner'");
			$this->db->delete('user_permissions');

			// check if the user is not an owner already
			$this->db->where('company_id', $company_id);
			$this->db->where('user_id', $user_id);
			$this->db->where('permission', 'is_owner');
			$this->db->from('user_permissions');
			$query = $this->db->get();

			// if the user exists, assign the user_id to the is_owner account of the company
			if ($query->num_rows == 0)
			{
				// create new owner permissions
				$this->db->insert('user_permissions', array(
						'company_id'=> $company_id,
						'user_id' =>$user_id,
						'permission'=> 'is_owner'
					)
				);
			}
        }

    }

    function update_user($user_id, $data)
    {
        $this->db->where('id', $user_id);
        $query = $this->db->update('users', $data);
    }

    function update_user_profile($user_id, $data)
    {
        $this->db->where('user_id', $user_id);
        $query = $this->db->update('user_profiles', $data);
    }

    function get_user_profile($user_id)
    {

        $this->db->select('*');
        $this->db->from('users');
        $this->db->join('user_profiles', 'user_profiles.user_id = users.id', 'left');
        $this->db->where('users.id', $user_id);

        $query = $this->db->get();

        if ($query->num_rows >= 1)
        {
            $results = $query->row_array();
            return $results;
        }
    }
    /**
     * deleted all login session for a user with email
     * @param $email
     * @return bool
     */
    function delete_all_user_sessions($email){

        $sql ="DELETE FROM sessions where user_data like '%".$email."%'";
    
        $query = $this->db->query($sql);

        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }else{
            if ($this->db->affected_rows() > 0){
                return TRUE;
            }else{
                return FALSE;
            }
        }

    }
    
    /**
     * Checks if user is admin
     * @param $user_id
     * @return bool
     */
    public function is_admin($user_id)
    {
        $result = false;
        
        if(SUPER_ADMIN_USER_ID == $user_id){
            $result = true;
        }
        if (!$result) {
            $this->db->where('id', $user_id);
            $query = $this->db->get('users');
            if ($query->num_rows >= 1) {
                $user   = $query->row_array();
                $result = (bool)$user['is_admin'];
            }
        }
        if (!$result) {
            $sql = "SELECT * 
                    FROM whitelabel_partner as wp
                    LEFT JOIN 
                        whitelabel_partner_x_admin as wpxa on wp.id = wpxa.partner_id
                    WHERE 
                        wpxa.admin_id = '$user_id'";

            $query = $this->db->query($sql);
            if($query->num_rows() >= 1)
            {
                $result = true;
            }
        }
        return $result;
    }

    function get_user_autologin($user_id)
    {
        $this->db->where('user_id', $user_id);

        $query = $this->db->get('user_autologin');
        if ($query->num_rows >= 1)
        {
            $results = $query->row_array();
            return $results;
        }
        return null;
    }


    // check if the user has completed the tutorial
    function has_user_completed_tutorial($user_id) {
        $this->db->select('completed_tutorial');
        $this->db->where('id', $user_id);
        $query = $this->db->get('users');

        $q = $query->result();
        return $q[0]->completed_tutorial;
    }

    /**
     * Get the date when the trial should expire and the customer should be billed at
     * The next_billing_date is 1 month after the user account has been created.
     * Called from account_settings.php
     *
     * @param	int
     * @return	string
     */
    function get_first_billing_date($user_id) {
        $this->db->select("created");
        $this->db->where('id', $user_id);
        $query = $this->db->get('users');

        if ($query->num_rows >= 1)
        {
            $results = $query->result_array();
            $created_date = $results[0]['created']; //roles are stored in the same column as permission
            $trial_expiry_date = Date("Y-m-d",strtotime("+1 month", strtotime($created_date)));
            return $trial_expiry_date;
        }

        return NULL;
    }


    /**
     * Get currently selected company of the user
     * ie) owner, employee
     *
     * @param	int
     * @return	string
     */
    function get_current_company_id($user_id) {
        $this->db->where('user_id', $user_id);
        $this->db->from('user_profiles');
        $query = $this->db->get();

        if ($query->num_rows >= 1)
        {
            $results = $query->result_array();
            return $results[0]['current_company_id']; //roles are stored in the same column as permission
        }

        return NULL;
    }

    // check if user hid the booking reminder
    function is_reminder_hidden($user_id)
    {
        $this->db->select('is_reminder_hidden');
        $this->db->where('id', $user_id);
        $query = $this->db->get('users');

        $q = $query->result();
        return $q[0]->is_reminder_hidden;
    }

    function set_reminder_as_hidden($user_id) {
        $this->db->where('id', $user_id);
        return $this->db->update("users", array('is_reminder_hidden' => '1'));
    }

    function set_reminder_as_visible($user_id) {
        $this->db->where('id', $user_id);
        return $this->db->update("users", array('is_reminder_hidden' => '0'));
    }

    function delete_user_by_email($email)
    {
        $this->db->where('email', $email);
        $this->db->delete('users');
    }

    function get_user_by_id($id, $get_company_data = true)
    {
        if ($get_company_data) {
            $this->db->select('users.tos_agreed_date, users.email, users.is_overview_calendar, user_profiles.first_name, user_profiles.last_name, company.phone, company.address, company.city, company.country, user_permissions.permission');
            $this->db->from('users');
            $this->db->join('user_permissions','user_permissions.user_id=users.id','left');
            $this->db->join('user_profiles','user_profiles.user_id=users.id','left');
            $this->db->join('company','company.company_id=user_permissions.company_id','left');
        } else {
            $this->db->select('users.tos_agreed_date, users.email, users.is_overview_calendar, user_profiles.first_name, user_profiles.last_name');
            $this->db->from('users');
            $this->db->join('user_profiles','user_profiles.user_id=users.id','left');
        }

        $this->db->where('users.id', $id);
        $query = $this->db->get();

        //echo $this->db->last_query();
        if ($query->num_rows >= 1)
        {
            $results = $query->result_array();
            return $results[0]; //roles are stored in the same column as permission
        }
        return false;
    }
    
    function get_partner_id_by_user_id($user_id){
        $sql = "SELECT 
                    IF(wp.id, wp.id, u.partner_id) as partner_id, wp.timezone, wp.currency_id, wp.default_property_status
                FROM users as u
                LEFT JOIN 
                    whitelabel_partner_x_admin as wpxa on u.id = wpxa.admin_id
                LEFT JOIN 
                    whitelabel_partner as wp on wp.id = wpxa.partner_id
                WHERE 
                    u.id = '$user_id'
                ORDER BY wpxa.partner_id ASC
                ";
        
        $query = $this->db->query($sql);
		if($query->num_rows() >= 1)
		{
            $results = $query->result_array();
            return $results;
        }
        return NULL;
    }

    function get_users_count(){
        $this->db->select('count(*) as total_users');
        $this->db->from('users');

        $query = $this->db->get();

        if ($query->num_rows >= 1)
        {
            $results = $query->row_array();
            return $results['total_users'];
        }
        return null;
    }

    function get_company_mambers($company_id, $permission)
    {
        $this->db->select('users.id,users.email, user_profiles.first_name, user_profiles.last_name,user_permissions.permission');
        $this->db->from('users');
        $this->db->join('user_permissions','user_permissions.user_id=users.id');
        $this->db->join('user_profiles','user_profiles.user_id=users.id');
        $this->db->where('user_permissions.permission', $permission);
        $this->db->where('user_permissions.company_id', $company_id);
        $query = $this->db->get();

        //echo $this->db->last_query();

        if ($query->num_rows >= 1)
        {
            $results = $query->result_array();
            return $results; //roles are stored in the same column as permission
        }
        return false;
    }
}

/* End of file user_model.php */
