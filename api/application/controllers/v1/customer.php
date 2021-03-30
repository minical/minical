<?php defined('BASEPATH') OR exit('No direct script access allowed');

//require APPPATH.'/libraries/REST_Controller.php';

class Customer extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model("Company_model");
        $this->load->model("Booking_model");
        $this->load->model('Booking_log_model');
        $this->load->model("Room_model");
        $this->load->model("Booking_room_history_model");
        $this->load->model("Customer_model");
        $this->load->model("Rate_plan_model");
        $this->load->model("Rate_model");
        $this->load->model('Extra_model');
        $this->load->model('Booking_extra_model');
        $this->load->model("Date_range_model");
        $this->load->model("Currency_model");
        $this->load->model("Charge_type_model");
        $this->load->model("Invoice_model");
        
        $this->load->helper('timezone');
    }

	function index_get()
	{
        $customer_id = $this->get('customer_id');
		$customer = $this->Customer_model->get_customer($customer_id);
        $this->response($customer, 200);
	}

    public function stripe_pubkey_post($company_id =null)
    {
        if(!$company_id){
            $company_id = $this->post('company_id');
        }

        if($company_id){
            $this->load->library('session');// dependency
            $this->load->library('PaymentGateway', array(
                'company_id' => $company_id
            ));

            $public_key = $this->paymentgateway->getStripePublicKey();

            $this->response(compact('public_key'), 200);
        }

        $this->response(array(), 404);

    }
    
    public function get_tokenex_token_and_encrypted_cvc_post($card_number = null, $cvc = null)
    { 
        $selected_gateway = null;
        $company_id = $this->post('company_id');
        if($company_id){
            $this->load->library('session');// dependency
            $this->load->library('PaymentGateway', array(
                'company_id' => $company_id
            ));

            $selected_gateway = $this->paymentgateway->getSelectedGateway();
        }
        
        if($selected_gateway && $this->post('card_number'))
        {
            $data["Data"] = $this->post('card_number');
            $this->load->library('Tokenex');
            $result = $this->tokenex->tokenize($data); // get tokenex token
          
            if(isset($result["token"]) && $result["token"])
            {
                $cvc_encrypted = null;
                
                if($this->post('cvc'))
                    $cvc_encrypted = $this->get_cc_cvc_encrypted($this->post('cvc'), $result["token"]); // get cvc encrypted
                
                if (ob_get_contents()) ob_end_clean();
                
                $this->response(
                        array(
                            "token_ex_token" => $result["token"],
                            "cvc_encrypted"  => $cvc_encrypted
                        ), 
                    200);
            }
            else
               $this->response(null, 200);
        }
       $this->response(array(), 404);
    }
    
    public function get_cc_cvc_encrypted($cvc = null, $token = null)
    {       
        if($cvc && is_numeric($cvc) && $token)
        {
            $this->load->library('Encrypt');
            $cc_cvc_encrypted = $this->encrypt->encode($cvc, $token); // get encoded cvc
            return $cc_cvc_encrypted;
        }
        return null;
    }
}
