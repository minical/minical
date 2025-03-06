<?php
class Invoice extends MY_Controller {
   

    function __construct()
    {
        parent::__construct();      
        $this->load->model('Invoice_model');
        $this->load->model('Invoice_log_model');
        $this->load->model('Company_model');                
        $this->load->model('Room_model');
        $this->load->model('Rate_plan_model');
        $this->load->model('Charge_model');
        $this->load->model('Charge_type_model');
        $this->load->model('Payment_model');            
        $this->load->model('Image_model');
        $this->load->model('Tax_model');
        $this->load->model('Customer_model');
        $this->load->model('Booking_model');
        $this->load->model('Booking_log_model');
        $this->load->model('Booking_extra_model');
        $this->load->model('Booking_room_history_model');
        $this->load->model('Card_model');
        $this->load->model('Folio_model');
        $this->load->model('payment_gateway_model');
        $this->load->model('Booking_linked_group_model');
        $this->load->model('Currency_model');
        $this->load->model('Customer_field_model');
        $this->load->model('Booking_field_model');
        $this->load->model('Extra_model');
        $this->load->model('Rate_model');

        $this->load->helper('timezone');
        $this->load->helper('date_format_helper');
        
        $this->load->library('PaymentGateway');
        $this->load->library('Forecast_charges');
        $this->load->library('Tokenex');
        $global_data['menu_on'] = true;
        $this->load->vars($global_data);        
        
        $language = $this->session->userdata('language');
        $this->lang->load('booking', $language);
    }

    function index($invoice_number = "")
    {
        if(is_numeric($invoice_number)) {
            $booking_id = $this->Invoice_model->get_booking_id_by_invoice_number($invoice_number, $this->company_id);
            $this->show_invoice($booking_id);
        }
    }

    function show_invoice_read_only($hash = "")
    {
        // Show invoice based on hash value received
        $booking_id = $this->Booking_model->get_booking_id_from_invoice_hash($hash);

        if ($hash === "" || !isset($booking_id))
        {
            show_404();
        }

        $global_data['menu_on'] = false;
        $this->load->vars($global_data);
        $this->_mark_invoice_as_read($booking_id);

        // used for mouse-hover delete buttons
        $this->show_invoice($booking_id, false, false, true);
    }

    function show_master_invoice_read_only($hash = "")
    {
        // Show invoice based on hash value received
        $group_id = $this->Booking_model->get_group_id_from_invoice_hash($hash);
        
        if ($hash === "" || !isset($group_id))
        {
            show_404();
        }

        $global_data['menu_on'] = false;
        $this->load->vars($global_data);
        // $this->_mark_invoice_as_read($booking_id);

        // used for mouse-hover delete buttons
        $this->show_master_invoice($group_id, false, false, true);
    }

    function _mark_invoice_as_read($booking_id)
    {
        // Check if the invoice has already been read
        $this->load->model('Invoice_model');
        if ($this->Invoice_model->has_invoice_been_viewed($booking_id))
        {
            return;
        } else
        {
            $log_data['user_id']    = '0'; // user_id is left empty, because there is no user, but it is viewed by the guest
            $log_data['booking_id'] = $booking_id;
            $log_data['log_type']   = INVOICE_LOG;
            $log_data['date_time']  = gmdate('Y-m-d H:i:s');
            $log_data['log']        = "Invoice has been viewed by the e-mail recipient";
            $this->load->model('Booking_log_model');
            $this->Booking_log_model->insert_log($log_data);
        }
    }


    // Create a booking log to indicate that the invoice has been read.
    // If the invoice has already been read, then return

    /**
     *
     * shows invoice form for either: Minical users to modify/print invoice OR guests to view their invoice
     *
     * I decided to go with html e-mail approach because
     * 1. It is pain in the ass to convert our css+html email to HTML email friendly 3D style format
     * 2. We can verify if the guest has viewed the email & log it
     * 3. The email format improves as our invoice improves. (Since it's using same invoice page)
     * Send email message of given type (activate, forgot_password, etc.)
     *
     * @param    booking_id              = integer
     * @param    show_forecasted_charges = integer
     * @param    read_only               = integer
     * @return    void
     */

