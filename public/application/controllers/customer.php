<?php

/**
 * @property PaymentGateway paymentgateway
 */
class Customer extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        
        $this->load->library('encrypt');
        $this->load->library('form_validation');
        $this->load->library('PaymentGateway');
        $this->load->library('tokenex');
        
        $this->load->model('Company_model');
        $this->load->model('Customer_model');
        $this->load->model('Customer_type_model');
        $this->load->model('Customer_field_model');
        $this->load->model('Customer_log_model');
        $this->load->model('User_model');       
        $this->load->model('Booking_model');        
        $this->load->model('Payment_model');        
        $this->load->model('Tax_model');
        $this->load->model('Group_model');
        $this->load->model('Charge_model');
        $this->load->model('Statement_model');
        $this->load->model('Rate_plan_model');
        $this->load->model('Invoice_log_model');
        $this->load->model('Card_model');
        $this->load->model('Folio_model');
        $this->load->model('Booking_log_model');
        
        $this->load->helper('timezone');
        $this->load->helper('url');
        
    }
    
    function index()
    {
        //Preset customer list to show customers by balance
        $this->session->set_userdata('customer_search_query', '');
        $this->session->set_userdata('customer_order_by', 'balance');
        $this->session->set_userdata('customer_order', 'DESC');
        $this->session->set_userdata('booking_order', 'ASC');
        $this->show_customers();
    }
    
    function get_credit_card_frame()
    {
        if(!$this->input->is_ajax_request()){
            return;
        }
        $response = $this->tokenex->_iframe_session_tokenex();
        echo json_encode($response);
    }

    function get_credit_card_number()
    {
        if(function_exists('show_cc_details')){
            $customer_pci_token = $this->input->get('customer_pci_token');
            $response = show_cc_details($customer_pci_token);

            echo $response;
        }
   }

    /**
     * Show customer page includes: customer/guest lists, customer information and booking list
     */
    function show_customers()
    {
        $config['per_page'] = (sqli_clean($this->security->xss_clean($this->input->get('per_page'))) ? sqli_clean($this->security->xss_clean($this->input->get('per_page'))) : '30');
        $config['uri_segment'] = 3; // uri_segment is used to tell pagination which page we're on. it seems that default is 3.
        $config['base_url'] = base_url() . "customer/show_customers";
        $config['suffix'] = '?'.http_build_query($_GET, '', "&");
        
        // pagination stuff
        $filters['company_id'] = $this->company_id;
        $filters['order_by'] = $this->session->userdata('customer_order_by');
        $filters['order'] = $this->session->userdata('customer_order');
        $filters['per_page'] = $config['per_page'];
        $filters['offset'] = $this->uri->segment(3);
        $filters['submit'] = $this->security->xss_clean($this->input->get('submit'));
        $filters['customer_type_id'] = sqli_clean($this->security->xss_clean($this->input->get('customer_type_id')));
        $filters['show_deleted'] = sqli_clean($this->security->xss_clean($this->input->get('show_deleted')));
        $filters['search_query'] = sqli_clean($this->security->xss_clean($this->input->get('search_query')));
        $filters['selling_date'] = ($this->security->xss_clean($this->input->get('date')) ? sqli_clean($this->security->xss_clean($this->input->get('date'))) : "");
        $view_data['rows'] = $this->Customer_model->get_customers($filters);
        $config['total_rows'] = $this->Customer_model->get_found_rows();
        // $config['total_rowsfilters'] = $this->Customer_model->get_found_rows();
       
        $this->load->library('pagination');
        $this->pagination->initialize($config);
        
        //Load view data
        $view_data['js_files'] = array(
            base_url() . auto_version('js/customer/customer_main.js'),
            base_url() . auto_version('js/customer/customerModal.js'),
            base_url() . auto_version('js/card_detail/cardModal.js')
        );
        
        $view_data['menu_on'] = true;

        $view_data['customer_types'] = $this->Customer_type_model->get_customer_types($this->company_id);

        $common_customer_types = json_decode(COMMON_CUSTOMER_TYPES, true);

        foreach($common_customer_types as $id => $name) {
            $view_data['customer_types'][] = array(
                                                'id' => $id,
                                                'name' => $name,
                                                'company_id' => $this->company_id,
                                                'is_deleted' => 0,
                                                'is_common_type' => true,
                                                'sort_order' => 0
                                            );
        }

        

        $view_data['selected_menu'] = 'customers';
        $view_data['main_content'] = 'customer/customer_main';
        $this->load->view('includes/bootstrapped_template',$view_data + $filters);
    }   
    

    // This is the main customer page that the user is taken to after clicking on a customer from a customer list page.
    function history()
    {
        //############################## customer information form ##############################
        $segment_array = $this->uri->segment_array();
        $last_segment_index = sizeof($segment_array);

        $customer_id = $this->uri->segment(3);
        $base_url = base_url() . "customer/history/".$customer_id;
        $segment_four = $this->uri->segment(4);
        $pagination_uri_segment = 4;
        $order_by = '';
        $order = '';
        if($segment_four && !is_numeric($segment_four))
        {
            $order_by = $this->uri->segment(4);
            $order = $this->uri->segment(5);
            $order = $order && !is_numeric($order) ? $order : "";
            $pagination_uri_segment = $order ? 6 : 5;
            $base_url .= $order_by ? "/".$order_by : "";
            $base_url .= $order ? "/".$order : "";
        }
        
        $config['per_page'] = '30';        
        $config['uri_segment'] = $pagination_uri_segment; // uri_segment is used to tell pagination which page we're on. it seems that default is 3.
        $offset = $this->uri->segment($pagination_uri_segment);
        $config['base_url'] = $base_url;
        $config['suffix'] = '?'.http_build_query($_GET, '', "&");
        $config['first_url'] = $base_url . $config['suffix'];

        $data['order'] = $order;
        $data['groups'] = $this->Group_model->get_groups();
        $global_data['menu_on'] = true;         
        $global_data['submenu'] = 'includes/submenu.php';
        $global_data['submenu_parent_url'] = base_url()."customer/";
        $global_data['menu_items'] = $this->Menu_model->get_menus(array('parent_id' => 2, 'wp_id' => 1));
        $data['selected_submenu'] = 'Booking History';
        $this->load->vars($global_data);
        
        $this->permission->check_access_to_customer_id($this->user_id, $customer_id);

        if (isset($order)) 
        {           
            if ($order == 'asc')
                $this->session->set_userdata('booking_order', 'ASC');           
            else
                $this->session->set_userdata('booking_order', 'DESC');          
        } else 
            $this->session->set_userdata('booking_order', 'DESC');
        
        $order = $this->session->userdata('booking_order');
        if ($order_by == '')
            $order_by = "balance $order, booking_id";
        else{
            $order_by = "$order_by $order, booking_id";
        }
        
        $data['company'] = $this->Company_model->get_company($this->company_id);
        $data['customer_detail'] = $this->Customer_model->get_customer_info($customer_id);
        
            unset($data['customer_detail']['cc_number']);
            unset($data['customer_detail']['cc_expiry_month']);
            unset($data['customer_detail']['cc_expiry_year']);
            unset($data['customer_detail']['cc_tokenex_token']);
            unset($data['customer_detail']['cc_cvc_encrypted']);
            
            $card_data = $this->Card_model->get_active_card($customer_id, $this->company_id);
           
            if(isset($card_data) && $card_data){
                $data['customer_detail']['cc_number'] = $card_data['cc_number'];
                $data['customer_detail']['cc_expiry_month'] = $card_data['cc_expiry_month'];
                $data['customer_detail']['cc_expiry_year'] = $card_data['cc_expiry_year'];
                $data['customer_detail']['cc_tokenex_token'] = $card_data['cc_tokenex_token'];
                $data['customer_detail']['cc_cvc_encrypted'] = $card_data['cc_cvc_encrypted'];
            }
       
        $search_filter_data['submit'] = $this->security->xss_clean($this->input->get('submit'));
        $search_filter_data['start_date'] = sqli_clean($this->security->xss_clean($this->input->get('start-date')));
        $search_filter_data['end_date'] = sqli_clean($this->security->xss_clean($this->input->get('end-date')));
        $search_filter_data['show_paid'] = sqli_clean($this->security->xss_clean($this->input->get('show_paid')));
        $search_filter_data['status'] = sqli_clean($this->security->xss_clean($this->input->get('status')));
        $search_filter_data['group'] = $this->input->get('group');
        $search_filter_data['statement_date'] = sqli_clean($this->security->xss_clean(trim($this->input->get('statement_date'))));
        $search_filter_data['in_statement'] = sqli_clean($this->security->xss_clean(trim($this->input->get('in_statement'))));
        $search_filter_data['linked_group_id'] = sqli_clean($this->security->xss_clean(trim($this->input->get('linked_group_id'))));
        $search_filter_data['staying_guest_name'] = sqli_clean($this->security->xss_clean(trim($this->input->get('staying_guest_name'))));
        
        switch ($search_filter_data['status']) {        
            case "reservation" : $state = '0'; break;
            case "checkin" : $state = '1'; break;
            case "checkout" : $state = '2'; break;
            case "cancelled" : $state = '4'; break;
            default: $state = 'all'; break;
        }
        
        $filters = array(
            'start_date' => $search_filter_data['start_date'],
            'end_date' => $search_filter_data['end_date'],
            'show_paid' => $search_filter_data['show_paid'],
            'order_by' => $order_by,
            'order' => "DESC", // this is for booking id, for balance we appended it in order_by
            'per_page' => $config['per_page'], // pagination stuff - deprecated
            'offset' => $offset,
            'state' => $state,
            'group' => $search_filter_data['group'],
            'statement_date' => $search_filter_data['statement_date'],
            'group_ids' => $search_filter_data['linked_group_id'],
            'staying_guest_name' => $search_filter_data['staying_guest_name']
        );
        if($search_filter_data['in_statement'] == 'yes')
        {
            $filters['in_statement'] = true;
        }
        elseif($search_filter_data['in_statement'] == 'no')
        {
            $filters['in_statement'] = false;
        }
        
        // if the dates entered are in valid format (yyyy-mm-dd), otherwise clear the dates
        if (!$this->_is_valid_date($search_filter_data['start_date']) || !$this->_is_valid_date($search_filter_data['start_date']) ) {          
            // for clearing input field
            $search_filter_data['start_date'] = ''; 
            $search_filter_data['end_date'] = ''; 
            // for clearing MYSQL query
            $filters['start_date'] = '';
            $filters['end_date'] = '';                  
        }
        if(!$this->_is_valid_date($search_filter_data['statement_date'])){
            $search_filter_data['statement_date'] = $filters['statement_date'] = '';
        }
       
        $data['payment_types'] = $this->Payment_model->get_payment_types($this->company_id);    
        
        $payment_gateway_credentials = $this->paymentgateway->getGatewayCredentials();
        
        $data['selected_payment_gateway'] = null;
        if(isset($payment_gateway_credentials['selected_payment_gateway'])){
            $data['selected_payment_gateway'] = $payment_gateway_credentials['selected_payment_gateway'];
        }
        
        $filters['with_booking_statements'] = true;
        
        if($filters['state'] == INHOUSE || $filters['state'] == CHECKOUT){ // only for inhouse and checked out bookings 
            $filters['show_only_checked_in_and_checked_out'] = true;
        }
        
        $bookings = $this->Booking_model->get_bookings($filters+array('booking_customer_id' => $customer_id), null, null, true);
        // Must be called RIGHT AFTER the query that we want to get the number of rows for.
        $config['total_rows'] = $this->Booking_model->get_found_rows(); 
        foreach ($bookings as $index => $booking)
        {
            $next_room_charge_selling_date = max($booking['check_in_date'], $this->selling_date);
            $last_room_charge = $this->Charge_model->get_last_applied_charge($booking['booking_id'], $booking['charge_type_id'], null, true);
            if(isset($last_room_charge['selling_date']) && $last_room_charge['selling_date']){
                if($last_room_charge['pay_period'] == DAILY){
                    $next_room_charge_selling_date = date('Y-m-d', strtotime($last_room_charge['selling_date'].' +1 day'));
                }
                elseif($last_room_charge['pay_period'] == WEEKLY){
                    $next_room_charge_selling_date = date('Y-m-d', strtotime($last_room_charge['selling_date'].' +7 day'));
                }
                elseif($last_room_charge['pay_period'] == MONTHLY){
                    $next_room_charge_selling_date = date('Y-m-d', strtotime($last_room_charge['selling_date'].' +1 month'));
                }
                elseif($last_room_charge['pay_period'] == ONE_TIME){
                    // no next room charges if we already charged one time charge bookings.
                    $next_room_charge_selling_date = null;
                }
            }
            if($booking['use_rate_plan'] == 1)
            {
                $rate_plan = $this->Rate_plan_model->get_rate_plan($booking['rate_plan_id']);
            }
            $charge_type_id = isset($rate_plan['charge_type_id']) && $rate_plan['charge_type_id'] ? $rate_plan['charge_type_id'] : $booking['charge_type_id'];
            
            $tax_rates = $this->Tax_model->get_tax_rates_by_charge_type_id($charge_type_id);
            $end_date = $booking['check_out_date'];
            $bookings[$index]['charge_end_selling_date'] = $end_date;
            if($search_filter_data['statement_date'] && $search_filter_data['statement_date'] < $booking['check_out_date']){
                $end_date = $search_filter_data['statement_date'];
                $bookings[$index]['charge_end_selling_date'] = $search_filter_data['statement_date'];
            }
            
            $bookings[$index]['taxes'] = $this->Tax_model->get_tax_totals($booking['booking_id'], $search_filter_data['statement_date']);
            
            $next_expected_charge_date = null;
            $total_payments = $bookings[$index]['payment_total'];
            
            $applied_charges_and_date = $this->Charge_model->get_applied_charges_and_dates($booking['booking_id'], $booking['charge_type_id'], $bookings[$index]['charge_end_selling_date'], $this->company_id);
            if($applied_charges_and_date && count($applied_charges_and_date) > 0) {
                foreach($applied_charges_and_date as $selling_date => $charge){
                    $remaining_payments = $total_payments - $charge;
                    if($remaining_payments < 0){                    
                        $next_expected_charge_date = $selling_date;
                        break;
                    }
                    $total_payments = $remaining_payments;
                }
            }
            
            $forecast = $this->forecast_charges->_get_forecast_charges_with_next_charge_date($booking['booking_id'], $next_room_charge_selling_date, $end_date, $tax_rates, $total_payments);
            $bookings[$index]['charge_total'] += $forecast['total_charges'];
            
            if(!$next_expected_charge_date && isset($forecast['next_expected_charge_date'])){
                $next_expected_charge_date = $forecast['next_expected_charge_date'];
            }
            
            $bookings[$index]['charge_start_selling_date'] = $next_expected_charge_date;
            if(!$next_expected_charge_date || ($search_filter_data['statement_date'] && $search_filter_data['statement_date'] <= $next_expected_charge_date)){
                $bookings[$index]['charge_start_selling_date'] = $booking['check_in_date'];
            }
            
            if($bookings[$index]['taxes'] && count($bookings[$index]['taxes']) > 0 && $forecast['total_taxes'] && count($forecast['total_taxes']) > 0){
                foreach($bookings[$index]['taxes'] as $key => $tax){
                    if(isset($forecast['total_taxes'][$tax['tax_type']])){
                        $bookings[$index]['taxes'][$key]['tax_total'] += $forecast['total_taxes'][$tax['tax_type']];
                        unset($forecast['total_taxes'][$tax['tax_type']]);
                    }
                }
            }
            if($forecast['total_taxes'] && count($forecast['total_taxes']) > 0){
                foreach($forecast['total_taxes'] as $tax_type => $tax_amount){
                    $bookings[$index]['taxes'][] = array(
                        'tax_type' => $tax_type,
                        'selling_date' => $this->selling_date,
                        'tax_total' => $tax_amount
                    );
                }
            }
        }
        
        $data['bookings_made_by_this_customer'] = $bookings;
        
        // Not going to get number of rows for the below code. Eventually, I'll create a link "show all.." as we can only have one pagination per page with codeigniter
        $group_by = " check_in_date,check_out_date";
        $data['bookings_that_this_customer_stayed_in'] = $this->Booking_model->get_bookings($filters+array('staying_customer_id' => $customer_id),'',$group_by, true);  
        
        
        $this->load->library('pagination');
        $this->pagination->initialize($config);
        $data['menu_on'] = true;
        $data['selected_menu'] = 'customers';
        $data['main_content'] = 'customer/history';
                    
        
        //############################## customer information ends here ##############################
        
        
        //#################################### customer log ends here #################################
        $data['css_files'] = array(
            base_url() . auto_version('css/customer/history.css'),
            base_url() . auto_version('css/booking/booking_main.css')
        );
        $data['js_files'] = array(
            base_url() . auto_version('js/customer/history.js'),
            base_url() . auto_version('js/channel_manager/channel_manager.js'),
            base_url().'js/moment.min.js',
            base_url() . auto_version('js/booking/bookingModal.js'),
            base_url() . auto_version('js/card_detail/cardModal.js'),
        );
        
        $this->load->view('includes/bootstrapped_template', $search_filter_data + $data);
    }
    
    //Called by javascript

    function _is_valid_date($date)
    {
        //match the format of the date
        if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts)) {
            //check weather the date is valid of not
            if (checkdate($parts[2], $parts[3], $parts[1])) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    // called when "create new customer" is clicked in Customer Main

    function create_customer()
    {
        $customer_data = $this->get_customer_field_POSTs();
        unset($customer_data["customer_id"]); // new customer doesn't have customer_id
        $customer_data['company_id'] = $this->company_id;

        $this->load->library('form_validation');
        $this->_set_statement_validation_rules();

        if ($this->form_validation->run() == FALSE) {
            $errors = validation_errors('<div class="error">', '</div>');

            $data = array(
                'status'        => false,
                'errorMessages' => $errors
            );

            echo json_encode($data);
        } else {
            $customer_id = $this->Customer_model->create_customer($customer_data);

            $post_customer_data = $customer_data;
            $post_customer_data['customer_id'] = $customer_id;

            do_action('post.create.customer', $post_customer_data);

            $data = array(
                'status' => true,
                'data'   => array(
                    'customerId'   => $customer_id,
                    'customerName' => $customer_data['customer_name']
                )
            );

            echo json_encode($data);
        }
    }

    
    // called when "edit customer" is clicked in View Customer

    function get_customer_field_POSTs()
    {
        $data['customer_id']     = intval(sqli_clean($this->security->xss_clean($this->input->post('customer-id'))));
        $data['customer_name']   = sqli_clean($this->security->xss_clean($this->input->post('customer-name')));
        $data['customer_type_id']   = sqli_clean($this->security->xss_clean($this->input->post('customer-type-id')));
        $data['email']           = sqli_clean($this->security->xss_clean($this->input->post('email')));
        $data['phone']           = sqli_clean($this->security->xss_clean($this->input->post('phone')));
        $data['fax']             = sqli_clean($this->security->xss_clean($this->input->post('fax')));
        $data['address']         = sqli_clean($this->security->xss_clean($this->input->post('address')));
        $data['city']            = sqli_clean($this->security->xss_clean($this->input->post('city')));
        $data['region']          = sqli_clean($this->security->xss_clean($this->input->post('region')));
        $data['country']         = sqli_clean($this->security->xss_clean($this->input->post('country')));
        $data['postal_code']     = sqli_clean($this->security->xss_clean($this->input->post('postal-code')));
        $data['customer_notes']  = sqli_clean($this->security->xss_clean($this->input->post('customer-notes')));
        $cc_number               = sqli_clean($this->security->xss_clean($this->input->post('cc-number')));
        $encrypted_cc_number     = $this->encrypt->encode($cc_number);
        $data['cc_number']       = $encrypted_cc_number;
        $data['cc_expiry_month'] = sqli_clean($this->security->xss_clean($this->input->post('cc-expiry-month')));
        $data['cc_expiry_year']  = sqli_clean($this->security->xss_clean($this->input->post('cc-expiry-year')));

        return $data;
    }
    
    //called from booking_form.js

    function _set_statement_validation_rules()
    {
        $this->form_validation->set_rules('customer-name', 'Name', 'trim|required');
        $this->form_validation->set_rules('email', 'Email', 'trim|valid_email');
        $this->form_validation->set_rules('phone', 'Phone Number', 'trim');
        $this->form_validation->set_rules('fax', 'Fax', 'trim');
        $this->form_validation->set_rules('address', 'Address', 'trim');
        $this->form_validation->set_rules('city', 'City', 'trim');
        $this->form_validation->set_rules('region', 'State Province', 'trim');
        $this->form_validation->set_rules('country', 'Country', 'trim');
        $this->form_validation->set_rules('postal-code', 'Postal Code', 'trim');
        $this->form_validation->set_rules('customer_notes', 'Notes', 'trim');
    }
    


    // show & edit booking log

    function create_new_customer()
    {
        $customer_POST_data = $this->get_customer_field_POSTs();
        unset($customer_POST_data["customer_id"]); // new customer doesn't have customer_id
        $customer_POST_data["company_id"] = $this->company_id;

        $this->load->library('form_validation');
        $this->_set_statement_validation_rules();

        if ($this->form_validation->run() == false) {
            $customer_data['error_count'] = $this->form_validation->error_count();
            $view_data['css_files']       = array(
                base_url().auto_version('css/booking/booking_list.css'),
                base_url().auto_version('css/booking/booking_main.css'),
            );
            $view_data['menu_on']         = true;
            $view_data['form_type']       = "new";
            $view_data['selected_menu']   = 'customers';
            $view_data['main_content']    = 'customer/customer_profile';
            $this->load->view('includes/bootstrapped_template', $view_data + $customer_data);
        } else {
            $customer_id = $this->Customer_model->create_customer($customer_POST_data);

            $post_customer_data = $customer_POST_data;
            $post_customer_data['customer_id'] = $customer_id;

            do_action('post.create.customer', $post_customer_data);

            echo "<script type='text/javascript'>alert('Saved Successfully');</script>";
            redirect('/customer/history/'.$customer_id);
        }
    }

    function show_profile($customer_id = "")
    {
        $customer_POST_data = $this->get_customer_field_POSTs();

        $this->_set_statement_validation_rules();

        $view_data = $this->Customer_model->get_customer_info($customer_id);

        //####################################### customer log #######################################
        $view_data['time_zone'] = $this->Company_model->get_time_zone($this->company_id);
        $view_data['log']       = sqli_clean($this->security->xss_clean($this->input->post('log')));

        // get first 5 logs that belong to this customer
        $view_data['rows']      = $this->Customer_log_model->get_customer_log($customer_id, 5);
        $view_data['row_count'] = $this->Customer_log_model->get_customer_log_count($customer_id);


        // user is entering a log (instead of auto-generated)
        if ($view_data['log'] != "") {
            // The third parameter is set to 1
            // to indicate that it's a manual user entry (not automatically generated)
            $this->_create_customer_log($customer_id, $view_data['log'], 1);
            redirect('/customer/history/'.$customer_id);
        }


        if ($this->form_validation->run() == false) {
            $customer_data['error_count'] = $this->form_validation->error_count();
            $view_data['css_files']       = array(
                base_url().auto_version('css/booking/booking_list.css'),
                base_url().auto_version('css/booking/booking_main.css')
            );

            $view_data['menu_on']       = true;
            $view_data['form_type']     = "edit";
            $view_data['selected_menu'] = 'customers';
            $view_data['main_content']  = 'customer/customer_profile';
            $this->load->view('includes/bootstrapped_template', $view_data + $customer_data);
        } else {
            $this->Customer_model->update_customer($customer_id, $customer_POST_data);

            $post_customer_data = $customer_POST_data;
            $post_customer_data['customer_id'] = $customer_id;

            do_action('post.update.customer', $post_customer_data);

            echo "<script type='text/javascript'>alert('Saved Successfully');</script>";
            redirect('/customer/history/'.$customer_id);
        }
    }

    function _create_customer_log($customer_id, $log, $is_entered_by_user = 0)
    {
        $this->load->helper('timezone');
        $date      = convert_to_UTC_time(new DateTime(date('Y-m-d H:i:s'))); // Apply time zone
        $date_time = $date->format('Y-m-d H:i:s');

        $log_data['selling_date']       = $this->selling_date;
        $log_data['user_id']            = $this->user_id;
        $log_data['customer_id']        = $customer_id;
        $log_data['is_entered_by_user'] = $is_entered_by_user;
        $log_data['date_time']          = $date_time;
        $log_data['log']                = $log;

        $this->Customer_log_model->insert_log($log_data);
    }

    function update_customer()
    {
        $customer_data = $this->get_customer_field_POSTs();

        $this->load->library('form_validation');
        $this->_set_statement_validation_rules();

        if ($this->form_validation->run() == false) {
            $errors = validation_errors('<div class="error">', '</div>');

            $data = array(
                'status'        => false,
                'errorMessages' => $errors
            );

            echo json_encode($data);
        } else {
            $customer_id = $this->Customer_model->update_customer($customer_data['customer_id'], $customer_data);

            $post_customer_data = $customer_data;
            $post_customer_data['customer_id'] = $customer_data['customer_id'];

            do_action('post.update.customer', $post_customer_data);

            $data = array(
                'status' => true,
                'data'   => array(
                    'customerId'   => $customer_data['customer_id'],
                    'customerName' => $customer_data['customer_name']
                )
            );

            echo json_encode($data);
        }
    }

    function show_customer_log($customer_id)
    {
        $this->load->helper('timezone');
        $view_data  = array();
        $view_data['time_zone'] = $this->Company_model->get_time_zone($this->company_id);
        $view_data['customer_id'] = $customer_id;
        $view_data['log'] = sqli_clean($this->security->xss_clean($this->input->post('log')));
        $this->load->library('form_validation');
        $this->form_validation->set_rules('log', 'log', 'required|trim');

        $this->load->model('Customer_log_model');
        $view_data['customer_data'] = $this->Customer_model->get_customer_info($customer_id);
        $view_data['rows']          = $this->Customer_log_model->get_customer_log($customer_id);

        // user is entering a log (instead of auto-generated)
        if ($view_data['log'] != "") {
            // The third parameter is set to 1
            // to indicate that it's a manual user entry (not automatically generated)
            $this->_create_customer_log($customer_id, $view_data['log'], 1);
            redirect('/customer/show_customer_log/'.$customer_id);
        }

        /** itodo css lost lead */
        $view_data['css_files'] = array(//          base_url() . auto_version('css/customer/customer_log.css')
        );
        $view_data['js_files'] = array(
            base_url() . auto_version('js/customer/customer_log.js')
        );

        $view_data['menu_on'] = true;
        $view_data['selected_menu'] = 'customers';
        $view_data['main_content'] = 'customer/customer_log';
        $this->load->view('includes/bootstrapped_template', $view_data);
    }

    function set_order_by($field_name)
    {
        // Assign which field the ordering will be based on
        $this->session->set_userdata('customer_order_by', $field_name);

        // Choose ascending/descending order depending on session variable
        $customer_order = $this->session->userdata('customer_order');
        if (isset($customer_order))
        {
            if ($customer_order == 'ASC')
                $this->session->set_userdata('customer_order', 'DESC');
            else {
                $this->session->set_userdata('customer_order', 'ASC');
            }
        } else
            $this->session->set_userdata('customer_order', 'DESC');
        redirect('/customer/show_customers/');
    }

    // validation

    function get_customers_AJAX()
    {
        $query = sqli_clean($this->security->xss_clean($this->input->get('query')));

        if (isset($query)) {
            if (strlen($query) > 0) {
                $filters               = array('search_query' => addslashes($query));
                $filters['company_id'] = $this->company_id;
                $filters['offset'] = 25; // show 25 results only
                $filters['only_static_customer_info'] = true; // fetch customer info only without payment/charges etc.
                $data['rows'] = $this->Customer_model->search_customers($filters);

                $rows = json_encode($data['rows']);
                echo($rows);
            }
        }
    }
   
    // check if customer exists by searching the customer's name
    function get_customer_by_name()
    {
        $name = sqli_clean($this->security->xss_clean($this->input->get('name')));

        if (isset($name)) {
            if (strlen($name) > 0) {
                $customer = $this->Customer_model->get_customer_by_name($name, $this->company_id);
                if ($customer)
                {
                    echo json_encode($customer);
                    return;
                }
            }
        }
        
        echo json_encode(false);
    }
    
    // validation

    function delete_customer_JSON()
    {
        $customer_id = sqli_clean($this->security->xss_clean($this->input->post('customer_id')));
        $showdelete = $this->input->post('show_deleted');
        $bookings = $this->Booking_model->customer_total_bookings($customer_id, true);
        // restrict customer if he has already a bookings associated with him.
        $result = array();
        if ($bookings > 0) {
            $result['status'] = "fail";
            $result['msg'] = l("This customer cannot be deleted because there are bookings associated with him/her.", true);
        } else {
            if($showdelete == 'checked'){
                $this->Customer_model->delete_customer_permanently($customer_id);

                $post_customer_data['customer_id'] = $customer_id;
                do_action('post.delete.customer', $post_customer_data);
            }else{
                $this->Customer_model->delete_customer($customer_id, $this->company_id);

                $post_customer_data['company_id'] = $this->company_id;
                $post_customer_data['customer_id'] = $customer_id;

                do_action('post.delete.customer', $post_customer_data);
            }
            $result['status'] = "success";
        }
        echo json_encode($result);
    }
    
    // create booking log in the database
    // by default, the log is considered to be automatically generated from events that happens in booking
    // such as: check-in, check-out, create/cancel reservation, and other changes in booking information

    function get_customer_info_in_JSON($customer_id)
    {

        $customer_info = $this->Customer_model->get_customer_info($customer_id);
        $this->load->library('encrypt');
        $encrypted_cc_number        = $customer_info['cc_number'];
        $decrypted_cc_number        = $this->encrypt->decode($encrypted_cc_number);
        $customer_info['cc_number'] = $decrypted_cc_number;
        $json                       = json_encode($customer_info);

        echo $json;
    }
    
    function get_all_unpaid_bookings_AJAX()
    {
        $customer_id = sqli_clean($this->security->xss_clean($this->input->post('customer_id')));

        $filters = Array(
            'show_paid' => 'unpaid_only',
            'booking_customer_id' => $customer_id
            );
        
        $bookings = $this->Booking_model->get_bookings($filters);
        echo json_encode($bookings);
    }
    
    function get_customer_bookings_AJAX()
    {
        
        $checked_bookings = $this->input->post('checkedBookings');
        $statement_id = $this->input->post('statement_id');
        if($statement_id)
        {
            $customer_id = sqli_clean($this->security->xss_clean($this->input->post('customer_id')));
        
            $statement_bookings = $this->Statement_model->get_bookings_by_statement_id($statement_id);
            $booking_ids_ar = array();
            if(count($statement_bookings) > 0){
                $data['statement_details'] = $statement_bookings[0];
                foreach($statement_bookings as $statement_booking){
                    $booking_ids_ar[] = $statement_booking['booking_id'];
                }
            }
            $filters['booking_ids'] = $booking_ids_ar;
            $statement_date = '';
            $bookings = $this->Booking_model->get_bookings($filters+array('booking_customer_id' => $customer_id)); 
        }
        else{
            if($checked_bookings)
            {
                $bookings = $this->Booking_model->get_bookings(array('booking_ids' => $checked_bookings));
                $statement_date = sqli_clean($this->security->xss_clean($this->input->post('statement_date')));
            }
            else
            {
                $customer_id = sqli_clean($this->security->xss_clean($this->input->post('customer_id')));
                $booking_status = sqli_clean($this->security->xss_clean($this->input->post('status')));
                $start_date = sqli_clean($this->security->xss_clean($this->input->post('start_date')));
                $end_date = sqli_clean($this->security->xss_clean($this->input->post('end_date')));
                $show_paid = $this->input->post('show_paid');
                $groups = $this->input->post('group');
                $statement_date = sqli_clean($this->security->xss_clean($this->input->post('statement_date')));
                $linked_group_id = sqli_clean($this->security->xss_clean($this->input->post('linked_group_id')));
                $in_statement = sqli_clean($this->security->xss_clean($this->input->post('in_statement')));
                $staying_guest_name = sqli_clean($this->security->xss_clean($this->input->post('staying_guest_name')));

                $segment_four = $this->uri->segment(4);
                if(is_numeric($segment_four)){
                    $order_by = $this->uri->segment(5);
                    $order = $this->uri->segment(6);
                }else{
                    $order_by = $this->uri->segment(4);
                    $order = $this->uri->segment(5);
                }

                $this->permission->check_access_to_customer_id($this->user_id, $customer_id);

                if (isset($order)) 
                {           
                    if ($order == 'asc')
                        $this->session->set_userdata('booking_order', 'ASC');           
                    else
                        $this->session->set_userdata('booking_order', 'DESC');          
                } else 
                    $this->session->set_userdata('booking_order', 'DESC');

                $order = $this->session->userdata('booking_order');
                if ($order_by == '')
                    $order_by = "balance $order, booking_id";
                else{
                    $order_by = "$order_by $order, booking_id";
                }

                //$data['company'] = $this->Company_model->get_company($this->company_id);


                switch ($booking_status) {      
                    case "reservation" : $state = '0'; break;
                    case "checkin" : $state = '1'; break;
                    case "checkout" : $state = '2'; break;
                    case "cancelled" : $state = '4'; break;
                    default: $state = 'all'; break;
                }

                $filters = array(
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'show_paid' => $show_paid,
                    'order_by' => $order_by,
                    'order' => "DESC", // this is for booking id, for balance we appended it in order_by
                    'state' => $state,
                    'group' => $groups,
                    'statement_date' => $statement_date
                );

                if($linked_group_id)
                {
                    $filters['group_ids'] = $linked_group_id;
                }

                if($in_statement == 'yes')
                {
                    $filters['in_statement'] = true;
                }
                elseif($in_statement == 'no')
                {
                    $filters['in_statement'] = false;
                }
                if($staying_guest_name)
                {
                    $filters['staying_guest_name'] = $staying_guest_name;
                }

                $filters['with_booking_statements'] = true;
                // if the dates entered are in valid format (yyyy-mm-dd), otherwise clear the dates
                if (!$this->_is_valid_date($start_date) || !$this->_is_valid_date($start_date) ) {          
                    $filters['start_date'] = '';
                    $filters['end_date'] = '';                  
                }
                if(!$this->_is_valid_date($statement_date)){
                   $filters['statement_date'] = '';
                }
                
                if($filters['state'] == INHOUSE || $filters['state'] == CHECKOUT){ // only for inhouse and checked out bookings 
                    $filters['show_only_checked_in_and_checked_out'] = true;
                }
                
                $data['payment_types'] = $this->Payment_model->get_payment_types($this->company_id);    
                $bookings = $this->Booking_model->get_bookings($filters+array('booking_customer_id' => $customer_id));  
            }
        }  
        
        foreach ($bookings as $index => $booking)
        {
            $next_room_charge_selling_date = max($booking['check_in_date'], $this->selling_date);
            $last_room_charge = $this->Charge_model->get_last_applied_charge($booking['booking_id'], $booking['charge_type_id'], null, true);
            if(isset($last_room_charge['selling_date']) && $last_room_charge['selling_date']){
                if($booking['pay_period'] == DAILY){
                    $next_room_charge_selling_date = date('Y-m-d', strtotime($last_room_charge['selling_date'].' +1 day'));
                }
                elseif($booking['pay_period'] == WEEKLY){
                    $next_room_charge_selling_date = date('Y-m-d', strtotime($last_room_charge['selling_date'].' +7 day'));
                }
                elseif($booking['pay_period'] == MONTHLY){
                    $next_room_charge_selling_date = date('Y-m-d', strtotime($last_room_charge['selling_date'].' +1 month'));
                }
                elseif($booking['pay_period'] == ONE_TIME){
                    // no next room charges if we already charged one time charge bookings.
                    $next_room_charge_selling_date = null;
                }
            }
            if($booking['use_rate_plan'] == 1)
            {
                $rate_plan = $this->Rate_plan_model->get_rate_plan($booking['rate_plan_id']);
            }
            $charge_type_id = isset($rate_plan['charge_type_id']) && $rate_plan['charge_type_id'] ? $rate_plan['charge_type_id'] : $booking['charge_type_id'];
            
            $tax_rates = $this->Tax_model->get_tax_rates_by_charge_type_id($charge_type_id);
            $end_date = $booking['check_out_date'];
            $bookings[$index]['charge_end_selling_date'] = $end_date;
            if($statement_date && $statement_date < $booking['check_out_date']){
                $end_date = $statement_date;
                $bookings[$index]['charge_end_selling_date'] = $statement_date;
            }
            
            $bookings[$index]['taxes'] = $this->Tax_model->get_tax_totals($booking['booking_id'], $statement_date);
            
            $next_expected_charge_date = null;
            $total_payments = $bookings[$index]['payment_total'];
            
            $applied_charges_and_date = $this->Charge_model->get_applied_charges_and_dates($booking['booking_id'], $booking['charge_type_id'], $bookings[$index]['charge_end_selling_date'], $this->company_id);
            if($applied_charges_and_date && count($applied_charges_and_date) > 0) {
                foreach($applied_charges_and_date as $selling_date => $charge){
                    $remaining_payments = $total_payments - $charge;
                    if($remaining_payments < 0){                    
                        $next_expected_charge_date = $selling_date;
                        break;
                    }
                    $total_payments = $remaining_payments;
                }
            }
            
            $forecast = $this->forecast_charges->_get_forecast_charges_with_next_charge_date($booking['booking_id'], $next_room_charge_selling_date, $end_date, $tax_rates, $total_payments);
            $bookings[$index]['charge_total'] += $forecast['total_charges'];
            
            if(!$next_expected_charge_date && isset($forecast['next_expected_charge_date'])){
                $next_expected_charge_date = $forecast['next_expected_charge_date'];
            }
            
            $bookings[$index]['charge_start_selling_date'] = $next_expected_charge_date;
            if(!$next_expected_charge_date || ($statement_date && $statement_date <= $next_expected_charge_date)){
                $bookings[$index]['charge_start_selling_date'] = $booking['check_in_date'];
            }
            
            if($bookings[$index]['taxes'] && count($bookings[$index]['taxes']) > 0 && $forecast['total_taxes'] && count($forecast['total_taxes']) > 0){
                foreach($bookings[$index]['taxes'] as $key => $tax){
                    if(isset($forecast['total_taxes'][$tax['tax_type']])){
                        $bookings[$index]['taxes'][$key]['tax_total'] += $forecast['total_taxes'][$tax['tax_type']];
                        unset($forecast['total_taxes'][$tax['tax_type']]);
                    }
                }
            }
            if($forecast['total_taxes'] && count($forecast['total_taxes']) > 0){
                foreach($forecast['total_taxes'] as $tax_type => $tax_amount){
                    $bookings[$index]['taxes'][] = array(
                        'tax_type' => $tax_type,
                        'selling_date' => $this->selling_date,
                        'tax_total' => number_format($tax_amount, 2, ".", ",")
                    );
                }
            }
            
        }
        echo json_encode($bookings);
    }
    
    function check_EVC_folio($folio_name = null, $booking_id = null, $customer_id = null, $for = null)
    {
        $folio_name = $folio_name ? $folio_name : sqli_clean($this->security->xss_clean($this->input->post('folio_name')));
        $booking_id = $booking_id ? $booking_id : sqli_clean($this->security->xss_clean($this->input->post('booking_id')));
        $customer_id = $customer_id ? $customer_id : sqli_clean($this->security->xss_clean($this->input->post('customer_id')));
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
    
    function add_folio_AJAX($folio_name = null, $booking_id = null, $customer_id = null, $for = null)
    {
        $first_folio_id = $last_insert_id = null;
        
        $data['booking_id'] = $booking_id ? $booking_id : sqli_clean($this->security->xss_clean($this->input->post('booking_id')));
        $data['customer_id'] = $customer_id ? $customer_id : sqli_clean($this->security->xss_clean($this->input->post('customer_id')));
        $data['folio_name'] = $folio_name ? $folio_name : sqli_clean($this->security->xss_clean($this->input->post('folio_name')));

        $first_folio['booking_id'] = $booking_id ? $booking_id : sqli_clean($this->security->xss_clean($this->input->post('booking_id')));
        $first_folio['customer_id'] = $customer_id ? $customer_id : sqli_clean($this->security->xss_clean($this->input->post('customer_id')));
        $first_folio['folio_name'] = 'Folio #1';

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
        $invoice_log_data['booking_id'] = $data['booking_id'];
        $invoice_log_data['user_id'] = $this->session->userdata('user_id');
        $invoice_log_data['action_id'] = ADD_FOLIO;
        // $invoice_log_data['charge_or_payment_id'] = $response['payment_id'];
        // $invoice_log_data['new_amount'] = $this->input->post('payment_amount');
        if ($last_insert_id) {
            $invoice_log_data['log'] = 'Folio Added';
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
    
    function insert_payments_AJAX()
    {
        $bookings = $this->input->post('bookings');
        $customer_id = sqli_clean($this->security->xss_clean($this->input->post('customer_id')));
        $selling_date = sqli_clean($this->security->xss_clean($this->input->post('payment_date')));
        $payment_type_id = sqli_clean($this->security->xss_clean($this->input->post('payment_type_id')));
        $total_balance = sqli_clean($this->security->xss_clean($this->input->post('total_balance')));
        $description = sqli_clean($this->security->xss_clean($this->input->post('description')));
        $cvc = sqli_clean($this->security->xss_clean($this->input->post('cvc')));
        $distribute_equal_amount = $this->input->post('distribute_equal_amount');
        // $folio_id = sqli_clean($this->security->xss_clean($this->input->post('folio_id')));
        $capture_payment = sqli_clean($this->security->xss_clean(trim($this->input->post('capture_payment_type'))));
      
        $remaining_balance = $total_balance;
        $no_of_bookings = count($bookings);
        $equal_amount = $total_balance / $no_of_bookings;
        $round_total_amount = (round($equal_amount,2)) * $no_of_bookings;
        $amount_diff = round(($round_total_amount - $total_balance), 2);
        
        $company_data =  $this->Company_model->get_company($this->company_id);
        $capture_payment_type = $company_data['manual_payment_capture'];
        $capture_payment_type = ($capture_payment != 'authorize_only') ? false : true;
        
        $i = 1;
        $errors = array();
        foreach ($bookings as $booking)
        {
            $balance = $booking['balance'];
            
            if($distribute_equal_amount == "Yes")
            {
                if($i == $no_of_bookings && $amount_diff != 0)
                {
                    $amount = $equal_amount - $amount_diff;
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
                "amount" => $amount,
                "customer_id" => $customer_id,
                "payment_type_id" => $payment_type_id,
                "description" => $description,
                "date_time" => gmdate("Y-m-d H:i:s")
            );
            $card_data = $this->Card_model->get_active_card($customer_id, $this->company_id);
            $data['credit_card_id'] = "";
            if(isset($card_data) && $card_data){
                 $data['credit_card_id'] = $card_data['id'];
            }
            
            if(isset($card_data['evc_card_status']) && $card_data['evc_card_status'] && false){
                // check folio is exist or not
                $evc_folio_id = $this->check_EVC_folio('Expedia EVC', $data['booking_id'], $customer_id, true);
                if(isset($evc_folio_id) && $evc_folio_id)
                {
                    $data['folio_id'] = $folio_id = $evc_folio_id;
                }
                else
                {
                    $folio_id = $this->add_folio_AJAX('Expedia EVC', $data['booking_id'], $customer_id, true);
                    $data['folio_id'] = $folio_id = $folio_id ? $folio_id : 0;
                }
            }
            
            $response = $this->Payment_model->insert_payment($data, $cvc, $capture_payment_type);
            if($response['success'] == false){
                $errors[] = array(
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
                    $folio_data = $this->Folio_model->get_folios($booking['booking_id']);

                    $folio_id = (isset($folio_data) && count($folio_data) > 0) ? $folio_data[0]['id'] : 0;

                    $this->Payment_model->insert_payment_folio(array('payment_id' => $response['payment_id'], 'folio_id' => $folio_id));
                    
                    $invoice_log_data['log'] = $company_data['manual_payment_capture'] ? 'Payment Authorized' : 'Payment Captured';
                    $this->Invoice_log_model->insert_log($invoice_log_data);
                }
                $this->Booking_model->update_booking_balance($booking['booking_id']);
            }
            $i++;
            $remaining_balance -= $amount; 
        }

        if (!empty($errors)) {
            echo json_encode($errors);
        } else {
            echo json_encode($response);
        }
    }

    function get_customer_AJAX()
    {
        $customer_id = sqli_clean($this->security->xss_clean($this->input->post('customer_id')));
        $customer = $this->Customer_model->get_customer($customer_id);
        
        if($customer['cc_number'])
           $customer['cc_number'] = "";
        if($customer['cc_expiry_month'])
           $customer['cc_expiry_month'] = "";
        if($customer['cc_expiry_year'])
           $customer['cc_expiry_year'] = "";
        if($customer['cc_tokenex_token']) 
           $customer['cc_tokenex_token'] = "";
        if($customer['cc_cvc_encrypted'])
           $customer['cc_cvc_encrypted'] = "";
        
       
        $card_details = $this->Card_model->get_customer_primary_card($customer_id);
        
            if(isset($card_details) && $card_details){
                if(!strrpos($card_details['cc_number'], 'X') && preg_match("/[a-z]/i", $card_details['cc_number'])){
                    $card_details['cc_number_encrypted'] = $card_details['cc_number'];
                    $card_details['cc_number'] = 'XXXX XXXX XXXX '.substr(base64_decode($card_details['cc_number']), -4);
                }
            
                
                // $token = isset(json_decode($card_details['customer_meta_data'], true)['token']) && json_decode($card_details['customer_meta_data'], true)['token'] ? json_decode($card_details['customer_meta_data'], true)['token'] : json_decode($card_details['customer_meta_data'], true)['pci_token'];

                if (isset(json_decode($card_details['customer_meta_data'], true)['token']) && json_decode($card_details['customer_meta_data'], true)['token']) {
                    $token = json_decode($card_details['customer_meta_data'], true)['token'];
                } elseif(json_decode($card_details['customer_meta_data'], true)['source'] == 'pci_booking') {
                    $token = json_decode($card_details['customer_meta_data'], true)['pci_token'];
                } elseif (json_decode($card_details['customer_meta_data'], true)['source'] == 'cardknox') {
                    $token = json_decode($card_details['customer_meta_data'], true)['cardknox_token'];
                }
                
                // if (json_decode($card_details['customer_meta_data'], true)['source'] == 'pci_booking') {
                //  $token = json_decode($card_details['customer_meta_data'], true)['pci_token'];
                // } 
                // elseif (json_decode($card_details['customer_meta_data'], true)['source'] == 'cardknox') {
                //  $token = json_decode($card_details['customer_meta_data'], true)['cardknox_token'];
                // } 
                // else {
                //  $token = json_decode($card_details['customer_meta_data'], true)['token'];
                // }
                $customer['cc_number_encrypted'] = isset($card_details['cc_number_encrypted']) && $card_details['cc_number_encrypted'] ? $card_details['cc_number_encrypted'] : null;
                $customer['cc_number'] = $card_details['cc_number'];
                $customer['cc_expiry_month'] = $card_details['cc_expiry_month'];
                $customer['cc_expiry_year'] = $card_details['cc_expiry_year'];
                $customer['cc_tokenex_token'] = $card_details['cc_tokenex_token'];
                $customer['cc_cvc_encrypted'] = $card_details['cc_cvc_encrypted'];
                $customer['customer_pci_token'] = $token ?? null;
                $customer['token_source'] = json_decode($card_details['customer_meta_data'], true)['source'] ?? null;
            }

        echo json_encode($customer);
    }

    // function create_customer_AJAX()
    // {
    //      // ============check csrf_token==================
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    //         $encrypted_customerData = ($this->security->xss_clean($this->input->post('customer_data', TRUE)));
    //         $customer_data = json_decode(base64_decode($encrypted_customerData),true);
    //         $submittedToken = $customer_data['csrf_token'];


    //         if ($this->validateCSRFToken($submittedToken)) {
    //             // unset CSRF token
    //             if (isset($customer_data['csrf_token'])) {
    //                 $this->create_customer_AJAX_csrf();
    //             }
    //             // if CSRF token is valid
    //             // Process the form submission
    //         }
    //         else {
    //             // CSRF token validation failed
    //             echo "CSRF token is failed";
    //         }
    //     }
    // }

    // function generateCSRFToken()
    // {
    //     $token = bin2hex(random_bytes(32));
    //     return $token;
    // }

    // function validateCSRFToken($submittedToken)
    // {
    //     if (!isset($_COOKIE['csrf_token'])) {
    //         return false;
    //     }
    //     $storedToken = $_COOKIE['csrf_token'];
    //     return hash_equals($storedToken, $submittedToken);
    // }

    function create_customer_AJAX()
    {
        $error     = false;
        $error_msg = '';

        // $customer_data               = $this->security->xss_clean($this->input->post('customer_data', TRUE));

        $encrypted_customerData = ($this->security->xss_clean($this->input->post('customer_data', TRUE)));
        $customer_data = json_decode(base64_decode($encrypted_customerData), true);

        // prx($customer_data);

        $customer_data['customer_name'] = sqli_clean($this->security->xss_clean($customer_data['customer_name']));

        $customer_data['company_id'] = $this->company_id;

        $cvc = $customer_data['cvc'];
        $cc_number = $customer_data['cc_number'];
        $cc_token = '';
        $cardknox_token = '';
        $cardknox_cvv_token = '';
        

        if(isset($customer_data['cc_token']) && $customer_data['cc_token'])
            $cc_token = $customer_data['cc_token'];
        if(isset($customer_data['cardknox_token']) && $customer_data['cardknox_token'])
        $cardknox_token = $customer_data['cardknox_token'];
        if(isset($customer_data['cardknox_cvv_token']) && $customer_data['cardknox_cvv_token'])
        $cardknox_cvv_token = $customer_data['cardknox_cvv_token'];


        $customer_create_data['customer_data'] = $customer_data;

        unset($customer_data['cvc']);
        unset($customer_data['cc_number']);
        unset($customer_data['cc_token']);
        unset($customer_data['kovena_vault_token']);
        unset($customer_data['cardknox_token']);
        unset($customer_data['cardknox_cvv_token']);

        // if (isset($customer_data['csrf_token'])) {
        //     unset($customer_data['csrf_token']);
        // }
        
        $customer_id = $this->Customer_model->create_customer($customer_data);
        
        $customer_create_data['customer_data']['customer_id'] = $customer_id;
        apply_filters('post.build.customer', $customer_create_data);
        unset($customer_create_data);

        $post_customer_data = $customer_data;
        $post_customer_data['customer_id'] = $customer_id;

        do_action('post.create.customer', $post_customer_data);

        $customer_data['customer_id'] = $customer_id;
        $card_details = array(
           'is_primary' => 1,
           'customer_id' => $customer_id,
           'customer_name' => $customer_data['customer_name'],
           'card_name' => '',
           'company_id' => $this->company_id,
           'cc_number' => (isset($cc_number) ? 'XXXX XXXX XXXX '.substr($cc_number,-4) : NULL),
           'cc_expiry_month' => (isset($customer_data['cc_expiry_month']) ? $customer_data['cc_expiry_month'] : NULL),
           'cc_expiry_year' => (isset($customer_data['cc_expiry_year']) ? $customer_data['cc_expiry_year'] : NULL)
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

                $card_data_array['customer_data'] = $customer_data;

                $card_response = apply_filters('post.add.customer', $card_data_array);

                unset($card_data_array['customer_data']);
            }
            if(
                $card_response &&
                isset($card_response['tokenization_response']["data"]) &&
                isset($card_response['tokenization_response']["data"]["attributes"]) &&
                isset($card_response['tokenization_response']["data"]["attributes"]["card_token"])
            ){
                $card_token = $card_response['tokenization_response']["data"]["attributes"]["card_token"];
                $card_type = $card_response['tokenization_response']["data"]["attributes"]["card_type"];

                $cvc_encrypted = get_cc_cvc_encrypted($cvc, $card_token);

                $card_details['cc_cvc_encrypted'] = ($cvc_encrypted) ? $cvc_encrypted : "";
                $card_details['cc_number'] = 'XXXX XXXX XXXX '.substr($cc_number,-4);

                $meta['token'] = $card_token;
                $meta['card_type'] = $card_type;
                $card_details['customer_meta_data'] = json_encode($meta);
            }
        } else if($cc_token){
            $card_details['cc_number'] = $cc_number;
            $meta['pci_token'] = $cc_token;
            $meta['source'] = 'pci_booking';
            $card_details['customer_meta_data'] = json_encode($meta);
        }
        else if($cardknox_token){
            $card_details['cc_number'] = $cc_number;
            $meta['cardknox_token'] = $cardknox_token;
            $meta['cardknox_cvv_token'] = $cardknox_cvv_token;
            $meta['source'] = 'cardknox';
            $card_details['customer_meta_data'] = json_encode($meta);
        }

        $customer_data['cc_number'] = "";
        $customer_data['cc_expiry_month'] = "";
        $customer_data['cc_expiry_year'] = "";
        $customer_data['cc_tokenex_token'] = "";
        $customer_data['cc_cvc_encrypted'] = "";

        $check_data = $this->Card_model->get_customer_primary_card($customer_id);
        
        if(empty($check_data)){
            $this->Customer_model->update_customer($customer_id, $customer_data);

            $post_customer_data = $customer_data;
            $post_customer_data['customer_id'] = $customer_id;

            // do_action('post.update.customer', $post_customer_data);

            if(isset($cc_number)){
                $this->Card_model->create_customer_card_info($card_details);
            }
            if (isset($customer_data['customer_fields']))
            {
                $this->Customer_model->update_customer_fields($customer_id, $customer_data['customer_fields']); 
            }
        }
       
        $data['customer_id'] = $customer_id;
        $data['error']       = $error;
        $data['error_msg']   = $error_msg;

        echo json_encode($data);
    }

    /**
     * @param $token
     * @param $customer_id
     * @param $customer_data
     */
    private function tokenize_cc($token, $customer_id = null, $customer_data)
    {
        $this->paymentgateway->setCustomerById($customer_id);
        $this->paymentgateway->setCCToken($token);
        $external_entity_id                                             = $this->paymentgateway->createExternalEntity();
        $customer_data[$this->paymentgateway->getExternalEntityField()] = $external_entity_id;

        return $customer_data;
    }

    // function update_customer_AJAX()
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $encrypted_customerData = ($this->security->xss_clean($this->input->post('customer_data', TRUE)));
    //         $customer_data = json_decode(base64_decode($encrypted_customerData),true);
    //         $submittedToken = $customer_data['csrf_token'];
    //         if ($this->validateCSRFToken($submittedToken)) {
    //             // echo "CSRF token check is succeeded"."\n";
    //             if (isset($customer_data['csrf_token'])) {
    //                 $this->update_customer_AJAX_csrf();
    //             }
    //             // CSRF token is valid
    //             // echo "CSRF token is valid";
    //             // Process the form submission
    //         }
    //         else {
    //             // CSRF token validation failed
    //             echo "CSRF token check failed";
    //         }
    //     }
    // }

    public function update_customer_AJAX()
    {  
        $error     = false;
        $error_msg = '';

        // $customer_id   = sqli_clean($this->security->xss_clean($this->input->post('customer_id')));
        
        $encoded_customer_id   = sqli_clean($this->security->xss_clean($this->input->post('customer_id')));
        $customer_id = json_decode(base64_decode($encoded_customer_id), true);

        // $customer_data = ($this->security->xss_clean($this->input->post('customer_data', TRUE)));

        $encrypted_customerData = ($this->security->xss_clean($this->input->post('customer_data', TRUE)));
        $customer_data = json_decode(base64_decode($encrypted_customerData), true);
        
        // prx($customer_data); die();

        $customer_data['customer_name'] = sqli_clean($this->security->xss_clean($customer_data['customer_name']));

        $cvc = $customer_data['cvc'];
        $cc_number = $customer_data['cc_number'];
        $cardknox_token = '';
        $cardknox_cvv_token = '';

        if(isset($customer_data['cardknox_token']) && $customer_data['cardknox_token'])
        $cardknox_token = $customer_data['cardknox_token'];
        if(isset($customer_data['cardknox_cvv_token']) && $customer_data['cardknox_cvv_token'])
        $cardknox_cvv_token = $customer_data['cardknox_cvv_token'];

        unset($customer_data['cvc']);
        unset($customer_data['cc_number']);
        unset($customer_data['cardknox_token']);
        unset($customer_data['cardknox_cvv_token']);

        // if (isset($customer_data['csrf_token'])) {
        //     unset($customer_data['csrf_token']);
        // }

        $customer_data['customer_id'] = $customer_id;

        $card_data = $this->Card_model->get_active_card($customer_id, $this->company_id);

        $card_details = array();
        $card_details['cc_number'] = "";
        $card_details['cc_expiry_month'] = "";
        $card_details['cc_expiry_year'] = "";
        $card_details['cc_tokenex_token'] = "";
        $card_details['cc_cvc_encrypted'] = "";
        
        if(isset($customer_data['cc_number']) && $customer_data['cc_number']){
            $card_details['cc_number'] = $customer_data['cc_number'];
        }else{
            unset($card_details['cc_number']);
        }
        if(isset($customer_data['cc_expiry_month']) && $customer_data['cc_expiry_month']){
            $card_details['cc_expiry_month'] = $customer_data['cc_expiry_month'];
        }else{
            unset($card_details['cc_expiry_month']);
        }
        if(isset($customer_data['cc_expiry_year']) && $customer_data['cc_expiry_year']){
            $card_details['cc_expiry_year'] = $customer_data['cc_expiry_year'];
        }else{
            unset($card_details['cc_expiry_year']);
        }
        if(isset($customer_data['cc_tokenex_token']) && $customer_data['cc_tokenex_token']){
            $card_details['cc_tokenex_token'] = $customer_data['cc_tokenex_token'];
        }else{
            unset($card_details['cc_tokenex_token']);
        }
        if(isset($customer_data['cc_cvc_encrypted']) && $customer_data['cc_cvc_encrypted']){
            $card_details['cc_cvc_encrypted'] = $customer_data['cc_cvc_encrypted'];
        }else{
            unset($card_details['cc_cvc_encrypted']);
        }

        if(
            $cc_number && 
            is_numeric($cc_number) &&
            !strrpos($cc_number, 'X') && 
            $cvc && 
            is_numeric($cvc) &&
            !strrpos($cvc, '*') 
        ){
            
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

            if($card_data_array && $card_data_array['card']['card_number']){
                $customer_data['company_id'] = $this->company_id;
                $card_data_array['customer_data'] = $customer_data;
                $card_response = apply_filters('post.add.customer', $card_data_array);
                unset($card_data_array['customer_data']);
            }

            if(
                $card_response &&
                isset($card_response['tokenization_response']["data"]) &&
                isset($card_response['tokenization_response']["data"]["attributes"]) &&
                isset($card_response['tokenization_response']["data"]["attributes"]["card_token"])
            ){
                $card_token = $card_response['tokenization_response']["data"]["attributes"]["card_token"];
                $card_type = $card_response['tokenization_response']["data"]["attributes"]["card_type"];
                
                $cvc_encrypted = get_cc_cvc_encrypted($cvc, $card_token);

                $card_details['cc_cvc_encrypted'] = ($cvc_encrypted) ? $cvc_encrypted : "";
                $card_details['cc_number'] = 'XXXX XXXX XXXX '.substr($cc_number,-4);

                $meta['token'] = $card_token;
                $meta['card_type'] = $card_type;
                $card_details['customer_meta_data'] = json_encode($meta);
            }
            
        }
        else if($cardknox_token){
            $card_details['cc_number'] = $cc_number;
            $meta['cardknox_token'] = $cardknox_token;
            $meta['cardknox_cvv_token'] = $cardknox_cvv_token;
            $meta['source'] = 'cardknox';
            $card_details['customer_meta_data'] = json_encode($meta);
        }
         
        apply_filters('post.update.customer', $customer_data);
        
        if(isset($customer_data['cc_number']) && $customer_data['cc_number'])
           $customer_data['cc_number'] = "";
        if(isset($customer_data['cc_expiry_month']) && $customer_data['cc_expiry_month'])
           $customer_data['cc_expiry_month'] = "";
        if(isset($customer_data['cc_expiry_year']) && $customer_data['cc_expiry_year'])
           $customer_data['cc_expiry_year'] = "";
        if(isset($customer_data['cc_tokenex_token']) && $customer_data['cc_tokenex_token']) 
           $customer_data['cc_tokenex_token'] = "";
        if(isset($customer_data['cc_cvc_encrypted']) && $customer_data['cc_cvc_encrypted'])
           $customer_data['cc_cvc_encrypted'] = "";
        
        $this->Customer_model->update_customer($customer_id, $customer_data);
        /*update card */
        $cus_res = $this->Card_model->get_customer_cards($customer_id);
        if($cus_res){
            if($card_details && count($card_details) > 0) {
                $this->Card_model->update_customer_primary_card($customer_id, $card_details);
            }
        }else{
            $card_details['is_primary'] = 1;
            $card_details['customer_id'] = $customer_id;
            $card_details['company_id'] = $this->company_id;
            $card_details['customer_name'] = $customer_data['customer_name'];
            $this->Card_model->create_customer_card_info($card_details);
        }
       
        if (isset($customer_data['customer_fields']))
        {
            $this->Customer_model->update_customer_fields($customer_id, $customer_data['customer_fields']); 
        }
        
        $data['error']     = $error;
        $data['error_msg'] = $error_msg;

        echo json_encode($data);
    }

    public function update_customer_card_AJAX()
    {
       $error     = false;
       $error_msg = '';
       
        $customer_data = $this->input->post('customer_data');
        $customer_data['company_id'] = $this->company_id;

        $customer_id = $customer_data['customer_id'];
        $cardId   = sqli_clean($this->security->xss_clean($this->input->post('card_id'))); // card_id
        $cc_number = isset($customer_data['card_number']) && $customer_data['card_number'] ? $customer_data['card_number'] : NULL;
        $cvc = $customer_data['cvc'];
        
        $card_details = array(
           'is_primary' => 1,
           'customer_id' => $customer_data['customer_id'],
           'customer_name' => $customer_data['customer_name'],
           'card_name' => $customer_data['card_name'],
           'company_id' => $this->company_id,
           'cc_number' => $cc_number,
           'cc_expiry_month' => isset($customer_data['cc_expiry_month']) && $customer_data['cc_expiry_month'] ? $customer_data['cc_expiry_month'] : NULL,
           'cc_expiry_year' => isset($customer_data['cc_expiry_year']) && $customer_data['cc_expiry_year'] ? $customer_data['cc_expiry_year'] : NULL
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

                $card_data_array['customer_data'] = $customer_data;

                $card_response = apply_filters('post.add.customer', $card_data_array);

                unset($card_data_array['customer_data']);
            }
            if(
                $card_response &&
                isset($card_response['tokenization_response']["data"]) &&
                isset($card_response['tokenization_response']["data"]["attributes"]) &&
                isset($card_response['tokenization_response']["data"]["attributes"]["card_token"])
            ){
                $card_token = $card_response['tokenization_response']["data"]["attributes"]["card_token"];
                $card_type = $card_response['tokenization_response']["data"]["attributes"]["card_type"];

                $cvc_encrypted = get_cc_cvc_encrypted($cvc, $card_token);

                $card_details['cc_cvc_encrypted'] = ($cvc_encrypted) ? $cvc_encrypted : "";
                $card_details['cc_number'] = 'XXXX XXXX XXXX '.substr($cc_number,-4);

                $meta['token'] = $card_token;
                $meta['card_type'] = $card_type;
                $card_details['customer_meta_data'] = json_encode($meta);
            }
        } 

        if(isset($cc_number)){
            $this->Card_model->update_customer_card($cardId, $customer_id, $card_details);
        }
        
        $booking_id = $this->input->post('booking_id');

        $data = $card_details;

        $this->_create_booking_log($booking_id, "Credit Card Updated by " . $customer_data['customer_name'], USER_LOG);
        
        echo json_encode($data);
    }

    // not used anymore
    public function update_customer_card_AJAX_customer_table()
    {
        $error     = false;
        $error_msg = '';

        $customer_id   = $this->input->post('customer_id');
        //$cardId   = $this->input->post('card_id'); // card_id
        $customer_data = $this->input->post('customer_data');
        $token         = $this->input->post('token');
        $cc_tokenex_token = $this->input->post('cc_tokenex_token');
        $cc_cvc_encrypted = $this->input->post('cc_cvc_encrypted');
        
        if (
            empty($customer_data['cc_expiry_month'])
            or empty($customer_data['cc_expiry_year'])
        ) {
            $customer_data['cc_number']                                     = '';
            $customer_data['cc_expiry_month']                               = '';
            $customer_data['cc_expiry_year']                                = '';
            if ($this->paymentgateway->getExternalEntityField()) {
                $customer_data[$this->paymentgateway->getExternalEntityField()] = '';
            }
        }
        
        if ($cc_tokenex_token) {
            $customer_data['cc_tokenex_token']  = $cc_tokenex_token;
        }
        if ($cc_cvc_encrypted) {
            $customer_data['cc_cvc_encrypted']  = $cc_cvc_encrypted;
        }
                       
        if ($token) {
            try {
                $customer_data = $this->tokenize_cc($token, $customer_id, $customer_data);
            } catch (Exception $e) {
                $error     = true;
                $error_msg = $e->getMessage();
            }
        }
        
        $row = $this->Customer_model->update_customer_card_customer_table($customer_id, $customer_data);

        /*if (isset($customer_data['customer_fields']))
        {
            $this->Customer_model->update_customer_fields($customer_id, $customer_data['customer_fields']); 
        }*/
        if($row == "success"){
            $data['res']     = "success";
        }else{
            $data['res']     = "fail";
        }
        $data['error']     = $error;
        $data['error_msg'] = $error_msg;

        echo json_encode($data);
    }

    function get_customer_types()
    {
        $data = array();

        if(!is_null($customer_types = $this->Customer_type_model->get_customer_types($this->company_id, false, true)))
        {
            $data['customer_types'] = $customer_types;
        }
        $common_customer_types = json_decode(COMMON_CUSTOMER_TYPES, true);
        $common_types_setting = $this->Customer_type_model->get_common_customer_type_settings($this->company_id);

        $sort_orders = array();
        $common_types = array();
        foreach($common_customer_types as $id => $name)
        {
            if(isset($common_types_setting[$id])){
                $common_types_setting[$id]['sort_order'] = is_numeric($common_types_setting[$id]['sort_order']) ? $common_types_setting[$id]['sort_order'] : (count($sort_orders) > 0 ? max($sort_orders) : 0);
                if(in_array($common_types_setting[$id]['sort_order'], $sort_orders)){
                    while(in_array($common_types_setting[$id]['sort_order'], $sort_orders)){
                        $common_types_setting[$id]['sort_order'] = $common_types_setting[$id]['sort_order'] + 1;
                    }
                    $new_sort_order = $common_types_setting[$id]['sort_order'];
                    $sort_orders[] = $new_sort_order;
                }else{
                    $new_sort_order = $common_types_setting[$id]['sort_order'];
                    $sort_orders[] = $new_sort_order;
                }

                $common_types[$new_sort_order] = array(
                                                'id' => $id,
                                                'name' => $name,
                                                'company_id' => $this->company_id,
                                                'is_deleted' => 0,
                                                'is_common_type' => true,
                                                'sort_order' => $new_sort_order,
                                            );
            }else{

                $new_sort_order = (count($sort_orders) > 0 ? max($sort_orders) : 0) + 1;
                $sort_orders[] = $new_sort_order;

                $common_types[$new_sort_order] = array(
                                                'id' => $id,
                                                'name' => $name,
                                                'company_id' => $this->company_id,
                                                'is_deleted' => 0,
                                                'is_common_type' => true,
                                                'sort_order' => $new_sort_order,
                                            );
            }

        }

        $customer_types = array();
        if(isset($data['customer_types']) && count($data['customer_types']) > 0){
            foreach($data['customer_types'] as $key => $value){
                $data['customer_types'][$key]['sort_order'] = isset($data['customer_types'][$key]['sort_order']) ? 
                        $data['customer_types'][$key]['sort_order'] : (count($sort_orders) > 0 ? max($sort_orders) : 0);

                if(in_array($data['customer_types'][$key]['sort_order'], $sort_orders)){ 
                    while(in_array($data['customer_types'][$key]['sort_order'], $sort_orders)){
                        $data['customer_types'][$key]['sort_order'] = $data['customer_types'][$key]['sort_order'] + 1;
                    }
                    $new_sort_order = $data['customer_types'][$key]['sort_order'];
                    $sort_orders[] = $new_sort_order;
                }else{ 
                    $new_sort_order = $data['customer_types'][$key]['sort_order'];
                    $sort_orders[] = $new_sort_order;
                }
  
                $customer_types[$new_sort_order] = $value;
            }
        }
        
        $data['customer_types'] = $common_types + $customer_types;
        ksort($data['customer_types']);
        $customer_types = array();
        foreach($data['customer_types'] as $key => $val)
        {
            $customer_types[] = $val;
        }
        echo json_encode($customer_types);
    }

    function get_customer_fields()
    {
        // ============SET csrf_token==================
         //    $cookieName = 'csrf_token';
         //    $cookieValue = $this->generateCSRFToken();
         //    $storedToken = $cookieValue;
         //    // Set the cookie
         // setcookie($cookieName, $cookieValue, time() + 3600, '/');
        // ============SET csrf_token==================

        $result = $this->Customer_field_model->get_customer_fields_for_customer_form($this->company_id);
        echo json_encode($result);
    }

    function get_common_customer_fields()
    {
        $result = $this->Customer_field_model->get_customer_fields_for_customer_form($this->company_id, true);
        echo json_encode($result);
    }

    function download_csv_export()
    {
        //get user's shift information  
        $filters['company_id'] = $this->company_id;
        $filters['order_by'] = $this->session->userdata('customer_order_by');
        $filters['order'] = $this->session->userdata('customer_order');
        $filters['submit'] = $this->security->xss_clean($this->input->get('submit'));
        $filters['customer_type_id'] = sqli_clean($this->security->xss_clean($this->input->get('customer_type_id')));
        $filters['show_deleted'] = sqli_clean($this->security->xss_clean($this->input->get('show_deleted')));
        $filters['search_query'] = sqli_clean($this->security->xss_clean($this->input->get('search_query')));
        $filters['selling_date'] = ($this->security->xss_clean($this->input->get('date')) ? sqli_clean($this->security->xss_clean($this->input->get('date'))) : "");

        $csv_array = $this->Customer_model->get_customers($filters);

        $customer_types = $this->Customer_type_model->get_customer_types($this->company_id);

        $common_customer_types = json_decode(COMMON_CUSTOMER_TYPES, true);

        foreach($common_customer_types as $id => $name) {
            $customer_types[] = array(
                                        'id' => $id,
                                        'name' => $name
                                    );
        }

        $customer_type_array = array();
        foreach ($customer_types as $key => $value) {
            $customer_type_array[$value['id']] = $value['name'];
        }

        // get customer fields
        $customer_ids_array = array();
        foreach($csv_array as $key => $value){
            if($value->customer_id) {
                $customer_ids_array[] = $value->customer_id;
            }
        }

        $customer_fields = $this->Customer_model->get_customer_fields($customer_ids_array, true);
        $customer_fields_array = $customer_field_values = array();
        foreach($customer_fields as $key => $field)
        {
            if(
                isset($field['value']) && $field['value']
            ) {
                $customer_fields_array[] = array(
                                    'id' => $field['customer_id'],
                                    'name' => $field['name'],
                                    'value' => $field['value'],
                                    'cf_id' => $field['id']
                                );
            } 

            $field_names[] = $field['name'];
            $cf_field_ids[] = $field['id'];
        }

        $field_names = array_values(array_unique($field_names));
        $cf_field_ids = array_values(array_unique($cf_field_ids));
        $matched_ids = array_column($customer_fields_array, 'id');
        $unmatched_ids = array_diff($customer_ids_array, $matched_ids);

        foreach($unmatched_ids as $key => $field)
        {
            foreach ($field_names as $key1 => $value) {
                $customer_fields_array[] = array(
                                    'id' => $field,
                                    'name' => $value,
                                    'value' => "",
                                    'cf_id' => $cf_field_ids[$key1]
                                );
            }
        }

        foreach($matched_ids as $key => $field)
        {
            foreach ($field_names as $key1 => $value) {
                if(
                    !in_array($value, $customer_fields_array[$key])
                ) {

                    $customer_fields_array[] = array(
                                        'id' => $field,
                                        'name' => $value,
                                        'value' => "",
                                        'cf_id' => $cf_field_ids[$key1]
                                    );
                }
            }
        }
        $id = array_column($customer_fields_array, 'id');
        $cf_id = array_column($customer_fields_array, 'cf_id');

        array_multisort($id, SORT_ASC, $cf_id, SORT_ASC, $customer_fields_array);

        $csv_keys = array(
                            'customer_id',
                            'customer_name',
                            'email',
                            'address',
                            'city',
                            'region',
                            'country',
                            'postal_code',
                            'phone',
                            'fax',
                            'customer_notes',
                            'is_deleted',
                            'customer_type',
                            'charge_total',
                            'payment_total',
                            'balance'
                        );

        $customer_field_names = array();
        foreach ($customer_fields as $key => $value) {
            if(!in_array($value['name'], $customer_field_names)){
                $csv_keys[] = $customer_field_names[] = $value['name'];
            }
        }

        $customers = array($csv_keys);
        $customer_row = array();
        foreach($csv_array as $key => $value){

            $customer_row = array();
            $customer_row[] = $value->customer_id ? $value->customer_id : "";
            $customer_row[] = $value->customer_name ? $value->customer_name : "";
            $customer_row[] = $value->email ? $value->email : "";
            $customer_row[] = $value->address ? $value->address : "";
            $customer_row[] = $value->city ? $value->city : "";
            $customer_row[] = $value->region ? $value->region : "";
            $customer_row[] = $value->country ? $value->country : "";
            $customer_row[] = $value->postal_code ? $value->postal_code : "";
            $customer_row[] = $value->phone ? $value->phone : "";
            $customer_row[] = $value->fax ? $value->fax : "";
            $customer_row[] = $value->customer_notes ? $value->customer_notes : "";
            $customer_row[] = $value->is_deleted ? $value->is_deleted : 0;
            $customer_row[] = $value->customer_type_id ? $customer_type_array[$value->customer_type_id] : "";
            $customer_row[] = $value->charge_total ? number_format($value->charge_total, 2, ".", ",") : 0;
            $customer_row[] = $value->payment_total ? number_format($value->payment_total, 2, ".", ",") : 0;
            $customer_row[] = $value->balance ? number_format($value->balance, 2, ".", ",") : 0;
            $customers[$value->customer_id] = $customer_row;
        }

        $inputArray = $customer_fields_array;

        $outputArray = array();

        foreach ($inputArray as $entry) {
            $key = $entry['id'] . '_' . $entry['name'];

            if (!isset($outputArray[$key])) {
                $outputArray[$key] = $entry;
            } elseif ($entry['value'] !== '') {
                // Update the entry with non-blank value
                $outputArray[$key]['value'] = $entry['value'];
            }
        }

        $outputArray = array_values($outputArray); // Re-index the array

        $customer_fields_array = $outputArray;

        $customer_arr = array();
        foreach ($customer_fields_array as $key => $value) {
            array_push($customers[$value['id']], $value['value']);
        }

        $customers = array_values($customers);
        $this->load->helper('download');
        force_download_csv($customers, "customers.csv");
    }

    public function get_cc_cvc_encrypted()
    {
        $cvc = $this->input->post('cvc');
        $token = $this->input->post('token');
        if($cvc && is_numeric($cvc) && $token){
            $cc_cvc_encrypted = $this->encrypt->encode($cvc, $token);
            echo json_encode(array("success" => true, "cc_cvc_encrypted" => $cc_cvc_encrypted));
            return;
        }
        echo json_encode(array("success" => false));
    }
    
    public function detokenize_card()
    {
        if(!$this->input->is_ajax_request())
            return;
        
        $customer_id = sqli_clean($this->security->xss_clean($this->input->get('customer_id')));
        $customer = $this->Customer_model->get_customer($customer_id);
        $data = array();
        
        if($customer['cc_number'])
           $customer['cc_number'] = "";
        if($customer['cc_expiry_month'])
           $customer['cc_expiry_month'] = "";
        if($customer['cc_expiry_year'])
           $customer['cc_expiry_year'] = "";
        if($customer['cc_tokenex_token']) 
           $customer['cc_tokenex_token'] = "";
        if($customer['cc_cvc_encrypted'])
           $customer['cc_cvc_encrypted'] = "";
        
       
        $card_details = $this->Card_model->get_customer_primary_card($customer_id);
        
            if(isset($card_details) && $card_details){
                $customer['cc_number'] = $card_details['cc_number'];
                $customer['cc_expiry_month'] = $card_details['cc_expiry_month'];
                $customer['cc_expiry_year'] = $card_details['cc_expiry_year'];
                $customer['cc_tokenex_token'] = $card_details['cc_tokenex_token'];
                $customer['cc_cvc_encrypted'] = $card_details['cc_cvc_encrypted'];
            }
        if(isset($customer['cc_tokenex_token']) && $customer['cc_tokenex_token'])
        {
            $data['Token'] = $customer['cc_tokenex_token'];
            $response = $this->tokenex->detokenize($data);

            if(isset($response['data']) && $response['data'])
            {
                echo $response['data'];
            }else{
                print_r($response);
            }
        }
        echo false;
    }
        
    function clear_all_groups_Ajax() {
        $customer_id = $this->input->post("customer_id");
        echo $this->Group_model->update_customer_booking_group($customer_id);
    }
    
    function change_customer_type_id()
    {
        $array = array('1', '2');
        
        foreach($array as $value)
        {
            $customer_type = $this->Customer_type_model->get_customer_type($value);
            
            $customer_type_id = $customer_type[0]['id'];
            if($customer_type_id)
            {
                $last_insert_customer_type = $this->Customer_type_model->get_last_customer_type();
                $new_customer_type__id = $last_insert_customer_type[0]['max'] + 1;            

                $update_customer_id = $this->Customer_type_model->change_customer_type($customer_type_id, $new_customer_type__id);            

                $customers = $this->Customer_type_model->get_customers($value);
                
                foreach($customers as $cust)
                {
                    $update_customer = $this->Customer_model->update_customer_type($cust['customer_type_id'], $new_customer_type__id);
                }
            }
        }
    }
    function get_customer_card_AJAX()
    {
        $customer_id = sqli_clean($this->security->xss_clean($this->input->post('customer_id')));
        $customer = $this->Card_model->get_customer_cards($customer_id);
        echo json_encode($customer);
    }
    function get_customer_card_AJAX_by_Id()
    {
        $customer_id = sqli_clean($this->security->xss_clean($this->input->post('customer_id')));
        $customer_card_id = sqli_clean($this->security->xss_clean($this->input->post('customer_card_id')));
        $customer = $this->Card_model->get_customer_card_by_id($customer_id, $customer_card_id);
        echo json_encode($customer);
    }
    function get_customer_card_data()
    {
        
        $booking_id = sqli_clean($this->security->xss_clean($this->input->post('booking_id')));
        
        $cus_assay = array();
        $booking_data = $this->Booking_model->get_booking($booking_id);
        $customer = $this->Card_model->get_customer_card_detail($booking_data['booking_customer_id']);
        $cus_assay[] = $customer ;
        $staying_customers = $this->Customer_model->get_staying_customers($booking_id);
        if($staying_customers){
             foreach($staying_customers as $cus){
               $sta_data = $this->Card_model->get_customer_card_detail($cus['customer_id']);
               $cus_assay[] = $sta_data;
            } 
        }
           
        echo json_encode($cus_assay); 
           
        
        
    }
    function get_customer_card_AJAX_by_Id_customer_table()
    {
        $customer_id = $this->input->post('customer_id');
       // $customer_card_id = $this->input->post('customer_card_id');
        $customer = $this->Card_model->get_customer_card_by_id_customer_table($customer_id);
        echo json_encode($customer);
    }
    
    function insert_card_details(){
       $error     = false;
       $error_msg = '';
       
        $customer_data = $this->input->post('customer_data');
        $customer_data['company_id'] = $this->company_id;

        $customer_id = $customer_data['customer_id'];

        $cc_number = isset($customer_data['card_number']) && $customer_data['card_number'] ? $customer_data['card_number'] : NULL;
        $cvc = $customer_data['cvc'];
        
        $card_details = array(
           'is_primary' => 1,
           'customer_id' => $customer_data['customer_id'],
           'customer_name' => $customer_data['customer_name'],
           'card_name' => $customer_data['card_name'],
           'company_id' => $this->company_id,
           'cc_number' => $cc_number,
           'cc_expiry_month' => isset($customer_data['cc_expiry_month']) && $customer_data['cc_expiry_month'] ? $customer_data['cc_expiry_month'] : NULL,
           'cc_expiry_year' => isset($customer_data['cc_expiry_year']) && $customer_data['cc_expiry_year'] ? $customer_data['cc_expiry_year'] : NULL
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

                $card_data_array['customer_data'] = $customer_data;

                $card_response = apply_filters('post.add.customer', $card_data_array);

                unset($card_data_array['customer_data']);
            }
            if(
                $card_response &&
                isset($card_response['tokenization_response']["data"]) &&
                isset($card_response['tokenization_response']["data"]["attributes"]) &&
                isset($card_response['tokenization_response']["data"]["attributes"]["card_token"])
            ){
                $card_token = $card_response['tokenization_response']["data"]["attributes"]["card_token"];
                $card_type = $card_response['tokenization_response']["data"]["attributes"]["card_type"];

                $cvc_encrypted = get_cc_cvc_encrypted($cvc, $card_token);

                $card_details['cc_cvc_encrypted'] = ($cvc_encrypted) ? $cvc_encrypted : "";
                $card_details['cc_number'] = 'XXXX XXXX XXXX '.substr($cc_number,-4);

                $meta['token'] = $card_token;
                $meta['card_type'] = $card_type;
                $card_details['customer_meta_data'] = json_encode($meta);
            }
        } 

        $check_data = $this->Card_model->get_customer_primary_card($customer_id);
        
        if(empty($check_data)){
            $this->Customer_model->update_customer($customer_id, $customer_data);

            $post_customer_data = $customer_data;
            $post_customer_data['customer_id'] = $customer_id;

            // do_action('post.update.customer', $post_customer_data);

            if(isset($cc_number)){
                $this->Card_model->create_customer_card_info($card_details);
            }
            if (isset($customer_data['customer_fields']))
            {
                $this->Customer_model->update_customer_fields($customer_id, $customer_data['customer_fields']); 
            }
        } else {
            if(isset($cc_number)){
                $card_details['is_primary'] = 0;
                $this->Card_model->create_customer_card_info($card_details);
            }
        }
        
        $booking_id = $this->input->post('booking_id');
        
        $data = array(
           'customer_id' => $customer_data['customer_id'],
           'customer_name' => $customer_data['customer_name'],
           'card_name' => $customer_data['card_name'],
           'company_id' => $this->company_id,
           'cc_expiry_month' => $customer_data['cc_expiry_month'],
           'cc_expiry_year' => $customer_data['cc_expiry_year'],
           'cc_tokenex_token' => $cc_tokenex_token ?? null,
           'cc_cvc_encrypted' => $cc_cvc_encrypted ?? null,
           'cc_number' => $cc_number,
        );

        $is_primary_card = $this->Card_model->get_customer_primary_card($data['customer_id']);
        $customer_data['company_id'] = $this->company_id;

        if($is_primary_card) {
            if($is_primary_card['is_primary']) {
                // $card_details['is_primary'] = 0;
                // $insert_customer_card = $this->Card_model->create_customer_card_info($card_details);
            } else {
                $card_details['is_primary'] = 0;
                $insert_customer_card = $this->Card_model->create_customer_card_info($card_details);
            }
            // $card_details['is_primary'] = 0;
            // // $insert_customer_card = $this->Card_model->create_customer_card_info($card_details);
            // $insert_customer_card = $this->Card_model->update_customer_card($is_primary_card['id'], $data['customer_id'], $card_details);
            // $data['error']     = $error;
            // $data['error_msg'] = $error_msg;
        } 
        // else {
        //     $card_details['is_primary'] = 1;
        //     $insert_customer_card = $this->Card_model->create_customer_card_info($card_details);
        //     $data['error']     = $error;
        //     $data['error_msg'] = $error_msg;
        // }
        
        $customer_data['card_id'] = $insert_customer_card ?? null; 
        apply_filters('post.create.payment_source', $customer_data);

        $this->_create_booking_log($booking_id, "New Credit Card Added by " . $customer_data['customer_name'], USER_LOG);
        
        echo json_encode($data);
    }
    
    function delete_customer_card_AJAX()
    {   
        $customer_id = sqli_clean($this->security->xss_clean($this->input->post('customer_id')));
        $card_id = sqli_clean($this->security->xss_clean($this->input->post('card_id')));
        $card_token = sqli_clean($this->security->xss_clean($this->input->post('card_token'))); 
        $booking_id = sqli_clean($this->security->xss_clean($this->input->post('booking_id'))); 

        $cards = $this->Card_model->get_token_cards($card_token);
        if (!$cards || $cards && count($cards) <= 1) {
            $this->tokenex->delete_token($card_token);
        }
        
        $customer_data = array(
                        'customer_id' =>$customer_id,
                        'card_id'=> $card_id
                    );
        apply_filters('post.delete.payment_source', $customer_data);

        $del_result = $this->Card_model->delete_customer_card($customer_id, $card_id, $this->company_id);

       
        if($del_result){
            $customer_detail = $this->Customer_model->get_customer($customer_id);
            $this->_create_booking_log($booking_id, "Credit Card Deleted by " . $customer_detail['customer_name'], USER_LOG);
            echo true;
        }else{
            echo false;
        }

        //echo json_encode($del);
    }
   
    public function update_customer_card_is_primary()
    {
        $customer_id = sqli_clean($this->security->xss_clean($this->input->post('customer_id')));
        $card_id = sqli_clean($this->security->xss_clean($this->input->post('card_id')));
        $active = sqli_clean($this->security->xss_clean($this->input->post('active')));

        $update_res = $this->Card_model->update_customer_card_is_primary_card_table($customer_id, $card_id, $active, $this->company_id);

        $data = array(
            'customer_id' => $customer_id,
            'card_id' => $card_id,
        );

        if($active == 'active'){
            apply_filters("post.update.defult_payment_source",$data);
        }
    
        if($update_res){
            $res = "success";
        }else{
            $res = "fail";
        }
        echo json_encode($res);
    }
    //insert data from customer to customer_crad_details
    function get_card_data_for_exixting_customer()
    { 
        $customer_data = $this->Card_model->get_all_customer();
       // print_r($customer_data);
        $customer = array(); $i=0;
        foreach($customer_data as $cus){
           $customer[$i]['customer_id']         = $cus['customer_id'];
           $customer[$i]['customer_name']       = $cus['customer_name'];
           $customer[$i]['company_id']          = $cus['company_id'];
           $customer[$i]['cc_number']           = $cus['cc_number'];
           $customer[$i]['cc_expiry_month']     = $cus['cc_expiry_month'];
           $customer[$i]['cc_expiry_year']      = $cus['cc_expiry_year'];
           $customer[$i]['cc_tokenex_token']    = $cus['cc_tokenex_token'];
           $customer[$i]['cc_cvc_encrypted']    = $cus['cc_cvc_encrypted'];
           $i++;
        }
       // $customer_card_data = $this->Card_model->get_all_customer_cards();
        
       foreach($customer as $customer_data){
          $insert_customer_card = $this->Card_model->create_customer_card_info($customer_data);
       }
    }

    function _create_booking_log($booking_id, $log, $log_type = USER_LOG) {

        $this->Booking_log_model->insert_log(
                array(
                    "selling_date" => $this->selling_date,
                    "booking_id" => $booking_id,
                    "date_time" => gmdate('Y-m-d H:i:s'),
                    "log_type" => $log_type,
                    "log" => $log,
                    "user_id" => $this->user_id
                )
        );
    }

    function detokenize_unused_token($limit = 1000)
    {
        $card_data = $this->Card_model->get_unused_card_details($limit);
        
        $customer_ids = $card_ids = $response = $update_card_data = array();

        if($card_data && count($card_data) > 0) {
            foreach ($card_data as $key => $value) {
                $token = array("Token" => $value['token']);

                $response = $this->tokenex->detokenize($token);

                $card_data[$key]['card_id'] = $value['card_id'];
                $card_data[$key]['card_number'] = ($response && count($response) > 0 && isset($response['data']) && $response['data']) ? $response['data'] : '';
                $card_data[$key]['card_cvc'] = isset($value['cvc']) && $value['cvc'] ? $this->encrypt->decode($value['cvc'], $value['token']) : '';
                $card_data[$key]['token'] = $value['token'];
                $card_data[$key]['cvc'] = $value['cvc'];
                $card_data[$key]['detokenized_date'] = date('Y-m-d');

                $customer_ids[] = $value['customer_id'];
                $card_ids[] = $value['card_id'];
            }

            $this->Card_model->insert_detokenize_cards($card_data); // insert all unused tokens
            
            $update_card_data = array('is_card_deleted' => 1);
            $this->Card_model->update_detokenize_cards($customer_ids, $card_ids, $update_card_data, "customer_card_detail"); // soft delete all unused tokens

            echo json_encode(array('success' => true));
        } else {
            echo json_encode(array('success' => false, 'message' => l('Unused cards unavailable.', true)));
        }
    }

    function delete_unused_token($limit = 1000)
    {
        $unused_tokens = $this->Card_model->get_unused_tokens($limit);

        $customer_ids = $card_ids = array();

        if($unused_tokens && count($unused_tokens) > 0) {
            foreach ($unused_tokens as $key => $value) {
                
                $this->tokenex->delete_token($value['token']); // delete all unused tokens from tokenex
            
                $customer_ids[] = $value['customer_id'];
                $card_ids[] = $value['card_id'];
            }

            $update_card_data = array('is_deleted' => 1);
            $this->Card_model->update_detokenize_cards($customer_ids, $card_ids, $update_card_data, "customer_deleted_token_details"); // soft delete all unused tokens in new table
            
            //$this->Card_model->delete_unused_customer_cards($customer_ids, $card_ids); // delete all unused tokens from Minical
            
            echo json_encode(array('success' => true));
        }
    }

    function post_add_customer_callback($company_id) {

        $card_data_array = file_get_contents("php://input");
        $card_data_array = json_decode($card_data_array, true);

        $card_response = apply_filters('post.add.customer', $card_data_array);

        echo json_encode(array('resp' => $card_response));

    }
}
