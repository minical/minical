<?php
class Booking extends MY_Controller
{
    function __construct()
    {
        parent::__construct();


        // user is logged in
        $this->load->model('Company_model');
        $this->load->model('Booking_model');
        $this->load->model('Booking_extra_model');
        $this->load->model('Booking_log_model');
        $this->load->model('Invoice_model');
        $this->load->model('Group_model');
        $this->load->model('Booking_linked_group_model');
        $this->load->model('Customer_model');
        $this->load->model('Customer_field_model');
        $this->load->model('Extra_model');
        $this->load->model('Booking_room_history_model');
        $this->load->model('Room_type_model');
        $this->load->model('Room_model');
        $this->load->model('Charge_type_model');
        $this->load->model('Floor_model');
        $this->load->model('Room_location_model');
        $this->load->model('Charge_model');
        $this->load->model('Rate_plan_model');
        $this->load->model('Booking_source_model');
        $this->load->model('Payment_model');
        // Load Translation Model for Language Translation
        $this->load->model('translation_model');
        $this->load->model('User_model');
        $this->load->model('Currency_model');
        $this->load->model('Date_range_model');
        $this->load->model('Date_color_model');
        $this->load->model('Rate_plan_model');
        $this->load->model('Rate_model');
        $this->load->model('Booking_field_model');
        $this->load->model('Option_model');
        //$this->load->controller('company');
        $this->load->library('email');
        $this->load->library('form_validation');
        $this->load->helper('url'); // for redirect
        $this->load->helper('timezone');
        $this->load->helper('my_date_helper');
        $this->load->helper('date_format_helper');


        $this->load->library('pagination');
        $this->load->library('permission');
        $this->load->library('rate');
        $this->load->library('PHPRequests');

        $this->load->helper('timezone');
        // Load Language Translation Helper
        $this->load->helper('language_translation');
        $global_data['menu_on'] = true;
        /*
          $global_data['submenu'] = 'includes/submenu.php';
          $global_data['submenu_parent_url'] = base_url()."booking/";
          $global_data['menu_items'] = Array(
          Array('name' => 'Room View', 'controller_function' => '' ),
          Array('name' => 'Room Type View', 'controller_function' => 'show_room_type_view' ));
         */
        $this->load->vars($global_data);

        $language = $this->session->userdata('language');
        $this->lang->load('booking', $language);

    }

    function index()
    {
        $this->show_booking_main();
    }

    function overview()
    {
        $this->show_booking_main(true);
    }

    function booking_balance()
    {
        $this->load->view('fix_booking_balance');
    }

    function fix_booking_balance($company_id = null, $booking_id = null)
    {
        $this->load->model('Booking_model');
        if($booking_id)
        {
            echo $this->Booking_model->update_booking_balance($booking_id);
        }
        elseif(!$booking_id)
        {
            $bookings = $this->Booking_model->get_latest_bookings($company_id);
            if($bookings)
            {
                foreach($bookings as $booking)
                {
                    echo $booking['booking_id']."<br/>";
                    $this->Booking_model->update_booking_balance($booking['booking_id']);
                }
            }
//            $this->Booking_model->fix_booking_balance($company_id);
        }
        echo l("Done",true);
    }

    /**
     * Displays the main booking page. The page includes calendar and booking list
     * They are loaded seperately in order to reload divs after information updates.
     */
    function show_booking_main($is_overview = false)
    {
        $last_login_date = $this->session->userdata('last_login_date');
        $last_login_company = $this->session->userdata('last_login_company');
        // Set last login date in company.
        if(
            (
                !$last_login_date || !$last_login_company ||
                ($last_login_date && $last_login_date != date('Y-m-d')) ||
                ($last_login_company && $last_login_company != $this->company_id)
            ) &&
            $this->session->userdata('user_role') != 'is_admin'
        )
        {
            $updatedata = array('last_login' => date('Y-m-d'));
            $this->Company_model->update_company($this->company_id, $updatedata);
            $this->session->set_userdata(array('last_login_date' => date('Y-m-d'), 'last_login_company' => $this->company_id));
        }
        $company_data = $this->company_data;

        $data['is_overview_calendar'] = $this->is_overview_calendar;

        if ($is_overview || !$this->enable_new_calendar) {

            $data['is_overview_calendar'] = $is_overview ? true : $data['is_overview_calendar'];

            $data['css_files'] = array(
                base_url() . auto_version('css/booking/booking_main.css'),
                base_url() . auto_version('css/booking/booking_list.css'),
                base_url() . auto_version('css/booking/booking_blocks.css')
            );

            $data['js_files'] = array(
                base_url() . auto_version('js/channel_manager/channel_manager.js'),
                base_url().'js/moment.min.js',
                base_url() . auto_version('js/calendar/_loader.js'),
                base_url() . auto_version('js/calendar.js'),
                base_url() . auto_version('js/booking/bookingModal.js'),
                base_url() . auto_version('js/booking/booking_main.js'),
                base_url() . auto_version('js/booking/printThis.js')
            );

        } else {

            $data['css_files'] = array(
                base_url() . auto_version('css/booking/booking_main.css'),
                base_url() . auto_version('css/booking/booking_list.css'),
                base_url() . auto_version('css/booking/booking_blocks.css'),
                'https://unpkg.com/@fullcalendar/core@4.4.0/main.min.css',
                'https://unpkg.com/@fullcalendar/timeline@4.4.0/main.min.css',
                'https://unpkg.com/@fullcalendar/resource-timeline@4.4.0/main.min.css',
                base_url() . auto_version('js/fullcalendar/main.css'),
            );

            $data['js_files'] = array(
                base_url() . auto_version('js/channel_manager/channel_manager.js'),
                base_url().'js/moment.min.js',
                base_url() . auto_version('js/fullcalendar/lib/core/main.min.js'),
                base_url() . auto_version('js/fullcalendar/lib/timeline/main.min.js'),
                base_url() . auto_version('js/fullcalendar/lib/resource-common/main.min.js'),
                base_url() . auto_version('js/fullcalendar/lib/resource-timeline/main.min.js'),
                base_url() . auto_version('js/fullcalendar/lib/interaction/main.min.js'),
                base_url() . auto_version('js/fullcalendar/init.js'),
                base_url() . auto_version('js/booking/bookingModal.js'),
                base_url() . auto_version('js/booking/booking_main.js'),
                base_url() . auto_version('js/booking/printThis.js')
            );
        }

        $data['room_types'] =  $this->Room_type_model->get_room_types($this->company_id);
        $data['rate_plans'] =  $this->Rate_plan_model->get_rate_plans($this->company_id);

        $data['room_floor'] =  $this->Floor_model->get_floor($this->company_id);

        $data['room_location'] =  $this->Room_location_model->get_room_location($this->company_id);

        $data['company_data'] =  $company_data;

        $data['date_colors'] = array();

        if(check_active_extensions('room_date_manager', $this->company_id)){
            $data['date_colors'] = $this->Date_color_model->get_date_color();
        }

        $order = 'length(sort_order), sort_order, room_name';
        $data['rooms_without_filters'] = $this->Room_model->get_rooms($this->company_id, $order, ''); // sort by the list by room name
        if ($data['rooms_without_filters'] && count($data['rooms_without_filters']) > 0) {
            foreach($data['rooms_without_filters'] as $key => $room)
            {
                unset($data['rooms_without_filters'][$key]['room_type_description']);
            }
        }

        $source_data = array();
        $common_booking_sources = json_decode(COMMON_BOOKING_SOURCES, true);
        $coomon_sources_setting = $this->Booking_source_model->get_common_booking_sources_settings($this->company_id);
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

        $booking_sources = $this->Booking_source_model->get_booking_source($this->company_id);
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
        $data['booking_sources'] = $source_data;

        $whitelabelinfo = $this->session->userdata('white_label_information');

        $data['support_email'] = $whitelabelinfo && isset($whitelabelinfo['support_email']) && $whitelabelinfo['support_email'] ? $whitelabelinfo['support_email'] : 'support@minical.io';

        $data['whitelabel_detail'] = $whitelabelinfo;
        $data['is_show_unassigned_rooms'] = $company_data['force_room_selection'];
        $data['hide_decimal_places'] = isset($company_data["hide_decimal_places"]) ? $company_data["hide_decimal_places"] : "";
        $data['make_guest_field_mandatory'] = isset($company_data["make_guest_field_mandatory"]) ? $company_data["make_guest_field_mandatory"] : "";

        $data['selected_menu'] = 'bookings';
        $data['selected_submenu'] = 'Room View';
        $data['main_content'] = 'booking/booking_main';
        $this->load->view('includes/bootstrapped_template', $data);
    }

    function show_room_type_view()
    {
        $company_data = $this->company_data; //$this->Company_model->get_company($this->company_id);

        $data['css_files'] = array(
            base_url() . auto_version('css/booking/booking_main.css'),
            base_url() . auto_version('js/calendar/main.css'),
            base_url() . auto_version('css/booking/booking_list.css'),
            base_url() . auto_version('css/booking/booking_blocks.css')

        );

        $data['js_files'] = array(
            base_url() . auto_version('js/channel_manager/channel_manager.js'),
            base_url().'js/moment.min.js',
            base_url() . auto_version('js/booking/bookingModal.js'),
            base_url() . auto_version('js/booking/booking_main.js'),
        );

        $data['selected_menu'] = 'bookings';
        $data['selected_submenu'] = 'Room Type View';
        $data['main_content'] = 'booking/room_type_view';
        $this->load->view('includes/bootstrapped_template', $data);
    }

    function show_bookings()
    {
        $this->load->model('Booking_model');

        $company_data = $this->company_data; //$this->Company_model->get_company($this->company_id);

        $data['css_files'] = array(
            base_url() . auto_version('css/booking/booking_main.css'),
            base_url() . auto_version('css/booking/booking_list.css'),
            base_url() . auto_version('css/booking/booking_blocks.css')
        );
        $data['js_files'] = array(
            base_url() . auto_version('js/channel_manager/channel_manager.js'),
            base_url().'js/moment.min.js',
            base_url() . auto_version('js/booking/bookingModal.js'),
            base_url() . auto_version('js/booking/booking_main.js'),
            base_url() . auto_version('js/booking/printThis.js'),

        );

        $filters['state'] = sqli_clean($this->security->xss_clean($this->input->get('state')));
        $filters['search_query'] = sqli_clean($this->security->xss_clean($this->input->get('search_query')));
        $filters['order_by'] = sqli_clean($this->security->xss_clean($this->input->get('order_by')));
        $filters['order'] = sqli_clean($this->security->xss_clean($this->input->get('order')));

        $config['per_page'] = 30;
        $config['uri_segment'] = 3; // uri_segment is used to tell pagination which page we're on. it seems that default is 3.
        $config['base_url'] = base_url() . "booking/show_bookings/";
        $config['first_url'] = base_url(). "booking/show_bookings/?state=".$filters['state']."&order_by=".$filters['order_by']."&order=".$filters['order']."&search_query=".$filters['search_query'];

        $config['suffix'] = '?'.http_build_query($_GET, '', "&");

        $filters['per_page'] = $config['per_page'];
        $filters['offset'] = $this->uri->segment(3);


        $data['bookings'] = $this->Booking_model->get_bookings($filters, null, null, true);
        $config['total_rows'] = $this->Booking_model->get_found_rows();

        $this->pagination->initialize($config);

        $data['menu_on'] = TRUE;
        $data['selected_menu'] = 'bookings';
        $data['main_content'] = 'booking/booking_table';
        $this->load->view('includes/bootstrapped_template', $data + $filters);
    }


    /**
     * finds difference between booking information existing in the database (old)
     * and the booking information gathered from post data (new_data)
     */

    function _generate_logs($new_data, $old_data)
    {
        $new_array = isset($new_data['customers']) && isset($new_data['customers']['staying_customers']) ? $new_data['customers']['staying_customers'] : array();
        $old_array = isset($old_data['customers']) && isset($old_data['customers']['staying_customers']) ?$old_data['customers']['staying_customers'] : array();
        $flag = 2;
        $added_arr = $delete_arr = array();
        foreach($new_array as $value){

            $flag = 2;
            foreach($old_array as $v2){

                if($value['customer_name'] == $v2['customer_name'] && !empty($value['customer_name'])){
                    $flag = 1;

                }

            }
            if($flag != 1)
            {
                $added_arr[] = $value['customer_name'];  // added guest
            }


        }

        foreach($old_array as $value){

            $deleteflag = 2;
            foreach($new_array as $v2){

                if($value['customer_id'] == $v2['customer_id']){
                    $deleteflag = 1;
                }

            }

            if($deleteflag != 1)
            {
                if(in_array($value['customer_name'], $added_arr)){

                }else{
                    $delete_arr[] = $value['customer_name'];  // deleted guest
                }

            }

        }

        // Load existing_data from database
        $fields = array(
            'booking' => Array(
                'state' => 5,
                'charge_type_id' => 6,
                'adult_count' => 7,
                'children_count' => 8,
                'rate' => 9,
                'use_rate_plan' => 10,
                'rate_plan_id' => 11,
                'booking_notes' => 12,
                'color' => 13,
                'is_deleted' => 14,
                'pay_period' => 20,
                'booking_customer_name' => 18,
                'source' => 23
            ),
            'booking_block' => Array(
                'check_in_date' => 15,
                'check_out_date' => 16,
                'room_id' => 17,
            ),
            'customers' => Array(
                //'paying_customer' => 18,
                'staying_customers' => 19
            )
        );
        $new_data = array(
            'booking' => Array(
                'state' => isset($new_data['booking']['state']) ? $new_data['booking']['state'] : null,
                'charge_type_id' => isset($new_data['rooms'][0]['charge_type_id']) ? $new_data['rooms'][0]['charge_type_id'] : null,
                'adult_count' => isset($new_data['booking']['adult_count']) ? $new_data['booking']['adult_count'] : null,
                'children_count' => isset($new_data['booking']['children_count']) ? $new_data['booking']['children_count'] : null,
                'rate' => isset($new_data['rooms'][0]['rate']) ? $new_data['rooms'][0]['rate'] : null,
                'use_rate_plan' => isset($new_data['rooms'][0]['use_rate_plan']) ? $new_data['rooms'][0]['use_rate_plan'] : null,
                'rate_plan_id' => null,
                'booking_notes' => isset($new_data['booking']['booking_notes']) ? $new_data['booking']['booking_notes'] : null,
                'color' => isset($new_data['booking']['color']) ? $new_data['booking']['color'] : null,
                'is_deleted' => null,
                'pay_period' => isset($new_data['rooms'][0]['pay_period']) ? $new_data['rooms'][0]['pay_period'] : null,
                'booking_customer_name' => isset($new_data['customers']['paying_customer']['customer_name']) ? $new_data['customers']['paying_customer']['customer_name'] : null,
                'source'=> isset($new_data['booking']['source']) ? $new_data['booking']['source'] : null
            ),
            'booking_block' => Array(
                'check_in_date' => isset($new_data['rooms'][0]['check_in_date']) ? $new_data['rooms'][0]['check_in_date'] : null,
                'check_out_date' => isset($new_data['rooms'][0]['check_out_date']) ? $new_data['rooms'][0]['check_out_date'] : null,
                'room_id' => (isset($new_data['rooms'][0]['room_id']) && $new_data['rooms'][0]['room_id']) ? $new_data['rooms'][0]['room_id'] : 0,
            ),
            'customers' => Array(
                //'paying_customer' => $new_data['customers']['paying_customer']['customer_name'],
                'staying_customers' => isset($new_data['customers']['staying_customers']) ? $new_data['customers']['staying_customers'] : array()
            )
        );

        $logs = array();

        $date_time = gmdate('Y-m-d H:i:s');

        // Find the differences between existing data and new data.
        foreach ($fields as $category => $sub_fields)
        {
            foreach ($sub_fields as $index => $log_type){
                if (isset($old_data[$category]) && isset($new_data[$category]) && isset($old_data[$category][$index]) && isset($new_data[$category][$index]) && $old_data[$category][$index] != $new_data[$category][$index])
                {
                    if($log_type == '19'){

                        if (!empty($added_arr)) { // guest added
                            $added_guest_string = implode(',', $added_arr);
                            $added_guest = "A guest named " . $added_guest_string ." was added";
                        }
                        if (!empty($delete_arr)) { // guest deleted
                            if((!empty($new_data['booking']['booking_customer_name']) && !empty($old_data['booking']['booking_customer_name'])) && ($new_data['booking']['booking_customer_name'] != $old_data['booking']['booking_customer_name'])){

                            }else{
                                $deleted_guest_string = implode(',', $delete_arr);
                                $deleted_guest = "A guest named " . $deleted_guest_string ." was deleted";
                            }

                        }
                        if(!empty($added_guest) && !empty($deleted_guest)){
                            $guest_log = $added_guest." and ".$deleted_guest;
                        }elseif (!empty($added_guest)) {
                            $guest_log = $added_guest;
                        }elseif (!empty($deleted_guest)) {
                            $guest_log = $deleted_guest;
                        }

                        if(!empty($guest_log)){
                            $log_type = 19;
                            $logs[] = Array(
                                "booking_id" => $old_data['booking']['booking_id'],
                                "date_time" => $date_time,
                                "log_type" => $log_type,
                                "log" => $guest_log,
                                "user_id" => $this->user_id,
                                "selling_date" => $this->selling_date
                            );
                        }
                    }else{

                        $log_data = $new_data[$category][$index];
                        if($log_type == 18){
                            if(!empty($new_data[$category][$index]) && empty($old_data[$category][$index])){
                                $log_data = 'A paying customer named '.$new_data[$category][$index].' was added';
                            }elseif(!empty($new_data[$category][$index]) && !empty($old_data[$category][$index])){
                                $log_data = 'A paying customer named '.$old_data[$category][$index].' was deleted and a paying customer named '.$new_data[$category][$index].' (who was a guest) updated';
                            }else{
                                $log_data = 'A paying customer named '.$old_data[$category][$index].' was deleted';
                            }
                        }
                        if($log_type == 12){
                            if(!empty($new_data[$category][$index]) && empty($old_data[$category][$index])){
                                $log_data = 'Added booking notes is '.$new_data[$category][$index];
                            }elseif(!empty($new_data[$category][$index]) && !empty($old_data[$category][$index])){
                                $log_data = 'Changed booking notes to '.$new_data[$category][$index];
                            }elseif(empty($new_data[$category][$index]) && !empty($old_data[$category][$index])){
                                $log_data = 'Deleted booking notes is '.$old_data[$category][$index];
                            }
                        }

                        if(isset($log_data)){
                            $logs[] = Array(
                                "booking_id" => $old_data['booking']['booking_id'],
                                "date_time" => $date_time,
                                "log_type" => $log_type,
                                "log" => $log_data,
                                "user_id" => $this->user_id,
                                "selling_date" => $this->selling_date
                            );
                        }
                    }
                }
            }
        }

        if (empty($logs))
            return;

        $this->Booking_log_model->insert_logs($logs);
    }

