<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Cron extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
        // Load Language Translation Helper
        $this->load->helper('language_translation');
        $this->load->model('Channex_model');
	}
	
	function ota_booking_retrieval($authorization_code = null)
	{	
		if (getenv('CRON_AUTH_SECRET')) {
			if (!$authorization_code || $authorization_code !== getenv('CRON_AUTH_SECRET')) {
				echo 'Not authorized';
				return;
			}
		}
    	
    	$this->get_channex_bookings($authorization_code);
	}

	function get_channex_bookings($authorization_code)
	{
		if (getenv('CRON_AUTH_SECRET')) {
			if (!$authorization_code || $authorization_code !== getenv('CRON_AUTH_SECRET')) {
				echo 'Not authorized';
				return;
			}
		}

		$this->load->model('Company_model');
		
		$companies = $this->Company_model->get_all_companies(true, 'channex');
		
		if($companies) {
			foreach ($companies as $company)
			{
				$company_id = $company['company_id'];

	            $protocol = $this->config->item('server_protocol');
			    // $url = $protocol . $_SERVER['HTTP_HOST']."/cron/channex_get_bookings/".$company_id;

			    $url = base_url()."cron/channex_get_bookings/".$company_id;
			    
			    $ch = curl_init();
			    curl_setopt($ch, CURLOPT_URL, $url);
			    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			    curl_setopt($ch, CURLOPT_AUTOREFERER, false);
			    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			    curl_setopt($ch, CURLOPT_HEADER, 0);
			    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			    $result = curl_exec($ch);
			    curl_close($ch);

			    echo $company_id. ' => '; prx($result, 1);

			}
		}
	}

	function delete_channex_credit_cards()
	{
		$this->load->model('Company_model');
		$this->load->model('Booking_model');
		$this->load->model('Card_model');
		$this->load->model('Company_subscription_model');

		// Update company subscription status to "Unpaid"
		$company_data = $this->Company_model->get_companies_by_state('trialing');
 		if(isset($company_data) && $company_data){
 			foreach ($company_data as $comp){
 				if($comp['subscription_id'] == null && $comp['partner_id'] == 21 && $comp['expiration_date'] < date('Y-m-d') ){
                   $data = array(
                                'company_id' => $comp['company_id'],
                                 'subscription_state' => 'unpaid'
                            );
                     
                     $this->Company_subscription_model->insert_or_update_company_subscription($comp['company_id'],$data);
 				}
 			}	
 		}

		// $companies = $this->Company_model->get_all_companies(true, 'channex');
		$companies = $this->Company_model->get_all_companies();
		
		foreach ($companies as $company)
		{
			$company_id = $company['company_id'];

			$ota_bookings = $this->Booking_model->get_ota_bookings($company_id);

			if($ota_bookings){
				foreach($ota_bookings as $booking){

					$timestamp = strtotime($booking['check_out_date']); //1373673600

					// getting current date 
					$cDate = strtotime(date('Y-m-d H:i:s'));

					// Getting the value of old date + 7 days
					$oldDate = $timestamp + (86400 * 7); // 86400 seconds in 24 hrs 

					if($oldDate < $cDate)
					{
						$api_key = $_SERVER['CHANNEX_PCI_API_KEY'];

					    $customer_meta_data = json_decode($booking['customer_meta_data'], true);
					    $card_token = $customer_meta_data['token'];

					    $api_url = 'https://pci.channex.io/api/v1/cards/'.$card_token.'?api_key='.$api_key;

					    $method_type = 'delete';

					    $data = $headers = array();

					    $response = $this->call_api($api_url, $data, $headers, $method_type, $company_id);
					    $response = json_decode($response, true);

					    if($response['success']){

					    	$meta = $customer_meta_data;
					    	$meta['token'] = null;
	            			$update_data = array(
	            								'customer_meta_data' => NULL,
	            								'cc_number' => NULL,
	            								'cc_expiry_month' => NULL,
	            								'cc_expiry_year' => NULL,
	            								'cc_tokenex_token' => NULL,
	            								'cc_cvc_encrypted' => NULL,
	            								);

					    	$this->Card_model->update_customer_primary_card($booking['customer_id'], $update_data);

					    	$property_data = $this->Channex_model->get_channex_x_company(null, $company_id);
					    	
					    	$log_data = array(
				    					'ota_property_id' => $property_data['ota_property_id'],
				    					'request_type' => 4,
				    					'response_type' => 0,
				    					'xml_in' => $api_url,
				    					'xml_out' => json_encode($response['resp'])
									);
				    		$this->Channex_model->save_logs($log_data);

					    	echo "Card token deleted successfully - ".$card_token.'<br/>';
					    }
					}
				}
			}
		}
	}

	function call_api($api_url, $data, $headers, $method_type = 'POST'){

	    $url = $api_url;

	    $curl = curl_init();
	    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	        
	    if($method_type == 'GET'){

	    } else if($method_type == 'delete'){
	    	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
	    } else {
	        curl_setopt($curl, CURLOPT_POST, 1);
	        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
	    }
	           
	    curl_setopt($curl, CURLOPT_URL, $url);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
	    $response = curl_exec($curl);

	    $response = json_decode($response, true);
    	$response['curl_info'] = curl_getinfo($curl);

	    curl_close($curl);

	    if(isset($response['curl_info']['http_code']) && $response['curl_info']['http_code'] == '204'){
    		return json_encode(array('success' => true, 'resp' => $response['curl_info']));
	    }
	    return json_encode(array('success' => false, 'resp' => $response['curl_info']));
	}

	function handle_session_overflow()
    {
        $this->load->model('Cron_model');
        $this->Cron_model->handle_session_overflow();
    }

    function get_companies() {
        $this->load->model(array('Company_model'));

        $companies = $this->Company_model->get_all_companies(true);

        $company_ids = array();
        foreach ($companies as $company) {
            $company_ids[] = array('company_id' => $company['company_id'], 'company_name' => $company['name']);
        }
        echo json_encode($company_ids);
    }

    function set_pricing_mode(){
    	$this->load->model(array('Company_model'));

    	$companies = $this->Company_model->get_all_companies(true);
		$company_ids = array();

		foreach ($companies as $company)
		{
			$company_ids[] = $company['company_id'];
		}

		$ota_x_data = $this->Company_model->get_ota_x_company_data($company_ids);

		foreach ($ota_x_data as $key => $value) {
			if(isset($value['rate_update_type']) && $value['rate_update_type']){
				$this->Company_model->set_pricing_mode_rate_plan($value);
			}
		}
    }

    function full_sync(){
    	$this->load->model(array('Company_model'));

    	$companies = $this->Company_model->get_all_companies(true, 'channex');
		
		foreach ($companies as $company)
		{
			$company_id = $company['company_id'];

            $protocol = $this->config->item('server_protocol');
		    $url = base_url()."cron/full_sync_cron/".$company_id;

		    $ch = curl_init();
		    curl_setopt($ch, CURLOPT_URL, $url);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		    curl_setopt($ch, CURLOPT_AUTOREFERER, false);
		    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		    curl_setopt($ch, CURLOPT_HEADER, 0);
		    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		    $result = curl_exec($ch);
		    curl_close($ch);

		    echo 'company_id '.$company_id. ' => '; prx($result, 1);

		}
    }

    function full_sync_cron($company_id)
	{
		$this->load->model(array('Extension_model'));
		$this->load->helper('my_assets_helper');
		
		// $companies = $this->Company_model->get_all_companies();
		
		//foreach ($companies as $company)
		//{
		// $company_id = $company['company_id'];

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
                    else
                    {
                        continue;
                    }
                }
            }
        }

        // prx($autoload_packages, 1);

        if($autoload_helpers && count($autoload_helpers) > 0)
            $this->load->helper($autoload_helpers);
        if($autoload_packages && count($autoload_packages) > 0)
            $this->load->helper($autoload_packages, true);


        $data = array(
					'company_id' => $company_id,
					'update_from' => 'extension'
				);

        // update availabilities
        do_action('update_availability', $data);
        do_action('update_siteminder_availability', $data);

        // update rates
        do_action('update_rates', $data);
        do_action('update_siteminder_rates', $data);
		// }
	}

	function hourly(){

		$data = array();
		do_action('hourly-cron', $data);
	}

	function daily(){

		$this->load->model(array('Extension_model','Company_model'));
		$this->load->helper('my_assets_helper');
        $companies = $this->Company_model->get_all_companies(false);
		$data = array();
		foreach ($companies as $company)
	 	{ 

	        $active_extensions = $this->Extension_model->get_active_extensions($company['company_id']);
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
	                    else
	                    {
	                        continue;
	                    }
	                }
	            }
	        }
	     
        if($autoload_helpers && count($autoload_helpers) > 0)
            $this->load->helper($autoload_helpers);
        if($autoload_packages && count($autoload_packages) > 0)
            $this->load->helper($autoload_packages, true);
        }
        
		do_action('daily-cron', $data);
	}

	function send_payment_reminder()
	{
		$this->load->model('Company_model');

		
        // $all_active_modules = array();
        // $module_name ='';
        // $modules_path = $this->config->item('module_location');
        // $modules = scandir($modules_path);
        // // $extensions = $this->session->userdata('all_active_modules');
        // foreach($modules as $module)
        // {
        //     if($module === '.' || $module === '..') continue;
        //     if(is_dir($modules_path) . '/' . $module)
        //     {
        //         $config = array();
        //         $module_config = $modules_path . $module . '/config/config.php';
        //         if(file_exists($module_config))
        //         {
        //             require($module_config);
        //             $config['extension_folder_name'] = $module;
        //             $all_active_modules[$module] = $config;
                  
        //         }
        //     }
        // }
        // if($all_active_modules){
        //     foreach($all_active_modules as $key => $mod)
        //     {
        //         $name = strtolower($mod['extension_folder_name']);
        //         $all_active_modules[$key]['extension_folder_name'] = str_replace(" ","_",$name);
        //     }
        // }
        // //prx($all_active_modules);
        // foreach ($all_active_modules as $key => $value) {
        //         if($value['name'] == 'Payment reminder'){
        //             $module_name = $key;
        //             break;
        //         }
        //     }
        //echo $module_name; die('module_name');
		//$companies = $this->Company_model->get_all_companies(false);
		$companies = $this->Company_model->get_total_companies('payment_reminder' , true);
		
		foreach ($companies as $company)
		{
			$company_id = $company['company_id'];

		    $url = base_url()."cron/send_payment_reminder_cron/".$company_id;
		    
		    $ch = curl_init();
		    curl_setopt($ch, CURLOPT_URL, $url);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		    curl_setopt($ch, CURLOPT_AUTOREFERER, false);
		    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		    curl_setopt($ch, CURLOPT_HEADER, 0);
		    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		    $result = curl_exec($ch);
		    curl_close($ch);

		    echo $company_id. ' => '; prx($result, 1);

		}
	}

	function delete_ota_xml_logs($limit = 1000){
        $this->load->model('Channex_model');

        $ota_xml_logs = $this->Channex_model->get_ota_xml_logs($limit);

        $xml_log_ids = array();

        if($ota_xml_logs){
            foreach($ota_xml_logs as $log) {

                $timestamp = strtotime($log['datetime']); //1373673600

                // getting current date 
                $cDate = strtotime(date('Y-m-d H:i:s'));

                // Getting the value of old date + 7 days
                $old_blank_response_log_date = $timestamp + (86400 * 7);

                // Getting the value of old date + 2 month
                $old_booking_retrieval_log_date = $timestamp + (86400 * 60); // 86400 seconds in 24 hrs 

                // for delete blank response logs
                if(
                	$old_blank_response_log_date < $cDate && 
                	$log['request_type'] != 2  && 
                	strpos($log['xml_out'], '"data":[]')
                )
                {
                    $xml_log_ids[] = $log['xml_log_id'];
                }

                // for delete booking retrieval logs
                if(
                	$old_booking_retrieval_log_date < $cDate && 
                	$log['request_type'] == 2 && 
                	strpos($log['xml_out'], '"data":[]')
                )
                {
                    $xml_log_ids[] = $log['xml_log_id'];
                }
            }

            prx($xml_log_ids);

            $this->Channex_model->delete_old_xml_logs($xml_log_ids);
        }
    }

    function install_extension_vendor(){
    	$extension_name = isset($_GET['extension']) ? $_GET['extension']: "";
    	if($extension_name ==''){
    		echo 'please provide extension name';
    		return;
    	}
    	$this->load->model('Whitelabel_partner_model');
    	$this->load->model('Extension_model');
    	$whitelabel_partners = $this->Whitelabel_partner_model->get_whitelabel_partners();
        foreach ($whitelabel_partners as $key => $value) {
             
           $is_install = $this->Extension_model->get_vendor_extension_status($extension_name,1,$value['id']);
           if(isset($is_install) && $is_install){
           		echo $extension_name." already install for vendor id ".$value['id']."<br>";
           }else{
           		$data = array('extension_name'=>$extension_name,'vendor_id'=>$value['id'],'is_installed'=> 1);
           		$this->Extension_model->add_vendors_extension($data);
           		echo $extension_name." installed for vendor id ".$value['id']."<br>";
           }
        	
        }
    }
    function update_channex_availability(){
    	$start_date = $this->input->get('start_date');
    	$end_date = $this->input->get('end_date');
    	$room_type_id = $this->input->get('room_type_id');
    	$company_id = $this->input->get('company_id');
    	$update_from = $this->input->get('update_from');

        $update_availability_data = array(
                        'start_date' => $start_date,
                        'end_date' => $end_date,
                        'room_type_id' => $room_type_id,
                        'company_id' => $company_id,
                        'update_from' => $update_from
                    );

        $api_url = base_url();
        $method = "cron/channex_availability/".$company_id;
        $headers = array(
                "Content-Type: application/json",
            );

        $response = $this->channex_call_api($api_url, $method, $update_availability_data, $headers);

        echo json_encode(array('resp' => $response));
        
    }

    function siteminder_booking_retrieval($authorization_code = null)
	{	
		if (getenv('CRON_AUTH_SECRET')) {
			if (!$authorization_code || $authorization_code !== getenv('CRON_AUTH_SECRET')) {
				echo 'Not authorized';
				return;
			}
		}
    	
    	$this->get_siteminder_bookings($authorization_code);
	}

	function get_siteminder_bookings($authorization_code)
	{
		if (getenv('CRON_AUTH_SECRET')) {
			if (!$authorization_code || $authorization_code !== getenv('CRON_AUTH_SECRET')) {
				echo 'Not authorized';
				return;
			}
		}

		$this->load->model('Company_model');
		
		$companies = $this->Company_model->get_all_companies(true, 'siteminder');
		
		if($companies) {
			foreach ($companies as $company)
			{
				$company_id = $company['company_id'];

	            $protocol = $this->config->item('server_protocol');
			    // $url = $protocol . $_SERVER['HTTP_HOST']."/cron/channex_get_bookings/".$company_id;

			    $url = base_url()."cron/siteminder_get_bookings/".$company_id;
			    
			    $ch = curl_init();
			    curl_setopt($ch, CURLOPT_URL, $url);
			    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			    curl_setopt($ch, CURLOPT_AUTOREFERER, false);
			    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			    curl_setopt($ch, CURLOPT_HEADER, 0);
			    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			    $result = curl_exec($ch);
			    curl_close($ch);

			    echo $company_id. ' => '; prx($result, 1);

			}
		}
	}

	
}
