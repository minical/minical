<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Cron extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
        // Load Language Translation Helper
        $this->load->helper('language_translation');
	}
	
	function ota_booking_retrieval()
	{	
        $this->get_channex_bookings();
	}

	function get_channex_bookings()
	{
		$this->load->model('Company_model');
		
		$companies = $this->Company_model->get_all_companies();
		
		foreach ($companies as $company)
		{
			$company_id = $company['company_id'];

		    $url = $_SERVER['HTTP_HOST']."/cron/channex_get_bookings/".$company_id;

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

		    prx($result);

		}
	}
}