    function get_available_room_types_in_JSON($check_in_date = null, $check_out_date = null, $isAJAX = true)
    {
        if(!$check_in_date && !$check_out_date)
        {
            $check_in_date = $this->input->post('check_in_date');
            $check_out_date =  $this->input->post('check_out_date');
            $isAJAX = $this->input->post('isAJAX');

            $check_in_date = urldecode($check_in_date);
            $check_out_date = urldecode($check_out_date);
        }

        $check_in_date = date('Y-m-d H:i:s', strtotime($check_in_date));
        $check_out_date = date('Y-m-d H:i:s', strtotime($check_out_date));
        //If current selling date is between check-in and check-out date
        //use current selling date as the check in date for checking available rooms
        if ($check_in_date <= $this->selling_date && $this->selling_date <= $check_out_date) {
            $check_in_date = $this->selling_date;
        }
        $company_data = $this->company_data; //$this->Company_model->get_company($this->company_id);
        $force_room_selection = $company_data['force_room_selection'];

        $room_types_array = $this->Room_type_model->get_room_types_and_availabilities(
            $this->company_id,
            $check_in_date,
            $check_out_date
        );
        $available_room_types = $room_types_array['available_room_types'];
        $occupancies = $room_types_array['occupancies'];

        if(!$force_room_selection)
        {
            // unassigned rooms allowed
            $room_types = array();
            $filters = array(
                'start_date' => $check_in_date,
                'end_date' => $check_out_date,
                'unassigned_bookings' => true,
                'state' => 'active'
            );
            $bookings = $this->Booking_model->get_bookings($filters, null, null ,true);
            foreach ($bookings as $booking) {
                if($booking['room_id'] && $booking['check_out_date'] > $check_in_date && $check_out_date > $booking['check_in_date'])
                {
                    $_room_type_id = $booking['r_room_type_id'] ? $booking['r_room_type_id'] : $booking['brh_room_type_id'];
                    if(!isset($room_types[$_room_type_id])) {
                        $room_types[$_room_type_id] = array();
                    }
                    if(!isset($room_types[$_room_type_id][$booking['room_id']])) {
                        $room_types[$_room_type_id][$booking['room_id']] = array();
                    }
                    $room_types[$_room_type_id][$booking['room_id']][] = $booking;
                }
            }
            foreach ($bookings as $booking) {
                if(!$booking['room_id'] && $booking['check_out_date'] > $check_in_date && $check_out_date > $booking['check_in_date'])
                {
                    if (isset($room_types[$booking['brh_room_type_id']]) && $room_types[$booking['brh_room_type_id']]) {
                        $overlapping_with_other_bookings = false;
                        foreach ($room_types[$booking['brh_room_type_id']] as $key => $room_bookings) {

                            //check if room_booking.start-date and room_booking.end-date overlaps with new booking-start-date and new booking-end-adte;
                            foreach ($room_bookings as $room_booking)
                            {
                                if ($booking['check_out_date'] > $room_booking['check_in_date'] && $booking['check_in_date'] < $room_booking['check_out_date']) { // overlapping

                                    // assign this booking to same room as not overlapping
                                    $overlapping_with_other_bookings = true;
                                }
                            }
                        }

                        if($overlapping_with_other_bookings) {
                            // assign this booking to new room
                            $room_types[$booking['brh_room_type_id']][] = array($booking);
                        } else {
                            $room_types[$booking['brh_room_type_id']][$key][] = $booking;
                        }

                    } else {
                        $room_types[$booking['brh_room_type_id']][] = array($booking);
                    }
                }
            }

            foreach ($available_room_types as $key => $room_type) {
                // update the availability of the available room type
                $availability = $room_type['availability'];

                $unassigned_occupancy = isset($room_types[$room_type['id']]) ? count($room_types[$room_type['id']]) : 0;
                $availability = $availability - $unassigned_occupancy;

                $available_room_types[$key]['id']           = $room_type['id'];
                $available_room_types[$key]['availability'] = $availability > 0 ? $availability : 0;
            }
        }
        else
        {
            foreach ($available_room_types as $key => $room_type) {
                // update the availability of the available room type
                $availability = $room_type['availability'];
                foreach ($occupancies as $occupancy) {
                    if ($room_type['id'] == $occupancy['id']) {
                        $availability = $room_type['availability'] - $occupancy['occupancy'];
                    }
                }

                $available_room_types[$key]['id']           = $room_type['id'];
                $available_room_types[$key]['availability'] = $availability > 0 ? $availability : 0;
            }
        }
        if ($isAJAX) {
            echo json_encode($available_room_types);
            return;
        } else {
            return $available_room_types;
        }
    }

    function get_available_rooms_in_AJAX($check_in_date = null, $check_out_date = null, $room_type_id = null, $booking_id = null, $room_id = null, $isAJAX = true)
    {
        $check_in_date = $check_in_date ? $check_in_date : sqli_clean($this->security->xss_clean($this->input->post('check_in_date', TRUE)));
        $check_out_date = $check_out_date ? $check_out_date : sqli_clean($this->security->xss_clean($this->input->post('check_out_date', TRUE)));
        $room_type_id = $room_type_id ? $room_type_id : sqli_clean($this->security->xss_clean($this->input->post('room_type_id', TRUE)));
        $booking_id = $booking_id ? $booking_id : sqli_clean($this->security->xss_clean($this->input->post('booking_id', TRUE)));
        $room_id = $room_id ? $room_id : sqli_clean($this->security->xss_clean($this->input->post('room_id', TRUE)));

        $check_in_date = date('Y-m-d H:i:s', strtotime($check_in_date));
        $check_out_date = date('Y-m-d H:i:s', strtotime($check_out_date));

        if ($check_in_date <= $this->selling_date." 00:00:00" && $this->selling_date." 00:00:00" <= $check_out_date) {
            $check_in_date = $this->selling_date." 00:00:00";
        }

        $available_rooms = $this->Room_model->get_available_rooms(
            $check_in_date,
            $check_out_date,
            $room_type_id,
            $booking_id,
            null,
            0,
            null,
            null,
            $room_id
        );

        if ($isAJAX) {
            echo json_encode($available_rooms);
            return;
        } else {
            return $available_rooms;
        }
    }

    function get_rooms_available()
    {
        $check_in_date = sqli_clean($this->security->xss_clean($this->input->post('check_in_date')));
        $check_out_date = sqli_clean($this->security->xss_clean($this->input->post('check_out_date')));

        $booked_reservations = $this->Room_model->get_rooms_for_reservations(
            $check_in_date,
            $check_out_date,
            $this->company_id
        );

        foreach($booked_reservations as $book)
        {
            $check_in_date = $book['check_in_date'];
            $check_out_date = $book['check_out_date'];

            $diff = abs(strtotime($check_out_date) - strtotime($check_in_date));

            $years = floor($diff / (365*60*60*24));
            $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
            $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));

