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
	
	function ota_booking_retrieval()
	{	
        $this->get_channex_bookings();
	}

	function get_channex_bookings()
	{
		$this->load->model('Company_model');
		
		$companies = $this->Company_model->get_all_companies(true);
		
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

	function delete_channex_credit_cards()
	{
		$this->load->model('Company_model');
		$this->load->model('Booking_model');
		$this->load->model('Card_model');
		
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

    	$companies = $this->Company_model->get_all_companies(true);
		
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

        // update rates
        do_action('update_rates', $data);
		// }
	}

	function hourly(){

		$data = array();
		do_action('hourly-cron', $data);
	}

	function daily(){

		$data = array();
		do_action('daily-cron', $data);
	}
}
