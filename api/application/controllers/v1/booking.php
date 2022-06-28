<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Example
 *
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array.
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
 * @author		Phil Sturgeon
 * @link		http://philsturgeon.co.uk/code/
*/

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
//require APPPATH.'/libraries/REST_Controller.php';

/**
 *
 * @SWG\Model(id="Booking")
 */
class Booking extends REST_Controller
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
        $this->load->model("translation_model");
        $this->load->library('Rate');
        $this->load->library('email');
            
        $this->load->helper('timezone');
        
        //$this->response(array('error' => 'Invalid API_key'), 404);
    }

	function index_get()
	{
        $booking_id = $this->get('booking_id');
        $booking = $this->Booking_model->get_booking($booking_id);
        $booking_room_history_data = $this->Booking_room_history_model->get_block($booking_id);
        $number_of_nights = (strtotime($booking_room_history_data['check_out_date']) - strtotime($booking_room_history_data['check_in_date']))/(60*60*24);
        if ($booking['use_rate_plan'] == '1')
        {
            $rate_array = $this->rate->get_rate_array(
                                            $booking['rate_plan_id'], 
                                            $booking_room_history_data['check_in_date'], 
                                            $booking_room_history_data['check_out_date'],
                                            $booking['adult_count'],
                                            $booking['children_count']
                                            );
            $average_daily_rate = $this->rate->get_average_daily_rate($rate_array);
            $total = $average_daily_rate * $number_of_nights;
            $booking['rate_array'] = $rate_array;
        }   
        else
        {
            $total = $booking['rate'] * $number_of_nights;
        }

        $booking['total'] = $total;
        $booking['room_history'] = $booking_room_history_data;
        $this->response($booking, 200);
	}
    
    function bookings_by_room_id_get()
    {
        $company_id = $this->get('company_id');
        $room_id = $this->get('room_id');
        $start_date = $this->get('start_date');
        $end_date = $this->get('end_date');
        
        $filters = array(
            'room_id'    => $room_id,
            'start_date' => $start_date,
            'end_date'   => $end_date
        );
       
        $company_info = $this->Company_model->get_company_data($company_id);
        $bookings = $this->Booking_model->get_bookings_by_room_id($filters, $company_id); 
        $room_info = $this->Room_model->get_room($room_id);
        
        $response = array(
            'bookings'     => !empty($bookings) ? $bookings : null,
            'company_info' => !empty($company_info) ? $company_info : null,
            'room_info' => !empty($room_info) ? $room_info : null
        );
        
        if(!empty($response))
            $this->response($response, 200);
        
        $this->response(null, 200);
    }
    
    function modified_booking_charges_and_payments_post()
    {
        $charges_payments['payments'] = $charges_payments['charges'] = $charges_payments['states'] = $charges_payments['colors'] = '';
        $charges_total = $payments_total = $booking_details = array();
        $pms_booking_ids = $this->post('pms_booking_ids');
        $ota_type = $this->post('ota_type');

		if(count($pms_booking_ids) > 0)
        {
            foreach($pms_booking_ids as $pms_booking_id)
            {
                $charges = $this->Charge_model->get_charges($pms_booking_id, $exclude_deleted_bookings = true);
                $payments = $this->Payment_model->get_payments($pms_booking_id, $exclude_deleted_bookings = true);
                $booking_detail = $this->Booking_model->get_booking_detail($pms_booking_id);
                if(!empty($charges))
                    $charges_total[$pms_booking_id] = $charges;
                if(!empty($payments))
                    $payments_total[$pms_booking_id] = $payments;
                if(!empty($booking_detail))
                    $booking_details[$pms_booking_id] = $booking_detail;
            }
        }
        
        if(count($charges_total) > 0)
        {
            foreach($charges_total as $charges)
            {
                foreach($charges as $charge){
                    $charges_payments['charges'][] = $charge;
                }
            }
        }
        
        if(count($payments_total) > 0)
        {
            foreach($payments_total as $payments)
            {
                foreach($payments as $payment){
                    $charges_payments['payments'][] = $payment;
                }
            }
        }
        if(count($booking_details) > 0)
        {
            foreach($booking_details as $booking_detail)
            {
                $charges_payments['states'][] = $booking_detail['state'];
                $charges_payments['colors'][] = $booking_detail['color'];
            }
        }
        
        if(count($charges_payments) > 0)
            $this->response($charges_payments, 200);
        else
            $this->response(null, 200);
      
    }
    
    function set_modified_booking_charges_and_payments_post()
    {
        $new_charges = $this->post('new_charges');
        $new_payments = $this->post('new_payments');
        $new_state = $this->post('new_state');
        $new_color = $this->post('new_color');
        $new_pms_booking_id = $this->post('new_pms_booking_id');
        if(count($new_charges) > 0)
        {
            $this->Charge_model->insert_charges($new_charges);
        }
        
        if(count($new_payments) > 0)
        {
            $this->Payment_model->insert_payments($new_payments);
        }

        if($new_state)
        {
            $this->Booking_model->update_booking(array('state' => $new_state, 'booking_id' => $new_pms_booking_id));
        }

        $this->Booking_model->update_booking(array('color' => $new_color, 'booking_id' => $new_pms_booking_id));
        
        $this->Booking_model->update_booking_balance($new_pms_booking_id); 
        
        $this->response("Success", 200); // 200 being the HTTP response code
    }

    // delete
    function index_delete()
    {
        $booking_ids_to_be_deleted = $this->delete('booking_ids_to_be_deleted');
        if (isset($booking_ids_to_be_deleted))
        {
            if($booking_ids_to_be_deleted && count($booking_ids_to_be_deleted) > 0){
                
                $this->Booking_model->delete_bookings($booking_ids_to_be_deleted);
                
                foreach($booking_ids_to_be_deleted as $booking_id_to_be_deleted) {
                    
                    $booking = $this->Booking_model->get_booking($booking_id_to_be_deleted);
                    $company = null;
                    if($booking && isset($booking['company_id'])) {
                        $company = $this->Company_model->get_company_data($booking['company_id']);
                    }
                    $log_data = array(
                        'selling_date' => $company && isset($company['selling_date']) ? $company['selling_date'] : date('Y-m-d'),
                        'user_id' => 2, //User_id 2 is Online Reservation
                        'booking_id' => $booking_id_to_be_deleted,                  
                        'date_time' => gmdate('Y-m-d H:i:s'),
                        'log' => 'Booking deleted',
                        'log_type' => SYSTEM_LOG
                    );
                    
                    $this->Booking_log_model->insert_log($log_data);
                }
            }
            
        }
    }

    // create
    function index_post()
    {
        //error_reporting(E_ALL);
        //ini_set('display_errors', 1);
        
        $booking = $this->post('booking');
        //print_r($booking);
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
        else
        {
            $action = $this->post('action');
            if($action && $action === "cancel_booking")
            {
                $pms_booking_ids = $this->post('pms_booking_ids');
                
                $cancellation_fee = $this->post('cancellation_fee');
                
                if(count($pms_booking_ids) > 0)
                {
                    foreach ($pms_booking_ids as $pms_booking_id)
                    {
                        $this->Booking_model->cancel_booking($pms_booking_id, $cancellation_fee);
                        $company_detail = $this->Booking_model->get_company_by_booking($pms_booking_id);
                        $log_data = array(
                            'selling_date' => $company_detail['selling_date'],
                            'user_id' => 2, //User_id 2 is Online Reservation
                            'booking_id' => $pms_booking_id,                  
                            'date_time' => gmdate('Y-m-d H:i:s'),
                            'log' => 'OTA Booking cancelled',
                            'log_type' => SYSTEM_LOG

                        );

                        $this->Booking_log_model->insert_log($log_data);
                    }
                }
            }
        }
        
        $this->response(null, 200); // 200 being the HTTP response code
    } 
    /*
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
            if(isset($booking['card']) && isset($booking['card']['number']) && isset($booking['card']['token'])){
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
                    'cc_number'         => $booking['card']['number'],
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
                if($booking['source'] == SOURCE_EXPEDIA && $booking_customer['card_holder_name'] == 'Expedia VirtualCard'){
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



            // try to create stripe customer
            /*if (isset($booking['card']['token'])) {
                try {
                    $this->load->library('session');
                    $this->load->library('PaymentGateway',compact('company_id'));
                    $this->paymentgateway->setCustomerById($booking_customer_id);
                    $this->paymentgateway->setCCToken($booking['card']['token']);
                    $external_entity_id                                    = $this->paymentgateway->createExternalEntity();
                    $temp                                                  = $booking_customer;
                    $temp[$this->paymentgateway->getExternalEntityField()] = $external_entity_id;
                    $temp['customer_id']                                   = $booking_customer_id;
                    $this->Customer_model->update_customer($temp);
                } catch (Exception $e) {
                    // oh well..
                }
            }*/

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

            $average_daily_rate = 0;
            $average_daily_rate_set = false;

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

                $average_daily_rate = $average_daily_rate_set ? $average_daily_rate : $rate['base_rate'];
                $average_daily_rate_set = true;
            }
           // if adult count not provided, use default max
            $room_type = $this->Room_type_model->get_room_type($pms_room_type_id);
            if(!isset($booking['adult_count']) || !$booking['adult_count'])
            {
                $booking['adult_count'] = isset($room_type['max_adults']) ? $room_type['max_adults'] : 1;
            }

            $common_booking_sources = json_decode(COMMON_BOOKING_SOURCES, true);
            
            $source_id = $booking['source'];
            $is_new_source = null;
            if($booking['source'] == SOURCE_EXPEDIA){
                
                $source = isset($booking['sub_source']) && $booking['sub_source'] ? $booking['sub_source'] : "";
                $parent_source = "Expedia";
                if($source) {
                    $is_new_source = true;
                    if(strcmp($parent_source, trim($source)) == 0){
                        $source_id = SOURCE_EXPEDIA;
                        $is_new_source = false;
                    }else{
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

            if($booking['source'] == SOURCE_MYALLOCATOR){
                
                $source = isset($booking['sub_source']) && $booking['sub_source'] ? $booking['sub_source'] : "";
                $parent_source = "Myallocator";
                if($source) {
                    $is_new_source = true;
                    if(strcmp($parent_source, trim($source)) == 0){
                        $source_id = SOURCE_MYALLOCATOR;
                        $is_new_source = false;
                    }else{
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
                        if ($common_booking_sources && count($common_booking_sources) > 0) {
                            foreach ($common_booking_sources as $id => $name) {
                                if(strtolower($source) == strtolower($name)) {
                                    $source_id = $id;
                                    $is_new_source = false;
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            if($booking['source'] == SOURCE_SITEMINDER){
                
                $source = isset($booking['sub_source']) && $booking['sub_source'] ? $booking['sub_source'] : "";
                $parent_source = "Siteminder";
                if($source) {
                    $is_new_source = true;
                    if(strcmp($parent_source, trim($source)) == 0){
                        $source_id = SOURCE_SITEMINDER;
                        $is_new_source = false;
                    }else{
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
                        if ($common_booking_sources && count($common_booking_sources) > 0) {
                            foreach ($common_booking_sources as $id => $name) {
                                if(strtolower($source) == strtolower($name)) {
                                    $source_id = $id;
                                    $is_new_source = false;
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            if($is_new_source){
                $source_id = $this->Booking_model->create_booking_source($company_id, $source);
            } 
            
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
                'color' => '8EB3DE',
                'rate' => $average_daily_rate,
                'charge_type_id' => $rate_plan['charge_type_id']
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
                                                    $company_detail['subscription_level'],
                                                    $company_detail['book_over_unconfirmed_reservations']
                                                );
            //echo "available room_id:";
            //print_r($room_id);
            $is_overbooking = false;
            $is_non_continuous_available = false;
            // if no room is available then get non contiuous room blocks
            if(is_null($room_id) && isset($company_detail['allow_non_continuous_bookings']) && $company_detail['allow_non_continuous_bookings']){
                $is_non_continuous_available = $this->get_non_continuous_available_room_ids($booking_id, $pms_room_type_id, $company_id, $booking['check_in_date'],  $booking['check_out_date'], $company_detail['subscription_level'], $company_detail['book_over_unconfirmed_reservations']);
            }
            $inventory = $avail_array = array();
            // if no room is available, then just assign the first room of the room_type
            if (is_null($room_id) && ($is_non_continuous_available === 'less_blocks' || !$is_non_continuous_available))
            {
                $rooms_available = $this->Room_model->get_rooms_by_room_type_id($pms_room_type_id, $company_detail['subscription_level']);
                if (isset($rooms_available[0]->room_id))
                {
                    if($booking['booking_type'] == "new")
                    {
                        $is_overbooking = true;
                        $inventory[$pms_room_type_id] = $room_type;
                        $avail_array = $this->Availability_model->get_availability(
                                                                $booking['check_in_date'], 
                                                                $booking['check_out_date'], 
                                                                array_keys($inventory), 
                                                                $booking['source'],
                                                                true,
                                                                $booking['adult_count'],
                                                                isset($booking['children_count']) ? $booking['children_count'] : 0,
                                                                false,
                                                                false,
                                                                false,
                                                                false,
                                                                $is_overbooking,
                                                                $company_id
                        );
                    }
                    $room_id = $rooms_available[0]->room_id;
                }
            }

            //echo "chosen room_id:";
            // prx($avail_array);

            if(!is_null($room_id)){
                // create booking_room_history
                $booking_room_history_data = Array(
                'room_id' => $room_id,
                'check_in_date' => $company_detail['enable_new_calendar'] ? $booking['check_in_date'].' '.date("H:i:s", strtotime($company_detail['default_checkin_time'])) : $booking['check_in_date'],
                'check_out_date' => $company_detail['enable_new_calendar'] ? $booking['check_out_date'].' '.date("H:i:s", strtotime($company_detail['default_checkout_time'])) : $booking['check_out_date'],
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

            $log_data = array(
                'selling_date' => $company_detail['selling_date'],
                'user_id' => 2, //User_id 2 is Online Reservation
                'booking_id' => $booking_id,                  
                'date_time' => gmdate('Y-m-d H:i:s'),
                'log' => 'OTA Booking created',
                'log_type' => SYSTEM_LOG

            );

            $this->Booking_log_model->insert_log($log_data);

            //Create a corresponding invoice
            $this->Invoice_model->create_invoice($booking_id);

            
            $this->Booking_model->update_booking_balance($booking_id);

            $rt_availability = array();
            $no_rooms_available = false;
            if($is_overbooking)
            {
                if(isset($avail_array) && $avail_array && count($avail_array) > 0)
                {
                    foreach ($avail_array as $key => $value) {
                        $rt_availability[$value['date']] = $value['availability'];
                    }
                }
                array_pop($rt_availability);

                if(in_array("0", $rt_availability))
                {
                    $no_rooms_available = true;
                }
                
                $this->send_overbooking_email($booking_id, $is_non_continuous_available, $rt_availability, $no_rooms_available, $company_detail);
            }

            return $booking_id;
        }
        catch(Exception $e){
            return $booking_id ? $booking_id : $e->getMessage();
        }
    }
    
    function get_non_continuous_available_room_ids($booking_id, $room_type_id, $company_id, $check_in_date, $check_out_date, $subscription_level = null, $book_over_unconfirmed_reservations = 0)
    {
        $rooms_info = $this->Room_model->get_rooms_by_room_type_id($room_type_id, $subscription_level);
        $all_room_data = array();
        $check_out_date = date("Y-m-d", strtotime("-1 day", strtotime($check_out_date)));
        if(count($rooms_info) > 0)
        {
            foreach($rooms_info as $room_info){
                $all_room_data[] = $this->Room_model->get_availability_for_room_by_date_range($check_in_date, $check_out_date, $room_type_id, $room_info->room_id, null, $book_over_unconfirmed_reservations);
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
        
        return $this->get_non_continuous_blocks($sorted_free_rooms_ar, $booking_id, $company_id, $room_type_id);
    }
    
    function get_non_continuous_blocks($available_rooms, $booking_id, $company_id, $room_type_id)
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
                if(isset($company_data['maximum_no_of_blocks']) && $company_data['maximum_no_of_blocks'] >= count($available_rooms))
                {
                    foreach($available_rooms as $available_room_id => $dates_array)
                    {
                        $check_in_date = current($dates_array);
                        $check_out_date = end($dates_array);
                        $check_out_date = date("Y-m-d", strtotime("+1 day", strtotime($check_out_date))); // check out date will be +1 day

                        $booking_room_history_data = Array(
                                                        'room_id' => $available_room_id,
                                                        'check_in_date' => $company_data['enable_new_calendar'] ? $check_in_date.' '.date("H:i:s", strtotime($company_data['default_checkin_time'])) : $check_in_date,
                                                        'check_out_date' => $company_data['enable_new_calendar'] ? $check_out_date.' '.date("H:i:s", strtotime($company_data['default_checkout_time'])) : $check_out_date,
                                                        'booking_id' => $booking_id,
                                                        'room_type_id' => $room_type_id
                                                    );

                        $this->Booking_room_history_model->create_booking_room_history($booking_room_history_data);
                    }
                    return true;
                }
                else
                {
                    return 'less_blocks';
                }
            }
        }
        else
        {
            return $this->get_non_continuous_blocks($array_merge, $booking_id, $company_id, $room_type_id);
        }
        return false;
    }
    
    function get_company_data_post()
    {
        $company_id = $this->post('company_id');
        $company_data = $this->Company_model->get_company_data($company_id);
        
        $this->response($company_data, 200); // 200 being the HTTP response code
    }

    function send_overbooking_email($booking_id, $is_non_continuous_available = true, $room_type_availability = null, $no_rooms_available = false, $company = null) {
        $this->load->library('email_template');
        $result_array = $this->email_template->send_overbooking_email($booking_id, $is_non_continuous_available, $room_type_availability, $no_rooms_available);
        if ($result_array && $result_array['success']) {
            $log_data = array(
                    'selling_date' => $company['selling_date'],
                    'user_id' => 2, //User_id 2 is Online Reservation
                    'booking_id' => $booking_id,                  
                    'date_time' => gmdate('Y-m-d H:i:s'),
                    'log' => "Room allocation conflict alert email sent to " . $result_array['owner_email'],
                    'log_type' => SYSTEM_LOG
                );

            $this->Booking_log_model->insert_log($log_data);
        }
    }
}
