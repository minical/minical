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


    public function __construct()
    {
        parent::__construct();
        $this->ci =& get_instance();
        
        $this->profiler_is_on = false;
        if($this->ci->input->get('dev_mode') == "b1m8V0I5ZT"){
            $this->ci->output->enable_profiler(TRUE);
            $this->profiler_is_on = true;
        }
        
        $this->load->library('tank_auth');
        $this->load->library('permission');
        $this->load->library('Template');
        $this->load->model(array('Booking_model','Menu_model','User_model','Whitelabel_partner_model','Company_model','Extension_model'));

        $this->load->helper('language');
        $this->load->helper('my_assets_helper');

        $this->controller_name = $this->ci->uri->rsegment(1);
        $this->function_name = $this->ci->uri->rsegment(2);

        // set language strings
        $language = $this->session->userdata('language');

        $this->language = $this->lang->language;
        $this->load->vars(array("l" => (object)$this->lang->language));

        $this->check_login();

        $all_active_modules = array();
        $modules_path = $this->config->item('module_location'); 
        $modules = scandir($modules_path);

        $extensions = $this->session->userdata('all_active_modules');
        
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
                else
                {
                    continue;
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
        

        // if(empty($extensions) || (count($extensions) != count($all_active_modules))){
            $this->session->set_userdata('all_active_modules', $all_active_modules);

        // }

        $this->module_assets_files = array();
        $modules_path = $this->config->item('module_location');     
        $modules = scandir($modules_path);

        foreach($modules as $module)
        {
            if($module === '.' || $module === '..') continue;
            if(is_dir($modules_path) . '/' . $module)
            {
                $config = array();
                $files_path = $modules_path . $module . '/config/autoload.php';
                if(file_exists($files_path) && $this->permission->is_extension_active($module, $this->company_id))
                {
                    require($files_path);
                    $this->module_assets_files[$module] = $config;
                }
                else
                {
                    continue;
                }
            }
        }

        $this->module_menus = array();
        $modules_path = $this->config->item('module_location');     
        $modules = scandir($modules_path);

        foreach($modules as $module)
        {
            if($module === '.' || $module === '..') continue;
            if(is_dir($modules_path) . '/' . $module)
            {
                $module_menu = array();
                $module_file = $modules_path . $module . '/config/menu.php';
                //prx($this->permission->is_extension_active($module, $this->company_id));
                if(file_exists($module_file) && $this->permission->is_extension_active($module, $this->company_id))
                {
                    require($module_file);
                    $this->module_menus[$module] = $module_menu;
                    //prx($module);
                }
                else
                {
                    continue;
                }
            }
        }
        // echo $this->router->fetch_module(); die;
        //prx($this->module_menus);

        require APPPATH."config/routes.php";

        foreach ($module_permission as $module) {
            if($this->router->fetch_module() != '' && !strpos($module, $this->router->fetch_module()) && !($this->permission->is_extension_active($this->router->fetch_module(), $this->company_id))){
                show_404();
            }
        }
    }

    public function check_login()
    {
        if ($this->tank_auth->is_logged_in()) 
        {
            $this->company_id = $this->ci->session->userdata('current_company_id');

            $company = $this->ci->Company_model->get_company($this->company_id);
            $company_key_data = $this->ci->Company_model->get_company_api_permission($this->company_id);

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

            $user = $this->User_model->get_user_by_id($this->user_id, FALSE);
            $this->company_is_tos_agreed = ($user['tos_agreed_date'] >= TOS_PUBLISH_DATE);
            $this->is_overview_calendar = false; // $user['is_overview_calendar'];

            $this->enable_new_calendar = $company['enable_new_calendar'];
            $this->enable_hourly_booking = $this->enable_new_calendar ? $company['enable_hourly_booking'] : false;

            $this->first_name = $user['first_name'];
            $this->last_name = $user['last_name'];

            $whitelabelinfo = $this->ci->session->userdata('white_label_information');

            $admin_user_ids = $this->Whitelabel_partner_model->get_partner_detail();
            $this->is_super_admin = ($this->company_email == SUPER_ADMIN ||  $this->user_id == $admin_user_ids['admin_user_id']);

            if($this->is_super_admin){
                $get_active_extensions = $this->Extension_model->get_active_extensions($this->company_id, 'reseller_package');
                if(empty($get_active_extensions) && $this->company_id){
                    $new_extensions = array(
                                    'extension_name' => 'reseller_package',
                                    'company_id' => $this->company_id,
                                    'is_active' => 1
                                );
                    $this->Extension_model->add_extension($new_extensions);
                }
            }

            $host_name = $_SERVER['HTTP_HOST'];
            if ((!$whitelabelinfo && $this->company_data['partner_id']) || ($whitelabelinfo && ($host_name ==  'http://'. $_SERVER['HTTP_HOST']) && isset($whitelabelinfo['id']) && $whitelabelinfo['id'] != $this->company_data['partner_id'])) {
                $white_label_detail = $this->Whitelabel_partner_model->get_partners(array('id' => $this->company_data['partner_id']));
                if($white_label_detail && isset($white_label_detail[0])) {
                    $this->session->set_userdata('white_label_information', $white_label_detail[0]);
                }
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
            // if user is not logged-in, but the controller & function combination is publicly accessible

            if ($this->permission->is_function_public($this->controller_name, $this->function_name)) 
            {
                return;
            }

            redirect('/auth/login/');
            
        }
    }

}
