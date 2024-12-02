<?php
class Company extends MY_Controller
{
    function __construct()
    {

        parent::__construct();
        $this->load->model('Image_model');
        $this->load->model('Charge_type_model');
        $this->load->model('Company_model');
        $this->load->model('User_model');
        $this->load->model("Room_model");
        $this->load->model('Currency_model');
        $this->load->model('Employee_log_model');
        $this->load->model('Room_type_model');
        $this->load->model('Customer_field_model');
        $this->load->model('Customer_type_model');
        $this->load->model('Customer_model');
        $this->load->model('Charge_model');
        $this->load->model('Extra_model');
        $this->load->model('Payment_model');
        $this->load->model('Booking_field_model');
        $this->load->model('Booking_source_model');
        $this->load->model('Import_mapping_model');
        $this->load->model('Rate_plan_model');
        $this->load->model('Date_range_model');
        $this->load->model('Booking_linked_group_model');
        $this->load->model('Tax_price_bracket_model');
        $this->load->model('Room_location_model');
        $this->load->model('Statement_model');

        $this->load->library('email');
        $this->load->library('form_validation');
        $this->load->library('ckeditor');
        $this->load->library('ckfinder');

        $this->load->helper('url'); // for redirect
        $this->load->helper('timezone');

        $this->load->helper('ckeditor_helper');

        $view_data['menu_on'] = true;

        $this->user_id    = $this->session->userdata('user_id');
        $this->company_id = $this->session->userdata('current_company_id');

        $view_data['menu_on']          = true;
        $view_data['selected_menu']    = 'Settings';
        $view_data['selected_submenu'] = 'Company';

        $view_data['submenu'] = 'hotel_settings/hotel_settings_submenu.php';

        $view_data['submenu_parent_url'] = base_url()."settings/";
        $view_data['sidebar_menu_url'] = base_url()."settings/company/";

        $view_data['menu_items'] = $this->Menu_model->get_menus(array('parent_id' => 5, 'wp_id' => 1));
        $view_data['sidebar_links'] = $this->Menu_model->get_menus(array('parent_id' => 29, 'wp_id' => 1));

        $this->load->vars($view_data);
    }

    function index()
    {
        $this->general();
    }

    function general()
    {
        $data = array();

        //Load company data
        if (!is_null($company = $this->Company_model->get_company($this->company_id))) {
            $data['company'] = $company;
        }
        $data['actual_number_of_rooms'] = $this->Room_model->get_number_of_rooms($this->company_id);

        $data['available_currencies'] = $this->Currency_model->get_available_currency_list($this->company_id);

        if(!$company['logo_image_group_id']) {
            $cdata = array();
            $cdata['logo_image_group_id'] = $this->Image_model->create_image_group(LOGO_IMAGE_TYPE_ID);
            $this->Company_model->update_company($this->company_id, $cdata);
            $data['company']['logo_image_group_id'] = $company['logo_image_group_id'] = $cdata['logo_image_group_id'];
        }

        $data['logo_images'] = $this->Image_model->get_images($company['logo_image_group_id']);
        $data['timezones'] = get_timezones();
        $data['languages'] = get_enabled_languages();

        // Validation
        $this->form_validation->set_rules('company_name', 'Company Name', 'required|trim');
        $this->form_validation->set_rules('company_address', 'Address', 'required|trim');
        $this->form_validation->set_rules('company_phone', 'Phone', 'required|trim');
        $this->form_validation->set_rules('company_city', 'City', 'required|trim');
        $this->form_validation->set_rules('company_region', 'Region', 'required|trim');
        $this->form_validation->set_rules('company_country', 'Country', 'required|trim');
        $this->form_validation->set_rules('company_postal_code', 'Postal Code', 'trim');
        // $this->form_validation->set_rules('company_website', 'Website', 'required|trim');
        $this->form_validation->set_rules('company_email', 'Email', 'valid_email|trim');

        if ($this->form_validation->run() == true) {

            $update_data = array(

                'name'                => $this->input->post('company_name'),
                'address'             => $this->input->post('company_address'),
                'phone'               => $this->input->post('company_phone'),
                'city'                => $this->input->post('company_city'),
                'region'              => $this->input->post('company_region'),
                'country'             => $this->input->post('company_country'),
                'postal_code'         => $this->input->post('company_postal_code'),
                'number_of_rooms'     => $this->input->post('number_of_rooms'),
                'website'             => $this->input->post('company_website'),
                'bussiness_name'      => $this->input->post('bussiness_name'),
                'bussiness_number'    => $this->input->post('bussiness_number'),
                'bussiness_fiscal_number'=> $this->input->post('bussiness_fiscal_number'),
                'email'               => $this->input->post('company_email'),
                'fax'                 => $this->input->post('company_fax'),
                'time_zone'           => $this->input->post('time_zone'),
                'default_currency_id' => $this->input->post('default_currency'),
                'default_language'    => $this->input->post('default_language')
            );

            $this->company_id  = $this->session->userdata('current_company_id');
            $this->Company_model->update_company($this->company_id, $update_data);
            $default_currency       = $this->Currency_model->get_default_currency($this->company_id);
            $data['currency_symbol'] = isset($default_currency['currency_code']) ? $default_currency['currency_code'] : null;
            $this->session->set_userdata(array('currency_symbol' => $data['currency_symbol']));
            redirect('/settings/company/general'); // redirect to settings unavailable screen
        }

        $data['js_files'] = array(
            base_url().auto_version('js/hotel-settings/cropper_jsmin.js'),
            base_url().auto_version('js/company_settings.js'),
            base_url().auto_version('js/hotel-settings/logo-image-settings.js'),
            "https://ajax.aspnetcdn.com/ajax/jquery.validate/1.7/jquery.validate.min.js",
        );

        $data['company_ID'] = $this->company_id;

        $data['selected_sidebar_link'] = 'General Information';

        $data['main_content'] = 'hotel_settings/company/general';
        $this->load->view('includes/bootstrapped_template', $data);

    }


    function night_audit()
    {
        $data['company'] = $this->Company_model->get_company($this->company_id);

        $this->form_validation->set_rules('selling_date', 'Selling Date', 'required|trim|callback_date_check');
        $this->form_validation->set_rules('night_audit_auto_run_is_enabled', 'Automatically prompt night audit', 'trim'); // This is just here to check the form submission is initiated from night audit settings page
        if ($this->form_validation->run() == true) {

            $update_data = array(
                'selling_date'                      => $this->input->post('selling_date'),
                'night_audit_auto_run_is_enabled'   => ($this->input->post('night_audit_auto_run_is_enabled') == 'on') ? '1' : '0',
                'night_audit_auto_run_time'         => $this->input->post('night_audit_auto_run_time'),
                'night_audit_auto_prompt'           => ($this->input->post('night_audit_auto_prompt') == 'on') ? '1' : '0',
                'night_audit_auto_prompt_time'      => $this->input->post('night_audit_auto_prompt_time'),
                'night_audit_force_check_out'       => ($this->input->post('night_audit_force_check_out') == 'on') ? '1' : '0',
                'night_audit_multiple_days'         => ($this->input->post('night_audit_multiple_days') == 'on') ? '1' : '0',
                'night_audit_charge_in_house_only'  => ($this->input->post('night_audit_charge_in_house_only') == 'on') ? '1' : '0',
                'night_audit_ignore_check_out_date' => ($this->input->post('night_audit_ignore_check_out_date') == 'on') ? '1' : '0'
            );
            $this->Company_model->update_company($this->company_id, $update_data);
            $this->_create_employee_log("Night audit settings updated");

            $default_room_charge_type_id = $this->input->post('default_room_charge_type_id');
            $this->Charge_type_model->set_default_room_charge_type($this->company_id, $default_room_charge_type_id);

            $new_selling_date = $this->Company_model->get_selling_date($this->company_id);
            $this->session->set_userdata('current_selling_date', $new_selling_date);

            redirect('/settings/company/night_audit');
        }

        $data['charge_types'] = $this->Charge_type_model->get_room_charge_types($this->company_id);

        //load view
        $data['js_files']   = array(base_url().auto_version('js/hotel-settings/night-audit-settings.js'));
        $data['company_ID'] = $this->company_id;

        $data['selected_sidebar_link'] = 'Night Audit & Date';
        $data['main_content']          = 'hotel_settings/company/night_audit';

        //No Post Redirect Get here, because the validation error message must be shown
        $this->load->view('includes/bootstrapped_template', $data);

        return;
    }
    public function xss_clean($str, $is_image = FALSE)
    {
        return $str;
    }

