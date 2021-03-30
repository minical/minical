<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;

class Tokenex {
    
    public $api_key, $tokenex_id, $api_tokenization_url, $api_payment_url, $api_iframe_url, $api_transparent_url, 
            $payu_url, $x_payments_os_env, $api_version; // thses for payu hub

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->library('encrypt');
        $this->ci->load->library('PaymentGateway');
        $this->ci->load->model('Payment_gateway_model');
        $this->ci->load->model('Booking_model');
        $this->ci->load->model('Customer_model');
        $this->ci->load->model('Card_model'); 
        $this->ci->load->model('company_model');          
        $this->api_key = isset($_SERVER["TOKENEX_API_KEY"]) ? $_SERVER["TOKENEX_API_KEY"] : "";
        $this->tokenex_id = isset($_SERVER["TOKENEX_ID"]) ? $_SERVER["TOKENEX_ID"] : "";
        
        $api_prefix = ($this->ci->config->item('app_environment') == "development") ? "test-" : "";
        $this->api_tokenization_url = "https://".$api_prefix."api.tokenex.com/TokenServices.svc/REST/";
        $this->api_payment_url = "https://".$api_prefix."api.tokenex.com/PaymentServices.svc/REST/";
        $this->api_iframe_url = "https://".$api_prefix."htp.tokenex.com/api/v2/";
        $this->api_transparent_url = "https://".$api_prefix."api.tokenex.com/TransparentGatewayAPI/";
        
        // for payu hub
        $this->payu_url = 'https://api.paymentsos.com/';
        $this->x_payments_os_env = ($this->ci->config->item('app_environment') == "development") ? "test" : "live";
        $this->api_version = '1.2.0';

