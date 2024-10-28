<?php
class Company_security extends MY_Controller
{
    function __construct()
    {

        parent::__construct();
        
        $this->load->model('Company_model');
        $this->load->model('User_model');
        $this->load->model('Option_model');
        $this->load->model('Employee_log_model');
        $this->load->model('Company_security_model');
        
        $this->load->library('google_security');

        $view_data['menu_on'] = true;

        $this->user_id    = $this->session->userdata('user_id');
        $this->company_id = $this->session->userdata('current_company_id');

        $view_data['menu_on']          = true;
        $view_data['selected_menu']    = 'Settings';
        $view_data['selected_submenu'] = 'Security';

        $view_data['submenu'] = 'hotel_settings/hotel_settings_submenu.php';

        $view_data['submenu_parent_url'] = base_url()."settings/";
        $view_data['sidebar_menu_url'] = base_url()."settings/company_security/";

        $view_data['menu_items'] = $this->Menu_model->get_menus(array('parent_id' => 6, 'wp_id' => 1));
        $view_data['sidebar_links'] = $this->Menu_model->get_menus(array('parent_id' => 29, 'wp_id' => 1));

        $this->load->vars($view_data);
    }

    function index()
    {
        $this->view_company_security();
    }

    function view_company_security()
    {
        $security_data =  $this->Option_model->get_option_by_company('company_security',$this->company_id);
        
        $data['company_security'] = isset($security_data[0]['option_value']) ? json_decode($security_data[0]['option_value'],true) :"";

        $data['security_data'] = $this->Company_security_model->get_deatils_by_company_user(null, $this->user_id);

        $email = $this->user_email;
        $security_name = 'security';

        $data['secure_data'] = $this->google_security->create_secret($email, $security_name);
        $data['security_name'] = $security_name;

        $company_id = $this->company_id;
        
        $data['main_content'] = 'hotel_settings/company/company_security';
        $this->load->view('includes/bootstrapped_template', $data);

    }
  
    function add_update_company_security()
    {
            $status = $this->input->post('security_status');
        
            $security_status = isset($status) && $status !='' ? 1 : 0;
            $lock_timer = $this->input->post('lock_timer');
            $company_data = array(
                'security_status' => $security_status,
                'lock_timer' => $this->input->post('lock_timer'),
                'otp_verified' => 1
            );
            
            $security_data =  $this->Option_model->get_option_by_company('company_security',$this->company_id);
            
            if($security_data){
               
               $id = $this->Option_model->update_option_company('company_security',json_encode($company_data),$this->company_id);
            } else {

                $security = array(
                            'company_id' => $this->company_id,
                            'option_name ' => 'company_security',
                            'option_value ' => json_encode($company_data),
                            'autoload' => 0
                        );
                 $this->Option_model->add_option($security); 
            }
        
            $this->_create_employee_log("Update security settings [comp_id {$this->company_id}, status {$security_status}, lock_timer {$lock_timer}]");
            redirect('/settings/company_security');
    }

    function enable_otp_verification(){
        
        $security_data =  $this->Option_model->get_option_by_company('company_security',$this->company_id);
        
        if($security_data){

            $secure = json_decode($security_data[0]['option_value'], true);

            $company_data = array(
                'security_status' => $secure['security_status'],
                'lock_timer' => $secure['lock_timer'],
                'otp_verified' => 0
            );
           
            $this->Option_model->update_option_company('company_security', json_encode($company_data), $this->company_id);

            echo json_encode(array('otp_verified' => 0));
       }
    }

    function get_otp_verification(){

        // if(!$this->is_partner_owner){
        //     $security_data =  $this->Company_security_model->get_deatils_by_company_user($this->company_id, $this->user_id);
        // } else {
        //     $security_data = true;
        // }

        // prx($security_data);

        $company_security = $this->Option_model->get_option_by_company('company_security',$this->company_id);

        // if($company_security && $security_data){
        if($company_security){

            $secure = json_decode($company_security[0]['option_value'], true);

            if(!$secure['otp_verified'])
                echo json_encode(array('otp_verified' => 0));
       }
    }

    function change_new_qr_code(){

        $email = $this->user_email;
        $security_name = 'new_code_'.date('m-d');

        $data['secure_data'] = $this->google_security->create_secret($email, $security_name);
        $data['security_name'] = $security_name;

        $company_id = $this->company_id;

        $this->load->view('hotel_settings/company/change_new_qr_code', $data);
    }

    function disable_security(){

        $security_data =  $this->Option_model->get_option_by_company('company_security',$this->company_id);
        
        if($security_data){

            $option_value = json_decode($security_data[0]['option_value'], true);

            $company_data = array(
                'security_status' => 0,
                'lock_timer' => $option_value['lock_timer'],
                'otp_verified' => 1
            );
           
            $id = $this->Option_model->update_option_company('company_security',json_encode($company_data), $this->company_id);
        }

        $this->load->view('hotel_settings/company/disable_security');
    }

    function verify_new_otp(){
        $otp = $this->input->post('otp');
        $secret = $this->input->post('secret');
        $qr_code_url = $this->input->post('qr_code_url');
        $security_name = $this->input->post('security_name');
        
        $via = $this->input->post('via');

        $company_id = $this->session->userdata('current_company_id');
        $user_id = $this->user_id;

        $check = $this->google_security->check_secret_with_otp($secret, $otp);

        if($check){

            $company_security_data = array();
            $company_security_data['company_id'] = $company_id;
            $company_security_data['user_id'] = $user_id;
            $company_security_data['secret_code'] = $secret;
            $company_security_data['qr_code_url'] = $qr_code_url;
            $company_security_data['security_name'] = $security_name;
            $company_security_data['created_at'] = gmdate('Y-m-d H:i:s');

            $security_data = $this->Company_security_model->get_deatils_by_company_user(null, $this->user_id);
// prx($security_data);
            if($security_data && count($security_data) > 0) {

                $company_security_data['company_id'] = $security_data['company_id'];
                $this->Company_security_model->update($security_data['company_id'], $this->user_id, $company_security_data);
                $this->session->set_flashdata('code_changed', 'QR Code changed successfully');

                $this->_create_employee_log("Update QR Code [comp_id {$this->company_id}, user_id {$user_id}]");

                echo json_encode(array('success' => true));
            } elseif($via == 'company_security_first_time') {
                $this->Company_security_model->insert($company_security_data);

                $this->_create_employee_log("Insert QR Code [comp_id {$this->company_id}, user_id {$user_id}]");

                echo json_encode(array('success' => true));
            }

        } else {
            echo json_encode(array('success' => false, 'error_msg' => 'Invalid code. Please try again.'));
        }
    }

    function afk_verify_otp(){
        $otp = $this->input->post('otp');
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

        $secret_data = $this->Company_security_model->get_deatils_by_company_user(null, $this->user_id);

        $secret = $secret_data['secret_code'];

        $check = $this->google_security->check_secret_with_otp($secret, $otp);

        if($check){

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
            echo json_encode(array('success' => true));
    
        } else {
            echo json_encode(array('success' => false, 'error_msg' => 'Invalid code. Please try again.'));
        }
    }

    function show_cc_verify_otp(){
        $otp = $this->input->post('otp');

        $secret_data = $this->Company_security_model->get_deatils_by_company_user(null, $this->user_id);

        $secret = $secret_data['secret_code'];

        $check = $this->google_security->check_secret_with_otp($secret, $otp);

        if($check){
                
            echo json_encode(array('success' => true));
    
        } else {
            echo json_encode(array('success' => false, 'error_msg' => 'Invalid code. Please try again.'));
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
    
}
