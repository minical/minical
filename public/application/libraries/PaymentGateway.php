<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Manages gateway operations
 * @property  Currency_model Currency_model
 */
class PaymentGateway
{

    const DEFAULT_CURRENCY = 'usd';

    /**
     * @var CI_Controller
     */
    private $ci;

    /**
     * @var int Stripe token
     */
    private $stripe_token;

    /**
     * @var string Stripe private key
     */
    private $stripe_private_key;

    /**
     * @var string Stripe public key
     */
    private $stripe_public_key;

    /**
     * @var string selected gateway
     */
    private $selected_gateway;

    /**
     * @var array Company gateway settings
     */
    private $company_gateway_settings;

    /**
     * @var array Customer
     */
    private $customer;

    /**
     * @var string Error message
     */
    private $error_message;

    /**
     * @var string
     */
    private $currency = self::DEFAULT_CURRENCY;

    /**
     *
     * @var string External Id, can only be one per gateway
     */
    private $customer_external_entity_id;

    function __construct($params = null)
    {   
        $this->ci =& get_instance();
        $this->ci->load->model('Payment_gateway_model');
        $this->ci->load->model('Customer_model');
        $this->ci->load->library('session');
        $this->ci->load->model("Card_model");
                
        $company_id = $this->ci->session->userdata('current_company_id');

        if (isset($params['company_id'])) {
            $company_id = $params['company_id'];
        }

        $gateway_settings = $this->ci->Payment_gateway_model->get_payment_gateway_settings(
            $company_id
        );
                    
                if($gateway_settings)
                {
                    $this->setCompanyGatewaySettings($gateway_settings);
                    $this->setSelectedGateway($this->company_gateway_settings['selected_payment_gateway']);
                    $this->populateGatewaySettings();
                    $this->setCurrency();       
                }       
    }

    /**
     *
     */
    private function populateGatewaySettings()
    {
        switch ($this->selected_gateway) {
            case 'stripe':
                $this->stripe_private_key = $this->company_gateway_settings['stripe_secret_key'];
                $this->stripe_public_key  = $this->company_gateway_settings['stripe_publishable_key'];
                \Stripe\Stripe::setApiKey($this->stripe_private_key);
                break;
        }
    }

    private function setCurrency()
    {
        // itodo some gateway currency maybe unavailable
        $this->ci->load->model('Currency_model');
        $currency       = $this->ci->Currency_model->get_default_currency($this->company_gateway_settings['company_id']);
        $this->currency = strtolower($currency['currency_code']);
    }

    /**
     * @param $generic_name
     * @return null|string
     */
    public static function getGatewayName($generic_name)
    {
        $names = self::getGatewayNames();

        return isset($names[$generic_name]) ? $names[$generic_name] : null;
    }

    /**
     * @return array
     */
    public static function getGatewayNames()
    {
        return array(
            'stripe' => 'Stripe'
        );
    }

    /**
     * @return int
     */
    public function getStripeToken()
    {
        return $this->stripe_token;
    }

    /**
     * @param $token
     */
    public function setCCToken($token)
    {
        switch ($this->selected_gateway) {
            case 'stripe':
                $this->setStripeToken($token);
                break;
        }
    }

    /**
     * @param int $stripe_token
     */
    private function setStripeToken($stripe_token)
    {
        $this->stripe_token = $stripe_token;
    }

    /**
     * @return string
     */
    public function getStripePrivateKey()
    {
        return $this->stripe_private_key;
    }

    /**
     * @param string $stripe_private_key
     */
    public function setStripePrivateKey($stripe_private_key)
    {
        $this->stripe_private_key = $stripe_private_key;
    }

    /**
     * @return string
     */
    public function getStripePublicKey()
    {
        return $this->stripe_public_key;
    }

    /**
     * @param string $stripe_public_key
     */
    public function setStripePublicKey($stripe_public_key)
    {
        $this->stripe_public_key = $stripe_public_key;
    }

