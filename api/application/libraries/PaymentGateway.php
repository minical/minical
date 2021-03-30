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

        $company_id = $this->ci->session->userdata('current_company_id');

        if (isset($params['company_id'])) {
            $company_id = $params['company_id'];
        }

        $gateway_settings = $this->ci->Payment_gateway_model->get_payment_gateway_settings(
            $company_id
        );

        $this->setCompanyGatewaySettings($gateway_settings);
        $this->setSelectedGateway($this->company_gateway_settings['selected_payment_gateway']);
        $this->populateGatewaySettings();
        $this->setCurrency();
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

        //if (isset($this->customer['customer_id'])) {
        switch ($this->selected_gateway) {
            case 'stripe':
                $data        = \Stripe\Customer::create(
                    array(
                        "description" => json_encode(
                            array(
                                'customer_name' => $this->customer['customer_name'],
                                'customer_id' => $this->customer['customer_id']
                            )
                        ),
                        "email" => $this->customer['email'],
                        "source"      => $this->stripe_token
                    )
                );
                $external_id = $data->id;
                break;
        }
        //}

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
        $this->customer = json_decode(json_encode($this->ci->Customer_model->get_customer($customer_id)), 1);

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
    public function createBookingCharge($booking_id, $amount)
    {
        $charge = null;

        if ($this->isGatewayPaymentAvailableForBooking($booking_id)) {
            try {
                $this->ci->load->model('Booking_model');
                $this->ci->load->model('Customer_model');
                $booking     = $this->ci->Booking_model->get_booking($booking_id);
                $customer    = $this->ci->Customer_model->get_customer($booking['booking_customer_id']);
                $customer    = json_decode(json_encode($customer), 1);
                $external_id = $customer[$this->getExternalEntityField()];

                switch ($this->selected_gateway) {
                    case 'stripe':
                        $charge = $this->createStripeCharge($amount, $external_id);
                        break;
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
                $this->setErrorMessage($error);
            }
        }

        return $charge;
    }

    /**
     * Can Booking perform payment operations
     *
     * @param $booking_id
     * @return bool
     */
    public function isGatewayPaymentAvailableForBooking($booking_id)
    {
        $result = false;

        $this->ci->load->model('Booking_model');
        $this->ci->load->model('Customer_model');

        $booking       = $this->ci->Booking_model->get_booking($booking_id);
        $customer      = $this->ci->Customer_model->get_customer($booking['booking_customer_id']);
        $customer      = json_decode(json_encode($customer), 1);
        $hasExternalId = (isset($customer[$this->getExternalEntityField()]) and $customer[$this->getExternalEntityField()]);

        if (
            $this->areGatewayCredentialsFilled()
            and $customer
            and $hasExternalId
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
        $credentials['stripe']['stripe_publishable_key'] = $this->company_gateway_settings['stripe_publishable_key'];

        if (!$publicOnly) {
            $credentials['stripe']['stripe_secret_key'] = $this->company_gateway_settings['stripe_secret_key'];
        }

        $credentials['paypal']['paypal_email'] = $this->company_gateway_settings['paypal_email'];
        $result                                = $credentials;

        if ($filter) {
            $result                             = $result[$filter];
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
    public function refundBookingPayment($payment_id)
    {
        $result = true;
        $this->ci->load->model('Payment_model');
        $payment = $this->ci->Payment_model->get_payment($payment_id);

        try {
            if ($payment['payment_gateway_used'] and $payment['gateway_charge_id']) {
                switch ($this->selected_gateway) {
                    case 'stripe':
                        $this->stripeRefund($payment['gateway_charge_id']);
                        break;
                }
            }
        } catch (Exception $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * @param $charge_id
     */
    private function stripeRefund($charge_id)
    {
        $ch = \Stripe\Charge::retrieve($charge_id);
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