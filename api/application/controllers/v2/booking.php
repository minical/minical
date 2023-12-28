<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Example
 *
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array.
 *
 * @package     CodeIgniter
 * @subpackage  Rest Server
 * @category    Controller
 * @author      Phil Sturgeon
 * @link        http://philsturgeon.co.uk/code/
*/

// This can be removed if you use __autoload() in config.php OR use Modular Extensions

/**
 *
 * @SWG\Model(id="Booking")
 */
class Booking extends MY_Controller
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
        $this->load->model("Room_type_model");
        $this->load->model("Charge_model");
        $this->load->model("Payment_model");
        $this->load->model("Card_model");
        $this->load->model("Availability_model");
        $this->load->model("Image_model");
        $this->load->model("Tax_model");
            
        $this->load->helper('timezone');


        
        //$this->response(array('error' => 'Invalid API_key'), 404);
    }

    function receive_booking_post()
    {
        
        $booking = $this->post('booking');

        if(!is_array($booking)){
            $booking = json_decode($booking, true);
        }

        if(isset($booking) && $booking)
        {
            $booking['api_key'] = $this->post('X-API-KEY');
            if ($booking['booking_type'] && $booking['booking_type'] === "cancelled") {
                
                $this->_process_cancel_booking_request($booking);
                
            } elseif ($booking['booking_type'] && $booking['booking_type'] === "modified") {
                
                $this->_process_modify_booking_request($booking);
                
            } elseif ($booking['booking_type'] && $booking['booking_type'] === "new") {
                
                $this->_process_create_booking_request($booking);
            }
            else
            {
                $this->response(array('status' => false, 'error' => 'Booking type is invalid OR missing.'), 200);
            }
        }
        else
        {
            $this->response(array('status' => false, 'error' => 'Invalid json.'), 200);
        }
    }

    function _process_create_booking_request ($booking) {
        
        if (!empty($booking))
        {   
            try{
                $response = array(
                            'error' => "Some error occured!"
                        );
                $pms_booking_id = $this->_create_booking($booking);
                if(is_numeric($pms_booking_id)){
                    $response = array(
                            'ota_booking_id' => $booking['ota_booking_id'],
                            'pms_booking_id' => (isset($pms_booking_id))?$pms_booking_id:'',
                            'booking_type' => $booking['booking_type']
                        );

                    $booking['pms_booking_id'] = (isset($pms_booking_id))?$pms_booking_id:'';
                    $booking['response'] = $response;

                    switch ($_SERVER['HTTP_HOST']) {
                        case 'roomsy-api-staging.azurewebsites.net': // staging
                            $cm_url   = "http://roomsycm-staging.azurewebsites.net"; 
                            break;
                        case 'api.roomsy.com': // production
                            $cm_url   = "http://cm.roomsy.com"; // production
                            break;
                        case 'roomsy.api': // local
                            $cm_url   = "http://roomsy.local"; // production
                            break;
                        default: // production // other whitelabel companies
                            $cm_url   = "http://cm.roomsy.com"; // production
                            break;
                    }

                    $url = $cm_url."/sync/save_booking";

                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($booking));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_MAXCONNECTS, 1);

                    $output = curl_exec($ch);
                    curl_close($ch);
                }
                else
                {
                    $response = array(
                            'error' => $pms_booking_id
                        );
                }
                if (ob_get_contents()) ob_end_clean();
                
                $this->response($response, 200); // 200 being the HTTP response code
            }
            catch(Exception $e){
                $this->response(array("error" => $e->getMessage()), 200);
            }
        }
    }
    
    function _process_modify_booking_request ($booking) {

        if (!empty($booking))
        {   
            if(!array_key_exists('pms_booking_id', $booking))
            {
                $this->response(array('status' => false, 'error' => 'PMS booking id is missing.'), 200);
            }
            else if(array_key_exists('pms_booking_id', $booking) && $booking['pms_booking_id'] == '')
            {
                $this->response(array('status' => false, 'error' => 'PMS booking id is missing.'), 200);
            }
            try{
                $response = array(
                            'error' => "Some error occured!"
                        );
                $pms_booking_id = $this->_modify_booking($booking);
                if(is_numeric($pms_booking_id)){
                    $response = array(
                            'ota_booking_id' => $booking['ota_booking_id'],
                            'pms_booking_id' => (isset($pms_booking_id))?$pms_booking_id:'',
                            'booking_type' => $booking['booking_type']
                        );

                    $booking['pms_booking_id'] = (isset($pms_booking_id))?$pms_booking_id:'';
                    $booking['response'] = $response;

                    switch ($_SERVER['HTTP_HOST']) {
                        case 'roomsy-api-staging.azurewebsites.net': // staging
                            $cm_url   = "http://roomsycm-staging.azurewebsites.net"; 
                            break;
                        case 'api.roomsy.com': // production
                            $cm_url   = "http://cm.roomsy.com"; // production
                            break;
                        case 'roomsy.api': // local
                            $cm_url   = "http://roomsy.local"; // production
                            break;
                        default: // production // other whitelabel companies
                            $cm_url   = "http://cm.roomsy.com"; // production
                            break;
                    }

                    $url = $cm_url."/sync/save_booking";

                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($booking));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_MAXCONNECTS, 1);

                    $output = curl_exec($ch);
                    curl_close($ch);
                }
                else
                {
                    $response = array(
                            'error' => $pms_booking_id
                        );
                }
                if (ob_get_contents()) ob_end_clean();
                
                $this->response($response, 200); // 200 being the HTTP response code
            }
            catch(Exception $e){
                $this->response(array("error" => $e->getMessage()), 200);
            }
        }
    }
    
    function _process_cancel_booking_request ($booking) {

        if(!array_key_exists('pms_booking_id', $booking))
        {
            $this->response(array('status' => false, 'error' => 'PMS booking id is missing.'), 200);
        }
        else if(array_key_exists('pms_booking_id', $booking) && $booking['pms_booking_id'] == '')
        {
            $this->response(array('status' => false, 'error' => 'PMS booking id is missing.'), 200);
        }
        
        $pms_booking_id = $booking['pms_booking_id'];

        $cancellation_fee = $this->post('cancellation_fee');

        $company_id = $this->post('company_id');
        $company = $this->Company_model->get_company_data($company_id);

        if($pms_booking_id && count($pms_booking_id) > 0)
        {
            $this->Booking_model->cancel_booking($pms_booking_id, $cancellation_fee);
            $log_data = array(
                'selling_date' => $company['selling_date'],
                'user_id' => 2, //User_id 2 is Online Reservation
                'booking_id' => $pms_booking_id,                  
                'date_time' => gmdate('Y-m-d H:i:s'),
                'log' => 'OTA Booking cancelled',
                'log_type' => SYSTEM_LOG
            );

            $this->Booking_log_model->insert_log($log_data);

            $response = array(
                            'ota_booking_id' => $booking['ota_booking_id'],
                            'pms_booking_id' => (isset($pms_booking_id))?$pms_booking_id:'',
                            'booking_type' => $booking['booking_type']
                        );

            $booking['pms_booking_id'] = (isset($pms_booking_id))?$pms_booking_id:'';
            $booking['response'] = $response;

            switch ($_SERVER['HTTP_HOST']) {
                case 'roomsy-api-staging.azurewebsites.net': // staging
                    $cm_url   = "http://roomsycm-staging.azurewebsites.net"; 
                    break;
                case 'api.roomsy.com': // production
                    $cm_url   = "http://cm.roomsy.com"; // production
                    break;
                case 'roomsy.api': // local
                    $cm_url   = "http://roomsy.local"; // production
                    break;
                default: // production // other whitelabel companies
                    $cm_url   = "http://cm.roomsy.com"; // production
                    break;
            }

            $url = $cm_url."/sync/save_booking";

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($booking));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_MAXCONNECTS, 1);

            $output = curl_exec($ch);
            curl_close($ch);

            $this->response($response, 200);
        }
    }

    /**
    *
    *   @return booking_id of the newly created booking
    */
    function _create_booking($booking)
    {
        try{
            // create customer
            $booking_customer = $booking['booking_customer'];
            $company_id = $booking['company_id'];

            // cc details, masked
            if(isset($booking['card']) && isset($booking['card']['number'])){
                $cc_tokenex_token = $cc_cvc_encrypted = NULL;
                $cc_data = array();//$this->get_tokenex_token_and_encrypted_cvc($booking['card']['number'], $booking['card']['cvc'], $company_id);
                if(isset($cc_data['token_ex_token']))
                {
                    $cc_tokenex_token = $cc_data['token_ex_token']; // tokenex token 
                }
                if(isset($cc_data['cvc_encrypted']))
                {
                    $cc_cvc_encrypted = $cc_data['cvc_encrypted']; // encrypted format
                }
                
                $booking_customer += array(
                    'cc_number'         => 'XXXX XXXX XXXX '.substr($booking['card']['number'], -4),
                    'cc_expiry_month'   => isset($booking['card']['exp_month']) ? $booking['card']['exp_month'] : null,
                    'cc_expiry_year'    => isset($booking['card']['exp_year']) ? $booking['card']['exp_year'] : null,
                    'cc_tokenex_token'  => $cc_tokenex_token,
                    'cc_cvc_encrypted'  => $cc_cvc_encrypted,
                    'card_holder_name'  => (isset($booking['card']['name']) ? $booking['card']['name'] : "")
                );
                
                
            }
            // check if this customer has stayed in this hotel before. 
            // (by checking email) if so, update the profile
            $booking_customer_id = null;
            if (isset($booking_customer['email']) && $booking_customer['email'])
            {
                $customers = $this->Customer_model->get_customer_by_email($booking_customer['email'], $company_id);
                if($customers && count($customers) > 0)
                {
                    foreach($customers as $customer){
                        if($customer['customer_name'] == $booking_customer['customer_name'])
                        {
                            $customer_info = $customer;
                            $booking_customer_id = $customer['customer_id'];
                        }
                    }
                }
            }
            
            //check card is evc or not  
            $isEVC = 0;
            if(isset($booking['card']) && isset($booking_customer['card_holder_name']) && isset($booking['source'])){
                if($booking['source'] == 'expedia' && $booking_customer['card_holder_name'] == 'Expedia VirtualCard'){
                    $isEVC = 1;
                } 
            }
            
            // if customer already exists, update it. otherwise, create new customer
            $card_data = null;
            if(isset($booking['card']) && isset($booking_customer['cc_tokenex_token'])){
                $card_data = array(
                    'customer_name' => isset($booking_customer['customer_name']) ? $booking_customer['customer_name'] : null,
                    'company_id' => isset($booking_customer['company_id']) ? $booking_customer['company_id'] : null,
                    'cc_number' => isset($booking_customer['cc_number']) ? $booking_customer['cc_number'] : null,
                    'cc_expiry_month' => isset($booking_customer['cc_expiry_month']) ? $booking_customer['cc_expiry_month'] : null,
                    'cc_expiry_year' => isset($booking_customer['cc_expiry_year']) ? $booking_customer['cc_expiry_year'] : null,
                    'cc_tokenex_token' => isset($booking_customer['cc_tokenex_token']) ? $booking_customer['cc_tokenex_token'] : null,
                    'cc_cvc_encrypted' => isset($booking_customer['cc_cvc_encrypted']) ? $booking_customer['cc_cvc_encrypted'] : null,
                    'card_name' => isset($booking_customer['card_holder_name']) ? $booking_customer['card_holder_name'] : null,
                    'evc_card_status' => $isEVC,
                    'is_primary' => 1    
                ); 
            }
                    
       
            if ($booking_customer_id)
            {
                unset($booking_customer['cc_number']);
                unset($booking_customer['cc_expiry_month']);
                unset($booking_customer['cc_expiry_year']);
                unset($booking_customer['cc_tokenex_token']);
                unset($booking_customer['cc_cvc_encrypted']);
                unset($booking_customer['card_holder_name']);
                
                
                $booking_customer['customer_id'] = $booking_customer_id;
                $this->Customer_model->update_customer($booking_customer);
                
                if($card_data) {
                    $card_data['customer_id'] = $booking_customer_id;
                    $this->Card_model->update_customer_card($card_data);
                }
            }
            else
            {
                unset($booking_customer['cc_number']);
                unset($booking_customer['cc_expiry_month']);
                unset($booking_customer['cc_expiry_year']);
                unset($booking_customer['cc_tokenex_token']);
                unset($booking_customer['cc_cvc_encrypted']);
                unset($booking_customer['card_holder_name']);
                
                $booking_customer_id = $this->Customer_model->create_customer($booking_customer);
                
                if($card_data) {
                    $card_data['customer_id'] = $booking_customer_id;
                    $this->Card_model->create_customer_card($card_data);
                }
            }

            //curl to public cron

            // create rate plan, currency_id, rates, date_range, and rate_range_x_rate
            $rate_plan = $booking['rate_plan'];

            // get rate_plan of the hotel that is relevant to this booking

            $rate_plan['charge_type_id'] = $this->Charge_type_model->get_default_charge_type_id($company_id);

            if (isset($rate_plan['pms_rate_plan_id']))
            {
                if ($rate_plan['pms_rate_plan_id'])
                {
                    $pms_rate_plan = $this->Rate_plan_model->get_rate_plan($rate_plan['pms_rate_plan_id']);
                    if(isset($pms_rate_plan['charge_type_id']) && $pms_rate_plan['charge_type_id']) {
                        $rate_plan['charge_type_id'] =  $pms_rate_plan['charge_type_id'];
                    }
                }
                unset($rate_plan['pms_rate_plan_id']);
            }

            // get currency_id
            $currency_id = $this->Currency_model->get_currency_id($rate_plan['currency']['currency_code']);
            $rate_plan['currency_id'] = $currency_id;
            $rate_plan['company_id'] = $company_id;
            $rate_plan['is_selectable'] = '0';
            unset($rate_plan['currency']);

            // create rates
            $rates = $rate_plan['rates'];
            unset($rate_plan['rates']);
            $pms_room_type_id = (isset($booking['pms_room_type_id']))?$booking['pms_room_type_id']:'';
            
                    // create rate plan
                    $rate_plan['room_type_id'] = $pms_room_type_id;
                    $rate_plan_id = $this->Rate_plan_model->create_rate_plan($rate_plan);

                    foreach ($rates as $rate)
                    {
                        $rate_id = $this->Rate_model->create_rate(
                            Array(
                                'rate_plan_id' => $rate_plan_id,
                                'base_rate' => $rate['base_rate'],
                                'adult_1_rate' => $rate['base_rate'],
                                'adult_2_rate' => $rate['base_rate'],
                                'adult_3_rate' => $rate['base_rate'],
                                'adult_4_rate' => $rate['base_rate']
                                )
                            );

                        $date_range_id = $this->Date_range_model->create_date_range(
                            Array(
                                'date_start' => $rate['date_start'],
                                'date_end' => $rate['date_end']
                                )
                            );

                        $this->Date_range_model->create_date_range_x_rate(
                            Array(
                                'rate_id' => $rate_id,
                                'date_range_id' => $date_range_id
                                )
                            );
                    }
           // if adult count not provided, use default max
            if(!isset($booking['adult_count']) || !$booking['adult_count'])
            {
                $room_type = $this->Room_type_model->get_room_type($pms_room_type_id);
                $booking['adult_count'] = isset($room_type['max_adults']) ? $room_type['max_adults'] : 1;
            }

            if($booking['source'] == 'booking_dot_com')
                $source_id = SOURCE_BOOKING_DOT_COM;
            else if($booking['source'] == 'expedia')
                $source_id = SOURCE_EXPEDIA;
            else if($booking['source'] == 'agoda')
                $source_id = SOURCE_AGODA;
            else if($booking['source'] == 'myallocator')
                $source_id = SOURCE_MYALLOCATOR;
            else if($booking['source'] == 'siteminder')
                $source_id = SOURCE_SITEMINDER;
            else
                $source_id = 0;
            
            // create booking
            $booking_data = Array(
                'company_id' => $company_id,
                'state' => '0',
                'rate_plan_id' => $rate_plan_id,
                'booking_customer_id' => $booking_customer_id,
                'adult_count' => $booking['adult_count'],
                'children_count' => isset($booking['children_count']) ? $booking['children_count'] : 0,
                'use_rate_plan' => '1',
                'source' => $source_id,
                'booking_notes' => $booking['booking_notes'],
                'color' => '8EB3DE'
            ); 
            $booking_id = $this->Booking_model->create_booking($booking_data);

            // find an available room and get its room_id
            $company_detail = $this->Company_model->get_company_detail($company_id);

            $room_id = $this->Room_model->get_available_room_id(
                                                    $booking['check_in_date'], 
                                                    $booking['check_out_date'],
                                                    $pms_room_type_id,
                                                    'undefined' ,
                                                    $company_id,
                                                    1, // can be sold online
                                                    null,
                                                    null,
                                                    $company_detail['subscription_level']
                                                );
            //echo "available room_id:";
            //print_r($room_id);
            
            $is_non_continuous_available = false;
            // if no room is available then get non contiuous room blocks
            if(is_null($room_id)){
                $is_non_continuous_available = $this->get_non_continuous_available_room_ids($booking_id, $pms_room_type_id, $company_id, $booking['check_in_date'],  $booking['check_out_date'], $company_detail['subscription_level']);                                   
            }
            
            // if no room is available, then just assign the first room of the room_type
            if (is_null($room_id) && !$is_non_continuous_available)
            {
                $rooms_available = $this->Room_model->get_rooms_by_room_type_id($pms_room_type_id, $company_detail['subscription_level']);
                if (isset($rooms_available[0]->room_id))
                {
                    $room_id = $rooms_available[0]->room_id;
                }
            }

            //echo "chosen room_id:";
            //print_r($room_id);
            
            if(!is_null($room_id)){
                // create booking_room_history
                $booking_room_history_data = Array(
                    'room_id' => $room_id,
                    'check_in_date' => $booking['check_in_date'],
                    'check_out_date' => $booking['check_out_date'],
                    'booking_id' => $booking_id,
                    'room_type_id' => $pms_room_type_id
                );
                $this->Booking_room_history_model->create_booking_room_history($booking_room_history_data);
            }

            // add staying guest if and only if staying guest's name is different from the booking customer
            if (isset($booking['staying_guest']))
            {
                $staying_guest = $booking['staying_guest'];
                if ($staying_guest['guest_name'] != $booking_customer['customer_name'])
                {
                    $staying_guest_data = Array(
                        'company_id' => $company_id,
                        'customer_name' => $staying_guest['guest_name']
                        );
                    $staying_guest_id = $this->Customer_model->create_customer($staying_guest_data);
                    $this->Customer_model->add_staying_customer_to_booking(Array(
                        'customer_id' => $staying_guest_id,
                        'booking_id' => $booking_id,
                        'company_id' => $company_id
                        ));
                }
            }


            // extras
            if (isset($booking['extras']))
            {
                $extras = $booking['extras'];
                foreach ($extras as $extra)
                {
                    // im intentionally not entering $company_id, 
                    // so this extra doesn't show up on the company's innGrid settings.
                    // this extra is for Booking.com only.
                    $extra_data = array(
                                    "extra_name" => $extra['extra_name'],
                                    "extra_type" => $extra['extra_type'],
                                    "charge_type_id" => $this->Charge_type_model->get_default_charge_type_id($company_id),
                                    "charging_scheme" => $extra['charging_scheme']
                                    );
                    $extra_id = $this->Extra_model->create_extra($extra_data);
                    $this->Booking_extra_model->create_booking_extra(
                        $booking_id, 
                        $extra_id,
                        $extra['start_date'],
                        $extra['end_date'],
                        $extra['quantity'],
                        $extra['rate']);

                }
            }

            $company = $this->Company_model->get_company_data($company_id);

            $log_data = array(
                'selling_date' => $company['selling_date'],
                'user_id' => 0, //User_id 0 is System user (null null)
                'booking_id' => $booking_id,                  
                'date_time' => gmdate('Y-m-d H:i:s'),
                'log' => 'OTA Booking created',
                'log_type' => SYSTEM_LOG

            );

            $this->Booking_log_model->insert_log($log_data);

            //Create a corresponding invoice
            $this->Invoice_model->create_invoice($booking_id);

            
            $this->Booking_model->update_booking_balance($booking_id);

            // update channex availability
            $query = http_build_query(
                array(
                    'start_date'                => $booking['check_in_date'],
                    'end_date'                  => $booking['check_out_date'],
                    'room_type_id'              => $pms_room_type_id,
                    'company_id'                => $company_id,
                    'update_from'               => 'extension'
                )
            );

            $req = $this->call_api($this->config->item('app_url'),'/cron/update_channex_availability?'.$query, array(), array(), 'GET');

            // $this->_send_booking_emails(
            //     $booking['ota_booking_id'],
            //     $pms_room_type_id, 
            //     $booking['booking_type'],
            //     $booking['check_in_date'], 
            //     $booking['check_out_date'],
            //     $company_id,
            //     $booking_id
            // );
            
            return $booking_id;
        }
        catch(Exception $e){
            return $booking_id ? $booking_id : $e->getMessage();
        }
    }
    
    function _modify_booking($booking)
    {
        try{
            // create customer
            $booking_customer = $booking['booking_customer'];
            $company_id = $booking['company_id'];
            
            $booking['booking_id'] = $booking['pms_booking_id'];
            $booking_id = $booking['booking_id'];
            unset($booking['pms_booking_id']);

            $real_booking = $this->Booking_model->get_booking_detail($booking_id);

            if(!(isset($real_booking['is_deleted']) && $real_booking['is_deleted'] == '0'))
            {
                return false;
            }

            if(
                isset($booking['card']) && 
                isset($booking['card']['number'])
            ){
                $cc_tokenex_token = $cc_cvc_encrypted = NULL;

                if(isset($booking['card']['token']))
                {
                    $cc_tokenex_token = $booking['card']['token']; // tokenex token 
                }
                if(isset($booking['card']['cvc']))
                {
                    $cc_cvc_encrypted = $booking['card']['cvc']; // encrypted format
                }
                
                $booking_customer += array(
                    'cc_number'         => 'XXXX XXXX XXXX '.substr($booking['card']['number'], -4),
                    'cc_expiry_month'   => isset($booking['card']['exp_month']) ? $booking['card']['exp_month'] : null,
                    'cc_expiry_year'    => isset($booking['card']['exp_year']) ? $booking['card']['exp_year'] : null,
                    'cc_tokenex_token'  => null,
                    'cc_cvc_encrypted'  => null,
                    'card_holder_name'  => (isset($booking['card']['name']) ? $booking['card']['name'] : "")
                );
            }

            // check if this customer has stayed in this hotel before. 
            // (by checking email) if so, update the profile
            $booking_customer_id = null;
            if (isset($booking_customer['email']) && $booking_customer['email'])
            {
                $customers = $this->Customer_model->get_customer_by_email($booking_customer['email'], $company_id);
                if($customers && count($customers) > 0)
                {
                    foreach($customers as $customer){
                        if($customer['customer_name'] == $booking_customer['customer_name'])
                        {
                            $customer_info = $customer;
                            $booking_customer_id = $customer['customer_id'];
                        }
                    }
                }
            }
            
            // if customer already exists, update it. otherwise, create new customer
            $card_data = null;
            if(isset($booking['card'])){
                $card_data = array(
                    'customer_name' => isset($booking_customer['customer_name']) ? $booking_customer['customer_name'] : null,
                    'company_id' => isset($booking_customer['company_id']) ? $booking_customer['company_id'] : null,
                    'cc_number' => isset($booking_customer['cc_number']) ? $booking_customer['cc_number'] : null,
                    'cc_expiry_month' => isset($booking_customer['cc_expiry_month']) ? $booking_customer['cc_expiry_month'] : null,
                    'cc_expiry_year' => isset($booking_customer['cc_expiry_year']) ? $booking_customer['cc_expiry_year'] : null,
                    'cc_tokenex_token' => isset($booking_customer['cc_tokenex_token']) ? $booking_customer['cc_tokenex_token'] : null,
                    'cc_cvc_encrypted' => isset($booking_customer['cc_cvc_encrypted']) ? $booking_customer['cc_cvc_encrypted'] : null,
                    'card_name' => isset($booking_customer['card_holder_name']) ? $booking_customer['card_holder_name'] : null,
                    'is_primary' => 1,   
                ); 

                $meta['token'] = $cc_tokenex_token ? $cc_tokenex_token : null;

                // if(isset($booking['is_card_virtual']) && $booking['is_card_virtual'])
                //     $meta['is_card_virtual'] = 1;

                $card_data['customer_meta_data'] = json_encode($meta);

            }
                    
            if ($booking_customer_id)
            {
                unset($booking_customer['cc_number']);
                unset($booking_customer['cc_expiry_month']);
                unset($booking_customer['cc_expiry_year']);
                unset($booking_customer['cc_tokenex_token']);
                unset($booking_customer['cc_cvc_encrypted']);
                unset($booking_customer['card_holder_name']);
                
                
                $booking_customer['customer_id'] = $booking_customer_id;
                $this->Customer_model->update_customer($booking_customer);

                if($card_data) {
                    $card_data['customer_id'] = $booking_customer_id;
                    $this->Card_model->update_customer_card($card_data);
                }
            }
            else
            {
                unset($booking_customer['cc_number']);
                unset($booking_customer['cc_expiry_month']);
                unset($booking_customer['cc_expiry_year']);
                unset($booking_customer['cc_tokenex_token']);
                unset($booking_customer['cc_cvc_encrypted']);
                unset($booking_customer['card_holder_name']);
                
                $booking_customer_id = $this->Customer_model->create_customer($booking_customer);

                if($card_data) {
                    $card_data['customer_id'] = $booking_customer_id;
                    $this->Card_model->create_customer_card($card_data);
                }
            }

            $customer_data = array(
                "customer_id" => $booking_customer_id,
                "first_name" =>  $booking_customer['customer_name'] ?? null,
                "email" => $booking_customer['email'] ?? null,
                "payment_source" => array(
                    "address_line1" => $booking_customer['address'] ?? null,
                    "address_city" => $booking_customer['city'] ?? null,
                    "address_state" =>  $booking_customer['state'] ?? null,
                    "address_country" => $booking_customer['country'] ?? null,
                    "address_postcode" => $booking_customer['postal_code'] ?? null,
                    "phone" => $booking_customer['phone'] ?? null,
                    "card_name" => "%CARDHOLDER_NAME%",
                    "card_number" => "%CARD_NUMBER%",
                    "expire_month" => "%EXPIRATION_MM%",
                    "expire_year" => "%EXPIRATION_YYYY%",
                    "card_ccv" => "%SERVICE_CODE%",
                ),
            );

            if(isset($company_detail['gateway_meta_data']) && $company_detail['gateway_meta_data']) {
                $company_gateway_meta_data = json_decode($company_detail['gateway_meta_data'], true);

                if(
                    isset($company_gateway_meta_data['gateway_id']) &&
                    $company_gateway_meta_data['gateway_id']
                ) {
                    $customer_data['gateway_id'] = $company_gateway_meta_data['gateway_id'];
                }
            }

            if(isset($cc_tokenex_token) && $cc_tokenex_token != ''){
                $customer_data['company_id'] = $company_id;
                $customer_data['pci_token'] = $cc_tokenex_token;
                $pci_customer_response = apply_filters('post.add.pci_customer', $customer_data);

                if (isset($pci_customer_response['customer_response']['success']) && $pci_customer_response['customer_response']['success'] == true)
                {
                    if(isset($pci_customer_response['customer_response']['customer_id'])){
                        $meta['customer_id'] = $pci_customer_response['customer_response']['customer_id'];
                        $meta['payment_source_id'] = $pci_customer_response['customer_response']['payment_source_id'];
                        $meta['token'] = $pci_customer_response['customer_response']['customer_id'];
                        $meta['vault_token'] = $pci_customer_response['customer_response']['vault_token'];
                        $meta['source'] = $pci_customer_response['customer_response']['source'];
                    } elseif(isset($pci_customer_response['customer_response']['card_token'])){
                        $meta['nexio_token'] = $pci_customer_response['customer_response']['card_token'];
                    }
                    $data['customer_meta_data'] = json_encode($meta);
                    $data['customer_id'] = $booking_customer_id;
                    $this->Card_model->update_customer_card($data);
                }
            }

            // create rate plan, currency_id, rates, date_range, and rate_range_x_rate
            $rate_plan = $booking['rate_plan'];

            // get rate_plan of the hotel that is relevant to this booking

            $rate_plan['charge_type_id'] = $this->Charge_type_model->get_default_charge_type_id($company_id);

            if (isset($rate_plan['pms_rate_plan_id']))
            {
                if ($rate_plan['pms_rate_plan_id'])
                {
                    $pms_rate_plan = $this->Rate_plan_model->get_rate_plan($rate_plan['pms_rate_plan_id']);
                    if(isset($pms_rate_plan['charge_type_id']) && $pms_rate_plan['charge_type_id']) {
                        $rate_plan['charge_type_id'] =  $pms_rate_plan['charge_type_id'];
                    }
                }
                unset($rate_plan['pms_rate_plan_id']);
            }

            // get currency_id
            $currency_id = $this->Currency_model->get_currency_id($rate_plan['currency']['currency_code']);
            $rate_plan['currency_id'] = $currency_id;
            $rate_plan['company_id'] = $company_id;
            $rate_plan['is_selectable'] = '0';
            unset($rate_plan['currency']);

            // create rates
            $rates = $rate_plan['rates'];
            unset($rate_plan['rates']);
            $pms_room_type_id = (isset($booking['pms_room_type_id']))?$booking['pms_room_type_id']:'';
            
            // create rate plan
            $rate_plan['room_type_id'] = $pms_room_type_id;
            $rate_plan_id = $this->Rate_plan_model->create_rate_plan($rate_plan);

            foreach ($rates as $rate)
            {
                $rate_id = $this->Rate_model->create_rate(
                    Array(
                        'rate_plan_id' => $rate_plan_id,
                        'base_rate' => $rate['base_rate'],
                        'adult_1_rate' => $rate['base_rate'],
                        'adult_2_rate' => $rate['base_rate'],
                        'adult_3_rate' => $rate['base_rate'],
                        'adult_4_rate' => $rate['base_rate']
                        )
                    );

                $date_range_id = $this->Date_range_model->create_date_range(
                    Array(
                        'date_start' => $rate['date_start'],
                        'date_end' => $rate['date_end']
                        )
                    );

                $this->Date_range_model->create_date_range_x_rate(
                    Array(
                        'rate_id' => $rate_id,
                        'date_range_id' => $date_range_id
                        )
                    );
            }
            
           // if adult count not provided, use default max
            if(!isset($booking['adult_count']) || !$booking['adult_count'])
            {
                $room_type = $this->Room_type_model->get_room_type($pms_room_type_id);
                $booking['adult_count'] = isset($room_type['max_adults']) ? $room_type['max_adults'] : 1;
            }
            
            if($booking['source'] == 'booking_dot_com')
                $source_id = SOURCE_BOOKING_DOT_COM;
            else if($booking['source'] == 'expedia')
                $source_id = SOURCE_EXPEDIA;
            else if($booking['source'] == 'agoda')
                $source_id = SOURCE_AGODA;
            else if($booking['source'] == 'myallocator')
                $source_id = SOURCE_MYALLOCATOR;
            else if($booking['source'] == 'siteminder')
                $source_id = SOURCE_SITEMINDER;
            else
                $source_id = 0;
            
            // create booking
            $booking_data = Array(
                'booking_id' => $booking_id,
                'rate_plan_id' => $rate_plan_id,
                'booking_customer_id' => $booking_customer_id,
                'adult_count' => $booking['adult_count'],
                'children_count' => isset($booking['children_count']) ? $booking['children_count'] : 0,
                'use_rate_plan' => '1',
                'booking_notes' => $booking['booking_notes']
            ); 

            $this->Booking_model->update_booking($booking_data);
            
            $booking_room_history_data = $this->Booking_room_history_model->get_block($booking_id);
            $booking_room_id = $booking_room_history_data['room_id'];
            $booking_room_type_id = $booking_room_history_data['room_type_id'];
            
            // clean existing booking room history data  
            $this->Booking_room_history_model->delete_booking_room_history($booking_id);

            $company_detail = $this->Company_model->get_company_detail($company_id);

            if (!$booking_room_id || $booking_room_type_id != $pms_room_type_id) {
                // assign new room
                // find an available room and get its room_id
                $room_id = $this->Room_model->get_available_room_id(
                                                        $booking['check_in_date'], 
                                                        $booking['check_out_date'],
                                                        $pms_room_type_id,
                                                        'undefined' ,
                                                        $company_id,
                                                        1, // can be sold online
                                                        null,
                                                        null,
                                                        $company_detail['subscription_level']
                                                    );
                //echo "available room_id:";
                //print_r($room_id);

                $is_non_continuous_available = false;
                // if no room is available then get non contiuous room blocks
                if(is_null($room_id)){
                    $is_non_continuous_available = $this->get_non_continuous_available_room_ids($booking_id, $pms_room_type_id, $company_id, $booking['check_in_date'],  $booking['check_out_date'],$company_detail['subscription_level']);                                   
                }

                // if no room is available, then just assign the first room of the room_type
                if (is_null($room_id) && !$is_non_continuous_available)
                {
                    $rooms_available = $this->Room_model->get_rooms_by_room_type_id($pms_room_type_id, $company_detail['subscription_level']);
                    if (isset($rooms_available[0]->room_id))
                    {
                        $room_id = $rooms_available[0]->room_id;
                    }
                }
            } else {
                // keep old room
                $room_id = $booking_room_id;
            }
            
            
            if(!is_null($room_id)){
                // create booking_room_history
                $booking_room_history_data = Array(
                    'room_id' => $room_id,
                    'check_in_date' => $booking['check_in_date'],
                    'check_out_date' => $booking['check_out_date'],
                    'booking_id' => $booking_id,
                    'room_type_id' => $pms_room_type_id
                );
                $this->Booking_room_history_model->create_booking_room_history($booking_room_history_data);
            }

            // add staying guest if and only if staying guest's name is different from the booking customer
            if (isset($booking['staying_guest']))
            {
                $staying_guest = $booking['staying_guest'];
                if ($staying_guest['guest_name'] != $booking_customer['customer_name'])
                {
                    $staying_guest_data = Array(
                        'company_id' => $company_id,
                        'customer_name' => $staying_guest['guest_name']
                        );
                    $staying_guest_id = $this->Customer_model->create_customer($staying_guest_data);

                    $this->Customer_model->delete_staying_customer_to_booking(Array(
                        'booking_id' => $booking_id,
                        'company_id' => $company_id
                        ));

                    $this->Customer_model->add_staying_customer_to_booking(Array(
                        'customer_id' => $staying_guest_id,
                        'booking_id' => $booking_id,
                        'company_id' => $company_id
                        ));
                }
            }


            // extras
            if (isset($booking['extras']))
            {
                $extras = $booking['extras'];
                foreach ($extras as $extra)
                {
                    // im intentionally not entering $company_id, 
                    // so this extra doesn't show up on the company's innGrid settings.
                    // this extra is for Booking.com only.
                    $extra_data = array(
                                    "extra_name" => $extra['extra_name'],
                                    "extra_type" => $extra['extra_type'],
                                    "charge_type_id" => $this->Charge_type_model->get_default_charge_type_id($company_id),
                                    "charging_scheme" => $extra['charging_scheme']
                                    );
                    $extra_id = $this->Extra_model->create_extra($extra_data);
                    $this->Booking_extra_model->create_booking_extra(
                        $booking_id, 
                        $extra_id,
                        $extra['start_date'],
                        $extra['end_date'],
                        $extra['quantity'],
                        $extra['rate']);

                }
            }

            $company = $this->Company_model->get_company_data($company_id);

            $log_data = array(
                'selling_date' => $company['selling_date'],
                'user_id' => 2, //User_id 2 is Online Reservation
                'booking_id' => $booking_id,                  
                'date_time' => gmdate('Y-m-d H:i:s'),
                'log' => 'OTA Booking modified',
                'log_type' => SYSTEM_LOG
            );

            $this->Booking_log_model->insert_log($log_data);
            
            $this->Booking_model->update_booking_balance($booking_id);
            
            return $booking_id;
        }
        catch(Exception $e){
            return $booking_id ? $booking_id : $e->getMessage();
        }
    }

    function get_non_continuous_available_room_ids($booking_id, $room_type_id, $company_id, $check_in_date, $check_out_date, $subscription_level = null)
    {
        $rooms_info = $this->Room_model->get_rooms_by_room_type_id($room_type_id, $subscription_level);
        $all_room_data = array();
        $check_out_date = date("Y-m-d", strtotime("-1 day", strtotime($check_out_date)));
        if(count($rooms_info) > 0)
        {
            foreach($rooms_info as $room_info){
                $all_room_data[] = $this->Room_model->get_availability_for_room_by_date_range($check_in_date, $check_out_date, $room_type_id, $room_info->room_id);
            }
        }

        $free_rooms_ar = array();
        foreach($all_room_data as $key1 => $room_data)
        {       
            if(!empty($room_data))
            {                    
                foreach($room_data as $key => $room)
                {
                    $free_rooms_ar[$key][] = $room;
                }  
            }
        }
        // check all date range is exist in array or not 
        $start_timestamp = strtotime($check_in_date);
        $end_timestamp   = strtotime($check_out_date);
        while ($start_timestamp <= $end_timestamp) {
            if(!array_key_exists(date("Y-m-d", $start_timestamp), $free_rooms_ar))
            {
                return false;
            }
            else{

                $sorted_free_rooms_ar[date("Y-m-d", $start_timestamp)] = $free_rooms_ar[date("Y-m-d", $start_timestamp)];
            }
            $start_timestamp = strtotime('+1 day', $start_timestamp);
        }
        
        return $this->get_non_continuous_blocks($sorted_free_rooms_ar, $booking_id, $company_id);
    }
    
    function get_non_continuous_blocks($available_rooms, $booking_id, $company_id)
    {
        $last_date = null;
        $array_merge = array();
        $flag = $group = 0;
 
        foreach($available_rooms as $date => $available_room)
        {
            if($last_date)
            {
                $group++;
                $common_room_ar = array_intersect($available_room, $available_rooms[$last_date]);
                
                if(count($common_room_ar) > 0)
                {
                    $actual_date = $date;
                    $separator = "_";
                    
                    if(strpos($date, "_") > 0)
                    {
                        $date_explode_ar = explode("_", $actual_date);
                        $actual_date = end($date_explode_ar);
                    }
                    elseif(strpos($last_date, $date))
                    {
                        $actual_date = $separator = "" ;
                    }
            
                    $array_merge[$last_date.$separator.$actual_date] = $common_room_ar;
                    
                }
                else
                {  
                    $flag++;
                    if($flag == $group  )
                    {
                        $array_merge[$last_date] = $available_rooms[$last_date]; 
                    }
                    else
                    {
                        $array_merge[$date] = $available_room;
                    }
                    if( ($available_room == end($available_rooms)) && ($flag == count($available_rooms) -1 ))  // for last element of array
                    { 
                       $array_merge[$date] = $available_room;
                    }
                } 
            }
            $last_date = $date;
        }
        if($flag == (count($available_rooms) - 1))  // recursion termination condition
        {          
            $free_rooms_ar = array();
            foreach($array_merge as $date => $array_data) // remove overlap dates in array 
            {     
                $dates_array =  explode("_", $date); // extract  
                foreach($dates_array as $date_arr)
                {  
                    if(array_key_exists($date_arr, $free_rooms_ar))
                    {
                        continue;
                    }
                    $free_rooms_ar[$date_arr] = current($array_data);
                }
            }
            $available_rooms = array(); // create final available rooms_ar
         
            $block_number = 1;
            $last_free_room_id = null;
            foreach($free_rooms_ar as $date => $free_room_id)
            {               
                if($last_free_room_id != $free_room_id){
                    $block_number++;
                }
                if(!isset($available_rooms[$free_room_id."_".$block_number])) {
                    $available_rooms[$free_room_id."_".$block_number] = array();
                }
                $available_rooms[$free_room_id."_".$block_number][] = $date;
                
                $last_free_room_id = $free_room_id;
            }
                      
            //    create booking room history data for available rooms
            if(count($available_rooms) > 0)
            {
                $company_data = $this->Company_model->get_company_data($company_id);
                if(isset($company_data['allow_non_continuous_bookings']) && $company_data['allow_non_continuous_bookings'] && isset($company_data['maximum_no_of_blocks']) && $company_data['maximum_no_of_blocks'] >= count($available_rooms))
                {
                    foreach($available_rooms as $available_room_id => $dates_array)
                    {
                        $check_in_date = current($dates_array);
                        $check_out_date = end($dates_array);
                        $check_out_date = date("Y-m-d", strtotime("+1 day", strtotime($check_out_date))); // check out date will be +1 day

                        $booking_room_history_data = Array(
                                                        'room_id' => $available_room_id,
                                                        'check_in_date' => $check_in_date,
                                                        'check_out_date' => $check_out_date,
                                                        'booking_id' => $booking_id
                                                    );

                        $this->Booking_room_history_model->create_booking_room_history($booking_room_history_data);
                    }
                    return true;
                }
            }
        }
        else
        {
            return $this->get_non_continuous_blocks($array_merge, $booking_id, $company_id);
        }
        return false;
    }

    public function get_tokenex_token_and_encrypted_cvc($card_number = null, $cvc = null, $company_id = null)
    { 
        $selected_gateway = null;
        if($company_id){
            $this->load->library('session');// dependency
            $this->load->library('PaymentGateway', array(
                'company_id' => $company_id
            ));

            $selected_gateway = $this->paymentgateway->getSelectedGateway();
        }
        
        if($selected_gateway && $card_number)
        {
            $data["Data"] = $card_number;
            $this->load->library('Tokenex');
            $result = $this->tokenex->tokenize($data); // get tokenex token
          
            if(isset($result["token"]) && $result["token"])
            {
                $cvc_encrypted = null;
                
                if($cvc)
                    $cvc_encrypted = $this->get_cc_cvc_encrypted($cvc, $result["token"]); // get cvc encrypted
                
                if (ob_get_contents()) ob_end_clean();
                
                return array(
                            "token_ex_token" => $result["token"],
                            "cvc_encrypted"  => $cvc_encrypted
                        );
            }
            else
               return null;
        }
       return null;
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

    public function get_booking_detail_post()
    {       
        $company_id = $this->post('company_id');

        if(!$company_id) {
            $this->response(array('status' => false, 'error' => 'Company ID is missing.'), 200);
        }

        $bookings = $this->Booking_model->get_recent_bookings($company_id);
        $common_booking_sources = json_decode(COMMON_BOOKING_SOURCES, true);

        foreach($bookings as $key => $booking) {
            foreach($common_booking_sources as $id => $name) {
                if(intval($booking['booking_source']) && $booking['booking_source'] == $id) {
                    $bookings[$key]['booking_source'] = $name;
                    break;
                } 
            }
        }

        $common_customer_types = json_decode(COMMON_CUSTOMER_TYPES, true);

        foreach($bookings as $key => $booking) {
            foreach($common_customer_types as $id => $name) {
                if(intval($booking['customer_type']) && $booking['customer_type'] == $id) {
                    $bookings[$key]['customer_type'] = $name;
                    break;
                } 
            }
        }

        $this->response($bookings, 200);
    }

    public function get_booking_charges_post()
    {       
        $booking_id = $this->post('booking_id');
        $company_id = $this->post('company_id');

        if(!$booking_id) {
            $this->response(array('status' => false, 'error' => 'Booking ID is missing.'), 200);
        }

        $charges = $this->Charge_model->get_charges($booking_id, false, $company_id);
        $this->response($charges, 200);
    }

    public function add_booking_charges_post()
    {       
        $charges = $this->post('charges');
        $company_id = $this->post('company_id');

        if(empty($charges)) {
            $this->response(array('status' => false, 'error' => 'Charge object is missing.'), 200);
        }

        $charge_data = array();
        foreach($charges as $key => $charge){
            $charge_data[$key]['selling_date'] = date('Y-m-d', strtotime($charge['selling_date']));
            $charge_data[$key]['booking_id'] = $charge['booking_id'];
            $charge_data[$key]['description'] = $charge['description'];
            $charge_data[$key]['amount'] = $charge['amount'];
            $charge_data[$key]['charge_type_id'] = $charge['charge_type_id'];
        }

        $this->Charge_model->insert_charges($charge_data);
            
        $this->response(array('status' => 'success'), 200);
    }

    public function get_booking_payments_post()
    {       
        $booking_id = $this->post('booking_id');
        $company_id = $this->post('company_id');

        if(!$booking_id) {
            $this->response(array('status' => false, 'error' => 'Booking ID is missing.'), 200);
        }

        $payments = $this->Payment_model->get_payments($booking_id, false);

        if(empty($payments)) {
            $this->response(array('data' => 'No payment made.'), 200);
        }

        $this->response($payments, 200);
    }

    function show_booking_detail_post()
    {
        $company_id = $this->post('company_id');
        $booking_id = $this->post('pms_booking_id');
        $ota_booking_id = $this->post('ota_booking_id');

        if(!$company_id) {
            $this->response(array('status' => false, 'error' => 'Company ID is missing.'), 200);
        }

        $config['per_page'] = 30;
        $config['uri_segment'] = 3;

        $filters['per_page'] = $config['per_page'];

        if($booking_id)
            $filters['booking_id'] = $booking_id;

        if($ota_booking_id)
            $filters['ota_booking_id'] = $ota_booking_id;

        $bookings = $this->Booking_model->get_bookings($filters, $company_id);

        if($bookings && count($bookings) > 0)
            $this->response($bookings, 200);
        else
            $this->response(array('status' => false, 'error' => 'No bookings found.'), 200);

    }

    function show_bookings_post()
    {
        $company_id = $this->post('company_id');

        if(!$company_id) {
            $this->response(array('status' => false, 'error' => 'Company ID is missing.'), 200);
        }

        $config['per_page'] = 30;
        $config['uri_segment'] = 3;

        $filters['per_page'] = $config['per_page'];

        $bookings = $this->Booking_model->get_bookings($filters, $company_id);

        if($bookings && count($bookings) > 0)
            $this->response($bookings, 200);
        else
            $this->response(array('status' => false, 'error' => 'No bookings found.'), 200);

    }

    function check_room_type_availability_post()
    {
        $company_id = $this->post('company_id');
        $check_in_date = $this->post('start_date');
        $check_out_date = $this->post('end_date');
        $adult_count = $this->post('adult_count');
        $children_count = $this->post('children_count');
        $is_ajax_wp = $this->post('is_ajax_wp');

        $inventory = array();

        $company_data = $this->Company_model->get_company_data($company_id);
        if (is_null($company_data)) {
            $this->response(array('status' => false, 'error' => "Company doesn't exist"), 200);
        }

        $length_of_stay = floor((abs(strtotime($check_out_date) - strtotime($check_in_date))) / (60 * 60 * 24));

        $room_types = $this->Room_type_model->get_room_types_data($company_id, $adult_count, $children_count);
        if(count($room_types)!=0){
            foreach ($room_types as $room_type)
            {
                $inventory[$room_type['id']] = $room_type;
            }
        }

        $company_key_data = $this->Company_model->get_company_api_permission($company_id);
        $company_access_key = isset($company_key_data[0]['key']) && $company_key_data[0]['key'] ? $company_key_data[0]['key'] : null;

        $ota_id = $this->Company_model->get_ota_id('obe');
        $ota_id = $ota_id ? $ota_id : SOURCE_ONLINE_WIDGET;

        //$available_room_types = $this->Room_type_model->get_room_type_availability($company_id, $ota_id, $check_in_date, $check_out_date, $adult_count, $children_count, true, null, true, true, true, true, $company_access_key, 'obe');

        $avail_array = $this->Availability_model->get_availability($check_in_date, $check_out_date, array_keys($inventory), $ota_id, true, $adult_count, $children_count, true, true, true, true, false, $company_id);

        foreach ($avail_array as $room_type_id => $room_type_availability) {
            $inventory[$room_type_id]['availability'] = $room_type_availability;
        }

        $available_room_types = $inventory;

        $available_rate_plans = $rooms_available = $unavailable_room_types = array();
            // prx($available_room_types);
        foreach ($available_room_types as $key => $available_room_type) {
            unset($available_room_types[$key]['description']);
            unset($available_room_type['description']);

            $minimum_avaialbility_of_current_room_type = 999;
            if (isset($available_room_type['availability']) && $available_room_type['availability'] && count($available_room_type['availability']) > 0) {
                foreach ($available_room_type['availability'] as $availability)
                {
                    if (
                        ($minimum_avaialbility_of_current_room_type > $availability['availability']) &&
                        ($availability['date_start'] != $availability['date_end'])
                    )
                    {
                        $minimum_avaialbility_of_current_room_type = $availability['availability'];
                    }
                }
            } else {
                $minimum_avaialbility_of_current_room_type = 0;
            }

            $number_of_rooms_requested = 1;
            if ($minimum_avaialbility_of_current_room_type < $number_of_rooms_requested)
            {
                // continue;
                $unavailable_room_types[] = array ('id' => $available_room_type['id']);
            }

            $rate_plans = $this->Rate_plan_model->get_rate_plans_by_room_type_id($available_room_type['id']);

            if (isset($rate_plans)) {

                foreach ($rate_plans as $rate_plan) {
                    if ($rate_plan['is_shown_in_online_booking_engine'] == '1')
                    {

                        $rates = $this->Rate_model->get_daily_rates($rate_plan['rate_plan_id'], $check_in_date, $check_out_date);

                        $passed_all_restrictions = true;

                        foreach ($rates as $rate) {
                            if ($rate['can_be_sold_online'] != '1') {
                                $passed_all_restrictions = false;
                            }

                            if ($rate['minimum_length_of_stay'] > $length_of_stay && isset($rate['minimum_length_of_stay'])) {
                                $rate_plan['min_length']="This room requires minimum ".$rate['minimum_length_of_stay']." nights of stay";
                            }

                            if ($rate['maximum_length_of_stay'] < $length_of_stay && isset($rate['maximum_length_of_stay'])) {
                                $rate_plan['max_length']="This room requires maximum ".$rate['maximum_length_of_stay']." nights of stay";
                            }

                            if (
                                $rate['date'] == $check_in_date &&
                                $rate['closed_to_arrival'] == '1'
                            ) {
                                $rate_plan['arrival']="please enable close to arrival on selected date";
                            }
                        }

                        $checkout_date = date('Y-m-d', strtotime($check_out_date . " + 1 day"));

                        $rates = $this->Rate_model->get_daily_rates($rate_plan['rate_plan_id'], $check_in_date, $checkout_date);

                        foreach ($rates as $rate) {
                            if (
                                $rate['date'] == $check_out_date &&
                                $rate['closed_to_departure'] == '1'
                            ) {
                                $rate_plan['departure']="please enable close to departure on selected date";
                            }
                        }

                        if ($passed_all_restrictions) {
                            $rate_plan['room_type_image_group_id']    = $available_room_type['image_group_id'];

                            unset($rate_plan['description']);

                            $rate_plan['max_adults'] = $available_room_type['max_adults'];
                            $available_rate_plans[] = $rate_plan;
                        }
                    }
                }
            }
        }

        ksort($available_rate_plans);

        $date_start     = $check_in_date;
        $date_end       = $check_out_date;
        //Calculate default rates
        $is_available_rate_plan = $is_available_room = false;
        $best_available_rate = -1;

        $rate_plan_ids = array();
        // fetch rate plan description $rate_plan_ids

        foreach ($available_rate_plans as $key => $rate_plan) {

            $this->load->library('rate');
            $rate_array = $this->rate->get_rate_array(
                $rate_plan['rate_plan_id'],
                $date_start,
                $date_end,
                $adult_count,
                $children_count
            );

            $average_daily_rate = $this->rate->get_average_daily_rate($rate_array);
            $available_rate_plans[$key]['average_daily_rate'] = $average_daily_rate;
            if ($best_available_rate == -1 || $average_daily_rate < $best_available_rate) {
                $best_available_rate = $average_daily_rate;
            }
            $rate_plan_ids[] = $rate_plan['rate_plan_id'];
            if($average_daily_rate > 0 || ($company_data['allow_free_bookings'] && (!$rate_plan['charge_type_id'] || $rate_plan['charge_type_id'] == '0')))
            {
                $is_available_rate_plan = true;
            }

            if($this->Room_model->get_available_rooms(
                $check_in_date,
                $check_out_date,
                $rate_plan['room_type_id'],
                null,
                $company_id,
                1
            ))
            {
                $is_available_room = true;
            }
        }

        $data['view_data']['best_available_rate'] = number_format($best_available_rate, 2, ".", ",");

        $data['view_data']['default_currency'] = $this->Currency_model->get_default_currency($company_id);

        $data['view_data']['check_in_date']             = $check_in_date;
        $data['view_data']['check_out_date']            = $check_out_date;
        $data['view_data']['adult_count']               = $adult_count;
        $data['view_data']['children_count']            = $children_count;
        $data['view_data']['available_rate_plans']      = $available_rate_plans;

        $data['view_data']['unavailable_room_types']    = $unavailable_room_types;

        // $_SESSION['data'] = $data;
        // $this->session->set_userdata($data);

        $data['company_data'] = $company_data;

        // set session variables
        unset($data['company_data']['reservation_policies']);
        unset($data['company_data']['check_in_policies']);
        unset($data['company_data']['invoice_email_header']);
        unset($data['company_data']['booking_confirmation_email_header']);

        $descriptions = $this->Rate_plan_model->get_rate_plan_descriptions($rate_plan_ids);
        foreach($data['view_data']['available_rate_plans'] as $key => $rate_plan)
        {
            $data['view_data']['available_rate_plans'][$key]['description'] = isset($descriptions[$rate_plan['rate_plan_id']]) ? $descriptions[$rate_plan['rate_plan_id']] : "";
        }

        foreach($data['view_data']['available_rate_plans'] as $key => $rate_plan)
        {
            $room_type_images = $this->Image_model->get_images($rate_plan['room_type_image_group_id']);

            if($is_ajax_wp) {
                $data['view_data']['available_rate_plans'][$key]['images'] = $room_type_images;
                $data['view_data']['available_rate_plans'][$key]['image_url'] = $this->image_url;
            } else {
                $data['view_data']['available_rate_plans'][$key]['images'] = $room_type_images;
            }
        }

        if($is_available_rate_plan && $is_available_room)
        {
            $this->response(array('status' => true, 'company_id' => $company_id, 'data' => $data), 200);
        }
        else
        {
            $this->response(array('status' => false, 'msg' => 'No rooms available on the selected dates. Please try changing the dates.'), 200);
        }
    }

    function booking_engine_charge_calculation_post(){
        
        $data['view_data'] = $this->post('data');
        $rate_plan_id = $this->post('rate_plan_id');
        unset($data['view_data']['ID']);
        unset($data['view_data']['filter']);
        $data['view_data']['number_of_nights'] = (strtotime($data['view_data']['check_out_date']) - strtotime($data['view_data']['check_in_date'])) / (60 * 60 * 24);

        $sub_total = 0;

        foreach ($data['view_data']['available_rate_plans'] as $available_rate_plan) {
            if ($rate_plan_id == $available_rate_plan['rate_plan_id']) {
                $rate_plan = $available_rate_plan;
                $sub_total += $available_rate_plan['average_daily_rate'] * $data['view_data']['number_of_nights'];
            }
        }

        $total_tax_percentage            = $this->Tax_model->get_total_tax_percentage_by_charge_type_id($rate_plan['charge_type_id']);
        $total_flat_rate_tax             = $this->Tax_model->get_total_tax_flat_rate_by_charge_type_id($rate_plan['charge_type_id']) * $data['view_data']['number_of_nights'];
        $data['view_data']['sub_total']  = $sub_total;
        $data['view_data']['tax_amount'] = $tax_amount = ($sub_total * $total_tax_percentage * 0.01) + $total_flat_rate_tax;
        $data['view_data']['total']      = $data['view_data']['sub_total'] + $data['view_data']['tax_amount'];

        $this->response(array('status' => true, 'data' => $data), 200);
    }

    function create_booking_post(){
        $customer_data = $this->post('form_data');
        $company_id = $this->post('company_id');
        $rate_plan_id = $this->post('rate_plan_id');
        $view_data = $this->post('view_data');
        $company_data = $this->post('company_data');
        $average_daily_rate = $this->post('average_daily_rate');
        $send_confirmation_email = $this->post('send_confirmation_email');
        $booking_engine_booking_status = $this->post('booking_engine_booking_status');
        
        $public_url = $this->post('public_url');

        $special_requests = $customer_data['special_requests'];
        unset($customer_data['special_requests']);
        unset($customer_data['ID']);
        unset($customer_data['filter']);
        unset($view_data['ID']);
        unset($view_data['filter']);
        unset($company_data['ID']);
        unset($company_data['filter']);

        $selected_rate_plans = array();

        foreach ($view_data['available_rate_plans'] as $key => $value) {
            if($value['rate_plan_id'] == $rate_plan_id)
                $selected_rate_plans[] = $value;
        }

        $selected_rooms = $this->_select_rooms_for_booking(
            $view_data['check_in_date'],
            $view_data['check_out_date'],
            $company_id,
            $selected_rate_plans
        );

        if (!is_null($selected_rooms))
        {
            $customer_data['company_id']    = $company_id;
            $cc_number = $cc_expiry = $cvc = null;

            if(isset($customer_data['cc_number']) && $customer_data['cc_number']){
                $cc_number = $customer_data['cc_number'];
                $cc_expiry = $customer_data['cc_expiry'];
                $cvc = $customer_data['cc_cvc'];

                unset($customer_data['cc_number']);
                unset($customer_data['cc_expiry']);
                unset($customer_data['cc_cvc']);
            }

            if ($customer_id = $this->Customer_model->get_customer_id_by_email($customer_data['email'], $company_id)) {
                $customer_data['customer_id'] = $customer_id;
                $this->Customer_model->update_customer($customer_data);
            } else {
                $customer_id = $this->Customer_model->create_customer($customer_data);
            }

            if($cc_number && $cvc && $cc_expiry){

                $cc_expiry = explode(' / ', $cc_expiry);
                $customer_data['cc_expiry_month'] = $cc_expiry_month = $cc_expiry[0] ?? null;
                $customer_data['cc_expiry_year'] = $cc_expiry_year = $cc_expiry[1] ?? null;

                $card_details = array(
                        'is_primary' => 1,
                        'customer_id' => $customer_id,
                        'customer_name' => $customer_data['customer_name'],
                        'card_name' => '',
                        'company_id' => $customer_data['company_id'],
                        'cc_expiry_month' => $cc_expiry_month,
                        'cc_expiry_year' => $cc_expiry_year,
                        'cc_tokenex_token' => null
                    );


                if(
                    $cc_number && 
                    is_numeric($cc_number) &&
                    !strrpos($cc_number, 'X') && 
                    $cvc && 
                    is_numeric($cvc) &&
                    !strrpos($cvc, '*')
                )
                {
                    $card_data_array = array('card' =>
                        array(
                            'card_number'       => $cc_number,
                            'card_type'         => "",
                            'cardholder_name'   => (isset($customer_data['customer_name']) ? $customer_data['customer_name'] : ""),
                            'service_code'      => $cvc,
                            'expiration_month'  => isset($customer_data['cc_expiry_month']) ? $customer_data['cc_expiry_month'] : null,
                            'expiration_year'   => isset($customer_data['cc_expiry_year']) ? $customer_data['cc_expiry_year'] : null
                        )
                    );
                    $card_response = array();

                    if($card_data_array && $card_data_array['card']['card_number']) {

                        $customer_data['customer_id'] = $customer_id;
                        $card_data_array['customer_data'] = $customer_data;

                        $api_url = $public_url;
                        $method = '/customer/post_add_customer_callback/'.$company_id;
                        $method_type = 'POST';
                        $data = $card_data_array;

                        $headers = array(
                            "Content-Type: application/json"
                        );

                        $card_response = $this->call_api($api_url, $method, $data, $headers, $method_type);

                        $card_response = json_decode($card_response, true);
                        $card_response = $card_response['resp'];
                        unset($card_data_array['customer_data']);
                    }

                    if(
                        $card_response &&
                        isset($card_response['tokenization_response']["data"]) &&
                        isset($card_response['tokenization_response']["data"]["attributes"]) &&
                        isset($card_response['tokenization_response']["data"]["attributes"]["card_token"])
                    ){
                        $card_token = $card_response['tokenization_response']["data"]["attributes"]["card_token"];

                        $cvc_encrypted = $this->get_cc_cvc_encrypted($cvc, $card_token);

                        $card_details['cc_cvc_encrypted'] = ($cvc_encrypted) ? $cvc_encrypted : "";
                        $card_details['cc_number'] = 'XXXX XXXX XXXX '.substr($cc_number,-4);

                        $meta['token'] = $card_token;
                        $card_details['customer_meta_data'] = json_encode($meta);
                    }
                }

                $customer_data['cc_number'] = "";
                $customer_data['cc_expiry_month'] = "";
                $customer_data['cc_expiry_year'] = "";
                $customer_data['cc_tokenex_token'] = "";
                $customer_data['cc_cvc_encrypted'] = "";

                $check_data = $this->Card_model->get_customer_primary_card($customer_id);
        
                if(empty($check_data)){
                    $customer_data['customer_id'] = $customer_id;
                    $this->Customer_model->update_customer($customer_data);
                    if($cc_number){
                        $this->Card_model->create_customer_card_info($card_details);
                    }
                }
            }

            //Create Booking(s)
            $bookings = array();

            $common_booking_sources = json_decode(COMMON_BOOKING_SOURCES, true);
            
            $booking['source'] = SOURCE_ONLINE_WIDGET;
            $booking['sub_source'] = 'WP Booking Engine';

            $source_id = $booking['source'];
            $is_new_source = null;
            if($booking['source'] == SOURCE_ONLINE_WIDGET){
                
                $source = isset($booking['sub_source']) && $booking['sub_source'] ? $booking['sub_source'] : "";
                $parent_source = "Expedia";
                if($source) {
                    $is_new_source = true;
                    if(strcmp($parent_source, trim($source)) == 0) {
                        $source_id = SOURCE_ONLINE_WIDGET;
                        $is_new_source = false;
                    } else {
                        $source_ids = $this->Booking_model->get_booking_source_detail($company_id);
                        if($source_ids){
                            foreach($source_ids as $ids){
                                if(strcmp($ids['name'], $source) == 0)
                                {   
                                    $source_id = $ids['id'];
                                    $is_new_source = false;
                                }
                            }
                        }
                    }
                }
            }

            if($is_new_source) {
                $source_id = $this->Booking_model->create_booking_source($company_id, $source);
            }

            foreach ($selected_rooms as $selected_room_index => $selected_room)
            {
                $booking_data['rate_plan_id']  = $rate_plan_id;
                $booking_data['use_rate_plan'] = 1;
                
                $booking_data['rate']    = number_format($average_daily_rate, 2, ".", ",");

                $booking_data['adult_count']    = $view_data['adult_count'];
                $booking_data['children_count'] = $view_data['children_count'];

                $booking_data['state']               = $booking_engine_booking_status ? RESERVATION : UNCONFIRMED_RESERVATION;
                $booking_data['source']              = $source_id;
                $booking_data['company_id']          = $company_id;
                $booking_data['booking_customer_id'] = $customer_id;
                $booking_data['booking_notes']       = $special_requests;

                $booking_id = $this->Booking_model->create_booking($booking_data);
                $bookings[] = $booking_id;

                $booking_history               = array();
                $booking_history['booking_id'] = $booking_id;

                $booking_history['room_id'] = $selected_room['room_id'];

                $booking_history['check_in_date']  = $company_data['enable_new_calendar'] ? $view_data['check_in_date'].' '.date("H:i:s", strtotime($company_data['default_checkin_time'])) : $view_data['check_in_date'];

                $booking_history['check_out_date'] = $company_data['enable_new_calendar'] ? $view_data['check_out_date'].' '.date("H:i:s", strtotime($company_data['default_checkout_time'])) : $view_data['check_out_date'];

                $this->Booking_room_history_model->create_booking_room_history($booking_history);

                $selling_date = $this->Company_model->get_selling_date($company_id);

                // start create new rate plan with booking id

                if(isset($booking_data['use_rate_plan']) && $booking_data['use_rate_plan'])
                {
                    $start_date = date('Y-m-d', strtotime($booking_history['check_in_date']));
                    $end_date = date('Y-m-d', strtotime($booking_history['check_out_date']));

                    $this->load->library('rate');
                    $raw_rate_array = $this->rate->get_rate_array($booking_data['rate_plan_id'], $start_date, $end_date, $booking_data['adult_count'], $booking_data['children_count']);

                    $rate_array = array();
                    foreach ($raw_rate_array as $rate)
                    {
                        $rate_array[] = array(
                            'date' => $rate['date'],
                            'base_rate' => $rate['base_rate'],
                            'adult_1_rate' => $rate['adult_1_rate'],
                            'adult_2_rate' => $rate['adult_2_rate'],
                            'adult_3_rate' => $rate['adult_3_rate'],
                            'adult_4_rate' => $rate['adult_4_rate'],
                            'additional_adult_rate' => $rate['additional_adult_rate'],
                            'additional_child_rate' => $rate['additional_child_rate'],
                            'minimum_length_of_stay' => $rate['minimum_length_of_stay'],
                            'maximum_length_of_stay' => $rate['maximum_length_of_stay'],
                            'minimum_length_of_stay_arrival' => $rate['minimum_length_of_stay_arrival'],
                            'maximum_length_of_stay_arrival' => $rate['maximum_length_of_stay_arrival']
                        );
                    }

                    $curreny_data = $this->Currency_model->get_default_currency($company_id);
                    $rate_plan_data = $this->Rate_plan_model->get_rate_plan($booking_data['rate_plan_id']);

                    $new_rate_plan = array(
                        "rate_plan_name" => $rate_plan_data['rate_plan_name']." #".$booking_id,
                        "number_of_adults_included_for_base_rate" => $booking_data['adult_count'],
                        "rates" => $this->get_array_with_range_of_dates($rate_array, SOURCE_ONLINE_WIDGET),
                        "currency_id" => $curreny_data['currency_id'],
                        "charge_type_id" => $rate_plan_data['charge_type_id'],
                        "company_id" => $company_id,
                        "is_selectable" => '0',
                        "room_type_id" => $rate_plan_data['room_type_id'],
                        "parent_rate_plan_id" => $booking_data['rate_plan_id']
                    );

                    // create rates
                    $rates = $new_rate_plan['rates'];
                    unset($new_rate_plan['rates']);

                    // create rate plan
                    $rate_plan_id = $this->Rate_plan_model->create_rate_plan($new_rate_plan);
                    $this->Booking_model->update_booking(array('rate_plan_id' => $rate_plan_id), $booking_id);
                    
                    foreach ($rates as $rate)
                    {
                        $rate_id = $this->Rate_model->create_rate(
                            Array(
                                'rate_plan_id' => $rate_plan_id,
                                'base_rate' => $rate['base_rate'],
                                'adult_1_rate' => $rate['adult_1_rate'],
                                'adult_2_rate' => $rate['adult_2_rate'],
                                'adult_3_rate' => $rate['adult_3_rate'],
                                'adult_4_rate' => $rate['adult_4_rate'],
                                'additional_adult_rate' => $rate['additional_adult_rate'],
                                'additional_child_rate' => $rate['additional_child_rate'],
                                'minimum_length_of_stay' => $rate['minimum_length_of_stay'],
                                'maximum_length_of_stay' => $rate['maximum_length_of_stay'],
                                'minimum_length_of_stay_arrival' => $rate['minimum_length_of_stay_arrival'],
                                'maximum_length_of_stay_arrival' => $rate['maximum_length_of_stay_arrival']
                            )
                        );

                        $date_range_id = $this->Date_range_model->create_date_range(
                            Array(
                                'date_start' => $rate['date_start'],
                                'date_end' => $rate['date_end']
                            )
                        );

                        $this->Date_range_model->create_date_range_x_rate(
                            Array(
                                'rate_id' => $rate_id,
                                'date_range_id' => $date_range_id
                            )
                        );
                    }
                }

                // end create new rate plan with booking id

                $this->Booking_model->update_booking_balance($booking_id);

                $log_data['selling_date'] = $selling_date;
                $log_data['user_id']      = 0; //User_id 2 is Online Reservation
                $log_data['booking_id']   = $booking_id;
                $log_data['date_time']    = gmdate('Y-m-d H:i:s');
                $log_data['log']          = 'Online reservation submitted';
                $log_data['log_type']     = SYSTEM_LOG;

                $this->Booking_log_model->insert_log($log_data);

                //Create a corresponding invoice
                $this->Invoice_model->create_invoice($booking_id);
            }

            $room_type = $this->Room_type_model->get_room_type_by_room_id($booking_history['room_id']);

            // send booking confirmation email
            $this->load->library('email_template');
            if($send_confirmation_email)
            {
                
                $result_array = $this->email_template->send_booking_confirmation_email($booking_id);
                if ($result_array && $result_array['success'])
                {
                    $log_data = array();
                    $log_data['selling_date'] = $this->Company_model->get_selling_date($company_id);
                    $log_data['user_id']      = 0; //User_id 2 is Online Reservation
                    $log_data['booking_id']   = $booking_id;
                    $log_data['date_time']    = gmdate('Y-m-d H:i:s');
                    $log_data['log']          = 'Automatic Confirmation Email Sent to '.$result_array['customer_email'];
                    $log_data['log_type']     = SYSTEM_LOG;
                    $this->Booking_log_model->insert_log($log_data);
                }
            }

            // send booking alert email to hotel owner
            $this->email_template->send_booking_alert_email($booking_id);

            $this->response(array('status' => true, 'booking_id' => $booking_id), 200);
        } else {
            $this->response(array('status' => false, 'msg' => "We're sorry. The rooms you selected are no longer available. Please start over and select new rooms."), 200);
        }

        
    }

    function _select_rooms_for_booking($check_in_date, $check_out_date, $company_id, $selected_rate_plans)
    {

        $rooms_available = $this->Room_model->get_available_rooms(
            $check_in_date,
            $check_out_date,
            null,
            null,
            $company_id,
            1
        );

        // Rooms selected while avoiding using blocks that are already occupied by unconfirmed reservations
        $rooms_available_avoiding_unconfirmed_reservations = array();

        // Rooms selected while ignoring, (and potentially overlapping )blocks that are already occupied by unconfirmed reservations
        $rooms_available_ignoring_unconfirmed_reservations = array();
        foreach ($selected_rate_plans as $selected_rate_plan) {
            foreach ($rooms_available as $key => $room_available) {
                // among the available rooms, divide them by ones containing unconfirmed reservations and ones that don't
                if ($selected_rate_plan['room_type_id'] == $room_available['room_type_id']) {
                    // if there is reservation, inhouse, or checkout existing between the given dates,
                    if ($this->Booking_room_history_model->check_if_booking_exists_between_two_dates(
                        $room_available['room_id'],
                        $check_in_date,
                        $check_out_date,
                        'undefined',
                        false // do not consider_unconfirmed_reservations
                    )
                    ) {
                        $rooms_available_ignoring_unconfirmed_reservations[] = $room_available;
                    } else {
                        $rooms_available_avoiding_unconfirmed_reservations[] = $room_available;
                    }
                }
            }
        }

        // We don't have to worry about group booking online reservation yet. So we will just pick the first room available.
        // Preferably, we will choose a room that is avoiding using blocks that are already occupied by unconfirmed reservations
        if (sizeof($rooms_available_avoiding_unconfirmed_reservations) > 0) {
            $rooms_selected = $rooms_available_avoiding_unconfirmed_reservations;
        } else {
            if (sizeof($rooms_available_ignoring_unconfirmed_reservations) > 0) {
                $rooms_selected = $rooms_available_ignoring_unconfirmed_reservations;
            }else{
                return null;
            }
        }

        return array('0' => $rooms_selected[0]);
    }

    function get_customer_info_form_post()
    {
        $company_id = $this->post('company_id');

        $common_booking_engine_fields = json_decode(COMMON_BOOKING_ENGINE_FIELDS, true);
        $get_common_booking_engine_fields = $this->Company_model->get_common_booking_engine_fields($company_id);

        $booking_engine_fields = array();

        $booking_form = '';

        foreach($common_booking_engine_fields as $id => $name)
        {
            $is_required = 1;
            if ($id == BOOKING_FIELD_NAME) {
                $is_required = 1;
            } else if ($get_common_booking_engine_fields && isset($get_common_booking_engine_fields[$id]) && isset($get_common_booking_engine_fields[$id]['is_required'])) {
                $is_required = $get_common_booking_engine_fields[$id]['is_required'];
            } else if ($id == BOOKING_FIELD_POSTAL_CODE || $id == BOOKING_FIELD_SPECIAL_REQUEST) {
                $is_required = 0;
            }

            $booking_engine_fields[] = array(
                'id' => $id,
                'field_name' => $name,
                'company_id' => $company_id,
                'show_on_booking_form'=> ($id == BOOKING_FIELD_NAME) ? 1 : (($get_common_booking_engine_fields && isset($get_common_booking_engine_fields[$id]) && isset($get_common_booking_engine_fields[$id]['show_on_booking_form'])) ? $get_common_booking_engine_fields[$id]['show_on_booking_form'] : 1),
                'is_required' => $is_required
            );
        }

        if(count($booking_engine_fields) > 0) 
        {
            foreach ($booking_engine_fields as $key => $value) 
            {
                if($value['id'] == BOOKING_FIELD_NAME){
                    $name = 'customer_name';
                    $is_required = $value['is_required'] ? 'required' : '';
                    $show = $value['show_on_booking_form'] ? '' : 'hidden';
                } else if($value['id'] == BOOKING_FIELD_EMAIL){
                    $name = 'email';
                    $is_required = $value['is_required'] ? 'required' : '';
                    $show = $value['show_on_booking_form'] ? '' : 'hidden';
                } else if($value['id'] == BOOKING_FIELD_PHONE){
                    $name = 'phone';
                    $is_required = $value['is_required'] ? 'required' : '';
                    $show = $value['show_on_booking_form'] ? '' : 'hidden';
                } else if($value['id'] == BOOKING_FIELD_ADDRESS){
                    $name = 'address';
                    $is_required = $value['is_required'] ? 'required' : '';
                    $show = $value['show_on_booking_form'] ? '' : 'hidden';
                } else if($value['id'] == BOOKING_FIELD_CITY){
                    $name = 'city';
                    $is_required = $value['is_required'] ? 'required' : '';
                    $show = $value['show_on_booking_form'] ? '' : 'hidden';
                } else if($value['id'] == BOOKING_FIELD_REGION){
                    $name = 'region';
                    $is_required = $value['is_required'] ? 'required' : '';
                    $show = $value['show_on_booking_form'] ? '' : 'hidden';
                } else if($value['id'] == BOOKING_FIELD_COUNTRY){
                    $name = 'country';
                    $is_required = $value['is_required'] ? 'required' : '';
                    $show = $value['show_on_booking_form'] ? '' : 'hidden';
                } else if($value['id'] == BOOKING_FIELD_POSTAL_CODE){
                    $name = 'postal_code';
                    $is_required = $value['is_required'] ? 'required' : '';
                    $show = $value['show_on_booking_form'] ? '' : 'hidden';
                } else if($value['id'] == BOOKING_FIELD_SPECIAL_REQUEST){
                    $name = 'special_requests';
                    $is_required = $value['is_required'] ? 'required' : '';
                    $show = $value['show_on_booking_form'] ? '' : 'hidden';
                }


                $field_name = ucfirst($value["field_name"]);

                if($is_required == "required"){
                    $is_required_span = '<span style="color:red;">*</span>';
                } else {
                    $is_required_span = '';
                }

                $data_error = 'Please enter your '.strtolower($field_name);
                
                if($value['id'] == BOOKING_FIELD_SPECIAL_REQUEST){ 

                    $booking_form .= '<div class="form-group '. $show .'">
                                    <label for="customer-name" class="col-sm-3 control-label">'. $field_name . ' ' . $is_required_span . '
                                    </label>
                                    <div class="col-sm-9">
                                        <textarea 
                                            name="'.$name.'"
                                            class="input-form-control form-control"
                                            rows = "5"
                                            data-error="' .$data_error. '"
                                            ' .$is_required. '
                                        ></textarea>
                                        <div class="help-block with-errors"></div>
                                    </div>
                                </div>';
                } else {
                    $booking_form .= '<div class="form-group '. $show .'">
                                    <label for="customer-name" class="col-sm-3 control-label">'. $field_name . ' ' . $is_required_span . '
                                    </label>
                                    <div class="col-sm-9">
                                        <input 
                                            name="'.$name.'"
                                            class="input-form-control form-control"
                                            type="text"
                                            data-error="' .$data_error. '"
                                            ' .$is_required. '
                                            id="'.$value['id'].'"
                                        />
                                        <div id="error_'.$value['id'].'" class="help-block with-errors"></div>
                                    </div>
                                    </div>';
                } 
            } 

            $booking_form .= '<input type="button" id="book_room" value="Book Now" class="btn btn-success btn-lg pull-right"/>';
        }



        //if(isset($booking_engine_fields) && count($booking_engine_fields) > 0){
            $this->response(array('status' => true, 'booking_form' => $booking_form), 200);
        
        // } else {
        //     $this->response(array('status' => false, 'msg' => ""), 200);
        // }
    }

    function call_api($api_url, $method, $data, $headers, $method_type = 'POST'){

        $url = $api_url . $method;
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            
        if($method_type == 'GET'){

        } else {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }
               
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($curl);
        
        curl_close($curl);
        
        return $response;
    }

    // Convert $change's date intervals of changes into a range of dates in the correct format
    function get_array_with_range_of_dates($changes, $ota_id)
    {
        $date_ranges = array();
        switch ($ota_id) {
            case SOURCE_ONLINE_WIDGET: // Minical's Online Booking Engine
                $date_ranges = $this->get_array_with_range_of_dates_iso8601($changes, FALSE);break;
            case SOURCE_BOOKING_DOT_COM: // Booking.com
                $date_ranges = $this->get_array_with_range_of_dates_iso8601($changes, FALSE);break;
            case SOURCE_EXPEDIA: // Expedia
                $date_ranges = $this->get_array_with_range_of_dates_iso8601($changes, TRUE);break;
            case SOURCE_MYALLOCATOR:
                $date_ranges = $this->get_array_with_range_of_dates_iso8601($changes, FALSE);break;
            case SOURCE_AGODA:
                $date_ranges = $this->get_array_with_range_of_dates_iso8601($changes, FALSE);break;
            case SOURCE_SITEMINDER:
                $date_ranges = $this->get_array_with_range_of_dates_iso8601($changes, TRUE);break;
            default:
                $date_ranges = $this->get_array_with_range_of_dates_iso8601($changes, FALSE);break;
        }
        return $date_ranges;
    }

    // Convert $change's date intervals of changes into a range of dates in ISO 8601 format
    function get_array_with_range_of_dates_iso8601($changes, $end_date_inclusive)
    {
        if (!isset($changes))
        {
            return null;
            
        } elseif (sizeof($changes) < 1)
        {
            return null;
        }
        
        $changes_indexed_by_date = array(); 
        $date_start = null;
        $last_change = null;
        foreach ($changes as $change)
        {   
            
            if ($last_change != null)
            {
                $change_detected = false;
                foreach ($change as $key => $value)
                {
                    if ($key != 'date')
                    {
                            // compare the actual number value to 2 decimal digits.
                            $change_in_two_decimal_digits = number_format(floatval($change[$key]), 2, ".", "");
                            $last_change_in_two_decimal_digits = number_format(floatval($last_change[$key]), 2, ".", "");
                            if ($change_in_two_decimal_digits != $last_change_in_two_decimal_digits) 
                            {
                                $change_detected = true;
                            }
                        
                    }
                }
                if (    !$change_detected   &&
                        $change['date'] == Date('Y-m-d', strtotime("+1 day", strtotime($last_change['date'])))
                )
                {
                    $last_change = $change;
                    continue;
                }
            }
            
            if ($date_start == null)
            {
                $date_start = $change['date'];
            }
            else
            {
                $changes_indexed_by_date[] = array('date_start'=>$date_start, 'date_end'=> $change['date']) + $last_change;
                $date_start = $change['date'];
                
            }
            $last_change = $change;     
            
        }
        $changes_indexed_by_date[] = array('date_start'=>$date_start, 'date_end'=> $last_change['date'])+$last_change ;

        return $changes_indexed_by_date;    
    }

    function get_company_data_post()
    {
        $company_id = $this->post('company_id');
        $company_data = $this->Company_model->get_company_data($company_id);
        
        $this->response($company_data, 200); // 200 being the HTTP response code
    }

    function update_booking_type_post(){
        $company_id = $this->post('company_id');
        $booking_id = $this->post('booking_id');
        $booking_type = $this->post('booking_type');

        if(!$company_id) {
            $this->response(array('status' => false, 'error' => 'Company ID is missing.'), 200);
        }

        if(!$booking_id) {
            $this->response(array('status' => false, 'error' => 'Booking ID is missing.'), 200);
        }

        if($booking_type == '') {
            $this->response(array('status' => false, 'error' => 'Booking Type is missing.'), 200);
        }

        $booking_data = $this->Booking_model->get_company_by_booking($booking_id);

        if(!$booking_data) {
            $this->response(array('status' => false, 'error' => 'Booking ID is Invalid.'), 200);
        }

        if(!is_numeric($booking_type)){
            $this->response(array('status' => false, 'error' => 'Booking Type is Invalid.'), 200);
        }

        $update_data = array('state' => $booking_type);

        $this->Booking_model->update_booking($update_data, $booking_id);
        
        $this->response(array('status' => true, 'message' => 'Booking Type is updated successfully.'), 200);
    }
}
