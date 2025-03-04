<?php

class Permission_model extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }


    // Controller & function combination that are allowed to be viewed publicly
    function is_public($controller_name, $function_name)
    {
        if (
            ($controller_name === "invoice" && ($function_name === 'show_invoice_read_only' || $function_name === 'show_master_invoice_read_only')) ||
            ($controller_name === "room" && $function_name === 'get_rooms_available_AJAX') ||
            ($controller_name === 'online_reservation') ||
            ($controller_name === 'online_booking_engine') ||
            ($controller_name === 'customer' && $function_name === 'get_cc_cvc_encrypted') ||
            ($controller_name === 'customer' && $function_name === 'detokenize_unused_token') ||
            ($controller_name === 'customer' && $function_name === 'delete_unused_token') ||
            ($controller_name === 'cron') ||
            ($controller_name === "auth" && $function_name != 'create_company') ||
            ($controller_name === "language_translation" && $function_name == 'get_translated_phrase') ||
            ($controller_name === "menu") ||
            ($controller_name === "test") ||
            ($controller_name === "help") ||
			($controller_name === "channel_manager") ||
            (
                isset($this->session->userdata['customer_modify_booking']) && $this->session->userdata['customer_modify_booking'] &&
                $this->session->userdata['customer_modify_booking']['allow'] &&
                $controller_name === "booking" && 
                (
                    $function_name === 'show_booking_information' ||
                    $function_name === 'check_overbooking_AJAX' ||
                    $function_name === 'guest_update_booking'
                )
            ) ||
            (
                $controller_name === "channex_bookings" && 
                (
                    $function_name === 'channex_get_bookings'
                )
            ) ||
            (
                $controller_name === "subscription" && 
                (
                    $function_name === 'get_subscription_status'||
                    $function_name === 'get_subscription_update_cancelled'
                )
            )
            ||
            (
                $controller_name === "nexio_integration" && 
                (
                    $function_name === 'forward_encrypt_card'
                )
            ) ||

            (
                $controller_name === "pci_booking" && 
                (
                    ($function_name === 'card_token_success' || $function_name === 'card_token_failure'|| $function_name ==='get_tokenization_response' || $function_name ===  'card_over_phone_success')
                )
            ) ||
            (
                $controller_name === "cardknox_controller" && 
                (
                    ($function_name === 'save_customer_cardknox_card' )
                )
            ) 
            ||
            (
                $controller_name === "restaurant_pos" && 
                (
                    ($function_name === 'checkin_guest_details_post'  || $function_name === 'room_transfer_push' || $function_name === 'invoice_item_transfer_push')
                )
            )
            ||
            (
                $controller_name === "nestpay_integration" && 
                (
                    $function_name === 'nestpay_response_ok_url' || $function_name === 'nestpay_response_fail_url' || $function_name === 'get_nestpay_post_bookingengine' || $function_name ===  'nestpay_bookingengine_ok_fail_url' || $function_name ==='nestpay_group_response_ok_url' || $function_name ==='nestpay_group_response_fail_url'
                )
            )||
            (
                $controller_name === "nestpaymkd_integration" && 
                (
                    $function_name === 'nestpaymkd_response_ok_url' || $function_name === 'nestpaymkd_response_fail_url' || $function_name === 'get_nestpaymkd_post_bookingengine' || $function_name ===  'nestpaymkd_bookingengine_ok_fail_url' || $function_name ==='nestpaymkd_group_response_ok_url' || $function_name ==='nestpaymkd_group_response_fail_url'
                )
            )
            ||
            (
                $controller_name === "nestpayalb_integration" && 
                (
                    $function_name === 'nestpayalb_response_ok_url' || $function_name === 'nestpayalb_response_fail_url' || $function_name === 'get_nestpayalb_post_bookingengine' || $function_name ===  'nestpayalb_bookingengine_ok_fail_url' || $function_name ==='nestpayalb_group_response_ok_url' || $function_name ==='nestpayalb_group_response_fail_url'
                )
            )
            ||
            (
                $controller_name === "nestpaysrb_integration" && 
                (
                    $function_name === 'nestpaysrb_response_ok_url' || $function_name === 'nestpaysrb_response_fail_url' || $function_name === 'get_nestpaysrb_post_bookingengine' || $function_name ===  'nestpaysrb_bookingengine_ok_fail_url' || $function_name ==='nestpaysrb_group_response_ok_url' || $function_name ==='nestpaysrb_group_response_fail_url'
                )
            )
             ||
            (
                $controller_name === "customer" && 
                (
                    $function_name === 'post_add_customer_callback'
                )
            )

        ) {
            return true; // let em access the page!
        }
        return false;
    }


    function is_route_public($route_name) {
        if (
            ($route_name === 'cron') ||
            ($route_name === 'public')
        ) {
            return true; // let em access the page!
        }
        return false;
    }

    // this is the new, better way to control user access (2015-01-30)
    function has_access_to_function($user_id, $company_id, $controller_name, $function_name)
    {
        if (
            $controller_name === "auth" ||
            ($controller_name === 'account_settings') ||
            ($controller_name === 'menu' && $function_name === 'select_hotel') ||
            $controller_name === "properties"
        ) {
            return true;
        }


        // If user is able to login to the property, user is given with following permissions
        $user_permissions = $this->_get_user_permissions($user_id, $company_id, $controller_name, $function_name);	
		
        if (sizeof($user_permissions) > 0)
        {			
            if (
                ($controller_name === "wizard") ||
                ($controller_name === "menu") ||
                ($controller_name === "account_settings") ||
                ($controller_name === "rate_plan") ||
                ($controller_name === "booking" &&
                    (
                        $function_name !== "index" &&
                        $function_name !== 'delete_booking_AJAX' // no access
                    )
                ) ||
                ($controller_name === "user") ||
                ($controller_name === "accounting" &&
                    (
                        $function_name !== "index" &&
                        $function_name !== "invoice" &&
                        $function_name === "cc_tokenization_status" &&
                        $function_name !== "save_invoice_settings"
                    )
                ) ||
                ($controller_name === "customer" && $function_name !== "index") ||
                ($controller_name === "invoice" &&
                    (
                        $function_name !== 'delete_charge_JSON' &&
                        $function_name !== 'delete_payment_JSON' &&
                        $function_name !== 'refund_payment_JSON' && 
                        $function_name !== 'add_folio_AJAX' && 
                        $function_name !== 'remove_folio_AJAX' &&    
                        $function_name !== 'update_folio_AJAX' && 
                        $function_name !== 'insert_payment_AJAX' &&   
                        $function_name !== 'move_charge_payment' && 
                        $function_name !== 'save_invoice'
                    )
                ) ||
                // channel manager update
                $controller_name === "channel_manager"
                ||
                ($controller_name === "settings" &&
                    (
                        $function_name == "show_booking_list"
                    )
                )

            ) {
                return true;
            }

            if(
                $controller_name === "calendar" &&
                !in_array('bookings_view_only', $user_permissions)                        
            ) {
                return true;
            } else
                if(
                    $controller_name === "calendar" && 
                    $function_name !== "move_booking_room_history" && 
                    $function_name !== "resize_booking_room_history"
                )
                {
                    return true;
                }
		}
        
        // Permission-specific access grants
        foreach ($user_permissions as $permission)
        {

            if (
                $permission == 'is_admin' // admins can do whatever the hell they want
                ||
                (
                    $permission == 'is_owner' &&  // owners can do except for deleting company
                    $controller_name != 'admin' &&
                    (
                    !(
                        $controller_name == 'company' && $function_name == "delete"
                    )
                    )
                )
                || // the rest are custom employee permissions
                (
                    $permission == 'access_to_bookings' &&
                    (
                        (
                            $controller_name == 'booking' && $function_name != 'delete_booking_AJAX'
                        ) ||
                        (
                            $controller_name == 'extra'
                        )

                    )
                )
                || 
                (
                    $permission == 'is_employee' &&
                    (
                        (
                            $controller_name == 'company_security'
                        )
                    )
                )
                || 
                (
                    $permission == 'bookings_view_only' &&
                    (
                        (
                            $controller_name == 'booking' && $function_name != 'delete_booking_AJAX'
                        ) ||
                        (
                            $controller_name == 'booking' && $function_name != '_create_booking'
                        ) ||
                        (
                            $controller_name == 'booking' && $function_name != 'create_booking_AJAX'
                        ) ||
                        (
                            $controller_name == 'booking' && $function_name != 'update_booking_AJAX'
                        ) ||
                        (
                            $controller_name == "calendar" && 
                            (
                                $function_name != "move_booking_room_history" && 
                                $function_name != "resize_booking_room_history"
                            )
                        ) ||
                        (
                            $controller_name == 'extra'
                        ) 

                    )
                ) || 
                ($permission == 'is_salesperson' && 
                    (
                        $controller_name == 'admin' ||
                        (
                            $controller_name == 'company' && 
                            (
                                $function_name == "get_partners_in_JSON" || 
                                $function_name == "get_company_in_JSON"
                            )
                        )
                    )
                ) ||
                ($permission == 'access_to_customers' && $controller_name == 'customer') ||
                ($permission == 'access_to_rooms' && $controller_name == 'room' && $function_name != 'date_manager') ||
                ($permission == 'can_view_reports' &&
                    (
                        // reports
                        // $controller_name === "ledger" ||
                        $controller_name === "employee" ||
                        $controller_name === "market_segment" ||
                        $controller_name === "reservation" ||
                        ($controller_name === "room" &&
                            (
                                $function_name === "show_housekeeping_report" ||
                                $function_name === "show_inhouse_report" ||
                                $function_name === "show_room_report"
                            )
                        )  ||
                        ($controller_name == 'booking' && $function_name == 'download_csv_export')
                    )
                ) ||
                
                ($permission == 'can_change_settings' &&
                    (
                        (
                            $controller_name == 'company' && $function_name != "delete" &&
                            $controller_name == 'company' && $function_name != "get_company_in_JSON"
                        ) ||
                        $controller_name == 'room_inventory' ||
                        $controller_name == 'accounting' ||
                        $controller_name == 'email' ||
                        $controller_name == 'rates' ||
                        $controller_name == 'website' ||
                        $controller_name == 'permissions' ||
                        $controller_name == 'image' ||
                        $controller_name == 'reservations'
                    )
                ) ||
                ($permission == 'access_to_integrations' && $controller_name == 'integrations') ||
                ($permission == 'access_to_ledger_reports' && $controller_name == 'ledger') ||

                ($permission == 'can_edit_invoices' && $controller_name == 'invoice' && (
                        $function_name == 'refund_payment_JSON' || 
                        $function_name == 'delete_payment_JSON' || 
                        $function_name == 'delete_charge_JSON'
                    ) 
                ) ||
                ($permission == 'can_post_charges_payments' && $controller_name == 'invoice' && 
                    (
                        ($function_name == 'save_invoice' && !$this->input->post('charge_changes')) ||
                        $function_name == 'insert_payment_AJAX' ||
                        $function_name == 'add_folio_AJAX' ||
                        $function_name == 'remove_folio_AJAX' ||
                        $function_name == 'update_folio_AJAX' ||
                        $function_name == 'move_charge_payment'
                    ) 
                ) ||
                ($permission == 'can_modify_charges' && $controller_name == 'invoice' &&
                    (
                        $function_name == 'save_invoice'
                    )
                ) ||
                //($permission == 'can_delete_payments' && $controller_name == 'invoice' && $function_name == 'delete_payment_JSON') ||
                ($permission == 'can_delete_bookings' &&
                    (
                        $controller_name == 'booking' && $function_name == 'delete_booking_AJAX'
                    )
                ) ||
                ($permission == 'can_date_manage' &&
                    (
                        $controller_name == 'room' &&  $function_name == 'date_manager'
                    )
                ) ||
				($permission == 'is_housekeeping' &&
                    (
                        $controller_name == 'room' &&  
						(
							$function_name == 'index' || $function_name == 'update_room_status' || $function_name == 'get_notes_AJAX' ||
							$function_name == 'update_notes_AJAX' || $function_name == 'set_rooms_clean'
						)
                    )
                )
                ||
                (
                    $permission == 'access_to_extensions' && 
                    ($controller_name == 'extensions' || $this->router->fetch_module() != '')
                )
            )
            {
                return true;
            }

        }

        return false;

    }

    function has_access_to_booking_id($user_id, $booking_id) {
        
        if(isset($this->session->userdata['customer_modify_booking']) && $this->session->userdata['customer_modify_booking']['allow'] && $this->session->userdata['customer_modify_booking']['booking_id'] == $booking_id)
        {
            return true;
        }

        $this->db->from('user_permissions as up, booking as b');
        $this->db->where('up.user_id', $user_id);
        $this->db->where('up.company_id = b.company_id');
        $this->db->where('b.booking_id', $booking_id);

        $query = $this->db->get();

        if ($query->num_rows >= 1)
        {
            return true;
        }
        
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
                    up.company_id = '{$this->company_id}' AND
                    (wpxa.admin_id = '$user_id' OR '".SUPER_ADMIN_USER_ID."' = '$user_id')
                GROUP BY up.company_id";
        
        $query = $this->db->query($sql);
		if($query->num_rows() >= 1)
		{
            return true;
		}
        
        return false;
    }

    function has_access_to_customer_id($user_id, $customer_id) {
        $this->db->from('user_permissions as up, customer as c');
        $this->db->where('up.user_id', $user_id);
        $this->db->where('up.company_id = c.company_id');
        $this->db->where('c.customer_id', $customer_id);

        $query = $this->db->get();

        if ($query->num_rows >= 1)
        {
            return true;
        }
        
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
                    up.company_id = '{$this->company_id}' AND
                    (wpxa.admin_id = '$user_id' OR '".SUPER_ADMIN_USER_ID."' = '$user_id')
                GROUP BY up.company_id";
        
        $query = $this->db->query($sql);
		if($query->num_rows() >= 1)
		{
            return true;
		}
        
        return false;
    }

    function has_access_to_customer_type_id($user_id, $customer_type_id) {
        $this->db->from('user_permissions as up, customer_type as ct');
        $this->db->where('up.user_id', $user_id);
        $this->db->where('up.company_id = ct.company_id');
        $this->db->where('ct.id', $customer_type_id);

        $query = $this->db->get();

        if ($query->num_rows >= 1)
        {
            return true;
        }
        
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
                    up.company_id = '{$this->company_id}' AND
                    (wpxa.admin_id = '$user_id' OR '".SUPER_ADMIN_USER_ID."' = '$user_id')
                GROUP BY up.company_id";
        
        $query = $this->db->query($sql);
		if($query->num_rows() >= 1)
		{
            return true;
		}

        return false;
    }


    function has_access_to_customer_field_id($user_id, $customer_field_id) {
        $this->db->from('user_permissions as up, customer_field as cf');
        $this->db->where('up.user_id', $user_id);
        $this->db->where('up.company_id = cf.company_id');
        $this->db->where('cf.id', $customer_field_id);

        $query = $this->db->get();
        //echo $this->db->last_query();
        if ($query->num_rows >= 1)
        {
            return true;
        }

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
                    up.company_id = '{$this->company_id}' AND
                    (wpxa.admin_id = '$user_id' OR '".SUPER_ADMIN_USER_ID."' = '$user_id')
                GROUP BY up.company_id";
        
        $query = $this->db->query($sql);
		if($query->num_rows() >= 1)
		{
            return true;
		}
        
        return false;
    }



    function has_access_to_company_id($user_id, $company_id)
    {
        $this->db->from('user_permissions as up, company as c');
        $this->db->where('up.user_id', $user_id);
        $this->db->where('up.company_id = c.company_id');
        $this->db->where('c.company_id', $company_id);

        $query = $this->db->get();

        if ($query->num_rows >= 1)
        {
            return true;
        }
        $permissions = $this->session->userdata("permissions");
        $is_salesperson = is_array($permissions) && in_array("is_salesperson", $permissions) ? 1 : 0;
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
                    (wpxa.admin_id = '$user_id' OR '".SUPER_ADMIN_USER_ID."' = '$user_id' OR ($is_salesperson = 1))
                GROUP BY up.company_id";
        
        $query = $this->db->query($sql);
		if($query->num_rows() >= 1)
		{
            return true;
		}
        
        return false;
    }

    function _get_user_permissions($user_id, $company_id, $controller_name = null, $function_name = null) {
        $results = array();
        
        $this->db->select('permission');
        $this->db->where('user_id', $user_id);
        $this->db->where('company_id', $company_id);

        $query = $this->db->get('user_permissions');

        if ($query->num_rows >= 1)
        {
            $permissions = $query->result_array();
            $results = array();
            foreach ($permissions as $permission)
            {
                $results[] = $permission['permission'];
            }
        }
        
        $sql = "SELECT 
                    'is_admin' as permission
                FROM user_permissions as up
                LEFT JOIN 
                    company as c on c.company_id = up.company_id
                LEFT JOIN 
                    whitelabel_partner as wp on 1 OR wp.id = c.partner_id #check if current user is admin of any whitelabel partner
                LEFT JOIN 
                    whitelabel_partner_x_admin as wpxa on wp.id = wpxa.partner_id
                WHERE 
                    up.permission = 'is_owner' AND
                    (up.company_id = '$company_id' OR '$controller_name' = 'admin') AND
                    (wpxa.admin_id = '$user_id' OR '".SUPER_ADMIN_USER_ID."' = '$user_id')
                GROUP BY up.company_id";
        
        $query = $this->db->query($sql);
		if($query->num_rows() >= 1)
		{
            $result_array = $query->result_array();
            $results[] = $result_array[0]['permission'];
		}

        return $results;
    }

    function is_extension_active($extension_name, $company_id, $is_array = false)
    {
        $this->db->from('extensions_x_company');

        if($is_array){
            $this->db->where_in('extension_name', $extension_name);
        } else {
            $this->db->where('extension_name', $extension_name);
        }

        $this->db->where('company_id', $company_id);
        $this->db->where('is_active', 1);
        
        $query = $this->db->get();

        if ($query->num_rows >= 1)
        {
            if($is_array){
                $result_array = $query->result_array();
                return $result_array;
            } else {
                return true;
            }
        }

        if($is_array){
            return null;
        } else {
            return false;
        }
    }


}

/* End of file user_model.php */