    function show_invoice($booking_id, $folio_id = false, $customer_id = false, $read_only = false)
    {
        // if user is viewing the invoice through Hash,
        // then don't check if the booking belongs to the company
        if (isset($this->user_id))
            $this->permission->check_access_to_booking_id($this->user_id, $booking_id);

        $data['booking_detail'] = $this->Booking_model->get_booking_detail($booking_id);
        $company_id             = isset($data['booking_detail']['company_id']) ? $data['booking_detail']['company_id'] : $this->Company_model->get_company_id_by_booking_id($booking_id);

        $data['company']        = ($company_id == $this->company_id && $this->company_data) ? $this->company_data : $this->Company_model->get_company($company_id);

        $this->company_date_format = $data['company']['date_format'];

        $booking_room_history   = $this->Booking_room_history_model->get_booking_detail($booking_id);
        $data['room_detail']    = $this->Room_model->get_room($booking_room_history['room_id'], $booking_room_history['room_type_id']);
        $data['customer_id']    = $customer_id;

        $this->company_id = $company_id;

        //$data['currency_symbol'] = $this->session->userdata('currency_symbol');
        //if (!$data['currency_symbol']) {
            $default_currency       = $this->Currency_model->get_default_currency($company_id);
            $data['currency_symbol'] = isset($default_currency['currency_code']) ? $default_currency['currency_code'] : null;
            $this->session->set_userdata(array('currency_symbol' => $data['currency_symbol']));
        //}

        $staying_customers       = $this->Customer_model->get_staying_customers($booking_id);
        $staying_customer_names  = array();
        foreach ($staying_customers as $customer) {
            $staying_customer_names[] = $customer['customer_name'];
        }
        $booking_customer_id_info = $this->Customer_model->get_customer_info($data['booking_detail']['booking_customer_id']);
        $data['customers'] = array_merge(array($booking_customer_id_info), $staying_customers);

        if ($customer_id) // if invoice is billed to everyone
        {
            $data['booking_customer'] = $this->Customer_model->get_customer_info($customer_id);
        }
        else
        {
            $booking_customer = $booking_customer_id_info;
            $data['booking_customer'] = $booking_customer;
            if ($data['booking_customer'] && $data['customers'] && count($data['customers']) > 1)
            {
                $data['booking_customer']['customer_name'] = $booking_customer['customer_name'].", ".implode(", ", $staying_customer_names);
            }

            // $customer_id = $data['booking_detail']['booking_customer_id'];
        }
        /*Get data from card table*/
      
        foreach($data['customers'] as $key => $customer){
            
            unset($data['customers'][$key]['cc_number']);
            unset($data['customers'][$key]['cc_expiry_month']);
            unset($data['customers'][$key]['cc_expiry_year']);
            unset($data['customers'][$key]['cc_tokenex_token']);
            unset($data['customers'][$key]['cc_cvc_encrypted']);
            
            $card_data = isset($customer['customer_id']) ? $this->Card_model->get_active_card($customer['customer_id'], $this->company_id) : null;
            // $token = isset($card_data['customer_meta_data']) && $card_data['customer_meta_data'] ? (isset(json_decode($card_data['customer_meta_data'], true)['token']) && json_decode($card_data['customer_meta_data'], true)['token'] ? json_decode($card_data['customer_meta_data'], true)['token'] : isset(json_decode($card_data['customer_meta_data'], true)['pci_token'])) : null;

            $token = "";
            if (isset($card_data['customer_meta_data']) && $card_data['customer_meta_data'] ) {

                if (isset(json_decode($card_data['customer_meta_data'], true)['token']) && json_decode($card_data['customer_meta_data'], true)['token']) {
                    $token = json_decode($card_data['customer_meta_data'], true)['token'];
                }else{
                    if (json_decode($card_data['customer_meta_data'], true)['source'] == 'pci_booking') {
                        $token = json_decode($card_data['customer_meta_data'], true)['pci_token'];
                    } elseif (json_decode($card_data['customer_meta_data'], true)['source'] == 'cardknox') {
                        $token = json_decode($card_data['customer_meta_data'], true)['cardknox_token'];
                    } else {
                        $token = json_decode($card_data['customer_meta_data'], true)['token'];
                    }
                }
            }



            if(isset($card_data) && $card_data){
                $data['customers'][$key]['cc_number'] = $card_data['cc_number'];
                $data['customers'][$key]['cc_expiry_month'] = $card_data['cc_expiry_month'];
                $data['customers'][$key]['cc_expiry_year'] = $card_data['cc_expiry_year'];
                $data['customers'][$key]['cc_tokenex_token'] = $card_data['cc_tokenex_token'];
                $data['customers'][$key]['cc_cvc_encrypted'] = $card_data['cc_cvc_encrypted'];
                $data['customers'][$key]['evc_card_status'] = $card_data['evc_card_status'];
                $data['customers'][$key]['customer_meta_token'] = $token ?? null;
            }
        }
        // for company logo
        $data['company_logos']   = $this->Image_model->get_images($data['company']['logo_image_group_id']);
        
        

        //Get charges and payments sorted based on its selling dates
        $data['charge_types']  = $this->Charge_type_model->get_charge_types($this->company_id);
        $data['payment_types'] = $this->Payment_model->get_payment_types($this->company_id);
        
        $payment_gateway_credentials = $this->paymentgateway->getGatewayCredentials();
        $data['selected_payment_gateway'] = null;
        if(isset($payment_gateway_credentials['selected_payment_gateway'])){
            $data['selected_payment_gateway'] = $payment_gateway_credentials['selected_payment_gateway'];
        }

        $data['folios'] = $this->Folio_model->get_folios($booking_id);
        $data['invoice_create_data'] = $this->Booking_log_model->get_booking_createdate($booking_id); 

        
         $data['invoice_number'] = $this->Invoice_model->get_invoice_number($booking_id); 
        $first_folio_id = isset($data['folios'][0]) && isset($data['folios'][0]['id']) ? $data['folios'][0]['id'] : null;
        
        $folio_id = $folio_id ? $folio_id : $first_folio_id;
        
        $is_first_folio = ($folio_id === $first_folio_id);
        
        $data['current_folio_id'] = $folio_id;
        
        $charges  = $this->Charge_model->get_charges($booking_id, $customer_id, $folio_id, $is_first_folio);

        // and invoice currently viewing transactions for 'booking customer'
        if(!$data['company']['hide_forecast_charges'])
        {
            if ($is_first_folio && ($data['booking_detail']['booking_customer_id'] == $customer_id || $customer_id == false))
            {
                $charges = array_merge($charges, $this->forecast_charges->_get_forecast_charges($booking_id, false, $data['booking_detail']));
                $charges = array_merge($charges, $this->forecast_charges->_get_forecast_extra_charges($booking_id, false, $data['booking_detail']));
                if(isset($data['folios'][0])) {
                    $data['folios'][0]['charge_count'] = count($charges);
                }
            }
            elseif(isset($data['folios'][0]))
            {
                $forecast_charges = $this->forecast_charges->_get_forecast_charges($booking_id);
                $forecast_charges = array_merge($forecast_charges, $this->forecast_charges->_get_forecast_extra_charges($booking_id, false, $data['booking_detail']));
                $data['folios'][0]['charge_count'] = count($forecast_charges);
            }
        }
        // assign taxes to charges
        $taxes_cache = array();
        foreach ($charges as $index => $charge) {
            if (isset($taxes_cache[$charge['charge_type_id'] .'-'. $charge['amount']])) {
                $charges[$index]['taxes'] = $taxes_cache[$charge['charge_type_id'] .'-'. $charge['amount']];
            } else {
                $charges[$index]['taxes'] = $this->Charge_type_model->get_taxes($charge['charge_type_id'], $charge['amount']);
                $taxes_cache[$charge['charge_type_id'] .'-'. $charge['amount']] = $charges[$index]['taxes'];
            }
        }
        $taxes_cache = array(); // release memory
        
        $data['charges']  = $charges;
        $payments = $this->Payment_model->get_payments($booking_id, $customer_id, $folio_id, $is_first_folio);
        if($payments){
            $total_amount = 0;
            foreach($payments as $index => $payment){
                $payments[$index]['remaining_amount'] = '';
                if($payment['payment_status'] == 'charge' ||$payment['payment_status'] == NULL ){
                    $total_amount = $payment['amount'];
                    $charge_id = $payment['gateway_charge_id'];
                    $partial_payments = isset($charge_id) ? $this->Payment_model->get_partial_refunds_by_charge_id($charge_id) : null;

                    $total_partial_amount = 0;
                    if(!empty($partial_payments)){
                        foreach($partial_payments as $partial_payment){
                            $total_partial_amount += $partial_payment['amount'];
                        }
                        $payments[$index]['remaining_amount'] = $total_amount + $total_partial_amount;
                    }
                }
            }
        }
        $data['payments'] = $payments;        
        $data['read_only'] = $read_only;
        //Get invoice autobiography
       
        $data['invoice_log']    = $this->Invoice_model->get_invoice_log($booking_id);

        if ($data['booking_customer']) {
            $data['booking_customer']['custom_fields'] = $this->Customer_model->get_customer_fields($data['booking_customer']['customer_id']);

            $customer_fields = $this->Customer_field_model->get_customer_fields($this->company_id, false, 'show_on_invoice');

            $customer_fields = $customer_fields ? $customer_fields : [];

            $common_customer_fields = json_decode(COMMON_CUSTOMER_FIELDS, true);
            $get_common_customer_fields = $this->Customer_field_model->get_common_customer_fields_settings($this->company_id);

            foreach($common_customer_fields as $id => $name)
            {
                $value = '';
                if ($get_common_customer_fields && isset($get_common_customer_fields[$id]) && isset($get_common_customer_fields[$id]['show_on_invoice']) && $get_common_customer_fields[$id]['show_on_invoice']) {

                    $common_name = strtolower($name);
                    if(isset($data['booking_customer'][$common_name]) && $data['booking_customer'][$common_name] != '')
                    {
                        $value = $data['booking_customer'][$common_name];
                    }
                    elseif(isset($data['booking_customer']['customer_'.$common_name]) && $data['booking_customer']['customer_'.$common_name] != '')
                    {
                        $value = $data['booking_customer']['customer_'.$common_name];
                    }
                    else
                    {
                        $explode_name = explode(' ',$common_name);
                        $cn = $explode_name[0].''.(isset($explode_name[1]) && $explode_name[1] ? $explode_name[1] : '');
                        if(isset($data['booking_customer'][$cn]) && $data['booking_customer'][$cn] != '')
                        {
                            $value = $data['booking_customer'][$cn];
                        }
                        else
                        {
                            $cn = (isset($explode_name[0]) && $explode_name[0] ? $explode_name[0] : '').'_'.(isset($explode_name[1]) && $explode_name[1] ? $explode_name[1] : '');
                            if(isset($data['booking_customer'][$cn]) && $data['booking_customer'][$cn] != "") {
                                
                                if($cn == 'customer_type') {
                                    $cn = 'customer_type_id';
                                    $this->load->model('customer_type_model');
                                    $customer_type = $this->customer_type_model->get_customer_type($data['booking_customer'][$cn]); 
                                    $value = $customer_type[0]['name'];
                                }
                                else {
                                    $value = $data['booking_customer'][$cn];
                                }
                            }
                        }
                    }

                    $customer_fields[] = array(
                        'id' => $id,
                        'name' => $name,
                        'value' => $value,
                    );
                }
            }

            $data['customer_fields'] = $customer_fields;

            $data['booking_customer']['customer_fields'] = [];
            foreach ($customer_fields as $customer_field) {
                if(!(
                    $customer_field['name'] == 'Phone' ||
                    $customer_field['name'] == 'Fax' ||
                    $customer_field['name'] == 'Email' ||
                    $customer_field['name'] == 'Address' ||
                    $customer_field['name'] == 'Address 2' ||
                    $customer_field['name'] == 'City' ||
                    $customer_field['name'] == 'Region' ||
                    $customer_field['name'] == 'Country' ||
                    $customer_field['name'] == 'Postal Code' ||
                    $customer_field['name'] == 'Name'
                )) {
                    if (!isset($customer_field['value'])) {
                        if($data['booking_customer']['custom_fields'] && isset($data['booking_customer']['custom_fields'][$customer_field['id']]) && $data['booking_customer']['custom_fields'][$customer_field['id']] != '')
                        {
                            $customer_field['value'] = $data['booking_customer']['custom_fields'][$customer_field['id']];
                        }
                    }
                    $data['booking_customer']['customer_fields'][] = $customer_field;
                }
            }
        }
        $data['custom_booking_fields'] = $this->Booking_field_model->get_booking_fields_data($booking_id,'show_on_invoice');

        $data['booking_fields'] = $this->Booking_field_model->get_booking_fields($this->company_id,'show_on_invoice');

        $data['pos_extras'] = $this->Extra_model->get_extras($this->company_id, true);

        $data['js_files'] = Array(
            base_url() . auto_version('js/channel_manager/channel_manager.js'),
            base_url().'js/moment.min.js',
            base_url() . auto_version('js/booking/printThis.js'),
            base_url() . auto_version('js/booking/bookingModal.js'),
            base_url().auto_version('js/invoice/invoice.js')
        );
           
        
        if (!$read_only) {
             $data['selected_menu'] = 'invoices';
             $data['css_files'][]   = base_url().auto_version('css/invoice.css');
             $data['css_files'][]   = base_url().auto_version('css/booking/booking_main.css');
             $data['js_files'][]    = base_url().auto_version('js/invoice/invoice_edit.js');
        
            $this->load->library('PaymentGateway');
        }
        $data['selected_customer_id'] = $customer_id;
        $data['main_content'] = 'invoice/invoice';
        $this->load->view('includes/bootstrapped_template', $data);
    }