    /**
     * @return string
     */
    public function getSelectedGateway()
    {
        return $this->selected_gateway;
    }

    /**
     * @param string $selected_gateway
     */
    public function setSelectedGateway($selected_gateway)
    {
        $this->selected_gateway = $selected_gateway;
    }

    /**
     * Creates an external entity, required to reuse cc tokens for most systems
     */
    public function createExternalEntity()
    {
        $external_id = null;

        switch ($this->selected_gateway) {
            case 'stripe':
                $data        = \Stripe\Customer::create(
                    array(
                        "description" => json_encode(
                            array(
                                'customer_id' => isset($this->customer['customer_id']) ? $this->customer['customer_id'] : 'new_customer',
                            )
                        ),
                        "source"      => $this->stripe_token
                    )
                );
                $external_id = $data->id;
                break;
        }

        return $external_id;
    }

    /**
     * @return mixed
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param $customer_id
     */
    public function setCustomerById($customer_id)
    {
        $customer = $this->ci->Customer_model->get_customer($customer_id);
        unset($customer['cc_number']);
        unset($customer['cc_expiry_month']);
        unset($customer['cc_expiry_year']);
        unset($customer['cc_tokenex_token']);
        unset($customer['cc_cvc_encrypted']);
        
        $card_data = $this->ci->Card_model->get_active_card($customer_id, $this->ci->company_id);
            
        if($card_data){
            $customer['cc_number'] = $card_data['cc_number'];
            $customer['cc_expiry_month'] = $card_data['cc_expiry_month'];
            $customer['cc_expiry_year'] = $card_data['cc_expiry_year'];
            $customer['cc_tokenex_token'] = $card_data['cc_tokenex_token'];
            $customer['cc_cvc_encrypted'] = $card_data['cc_cvc_encrypted'];
        }
            
        $this->customer = json_decode(json_encode($customer), 1);
    }

    /**
     * @return string
     */
    public function getCustomerExternalEntityId()
    {
        $id = null;

        if ($this->customer) {
            switch ($this->selected_gateway) {
                case 'stripe':
                    $id = $this->customer['stripe_customer_id'];
                    break;
            }
        }


        return $id;
    }

    /**
     * @param string $customer_external_entity_id
     */
    public function setCustomerExternalEntityId($customer_external_entity_id)
    {
        $this->customer_external_entity_id = $customer_external_entity_id;
    }

    /**
     * @param $booking_id
     * @param $amount
     * @return bool
     */
    public function createBookingCharge($booking_id, $amount, $customer_id = null, $cvc = null, $is_capture = true)
    {
        $charge_id = null;

        if ($this->isGatewayPaymentAvailableForBooking($booking_id, $customer_id)) {
            try {
                $this->ci->load->model('Booking_model');
                $this->ci->load->model('Customer_model');
                $this->ci->load->model('Card_model');
                $this->ci->load->library('tokenex');
                
                $booking     = $this->ci->Booking_model->get_booking($booking_id);
                
                $customer_id = $customer_id ? $customer_id : $booking['booking_customer_id'];
                
                $customer_info    = $this->ci->Card_model->get_customer_cards($customer_id);
                //print_r($customer);
                $customer = "";
                if(isset($customer_info) && $customer_info){
                    
                    foreach($customer_info as $customer_data){
                        if(($customer_data['is_primary']) && !$customer_data['is_card_deleted']){
                            $customer = $customer_data;
                        }
                    } 
                }
               /* if($checkPrimarycard){
                    $customer    = $this->ci->Customer_model->get_customer($customer_id);
                    if(isset($customer) && $customer){
                        if($customer['is_card_deleted'] == 0){
                            $customer = $customer;
                        }else{
                            $customer = null;
                        }
                    }
                }*/
                
                
                $customer    = json_decode(json_encode($customer), 1);
               
                if(isset($customer['cc_tokenex_token']) && $customer['cc_tokenex_token'])
                {
                   
                    // use tokenex for payments
                        $charge = $this->ci->tokenex->make_payment($this->selected_gateway, $amount, $this->currency, $customer_id, $booking_id, $cvc, null, null, $is_capture);
                        
                    $charge_id = null;
                    if($charge['success'])
                    {
                        if(isset($charge['charge_id']) && $charge['charge_id'])
                            $charge_id = $charge['charge_id'];
                        else
                        {
                           return $charge['authorization'];
                        }
                    }
                    else
                    {
                        $charge_id = isset($charge['charge_failed']) && $charge['charge_failed'] ? $charge['charge_failed'].'-charge_failed' : (isset($charge['message']) && $charge['message'] ? $charge['message'].'-charge_failed' : '');
                        $this->setErrorMessage($charge['message']);
                    }
                }
                else
                {
                    // use stripe for old customers 
                    $external_id = $customer[$this->getExternalEntityField()];
                    switch ($this->selected_gateway) {
                        case 'stripe':
                            $charge_id = $this->createStripeCharge($amount, $external_id);
                            break;
                    }
                }
                              
            } catch (Exception $e) {
                $error = $e->getMessage();
                $this->setErrorMessage($error);
            }
        }

        return $charge_id;
    }

