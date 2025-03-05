<?php

/* 
*   Base Controller that takes care of Security & Permission (User Access) of Minical
*/
class MY_Controller extends CI_Controller {

    public $controller_name;
    public $function_name;
    public $user_id;
    public $company_id;
    public $company_name;
    public $selling_date;
    public $api_key;
    public $language;
    public $module_assets_files;
    public $module_menus;
    public $current_payment_gateway;
    public $is_super_admin;
    public $all_active_modules;
    public $cache_values;
    public $import_insert_batch;


    public function __construct()
    {
        parent::__construct();
        $this->ci =& get_instance();
        
        $this->profiler_is_on = false;
        if(isset($_GET['dev_mode']) && $_GET['dev_mode'] == getenv('DEVMODE_PASS')){
            $this->ci->output->enable_profiler(TRUE);
            $this->profiler_is_on = true;
        }
        
        $this->load->library('tank_auth');
        $this->load->library('permission');
        $this->load->library('Template');
        $this->load->model(array('Booking_model','Menu_model','User_model','Whitelabel_partner_model','Company_model','Extension_model','Booking_source_model', 'Option_model', 'Company_security_model'));

        $this->load->helper('language');
        $this->load->helper('my_assets_helper');

        $this->controller_name = $this->ci->uri->rsegment(1);
        $this->function_name = $this->ci->uri->rsegment(2);

        // set language strings
        $language = $this->session->userdata('language');

        $this->language = $this->lang->language;
        $this->load->vars(array("l" => (object)$this->lang->language));

        $this->image_url = "https://".getenv("AWS_S3_BUCKET").".s3.amazonaws.com/";

        $this->check_login();

        $all_active_modules = array();
        $modules_path = $this->config->item('module_location'); 
        $modules = scandir($modules_path);

        // $extensions = $this->session->userdata('all_active_modules');
        
        foreach($modules as $module)
        {
            if($module === '.' || $module === '..') continue;
            if(is_dir($modules_path) . '/' . $module)
            {
                $config = array();
                $module_config = $modules_path . $module . '/config/config.php';
                if(file_exists($module_config))
                {
                    require($module_config);
                    $config['extension_folder_name'] = $module;
                    $all_active_modules[$module] = $config;

                    if(isset($config['gateway_key']) && isset($this->selected_payment_gateway) && $config['gateway_key'] == $this->selected_payment_gateway ){
                        $this->current_payment_gateway = $module;
                    }

                }
            }
        }

        if($all_active_modules){
            foreach($all_active_modules as $key => $mod)
            {
                $name = strtolower($mod['extension_folder_name']);
                $all_active_modules[$key]['extension_folder_name'] = str_replace(" ","_",$name);
            }
        }

        $this->all_active_modules = $all_active_modules;

        // $this->session->set_userdata('all_active_modules', $all_active_modules);

        $this->module_assets_files = array();
        $modules_path = $this->config->item('module_location');     
        $modules = scandir($modules_path);

        if(!$this->company_id && $this->uri->segment(3) != ''){
            $company_id = $this->uri->segment(3);
        } else {
            $company_id = $this->company_id;
        }

        if($company_id){
            $this->session->set_userdata('anonymous_company_id', $company_id);
        } else {
            $company_id = $this->session->userdata('anonymous_company_id');
        }

        $all_modules = $get_active_modules = array();

        foreach($modules as $module)
        {
            if($module === '.' || $module === '..') continue;
            if(is_dir($modules_path) . '/' . $module)
            {
                $all_modules[] = $module;
            }
        }

        if($all_modules){
            $get_active_modules = $this->permission->is_extension_active($all_modules, $company_id, true);
        }

        $this->is_channex_pci_enabled = false;
        $this->is_pci_booking_enabled = false;
        $this->is_intercom_enabled = false;
        $this->is_kovena_enabled = false;
        $this->booking_confirmation_email = false;
        $this->invoice_email = false;
        $this->is_custom_invoice_enabled = false;
        $this->review_management_settings = false;
        $this->is_cardknox_enabled = false;
        $this->is_nestpay_enabled = false;
        $this->is_nestpaymkd_enabled = false;
        $this->is_nestpayalb_enabled = false;
        $this->is_nestpaysrb_enabled = false;
        $this->is_oevai_enabled = false;
        $this->is_housekeeper_manage_enabled = false;
        $this->is_invoice_transfer_enabled = false;
        $this->is_loyalty_program = false;
        $this->is_easypos_fisical_enabled = false;
        $this->is_derived_rate_enabled = false;
        $this->is_group_booking_features = false;

        if($get_active_modules){
            foreach ($get_active_modules as $key => $value) {

                if($value['extension_name'] == 'channexpci_integration'){
                    $this->is_channex_pci_enabled = true;
                }
                if($value['extension_name'] == 'pcibooking-integration'){
                    $this->is_pci_booking_enabled = true;
                }
                if($value['extension_name'] == 'intercom'){
                    $this->is_intercom_enabled = true;
                }
                if($value['extension_name'] == 'kovena_integration'){
                    $this->is_kovena_enabled = true;
                }
                if($value['extension_name'] == 'booking_confirmation_email'){
                    $this->booking_confirmation_email = true;
                }
                if($value['extension_name'] == 'invoice_email'){
                    $this->invoice_email = true;
                }
                if($value['extension_name'] == 'custom_invoice'){
                    $this->is_custom_invoice_enabled = true;
                }
                if($value['extension_name'] == 'review_management_settings'){
                    $this->review_management_settings = true;
                }
                if($value['extension_name'] == 'cardknox-integration'){
                    $this->is_cardknox_enabled = true;
                }
                 if($value['extension_name'] == 'nestpay_integration'){
                     $this->is_nestpay_enabled = true;
                }
                if($value['extension_name'] == 'nestpaymkd_integration'){
                     $this->is_nestpaymkd_enabled = true;
                }
                if($value['extension_name'] == 'nestpayalb_integration'){
                    $this->is_nestpayalb_enabled = true;
                }
                if($value['extension_name'] == 'nestpaysrb_integration'){
                    $this->is_nestpaysrb_enabled = true;
                }
                if($value['extension_name'] == 'oevai_integration'){
                    $this->is_oevai_enabled = true;
                }
                if($value['extension_name'] == 'housekeeper_management'){
                    $this->is_housekeeper_manage_enabled = true;
                }
                if($value['extension_name'] == 'invoice_transfer'){
                    $this->is_invoice_transfer_enabled = true;
                }
                if($value['extension_name'] == 'loyalty_program'){
                    $this->is_loyalty_program = true;
                }
                if($value['extension_name'] == 'easypos_fisical_integration'){
                    $this->is_easypos_fisical_enabled = true;
                }
                if($value['extension_name'] == 'derived_rate_manager'){
                    $this->is_derived_rate_enabled = true;
                }
                if($value['extension_name'] == 'group_booking_features'){
                    $this->is_group_booking_features = true;
                }

                $config = array();
                $files_path = $modules_path . $value['extension_name'] . '/config/autoload.php';
                if(file_exists($files_path))
                {
                    require($files_path);
                    $this->module_assets_files[$value['extension_name']] = $config;
                }
            }
        }

        $this->module_menus = array();

        if($get_active_modules){
            foreach ($get_active_modules as $key => $value) {

                $module_menu = array();
                $module_file = $modules_path . $value['extension_name'] . '/config/menu.php';
                if(file_exists($module_file))
                {
                    require($module_file);
                    $this->module_menus[$value['extension_name']] = $module_menu;
                }
            }
        }
        
        require APPPATH."config/routes.php";

        if (isset($module_permission) && count($module_permission) > 0) {
            foreach ($module_permission as $key => $module) {

                if ($this->router->fetch_module() && strpos($module, $this->router->fetch_module()) !== FALSE) {
                    if (
                        isset($company_id) &&
                        $company_id &&
                        (strpos($key, 'cron') == 0 || strpos($key, 'public') == 0) &&
                        ($this->permission->is_extension_active($this->router->fetch_module(), $company_id))
                    ) {
                        // let it run
                    } else {
                        if(
                            isset($company_id) &&
                            $company_id &&
                            ($this->permission->is_extension_active($this->router->fetch_module(), $company_id))
                        ){
                            // let it run
                        } else {
                            show_404();
                        }
                    }
                } else {
                    // continue with loop
                }
            }
        }

        $active_extensions = $this->Extension_model->get_active_extensions($company_id);
        $modules_path = APPPATH.'extensions/';

        $active_modules = array();
        if($active_extensions){
            foreach ($active_extensions as $key => $extension) {
                $active_modules[] = $extension['extension_name'];
            }
        }

        $autoload_helpers = array();
        $autoload_packages = array();

        if($active_modules && count($active_modules) > 0){
            foreach($active_modules as $module)
            {
                $extension_helper = array();
                if($module === '.' || $module === '..') continue;
                if(is_dir($modules_path) . '/' . $module)
                {

                    if(file_exists('application/extensions/'.$module . '/hooks/actions.php')) {
                        $autoload_packages[$module.'-actions'] = '../extensions/'.$module . '/hooks/actions';
                    }
                    if(file_exists('application/extensions/'.$module . '/hooks/filters.php')) {
                        $autoload_packages[$module.'-filters'] = '../extensions/'.$module . '/hooks/filters';
                    }

                    $helpers_path = $modules_path . $module . '/config/autoload.php';
                    if(file_exists($helpers_path))
                    {
                        require($helpers_path);

                        if($extension_helper && is_array($extension_helper)){
                            foreach($extension_helper as $key => $extension_helper_item) {
                                if ($extension_helper_item) {
                                    $autoload_helpers[$extension_helper_item] = '../extensions/'.$module . '/helpers/' . $extension_helper_item;
                                }
                            }
                        }
                    }
                }
            }
        }

        if($autoload_helpers && count($autoload_helpers) > 0)
            $this->load->helper($autoload_helpers);
        if($autoload_packages && count($autoload_packages) > 0)
            $this->load->helper($autoload_packages, true);

    }

