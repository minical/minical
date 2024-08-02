<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Email_template {

    public function __construct()
    {
        $this->ci =& get_instance();

        $this->ci->load->model('Booking_model');
        $this->ci->load->model('Booking_extra_model');
        $this->ci->load->model('Booking_room_history_model');
        $this->ci->load->model('Room_model');
        $this->ci->load->model('Room_type_model');
        $this->ci->load->model('Rate_model');
        $this->ci->load->model('Customer_model');
        $this->ci->load->model('Company_model');
        $this->ci->load->model('Charge_type_model');
        $this->ci->load->model('Image_model');
        $this->ci->load->model('Booking_source_model');
        $this->ci->load->model('Tax_model');
        $this->ci->load->model('Whitelabel_partner_model');
        $this->ci->load->model('Currency_model');

        $this->ci->load->library('Email');
        $this->ci->load->helper('language_translation_helper');

        log_message('debug', "Email template initialized");
    }

    function set_language($company_language_id) {
        load_translations($company_language_id);
    }

    function reset_language($company_language_id) {
        if(empty($this->ci->session->userdata('language_id')))
        {
            get_language_id($this->ci->session->userdata('language'));
        }
        $language_id = $this->ci->session->userdata('language_id');
        if ($company_language_id != $language_id) {
            load_translations($language_id);
        }
    }

    function send_invoice_email($booking_id, $folio_id = null)
    {
        $booking = $this->ci->Booking_model->get_booking_detail($booking_id);
        $company = $this->ci->Company_model->get_company($booking['company_id']);

        // Allow send invoice emails only for paying customers
        if(
            isset($company['subscription_state']) && 
            $company['subscription_state'] != 'active'
        ) {

            $customer_details = $this->ci->Customer_model->get_customer_info($booking['booking_customer_id']);

            echo "Sending emails is allowed for paying accounts only";
            return false;
        }


        $whitelabelinfo = null;
        $white_label_detail = $this->ci->Whitelabel_partner_model->get_partners(array('id' => $company['partner_id']));
        
        if($white_label_detail && isset($white_label_detail[0])) {
            $whitelabelinfo = $white_label_detail[0];
        }

        $base_url = base_url();

        if(
            $whitelabelinfo && 
            isset($whitelabelinfo['support_email']) && 
            $whitelabelinfo['support_email']
        ) {
            if(
                isset($whitelabelinfo['domain']) && 
                $whitelabelinfo['domain']
            ) {
                $base_url = $whitelabelinfo['domain'].'/';
            }
        }
        
        //$base_url = $whitelabelinfo && isset($whitelabelinfo['support_email']) && $whitelabelinfo['support_email'] ? isset($whitelabelinfo['domain']) && $whitelabelinfo['domain'] ? $whitelabelinfo['domain'].'/' : base_url();

        $this->set_language($company['default_language']);

        $email = $booking['booking_customer_email'];
        if ($email != "")
        {
            $customer_name = $booking['booking_customer_name'];
            $invoice_hash = $booking['invoice_hash'];
            $invoice_link = $base_url . "invoice/show_invoice_read_only/".$invoice_hash;
            $invoice_link = $folio_id ? $invoice_link.'/'.$folio_id : $invoice_link;
            $review_link = base_url() . "review/?hash=".$invoice_hash;

            $invoice_email_header = '';
            if($this->ci->invoice_email) {
                $invoice_email_header = $company['invoice_email_header'];
            }

            // generate html email
            // for a good guideline, read: http://kb.mailchimp.com/article/how-to-code-html-emails
            // Company logo
            $email_header = $this->ci->Image_model
                ->get_company_logo_url($company['company_id'], $company['logo_image_group_id'], 1);

            $content =  $email_header.l("Dear", true)." ".$customer_name.",
                                <br/><br/>"
                .$invoice_email_header.
                "<br/><br/>
                                ".l('Please visit the following link to view your invoice', true).": <a href='".$invoice_link."'>".$invoice_link."</a>
                                <br/><br/>";

            $content = $content."<br/><br/>".l('Thank you for your business', true).",<br/><br/>"
                .$company['name']."
                    <br/>".$company['email']. "<br/>".$company['phone']."<br/>";


            $config['mailtype'] = 'html';

            $this->ci->email->initialize($config);

            $email_list = $email;
            if(isset($company['send_copy_to_additional_emails']) && $company['send_copy_to_additional_emails'])
            {
                $email_list .= ",".$company['additional_company_emails'];
            }

            $whitelabelinfo = $this->ci->session->userdata('white_label_information');

            $from_email = isset($whitelabelinfo['do_not_reply_email']) && $whitelabelinfo['do_not_reply_email'] ? $whitelabelinfo['do_not_reply_email'] : (isset($company['email']) && $company['email'] ? $company['email'] : 'donotreply@minical.io');

            $this->ci->email->from($from_email, $company['name']);
            $this->ci->email->to($email_list);
            $this->ci->email->reply_to($company['email'], $company['name']);
            $this->ci->email->subject("Invoice ".$booking_id." from ".$company['name']);
            $this->ci->email->message($content);

            $this->ci->email->send();
            echo "sent email to ".$email_list;

            if(isset($_GET['dev_mode'])) {
                echo $this->ci->email->print_debugger();
            }

            // Log the email sent
            $log_data['date_time'] = gmdate('Y-m-d H:i:s');
            $log_data['selling_date'] = $this->ci->session->userdata('current_selling_date');
            $log_data['user_id'] = $this->ci->session->userdata('user_id');
            $log_data['log_type'] = INVOICE_LOG;
            $log_data['booking_id'] = $booking_id;
            $log_data['log'] = "Invoice sent via e-mail to ".$customer_name;
            $this->ci->load->model('Booking_log_model');

            $this->ci->Booking_log_model->insert_log($log_data);

            $this->reset_language($company['default_language']);
        }
    }

    function send_master_invoice_email($booking_id, $group_id)
    {
        $booking = $this->ci->Booking_model->get_booking_detail($booking_id);
        $company = $this->ci->Company_model->get_company($booking['company_id']);

        $this->set_language($company['default_language']);

        $email = $booking['booking_customer_email'];
        if ($email != "")
        {
            $customer_name = $booking['booking_customer_name'];
            $invoice_hash = $booking['invoice_hash'];
            $invoice_link = base_url() . "invoice/show_master_invoice_read_only/".$invoice_hash;
            //$review_link = base_url() . "review/?hash=".$invoice_hash;
            $invoice_email_header = '';
            if($this->ci->invoice_email) {
                $invoice_email_header = $company['invoice_email_header'];
            }

            // generate html email
            // for a good guideline, read: http://kb.mailchimp.com/article/how-to-code-html-emails
            // Company logo
            $email_header = $this->ci->Image_model
                ->get_company_logo_url($company['company_id'], $company['logo_image_group_id'], 1);
            $content =  $email_header.l("Dear", true).' '.$customer_name.",
                                <br/><br/>"
                .$invoice_email_header.
                "<br/><br/>
                                ".l("Please visit the following link to view your master invoice", true).": <a href='".$invoice_link."'>".$invoice_link."</a>
                                <br/><br/>";

            $content = $content."<br/><br/>".l('Thank you for your business', true).",<br/><br/>"
                .$company['name']."
                    <br/>".$company['email']. "<br/>".$company['phone']."<br/>";


            $config['mailtype'] = 'html';

            $this->ci->email->initialize($config);

            $email_list = $email;
            if(isset($company['send_copy_to_additional_emails']) && $company['send_copy_to_additional_emails'])
            {
                $email_list .= ",".$company['additional_company_emails'];
            }

            $whitelabelinfo = $this->ci->session->userdata('white_label_information');

            $from_email = isset($whitelabelinfo['do_not_reply_email']) && $whitelabelinfo['do_not_reply_email'] ? $whitelabelinfo['do_not_reply_email'] : (isset($company['email']) && $company['email'] ? $company['email'] : 'donotreply@minical.io');

            $this->ci->email->from($from_email, $company['name']);
            $this->ci->email->to($email_list);
            $this->ci->email->reply_to($company['email'], $company['name']);
            $this->ci->email->subject("Invoice Group ".$group_id." from ".$company['name']);
            $this->ci->email->message($content);

            $this->ci->email->send();
            //echo $this->ci->email->print_debugger();
            echo "sent email to ".$email_list;

            // Log the email sent
            $log_data['date_time'] = gmdate('Y-m-d H:i:s');
            $log_data['selling_date'] = $this->ci->session->userdata('current_selling_date');
            $log_data['user_id'] = $this->ci->session->userdata('user_id');
            $log_data['log_type'] = INVOICE_LOG;
            $log_data['booking_id'] = $booking_id;
            $log_data['log'] = "Invoice sent via e-mail to ".$customer_name;
            $this->ci->load->model('Booking_log_model');

            $this->ci->Booking_log_model->insert_log($log_data);

            $this->reset_language($company['default_language']);
        }
    }

    function send_booking_confirmation_email($booking_id)
    {
        $this->ci->load->helper('date_format_helper');
        $booking_data = $this->ci->Booking_model->get_booking_detail($booking_id);
        $company_id = $this->ci->Booking_model->get_company_id($booking_id);
        $company = $this->ci->Company_model->get_company($company_id);

        // Allow send booking confirmation emails only for paying customers
        if(
            isset($company['subscription_state']) && 
            $company['subscription_state'] != 'active'
        ) {

            $customer_details = $this->ci->Customer_model->get_customer_info($booking_data['booking_customer_id']);

            return array(
                "success" => false,
                "message" => "Sending emails is allowed for paying accounts only",
                "customer_email" => $customer_details['email']
            );
        }


        $this->set_language($company['default_language']);

        $booking_room_history_data = $this->ci->Booking_room_history_model->get_booking_detail($booking_id);
        $room_data = $this->ci->Room_model->get_room($booking_room_history_data['room_id']);
        $customer_data['staying_customers'] = $this->ci->Booking_model->get_booking_staying_customers($booking_id, $company_id);

        $number_of_nights = (strtotime($booking_room_history_data['check_out_date']) - strtotime($booking_room_history_data['check_in_date']))/(60*60*24);

        $check_in_date = date('Y-m-d', strtotime($booking_room_history_data['check_in_date']));
        $check_out_date = date('Y-m-d', strtotime($booking_room_history_data['check_out_date']));
        $rate = $rate_with_taxes = $booking_data['rate'];
        $total_charges = $total_charges_with_taxes = 0;

        $charge_type_id = null;
        $rate_plan = array();

        if ($booking_data['use_rate_plan'] == '1')
        {
            $this->ci->load->library('Rate');
            $rate_array = $this->ci->rate->get_rate_array(
                $booking_data['rate_plan_id'],
                date('Y-m-d', strtotime($booking_room_history_data['check_in_date'])),
                date('Y-m-d', strtotime($booking_room_history_data['check_out_date'])),
                $booking_data['adult_count'],
                $booking_data['children_count']
            );

            $rate_plan   = $this->ci->Rate_plan_model->get_rate_plan($booking_data['rate_plan_id']);

            $tax_rates = $this->ci->Charge_type_model->get_taxes($rate_plan['charge_type_id']);

            $charge_type_id = $rate_plan['charge_type_id'];

            foreach ($rate_array as $index => $rate)
            {
                $tax_total = 0;
                if($tax_rates && count($tax_rates) > 0)
                {
                    foreach($tax_rates as $tax){
                        if($tax['is_tax_inclusive'] == 0){
                            $tax_total += (($tax['is_percentage'] == '1') ? ($rate_array[$index]['rate'] * $tax['tax_rate'] / 100) : $tax['tax_rate']);
                        }
                    }
                }
                $total_charges += $rate_array[$index]['rate'];
                $total_charges_with_taxes += $rate_array[$index]['rate'] + $tax_total;
            }
            $rate = $total_charges;
            $rate_with_taxes = $total_charges_with_taxes;
        }
        else
        {
            $charge_type_id = $booking_data['charge_type_id'];

            $tax_rates = $this->ci->Charge_type_model->get_taxes($booking_data['charge_type_id'], $rate);

            if($booking_data['pay_period'] == ONE_TIME)
            {
                $rate = $booking_data['rate'];
                $tax_total = 0;
                if($tax_rates && count($tax_rates) > 0)
                {

                    foreach($tax_rates as $tax){
                        if($tax['is_tax_inclusive'] == 0){
                            $tax_total += (($tax['is_percentage'] == '1') ? ($rate * $tax['tax_rate'] / 100) : $tax['tax_rate']);
                        }
                    }
                }
                $total_charges += $rate;
                $total_charges_with_taxes += $rate + $tax_total;

                $rate = $total_charges;
                $rate_with_taxes = $total_charges_with_taxes;
            }
            else
            {

                $days = 1;
                $date_increment = "+1 day";
                $date_decrement = "-1 day";
                $description = "Daily Room Charge";

                if($booking_data['pay_period'] == WEEKLY)
                {
                    $days = 7;
                    $date_increment = "+7 days";
                    $date_decrement = "-7 days";
                    $description = "Weekly Room Charge";
                }
                if($booking_data['pay_period'] == MONTHLY)
                {
                    $days = 30;
                    $date_increment = "+1 month";
                    $date_decrement = "-1 month";
                }

                for ($charge_start_date = $check_in_date;
                     $charge_start_date < $check_out_date && Date("Y-m-d", strtotime($date_increment, strtotime($charge_start_date))) <= $check_out_date;
                     $charge_start_date = Date("Y-m-d", strtotime($date_increment, strtotime($charge_start_date)))
                ) {
                    $tax_total = 0;
                    if($tax_rates && count($tax_rates) > 0)
                    {
                        foreach($tax_rates as $tax){
                            if($tax['is_tax_inclusive'] == 0){
                                $tax_total += (($tax['is_percentage'] == '1') ? ($rate * $tax['tax_rate'] / 100) : $tax['tax_rate']);
                            }
                        }
                    }
                    $total_charges += $rate;
                    $total_charges_with_taxes += $rate + $tax_total;
                }

                if($charge_start_date < $check_out_date)
                {
                    $daily_rate = round(($rate / $days), 2, PHP_ROUND_HALF_UP);
                    for ($date = $charge_start_date; $date < $check_out_date; $date = Date("Y-m-d", strtotime("+1 day", strtotime($date))) )
                    {
                        $tax_total = 0;
                        if($tax_rates && count($tax_rates) > 0)
                        {
                            foreach($tax_rates as $tax){
                                if($tax['is_tax_inclusive'] == 0){
                                    $tax_total += ($daily_rate * $tax['tax_rate'] / 100);
                                }
                            }
                        }
                        $total_charges += $daily_rate;
                        $total_charges_with_taxes += $daily_rate + $tax_total;
                    }
                }
                $rate = $total_charges;
                $rate_with_taxes = $total_charges_with_taxes;
            }
        }

        $customer_info = $this->ci->Customer_model->get_customer_info($booking_data['booking_customer_id']);

        if (!$customer_info)
        {
            return array(
                "success" => false,
                "message" => "Customer not found in the Booking"
            );
        }

        $room_type = $this->ci->Room_type_model->get_room_type($room_data['room_type_id']);
        $logo_images = $this->ci->Image_model->get_images($company['logo_image_group_id']);


        $booking_hash = $booking_modify_link = "";
        if($company['customer_modify_booking'])
        {
            $booking_hash = $booking_data['invoice_hash'];
            $booking_modify_link = base_url() . "booking/show_booking_information/".$booking_hash;
        }

        $booking_notes = "";
        if($company['send_booking_notes'])
        {
            $booking_notes = $booking_data['booking_notes'];
        }

        $room_instructions = "";
        if(isset($room_data['instructions']) && $room_data['instructions'])
        {
            $room_instructions = $room_data['instructions'];
        }

        $check_in_date = $this->ci->enable_hourly_booking ? get_local_formatted_date($booking_room_history_data['check_in_date']).' '.date('h:i A', strtotime($booking_room_history_data['check_in_date'])) : get_local_formatted_date($booking_room_history_data['check_in_date']);

        $check_out_date = $this->ci->enable_hourly_booking ? get_local_formatted_date($booking_room_history_data['check_out_date']).' '.date('h:i A', strtotime($booking_room_history_data['check_out_date'])) : get_local_formatted_date($booking_room_history_data['check_out_date']);

        $booking_types = Array(UNCONFIRMED_RESERVATION, RESERVATION, INHOUSE, CHECKOUT, OUT_OF_ORDER);
        $booking_type = "";

        switch($booking_data['state']) {
            case UNCONFIRMED_RESERVATION:
                $booking_type = l('Unconfirmed Reservation', true);
                break;
            case RESERVATION:
                $booking_type = l('Reservation', true);
                break;
            case INHOUSE:
                $booking_type = l('Checked-In', true);
                break;
            case CHECKOUT:
                $booking_type = l('Checked-Out', true);
                break;
            case CANCELLED:
                $booking_type = l('Cancelled', true);
                break;
            case OUT_OF_ORDER:
                $booking_type = l('Out of order');
                break;
        }

        $common_booking_sources = json_decode(COMMON_BOOKING_SOURCES, true);
        $coomon_sources_setting = $this->ci->Booking_source_model->get_common_booking_sources_settings($company_id);
        $sort_order = 0;
        foreach($common_booking_sources as $id => $name)
        {
            if(!(isset($coomon_sources_setting[$id]) && $coomon_sources_setting[$id]['is_hidden'] == 1))
            {
                $source_data[] = array(
                    'id' => $id,
                    'name' => $name,
                    'sort_order' => isset($coomon_sources_setting[$id]) ? $coomon_sources_setting[$id]['sort_order'] : $sort_order
                );
            }
            $sort_order++;
        }

        $booking_sources = $this->ci->Booking_source_model->get_booking_source($company_id);
        if (!empty($booking_sources)) {
            foreach ($booking_sources as $booking_source) {
                if($booking_source['is_hidden'] != 1)
                {
                    $source_data[] = array(
                        'id' => $booking_source['id'],
                        'name' => $booking_source['name'],
                        'sort_order' => $booking_source['sort_order']
                    );
                }
            }
        }
        usort($source_data, function($a, $b) {
            return $a['sort_order'] - $b['sort_order'];
        });

        $booking_sources = $source_data;

        $booking_source = '';

        if($booking_sources){
            foreach ($booking_sources as $key => $value) {
                if($value['id'] == $booking_data['source'])
                {
                    $booking_source = $value['name'];
                    break;
                }
            }
        }         
        $extras = $this->ci->Booking_extra_model->get_booking_extras($booking_id);
        $extra_tax_rates = array();

        if($extras && count($extras) > 0){
            foreach ($extras as $key => $extra) {
                $extra_tax_rates[$extra['extra_id']] = $this->ci->Tax_model->get_tax_rates_by_charge_type_id($extra['charge_type_id'], $company_id, $extra['rate']);
            }
        }
        
        $default_currency       = $this->ci->Currency_model->get_default_currency($company_id);
        $currency_symbol = isset($default_currency['currency_code']) ? $default_currency['currency_code'] : null;

        //Send confirmation email
        $email_data = array (
            'extras' => $extras,
            'extra_tax_rates' => $extra_tax_rates,
            'selling_date' => $company['selling_date'],
            'booking_id' => $booking_id,
            'currency_symbol' => $currency_symbol,
            'customer_name' => $customer_info['customer_name'],

            'customer_address' => $customer_info['address'],
            'customer_city' => $customer_info['city'],
            'customer_region' => $customer_info['region'],
            'customer_country' => $customer_info['country'],
            'customer_postal_code' => $customer_info['postal_code'],

            'customer_phone' => $customer_info['phone'],
            'customer_email' => $customer_info['email'],

            'check_in_date' => $check_in_date,
            'check_out_date' => $check_out_date,

            'room_type' => $room_type['name'],

            'average_daily_rate' => $booking_data['rate'],
            'rate' => $rate,
            'rate_with_taxes' => $rate_with_taxes,
            'charge_type_id' => $charge_type_id,

            'company_name' => $company['name'],

            'company_address' => $company['address'],

            'company_city' => $company['city'],
            'company_region' => $company['region'],
            'company_country' => $company['country'],
            'company_postal_code' => $company['postal_code'],
            'default_room_singular' => $company['default_room_singular'],
            'default_room_plural' => $company['default_room_plural'],
            'default_room_type' => $company['default_room_type'],
            'company_phone' => $company['phone'],
            'company_email' => $company['email'],
            'company_website' => $company['website'],
            'company_fax' => $company['fax'],
            'company_room' => $company['default_room_plural'],
            'reservation_policies' => $company['reservation_policies'],
            'date_format' => $company['date_format'],
            'company_room' => $company['default_room_plural'],
            'customer_modify_booking' => $company['customer_modify_booking'],
            'booking_modify_link' => $booking_modify_link,
            'room_instructions' => $room_instructions,
            'adult_count' => $booking_data['adult_count'],
            'children_count' => $booking_data['children_count'],
            'logo_images' => $logo_images,
            'company_id' => $company_id,
            'booking_type' => $booking_type,
            'amount_due' => $booking_data['balance'],
            'rate_plan_detail' => $rate_plan,
            'booking_source' => $booking_source,
            'booking_notes' => $booking_notes
        );

        $email_data['confirmation_email_header'] = '';
        if($this->ci->booking_confirmation_email){
            $email_data['confirmation_email_header'] = $company['booking_confirmation_email_header'];
        }

        if ($email_data['customer_email'] == null || strlen($email_data['customer_email']) <= 1) {
            return array(
                "success" => false,
                "message" => "ERROR: Customer Making Reservation does not have email entered"
            );
        }

        $email_list = $email_data['customer_email'];
        if(isset($company['send_copy_to_additional_emails']) && $company['send_copy_to_additional_emails'])
        {
            $email_list .= ",".$company['additional_company_emails'];
        }

        $whitelabelinfo = $this->ci->session->userdata('white_label_information');

        $from_email = isset($whitelabelinfo['do_not_reply_email']) && $whitelabelinfo['do_not_reply_email'] ? $whitelabelinfo['do_not_reply_email'] : (isset($company['email']) && $company['email'] ? $company['email'] : 'donotreply@minical.io');

        $email_from = isset($this->ci->avoid_dmarc_blocking) && $this->ci->avoid_dmarc_blocking ? $from_email : $company['email'];

        $this->ci->email->from($email_from, $company['name']);
        $this->ci->email->to($email_list);
        $this->ci->email->reply_to($email_data['company_email']);

        $this->ci->email->subject($email_data['company_name'] . ' - Booking Confirmation: ' . $email_data['booking_id']);
        $this->ci->email->message($this->ci->load->view('email/new_booking_confirm-html', $email_data, true));

        $this->ci->email->send();

        $this->reset_language($company['default_language']);

        if(isset($_GET['dev_mode'])) {
            echo $this->ci->email->print_debugger();
        }

        return array(
            "success" => true,
            "message" => "Email successfully sent to ".$email_list,
            "customer_email" => $email_data['customer_email']
        );
    }

    function send_group_booking_confirmation_email($booking_id, $group_id)
    {
        $booked_rooms = array();
        if($group_id != NULL)
        {
            $booking_ids = $this->ci->Booking_linked_group_model->get_group_booking_ids($group_id);
            //print_r($booking_ids);
            if (!empty($booking_ids)) {
                foreach ($booking_ids as $group_booking_id) {
                    $group_booking_id = $group_booking_id['booking_id'];
                    $booking = $this->ci->Booking_model->get_booking($group_booking_id, false);
                    $customer_name = $check_in_date = $check_out_date = '';

                    if ($booking['is_deleted'] != 1) {
                        if ($booking['booking_customer_id']) {
                            $paying_customer = $this->ci->Customer_model->get_customer($booking['booked_by']);
                            $customer_detail = $this->ci->Customer_model->get_customer($booking['booking_customer_id']);
                            $customer_name = isset($customer_detail['customer_name']) ? $customer_detail['customer_name'] : null;
                        }

                        $booking_block = $this->ci->Booking_room_history_model->get_booking_detail($group_booking_id);
                        $booking_data = $this->ci->Booking_model->get_booking_detail($group_booking_id);
                        $check_in_date = date('Y-m-d', strtotime($booking_block['check_in_date']));
                        $check_out_date = date('Y-m-d', strtotime($booking_block['check_out_date']));

                        if (isset($booking_block['room_id'])) {
                            $room_info = $this->ci->Room_model->get_room($booking_block['room_id']); //get room name

                            $current_room_name = isset($room_info['room_name']) ? $room_info['room_name'] : null;
                        }
                        if ($booking['state'] == CANCELLED)
                            $room_cancelled = true;
                        else
                            $room_cancelled = false;


                        $number_of_nights = (strtotime($booking_block['check_out_date']) - strtotime($booking_block['check_in_date']))/(60*60*24);
                        $rate = $rate_with_taxes = $booking_data['rate'];
                        $total_charges = $total_charges_with_taxes = 0;

                        if ($booking_data['use_rate_plan'] == '1')
                        {
                            $this->ci->load->library('Rate');
                            $rate_array = $this->ci->rate->get_rate_array(
                                $booking_data['rate_plan_id'],
                                $booking_block['check_in_date'],
                                $booking_block['check_out_date'],
                                $booking_data['adult_count'],
                                $booking_data['children_count']
                            );

                            $rate_plan   = $this->ci->Rate_plan_model->get_rate_plan($booking_data['rate_plan_id']);

                            $tax_rates = $this->ci->Charge_type_model->get_taxes($rate_plan['charge_type_id']);

                            foreach ($rate_array as $index => $rate)
                            {
                                $tax_total = 0;
                                if($tax_rates && count($tax_rates) > 0)
                                {
                                    foreach($tax_rates as $tax){
                                        if($tax['is_tax_inclusive'] == 0){
                                            $tax_total += (($tax['is_percentage'] == '1') ? ($rate_array[$index]['rate'] * $tax['tax_rate'] / 100) : $tax['tax_rate']);
                                        }
                                    }
                                }
                                $total_charges += $rate_array[$index]['rate'];
                                $total_charges_with_taxes += $rate_array[$index]['rate'] + $tax_total;
                            }
                            $rate = $total_charges;
                            $rate_with_taxes = $total_charges_with_taxes;
                        }
                        else
                        {
                            $tax_rates = $this->ci->Charge_type_model->get_taxes($booking_data['charge_type_id']);

                            if($booking_data['pay_period'] == ONE_TIME)
                            {
                                $rate = $booking_data['rate'];
                                $tax_total = 0;
                                if($tax_rates && count($tax_rates) > 0)
                                {
                                    foreach($tax_rates as $tax){
                                        if($tax['is_tax_inclusive'] == 0){
                                            $tax_total += (($tax['is_percentage'] == '1') ? ($rate * $tax['tax_rate'] / 100) : $tax['tax_rate']);
                                        }
                                    }
                                }
                                $total_charges += $rate;
                                $total_charges_with_taxes += $rate + $tax_total;

                                $rate = $total_charges;
                                $rate_with_taxes = $total_charges_with_taxes;
                            }
                            else
                            {

                                $days = 1;
                                $date_increment = "+1 day";
                                $date_decrement = "-1 day";
                                $description = "Daily Room Charge";

                                if($booking_data['pay_period'] == WEEKLY)
                                {
                                    $days = 7;
                                    $date_increment = "+7 days";
                                    $date_decrement = "-7 days";
                                    $description = "Weekly Room Charge";
                                }
                                if($booking_data['pay_period'] == MONTHLY)
                                {
                                    $days = 30;
                                    $date_increment = "+1 month";
                                    $date_decrement = "-1 month";
                                }

                                for ($charge_start_date = $check_in_date;
                                     $charge_start_date < $check_out_date && Date("Y-m-d", strtotime($date_increment, strtotime($charge_start_date))) <= $check_out_date;
                                     $charge_start_date = Date("Y-m-d", strtotime($date_increment, strtotime($charge_start_date)))
                                ) {
                                    $tax_total = 0;
                                    if($tax_rates && count($tax_rates) > 0)
                                    {
                                        foreach($tax_rates as $tax){
                                            if($tax['is_tax_inclusive'] == 0){
                                                $tax_total += (($tax['is_percentage'] == '1') ? ($rate * $tax['tax_rate'] / 100) : $tax['tax_rate']);
                                            }
                                        }
                                    }
                                    $total_charges += $rate;
                                    $total_charges_with_taxes += $rate + $tax_total;
                                }

                                if($charge_start_date < $check_out_date)
                                {
                                    $daily_rate = round(($rate / $days), 2, PHP_ROUND_HALF_UP);
                                    for ($date = $charge_start_date; $date < $check_out_date; $date = Date("Y-m-d", strtotime("+1 day", strtotime($date))) )
                                    {
                                        $tax_total = 0;
                                        if($tax_rates && count($tax_rates) > 0)
                                        {
                                            foreach($tax_rates as $tax){
                                                if($tax['is_tax_inclusive'] == 0){
                                                    $tax_total += ($daily_rate * $tax['tax_rate'] / 100);
                                                }
                                            }
                                        }
                                        $total_charges += $daily_rate;
                                        $total_charges_with_taxes += $daily_rate + $tax_total;
                                    }
                                }
                                $rate = $total_charges;
                                $rate_with_taxes = $total_charges_with_taxes;
                            }
                        }
                        $company_id = $this->ci->Booking_model->get_company_id($group_booking_id);
                        $staying_customers = $this->ci->Booking_model->get_booking_staying_customers($group_booking_id, $company_id);
                        $customer_array = array();
                        foreach($staying_customers as $cust)
                        {
                            array_push($customer_array, $cust['customer_name']);
                        }
                        $booked_rooms[] = array(
                            'room_name' => $current_room_name,
                            'customer_name' => $customer_name,
                            'check_in_date' => $check_in_date,
                            'check_out_date' => $check_out_date,
                            'booking_id' => $group_booking_id,
                            'room_cancelled' => $room_cancelled,
                            'name' => $room_info['name'],
                            'room_id' => $booking_block['room_id'],
                            'rate' => $rate,
                            'rate_with_taxes' => $rate_with_taxes,
                            'staying_customers' => $customer_array,
                            'average_daily_rate' => $booking['rate']
                        );
                    }
                }
            }
        }

        $booking_data = $this->ci->Booking_model->get_booking_detail($booking_id);
        $company_id = $this->ci->Booking_model->get_company_id($booking_id);
        $booking_room_history_data = $this->ci->Booking_room_history_model->get_booking_detail($booking_id);
        $room_data = $this->ci->Room_model->get_room($booking_room_history_data['room_id']);

        $number_of_nights = (strtotime($booking_room_history_data['check_out_date']) - strtotime($booking_room_history_data['check_in_date']))/(60*60*24);

        $customer_info = $this->ci->Customer_model->get_customer_info($booking_data['booked_by']);

        if (!$customer_info)
        {
            return array(
                "success" => false,
                "message" => "Customer not found in the Booking"
            );
        }


        $room_type = $this->ci->Room_type_model->get_room_type($room_data['room_type_id']);
        $company = $this->ci->Company_model->get_company($company_id);

        $this->set_language($company['default_language']);
        // Company logo
        $logo_url = $this->ci->Image_model
            ->get_company_logo_url($company['company_id'], $company['logo_image_group_id']);

        $room_instructions = "";
        if(isset($room_data['instructions']) && $room_data['instructions'])
        {
            $room_instructions = $room_data['instructions'];
        }

        $booking_hash = $booking_modify_link = "";
        if($company['customer_modify_booking'])
        {
            $booking_hash = $booking_data['invoice_hash'];
            $booking_modify_link = base_url() . "booking/show_booking_information/".$booking_hash;
        }

        //Send confirmation email
        $email_data = array (
            'booking_id' => $booking_id,

            'customer_name' => $customer_info['customer_name'],

            'customer_address' => $customer_info['address'],
            'customer_city' => $customer_info['city'],
            'customer_region' => $customer_info['region'],
            'customer_country' => $customer_info['country'],
            'customer_postal_code' => $customer_info['postal_code'],

            'customer_phone' => $customer_info['phone'],
            'customer_email' => $customer_info['email'],

            'check_in_date' => $booking_room_history_data['check_in_date'],
            'check_out_date' => $booking_room_history_data['check_out_date'],

            'room_type' => $room_type['name'],

            'company_name' => $company['name'],

            'company_address' => $company['address'],

            'company_city' => $company['city'],
            'company_region' => $company['region'],
            'company_country' => $company['country'],
            'company_postal_code' => $company['postal_code'],

            'company_phone' => $company['phone'],
            'company_email' => $company['email'],
            'company_website' => $company['website'],
            'company_fax' => $company['fax'],
            'reservation_policies' => $company['reservation_policies'],
            'reservation_info' => $booked_rooms,
            'group_id' => $group_id,
            'company_logo_url' => $logo_url,
            'customer_modify_booking' => $company['customer_modify_booking'],
            'company_room' => $company['default_room_plural'],
            'room_instructions' => $room_instructions,
            'booking_modify_link' => $booking_modify_link
        );

        $email_data['booking_confirmation_email_header'] = '';
        if($this->ci->booking_confirmation_email){
            $email_data['booking_confirmation_email_header'] = $company['booking_confirmation_email_header'];
        }

        if ($email_data['customer_email'] == null || strlen($email_data['customer_email']) <= 1) {
            return array(
                "success" => false,
                "message" => "ERROR: Customer Making Reservation does not have email entered"
            );
        }

        $email_list = $email_data['customer_email'];
        if(isset($company['send_copy_to_additional_emails']) && $company['send_copy_to_additional_emails'])
        {
            $email_list .= ",".$company['additional_company_emails'];
        }

        $whitelabelinfo = $this->ci->session->userdata('white_label_information');

        $from_email = isset($whitelabelinfo['do_not_reply_email']) && $whitelabelinfo['do_not_reply_email'] ? $whitelabelinfo['do_not_reply_email'] : (isset($company['email']) && $company['email'] ? $company['email'] : 'donotreply@minical.io');

        $this->ci->email->from($from_email, $company['name']);
        $this->ci->email->to($email_list);
        $this->ci->email->reply_to($email_data['company_email']);

        $this->ci->email->subject($email_data['company_name'] . ' - Booking Confirmation: ' . $email_data['booking_id']);
        $this->ci->email->message($this->ci->load->view('email/booking_confirmation-html', $email_data, true));

        $this->ci->email->send();

        $this->reset_language($company['default_language']);
        //echo $this->ci->email->print_debugger();
        return array(
            "success" => true,
            "message" => "Email successfully sent to ".$email_list,
            "customer_email" => $email_data['customer_email']
        );
    }

    function send_multiple_booking_confirmation_email($booking_ids, $group_id)
    {
        if (!empty($booking_ids)) {
            foreach ($booking_ids as $booking_id) {
                //$booking_id = $booking_id['booking_id'];
                $booking = $this->ci->Booking_model->get_booking($booking_id);
                $customer_name = $check_in_date = $check_out_date = '';

                if ($booking['is_deleted'] != 1) {
                    if ($booking['booking_customer_id']) {
                        $paying_customer = $this->ci->Customer_model->get_customer($booking['booking_customer_id']);
                        $customer_name = isset($paying_customer['customer_name']) ? $paying_customer['customer_name'] : null;
                    }

                    $staying_customers = $this->ci->Customer_model->get_staying_customers($booking_id);
                    $booking_block = $this->ci->Booking_room_history_model->get_booking_detail($booking_id);
                    $check_in_date = $booking_block['check_in_date'];
                    $check_out_date = $booking_block['check_out_date'];
                    $customer_array = array();
                    foreach($staying_customers as $cust)
                    {
                        array_push($customer_array, $cust['customer_name']);
                    }

                    if (isset($booking_block['room_id'])) {
                        $room_info = $this->ci->Room_model->get_room($booking_block['room_id']); //get room name

                        $current_room_name = isset($room_info['room_name']) ? $room_info['room_name'] : null;
                    }
                    if ($booking['state'] == CANCELLED)
                        $room_cancelled = true;
                    else
                        $room_cancelled = false;

                    $booked_rooms[] = array(
                        'room_name' => $current_room_name,
                        'customer_name' => $customer_name,
                        'check_in_date' => $check_in_date,
                        'check_out_date' => $check_out_date,
                        'booking_id' => $booking_id,
                        'room_cancelled' => $room_cancelled,
                        'name' => $room_info['name'],
                        'room_id' => $booking_block['room_id'],
                        'staying_customers' => $customer_array
                    );
                }
            }
        }

        $booking_data = $this->ci->Booking_model->get_booking_detail($booking_id);
        $company_id = $this->ci->Booking_model->get_company_id($booking_id);
        $company = $this->ci->Company_model->get_company($company_id);

        $this->set_language($company['default_language']);

        $booking_room_history_data = $this->ci->Booking_room_history_model->get_booking_detail($booking_id);
        $room_data = $this->ci->Room_model->get_room($booking_room_history_data['room_id']);
        $customer_data['staying_customers'] = $this->ci->Booking_model->get_booking_staying_customers($booking_id, $company_id);

        $number_of_nights = (strtotime($booking_room_history_data['check_out_date']) - strtotime($booking_room_history_data['check_in_date']))/(60*60*24);

        if ($booking_data['use_rate_plan'] == '1')
        {
            $this->ci->load->library('Rate');
            $rate_array = $this->ci->rate->get_rate_array(
                $booking_data['rate_plan_id'],
                $booking_room_history_data['check_in_date'],
                $booking_room_history_data['check_out_date'],
                $booking_data['adult_count'],
                $booking_data['children_count']
            );
            $average_daily_rate = $this->ci->rate->get_average_daily_rate($rate_array);
            $rate = $average_daily_rate * $number_of_nights;
        }
        else
        {
            $rate = $booking_data['rate'] * $number_of_nights;
        }
        
        $customer_info = $this->ci->Customer_model->get_customer_info($booking_data['booking_customer_id']);

        if (!$customer_info)
        {
            return array(
                "success" => false,
                "message" => "Customer not found in the Booking"
            );
        }


        $room_type = $this->ci->Room_type_model->get_room_type($room_data['room_type_id']);

        //Send confirmation email
        $email_data = array (
            'booking_id' => $booking_id,

            'customer_name' => $customer_info['customer_name'],

            'customer_address' => $customer_info['address'],
            'customer_city' => $customer_info['city'],
            'customer_region' => $customer_info['region'],
            'customer_country' => $customer_info['country'],
            'customer_postal_code' => $customer_info['postal_code'],

            'customer_phone' => $customer_info['phone'],
            'customer_email' => $customer_info['email'],

            'check_in_date' => $booking_room_history_data['check_in_date'],
            'check_out_date' => $booking_room_history_data['check_out_date'],

            'room_type' => $room_type['name'],

            'rate' => $rate,

            'company_name' => $company['name'],

            'company_address' => $company['address'],

            'company_city' => $company['city'],
            'company_region' => $company['region'],
            'company_country' => $company['country'],
            'company_postal_code' => $company['postal_code'],

            'company_phone' => $company['phone'],
            'company_email' => $company['email'],
            'company_website' => $company['website'],
            'company_fax' => $company['fax'],
            'reservation_policies' => $company['reservation_policies'],
            'reservation_info' => $booked_rooms,
            'group_id' => $group_id
        );

        $email_data['booking_confirmation_email_header'] = '';
        if($this->ci->booking_confirmation_email){
            $email_data['booking_confirmation_email_header'] = $company['booking_confirmation_email_header'];
        }

        if ($email_data['customer_email'] == null || strlen($email_data['customer_email']) <= 1) {
            return array(
                "success" => false,
                "message" => "ERROR: Customer Making Reservation does not have email entered"
            );
        }

        $email_list = $email_data['customer_email'];
        if(isset($company['send_copy_to_additional_emails']) && $company['send_copy_to_additional_emails'])
        {
            $email_list .= ",".$company['additional_company_emails'];
        }

        $whitelabelinfo = $this->ci->session->userdata('white_label_information');

        $from_email = isset($whitelabelinfo['do_not_reply_email']) && $whitelabelinfo['do_not_reply_email'] ? $whitelabelinfo['do_not_reply_email'] : (isset($company['email']) && $company['email'] ? $company['email'] : 'donotreply@minical.io');

        $this->ci->email->from($from_email, $company['name']);
        $this->ci->email->to($email_list);
        $this->ci->email->reply_to($email_data['company_email']);

        $this->ci->email->subject($email_data['company_name'] . ' - Booking Confirmation: ' . $email_data['booking_id']);
        $this->ci->email->message($this->ci->load->view('email/booking_confirmation-html', $email_data, true));

        $this->ci->email->send();

        $this->reset_language($company['default_language']);

        return array(
            "success" => true,
            "message" => "Email successfully sent to ".$email_list,
            "customer_email" => $email_data['customer_email']
        );
    }

    // notify the hotel owner that new booking has been made
    function send_booking_alert_email($booking_id)
    {
        $booking_data = $this->ci->Booking_model->get_booking_detail($booking_id);
        $company_id = $this->ci->Booking_model->get_company_id($booking_id);
        $company = $this->ci->Company_model->get_company($company_id);

        $this->set_language($company['default_language']);

        $booking_room_history_data = $this->ci->Booking_room_history_model->get_booking_detail($booking_id);
        $room_data = $this->ci->Room_model->get_room($booking_room_history_data['room_id']);
        $customer_data['staying_customers'] = $this->ci->Booking_model->get_booking_staying_customers($booking_id, $company_id);

        $number_of_nights = (strtotime($booking_room_history_data['check_out_date']) - strtotime($booking_room_history_data['check_in_date']))/(60*60*24);

        $check_in_date = date('Y-m-d', strtotime($booking_room_history_data['check_in_date']));
        $check_out_date = date('Y-m-d', strtotime($booking_room_history_data['check_out_date']));
        $rate = $rate_with_taxes = $booking_data['rate'];
        $total_charges = $total_charges_with_taxes = 0;

        $charge_type_id = null;
        $rate_plan = array();

        if ($booking_data['use_rate_plan'] == '1')
        {
            $this->ci->load->library('Rate');
            $rate_array = $this->ci->rate->get_rate_array(
                $booking_data['rate_plan_id'],
                date('Y-m-d', strtotime($booking_room_history_data['check_in_date'])),
                date('Y-m-d', strtotime($booking_room_history_data['check_out_date'])),
                $booking_data['adult_count'],
                $booking_data['children_count']
            );

            $rate_plan   = $this->ci->Rate_plan_model->get_rate_plan($booking_data['rate_plan_id']);

            $tax_rates = $this->ci->Charge_type_model->get_taxes($rate_plan['charge_type_id']);

            $charge_type_id = $rate_plan['charge_type_id'];

            foreach ($rate_array as $index => $rate)
            {
                $tax_total = 0;
                if($tax_rates && count($tax_rates) > 0)
                {
                    foreach($tax_rates as $tax){
                        if($tax['is_tax_inclusive'] == 0){
                            $tax_total += (($tax['is_percentage'] == '1') ? ($rate_array[$index]['rate'] * $tax['tax_rate'] / 100) : $tax['tax_rate']);
                        }
                    }
                }
                $total_charges += $rate_array[$index]['rate'];
                $total_charges_with_taxes += $rate_array[$index]['rate'] + $tax_total;
            }
            $rate = $total_charges;
            $rate_with_taxes = $total_charges_with_taxes;
        }
        else
        {
            $charge_type_id = $booking_data['charge_type_id'];

            $tax_rates = $this->ci->Charge_type_model->get_taxes($booking_data['charge_type_id'], $rate);

            if($booking_data['pay_period'] == ONE_TIME)
            {
                $rate = $booking_data['rate'];
                $tax_total = 0;
                if($tax_rates && count($tax_rates) > 0)
                {

                    foreach($tax_rates as $tax){
                        if($tax['is_tax_inclusive'] == 0){
                            $tax_total += (($tax['is_percentage'] == '1') ? ($rate * $tax['tax_rate'] / 100) : $tax['tax_rate']);
                        }
                    }
                }
                $total_charges += $rate;
                $total_charges_with_taxes += $rate + $tax_total;

                $rate = $total_charges;
                $rate_with_taxes = $total_charges_with_taxes;
            }
            else
            {

                $days = 1;
                $date_increment = "+1 day";
                $date_decrement = "-1 day";
                $description = "Daily Room Charge";

                if($booking_data['pay_period'] == WEEKLY)
                {
                    $days = 7;
                    $date_increment = "+7 days";
                    $date_decrement = "-7 days";
                    $description = "Weekly Room Charge";
                }
                if($booking_data['pay_period'] == MONTHLY)
                {
                    $days = 30;
                    $date_increment = "+1 month";
                    $date_decrement = "-1 month";
                }

                for ($charge_start_date = $check_in_date;
                     $charge_start_date < $check_out_date && Date("Y-m-d", strtotime($date_increment, strtotime($charge_start_date))) <= $check_out_date;
                     $charge_start_date = Date("Y-m-d", strtotime($date_increment, strtotime($charge_start_date)))
                ) {
                    $tax_total = 0;
                    if($tax_rates && count($tax_rates) > 0)
                    {
                        foreach($tax_rates as $tax){
                            if($tax['is_tax_inclusive'] == 0){
                                $tax_total += (($tax['is_percentage'] == '1') ? ($rate * $tax['tax_rate'] / 100) : $tax['tax_rate']);
                            }
                        }
                    }
                    $total_charges += $rate;
                    $total_charges_with_taxes += $rate + $tax_total;
                }

                if($charge_start_date < $check_out_date)
                {
                    $daily_rate = round(($rate / $days), 2, PHP_ROUND_HALF_UP);
                    for ($date = $charge_start_date; $date < $check_out_date; $date = Date("Y-m-d", strtotime("+1 day", strtotime($date))) )
                    {
                        $tax_total = 0;
                        if($tax_rates && count($tax_rates) > 0)
                        {
                            foreach($tax_rates as $tax){
                                if($tax['is_tax_inclusive'] == 0){
                                    $tax_total += ($daily_rate * $tax['tax_rate'] / 100);
                                }
                            }
                        }
                        $total_charges += $daily_rate;
                        $total_charges_with_taxes += $daily_rate + $tax_total;
                    }
                }
                $rate = $total_charges;
                $rate_with_taxes = $total_charges_with_taxes;
            }
        }

        $customer_info = $this->ci->Customer_model->get_customer_info($booking_data['booking_customer_id']);

        $room_type = $this->ci->Room_type_model->get_room_type($room_data['room_type_id']);
        $logo_images = $this->ci->Image_model->get_images($company['logo_image_group_id']);

        $booking_hash = $booking_modify_link = "";
        if($company['customer_modify_booking'])
        {
            $booking_hash = $booking_data['invoice_hash'];
            $booking_modify_link = base_url() . "booking/show_booking_information/".$booking_hash;
        }

        $room_instructions = "";
        if(isset($room_data['instructions']) && $room_data['instructions'])
        {
            $room_instructions = $room_data['instructions'];
        }

        $booking_types = Array(UNCONFIRMED_RESERVATION, RESERVATION, INHOUSE, CHECKOUT, OUT_OF_ORDER);
        $booking_type = "";

        switch($booking_data['state']) {
            case UNCONFIRMED_RESERVATION:
                $booking_type = l('Unconfirmed Reservation', true);
                break;
            case RESERVATION:
                $booking_type = l('Reservation', true);
                break;
            case INHOUSE:
                $booking_type = l('Checked-In', true);
                break;
            case CHECKOUT:
                $booking_type = l('Checked-Out', true);
                break;
            case CANCELLED:
                $booking_type = l('Cancelled', true);
                break;
            case OUT_OF_ORDER:
                $booking_type = l('Out of order', true);
                break;
        }

        $extras = $this->ci->Booking_extra_model->get_booking_extras($booking_id);

        $default_currency       = $this->ci->Currency_model->get_default_currency($company_id);
        $currency_symbol = isset($default_currency['currency_code']) ? $default_currency['currency_code'] : null;

        //Send confirmation email
        $email_data = array (
            'extras' => $extras,
            'booking_id' => $booking_id,

            'customer_name' => $customer_info['customer_name'],
            'currency_symbol' => $currency_symbol,
            'customer_address' => $customer_info['address'],
            'customer_city' => $customer_info['city'],
            'customer_region' => $customer_info['region'],
            'customer_country' => $customer_info['country'],
            'customer_postal_code' => $customer_info['postal_code'],

            'customer_phone' => $customer_info['phone'],
            'customer_email' => $customer_info['email'],

            'check_in_date' => $check_in_date,
            'check_out_date' => $check_out_date,

            'room_type' => $room_type['name'],

            'average_daily_rate' => $booking_data['rate'],
            'rate' => $rate,
            'rate_with_taxes' => $rate_with_taxes,
            'charge_type_id' => $charge_type_id,

            'company_name' => $company['name'],

            'company_address' => $company['address'],

            'default_room_singular' => $company['default_room_singular'],
            'default_room_plural' => $company['default_room_plural'],
            'default_room_type' => $company['default_room_type'],

            'company_city' => $company['city'],
            'company_region' => $company['region'],
            'company_country' => $company['country'],
            'company_postal_code' => $company['postal_code'],

            'company_phone' => $company['phone'],
            'company_email' => $company['email'],
            'company_website' => $company['website'],
            'company_fax' => $company['fax'],
            'company_room' => $company['default_room_plural'],
            'reservation_policies' => $company['reservation_policies'],
            'date_format' => $company['date_format'],
            'company_room' => $company['default_room_plural'],
            'customer_modify_booking' => $company['customer_modify_booking'],
            'booking_modify_link' => $booking_modify_link,
            'room_instructions' => $room_instructions,
            'adult_count' => $booking_data['adult_count'],
            'children_count' => $booking_data['children_count'],
            'logo_images' => $logo_images,
            'company_id' => $company_id,
            'booking_type' => $booking_type,
            'amount_due' => $booking_data['balance'],
            'rate_plan_detail' => $rate_plan,
        );

        $email_data['confirmation_email_header'] = '';
        if($this->ci->booking_confirmation_email){
            $email_data['confirmation_email_header'] = $company['booking_confirmation_email_header'];
        }

        if ($email_data['customer_email'] == null || strlen($email_data['customer_email']) <= 1) {
            echo "ERROR: Customer Making Reservation does not have email entered";
            return;
        }

        $email_list = $company['email'];
        if(isset($company['send_copy_to_additional_emails']) && $company['send_copy_to_additional_emails'])
        {
            $email_list .= ",".$company['additional_company_emails'];
        }
        
        $whitelabelinfo = $this->ci->session->userdata('white_label_information');

        $from_email = isset($whitelabelinfo['do_not_reply_email']) && $whitelabelinfo['do_not_reply_email'] ? $whitelabelinfo['do_not_reply_email'] : 'donotreply@minical.io';

        $email_from = isset($this->ci->avoid_dmarc_blocking) && $this->ci->avoid_dmarc_blocking ? $from_email : $company['email'];

        $this->ci->email->from($email_from);
        $this->ci->email->to($email_list);
        $this->ci->email->reply_to($customer_info['email']);

        $this->ci->email->subject("You got a new booking! - Booking Confirmation: ".$email_data['booking_id']);
        $this->ci->email->message($this->ci->load->view('email/new_booking_confirm-html', $email_data, true));

        $this->ci->email->send();

        $this->reset_language($company['default_language']);

        return "Email successfully sent to ".$email_list;
    }

    function send_booking_cancellation_email($booking_id)
    {
        $booking = $this->ci->Booking_model->get_booking_detail($booking_id);

        if (!$booking['booking_customer_email']) {

            return array(
                "success" => false
            );
        }

        $company_id = $this->ci->Booking_model->get_company_id($booking_id);
        $booking_room_history_data = $this->ci->Booking_room_history_model->get_booking_detail($booking_id);
        $room_data = $this->ci->Room_model->get_room($booking_room_history_data['room_id']);
        $room_type = $this->ci->Room_type_model->get_room_type($room_data['room_type_id']);
        $company = $this->ci->Company_model->get_company($company_id);

        $this->set_language($company['default_language']);

        $invoice_hash = $booking['invoice_hash'];
        $invoice_link = base_url() . "invoice/show_invoice_read_only/".$invoice_hash;


        $content =  l('Please visit the following link to view your invoice', true)." :<a href='".$invoice_link."'>".$invoice_link."</a><br/><br/>";
        $content1 = "<br/><br/>".l('Thank you for your business', true).",<br/><br/>"
            .$company['name']."
                <br/>".$company['email']. "<br/>".$company['phone']."<br/>";


        $config['mailtype'] = 'html';

        $this->ci->email->initialize($config);
        // Company logo
        $logo_url = $this->ci->Image_model
            ->get_company_logo_url($company['company_id'], $company['logo_image_group_id']);

        $email_data = array (
            'booking_id' => $booking_id,
            'customer_name' => $booking['booking_customer_name'],
            'check_in_date' => $booking_room_history_data['check_in_date'],
            'check_out_date' => $booking_room_history_data['check_out_date'],
            'room_type' => $room_type['name'],
            'company_name' => $company['name'],
            'company_email' => $company['email'],
            'content' => $content,
            'content1' => $content1,
            'company_room' => $company['default_room_plural'],
            'company_logo_url' => $logo_url
        );

        $customer_email = $booking['booking_customer_email'];

        $whitelabelinfo = $this->ci->session->userdata('white_label_information');

        $from_email = isset($whitelabelinfo['do_not_reply_email']) && $whitelabelinfo['do_not_reply_email'] ? $whitelabelinfo['do_not_reply_email'] : (isset($company['email']) && $company['email'] ? $company['email'] : 'donotreply@minical.io');

        $email_list = $customer_email;
        if(
            isset($company['email']) && 
            $company['email']
        )
        {
            $email_list .= ",".$company['email'];
        }

        $this->ci->email->from($from_email, $company['name']);
        $this->ci->email->to($email_list);
        $this->ci->email->reply_to($email_data['company_email']);

        $this->ci->email->subject($email_data['company_name'] . ' - Booking Cancellation: ' . $email_data['booking_id']);
        $this->ci->email->message($this->ci->load->view('email/booking_cancellation-html', $email_data, true));

        $this->ci->email->send();

        $this->reset_language($company['default_language']);

        if(isset($_GET['dev_mode'])) {
            echo $this->ci->email->print_debugger();
        }

        return array(
            "success" => true,
            "message" => "Email successfully sent to ".$customer_email,
            "customer_email" => $customer_email
        );
    }

    function send_rating_email($booking_id, $folio_id = null)
    {
        $booking = $this->ci->Booking_model->get_booking_detail($booking_id);
        $company = $this->ci->Company_model->get_company($booking['company_id']);

        $this->set_language($company['default_language']);

        $email = $booking['booking_customer_email'];
        if ($email != "")
        {
            $customer_name = $booking['booking_customer_name'];
            $invoice_hash = $booking['invoice_hash'];
            $review_link = base_url() . "review/?hash=".$invoice_hash;
            // Company logo
            $email_header = $this->ci->Image_model
                ->get_company_logo_url($company['company_id'], $company['logo_image_group_id'], 1);

            $content = "<div style='background-color:#ddd;padding:5%;'>
                            <div style='background-color:#fff; width:auto; margin: 0 auto;padding:2%;'>
                            ".$email_header."
                                <h2 style='font-size:1.5rem; text-align: center; font-weight: normal;'>".l('We care about your experience. Please rate the quality of your stay at', true)." {$company['name']}:</h2> ";
            $content = $content.'
                <table width="100%" border="0" align="center" cellpadding="20" cellspacing="0" style="text-align: center; background-color: #FFFFFF;">
                    <tr>
                        <td>
                            <div class="rating" style="font-size:2.5rem;">
                            <a style="text-decoration:none; color:#FF9529;" href="'.$review_link.'&rating='.base64_encode('1').'" title="1">★</a>
                            <a style="text-decoration:none; color:#FF9529;" href="'.$review_link.'&rating='.base64_encode('2').'" title="2">★</a>
                            <a style="text-decoration:none; color:#FF9529;" href="'.$review_link.'&rating='.base64_encode('3').'" title="3">★</a>
                            <a style="text-decoration:none; color:#FF9529;" href="'.$review_link.'&rating='.base64_encode('4').'" title="4">★</a>
                            <a style="text-decoration:none; color:#FF9529;" href="'.$review_link.'&rating='.base64_encode('5').'" title="5">★</a>
                            </div>
                        </td>
                    </tr>
                </table>';

            $content = $content."<br/><br/>".l('Booking ID', true).": $booking_id <br/><br/>".l('Thank you for your business', true).",<br/><br/>"
                .$company['name']."
                    <br/>".$company['email']. "<br/>".$company['phone']."<br/></div></div>";


            $config['mailtype'] = 'html';

            $this->ci->email->initialize($config);

            $email_list = $email;
            if(isset($company['send_copy_to_additional_emails']) && $company['send_copy_to_additional_emails'])
            {
                //$email_list .= ",".$company['additional_company_emails'];
            }

            $whitelabelinfo = $this->ci->session->userdata('white_label_information');

            $from_email = isset($whitelabelinfo['do_not_reply_email']) && $whitelabelinfo['do_not_reply_email'] ? $whitelabelinfo['do_not_reply_email'] : (isset($company['email']) && $company['email'] ? $company['email'] : 'donotreply@minical.io');

            $this->ci->email->from($from_email, $company['name']);
            $this->ci->email->to($email_list);
            $this->ci->email->reply_to($company['email'], $company['name']);
            $this->ci->email->subject("How was your stay at ".$company['name']."?");
            $this->ci->email->message($content);

            $this->ci->email->send();
            echo "sent email to ".$email_list;

            // Log the email sent
            $log_data['date_time'] = gmdate('Y-m-d H:i:s');
            $log_data['selling_date'] = $this->ci->session->userdata('current_selling_date');
            $log_data['user_id'] = $this->ci->session->userdata('user_id');
            $log_data['log_type'] = INVOICE_LOG;
            $log_data['booking_id'] = $booking_id;
            $log_data['log'] = "Requested feedback via e-mail from ".$customer_name;
            $this->ci->load->model('Booking_log_model');

            $this->ci->Booking_log_model->insert_log($log_data);

            $this->reset_language($company['default_language']);
        }
    }

    function send_email_to_partner($data)
    {
        $company = $this->ci->Company_model->get_company($data['company_id']);

        $this->set_language($company['default_language']);

        $config['mailtype'] = 'html';

        $this->ci->email->initialize($config);

        $customer_email = $data['partner_email'];

        $from_email = $data['email'];

        $this->ci->email->from($from_email, $company['name']);
        $this->ci->email->to($customer_email);
        $this->ci->email->bcc('support@minical.io');

        $this->ci->email->subject($data['company_name'] . ' wants to join ' . $data['partner_name'] . ' partner');
        $this->ci->email->message($this->ci->load->view('email/partner-contact-html', $data, true));

        $this->ci->email->send();

        $this->reset_language($company['default_language']);

        if(isset($_GET['dev_mode'])) {
            echo $this->ci->email->print_debugger();
        }

        return array(
            "success" => true,
            "message" => "Email successfully sent to ".$customer_email,
            "customer_email" => $customer_email
        );
    }

}