            for($i = 0; $i <= $days ; $i++)
            {
                $dates[] = $check_in_date;
                $check_in_date = date('Y-m-d',strtotime($check_in_date . "+1 days"));
            }
        }
        //print_r($dates);
        $json = json_encode($dates);
        echo $json;
    }

    function get_bookings_in_JSON_debug()
    {

        $this->output->enable_profiler(TRUE);

        $this->load->helper('date'); // load date helper. Keep in mind that I have also extended my_date_helper.php
        $rows = array();

        $start = setDate("Thu, 24 Aug 2017 18:30:00 GMT", 'notime');
        $end = setDate("Wed, 20 Sep 2017 18:30:00 GMT", 'notime');
        $group_id = "";
        $floor_id = "";
        $location_id = "";
        $room_type_id = "";
        $reservation_type = "";

        $this->load->model('Booking_model');
        $this->load->model('Booking_room_history_model');
        $filters = array(
            'start_date' => $start,
            'end_date' => $end,
            'state' => "active",
            'group_id' => $group_id,
            'location_id' => $location_id,
            'room_type_id' => $room_type_id,
            'floor_id' => $floor_id,
            'reservation_type' => $reservation_type,
            'group_by' => 'booking_room_history_id' // grouping by booking blocks!
        );
        $bookings = $this->Booking_model->get_bookings($filters);


        $data['rows'] = array();

        foreach($bookings as $booking)
        {
            $warning_array = array();
            $warning = "";

            if ($booking['check_in_date'] == $booking['check_out_date'])
            {
                // TODO: use language pack (i.e. $this->lang->line('warning_short_stay'))
                $warning_array[] = l("This block's check-in and check-out dates are the same", true);
            }
            if ($booking['state'] == UNCONFIRMED_RESERVATION)
            {
                $warning_array[] = l("This is an unconfirmed reservation. New reservation or new walk-in can be made on top of this block", true);
            }
            if ($this->selling_date >= $booking['check_out_date'] && $booking['state'] == INHOUSE)
            {
                // Make sure we consider this booking's check-out date. NOT this booking BLOCK's check-out date
                $latest_block = $this->Booking_room_history_model->get_latest_booking_room_history($booking['booking_id']);
                if ($latest_block['check_out_date'] == $booking['check_out_date'])
                {
                    $warning_array[] = l("This guest is supposed to check out", true);
                }
            }

            if($this->is_total_balance_include_forecast == 1)
            {
                $booking['balance'] = isset($booking['balance']) ? round(floatval($booking['balance']), 2) : 0;
                $booking['charge_total'] = floatval($booking['balance']) + floatval($booking['payment_total']);
            }
            else
            {
                $booking['balance'] = floatval($booking['charge_total']) - floatval($booking['payment_total']);
            }

            if ($booking['state'] == CHECKOUT && $booking['balance'] > 0)
            {
                $warning_array[] = l("This guest has an outstanding balance", true);
            }

            if (count($warning_array) > 0)
            {
                $warning = implode(", and ", $warning_array);
            }

            // TODO: Check if booking already has custom color assigned. Otherwise, just make them red
            if ($warning != "")
            {
                $booking['border_color'] =  "red";
            }
            else
            {
                $booking['border_color'] =  "black";
            }

            $booking['warning_message'] = $warning;

            $data['rows'][] = $booking;

        }

        $rows = json_encode($data['rows']);
        print_r($rows);
    }

    // accessed by jquery calendar.js
    // not the best practice. This function should be in model
    // function get_bookings_in_JSON()
    // {

    //     $this->load->helper('date'); // load date helper. Keep in mind that I have also extended my_date_helper.php
    //     $rows = array();

    //     $start = setDate(sqli_clean($this->security->xss_clean($this->input->post('start'))), 'notime');
    //     $end = setDate(sqli_clean($this->security->xss_clean($this->input->post('end'))), 'notime');
    //     $group_id = sqli_clean($this->security->xss_clean($this->input->post('group_id')));
    //     $floor_id = sqli_clean($this->security->xss_clean($this->input->post('floor_id')));
    //     $location_id = sqli_clean($this->security->xss_clean($this->input->post('location_id')));
    //     $room_type_id = sqli_clean($this->security->xss_clean($this->input->post('room_type_id')));
    //     $reservation_type = sqli_clean($this->security->xss_clean($this->input->post('reservation_type')));
    //     $booking_source = sqli_clean($this->security->xss_clean($this->input->post('booking_source')));

    //     $this->load->model('Booking_model');
    //     $this->load->model('Booking_room_history_model');
    //     $filters = array(
    //         'start_date' => $start,
    //         'end_date' => $end,
    //         'state' => "active",
    //         //'group_id' => $group_id,
    //         'location_id' => $location_id,
    //         'room_type_id' => $room_type_id,
    //         'floor_id' => $floor_id,
    //         'reservation_type' => $reservation_type,
    //         'booking_source' => $booking_source,
    //         'group_by' => 'booking_room_history_id', // grouping by booking blocks!
    //         'not_include_charge_payment_total' => true
    //     );
    //     $bookings = $this->Booking_model->get_bookings($filters, null, null ,true);


    //     $data['rows'] = array();

    //     foreach($bookings as $booking)
    //     {
    //         $warning_array = array();
    //         $warning = "";

    //         if ($booking['check_in_date'] == $booking['check_out_date'])
    //         {
    //             // TODO: use language pack (i.e. $this->lang->line('warning_short_stay'))
    //             $warning_array[] = l("This block's check-in and check-out dates are the same", true);
    //         }
    //         if ($booking['state'] == UNCONFIRMED_RESERVATION)
    //         {
    //             $warning_array[] = l("This is an unconfirmed reservation. New reservation or new walk-in can be made on top of this block", true);
    //         }
    //         if ($this->selling_date >= date('Y-m-d', strtotime($booking['check_out_date'])) &&
    //             $this->selling_date > date('Y-m-d', strtotime($booking['check_in_date'])) && $booking['state'] == INHOUSE)
    //         {
    //             // Make sure we consider this booking's check-out date. NOT this booking BLOCK's check-out date
    //             $latest_block = $this->Booking_room_history_model->get_latest_booking_room_history($booking['booking_id']);
    //             if ($latest_block['check_out_date'] == $booking['check_out_date'])
    //             {
    //                 $warning_array[] = l("This guest is supposed to check out", true);
    //             }
    //         }

    //         if($this->is_total_balance_include_forecast == 1)
    //         {
    //             $booking['balance'] = isset($booking['balance']) ? round(floatval($booking['balance']), 2) : 0;
    //             //$booking['charge_total'] = floatval($booking['balance']) + floatval($booking['payment_total']);
    //         }
    //         else
    //         {
    //             $booking['balance'] = isset($booking['balance_without_forecast']) ? round(floatval($booking['balance_without_forecast']), 2) : 0;
    //             //$booking['balance'] = floatval($booking['charge_total']) - floatval($booking['payment_total']);
    //         }

    //         if ($booking['state'] == CHECKOUT && $booking['balance'] > 0)
    //         {
    //             $warning_array[] = l("This guest has an outstanding balance", true);
    //         }

    //         if (count($warning_array) > 0)
    //         {
    //             $warning = implode(", and ", $warning_array);
    //         }
    //         $payment_details = $this->Payment_model->get_payments($booking['booking_id']);
    //         $booking['payment_total'] = 0;
    //          if (isset($payment_details)){
    //             foreach($payment_details as $payment)
    //             {
    //             $booking['payment_total'] += $payment['amount'];
    //             }
    //         }else{
    //             $booking['payment_total'] = 0;
    //         }
    //         // TODO: Check if booking already has custom color assigned. Otherwise, just make them red
    //         if ($warning != "")
    //         {
    //             $booking['border_color'] =  "red";
    //         }
    //         else
    //         {
    //             $booking['border_color'] =  "black";
    //         }

    //         $booking['warning_message'] = $warning;

    //         $data['rows'][] = $booking;

    //     }

    //     $rows = json_encode($data['rows']);
    //     print_r($rows);
    // }

    function get_bookings_in_JSON()
    {

        $this->load->helper('date'); // load date helper. Keep in mind that I have also extended my_date_helper.php
        $rows = array();

        $start = setDate(sqli_clean($this->security->xss_clean($this->input->post('start'))), 'notime');
        $end = setDate(sqli_clean($this->security->xss_clean($this->input->post('end'))), 'notime');
        $group_id = sqli_clean($this->security->xss_clean($this->input->post('group_id')));
        $floor_id = sqli_clean($this->security->xss_clean($this->input->post('floor_id')));
        $location_id = sqli_clean($this->security->xss_clean($this->input->post('location_id')));
        $room_type_id = sqli_clean($this->security->xss_clean($this->input->post('room_type_id')));
        $reservation_type = sqli_clean($this->security->xss_clean($this->input->post('reservation_type')));
        $booking_source = sqli_clean($this->security->xss_clean($this->input->post('booking_source')));

        $this->load->model('Booking_model');
        $this->load->model('Booking_room_history_model');
        $filters = array(
            'start_date' => $start,
            'end_date' => $end,
            'state' => "active",
            //'group_id' => $group_id,
            'location_id' => $location_id,
            'room_type_id' => $room_type_id,
            'floor_id' => $floor_id,
            'reservation_type' => $reservation_type,
            'booking_source' => $booking_source,
            'group_by' => 'booking_room_history_id', // grouping by booking blocks!
            'not_include_charge_payment_total' => true
        );
        $bookings = $this->Booking_model->get_bookings($filters, null, null ,true);


        $data['rows'] = $booking_ids_arr = array();
        $i = 0;

        foreach($bookings as $booking)
        {
            $warning_array = array();
            $warning = "";

            $booking_ids_arr[] = $booking['booking_id'];

            if ($booking['check_in_date'] == $booking['check_out_date'])
            {
                // TODO: use language pack (i.e. $this->lang->line('warning_short_stay'))
                $warning_array[] = l("This block's check-in and check-out dates are the same", true);
            }
            if ($booking['state'] == UNCONFIRMED_RESERVATION)
            {
                $warning_array[] = l("This is an unconfirmed reservation. New reservation or new walk-in can be made on top of this block", true);
            }
            if ($this->selling_date >= date('Y-m-d', strtotime($booking['check_out_date'])) &&
                $this->selling_date > date('Y-m-d', strtotime($booking['check_in_date'])) && $booking['state'] == INHOUSE)
            {
                // Make sure we consider this booking's check-out date. NOT this booking BLOCK's check-out date
                $latest_block = $this->Booking_room_history_model->get_latest_booking_room_history($booking['booking_id']);
                if ($latest_block['check_out_date'] == $booking['check_out_date'])
                {
                    $warning_array[] = l("This guest is supposed to check out", true);
                }
            }

            if($this->is_total_balance_include_forecast == 1)
            {
                $booking['balance'] = isset($booking['balance']) ? round(floatval($booking['balance']), 2) : 0;
                //$booking['charge_total'] = floatval($booking['balance']) + floatval($booking['payment_total']);
            }
            else
            {
                $booking['balance'] = isset($booking['balance_without_forecast']) ? round(floatval($booking['balance_without_forecast']), 2) : 0;
                //$booking['balance'] = floatval($booking['charge_total']) - floatval($booking['payment_total']);
            }

            if ($booking['state'] == CHECKOUT && $booking['balance'] > 0)
            {
                $warning_array[] = l("This guest has an outstanding balance", true);
            }

            if (count($warning_array) > 0)
            {
                $warning = implode(", and ", $warning_array);
            }
            // $payment_details = $this->Payment_model->get_payments($booking['booking_id']);
            // $booking['payment_total'] = 0;
            //  if (isset($payment_details)){
            //     foreach($payment_details as $payment)
            //     {
            //     $booking['payment_total'] += $payment['amount'];
            //     }
            // }else{
            //     $booking['payment_total'] = 0;
            // }
            // TODO: Check if booking already has custom color assigned. Otherwise, just make them red
            if ($warning != "")
            {
                $booking['border_color'] =  "red";
            }
            else
            {
                $booking['border_color'] =  "black";
            }

            $booking['payment_total'] = 0;

            $booking['warning_message'] = $warning;

            $data['rows'][$booking['booking_id'].'_'.$i] = $booking;

            $i++;

        }

        if(!empty($booking_ids_arr)) {

            $booking_ids = implode(',', $booking_ids_arr);

            $payment_details = $this->Payment_model->get_payments($booking_ids);
            $payment_total = 0;
            $payment_book_ids = array();

            // foreach($payment_details as $payment)
            // {
            //     // $payment_total = 0;
            //     if(!in_array($payment['booking_id'], $payment_book_ids))
            //         $payment_total += $payment['amount'];

            //     // $key1 = explode('_', $key);

            //     // if($payment['booking_id'] == $key1[0]){
            //     //     $data['rows'][$key1[0].'_'.$key1[1]]['payment_total'] = $payment_total;
            //     // }
            // }

            // prx($payment_details);

            // foreach ($data['rows'] as $key => $value) {
            
            //     if (isset($payment_details)){
            //         foreach($payment_details as $payment)
            //         {
            //             $payment_total = 0;
            //             $payment_total += $payment['amount'];

            //             $key1 = explode('_', $key);

            //             if($payment['booking_id'] == $key1[0]){
            //                 $data['rows'][$key1[0].'_'.$key1[1]]['payment_total'] = $payment_total;
            //             }
            //         }
            //     }
            // }

            // Step 1: Accumulate payments for each booking_id
            $payment_totals = [];
            if($payment_details && count($payment_details) > 0){
            foreach ($payment_details as $payment) {
                if (!isset($payment_totals[$payment['booking_id']])) {
                    $payment_totals[$payment['booking_id']] = 0;
                }
                $payment_totals[$payment['booking_id']] += $payment['amount'];
            }
            }

            // Step 2: Update $data['rows'] with the accumulated payment totals
            foreach ($data['rows'] as $key => $value) {
                $key1 = explode('_', $key);
                $booking_id = $key1[0];
                
                if (isset($payment_totals[$booking_id])) {
                    $data['rows'][$key1[0].'_'.$key1[1]]['payment_total'] = $payment_totals[$booking_id];
                }
            }

        }

        $data['rows'] = array_values($data['rows']);

        $rows = json_encode($data['rows']);
        print_r($rows);
    }

    function print_registration_card($booking_id='') {
        $data['company'] = $this->company_data; //$this->Company_model->get_company($this->company_id);
        $data['check_in_policies'] = $this->Company_model->get_check_in_policies($this->company_id);

        $booking = $this->Booking_model->get_booking($booking_id);
        $booking['booking_blocks'] = $this->Booking_room_history_model->get_booking_blocks($booking_id);
        $data['customer'] = $this->Customer_model->get_customer($booking['booking_customer_id']);
        $booking['staying_customers'] = $this->Customer_model->get_staying_customers($booking_id);
        $booking['extras'] = $this->Booking_extra_model->get_booking_extras($booking_id);

        //If current selling date is between check-in and check-out date
        //use current selling date as the check in date for checking available rooms
        $current_booking_block = $this->Booking_room_history_model->get_booking_detail($booking_id);
        $booking['check_in_date'] = $current_booking_block['check_in_date'];
        $booking['check_out_date'] = $current_booking_block['check_out_date'];
        $booking['room_type_name'] = $this->Room_type_model->get_room_type_by_room_id($current_booking_block['room_id'])['name'];

        $room = $this->Room_model->get_room($current_booking_block['room_id']);
        $booking['room_name'] = $room['room_name'];

        $data['customer_fields'] = $this->Customer_field_model->get_customer_fields_for_registration_card($this->company_id);

        // if booking is using rate plan, get the rate of the check-in date of the booking
        if ($booking['use_rate_plan'] == 1)
        {
            $rate_array = $this->rate->get_rate_array(
                $booking['rate_plan_id'],
                $booking['check_in_date'],
                $booking['check_out_date'],
                $booking['adult_count'],
                $booking['children_count']
            );
            if(isset($rate_array[0]))
            {
                $booking['rate'] = $rate_array[0]['rate'];
            }
        }

        $data['menu_on'] = false;

        $data['booking'] = $booking;
        $data['js_files'] = array(
            base_url() . auto_version('js/booking/registration_card.js')
        );

        $data['main_content'] = 'booking/registration_card';
        $this->load->view('includes/bootstrapped_template', $data);
    }

    // If there are continuous bloking blocks that are split, then combine them.
    function _combine_booking_blocks($booking_id) {
        $this->Booking_room_history_model->check_and_combine_booking_blocks($booking_id);
    }

    function get_rate_plans_JSON($room_type_id = null, $previous_rate_plan_id = null, $isAJAX = true) {
        $room_type_id = $room_type_id ? $room_type_id : sqli_clean($this->security->xss_clean($this->input->post('room_type_id')));
        $previous_rate_plan_id = $previous_rate_plan_id ? $previous_rate_plan_id : sqli_clean($this->security->xss_clean($this->input->post('previous_rate_plan_id')));

        $this->load->model('Rate_plan_model');
        $rate_plan_array = $this->Rate_plan_model->get_rate_plans_by_room_type_id($room_type_id, $previous_rate_plan_id);

        if ($isAJAX) {
            $json = json_encode($rate_plan_array);
            echo $json;
            return;
        } else {
            return $rate_plan_array;
        }
    }

    function get_charge_types_in_JSON() {
        echo json_encode($this->Charge_type_model->get_room_charge_types($this->company_id));
    }

    function get_customer_data_on_pageload()
    {
        $data = array();
        $data['common_customer_fields'] = $this->Customer_field_model->get_customer_fields_for_customer_form($this->company_id, true);
        $data['room_charge_types'] = $this->Charge_type_model->get_room_charge_types($this->company_id);

        echo json_encode($data);
    }

    // accessed by jquery calendar.js
    // not the best practice. this function should be in model
    // should really convert this to appropriate JSON variable camelcasing
    function get_all_rooms_in_JSON() {
        $where = '';
        if (isset($_GET['location_id']) && $_GET['location_id']) {
            $where .= ' AND location_id="' . $_GET['location_id'] . '"';
        }
        if (isset($_GET['floor_id']) && $_GET['floor_id']) {
            $where .= ' AND floor_id="' . $_GET['floor_id'] . '"';
        }
        if (isset($_GET['room_type_id']) && $_GET['room_type_id']) {
            $where .= ' AND room_type_id="' . $_GET['room_type_id'] . '"';
        }
        if (isset($_GET['group_id']) && $_GET['group_id']) {
            $where .= ' AND group_id="' . $_GET['group_id'] . '"';
        }

        $order = 'length(sort_order), sort_order' . ',' . $_GET['order'];
        $rooms = $this->Room_model->get_rooms($this->company_id, $order, $where); // sort by the list by room name
        $rooms_in_json = json_encode($rooms);
        print_r($rooms_in_json);
    }

    function send_booking_confirmation_email($booking_id) {
        $this->load->library('email_template');
        $result_array = $this->email_template->send_booking_confirmation_email($booking_id);
        if ($result_array && $result_array['success']) {
            $this->_create_booking_log($booking_id, "Confirmation Email Sent to " . $result_array['customer_email'], SYSTEM_LOG);
        }
        echo $result_array['message'];
    }

    function send_multiple_booking_confirmation_email() {
        $booking_ids = $this->input->post('booking_ids');
        $group_id = $this->input->post('group_id');
        $this->load->library('email_template');
        //$result_array = $this->email_template->send_multiple_booking_confirmation_email($booking_ids, $group_id);
        // if ($result_array && $result_array['success']) {
        foreach($booking_ids as $id)
        {
            $result_array = $this->email_template->send_booking_confirmation_email($id);
            $this->_create_booking_log($id, "Confirmation Email Sent to " . $result_array['customer_email'], SYSTEM_LOG);
        }
        //}
        echo $result_array['message'];
    }

    function send_group_booking_confirmation_email($booking_id, $group_id = NULL) {
        $this->load->library('email_template');
        $result_array = $this->email_template->send_group_booking_confirmation_email($booking_id, $group_id);
        if ($result_array && $result_array['success']) {
            $this->_create_booking_log($booking_id, "Confirmation Email Sent to " . $result_array['customer_email'], SYSTEM_LOG);
        }
        echo $result_array['message'];
    }

    function send_booking_cancellation_email($booking_id) {
        $this->load->library('email_template');
        $result_array = $this->email_template->send_booking_cancellation_email($booking_id);
        if ($result_array && $result_array['success']) {
            $this->_create_booking_log($booking_id, "Cancellation Email Sent to " . $result_array['customer_email'], SYSTEM_LOG);
        }
        return $result_array;
    }

    function send_booking_cancellation_email_AJAX($booking_id) {
        $result_array = $this->send_booking_cancellation_email($booking_id);
        if ($result_array && $result_array['success']) {
            $message = l("Cancellation Email Sent to ", true);
            echo $message . $result_array['customer_email'];
        } else {
            echo l("Customer email not found!", true);
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

    function _create_booking_log_batch ($booking_ids, $log, $log_type = USER_LOG) {

        $batch = array();

        foreach ($booking_ids as $booking_id) {
            $batch[] = array(
                "selling_date" => $this->selling_date,
                "booking_id" => $booking_id,
                "date_time" => gmdate('Y-m-d H:i:s'),
                "log_type" => $log_type,
                "log" => $log,
                "user_id" => $this->user_id
            );
        }

        $this->Booking_log_model->insert_logs($batch);
    }

    
    /* New shit! 2015-03-09 Jaeyun */

    function get_booking_AJAX() {

        $booking_id = sqli_clean($this->security->xss_clean($this->input->post('booking_id')));

        if (!$this->Booking_model->booking_belongs_to_company($booking_id, $this->company_id))
            return;

        $this->permission->check_access_to_booking_id($this->user_id, $booking_id);

        $booking = $this->Booking_model->get_booking($booking_id);
        $booking['custom_booking_fields'] = $this->Booking_model->get_booking_fields($booking_id);

        // get booking group
        $booking_group_detail = $this->Booking_linked_group_model->get_booking_linked_group($booking_id, $this->company_id);
        if (!empty($booking_group_detail)) {

            if($this->is_group_booking_features == true){

                $option_name = 'group_booking_total_counts_'.$booking_group_detail['id'];
            
                $grp_total_counts = $this->Option_model->get_option($option_name);

                $total_room_count = $total_guest_count = 0;
                
                if(!empty($grp_total_counts)){
                    foreach ($grp_total_counts as $key => $value) {
                        
                        $val = json_decode($value['option_value'], true);

                        $total_room_count += $val['room_count'];
                        $total_guest_count += $val['guest_count'];
                    }
                }

                $group_booking_info = array(
                    'group_id' => $booking_group_detail['id'],
                    'group_name' => $booking_group_detail['name'],
                    'total_room_count' => $total_room_count,
                    'total_guest_count' => $total_guest_count
                );
            } else {
                $group_booking_info = array(
                    'group_id' => $booking_group_detail['id'],
                    'group_name' => $booking_group_detail['name']
                );
            }
        }
        else
            $group_booking_info = null;

        $booking['booking_blocks'] = $this->Booking_room_history_model->get_booking_blocks($booking_id);

        if($this->is_total_balance_include_forecast == 1)
        {
            $booking['balance'] = isset($booking['balance']) ? round(floatval($booking['balance']), 2) : 0;
        }
        else
        {
            $booking['balance'] = isset($booking['balance_without_forecast']) ? round(floatval($booking['balance_without_forecast']), 2) : 0;
            //$booking['balance'] = $this->Booking_model->get_balance($booking_id);
        }

        if ($booking['booking_customer_id'])
            $booking['paying_customer'] = $this->Customer_model->get_customer($booking['booking_customer_id']);

        if ($booking['booked_by'])
            $booking['booked_by'] = $this->Customer_model->get_bookedby_customer($booking['booked_by']);

        $booking['staying_customers'] = $this->Customer_model->get_staying_customers($booking_id);
        $booking['extras'] = $this->Booking_extra_model->get_booking_extras($booking_id);
        $booking['extras_count'] = count($booking['extras']);

        //If current selling date is between check-in and check-out date
        //use current selling date as the check in date for checking available rooms
        $current_booking_block = $this->Booking_room_history_model->get_booking_detail($booking_id);
        $booking['check_in_date'] = (isset($current_booking_block['check_in_date'])) ? $current_booking_block['check_in_date'] : '';
        $booking['check_out_date'] = (isset($current_booking_block['check_out_date'])) ? $current_booking_block['check_out_date'] : '';

        if (isset($current_booking_block['room_id'])) {
            // get current room name
            $room_info = $this->Room_model->get_room($current_booking_block['room_id']);
            $booking['current_room_type_id'] = $this->Room_type_model->get_room_type_by_room_id($current_booking_block['room_id'])['id'];
            $booking['current_room_id'] = $current_booking_block['room_id'];
            $booking['current_room_name'] = isset($room_info['room_name']) ? $room_info['room_name'] : null;
        }


        $rate_plan = false;
        if (isset($booking['current_room_type_id'])) {
            $rate_plan = $this->get_rate_plans_JSON($booking['current_room_type_id'], $booking['rate_plan_id'], false);
        }

        $available_room_types = $this->get_available_room_types_in_JSON($booking['check_in_date'], $booking['check_out_date'], false);

        $available_rooms = $this->get_available_rooms_in_AJAX($booking['check_in_date'], $booking['check_out_date'], $booking['current_room_type_id'], $booking_id, $booking['current_room_id'], false);
        $faetures = $this->Company_model->get_company($this->company_id);

        $end_date = date('Y-m-d', strtotime($booking['check_out_date']));
        $start_date = date('Y-m-d', strtotime($booking['check_in_date']));
        $datetime1 = date_create($start_date);
        $datetime2 = date_create($end_date);
        $interval = date_diff($datetime1, $datetime2);
        $number_of_days = $interval->format('%a');

        $allow_state_change = true;

        if($number_of_days == 0) {
            $company_id = $this->company_id;
            $company_time_zone = $this->Company_model->get_time_zone($company_id);
            
            $actual_time = convert_to_local_time(new DateTime(), $company_time_zone)->format("H:i:s");

            $booking_check_in_date = explode(' ', $booking['check_in_date']);
            $booking_check_in_time = $booking_check_in_date[1];

            $booking_check_out_date = explode(' ', $booking['check_out_date']);
            $booking_check_out_time = $booking_check_out_date[1];

            if($actual_time > $booking_check_out_time) {
                $allow_state_change = false;
            }
        }

        $response = array(
            'success' => 'true',
            'booking' => $booking,
            'extras' => $this->Extra_model->get_extras($this->company_id),
            'group_info' => $group_booking_info,
            'rate_plan' => $rate_plan,
            'available_room_types' => $available_room_types,
            'available_rooms' => $available_rooms,
            'allow_state_change' => $allow_state_change,
            'allow_change_state' => isset($faetures["allow_change_previous_booking_status"]) ? $faetures["allow_change_previous_booking_status"] : 0,
        );

        echo json_encode($response);
    }

    function create_booking_AJAX() {

        ob_start();

        $response = Array();
        $booking_group_id = null;
        $data = $this->input->post('data');
        $custom_booking_fields = isset($data['custom_booking_fields']) ? $data['custom_booking_fields'] : null;
        

        $existing_group_id = $this->input->post('existing_group_id');

        $company_detail = $this->company_data; //$this->Company_model->get_company($this->company_id);
        $errors = $this->_validate_booking_data($data, '', '', $company_detail);

        unset($data['custom_booking_fields']);

        if (!empty($errors)) {
            ob_clean();
            echo json_encode(
                array(
                    'errors' => $errors
                )
            );
            return;
        }

        $booking_data = $data['booking'];
        $booking_data['booked_by'] = isset($booking_data['booked_by']) && $booking_data['booked_by'] ? $booking_data['booked_by'] : NULL;
        $pay_period = isset($data['booking']['pay_period']) ? $data['booking']['pay_period'] : DAILY;

        // check duplicate booking
        $is_duplicate = $this->input->post('is_duplicate') === "true";
        $old_booking_id = $this->input->post('old_booking_id');

        //check overbooking
        if(!$is_duplicate && !(isset($data['isGroupBooking']) && (boolean)$data['isGroupBooking']))
        {
            $start_date = (isset($data['rooms']) && isset($data['rooms'][0]) && isset($data['rooms'][0]['check_in_date']) ? $data['rooms'][0]['check_in_date'] : null);
            $end_date = (isset($data['rooms']) && isset($data['rooms'][0]) && isset($data['rooms'][0]['check_out_date']) ? $data['rooms'][0]['check_out_date'] : null);
            $room_id = (isset($data['rooms']) && isset($data['rooms'][0]) && isset($data['rooms'][0]['room_id']) ? $data['rooms'][0]['room_id'] : null);
            if ($room_id) {
                $bookings_found_in_range = $this->Booking_model->check_overbooking($start_date, $end_date, $room_id);
                if ($bookings_found_in_range) {
                    echo json_encode(array("overbooking_status" => true));
                    return;
                }
            }
        }

        // update customers
        // if any customer is entered at all
        if (isset($data['customers']['paying_customer']) &&
            $data['customers']['paying_customer'] != '') {
            $paying_customer = $data['customers']['paying_customer'];
            if (isset($paying_customer['customer_id'])) {
                $booking_data['booking_customer_id'] = $paying_customer['customer_id'];
            } else {
                $paying_customer_id = $this->Customer_model->create_customer(
                    Array(
                        'company_id' => $this->company_id,
                        'customer_name' => $paying_customer['customer_name']
                    )
                );

                $post_customer_data = $paying_customer;
                $post_customer_data['customer_id'] = $paying_customer_id;

                do_action('post.create.customer', $post_customer_data);

                $booking_data['booking_customer_id'] = $paying_customer_id;
            }
        } else {
            $booking_data['booking_customer_id'] = NULL;
        }

        $source = $data['booking']['source'];
        $booking_data['company_id'] = $this->company_id;
        if($source == '2' || $source == '3' || $source == '4' || $source == '8')
        {
            $booking_data['is_ota_booking'] = '1';
        }

        // check linked group booking 
        if ($existing_group_id != null) {
            $booking_group_id = $existing_group_id;
            $data['rooms'][0]['room_count'] = 1;
        } elseif ($data['isGroupBooking'] == true && isset($data['isGroupBooking'])) {
            $group_name = isset($data['groupName']) ? $data['groupName'] : null;
            $booking_group_id = $this->Booking_linked_group_model->create_booking_linked_group($group_name); // 
        }

        // update booking data
        foreach ($data['rooms'] as $room) {
            $booking_data['rate'] = $room['rate'];
            $booking_data['use_rate_plan'] = $room['use_rate_plan'];
            $booking_data['rate_plan_id'] = isset($room['rate_plan_id']) ? $room['rate_plan_id'] : NULL;
            $booking_data['charge_type_id'] = isset($room['charge_type_id']) ? $room['charge_type_id'] : NULL;

            $booking_data['pay_period'] = isset($room['pay_period']) ? $room['pay_period'] : $pay_period;

            if (isset($room['room_count'])) { // book multiple rooms
                $available_rooms = $this->Room_model->get_available_rooms(
                    $room['check_in_date'], $room['check_out_date'], $room['room_type_id']
                );

                $temp_response = $this->_create_group_booking($booking_data, $data, $room, $available_rooms, $booking_group_id, $custom_booking_fields);
                $response = array_merge($response, $temp_response);

            } else { // booking a single room
                $booking_response = $this->_create_booking($booking_data, $data['customers'], $room, $data['isGroupBooking'], $is_duplicate, $old_booking_id);

                $booking_id = $booking_response['booking_id'];

                $this->Booking_model->create_booking_fields($booking_id, $custom_booking_fields);

                if ($existing_group_id != null) {
                    // insert booking_id in booking_x_booking_group at group id
                    $booking_group_data = array(
                        'booking_id' => $booking_id,
                        'booking_group_id' => $booking_group_id
                    );
                    $this->Booking_linked_group_model->insert_booking_x_booking_linked_group($booking_group_data);
                }

                $balance = $this->Booking_model->update_booking_balance($booking_id);
                if(!$this->is_total_balance_include_forecast)
                {
                    $balance = 0;
                }

                if(!$data['isGroupBooking'] && (isset($this->automatic_email_confirmation) && $this->automatic_email_confirmation))
                {
                    $this->send_booking_confirmation_email($booking_id);
                }

                if(isset($booking_response['rate_plan_id']) && $booking_response['rate_plan_id'])
                {
                    $response[] = array(
                                        'booking_id' => $booking_id, 
                                        'rate_plan_id' => $booking_response['rate_plan_id'], 
                                        'rate_plan_name' => $booking_response['rate_plan_name'], 'balance' => $balance
                                    );
                } else {
                    $response[] = array('booking_id' => $booking_id, 'balance' => $balance);
                }
                

            }
        }
        ob_clean();
        echo json_encode($response);
    }

    function _create_booking($booking_data, $customer_data, $room, $isGroupBooking = false, $is_duplicate = null, $old_booking_id = null) {
        $rate_plan = $rate_array = array();
        $booking_id = $this->Booking_model->create_booking($booking_data);

        $post_booking_data = $booking_data;
        $post_booking_data['booking_id'] = $booking_id;
        $post_booking_data['company_id'] = $this->company_id;

        $response = array();

        if(isset($booking_data['use_rate_plan']) && $booking_data['use_rate_plan'])
        {
            $end_date = date('Y-m-d', strtotime($room['check_out_date']));
            $start_date = date('Y-m-d', strtotime($room['check_in_date']));
            $datetime1 = date_create($start_date);
            $datetime2 = date_create($end_date);
            $interval = date_diff($datetime1, $datetime2);
            $days = $interval->format('%a');

            $this->load->library('rate');
            $raw_rate_array = $this->rate->get_rate_array($booking_data['rate_plan_id'], $start_date, $end_date, $booking_data['adult_count'], $booking_data['children_count']);

            $rate_array = array();
            foreach ($raw_rate_array as $rate)
            {
//              $base_rate = $rate['base_rate'];
//              $base_rate = $base_rate ? $base_rate : isset($rate['adult_'.$booking_data['adult_count'].'_rate']) ? $rate['adult_'.$booking_data['adult_count'].'_rate'] : 0;

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

            $curreny_data = $this->Currency_model->get_default_currency($this->company_id);
            $rate_plan_data = $this->Rate_plan_model->get_rate_plan($booking_data['rate_plan_id']);
            $rate_plan = array(
                "rate_plan_name" => $rate_plan_data['rate_plan_name']." #".$booking_id,
                "number_of_adults_included_for_base_rate" => $booking_data['adult_count'],
                "rates" => get_array_with_range_of_dates($rate_array),
                "currency_id" => $rate_plan_data['currency_id'],
                "charge_type_id" => $rate_plan_data['charge_type_id'],
                "company_id" => $this->company_id,
                "is_selectable" => '0',
                "room_type_id" => $room['room_type_id'],
                "parent_rate_plan_id" => $booking_data['rate_plan_id'],
                "policy_code"=> $rate_plan_data['policy_code']
            );

            // create rates
            $rates = $rate_plan['rates'];
            unset($rate_plan['rates']);

            // create rate plan
            $rate_plan_id = $this->Rate_plan_model->create_rate_plan($rate_plan);
            $this->Booking_model->update_booking($booking_id, array('rate_plan_id' => $rate_plan_id));

            $post_booking_data = array('rate_plan_id' => $rate_plan_id);
            $post_booking_data['booking_id'] = $booking_id;
            $post_booking_data['company_id'] = $this->company_id;

            do_action('post.update.booking', $post_booking_data);

            $response['rate_plan_id'] = $rate_plan_id;
            $response['rate_plan_name'] = $rate_plan['rate_plan_name'];

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

        //Create a corresponding invoice
        $this->Invoice_model->create_invoice($booking_id);
        $this->Booking_room_history_model->create_booking_room_history(
            array(
                "booking_id" => $booking_id,
                "check_in_date" => $room['check_in_date'],
                "check_out_date" => $room['check_out_date'],
                "room_id" => $room['room_id'],
                "room_type_id" => $room['room_type_id']
            )
        );

        $post_booking_data['check_in_date'] =  $room['check_in_date'];
        $post_booking_data['check_out_date'] = $room['check_out_date'];
        $post_booking_data['room_id'] = $room['room_id'];
        $post_booking_data['room_type_id'] =  $room['room_type_id'];
        $post_booking_data['company_id'] = $this->company_id;
        do_action('post.create.booking', $post_booking_data);

        if (isset($customer_data['staying_customers'])) {
            $staying_customers = $customer_data['staying_customers'];
            $staying_customer_ids = Array();
            foreach ($staying_customers as $customer) {
                if (isset($customer['customer_id'])) {
                    $staying_customer_ids[] = $customer['customer_id'];
                } else {
                    $staying_customer_id = $this->Customer_model->create_customer(
                        Array(
                            'company_id' => $this->company_id,
                            'customer_name' => $customer['customer_name']
                        )
                    );

                    $post_customer_data = $customer;
                    $post_customer_data['customer_id'] = $staying_customer_id;

                    do_action('post.create.customer', $post_customer_data);

                    $staying_customer_ids[] = $staying_customer_id;
                }
            }
            $this->Customer_model->create_staying_customers($booking_id, $staying_customer_ids);
        }

        if($is_duplicate == 'true')
        {
            $this->_create_booking_log($booking_id, "Duplicate booking created from booking ".$old_booking_id);
        }
        else
        {
            $this->_create_booking_log($booking_id, "Booking created");
        }

        
        if(isset($booking_data['state']) && $booking_data['state'] == INHOUSE){
            $this->_create_booking_log($booking_id, 1, 5);
        }
                
        $response['booking_id'] = $booking_id;
        return $response;
    }

    function _create_group_booking($booking_data, $data, $room, $available_rooms, $booking_group_id, $custom_booking_fields) {

        if ($room['room_count'] == 0) {
            return array();
        }

        $customer_data = $data['customers'];

        $booking_batch = array();

        for ($i = 0; $i < $room['room_count']; $i++) {
            $room['room_id'] = $available_rooms[$i]['room_id'];

            $booking_batch[$i] = $booking_data;
            $booking_batch[$i]['adult_count'] = $room['adult_count'];
        }
        $booking_ids = $this->Booking_model->insert_booking_batch($booking_batch);

        $post_booking_batch_data = $booking_batch;
        $post_booking_batch_data['booking_ids'] = $booking_ids;
        $post_booking_batch_data['company_id'] = $this->company_id;



        for ($i = 0; $i < $room['room_count']; $i++) {
            if(isset($booking_data['use_rate_plan']) && $booking_data['use_rate_plan'])
            {
                $end_date = date('Y-m-d', strtotime($room['check_out_date']));
                $start_date = date('Y-m-d', strtotime($room['check_in_date']));
                $datetime1 = date_create($start_date);
                $datetime2 = date_create($end_date);
                $interval = date_diff($datetime1, $datetime2);
                $days = $interval->format('%a');

                $this->load->library('rate');
                $raw_rate_array = $this->rate->get_rate_array($booking_data['rate_plan_id'], $start_date, $end_date, $booking_data['adult_count'], $booking_data['children_count']);

                $rate_array = array();
                foreach ($raw_rate_array as $rate)
                {


                    if($this->is_group_booking_features){
                        if(
                            isset($rate['adult_1_rate']) && 
                            $rate['adult_1_rate'] &&
                            $booking_data['rate'] != $rate['adult_1_rate']
                        ){
                            $rate['adult_1_rate'] = $booking_data['rate'];
                        }

                        if(
                            isset($rate['adult_2_rate']) && 
                            $rate['adult_2_rate'] &&
                            $booking_data['rate'] != $rate['adult_2_rate']
                        ){
                            $rate['adult_2_rate'] = $booking_data['rate'];
                        }

                        if(
                            isset($rate['adult_3_rate']) && 
                            $rate['adult_3_rate'] &&
                            $booking_data['rate'] != $rate['adult_3_rate']
                        ){
                            $rate['adult_3_rate'] = $booking_data['rate'];
                        }

                        if(
                            isset($rate['adult_4_rate']) && 
                            $rate['adult_4_rate'] &&
                            $booking_data['rate'] != $rate['adult_4_rate']
                        ){
                            $rate['adult_4_rate'] = $booking_data['rate'];
                        }
                    }

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

                $curreny_data = $this->Currency_model->get_default_currency($this->company_id);
                $rate_plan_data = $this->Rate_plan_model->get_rate_plan($booking_data['rate_plan_id']);
                $rate_plan = array(
                    "rate_plan_name" => $rate_plan_data['rate_plan_name']." #".$booking_ids[$i],
                    "number_of_adults_included_for_base_rate" => $booking_data['adult_count'],
                    "rates" => get_array_with_range_of_dates($rate_array),
                    "currency_id" => $rate_plan_data['currency_id'],
                    "charge_type_id" => $rate_plan_data['charge_type_id'],
                    "company_id" => $this->company_id,
                    "is_selectable" => '0',
                    "room_type_id" => $room['room_type_id'],
                    "parent_rate_plan_id" => $booking_data['rate_plan_id'],
                    "policy_code"=> $rate_plan_data['policy_code']
                );

                // create rates
                $rates = $rate_plan['rates'];
                unset($rate_plan['rates']);

                // create rate plan
                $rate_plan_id = $this->Rate_plan_model->create_rate_plan($rate_plan);
                $this->Booking_model->update_booking($booking_ids[$i], array('rate_plan_id' => $rate_plan_id));

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
        }






        $booking_history_batch = array();

        for ($i = 0; $i < $room['room_count']; $i++) {
            $room_id = $available_rooms[$i]['room_id'];

            $booking_history_batch[$i] = array(
                "booking_id" => $booking_ids[$i],
                "check_in_date" => $room['check_in_date'],
                "check_out_date" => $room['check_out_date'],
                "room_id" => $room_id,
                "room_type_id" => $room['room_type_id']
            );
        }

        $this->Booking_room_history_model->insert_booking_room_history_batch($booking_history_batch);
        $post_booking_data['group_booking'] = $booking_history_batch;

        do_action('post.create.booking', $post_booking_batch_data);


        //Create a corresponding invoice
        $this->Invoice_model->create_batch_invoice($booking_ids);


        if (isset($customer_data['staying_customers'])) {
            $staying_customers = $customer_data['staying_customers'];
            $staying_customer_ids = Array();
            foreach ($staying_customers as $customer) {
                if (isset($customer['customer_id'])) {
                    $staying_customer_ids[] = $customer['customer_id'];
                } else {
                    $staying_customer_id = $this->Customer_model->create_customer(
                        Array(
                            'company_id' => $this->company_id,
                            'customer_name' => $customer['customer_name']
                        )
                    );

                    $post_customer_data = $customer;
                    $post_customer_data['customer_id'] = $staying_customer_id;

                    do_action('post.create.customer', $post_customer_data);

                    $staying_customer_ids[] = $staying_customer_id;
                }
            }
            $this->Customer_model->create_staying_customers_batch($booking_ids, $staying_customer_ids);
        }

        $this->_create_booking_log_batch($booking_ids, "Booking created");


        if (isset($booking_group_id)) { // linked group booking
            // insert booking_id in booking_x_booking_linked_group at group id
            $booking_group_data = array();
            foreach ($booking_ids as $booking_id) {
                $booking_group_data[] = array(
                    'booking_id' => $booking_id,
                    'booking_group_id' => $booking_group_id
                );
            }

            $this->Booking_linked_group_model->insert_booking_x_booking_linked_group_batch($booking_group_data);
        }

        $response = array();
        foreach ($booking_ids as $booking_id) {
            $balance = $this->Booking_model->update_booking_balance($booking_id);
            if(!$this->is_total_balance_include_forecast)
            {
                $balance = 0;
            }

            $room_type_id = $room['room_type_id'];
            $response[] = array(
                            'booking_id' => intval($booking_id),
                            'balance' => $balance, 
                            'room_type_id' => intval($room_type_id),
                            'check_in_date' => $room['check_in_date'],
                            'check_out_date' => $room['check_out_date']
                        );

            $this->Booking_model->create_booking_fields($booking_id, $custom_booking_fields);
        }

        if($this->is_group_booking_features == true){
            $total_room_count += $room['room_count'];
            $total_guest_count += $room['adult_count'];

            $total_guest_count = $total_guest_count * $total_room_count;

            $option_data = array(
                'company_id' => $this->company_id,
                'option_name' => 'group_booking_total_counts_'.$booking_group_id,
                'option_value' => json_encode(
                    array(
                        'room_type_id' => intval($room_type_id),
                        'room_count' => $total_room_count,
                        'guest_count' => $total_guest_count
                    )
                )
            );

            $this->Option_model->add_option($option_data);
        }

        return $response;
    }

    function update_booking_AJAX() {
        $response = array();

        $booking_id = sqli_clean($this->security->xss_clean($this->input->post('booking_id')));
        $new_data = $this->input->post('data');
        $custom_booking_fields = isset($new_data['custom_booking_fields']) ? $new_data['custom_booking_fields'] : array();
        //unset($new_data['custom_booking_fields']);

        $new_state = isset($new_data['booking']['state']) ? $new_data['booking']['state'] : null;

        $cancelled_group_booking = $this->input->post('group_booking_cancellation');
        $brh_data = array();

        $payment_details = $this->Payment_model->get_payments($booking_id);
        $booking_existing_data = $this->Booking_model->get_booking($booking_id);

        $final_amount = 0;

        if($new_state == 4 && $payment_details)
        {
            foreach($payment_details as $payment)
            {
                $final_amount += $payment['amount'];
            }

            if(isset($final_amount) && ($booking_existing_data['balance'] && $booking_existing_data['balance_without_forecast']) && !$this->booking_cancelled_with_balance)
            {
                echo json_encode(array('response' => 'failure', 'message' => l('A payment has been made to the room(s). Please refund or delete the payment record and then cancel the reservation', true)));
                return;
            }
        }

        // convert forecast charges into custom charges for hourly bookings

        if(
            isset($new_data['number_of_days']) && 
            $new_data['number_of_days'] == 0 &&
            $new_state == CHECKOUT
        )
        {

            // check if payment is done or not
            if(empty($payment_details))
            {
                if(($booking_existing_data['balance'] || $booking_existing_data['balance_without_forecast']) && $this->restrict_checkout_with_balance)
                {
                    echo json_encode(array('response' => 'failure', 'message' => l('You are unable to checkout with a balance on the invoice', true)));
                    return;
                }
            } else {

                $final_amount = 0;
                foreach($payment_details as $payment)
                {
                    $final_amount += $payment['amount'];
                }

                if($booking_existing_data['rate'] != $final_amount){
                    echo json_encode(array('response' => 'failure', 'message' => l('You are unable to checkout with a balance on the invoice', true)));
                    return;
                }
            }

            $charge_data = Array(
                            'amount' => $new_data['booking']['rate'],
                            'booking_id' => $booking_id,
                            'date_time' => gmdate('Y-m-d H:i:s'),
                            'customer_id' => isset($new_data['customers']['paying_customer']['customer_id']) ? $new_data['customers']['paying_customer']['customer_id'] : null,
                            'user_id' => $this->user_id,
                            'pay_period' => DAILY,
                            'is_night_audit_charge' => 0
                        );

            $charge_data['selling_date'] = date('Y-m-d', strtotime($this->selling_date));

            if(
                isset($new_data['rooms'][0]['use_rate_plan']) && 
                $new_data['rooms'][0]['use_rate_plan']
            ) {
                $charge_data['description'] = "Hourly Booking Charge #".$booking_id;

                $rp_data = $this->Rate_plan_model->get_rate_plan($new_data['rooms'][0]['rate_plan_id']);
                $charge_data['charge_type_id'] = $rp_data['charge_type_id'];

            } else {
                $charge_data['description'] = "Hourly Booking Charge";
                $charge_data['charge_type_id'] = $new_data['rooms'][0]['charge_type_id'];
            }

            $last_hourly_charge = $this->Charge_model->get_last_applied_charge($booking_id, $charge_data['charge_type_id'], null, false);

            if(empty($last_hourly_charge)){

                $charge_id = $this->Charge_model->insert_charge($charge_data);

                //invoice log 
                $this->load->model('Invoice_log_model');
                $invoice_log_data = array();
                $invoice_log_data['date_time'] = gmdate('Y-m-d h:i:s');
                $invoice_log_data['booking_id'] = $booking_id;
                $invoice_log_data['user_id'] = $this->user_id;
                $invoice_log_data['action_id'] = ADD_CHARGE;
                $invoice_log_data['charge_or_payment_id'] = $charge_id;
                $invoice_log_data['new_amount'] = $charge_data['amount'];
                // Added item description in invoice log
                $invoice_log_data['log'] = 'Charge Added';

                $this->Invoice_log_model->insert_log($invoice_log_data);
            }
            $charge_data = array();

            $this->Booking_model->update_booking_balance($booking_id);
        }

        if(
            isset($new_data['number_of_days']) && 
            $new_data['number_of_days'] == 0 &&
            $new_state == INHOUSE
        ) {
            $company_id = $this->company_id;
            $company_time_zone = $this->Company_model->get_time_zone($company_id);
            
            $actual_time = convert_to_local_time(new DateTime(), $company_time_zone)->format("H:i:s");

            $booking_check_in_date = explode(' ', $new_data['rooms'][0]['check_in_date']);
            $booking_check_in_time = $booking_check_in_date[1];

            $booking_check_out_date = explode(' ', $new_data['rooms'][0]['check_out_date']);
            $booking_check_out_time = $booking_check_out_date[1];

            if (
                    $booking_check_in_time <= $actual_time && 
                    $booking_check_out_time >= $actual_time
            ) {

            } else if($actual_time > $booking_check_out_time) {
                echo json_encode(array('response' => 'failure', 'message' => l("The check-in time has already passed, please adjust the time to enable check-in", true)));
                return;
            } else {
                echo json_encode(array('response' => 'failure', 'message' => l("You can't check in as of now. It's not yet time for the booking to be checked in.", true)));
                return;
            }
        }

        if (isset($cancelled_group_booking) && $cancelled_group_booking == 'cancelled') {
            $booking['state'] = $new_data['booking']['state'];
            // update booking data
            $this->Booking_model->update_booking($booking_id, $booking);

            $post_booking_data = $booking;
            $post_booking_data['booking_id'] = $booking_id;

            do_action('post.update.booking', $post_booking_data);


            $this->_create_booking_log($booking_id, "Booking cancelled");
            if(isset($this->automatic_email_cancellation) && $this->automatic_email_cancellation) {
                $this->send_booking_cancellation_email($booking_id);
            }

        } elseif (isset($new_data['booking']['update_date']) && $new_data['booking']['update_date'] == true) {

            if (isset($new_data['booking']['rooms'][0])) {
                $room = $new_data['booking']['rooms'][0];
                if(isset($room['use_rate_plan']) && $room['use_rate_plan'])
                {
                    if($booking_existing_data['rate_plan_id'] != $room['rate_plan_id'])
                    {
                        $end_date = date('Y-m-d', strtotime($room['check_out_date']));
                        $start_date = date('Y-m-d', strtotime($room['check_in_date']));
                        $datetime1 = date_create($start_date);
                        $datetime2 = date_create($end_date);
                        $interval = date_diff($datetime1, $datetime2);
                        $days = $interval->format('%a');

                        $this->load->library('rate');
                        $raw_rate_array = $this->rate->get_rate_array($booking_existing_data['rate_plan_id'], $start_date, $end_date, $booking_existing_data['adult_count'], $booking_existing_data['children_count']);
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

                        $curreny_data = $this->Currency_model->get_default_currency($this->company_id);
                        $rate_plan_data = $this->Rate_plan_model->get_rate_plan($room['rate_plan_id']);
                        $rate_plan = array(
                            "rate_plan_name" => $rate_plan_data['rate_plan_name']." #".$booking_id,
                            "number_of_adults_included_for_base_rate" => $booking_existing_data['adult_count'],
                            "rates" => get_array_with_range_of_dates($rate_array),
                            "currency_id" => $curreny_data['currency_id'],
                            "charge_type_id" => $rate_plan_data['charge_type_id'],
                            "company_id" => $this->company_id,
                            "is_selectable" => '0',
                            "room_type_id" => $room['room_type_id'],
                            "parent_rate_plan_id" => $room['rate_plan_id'],
                            "policy_code"=> $rate_plan_data['policy_code']
                        );

                        // create rates
                        $rates = $rate_plan['rates'];
                        unset($rate_plan['rates']);

                        // create rate plan
                        $rate_plan_id = $this->Rate_plan_model->create_rate_plan($rate_plan);
                        $booking['rate_plan_id'] = $rate_plan_id;
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
                    else
                    {

                        $rate_plan_id = $booking_existing_data['rate_plan_id'];
                        $rate_plan = $this->Rate_plan_model->get_rate_plan($rate_plan_id);

                        $parent_rate_plan_id = $rate_plan['parent_rate_plan_id'];
                        $date_start = $new_data['booking']['check_in_date'];
                        $date_end = $new_data['booking']['check_out_date'];
                        $adult_count = $booking_existing_data['adult_count'];
                        $children_count = $booking_existing_data['children_count'];

                        $this->load->library('rate');
                        $rate_array = $this->rate->get_rate_array($parent_rate_plan_id, $date_start, $date_end, $adult_count, $children_count);

                        foreach ($rate_array as $rate)
                        {
                            if(!(date("Y-m-d", strtotime($booking_existing_data['check_in_date'])) <= $rate['date'] && $rate['date'] < date("Y-m-d", strtotime($booking_existing_data['check_out_date']))))
                            {
                                $rate_id = $this->Rate_model->create_rate(
                                    Array(
                                        'rate_plan_id' => $rate_plan_id,
                                        'base_rate' => $rate['base_rate'],
                                        'adult_1_rate' => $rate['adult_1_rate'] ? $rate['adult_1_rate'] : 0,
                                        'adult_2_rate' => $rate['adult_2_rate'] ? $rate['adult_2_rate'] : 0,
                                        'adult_3_rate' => $rate['adult_3_rate'] ? $rate['adult_3_rate'] : 0,
                                        'adult_4_rate' => $rate['adult_4_rate'] ? $rate['adult_4_rate'] : 0,
                                        'additional_adult_rate' => $rate['additional_adult_rate'] ? $rate['additional_adult_rate'] : 0,
                                        'additional_child_rate' => $rate['additional_child_rate'] ? $rate['additional_child_rate'] : 0,
                                        'minimum_length_of_stay' => $rate['minimum_length_of_stay'] ? $rate['minimum_length_of_stay'] : 0,
                                        'maximum_length_of_stay' => $rate['maximum_length_of_stay'] ? $rate['maximum_length_of_stay'] : 0,
                                        'minimum_length_of_stay_arrival' => $rate['minimum_length_of_stay_arrival'] ? $rate['minimum_length_of_stay_arrival'] : 0,
                                        'maximum_length_of_stay_arrival' => $rate['maximum_length_of_stay_arrival'] ? $rate['maximum_length_of_stay_arrival'] : 0
                                    )
                                );

                                $date_range_id = $this->Date_range_model->create_date_range(
                                    Array(
                                        'date_start' => $rate['date'],
                                        'date_end' => $rate['date']
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

                    }
                }
            }
            if (!empty($new_data['booking']['room_booking_ar'])) {
                $room_booking_ar = $new_data['booking']['room_booking_ar'];
                foreach ($room_booking_ar as $room_booking) {
                    $brh_data['booking_id'] = $room_booking['bookingId'];
                    $brh_data['room_id'] = $room_booking['roomId'];
                    $brh_data['check_in_date'] = $brh_data['booking_room_history_id'] = '';

                    $existing_data = $this->Booking_room_history_model->get_booking_detail($booking_id);

                    if ($this->Booking_room_history_model->get_booking_block_count($room_booking['bookingId']) < 2) {
                        $this->Booking_room_history_model->update_check_out_date($brh_data, $new_data['booking']['check_out_date']);
                        $this->Booking_room_history_model->update_check_in_date($brh_data, $new_data['booking']['new_check_in_date']);
                    } else {
                        // get last inserted booking room history id 
                        $booking_room_history_id = $this->Booking_room_history_model->get_last_inserted_booking_room_history_id($room_booking['bookingId']);
                        $brh_data['booking_room_history_id'] = isset($booking_room_history_id) ? $booking_room_history_id : '';
                        $this->Booking_room_history_model->update_check_out_date($brh_data, $new_data['booking']['check_out_date']);
                        $this->Booking_room_history_model->update_check_in_date($brh_data, $new_data['booking']['new_check_in_date']);
                    }

                    $destination = array(
                        'booking_id' => $room_booking['bookingId'],
                        'check_in_date' => $new_data['booking']['new_check_in_date'],
                        'check_out_date' => $new_data['booking']['check_out_date']
                    );
                    $changes = $this->_generate_log_of_differences_between_new_booking_data_and_database($room_booking['bookingId'], $destination, $existing_data);
                    if($changes) {
                        $this->_create_booking_log($room_booking['bookingId'], $changes);
                    }
                }
            }
        } else {
            // hack to make booking log work for change in booking blocks (room_id, check-in/out dates)
            $old_data['booking'] = $this->Booking_model->get_booking_detail($booking_id);
            $old_data['customers']['paying_customer'] = "";
            if (isset($old_data['booking']['booking_customer_id'])) {
                $old_data['customers']['paying_customer'] = $this->Customer_model->get_customer($old_data['booking']['booking_customer_id']);
            }
            $old_data['customers']['staying_customers'] = $this->Customer_model->get_staying_customers($booking_id, $this->company_id);
            $old_data['booking_block'] = $this->Booking_room_history_model->get_booking_detail($booking_id);
            $old_data['booking_extras'] = $this->Booking_extra_model->get_booking_extras($booking_id, $this->company_id);
            $company_detail = $this->company_data; //$this->Company_model->get_company($this->company_id);
            // validation
            $errors = $this->_validate_booking_data($new_data, $old_data, $booking_id, $company_detail);

            // log generation
            if (!empty($errors)) {
                echo json_encode(
                    array(
                        'errors' => $errors
                    )
                );
                return;
            }

            // update paying customer
            if (isset($new_data['customers']['paying_customer']) &&
                $new_data['customers']['paying_customer'] != '') {
                $paying_customer = $new_data['customers']['paying_customer'];
                if (isset($paying_customer['customer_id'])) {
                    $new_data['booking']['booking_customer_id'] = $paying_customer['customer_id'];
                } else {
                    $paying_customer_id = $this->Customer_model->create_customer(
                        Array(
                            'company_id' => $this->company_id,
                            'customer_name' => $paying_customer['customer_name']
                        )
                    );

                    $post_customer_data = $paying_customer;
                    $post_customer_data['customer_id'] = $paying_customer_id;

                    do_action('post.create.customer', $post_customer_data);

                    $new_data['booking']['booking_customer_id'] = $paying_customer_id;
                }
            } else {
                $new_data['booking']['booking_customer_id'] = 0;
            }


            // update staying customers
            $this->Customer_model->delete_all_staying_customers($booking_id);
            if (isset($new_data['customers']['staying_customers'])) {
                $staying_customers = $new_data['customers']['staying_customers'];
                $staying_customer_ids = Array();
                foreach ($staying_customers as $customer) {
                    if (isset($customer['customer_id'])) {
                        $staying_customer_ids[] = $customer['customer_id'];
                    } else {
                        $staying_customer_id = $this->Customer_model->create_customer(
                            Array(
                                'company_id' => $this->company_id,
                                'customer_name' => $customer['customer_name']
                            )
                        );

                        $post_customer_data = $customer;
                        $post_customer_data['customer_id'] = $staying_customer_id;

                        do_action('post.create.customer', $post_customer_data);

                        $staying_customer_ids[] = $staying_customer_id;
                    }
                }
                $this->Customer_model->create_staying_customers($booking_id, $staying_customer_ids);
            }

            // updating room info
            if (isset($new_data['rooms'][0])) {
                $room = $new_data['rooms'][0];

                $booking = $new_data['booking'];
                $booking['rate'] = $room['rate'];
                $booking['use_rate_plan'] = $room['use_rate_plan'];
                $booking['rate_plan_id'] = isset($room['rate_plan_id']) ? $room['rate_plan_id'] : NULL;
                $booking['charge_type_id'] = isset($room['charge_type_id']) ? $room['charge_type_id'] : NULL;

                $booking['booked_by'] = isset($new_data['booking']['booked_by']) && $new_data['booking']['booked_by'] ? $new_data['booking']['booked_by'] : NULL;

                $booking_existing_data = $this->Booking_model->get_booking($booking_id);

                // if rate plan changes then create new rate plan
                if(isset($room['use_rate_plan']) && $room['use_rate_plan'])
                {
                    if($booking_existing_data['rate_plan_id'] != $room['rate_plan_id'])
                    {
                        $end_date = date('Y-m-d', strtotime($room['check_out_date']));
                        $start_date = date('Y-m-d', strtotime($room['check_in_date']));
                        $datetime1 = date_create($start_date);
                        $datetime2 = date_create($end_date);
                        $interval = date_diff($datetime1, $datetime2);
                        $days = $interval->format('%a');

                        $this->load->library('rate');
                        $raw_rate_array = $this->rate->get_rate_array($booking['rate_plan_id'], $start_date, $end_date, $booking['adult_count'], $booking['children_count']);
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

                        $curreny_data = $this->Currency_model->get_default_currency($this->company_id);
                        $rate_plan_data = $this->Rate_plan_model->get_rate_plan($booking['rate_plan_id']);
                        $rate_plan = array(
                            "rate_plan_name" => $rate_plan_data['rate_plan_name']." #".$booking_id,
                            "number_of_adults_included_for_base_rate" => $booking['adult_count'],
                            "rates" => get_array_with_range_of_dates($rate_array),
                            "currency_id" => $curreny_data['currency_id'],
                            "charge_type_id" => $rate_plan_data['charge_type_id'],
                            "company_id" => $this->company_id,
                            "is_selectable" => '0',
                            "room_type_id" => $room['room_type_id'],
                            "parent_rate_plan_id" => $booking['rate_plan_id'],
                            "policy_code"=> $rate_plan_data['policy_code']
                        );

                        // create rates
                        $rates = $rate_plan['rates'];
                        unset($rate_plan['rates']);

                        // create rate plan
                        $rate_plan_id = $this->Rate_plan_model->create_rate_plan($rate_plan);
                        $booking['rate_plan_id'] = $rate_plan_id;
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
                    else
                    {

                        $rate_plan_id = $booking_existing_data['rate_plan_id'];
                        $rate_plan = $this->Rate_plan_model->get_rate_plan($rate_plan_id);

                        $parent_rate_plan_id = $rate_plan['parent_rate_plan_id'];
                        $date_start = $room['check_in_date'];
                        $date_end = $room['check_out_date'];
                        $adult_count = $booking['adult_count'];
                        $children_count = $booking['children_count'];

                        if (!($old_data['booking']['check_in_date'] == $room['check_in_date'] && $old_data['booking']['check_out_date'] == $room['check_out_date'])) {

                            $this->load->library('rate');
                            $rate_array = $this->rate->get_rate_array($parent_rate_plan_id, $date_start, $date_end, $adult_count, $children_count);

                            foreach ($rate_array as $rate)
                            {
                                if(!(date("Y-m-d", strtotime($old_data['booking']['check_in_date'])) <= $rate['date'] && $rate['date'] < date("Y-m-d", strtotime($old_data['booking']['check_out_date']))))
                                {
                                    $rate_id = $this->Rate_model->create_rate(
                                        Array(
                                            'rate_plan_id' => $rate_plan_id,
                                            'base_rate' => $rate['base_rate'],
                                            'adult_1_rate' => $rate['adult_1_rate'] ? $rate['adult_1_rate'] : 0,
                                            'adult_2_rate' => $rate['adult_2_rate'] ? $rate['adult_2_rate'] : 0,
                                            'adult_3_rate' => $rate['adult_3_rate'] ? $rate['adult_3_rate'] : 0,
                                            'adult_4_rate' => $rate['adult_4_rate'] ? $rate['adult_4_rate'] : 0,
                                            'additional_adult_rate' => $rate['additional_adult_rate'] ? $rate['additional_adult_rate'] : 0,
                                            'additional_child_rate' => $rate['additional_child_rate'] ? $rate['additional_child_rate'] : 0,
                                            'minimum_length_of_stay' => $rate['minimum_length_of_stay'] ? $rate['minimum_length_of_stay'] : 0,
                                            'maximum_length_of_stay' => $rate['maximum_length_of_stay'] ? $rate['maximum_length_of_stay'] : 0,
                                            'minimum_length_of_stay_arrival' => $rate['minimum_length_of_stay_arrival'] ? $rate['minimum_length_of_stay_arrival'] : 0,
                                            'maximum_length_of_stay_arrival' => $rate['maximum_length_of_stay_arrival'] ? $rate['maximum_length_of_stay_arrival'] : 0
                                        )
                                    );

                                    $date_range_id = $this->Date_range_model->create_date_range(
                                        Array(
                                            'date_start' => $rate['date'],
                                            'date_end' => $rate['date']
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
                        }
                    }
                }

                // update booking data
                $this->Booking_model->update_booking($booking_id, $booking);

                $post_booking_data = $booking;
                $post_booking_data['booking_id'] = $booking_id;

                $block = array(
                    "booking_id" => $booking_id,
                    "check_in_date" => $room['check_in_date'],
                    "check_out_date" => $room['check_out_date'],
                    "room_id" => $room['room_id'],
                    "room_type_id" => $room['room_type_id']
                );

                $new_room_id = $block['room_id'];
                $new_room_type_id = $block['room_type_id'];

                // delete all blocks that has check-in-date greater than new checkout-date 
                // delete all blocks that have checkin and chekout date lesser than booking's final checkin date
                // because all the new blocks must have check-in-date less than final checkout-date
                $this->Booking_room_history_model->delete_extra_booking_blocks($booking_id, $room['check_out_date'], $room['check_in_date']);

                $latest_block = $this->Booking_room_history_model->get_latest_booking_room_history($booking_id);

                if (empty($latest_block)) {
                    // if for some reason, the booking never had a block set (most likely caused by bug)
                    // create an empty block for latest block, so we can still recover the corrupted booking
                    $latest_block = array(
                        'booking_id' => '',
                        'room_id' => '',
                        'room_type_id' => '',
                        'check_in_date' => '',
                        'check_out_date' => ''
                    );
                }

                // If room has been changed
                if ($latest_block['room_id'] != $new_room_id) {

                    // If the guest is checking out today, then we should neither split booking or change room of the block.
                    // (The checkout date of the latest block shouldn't be same as current selling date.)
                    // Because, otherwise, the newly created booking block will have same check-in and check-out date
                    // However, if the user is EXTENDING the checkout date, then split booking
                    if (strtotime($latest_block['check_out_date']) != strtotime($this->selling_date) ||
                        strtotime($latest_block['check_out_date']) < strtotime($block['check_out_date'])
                    ) {
                        // If the booking's check-in date is before selling_date,
                        //  divide the booking-room history into two blocks: 
                        //  Head block with range [check-in date, selling_date]
                        //  Tail block with range [selling_date, check out date]
                        // SPLIT: Check if latest brh block (The tail block if splitted) is before current selling date
                        if (strtotime($latest_block['check_in_date']) < strtotime($this->selling_date)) {
                            if (strtotime($block['check_out_date']) > strtotime($this->selling_date))
                            {
                                // modify the head block
                                $this->Booking_room_history_model->update_check_out_date($latest_block, $this->selling_date);

                                // create a tail block
                                $new_booking_block = array();
                                $new_booking_block['booking_id'] = $booking_id;
                                $new_booking_block['room_id'] = $new_room_id;
                                $new_booking_block['room_type_id'] = $block['room_type_id'];
                                $new_booking_block['check_in_date'] = $this->selling_date;
                                $new_booking_block['check_out_date'] = $block['check_out_date'];
                                $this->Booking_room_history_model->create_booking_room_history($new_booking_block);

                                
                                $post_booking_data['room_id'] = $new_room_id;;
                                $post_booking_data['room_type_id'] = $block['room_type_id'];
                                $post_booking_data['check_in_date'] = $this->selling_date;
                                $post_booking_data['check_out_date'] = $block['check_out_date'];
                                do_action('post.update.booking', $post_booking_data);
                            }
                            else
                            {
                                // modify the head block
                                $this->Booking_room_history_model->update_check_out_date($latest_block, $block['check_out_date']);
                            }
                        }
                        // NO SPLIT: check-in date is after current selling date. Freely update the booking around without splitting.
                        else {
                            $this->Booking_room_history_model->update_room_id($latest_block, $new_room_id, $new_room_type_id);
                            $latest_block['room_id'] = $new_room_id; // because room_id's been modified
                            // only change check in date when editing reservation
                            if ($new_data['booking']['state'] == RESERVATION ||
                                $old_data['booking']['state'] == RESERVATION) {
                                // only change check-in-date if there's single booking block for this booking
                                if ($this->Booking_room_history_model->get_booking_block_count($booking_id) < 2) {
                                    $new_check_in_date = $block['check_in_date'];
                                    $this->Booking_room_history_model->update_check_in_date($latest_block, $new_check_in_date); // used for changing reservation's check in date. Irrelevant for inhouse guests
                                    $latest_block['check_in_date'] = $new_check_in_date; // because check_in_date's been modified                           
                                }
                            }
                            $this->Booking_room_history_model->update_check_out_date($latest_block, $block['check_out_date']);
                        }
                    } else { // user attempted to change the room while its checkout date is current selling date
                        $response['warning'] = "Room did not change, because the guest is checking out today";
                    }

                    $this->_combine_booking_blocks($booking_id); // combine booking blocks if plausible

                } else { // room has not been changed. Hence, just update check-in and check-out date

                    // only change check in date when editing reservation
                    if (
                        $old_data['booking']['state'] == RESERVATION
                    ) {
                        // only change check-in-date if there's single booking block for this booking
                        if ($this->Booking_room_history_model->get_booking_block_count($booking_id) < 2) {
                            $new_check_in_date = $block['check_in_date'];
                            $this->Booking_room_history_model->update_check_in_date($latest_block, $new_check_in_date); // used for changing reservation's check in date. Irrelevant for inhouse guests
                            $latest_block['check_in_date'] = $new_check_in_date; // because check_in_date's been modified                           
                        }
                    }
                    $this->Booking_room_history_model->update_check_out_date($latest_block, $block['check_out_date']);
                }
            }

            $this->_generate_logs($new_data, $old_data);
        }

        if(isset($new_data['booking']['pay_period']))
        {
            if($new_data['booking']['pay_period'] == ONE_TIME && $new_data['booking']['state'] == INHOUSE)
            {
                // create charge 
                $room_charge_type_id = $this->Charge_type_model->get_room_charge_type_id($booking_id);
                $last_room_charge = $this->Charge_model->get_last_applied_charge($booking_id, $room_charge_type_id, null, true);
                if(empty($last_room_charge))
                {
                    $charge_data = array();
                    $charge_data[] = Array(
                        'description' => "One Time Charge",
                        'charge_type_id' => $room_charge_type_id,
                        'amount' => $new_data['booking']['rate'],
                        'booking_id' => $booking_id,
                        'user_id' => isset($new_data['customers']['paying_customer']['customer_id']) ? $new_data['customers']['paying_customer']['customer_id'] : null,
                        'pay_period' => ONE_TIME,
                        'is_night_audit_charge' => 1
                    );
                    $this->Charge_model->insert_charges($this->company_id, $charge_data);

                    $post_charge_data = $charge_data;
                    $post_charge_data['company_id'] = $this->company_id;

                    do_action('post.create.charge', $post_charge_data);
                }
            }
        }


        if (
            isset($this->automatic_email_cancellation) && $this->automatic_email_cancellation &&
            isset($booking['state']) && isset($booking_existing_data['state']) &&
            $booking['state'] != $booking_existing_data['state'] &&
            $booking['state'] == CANCELLED
        ) {
            $this->send_booking_cancellation_email($booking_id);
        }

        $return_type = $this->is_total_balance_include_forecast ? 'balance' : 'balance_without_forecast';
        $balance = $this->Booking_model->update_booking_balance($booking_id, $return_type);
        $response['balance'] = $balance;
        if(is_numeric($new_state) && $new_state == '5')
        {
            $new_checkin_date = $new_data['rooms'][0]['check_in_date'];
            $new_checkout_date = date('Y-m-d', strtotime($new_checkin_date . ' +1 day'));
            // update booking for one day
            $this->Booking_room_history_model->update_noshow_booking($booking_id, $new_checkin_date, $new_checkout_date);
        }

        if ($custom_booking_fields) {
            $this->Booking_model->update_booking_fields($booking_id, $custom_booking_fields);
        }

        if(strtotime($new_data['rooms'][0]['check_in_date']) > strtotime($booking_existing_data['check_in_date'])) {
            $start_date = $booking_existing_data['check_in_date'];
        } else {
            $start_date = $new_data['rooms'][0]['check_in_date'];
        }

        if(strtotime($new_data['rooms'][0]['check_out_date']) > strtotime($booking_existing_data['check_out_date'])) {
            $end_date = $new_data['rooms'][0]['check_out_date'];
        } else {
            $end_date = $booking_existing_data['check_out_date'];
        }

        $this->Booking_model->update_booking($booking_id, array('state' => $new_state));

        $update_availability_data = array(
                        'start_date' => $start_date,
                        'end_date' => $end_date,
                        'room_type_id' => $new_data['rooms'][0]['room_type_id'],
                        'company_id' => $this->company_id,
                        'update_from' => 'booking_controller'
                    );

        do_action('update_availability', $update_availability_data);
        do_action('update_siteminder_availability', $update_availability_data);

        echo json_encode($response);
    }

    function update_channel_manager() {
        $booking_id = $this->input->post('booking_id');
        $block = $this->Booking_room_history_model->get_block($booking_id);
        if (isset($block)) {
            $data = http_build_query(
                array(
                    'start_date' => $block['check_in_date'],
                    'end_date' => $block['check_out_date'],
                    'company_id' => $this->company_id,
                    'company_api_key' => $this->api_key,
                )
            );

            $req = Requests::post(
                $this->config->item('cm_url') . '/sync/update_rates_and_availability/', array(
                'X-API-KEY' => $this->config->item('api_key')
            ), $data);

            // if response empty aka no availability, ensure actual array is created anyway
            echo json_decode($req->body, true);
        }
    }

    function get_housekeeping_notes_AJAX() {
        $booking_id = $this->input->post('booking_id');
        $booking = $this->Booking_model->get_booking($booking_id);
        echo json_encode($booking['housekeeping_notes']);
    }

    function update_housekeeping_notes_AJAX() {
        $booking_id = $this->input->post('booking_id');
        $housekeeping_notes = $this->input->post('housekeeping_notes');
        $this->Booking_model->update_booking($booking_id, Array('housekeeping_notes' => $housekeeping_notes));
        echo json_encode("success!");
    }

    function get_history_AJAX() {
        $booking_id = $this->input->post('booking_id');

        $log_types = array(
            5 => 'state',
            6 => 'charge type',
            7 => 'adult count',
            8 => 'children count',
            9 => 'rate',
            10 => 'rate plan mode', // use_rate_plan
            11 => 'rate plan',
            12 => 'booking notes',
            13 => 'color',
            14 => 'is deleted',
            15 => 'check-in date',
            16 => 'check-out date',
            17 => 'room',
            18 => 'booking customer name',
            19 => 'staying customers',
            20 => 'pay period',
//            21 => 'New guest name added',
//            22 => 'Guest name deleted',
            23 => 'Booking Source',
            24 => "extra rate created",
            25 => "extra rate deleted"
        );

        $logs = $this->Booking_log_model->get_booking_logs($booking_id);

        foreach ($logs as $index => $log) {

            // apply timezone
            $date = convert_to_local_time(new DateTime($log['date_time'], new DateTimeZone('UTC')), $this->Company_model->get_time_zone($this->company_id)); // Apply time zone
            $logs[$index]['date_time'] = $date->format('Y-m-d H:i:s');

            $log_type = $log['log_type'];
            if ($log_type >= 5) {

                if ($log_type == 5) { // state
                    switch ($log['log']) {
                        case '0': $log['log'] = 'reservation';
                            break;
                        case '1': $log['log'] = 'checked-in';
                            break;
                        case '2': $log['log'] = 'checked-out';
                            break;
                        case '3': $log['log'] = 'out-of-order';
                            break;
                        case '4': $log['log'] = 'cancelled';
                            break;
                        case '5': $log['log'] = 'no-show';
                            break;
                        case '7': $log['log'] = 'unconfirmed';
                            break;
                    }
                } elseif ($log_type == 6) { // charge_type
                    $charge_types = $this->Charge_type_model->get_charge_types($this->company_id);
                        foreach ($charge_types as $charge_type) {
                            if ($charge_type['id'] == $log['log'])
                                $log['log'] = $charge_type['name'];
                        }
                    }
                elseif ($log_type == 10) { // use_rate_plan
                    switch ($log['log']) {
                        case '0': $log['log'] = 'disabled';
                            break;
                        case '1': $log['log'] = 'enabled';
                            break;
                    }
                } elseif ($log_type == 11) { // rate plan
                    $rate_plans = $this->Rate_plan_model->get_rate_plans($this->company_id);
                    foreach ($rate_plans as $rate_plan) {
                        if ($rate_plan['rate_plan_id'] == $log['log'])
                            $log['log'] = $rate_plan['rate_plan_name'];
                    }
                }
                elseif ($log_type == 17) { // room
                    $rooms = $this->Room_model->get_room($log['log']);
                    if(isset($rooms) && $rooms)
                    {
                        $log['log'] = $rooms['room_name'];
                    }
                    if(empty($rooms))
                    {
                        $log['log'] = 'Not Assigned';
                    }
//                    foreach ($rooms as $room) {
//                        if ($room['room_id'] == $log['log'])
//                            $log['log'] = $room['room_name'];
//                      if ($log['log'] == 0)
//                            $log['log'] = 'Not Assigned';
//                  }
                }
                elseif ($log_type == 20) { // payperiod
                    switch ($log['log']) {
                        case '0': $log['log'] = 'nightly';
                            break;
                        case '1': $log['log'] = 'weekly';
                            break;
                        case '2': $log['log'] = 'monthly';
                            break;
                        case '3': $log['log'] = 'one time';
                            break;
                    }
                }
                elseif($log_type == 23){
                    $booking_source = $this->Booking_source_model->get_booking_source_detail($log['log'], $company_id = null);

                    if(!empty($booking_source))
                        $log['log'] = $booking_source[0]['name'];
                }

                if($log_type == 12){
                    $logs[$index]['log'] = $log['log'];
                } elseif($log_type == 18){
                    $logs[$index]['log'] = $log['log'];
                } elseif($log_type == 19){
                    $logs[$index]['log'] = $log['log'];
                }
                elseif($log_type == 24 || $log_type == 25){
                    $booking_extra_id = $log['log'];
                    $booking_extra = $this->Booking_extra_model->get_booking_extra($booking_extra_id);
                    $logs[$index]['log'] = $log_types[$log['log_type']] . " ". $booking_extra->rate;
                }
                else{
                    $logs[$index]['log'] = "Changed " . $log_types[$log['log_type']] . " to " . $log['log'];
                }

            }
        }

        echo json_encode($logs);
    }

    function _validate_booking_data($new_data, $old_data = '', $booking_id = '', $company_detail = array()) {
        $errors = array();

        if(isset($new_data['custom_booking_fields']) && $new_data['custom_booking_fields']) {
            $booking_fields = $this->Booking_field_model->get_booking_fields($this->company_id);

            foreach ($booking_fields as $key => $value) {
                foreach ($new_data['custom_booking_fields'] as $key1 => $value1) {
                    if($value1['id'] == $value['id'] && $value['is_required'] == '1') {
                        if($value1['value'] == '')
                        {
                            $errors[] = $value['name'].' field is required';
                        }
                    }
                }
            }
        }

        if (isset($new_data['rooms'][0]['check_in_date'])) {
            if (!$this->_is_valid_date($new_data['rooms'][0]['check_in_date'])) {
                $errors[] = l('check-in date must be in a valid format', true)." (YYYY-MM-DD)";
            }
        }

        if(isset($company_detail['make_guest_field_mandatory']) && $company_detail['make_guest_field_mandatory'] == '1' && $new_data['booking']['state'] != OUT_OF_ORDER) {
            if($new_data['guests'] == '') {
                $errors[] = l('Guest field is required', true);
            }
        }

        if (isset($new_data['rooms'][0]['check_out_date'])) {
            if (!$this->_is_valid_date($new_data['rooms'][0]['check_out_date'])) {
                $errors[] = l('check-out date must be in a valid format', true)." (YYYY-MM-DD)";
            }
        }

        if (isset($new_data['rooms'][0]['room_id']) && $new_data['rooms'][0]['room_id'] == '') {
            $errors[] = l('Room selection is mandatory', true);
        }

        // validation for existing booking (not new booking)
        if (isset($new_data['booking']['state']) &&
            isset($old_data['booking_block']['check_in_date']) &&
            isset($new_data['rooms']) &&
            $booking_id != ''
        ) {
            if (
                $old_data['booking']['state'] != RESERVATION &&
                ($new_data['booking']['state'] != RESERVATION && $new_data['booking']['state'] != UNCONFIRMED_RESERVATION ) &&
                $new_data['rooms'][0]['check_in_date'] != $old_data['booking_block']['check_in_date']
            ) {
                $errors[] = l("check-in date can only be changed for reservations", true);
            }

            //echo $old_data['booking']['state']." == 0 &&".$new_data['booking']['state'];
        }

        // check if any room has been selected for both single & group booking
        $total_room_count = 0;
        if (isset($new_data['rooms'])) {
            foreach ($new_data['rooms'] as $room) {
                $total_room_count += (isset($room['room_count'])) ? $room['room_count'] : 0;
            }
//            if (
//                    (
//                    !isset($new_data['rooms'][0]['room_id']) ||
//                    !$new_data['rooms'][0]['room_id']
//                    ) && $total_room_count < 1
//            ) {
//                $errors[] = "room must be selected";
//            }


            if (
                isset($new_data['rooms'][0]['check_in_date']) &&
                isset($new_data['rooms'][0]['check_out_date']) &&
                isset($new_data['rooms'][0]['room_id'])
            ) {
                if ($new_data['rooms'][0]['check_in_date'] == '')
                    $errors[] = "check-in date field required";

                if ($new_data['rooms'][0]['check_out_date'] == '')
                    $errors[] = "check-out date field required";

                if ($new_data['rooms'][0]['check_in_date'] != '' &&
                    $new_data['rooms'][0]['check_out_date'] != '' &&
                    date("Y-m-d", strtotime($new_data['rooms'][0]['check_in_date'])) > date("Y-m-d", strtotime($new_data['rooms'][0]['check_out_date'])))
                    $errors[] = l("check-out date must be same or later than check-in date", true);
            }
        }

        return $errors;
    }

    function _is_valid_date($date) {
        //match the format of the date
        if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$/", $date, $parts)) {
            //check weather the date is valid of not
            if (checkdate($parts[2], $parts[3], $parts[1]))
                return true;
            else
                return false;
        } else if (preg_match("/^([0-9]{2})-([0-9]{2})-([0-9]{4}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$/", $date, $parts)) {
            //check weather the date is valid of not
            if (checkdate($parts[2], $parts[1], $parts[3]))
                return true;
            else
                return false;
        } else if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts)) {
            //check weather the date is valid of not
            if (checkdate($parts[2], $parts[3], $parts[1]))
                return true;
            else
                return false;
        } else if (preg_match("/^([0-9]{2})-([0-9]{2})-([0-9]{4})$/", $date, $parts)) {
            //check weather the date is valid of not
            if (checkdate($parts[2], $parts[1], $parts[3]))
                return true;
            else
                return false;
        } else
            return false;
    }

    function delete_booking_AJAX() {
        $booking_id = $this->input->post('booking_id');

        $payment_details = $this->Payment_model->get_payments($booking_id);
        $final_amount = 0;

        if($payment_details)
        {
            foreach($payment_details as $payment)
            {
                $final_amount += $payment['amount'];
            }
            if($final_amount)
            {
                echo json_encode(array('response' => 'failure', 'message' => l('A payment has been made to the room(s). Please refund or delete the payment record and then cancel the reservation', true)));
                die;
            }
        }
        $this->Booking_model->delete_booking($booking_id);

        $post_booking_data['booking_id'] = $booking_id;
        do_action('post.delete.booking', $post_booking_data);

        $this->_create_booking_log($booking_id, "Booking deleted");

        echo json_encode(array('response' => 'success'));
    }

    function checkin_booking_AJAX() {

        $booking_id = $this->input->post('booking_id');

        $booking_details = $this->Booking_model->get_booking($booking_id);

       $check_in_date = date('Y-m-d', strtotime($booking_details['check_in_date']));
        
        //prx($booking_details); 

        if ($check_in_date == $this->selling_date) {
            
            $booking_unconfirm = array('state' => 1);
            $this->Booking_model->update_booking($booking_id,$booking_unconfirm);

            $this->_create_booking_log($booking_id, "Booking check-in for entire group booking");

            echo json_encode(array('response' => 'success'));
        }else{

            echo json_encode(array('response' => 'failure'));
        }
        
    }

    function checkout_booking_AJAX() {

        $booking_id = $this->input->post('booking_id');

        $booking_details = $this->Booking_model->get_booking($booking_id);

        $check_out_date = date('Y-m-d', strtotime($booking_details['check_out_date']));
        
        //prx($booking_details); 

        if ($check_out_date == $this->selling_date) {
            
            $booking_unconfirm = array('state' => 2);
            $this->Booking_model->update_booking($booking_id,$booking_unconfirm);

            $this->_create_booking_log($booking_id, "Booking check-out for entire group booking");

            echo json_encode(array('response' => 'success'));
        }else{

            echo json_encode(array('response' => 'failure'));
        }
        
    }

    function create_charges($invoice_array, $booking_id, $customer_id) {
        $total = 0;
        $tax_amount = 0;
        for ($i = 0; $i < count($invoice_array); $i++) {
            $charges = array();
            $charges[] = array('amount' => $invoice_array[$i]['amount'],
                'booking_id' => $booking_id,
                'charge_type_id' => $invoice_array[$i]['charge_type_id'],
                'customer_id' => $customer_id,
                'description' => $invoice_array[$i]['description'],
                'selling_date' => $invoice_array[$i]['selling_date'],
                'user_id' => $this->user_id
            );
            $this->Charge_model->insert_charges($this->company_id, $charges);

            $post_charge_data = $charges;
            $post_charge_data['company_id'] = $this->company_id;

            do_action('post.create.charge', $post_charge_data);
            
            $charge_rate = $this->Charge_type_model->get_taxes($invoice_array[$i]['charge_type_id']);
            for ($j = 0; $j < count($charge_rate); $j++) {
                $tax_amount += number_format(($invoice_array[$i]['amount'] * $charge_rate[$j]['tax_rate']) / 100, 2);
            }
            $total += number_format($invoice_array[$i]['amount'], 2);
        }
        $total += number_format($tax_amount, 2);

        $this->Booking_model->update_booking_balance($booking_id);

        return $total;
    }

    function create_booking_group_Ajax() {
        echo $this->Group_model->create_booking_group($this->input->post());
    }

    function get_booking_source_AJAX()
    {
        $data = array();
        $common_booking_sources = json_decode(COMMON_BOOKING_SOURCES, true);
        $coomon_sources_setting = $this->Booking_source_model->get_common_booking_sources_settings($this->company_id);
        $sort_order = 0;
        foreach($common_booking_sources as $id => $name)
        {
            if(!(isset($coomon_sources_setting[$id]) && $coomon_sources_setting[$id]['is_hidden'] == 1))
            {
                $data[] = array(
                    'id' => $id,
                    'name' => $name,
                    'sort_order' => isset($coomon_sources_setting[$id]) ? $coomon_sources_setting[$id]['sort_order'] : $sort_order
                );
            }
            $sort_order++;
        }

        $booking_sources = $this->Booking_source_model->get_booking_source($this->company_id);
        if (!empty($booking_sources)) {
            foreach ($booking_sources as $booking_source) {
                if($booking_source['is_hidden'] != 1)
                {
                    $data[] = array(
                        'id' => $booking_source['id'],
                        'name' => $booking_source['name'],
                        'sort_order' => $booking_source['sort_order']
                    );
                }
            }
        }

        usort($data, function($a, $b) {
            return $a['sort_order'] - $b['sort_order'];
        });

        echo json_encode($data);
    }

    function get_booking_linked_group_room_list_AJAX() {
        $group_id = $this->input->post('group_id');

        $booking_ids = $this->Booking_linked_group_model->get_group_booking_ids($group_id);

        if (!empty($booking_ids)) {
            foreach ($booking_ids as $booking_id) {
                $booking_id = $booking_id['booking_id'];
                $booking = $this->Booking_model->get_booking($booking_id, false);
                $customer_name = $check_in_date = $check_out_date = '';

                if ($booking['is_deleted'] != 1 && $booking['company_id'] == $this->company_id) {
                    if ($booking['booking_customer_id']) {
                        $paying_customer = $this->Customer_model->get_customer($booking['booking_customer_id']);
                        $customer_name = isset($paying_customer['customer_name']) ? $paying_customer['customer_name'] : null;
                    }

                    $staying_customers = $this->Customer_model->get_staying_customers($booking_id);
                    $booking_block = $this->Booking_room_history_model->get_booking_detail($booking_id);
                    $check_in_date = $booking_block['check_in_date'];
                    $check_out_date = $booking_block['check_out_date'];

                    if (isset($booking_block['room_id'])) {
                        $room_info = $this->Room_model->get_room($booking_block['room_id']); //get room name
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
                        'room_id' => $booking_block['room_id'],
                        'room_type_id' => $booking_block['room_type_id']
                    );
                }
            }
        }

        $response = array(
            'success' => 'true',
            'booked_rooms_list' => $booked_rooms
        );

        echo json_encode($response);
    }

    function search_linked_groups() {
        $group_id = $this->input->post('group_id');
        $group_name = $this->input->post('group_name');
        $customer_name = $this->input->post('customer_name');
        $data_filter = array();
        $response = null;
        if ($group_id != '')
            $data_filter['group_id'] = $group_id;
        if ($group_name != '')
            $data_filter['group_name'] = $group_name;
        if ($customer_name != '')
            $data_filter['customer_name'] = $customer_name;

        $result = $this->Booking_linked_group_model->get_linked_groups($data_filter, $this->company_id);

        if (!empty($result) && $result) {
            $response['success'] = true;
            $response['group_info'] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = l('no data found', true);
        }
        echo json_encode($response);
    }

    function fix_hardcoded_booking_sources($company_id = null)
    {
        $common_booking_sources = json_decode(COMMON_BOOKING_SOURCES, true);

        $sort_order = 0;
        foreach($common_booking_sources as $new_booking_source_id => $value)
        {
            $booking_source_id = $this->Booking_source_model->get_booking_source_by_company($company_id, $value);
            if($booking_source_id){
                $update_res =  $this->Booking_source_model->update_booking_source_for_company($booking_source_id, $new_booking_source_id, $company_id);
                $this->Booking_source_model->delete_booking_sources_by_company($company_id, $booking_source_id);
//                $data = array(
//                    'booking_source_id' => $new_booking_source_id,
//                    'company_id' => $company_id,
//                    'is_hidden' => 0,
//                    'sort_order' => $sort_order
//                );
                //$this->Booking_source_model->create_common_booking_source_setting($data);
            }
            $sort_order++;
        }
    }

    function hardcoded_booking_source()
    {
        $this->load->view('fix_hardcoded_booking_source');
    }

    function array_diff_assoc_recursive($array1, $array2)
    {
        foreach($array1 as $key => $value)
        {
            if(is_array($value))
            {
                if(!isset($array2[$key]))
                {
                    $difference[$key] = $value;
                }
                elseif(!is_array($array2[$key]))
                {
                    $difference[$key] = $value;
                }
                else
                {
                    $new_diff = array_diff_assoc_recursive($value, $array2[$key]);
                    if($new_diff != FALSE)
                    {
                        $difference[$key] = $new_diff;
                    }
                }
            }
            elseif(!isset($array2[$key]) || $array2[$key] != $value)
            {
                $difference[$key] = $value;
            }
        }
        return !isset($difference) ? 0 : $difference;
    }


    function calculateDifference($array1, $array2){
        $difference = array();
        foreach($array1 as $key => $value){
            if(isset($array2[$key])){
                $difference[$key] = abs($array1[$key] - $array2[$key]);
            }else{
                $difference[$key] = $value;
            }
        }
        foreach($array2 as $key => $value){
            if(isset($array1[$key])){
                $difference[$key] = abs($array1[$key] - $array2[$key]);
            }else{
                $difference[$key] = $value;
            }
        }
        return $difference;
    }

    function check_overbooking_AJAX ()
    {
        $start_date = $this->input->post('check_in_date');
        $end_date = $this->input->post('check_out_date');
        $room_id = $this->input->post('room_id');
        $booking_id = $this->input->post('booking_id');

        if (!$room_id || !$booking_id) {
            echo json_encode(array("success" => false));
            return;
        }

        $bookings_found_in_range = $this->Booking_model->check_overbooking($start_date, $end_date, $room_id, $booking_id);
        if ($bookings_found_in_range) {
            echo json_encode(array("success" => true));
            return;
        }
        echo json_encode(array("success" => false));
        return;
    }

    function show_booking_information($hash = "")
    {
        $booking_id = $this->Booking_model->get_booking_id_from_invoice_hash($hash);

        if (!$hash || !$booking_id)
        {
            show_404();
        }

        $global_data['menu_on'] = false;
        $this->load->vars($global_data);

        if (isset($this->user_id))
            $this->permission->check_access_to_booking_id($this->user_id, $booking_id);

        $data['booking_detail'] = $this->Booking_model->get_booking_detail($booking_id);
        $company_id             = isset($data['booking_detail']['company_id']) ? $data['booking_detail']['company_id'] : $this->Company_model->get_company_id_by_booking_id($booking_id);

        $data['company']        = ($company_id == $this->company_id && $this->company_data) ? $this->company_data : $this->Company_model->get_company($company_id);

        $this->company_date_format = $data['company']['date_format'];

        $booking_room_history   = $this->Booking_room_history_model->get_booking_detail($booking_id);
        $data['room_detail']    = $this->Room_model->get_room($booking_room_history['room_id'], $booking_room_history['room_type_id']);
        $data['customer_id'] = $customer_id = isset($data['booking_detail']['customer_id']) ? $data['booking_detail']['customer_id'] : '';

        $data['currency_symbol'] = $this->session->userdata('currency_symbol');
        if (!$data['currency_symbol']) {
            $default_currency       = $this->Currency_model->get_default_currency($this->company_id);
            $data['currency_symbol'] = $default_currency['currency_code'];
            $this->session->set_userdata(array('currency_symbol' => $data['currency_symbol']));
        }

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
            if (count($data['customers']) > 1)
            {
                $data['booking_customer']['customer_name'] = $booking_customer['customer_name'].", ".implode(", ", $staying_customer_names);
            }
        }
        /*Get data from card table*/
      
        // for company logo
        $data['company_logos']   = $this->Image_model->get_images($data['company']['logo_image_group_id']);
        
        

        $data['read_only'] = $read_only = true;

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
                            if(isset($data['booking_customer'][$cn]) && $data['booking_customer'][$cn] != "")
                                $value = $data['booking_customer'][$cn];
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
                        if($data['booking_customer']['custom_fields'] && $data['booking_customer']['custom_fields'][$customer_field['id']] != '')
                        {
                            $customer_field['value'] = $data['booking_customer']['custom_fields'][$customer_field['id']];
                        }
                    }
                    $data['booking_customer']['customer_fields'][] = $customer_field;
                }
            }
        }

        $data['css_files'][]   = base_url().auto_version('css/booking/booking_main.css');

        $data['js_files'] = Array(
            base_url() . auto_version('js/channel_manager/channel_manager.js'),
            base_url().'js/moment.min.js',
            base_url() . auto_version('js/booking/printThis.js'),
            base_url() . auto_version('js/booking/bookingInformation.js'),
            base_url().auto_version('js/invoice/invoice.js')
        );
           
        
        if (!$read_only) {
             $data['selected_menu'] = 'invoices';
             $data['css_files'][]   = base_url().auto_version('css/invoice.css');
             $data['css_files'][]   = base_url().auto_version('css/booking/booking_main.css');
             $data['js_files'][]    = base_url().auto_version('js/invoice/invoice_edit.js');
        
        }
        $data['selected_customer_id'] = $customer_id;
        $data['main_content'] = 'booking/show_booking_information';
        $this->load->view('includes/bootstrapped_template', $data);
    }

    function guest_update_booking() {
        $response = array();

        $booking_id = sqli_clean($this->security->xss_clean($this->input->post('booking_id')));
        $status = sqli_clean($this->security->xss_clean($this->input->post('status')));

        $session_data = $this->session->userdata('customer_modify_booking');
        $this->user_id = -1; // user id is set to -1 as booking is being modified by guest
        $this->selling_date = $session_data['selling_date'];
            
        if($status == 'cancel') {
            $update_data = array('state' => CANCELLED);
            $this->Booking_model->update_booking($booking_id, $update_data);

            $post_booking_data = $update_data;
            $post_booking_data['booking_id'] = $booking_id;

            do_action('post.update.booking', $post_booking_data);
            
            $this->_create_booking_log(
                                        $booking_id,
                                        $status == "Booking cancelled",
                                        SYSTEM_LOG
                                    );
            $this->send_booking_cancellation_email($booking_id);

            echo json_encode(array('success' => true, 'message' => $status == l('Booking cancelled', true)));
        } else {
            $room_id = sqli_clean($this->security->xss_clean($this->input->post('room_id')));
            $check_in_date = sqli_clean($this->security->xss_clean($this->input->post('check_in_date')));
            $check_in_time = sqli_clean($this->security->xss_clean($this->input->post('check_in_time')));

            $check_out_date = sqli_clean($this->security->xss_clean($this->input->post('check_out_date')));
            $check_out_time = sqli_clean($this->security->xss_clean($this->input->post('check_out_time')));

            $old_check_in_date = sqli_clean($this->security->xss_clean($this->input->post('old_check_in_date')));
            $old_check_in_time = sqli_clean($this->security->xss_clean($this->input->post('old_check_in_time')));

            $old_check_out_date = sqli_clean($this->security->xss_clean($this->input->post('old_check_out_date')));
            $old_check_out_time = sqli_clean($this->security->xss_clean($this->input->post('old_check_out_time')));

            $check_in_date = isset($check_in_time) && $check_in_time ? date('Y-m-d', strtotime($check_in_date)).' '.date("H:i:s", strtotime($check_in_time)) : date('Y-m-d', strtotime($check_in_date));

            $check_out_date = isset($check_out_time) && $check_out_time ? date('Y-m-d', strtotime($check_out_date)).' '.date("H:i:s", strtotime($check_out_time)) : date('Y-m-d', strtotime($check_out_date));

            $old_check_in_date = isset($old_check_in_time) && $old_check_in_time ? date('Y-m-d', strtotime($old_check_in_date)).' '.date("H:i:s", strtotime($old_check_in_time)) : date('Y-m-d', strtotime($old_check_in_date));

            $old_check_out_date = isset($old_check_out_time) && $old_check_out_time ? date('Y-m-d', strtotime($old_check_out_date)).' '.date("H:i:s", strtotime($old_check_out_time)) : date('Y-m-d', strtotime($old_check_out_date));

            $brh_data['booking_id'] = $booking_id;
            $brh_data['room_id'] = $room_id;

            if($old_check_in_date != $check_in_date) {
                $brh_data['check_in_date'] = $check_in_date;
                $this->Booking_room_history_model->update_check_in_date($brh_data, $check_in_date);
            }

            if($old_check_out_date != $check_out_date) {
                $brh_data['check_out_date'] = $check_out_date;
                $this->Booking_room_history_model->update_check_out_date($brh_data, $check_out_date);
            }
            
            $post_booking_data['booking_id'] = $booking_id;
            $post_booking_data['room_id'] = $room_id;
            $post_booking_data['check_in_date'] = $check_in_date;
            $post_booking_data['check_out_date'] = $check_out_date;

            do_action('post.update.booking', $post_booking_data);
            

            $return_type = $session_data['is_total_balance_include_forecast'] ? 'balance' : 'balance_without_forecast';
            $balance = $this->Booking_model->update_booking_balance($booking_id, $return_type);

            $old_check_out_date = $session_data['enable_hourly_booking'] ? get_local_formatted_date($old_check_out_date).' '.date('h:i A', strtotime($old_check_out_date)) : get_local_formatted_date($old_check_out_date);
            
            $check_out_date = $session_data['enable_hourly_booking'] ? get_local_formatted_date($check_out_date).' '.date('h:i A', strtotime($check_out_date)) : get_local_formatted_date($check_out_date);

            $this->_create_booking_log(
                                        $booking_id,
                                        "Changed check-out date from ".$old_check_out_date." to ".$check_out_date,
                                        SYSTEM_LOG);

            echo json_encode(array('success' => true, 'balance' => $balance));
        }
    }
    function _generate_log_of_differences_between_new_booking_data_and_database($booking_id, $new_data, $existing_data)
    {
        $field_array = array(
            'check_in_date' => 'Check-in date',
            'check_out_date' => 'Check-out date',
        );

        $log_array = array();

        // Find the differences between existing data and new data.
        // and returns a <br /> seperated string
        foreach (array_keys($field_array) as $field)
        {
            if (isset($existing_data[$field]))
            {
                if (isset($new_data[$field]) )
                {
                    if ($existing_data[$field] != $new_data[$field])
                    {
                        $log_array[] = $field_array[$field]." changed from ".$existing_data[$field]." to ".$new_data[$field];
                    }
                }
            }
        }

        return implode("<br/>", $log_array);
    }

    function get_booking_fields()
    {
        $result = $this->Booking_field_model->get_booking_fields($this->company_id, 'show_on_booking_form');
        echo json_encode($result);
    }

    function delete_booking_extra_AJAX() {

        $booking_extra_id = $this->input->post('booking_extra_id');

        $booking_extra = $this->Booking_extra_model->get_booking_extra($booking_extra_id); // get extra by booking extra id 

        //$this->Booking_extra_model->delete_booking_extra($booking_extra_id);
        $data = array('is_deleted'=>1);
        $this->Booking_extra_model->update_booking_extra($booking_extra_id, $data);

        $balance = $this->Booking_model->update_booking_balance($booking_extra->booking_id);

        if($booking_extra_id)
        {

            $log = array(
                "booking_id" => $booking_extra->booking_id,
                "date_time" => gmdate('Y-m-d H:i:s'),
                "log_type" => 25,
                "log" => $booking_extra_id,
                "user_id" => $this->user_id,
                "selling_date" => $this->selling_date
            );
            $this->Booking_log_model->insert_log($log);
        }
        $response = array("message" => l("extra successfully deleted", true), 'balance' => $balance);
        echo json_encode($response);
    }


}
