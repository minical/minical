<?php
class Company extends MY_controller
{
	function __construct()
	{
		parent::__construct();
		 
		$this->load->model('Company_model');
		$this->load->model('Company_subscription_model');
		$this->load->model('User_model');
        $this->load->model('Whitelabel_partner_model');
        $this->load->model('Image_model');
		$this->load->library('form_validation');			
		
		$this->load->helper('url'); // for redirect
		$this->load->helper('timezone');
		
		$view_data['menu_on'] = TRUE;		
		
		$this->load->vars($view_data);
	}
	
	function index()
	{
        
	}
    
    function get_all_companies(){
        $this->load->model('Admin_model');
        $Promising  = $this->Admin_model->get_company_list(array(
                                                                    'active_subscriptions' => false,
                                                                    'status'=> 'trialing',
																	'last_login' => 10
                                                                ));

        $Unpaid  = $this->Admin_model->get_company_list(array(
																	'active_subscriptions' => false,
																	'status'=> 'unpaid',
																	'last_login' => 10
                                                                ));
        $Active  = $this->Admin_model->get_company_list(array(
                                                                    'active_subscriptions' => true,
																	'status'=> 'active'
                                                                ));
        if($Unpaid)
            echo json_encode(array_merge($Promising, $Unpaid, $Active));
        else 
            echo json_encode(array_merge($Promising, $Active));
        //$companies = $this->Company_model->get_all_companies();
        //echo json_encode($companies);
    }
    
	function get_company_in_JSON($company_id, $get_last_action = 0) 
	{
		if ($this->permission->check_access_to_company_id($this->user_id, $company_id))
		{
            $company = $this->Company_model->get_company($company_id, array("get_last_action" => (boolean) $get_last_action)); 
            // calculate NAD
            $now = time(); // or your date as well
            $datediff = $now - strtotime($company['selling_date']);
            $company['nad'] = floor($datediff / (60 * 60 * 24));   

            $company_subscription = $this->Company_subscription_model->get_company_subscription($company_id);
            // calculate mrr 
            $renewal_multiple = 1;

            if (strpos($company_subscription['renewal_period'], 'year')  !== false || strpos($company_subscription['renewal_period'], '12 month') !== false)
            {
                    $renewal_multiple = 12;
            }
            elseif (strpos($company_subscription['renewal_period'], "6 month")  !== false)
            {
                    $renewal_multiple = 6;
            }
            elseif (strpos($company_subscription['renewal_period'], "3 month")  !== false)
            {
                    $renewal_multiple = 3;
            }
            if(is_numeric($company['partner_id'])) {
                $partner = $this->Whitelabel_partner_model->get_partner_detail($company['partner_id']);
                $company['partner_slug'] = isset($partner['partner_slug']) ? $partner['partner_slug'] : "";
            }
            $company['is_current_user_super_admin'] = ($this->user_id == SUPER_ADMIN_USER_ID);
            
            $mrr = $company_subscription['renewal_cost'] / $renewal_multiple;
            $company_subscription['mrr'] = number_format($mrr, 2, ".", "");  

            $company_key_data = $this->ci->Company_model->get_company_api_permission($company_id);
            if(isset($company_key_data[0]['key'])){
                $company['api_key'] = $company_key_data[0]['key'];
            }

			$company = array_merge($company, $company_subscription );
			echo json_encode($company);	
		}
	}
    
    function get_partners_in_JSON(){
       $array['is_deleted'] = '0';
        $data = $this->Whitelabel_partner_model->get_partners($array,false);
        echo json_encode($data);
    }

	// Updates "Current Time" based on which timezone the user selects
	function get_current_time_in_JSON() 
	{
		$time_zone = $this->input->post('time_zone');
		$str = convert_to_local_time(new DateTime(), $time_zone)->format("Y-m-d h:i A");
		echo json_encode($str);
	}
	
	// Check if the currently selected company (according to session data) has the tag associated to it (according to Admin panel)
	function has_tag($tag)
	{
		$json = json_encode($this->Company_model->has_tag($this->company_id, $tag));
		echo $json;
	}

	function delete($company_id)
	{
		$this->Company_model->delete_company($company_id);
		echo l("company deleted",true);
	}
    
    function update_logo_image_group_id()
    {
        
        $companies = $this->Company_model->get_data_of_companies();        
        foreach($companies as $row)
        {            
            $data = array();
            $data['logo_image_group_id'] = $this->Image_model->create_image_group(LOGO_IMAGE_TYPE_ID);
            $this->Company_model->update_company($row['company_id'], $data);
        }
       
    }
    function turn_on_off()
    {
        $this->load->model('Whitelabel_partner_model');
        $view_data['company_data'] = $this->Company_model->get_company($this->company_id);
        $view_data['company_api_key'] = $this->Company_model->get_company_api_permission($this->company_id);
        $company_partner_id = isset($this->company_partner_id) && $this->company_partner_id ? $this->company_partner_id : 1;
        $view_data['partner'] = $this->Whitelabel_partner_model->get_partner_detail($company_partner_id);
        $view_data['feature_setting_enabled'] = $this->company_subscription_level == ELITE ? true : false;
        $view_data['js_files'] = array(
            base_url() . 'js/hotel-settings/email-settings.js',
            base_url() . auto_version('js/hotel-settings/online-settings.js')
        );
//        $view_data['selected_sidebar_link'] = 'Turn Features On/Off';
        $view_data['selected_sidebar_link'] = 'Feature Settings';
        $view_data['main_content'] = 'hotel_settings/feature_settings/turn_on_off';
        $this->load->view('includes/bootstrapped_template', $view_data);
    }

    function feature_settings() {
        $this->turn_on_off();
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
	
}