    function show_master_invoice($group_id, $booking_id = false, $customer_id = false, $read_only = false)
    {
        $is_group_invoice = true;
        $data['single_booking_id'] = $booking_id;//for single booking if booking_id is passed with group id
        $get_bookings_by_group_id = $this->Booking_model->get_bookings_by_group_id($group_id);
        if($booking_id)
        {
            $is_group_invoice = false;
        }

        $data['group_id'] = $group_id;
        $data['is_group_invoice'] = $is_group_invoice;
        $data['first_booking_id'] = $get_bookings_by_group_id[0]['booking_id'];//first booking_id of group
        if(!$booking_id) {
            foreach ($get_bookings_by_group_id as $value) {
              $all_booking_ids[] =  $value['booking_id'];
            }    
        }
        if(isset($all_booking_ids) && $all_booking_ids) {
            $all_booking_ids = $all_booking_ids;  
        }
        else {
            $all_booking_ids[] =  $booking_id;    
        }
        $booking_id = $booking_id ? $booking_id : $get_bookings_by_group_id[0]['booking_id'];
        $company_id             = $this->Company_model->get_company_id_by_booking_id($booking_id);
        $data['booking_group_detail'] = $this->Booking_linked_group_model->get_booking_linked_group($booking_id, $company_id);
        $data['company']        = $this->Company_model->get_company($company_id);
        $data['booking_detail'] = $this->Booking_model->get_booking_detail($booking_id);
        $data['customer_id']    = $customer_id;

        $data['customers'] = array();

        if(!$is_group_invoice && !$group_id)
        {
            $customer_data = array_merge(array($this->Customer_model->get_customer_info($data['booking_detail']['booking_customer_id'])));
        }
        else
        {
            $customer_data = $this->Booking_model->get_all_group_booking_customers($group_id);
        }

        $staying_customers = $this->Customer_model->get_staying_customers($all_booking_ids);
        if($customer_data)
            $data['customers'] = array_merge($customer_data, $staying_customers);

        if(isset($data['customers']) && $data['customers'])
        {
            foreach ($data['customers'] as $key => $value) {
                if(isset($value['cust_type']) && $value['cust_type'])
                {
                    $data['customers'][$key]['cust_type'] = $value['cust_type'];
                }
                else
                {
                    $data['customers'][$key]['cust_type'] = 'Staying Customer';
                }
            }
            usort($data['customers'], function ($a, $b) {
                return $a['cust_type'] > $b['cust_type'];
            });
        }
        

        if ($customer_id) {// if invoice is billed to everyone
            $data['booking_customer'] = $this->Customer_model->get_customer_info($customer_id);
        }
        else {
            $booking_customer = $this->Customer_model->get_customer_info($data['booking_detail']['booking_customer_id']);
            $data['booking_customer'] = $booking_customer;
        }
        /*Get data from card table*/
        if(isset($data['customers']) && $data['customers'])
        {
            foreach($data['customers'] as $key => $customer){
                unset($data['customers'][$key]['cc_number']);
                unset($data['customers'][$key]['cc_expiry_month']);
                unset($data['customers'][$key]['cc_expiry_year']);
                unset($data['customers'][$key]['cc_tokenex_token']);
                unset($data['customers'][$key]['cc_cvc_encrypted']);
                $card_data = isset($customer['customer_id']) ? $this->Card_model->get_active_card($customer['customer_id'], $this->company_id) : null;

                $token = "";
                if (isset($card_data['customer_meta_data']) && $card_data['customer_meta_data'] ) {

                    if (isset(json_decode($card_data['customer_meta_data'], true)['token']) && json_decode($card_data['customer_meta_data'], true)['token']) {
                        $token = json_decode($card_data['customer_meta_data'], true)['token'];
                    }
                }
                if(isset($card_data) && $card_data){
                    $data['customers'][$key]['cc_number'] = $card_data['cc_number'];
                    $data['customers'][$key]['cc_expiry_month'] = $card_data['cc_expiry_month'];
                    $data['customers'][$key]['cc_expiry_year'] = $card_data['cc_expiry_year'];
                    $data['customers'][$key]['cc_tokenex_token'] = $card_data['cc_tokenex_token'];
                    $data['customers'][$key]['cc_cvc_encrypted'] = $card_data['cc_cvc_encrypted'];
                    $data['customers'][$key]['customer_meta_token'] = $token ?? null;
                }
            } 
        }
        // prx($data['customers']);
        // for company logo
        
        
        $data['company_logos']   = $this->Image_model->get_images($data['company']['logo_image_group_id']);
        //Get charges and payments sorted based on its selling dates
        $data['charge_types']  = $this->Charge_type_model->get_charge_types($this->company_id);
        $data['payment_types'] = $this->Payment_model->get_payment_types($this->company_id);
        $payment_gateway_credentials = $this->paymentgateway->getGatewayCredentials();
        $data['selected_payment_gateway'] = null;
        if(isset($payment_gateway_credentials['selected_payment_gateway'])){
            $data['selected_payment_gateway'] = $payment_gateway_credentials['selected_payment_gateway'];
        }
        $charges  = $this->Charge_model->get_charges(implode(',',$all_booking_ids), $customer_id);
        if(!$data['company']['hide_forecast_charges'])
        {
            if ($data['booking_detail']['booking_customer_id'] == $customer_id || $customer_id == false) {
                if($data['single_booking_id']) {
                    $charges = array_merge($charges, $this->forecast_charges->_get_forecast_charges($booking_id));
                    $charges = array_merge($charges, $this->forecast_charges->_get_forecast_extra_charges($booking_id));
                }
                else {
                    foreach ($get_bookings_by_group_id as $key => $value) {

                    $charges = array_merge($charges, $this->forecast_charges->_get_forecast_charges($value['booking_id']));
                    $charges = array_merge($charges, $this->forecast_charges->_get_forecast_extra_charges($value['booking_id']));
                    }          
                }
            }
        }
        // sort array by date
        usort($charges, function($a, $b) {
            return strtotime($a['selling_date']) - strtotime($b['selling_date']);
        });
        // assign taxes to charges
        foreach ($charges as $index => $charge) {
            $charges[$index]['taxes'] = $this->Charge_type_model->get_taxes($charge['charge_type_id'], $charge['amount']);
        }
        $data['charges']  = $charges;
        $payments = $this->Payment_model->get_payments(implode(',',$all_booking_ids), $customer_id);
        if($payments){
            $total_amount = 0;
            foreach($payments as $index => $payment){
                $payments[$index]['remaining_amount'] = '';
                if($payment['payment_status'] == 'charge' ||$payment['payment_status'] == NULL ){
                    $total_amount = $payment['amount'];
                    $charge_id = $payment['gateway_charge_id'];
                    $partial_payments = isset($charge_id) ? $this->Payment_model->get_partial_refunds_by_charge_id($charge_id) : null;

                    $total_partial_amount = 0;
                    if(!empty($partial_payments)){
                        foreach($partial_payments as $partial_payment){
                            $total_partial_amount += $partial_payment['amount'];
                        }
                        $payments[$index]['remaining_amount'] = $total_amount + $total_partial_amount;
                    }
                }
            }
        }

        if ($data['booking_customer']) {
            $data['booking_customer']['custom_fields'] = $this->Customer_model->get_customer_fields($data['booking_customer']['customer_id']);

            $customer_fields = $this->Customer_field_model->get_customer_fields($this->company_id, false, 'show_on_invoice');

            $customer_fields = $customer_fields ? $customer_fields : [];

            $common_customer_fields = json_decode(COMMON_CUSTOMER_FIELDS, true);
            $get_common_customer_fields = $this->Customer_field_model->get_common_customer_fields_settings($this->company_id);

            foreach($common_customer_fields as $id => $name)
            {
                $value = '';
                if ($get_common_customer_fields && isset($get_common_customer_fields[$id]) && isset($get_common_customer_fields[$id]['show_on_invoice']) && $get_common_customer_fields[$id]['show_on_invoice']) {

                    $common_name = strtolower($name);
                    if(isset($data['booking_customer'][$common_name]) && $data['booking_customer'][$common_name] != '')
                    {
                        $value = $data['booking_customer'][$common_name];
                    }
                    elseif(isset($data['booking_customer']['customer_'.$common_name]) && $data['booking_customer']['customer_'.$common_name] != '')
                    {
                        $value = $data['booking_customer']['customer_'.$common_name];
                    }
                    else
                    {
                        $explode_name = explode(' ',$common_name);
                        $cn = $explode_name[0].''.(isset($explode_name[1]) && $explode_name[1] ? $explode_name[1] : '');
                        if(isset($data['booking_customer'][$cn]) && $data['booking_customer'][$cn] != '')
                        {
                            $value = $data['booking_customer'][$cn];
                        }
                        else
                        {
                            $cn = (isset($explode_name[0]) && $explode_name[0] ? $explode_name[0] : '').'_'.(isset($explode_name[1]) && $explode_name[1] ? $explode_name[1] : '');
                            if(isset($data['booking_customer'][$cn]) && $data['booking_customer'][$cn] != "") {
                                
                                if($cn == 'customer_type') {
                                    $cn = 'customer_type_id';
                                    $this->load->model('customer_type_model');
                                    $customer_type = $this->customer_type_model->get_customer_type($data['booking_customer'][$cn]); 
                                    $value = $customer_type[0]['name'];
                                }
                                else {
                                    $value = $data['booking_customer'][$cn];
                                }
                            }
                        }
                    }

                    $customer_fields[] = array(
                        'id' => $id,
                        'name' => $name,
                        'value' => $value,
                    );
                }
            }

            $data['booking_customer']['customer_fields'] = [];
            foreach ($customer_fields as $customer_field) {
                if(!(
                    $customer_field['name'] == 'Phone' ||
                    $customer_field['name'] == 'Fax' ||
                    $customer_field['name'] == 'Email' ||
                    $customer_field['name'] == 'Address' ||
                    $customer_field['name'] == 'Address 2' ||
                    $customer_field['name'] == 'City' ||
                    $customer_field['name'] == 'Region' ||
                    $customer_field['name'] == 'Country' ||
                    $customer_field['name'] == 'Postal Code' ||
                    $customer_field['name'] == 'Name'
                )) {
                    if (!isset($customer_field['value'])) {
                        if($data['booking_customer']['custom_fields'] && isset($data['booking_customer']['custom_fields'][$customer_field['id']]) && $data['booking_customer']['custom_fields'][$customer_field['id']] != '')
                        {
                            $customer_field['value'] = $data['booking_customer']['custom_fields'][$customer_field['id']];
                        }
                    }
                    $data['booking_customer']['customer_fields'][] = $customer_field;
                }
            }
        }

        $data['custom_booking_fields'] = $this->Booking_field_model->get_booking_fields_data($booking_id,'show_on_registration_card');

        $data['booking_fields'] = $this->Booking_field_model->get_booking_fields($this->company_id,'show_on_registration_card');
        $data['payments'] = $payments; 
        $data['booking_ids'] = $get_bookings_by_group_id;
        $data['group_id'] = $group_id;       
        $data['read_only'] = $read_only;
        //Get invoice autobiography
        $data['invoice_log']    = $this->Invoice_model->get_invoice_log(implode(',',$all_booking_ids));
        $company_data = $this->Company_model->get_company($this->company_id);
        $data['js_files'] = Array(
            base_url() . auto_version('js/channel_manager/channel_manager.js'),
            base_url().'js/moment.min.js',
            base_url() . auto_version('js/booking/printThis.js'),
            base_url() . auto_version('js/booking/bookingModal.js'),
            base_url().auto_version('js/invoice/invoice.js')
        );
        if (!$read_only) {
             $data['selected_menu'] = 'invoices';
             $data['css_files'][]   = base_url().auto_version('css/invoice.css');
             $data['css_files'][]   = base_url().auto_version('css/booking/booking_main.css');
             $data['js_files'][]    = base_url().auto_version('js/invoice/invoice_edit.js');
        
            $this->load->library('PaymentGateway');
        }
        $data['selected_customer_id'] = $customer_id;
        $data['main_content'] = 'invoice/master_invoice';
        $this->load->view('includes/bootstrapped_template', $data);
    }
    
