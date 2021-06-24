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


}