        // for cielo
        $this->cielo_url = ($this->ci->config->item('app_environment') == "development") ? "https://apisandbox.cieloecommerce.cielo.com.br" : "https://api.cieloecommerce.cielo.com.br";
    }
    
    public function tokenize($data){
        $data['TokenScheme']  = TOKEN_SCHEME_TOKEN;
        $response_xml = $this->call_api($this->api_tokenization_url, 'Tokenize', $data);
        $response_obj = $this->xml_to_object($response_xml);
        $response = array();
        if($response_obj->Success == 'true')
        {
            $response['token'] = (string)$response_obj->Token;
        }
        elseif($response_obj->Success == 'false')
        {
            $response['error'] = (string)$response_obj->Error;
        }
        return $response;
    }
    
    public function detokenize($data){
        $response_xml = $this->call_api($this->api_tokenization_url, 'Detokenize', $data);
        $response_obj = $this->xml_to_object($response_xml);
        $response['response_xml'] = $response_xml;
        if($response_obj->Success == 'true')
        {
            $response['data'] = (string)$response_obj->Value;
        }
        elseif($response_obj->Success == 'false')
        {
            $response['error'] = (string)$response_obj->Error;
        }
        return $response;
    }
    
    public function validate_token($data){
        $response_xml = $this->call_api($this->api_tokenization_url, 'ValidateToken', $data);
        $response_obj = $this->xml_to_object($response_xml);
        return $response_obj;
    }
    
    public function xml_to_object($response_xml){
       return simplexml_load_string($response_xml);
    }
    
    public function create_token_for_payu($customer, $app_id, $public_key){
        $tokenize_data = array(
                                "token_type" => "credit_card",
                                "card_number" => '{{{'.$customer['cc_tokenex_token'].'}}}',
                                "expiration_date" => $customer['cc_expiry_month'].'/'.$customer['cc_expiry_year'],
                                "holder_name" => $customer['customer_name']
                                );
        $tokenize_header = array('Content-Type: application/json',
                        'app_id:'.$app_id,
                        'public_key:'.$public_key,
                        'api-version:'.$this->api_version,
                        'x-payments-os-env:'.$this->x_payments_os_env,
                        'TX_TokenExID:'.$this->tokenex_id,
                        'TX_APIKey:'.$this->api_key,
                        'tx_URL:'.$this->payu_url.'tokens'
                        );
        
        $token_response = $this->call_api($this->api_transparent_url, 'Detokenize', $tokenize_data, $tokenize_header, true);
        $token = json_decode($token_response)->token; // got token
        return $token;
    }
    
    public function create_payment_id_for_payu($amount, $customer, $currency, $booking_id, $common_header){
        $payment_data = array(
                                "amount" => $amount,
                                "card_number" => '{{{'.$customer['cc_tokenex_token'].'}}}',
                                "currency" => strtoupper($currency),
                                "billing_address" => array(
                                    "phone" => $customer['phone']
                                ),
                                "order" => array(
                                    "id" => $booking_id
                                )
                            );
        $payment_header = array_merge($common_header, array('tx_URL:'.$this->payu_url.'payments'));
        $payment_response = $this->call_api($this->api_transparent_url, 'Detokenize', $payment_data, $payment_header, true);
        
        $payment_id = json_decode($payment_response)->id; // got payment id
        return $payment_id;
    }
    
    public function make_authorization_for_payu($token, $customer, $common_header, $payment_id)
    {
        $authorize_data = array(
                                "payment_method" => array(
                                    "type" => 'tokenized',
                                    "token" => $token,
                                    "card_number" => '{{{'.$customer['cc_tokenex_token'].'}}}',
                                )
                            );
        $authorize_header = array_merge($common_header, array('tx_URL:'.$this->payu_url.'payments/'.$payment_id.'/authorizations'));
        
        $authorize_response = $this->call_api($this->api_transparent_url, 'Detokenize', $authorize_data, $authorize_header, true);
        $authorize_response = json_decode($authorize_response);
        $authorize_response_result = $authorize_response->result;
        return $authorize_response_result;
    }
    
    public function capture_payment_for_payu($payu_payment)
    {
        $capture_data = array(
                                "card" => array(
                                    "card_number" => '{{{'.$payu_payment['customer']['cc_tokenex_token'].'}}}',
                                )
                            );
        $capture_header = array_merge($payu_payment['header'], array('tx_URL:'.$this->payu_url.'payments/'.$payu_payment['payment_id'].'/captures'));
        
        $capture_response = $this->call_api($this->api_transparent_url, 'Detokenize', $capture_data, $capture_header, true);
        $capture_response = json_decode($capture_response);
        return $capture_response;
    }
    
    public function create_charge_for_payu($token, $customer, $common_header, $payment_id)
    {
        $charges_data = array(
                                "payment_method" => array(
                                    "type" => 'tokenized',
                                    "token" => $token,
                                    "card_number" => '{{{'.$customer['cc_tokenex_token'].'}}}'
                                )
                            );
        $charges_header = array_merge($common_header, array('tx_URL:'.$this->payu_url.'payments/'.$payment_id.'/charges'));
        
        $charges_response = $this->call_api($this->api_transparent_url, 'Detokenize', $charges_data, $charges_header, true);
        $charges_response = json_decode($charges_response);
        $charges_response_result = $charges_response->result;
        return $charges_response_result;
    }
    
    public function refund_payment_for_payu($customer, $common_header, $payment_id)
    {
        $refund_data = array(
                                "card" => array(
                                    "card_number" => '{{{'.$customer['cc_tokenex_token'].'}}}',
                                )
                            );
        $refund_header = array_merge($common_header, array('tx_URL:'.$this->payu_url.'payments/'.$payment_id.'/refunds'));
        
        $refund_response = $this->call_api($this->api_transparent_url, 'Detokenize', $refund_data, $refund_header, true);
        $refund_response = json_decode($refund_response);
        return $refund_response;
    }

    public function make_payment_by_payuhub($gateway, $amount, $currency, $customer_id, $booking_id = null, $cvc = null, $auth_id = null, $payment_capture = false)
    {
        $currency_json = CURRENCY_JSON;
        $currency_array = json_decode($currency_json, true);
        foreach($currency_array as $cc => $c)
        {
            if(strtoupper($currency) == $c['currency'])
            {
                $minor_unit = $c['minor_unit'];
                break;
            }
        }
        
        if($amount/100 < 20 && $minor_unit == 2)
        {
            return array("success" => false, "message" => "Amount must be equal OR more than 20");
        }
        elseif($amount/1000 < 200 && $minor_unit == 3)
        {
            return array("success" => false, "message" => "Amount must be equal OR more than 200");
        }
        elseif($amount/10000 < 2000 && $minor_unit == 4)
        {
            return array("success" => false, "message" => "Amount must be equal OR more than 2000");
        }
        
        $idempotency_key = 'MINICAL'.strtotime(date('Y-m-d H:i:s'));
        
        $customer = $this->ci->Customer_model->get_customer_info($customer_id);
        
        unset($customer['cc_number']);
        unset($customer['cc_expiry_month']);
        unset($customer['cc_expiry_year']);
        unset($customer['cc_tokenex_token']);
        unset($customer['cc_cvc_encrypted']);
        $card_data = $this->ci->Card_model->get_active_card($customer_id, $this->ci->company_id);

        if(isset($card_data) && $card_data){
            $customer['cc_number'] = $card_data['cc_number'];
            $customer['cc_expiry_month'] = $card_data['cc_expiry_month'];
            $customer['cc_expiry_year'] = $card_data['cc_expiry_year'];
            $customer['cc_tokenex_token'] = $card_data['cc_tokenex_token'];
            $customer['cc_cvc_encrypted'] = $card_data['cc_cvc_encrypted'];
        }
        $payment_gateway = $payment_gateway_settings_key = $login = $password = $login_key = $password_key = null;
        $payment_gateway = "PayUGateway"; 
        $payment_gateway_settings_key = "payment_gateway";
        $login_key = "gateway_login";
        $password_key = "gateway_password";
        
        $payment_gateway_settings = $this->ci->paymentgateway->getGatewayCredentials();
        $app_id = $payment_gateway_settings[$payment_gateway_settings_key]['gateway_app_id'];
        $public_key = $payment_gateway_settings[$payment_gateway_settings_key]['gateway_public_key'];
        $private_key = $payment_gateway_settings[$payment_gateway_settings_key]['gateway_private_key'];
        
        if($payment_gateway_settings['selected_payment_gateway'] != $gateway)
        {
            return array("success" => false, "message" => "Invalid gateway settings!");
        }
        
        $login = trim($payment_gateway_settings[$payment_gateway_settings_key][$login_key]);
        
        $cvc = $this->ci->encrypt->decode($customer['cc_cvc_encrypted'], $customer['cc_tokenex_token']);
        
        if(!$customer['cc_tokenex_token'])
        {
            return array("success" => false, "message" => "Invalid card details!");
        }
        if(!$login || !$payment_gateway)
        {
            return array("success" => false, "message" => "Invalid gateway settings!");
        }
        
        $common_header = array('Content-Type: application/json',
                                'app_id:'.$app_id,
                                'private_key:'.$private_key,
                                'api-version:'.$this->api_version,
                                'x-payments-os-env:'.$this->x_payments_os_env,
                                'idempotency_key:'.$idempotency_key,
                                'TX_TokenExID:'.$this->tokenex_id,
                                'TX_APIKey:'.$this->api_key,
                            );
        
        // create token
        if(!$payment_capture)
        {
            $token = $this->create_token_for_payu($customer, $app_id, $public_key);

            // make payment
            $payment_id = $this->create_payment_id_for_payu($amount, $customer, $currency, $booking_id, $common_header);

            // for authorization
            $authorize_response_result = $this->make_authorization_for_payu($token, $customer, $common_header, $payment_id);
            if($authorize_response_result->status == 'Failed')
            {
                return array("success" => false, "message" => $authorize_response_result->description);
            }
        }
        // for capture
        
        if($payment_capture)
        {
            $payu_payment = array(
                                'customer' => $customer,
                                'header' => $common_header,
                                'payment_id' => $auth_id
                            );
            $capture_data = $this->_payment_capture(null, null, null, null, null, null, null, null, null, null, $cvc, $customer_id, $payu_payment, 'payuhub');
            return array("success" => true, "charge_id" => $capture_data['charge_id']);
        }
        else
        {
            $payu_payment = array(
                                'customer' => $customer,
                                'header' => $common_header,
                                'payment_id' => $payment_id
                            );
            $company_data = $this->ci->Company_model->get_company( $this->ci->company_id);  
            
            if(!$company_data['manual_payment_capture']){
                
                $capture_data = $this->_payment_capture(null, null, null, null, null, null, null, null, null, null, $cvc, $customer_id, $payu_payment, 'payuhub');
                return array("success" => true, "charge_id" => $capture_data['charge_id']);
            }
            else
            {
                return array("success" => true, "authorization" => $payment_id);
            }
        }
    }
        
    public function refund_payment_by_payuhub($gateway, $currency, $charge_id, $amount, $booking_id = null, $customer_id = null, $credit_card_id = null){
        
        $currency_json = CURRENCY_JSON;
        $currency_array = json_decode($currency_json, true);
        foreach($currency_array as $cc => $c)
        {
            if(strtoupper($currency) == $c['currency'])
            {
                $minor_unit = $c['minor_unit'];
                break;
            }
        }
        
        if($amount/100 < 20 && $minor_unit == 2)
        {
            return array("success" => false, "message" => "Amount must be equal OR more than 20");
        }
        elseif($amount/1000 < 200 && $minor_unit == 3)
        {
            return array("success" => false, "message" => "Amount must be equal OR more than 200");
        }
        elseif($amount/10000 < 2000 && $minor_unit == 4)
        {
            return array("success" => false, "message" => "Amount must be equal OR more than 2000");
        }
        $idempotency_key = 'MINICAL'.strtotime(date('Y-m-d H:i:s'));
        
        $payment_gateway = $payment_gateway_settings_key = $login = $password = $login_key = $password_key = null;
        $payment_gateway = "PayUGateway"; 
        $payment_gateway_settings_key = "payment_gateway";
        $login_key = "gateway_login";
        $password_key = "gateway_password";
                
        $payment_gateway_settings = $this->ci->paymentgateway->getGatewayCredentials();
        $app_id = $payment_gateway_settings[$payment_gateway_settings_key]['gateway_app_id'];
        $public_key = $payment_gateway_settings[$payment_gateway_settings_key]['gateway_public_key'];
        $private_key = $payment_gateway_settings[$payment_gateway_settings_key]['gateway_private_key'];
        
        if($payment_gateway_settings['selected_payment_gateway'] != $gateway)
        {
            return array("success" => false, "message" => "Invalid gateway settings!");
        }
        
        $login = trim($payment_gateway_settings[$payment_gateway_settings_key][$login_key]);
        
        if(!$login || !$payment_gateway)
        {
            return array("success" => false, "message" => "Invalid gateway settings!");
        }
        
        $customer = $this->ci->Customer_model->get_customer_info($customer_id);
        
        unset($customer['cc_number']);
        unset($customer['cc_expiry_month']);
        unset($customer['cc_expiry_year']);
        unset($customer['cc_tokenex_token']);
        unset($customer['cc_cvc_encrypted']);
        $card_data = $this->ci->Card_model->get_active_card($customer_id, $this->ci->company_id);

        if(isset($card_data) && $card_data){
            $customer['cc_number'] = $card_data['cc_number'];
            $customer['cc_expiry_month'] = $card_data['cc_expiry_month'];
            $customer['cc_expiry_year'] = $card_data['cc_expiry_year'];
            $customer['cc_tokenex_token'] = $card_data['cc_tokenex_token'];
            $customer['cc_cvc_encrypted'] = $card_data['cc_cvc_encrypted'];
        }
        
        $common_header = array('Content-Type: application/json',
                                'app_id:'.$app_id,
                                'private_key:'.$private_key,
                                'api-version:'.$this->api_version,
                                'x-payments-os-env:'.$this->x_payments_os_env,
                                'idempotency_key:'.$idempotency_key,
                                'TX_TokenExID:'.$this->tokenex_id,
                                'TX_APIKey:'.$this->api_key,
                            );
        
        // create token
        $token = $this->create_token_for_payu($customer, $app_id, $public_key);

        // make payment
        $payment_id = $this->create_payment_id_for_payu($amount, $customer, $currency, $booking_id, $common_header);
        
        // for charges
        $charges_response_result = $this->create_charge_for_payu($token, $customer, $common_header, $payment_id);
        if($charges_response_result->status == 'Failed')
        {
            return array("success" => false, "message" => $charges_response_result->description);
        }
        
        // for refund
        $refund_response = $this->refund_payment_for_payu($customer, $common_header, $payment_id);
        $refund_response_result = $refund_response->result;
        if($refund_response_result->status == 'Failed')
        {
            return array("success" => false, "message" => $refund_response_result->description);
        }
        
        $refund_id = $refund_response->id;
        return array("success" => true, "refund_id" => $refund_id);
    }

    public function make_payment_by_cielo($gateway, $amount, $currency, $customer_id, $booking_id = null, $cvc = null, $auth_id = null, $payment_capture = false, $is_capture = null)
    {
        $customer = $this->ci->Customer_model->get_customer_info($customer_id);

        unset($customer['cc_number']);
        unset($customer['cc_expiry_month']);
        unset($customer['cc_expiry_year']);
        unset($customer['cc_tokenex_token']);
        unset($customer['cc_cvc_encrypted']);
        $card_data = $this->ci->Card_model->get_active_card($customer_id, $this->ci->company_id);

        if(isset($card_data) && $card_data){
            $customer['cc_number'] = $card_data['cc_number'];
            $customer['cc_expiry_month'] = $card_data['cc_expiry_month'];
            $customer['cc_expiry_year'] = $card_data['cc_expiry_year'];
            $customer['cc_tokenex_token'] = $card_data['cc_tokenex_token'];
            $customer['cc_cvc_encrypted'] = $card_data['cc_cvc_encrypted'];
        }
        $payment_gateway = $payment_gateway_settings_key = $login = $password = $login_key = $password_key = null;
        $payment_gateway = "CieloGateway"; 
        $payment_gateway_settings_key = "payment_gateway";
        
        $payment_gateway_settings = $this->ci->paymentgateway->getGatewayCredentials();
        $merchant_id = $payment_gateway_settings[$payment_gateway_settings_key]['gateway_merchant_id'];
        $merchant_key = $payment_gateway_settings[$payment_gateway_settings_key]['gateway_merchant_key'];
        
        if($payment_gateway_settings['selected_payment_gateway'] != $gateway)
        {
            return array("success" => false, "message" => "Invalid gateway settings!");
        }

        $cvc = $this->ci->encrypt->decode($customer['cc_cvc_encrypted'], $customer['cc_tokenex_token']);
        
        if(!$customer['cc_tokenex_token'])
        {
            return array("success" => false, "message" => "Invalid card details!");
        }
        if(!$payment_gateway)
        {
            return array("success" => false, "message" => "Invalid gateway settings!");
        }
        $payment_id = null;
        if(!$payment_capture) {
            $auth_response = $this->make_authorization_for_cielo($customer, $merchant_id, $merchant_key, $booking_id, $amount);

            if(isset($auth_response) && $auth_response['success'])
            {
                $payment_id = $auth_response['payment_id'];
            } else if(isset($auth_response) && !$auth_response['success'])
            {
                return array("success" => false, "message" => $auth_response['message']);
            }
        }
        
        if($payment_capture)
        {
            $charge_id = $this->payment_capture_for_cielo($customer, $merchant_id, $merchant_key, $booking_id, $amount, $auth_id);
            return array("success" => true, "charge_id" => $auth_id);
        } else {

            if($is_capture){

                $capture_response = $this->payment_capture_for_cielo($customer, $merchant_id, $merchant_key, $booking_id, $amount, $payment_id);

                if(isset($capture_response) && $capture_response['success'])
                {
                    $charge_id = $capture_response['charge_id'];
                    return array("success" => true, "charge_id" => $charge_id.'='.$payment_id);
                } else if(isset($capture_response) && !$capture_response['success'])
                {
                    return array("success" => false, "message" => $capture_response['message']);
                }
            } else {
                return array("success" => true, "authorization" => $payment_id);
            }
        }
    }

    public function make_authorization_for_cielo($customer, $merchant_id, $merchant_key, $booking_id, $amount){

        if (isset($_GET['dev_mode'])) {
            echo "customer";print_r($customer);
        }
        $card_data = array(
                            "Token" => $customer['cc_tokenex_token']
                        );
        $card_number_data = $this->detokenize($card_data);
        if (isset($_GET['dev_mode'])) {
            echo "card_number_data";print_r($card_number_data);
        }
        $card_type = $this->_get_credit_card_type($card_number_data['data']);
        if (isset($_GET['dev_mode'])) {
            echo "card_type";print_r($card_type);
        }
        
        $authorize_header = array('Content-Type: application/json',
                                'MerchantId:'.$merchant_id,
                                'MerchantKey:'.$merchant_key,
                                'TX_TokenExID:'.$this->tokenex_id,
                                'TX_APIKey:'.$this->api_key,
                                'tx_URL:'.$this->cielo_url.'/1/sales'
                            );

        $authorize_data = array(
                "MerchantOrderId" => $booking_id,
                "Customer" => array(
                        "Name" => $customer['customer_name']
                    ),
                    "Payment" => array(
                        "Type" => "CreditCard",
                        "Amount" => $amount,
                        "Installments" => 1,
                        "CreditCard" => array(
                            "CardNumber" => '{{{'.$customer['cc_tokenex_token'].'}}}',
                            "ExpirationDate" => $customer['cc_expiry_month'].'/'."20".$customer['cc_expiry_year'],
                            "Brand" => $card_type

                                       ),
                    )
                );
        
        $authorize_response = $this->call_api($this->api_transparent_url, 'Detokenize', $authorize_data, $authorize_header, true);
        if (isset($_GET['dev_mode'])) {
            echo "authorize_response";print_r($authorize_response);
        }

        $authorize_response = json_decode($authorize_response);
        if(
            isset($authorize_response) && isset($authorize_response->Payment) &&
            isset($authorize_response->Payment->ReturnCode) &&
            ($authorize_response->Payment->ReturnCode == 4 || $authorize_response->Payment->ReturnCode == "00" || $authorize_response->Payment->ReturnCode == 0) &&
            isset($authorize_response->Payment->PaymentId) && $authorize_response->Payment->PaymentId
        ) {

            $payment_id = $authorize_response->Payment->PaymentId; // got payment_id
            return array("success" => true, "payment_id" => $payment_id);

        } else if(isset($authorize_response) && isset($authorize_response->Payment) && isset($authorize_response->Payment->ReturnCode) && $authorize_response->Payment->ReturnCode && $authorize_response->Payment->ReturnCode != 4) {

            return array("success" => false, "message" => $authorize_response->Payment->ReturnMessage);
        } elseif(isset($authorize_response) && isset($authorize_response[0]) && isset($authorize_response[0]->Message) && $authorize_response[0]->Message) {
            return array("success" => false, "message" => $authorize_response[0]->Message);
        }
    }

    public function payment_capture_for_cielo($customer, $merchant_id, $merchant_key, $booking_id, $amount, $payment_id){

        $capture_header = array('Content-Type: application/json',
                                'MerchantId:'.$merchant_id,
                                'MerchantKey:'.$merchant_key
                            );

        $capture_data = array();

        $capture_response = $this->call_api($this->cielo_url, '/1/sales/'.$payment_id.'/capture', $capture_data, $capture_header, true, 'put');

        $capture_response = json_decode($capture_response);

        if(isset($capture_response) && isset($capture_response->ReturnCode) && $capture_response->ReturnCode && $capture_response->ReturnCode == 6) {

            $charge_id = $capture_response->Tid; // got charge_id // got payment_id
            return array("success" => true, "charge_id" => $charge_id);

        } else if(isset($capture_response) && isset($capture_response->ReturnCode) && $capture_response->ReturnCode && $capture_response->ReturnCode == 6) {

            return array("success" => false, "message" => $capture_response->ReturnMessage);
        }
    }

    public function refund_payment_by_cielo($gateway, $charge_id, $amount, $booking_id = null, $customer_id = null){

        $customer = $this->ci->Customer_model->get_customer_info($customer_id);

        unset($customer['cc_number']);
        unset($customer['cc_expiry_month']);
        unset($customer['cc_expiry_year']);
        unset($customer['cc_tokenex_token']);
        unset($customer['cc_cvc_encrypted']);
        $card_data = $this->ci->Card_model->get_active_card($customer_id, $this->ci->company_id);

        if(isset($card_data) && $card_data){
            $customer['cc_number'] = $card_data['cc_number'];
            $customer['cc_expiry_month'] = $card_data['cc_expiry_month'];
            $customer['cc_expiry_year'] = $card_data['cc_expiry_year'];
            $customer['cc_tokenex_token'] = $card_data['cc_tokenex_token'];
            $customer['cc_cvc_encrypted'] = $card_data['cc_cvc_encrypted'];
        }
        $payment_gateway = $payment_gateway_settings_key = $login = $password = $login_key = $password_key = null;
        $payment_gateway = "CieloGateway"; 
        $payment_gateway_settings_key = "payment_gateway";
        
        $payment_gateway_settings = $this->ci->paymentgateway->getGatewayCredentials();
        $merchant_id = $payment_gateway_settings[$payment_gateway_settings_key]['gateway_merchant_id'];
        $merchant_key = $payment_gateway_settings[$payment_gateway_settings_key]['gateway_merchant_key'];
        
        if($payment_gateway_settings['selected_payment_gateway'] != $gateway)
        {
            return array("success" => false, "message" => "Invalid gateway settings!");
        }

        $cvc = $this->ci->encrypt->decode($customer['cc_cvc_encrypted'], $customer['cc_tokenex_token']);
        
        if(!$customer['cc_tokenex_token'])
        {
            return array("success" => false, "message" => "Invalid card details!");
        }
        if(!$payment_gateway)
        {
            return array("success" => false, "message" => "Invalid gateway settings!");
        }

        $refund_response = $this->refund_payment_for_cielo($customer, $merchant_id, $merchant_key, $booking_id, $amount, $charge_id);

        if ($refund_response['success']) {
            return array("success" => true, "refund_id" => $refund_response['refund_id']);
        }

        return array("success" => false, "message" => $refund_response['message']);
    }

    public function refund_payment_for_cielo($customer, $merchant_id, $merchant_key, $booking_id, $amount, $payment_id){
        
        $refund_header = array('Content-Type: application/json',
                                'MerchantId:'.$merchant_id,
                                'MerchantKey:'.$merchant_key
                            );

        $refund_data = array();

        $refund_response = $this->call_api($this->cielo_url, '/1/sales/'.$payment_id.'/void?amount='.$amount, $refund_data, $refund_header, true, 'put');
        if (isset($_GET['dev_mode'])) {
            echo "refund_response";print_r($refund_response);
        }
        $refund_response = json_decode($refund_response);

        if(
            isset($refund_response) && isset($refund_response->ReturnCode) &&
            ($refund_response->ReturnCode == 0 || $refund_response->ReturnCode == 6 || $refund_response->ReturnCode == 9) &&
            isset($refund_response->Tid) && $refund_response->Tid
        ) {

            $refund_id = $refund_response->Tid; // got refund_id
            return array("success" => true, "refund_id" => $refund_id);
        }
        else if(isset($refund_response[0]) && isset($refund_response[0]->Code) && $refund_response[0]->Code == '312'){
            return array("success" => false, "message" => $refund_response[0]->Message.' (Transaction does not allow cancellation after 24 hours)');
        }
        else if(isset($refund_response[0]) && $refund_response[0]->Message){
            return array("success" => false, "message" => $refund_response[0]->Message);
        }
    }
    
    public function make_payment($gateway, $amount, $currency, $customer_id, $booking_id = null, $cvc = null, $auth_id = null, $payment_capture = false, $is_capture){
        
        $customer = $this->ci->Customer_model->get_customer_info($customer_id);
        unset($customer['cc_number']);
        unset($customer['cc_expiry_month']);
        unset($customer['cc_expiry_year']);
        unset($customer['cc_tokenex_token']);
        unset($customer['cc_cvc_encrypted']);
        $card_data = $this->ci->Card_model->get_active_card($customer_id, $this->ci->company_id);
        
        if(isset($card_data) && $card_data){
            $customer['cc_number'] = $card_data['cc_number'];
            $customer['cc_expiry_month'] = $card_data['cc_expiry_month'];
            $customer['cc_expiry_year'] = $card_data['cc_expiry_year'];
            $customer['cc_tokenex_token'] = $card_data['cc_tokenex_token'];
            $customer['cc_cvc_encrypted'] = $card_data['cc_cvc_encrypted'];
        }

        $token_data = array(
                                "APIKey" => $this->api_key,
                                "TokenExID" => $this->tokenex_id,
                                "Token" => $customer['cc_tokenex_token']
                            );
        $token_response = $this->validate_token($token_data);

        if((string) $token_response->Success == 'true' && (string) $token_response->Valid == 'true')
        {
            if($gateway == "PayuGateway"){
                return $this->make_payment_by_payuhub($gateway, $amount, $currency, $customer_id, $booking_id, $cvc, $auth_id, $payment_capture);
            }

            if($gateway == "CieloGateway"){
                return $this->make_payment_by_cielo($gateway, $amount, $currency, $customer_id, $booking_id, $cvc, $auth_id, $payment_capture, $is_capture);
            }
            
            $payment_gateway = $payment_gateway_settings_key = $login = $password = $login_key = $password_key = $gateway_division_id = $gateway_ssl_cert = $gateway_ssl_key = null;
            switch($gateway){
                case "stripe": 
                    $payment_gateway = "StripeGateway"; 
                    $payment_gateway_settings_key = "stripe";
                    $login_key = "stripe_secret_key";
                    $password_key = null;
                    break;
                case "PayflowGateway": 
                    $payment_gateway = "PayflowGateway"; 
                    $payment_gateway_settings_key = "payment_gateway";
                    $login_key = "gateway_login";
                    $password_key = "gateway_password";
                    break;
                case "FirstdataE4Gateway": 
                    $payment_gateway = "FirstdataE4Gateway"; 
                    $payment_gateway_settings_key = "payment_gateway";
                    $login_key = "gateway_login";
                    $password_key = "gateway_password";
                    break;
                case "ChaseNetConnectGateway": 
                    $payment_gateway = "ChaseNetConnectGateway"; 
                    $payment_gateway_settings_key = "payment_gateway";
                    $login_key = "gateway_login";
                    $password_key = "gateway_password";
                    $chase_gateway_mid = "gateway_mid";
                    $chase_gateway_tid = "gateway_tid";
                    $chase_gateway_cid = "gateway_cid";
                    break;
                case "AuthorizeNetGateway": 
                    $payment_gateway = "AuthorizeNetGateway"; 
                    $payment_gateway_settings_key = "payment_gateway";
                    $login_key = "gateway_login";
                    $password_key = "gateway_password";
                    break;
                case "QuickbooksGateway": 
                    $payment_gateway = "QuickbooksGateway"; 
                    $payment_gateway_settings_key = "payment_gateway";
                    $quick_gateway_refresh_token = "gateway_refresh_token";
                    $quick_gateway_realm_id = "gateway_realm_id";
                    $quick_refresh_token_created_date = "refresh_token_created_date";
                    break;
                case "ElavonGateway": 
                    $payment_gateway = "ElavonGateway"; 
                    $payment_gateway_settings_key = "payment_gateway";
                    $login_key = "gateway_login";
                    $elavon_user = "gateway_user";
                    $password_key = "gateway_password";
                    break;
                case "MonerisGateway": 
                    $payment_gateway = "MonerisGateway"; 
                    $payment_gateway_settings_key = "payment_gateway";
                    $login_key = "gateway_login";
                    $password_key = "gateway_password";
                    break;
                default: 
                    return "gateway_not_supported"; 
                    break;
            }
            
            $payment_gateway_settings = $this->ci->paymentgateway->getGatewayCredentials();
            
            if($payment_gateway_settings['selected_payment_gateway'] != $gateway)
            {
                array("success" => false, "message" => "Invalid gateway settings!");
            }
            $gateway_mid = '';
            $gateway_tid = '';
            $gateway_cid = '';
            $gateway_user = '';
            $gateway_refresh_token = $gateway_realm_id = '';
            $accessTokenObj = $gateway_access_token = $gateway_refresh_token_created_date = '';

            if($payment_gateway != 'QuickbooksGateway')
                $login = trim($payment_gateway_settings[$payment_gateway_settings_key][$login_key]);
            
            if($payment_gateway == 'ChaseNetConnectGateway')
            {
                $gateway_mid = trim($payment_gateway_settings[$payment_gateway_settings_key][$chase_gateway_mid]);
                $gateway_tid = trim($payment_gateway_settings[$payment_gateway_settings_key][$chase_gateway_tid]);
                $gateway_cid = trim($payment_gateway_settings[$payment_gateway_settings_key][$chase_gateway_cid]);
            }

            if($payment_gateway == 'ElavonGateway')
            {
                $gateway_user = trim($payment_gateway_settings[$payment_gateway_settings_key][$elavon_user]);
            }

            if($payment_gateway == 'QuickbooksGateway')
            {
                $gateway_client_id = $this->ci->config->item('quickbooks_gateway_client_id');
                $gateway_client_secret = $this->ci->config->item('quickbooks_gateway_client_secret');
                                
                $gateway_refresh_token = trim($payment_gateway_settings[$payment_gateway_settings_key][$quick_gateway_refresh_token]);
                $gateway_realm_id = trim($payment_gateway_settings[$payment_gateway_settings_key][$quick_gateway_realm_id]);
                
                $gateway_refresh_token_created_date = trim($payment_gateway_settings[$payment_gateway_settings_key][$quick_refresh_token_created_date]);

                if (strtotime($gateway_refresh_token_created_date. '+90 days') < strtotime(date('Y-m-d'))) {
                    return array('success' => false, 'expire' => true, 'message' => "QuickBooks payments authorizations has been expired, please re-authorize your Quickbooks Payments. Click 'OK' to re-authorize.");
                }

                $gateway_access_token = $this->_get_quickbook_access_token(
                                                    $gateway_client_id,
                                                    $gateway_client_secret,
                                                    $gateway_refresh_token,
                                                    $gateway_realm_id
                                                );
                
            }
            
            if($password_key && $payment_gateway != 'QuickbooksGateway'){
                $password = trim($payment_gateway_settings[$payment_gateway_settings_key][$password_key]);
            }
            
            $cvc = $this->ci->encrypt->decode($customer['cc_cvc_encrypted'], $customer['cc_tokenex_token']);
            
            $first_name = $customer['customer_name'];        
            $last_name = "(BID:$booking_id,CID:$customer_id)";
            
            if(!$customer['cc_tokenex_token'])
            {
                return array("success" => false, "message" => "Invalid card details!");
            }
            if((!$login || !$payment_gateway) && $payment_gateway != 'QuickbooksGateway')
            {
                return array("success" => false, "message" => "Invalid gateway settings!");
            }
            $gateway_credentials = array(
                        "name" => $payment_gateway,
                        "login" => $login
                    );
            if($password){
                $gateway_credentials['password'] = $password;
            }

            if($payment_gateway == 'QuickbooksGateway')
            {
                $gateway_credentials['cid'] = $gateway_client_id;
                $gateway_credentials['password'] = $gateway_client_secret;

                unset($gateway_credentials['login']);
            }

            if($payment_gateway == 'ElavonGateway')
            {
                $gateway_credentials['user'] = $gateway_user;
            }

            if($payment_gateway == 'MonerisGateway')
            {
                $gateway_credentials['avs_enabled'] = true;
            }

            $authorize = array(
                "TransactionType" => TOKENEX_PAYMENT_AUTHORIZE,
                "TransactionRequest" => array(
                    "gateway" => $gateway_credentials,
                    "credit_card" => array(
                        "first_name" => $first_name,
                        "last_name" => $last_name,
                        "number" => $customer['cc_tokenex_token'],
                        "month" => $customer['cc_expiry_month'],
                        "year" => "20".$customer['cc_expiry_year']
                    ),
                    "transaction" => array(
                        "amount" => $amount,
                        "currency" => strtoupper($currency),
                        "order_id" => $booking_id,
                        "billing_address" => array(
                            "address1" => $customer['address'],
                            "city" => $customer['city'],
                            "state" => $customer['region'],
                            "zip" => $customer['postal_code']
                        )
                    )
                )
            );

            if($payment_gateway == 'MonerisGateway')
            {
                $authorize['TransactionRequest']['transaction']['order_id'] = $booking_id.'_'.rand(1000000,9999999);
            }

            if($payment_gateway == 'ChaseNetConnectGateway')
            {
                $authorize['TransactionRequest']['gateway']['mid'] = $gateway_mid;
                $authorize['TransactionRequest']['gateway']['tid'] = $gateway_tid;
                $authorize['TransactionRequest']['gateway']['cid'] = $gateway_cid;
                $authorize['TransactionRequest']['transaction']['order_id'] = $booking_id;
            }
            if($payment_gateway == 'QuickbooksGateway')
            {
                $authorize['TransactionRequest']['gateway']['merchant_id'] = $gateway_access_token;
                $authorize['TransactionRequest']['gateway']['merchantpin'] = $gateway_refresh_token;
            }
            if($cvc)
            {
                $authorize['TransactionRequest']['credit_card']['verification_value'] = $cvc;
            }

            $authorization = null;
            
            if($payment_capture)
            {
                $capture_data = $this->_payment_capture($gateway_credentials, $auth_id, $amount, $currency, $payment_gateway, $gateway_mid, $gateway_tid, $gateway_cid, $booking_id, $authorize, $cvc, $customer_id, null, null, $gateway_access_token, $gateway_refresh_token);
                return array("success" => true, "charge_id" => $capture_data['charge_id']);
            }
            else
            {
                $data = $this->_process_transaction_tokenex($authorize);
                if(isset($data['error']))
                {
                    return array("success" => false, "message" => $data['error']);
                }
                $authorization = $data['data'];
                if($is_capture)
                {
                    $capture_data = $this->_payment_capture($gateway_credentials, $authorization, $amount, $currency, $payment_gateway, $gateway_mid, $gateway_tid, $gateway_cid, $booking_id, $authorize, $cvc, $customer_id);
                    return array("success" => true, "charge_id" => $capture_data['charge_id']);
                }
                else
                {
                    return array("success" => true, "authorization" => $authorization);
                }
            }
        }
        else
        {
            $this->ci->load->library('Email');
            $content = "CC token is missing in Tokenex vault. Details:-
                        Booking ID - ".$booking_id."<br/>
                        Customer ID - ".$customer_id."<br/>
                        CC Token - ".$customer['cc_tokenex_token'];

            $config['mailtype'] = 'html';
            $this->ci->email->initialize($config);

            $whitelabelinfo = $this->ci->session->userdata('white_label_information');

            $from_email = isset($whitelabelinfo['support_email']) && $whitelabelinfo['support_email'] ? $whitelabelinfo['support_email'] : 'support@minical.io';
            
            $to_email = isset($whitelabelinfo['support_email']) && $whitelabelinfo['support_email'] ? $whitelabelinfo['support_email'] : 'support@minical.io';

            $this->ci->email->from($from_email);
            $this->ci->email->to($to_email);
            $this->ci->email->subject("CC token is missing in Tokenex vault.");
            $this->ci->email->message($content);

            $this->ci->email->send();

            return array("success" => false, "message" => "The CC Token has been deleted from tokenex, Please re-add another card details.");
        }
    }
    
    public function _payment_capture($gateway_credentials, $authorization, $amount, $currency, $payment_gateway, $gateway_mid, $gateway_tid, $gateway_cid, $booking_id, $authorize, $cvc, $customer_id, $payu_payment = null, $payment_for = null, $gateway_access_token = null, $gateway_refresh_token = null)
    {       
        if($payment_for != null && $payment_for = 'payuhub')
        {
            $capture_response = $this->capture_payment_for_payu($payu_payment);
            $capture_response_result = $capture_response->result;
            if($capture_response_result->status == 'Failed')
            {
                return array("success" => false, "message" => $capture_response_result->description);
            }
            
            $charge_id = $capture_response->id;
            if($charge_id && $cvc && $customer_id)
            {
                // unset cvc after first time payment
                $this->ci->Customer_model->update_customer($customer_id, array('cc_cvc_encrypted' => '')); 
            }
            return array("success" => true, "charge_id" => $charge_id);
        }
        else
        {
            if($payment_gateway == 'MonerisGateway')
            {
                unset($gateway_credentials['avs_enabled']);
            }

            $capture = array(
                "TransactionType" => TOKENEX_PAYMENT_CAPTURE,
                "TransactionRequest" => array(
                    "gateway" => $gateway_credentials,
                    "transaction" => array(
                        "authorization" => $authorization,
                        "amount" => $amount,
                        "currency" => strtoupper($currency)
                    )
                )
            );

            if($payment_gateway == 'ChaseNetConnectGateway')
            {
                $capture['TransactionRequest']['gateway']['mid'] = $gateway_mid;
                $capture['TransactionRequest']['gateway']['tid'] = $gateway_tid;
                $capture['TransactionRequest']['gateway']['cid'] = $gateway_cid;                        
                $capture['TransactionRequest']['credit_card'] = $authorize['TransactionRequest']['credit_card'];
                $capture['TransactionRequest']['transaction']['order_id'] = $booking_id;
                $capture['TransactionRequest']['transaction']["billing_address"] = $authorize['TransactionRequest']["transaction"]["billing_address"];
            }

            if($payment_gateway == 'QuickbooksGateway')
            {
                $capture['TransactionRequest']['gateway']['merchant_id'] = $authorize['TransactionRequest']['gateway']['merchant_id'];
                $capture['TransactionRequest']['gateway']['merchantpin'] = $authorize['TransactionRequest']['gateway']['merchantpin'];
            }

            if($payment_gateway == 'ElavonGateway')
            {
                $capture['TransactionRequest']['gateway']['user'] = $authorize['TransactionRequest']['gateway']['user'];
            }
            $response = $this->_process_transaction_tokenex($capture);  
            if(isset($response['error']))
            {
                return array("success" => false, "message" => $response['error']);
            }
            $charge_id=$response['data'];
            if($charge_id && $cvc && $customer_id)
            {
                // unset cvc after first time payment
                $this->ci->Customer_model->update_customer($customer_id, array('cc_cvc_encrypted' => '')); 
            }
            return array("success" => true, "charge_id" => $charge_id);
        }
    }
    
    public function refund_payment($gateway, $charge_id, $amount, $currency, $booking_id = null, $credit_card_id = null){
        
        $payment_gateway = $payment_gateway_settings_key = $login = $password = $login_key = $password_key = null;
        switch($gateway){
            case "stripe": 
                $payment_gateway = "StripeGateway"; 
                $payment_gateway_settings_key = "stripe";
                $login_key = "stripe_secret_key";
                $password_key = null;
                break;
            case "PayfloywGateway": 
                $payment_gateway = "PayflowGateway"; 
                $payment_gateway_settings_key = "payment_gateway";
                $login_key = "gateway_login";
                $password_key = "gateway_password";
                break;
            case "ChaseNetConnectGateway": 
                $payment_gateway = "ChaseNetConnectGateway"; 
                $payment_gateway_settings_key = "payment_gateway";
                $login_key = "gateway_login";
                $password_key = "gateway_password";
                $chase_gateway_mid = "gateway_mid";
                $chase_gateway_tid = "gateway_tid";
                $chase_gateway_cid = "gateway_cid";
                break;
            case "FirstdataE4Gateway":
                $payment_gateway = "FirstdataE4Gateway"; 
                $payment_gateway_settings_key = "payment_gateway";
                $login_key = "gateway_login";
                $password_key = "gateway_password";
                break;
            case "AuthorizeNetGateway": 
                $payment_gateway = "AuthorizeNetGateway"; 
                $payment_gateway_settings_key = "payment_gateway";
                $login_key = "gateway_login";
                $password_key = "gateway_password";
                break;
            case "QuickbooksGateway": 
                $payment_gateway = "QuickbooksGateway"; 
                $payment_gateway_settings_key = "payment_gateway";
                $quick_gateway_refresh_token = "gateway_refresh_token";
                $quick_gateway_realm_id = "gateway_realm_id";
                break;
            case "ElavonGateway": 
                $payment_gateway = "ElavonGateway"; 
                $payment_gateway_settings_key = "payment_gateway";
                $login_key = "gateway_login";
                $elavon_user = "gateway_user";
                $password_key = "gateway_password";
                break;
            case "MonerisGateway":
                $payment_gateway = "MonerisGateway"; 
                $payment_gateway_settings_key = "payment_gateway";
                $login_key = "gateway_login";
                $password_key = "gateway_password";
                break;
            default: 
                return "gateway_not_supported"; 
                break;
        }
        
        $payment_gateway_settings = $this->ci->paymentgateway->getGatewayCredentials();
        
        if($payment_gateway_settings['selected_payment_gateway'] != $gateway)
        {
            array("success" => false, "message" => "Invalid gateway settings!");
        }
        
        if($payment_gateway != 'QuickbooksGateway')
            $login = trim($payment_gateway_settings[$payment_gateway_settings_key][$login_key]);
        if($payment_gateway == 'ChaseNetConnectGateway')
        {
            $gateway_mid = trim($payment_gateway_settings[$payment_gateway_settings_key][$chase_gateway_mid]);
            $gateway_tid = trim($payment_gateway_settings[$payment_gateway_settings_key][$chase_gateway_tid]);
            $gateway_cid = trim($payment_gateway_settings[$payment_gateway_settings_key][$chase_gateway_cid]);
        }

        if($payment_gateway == 'ElavonGateway')
        {
            $gateway_user = trim($payment_gateway_settings[$payment_gateway_settings_key][$elavon_user]);
        }

        if($payment_gateway == 'QuickbooksGateway')
        {
            $gateway_client_id = $this->ci->config->item('quickbooks_gateway_client_id');
            $gateway_client_secret = $this->ci->config->item('quickbooks_gateway_client_secret');
                            
            $gateway_refresh_token = trim($payment_gateway_settings[$payment_gateway_settings_key][$quick_gateway_refresh_token]);
            $gateway_realm_id = trim($payment_gateway_settings[$payment_gateway_settings_key][$quick_gateway_realm_id]);

            $gateway_access_token = $this->_get_quickbook_access_token(
                                                $gateway_client_id,
                                                $gateway_client_secret,
                                                $gateway_refresh_token,
                                                $gateway_realm_id
                                            );
            
        }
        
        if($password_key && $payment_gateway != 'QuickbooksGateway'){
            $password = trim($payment_gateway_settings[$payment_gateway_settings_key][$password_key]);
        }
        
        if((!$login || !$payment_gateway) && $payment_gateway != 'QuickbooksGateway')
        {
            return array("success" => false, "message" => "Invalid gateway settings!");
        }
        $gateway_credentials = array(
                    "name" => $payment_gateway,
                    "login" => $login
                );
        if($password){
            $gateway_credentials['password'] = $password;
        }
        if($payment_gateway == 'QuickbooksGateway')
        {
            $gateway_credentials['cid'] = $gateway_client_id;
            $gateway_credentials['password'] = $gateway_client_secret;
            unset($gateway_credentials['login']);
        }  

        if($payment_gateway == 'ElavonGateway')
        {
            $gateway_credentials['user'] = $gateway_user;
        } 

        if($payment_gateway == 'MonerisGateway')
        {
            $gateway_credentials['avs_enabled'] = true;
        } 

        $refund = array(
            "TransactionType" => TOKENEX_PAYMENT_REFUND,
            "TransactionRequest" => array(
                "gateway" => $gateway_credentials,
                "transaction" => array(
                    "authorization" => $charge_id,
                    "amount" => $amount,
                    "currency" => strtoupper($currency),
                )
            )
        );

        if($payment_gateway == 'QuickbooksGateway')
        {
            $refund['TransactionRequest']['gateway']['merchant_id'] = $gateway_access_token;
            $refund['TransactionRequest']['gateway']['merchantpin'] = $gateway_refresh_token;
        }
            
        if($payment_gateway == 'ChaseNetConnectGateway')
        {
            $refund['TransactionRequest']['gateway']['mid'] = $gateway_mid;
            $refund['TransactionRequest']['gateway']['tid'] = $gateway_tid;
            $refund['TransactionRequest']['gateway']['cid'] = $gateway_cid;
            
            if ($booking_id) {
                
                $booking = $this->ci->Booking_model->get_booking($booking_id);
                $customer = $this->ci->Customer_model->get_customer_info($booking['booking_customer_id']);
                $customer_card = null;
                if ($credit_card_id) {
                    $customer_card = $this->ci->Card_model->get_customer_card_by_id($booking['booking_customer_id'], $credit_card_id);
                    $customer_card = isset($customer_card[0]) ? $customer_card[0] : null;
                }
                
                $first_name = $customer['customer_name'];        
                $last_name = "(BID:$booking_id,CID:{$booking['booking_customer_id']})";
                
                $refund['TransactionRequest']['transaction']['order_id'] = $booking_id;
                $refund['TransactionRequest']['credit_card'] = array(
                                                                    "first_name" => $first_name,
                                                                    "last_name" => $last_name,
                                                                    "number" => $customer_card ? $customer_card['cc_tokenex_token'] : $customer['cc_tokenex_token'],
                                                                    "month" => $customer_card ? $customer_card['cc_expiry_month'] : $customer['cc_expiry_month'],
                                                                    "year" => "20".($customer_card ? $customer_card['cc_expiry_year'] : $customer['cc_expiry_year'])
                                                                );
                $refund['TransactionRequest']['transaction']['order_id'] = $booking_id;
                $refund['TransactionRequest']['transaction']["billing_address"] = array(
                                                                                        "address1" => $customer['address'],
                                                                                        "city" => $customer['city'],
                                                                                        "state" => $customer['region'],
                                                                                        "zip" => $customer['postal_code']
                                                                                    );
            }
        }
        $response = $this->_process_transaction_tokenex($refund);
        if(isset($response['error']))
        {
            if($payment_gateway == "AuthorizeNetGateway")
            {
                $response['error'] .= ".\nRefunds can not be issued within 24 hours of receiving the payment.";
            }
            return array("success" => false, "message" => $response['error']);
        }
        $refund_id = $response['data'];
        return array("success" => true, "refund_id" => $refund_id);
    }
    
    
    function _process_transaction_tokenex($data){        
        if (isset($_GET['dev_mode'])) {
            print_r($data);
        }
        $response_xml = $this->call_api($this->api_payment_url, 'ProcessTransaction', $data);
        if (isset($_GET['dev_mode'])) {
            print_r($response_xml);
        }
        
        $response_obj = $this->xml_to_object($response_xml);
        // prx($response_obj);
        $charge_failed = false;
        $response = array ();
        if($response_obj->TransactionResult == 'true')
        {
            $response['data'] = (string)$response_obj->Authorization;
            if(!$response['data'])
            {
                $charge_failed = true; // store $response_obj in DB
                $response['error'] = (string)$response_obj->Message;
            }
        }
        elseif($response_obj->TransactionResult == 'false')
        {
            $response['error'] = (string)$response_obj->Error;   
            if(!$response['error'])
            {
                $charge_failed = true; // store $response_obj in DB
                $response['error'] = (string)$response_obj->Message;
            }
        }
        else {
            $charge_failed = true; // store $response_obj in DB
            $response['error'] = "some error occured!";
        }

        if($charge_failed)
            $response['charge_failed'] = $response_obj;
        
        return $response;
    }
    
    function _iframe_session_tokenex()
    {
        $varification_hash = hash_hmac("md5", rand(), "secret");
        $data = array(
            "OriginURL" => base_url(),
            "PlaceHolder" => "•••• •••• •••• ••••",
            "CSS" => "input{display: block;box-sizing: border-box;width: 100%; height: 34px; padding: 6px 12px; font-size: 14px; line-height: 1.42857143; color: #555; background-color: #fff; background-image: none; border: 1px solid #ccc; border-radius: 4px; -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075); box-shadow: inset 0 1px 1px rgba(0,0,0,.075); -webkit-transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s; -o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s; transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;}input:focus { border-color: #66afe9; outline: 0; -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102,175,233,.6); box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102,175,233,.6); outline: 0; }",
            "HMACKey" => $varification_hash,
            "CustomerRefnumber" => 123, // some dummy number, not imp for now
            "TokenScheme" => TOKEN_SCHEME_TOKEN
        );
        $response_json = $this->call_api($this->api_iframe_url, '', $data);
        
        $response_obj = json_decode($response_json);
        
        if($response_obj->Success == true)
        {
            $response['iframe_url'] = (string)$response_obj->HTPURL;
        }
        elseif($response_obj->Success == false)
        {
            $response['error'] = (string)$response_obj->Error;
        }
        return $response;
    }
    
    public function call_api($api_url, $method_name, $data = array(), $header = null, $is_tranparent_payment = null, $request_method = null){
        $data['APIKey'] = $this->api_key;
        $data['TokenExID'] = $this->tokenex_id;
        
        $url = $api_url.$method_name;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        if($request_method == 'put'){
            curl_setopt($ch, CURLOPT_PUT, 1);
        } else {
            curl_setopt($ch, CURLOPT_POST, 1);
        }

        if($is_tranparent_payment)
        {
            unset($data['APIKey']);
            unset($data['TokenExID']);
            $data = json_encode($data);
            curl_setopt($ch, CURLOPT_ENCODING, '');
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        else
        {
            $data = json_encode($data);
            if(!empty($data)){
                $content_length = strlen($data);
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', "Content-Length:$content_length"));
        }
        if($request_method == 'put'){
            // PUT don't need CURLOPT_POSTFIELDS
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_MAXCONNECTS, 1);
                   
        $output = curl_exec($ch);
        
        if(curl_error($ch))
            echo curl_error($ch);
         
        curl_close($ch);
        return $output;
    }
    
    function delete_token($token)
    {
        $data["Token"] = $token;
        $response_xml = $this->call_api($this->api_tokenization_url, 'DeleteToken', $data);
        $response_obj = $this->xml_to_object($response_xml);
        if($response_obj->Success == 'true')
        {
            $response['success'] = true;
        }
        elseif($response_obj->Success == 'false')
        {
            $response['error'] = (string)$response_obj->Error;
        }
        return $response;
    }

    function _get_quickbook_access_token($client_id, $client_secret, $refresh_token, $realm_id)
    {
        $oauth2LoginHelper = new OAuth2LoginHelper($client_id, $client_secret);
        $accessTokenObj = $oauth2LoginHelper->
                            refreshAccessTokenWithRefreshToken($refresh_token);
        $accessTokenValue = $accessTokenObj->getAccessToken();
        $refreshTokenValue = $accessTokenObj->getRefreshToken();

        $meta = array(
            "gateway_refresh_token" => $refreshTokenValue,
            "gateway_realm_id" => $realm_id,
            "refresh_token_created_date" => Date("Y-m-d")
        );
        $update_data['gateway_meta_data'] = json_encode($meta);
        $this->ci->Payment_gateway_model->update_payment_gateway_settings($update_data);

        return $accessTokenValue;
    }

    function _get_credit_card_type($cardNumber) {
    // Remove non-digits from the number
    $cardNumber = preg_replace('/\D/', '', $cardNumber);
 
    // Validate the length
    $len = strlen($cardNumber);
    if ($len < 15 || $len > 16) {
        throw new Exception("Invalid credit card number. Length does not match");
    }else{
        switch($cardNumber) {
            case(preg_match ('/^4/', $cardNumber) >= 1):
                return 'Visa';
            case(preg_match ('/^5[1-5]/', $cardNumber) >= 1):
                return 'Master';
            case(preg_match ('/^3[47]/', $cardNumber) >= 1):
                return 'Amex';
            case(preg_match ('/^3(?:0[0-5]|[68])/', $cardNumber) >= 1):
                return 'Diners';
            case(preg_match ('/^6(?:011|5)/', $cardNumber) >= 1):
                return 'Discover';
            case(preg_match ('/^(?:2131|1800|35\d{3})/', $cardNumber) >= 1):
                return 'JCB';
            default:
                throw new Exception("Could not determine the credit card type.");
                break;
        }
    }
}
}