    function update_folio_AJAX()
    {
        $first_folio_id = $last_insert_id = null;
        
        $folio_id = $this->input->post('folio_id');
        $data['folio_name'] = $this->input->post('folio_name');
        $booking_id = $this->input->post('booking_id');
        $customer_id = $this->input->post('customer_id');
        $flag = '';
        if($folio_id)
        {
            $last_insert_id = $this->Folio_model->update_folio($folio_id, $data) ? $folio_id : null;
            $flag = 'update_folio';
        }
        else 
        {
            $existing_folios = $this->Folio_model->get_folios($booking_id, $customer_id);

            if(empty($existing_folios))
            {
                $first_folio['booking_id'] = $this->input->post('booking_id');
                $first_folio['customer_id'] = $this->input->post('customer_id');
                $first_folio['folio_name'] = $data['folio_name'];
                $first_folio_id = $last_insert_id = $this->Folio_model->add_folio($first_folio);
                $flag = 'add_folio';
            }
            else
            {
                $last_insert_id = $this->Folio_model->update_folio($existing_folios[0]['id'], $data) ? $existing_folios[0]['id'] : null;
                $flag = 'update_folio';
            }
        }

        $invoice_log_data = array();
        $invoice_log_data['date_time'] = gmdate('Y-m-d h:i:s');
        $invoice_log_data['booking_id'] = $booking_id;
        $invoice_log_data['user_id'] = $this->session->userdata('user_id');
        $invoice_log_data['action_id'] = (isset($flag) && $flag == 'add_folio') ? ADD_FOLIO : UPDATE_FOLIO;
        if ($last_insert_id) {
            $invoice_log_data['log'] = (isset($flag) && $flag == 'add_folio') ? $data['folio_name'].' Folio Added' : $data['folio_name'].' Folio Updated';
            $this->Invoice_log_model->insert_log($invoice_log_data);
        }

        echo json_encode(array("folio_id" => $last_insert_id, "first_folio_id" => $first_folio_id));
    }

    function add_folio_AJAX($folio_name = null, $booking_id = null, $customer_id = null, $for = null)
    {
        $first_folio_id = $last_insert_id = null;
        
        $data['booking_id'] = $booking_id ? $booking_id : $this->input->post('booking_id');
        $data['customer_id'] = $customer_id ? $customer_id : $this->input->post('customer_id');
        $data['folio_name'] = $folio_name ? $folio_name : $this->input->post('folio_name');

        $first_folio['booking_id'] = $booking_id ? $booking_id : $this->input->post('booking_id');
        $first_folio['customer_id'] = $customer_id ? $customer_id : $this->input->post('customer_id');
        $first_folio['folio_name'] = 'Folio #1';

        $booking_id = $this->input->post('booking_id');
        $customer_id = $this->input->post('customer_id');
        $existing_folios = $this->Folio_model->get_folios($booking_id, $customer_id);

        if(!empty($existing_folios))
        {
            $last_insert_id = $this->Folio_model->add_folio($data);  
        }
        else
        {
            $first_folio_id = $this->Folio_model->add_folio($first_folio);
            $last_insert_id = $this->Folio_model->add_folio($data);
        }

        $invoice_log_data = array();
        $invoice_log_data['date_time'] = gmdate('Y-m-d h:i:s');
        $invoice_log_data['booking_id'] = $booking_id;
        $invoice_log_data['user_id'] = $this->session->userdata('user_id');
        $invoice_log_data['action_id'] = ADD_FOLIO;
        if ($last_insert_id) {
            $invoice_log_data['log'] = $data['folio_name'].' Folio Added';
            $this->Invoice_log_model->insert_log($invoice_log_data);
        }

        if($for)
        {
            return $last_insert_id;
        }
        else
        {
            echo json_encode(array("folio_id" => $last_insert_id, "first_folio_id" => $first_folio_id));
        }
    }