    function date_check($date)
    {
        //match the format of the date
        if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts)) {
            //check wheter the date is valid of not
            if (checkdate($parts[2], $parts[3], $parts[1])) {
                return true;
            } else {
                $this->form_validation->set_message('date_check', 'Sell date must be a valid date');
            }
        } else {
            $this->form_validation->set_message('date_check', 'Sell date must be a valid date');
        }

        return false;
    }

    /* EMPLOYEES */


    function employees()
    {
        $this->form_validation->set_rules('employee_first_name', 'First Name', 'required|trim');
        $this->form_validation->set_rules('employee_last_name', 'Last Name', 'required|trim');
        $this->form_validation->set_rules('employee_email', 'Email', 'required|trim|email');

        //Add employee
        if ($this->form_validation->run() == true) {
            $this->load->model('tank_auth/users');
            $email      = $this->input->post('employee_email');
            $first_name = $this->input->post('employee_first_name');
            $last_name  = $this->input->post('employee_last_name');

            // $this->_add_employee($this->company_id, $email, $first_name, $last_name);
            $user_id = $this->_add_employee($this->company_id, $email, $first_name, $last_name);

            $this->User_model->update_user($user_id, array('partner_id' => $this->vendor_id));
            redirect('settings/company/employees');
        }

        $data = array();

        $data['can_edit_employees'] = $this->session->userdata('user_role') == 'is_admin'
            || $this->session->userdata('user_role') == 'is_owner'
            || $this->User_model->check_for_permission('can_change_settings');
        //Get employee list
        $company                                 = $this->Company_model->get_company($this->company_id);
        $data['employee_auto_logout_is_enabled'] = $company['employee_auto_logout_is_enabled'];

        $data['employees'] = array();
        //Get employee list
        if (!is_null($employees = $this->Company_model->get_user_list($this->company_id, true))) {
            $data['employees'] = $employees;
        }

        //Get all user permissions
        if (!is_null($company_permissions = $this->Company_model->get_all_user_permissions($this->company_id))) {
            $data['company_permissions'] = $company_permissions;
        }

        $data['selected_sidebar_link'] = 'Team';
        $data['main_content']          = 'hotel_settings/company/employees';
        $data['js_files']              = array(base_url().auto_version('js/hotel-settings/employees.js'));

        $this->load->view('includes/bootstrapped_template', $data);

    }

    function _add_employee($company_id, $email, $first_name, $last_name)
    {
        //Create user if user doesn't exist, otherwise get user info

        // if  user doesn't exist in company
        if (!$this->User_model->user_exists_in_company($email, $company_id)) {

            // if user doesn't exist in Minical, create a new user
            if (is_null($user = $this->users->get_user_by_email($email)))
            {

                echo l("email doesn't exist in Minical. creating a new user",true);
                //Note: password is not set for employees until they register
                $data = array(
                    'email'              => $email,
                    'current_company_id' => $company_id,
                    'first_name'         => $first_name,
                    'last_name'          => $last_name,
                    'password'           => md5(rand().microtime()) //random password to prevent login when password hasn't been set yet
                );

                //Create user
                if (!is_null($user = $this->users->create_user($data, false)))
                {

                } else {
                    //TO DO: error in creating user
                }


            }

            //Email employee activation form
            $employee_email = $this->form_validation->set_value('employee_email');

            // to avoid sending emails to test accounts
            if (
                $employee_email != "testAdmin@innGrid.net" &&
                $employee_email != "testOwner@innGrid.net" &&
                $employee_email != "testEmployee@innGrid.net"
            ) {
                //echo "sending activation email to ".$employee_email;
                $this->_email_employee_activation($employee_email);
            }

            //The above functions return user id in different forms.
            //'get_user_by_email' returns user as an object
            //'create_user' returns user as an array
            if (gettype($user) == 'object') //user already exists
            {
                $user_id = $user->id;
            } else //user is newly created user
            {
                $user_id = $user['user_id'];
            }

            //Check if company permission already added
            if (is_null($role = $this->User_model->get_user_role($user_id, $company_id))) {
                $this->User_model->add_user_permission($company_id, $user_id, 'is_employee', $add_default_permissions = true);
            }

            return $user_id;

        }

    }

    function _email_employee_activation($employee_email)
    {
        //sets password key for setting password
        $data = $this->tank_auth->forgot_password($employee_email);

        //echo "employee activation";
        // Send email with password activation link
        
        $whitelabelinfo = $this->ci->ci->session->userdata('white_label_information');
        if($whitelabelinfo && isset($whitelabelinfo['name']) && $whitelabelinfo['name']){
            $data['partner_name'] = $whitelabelinfo['name'];
        } else {
            $data['partner_name'] = 'Minical';
        }
        $this->_send_email('register_employee', $employee_email, $data);
    }

    /**
     * Send email message of given type (activate, forgot_password, etc.)
     *
     * @param    string
     * @param    string
     * @param    array
     * @return    void
     */
    function _send_email($type, $email, &$data)
    {
        echo "_send_email to ". $email;

        $whitelabelinfo = $this->session->userdata('white_label_information');

        $from_email = isset($whitelabelinfo['support_email']) && $whitelabelinfo['support_email'] ? $whitelabelinfo['support_email'] : $this->config->item('webmaster_email', 'tank_auth');
        
        $from_name = isset($whitelabelinfo['name']) && $whitelabelinfo['name'] ? $whitelabelinfo['name'] : $this->config->item('website_name', 'tank_auth');

        $reply_to_email = isset($whitelabelinfo['support_email']) && $whitelabelinfo['support_email'] ? $whitelabelinfo['support_email'] : $this->config->item('webmaster_email', 'tank_auth');
        
        $reply_to_name = isset($whitelabelinfo['name']) && $whitelabelinfo['name'] ? $whitelabelinfo['name'] : $this->config->item('website_name', 'tank_auth');

        $data['support_email'] = $from_email;
        $this->email->from($from_email, $from_name);
        $this->email->reply_to($reply_to_email, $reply_to_name);
        $this->email->to($email);
        $this->email->subject('Activate Your '.$data["partner_name"].' Account'); //only english supported with this
        $this->email->message($this->load->view('email/'.$type.'-html', $data, true));
        $this->email->set_alt_message($this->load->view('email/'.$type.'-txt', $data, true));
        $this->email->send();
    }

    function employee_auto_logout_settings()
    {
        $update_data['employee_auto_logout_is_enabled'] =
            ($this->input->post('employee_auto_logout_is_enabled') == 'on') ? '1' : '0';
        //echo $update_data['employee_auto_logout_is_enabled'] ;
        $this->Company_model->update_company($this->company_id, $update_data);

        redirect('/settings/company/employees');
    }

    function remove_employee_access()
    {
        $user_id = $this->input->post('user_id');

        $this->User_model->remove_all_user_permissions($this->company_id, $user_id);
        $user_detail = $this->User_model->get_user_profile($user_id);

        $data['isSuccess'] = true;
        $data['message']   = 'Employee removed';

        $this->_create_employee_log("User '{$user_detail['first_name']}' removed");

        echo json_encode($data);
    }

    function re_email()
    {
        $this->_email_employee_activation($this->input->post('email'));
    }

    function create_booking_invoice_Ajax()
    {
        echo $this->Company_model->create_booking_invoice($this->input->post());
    }

    function update_users_AJAX(){
        $user_id = $this->input->post('user_id');
        $user_name = $this->input->post('user_name');
        $user_email = $this->input->post('user_email');
        $new_user_role = $this->input->post('new_user_role');
        if($new_user_role)
        {
            $this->User_model->remove_all_user_permissions($this->company_id, $user_id);
            
            if($new_user_role == 'is_owner'){
                $this->User_model->add_user_permission($this->company_id, $user_id, 'is_admin');
            }
            
            $this->User_model->add_user_permission($this->company_id, $user_id, $new_user_role);

            $user_detail = $this->User_model->get_user_profile($user_id);
            $this->_create_employee_log("Change '$new_user_role' role for user '{$user_detail['first_name']}'");
        }
        elseif($user_email && $user_name)
        {
            $user_extract = explode(' ', $user_name);
            $data['first_name'] = isset($user_extract[0]) ? $user_extract[0] : '';
            $data['last_name'] = isset($user_extract[1]) ? $user_extract[1] : '';
            $data_email['email'] = isset($user_email) ? $user_email : '';
            $this->User_model->update_user_profile($user_id, $data); // update user's first name and last name 
            $this->User_model->update_user($user_id, $data_email); // update user email 
            $this->_create_employee_log("Change name/email for user '{$user_name}'");
        }

    }

    function _create_employee_log($log) {
        $log_detail =  array(
            "user_id" => $this->user_id,
            "selling_date" => $this->selling_date,
            "date_time" => gmdate('Y-m-d H:i:s'),
            "log" => $log,
        );


        $this->Employee_log_model->insert_log($log_detail);
    }

    function accounting() {
        $view_data['company_data'] = $this->Company_model->get_company($this->company_id);
        $view_data['js_files'] = array(base_url() . auto_version('js/hotel-settings/online-settings.js'));
        $view_data['selected_sidebar_link'] = 'Accounting';
        $view_data['main_content'] = 'hotel_settings/feature_settings/accounting';
        $this->load->view('includes/bootstrapped_template', $view_data);
    }

    function no_show() {
        $view_data['company_data'] = $this->Company_model->get_company($this->company_id);
        $view_data['js_files'] = array(base_url() . auto_version('js/hotel-settings/online-settings.js'));
        $view_data['selected_sidebar_link'] = 'Enable Auto No Show';
        $view_data['main_content'] = 'hotel_settings/feature_settings/no_show';
        $this->load->view('includes/bootstrapped_template', $view_data);
    }

    function tooltip() {
        $view_data['company_data'] = $this->Company_model->get_company($this->company_id);
        $view_data['js_files'] = array(base_url() . auto_version('js/hotel-settings/online-settings.js'));
        $view_data['selected_sidebar_link'] = 'Tooltip';
        $view_data['main_content'] = 'hotel_settings/feature_settings/tooltip';
        $this->load->view('includes/bootstrapped_template', $view_data);
    }

    function switch_booking_modal() {
        $view_data['company_data'] = $this->Company_model->get_company($this->company_id);
        $view_data['js_files'] = array(base_url() . auto_version('js/hotel-settings/online-settings.js'));
        $view_data['selected_sidebar_link'] = 'Switch to Old Booking Modal';
        $view_data['main_content'] = 'hotel_settings/feature_settings/switch_booking_modal';
        $this->load->view('includes/bootstrapped_template', $view_data);
    }

    function update_display_tooltip_AJAX()
    {
        if ($this->input->post()) {
            $company_data = array(
                'is_display_tooltip' => $this->input->post('is_display_tooltip'),
            );
            $this->Company_model->update_company($this->company_id, $company_data);
            echo json_encode(array('status' => true));
            return;
        }
        echo json_encode(array('status' => false));
    }

    function update_total_balance_include_forecast_AJAX()
    {
        if ($this->input->post()) {
            $company_data = array(
                'is_total_balance_include_forecast' => $this->input->post('is_total_balance_include_forecast'),
            );
            $this->Company_model->update_company($this->company_id, $company_data);
            echo json_encode(array('status' => true));
            return;
        }
        echo json_encode(array('status' => false));
    }


    function update_no_show_AJAX()
    {
        if ($this->input->post()) {
            $company_data = array(
                'auto_no_show' => $this->input->post('auto_no_show'),
            );
            $this->Company_model->update_company($this->company_id, $company_data);
            echo json_encode(array('status' => true));
            return;
        }
        echo json_encode(array('status' => false));
    }

    function get_features_AJAX()
    {
        $faetures = $this->Company_model->get_company($this->company_id);
        $required_features = array(
            "hide_decimal_places" => isset($faetures["hide_decimal_places"]) ? $faetures["hide_decimal_places"] : "",
            "make_guest_field_mandatory" => isset($faetures["make_guest_field_mandatory"]) ? $faetures["make_guest_field_mandatory"] : "",
            "allow_change_previous_booking_status" => isset($faetures["allow_change_previous_booking_status"]) ? $faetures["allow_change_previous_booking_status"] : 0
        );
        echo json_encode($required_features);
    }

    function insert_company_api_key()
    {
        $api_key = $this->input->post('api_key');
        $response = $this->Company_model->insert_company_api_key($this->company_id, $api_key);
        echo json_encode(array('success' => true, 'response' => $response));
    }

    function update_features_AJAX()
    {
        if ($this->input->post()) {
            $company_data = array(
                'is_total_balance_include_forecast' => $this->input->post('is_total_balance_include_forecast'),
                'ui_theme' => $this->input->post('ui_theme'),
                'is_display_tooltip' => $this->input->post('is_display_tooltip'),
                'auto_no_show' => $this->input->post('auto_no_show'),
                'book_over_unconfirmed_reservations' => $this->input->post('book_over_unconfirmed_reservations'),
                'send_invoice_email_automatically' => $this->input->post('send_invoice_email_automatically'),
                'ask_for_review_in_invoice_email' => $this->input->post('ask_for_review_in_invoice_email'),
                'hide_decimal_places' => $this->input->post('hide_decimal_places'),
                'redirect_to_trip_advisor' => $this->input->post('redirect_to_trip_advisor'),
                'tripadvisor_link' => trim($this->input->post('tripadvisor_link')),
                'automatic_email_confirmation' => $this->input->post('automatic_email_confirmation'),
                'automatic_email_cancellation' => $this->input->post('automatic_email_cancellation'),
                'send_booking_notes' => $this->input->post('send_booking_notes'),
                'email_confirmation_for_ota_reservations' => $this->input->post('email_confirmation_for_ota_reservations'),
                'email_cancellation_for_ota_reservations' => $this->input->post('email_cancellation_for_ota_reservations'),
                'allow_non_continuous_bookings' => $this->input->post('allow_non_continuous_bookings'),
                'maximum_no_of_blocks' => $this->input->post('maximum_no_of_blocks'),
                'make_guest_field_mandatory' => $this->input->post('make_guest_field_mandatory'),
                'manual_payment_capture' => $this->input->post('payment_capture'),
                'include_cancelled_noshow_bookings' => $this->input->post('include_cancelled_noshow_bookings'),
                'force_room_selection' => $this->input->post('force_room_selection'),
                'hide_forecast_charges' => $this->input->post('hide_forecast_charges'),
                'send_copy_to_additional_emails' => $this->input->post('send_copy_to_additional_emails'),
                'additional_company_emails' => $this->input->post('additional_company_emails'),
                'automatic_feedback_email' => $this->input->post('automatic_feedback_email'),
                'avoid_dmarc_blocking' => $this->input->post('avoid_dmarc_blocking'),
                'allow_free_bookings' => $this->input->post('allow_free_bookings'),
                'default_charge_name' => $this->input->post('default_charge_name'),
                'default_room_singular' => $this->input->post('default_room_singular'),
                'default_room_plural' => $this->input->post('default_room_plural'),
                'default_room_type' => $this->input->post('default_room_type'),
                'date_format' => $this->input->post('date_format'),
                'default_checkin_time' => $this->input->post('default_checkin_time'),
                'default_checkout_time' => $this->input->post('default_checkout_time'),
                'enable_hourly_booking' => $this->input->post('enable_hourly_booking'),
                'enable_api_access' => $this->input->post('enable_api_access'),
                'customer_modify_booking' => $this->input->post('customer_modify_booking'),
                'booking_cancelled_with_balance' => $this->input->post('booking_cancelled_with_balance'),
                'enable_new_calendar' => $this->input->post('enable_new_calendar'),
                'hide_room_name' => $this->input->post('hide_room_name'),
                'restrict_booking_dates_modification' => $this->input->post('restrict_booking_dates_modification'),
                'restrict_checkout_with_balance' => $this->input->post('restrict_checkout_with_balance'),
                'show_guest_group_invoice' => $this->input->post('show_guest_group_invoice'),
                'restrict_cvc_not_mandatory' => $this->input->post('restrict_cvc_not_mandatory'),
                'calendar_days' => $this->input->post('calendar_days'),
                'restrict_edit_after_checkout' => $this->input->post('restrict_edit_after_checkout'),
                'allow_change_previous_booking_status' => $this->input->post('allow_change_previous_booking_status')
            );
            $this->Company_model->update_company($this->company_id, $company_data);
            $this->_create_employee_log("Feature settings updated");
            echo json_encode(array('status' => true));
            return;
        }
        echo json_encode(array('status' => false));
    }

    function update_api_AJAX()
    {
        if ($this->input->post()) {
            $company_data = array(
                'enable_api_access' => $this->input->post('enable_api_access')
            );
            $this->Company_model->update_company($this->company_id, $company_data);
            $this->_create_employee_log("API settings updated");
            echo json_encode(array('status' => true));
            return;
        }
        echo json_encode(array('status' => false));
    }

    function turn_on_off()
    {
        $this->load->model('Whitelabel_partner_model');
        $view_data['company_data'] = $this->Company_model->get_company($this->company_id);
        //$view_data['company_api_key'] = $this->Company_model->get_company_api_permission($this->company_id);
        
        if(!$view_data['company_data']['calendar_days']) {
            $width = isset($_COOKIE['width']) && $_COOKIE['width'] ? $_COOKIE['width'] : 1200;
            $days_before_today = intval(round($width / 400));
            $days_after_today = intval(round($width / 60));

            $total_days = intval($days_before_today + $days_after_today);
            $view_data['company_data']['calendar_days'] = $total_days;
        }

        $company_partner_id = isset($this->company_partner_id) && $this->company_partner_id ? $this->company_partner_id : 1;
        $view_data['partner'] = $this->Whitelabel_partner_model->get_partner_detail($company_partner_id);
        $view_data['feature_setting_enabled'] = $this->company_subscription_level == ELITE ? true : false;
        $view_data['js_files'] = array(
            base_url() . 'js/hotel-settings/email-settings.js',
            base_url() . auto_version('js/hotel-settings/online-settings.js')
        );
        $view_data['selected_sidebar_link'] = 'Feature Settings';
        $view_data['main_content'] = 'hotel_settings/feature_settings/turn_on_off';
        $this->load->view('includes/bootstrapped_template', $view_data);
    }

    function feature_settings() {
        $this->turn_on_off();
    }

    function import()
    {
        $data['company_ID'] = $this->company_id;
        $data['selected_sidebar_link'] = 'Import';
        $data['main_content']          = 'hotel_settings/company/import';
        $this->load->view('includes/bootstrapped_template', $data);
    }

    function import_company_data(){

        if($this->input->post('removd_old_data') == 1){

            $get_bookings = $this->Booking_model->get_bookings_company($this->company_id);

            if($get_bookings){
                $delete_booking_ids = array();
                foreach ($get_bookings as $key => $booking) {
                    $delete_booking_ids[] = $booking['booking_id'];
                }

                for ($i = 0, $total = count($delete_booking_ids); $i < $total; $i = $i + 500)
                {
                    $delete_booking_batch = array_slice($delete_booking_ids, $i, 500);

                    $this->Charge_model->delete_charges($delete_booking_ids, true);
                    $this->Payment_model->delete_payments($delete_booking_ids, true);

                    if ($this->db->_error_message())
                    {
                        show_error($this->db->_error_message());
                    }
                }

                $this->Booking_model->delete_bookings($this->company_id);
            }
            $this->Booking_source_model->delete_booking_sources($this->company_id);
            $this->Booking_field_model->delete_booking_fields($this->company_id);
            $this->Customer_type_model->delete_customer_types($this->company_id);
            $this->Customer_field_model->delete_customer_fields($this->company_id);
            $this->Customer_model->delete_customers($this->company_id);
            $this->Payment_model->delete_payment_types($this->company_id);
            $this->Charge_type_model->delete_charge_types($this->company_id);
            $this->Room_type_model->delete_room_types($this->company_id);
            $this->Room_model->delete_rooms($this->company_id);
            $this->Tax_model->delete_tax_types($this->company_id);
            $this->Rate_plan_model->delete_rate_plans($this->company_id);
            $this->Import_mapping_model->delete_mapping_field($this->company_id);
            $this->Extra_model->delete_extras($this->company_id);
            // $this->Rate_model->delete_rates($this->company_id);
        }

        $this->import_functionality();

    }

    function import_functionality(){

        if($_FILES['file']['name'] != '')
        {
            $file_name = $_FILES['file']['name'];
            $array = explode(".", $file_name);
            $name = $array[0];
            $ext = $array[1];
            if($ext == 'zip')
            {
                $path = $_SERVER["DOCUMENT_ROOT"].'/upload/';
                $location = $path . $file_name;
                $result=array();
                if(move_uploaded_file($_FILES['file']['tmp_name'], $location))
                {
                    $zip = new ZipArchive;
                    if($zip->open($location))
                    {
                        $zip->extractTo($path);
                        $zip->close();
                    }
                    $files = scandir($path);

                    foreach($files as $file)
                    {

                        if($file === '.' || $file === '..') continue;

                        $file_ext = explode(".", $file);

                        $allowed_ext = array('csv');
                        $ext_allowed = array('json');

                        if(isset($file_ext[1]) && in_array($file_ext[1], $allowed_ext))
                        {

                            if (!($fp = fopen($path.$file, 'r'))) {
                                die("Can't open file...");
                            }
                            //read csv headers
                            $key = fgetcsv($fp,"1024",",");
                            $zip_name = explode(".",$file);


                            // parse csv rows into array
                            $json = array();
                            while ($row = fgetcsv($fp,"1024",",")) {

                                if (count($row) === count($key)) {
                                    $json[] = array_combine($key, $row);
                                } else {
                                    $new_result = array();
                                    foreach ($key as $i => $k) {
                                        $new_result[$k] = isset($row[$i]) ? $row[$i] : '';
                                    }
                                    $json[] = $new_result;
                                }
                            }
                            $result[$zip_name[0]] = $json;
                            // release file handle
                            fclose($fp);
                            unlink($path.'/'.$file);
                        }

                        if(isset($file_ext[1]) && in_array($file_ext[1], $ext_allowed)){
                            $fp = fopen($path.$file, 'r');
                            $setting = fgets($fp);
                            $result['settings'] = $setting;

                            fclose($fp);
                            unlink($path.'/'.$file);

                        }
                    }

                    $csv_data = $result;

                    if (isset($csv_data['rooms'])) {
                        $this->import_rooms_csv($csv_data['rooms'],$csv_data['settings']);
                    }
                    if (isset($csv_data['taxes'])) {
                        $this->import_taxes_csv($csv_data['taxes']);
                    }
                    if (isset($csv_data['customers'])) {
                        $this->import_customers_csv($csv_data['customers']);
                    }
                    if (isset($csv_data['charges'])) {
                        $this->import_charges_csv($csv_data['charges']);
                    }
                    if (isset($csv_data['rates'])) {
                        $this->import_rates_csv($csv_data['rates']);
                    }
                    if (isset($csv_data['bookings'])) {
                        $this->import_bookings_csv($csv_data['bookings']);
                    }
                    if (isset($csv_data['extras'])) {
                        $this->import_extras_csv($csv_data['extras']);
                    }
                    if (isset($csv_data['payments'])) {
                        $this->import_payments_csv($csv_data['payments']);
                    }
                    if (isset($csv_data['statements'])) {
                        $this->import_statements_csv($csv_data['statements']);
                    }
                    if (isset($csv_data['settings'])) {
                        $this->import_company_setting($csv_data['settings']);
                        update_customer_field($this->company_id);
                    }

                    unlink($location);

                    echo ("<script LANGUAGE='JavaScript'>
                            window.alert('Succesfully Imported');
                            window.location.href='".base_url()."';
                            </script>");
                }
            }
        }

        redirect('booking');
    }

    function import_rooms_csv($value, $setting){

        foreach ($value as $room) {

            // for get room type id
            $get_room_type = null;
            if(
                isset($this->cache_values['Room Type'][$room['Room Type Id']]) && 
                    $this->cache_values['Room Type'][$room['Room Type Id']]
            ){
                $get_room_type = $this->cache_values['Room Type'][$room['Room Type Id']];
            } 

            if (empty($get_room_type)) {
                $data = array(
                    'company_id' => $this->company_id,
                    'name' => $room['Room Type Name'] == '' ? null : $room['Room Type Name'],
                    'acronym' => $room['Acronym'] == ''? null : $room['Acronym'] ,
                    'max_adults' => $room['Max Adults'] == ''  ? 0 : $room['Max Adults'] ,
                    'max_children' => $room['Max Children'] == ''  ? 0 : $room['Max Children'] ,
                    'max_occupancy' => $room['Max Occupancy'] == ''  ? 0 : $room['Max Occupancy'] ,
                    'min_occupancy' => $room['Min Occupancy'] == ''  ? 0 : $room['Min Occupancy'] ,
                    'can_be_sold_online' => $room['Room Type Can be Sold online'] == 'true' ? 1 : 0,
                    'default_room_charge' => $room['Room Charge'],
                    'description' => $room['Description']

                );

                $room_type_id = $this->Room_type_model->add_new_room_type($data);
                $data_import_mapping = Array(
                    "new_id" => $room_type_id,
                    "old_id" => $room['Room Type Id'],
                    "company_id" => $this->company_id,
                    "type" => "room_type"
                );

                $this->import_insert_batch[] = $data_import_mapping;
                $this->cache_values['Room Type'][$room['Room Type Id']] = $data_import_mapping;
            } else {
                $room_type_id = isset($get_room_type['new_id']) ? $get_room_type['new_id'] : '';
            }

            if(!empty($room['Room Id'])){

                // for get room id
                $get_room = null;
                if(
                    isset($this->cache_values['Room'][$room['Room Id']]) && 
                        $this->cache_values['Room'][$room['Room Id']]
                ){
                    $get_room = $this->cache_values['Room'][$room['Room Id']];
                } 

                if(empty($get_room)){
                    $sold_online = $room['Room Can be Sold online'] == 'true' ? 1 : 0 ;
                    $sort_order = isset($room['Sort Order']) && $room['Sort Order'] != '' && $room['Sort Order'] != null ? $room['Sort Order'] : 0 ;

                    $room_id = $this->Room_model->create_rooms(
                        $this->company_id,
                        $room['Room Name'],
                        $room_type_id,
                        $sort_order,
                        $sold_online,
                        $room['Status']
                    );

                    $data_import_mapping = Array(
                        "new_id" => $room_id,
                        "old_id" => $room['Room Id'],
                        "company_id" => $this->company_id,
                        "type" => "room"
                    );

                    $this->import_insert_batch[] = $data_import_mapping;
                    $this->cache_values['Room'][$room['Room Id']] = $data_import_mapping;
                }
            }

            $settings = json_decode($setting,true);

            $extra_room_types = $settings['Room Types'];

            if($extra_room_types){

                foreach ($extra_room_types as $key => $rt) {

                    // for extra room type
                    $extra_room_type = null;
                    if(
                        isset($this->cache_values['Room Type'][$rt['id']]) && 
                            $this->cache_values['Room Type'][$rt['id']]
                    ){
                        $extra_room_type = $this->cache_values['Room Type'][$rt['id']];
                    } 

                    if(empty($extra_room_type)) {
                        $extra_data = array(
                            'company_id' => $this->company_id,
                            'name' => $rt['name'] == '' ? null : $rt['name'],
                            'acronym' => $rt['acronym'] == ''? null : $rt['acronym'] ,
                            'max_adults' => $rt['max_adults'] == ''  ? 0 : $rt['max_adults'] ,
                            'max_children' => $rt['max_children'] == ''  ? 0 : $rt['max_children'] ,
                            'max_occupancy' => $rt['max_occupancy'] == ''  ? 0 : $rt['max_occupancy'] ,
                            'min_occupancy' => $rt['min_occupancy'] == ''  ? 0 : $rt['min_occupancy'] ,
                            'can_be_sold_online' => $rt['can_be_sold_online'] == 'true' ? 1 : 0,
                            'default_room_charge' => $rt['default_room_charge'],
                            'description' => $rt['description']
                        );

                        $extra_room_type_id = $this->Room_type_model->add_new_room_type($extra_data);
                        $extra_data_import_mapping = Array(
                            "new_id" => $extra_room_type_id,
                            "old_id" => $rt['id'],
                            "company_id" => $this->company_id,
                            "type" => "room_type"
                        );

                        $this->import_insert_batch[] = $extra_data_import_mapping;
                        $this->cache_values['Room Type'][$rt['id']] = $extra_data_import_mapping;
                    }
                }
            }
        }
    }

    function import_taxes_csv($value){

        foreach ($value as $tax) {

            $get_tax_type = null;
            if(
                isset($this->cache_values['Tax'][$tax['Tax Type Id']]) && 
                    $this->cache_values['Tax'][$tax['Tax Type Id']]
            ){
                $get_tax_type = $this->cache_values['Tax'][$tax['Tax Type Id']];
            } 

            if(empty($get_tax_type)){

                $data = array(
                    "tax_type" => $tax['Tax Type'] == '' ? null : $tax['Tax Type'],
                    "tax_rate" => $tax['Tax Rate'] == '' ? 0 : $tax['Tax Rate'],
                    "company_id" => $this->company_id ,
                    "is_percentage" => $tax['Is Percentage'] == 'true' ? 1 : 0,
                    "is_brackets_active" => $tax['Bracket Active'] == 'true' ? 1 : 0,
                    "is_tax_inclusive" => $tax['Is Tax Inclusive'] == 'true' ? 1 : 0
                );

                $new_taxes = $this->Tax_model->create_new_tax_type($data);

                if($tax['Bracket Active'] == 'true'){
                    $price_bracket = json_decode($tax['Price Bracket'],true);

                    foreach ($price_bracket as $price) {
                        $price_brackets = array(
                            "tax_type_id" => $new_taxes,
                            "start_range" => $price['start'],
                            "end_range" =>$price['end'],
                            "tax_rate" =>$price['rate'],
                            "is_percentage" =>$price['is_percentage']
                        );
                        $this->Tax_price_bracket_model->create_price_bracket($price_brackets);
                    }
                }

                $data_import_mapping = Array(
                    "new_id" => $new_taxes,
                    "old_id" => $tax['Tax Type Id'],
                    "company_id" => $this->company_id,
                    "type" => "tax_type"
                );

                $this->import_insert_batch[] = $data_import_mapping;
                $this->cache_values['Tax'][$tax['Tax Type Id']] = $data_import_mapping;
            }
        }
    }

    function import_charges_csv($value)
    {
        $cache_tax_type_id = array();
        $cache_charge_taxes = array();
        $new_charge_type_ids = array();
        $old_charge_type_ids = array();
        $cache_charge_data = array();
        $old_charge_ids = array();

        foreach ($value as $charge) {

            $get_the_charge_type = null;
            if(
                isset($this->cache_values['Charge Type'][$charge['Charge Type Id']]) && 
                    $this->cache_values['Charge Type'][$charge['Charge Type Id']]
            ){
                $get_the_charge_type = $this->cache_values['Charge Type'][$charge['Charge Type Id']];
            } 

            if(empty($get_the_charge_type)){

                $data = array (
                    'name' => $charge['Charge Type'],
                    'company_id' => $this->company_id,
                    'is_room_charge_type' => $charge['Room Charge Type'] == 'true' ? 1 : 0,
                    'is_tax_exempt' => $charge['Tax Exempt'] == 'true' ? 1 : 0,
                    'is_default_room_charge_type' => $charge['Default Room Charge'] == 'true' ? 1 : 0,
                );

                $charge_type_id = $this->Charge_type_model->create_charge_types($data);

                $data_import_mapping = Array(
                    "new_id" => $charge_type_id,
                    "old_id" => $charge['Charge Type Id'],
                    "company_id" => $this->company_id,
                    "type" => "charge_type"
                );

                $this->import_insert_batch[] = $data_import_mapping;
                $this->cache_values['Charge Type'][$charge['Charge Type Id']] = $data_import_mapping;

                $taxes = explode(',', $charge['Tax Type']);

                foreach ($taxes as $tax_type) {
                    if($tax_type){

                        if(
                            isset($cache_tax_type_id[$tax_type]) && 
                                $cache_tax_type_id[$tax_type]
                        ){
                            $tax_type_id = $cache_tax_type_id[$tax_type];
                        } else {
                            $tax_type_id = $this->Tax_model->get_tax_type_by_name($tax_type);
                            $cache_tax_type_id[$tax_type] = $tax_type_id;
                        }

                        if(
                            isset($cache_charge_taxes[$charge_type_id.'-'.$tax_type_id]) && 
                                $cache_charge_taxes[$charge_type_id.'-'.$tax_type_id]
                        ){
                            $charge_taxes = $cache_charge_taxes[$charge_type_id.'-'.$tax_type_id];
                        } else {
                            $charge_taxes = $this->Charge_type_model->get_charge_tax($charge_type_id, $tax_type_id);
                            $cache_charge_taxes[$charge_type_id.'-'.$tax_type_id] = $charge_taxes;
                        }
                        
                        if(!$charge_taxes){
                            $this->Charge_type_model->add_charge_type_tax($charge_type_id, $tax_type_id);
                        }
                    }
                }
            } else {
                $charge_type_id = isset($get_the_charge_type['new_id']) ? $get_the_charge_type['new_id'] : 0 ;
            }

            $new_charge_type_ids[] = $charge_type_id;
            $old_charge_type_ids[] = $charge['Charge Type Id'];

            $customer_id = null;
            if(
                isset($this->cache_values['Customer'][$charge['Customer Id']]) && 
                    $this->cache_values['Customer'][$charge['Customer Id']]
            ){
                $customer_id = $this->cache_values['Customer'][$charge['Customer Id']];
            } 

            if(!empty($charge['Charge Id'])) {

                $charges = null;
                if(
                    isset($this->cache_values['Charge'][$charge['Charge Id']]) && 
                        $this->cache_values['Charge'][$charge['Charge Id']]
                ){
                    $charges = $this->cache_values['Charge'][$charge['Charge Id']];
                } 

                if(empty($charges))
                {
                    switch ($charge['Pay Period']) {
                        case "Daily" : $pay_period = '0'; break;
                        case "Weekly" : $pay_period = '1'; break;
                        case "Monthly" : $pay_period = '2'; break;
                        case "One time" : $pay_period = '3'; break;
                    }

                    $cache_charge_data[] = Array(
                        "description" => $charge['Description'] == '' ? null : $charge['Description'],
                        "date_time" =>$charge['Date Time'] != null ? $charge['Date Time'] : date('Y-m-d H:i:s') ,
                        "booking_id" => $charge['Booking Id'],
                        "amount" => $charge['Amount'] == '' ? 0 : $charge['Amount'],
                        "charge_type_id" => $charge_type_id,
                        "selling_date" => $charge['Selling Date'],
                        "customer_id" => isset($customer_id['new_id']) && $customer_id['new_id'] ? $customer_id['new_id'] : null,
                        "pay_period" => $pay_period,
                        "is_night_audit_charge" => $charge['Night Audit Charge'] == 'true' ? 1 : 0
                    );

                    $old_charge_ids[] = $charge['Charge Id'];
                }
            }
        }

        $new_charge_type_ids = array_values(array_unique($new_charge_type_ids));
        $old_charge_type_ids = array_values(array_unique($old_charge_type_ids));

        $this->Room_type_model->update_charge_type_room_types($old_charge_type_ids, $new_charge_type_ids);

        // for($i = 0; $i < count($new_charge_type_ids); $i++) {
        //     $room_charge_type = $this->Room_type_model->update_room_charge_type($new_charge_type_ids[$i], $old_charge_type_ids[$i]);
        // }

        $first_charge_ids = array();
        $all_charge_ids = array();

        for ($i = 0, $total = count($cache_charge_data); $i < $total; $i = $i + 100)
        {
            $import_charge_batch = array_slice($cache_charge_data, $i, 100);

            $this->db->insert_batch("charge", $import_charge_batch);
            $first_charge_ids[] = $this->db->insert_id();

            if ($this->db->_error_message())
            {
                show_error($this->db->_error_message());
            }
        }


        foreach($first_charge_ids as $k => $val) {
            for($a = 0; $a < 100; $a++) {
                $all_charge_ids[] = $val++;
            }
        }

        for($j = 0; $j < count($cache_charge_data); $j++){
            if(isset($old_charge_ids[$j]) && $old_charge_ids[$j]) {
                $data_import_mapping = Array(
                    "new_id" => $all_charge_ids[$j],
                    "old_id" => $old_charge_ids[$j],
                    "company_id" => $this->company_id,
                    "type" => "charge"
                );
                $this->import_insert_batch[] = $data_import_mapping;
                $this->cache_values['Charge'][$old_charge_ids[$j]] = $data_import_mapping;
            }
        }
    }

    function import_rates_csv($value)
    {
        $rate_ids = $cache_rate_array = $cache_date_range_array = $new_rate_plan_ids = array();
        $new_rate_plan_ids = $old_rate_plan_ids = $cache_rate_plan_data = array();
        $cache_rate_plan_array_count = $rate_plan_ids = $cache_image_group_data = array();
        $rate_plan_count = $room_types = array();

        foreach ($value as $rate) {

            $rate_ids[] = $rate['Rate Id'];

            $room_type = null;
            if(
                isset($this->cache_values['Room Type'][$rate['Room type Id']]) && 
                    $this->cache_values['Room Type'][$rate['Room type Id']]
            ){
                $room_type = $this->cache_values['Room Type'][$rate['Room type Id']];
            } 

            $charge_type_id = null;
            if(
                isset($this->cache_values['Charge Type'][$rate['Charge Type']]) && 
                    $this->cache_values['Charge Type'][$rate['Charge Type']]
            ){
                $charge_type_id = $this->cache_values['Charge Type'][$rate['Charge Type']];
            } 

            $room_type_id = isset($room_type['new_id']) && $room_type['new_id'] ? $room_type['new_id'] : null;

            if(empty($room_type_id) && !in_array($rate['Room Type Name'], $room_types)) {
                $room_type = $this->Room_type_model->get_room_type_name($rate['Room Type Name'] , $this->company_id);

                $room_types[] = $rate['Room Type Name'];

                $room_type_id = isset($room_type[0]['id']) && $room_type[0]['id'] ? $room_type[0]['id'] : null;
            }

            $get_import_rate_plan = null;

            if(empty($get_import_rate_plan)) {

                if(isset($rate['Rate Plan Id']) && $rate['Rate Plan Id']) {

                    $cache_rate_plan_array_count[$rate['Rate Plan Id']][] = array(
                    "rate_plan_name" => $rate['Name'] == '' ? null : $rate['Name'],
                    "room_type_id" => $room_type_id,
                    "company_id" => $this->company_id,
                    "is_selectable" => $rate['Read Only'] == 'true' ? 1 : 0,
                    "charge_type_id" => isset($charge_type_id['new_id']) && $charge_type_id['new_id'] ? $charge_type_id['new_id'] : null,
                    "description" => $rate['Description']? $rate['Description'] : "",
                    "currency_id" => $rate['Currency'] ? $rate['Currency'] : null
                );

                $cache_rate_plan_data[] = array(
                        "old_rate_plan_id" => $rate['Rate Plan Id'],
                    "rate_plan_name" => $rate['Name'] == '' ? null : $rate['Name'],
                    "room_type_id" => $room_type_id,
                    "company_id" => $this->company_id,
                    "is_selectable" => $rate['Read Only'] == 'true' ? 1 : 0,
                    "charge_type_id" => isset($charge_type_id['new_id']) && $charge_type_id['new_id'] ? $charge_type_id['new_id'] : null,
                    "description" => $rate['Description']? $rate['Description'] : "",
                    "currency_id" => $rate['Currency'] ? $rate['Currency'] : null
                );

                $old_rate_plan_ids[] = $rate['Rate Plan Id'];
            }

            }
        }

        foreach($cache_rate_plan_array_count as $key => $val) {
            $rate_plan_count[$key] = count($cache_rate_plan_array_count[$key]);
        }

        $cache_rate_plan_data = array_values(array_map("unserialize", array_unique(array_map("serialize", $cache_rate_plan_data))));
        $old_rate_plan_ids = array_values(array_unique($old_rate_plan_ids));

        foreach ($cache_rate_plan_data as $key => $val) {
            unset($cache_rate_plan_data[$key]['old_rate_plan_id']);
        }

        for($i = 0; $i < count($cache_rate_plan_data); $i++) {
            $cache_image_group_data[] = Array("image_type_id" => RATE_PLAN_IMAGE_TYPE_ID);
        }

        $rate_plan_ids_array = $this->_create_rate_plan($cache_rate_plan_data, $old_rate_plan_ids, $cache_image_group_data, $rate_plan_count);

        $inserted_rate_plan_ids = $rate_plan_ids_array['inserted_rate_plan_ids'];
        $new_rate_plan_ids = $rate_plan_ids_array['new_rate_plan_ids'];

        foreach ($value as $key => $rate) {

            if(
                isset($rate['Rate Plan Id']) && 
                $rate['Rate Plan Id']
            ) {

                $rt_plan_id = $inserted_rate_plan_ids[$rate['Rate Plan Id']][0];

                $cache_rate_array[] = Array(
                    'rate_plan_id' => $rt_plan_id,
                        'base_rate' => $rate['Base Rate'] == '' ? null : $rate['Base Rate'],
                        'adult_1_rate' => $rate['Adult Rate 1'] ? $rate['Adult Rate 1'] : null,
                        'adult_2_rate' => $rate['Adult Rate 2'] ? $rate['Adult Rate 2'] : null,
                        'adult_3_rate' => $rate['Adult Rate 3'] ? $rate['Adult Rate 3'] : null,
                        'adult_4_rate' => $rate['Adult Rate 4'] ? $rate['Adult Rate 4'] : null,
                        'additional_adult_rate' => $rate['Additional Adult Rate'] ? $rate['Additional Adult Rate'] : null,
                        'additional_child_rate' => $rate['Aditional Child Rate'] ? $rate['Aditional Child Rate'] : null,
                        'minimum_length_of_stay' => $rate['Min Length of Stay'] ? $rate['Min Length of Stay'] : null,
                        'maximum_length_of_stay' => $rate['Max Length of Stay'] ? $rate['Max Length of Stay'] : null,
                        'closed_to_departure' => $rate['Close to Departure'] == 'true' ? 1 : 0,
                        'closed_to_arrival' => $rate['Close to Arrival'] == 'true' ? 1 : 0,
                        'can_be_sold_online' => $rate['Can be sold online'] == 'true' ? 1 : 0
                    );

                $cache_date_range_array[] = Array(
                        'date_start' => $rate['From Date'] == '' ? null : $rate['From Date'],
                        'date_end' => $rate['To Date'] == '' ? null : $rate['To Date'],
                        'monday' => $rate['Monday'] == '' ? null : $rate['Monday'],
                        'tuesday' => $rate['Tuesday'] == '' ? null : $rate['Tuesday'],
                        'wednesday' => $rate['Wednesday'] == '' ? null : $rate['Wednesday'],
                        'thursday' => $rate['Thursday'] == '' ? null : $rate['Thursday'],
                        'friday' => $rate['Friday'] == '' ? null : $rate['Friday'],
                        'saturday' => $rate['Saturday'] == '' ? null : $rate['Saturday'],
                        'sunday' => $rate['Sunday']== '' ? null : $rate['Sunday']
                    );
            }
        }

        $new_rate_plan_ids = array_values(array_unique($new_rate_plan_ids));
        // $old_rate_plan_ids = array_values(array_unique($old_rate_plan_ids));

        $this->Rate_plan_model->update_rate_plan_room_types($old_rate_plan_ids, $new_rate_plan_ids);

        $first_rate_ids = $first_date_range_ids = $all_rate_ids = $all_date_range_ids = array();

        for ($i = 0, $total = count($cache_rate_array); $i < $total; $i = $i + 100)
        {
            $import_rate_batch = array_slice($cache_rate_array, $i, 100);
            $import_date_range_batch = array_slice($cache_date_range_array, $i, 100);

            $this->db->insert_batch("rate", $import_rate_batch);
            $first_rate_ids[] = $this->db->insert_id();

            $this->db->insert_batch("date_range", $import_date_range_batch);
            $first_date_range_ids[] = $this->db->insert_id();

            if ($this->db->_error_message())
            {
                show_error($this->db->_error_message());
            }
        }

        $rate_id_count = count($cache_rate_array);

        foreach($first_rate_ids as $k => $val) {
            
            if($rate_id_count > 100) {
            for($a = 0; $a < 100; $a++) {
                $all_rate_ids[] = $val++;
            }
            } else if($rate_id_count > 0 && $rate_id_count < 100) {
                for($a = 0; $a < $rate_id_count; $a++) {
                    $all_rate_ids[] = $val++;
                }
            }
            $rate_id_count = $rate_id_count - 100;
        }

        $date_range_id_count = count($cache_rate_array);

        foreach($first_date_range_ids as $k => $val) {
            
            if($date_range_id_count > 100) {
            for($a = 0; $a < 100; $a++) {
                $all_date_range_ids[] = $val++;
            }
            } else if($date_range_id_count > 0 && $date_range_id_count < 100) {
                for($a = 0; $a < $date_range_id_count; $a++) {
                    $all_date_range_ids[] = $val++;
                }
            }
            $date_range_id_count = $date_range_id_count - 100;
        }

        $date_range_x_rate_array = array();

        for($j = 0; $j < count($cache_rate_array); $j++){

            $date_range_x_rate_array[] = Array(
                'rate_id' => $all_rate_ids[$j],
                'date_range_id' => $all_date_range_ids[$j]
            );

            if(isset($rate_ids[$j]) && $rate_ids[$j]) {
                $data_import_mapping = Array(
                    "new_id" => $all_rate_ids[$j],
                    "old_id" => $rate_ids[$j],
                    "company_id" => $this->company_id,
                    "type" => "rate"
                );
                $this->import_insert_batch[] = $data_import_mapping;
            }
        }

        for ($i = 0, $total = count($cache_rate_array); $i < $total; $i = $i + 100)
        {
            $import_date_range_x_batch = array_slice($date_range_x_rate_array, $i, 100);

            $this->db->insert_batch("date_range_x_rate", $import_date_range_x_batch);
            if ($this->db->_error_message())
            {
                show_error($this->db->_error_message());
            }
        }
    }

    function _create_rate_plan($rate_plan_data, $old_rate_plan_ids, $cache_image_group_data, $rate_plan_count) {

        // insert image group ids
        $first_image_group_ids = array();
        $all_image_group_ids = array();
        $new_image_group_ids = array();

        for ($i = 0, $total = count($cache_image_group_data); $i < $total; $i = $i + 100)
        {
            $import_image_group_batch = array_slice($cache_image_group_data, $i, 100);

            $this->db->insert_batch("image_group", $import_image_group_batch);
            $first_image_group_ids[] = $this->db->insert_id();

            if ($this->db->_error_message())
            {
                show_error($this->db->_error_message());
            }
        }

        foreach($first_image_group_ids as $k => $val) {
            for($a = 0; $a < 100; $a++) {
                $all_image_group_ids[] = $val++;
            }
        }

        for($j = 0; $j < count($cache_image_group_data); $j++){
            $rate_plan_data[$j]['image_group_id'] = $all_image_group_ids[$j];
        }

        // insert rate plans
        $first_rate_plan_ids = array();
        $all_rate_plan_ids = array();
        $new_rate_plan_ids = array();
        for ($i = 0, $total = count($rate_plan_data); $i < $total; $i = $i + 100)
        {
            $import_rate_plan_batch = array_slice($rate_plan_data, $i, 100);

            $this->db->insert_batch("rate_plan", $import_rate_plan_batch);
            $first_rate_plan_ids[] = $this->db->insert_id();

            if ($this->db->_error_message())
            {
                show_error($this->db->_error_message());
            }
        }

        $count = count($rate_plan_data);

        foreach($first_rate_plan_ids as $k => $val) {
            
            if($count > 100) {
            for($a = 0; $a < 100; $a++) {
                $all_rate_plan_ids[] = $val++;
            }
            } else if($count > 0 && $count < 100) {
                for($a = 0; $a < $count; $a++) {
                    $all_rate_plan_ids[] = $val++;
                }
            }
            $count = $count - 100;
        }

        for($j = 0; $j < count($rate_plan_data); $j++){

            if(isset($old_rate_plan_ids[$j]) && $old_rate_plan_ids[$j]) {

                $data_import_mapping = Array(
                    "new_id" => $all_rate_plan_ids[$j],
                    "old_id" => $old_rate_plan_ids[$j],
                    "company_id" => $this->company_id,
                    "type" => "rate_plan"
                );

                $this->import_insert_batch[] = $data_import_mapping;
                $this->cache_values['Rate Plan'][$old_rate_plan_ids[$j]] = $data_import_mapping;
            }

            $new_rate_plan_ids[] = $all_rate_plan_ids[$j];
        }

        $inserted_rate_plan_ids = array();

        $k = 0;
        // prx($new_rate_plan_ids);
        foreach($rate_plan_count as $key => $value) {
            for($i = 0; $i < $value; $i++){
                $inserted_rate_plan_ids[$old_rate_plan_ids[$k]][] = $new_rate_plan_ids[$k];
            }
            $k++;
        }

        return array(
            'new_rate_plan_ids' => $new_rate_plan_ids,
            'inserted_rate_plan_ids' => $inserted_rate_plan_ids
        );
    }

    function import_customers_csv($value)
    {
        $cache_customer_type = array();
        $cache_exist_customer_field = array();
        $cache_customer_data = array();
        $old_customer_ids = $customer_types = array();

        $id = 0;

        foreach ($value as $customer) {

            $get_customer_type = null;

            if($customer['Customer Type'] == '-1') {
                $customer_type_id = '-1';
            } else if($customer['Customer Type'] == '-2') {
                $customer_type_id = '-2';
            } else {

                if(
                    isset($cache_customer_type[$customer['Customer Type']]) && 
                        $cache_customer_type[$customer['Customer Type']]
                ){
                    $get_customer_type = $cache_customer_type[$customer['Customer Type']];
                } else {
                    $get_customer_type = $this->Customer_type_model->get_customer_type_by_name($this->company_id, $customer['Customer Type']);
                    $cache_customer_type[$customer['Customer Type']] = $get_customer_type;
                }
                $customer_type_id = 0;
            if(empty($get_customer_type) && !in_array($customer['Customer Type'], $customer_types) && $customer['Customer Type'] != ''){
                $customer_type_id = $this->Customer_type_model->create_customer_type($this->company_id, $customer['Customer Type']);

                $customer_types[] = $customer['Customer Type'];
            } 
                else {
                    $customer_type_id = isset($get_customer_type[0]['id']) ? $get_customer_type[0]['id'] : ' ' ;
                }
            }

            $cache_customer_data[] = Array(
                "customer_name" => $customer['Customer Name'] == ''? null : $customer['Customer Name'],
                "address" => $customer['Address'] == ''? null : $customer['Address'],
                "email" => $customer['Email'] == ''? null : $customer['Email'],
                "city" => $customer['City'] == ''? null : $customer['City'],
                "region" => $customer['Region'] == ''? null : $customer['Region'],
                "phone" => $customer['Phone'] == ''? null : $customer['Phone'],
                "country" => $customer['Country'] == ''? null : $customer['Country'],
                "postal_code" => $customer['Postal Code'] == ''? null : $customer['Postal Code'],
                "customer_notes" => $customer['Customer Notes'] == ''? null : $customer['Customer Notes'],
                "address2" => $customer['Address2'] == ''? null : $customer['Address2'],
                "phone2" => $customer['Phone2'] == ''? null : $customer['Phone2'],
                "customer_type_id" => $customer_type_id,
                "company_id" => $this->company_id
            );

            $old_customer_ids[] = $customer['Customer Id'];

        }

        $new_customer_ids = $this->_create_customer($cache_customer_data, $old_customer_ids);

        foreach ($value as $customer) {
            foreach($customer as $key => $customer_data) {

                $key_name =  array(
                    'Customer Id','Customer Name','Customer Type','Address','City','Region' ,'Country','Postal Code','Phone','Fax' ,'Email','Customer Notes','Address2','Phone2'
                );

                if (!in_array($key, $key_name)) {

                    if(
                        isset($cache_exist_customer_field[$key]) && 
                            $cache_exist_customer_field[$key]
                    ){
                        $existing_customer_fields = $cache_exist_customer_field[$key];
                    } else {
                        $existing_customer_fields = $this->Customer_field_model->get_customer_field_by_name($this->company_id, $key);
                        $cache_exist_customer_field[$key] = $existing_customer_fields;
                    }

                    if(empty($existing_customer_fields)) {
                        $customer_fields = $this->Customer_field_model->create_customer_field($this->company_id, $key);
                    } else {
                        $customer_fields = $existing_customer_fields[0]['id'];
                    }

                    if($customer_data) {
                        $custom_customer_fields = array(
                            "customer_id" => $new_customer_ids[$id],
                            "customer_field_id" => $customer_fields,
                            "value" => $customer_data
                        );
                        $this->Customer_field_model->customer_field($custom_customer_fields);
                    }
                }
            }
        }
    }

    function _create_customer($customer_data, $old_customer_ids) {

        $first_customer_ids = array();
        $all_customer_ids = array();
        $new_customer_ids = array();

        for ($i = 0, $total = count($customer_data); $i < $total; $i = $i + 100)
        {
            $import_customer_batch = array_slice($customer_data, $i, 100);

            $this->db->insert_batch("customer", $import_customer_batch);
            $first_customer_ids[] = $this->db->insert_id();

            if ($this->db->_error_message())
            {
                show_error($this->db->_error_message());
            }
        }

        foreach($first_customer_ids as $k => $val) {
            for($a = 0; $a < 100; $a++) {
                $all_customer_ids[] = $val++;
            }
        }

        for($j = 0; $j < count($customer_data); $j++){

            if(isset($old_customer_ids[$j]) && $old_customer_ids[$j]) {

                $data_import_mapping = Array(
                    "new_id" => $all_customer_ids[$j],
                    "old_id" => $old_customer_ids[$j],
                    "company_id" => $this->company_id,
                    "type" => "customer"
                );

                $this->import_insert_batch[] = $data_import_mapping;
                $this->cache_values['Customer'][$old_customer_ids[$j]] = $data_import_mapping;
            }

            $new_customer_ids[] = $all_customer_ids[$j];
        }

        return $new_customer_ids;
    }

    function import_bookings_csv($value){

        $cache_get_source = array();
        $cache_existing_booking_field = array();
        $cache_booking_blocks = array();
        $old_booking_block_ids = array();
        $cache_booking_data = $staying_customer_data = array();
        $new_booking_ids = $old_booking_ids = $booking_x_booking_linked_group_data = array();

        foreach ($value as $booking) {

            // for get charge type id
            $charge_type_id = null;
            if(
                isset($this->cache_values['Charge Type'][$booking['Charge Type']]) && 
                    $this->cache_values['Charge Type'][$booking['Charge Type']]
            ){
                $charge_type_id = $this->cache_values['Charge Type'][$booking['Charge Type']];
            } 
            
            // for get room type id
            $room_type_id = null;
            if(
                isset($this->cache_values['Room Type'][$booking['Room Type']]) && 
                    $this->cache_values['Room Type'][$booking['Room Type']]
            ){
                $room_type_id = $this->cache_values['Room Type'][$booking['Room Type']];
            } 

            // for get room id
            $room_id = null;
            if(
                isset($this->cache_values['Room'][$booking['Room']]) && 
                    $this->cache_values['Room'][$booking['Room']]
            ){
                $room_id = $this->cache_values['Room'][$booking['Room']];
            } 

            // for get rate plan id
            $rate_plan_id = null;
            if(
                isset($this->cache_values['Rate Plan'][$booking['Rate Plan Id']]) && 
                    $this->cache_values['Rate Plan'][$booking['Rate Plan Id']]
            ){
                $rate_plan_id = $this->cache_values['Rate Plan'][$booking['Rate Plan Id']];
            } 

            // for get customer id by booking_customer
            $customer_id = null;
            if(
                isset($this->cache_values['Customer'][$booking['Booking Customer Id']]) && 
                    $this->cache_values['Customer'][$booking['Booking Customer Id']]
            ){
                $customer_id = $this->cache_values['Customer'][$booking['Booking Customer Id']];
            } 

            // for get customer id by booked_by
            $booked_by = null;
            if(
                isset($this->cache_values['Customer'][$booking['Booked By']]) && 
                    $this->cache_values['Customer'][$booking['Booked By']]
            ){
                $booked_by = $this->cache_values['Customer'][$booking['Booked By']];
            } 

            switch ($booking['State']) {
                case "Reservation" : $state = '0'; break;
                case "Checked-in" : $state = '1'; break;
                case "Checked-out" : $state = '2'; break;
                case "Out-of-Order" : $state = '3'; break;
                case "Cancelled" : $state = '4'; break;
                case "No-show" : $state = '5'; break;
                case "Delete" : $state = '6'; break;
                case "Unconfirmed" : $state = '7'; break;
            }

            switch ($booking['Pay Period']) {
                case "Daily" : $pay_period = '0'; break;
                case "Weekly" : $pay_period = '1'; break;
                case "Monthly" : $pay_period = '2'; break;
                case "One time" : $pay_period = '3'; break;
            }

            $source = "";

            if(isset($booking['Custom Booking Source']) && $booking['Custom Booking Source'] != ''){

                // for get get source
                if(
                    isset($cache_get_source[$booking['Custom Booking Source']]) && 
                        $cache_get_source[$booking['Custom Booking Source']]
                ){
                    $get_source = $cache_get_source[$booking['Custom Booking Source']];
                } else {
                    $get_source = $this->Booking_source_model->get_booking_source_by_company($this->company_id, $booking['Custom Booking Source']);
                    $cache_get_source[$booking['Custom Booking Source']] = $get_source;
                }
                
                if(empty($get_source)) {
                    $source = $this->Booking_source_model->create_booking_source($this->company_id, $booking['Custom Booking Source']);
                } else {
                    $source = $get_source ? $get_source : 0 ;
                }
            } else {
                switch ($booking['Source']) {
                    case "Walk-in / Telephone" : $source = '0'; break;
                    case "Online Widget" : $source = '-1'; break;
                    case "Booking Dot Com" : $source = '-2'; break;
                    case "Expedia" : $source = '-3'; break;
                    case "Agoda" : $source = '-4'; break;
                    case "Trip Connect" : $source = '-5'; break;
                    case "Air BNB" : $source = '-6'; break;
                    case "Hotel World" : $source = '-7'; break;
                    case "Myallocator" : $source = '-8'; break;
                    case "Company" : $source = '-9'; break;
                    case "Guest Member" : $source = '-10'; break;
                    case "Owner" : $source = '-11'; break;
                    case "Returning Guest" : $source = '-12'; break;
                    case "Apartment" : $source = '-13'; break;
                    case "sitminder" : $source = '-14'; break;
                    case "Seasonal" : $source = '-15'; break;
                    case "Other taravel agency" : $source = '-20'; break;
                }
            }

            // for get booking id
            $booking_id = null;

            if(empty($booking_id)){
                
                $this->load->helper("guid");
                $guid = generate_guid();
                $cache_booking_data[] = Array(
                    "rate" => $booking['Rate'] == '' ? null : $booking['Rate'],
                    "adult_count" => $booking['Adult Count'] == '' ? null : $booking['Adult Count'],
                    "children_count" => isset($booking['Children Count']) && $booking['Children Count'] ? $booking['Children Count'] : 0 ,
                    "booking_customer_id" => isset($customer_id['new_id']) && $customer_id['new_id'] ? $customer_id['new_id'] : null,
                    "booking_notes" => $booking['Booking Note'] == '' ? '' : $booking['Booking Note'] ,
                    "booked_by" => isset($booked_by['new_id']) && $booked_by['new_id'] ? $booked_by['new_id'] : null,
                    "balance" => $booking['Balance'] == '' ? null : $booking['Balance'],
                    "balance_without_forecast" => isset($booking['Balance Without Forecast']) && $booking['Balance Without Forecast'] ? $booking['Balance Without Forecast'] : 0,
                    "use_rate_plan" => $booking['Use Rate Plan'] == 'true' ? 1 : 0,
                    "rate_plan_id" => isset($rate_plan_id['new_id']) && $rate_plan_id['new_id'] ? $rate_plan_id['new_id'] : null,
                    "color" => $booking['Color'] != '' ? $booking['Color'] : '',
                    "charge_type_id" => isset($charge_type_id['new_id']) && $charge_type_id['new_id'] ? $charge_type_id['new_id'] : null,
                    "pay_period" => isset($pay_period) ? $pay_period : 0,
                    "source" => $source ? $source : 0 ,
                    "company_id" => $this->company_id,
                    "state" => isset($state) ? $state : 0,
                    "invoice_hash" => $guid
                );

                $old_booking_ids[] = $booking['Booking Id'];

                // for get booking room history
                $booking_block = null;
                if(
                    isset($this->cache_values['Booking Room History'][$booking['Booking Room History']]) && 
                        $this->cache_values['Booking Room History'][$booking['Booking Room History']]
                ){
                    $booking_block = $this->cache_values['Booking Room History'][$booking['Booking Room History']];
                } 

                if(empty($booking_block)){

                    // for get booking id
                    $cache_booking_blocks[] = Array(
                        // "booking_id" => $new_booking_ids[$id],
                        "room_id" => isset($room_id['new_id']) &&  $room_id['new_id'] ? $room_id['new_id'] : 0,
                        "room_type_id" => isset($room_type_id['new_id']) && $room_type_id['new_id'] ? $room_type_id['new_id'] : 0,
                        "check_in_date" => $booking['Check In Date'] == '' ? null : $booking['Check In Date'],
                        "check_out_date" => $booking['Check Out Date'] == '' ? null : $booking['Check Out Date']
                    );

                    $old_booking_block_ids[] = $booking['Booking Room History'];
                }
            }
        }

        $new_booking_ids = $this->_create_booking(
                                                    $cache_booking_data,
                                                    $old_booking_ids,
                                                    $cache_booking_blocks,
                                                    $old_booking_block_ids
                                                );

        $this->Charge_model->update_booking_charges($old_booking_ids, $new_booking_ids);

        $id = 0;
        foreach ($value as $booking) {

            // if(
            //     isset($booking['Booking Id']) && 
            //     $booking['Booking Id']
            // ) {
            //     $charge_update = $this->Charge_model->update_charge_booking(
            //         $booking['Booking Id'],
            //         $new_booking_ids[$id],
            //         null
            //     );
            // }

            $stay_in_customers = $booking['Staying Customers'];

            if(isset($stay_in_customers) && $stay_in_customers != '' && $stay_in_customers != null){

                $customer_ids = explode(',', $booking['Staying Customers']);

                foreach ($customer_ids as $customer_id) {

                    // for get staying customer id
                    $staying_customer_id = null;
                    if(
                        isset($this->cache_values['Customer'][$customer_id]) && 
                            $this->cache_values['Customer'][$customer_id]
                    ){
                        $staying_customer_id = $this->cache_values['Customer'][$customer_id];
                    } 

                    if($staying_customer_id){

                        $existing_customer = null;

                        // $existing_customer = $this->Booking_model->get_booking_staying_customer_by_id(
                        //     $staying_customer_id['new_id'],
                        //     $this->company_id,
                        //     $new_booking_ids[$id]
                        // );

                        if(!$existing_customer){

                            $staying_customer_data[] = array(
                                'booking_id' => $new_booking_ids[$id],
                                'company_id' => $this->company_id,
                                'customer_id' => $staying_customer_id['new_id']
                            );

                            // $data = array(
                            //     'booking_id' => $new_booking_ids[$id],
                            //     'company_id' => $this->company_id,
                            //     'customer_id' => $staying_customer_id['new_id']
                            // );

                            // $this->Booking_model->create_booking_staying_customer($data);
                        }
                    }
                }
            }

            foreach($booking as $key => $booking_data) {
                $key_name = array(
                    "Booking Id","Rate","Adult Count","Children Count","State","Booking Customer Id","Booked By","Balance","Balance Without Forecast","Use Rate Plan","Rate Plan Id","Color","Charge Type","Check In Date","Check Out Date","Room","Room Type","Group Id","Group Name", "Daily Charges", "Pay Period", "Source", "Custom Booking Source","Booking Note","Booking Room History","Staying Customers"
                );

                if (!in_array($key, $key_name) ) {

                    // for get existing booking field
                    $existing_booking_field = null;
                    if(
                        isset($cache_existing_booking_field[$key]) && 
                            $cache_existing_booking_field[$key]
                    ){
                        $existing_booking_field = $cache_existing_booking_field[$key];
                    } 

                    // else {
                    //     $existing_booking_field = $this->Booking_field_model->get_the_booking_fields_by_name($key , $this->company_id);
                    //     $cache_existing_booking_field[$key] = $existing_booking_field;
                    // }

                    if(!$existing_booking_field){
                        $booking_fields = $this->Booking_field_model->create_booking_field($this->company_id, $key);
                        $cache_existing_booking_field[$key] = $booking_fields;
                    } 
                    // else {
                    //     echo '<br/>';echo ' out ';
                    //     $booking_fields = isset($existing_booking_field[0]['id']) && $existing_booking_field[0]['id'] ? $existing_booking_field[0]['id'] : null;
                    // }

                    

                    if($booking_data){

                        $custom_booking_fields = array(
                            "booking_id" => $new_booking_ids[$id],
                            "booking_field_id" => $booking_fields,
                            "value" => $booking_data
                        );

                        $this->Booking_field_model->booking_field($new_booking_ids[$id], $custom_booking_fields);
                    }
                }
            }

            if(!empty($booking['Group Id'])){

                // for get group id
                $group_id = null;
                if(
                    isset($this->cache_values['Group Booking'][$booking['Group Id']]) && 
                        $this->cache_values['Group Booking'][$booking['Group Id']]
                ){
                    $group_id = $this->cache_values['Group Booking'][$booking['Group Id']];
                } 

                if(empty($group_id)){

                    $group_name = ($booking['Group Name']) != '' ? $booking['Group Name'] : null ;
                    $new_group_id = $this->Booking_linked_group_model->create_booking_linked_groups($group_name);
                    $data_import_mapping = Array(
                        "new_id" => $new_group_id,
                        "old_id" => $booking['Group Id'],
                        "company_id" => $this->company_id,
                        "type" => "group_booking"
                    );

                    $this->import_insert_batch[] = $data_import_mapping;
                    $this->cache_values['Group Booking'][$booking['Group Id']] = $data_import_mapping;

                } else {
                    $new_group_id = $group_id['new_id'];
                }

                $booking_x_booking_linked_group_data[] = array(
                    "booking_id " => $new_booking_ids[$id],
                    "booking_group_id" => $new_group_id
                );

                // $data = array(
                //     "booking_id " => $new_booking_ids[$id],
                //     "booking_group_id" => $new_group_id
                // );

                // $booking_linke_group = $this->Booking_linked_group_model->insert_booking_x_booking_linked_group($data);
            }

            $id++;
        }

        // $this->Booking_linked_group_model->insert_booking_x_booking_linked_group_batch($data);

        for ($i = 0, $total = count($staying_customer_data); $i < $total; $i = $i + 100)
        {
            $import_staying_customer_batch = array_slice($staying_customer_data, $i, 100);

            $this->db->insert_batch("booking_staying_customer_list", $import_staying_customer_batch);

            if ($this->db->_error_message())
            {
                show_error($this->db->_error_message());
            }
        }

        for ($i = 0, $total = count($booking_x_booking_linked_group_data); $i < $total; $i = $i + 100)
        {
            $import_booking_linked_group_batch = array_slice($booking_x_booking_linked_group_data, $i, 100);

            $this->db->insert_batch("booking_x_booking_linked_group", $import_booking_linked_group_batch);

            if ($this->db->_error_message())
            {
                show_error($this->db->_error_message());
            }
        }
    }



    function _create_booking($booking_data, $old_booking_ids, $booking_blocks_data, $old_booking_block_ids) {

        $first_booking_ids = $first_booking_block_ids = $all_booking_ids = $all_booking_block_ids = $new_booking_ids = array();

        for ($i = 0, $total = count($booking_data); $i < $total; $i = $i + 100)
        {
            $import_booking_batch = array_slice($booking_data, $i, 100);

            $this->db->insert_batch("booking", $import_booking_batch);
            $first_booking_ids[] = $this->db->insert_id();

            if ($this->db->_error_message())
            {
                show_error($this->db->_error_message());
            }
        }

        foreach($first_booking_ids as $k => $val) {
            for($a = 0; $a < 100; $a++) {
                $all_booking_ids[] = $val++;
            }
        }

        for($j = 0; $j < count($booking_data); $j++){

            if(isset($old_booking_ids[$j]) && $old_booking_ids[$j]) {

                $data_import_mapping = Array(
                    "new_id" => $all_booking_ids[$j],
                    "old_id" => $old_booking_ids[$j],
                    "company_id" => $this->company_id,
                    "type" => "booking"
                );
                $this->import_insert_batch[] = $data_import_mapping;
                $this->cache_values['Booking'][$old_booking_ids[$j]] = $data_import_mapping;
            }

            $new_booking_ids[] = $all_booking_ids[$j];
            $booking_blocks_data[$j]['booking_id'] = $all_booking_ids[$j];
        }

        for ($i = 0, $total = count($booking_blocks_data); $i < $total; $i = $i + 100)
        {
            $import_booking_block_batch = array_slice($booking_blocks_data, $i, 100);

            $this->db->insert_batch("booking_block", $import_booking_block_batch);
            $first_booking_block_ids[] = $this->db->insert_id();

            if ($this->db->_error_message())
            {
                show_error($this->db->_error_message());
            }
        }

        foreach($first_booking_block_ids as $k => $val) {
            for($a = 0; $a < 100; $a++) {
                $all_booking_block_ids[] = $val++;
            }
        }

        for($j = 0; $j < count($booking_blocks_data); $j++){
            if(isset($old_booking_block_ids[$j]) && $old_booking_block_ids[$j]) {
                $data_import_mapping = Array(
                    "new_id" => $all_booking_block_ids[$j],
                    "old_id" => $old_booking_block_ids[$j],
                    "company_id" => $this->company_id,
                    "type" => "booking_block"
                );
                $this->import_insert_batch[] = $data_import_mapping;
            }
        }

        return $new_booking_ids;
    }

    function import_extras_csv($value){

        $cache_booking_id = array();

        foreach ($value as $extra) {

            $extras = null;
            if(
                isset($this->cache_values['Extra'][$extra['Extra Id']]) && 
                    $this->cache_values['Extra'][$extra['Extra Id']]
            ){
                $extras = $this->cache_values['Extra'][$extra['Extra Id']];
            } 

            $charge_type_id = null;
            if(
                isset($this->cache_values['Charge Type'][$extra['Charge Type']]) && 
                    $this->cache_values['Charge Type'][$extra['Charge Type']]
            ){
                $charge_type_id = $this->cache_values['Charge Type'][$extra['Charge Type']];
            } 

            if(empty($extras)){

                $data = array (
                    'extra_name' => $extra['Extra Name'] != '' ? $extra['Extra Name']  : null ,
                    'company_id' => $this->company_id,
                    'extra_type' => $extra['Extra Type'] != '' ? $extra['Extra Type'] : null ,
                    'charging_scheme' => $extra['Charging Scheme'] != '' ? $extra['Charging Scheme'] : null ,
                    'show_on_pos' => $extra['Show on POS'],
                    'charge_type_id' => isset($charge_type_id['new_id']) && $charge_type_id['new_id'] ? $charge_type_id['new_id'] : 0
                );

                $extra_id = $this->Extra_model->create_all_extras($data);

                $rate_extra_data = array(
                    'rate' => $extra['Rate'] != '' ? $extra['Rate'] : 0 ,
                    'currency_id' => $extra['Curreny'] != '' ? $extra['Curreny'] : null,
                    'extra_id' => $extra_id
                );
                $rate_extra = $this->Rate_model->create_extra_rate($rate_extra_data);

                $data_import_mapping = Array(
                    "new_id" => $extra_id,
                    "old_id" => $extra['Extra Id'],
                    "company_id" => $this->company_id,
                    "type" => "extra"
                );

                $this->import_insert_batch[] = $data_import_mapping;
                $this->cache_values['Extra'][$extra['Extra Id']] = $data_import_mapping;

            } else {
                $extra_id = $extras['new_id'];
            }

            if($extra['Booking Id']){

                $booking_extra = null;
                if(
                    isset($this->cache_values['Extra Booking'][$extra['Booking Id']]) && 
                        $this->cache_values['Extra Booking'][$extra['Booking Id']]
                ){
                    $booking_extra = $this->cache_values['Extra Booking'][$extra['Booking Id']];
                } 

                if(empty($booking_extra)){

                    $booking_id = null;
                    if(
                        isset($this->cache_values['Booking'][$extra['Booking Id']]) && 
                            $this->cache_values['Booking'][$extra['Booking Id']]
                    ){
                        $booking_id = $this->cache_values['Booking'][$extra['Booking Id']];
                    } 

                    $booking_extra_id = null;

                    if(isset($booking_id['new_id']) && $booking_id['new_id']) {
                        $booking_extra_id = $this->Booking_extra_model->create_booking_extra(
                            $booking_id['new_id'],
                            $extra_id,$extra['Start Date'],
                            $extra['End Date'],
                            $extra['Quantity'],
                            $extra['Default Rate']
                        );
                    }

                    if($booking_extra_id) {
                        $data_import_mapping = Array(
                            "new_id" => $booking_extra_id,
                            "old_id" => $extra['Booking Extra Id'],
                            "company_id" => $this->company_id,
                            "type" => "extra_booking"
                        );

                        $this->import_insert_batch[] = $data_import_mapping;
                        $this->cache_values['Extra Booking'][$extra['Booking Id']] = $data_import_mapping;
                    }
                }
            }
        }
    }

    function import_payments_csv($value){
        $cache_get_payment_type = array();
        $cache_booking_id = array();
        $cache_payment_data = array();
        $old_payment_ids = $payment_types = array();

        foreach ($value as $payment) {

            $get_payment_type = null;
            // if(
            //     isset($cache_get_payment_type[$payment['Payment Type']]) && 
            //         $cache_get_payment_type[$payment['Payment Type']]
            // ){
            //     $get_payment_type = $cache_get_payment_type[$payment['Payment Type']];
            // } 
            // else {
            //     $get_payment_type = $this->Payment_model->get_payment_types_by_name($payment['Payment Type'], $this->company_id);
            //     $cache_get_payment_type[$payment['Payment Type']] = $get_payment_type;
            // }

            if(empty($get_payment_type) && !in_array($payment['Payment Type'], $payment_types)) {
                $read_only = $payment['Read Only'] == 'true' ? 1 : 0 ;
                $payment_id = $this->Payment_model->create_payment_type($this->company_id, $payment['Payment Type'],$read_only);

                $payment_types[] = $payment['Payment Type'];

            } 
            // else {
            //     $payment_id = isset($get_payment_type[0]->payment_type_id) ? $get_payment_type[0]->payment_type_id : ' ' ;
            // }

            $customer_id = null;
            if(
                isset($this->cache_values['Customer'][$payment['Customer Id']]) && 
                    $this->cache_values['Customer'][$payment['Customer Id']]
            ){
                $customer_id = $this->cache_values['Customer'][$payment['Customer Id']];
            } 

            $booking_id = null;
            if(
                isset($this->cache_values['Booking'][$payment['Booking Id']]) && 
                    $this->cache_values['Booking'][$payment['Booking Id']]
            ){
                $booking_id = $this->cache_values['Booking'][$payment['Booking Id']];
            } 

            if(!empty($payment['Payment Id'])){

                $get_import_payment = null;
                if(
                    isset($this->cache_values['Payment'][$payment['Payment Id']]) && 
                        $this->cache_values['Payment'][$payment['Payment Id']]
                ){
                    $get_import_payment = $this->cache_values['Payment'][$payment['Payment Id']];
                } 
                
                if(empty($get_import_payment)){

                    $cache_payment_data[] = Array(
                        "description" => $payment['Description'],
                        "date_time" => $payment['Date Time'] == '' ? null : $payment['Date Time'],
                        "booking_id" => isset($booking_id['new_id']) && $booking_id['new_id'] ? $booking_id['new_id'] : null,
                        "payment_type_id" => $payment_id,
                        "amount" => $payment['Amount'] == '' ? null : $payment['Amount'],
                        "credit_card_id" => $payment['Credit Card Id'] == '' ? null : $payment['Credit Card Id'],
                        "selling_date" => $payment['Selling Date'] == '' ? null : $payment['Selling Date'],
                        "customer_id" => isset($customer_id['new_id']) && $customer_id['new_id'] ? $customer_id['new_id'] : null,
                        "payment_status" => $payment['Payment Status'] == '' ? null : $payment['Payment Status'],
                        "is_captured" => $payment['Payment Capture'] == 'true' ? 1 : 0,
                        "read_only" => $payment['Payment Read Only'] == 'true' ? 1 : 0
                    );
                }
            }
        }

        $first_payment_ids = array();
        $all_payment_ids = array();

        for ($i = 0, $total = count($cache_payment_data); $i < $total; $i = $i + 100)
        {
            $import_payment_batch = array_slice($cache_payment_data, $i, 100);

            $this->db->insert_batch("payment", $import_payment_batch);
            $first_payment_ids[] = $this->db->insert_id();

            if ($this->db->_error_message())
            {
                show_error($this->db->_error_message());
            }
        }

        foreach($first_payment_ids as $k => $val) {
            for($a = 0; $a < 100; $a++) {
                $all_payment_ids[] = $val++;
            }
        }

        for($j = 0; $j < count($cache_payment_data); $j++){
            if(isset($old_payment_ids[$j]) && $old_payment_ids[$j]) {
                $data_import_mapping = Array(
                    "new_id" => $all_payment_ids[$j],
                    "old_id" => $old_payment_ids[$j],
                    "company_id" => $this->company_id,
                    "type" => "payment"
                );
                $this->import_insert_batch[] = $data_import_mapping;
                $this->cache_values['Payment'][$old_payment_ids[$j]] = $data_import_mapping;
            }
        }
    }

    function import_statements_csv($value){

        foreach($value as $statement){
            if($statement['Statment Id']){

                $statements = null;
                if(
                    isset($this->cache_values['Statment'][$statement['Statment Id']]) && 
                        $this->cache_values['Statment'][$statement['Statment Id']]
                ){
                    $statements = $this->cache_values['Statment'][$statement['Statment Id']];
                } 

                $booking_id = null;
                if(
                    isset($this->cache_values['Booking'][$statement['Booking Id']]) && 
                        $this->cache_values['Booking'][$statement['Booking Id']]
                ){
                    $booking_id = $this->cache_values['Booking'][$statement['Booking Id']];
                } 

                if(empty($statements)){

                    $current_date = date('Y-m-d H:i:s');
                    $statement_date = date('Y-m-d', strtotime($current_date));
                    $data = array(
                        "statement_number" => $statement['Statement Number'] ,
                        "creation_date" => $statement['Creation Date'] ? $statement['Creation Date'] : date('Y-m-d H:i:s'),
                        "statement_name" => $statement['Statement Name'] ? $statement['Statement Name'] : "Statement of ".date('M Y', strtotime($statement_date) )
                    );

                    $statement_id =  $this->Statement_model->create_statement($data);

                    $booking_statement = array(
                        "booking_id" => $booking_id['new_id'],
                        "statement_id" => $statement_id
                    );

                    $this->Statement_model->create_statement_booking($booking_statement);

                    $data_import_mapping = Array(
                        "new_id" => $statement_id,
                        "old_id" => $statement['Statment Id'],
                        "company_id" => $this->company_id,
                        "type" => "statement"
                    );

                    $this->import_insert_batch[] = $data_import_mapping;
                    $this->cache_values['Statment'][$statement['Statment Id']] = $data_import_mapping;
                }
            }
        }
    }

    function import_company_setting($values){

        $value = json_decode($values, true);

        $company_data = array(
            'is_total_balance_include_forecast' => isset($value['Feature settings']['Total Balance Include Forecast']) ? $value['Feature settings']['Total Balance Include Forecast'] : ""  ,
            'auto_no_show'  => isset($value['Feature settings']['Auto No Show']) ? $value['Feature settings']['Auto No Show'] : "",
            'book_over_unconfirmed_reservations'=> isset($value['Feature settings']['Book Over Unconfirmed Reservations']) ? $value['Feature settings']['Book Over Unconfirmed Reservations'] : "" ,
            'send_invoice_email_automatically' => isset($value['Feature settings']['Send Invoice Email Automatically']) ? $value['Feature settings']['Send Invoice Email Automatically']: "",
            'hide_decimal_places'=> isset($value['Feature settings']['Hide Decimal Places']) ? $value['Feature settings']['Hide Decimal Places']: "",
            'automatic_email_confirmation' => isset($value['Feature settings']['Automatic Email Confirmation']) ? $value['Feature settings']['Automatic Email Confirmation'] : "",
            'automatic_email_cancellation' => isset($value['Feature settings']['Automatic Email Cancellation']) ? $value['Feature settings']['Automatic Email Cancellation'] : "",
            'send_booking_notes' => isset($value['Feature settings']['Send Booking Notes']) ? $value['Feature settings']['Send Booking Notes']: "",
            'make_guest_field_mandatory' => isset($value['Feature settings']['Make Guest Field Mandatory']) ? $value['Feature settings']['Make Guest Field Mandatory'] : "",
            'include_cancelled_noshow_bookings' => isset($value['Feature settings']['Include Cancelled Noshow Bookings']) ? $value['Feature settings']['Include Cancelled Noshow Bookings']: "",
            'hide_forecast_charges' => isset($value['Feature settings']['Hide Forecast Charges']) ? $value['Feature settings']['Hide Forecast Charges']:"",
            'send_copy_to_additional_emails' => isset($value['Feature settings']['Send Copy To Additional Emails']) ? $value['Feature settings']['Send Copy To Additional Emails']:"",
            'additional_company_emails' => isset($value['Feature settings']['Additional Company Emails'])? $value['Feature settings']['Additional Company Emails']: "",
            'default_charge_name' => isset($value['Feature settings']['Default Charge Name']) ? $value['Feature settings']['Default Charge Name']:"",
            'default_room_singular' => isset($value['Feature settings']['Default Room Singular']) ? $value['Feature settings']['Default Room Singular'] : "",
            'default_room_plural' => isset($value['Feature settings']['Default Room Plural']) ? $value['Feature settings']['Default Room Plural'] : "",
            'default_room_type'=> isset($value['Feature settings']['Default Room Type'])? $value['Feature settings']['Default Room Type'] : "",
            'date_format' => isset($value['Feature settings']['Date Format']) ? $value['Feature settings']['Date Format'] : "",
            'default_checkin_time' => isset($value['Feature settings']['Default Checkin Time'])? $value['Feature settings']['Default Checkin Time'] : "",
            'default_checkout_time' => isset($value['Feature settings']['Default Checkout Time']) ? $value['Feature settings']['Default Checkout Time'] : "",
            'enable_hourly_booking' => isset($value['Feature settings']['Enable Hourly Booking']) ? $value['Feature settings']['Enable Hourly Booking'] : "",
            'enable_api_access'=> isset($value['Feature settings']['Enable Api Access']) ? $value['Feature settings']['Enable Api Access'] : "",
            'booking_cancelled_with_balance' => isset($value['Feature settings']['Booking Cancelled With Balance']) ? $value['Feature settings']['Booking Cancelled With Balance'] : "",
            'enable_new_calendar' => 1,
            'hide_room_name' => isset($value['Feature settings']['Hide Room Name']) ? $value['Feature settings']['Hide Room Name'] : "",
            'restrict_booking_dates_modification' => isset($value['Feature settings']['Restrict Booking Dates Modification']) ? $value['Feature settings']['Restrict Booking Dates Modification'] : "",
            'restrict_checkout_with_balance' => isset($value['Feature settings']['Restrict Checkout With Balance']) ? $value['Feature settings']['Restrict Checkout With Balance'] : "",
            'show_guest_group_invoice' => isset($value['Feature settings']['Show Guest Group Invoice']) ? $value['Feature settings']['Show Guest Group Invoice'] : "",
            'ui_theme' => isset($value['Feature settings']['Ui Theme']) ? $value['Feature settings']['Ui Theme'] : "",
            'is_display_tooltip' => isset($value['Feature settings']['Display Tooltip']) ? $value['Feature settings']['Display Tooltip'] : "",
            'ask_for_review_in_invoice_email' => isset($value['Feature settings']['Ask For Review In Invoice Email']) ? $value['Feature settings']['Ask For Review In Invoice Email'] : "",
            'redirect_to_trip_advisor' => isset($value['Feature settings']['Redirect To Trip Advisor']) ? $value['Feature settings']['Redirect To Trip Advisor'] : "",
            'email_confirmation_for_ota_reservations' => isset($value['Feature settings']['Email Confirmation For Ota Reservations']) ? $value['Feature settings']['Email Confirmation For Ota Reservations'] : "",
            'email_cancellation_for_ota_reservations' => isset($value['Feature settings']['Email Cancellation For Ota Reservations']) ? $value['Feature settings']['Email Cancellation For Ota Reservations'] : "",
            'allow_non_continuous_bookings' => isset($value['Feature settings']['Allow Non Continuous Bookings']) ? $value['Feature settings']['Allow Non Continuous Bookings'] : "",
            'maximum_no_of_blocks' => isset($value['Feature settings']['Maximum No Of Blocks']) ? $value['Feature settings']['Maximum No Of Blocks'] : "",
            'force_room_selection' => isset($value['Feature settings']['Force Room Selection']) ? $value['Feature settings']['Force Room Selection'] : "",
            'automatic_feedback_email' => isset($value['Feature settings']['Automatic Feedback Email']) ? $value['Feature settings']['Automatic Feedback Email'] : "",
            'avoid_dmarc_blocking' => isset($value['Feature settings']['Avoid Dmarc Blocking']) ? $value['Feature settings']['Avoid Dmarc Blocking'] : "",
            'allow_free_bookings' => isset($value['Feature settings']['Allow Free Bookings']) ? $value['Feature settings']['Allow Free Bookings'] : "",
            'customer_modify_booking' => isset($value['Feature settings']['Customer Modify Booking']) ? $value['Feature settings']['Customer Modify Booking'] : "",
            'housekeeping_auto_clean_is_enabled' => isset($value['Housekeepings']['Housekeeping Auto Clean is Enabled']) ? $value['Housekeepings']['Housekeeping Auto Clean is Enabled'] : "",
            'housekeeping_auto_clean_time' => isset($value['Housekeepings']['Housekeeping Auto Clean Time']) ? $value['Housekeepings']['Housekeeping Auto Clean Time'] : "",
            'housekeeping_day_interval_for_full_cleaning' => isset($value['Housekeepings']['Housekeeping Day Interval For Full Cleaning']) ? $value['Housekeepings']['Housekeeping Day Interval For Full Cleaning'] : "",
            'housekeeping_auto_dirty_is_enabled' => isset($value['Housekeepings']['Housekeeping Auto Dirty is Enabled']) ? $value['Housekeepings']['Housekeeping Auto Dirty is Enabled'] : "",
            'housekeeping_auto_dirty_time' => isset($value['Housekeepings']['Housekeeping Auto Dirty Time']) ? $value['Housekeepings']['Housekeeping Auto Dirty Time'] : "",
            'invoice_email_header' => isset($value['Email Templates']['Invoice Email Header']) ? $value['Email Templates']['Invoice Email Header'] : "",
            'booking_confirmation_email_header' => isset($value['Email Templates']['Booking Confirmation Email Header']) ? $value['Email Templates']['Booking Confirmation Email Header'] : "",
            'reservation_policies'=> isset($value['Policies']['Reservation Policies']) ? $value['Policies']['Reservation Policies'] : "",
            'check_in_policies'=> isset($value['Policies']['Check In Policies']) ? $value['Policies']['Check In Policies'] : "",
            'show_logo_on_registration_card'=> isset($value['Registration cards']['Show logo on registration card']) ? $value['Registration cards']['Show logo on registration card'] : "",
            'show_rate_on_registration_card'=> isset($value['Registration cards']['Show rate on registration card']) ? $value['Registration cards']['Show rate on registration card'] : "",
            'default_currency_id'=> isset($value['Company Details']['Default Currency']) ? $value['Company Details']['Default Currency'] : "",
            'default_language'=> isset($value['Company Details']['Default language']) ? $value['Company Details']['Default language'] : "",
            'address'=> isset($value['Company Details']['Address']) ? $value['Company Details']['Address'] : "",
            'city'=> isset($value['Company Details']['City']) ? $value['Company Details']['City'] : "",
            'region'=> isset($value['Company Details']['Region']) ? $value['Company Details']['Region'] : "",
            'country'=> isset($value['Company Details']['Country']) ? $value['Company Details']['Country'] : "",
            'postal_code'=> isset($value['Company Details']['Postal code']) ? $value['Company Details']['Postal code'] : "",
            'phone'=> isset($value['Company Details']['Phone']) ? $value['Company Details']['Phone'] : "",
            'fax'=> isset($value['Company Details']['Fax']) ? $value['Company Details']['Fax'] : "",
            'email'=> isset($value['Company Details']['Email']) ? $value['Company Details']['Email'] : "",
            'time_zone'=> isset($value['Company Details']['Time zone']) ? $value['Company Details']['Time zone'] : "",
            'number_of_rooms'=> isset($value['Company Details']['Rooms']) ? $value['Company Details']['Rooms'] : "",
            'website' => isset($value['Company Details']['Website']) ? $value['Company Details']['Website'] : "",
            'invoice_header'=> isset($value['Invoice headers']['Invoice Header']) ? $value['Invoice headers']['Invoice Header'] : "",
            'statement_number'=> isset($value['Invoice headers']['Statement Number']) ? $value['Invoice headers']['Statement Number'] : "",
            'allow_same_day_check_in'=> isset($value['Online Bookings']['Allow same day check in']) ? $value['Online Bookings']['Allow same day check in'] : "",
            'require_paypal_payment'=> isset($value['Online Bookings']['Require paypal payment']) ? $value['Online Bookings']['Require paypal payment'] : "",
            'paypal_account'=> isset($value['Online Bookings']['Paypal account']) ? $value['Online Bookings']['Paypal account'] : "",
            'percentage_of_required_paypal_payment'=> isset($value['Online Bookings']['Percentage of required paypal payment']) ? $value['Online Bookings']['Percentage of required paypal payment'] : "",
            'booking_engine_booking_status'=> isset($value['Online Bookings']['Booking engine booking status']) ? $value['Online Bookings']['Booking engine booking status'] : "",
            'email_confirmation_for_booking_engine'=> isset($value['Online Bookings']['Email confirmation for booking engine']) ? $value['Online Bookings']['Email confirmation for booking engine'] : "",
            'booking_engine_tracking_code'=> isset($value['Online Bookings']['Booking engine tracking code']) ? $value['Online Bookings']['Booking engine tracking code'] : "",
            'selling_date'=> isset($value['Night Audits']['Selling date']) ? $value['Night Audits']['Selling date'] : "",
            'night_audit_auto_run_is_enabled'=> isset($value['Night Audits']['Night audit auto run is enabled']) ? $value['Night Audits']['Night audit auto run is enabled'] : "",
            'night_audit_auto_run_time'=> isset($value['Night Audits']['Night audit auto run time']) ? $value['Night Audits']['Night audit auto run time'] : "",
            'night_audit_ignore_check_out_date'=> isset($value['Night Audits']['Night audit ignore check out date']) ? $value['Night Audits']['Night audit ignore check out date'] : "",
            'night_audit_charge_in_house_only'=> isset($value['Night Audits']['Night audit charge in house only']) ? $value['Night Audits']['Night audit charge in house only'] : "",
            'night_audit_force_check_out'=> isset($value['Night Audits']['Night audit force check out']) ? $value['Night Audits']['Night audit force check out'] : ""

        );
        $this->Company_model->update_company($this->company_id, $company_data);

        // $teams = $value['Teams'];

        // foreach ($teams as $key => $team) {
        //     $data = array(
        //         'email'              => $team['Email'],
        //         'current_company_id' => $this->company_id,
        //         'first_name'         => $team['First Name'],
        //         'last_name'          => $team['Last Name'],
        //         'password'           => $team['Password']
        //     );

        //     $get_user = $this->User_model->get_user_by_email($team['Email']);

        //     if(!$get_user){

        //         $user =  $this->users->create_user($data, true);

        //         $this->User_model->add_teams($this->company_id, $user['user_id'],$team['permission']);
        //     }
        // }

        $booking_fields = $value['Booking Fields'];


        if($booking_fields != "" ){
            foreach ($booking_fields as $key => $fields) {
                $booking_field_id = $this->Booking_field_model->get_the_booking_fields_by_name($key,$this->company_id);

                if($booking_field_id){
                    $booking_fieldid = $booking_field_id[0]['id'];
                } else {
                    $booking_fieldid = $this->Booking_field_model->create_booking_field($this->company_id, $key);
                }

                $data = array(
                    'show_on_booking_form' => $fields['show_on_booking_form'],
                    'show_on_registration_card' => $fields['show_on_registration_card'],
                    'show_on_in_house_report' => $fields['show_on_in_house_report'],
                    'show_on_invoice' => $fields['show_on_invoice'],
                    'is_required' => $fields['is_required']
                );
                $this->Booking_field_model->update_booking_field($booking_fieldid,$data);
            }
        }

        $customer_fields = $value['Customer Fields'];

        if($customer_fields != "" ){
            foreach ($customer_fields as $key => $customer_field_data) {
                $customer_field_id = $this->Customer_field_model->get_customer_field_by_name($this->company_id, $key);

                if($customer_field_id){
                    $customer_fieldid = $customer_field_id[0]['id'];
                } else {
                    $customer_fieldid = $this->Customer_field_model->create_customer_field($this->company_id, $key);
                }

                $customer_data = array(
                    'show_on_customer_form' => $customer_field_data['show_on_customer_form'],
                    'show_on_registration_card' => $customer_field_data['show_on_registration_card'],
                    'show_on_in_house_report' => $customer_field_data['show_on_in_house_report'],
                    'show_on_invoice' => $customer_field_data['show_on_invoice'],
                    'is_required' => $customer_field_data['is_required']
                );

                $this->Customer_field_model->update_customer_field($customer_fieldid,$customer_data);
            }
        }

        $payment_types = $value['Payment Types'];

        if($payment_types){
            foreach ($payment_types as $payment_type) {
                $existing_payment_type = $this->Payment_model->get_payment_types_by_name($payment_type['payment_type'], $this->company_id);

                if(empty($existing_payment_type)){

                    $payment_type = $this->Payment_model->create_payment_type($this->company_id,$payment_type['payment_type']);
                }
            }
        }

        $charge_types = $value['Charge Types'];

        if($charge_types){
            foreach ($charge_types as $charge_type) {
                $existing_charge_type = $this->Charge_type_model->get_charge_type_by_name($charge_type['name'], $this->company_id);

                if(empty($existing_charge_type)){
                    $charge_type_id = $this->Charge_type_model->create_charge_type($this->company_id, $charge_type['name']);

                    $taxes = explode(',', $charge_type['taxes']);

                    foreach ($taxes as $tax_type) {
                        if($tax_type){
                            $tax_type_id = $this->Tax_model->get_tax_type_by_name($tax_type);
                            $charge_taxes = $this->Charge_type_model->get_charge_tax($charge_type_id, $tax_type_id);
                            if(!$charge_taxes){
                                $this->Charge_type_model->add_charge_type_tax($charge_type_id, $tax_type_id);
                            }
                        }
                    }

                    $data = array(
                        "is_default_room_charge_type" => $charge_type['is_default_room_charge_type'],
                        "is_room_charge_type" => $charge_type['is_room_charge_type']
                    );
                    $this->Charge_type_model->update_charge_type($charge_type_id, $data);
                }
            }
        }

        $room_types = $value['Room Types'];

        if($room_types){
            foreach ($room_types as $room_type) {
                $existing_room_type = $this->Room_type_model->get_room_type_name($room_type['name'], $this->company_id);

                if(empty($existing_room_type)){
                    $room_type = $this->Room_type_model->create_room_type($this->company_id, $room_type['name'],$room_type['acronym'],$room_type['max_adults'],$room_type['max_children']);
                }
            }
        }

        $customer_types = $value['Customer Types'];

        if($customer_types){
            foreach ($customer_types as $customer_type) {
                if(isset($customer_type['name']) && $customer_type['name']) {
                    $existing_customer_type = $this->Customer_type_model->get_customer_type_by_name($this->company_id,$customer_type['name']);

                    if(empty($existing_customer_type)){

                        $customer_type = $this->Customer_type_model->create_customer_type($this->company_id, $customer_type['name']);
                    }
                }
            }
        }

        $booking_sources = $value['Booking Source'];

        if($booking_sources){
            foreach ($booking_sources as $booking_source) {
                $existing_booking_source = $this->Booking_source_model->get_booking_source_by_company($this->company_id, $booking_source['name']);
                
                if(empty($existing_booking_source)){
                    $booking_source_id = $this->Booking_source_model->create_booking_source($this->company_id, $booking_source['name']);
                } else {
                    $booking_source_id = $existing_booking_source;
                }

                $data = array(
                    'commission_rate' => $booking_source['commission_rate'],
                    'is_hidden' => $booking_source['is_hidden']
                );

                $this->Booking_source_model->update_booking_source($booking_source_id,$data);
            }
        }

        $room_types = $value['Room Types'];
        if($room_types){
            foreach($room_types as $room_type){
                
                $room_type_id = null;
                if(
                    isset($this->cache_values['Room Type'][$room_type['id']]) && 
                        $this->cache_values['Room Type'][$room_type['id']]
                ){
                    $room_type_id = $this->cache_values['Room Type'][$room_type['id']];
                } 

                if($room_type_id) {
                    if(isset($room_type['description']) && $room_type['description']) {
                        $data = array(
                            'description' => $room_type['description']
                        );
                        $this->Room_type_model->update_room_type($room_type_id['new_id'], $data);
                    }
                }
            }
        }

        $rate_plans = $value['Rate Plan'];
        if($rate_plans){
            foreach($rate_plans as $rate_plan){

                $rate_plan_id = null;
                if(
                    isset($this->cache_values['Rate Plan'][$rate_plan['rate_plan_id']]) && 
                        $this->cache_values['Rate Plan'][$rate_plan['rate_plan_id']]
                ){
                    $rate_plan_id = $this->cache_values['Rate Plan'][$rate_plan['rate_plan_id']];
                } 

                if($rate_plan_id){
                    if(isset($rate_plan['description']) && $rate_plan['description']) {
                        $data = array(
                            'description' => $rate_plan['description']
                        );
                        $this->Rate_plan_model->update_rate_plan($data,$rate_plan_id['new_id']);
                    }
                }
            }
        }

        for ($i = 0, $total = count($this->import_insert_batch); $i < $total; $i = $i + 100)
        {
            $import_batch = array_slice($this->import_insert_batch, $i, 100);

            $this->db->insert_batch("import_mapping", $import_batch);

            if ($this->db->_error_message())
            {
                show_error($this->db->_error_message());
            }
        }
    }
}