    public function check_login()
    {
        if ($this->tank_auth->is_logged_in()) 
        {
            $user_restriction = $this->Option_model->get_option_by_user('login_security', $this->ci->session->userdata('user_id'));

            if($user_restriction){
                $minical_access = json_decode($user_restriction[0]['option_value'], true);

                if($minical_access['login_security_otp_verified'] == 0) {

                    $email = $this->ci->session->userdata('email');

                    $encode_email = base64_encode($email);
                    $encode_from = base64_encode('security');

                    if( 
                        $this->uri->segment(1) != 'auth' &&
                        $this->uri->segment(2) != 'show_qr_code'
                    ) {

                        redirect('auth/show_qr_code?email='.$encode_email.'&from='.$encode_from, 'refresh');
                        // return false;
                    }
                }
            }

            $this->company_id = $this->ci->session->userdata('current_company_id');

            $company = $this->ci->Company_model->get_company($this->company_id);
            $company_key_data = $this->ci->Company_model->get_company_api_permission($this->company_id);
            $company_security_data = $this->ci->Option_model->get_option_by_company('company_security', $this->company_id);

            $company_security = array();
            if($company_security_data)
                $company_security = json_decode($company_security_data[0]['option_value'], true);

            if(!$this->input->is_ajax_request() && !($company && isset($company['company_id']) && $company['company_id'])){
                $controller_name = $this->ci->uri->rsegment(1);
                if($controller_name != "properties" && $controller_name != "menu" && $controller_name != "auth" && $controller_name != "admin"){
                    $this->session->set_flashdata('flash_warning_message', 'Please select a property.');
                    redirect('/properties/my_properties');
                }
            }
            
            $this->company_key_data = $company_key_data;

            if(isset($company_key_data[0]['key'])){
                $this->company_api_key = $company_key_data[0]['key'];
            }
            
            $this->company_data = $company;
            $this->company_name = $company['name'];
            $this->company_email = $company['email'];
            $this->company_timezone = $company['time_zone'];
            $this->company_subscription_level = $company['subscription_level'];
            $this->company_subscription_state = $company['subscription_state'];
            $this->company_feature_limit = $company['limit_feature'];
            $this->company_creation_date = $company['creation_date'];
            
            if($company_security){
                $this->company_lock_time = $company_security['lock_timer'];
                $this->company_security_status = $company_security['security_status'];
            }

            $this->company_partner_id = $company['partner_id'];
            $this->company_force_room_selection = $company['force_room_selection'];

            $this->automatic_email_confirmation = $company['automatic_email_confirmation'];
            $this->automatic_email_cancellation = $company['automatic_email_cancellation'];
            
            $company_partner_type_id = $this->Whitelabel_partner_model->get_partner_detail($company['partner_id']);
            $this->company_partner_type_id = isset($company_partner_type_id) && isset($company_partner_type_id['type_id']) ? $company_partner_type_id['type_id'] : 1;
            $this->company_ui_theme = isset($company['ui_theme']) ? $company['ui_theme'] : 0;
            
            $this->selling_date = $company['selling_date'] ? $company['selling_date'] : date('Y-m-d');
            $this->api_key = $company['api_key'];
            $this->user_id = $this->ci->session->userdata('user_id');
            $this->is_tokenization_enabled = $company['enable_card_tokenization'];
            $this->is_cc_visualization_enabled = $company['is_cc_visualization_enabled'];
            $this->is_total_balance_include_forecast = $company['is_total_balance_include_forecast'];
            $this->is_display_tooltip = $company['is_display_tooltip'];
            $this->avoid_dmarc_blocking = $company['avoid_dmarc_blocking'];
            $this->allow_free_bookings = $company['allow_free_bookings'];
            $this->company_date_format = $company['date_format'];
            $this->default_room_singular = $company['default_room_singular'];
            $this->default_room_plural = $company['default_room_plural'];
            $this->default_room_type = $company['default_room_type'];
            $this->default_checkin_time = $company['default_checkin_time'];
            $this->default_checkout_time = $company['default_checkout_time'];
            $this->selected_payment_gateway = $company['selected_payment_gateway'];
            $this->booking_cancelled_with_balance = $company['booking_cancelled_with_balance'];
            $this->restrict_cvc_not_mandatory = $company['restrict_cvc_not_mandatory'];
            $this->restrict_edit_after_checkout = $company['restrict_edit_after_checkout'];
            $this->restrict_checkout_with_balance = $company['restrict_checkout_with_balance'];
            $this->calendar_days = $company['calendar_days'];

            $this->security_data =  $this->Company_security_model->get_deatils_by_company_user(null, $this->user_id);

            $this->security_data_length = count($this->security_data);
            
            $user = $this->User_model->get_user_by_id($this->user_id);
            $this->user_email = $user['email'];
            $this->company_is_tos_agreed = ($user['tos_agreed_date'] >= TOS_PUBLISH_DATE);
            $this->is_overview_calendar = false; // $user['is_overview_calendar'];

            $this->enable_new_calendar = $company['enable_new_calendar'];
            $this->enable_hourly_booking = $this->enable_new_calendar ? $company['enable_hourly_booking'] : false;

            $this->first_name = $user['first_name'];
            $this->last_name = $user['last_name'];

            $whitelabelinfo = $this->ci->session->userdata('white_label_information');

            $this->vendor_currency_id = isset($whitelabelinfo['currency_id']) && $whitelabelinfo['currency_id'] ? $whitelabelinfo['currency_id'] : 'USD';
            $admin_user_ids = $this->Whitelabel_partner_model->get_partner_detail();
            $this->is_super_admin = (($user && isset($user['email']) && $user['email'] == SUPER_ADMIN) || ($admin_user_ids && isset($admin_user_ids['admin_user_id']) && $this->user_id == $admin_user_ids['admin_user_id']));

            $this->vendor_id = isset($admin_user_ids['partner_id']) && $admin_user_ids['partner_id'] ? $admin_user_ids['partner_id'] : $this->company_data['partner_id'];
            $this->user_permission = ($user && isset($user['permission']) && $user['permission']) ? $user['permission'] : '';

            $this->is_partner_owner = ($admin_user_ids && isset($admin_user_ids['admin_user_id']) && $this->user_id == $admin_user_ids['admin_user_id']);
            $this->is_partner_admin = (isset($admin_user_ids['admin_user_id']) && $this->user_id == $admin_user_ids['admin_user_id'] ) ? 1 :0;
            
            // Will be used for support, vendor, and property owner
            $this->is_property_owner = (($user && isset($user['email']) && $user['email'] == SUPER_ADMIN) || ($admin_user_ids && isset($admin_user_ids['admin_user_id']) && $this->user_id == $admin_user_ids['admin_user_id']) || $this->user_permission == 'is_owner');

            $common_booking_sources = json_decode(COMMON_BOOKING_SOURCES, true);
            $i = 0;
            $booking_sources = $this->Booking_source_model->get_common_booking_sources_settings($this->company_id);
            
            if(empty($booking_sources)){
                foreach($common_booking_sources as $key => $source)
                {
                    $data = array(
                        'booking_source_id' => $key,
                        'company_id' => $this->company_id,
                        'is_hidden' => 0,
                        'sort_order' => $i++,
                        'commission_rate' => 0
                    );
                    $this->Booking_source_model->update_common_booking_sources_settings($this->company_id, $key, $data);
                }
            }

            $host_name = $_SERVER['HTTP_HOST'];
            $protocol = $this->config->item('server_protocol');
            $is_hosted_prod_service = getenv('IS_HOSTED_PROD_SERVICE');
            if ((!$whitelabelinfo && $this->company_data['partner_id']) || ($whitelabelinfo && ($is_hosted_prod_service || $host_name ==  'app.minical.io' || $host_name ==  'demo.minical.io') && isset($whitelabelinfo['id']) && $whitelabelinfo['id'] != $this->company_data['partner_id'])) {
                $white_label_detail = $this->Whitelabel_partner_model->get_partners(array('id' => $this->company_data['partner_id']));
                if($white_label_detail && isset($white_label_detail[0])) {
                    $this->session->set_userdata('white_label_information', $white_label_detail[0]);
                }
            }

            if(
                isset($whitelabelinfo['domain']) && 
                $whitelabelinfo['domain'] &&
                $host_name == $whitelabelinfo['domain']
            ) {
                $this->is_self_hosted_domain = 1;
            }
            else {
                $this->is_self_hosted_domain = 0;
            }

            if(
                $this->company_feature_limit == 1 && 
                $this->company_subscription_state != 'trialing' &&
                !empty($this->Company_model->get_subscription_restriction(
                                            $this->company_subscription_level,
                                            $this->controller_name, 
                                            $this->function_name)
                )
            )
            {
                redirect('/auth/access_restriction');
                exit;
            }
            elseif (
                $this->permission->check_access_to_function(
                                            $this->user_id,
                                            $this->company_id,
                                            $this->controller_name, 
                                            $this->function_name)
            )
            {
                return;
            }
            
            else
            {   
                if ($this->input->is_ajax_request()) {
                    echo "You don't have permission to access this functionality.";
                    exit;
                }
                else {
                    redirect('/auth/forbidden');
                    exit;
                }
            }
        }
        else
        {
            $this->enable_hourly_booking =  false;
            // if user is not logged-in, but the controller & function combination is publicly accessible

            if ($this->permission->is_function_public($this->controller_name, $this->function_name)) 
            {
                return;
            }

            if ($this->permission->is_route_public($this->uri->segment(1)))
            {
                return;
            }

            if (
                $this->controller_name === "page" &&
                (
                    $this->function_name === 'redirectToPages'
                )
            ) {
                $builder_url = getenv('BUILDER_URL');
                $company = $this->Company_model->get_company($this->company_id);
                $website_uri = strtolower($this->uri->segment(2));
                $website_route = strtolower($this->uri->segment(3));
                if ($website_uri && $website_route =='index' ) {
                redirect($builder_url."pages/page/".$website_uri.'/'.$website_route, 'location', 301);
                } elseif ($website_uri && $website_route =='room_types' ) {
                    redirect($builder_url."pages/page/".$website_uri.'/'.$website_route, 'location', 301);
                } elseif ($website_uri && $website_route =='gallery' ) {
                    redirect($builder_url."pages/page/".$website_uri.'/'.$website_route, 'location', 301);
                } elseif ($website_uri && $website_route =='location' ) {
                    redirect($builder_url."pages/page/".$website_uri.'/'.$website_route, 'location', 301);
                }
            }

            redirect('/auth/login/');
            
        }
    }

}