    /**
     * Can Booking perform payment operations
     *
     * @param $booking_id
     * @return bool
     */
    public function isGatewayPaymentAvailableForBooking($booking_id, $customer_id = null)
    {
        $result = false;

        $this->ci->load->model('Booking_model');
        $this->ci->load->model('Customer_model');

        $booking       = $this->ci->Booking_model->get_booking($booking_id);
        
        $customer_id = $customer_id ? $customer_id : $booking['booking_customer_id'];
        
        $customer      = $this->ci->Customer_model->get_customer($customer_id);
        
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
            
        $customer      = json_decode(json_encode($customer), 1);
        $hasExternalId = (isset($customer[$this->getExternalEntityField()]) and $customer[$this->getExternalEntityField()]);
        $hasTokenexToken = (isset($customer['cc_tokenex_token']) and $customer['cc_tokenex_token']);
        
        if(!$hasTokenexToken)
        {
            $customer      = $this->ci->Customer_model->get_customer($customer_id);
            $customer      = json_decode(json_encode($customer), 1);
            $hasExternalId = (isset($customer[$this->getExternalEntityField()]) and $customer[$this->getExternalEntityField()]);
            $hasTokenexToken = (isset($customer['cc_tokenex_token']) and $customer['cc_tokenex_token']);
        }
        
        if (
            $this->areGatewayCredentialsFilled()
            and $customer
            and ($hasExternalId or $hasTokenexToken)
        ) {
            $result = true;
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getExternalEntityField()
    {
        $name = '';
        switch ($this->selected_gateway) {
            case 'stripe':
                $name = 'stripe_customer_id';
                break;
        }

        return $name;
    }

    /**
     * Checks if gateway settings are filled
     *
     * @return bool
     */
    public function areGatewayCredentialsFilled()
    {
        $filled                       = true;
        $selected_gateway_credentials = $this->getSelectedGatewayCredentials();

        foreach ($selected_gateway_credentials as $credential) {
            if (empty($credential)) {
                $filled = false;
            }
        }

        return $filled;
    }

    /**
     * @param bool $publicOnly
     * @return array
     */
    public function getSelectedGatewayCredentials($publicOnly = false)
    {
        $credentials = $this->getGatewayCredentials($this->selected_gateway, $publicOnly);

        return $credentials;
    }

    /**
     * @param null $filter
     * @param bool $publicOnly
     * @return array
     */
    public function getGatewayCredentials($filter = null, $publicOnly = false)
    {
        $credentials                                     = array();
        $credentials['selected_payment_gateway']         = $this->selected_gateway; // itodo legacy
        $credentials['stripe']['stripe_publishable_key'] = isset($this->company_gateway_settings['stripe_publishable_key']) ? $this->company_gateway_settings['stripe_publishable_key'] : null;

        if (!$publicOnly) {
            $credentials['stripe']['stripe_secret_key'] = isset($this->company_gateway_settings['stripe_secret_key']) ? $this->company_gateway_settings['stripe_secret_key'] : null;
        }
        
        if(isset($this->company_gateway_settings) && $this->company_gateway_settings && json_decode($this->company_gateway_settings['gateway_meta_data'], true)){
            $company_meta = json_decode($this->company_gateway_settings['gateway_meta_data'], true);
        }
        $meta_data = $company_meta ?? null;
        if($meta_data)
        {
            if((isset($meta_data["gateway_mid"]) && $meta_data["gateway_mid"]) || (isset($meta_data["gateway_client_id"]) && $meta_data["gateway_client_id"]))
            {
                $credentials['payment_gateway'] = array(
                    'gateway_login' => $this->company_gateway_settings['gateway_login'],
                    'gateway_password' => $this->company_gateway_settings['gateway_password'],
                    'gateway_mid' => isset($meta_data["gateway_mid"]) ? $meta_data["gateway_mid"] : "",
                    'gateway_tid' => isset($meta_data["gateway_tid"]) ? $meta_data["gateway_tid"] : "",
                    'gateway_cid' => isset($meta_data["gateway_cid"]) ? $meta_data["gateway_cid"] : ""
                );
            }
            elseif(isset($meta_data["gateway_refresh_token"]) && $meta_data["gateway_refresh_token"])
            {
                $credentials['payment_gateway'] = array(
                    'gateway_refresh_token' => isset($meta_data["gateway_refresh_token"]) ? $meta_data["gateway_refresh_token"] : "",
                    'gateway_realm_id' => isset($meta_data["gateway_realm_id"]) ? $meta_data["gateway_realm_id"] : "",
                    'refresh_token_created_date' => isset($meta_data["refresh_token_created_date"]) ? $meta_data["refresh_token_created_date"] : ""
                );
            }
            elseif(isset($meta_data["gateway_app_id"]) && $meta_data["gateway_app_id"])
            {
                $credentials['payment_gateway'] = array(
                    'gateway_login' => $this->company_gateway_settings['gateway_login'],
                    'gateway_password' => $this->company_gateway_settings['gateway_password'],
                    'gateway_app_id' => isset($meta_data["gateway_app_id"]) ? $meta_data["gateway_app_id"] : "",
                    'gateway_private_key' => isset($meta_data["gateway_private_key"]) ? $meta_data["gateway_private_key"] : "",
                    'gateway_public_key' => isset($meta_data["gateway_public_key"]) ? $meta_data["gateway_public_key"] : "",
                );
            }
            elseif(isset($meta_data["gateway_user"]) && $meta_data["gateway_user"])
            {
                $credentials['payment_gateway'] = array(
                    'gateway_login' => $this->company_gateway_settings['gateway_login'],
                    'gateway_password' => $this->company_gateway_settings['gateway_password'],
                    'gateway_user' => isset($meta_data["gateway_user"]) ? $meta_data["gateway_user"] : ""
                );
            }

            elseif(isset($meta_data["gateway_merchant_id"]) && $meta_data["gateway_merchant_id"])
            {
                $credentials['payment_gateway'] = array(
                    'gateway_merchant_id' => isset($meta_data["gateway_merchant_id"]) ? $meta_data["gateway_merchant_id"] : "",
                    'gateway_merchant_key' => isset($meta_data["gateway_merchant_key"]) ? $meta_data["gateway_merchant_key"] : ""
                );
            }

            elseif(isset($meta_data["gateway_square_app_id"]) && $meta_data["gateway_square_access_token"])
            {
                $credentials['payment_gateway'] = array(
                    'gateway_square_app_id' => isset($meta_data["gateway_square_app_id"]) ? $meta_data["gateway_square_app_id"] : "",
                    'gateway_square_access_token' => isset($meta_data["gateway_square_access_token"]) ? $meta_data["gateway_square_access_token"] : ""
                );
            }
        }

        if(
            $credentials['selected_payment_gateway'] == 'PayflowGateway' ||
            $credentials['selected_payment_gateway'] == 'FirstdataE4Gateway' ||
            $credentials['selected_payment_gateway'] == 'AuthorizeNetGateway' ||
            $credentials['selected_payment_gateway'] == 'MonerisGateway'
        )
        {
            $credentials['payment_gateway'] = array(
                'gateway_login' => $this->company_gateway_settings['gateway_login'],
                'gateway_password' => $this->company_gateway_settings['gateway_password']
            );
        }

        
        
        if($credentials['selected_payment_gateway'] == 'QuickbooksGateway')
        {
            unset($credentials['payment_gateway']['gateway_login']);
            unset($credentials['payment_gateway']['gateway_password']);
        }
        $credentials['paypal']['paypal_email'] = isset( $this->company_gateway_settings['paypal_email'] ) ? $this->company_gateway_settings['paypal_email'] : null;
        $result                                = $credentials;

        if ($filter) {
            $result                             = isset($result[$filter]) ? $result[$filter] : (isset($result['payment_gateway']) && $result['payment_gateway'] ? $result['payment_gateway'] : null);
            $result['selected_payment_gateway'] = $this->selected_gateway; // itodo legacy
        }

        return $result;
    }

    /**
     * @param $amount
     * @param $customer_token
     *
     * @return string
     */
    private function createStripeCharge($amount, $customer_token)
    {
        $charge = \Stripe\Charge::create(
            array(
                "amount"   => $amount,// in cents 1$ == 100c
                "currency" => $this->currency,
                "customer" => $customer_token
            )
        );
        if (isset($_GET['dev_mode'])) {
            print_r($charge);
        }

        return $charge->id;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->error_message;
    }

    /**
     * @param string $error_message
     */
    public function setErrorMessage($error_message)
    {
        $this->error_message = $error_message;
    }

    /**
     * @param $payment_id
     */
    public function refundBookingPayment($payment_id, $amount, $payment_type, $booking_id = null)
    {
        $result = array("success" => true, "refund_id" => true);
        $this->ci->load->model('Payment_model');
        $this->ci->load->model('Customer_model');
        $this->ci->load->library('tokenex');
   
        $payment = $this->ci->Payment_model->get_payment($payment_id);
        
        try {
            if ($payment['payment_gateway_used'] and $payment['gateway_charge_id']) {
                $customer    = $this->ci->Customer_model->get_customer($payment['customer_id']);
                
                unset($customer['cc_number']);
                unset($customer['cc_expiry_month']);
                unset($customer['cc_expiry_year']);
                unset($customer['cc_tokenex_token']);
                unset($customer['cc_cvc_encrypted']);

                $card_data = $this->ci->Card_model->get_active_card($payment['customer_id'], $this->ci->company_id);

                if(isset($card_data) && $card_data){
                    $customer['cc_number'] = $card_data['cc_number'];
                    $customer['cc_expiry_month'] = $card_data['cc_expiry_month'];
                    $customer['cc_expiry_year'] = $card_data['cc_expiry_year'];
                    $customer['cc_tokenex_token'] = $card_data['cc_tokenex_token'];
                    $customer['cc_cvc_encrypted'] = $card_data['cc_cvc_encrypted'];
                }
                
                $customer    = json_decode(json_encode($customer), 1);
                if(isset($customer['cc_tokenex_token']) && $customer['cc_tokenex_token'])
                {
                    if($payment_type == 'full'){
                        $amount = abs($payment['amount']) * 100; // in cents, only positive
                    }
                    if($this->selected_gateway == 'PayuGateway')
                    {
                        $result = $this->ci->tokenex->refund_payment_by_payuhub($this->selected_gateway, $this->currency, $payment['gateway_charge_id'], $amount, $booking_id, $payment['customer_id'], $payment['credit_card_id']);
                    }
                    elseif($this->selected_gateway == 'CieloGateway')
                    {
                        $result = $this->ci->tokenex->refund_payment_by_cielo($this->selected_gateway, $payment['gateway_charge_id'], $amount, $booking_id, $payment['customer_id']);
                    }
                    elseif($this->selected_gateway == 'SquareGateway')
                    {
                        $result = $this->ci->tokenex->refund_payment_by_square($this->selected_gateway, $payment['gateway_charge_id'], $amount, $booking_id, $payment['customer_id'], $this->currency);
                    }
                    else
                    {
                        $result = $this->ci->tokenex->refund_payment($this->selected_gateway, $payment['gateway_charge_id'], $amount, $this->currency, $booking_id, $payment['credit_card_id']);
                    }
                    
                }
                else
                {
                    switch ($this->selected_gateway) {
                        case 'stripe':
                            $this->stripeRefund($payment['gateway_charge_id'], $amount, $payment_type);
                            break;
                    }
                }
            }
        } catch (Exception $e) {
            $result = array("success" => false, "message" => $e->getMessage());
        }

        return $result;
    }

    /**
     * @param $charge_id
     */
    private function stripeRefund($charge_id, $amount, $payment_type)
    {
        $ch = \Stripe\Charge::retrieve($charge_id);
        if(isset($payment_type) && $payment_type == 'partial')
            $re = $ch->refunds->create(array('amount' => $amount));
        else 
            $re = $ch->refunds->create();
    }

    /**
     * Get array external customer id per gateway
     * itodo maybe not applicable to some gateways
     * @return array
     */
    public function getCustomerTokenInfo()
    {
        $data = array();
        foreach ($this->getPaymentGateways() as $gateway => $settings) {
            if (isset($this->customer[$settings['customer_token_field']]) and $this->customer[$settings['customer_token_field']]) {
                $data[$gateway] = $this->customer[$settings['customer_token_field']];
            }
        }
        return $data;
    }

    /**
     * @return array
     */
    public static function getPaymentGateways()
    {
        return array(
            'stripe' => array(
                'name'                 => 'Stripe',
                'customer_token_field' => 'stripe_customer_id'
            )
        );
    }

    /**
     * @param $payment_type
     * @param $company_id
     * @return array
     */
    public function getPaymentGatewayPaymentType($payment_type, $company_id = null)
    {
        $settings   = $this->getCompanyGatewaySettings();
        $company_id = $company_id ?: $settings['company_id'];

        $row = $this->query("select * from payment_type WHERE payment_type = '$payment_type' and company_id = '$company_id'");

        if (empty($row)) {
            // if doesn't exist - create
            $this->createPaymentGatewayPaymentType($payment_type, $company_id);
            $result = $this->getPaymentGatewayPaymentType($payment_type, $company_id);
        } else {
            $result = reset($row);
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getCompanyGatewaySettings()
    {
        return $this->company_gateway_settings;
    }

    /**
     * @param array $company_gateway_settings
     */
    public function setCompanyGatewaySettings($company_gateway_settings)
    {
        $this->company_gateway_settings = $company_gateway_settings;
    }

    private function query($sql)
    {
        return $this->ci->db->query($sql)->result_array();
    }

    /**
     * @param $company_id
     */
    public function createPaymentGatewayPaymentType($payment_type, $company_id)
    {
        $this->ci->db->insert(
            'payment_type',
            array(
                'payment_type' => $payment_type,
                'company_id'   => $company_id,
                'is_read_only' => '1'
            )
        );

        return $this->ci->db->insert_id();
    }

}