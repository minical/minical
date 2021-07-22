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
                'email'               => $this->input->post('company_email'),
                'fax'                 => $this->input->post('company_fax'),
                'time_zone'           => $this->input->post('time_zone'),
                'default_currency_id' => $this->input->post('default_currency'),
                'default_language'    => $this->input->post('default_language')
            );

            $this->company_id  = $this->session->userdata('current_company_id');
            $this->Company_model->update_company($this->company_id, $update_data);

            redirect('/settings/company/general'); // redirect to settings unavailable screen
        }

        $data['js_files'] = array(
            base_url().auto_version('js/hotel-settings/cropper_jsmin.js'),
            base_url().auto_version('js/company_settings.js'),
            base_url().auto_version('js/hotel-settings/logo-image-settings.js'),
           "http://ajax.aspnetcdn.com/ajax/jquery.validate/1.7/jquery.validate.min.js",
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

            $this->_add_employee($this->company_id, $email, $first_name, $last_name);
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
            "make_guest_field_mandatory" => isset($faetures["make_guest_field_mandatory"]) ? $faetures["make_guest_field_mandatory"] : ""
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
                'show_guest_group_invoice' => $this->input->post('show_guest_group_invoice')
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

                        if(in_array($file_ext[1], $allowed_ext))
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
                                $json[] = array_combine($key, $row);
                            }
                            $result[$zip_name[0]] = $json;
                            // release file handle
                            fclose($fp);
                            unlink($path.'/'.$file);
                        }

                        if(in_array($file_ext[1], $ext_allowed)){
                            $fp = fopen($path.$file, 'r');
                            $setting = fgets($fp);
                            $result['settings'] = $setting;

                            fclose($fp);
                            unlink($path.'/'.$file);

                        }
                    }

                    $csv_data = $result;

                    if (isset($csv_data['rooms'])) {
                        $this->import_rooms_csv($csv_data['rooms']);
                    }
                    if (isset($csv_data['taxes'])) {
                        $this->import_taxes_csv($csv_data['taxes']);
                    }
                    if (isset($csv_data['charges'])) {
                        $this->import_charges_csv($csv_data['charges']);
                    }
                    if (isset($csv_data['rates'])) {
                        $this->import_rates_csv($csv_data['rates']);
                    }
                    if (isset($csv_data['customers'])) {
                        $this->import_customers_csv($csv_data['customers']);
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
                    if (isset($csv_data['settings'])) {
                        $this->import_company_setting($csv_data['settings']);
                    }

                    unlink($location);
                    // rmdir($path . $name);  
                }
            }
        }

        // redirect('/settings/company/import');
    }

    function import_rooms_csv($value){

        foreach ($value as $room) {
            $get_room_type = $this->Room_type_model->get_room_type_name($room['Room Type Name'], $this->company_id);

            if (empty($get_room_type)) {
                $data = array(
                    'company_id' => $this->company_id,
                    'name' => $room['Room Type Name'] == '' ? null : $room['Room Type Name'],
                    'acronym' => $room['Acronym'] == ''? null : $room['Acronym'] ,
                    'max_adults' => $room['Max Adults'] == ''  ? 0 : $room['Max Adults'] ,
                    'max_children' => $room['Max Children'] == ''  ? 0 : $room['Max Children'] ,
                    'max_occupancy' => $room['Max Occupancy'] == ''  ? 0 : $room['Max Occupancy'] ,
                    'min_occupancy' => $room['Min Occupancy'] == ''  ? 0 : $room['Min Occupancy'] ,
                    'can_be_sold_online' => $room['Can be Sold online'] == 'true' ? 1 : 0
                );

                $room_type_id = $this->Room_type_model->add_new_room_type($data);
                $data_import_mapping = Array(
                    "new_id" => $room_type_id,
                    "old_id" => $room['Room Type Id'],
                    "company_id" => $this->company_id,
                    "type" => "room_type"
                );

                $import_data = $this->Import_mapping_model->insert_import_mapping($data_import_mapping);
            } else {
                $room_type_id = isset($get_room_type[0]['id']) ? $get_room_type[0]['id'] : '';
            }

            if(!empty($room['Room Id'])){
                $get_room = $this->Room_model->get_room_by_name($room['Room Name'], $room_type_id);
                if(empty($get_room)){

                    $room = $this->Room_model->create_room($this->company_id, $room['Room Name'], $room_type_id);
                }
            }

        }
    }

    function import_taxes_csv($value){

        foreach ($value as $tax) {

            $get_tax_type = $this->Import_mapping_model->get_mapping_tax_id($tax['Tax Type Id']);


            if(empty($get_tax_type)){

                $data = array(
                    "tax_type" => $tax['Tax Type'] == '' ? null : $tax['Tax Type'],
                    "tax_rate" => $tax['Tax Rate'] == '' ? null : $tax['Tax Rate'],
                    "company_id" => $this->company_id ,
                    "is_percentage" => $tax['Is Percentage'] == 'true' ? 1 : 0,
                    "is_brackets_active" => $tax['Bracket Active'] == 'true' ? 1 : 0,
                    "is_tax_inclusive" => $tax['Is Tax Inclusive'] == 'true' ? 1 : 0
                );

                $new_taxes = $this->Tax_model->create_new_tax_type($data);

                // if($tax['Bracket Active'] == 'true'){

                //     $price_brackets =array(
                //     "tax_type_id" => $new_taxes,
                //     "start_range" => $tax['start Range'],
                //     "end_range" =>$tax['End Range'],
                //     "tax_rate" =>$tax['Tax Rate']

                // );

                // $this->Tax_price_bracket_model->add_price_brakets($price_brackets);
                // }

                $data_import_mapping = Array(
                    "new_id" => $new_taxes,
                    "old_id" => $tax['Tax Type Id'],
                    "company_id" => $this->company_id,
                    "type" => "tax_type"
                );

                $import_data = $this->Import_mapping_model->insert_import_mapping($data_import_mapping);

            }


        }

    }

    function import_charges_csv($value){

        foreach ($value as $charge) {


            $get_the_charge_type = $this->Import_mapping_model->get_mapping_charge_id($charge['Charge Type Id']);

            if(empty($get_the_charge_type)){
               
                $data = array (
                    'name' => $charge['Charge Type'],
                    'company_id' => $this->company_id,
                    'is_room_charge_type' => $charge['Room Charge Type'] == 'true' ? 1 : 0,
                    'is_default_room_charge_type' => $charge['Tax Exempt'] == 'true' ? 1 : 0
                );

                $charge_type_id = $this->Charge_type_model->create_charge_types($data);

                $data_import_mapping = Array(
                    "new_id" => $charge_type_id,
                    "old_id" => $charge['Charge Type Id'],
                    "company_id" => $this->company_id,
                    "type" => "charge_type"
                );

                $import_data = $this->Import_mapping_model->insert_import_mapping($data_import_mapping);

            }else{
                $charge_type_id = isset($get_the_charge_type['new_id']) ? $get_the_charge_type['new_id'] : '';
            }

            $customer_id =  $this->Import_mapping_model->get_mapping_customer_id($charge['Customer Id']);


            if(!empty($charge['Charge Id'])){

                $charges =  $this->Import_mapping_model->get_mapping_charge($charge['Charge Id']);

                if(empty($charges))
                {
                    switch ($charge['Pay Period']) {
                        case "Daily" : $pay_period = '0'; break;
                        case "Weekly" : $pay_period = '1'; break;
                        case "Monthly" : $pay_period = '2'; break;
                        case "One time" : $pay_period = '3'; break;
                    }

                    $data = Array(
                        "description" => $charge['Description'] == '' ? null : $charge['Description'],
                        "date_time" =>$charge['Date Time'] != null ? $charge['Date Time'] : date('Y-m-d H:i:s') ,
                        "booking_id" => $charge['Booking Id'],
                        "amount" => $charge['Amount'] == '' ? 0 : $charge['Amount'],
                        "charge_type_id" => $charge_type_id,
                        "selling_date" => $charge['Selling Date'],
                        "customer_id" => $customer_id['new_id'],
                        "pay_period" => $pay_period,
                        "is_night_audit_charge" => $charge['Night Audit Charge'] == 'true' ? 1 : 0

                    );

                    $charge_id = $this->Charge_model->insert_charge($data);
                    $taxes = explode(',', $charge['Tax Type']);

                    foreach ($taxes as $tax_type) {
                        if($tax_type){
                            $tax_type_id = $this->Tax_model->get_tax_type_by_name($tax_type);
                            $charge_taxes = $this->Charge_type_model->get_charge_tax($charge_type_id, $tax_type_id);
                            if(!$charge_taxes){
                                $this->Charge_type_model->add_charge_type_tax($charge_type_id, $tax_type_id);
                            }
                        }

                    }

                    $data_import_mapping = Array(
                        "new_id" => $charge_id,
                        "old_id" => $charge['Charge Id'],
                        "company_id" => $this->company_id,
                        "type" => "charge"
                    );

                    $import_data = $this->Import_mapping_model->insert_import_mapping($data_import_mapping);
                }
            }

        }

    }


    function import_rates_csv($value){

        foreach ($value as $rate) {

            $get_rate_plan = $this->Rate_plan_model->get_rate_plan_by_name($rate['Name'], $this->company_id);
            
            $get_import_rate_plan = $this->Import_mapping_model->get_rate_plan_mapping_id($rate['Rate Plan Id']);

            $room_type =  $this->Import_mapping_model->get_mapping_room_type_id($rate['Room type Id']);

            $room_type_id = $room_type['new_id'];
            if(empty($room_type_id)){
                $room_type= $this->Room_type_model->get_room_type_name($rate['Room Type Name'] , $this->company_id);
                $room_type_id = $room_type[0]['id'];
            }

            if(empty($get_import_rate_plan)){

                $data = array(
                    "rate_plan_name" => $rate['Name'] == '' ? null : $rate['Name'],
                    "room_type_id" => $room_type_id,
                    "company_id" => $this->company_id,
                    "is_selectable" => $rate['Read Only'] == 'true' ? 1 : 0
                );
                $rate_plan_id = $this->Rate_plan_model->create_rate_plan($data);

                $data_import_mapping = Array(
                    "new_id" => $rate_plan_id,
                    "old_id" => $rate['Rate Plan Id'],
                    "company_id" => $this->company_id,
                    "type" => "rate_plan"
                );

                $import_data = $this->Import_mapping_model->insert_import_mapping($data_import_mapping);
            }
            else{
                $rate_plan_id = $get_import_rate_plan['new_id'];
            }


            $rates = $this->Import_mapping_model->get_rates_mapping_id($rate['Rate Id']);

            if(empty($rates)){

                $rate_id = $this->Rate_model->create_rate(
                    Array(
                        'rate_plan_id' => $rate_plan_id,
                        'base_rate' => $rate['Base Rate'] == '' ? null : $rate['Base Rate'],
                        'adult_1_rate' => $rate['Adult Rate 1'] ? $rate['Adult Rate 1'] : 0,
                        'adult_2_rate' => $rate['Adult Rate 2'] ? $rate['Adult Rate 2'] : 0,
                        'adult_3_rate' => $rate['Adult Rate 3'] ? $rate['Adult Rate 3'] : 0,
                        'adult_4_rate' => $rate['Adult Rate 4'] ? $rate['Adult Rate 4'] : 0,
                        'additional_adult_rate' => $rate['Additional Adult Rate'] ? $rate['Additional Adult Rate'] : 0,
                        'additional_child_rate' => $rate['Aditional Child Rate'] ? $rate['Aditional Child Rate'] : 0,
                        'minimum_length_of_stay' => $rate['Min Length of Stay'] ? $rate['Min Length of Stay'] : 0,
                        'maximum_length_of_stay' => $rate['Max Length of Stay'] ? $rate['Max Length of Stay'] : 0,
                        'closed_to_departure' => $rate['Close to Departure'] == 'true' ? 1 : 0,
                        'closed_to_arrival' => $rate['Close to Arrival'] == 'true' ? 1 : 0
                    )
                );

                $date_range_id = $this->Date_range_model->create_date_range(
                    Array(
                        'date_start' => $rate['From Date'] == '' ? null : $rate['From Date'],
                        'date_end' => $rate['To Date'] == '' ? null : $rate['To Date'],
                        'monday' => $rate['Monday'] == '' ? null : $rate['Monday'],
                        'tuesday' => $rate['Tuesday'] == '' ? null : $rate['Tuesday'],
                        'wednesday' => $rate['Wednesday'] == '' ? null : $rate['Wednesday'],
                        'thursday' => $rate['Thursday'] == '' ? null : $rate['Thursday'],
                        'friday' => $rate['Friday'] == '' ? null : $rate['Friday'],
                        'saturday' => $rate['Saturday'] == '' ? null : $rate['Saturday'],
                        'sunday' => $rate['Sunday']== '' ? null : $rate['Sunday']
                    )
                );

                $this->Date_range_model->create_date_range_x_rate(
                    Array(
                        'rate_id' => $rate_id,
                        'date_range_id' => $date_range_id
                    ));

                $data_import_mapping = Array(
                    "new_id" => $rate_id,
                    "old_id" => $rate['Rate Id'],
                    "company_id" => $this->company_id,
                    "type" => "rate"
                );

                $import_data = $this->Import_mapping_model->insert_import_mapping($data_import_mapping);
            }



        }
    }

    function import_customers_csv($value){

        foreach ($value as $customer) {

            $get_customer_type = $this->Customer_type_model->get_customer_type_by_name($this->company_id, $customer['Customer Type']);
            if(empty($get_customer_type)){
                $customer_type_id = $this->Customer_type_model->create_customer_type($this->company_id, $customer['Customer Type']);
            }else{
                $customer_type_id = isset($get_customer_type[0]['id']) ? $get_customer_type[0]['id'] : ' ' ;
            }

            $data = Array(
                "customer_name" => $customer['Customer Name'] == ''? null : $customer['Customer Name'],
                "address" => $customer['Address'] == ''? null : $customer['Address'],
                "email" => $customer['Email'] == ''? null : $customer['Email'],
                "city" => $customer['City'] == ''? null : $customer['City'],
                "region" => $customer['Region'] == ''? null : $customer['Region'],
                "customer_type_id" => $customer_type_id,
                "company_id" => $this->company_id
            );

            $get_customer = $this->Import_mapping_model->get_mapping_customer_id($customer['Customer Id']);

            if(!empty($customer['Customer Id'])){
                if(empty($get_customer)){
                    $customer_id = $this->Customer_model->create_customer($data);
                    $data_import_mapping = Array(
                        "new_id" => $customer_id,
                        "old_id" => $customer['Customer Id'],
                        "company_id" => $this->company_id,
                        "type" => "customer"
                    );
                    $import_data = $this->Import_mapping_model->insert_import_mapping($data_import_mapping);
                }
            }


        }

    }



    function import_bookings_csv($value){

        // prx($value);

        foreach ($value as $booking) {
            $charge_type_id = $this->Charge_type_model->get_charge_type_by_name($booking['Charge Type'], $this->company_id);
            $room_type_id = $this->Room_type_model->get_room_type_name($booking['Room Type'], $this->company_id);
            $room_id = $this->Room_model->get_room_by_name($booking['Room'] , $room_type_id[0]['id']);

            $customer_id =  $this->Import_mapping_model->get_mapping_customer_id($booking['Booking Customer Id']);
            $booked_by =  $this->Import_mapping_model->get_mapping_customer_id($booking['Booked By']);

            switch ($booking['State']) {
                case "Reservation" : $state = '0'; break;
                case "Checked-in" : $state = '1'; break;
                case "Checked-out" : $state = '2'; break;
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

            switch ($booking['Source']) {
                case "walk In" : $source = '0'; break;
                case "Online Widget" : $source = '1'; break;
                case "Booking Dot Com" : $source = '2'; break;
                case "Expedia" : $source = '3'; break;
                case "Agoda" : $source = '4'; break;
                case "Trip Connect" : $source = '5'; break;
                case "Air BNB" : $source = '6'; break;
                case "Hotel World" : $source = '7'; break;
                case "Myallocator" : $source = '8'; break;
                case "Company" : $source = '9'; break;
                case "Guest Member" : $source = '10'; break;
                case "Owner" : $source = '11'; break;
                case "Returning Guest" : $source = '12'; break;
                case "Apartment" : $source = '13'; break;
                case "sitminder" : $source = '14'; break;
                case "Seasonal" : $source = '15'; break;
                case "Other taravel agency" : $source = '20'; break;

            }

            if(empty($source)){

                $get_source = $this->Booking_source_model->get_booking_source_by_company($this->company_id, $booking['Source']);
                if(empty($get_source)){
                    $source = $this->Booking_source_model->create_booking_source($this->company_id, $booking['Source']);
                }else{
                    $source = isset($get_source['id']) ? $get_source['id'] : 0 ;
                }

            }

            $booking_id =  $this->Import_mapping_model->get_mapping_booking_id($booking['Booking Id']);

            if(empty($booking_id)){
                $data = Array(
                    "rate" => $booking['Rate'] == '' ? null : $booking['Rate'],
                    "adult_count" => $booking['Adult Count'] == '' ? null : $booking['Adult Count'],
                    "children_count" => $booking['Children Count'] == '' ? null : $booking['Children Count'],
                    "booking_customer_id" => $customer_id['new_id'],
                    "booking_notes" => $booking['Booking Note'] == '' ? null : $booking['Booking Note'] ,
                    "booked_by" => $booking['Booked By'] == '' ? null : $booked_by['new_id'],
                    "balance" => $booking['Balance'] == '' ? null : $booking['Balance'],
                    "balance_without_forecast" => $booking['Balance Without Forecast'] == '' ? null : $booking['Balance Without Forecast'],
                    "use_rate_plan" => $booking['Use Rate Plan'] == 'true' ? 1 : 0,
                    "rate_plan_id" => $booking['Rate Plan Id'] == '' ? null : $booking['Rate Plan Id'],
                    "charge_type_id" => $charge_type_id['id'],
                    "pay_period" => isset($pay_period) ? $pay_period : 0,
                    "source" => isset($source) ? $source : 0 ,
                    "company_id" => $this->company_id,
                    "state" => isset($state) ? $state : 0

                );

                $booking_id = $this->Booking_model->create_booking($data);

                $data_import_mapping = Array(
                    "new_id" => $booking_id,
                    "old_id" => $booking['Booking Id'],
                    "company_id" => $this->company_id,
                    "type" => "booking"
                );

                $import_data = $this->Import_mapping_model->insert_import_mapping($data_import_mapping);

                $charge_update = $this->Charge_model->update_charge_booking($booking['Booking Id'],$booking_id,$customer_id['new_id']);

                if(!empty($booking['Group Id'])){
                    $group_id =  $this->Import_mapping_model->get_mapping_group_booking_id($booking['Group Id']);

                    if(empty($group_id)){

                        $group_name = ($booking['Group Name']) != '' ? $booking['Group Name'] : null ;
                        $new_group_id = $this->Booking_linked_group_model->create_booking_linked_groups($group_name);
                        $data_import_mapping = Array(
                            "new_id" => $new_group_id,
                            "old_id" => $booking['Group Id'],
                            "company_id" => $this->company_id,
                            "type" => "group_booking"
                        );
                        $import_data = $this->Import_mapping_model->insert_import_mapping($data_import_mapping);

                    }else{

                        $new_group_id = $group_id['new_id'];
                    }

                    $data = array(
                        "booking_id " => $booking_id,
                        "booking_group_id" => $new_group_id
                    );

                    $booking_linke_group = $this->Booking_linked_group_model->insert_booking_x_booking_linked_group($data);

                }

            }

            $booking_block = $this->Import_mapping_model->get_mapping_booking_room_history_id($booking['Booking Room History']);

            if(empty($booking_block)){
                $booking_id =  $this->Import_mapping_model->get_mapping_booking_id($booking['Booking Id']);

                $booking_data_fileds = Array(
                    "booking_id" => $booking_id['new_id'],
                    "room_id" => isset($room_id[0]['room_id']) &&  $room_id[0]['room_id'] ? $room_id[0]['room_id'] : 0,
                    "room_type_id" => isset($room_type_id[0]['id']) && $room_type_id[0]['id'] ? $room_type_id[0]['id'] : 0,
                    "check_in_date" => $booking['Check In Date'] == '' ? null : $booking['Check In Date'],
                    "check_out_date" => $booking['Check Out Date'] == '' ? null : $booking['Check Out Date']

                );

                $booking_filed = $this->Booking_field_model->create_booking_fields($booking_data_fileds);

                $data_import_mapping = Array(
                    "new_id" => $booking_filed,
                    "old_id" => $booking['Booking Room History'],
                    "company_id" => $this->company_id,
                    "type" => "booking_block"
                );
                $import_data = $this->Import_mapping_model->insert_import_mapping($data_import_mapping);

            }


        }

    }

    function import_extras_csv($value){
        // prx($value);
        foreach ($value as $extra) {

            $extras = $this->Import_mapping_model->get_extra_mapping($extra['Extra Id']);
            $charge_type_id = $this->Charge_type_model->get_charge_type_by_name($extra['Charge Type'], $this->company_id);
            if(empty($extras)){

                $data = array (
                    'extra_name' => $extra['Extra Name'] != '' ? $extra['Extra Name']  : null ,
                    'company_id' => $this->company_id,
                    'extra_type' => $extra['Extra Type'] != '' ? $extra['Extra Type'] : null ,
                    'charging_scheme' => $extra['Charging Scheme'] != '' ? $extra['Charging Scheme'] : null ,
                    'show_on_pos' => $extra['Show on POS'],
                    'charge_type_id' => $charge_type_id['id'] ? $charge_type_id['id'] : 0

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
                $import_data = $this->Import_mapping_model->insert_import_mapping($data_import_mapping);

            }else{

                $extra_id = $extras['new_id'];
            }

            if($extra['Booking Id']){
                $booking_extra = $this->Import_mapping_model->get_booking_extras($extra['Booking Id']);

                if(empty($booking_extra)){

                    $booking_id = $this->Import_mapping_model->get_mapping_booking_id($extra['Booking Id']);

                    $booking_extra_id =  $this->Booking_extra_model->create_booking_extra($booking_id['new_id'],$extra_id,$extra['Start Date'],$extra['End Date'],$extra['Quantity'],$extra['Default Rate']);

                    $data_import_mapping = Array(
                        "new_id" => $booking_extra_id,
                        "old_id" => $extra['Booking Extra Id'],
                        "company_id" => $this->company_id,
                        "type" => "extra_booking"
                    );
                    $import_data = $this->Import_mapping_model->insert_import_mapping($data_import_mapping);

                }
            }

          

        }

    }



    function import_payments_csv($value){

        foreach ($value as $payment) {
            $get_payment_type = $this->Payment_model->get_payment_types_by_name($payment['Payment Type']);

            if(empty($get_payment_type)){
                $payment_id = $this->Payment_model->create_payment_type($this->company_id, $payment['Payment Type']);
            }else{
                $payment_id = isset($get_payment_type[0]->payment_type_id) ? $get_payment_type[0]->payment_type_id : ' ' ;
            }

            $customer_id =  $this->Import_mapping_model->get_mapping_customer_id($payment['Customer Id']);
            $booking_id =  $this->Import_mapping_model->get_mapping_booking_id($payment['Booking Id']);

            if(!empty($payment['Payment Id'])){
                $get_import_payment = $this->Import_mapping_model->get_mapping_payment_id($payment['Payment Id']);
                if(empty($get_import_payment)){
                    $data = Array(
                        "description" => $payment['Description'],
                        "date_time" => $payment['Date Time'] == '' ? null : $payment['Date Time'],
                        "booking_id" => $booking_id['new_id'],
                        "payment_type_id" => $payment_id,
                        "amount" => $payment['Amount'] == '' ? null : $payment['Amount'],
                        "credit_card_id" => $payment['Credit Card Id'] == '' ? null : $payment['Credit Card Id'],
                        "selling_date" => $payment['Selling Date'] == '' ? null : $payment['Selling Date'],
                        "customer_id" => $customer_id['new_id'],
                        "payment_status" => $payment['Payment Status'] == '' ? null : $payment['Payment Status'],
                        "is_captured" => $payment['Payment Capture'] == 'true' ? 1 : 0
                    );

                    $payment_create_id = $this->Payment_model->insert_payment($data);

                    $data_import_mapping = Array(
                        "new_id" => $payment_create_id['payment_id'],
                        "old_id" => $payment['Payment Id'],
                        "company_id" => $this->company_id,
                        "type" => "payment"
                    );

                    $import_data = $this->Import_mapping_model->insert_import_mapping($data_import_mapping);

                }
            }

        }

    }

    function import_company_setting($values){

        $value = json_decode($values,true);

        $company_data = array(
            'is_total_balance_include_forecast' => isset($value['Total Balance Include Forecast']) ? $value['Total Balance Include Forecast'] : ""  ,
            'auto_no_show'  => isset($value['Auto No Show']) ? $value['Auto No Show'] : "",
            'book_over_unconfirmed_reservations'=> isset($value['Book Over Unconfirmed Reservations']) ? $value['Book Over Unconfirmed Reservations'] : "" ,
            'send_invoice_email_automatically' => isset($value['Send Invoice Email Automatically']) ? $value['Send Invoice Email Automatically']: "",
            'hide_decimal_places'=> isset($value['Hide Decimal Places']) ? $value['Hide Decimal Places']: "",
            'automatic_email_confirmation' => isset($value['Automatic Email Confirmation']) ? $value['Automatic Email Confirmation'] : "",
            'automatic_email_cancellation' => isset($value['Automatic Email Cancellation']) ? $value['Automatic Email Cancellation'] : "",
            'send_booking_notes' => isset($value['Send Booking Notes']) ? $value['Send Booking Notes']: "",
            'make_guest_field_mandatory' => isset($value['Make Guest Field Mandatory']) ? $value['Make Guest Field Mandatory'] : "",
            'include_cancelled_noshow_bookings' => isset($value['Include Cancelled Noshow Bookings']) ? $value['Include Cancelled Noshow Bookings']: "",
            'hide_forecast_charges' => isset($value['Hide Forecast Charges']) ? $value['Hide Forecast Charges']:"",
            'send_copy_to_additional_emails' => isset($value['Send Copy To Additional Emails']) ? $value['Send Copy To Additional Emails']:"",
            'additional_company_emails' => isset($value['Additional Company Emails'])? $value['Additional Company Emails']: "",
            'default_charge_name' => isset($value['Default Charge Name']) ? $value['Default Charge Name']:"",
            'default_room_singular' => isset($value['Default Room Singular']) ? $value['Default Room Singular'] : "",
            'default_room_plural' => isset($value['Default Room Plural']) ? $value['Default Room Plural'] : "",
            'default_room_type'=> isset($value['Default Room Type'])? $value['Default Room Type'] : "",
            'date_format' => isset($value['Date Format']) ? $value['Date Format'] : "",
            'default_checkin_time' => isset($value['Default Checkin Time'])? $value['Default Checkin Time'] : "",
            'default_checkout_time' => isset($value['Default Checkout Time']) ? $value['Default Checkout Time'] : "",
            'enable_hourly_booking' => isset($value['Enable Hourly Booking']) ? $value['Enable Hourly Booking'] : "",
            'enable_api_access'=> isset($value['Enable Api Access']) ? $value['Enable Api Access'] : "",
            'booking_cancelled_with_balance' => isset($value['Booking Cancelled With Balance']) ? $value['Booking Cancelled With Balance'] : "",
            'enable_new_calendar' => isset($value['Enable New Calendar']) ? $value['Enable New Calendar'] : "",
            'hide_room_name' => isset($value['Hide Room Name']) ? $value['Hide Room Name'] : "",
            'restrict_booking_dates_modification' => isset($value['Restrict Booking Dates Modification']) ? $value['Restrict Booking Dates Modification'] : "",
            'restrict_checkout_with_balance' => isset($value['Restrict Checkout With Balance']) ? $value['Restrict Checkout With Balance'] : "",
            'show_guest_group_invoice' => isset($value['Show Guest Group Invoice']) ? $value['Show Guest Group Invoice'] : ""
        );
        $this->Company_model->update_company($this->company_id, $company_data);


    }


}