    function get_folios_JSON()
    {
        $folio_id = $this->input->post('folio_id');
        $booking_id = $this->input->post('booking_id');
        $customer_id = $this->input->post('customer_id');
        $folios = $this->Folio_model->folios($folio_id, $booking_id, $customer_id);
        echo json_encode($folios);
    }
    
    function check_EVC_folio($folio_name = null, $booking_id = null, $customer_id = null, $for = null)
    {
        $folio_name = $folio_name ? $folio_name : $this->input->post('folio_name');
        $booking_id = $booking_id ? $booking_id : $this->input->post('booking_id');
        $customer_id = $customer_id ? $customer_id : $this->input->post('customer_id');
        $folios = $this->Folio_model->folios($folio_name, $booking_id, $customer_id);
        if($for)
        {
            return (isset($folios) && $folios) ? $folios[0]['id'] : '0';
        }
        else
        {
            echo (isset($folios) && $folios) ? $folios[0]['id'] : '0';
        }
    }

    function move_charge_payment($folio_id = null, $charge_id = null, $payment_id = null)
    {
        $data['folio_id'] = $folio_id ? $folio_id : $this->input->post('folio_id_to');
        $data['charge_id'] = $charge_id ? $charge_id : $this->input->post('charge_id');
        $data['payment_id'] = $payment_id ? $payment_id : $this->input->post('payment_id');
        $result = $this->Folio_model->update_folio_charge_or_payment($data);
        $this->Folio_model->update_invoice_logs($data);
        if($result > 0) {
            echo "true";    
        }
    }
    
    function get_folio_data()
    {
        $folio_id = $this->input->post('folio_id');
        $booking_id = $this->input->post('booking_id');
        $customer_id = $this->input->post('customer_id');
        $is_first_folio = $this->input->post('is_first_folio') === 'true';
        
        $data = array();
        
        $charges  = $this->Charge_model->get_charges($booking_id, $customer_id, $folio_id);
        
        if($is_first_folio) {
            $charges = array_merge($charges, $this->forecast_charges->_get_forecast_charges($booking_id));
            $charges = array_merge($charges, $this->forecast_charges->_get_forecast_extra_charges($booking_id));
        }
        
        // assign taxes to charges
        foreach ($charges as $index => $charge) {
            $charges[$index]['taxes'] = $this->Charge_type_model->get_taxes($charge['charge_type_id']);
        }
        
        $data['charges']  = $charges;
        
        $payments = $this->Payment_model->get_payments($booking_id, $customer_id, $folio_id);    
        if ($payments) {
            $total_amount = 0;
            foreach($payments as $index => $payment){
                $payments[$index]['remaining_amount'] = '';
                if($payment['payment_status'] == 'charge' ||$payment['payment_status'] == NULL ){
                    $total_amount = $payment['amount'];
                    $charge_id = $payment['gateway_charge_id'];
                    $partial_payments = isset($charge_id) ? $this->Payment_model->get_partial_refunds_by_charge_id($charge_id) : null;

                    $total_partial_amount = 0;
                    if(!empty($partial_payments)){
                        foreach($partial_payments as $partial_payment){
                            $total_partial_amount += $partial_payment['amount'];
                        }
                        $payments[$index]['remaining_amount'] = $total_amount + $total_partial_amount;
                    }
                }
            }
        }
        $data['payments'] = $payments;   
        
        echo json_encode($data);
    }

    function remove_folio_AJAX()
    {
        $response = array("success" => true);
        $folio_id = $this->input->post('folio_id');
        $booking_id = $this->input->post('booking_id');
        $customer_id = $this->input->post('customer_id');

        $folio_data = $this->Folio_model->get_folio_details($folio_id);
        $charges  = $this->Charge_model->get_charges($booking_id, false, $folio_id);
        $payments = $this->Payment_model->get_payments($booking_id, false, $folio_id); 

        if(!empty($charges) || !empty($payments))
        {
            $response['success'] = false;
        }
        else if(empty($charges) && empty($payments))
        {
            $this->Folio_model->remove_folio($folio_id);
            $response['success'] = true;
        }

        $invoice_log_data = array();
        $invoice_log_data['date_time'] = gmdate('Y-m-d h:i:s');
        $invoice_log_data['booking_id'] = $booking_id;
        $invoice_log_data['user_id'] = $this->session->userdata('user_id');
        $invoice_log_data['action_id'] = DELETE_FOLIO;
        if ($response['success']) {
            $invoice_log_data['log'] = $folio_data['folio_name'].' Folio Deleted';
            $this->Invoice_log_model->insert_log($invoice_log_data);
        }
        echo json_encode($response);
    }

    public function is_payment_available()
    {  
        //itodo can be forged
        $this->load->library('PaymentGateway');
        $booking_id = $this->input->post('booking_id');
        $customer_id = $this->input->post('customer_id');

        if($this->current_payment_gateway){
            $this->load->library('../extensions/'.$this->current_payment_gateway.'/libraries/ProcessPayment');
            $available  = $this->processpayment->isGatewayPaymentAvailableForBooking($booking_id, $customer_id);
        } else {
            $available  = $this->paymentgateway->isGatewayPaymentAvailableForBooking($booking_id, $customer_id);
        }

        if(
            $this->current_payment_gateway == 'stripe-integration' && 
            is_array($available) && 
            $available['type'] == 'invalid_request_error') {

            $error = $available;

            echo json_encode(array('success' => false, 'error' => $error));
        } else {
            echo 1;
        }
    }

    function save_invoice()
    {

        $user_id = $this->session->userdata('user_id');
        $booking_id = $this->input->post('booking_id');
        $folio_id = $this->input->post('folio_id');
        $company_id = $this->Company_model->get_company_id_by_booking_id($booking_id); // i should acquire this through model
        $customer = $this->Booking_model->get_booking($booking_id);
        $customer_id = $customer['booking_customer_id'];
        $card_data = isset($customer_id) ? $this->Card_model->get_active_card($customer_id, $company_id) : null;
        // add new charge/payments, no permission rule applied here.
        $charges = $this->input->post('charges');

        $is_extra_pos = $this->input->post('is_extra_pos');


        if($charges && count($charges) > 0)
        {

            foreach ($charges as $key => $charge) {
                 // prx($charge);
                
                $charge['user_id'] = $user_id;

                $is_pos = false;
                $quantity = isset($charge['qty']) ? $charge['qty'] : 1;
                
                if(!$is_extra_pos){
                    $isRoomChargeType = $charge['isRoomChargeType'];
                    unset($charge['isRoomChargeType']);
                    $charge['selling_date'] = date('Y-m-d', strtotime($charge['selling_date']));
                    $is_pos = false;
                } else {
                    $charge['selling_date'] = date('Y-m-d', strtotime($this->selling_date));
                    $charge['booking_id'] = $booking_id;
                    $charge['customer_id'] = $customer_id;
                    $charge['description'] = (isset($charge['qty']) && $charge['qty'] > 1) ? $charge['description']." (". $charge['qty'] .")" : $charge['description'];
                    unset($charge['extra_id']);
                    unset($charge['extra_qty']);
                    unset($charge['qty']);
                    $is_pos = true;
                }

                $charge_id = $this->Charge_model->insert_charge($charge);

                $post_charge_data = $charge;
                $post_charge_data['charge_id'] = $charge_id;
                // $post_charge_data['qty'] = $quantity;

                
                do_action('post.create.charge', $post_charge_data);

                $charge_action_data = array('charge_id' => $charge_id, 'charge' => $charge, 'company_id'=> $this->company_id, 'is_pos' => $is_pos,'qty'=>$quantity);
                do_action('post.add.charge', $charge_action_data);

                if(isset($card_data['evc_card_status']) && $card_data['evc_card_status'] && $isRoomChargeType && false){
                    // check folio is exist or not
                    $evc_folio_id = $this->check_EVC_folio('Expedia EVC', $booking_id, $customer_id, true);
                    if(isset($evc_folio_id) && $evc_folio_id)
                    {
                        $folio_id = $evc_folio_id;
                        $this->move_charge_payment($folio_id, $charge_id, null);
                    }
                    else
                    {
                        $folio_id = $this->add_folio_AJAX('Expedia EVC', $booking_id, $customer_id, true);
                        $this->move_charge_payment($folio_id, $charge_id, null);
                    }
                }
                
                // check if evc booking, then check if EVC FOLIO exist ? move to EVE : create EVC Folio and move
                // else
                $folio_id = $folio_id ? $folio_id : 0;
                $charge_folio_id = $this->Charge_model->insert_charge_folio($charge_id , $folio_id);
                //invoice log 
                $invoice_log_data = array();
                $invoice_log_data['date_time'] = gmdate('Y-m-d h:i:s');
                $invoice_log_data['booking_id'] = $booking_id;
                $invoice_log_data['user_id'] = $user_id;
                $invoice_log_data['action_id'] = ADD_CHARGE;
                $invoice_log_data['charge_or_payment_id'] = $charge_id;
                $invoice_log_data['new_amount'] = $charge['amount'];
                // Added item description in invoice log
                $invoice_log_data['log'] = ($is_extra_pos) ? $charge['description']. ' Charge Added' : 'Charge Added';

                $this->Invoice_log_model->insert_log($invoice_log_data);   
            }
        }
           
        // update changes only if the user has a permission
        $charge_changes = $this->input->post('charge_changes');
        if($charge_changes && count($charge_changes) > 0)
        {
            foreach ($charge_changes as $key => $charge) {
                $invoice_log_data = array();
                $invoice_log_data['date_time'] = gmdate('Y-m-d h:i:s');
                $invoice_log_data['booking_id'] = $booking_id;
                $invoice_log_data['user_id'] = $user_id;
                $invoice_log_data['action_id'] = EDIT_CHARGE;
                $invoice_log_data['charge_or_payment_id'] = isset($charge['charge_id']) ? $charge['charge_id'] : '0';
                $invoice_log_data['new_amount'] = isset($charge['amount']) ? $charge['amount'] : '';
                $invoice_log_data['log'] = 'Charge Updated';
                $this->Invoice_log_model->insert_log($invoice_log_data); 

                if(isset($charge['selling_date']) && $charge['selling_date']){
                    $charge_changes[$key]['selling_date'] = get_base_formatted_date($charge['selling_date']);
                }

                $charge_id = $charge['charge_id'];
            }
        }
        $this->Invoice_model->update_charges($charge_changes);

        $post_charge_data = $charge_changes;
        do_action('post.update.charge', $post_charge_data);
        
        $this->Booking_model->update_booking_balance($booking_id);
        echo $charge_id;
    }
    
