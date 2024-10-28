<?php

use LooplineSystems\CloseIoApiWrapper\CloseIoApiWrapper as CL_Wrapper;
use LooplineSystems\CloseIoApiWrapper\CloseIoConfig as CL_Config;
use LooplineSystems\CloseIoApiWrapper\Model\Address as CL_Address;
use LooplineSystems\CloseIoApiWrapper\Model\Contact as CL_Contact;
use LooplineSystems\CloseIoApiWrapper\Model\Lead as CL_Lead;
use LooplineSystems\CloseIoApiWrapper\Model\Phone as CL_Phone;
use LooplineSystems\CloseIoApiWrapper\Model\Email as CL_Email;


if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Auth extends MY_Controller
{

    function __construct()
    {
        parent::__construct();

        $this->load->helper(array('form', 'url', 'timezone'));

        $this->load->library('form_validation');
        $this->load->library('security');
        $this->load->library('tank_auth');
        $this->load->library('google_security');

        $this->lang->load('tank_auth');

        $this->load->model('User_model');
        $this->load->model('Company_model');
        $this->load->model('Customer_type_model');
        $this->load->model('Image_model');
        $this->load->model('tank_auth/users');
        $this->load->model('Wizard_model');
        $this->load->model('Room_type_model');
        $this->load->model('Rate_model');
        $this->load->model('Room_model');
        $this->load->model('Date_range_model');
        $this->load->model('Employee_log_model');
        $this->load->model('Whitelabel_partner_model');
        $this->load->model('Extension_model');
        $this->load->model('Option_model');
        $this->load->model('Company_security_model');
        $this->test_email = 'test@minical.io';
    }

    function index()
    {
        if (strtolower($_SERVER['HTTP_HOST']) == 'localhost')
        {
            redirect('/auth/login/');
        }
        else
        {
            redirect('/auth/login/');
        }
    }

    // for migration. called from migrate.js
    function run_query($query_index)
    {
        if (!$this->tank_auth->is_logged_in() || $this->session->userdata('user_role') != "is_admin") {
            redirect('/booking/');
        }

        $this->load->helper('file');

        $sql_string = $this->load->file('./queries/migrate.sql', true);
        $sql_array  = explode(";", $sql_string);
        if (isset($sql_array[$query_index])) {
            $sql   = $sql_array[$query_index];
            $query = $this->db->query($sql);
            str_replace("\n", "<br/>", $sql);
            //echo $sql."<br/><br/>".$this->db->_error_message()."<br/><br/>";
        }
    }


    function migrate()
    {
        if (!$this->tank_auth->is_logged_in() || $this->session->userdata('user_role') != "is_admin") {
            redirect('/booking/');
        }
        //$this->output->enable_profiler(TRUE);

        $data['js_files']     = array(
            base_url().auto_version('js/migrate.js')
        );
        $data['main_content'] = 'auth/migrate';

        $this->load->view('includes/bootstrapped_template', $data);
    }

    /**
     * Login user on the site
     *
     * @return void
     */
    function login()
    {
        $this->load->model('user_model');
        $host_name = $_SERVER['HTTP_HOST'];
        $white_label_name = explode('.', $host_name);
        if(count($white_label_name) > 0)
        {
            $white_label_name = $white_label_name[0];
        }
        $data['whitelabel_detail'] = '';
        $white_label_detail = $this->Whitelabel_partner_model->get_partner_by_username($white_label_name);
        if($white_label_detail)
        {
            $white_label_detail = $white_label_detail[0];
            $data['whitelabel_detail'] = $white_label_detail;
        }
        else
        {           
            $white_label_detail = $this->Whitelabel_partner_model->get_partners(array('domain' => $host_name));
            if($white_label_detail)
            {
                $white_label_detail = $white_label_detail[0];
                $data['whitelabel_detail'] = $white_label_detail;
            }
        }
        if($data['whitelabel_detail'])
        {
            $this->session->set_userdata('white_label_information', $white_label_detail);
        } else {
            $white_label_detail = $this->Whitelabel_partner_model->get_partners(array('id' => 0)); // default Minical
            if($white_label_detail)
            {
                $white_label_detail = $white_label_detail[0];
                $data['whitelabel_detail'] = $white_label_detail;
                $this->session->set_userdata('white_label_information', $white_label_detail);
            }
        }

        $data['message'] = ($this->session->flashdata('message')) ? $this->session->flashdata('message') : "";

        if ($this->tank_auth->is_logged_in()) { // if user is already logged in
            redirect('/menu/select_hotel/'.$this->company_id);
        }
        else 
        { // time to authenticate!

            $this->form_validation->set_rules('login', 'Email', 'trim|required|xss_clean');
            $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
            $this->form_validation->set_rules('remember', 'Remember me', 'integer');

            $email = $this->input->post('login');
            $user_activation = $this->user_model->get_user_by_email($email);
            
            
            // Get login for counting attempts to login
            if ($this->config->item('login_count_attempts', 'tank_auth') AND
                ($login = $this->input->post('login'))
            ) {
                $login = $this->security->xss_clean($login);
            } else {
                $login = '';
            }

            $data['use_recaptcha'] = $this->config->item('use_recaptcha', 'tank_auth');
//            if ($this->tank_auth->is_max_login_attempts_exceeded($login)) {
//                if ($data['use_recaptcha']) {
//                    $this->form_validation->set_rules('recaptcha_response_field', 'Confirmation Code', 'trim|xss_clean|required|callback__check_recaptcha');
//                } else {
//                    $this->form_validation->set_rules('captcha', 'Confirmation Code', 'trim|xss_clean|required|callback__check_captcha');
//                }
//            }
            $data['errors'] = array();

            $this->form_validation->set_value('login');
            $password = $this->input->post('password');

            $this->form_validation->set_rules('login', 'Incorrect Login', 'callback__incorrect_login['.$password.']');

            // validation ok
            if ($this->form_validation->run()) {

                // if user hasn't agreed to Terms of Service yet, redirect to terms of service
                // otherwise, log the user in

                $user_id =  $this->session->userdata('user_id');
                $employee_log = $this->Employee_log_model->get_log(array('selling_date'=> gmdate('Y-m-d'),'user_id' => $user_id, 'log' => 'Logged in'));
                if(empty($employee_log))
                {
                    $employee_log_data = array();
                    $employee_log_data['user_id'] = $user_id;
                    $employee_log_data['selling_date'] = gmdate('Y-m-d');
                    $employee_log_data['log'] = 'Logged in';
                    $employee_log_data['date_time'] = gmdate('Y-m-d H:i:s');
                    $this->Employee_log_model->insert_log($employee_log_data);
                }

                $employee_permission['permissions'] = $this->Employee_log_model->get_user_permission($this->session->userdata('current_company_id'),$this->session->userdata('user_id'));
                $this->session->set_userdata($employee_permission);
                
                $is_db_name = getenv('DATABASE_NAME');
                
                // if(
                //     $is_db_name != 'minical-prod'
                // ){
                //     redirect('/booking');
                // } 

                // $this->verify_otp($email, $host_name);


                $admin_data = $this->Whitelabel_partner_model->get_whitelabel_partner_id($this->session->userdata('user_id'));

                if($admin_data){

                    $vendor_companies = $this->Company_security_model->get_vendor_companies($admin_data['partner_id']);
                    // lq();
                    // $vendor_company_ids = $vendor_compnies;
                    $vendor_company_ids = explode(',', $vendor_companies['comp_ids']);

                    $security_data = $this->Option_model->get_option_by_company('company_security',$vendor_company_ids, true);


                    // $mathced_vendor_company_ids = $this->Option_model->get_option_by_company('company_security',$vendor_company_ids, true);

                    // $vendor_comp_id = $mathced_vendor_company_ids[0]['company_id'];

                    // $security_data = $this->Option_model->get_option_by_company('company_security',$vendor_comp_id);
                } else {
                    $security_data = $this->Option_model->get_option_by_company('company_security',$this->session->userdata('current_company_id')); 
                }

                $security = json_decode($security_data[0]['option_value'], true);

                if($security['security_status'] == 1 && $email != SUPER_ADMIN && $email != 'support@roomsy.com'){
                    $encode_email = base64_encode($email);
                    $encode_from = base64_encode('security');
                    redirect('auth/show_qr_code?email='.$encode_email.'&from='.$encode_from);
                }

                $admin_user_ids = $this->Whitelabel_partner_model->get_whitelabel_admin_ids($this->session->userdata('user_id'));

                if (
                    (
                        $this->session->userdata('user_role') == "is_admin" || 
                        in_array("is_salesperson", $employee_permission['permissions'])
                    ) && $admin_user_ids[0] == $this->session->userdata('user_id')
                )
                {
                    redirect('/admin');
                }               
                elseif($this->session->userdata('user_role') == "is_housekeeping" || in_array("is_housekeeping", $employee_permission['permissions']))
                {
                    redirect('/room');
                }
                else
                {
                    redirect('/menu/select_hotel/'.$this->company_id);
                }
            } else { 
                $errors = $this->tank_auth->get_error_message();
                
                $this->form_validation->set_message("Incorrect Login");

                if (isset($errors['banned'])) // banned user
                {
                    $this->_show_message($this->lang->line('auth_message_banned').' '.$errors['banned'], 'danger');
                }
                elseif (isset($errors['not_activated'])) // not_activated user
                {
                    $this->_show_message('Your account has been deactivated. Please verify your email address to reinstate your account. <a href="#"  id="resend-verification-link">CLICK HERE</a> to resend verification link.', 'danger');
                }
                else 
                {
                    if(isset($_POST['submit'])){
                        foreach ($errors as $k => $v) {
                            $data['errors'][$k] = $this->lang->line($v);
                        }

                        if(!$this->user_model->get_user_by_email($email)){
                            $data['errors']['incorrect_email'] = 'Email does not exists';
                        }
                    }
                }
            }

            $data['show_captcha'] = false;
//            if ($this->tank_auth->is_max_login_attempts_exceeded($login)) {
//                $data['show_captcha'] = true;
//                if ($data['use_recaptcha']) {
//                    $data['recaptcha_html'] = $this->_create_recaptcha();
//                } else {
//                    $data['captcha_html'] = $this->_create_captcha();
//                }
//            }

            $data['main_content'] = 'auth/login_form';

            $this->load->view('includes/bootstrapped_template', $data);

        }
    }

    function show_qr_code(){

        $company_id = $this->company_id;

        // if($this->is_partner_owner){
        //     $partner_data = $this->Company_security_model->get_first_property_partner($this->vendor_id);
        //     $company_id = $partner_data['company_id'];
        // }

        $user_id = $this->user_id;

        $admin_data = $this->Whitelabel_partner_model->get_whitelabel_partner_id($user_id);

        if($admin_data){

            $vendor_companies = $this->Company_security_model->get_vendor_companies($admin_data['partner_id']);
            $vendor_company_ids = explode(',', $vendor_companies['comp_ids']);

            $mathced_vendor_company_ids = $this->Option_model->get_option_by_company('company_security',$vendor_company_ids, true);

            $company_id = $mathced_vendor_company_ids[0]['company_id'];

        }

        $user_restriction = $this->Option_model->get_option_by_user('login_security', $this->user_id);

        if(empty($user_restriction)) {

            $protocol = empty($_SERVER['HTTPS']) ? 'http' : 'https';

            $login_security_url = $protocol . "://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

            $login_security_data = array(
                            'company_id' => $company_id,
                            'option_name ' => 'login_security',
                            'option_value ' => json_encode(
                                                array(
                                                    'user_id' => $this->user_id,
                                                    'login_security_url' => $login_security_url,
                                                    'login_security_otp_verified' => 0
                                                    )
                                                ),
                            'autoload' => 0
                        );
            $this->Option_model->add_option($login_security_data);
        }

        
        
        $email = $this->input->get('email');
        $from = $this->input->get('from');

        $decode_email = base64_decode($email);
        $decode_from = base64_decode($from);
        $data['secure_data'] = $this->google_security->create_secret($decode_email, $decode_from);

        $security_data = $this->Company_security_model->get_deatils_by_company_user(null, $this->user_id);

        if($security_data && count($security_data) > 0) {

            $data['secure_data']['enabled'] = true; 
            // $this->Company_security_model->update($company_id, $this->user_id, $company_security_data);
        } 
        else {
            $data['secure_data']['enabled'] = false; 
            // $this->Company_security_model->insert($company_security_data);
        }

        $this->load->view('auth/show_qr_code', $data);
    }

    function verify_otp(){
        $otp = $this->input->post('otp');
        $secret_code = $this->input->post('secret_code');
        $qr_code_url = $this->input->post('qr_code_url');
        $via = $this->input->post('via');
        $otp_verification = $this->input->post('otp_verification');

        $company_id = $this->company_id;

        // if($this->is_partner_owner){
        //     $partner_data = $this->Company_security_model->get_first_property_partner($this->vendor_id);
        //     $company_id = $partner_data['company_id'];
        // }

        $user_id = $this->user_id;

        $admin_data = $this->Whitelabel_partner_model->get_whitelabel_partner_id($user_id);

        if($admin_data){

            $vendor_companies = $this->Company_security_model->get_vendor_companies($admin_data['partner_id']);
            // lq();
            // $vendor_company_ids = $vendor_compnies;
            $vendor_company_ids = explode(',', $vendor_companies['comp_ids']);

            $mathced_vendor_company_ids = $this->Option_model->get_option_by_company('company_security',$vendor_company_ids, true);

            $company_id = $mathced_vendor_company_ids[0]['company_id'];

        }

        $secret_data = $this->Company_security_model->get_deatils_by_company_user(null, $user_id);

        $secret = "";

        if(
            isset($secret_data['secret_code']) && 
            $secret_data['secret_code']
        ){
            $secret = $secret_data['secret_code'];
        } else {
            $secret = $secret_code;
        }

        $check = $this->google_security->check_secret_with_otp($secret, $otp);

        if($check){

            if($via == 'login') {

                $this->Option_model->delete_option('login_security', $user_id);

                $company_security_data = array();
                $company_security_data['company_id'] = $company_id;
                $company_security_data['user_id'] = $user_id;
                $company_security_data['secret_code'] = $secret;
                $company_security_data['qr_code_url'] = $qr_code_url;
                $company_security_data['security_name'] = 'security';
                $company_security_data['created_at'] = gmdate('Y-m-d H:i:s');

                $security_data = $this->Company_security_model->get_deatils_by_company_user(null, $user_id);

                if($security_data && count($security_data) > 0) {

                    $data['secure_data']['enabled'] = true; 
                    // $this->Company_security_model->update($company_id, $this->user_id, $company_security_data);
                } 
                else {
                    $data['secure_data']['enabled'] = false; 
                    $this->Company_security_model->insert($company_security_data);
                }
            }

            if($otp_verification){
                $security_data =  $this->Option_model->get_option_by_company('company_security',$company_id);
        
                if($security_data){

                    $secure = json_decode($security_data[0]['option_value'], true);

                    $company_data = array(
                        'security_status' => $secure['security_status'],
                        'lock_timer' => $secure['lock_timer'],
                        'otp_verified' => 1
                    );
                   
                    $this->Option_model->update_option_company('company_security', json_encode($company_data), $company_id);
                }
            }

            $employee_permission = $this->session->all_userdata('permissions');

            $admin_user_ids = $this->Whitelabel_partner_model->get_whitelabel_admin_ids($this->session->userdata('user_id'));

            if (
                (
                    $this->session->userdata('user_role') == "is_admin" || 
                    in_array("is_salesperson", $employee_permission['permissions'])
                ) && $admin_user_ids[0] == $this->session->userdata('user_id')
            )
            {
                // redirect('/admin');
                echo json_encode(array('success' => true, 'redirect' => 'admin'));
            }               
            elseif($this->session->userdata('user_role') == "is_housekeeping" || in_array("is_housekeeping", $employee_permission['permissions']))
            {
                // redirect('/room');
                echo json_encode(array('success' => true, 'redirect' => 'room'));
            }
            else
            {
                // redirect('/menu/select_hotel/'.$this->company_id);
                echo json_encode(array('success' => true, 'redirect' => 'booking', 'company_id'=> $this->company_id));
            }
        } else {
            echo json_encode(array('success' => false, 'error_msg' => 'Invalid code. Please try again.'));
        }
    }

    function send_secret_qr_to_customer(){
        $email = $this->input->post('email');

        $decode_email = base64_decode($email);

        $user_data = $this->User_model->get_user_by_email($decode_email);
        $security_data = $this->Company_security_model->get_deatils_by_company_user(null, $user_data['id']);
        // prx($security_data);
        $response = $this->_send_secret_qr_to_customer_email($user_data, $security_data);

        echo json_encode($response);
    }

    function _send_secret_qr_to_customer_email($user_data, $security_data)
    {

        $whitelabelinfo = $this->session->userdata('white_label_information');

        $email_from = $whitelabelinfo && isset($whitelabelinfo['do_not_reply_email']) && $whitelabelinfo['do_not_reply_email'] ? $whitelabelinfo['do_not_reply_email'] : 'donotreply@minical.io';

        $qr_code_url = '<img src="' . $security_data["qr_code_url"] . '" alt="QR Code">';

        // alert support@minical.io about this new user that registered
        $this->load->library('email');
        $this->email->from($email_from);
        $this->email->to($user_data['email']);
        $this->email->subject("Secret QR Code");
        // $this->email->message("Secret QR Code: ".$qr_code_url
        //     ." \n<br/>email: ".$user_data['email']."\n");

        $data['secure_data'] = $security_data;

        $data['secure_data']['enabled'] = false;

        $this->ci->email->message($this->load->view('email/show_qr_code-html', $data, true));

        if($this->email->send()){
            return array(
                "success" => true,
                "message" => "The QR code has been sent to your email. Please check your inbox."
            );
        }

    }

    // validation for login credentials
    function _incorrect_login($login, $password)
    {
        $data['login_by_username'] = (
            $this->config->item('login_by_username', 'tank_auth') AND
            $this->config->item('use_username', 'tank_auth')
        );

        $data['login_by_email'] = $this->config->item('login_by_email', 'tank_auth');

        if (
            $this->tank_auth->login(
                $login,
                $password,
                $this->form_validation->set_value('remember'),
                $data['login_by_username'],
                $data['login_by_email']
            )
        )
        {
            return true;
        }
        else
        {
            $this->form_validation->set_message('_incorrect_login', 'Incorrect Login');
            return false;
        }

    }
    /**
     * Show info message
     *
     * @param    string
     * @return    void
     */
    function _show_message($message, $type = 'warning')
    {        
        $this->session->set_flashdata('message', $message);
        $this->session->set_flashdata('flash_type', $type);
        if ($this->tank_auth->is_logged_in()) { // if user is already logged in
            redirect('/booking');
        }
        else
        {
            redirect('/auth/login');
        }
    }

    function check_for_employee_auto_logout()
    {
        $company    = $this->Company_model->get_company($this->company_id);
        if ($company['employee_auto_logout_is_enabled']) {
            $this->logout();
        }

    }

    /**
     * Logout user
     *
     * @return void
     */
    function logout()
    {
        $user_id =  $this->session->userdata('user_id');
        $employee_log_detail = array('log' => 'Logged out','user_id' => $user_id,'selling_date' => gmdate('Y-m-d'));
        $employee_log = $this->Employee_log_model->get_log($employee_log_detail);
        if($employee_log){
            $this->Employee_log_model->update_log(array('date_time'=> gmdate('Y-m-d H:i:s')), $employee_log_detail);
        }else{
            $employee_log_detail['date_time'] = gmdate('Y-m-d H:i:s');
            $this->Employee_log_model->insert_log($employee_log_detail);
        }
        $this->tank_auth->logout();
        redirect('/auth/login/');
    }

    /**
     * Register user on the site
     * @param string $subscription_type
     */
    
    function new_register_AJAX($subscription_type = "", $region = "")
    {
        //$this->ci->output->enable_profiler(TRUE);
        
        $lead_source = $this->input->post('source');
        $password = $this->input->post('password');
        
        if ($this->tank_auth->is_logged_in()) {
            // logged in
            //redirect('');
            echo l('Already loggedin with another user!', true);

        } else { 
            // check Whitelabel partner access or not
            $this->load->model('User_model');
            
            $lead_source_slug = $this->input->get('source');
            if(isset($lead_source_slug) && $lead_source_slug != NULL)
            {
                $this->session->set_userdata('lead_source_slug', $lead_source_slug);
            }   

            if(!(preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{6,20}$/', $password))){
                echo l("The password must contain at least one uppercase letter, one lowercase letter, one number, one special character, and be between 6 and 20 characters long.",true);
               return false;
            }             
            
            $this->_set_register_form_validation_rules();

            $captcha_registration = $this->config->item('captcha_registration', 'tank_auth');
            $use_recaptcha        = $this->config->item('use_recaptcha', 'tank_auth');
            $data['errors'] = array();

            $data['subscription_type']    = $subscription_type;
            $data['captcha_registration'] = $captcha_registration;
            $data['use_recaptcha']        = $use_recaptcha;
            $data['main_content']         = 'auth/register_form';
            
            // $recaptcha_response_token =  $this->input->post("g-recaptcha-response");
            // if(!empty($this->input->post("minical_homepage")) && $this->input->post("minical_homepage") == 'minical_homepage')
            // {
            //     $recaptcha_secret_key = $this->config->item('minical_homepage_recaptcha_secret_key', 'tank_auth');
            // }
            // else
            // {
            //     $recaptcha_secret_key = $this->config->item('recaptcha_secret_key', 'tank_auth');
            // }

            $accept_tnc =  $this->input->post("accept_tnc");

            if(!empty($this->input->post()) && $accept_tnc == ''){ 
               echo l("Please accept our terms and conditions.",true);
               return;
            }
            
            // if(!empty($this->input->post()) && $recaptcha_response_token == ""){ 
            //    echo "Please check CAPTCHA box";
            // }
            // elseif(!empty($this->input->post()) && !$this->google_recaptcha_validate_token($recaptcha_secret_key, $recaptcha_response_token)){
            //     echo "invalid CAPTCHA. Please try again!";  
            // }
            // input validation ok, now check if credit card is actually valid
            //elseif($this->form_validation->run()) {   
            if($this->form_validation->run()) {   
                $password = $this->input->post('password');
                $email = $this->input->post('email');
                //$email = $this->form_validation->set_value('email');

                if($email==$this->test_email) {
                    /*
                     * Remove associative information if given email is a
                     * test email
                     */
                    $this->clean_test_user($email);

                }

                $data = array_merge(
                    $data, array(
                        'email'           => $this->form_validation->set_value('email'),
                        'name'            => '',
                        'number_of_rooms' => 10
                    )
                );
                $whiteLabelInfo = $this->session->userdata('white_label_information');
                if($whiteLabelInfo){
                    $data['partner_id'] = $whiteLabelInfo['id'];
                }else{
                    $data['partner_id'] = 0;
                }

                $user_data = $this->tank_auth->create_user(
                    '',
                    $data['email'],
                    null,
                    null,
                    null,
                    false,
                    null,
                    true,
                    $data['partner_id']
                ); // last parameter is for setting TOS agreed

                $data['user_id'] = $user_data['user_id'];
                $data['close_io'] = false; //if false then the request is from registration page, otherwise My properties page.
                
                if($this->session->userdata('lead_source_slug'))
                {
                    $data['lead_source_slug'] = $this->session->userdata('lead_source_slug');
                }
                else
                {
                    $data['lead_source_slug'] = '';
                }

                
                $company_id = $this->_create_company($data, $subscription_type, $region);
                
                if($company_id > 0)
                {
                    $this->session->unset_userdata('lead_source_slug');
                }

                $this->session->set_userdata(array('current_company_id' => $company_id));

                $data['company_id'] = $company_id;

                // if the email address is not a test account (domain != innGrid.net or Minical)
                $domain = strtolower(substr(strrchr($data['email'], "@"), 1));

                if (($domain != "inngrid.net" && $domain != "minical.io"))
                {
                    /*
                     * Do not added to mailchimp,close io and don't send welcome email
                     * if email is a test email
                     */
                
                    if( $email != $this->test_email )
                    {
                        $this->_add_to_mailchimp($data);
                    }

                    $this->Wizard_model->create_user_x_guider($user_data['user_id']);

                }
                else
                {
                    // if email is test@minical.io then redirect
                    if($email==$this->test_email)
                    {
                        $employee_data = $this->tank_auth->forgot_password($data['email']);
                        $employee_data['company_name'] = $data['name'];
                        $employee_data['site_name'] = $this->config->item('website_name', 'tank_auth');
                        //echo 'email';
                        //redirect('/auth/activate_employee/' . $employee_data['user_id'] . '/' . $employee_data['new_pass_key'], 'refresh');
                    }

                }

                $employee_data = $this->tank_auth->forgot_password($data['email']);

                if (!is_null(
                    $data = $this->tank_auth->reset_password(
                        $user_data['user_id'],
                        $employee_data['new_pass_key'],
                        $password
                    )
                )
                ) {

                    // success

                    //$this->_show_message($this->lang->line('auth_message_registration_completed_2').' '.anchor('/auth/login/', 'Login'));
                    $user_data = $this->User_model->get_user_profile($user_data['user_id']);
                    $this->tank_auth->login(
                        $user_data['email'],
                        $password,
                        1,
                        0,
                        1
                    );
                    //$_SESSION['is_registration_page'] = '1';
                    $this->session->set_userdata('is_registration_page', '1');
                    $whitelabelinfo = $this->session->userdata('white_label_information');

                        $data['new_email_key'] = '';
                        $data['site_name']         = $whitelabelinfo && isset($whitelabelinfo['name']) && $whitelabelinfo['name'] ? $whitelabelinfo['name'] : $this->config->item('website_name', 'tank_auth');
                        $data['activation_period'] = $this->config->item('email_activation_expire', 'tank_auth') / 3600;

                        $this->_send_email('activate', $email, $data);

                       // $this->_show_message(sprintf($this->lang->line('auth_message_activation_email_sent'), $data['email']));

                        if(!empty($this->input->post("minical_homepage")) && $this->input->post("minical_homepage") == 'minical_homepage')
                        {
                            redirect('booking');
                        }
                        else
                        {
                            echo l('success',true);
                        }

                    //redirect('booking');
                } else {
                    // fail
                    echo $this->lang->line('auth_message_new_employee_failed'); 
//                    $this->_show_message($this->lang->line('auth_message_new_employee_failed'));
                }

                //redirect('/auth/thank_you/');
            }                
            else{
                //print_r(validation_errors());
                $is_hosted_prod_service = getenv('IS_HOSTED_PROD_SERVICE');
                if($is_hosted_prod_service || $_SERVER['HTTP_HOST'] == "app.minical.io" || $_SERVER['HTTP_HOST'] == "demo.minical.io"){
                    echo 'The Email field must contain a valid email address.';
                } else {
                    echo strip_tags(form_error('email'));
                }
                
            }
        }

    }
    
    function register()
    {
        if ($this->tank_auth->is_logged_in()) {
            // logged in
            redirect('/booking');           

        }

        $host_name = $_SERVER['HTTP_HOST'];
        $white_label_name = explode('.', $host_name);
        if(count($white_label_name) > 0)
        {
            $white_label_name = $white_label_name[0];
        }
        $data['whitelabel_detail'] = '';

      
        if($this->input->get('ref')){
            $white_label_detail = $this->Whitelabel_partner_model->get_partners(array('reference'=> $this->input->get('ref')),true);
            if($white_label_detail)
            {
                $white_label_detail = $white_label_detail[0];
                $data['whitelabel_detail'] = $white_label_detail;
            } else {
                //$white_label_detail = $this->Whitelabel_partner_model->get_partners(array('username' => $white_label_name));

                $white_label_detail = $this->Whitelabel_partner_model->get_partner_by_username($white_label_name);

                if($white_label_detail)
                {
                    $white_label_detail = $white_label_detail[0];
                    $data['whitelabel_detail'] = $white_label_detail;
                }
                else
                {
                    $white_label_detail = $this->Whitelabel_partner_model->get_partners(array('domain' => $host_name));
                    if($white_label_detail)
                    {
                        $white_label_detail = $white_label_detail[0];
                        $data['whitelabel_detail'] = $white_label_detail;
                    }
                }
            }
        } else {
            //$white_label_detail = $this->Whitelabel_partner_model->get_partners(array('username' => $white_label_name));
            $white_label_detail = $this->Whitelabel_partner_model->get_partner_by_username($white_label_name);
            if($white_label_detail)
            {
                $white_label_detail = $white_label_detail[0];
                $data['whitelabel_detail'] = $white_label_detail;
            }
            else
            {
                $white_label_detail = $this->Whitelabel_partner_model->get_partners(array('domain' => $host_name));
                if($white_label_detail)
                {
                    $white_label_detail = $white_label_detail[0];
                    $data['whitelabel_detail'] = $white_label_detail;
                }
            }
        }
        
        if($data['whitelabel_detail'])
        {
            $this->session->set_userdata('white_label_information', $white_label_detail);
        } else {
            $white_label_detail = $this->Whitelabel_partner_model->get_partners(array('id' => 0)); // default minical
            if($white_label_detail)
            {
                $white_label_detail = $white_label_detail[0];
                $data['whitelabel_detail'] = $white_label_detail;
                $this->session->set_userdata('white_label_information', $white_label_detail);
            }
        }

        $data['whitelabelinfo']  = $this->session->userdata('white_label_information');
        $company_data = $this->Company_model->get_company($this->company_id);
        $data['css_files'] = array(
            base_url() . auto_version('css/bootstrap.min.css'),
            base_url() . auto_version('css/bootstrap-theme.min.css'),
            base_url() . auto_version('css/bootstrap-override.css'),
            base_url() . auto_version('css/smoothness/jquery-ui.min.css'),
            base_url() . auto_version('css/menu.css'),
            base_url() . auto_version('css/booking/booking_main.css'),
            base_url() . auto_version('css/booking/booking_list.css'),
            base_url() . auto_version('css/booking/booking_blocks.css'),
            base_url() . auto_version('css/bootstrap-colorselector.css'),
            base_url() . auto_version('css/bootstrap-tokenfield.min.css'),
            base_url() . auto_version('js/calendar/main.css'),
            base_url() . auto_version('js/calendar/common/common.css'),
            base_url() . auto_version('js/calendar/basic/basic.css'),
            base_url() . auto_version('js/calendar/custom.css'),
            
        );

        
        $data['selected_menu'] = 'bookings';
        $data['selected_submenu'] = 'Room View';
        $data['main_content']         = 'auth/new_register';
        $this->load->view('includes/bootstrapped_template', $data);
    }
    
    // function terms_of_service()
    // {
    //     $this->load->view('terms_of_services.html');
    // }
    
    // function privacy_policy()
    // {
    //     $this->load->view('privacy_policy.html');
    // }
    
    // deprecated
    function _add_to_mailchimp($data)
    {

        // add new user to the list
        $this->load->library('Mailchimp_library');
        $result = $this->mailchimp_library->call(
            'lists/subscribe',
            array(
                'id'                => 'aecf920a0b',
                'email'             => array('email' => $data['email']),
                'merge_vars'        => array(
                    'FNAME'             => isset($data['first_name'])?$data['first_name']:'',
                    'LNAME'             => isset($data['last_name'])?$data['last_name']:'',
                    'COMPANY_ID'        => $data['company_id'],
                    'COMPANY'           => isset($data['name'])?$data['name']:'',
                    'PHONE'             => isset($data['phone'])?$data['phone']:'',
                    'COUNTRY'           => isset($data['country'])?$data['country']:'',
                    'ROOMCOUNT'         => isset($data['number_of_rooms'])?$data['number_of_rooms']:''
                ),
                'double_optin'      => false,
                'update_existing'   => true,
                'replace_interests' => false,
                'send_welcome'      => false,
            )
        );
    }

    function _send_new_user_alert_email($data)
    {

        $whitelabelinfo = $this->session->userdata('white_label_information');

        $email_from = $whitelabelinfo && isset($whitelabelinfo['do_not_reply_email']) && $whitelabelinfo['do_not_reply_email'] ? $whitelabelinfo['do_not_reply_email'] : 'donotreply@minical.io';

        // Send welcome email
        if (
            $this->config->item('email_account_details', 'tank_auth') &&
            $data['name'] != 'selenium test company'
        ) {    // send "welcome" email

            // alert support@minical.io about this new user that registered
            $this->load->library('email');
            $this->email->from($email_from);
            $this->email->to("sales@minical.io");
            $this->email->subject("New user alert");
            $this->email->message("company name: ".$data['name']
                ." \n<br/>email: ".$data['email']."\n<br/>name: "
                .$data['first_name']." ".$data['last_name']."\n<br/>phone: ".$data['phone']);
            $this->email->send();
        }

    }

    function _set_register_form_validation_rules()
    {

        // rules
        $email = $this->input->post('email');

        if($email == $this->test_email)
        {
            $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email');
        }
        else
        {
            $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email|callback__is_unused_email');
        }
        //$this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean|min_length['.$this->config->item('password_min_length', 'tank_auth').']|max_length['.$this->config->item('password_max_length', 'tank_auth').']|alpha_dash');
        //$this->form_validation->set_rules('confirm_password', 'Confirm Password', 'trim|required|xss_clean|matches[password]');

        //$this->form_validation->set_rules('first_name', 'First Name', 'trim|required|xss_clean');
        //$this->form_validation->set_rules('last_name', 'Last Name', 'trim|required|xss_clean');

        //$this->form_validation->set_rules('property_type', 'Property Type', 'trim|required|xss_clean');
        //$this->form_validation->set_rules('company_name', 'Company Name', 'trim|required|xss_clean');
        //$this->form_validation->set_rules('number_of_rooms', 'Number of Rooms', 'trim|required|numeric|xss_clean');
        //$this->form_validation->set_rules('phone', 'Phone', 'trim|required|xss_clean|min_length[7]');
        //$this->form_validation->set_rules('country', 'Country', 'trim|required|xss_clean');
        //$this->form_validation->set_rules('time_zone', 'Time Zone', 'trim|required|xss_clean');
        //$this->form_validation->set_rules('subscription_type', 'Subscription Type', 'trim|required|xss_clean');
        /*
        $this->form_validation->set_rules('cc_first_name', 'Cardholder\'s first Name', 'trim|xss_clean');
        $this->form_validation->set_rules('cc_last_name', 'Cardholder\'s last Name', 'trim|xss_clean');
        $this->form_validation->set_rules('cc_number', 'Credit card number', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('cc_expiry_month', 'Credit card expiry month', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('cc_expiry_year', 'Credit card expiry year', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('cc_cvv', 'CVV number', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('cc_address1', 'Cardholder\'s address1', 'trim|xss_clean');
        $this->form_validation->set_rules('cc_address2', 'Cardholder\'s address2', 'trim|xss_clean');
        $this->form_validation->set_rules('cc_city', 'Cardholder\'s city', 'trim|xss_clean');
        $this->form_validation->set_rules('cc_country', 'Cardholder\'s country', 'trim|xss_clean');
        $this->form_validation->set_rules('cc_province', 'Cardholder\'s Billing State/ Province', 'trim|xss_clean');
        $this->form_validation->set_rules('cc_postal_code', 'Cardholder\'s postal code', 'trim|xss_clean');
        */
    }

    public function create_company() {
        $data = array(
            'name' => $this->input->post('name'),
            'number_of_rooms' => $this->input->post('number_of_rooms'),
            'user_id' => $this->user_id,
            'close_io'=>true    // if true then the request is from My Property page, otherwise registration page.
        );

        $subscription_type = $this->input->post('subscription_type');
        $region = $this->input->post('region');
        $created_by = $this->input->post('created_by');

        $this->_create_company($data, $subscription_type, $region, $created_by);
        $response = array(
            'success'=> 'true'
        );

        echo json_encode($response);

    }

    /**
     * @param &$data
     * @param $time_zone
     * @return mixed
     */
    private function _create_company($data, $subscription_type = "BASIC", $region = "NA", $created_by = null)
    {
        $time_zone    = 'America/New_York';
        $this->load->model('Whitelabel_partner_model');
        // create company in minical database
        $company_data = array(
            'name'                     => $data['name'],
            'phone'                    => isset($data['phone'])?$data['phone']:'',
            'selling_date'             => convert_to_local_time(new DateTime(), $time_zone)->format("Y-m-d"),
            'number_of_rooms'          => isset($data['number_of_rooms'])?$data['number_of_rooms']:'1',
            'email'                    => isset($data['email'])?$data['email']:'',
            'time_zone'                => $time_zone,
            'default_currency_id'      => '151', // USD
            'country'                  => isset($data['country'])?$data['country']:'',
            'logo_image_group_id'      => $this->Image_model->create_image_group(LOGO_IMAGE_TYPE_ID),
            'slideshow_image_group_id' => $this->Image_model->create_image_group(SLIDE_IMAGE_TYPE_ID),
            'gallery_image_group_id'   => $this->Image_model->create_image_group(GALLERY_IMAGE_TYPE_ID),
            'enable_api_access' => 1

        );

        // For testing. We have to assign 1000000 as company_id, so we can
        // easily delete test company later
        if ($data['name'] == 'selenium test company') {
            $company_data['company_id'] = 1000000;
        }
        
        $company_data['partner_id'] = 0;
      
        if(isset($data['partner_id']))
        {
            $company_data['partner_id'] = $data['partner_id'];
            $partner_detail = $this->Whitelabel_partner_model->get_partners(array("id" => $company_data['partner_id']));
            
            if(isset($partner_detail[0]))
            {
                if(isset($partner_detail[0]['timezone']) && $partner_detail[0]['timezone']){
                    $company_data['time_zone'] = $partner_detail[0]['timezone'];
                }
                if(isset($partner_detail[0]['currency_id']) && $partner_detail[0]['currency_id']){
                    $company_data['default_currency_id'] = $partner_detail[0]['currency_id'];
                }
            }
        }
        else
        {
            $partner_detail = $this->User_model->get_partner_id_by_user_id($data['user_id']);
                
            if(isset($partner_detail[0]))
            {
                if(isset($partner_detail[0]['partner_id']) && $partner_detail[0]['partner_id']){
                    $company_data['partner_id'] = $partner_detail[0]['partner_id'];
                }
                if(isset($partner_detail[0]['timezone']) && $partner_detail[0]['timezone']){
                    $company_data['time_zone'] = $partner_detail[0]['timezone'];
                }
                if(isset($partner_detail[0]['currency_id']) && $partner_detail[0]['currency_id']){
                    $company_data['default_currency_id'] = $partner_detail[0]['currency_id'];
                }
            }
        }
        
        // create company in minical
        $company_id = $this->Company_model->create_company($company_data);
        update_customer_field($company_id);

        $api_key = md5(uniqid(rand(), true));
        $this->Company_model->insert_company_api_key($company_id, $api_key);
        
        if(isset($data['email']) && $data['email'] == SUPER_ADMIN){
            $this->User_model->add_user_permission($company_id, $data['user_id'], 'is_admin');
        }

        if($this->User_model->get_users_count() == 1 && isset($data['email']) && $data['email'] != SUPER_ADMIN){
            $this->User_model->add_user_permission($company_id, $data['user_id'], 'is_admin');

            $is_hosted_prod_service = getenv('IS_HOSTED_PROD_SERVICE');

            if(
                !$is_hosted_prod_service && 
                $_SERVER['HTTP_HOST'] != "app.minical.io" && 
                $_SERVER['HTTP_HOST'] != "demo.minical.io"
            ){
                $partner_x_admin_data = array(
                                            'partner_id' => $company_data['partner_id'], 
                                            'admin_id' => $data['user_id']
                                        );
                $this->Whitelabel_partner_model->add_whitelabel_partner_x_admin($partner_x_admin_data);
            }

        }

         // support@minical.io will have admin permission
        
        // check for whitelabel partner
        // $admin_user_ids = $this->Whitelabel_partner_model->get_whitelabel_admin_ids($data['user_id']);        
        // foreach($admin_user_ids as $admin_user_id){
        //     if($admin_user_id && $admin_user_id != SUPER_ADMIN_USER_ID)
        //     {
        //         $this->User_model->add_user_permission($company_id, $admin_user_id , 'is_admin');
        //     }
        // }
            
        if (isset($data['user_id']))
        {
            $this->User_model->add_user_permission($company_id, $data['user_id'], 'is_owner');
            $this->User_model->update_user_profile($data['user_id'], array('current_company_id' => $company_id));
        }
        
        // create all the default data entries. Such as rooms, room types, charge types, etc...
        if($created_by == 'admin')
        {
            $this->_initialize_company($company_id);
        }
        else
        {
            //Create customer types
            //$this->Customer_type_model->create_customer_type($company_id, 'Traveller');
            $room_type_id = $this->Room_type_model->create_room_type($company_id, 'Sample Room Type', 'SRT', 2, 1, 1);

            if ($room_type_id) {
                $company = $this->Company_model->get_company($company_id);
                // add constraint to limit rooms to 1000
                $rooms = array();
                for ($i = 1; $i <= min(1000, $company['number_of_rooms']); $i++) {
                    $rooms[] = array(
                            'company_id' => $company_id,
                            'room_name' => 100 + $i,
                            'room_type_id' => $room_type_id
                    );
                }
                $this->Room_model->create_room(null, null, null, $rooms);
            }
        }

        $is_hosted_prod_service = getenv('IS_HOSTED_PROD_SERVICE');

        if(!$is_hosted_prod_service) {
            $subscription_level = PREMIUM;
        } else {
            $subscription_level = BASIC;
        }
        
        $signup_minical_plan = isset($_COOKIE['signup-minical-plan']) ? $_COOKIE['signup-minical-plan'] : strtolower($subscription_type);
        $subscription_level = $signup_minical_plan == "minimal" ? BASIC : $subscription_level;
        $subscription_level = $signup_minical_plan == "premium" ? PREMIUM : $subscription_level;
        // $subscription_level = $signup_minical_plan == "elite" ? ELITE : $subscription_level;
        $subscription_state = isset($partner_detail[0]['default_property_status']) ? $partner_detail[0]['default_property_status'] :'active';

        $subscription_type = $this->_process_subscription_type($subscription_type, $company_id, $region, $subscription_level,$subscription_state);
        
        // Get Company was created by which lead source
        $lead_source_slug = isset($data['lead_source_slug']) ? $data['lead_source_slug'] : '';
        $utm_source = ($this->session->userdata('utm_source')) ? $this->session->userdata('utm_source') : '';
        $this->_process_admin_panel_info($company_id, $time_zone, $lead_source_slug, $utm_source); // to track when the company was created

        // push property data to close.io lead.
        if( $data['close_io'] ){
            //bring user by id.
            $user = $this->User_model->get_user_by_id($this->user_id);

            $closeiodata = array(
                'first_name'=>$user['first_name'],
                'last_name'=>$user['last_name'],
                'email'=>$user['email'],
                'phone'=>$user['phone'],
                'name'=>$data['name'],
                'number_of_rooms'=>$data['number_of_rooms'],
                'country' => $user['country']
            );
            $this->_add_to_close_io($closeiodata);
        }

        return $company_id;
    }



    function _initialize_company($company_id)
    {
        //TO DO: SETUP ERROR CHECKING FOR FAIL CREATION
        $this->load->model('Company_model');
        
        //Create customer types
        $this->Customer_type_model->create_customer_type($company_id, 'Traveller');
        $room_type_id = $this->Room_type_model->create_room_type($company_id, 'Sample Room Type', 'SRT', 2, 1, 1);

        if ($room_type_id) {
            $company = $this->Company_model->get_company($company_id);
            // add constraint to limit rooms to 1000
            $rooms = array();
            for ($i = 1; $i <= min(1000, $company['number_of_rooms']); $i++) {
                $rooms[] = array(
                        'company_id' => $company_id,
                        'room_name' => 100 + $i,
                        'room_type_id' => $room_type_id
                );
            }
            $this->Room_model->create_room(null, null, null, $rooms);
        }

        // set default availability for the room type..
        $date_range_id = $this->Date_range_model->create_date_range(
            Array(
                'date_start' => '2000-01-01',
                'date_end' => '2030-01-01',
            )
        );

        $this->Date_range_model->create_date_range_x_room_type(
            Array(
                'room_type_id' => $room_type_id,
                'date_range_id' => $date_range_id
                //'ota_availability' => '999' this is done as database default value
            )
        );

        //TO DO: SETUP ERROR CHECKING FOR FAIL CREATION
        //setup default charge types
        $this->load->model('Charge_type_model');
        $room_charge_id = $this->Charge_type_model->create_room_charge_type($company_id, 1);

        $service_charge_id = $this->Charge_type_model->create_charge_type($company_id, 'Service Charge');

        $tip_charge_type_id = $this->Charge_type_model->create_charge_type($company_id, 'Gratuity');


        //TO DO: SETUP ERROR CHECKING FOR FAIL CREATION
        //setup default payment types
        $this->load->model('Payment_model');
        $this->Payment_model->create_payment_type($company_id, 'AMEX');
        $this->Payment_model->create_payment_type($company_id, 'Mastercard');
        $this->Payment_model->create_payment_type($company_id, 'Visa');
        $this->Payment_model->create_payment_type($company_id, 'Debit');
        $this->Payment_model->create_payment_type($company_id, 'Cash');
        $this->Payment_model->create_payment_type($company_id, 'Gift Card');
        $this->Payment_model->create_payment_type($company_id, 'Cheque');
        $this->Payment_model->create_payment_type($company_id, 'PayPal', 1);


        $active_extensions = $this->Extension_model->get_active_extensions($this->company_id);
        if($active_extensions && count($active_extensions) > 0){
            $new_extensions = array();
            foreach ($active_extensions as $extension) {

                if($this->is_super_admin == 1){
                    if(isset($extension['extension_name']) && $extension['extension_name'] != 'reseller_package' && $extension['extension_name'] != 'subscription' && $extension['extension_name'] != 'vendor_monthly_report'){
                        $new_extensions[] = array(
                            'extension_name' => $extension['extension_name'],
                            'company_id' => $company_id,
                            'is_active' => 1
                        );
                    }
                } else {
                    if(isset($extension['extension_name']) && $extension['extension_name'] != 'reseller_package'  && $extension['extension_name'] != 'subscription' && $extension['extension_name'] != 'vendor_monthly_report'){
                        $new_extensions[] = array(
                            'extension_name' => $extension['extension_name'],
                            'company_id' => $company_id,
                            'is_active' => 1
                        );
                    }
                }
            }

            $new_extensions[] = array(
                'extension_name' => 'reseller_package',
                'company_id' => $company_id,
                'is_active' => 1
            );
            $new_extensions[] = array(
                'extension_name' => 'subscription',
                'company_id' => $company_id,
                'is_active' => 1
            );
            $new_extensions[] = array(
                'extension_name' => 'vendor_monthly_report',
                'company_id' => $company_id,
                'is_active' => 1
            );

            for ($i = 0, $total = count($new_extensions); $i < $total; $i = $i + 50)
            {
                $ext_batch = array_slice($new_extensions, $i, 50);

                $this->db->insert_batch("extensions_x_company", $ext_batch);

                if ($this->db->_error_message())
                {
                    show_error($this->db->_error_message());
                }
            }
        } else {
             $new_extensions[] = array(
                'extension_name' => 'reseller_package',
                'company_id' => $company_id,
                'is_active' => 1
            );
            $new_extensions[] = array(
                'extension_name' => 'subscription',
                'company_id' => $company_id,
                'is_active' => 1
            );
            $new_extensions[] = array(
                'extension_name' => 'vendor_monthly_report',
                'company_id' => $company_id,
                'is_active' => 1
            );

            for ($i = 0, $total = count($new_extensions); $i < $total; $i = $i + 50)
            {
                $ext_batch = array_slice($new_extensions, $i, 50);

                $this->db->insert_batch("extensions_x_company", $ext_batch);

                if ($this->db->_error_message())
                {
                    show_error($this->db->_error_message());
                }
            }
        }
    }


    /**
     * @param $subscription_type
     * @param $company_id
     */
    private function _process_subscription_type($subscription_type, $company_id, $region, $subscription_level = 0,$subscription_state)
    {
        $renewal_period = isset($_COOKIE['signup-minical-renewal']) ? $_COOKIE['signup-minical-renewal'] : "";
        $renewal_period = $renewal_period ? ($renewal_period) : '1 month';

        $subscription_type          = $this->form_validation->set_value('subscription_type'); // LITE, STANDARD, or ENTERPRISE
        $company_subscription_array = array(
            'company_id'         => $company_id,
            'subscription_type'  => $subscription_type,
            'subscription_state' => $subscription_state,
            'renewal_period'     => $renewal_period,
            'region' => $region,
            'subscription_level'  => $subscription_level
        );

        $this->load->model('Company_subscription_model');
        $this->Company_subscription_model->insert_or_update_company_subscription($company_id, $company_subscription_array);

        return $subscription_type;
    }

    /**
     * @param $company_id
     * @param $time_zone
     */
    private function _process_admin_panel_info($company_id, $time_zone, $lead_source_slug = NULL, $utm_source = NULL)
    {        
        $this->load->model('Admin_model');
        $this->load->model('Lead_source_model');
        // getting lead source id from lead_sources table 
        $lead_source_id = 0;
        if($lead_source_slug)
        {
            $lead_source_id = $this->Lead_source_model->get_lead_source_id($lead_source_slug);        
        }
                
        $company_admin_panel_info_array = array(
            'company_id'    => $company_id,
            'creation_date' => convert_to_local_time(new DateTime(), $time_zone)->format("Y-m-d G:i"), // for admin panel to log when the account was created, and when it needs to be followed
            'trial_expiry_date' => date('Y-m-d', strtotime(convert_to_local_time(new DateTime(), $time_zone)->format("Y-m-d G:i"). ' + 14 days')),
            'lead_source_id' => $lead_source_id, // added lead source id 
            'utm_source' => $utm_source // added utm_source
        );
        
        $this->Admin_model->insert_company_admin_panel_info($company_admin_panel_info_array);
    }
    
    public function add_to_close_io()
    {
        $user_id = $this->session->userdata('user_id');
        $user = $this->User_model->get_user_by_id($user_id);
        $closeiodata = array(
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'name' => $this->input->post('name'),
            'number_of_rooms' => $this->input->post('number_of_rooms'),
            'city' => $user['city'],
            'address' => $user['address'],
            'country' => $user['country']
        );

        $this->_add_to_close_io($closeiodata);

        // $whitelabelinfo = $this->session->userdata('white_label_information');
        // if(isset($whitelabelinfo['auto_close_io'])) {
        //     if ($whitelabelinfo['auto_close_io']) {
        //         $this->_add_to_close_io($closeiodata);
        //     }
        // } else {
        //     $this->_add_to_close_io($closeiodata);
        // }
    }
    
    /**
     * @param $data
     * @throws \LooplineSystems\CloseIoApiWrapper\Library\Exception\InvalidParamException
     */
    private function _add_to_close_io(&$data)
    {
        $closeIoConfig = new CL_Config();
        $closeIoConfig->setApiKey('7ff8a8de86dfc9659f9f7ad12140e72bc8f2471fcb90ddfd13d11ca8');

        $closeIoApiWrapper = new CL_Wrapper($closeIoConfig);
        $leadsApi          = $closeIoApiWrapper->getLeadApi();

        // create lead
        $lead = new CL_Lead();
        $lead->setName($data['name']);
        $lead->setDescription(sprintf('Requested number of rooms: %s', $data['number_of_rooms']));

        //print_r($data);
        
        // address
        $address = new CL_Address();
        $address->setCountry($data['country']);
        //$address->setCity($data['city']);
        //$address->setAddress1($data['address']);
        
        // contacts
        $contact = new CL_Contact();
        $contact->setName(
            sprintf(
                '%s %s',
                $data['first_name'],
                $data['last_name']
            )
        );

        // emails
        $email = new CL_Email();
        $email->setEmail($data['email']);
        $email->setType(CL_Email::EMAIL_TYPE_DIRECT);
        $contact->addEmail($email);

        // phones
        $phone = new CL_Phone();
        $phone->setPhone("+".$data['phone']);
        $phone->setType(CL_Phone::PHONE_TYPE_DIRECT);
        $contact->addPhone($phone);

        $lead->addAddress($address);
        $lead->addContact($contact);
        
        try {
            return $response = $leadsApi->addLead($lead);
        } catch (Exception $e) {
            
            // phone number is invalid so skip it for now
            
            // create lead
            $lead = new CL_Lead();
            $lead->setName($data['name']);
            $lead->setDescription(sprintf('Requested number of rooms: %s', $data['number_of_rooms']));

            // contacts
            $contact = new CL_Contact();
            $contact->setName(
                sprintf(
                    '%s %s',
                    $data['first_name'],
                    $data['last_name']
                )
            );
            $contact->addEmail($email);
            
            $lead->addAddress($address);
            $lead->addContact($contact);
                        
            return $response = $leadsApi->addLead($lead);
        }
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
        //echo "sending email to ".$email;
        $this->load->library('email');
        
        $whitelabelinfo = $this->session->userdata('white_label_information');

        $from_email = $whitelabelinfo && isset($whitelabelinfo['do_not_reply_email']) && $whitelabelinfo['do_not_reply_email'] ? $whitelabelinfo['do_not_reply_email'] : 'donotreply@minical.io';
        
        $from_name = $whitelabelinfo && isset($whitelabelinfo['name']) && $whitelabelinfo['name'] ? $whitelabelinfo['name'] : 'Minical';

        $reply_to_email = $whitelabelinfo && isset($whitelabelinfo['support_email']) && $whitelabelinfo['support_email'] ? $whitelabelinfo['support_email'] : 'support@minical.io';
        
        $reply_to_name = $whitelabelinfo && isset($whitelabelinfo['name']) && $whitelabelinfo['name'] ? $whitelabelinfo['name'] : 'Minical';

        $this->email->from($from_email, $from_name);
        $this->email->reply_to($reply_to_email, $reply_to_name." Support");
        $this->email->to($email);
        $this->email->subject(sprintf($this->lang->line('auth_subject_'.$type), $from_name));
        $this->email->message($this->load->view('email/'.$type.'-html', $data, true));
        $this->email->set_alt_message($this->load->view('email/'.$type.'-txt', $data, true));
        $this->email->send();
    }

    function _create_customer($data)
    {
        $customer_array = array(
            'organization' => $data['company_name'],
            'first_name'   => $data['first_name'],
            'last_name'    => $data['last_name'],
            'address'      => $data['cc_address1'],
            'address_2'    => $data['cc_address2'],
            'city'         => $data['cc_city'],
            'state'        => $data['cc_province'],
            'zip'          => $data['cc_postal_code'],
            'country'      => $data['cc_country'],
            'email'        => $data['email'],
            'phone'        => $data['phone']
        );

        return $this->chargify->create_customer($customer_array);
    }

    function _is_unused_email($email)
    {
        $this->load->model('tank_auth/users');
        if ($this->users->get_user_by_email($email)) {
            echo l('The Email entered is already registered.', true);
            // $this->form_validation->set_message('_is_unused_email', l('The Email entered is already registered.', true));

            return false;
        } else {
            return true;
        }
    }

    /**
     * Send activation email again, to the same or new email address
     *
     * @return void
     */
    function send_again()
    {
        if (!$this->tank_auth->is_logged_in(false)) {                            // not logged in or activated
            redirect('/auth/login/');

        } else {
            $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email');

            $data['errors'] = array();

            if ($this->form_validation->run()) {                                // validation ok
                if (!is_null(
                    $data = $this->tank_auth->change_email(
                        $this->form_validation->set_value('email')
                    )
                )
                ) {            // success

                    $whitelabelinfo = $this->session->userdata('white_label_information');
                    $data['site_name']         = $whitelabelinfo && isset($whitelabelinfo['name']) && $whitelabelinfo['name'] ? $whitelabelinfo['name'] : $this->config->item('website_name', 'tank_auth');
                    $data['activation_period'] = $this->config->item('email_activation_expire', 'tank_auth') / 3600;

                    $this->_send_email('activate', $data['email'], $data);

                    $this->_show_message(sprintf($this->lang->line('auth_message_activation_email_sent'), $data['email']));

                } else {
                    $errors = $this->tank_auth->get_error_message();
                    foreach ($errors as $k => $v) {
                        $data['errors'][$k] = $this->lang->line($v);
                    }
                }
            }
            $this->load->view('auth/send_again_form', $data);
        }
    }

    /**
     * Activate user account.
     * User is verified by user_id and authentication code in the URL.
     * Can be called by clicking on link in mail.
     *
     * @return void
     */
    function activate()
    {
        $user_id       = $this->uri->segment(3);
        $new_email_key = $this->uri->segment(4);

        // Activate user
        if ($this->tank_auth->activate_user($user_id, $new_email_key)) {        // success
            //$this->tank_auth->logout();
            $this->ci->session->set_userdata("status", STATUS_ACTIVATED);
            $this->_show_message($this->lang->line('auth_message_activation_completed'), 'success');

        } else {
            // fail
            $this->_show_message($this->lang->line('auth_message_activation_failed'), 'danger');
        }
    }

    /**
     * Generate reset code (to change password) and send it to user
     *
     * @return void
     */
    function forgot_password()
    {
        if ($this->tank_auth->is_logged_in()) {                                    // logged in
            redirect('');

        } elseif ($this->tank_auth->is_logged_in(false)) {                        // logged in, not activated
            redirect('/auth/send_again/');

        } else {
            $this->form_validation->set_rules('login', 'Email or login', 'trim|required|xss_clean');

            $data['errors'] = array();

            if ($this->form_validation->run()) {                                // validation ok
                if (!is_null(
                    $data = $this->tank_auth->forgot_password(
                        $this->form_validation->set_value('login')
                    )
                )
                ) {

                    $whitelabelinfo = $this->session->userdata('white_label_information');
                    $data['site_name'] = $whitelabelinfo && isset($whitelabelinfo['name']) && $whitelabelinfo['name'] ? $whitelabelinfo['name'] : $this->config->item('website_name', 'tank_auth');

                    // Send email with password activation link
                    $this->_send_email('forgot_password', $data['email'], $data);
                    $this->_show_message($this->lang->line('auth_message_new_password_sent').' '.anchor('/auth/login/', 'Login'));

                } else {
                    $errors = $this->tank_auth->get_error_message();
                    foreach ($errors as $k => $v) {
                        $data['errors'][$k] = $this->lang->line($v);
                    }
                }
            }

            $data['js_files'] = array(
                base_url().auto_version('js/login.js')
            );


            $data['main_content'] = 'auth/forgot_password_form';
            $this->load->view('includes/bootstrapped_template', $data);
        }
    }

    /**
     * Replace user password (forgotten) with a new one (set by user).
     * User is verified by user_id and authentication code in the URL.
     * Can be called by clicking on link in mail.
     *
     * @return void
     */
    function reset_password()
    {
        $user_id      = $this->uri->segment(3);
        $new_pass_key = $this->uri->segment(4);

        $this->form_validation->set_rules('new_password', 'New Password', 'trim|required|xss_clean|min_length['.$this->config->item('password_min_length', 'tank_auth').']|max_length['.$this->config->item('password_max_length', 'tank_auth').']|alpha_dash');
        $this->form_validation->set_rules('confirm_new_password', 'Confirm new Password', 'trim|required|xss_clean|matches[new_password]');

        $data['errors'] = array();

        if ($this->form_validation->run()) {                                // validation ok
            if (!is_null(
                $data = $this->tank_auth->reset_password(
                    $user_id,
                    $new_pass_key,
                    $this->form_validation->set_value('new_password')
                )
            )
            ) {    // success

                $whitelabelinfo = $this->session->userdata('white_label_information');
                $data['site_name'] = $whitelabelinfo && isset($whitelabelinfo['name']) && $whitelabelinfo['name'] ? $whitelabelinfo['name'] : $this->config->item('website_name', 'tank_auth');

                // Send email with new password
                $this->_send_email('reset_password', $data['email'], $data);

                $this->_show_message($this->lang->line('auth_message_new_password_activated'));

            } else {                                                        // fail
                $this->_show_message("ERR1 ".$this->lang->line('auth_message_new_password_failed'));
            }
        } else {
            // Try to activate user by password key (if not activated yet)
            if ($this->config->item('email_activation', 'tank_auth')) {
                $this->tank_auth->activate_user($user_id, $new_pass_key, false);
            }

            if (!$this->tank_auth->can_reset_password($user_id, $new_pass_key)) {
                $this->_show_message("ERR2 ".$this->lang->line('auth_message_new_password_failed'));
            }
        }

        if(isset($_POST['submit'])){

            if($this->input->post('new_password') == ''){
                $data['errors']['blank_new_password'] = 'The New Password field is required.';
            }

            if($this->input->post('new_password') != '' && strlen($this->input->post('new_password')) < 4){
                $data['errors']['short_new_password'] = 'The New Password field must be at least 4 characters in length.';
            }

            if($this->input->post('confirm_new_password') == ''){
                $data['errors']['blank_confirm_new_password'] = 'The Confirm new Password field is required.';
            }

            if($this->input->post('new_password') != $this->input->post('confirm_new_password')){
                $data['errors']['password_not_match'] = 'The Confirm new Password field does not match the New Password field.';
            }
            if ($this->input->post('new_password') != '' && preg_match('/[^a-zA-Z\d]/', $this->input->post('new_password'))) {
                $data['errors']['password_contains_special_characters'] = 'The New Password field must contain alphanumeric characters, underscores, and dashes.';
            }
        }

        $data['js_files'] = array(
            base_url().auto_version('js/login.js')
        );

        $data['main_content'] = 'auth/reset_password_form';
        $this->load->view('includes/bootstrapped_template', $data);

    }

    /**
     * activate_employee
     * User is verified by user_id and authentication code in the URL.
     * Can be called by clicking on link in mail.
     *
     * @return void
     */
    function activate_employee()
    {
        $user_id      = $this->uri->segment(3);
        $new_pass_key = $this->uri->segment(4);

        $this->form_validation->set_rules('new_password', 'New Password', 'trim|required|xss_clean|min_length['.$this->config->item('password_min_length', 'tank_auth').']|max_length['.$this->config->item('password_max_length', 'tank_auth').']|alpha_dash');
        $this->form_validation->set_rules('confirm_new_password', 'Confirm new Password', 'trim|required|xss_clean|matches[new_password]');



        $data['errors'] = array();
        if ($this->form_validation->run()) {                                // validation ok
            $new_password = $this->form_validation->set_value('new_password');

            if (!is_null(
                $data = $this->tank_auth->reset_password(
                    $user_id,
                    $new_pass_key,
                    $new_password
                )
            )
            ) {

                // success

                //$this->_show_message($this->lang->line('auth_message_registration_completed_2').' '.anchor('/auth/login/', 'Login'));
                $user_data = $this->User_model->get_user_profile($user_id);
                $this->tank_auth->login(
                    $user_data['email'],
                    $new_password,
                    1,
                    0,
                    1
                );

                redirect('/menu/select_hotel/'.$user_data['company_id']);
            } else {
                // fail
                $this->_show_message($this->lang->line('auth_message_new_employee_failed'));
            }
        } else {
            // Try to activate user by password key (if not activated yet)
            if ($this->config->item('email_activation', 'tank_auth')) {
                $this->tank_auth->activate_user($user_id, $new_pass_key, false);
            }

            if (!$this->tank_auth->can_reset_password($user_id, $new_pass_key)) {
                $this->_show_message($this->lang->line('auth_message_new_employee_failed'));
            }
        }

        if(isset($_POST['submit'])){

            if($this->input->post('new_password') == ''){
                $data['errors']['blank_new_password'] = 'The New Password field is required.';
            }

            if($this->input->post('new_password') != '' && strlen($this->input->post('new_password')) < 4){
                $data['errors']['short_new_password'] = 'The New Password field must be at least 4 characters in length.';
            }

            if($this->input->post('confirm_new_password') == ''){
                $data['errors']['blank_confirm_new_password'] = 'The Confirm new Password field is required.';
            }

            if($this->input->post('new_password') != $this->input->post('confirm_new_password')){
                $data['errors']['password_not_match'] = 'The Confirm new Password field does not match the New Password field.';
            }
            if ($this->input->post('new_password') != '' && preg_match('/[^a-zA-Z\d]/', $this->input->post('new_password'))) {
                $data['errors']['password_contains_special_characters'] = 'The New Password field must contain alphanumeric characters, underscores, and dashes.';
            }
        }

        $user_profile = $this->User_model->get_user_profile($user_id);
        $data['email'] = $user_profile['email'];

        $data['css_files'] = array(base_url().auto_version('css/register_employee_form.css'));

        $data['main_content'] = 'auth/activate_employee_form';

        $this->load->view('includes/bootstrapped_template', $data);
    }

    /**
     * Change user email
     *
     * @return void
     */
    function change_email()
    {
        if (!$this->tank_auth->is_logged_in()) {                                // not logged in or not activated
            redirect('/auth/login/');

        } else {
            $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
            $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email');

            $data['errors'] = array();

            if ($this->form_validation->run()) {                                // validation ok
                if (!is_null(
                    $data = $this->tank_auth->set_new_email(
                        $this->form_validation->set_value('email'),
                        $this->form_validation->set_value('password')
                    )
                )
                ) {            // success

                    $whitelabelinfo = $this->session->userdata('white_label_information');
                    $data['site_name'] = $whitelabelinfo && isset($whitelabelinfo['name']) && $whitelabelinfo['name'] ? $whitelabelinfo['name'] : $this->config->item('website_name', 'tank_auth');

                    // Send email with new email address and its activation link
                    $this->_send_email('change_email', $data['new_email'], $data);

                    $this->_show_message(sprintf($this->lang->line('auth_message_new_email_sent'), $data['new_email']));

                } else {
                    $errors = $this->tank_auth->get_error_message();
                    foreach ($errors as $k => $v) {
                        $data['errors'][$k] = $this->lang->line($v);
                    }
                }
            }
            $this->load->view('auth/change_email_form', $data);
        }
    }

    /**
     * Replace user email with a new one.
     * User is verified by user_id and authentication code in the URL.
     * Can be called by clicking on link in mail.
     *
     * @return void
     */
    function reset_email()
    {
        $user_id       = $this->uri->segment(3);
        $new_email_key = $this->uri->segment(4);

        // Reset email
        if ($this->tank_auth->activate_new_email($user_id, $new_email_key)) {    // success
            $this->tank_auth->logout();
            $this->_show_message($this->lang->line('auth_message_new_email_activated').' '.anchor('/auth/login/', 'Login'));

        } else {                                                                // fail
            $this->_show_message($this->lang->line('auth_message_new_email_failed'));
        }
    }

    /*
    // Deprecated

    // delete all bookings & clean all rooms for the existing test company.
    function reset_test_company() {
        $this->load->model('Test_model');
        $this->Test_model->reset_test_company();
        //$this->logout();
    }


    // delete all charges, payments and clean all rooms for the existing test company.
    function reset_bookings() {
        $this->load->model('Test_model');
        $this->Test_model->reset_bookings();
        $this->logout();
    }
    */

    // Used for Angular Client (app_angular.js)

    /**
     * Delete user from the site (only when user is logged in)
     *
     * @return void
     */
    function unregister()
    {
        if (!$this->tank_auth->is_logged_in()) {                                // not logged in or not activated
            redirect('/auth/login/');

        } else {
            $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');

            $data['errors'] = array();

            if ($this->form_validation->run()) {                                // validation ok
                if ($this->tank_auth->delete_user(
                    $this->form_validation->set_value('password')
                )
                ) {        // success
                    $this->_show_message($this->lang->line('auth_message_unregistered'));

                } else {                                                        // fail
                    $errors = $this->tank_auth->get_error_message();
                    foreach ($errors as $k => $v) {
                        $data['errors'][$k] = $this->lang->line($v);
                    }
                }
            }
            $this->load->view('auth/unregister_form', $data);
        }
    }

    /**
     * Callback function. Check if number of rooms < 70 test is passed.
     *
     * @return    bool
     * deprecated
     */
    function _check_max_rooms($number_of_rooms)
    {
        $whitelabelinfo = $this->session->userdata('white_label_information');
        $reply_to_email = $whitelabelinfo && isset($whitelabelinfo['support_email']) && $whitelabelinfo['support_email'] ? $whitelabelinfo['support_email'] : 'support@minical.io';

        if ($number_of_rooms > 500) {
            $this->form_validation->set_message(
                '_check_max_rooms',
                'Your property is larger than what we typically see.<br />
                    To sign up, please contact us at '.$reply_to_email,
            );

            return false;
        }

        return true;
    }

    function _check_phone_number($value)
    {
        if ($value[0] != '+')
            $value = "+".$value;
        if (1 !== preg_match('/\+(9[976]\d|8[987530]\d|6[987]\d|5[90]\d|42\d|3[875]\d|2[98654321]\d|9[8543210]|8[6421]|6[6543210]|5[87654321]|4[987654310]|3[9643210]|2[70]|7|1)\W*\d\W*\d\W*\d\W*\d\W*\d\W*\d\W*\d\W*\d\W*(\d{1,2})$/', $value))
        {
            $this->form_validation->set_message(
                '_check_phone_number',
                'Invalid phone number'
            );
            return false;
        }
        return true;

    }

    function show_terms_of_service()
    {
        $this->load->view('auth/terms_of_service.php');
    }

    function show_privacy_policy()
    {
        $this->load->view('auth/privacy_policy.php');
    }

    function get_all_session_variables()
    {
        echo json_encode($this->session->all_userdata());
    }

    public function get_subscription_state_extended()
    {
        $this->load->model('Company_subscription_model');
        $whitelabelinfo = $this->session->userdata('white_label_information');
        $reply_to_email = $whitelabelinfo && isset($whitelabelinfo['support_email']) && $whitelabelinfo['support_email'] ? $whitelabelinfo['support_email'] : 'support@minical.io';
        //$this->load->library('Chargify_wrapper');
        $subscription = null;
        $response     = array(
            'is_blocking' => 0,
            'message'     => '',
            'state'       => 'active',
            'show_link'   => 0
        );
        if ($this->company_id) {
            $subscription = $this->Company_subscription_model->get_company_subscription($this->company_id);
        }
        
        if ($subscription) {
            //itodo fix this
            $is_manual = ($subscription['payment_method'] == 'manual');
            switch ($subscription['subscription_state']) {
               case 'deleted':
                case 'past_due':
                case 'unpaid':                  
                    $response = array(
                        'is_blocking' => 0,
                        'message'     => 'Your account payment is past due. '.($is_manual
                                ? 'Please contact '.$reply_to_email
                                : 'Please update your payment details. '),
                        'show_link'   => 1,
                        'state'       => $subscription['subscription_state']
                    );
                    break;
                case 'trial_ended':
                    $response = array(
                        'is_blocking' => 0,
                        'message'     => 'Thank you for trying minical! To set up your recurring subscription '.($is_manual
                                ? 'please contact '.$reply_to_email
                                : 'please update your payment details. '),
                        'show_link'   => 1,
                        'state'       => $subscription['subscription_state']
                    );
                    break;
                // case 'canceled':
                //     $response = array(
                //         'is_blocking' => 1,
                //         'message'     => 'Your account is about to be suspended. '.($is_manual
                //                 ? 'Please contact '.$reply_to_email
                //                 : 'Please update your payment details. '),
                //         'show_link'   => $is_manual ? 0 : 1,
                //         'state'       => $subscription['subscription_state']
                //     );
                //     break;
                // case 'suspended':
                //     $response = array(
                //         'is_blocking' => 1,
                //         'message'     => 'Your account was suspended <br/> For more information please contact '.$reply_to_email,
                //         'show_link'   => 0,
                //         'state'       => $subscription['subscription_state']
                //     );
                //     break;
            }
        }
        $this->session->set_flashdata('flash', 'value');
        echo json_encode($response);
    }

    // show forbidden page
    function forbidden()
    {
        //Load view
        $data['menu_on'] = TRUE;
        $data['selected_menu'] = '';
        $data['main_content'] = 'forbidden';
        $this->load->view('includes/bootstrapped_template', $data);
    }

    // show restriction page
    function access_restriction()
    {
        //Load view
        $data['menu_on'] = TRUE;
        $data['selected_menu'] = '';
        $data['main_content'] = 'access_restriction';
        $this->load->view('includes/bootstrapped_template', $data);
    }

    function thank_you()
    {
        $whitelabelinfo = $this->session->userdata('white_label_information');
        $data['support_email'] = $whitelabelinfo && isset($whitelabelinfo['support_email']) && $whitelabelinfo['support_email'] ? $whitelabelinfo['support_email'] : 'support@minical.io';
       
        $data['menu_on'] = FALSE;
        $data['main_content'] = 'auth/thank_you';
        $this->load->view('includes/bootstrapped_template', $data);
    }

    function clean_test_user($email)
    {
        $user = $this->User_model->get_user_by_email($email);
        $this->User_model->delete_user_by_email($email);
        $companies = $this->Company_model->get_companies($user['id']);
        foreach($companies as $company)
        {
            $this->Company_model->delete_company($company['company_id']);
        }
    }

    function update_company(){
        $phone_number = $this->input->post('phone_number');
        $phone_number_country_code = $this->input->post('phone_number_country_code');
        $phone_number = $phone_number_country_code ."". $phone_number;
        $data = array(
            'first_name' => $this->input->post('first_name'),
            'last_name' => $this->input->post('last_name'),
            'name' => $this->input->post('property_name'),
            'number_of_rooms' => $this->input->post('number_of_rooms'),
            'property_type' => $this->input->post('property_type'),
            'country' => $this->input->post('country'),
            'lang_id' => $this->input->post('lang_id'),
            'phone' => $phone_number
        );
        $white_label_data = $this->Whitelabel_partner_model->get_whitelabel_partners();
        $white_label_partner_id = 0;
        if($white_label_data && count($white_label_data) > 0 ){
            foreach($white_label_data as $white_label)
            {
                $location = json_decode($white_label['location']);
                if(isset($location) && $location && in_array($data['country'], $location))
                {
                    $white_label_partner_id = $white_label['id'];
                    break;
                }
            }
        }

        $Rdata = $this->_update_company($data);

        ///property build logic
         $data_build = $company_data = array();
        $is_hosted_prod_service = getenv('IS_HOSTED_PROD_SERVICE');
        if($is_hosted_prod_service || $_SERVER['HTTP_HOST'] == "app.minical.io" || $_SERVER['HTTP_HOST'] == "demo.minical.io"){
         
            $property_data = $this->Company_model->get_property_build($data['property_type']);
            $feature_setting = json_decode($property_data['setting_json'], true);
            $dependencies = json_decode($property_data['dependences_json'], true);
           
        } else {
            
            $fileJson = file_get_contents("../build.json");
            $file = json_decode($fileJson, true);
           
            $feature_setting = $file['settings'];
            $dependencies= $file['dependencies'];
           
        }

        $extensions = $this->all_active_modules;

        $data_build['company_id'] = $this->company_id;
        if(isset($dependencies) && count($dependencies) > 0){
             foreach ($dependencies as $key => $value) {
                foreach($extensions as $ext => $extension){
                    if($ext == $key){
                        $data_build['extension_name'] = $key;
                        $this->Extension_model->update_extension($data_build);
                    }
                }
            }
        }
         
        if(isset($feature_setting) && count($feature_setting) > 0) {
         
            $build_mapping_array = json_decode(BUILD_KEY_MAPPING,true);
            foreach ($feature_setting as $key => $value) {
                if(is_array($value)){
                    foreach ($value as $key1 => $val) {
                        $keyname= $key."_".$key1;
                        if(in_array($keyname, array_keys($build_mapping_array)))
                        {
                            $map_value = $build_mapping_array[$keyname];
                            $company_data[$map_value] = $val ? $val : 0;
                        }
                    }
                } else {
                    if(in_array($key, array_keys($build_mapping_array))){
                        $map_value = $build_mapping_array[$key];
                        $company_data[$map_value] = $value ? $value : 0;
                    }
                }
            }
            $this->Company_model->update_company($this->company_id, $company_data);
        }
         ///end

        $user_id =  $this->session->userdata('user_id');

        $this->User_model->update_user($user_id, array('partner_id' => $white_label_partner_id));
        $response = array(
            'success'=> 'true'
        );

        $this->session->unset_userdata('is_registration_page');

        echo json_encode($response);
    }

    private function _update_company($data)
    {
        $user_id =  $this->session->userdata('user_id');
        
        $this->load->model('Charge_type_model');
        $this->load->model('Payment_model');
        
        $company = $this->User_model->get_company_id($user_id);
        $company_id = $company->company_id;

        // update company in minical database
        $company_data = array(
            'name'             => $data['name'],
            'number_of_rooms'  => isset($data['number_of_rooms'])?$data['number_of_rooms']:'15',
            'country'          => isset($data['country'])?$data['country']:'',
            'phone'            => isset($data['phone'])?$data['phone']:'',
            'property_type'    => $data['property_type'],
            'enable_api_access' => 1
        );

        // create company in minical
        $this->Company_model->update_company($company_id, $company_data);
        $api_key = md5(uniqid(rand(), true));
        $this->Company_model->insert_company_api_key($company_id, $api_key);
        // create all the default data entries. Such as rooms, room types, charge types, etc...
        //First remove previous charge types associated with this company
        $this->Charge_type_model->delete_charge_type($company_id);
        
        //First remove previous payment types associated with this company
        $this->Payment_model->delete_payment_type($company_id);

        //First remove previous room types associated with this company
        $this->Room_type_model->delete_room_type_by_company($company_id);
 
        //First remove previous rooms associated with this company
        $this->Room_model->delete_room($company_id);
        
        // Now innitialize new rooms
        $this->_initialize_company($company_id);

        // update user minical database

        if($this->config->item('app_environment') == "development"){
            $this->ci->session->set_userdata('status', STATUS_ACTIVATED);
        } else {
            $user_data = array(
                'activated'         => 0
            );
            $this->User_model->update_user($user_id, $user_data);
            $this->ci->session->set_userdata('status', STATUS_NOT_ACTIVATED);
        }

        $explode = explode(',', $data['lang_id']);
        $language_id = $explode[0];
        $new_language = $explode[1];
        
        $user_profile_data = array(
            'first_name'         => $data['first_name'],
            'last_name'          => $data['last_name'],
            'language'           => $new_language,
            'language_id'        => $language_id
        );
        $this->User_model->update_user_profile($user_id, $user_profile_data);

        $this->session->set_userdata('first_name',$data['first_name']);
        $this->session->set_userdata('last_name',$data['last_name']);

        // apply language change immediately by updating session variable
        $this->session->set_userdata(array( 'language' => $new_language ));
        $this->session->set_userdata(array( 'language_id' => $language_id ));
        // Call function to load translation of language
        load_translations($language_id);

        // push property data to close.io lead.
        //bring user by id.
        $user = $this->User_model->get_user_by_id($user_id);

        return 200;
    }
    
    function resend_verification_email_AJAX()
    {
        
        $user_id =  $this->session->userdata('user_id');
        $whitelabelinfo = $this->session->userdata('white_label_information');
        $user_profile = $this->User_model->get_user_profile($user_id);
        $data = array(
            'user_id' => $user_id,
            'username'    => $user_profile['username'],
            'email'        => $user_profile['email'],
            'new_email_key' => md5(rand().microtime()),
            'site_name' => $whitelabelinfo && isset($whitelabelinfo['name']) && $whitelabelinfo['name'] ? $whitelabelinfo['name'] : $this->config->item('website_name', 'tank_auth'),
            'activation_period' => $this->config->item('email_activation_expire', 'tank_auth') / 3600
        );
        
        $this->User_model->update_user($user_id, array('new_email_key' => $data['new_email_key']));
        
        $this->_send_email('activate', $data['email'], $data);
        echo l('successfully sent', true);    
   }
   
    function google_recaptcha_validate_token($secret_key, $recaptcha_token)
    {
        $data = array(
            'secret' => $secret_key,
            'response' => $recaptcha_token,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        );
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $response = json_decode($response, true);
  
        if($response["success"])
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function checkSession()
    {
        $user_id =  $this->session->userdata('user_id');
        $user_email =  $this->session->userdata('email');

        if (!$user_id || !$user_email) {
            $this->logout();
            redirect('/auth/login/');
        }
    }
}
