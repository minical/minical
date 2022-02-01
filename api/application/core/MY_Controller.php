<?php

/* 
*	Base Controller that takes care of Security & Permission (User Access) of Minical
*/
// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/REST_Controller.php';

class MY_Controller extends REST_Controller {

    public function __construct()
    {
        parent::__construct();
		$this->ci =& get_instance();

        $this->load->model(array('Booking_model'));

        $this->image_url = "https://".getenv("AWS_S3_BUCKET").".s3.amazonaws.com/";
        
		$this->validate_permissions();
	}

	public function validate_permissions()
	{
        $data = $this->input->request_headers();
        $key = $data['X-api-key'];
		$company_id = $this->post('company_id');

        if(empty($company_id)){
            return;
        } else {
            $get_permission = $this->Booking_model->get_company_api_permission($company_id, $key);
            if($get_permission)
                return;
            else
                $this->response(array('status' => false, 'error' => 'Invalid API Key.'), 200); exit;
        }

        
	}

}