    function get_invoice_history_AJAX()
    {
        $booking_id = $this->input->post('booking_id');
        $logs = $this->Invoice_log_model->get_invoice_logs($booking_id);

        foreach ($logs as $index => $log) {
            // apply timezone
            $date = convert_to_local_time(new DateTime($log['date_time'], new DateTimeZone('UTC')), $this->Company_model->get_time_zone($this->company_id)); // Apply time zone
            $logs[$index]['date_time'] = $date->format('Y-m-d H:i:s');
        }

        echo json_encode($logs);
    }
    
    function insert_payment_AJAX() {

        $group_id = $this->input->post('group_id');
        
        if($group_id)
        {
            $selling_date = sqli_clean($this->security->xss_clean($this->input->post('payment_date')));
            $payment_type_id = sqli_clean($this->security->xss_clean($this->input->post('payment_type_id')));
            $customer_id = sqli_clean($this->security->xss_clean($this->input->post('customer_id')));
            $total_balance = sqli_clean($this->security->xss_clean($this->input->post('payment_amount')));
            $description = sqli_clean($this->security->xss_clean($this->input->post('description')));
            $cvc = sqli_clean($this->security->xss_clean($this->input->post('cvc')));
            $distribute_equal_amount = $this->input->post('payment_distribution');
            $folio_id = sqli_clean($this->security->xss_clean($this->input->post('folio_id')));
            $capture_payment = sqli_clean($this->security->xss_clean(trim($this->input->post('capture_payment_type'))));

            $get_bookings_by_group_id = $this->Booking_model->get_bookings_by_group_id($group_id, true);
            // prx($get_bookings_by_group_id);
            $remaining_balance = $total_balance;
            $no_of_bookings = count($get_bookings_by_group_id);
            $equal_amount = $total_balance / $no_of_bookings;
            $round_total_amount = (round($equal_amount,2)) * $no_of_bookings;
            $amount_diff = round(($round_total_amount - $total_balance), 2);
            
            $company_data =  $this->Company_model->get_company($this->company_id);
            $capture_payment_type = $company_data['manual_payment_capture'];
            $capture_payment_type = ($capture_payment != 'authorize_only') ? false : true;
            $use_gateway = $this->input->post('use_gateway');

            if($use_gateway == 1 &&
            (isset($this->is_nestpay_enabled) && $this->is_nestpay_enabled == true) ||
            (isset($this->is_nestpaymkd_enabled) && $this->is_nestpaymkd_enabled == true) ||
            (isset($this->is_nestpayalb_enabled) && $this->is_nestpayalb_enabled == true) ||
            (isset($this->is_nestpaysrb_enabled) && $this->is_nestpaysrb_enabled == true)
             ) {

                $data = Array(
                    "user_id" => $this->user_id,
                    "booking_id" => $group_id,
                    "selling_date" => $selling_date,
                    "customer_id" => $customer_id,
                    "amount" => $total_balance,
                    "payment_type_id" => $payment_type_id,
                    "description" => $description,
                    "date_time" => gmdate("Y-m-d H:i:s"),
                    "selected_gateway" => $this->input->post('selected_gateway'),
                );
                $card_data = $this->Card_model->get_active_card($customer_id, $this->company_id);
                $data['credit_card_id'] = "";
                if(isset($card_data) && $card_data){
                     $data['credit_card_id'] = $card_data['id'];
                }
                
                if($data['amount'] > 0)
                {
                    $response = $this->Payment_model->insert_payment($data, $cvc, $capture_payment_type);
                    $get_bookings_by_group_id = $this->Booking_model->get_bookings_by_group_id($group_id, true);
                
                    $no_of_bookings = count($get_bookings_by_group_id);
                    $equal_amount = $total_balance / $no_of_bookings;
                   $payment_type    = $this->paymentgateway->getPaymentGatewayPaymentType($this->input->post('selected_gateway'));
                    $payment_type_ids = $payment_type['payment_type_id'];
                    foreach ($get_bookings_by_group_id as $booking)
                    {
                    
                        $data1 = Array(
                        "user_id" => $this->user_id,
                        "booking_id" => $booking['booking_id'],
                        "selling_date" => $selling_date,
                        "customer_id" => $customer_id,
                        "amount" => $equal_amount,
                        "payment_type_id" => $payment_type_ids,
                        "description" => 'PostAuth Approved ',
                        "date_time" => gmdate("Y-m-d H:i:s"),
                        "payment_gateway_used" =>'nestpay',
                        "gateway_charge_id" => $response['gateway_charge_id'],
                        "payment_status"=> 'charge',
                        "is_captured" => 1
                        );
                        
                        $this->db->insert('payment', $data1);  
                       
                    }
                }
                else
                {
                    $response = array('success' => false, 'message' => 'All the bookings in this group are already Paid in full.');
                }

            } else {
            $i = 1;
            $response = array();
            foreach ($get_bookings_by_group_id as $booking)
            {
                $balance = $booking['balance'];
                
                if($distribute_equal_amount == "Yes")
                {
                    if($i == $no_of_bookings && $amount_diff != 0)
                    {
                        $amount = $equal_amount - $amount_diff;
                        $amount = round($amount, 2);
                    }
                    else
                    {
                        $amount = round($equal_amount, 2);
                    }                
                }
                else
                {
                    $amount = $balance;
                    if($remaining_balance < $balance)
                    {
                        $amount = $remaining_balance;
                    }
                }
                if($remaining_balance <= 0){
                    break;
                }
                
                $data = Array(
                    "user_id" => $this->user_id,
                    "booking_id" => $booking['booking_id'],
                    "selling_date" => $selling_date,
                    "customer_id" => $customer_id,
                    "amount" => $amount,
                    "payment_type_id" => $payment_type_id,
                    "description" => $description,
                    "date_time" => gmdate("Y-m-d H:i:s"),
                    "selected_gateway" => $this->input->post('selected_gateway'),
                );
                $card_data = $this->Card_model->get_active_card($customer_id, $this->company_id);
                $data['credit_card_id'] = "";
                if(isset($card_data) && $card_data){
                     $data['credit_card_id'] = $card_data['id'];
                }
                
                if($data['amount'] > 0)
                {
                    $response = $this->Payment_model->insert_payment($data, $cvc, $capture_payment_type);
                }
                else
                {
                    $response = array('success' => false, 'message' => 'All the bookings in this group are already Paid in full.');
                }
                if($response['success'] == false){
                    $response[] = array(
                                    "booking_id" => $booking['booking_id'],
                                    "error_msg" =>  $response['message']
                                );
                }
                elseif($response['success'])
                {

                    $post_payment_data = $response;
                    $post_payment_data['payment_id'] = $response['payment_id'];

                    do_action('post.create.payment', $post_payment_data);

                    $invoice_log_data = array();
                    $invoice_log_data['date_time'] = gmdate('Y-m-d h:i:s');
                    $invoice_log_data['booking_id'] = $booking['booking_id'];
                    $invoice_log_data['user_id'] = $this->session->userdata('user_id');
                    $invoice_log_data['action_id'] = $company_data['manual_payment_capture'] ? AUTHORIZED_PAYMENT : CAPTURED_PAYMENT;
                    $invoice_log_data['charge_or_payment_id'] = $response['payment_id'];
                    $invoice_log_data['new_amount'] = $amount;
                    if($invoice_log_data['charge_or_payment_id'])
                    {
                        $this->Payment_model->insert_payment_folio(array('payment_id' => $response['payment_id'], 'folio_id' => $folio_id));
                        
                        $invoice_log_data['log'] = $company_data['manual_payment_capture'] ? 'Payment Authorized' : 'Payment Captured';
                        $this->Invoice_log_model->insert_log($invoice_log_data);
                    }
                    $this->Booking_model->update_booking_balance($booking['booking_id']);
                }
                $i++;
                $remaining_balance -= $amount; 
            }
            }
            if (!empty($response)) {
                echo json_encode($response);
            }
        }
        else
        {
            $data = Array(
                "user_id" => $this->session->userdata('user_id'),
                "booking_id" => $this->input->post('booking_id'),
                "selling_date" => date('Y-m-d', strtotime($this->input->post('payment_date'))),
                "amount" => $this->input->post('payment_amount'),
                "customer_id" => $this->input->post('customer_id'),
                "payment_type_id" => $this->input->post('payment_type_id'),
                "description" => $this->input->post('description'),
                "date_time" => gmdate("Y-m-d H:i:s"),
                "selected_gateway" => $this->input->post('selected_gateway'),
                "capture_payment_type" => trim($this->input->post('capture_payment_type'))
            );
        
            $payment_folio_id = $this->input->post('folio_id');
            $payment_folio_id = $payment_folio_id ? $payment_folio_id : 0;
            $card_data = $this->Card_model->get_active_card($data['customer_id'], $this->company_id);
            $data['credit_card_id'] = null;
            if (isset($card_data) && $card_data) {
                $data['credit_card_id'] = $card_data['id'];
            }
            $company_data =  $this->Company_model->get_company($this->company_id);
            $capture_payment_type = $company_data['manual_payment_capture'];
            $capture_payment_type = ($data['capture_payment_type'] != 'authorize_only') ? false : true;
            $cvc = $this->input->post('cvc');
            unset($data['capture_payment_type']);
            $response = $this->Payment_model->insert_payment($data, $cvc, $capture_payment_type);

            $post_payment_data = $response;
            $post_payment_data['payment_id'] = $response['payment_id'];

            do_action('post.create.payment', $post_payment_data);

            if (isset($card_data['evc_card_status']) && $card_data['evc_card_status'] && false) {
                // check folio is exist or not
                $evc_folio_id = $this->check_EVC_folio('Expedia EVC', $data['booking_id'], $data['customer_id'], true);
                if (isset($evc_folio_id) && $evc_folio_id) {
                    $payment_folio_id = $evc_folio_id;
                    $this->move_charge_payment($payment_folio_id, null, $response['payment_id']);
                } else {
                    $folio_id = $this->add_folio_AJAX('Expedia EVC', $data['booking_id'], $data['customer_id'], true);
                    $payment_folio_id = $folio_id;
                    $this->move_charge_payment($payment_folio_id, null, $response['payment_id']);
                }
            }

            $invoice_log_data = array();
            $invoice_log_data['date_time'] = gmdate('Y-m-d h:i:s');
            $invoice_log_data['booking_id'] = $this->input->post('booking_id');
            $invoice_log_data['user_id'] = $this->session->userdata('user_id');
            $invoice_log_data['action_id'] = $company_data['manual_payment_capture'] ? AUTHORIZED_PAYMENT : CAPTURED_PAYMENT;
            $invoice_log_data['charge_or_payment_id'] = $response['payment_id'];
            $invoice_log_data['new_amount'] = $this->input->post('payment_amount');
            if ($response['success'] && $invoice_log_data['charge_or_payment_id']) {
                $this->Payment_model->insert_payment_folio(array('payment_id' => $response['payment_id'], 'folio_id' => $payment_folio_id));
                $invoice_log_data['log'] = $company_data['manual_payment_capture'] ? 'Payment Authorized' : 'Payment Captured';
                $this->Invoice_log_model->insert_log($invoice_log_data);
            }
            else {
                $invoice_log_data['charge_or_payment_id'] = 0;
                $invoice_log_data['log'] = isset($response['message']) && $response['message'] ? $response['message'] : '';
                $this->Invoice_log_model->insert_log($invoice_log_data);

            }

            $this->Booking_model->update_booking_balance($this->input->post('booking_id'));

            echo json_encode($response);
        }
    }

    function email_invoice($booking_id, $folio_id = null) {
        // Security measure: Make sure the booking_id belongs to the logged in user's company
        if ($this->Booking_model->booking_belongs_to_company($booking_id, $this->company_id))
        {
            $this->load->library('email_template');
            $this->email_template->send_invoice_email($booking_id, $folio_id);
        }
        else
        {
            echo l("ERROR: booking doesn't belong to company",true);
        }
    }

    function email_feedback($booking_id, $folio_id = null) {
        // Security measure: Make sure the booking_id belongs to the logged in user's company
        if ($this->Booking_model->booking_belongs_to_company($booking_id, $this->company_id))
        {
            $this->load->library('email_template');
            $this->email_template->send_rating_email($booking_id, $folio_id);
        }
        else
        {
            echo l("ERROR: booking doesn't belong to company",true);
        }
    }

    function email_master_invoice($booking_id, $group_id) {
        // Security measure: Make sure the booking_id belongs to the logged in user's company
        if ($this->Booking_model->booking_belongs_to_company($booking_id, $this->company_id)) {
            $this->load->library('email_template');
            $this->email_template->send_master_invoice_email($booking_id, $group_id);
        }
        else {
            echo l("ERROR: booking doesn't belong to company",true);
        }
    }
    
    // returns JSON array of charge types. []
    function get_charge_types_in_JSON() {
        $charge_types = $this->Charge_type_model->get_charge_types($this->company_id);
        $json = json_encode($charge_types);
        echo $json;
    }

    // returns JSON array of charge types. []
    function get_customers_in_JSON() {
        $booking_id = $this->input->post('booking_id'); 
        $customers = $this->Customer_model->get_staying_customers($booking_id);
        $booking = $this->Booking_model->get_booking_detail($booking_id);
        if (isset($booking['booking_customer_id']))
        {
            $customers[] = $this->Customer_model->get_customer($booking['booking_customer_id']);
        }
        $json = json_encode($customers);
        echo $json;
    }

    // returns JSON array of tax table. [name, tax_type_id, percentage]
    function get_taxes_AJAX() {
        $charge_type_id = $this->input->post('charge_type_id'); 
        $amount = $this->input->post('amount'); 
        $json = json_encode($this->Charge_type_model->get_taxes($charge_type_id, $amount));
        echo $json;
    }
    
    // when user clicks on x-button beside charge
    function delete_charge_JSON() {
        $charge_id = $this->input->post('charge_id');   
        $company_id = $this->session->userdata('current_company_id');
        $booking_id = $this->Charge_model->get_booking_id_by_charge_id($charge_id);
        $charge_amount = $this->Charge_model->get_amount_by_charge_id($charge_id);
        
        if ($this->Booking_model->get_booking_detail($booking_id)) {                
            $this->Charge_model->delete_charge($charge_id, $company_id);
            
            $post_charge_data['charge_id'] = $charge_id;
            do_action('post.delete.charge', $post_charge_data);
        }
        $this->Booking_model->update_booking_balance($booking_id);

        $charge_action_data = array('charge_id' => $charge_id);
        do_action('post.delete.charge', $charge_action_data);
            
        $invoice_log_data = array();
        $invoice_log_data['date_time'] = gmdate('Y-m-d h:i:s');
        $invoice_log_data['booking_id'] = $booking_id;
        $invoice_log_data['user_id'] = $this->session->userdata('user_id');
        $invoice_log_data['action_id'] = DELETE_CHARGE;
        $invoice_log_data['charge_or_payment_id'] = $charge_id;
        $invoice_log_data['new_amount'] = $charge_amount;
        $invoice_log_data['log'] = 'Charge Deleted';
        $this->Invoice_log_model->insert_log($invoice_log_data);
    }

    function refund_payment_JSON()
    {
        $payment_id = $this->input->post('payment_id');
        $refund_amount = $this->input->post('amount');
        $folio_id = $this->input->post('folio_id');
        $payment = $this->Payment_model->get_payment($payment_id);
        $payment_type = $this->input->post('payment_type');
        $refund = $this->Payment_model->refund_payment($payment_id, $refund_amount, $payment_type, $payment['booking_id'], $folio_id);
        
        if (isset($refund['success']) && !$refund['success']) {
            echo json_encode($refund);
            return;
        }

        $post_payment_data = $refund;
        $post_payment_data['payment_id'] = $payment_id;

        do_action('post.create.payment', $post_payment_data);
        
        $this->Booking_model->update_booking_balance($payment['booking_id']);
        
        if($payment_type == 'full')
        {
            $new_amount = $payment['amount'];
            $action_id = REFUND_FULL_PAYMENT;
            $log = "Payment Fully Refunded";
        }
        else if($payment_type == 'partial')
        {
            $new_amount = $refund_amount;
            $action_id = REFUND_PARTIAL_PAYMENT;
            $log = "Payment Partially Refunded";
        }
        else if($payment_type == 'remaining')
        {
            $new_amount = $refund_amount;
            $action_id = REFUND_PARTIAL_PAYMENT;
            $log = "Payment Partially Refunded";
        }

        $invoice_log_data = array();
        $invoice_log_data['date_time'] = gmdate('Y-m-d h:i:s');
        $invoice_log_data['booking_id'] = $payment['booking_id'];
        $invoice_log_data['user_id'] = $this->session->userdata('user_id');
        $invoice_log_data['action_id'] = $action_id;
        $invoice_log_data['charge_or_payment_id'] = $payment_id;
        $invoice_log_data['new_amount'] = $new_amount;
        $invoice_log_data['log'] = $log;
        $this->Invoice_log_model->insert_log($invoice_log_data);
        
        echo json_encode($refund);
    }

    // when user clicks on x-button beside payment
    function delete_payment_JSON()
    {
        $payment_id = $this->input->post('payment_id');
        $payment = $this->Payment_model->get_payment($payment_id);
        // if the user permission level above employee, and the booking belongs to the company
        $this->Payment_model->delete_payment($payment_id);

        $post_payment_data['payment_id'] = $payment_id;
        do_action('post.delete.payment', $post_payment_data);

        $this->Booking_model->update_booking_balance($payment['booking_id']);        
        $invoice_log_data = array();
        $invoice_log_data['date_time'] = gmdate('Y-m-d h:i:s');
        $invoice_log_data['booking_id'] = $payment['booking_id'];
        $invoice_log_data['user_id'] = $this->session->userdata('user_id');
        $invoice_log_data['action_id'] = DELETE_PAYMENT;
        $invoice_log_data['charge_or_payment_id'] = $payment_id;
        $invoice_log_data['new_amount'] = $payment['amount'];
        $invoice_log_data['log'] = 'Payment Deleted';
        $this->Invoice_log_model->insert_log($invoice_log_data);
    }
    public function get_payment_capture()
    {   
        $this->ci->load->library('encrypt');
        $payment_id = $this->input->post('payment_id');
        $amount = $this->input->post('amount');
        // $amount = abs($amount) * 100; // in cents, only positive
        $amount = abs($amount);
        $auth_id = $this->input->post('auth_id');
        $booking_id = $this->input->post('booking_id');
        $customer_id = $this->input->post('customer_id');
        $company_id = $this->company_id;  
        $currency  = $this->Currency_model->get_default_currency($company_id); 
        $company_data = $this->Payment_gateway_model->get_payment_gateway_settings($company_id);
        $customer = $this->Customer_model->get_customer_info($customer_id); 
        $is_partial = $this->input->post('amount_status');
                
        unset($customer['cc_number']);
        unset($customer['cc_expiry_month']);
        unset($customer['cc_expiry_year']);
        unset($customer['cc_tokenex_token']);
        unset($customer['cc_cvc_encrypted']);
        $card_data = $this->Card_model->get_active_card($customer_id,$company_id);  

        if(isset($card_data) && $card_data){
            $customer['cc_number'] = $card_data['cc_number'];
            $customer['cc_expiry_month'] = $card_data['cc_expiry_month'];
            $customer['cc_expiry_year'] = $card_data['cc_expiry_year'];
            $customer['cc_tokenex_token'] = $card_data['cc_tokenex_token'];
            $customer['cc_cvc_encrypted'] = $card_data['cc_cvc_encrypted'];
            $cvc = $this->encrypt->decode($customer['cc_cvc_encrypted'], $customer['cc_tokenex_token']);         
        }  
        
        // $capture_data = $this->tokenex->make_payment($company_data['selected_payment_gateway'], $amount, $currency['currency_code'], $customer_id, $booking_id, $cvc, $auth_id, true, false);
        $this->ci->load->library('../extensions/'.$this->current_payment_gateway.'/libraries/ProcessPayment');
        $capture_data = $this->processpayment->capture_payment($payment_id, $amount);


        if(isset($capture_data['charge_id']) && $capture_data['charge_id'])
        {           
            $capture_updates = array(
                     'is_captured' => 1,
                     'gateway_charge_id' => $capture_data['charge_id']
                    );
            if($is_partial=='partial')
            {
                // $capture_updates['amount'] = abs($amount) / 100;
                $capture_updates['amount'] = abs($amount);

            }
            $update = $this->Invoice_model->update_capture_payments($payment_id, $capture_updates);
            
            $invoice_log_data = array();
            $invoice_log_data['date_time'] = gmdate('Y-m-d h:i:s');
            $invoice_log_data['booking_id'] = $booking_id;
            $invoice_log_data['user_id'] = $this->session->userdata('user_id');
            $invoice_log_data['action_id'] = $company_data['manual_payment_capture'] ? AUTHORIZED_PAYMENT : CAPTURED_PAYMENT;
            $invoice_log_data['charge_or_payment_id'] = $payment_id;
            // $invoice_log_data['new_amount'] = abs($amount) / 100;
            $invoice_log_data['new_amount'] = abs($amount) ;
            $invoice_log_data['log'] = 'Payment Captured';
            $this->Invoice_log_model->insert_log($invoice_log_data);

            $data = array("success" => true, "message" => "Payment Captured Successfully.");
            $responce = json_encode($data);
            echo $responce;                 
        }
        else
        {
            $data = array("success" => false, "message" => $capture_data['message']);            
            $responce = json_encode($data);
            echo $responce;         
        }
    }

     // when user clicks on x-button beside payment
     function void_payment()
     {
        $payment_id = $this->input->post('payment_id');
        $payment = $this->Payment_model->get_payment($payment_id);
        $void = $this->Payment_model->void_payment($payment_id); 

        $post_payment_data = $payment;
        $post_payment_data['payment_id'] = $payment_id;

        do_action('post.update.payment', $post_payment_data);

        if (isset($void['success']) && !$void['success']) {
            echo json_encode($void);
            return;
        }
    
        $this->Booking_model->update_booking_balance($payment['booking_id']);
        $invoice_log_data = array();
        $invoice_log_data['date_time'] = gmdate('Y-m-d h:i:s');
        $invoice_log_data['booking_id'] = $payment['booking_id'];
        $invoice_log_data['user_id'] = $this->session->userdata('user_id');
        $invoice_log_data['action_id'] = VOID_PAYMENT;
        $invoice_log_data['charge_or_payment_id'] = $payment_id;
        $invoice_log_data['new_amount'] =  -$payment['amount'];
        $invoice_log_data['log'] = 'Payment voided';
        $this->Invoice_log_model->insert_log($invoice_log_data);
        
        echo json_encode($void);   
    
    }
      
}

