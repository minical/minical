<?php
class Roomsy_migration extends MY_Controller
{
    function __construct()
    {

        parent::__construct();
        $this->load->model('Charge_type_model');
        $this->load->model('Company_model');
        $this->load->model('User_model');
        $this->load->model("Room_model");
        $this->load->model('Room_type_model');
        $this->load->model('Customer_field_model');
        $this->load->model('Customer_type_model');
        $this->load->model('Customer_model');
        $this->load->model('Charge_model');
        $this->load->model('Extra_model');
        $this->load->model('Payment_model');
        $this->load->model('Booking_field_model');
        $this->load->model('Booking_source_model');
        $this->load->model('Import_mapping_model');
        $this->load->model('Rate_plan_model');
        $this->load->model('Date_range_model');
        $this->load->model('Booking_linked_group_model');
        $this->load->model('Tax_price_bracket_model');
        $this->load->model('Room_location_model');
        $this->load->model('Statement_model');
        $this->load->model('Export_data_model');

        $this->load->helper('url'); // for redirect

        $view_data['menu_on'] = true;

        $this->user_id    = $this->session->userdata('user_id');
        $this->company_id = $this->session->userdata('current_company_id');

        $view_data['menu_on']          = true;
        $view_data['selected_menu']    = 'Settings';
        $view_data['selected_submenu'] = 'Company';

        $view_data['submenu'] = 'hotel_settings/hotel_settings_submenu.php';

        $view_data['submenu_parent_url'] = base_url()."settings/";
        $view_data['sidebar_menu_url'] = base_url()."settings/company/";

        $view_data['menu_items'] = $this->Menu_model->get_menus(array('parent_id' => 5, 'wp_id' => 1));
        $view_data['sidebar_links'] = $this->Menu_model->get_menus(array('parent_id' => 29, 'wp_id' => 1));

        $this->load->vars($view_data);
    }

    function index()
    {
        $this->minical_import();
    }

    function minical_import()
    {
        $data['company_ID'] = $this->company_id;
        $data['selected_sidebar_link'] = 'Import';
        $data['main_content']          = 'hotel_settings/company/roomsy_migration';
        $this->load->view('includes/bootstrapped_template', $data);
    }

    function import_rooms_csv($value,$setting){

        foreach ($value as $room) {
            $get_room_type = $this->Import_mapping_model->get_mapping_room_type_id($room['Room Type Id']);

            if (empty($get_room_type)) {
                $data = array(
                    'company_id' => $this->company_id,
                    'name' => $room['Room Type Name'] == '' ? null : $room['Room Type Name'],
                    'acronym' => $room['Acronym'] == ''? null : $room['Acronym'] ,
                    'max_adults' => $room['Max Adults'] == ''  ? 0 : $room['Max Adults'] ,
                    'max_children' => $room['Max Children'] == ''  ? 0 : $room['Max Children'] ,
                    'max_occupancy' => $room['Max Occupancy'] == ''  ? 0 : $room['Max Occupancy'] ,
                    'min_occupancy' => $room['Min Occupancy'] == ''  ? 0 : $room['Min Occupancy'] ,
                    'can_be_sold_online' => $room['Room Type Can be Sold online'] == 'true' ? 1 : 0,
                    'default_room_charge' => $room['Room Charge'],
                    'description' => $room['Description']

                );

                $room_type_id = $this->Room_type_model->add_new_room_type($data);
                $data_import_mapping = Array(
                    "new_id" => $room_type_id,
                    "old_id" => $room['Room Type Id'],
                    "company_id" => $this->company_id,
                    "type" => "room_type"
                );

                $import_data = $this->Import_mapping_model->insert_import_mapping($data_import_mapping);
            } else {
                $room_type_id = isset($get_room_type['new_id']) ? $get_room_type['new_id'] : '';
            }

            if(!empty($room['Room Id'])){
                // $get_room = $this->Room_model->get_room_by_name($room['Room Name'], $room_type_id);
                $get_room = $this->Import_mapping_model->get_mapping_room_id($room['Room Id']);
                if(empty($get_room)){
                    $sold_online = $room['Room Can be Sold online'] == 'true' ? 1 : 0 ;
                    $sort_order = isset($room['Sort Order']) && $room['Sort Order'] != '' && $room['Sort Order'] != null ? $room['Sort Order'] : 0 ;

                    $room_id = $this->Room_model->create_rooms(
                        $this->company_id,
                        $room['Room Name'],
                        $room_type_id,
                        $sort_order,
                        $sold_online,
                        $room['Status']
                    );

                    $data_import_mapping = Array(
                        "new_id" => $room_id,
                        "old_id" => $room['Room Id'],
                        "company_id" => $this->company_id,
                        "type" => "room"
                    );

                    $import_data = $this->Import_mapping_model->insert_import_mapping($data_import_mapping);

                }



            }

            // if(!empty($room['Floor'])){
            //     $get_floor = $this->Floor_model->get_floor($this->company_id);

            //     if(empty($get_floor)){
            //         $data = array(
            //             "floor_name" => $room['Floor'],
            //             "company_id" => $this->company_id
            //         );
            //         $floor = $this->Floor_model->insert($data);
            //     }

            // }

            // if(!empty($room['Location'])){

            //     $get_location = $this->Room_location_model->get_room_location($this->company_id);

            //     if(empty($get_location)){

            //         $data = array(
            //             "location_name" => $room['Location'],
            //             "company_id" => $this->company_id
            //         );
            //         $floor = $this->Room_location_model->insert($data);

            //     }

            // }

            $settings = json_decode($setting,true);

            $extra_room_types = $settings['Room Types'];


            if($extra_room_types){

                foreach ($extra_room_types as $key => $rt) {

                    $extra_room_type = $this->Import_mapping_model->get_mapping_room_type_id($rt['id']);

                    if(empty($extra_room_type)){

                        $extra_data = array(
                            'company_id' => $this->company_id,
                            'name' => $rt['name'] == '' ? null : $rt['name'],
                            'acronym' => $rt['acronym'] == ''? null : $rt['acronym'] ,
                            'max_adults' => $rt['max_adults'] == ''  ? 0 : $rt['max_adults'] ,
                            'max_children' => $rt['max_children'] == ''  ? 0 : $rt['max_children'] ,
                            'max_occupancy' => $rt['max_occupancy'] == ''  ? 0 : $rt['max_occupancy'] ,
                            'min_occupancy' => $rt['min_occupancy'] == ''  ? 0 : $rt['min_occupancy'] ,
                            'can_be_sold_online' => $rt['can_be_sold_online'] == 'true' ? 1 : 0,
                            'default_room_charge' => $rt['default_room_charge'],
                            'description' => $rt['description']

                        );

                        $extra_room_type_id = $this->Room_type_model->add_new_room_type($extra_data);
                        $extra_data_import_mapping = Array(
                            "new_id" => $extra_room_type_id,
                            "old_id" => $rt['id'],
                            "company_id" => $this->company_id,
                            "type" => "room_type"
                        );

                        $import_data = $this->Import_mapping_model->insert_import_mapping($extra_data_import_mapping);

                    }

                }

            }


        }
    }

    function import_taxes_csv($value){

        foreach ($value as $tax) {

            $get_tax_type = $this->Import_mapping_model->get_mapping_tax_id($tax['Tax Type Id']);

            if(empty($get_tax_type)){

                $data = array(
                    "tax_type" => $tax['Tax Type'] == '' ? null : $tax['Tax Type'],
                    "tax_rate" => $tax['Tax Rate'] == '' ? 0 : $tax['Tax Rate'],
                    "company_id" => $this->company_id ,
                    "is_percentage" => $tax['Is Percentage'] == 'true' ? 1 : 0,
                    "is_brackets_active" => $tax['Bracket Active'] == 'true' ? 1 : 0,
                    "is_tax_inclusive" => $tax['Is Tax Inclusive'] == 'true' ? 1 : 0
                );

                $new_taxes = $this->Tax_model->create_new_tax_type($data);

                if($tax['Bracket Active'] == 'true'){
                    $price_bracket = json_decode($tax['Price Bracket'],true);

                    foreach ($price_bracket as $price) {
                        $price_brackets = array(
                            "tax_type_id" => $new_taxes,
                            "start_range" => $price['start'],
                            "end_range" =>$price['end'],
                            "tax_rate" =>$price['rate'],
                            "is_percentage" =>$price['is_percentage']
                        );
                        $this->Tax_price_bracket_model->create_price_bracket($price_brackets);
                    }
                }

                $data_import_mapping = Array(
                    "new_id" => $new_taxes,
                    "old_id" => $tax['Tax Type Id'],
                    "company_id" => $this->company_id,
                    "type" => "tax_type"
                );

                $import_data = $this->Import_mapping_model->insert_import_mapping($data_import_mapping);
            }
        }
    }

    function import_charges_csv($value)
    {
        $cache_get_charge_type = array();
        $cache_tax_type_id = array();
        $cache_charge_taxes = array();
        $cache_customer_id = array();
        $cache_charges = array();

        foreach ($value as $charge) {

            if(
                isset($cache_get_charge_type[$charge['Charge Type Id']]) && 
                    $cache_get_charge_type[$charge['Charge Type Id']]
            ){
                $get_the_charge_type = $cache_get_charge_type[$charge['Charge Type Id']];
            } else {
                $get_the_charge_type = $this->Import_mapping_model->get_mapping_charge_id($charge['Charge Type Id']);
                $cache_get_charge_type[$charge['Charge Type Id']] = $get_the_charge_type;
            }

            if(empty($get_the_charge_type)){

                $data = array (
                    'name' => $charge['Charge Type'],
                    'company_id' => $this->company_id,
                    'is_room_charge_type' => $charge['Room Charge Type'] == 'true' ? 1 : 0,
                    'is_tax_exempt' => $charge['Tax Exempt'] == 'true' ? 1 : 0,
                    'is_default_room_charge_type' => $charge['Default Room Charge'] == 'true' ? 1 : 0,
                );

                $charge_type_id = $this->Charge_type_model->create_charge_types($data);

                $data_import_mapping = Array(
                    "new_id" => $charge_type_id,
                    "old_id" => $charge['Charge Type Id'],
                    "company_id" => $this->company_id,
                    "type" => "charge_type"
                );

                $import_data = $this->Import_mapping_model->insert_import_mapping($data_import_mapping);

                $taxes = explode(',', $charge['Tax Type']);

                foreach ($taxes as $tax_type) {
                    if($tax_type){

                        if(
                            isset($cache_tax_type_id[$tax_type]) && 
                                $cache_tax_type_id[$tax_type]
                        ){
                            $tax_type_id = $cache_tax_type_id[$tax_type];
                        } else {
                            $tax_type_id = $this->Tax_model->get_tax_type_by_name($tax_type);
                            $cache_tax_type_id[$tax_type] = $tax_type_id;
                        }

                        if(
                            isset($cache_charge_taxes[$charge_type_id.'-'.$tax_type_id]) && 
                                $cache_charge_taxes[$charge_type_id.'-'.$tax_type_id]
                        ){
                            $charge_taxes = $cache_charge_taxes[$charge_type_id.'-'.$tax_type_id];
                        } else {
                            $charge_taxes = $this->Charge_type_model->get_charge_tax($charge_type_id, $tax_type_id);
                            $cache_charge_taxes[$charge_type_id.'-'.$tax_type_id] = $charge_taxes;
                        }
                        
                        if(!$charge_taxes){
                            $this->Charge_type_model->add_charge_type_tax($charge_type_id, $tax_type_id);
                        }
                    }
                }
            } else {
                $charge_type_id = isset($get_the_charge_type['new_id']) ? $get_the_charge_type['new_id'] : 0 ;
            }

            $room_charge_type = $this->Room_type_model->update_room_charge_type($charge_type_id, $charge['Charge Type Id']);

            if(
                isset($cache_customer_id[$charge['Customer Id']]) && 
                    $cache_customer_id[$charge['Customer Id']]
            ){
                $customer_id = $cache_customer_id[$charge['Customer Id']];
            } else {
                $customer_id =  $this->Import_mapping_model->get_mapping_customer_id($charge['Customer Id']);
                $cache_customer_id[$charge['Customer Id']] = $customer_id;
            }

            if(!empty($charge['Charge Id'])){

                if(
                    isset($cache_charges[$charge['Charge Id']]) && 
                        $cache_charges[$charge['Charge Id']]
                ){
                    $charges = $cache_charges[$charge['Charge Id']];
                } else {
                    $charges =  $this->Import_mapping_model->get_mapping_charge($charge['Charge Id']);
                    $cache_charges[$charge['Charge Id']] = $charges;
                }

                if(empty($charges))
                {
                    switch ($charge['Pay Period']) {
                        case "Daily" : $pay_period = '0'; break;
                        case "Weekly" : $pay_period = '1'; break;
                        case "Monthly" : $pay_period = '2'; break;
                        case "One time" : $pay_period = '3'; break;
                    }

                    $data = Array(
                        "description" => $charge['Description'] == '' ? null : $charge['Description'],
                        "date_time" =>$charge['Date Time'] != null ? $charge['Date Time'] : date('Y-m-d H:i:s') ,
                        "booking_id" => $charge['Booking Id'],
                        "amount" => $charge['Amount'] == '' ? 0 : $charge['Amount'],
                        "charge_type_id" => $charge_type_id,
                        "selling_date" => $charge['Selling Date'],
                        "customer_id" => isset($customer_id['new_id']) && $customer_id['new_id'] ? $customer_id['new_id'] : null,
                        "pay_period" => $pay_period,
                        "is_night_audit_charge" => $charge['Night Audit Charge'] == 'true' ? 1 : 0

                    );

                    $charge_id = $this->Charge_model->insert_charge($data);

                    $data_import_mapping = Array(
                        "new_id" => $charge_id,
                        "old_id" => $charge['Charge Id'],
                        "company_id" => $this->company_id,
                        "type" => "charge"
                    );

                    $import_data = $this->Import_mapping_model->insert_import_mapping($data_import_mapping);
                }
            }
        }
    }

    function import_rates_csv($value)
    {
        $cache_import_rate_plan = array();
        $cache_room_type = array();
        $cache_charge_type_id = array();
        $cache_rates = array();

        foreach ($value as $rate) {

            if(
                isset($cache_import_rate_plan[$rate['Rate Plan Id']]) && 
                    $cache_import_rate_plan[$rate['Rate Plan Id']]
            ){
                $get_import_rate_plan = $cache_import_rate_plan[$rate['Rate Plan Id']];
            } else {
                $get_import_rate_plan = $this->Import_mapping_model->get_rate_plan_mapping_id($rate['Rate Plan Id']);
                $cache_import_rate_plan[$rate['Rate Plan Id']] = $get_import_rate_plan;
            }

            if(
                isset($cache_room_type[$rate['Room type Id']]) && 
                    $cache_room_type[$rate['Room type Id']]
            ){
                $room_type = $cache_room_type[$rate['Room type Id']];
            } else {
                $room_type =  $this->Import_mapping_model->get_mapping_room_type_id($rate['Room type Id']);
                $cache_room_type[$rate['Room type Id']] = $room_type;
            }

            if(
                isset($cache_charge_type_id[$rate['Charge Type']]) && 
                    $cache_charge_type_id[$rate['Charge Type']]
            ){
                $charge_type_id = $cache_charge_type_id[$rate['Charge Type']];
            } else {
                $charge_type_id = $this->Import_mapping_model->get_mapping_charge_id($rate['Charge Type']);
                $cache_charge_type_id[$rate['Charge Type']] = $charge_type_id;
            }

            $room_type_id = $room_type['new_id'];

            if(empty($room_type_id)){
                $room_type= $this->Room_type_model->get_room_type_name($rate['Room Type Name'] , $this->company_id);
                $room_type_id = $room_type[0]['id'];
            }

            if(empty($get_import_rate_plan)) {

                $data = array(
                    "rate_plan_name" => $rate['Name'] == '' ? null : $rate['Name'],
                    "room_type_id" => $room_type_id,
                    "company_id" => $this->company_id,
                    "is_selectable" => $rate['Read Only'] == 'true' ? 1 : 0,
                    "charge_type_id" => $charge_type_id['new_id'],
                    "description" => $rate['Description']? $rate['Description'] : "",
                    "currency_id" => $rate['Currency'] ? $rate['Currency'] : null
                );
                $rate_plan_id = $this->Rate_plan_model->create_rate_plan($data);

                $data_import_mapping = Array(
                    "new_id" => $rate_plan_id,
                    "old_id" => $rate['Rate Plan Id'],
                    "company_id" => $this->company_id,
                    "type" => "rate_plan"
                );

                $import_data = $this->Import_mapping_model->insert_import_mapping($data_import_mapping);
            }
            else {
                $rate_plan_id = $get_import_rate_plan['new_id'];
            }

            $room_rate_plan = $this->Rate_plan_model->update_room_rate_plan($rate_plan_id, $rate['Rate Plan Id']);

            if(
                isset($cache_rates[$rate['Rate Id']]) && 
                    $cache_rates[$rate['Rate Id']]
            ){
                $rates = $cache_rates[$rate['Rate Id']];
            } else {
                $rates = $this->Import_mapping_model->get_rates_mapping_id($rate['Rate Id']);
                $cache_rates[$rate['Rate Id']] = $rates;
            }

            if(empty($rates)){

                $rate_id = $this->Rate_model->create_rate(
                    Array(
                        'rate_plan_id' => $rate_plan_id,
                        'base_rate' => $rate['Base Rate'] == '' ? null : $rate['Base Rate'],
                        'adult_1_rate' => $rate['Adult Rate 1'] ? $rate['Adult Rate 1'] : null,
                        'adult_2_rate' => $rate['Adult Rate 2'] ? $rate['Adult Rate 2'] : null,
                        'adult_3_rate' => $rate['Adult Rate 3'] ? $rate['Adult Rate 3'] : null,
                        'adult_4_rate' => $rate['Adult Rate 4'] ? $rate['Adult Rate 4'] : null,
                        'additional_adult_rate' => $rate['Additional Adult Rate'] ? $rate['Additional Adult Rate'] : null,
                        'additional_child_rate' => $rate['Aditional Child Rate'] ? $rate['Aditional Child Rate'] : null,
                        'minimum_length_of_stay' => $rate['Min Length of Stay'] ? $rate['Min Length of Stay'] : null,
                        'maximum_length_of_stay' => $rate['Max Length of Stay'] ? $rate['Max Length of Stay'] : null,
                        'closed_to_departure' => $rate['Close to Departure'] == 'true' ? 1 : 0,
                        'closed_to_arrival' => $rate['Close to Arrival'] == 'true' ? 1 : 0,
                        'can_be_sold_online' => $rate['Can be sold online'] == 'true' ? 1 : 0
                    )
                );

                $date_range_id = $this->Date_range_model->create_date_range(
                    Array(
                        'date_start' => $rate['From Date'] == '' ? null : $rate['From Date'],
                        'date_end' => $rate['To Date'] == '' ? null : $rate['To Date'],
                        'monday' => $rate['Monday'] == '' ? null : $rate['Monday'],
                        'tuesday' => $rate['Tuesday'] == '' ? null : $rate['Tuesday'],
                        'wednesday' => $rate['Wednesday'] == '' ? null : $rate['Wednesday'],
                        'thursday' => $rate['Thursday'] == '' ? null : $rate['Thursday'],
                        'friday' => $rate['Friday'] == '' ? null : $rate['Friday'],
                        'saturday' => $rate['Saturday'] == '' ? null : $rate['Saturday'],
                        'sunday' => $rate['Sunday']== '' ? null : $rate['Sunday']
                    )
                );

                $this->Date_range_model->create_date_range_x_rate(
                    Array(
                        'rate_id' => $rate_id,
                        'date_range_id' => $date_range_id
                    ));

                $data_import_mapping = Array(
                    "new_id" => $rate_id,
                    "old_id" => $rate['Rate Id'],
                    "company_id" => $this->company_id,
                    "type" => "rate"
                );

                $import_data = $this->Import_mapping_model->insert_import_mapping($data_import_mapping);
            }
        }
    }

    function import_customers_csv($value)
    {
        $cache_customer_type = array();
        $cache_get_customer = array();
        $cache_exist_customer_field = array();

        foreach ($value as $customer) {

            if(
                isset($cache_customer_type[$customer['Customer Type']]) && 
                    $cache_customer_type[$customer['Customer Type']]
            ){
                $get_customer_type = $cache_customer_type[$customer['Customer Type']];
            } else {
                $get_customer_type = $this->Customer_type_model->get_customer_type_by_name($this->company_id, $customer['Customer Type']);
                $cache_customer_type[$customer['Customer Type']] = $get_customer_type;
            }

            if(empty($get_customer_type)){
                $customer_type_id = $this->Customer_type_model->create_customer_type($this->company_id, $customer['Customer Type']);
            } else {
                $customer_type_id = isset($get_customer_type[0]['id']) ? $get_customer_type[0]['id'] : ' ' ;
            }

            $data = Array(
                "customer_name" => $customer['Customer Name'] == ''? null : $customer['Customer Name'],
                "address" => $customer['Address'] == ''? null : $customer['Address'],
                "email" => $customer['Email'] == ''? null : $customer['Email'],
                "city" => $customer['City'] == ''? null : $customer['City'],
                "region" => $customer['Region'] == ''? null : $customer['Region'],
                "phone" => $customer['Phone'] == ''? null : $customer['Phone'],
                "country" => $customer['Country'] == ''? null : $customer['Country'],
                "postal_code" => $customer['Postal Code'] == ''? null : $customer['Postal Code'],
                "customer_notes" => $customer['Customer Notes'] == ''? null : $customer['Customer Notes'],
                "address2" => $customer['Address2'] == ''? null : $customer['Address2'],
                "phone2" => $customer['Phone2'] == ''? null : $customer['Phone2'],
                "customer_type_id" => $customer_type_id,
                "company_id" => $this->company_id
            );

            if(
                isset($cache_get_customer[$customer['Customer Id']]) && 
                    $cache_get_customer[$customer['Customer Id']]
            ){
                $get_customer = $cache_get_customer[$customer['Customer Id']];
            } else {
                $get_customer = $this->Import_mapping_model->get_mapping_customer_id($customer['Customer Id']);
                $cache_get_customer[$customer['Customer Id']] = $get_customer;
            }

            if(!empty($customer['Customer Id'])) {
                if(empty($get_customer)) {
                    $customer_id = $this->Customer_model->create_customer($data);
                    $data_import_mapping = Array(
                        "new_id" => $customer_id,
                        "old_id" => $customer['Customer Id'],
                        "company_id" => $this->company_id,
                        "type" => "customer"
                    );
                    $import_data = $this->Import_mapping_model->insert_import_mapping($data_import_mapping);
                }
            }

            foreach($customer as $key => $customer_data) {

                $key_name =  array(
                    'Customer Id','Customer Name','Customer Type','Address','City','Region' ,'Country','Postal Code','Phone','Fax' ,'Email','Customer Notes','Address2','Phone2'
                );

                if (!in_array($key, $key_name)) {

                    if(
                        isset($cache_exist_customer_field[$key]) && 
                            $cache_exist_customer_field[$key]
                    ){
                        $existing_customer_fields = $cache_exist_customer_field[$key];
                    } else {
                        $existing_customer_fields = $this->Customer_field_model->get_customer_field_by_name($this->company_id, $key);
                        $cache_exist_customer_field[$key] = $existing_customer_fields;
                    }

                    if(empty($existing_customer_fields)) {
                        $customer_fields = $this->Customer_field_model->create_customer_field($this->company_id, $key);
                    } else {
                        $customer_fields = $existing_customer_fields[0]['id'];
                    }

                    if($customer_data) {
                        $custom_customer_fields = array(
                            "customer_id" => $customer_id,
                            "customer_field_id" => $customer_fields,
                            "value" => $customer_data
                        );
                        $this->Customer_field_model->customer_field($custom_customer_fields);
                    }
                }
            }
        }
    }

    function import_bookings_csv($value){

        $cache_charge_type_id = $cache_room_type_id = $cache_room_id = array();
        $cache_rate_plan_id = $cache_customer_id_by_booking_customer = array();
        $cache_customer_id_by_booked_by = $cache_get_source = $cache_booking_id = array();
        $cache_staying_customer_id = $cache_existing_booking_field = array();
        $cache_booking_block = $cache_block_booking_id = $cache_group_id = array();

        foreach ($value as $booking) {

            // for get charge type id
            if(
                isset($cache_charge_type_id[$booking['Charge Type']]) && 
                    $cache_charge_type_id[$booking['Charge Type']]
            ){
                $charge_type_id = $cache_charge_type_id[$booking['Charge Type']];
            } else {
                $charge_type_id = $this->Import_mapping_model->get_mapping_charge_id($booking['Charge Type']);
                $cache_charge_type_id[$booking['Charge Type']] = $charge_type_id;
            }

            // for get room type id
            if(
                isset($cache_room_type_id[$booking['Room Type']]) && 
                    $cache_room_type_id[$booking['Room Type']]
            ){
                $room_type_id = $cache_room_type_id[$booking['Room Type']];
            } else {
                $room_type_id = $this->Import_mapping_model->get_mapping_room_type_id($booking['Room Type']);
                $cache_room_type_id[$booking['Room Type']] = $room_type_id;
            }

            // for get room id
            if(
                isset($cache_room_id[$booking['Room']]) && 
                    $cache_room_id[$booking['Room']]
            ){
                $room_id = $cache_room_id[$booking['Room']];
            } else {
                $room_id = $this->Import_mapping_model->get_mapping_room_id($booking['Room']);
                $cache_room_id[$booking['Room']] = $room_id;
            }

            // for get rate plan id
            if(
                isset($cache_rate_plan_id[$booking['Rate Plan Id']]) && 
                    $cache_rate_plan_id[$booking['Rate Plan Id']]
            ){
                $rate_plan_id = $cache_rate_plan_id[$booking['Rate Plan Id']];
            } else {
                $rate_plan_id = $this->Import_mapping_model->get_rate_plan_mapping_id($booking['Rate Plan Id']);
                $cache_rate_plan_id[$booking['Rate Plan Id']] = $rate_plan_id;
            }

            // for get customer id by booking_customer
            if(
                isset($cache_customer_id_by_booking_customer[$booking['Booking Customer Id']]) && 
                    $cache_customer_id_by_booking_customer[$booking['Booking Customer Id']]
            ){
                $customer_id = $cache_customer_id_by_booking_customer[$booking['Booking Customer Id']];
            } else {
                $customer_id =  $this->Import_mapping_model->get_mapping_customer_id($booking['Booking Customer Id']);
                $cache_customer_id_by_booking_customer[$booking['Booking Customer Id']] = $customer_id;
            }

            // for get customer id by booked_by
            if(
                isset($cache_customer_id_by_booked_by[$booking['Booked By']]) && 
                    $cache_customer_id_by_booked_by[$booking['Booked By']]
            ){
                $booked_by = $cache_customer_id_by_booked_by[$booking['Booked By']];
            } else {
                $booked_by =  $this->Import_mapping_model->get_mapping_customer_id($booking['Booked By']);
                $cache_customer_id_by_booked_by[$booking['Booked By']] = $booked_by;
            }

            switch ($booking['State']) {
                case "Reservation" : $state = '0'; break;
                case "Checked-in" : $state = '1'; break;
                case "Checked-out" : $state = '2'; break;
                case "Out-of-Order" : $state = '3'; break;
                case "Cancelled" : $state = '4'; break;
                case "No-show" : $state = '5'; break;
                case "Delete" : $state = '6'; break;
                case "Unconfirmed" : $state = '7'; break;
            }

            switch ($booking['Pay Period']) {
                case "Daily" : $pay_period = '0'; break;
                case "Weekly" : $pay_period = '1'; break;
                case "Monthly" : $pay_period = '2'; break;
                case "One time" : $pay_period = '3'; break;
            }

            $source = "";

            if(isset($booking['Custom Booking Source']) && $booking['Custom Booking Source'] != ''){

                // for get get source
                if(
                    isset($cache_get_source[$booking['Custom Booking Source']]) && 
                        $cache_get_source[$booking['Custom Booking Source']]
                ){
                    $get_source = $cache_get_source[$booking['Custom Booking Source']];
                } else {
                    $get_source = $this->Booking_source_model->get_booking_source_by_company($this->company_id, $booking['Custom Booking Source']);
                    $cache_get_source[$booking['Custom Booking Source']] = $get_source;
                }
                
                if(empty($get_source)) {
                    $source = $this->Booking_source_model->create_booking_source($this->company_id, $booking['Custom Booking Source']);
                } else {
                    $source = $get_source ? $get_source : 0 ;
                }
            } else {
                switch ($booking['Source']) {
                    case "Walk-in / Telephone" : $source = '0'; break;
                    case "Online Widget" : $source = '-1'; break;
                    case "Booking Dot Com" : $source = '-2'; break;
                    case "Expedia" : $source = '-3'; break;
                    case "Agoda" : $source = '-4'; break;
                    case "Trip Connect" : $source = '-5'; break;
                    case "Air BNB" : $source = '-6'; break;
                    case "Hotel World" : $source = '-7'; break;
                    case "Myallocator" : $source = '-8'; break;
                    case "Company" : $source = '-9'; break;
                    case "Guest Member" : $source = '-10'; break;
                    case "Owner" : $source = '-11'; break;
                    case "Returning Guest" : $source = '-12'; break;
                    case "Apartment" : $source = '-13'; break;
                    case "sitminder" : $source = '-14'; break;
                    case "Seasonal" : $source = '-15'; break;
                    case "Other taravel agency" : $source = '-20'; break;
                }
            }

            // for get booking id
            if(
                isset($cache_booking_id[$booking['Booking Id']]) && 
                    $cache_booking_id[$booking['Booking Id']]
            ){
                $booking_id = $cache_booking_id[$booking['Booking Id']];
            } else {
                $booking_id =  $this->Import_mapping_model->get_mapping_booking_id($booking['Booking Id']);
                $cache_booking_id[$booking['Booking Id']] = $booking_id;
            }

            if(empty($booking_id)){
                $data = Array(
                    "rate" => $booking['Rate'] == '' ? null : $booking['Rate'],
                    "adult_count" => $booking['Adult Count'] == '' ? null : $booking['Adult Count'],
                    "children_count" => $booking['Children Count'] == '' ? null : $booking['Children Count'],
                    "booking_customer_id" => $customer_id['new_id'],
                    "booking_notes" => $booking['Booking Note'] == '' ? '' : $booking['Booking Note'] ,
                    "booked_by" => $booking['Booked By'] == '' ? null : $booked_by['new_id'],
                    "balance" => $booking['Balance'] == '' ? null : $booking['Balance'],
                    "balance_without_forecast" => $booking['Balance Without Forecast'] == '' ? null : $booking['Balance Without Forecast'],
                    "use_rate_plan" => $booking['Use Rate Plan'] == 'true' ? 1 : 0,
                    "rate_plan_id" => $rate_plan_id['new_id'] == '' ? null : $rate_plan_id['new_id'],
                    "color" => $booking['Color'] != '' ? $booking['Color'] : '',
                    "charge_type_id" => $charge_type_id['new_id'],
                    "pay_period" => isset($pay_period) ? $pay_period : 0,
                    "source" => $source ? $source : 0 ,
                    "company_id" => $this->company_id,
                    "state" => isset($state) ? $state : 0
                );

                $booking_id = $this->Booking_model->create_booking($data);

                $data_import_mapping = Array(
                    "new_id" => $booking_id,
                    "old_id" => $booking['Booking Id'],
                    "company_id" => $this->company_id,
                    "type" => "booking"
                );

                $import_data = $this->Import_mapping_model->insert_import_mapping($data_import_mapping);

                $charge_update = $this->Charge_model->update_charge_booking($booking['Booking Id'],$booking_id,$customer_id['new_id']);

                $stay_in_customers = $booking['Staying Customers'];

                if(isset($stay_in_customers) && $stay_in_customers != '' && $stay_in_customers != null){

                    $customer_ids = explode(',', $booking['Staying Customers']);

                    foreach ($customer_ids as $customer_id) {

                        // for get staying customer id
                        if(
                            isset($cache_staying_customer_id[$customer_id]) && 
                                $cache_staying_customer_id[$customer_id]
                        ){
                            $staying_customer_id = $cache_staying_customer_id[$customer_id];
                        } else {
                            $staying_customer_id = $this->Import_mapping_model->get_mapping_customer_id($customer_id);
                            $cache_staying_customer_id[$customer_id] = $staying_customer_id;
                        }

                        if($staying_customer_id){

                            $existing_customer = $this->Booking_model->get_booking_staying_customer_by_id($staying_customer_id['new_id'], $this->company_id, $booking_id);
                            if(!$existing_customer){

                                $data = array(
                                    'booking_id' => $booking_id,
                                    'company_id' => $this->company_id,
                                    'customer_id' => $staying_customer_id['new_id']
                                );

                                $this->Booking_model->create_booking_staying_customer($data);
                            }
                        }
                    }
                }

                foreach($booking as $key => $booking_data) {
                    $key_name = array(
                        "Booking Id","Rate","Adult Count","Children Count","State","Booking Customer Id","Booked By","Balance","Balance Without Forecast","Use Rate Plan","Rate Plan Id","Color","Charge Type","Check In Date","Check Out Date","Room","Room Type","Group Id","Group Name", "Daily Charges", "Pay Period", "Source", "Custom Booking Source","Booking Note","Booking Room History","Staying Customers"
                    );

                    if (!in_array($key, $key_name) ) {

                        // for get existing booking field
                        if(
                            isset($cache_existing_booking_field[$key]) && 
                                $cache_existing_booking_field[$key]
                        ){
                            $existing_booking_field = $cache_existing_booking_field[$key];
                        } else {
                            $existing_booking_field = $this->Booking_field_model->get_the_booking_fields_by_name($key , $this->company_id);
                            $cache_existing_booking_field[$key] = $existing_booking_field;
                        }

                        if(empty($existing_booking_field)){
                            $booking_fields = $this->Booking_field_model->create_booking_field($this->company_id, $key);
                        } else {
                            $booking_fields =$existing_booking_field[0]['id'];
                        }

                        if($booking_data){

                            $custom_booking_fields = array(
                                "booking_id" => $booking_id,
                                "booking_field_id" => $booking_fields,
                                "value" => $booking_data
                            );

                            $this->Booking_field_model->booking_field($booking_id, $custom_booking_fields);
                        }
                    }
                }

                if(!empty($booking['Group Id'])){

                    // for get group id
                    if(
                        isset($cache_group_id[$booking['Group Id']]) && 
                            $cache_group_id[$booking['Group Id']]
                    ){
                        $group_id = $cache_group_id[$booking['Group Id']];
                    } else {
                        $group_id =  $this->Import_mapping_model->get_mapping_group_booking_id($booking['Group Id']);
                        $cache_group_id[$booking['Group Id']] = $group_id;
                    }

                    if(empty($group_id)){

                        $group_name = ($booking['Group Name']) != '' ? $booking['Group Name'] : null ;
                        $new_group_id = $this->Booking_linked_group_model->create_booking_linked_groups($group_name);
                        $data_import_mapping = Array(
                            "new_id" => $new_group_id,
                            "old_id" => $booking['Group Id'],
                            "company_id" => $this->company_id,
                            "type" => "group_booking"
                        );
                        $import_data = $this->Import_mapping_model->insert_import_mapping($data_import_mapping);

                    } else {
                        $new_group_id = $group_id['new_id'];
                    }

                    $data = array(
                        "booking_id " => $booking_id,
                        "booking_group_id" => $new_group_id
                    );

                    $booking_linke_group = $this->Booking_linked_group_model->insert_booking_x_booking_linked_group($data);
                }
            }

            // for get booking room history
            if(
                isset($cache_booking_block[$booking['Booking Room History']]) && 
                    $cache_booking_block[$booking['Booking Room History']]
            ){
                $booking_block = $cache_booking_block[$booking['Booking Room History']];
            } else {
                $booking_block = $this->Import_mapping_model->get_mapping_booking_room_history_id($booking['Booking Room History']);
                $cache_booking_block[$booking['Booking Room History']] = $booking_block;
            }

            if(empty($booking_block)){

                // for get booking id
                if(
                    isset($cache_block_booking_id[$booking['Booking Id']]) && 
                        $cache_block_booking_id[$booking['Booking Id']]
                ){
                    $booking_id = $cache_block_booking_id[$booking['Booking Id']];
                } else {
                    $booking_id =  $this->Import_mapping_model->get_mapping_booking_id($booking['Booking Id']);
                    $cache_block_booking_id[$booking['Booking Id']] = $booking_id;
                }

                $booking_data_fileds = Array(
                    "booking_id" => $booking_id['new_id'],
                    "room_id" => isset($room_id['new_id']) &&  $room_id['new_id'] ? $room_id['new_id'] : 0,
                    "room_type_id" => isset($room_type_id['new_id']) && $room_type_id['new_id'] ? $room_type_id['new_id'] : 0,
                    "check_in_date" => $booking['Check In Date'] == '' ? null : $booking['Check In Date'],
                    "check_out_date" => $booking['Check Out Date'] == '' ? null : $booking['Check Out Date']
                );

                $booking_filed = $this->Booking_field_model->create_booking_fields($booking_data_fileds);

                $data_import_mapping = Array(
                    "new_id" => $booking_filed,
                    "old_id" => $booking['Booking Room History'],
                    "company_id" => $this->company_id,
                    "type" => "booking_block"
                );
                $import_data = $this->Import_mapping_model->insert_import_mapping($data_import_mapping);
            }
        }
    }

    function import_extras_csv($value){

        $cache_extras = $cache_charge_type_id = $cache_booking_extras = $cache_booking_id = array();

        foreach ($value as $extra) {

            if(
                isset($cache_extras[$extra['Extra Id']]) && 
                    $cache_extras[$extra['Extra Id']]
            ){
                $extras = $cache_extras[$extra['Extra Id']];
            } else {
                $extras = $this->Import_mapping_model->get_extra_mapping($extra['Extra Id']);
                $cache_extras[$extra['Extra Id']] = $extras;
            }

            if(
                isset($cache_charge_type_id[$extra['Charge Type']]) && 
                    $cache_charge_type_id[$extra['Charge Type']]
            ){
                $charge_type_id = $cache_charge_type_id[$extra['Charge Type']];
            } else {
                $charge_type_id = $this->Import_mapping_model->get_mapping_charge_id($extra['Charge Type']);
                $cache_charge_type_id[$extra['Charge Type']] = $charge_type_id;
            }
            
            if(empty($extras)){

                $data = array (
                    'extra_name' => $extra['Extra Name'] != '' ? $extra['Extra Name']  : null ,
                    'company_id' => $this->company_id,
                    'extra_type' => $extra['Extra Type'] != '' ? $extra['Extra Type'] : null ,
                    'charging_scheme' => $extra['Charging Scheme'] != '' ? $extra['Charging Scheme'] : null ,
                    'show_on_pos' => $extra['Show on POS'],
                    'charge_type_id' => $charge_type_id['new_id'] ? $charge_type_id['new_id'] : 0
                );

                $extra_id = $this->Extra_model->create_all_extras($data);

                $rate_extra_data = array(
                    'rate' => $extra['Rate'] != '' ? $extra['Rate'] : 0 ,
                    'currency_id' => $extra['Curreny'] != '' ? $extra['Curreny'] : null,
                    'extra_id' => $extra_id
                );
                $rate_extra = $this->Rate_model->create_extra_rate($rate_extra_data);

                $data_import_mapping = Array(
                    "new_id" => $extra_id,
                    "old_id" => $extra['Extra Id'],
                    "company_id" => $this->company_id,
                    "type" => "extra"
                );
                $import_data = $this->Import_mapping_model->insert_import_mapping($data_import_mapping);

            } else {
                $extra_id = $extras['new_id'];
            }

            if($extra['Booking Id']){

                if(
                    isset($cache_booking_extras[$extra['Booking Id']]) && 
                        $cache_booking_extras[$extra['Booking Id']]
                ){
                    $booking_extra = $cache_booking_extras[$extra['Booking Id']];
                } else {
                    $booking_extra = $this->Import_mapping_model->get_booking_extras($extra['Booking Id']);
                    $cache_booking_extras[$extra['Booking Id']] = $booking_extra;
                }

                if(empty($booking_extra)){

                    if(
                        isset($cache_booking_id[$extra['Booking Id']]) && 
                            $cache_booking_id[$extra['Booking Id']]
                    ){
                        $booking_id = $cache_booking_id[$extra['Booking Id']];
                    } else {
                        $booking_id = $this->Import_mapping_model->get_mapping_booking_id($extra['Booking Id']);
                        $cache_booking_id[$extra['Booking Id']] = $booking_id;
                    }

                    

                    $booking_extra_id =  $this->Booking_extra_model->create_booking_extra($booking_id['new_id'],$extra_id,$extra['Start Date'],$extra['End Date'],$extra['Quantity'],$extra['Default Rate']);

                    $data_import_mapping = Array(
                        "new_id" => $booking_extra_id,
                        "old_id" => $extra['Booking Extra Id'],
                        "company_id" => $this->company_id,
                        "type" => "extra_booking"
                    );
                    $import_data = $this->Import_mapping_model->insert_import_mapping($data_import_mapping);
                }
            }
        }
    }

    function import_payments_csv($value){
        $cache_get_payment_type = array();
        $cache_customer_id = array();
        $cache_booking_id = array();
        $cache_get_import_payment = array();

        foreach ($value as $payment) {

            if(
                isset($cache_get_payment_type[$payment['Payment Type']]) && 
                    $cache_get_payment_type[$payment['Payment Type']]
            ){
                $get_payment_type = $cache_get_payment_type[$payment['Payment Type']];
            } else {
                $get_payment_type = $this->Payment_model->get_payment_types_by_name($payment['Payment Type'], $this->company_id);
                $cache_get_payment_type[$payment['Payment Type']] = $get_payment_type;
            }

            if(empty($get_payment_type)){
                $read_only = $payment['Read Only'] == 'true' ? 1 : 0 ;
                $payment_id = $this->Payment_model->create_payment_type($this->company_id, $payment['Payment Type'],$read_only);
            } else {
                $payment_id = isset($get_payment_type[0]->payment_type_id) ? $get_payment_type[0]->payment_type_id : ' ' ;
            }


            if(
                isset($cache_customer_id[$payment['Customer Id']]) && 
                    $cache_customer_id[$payment['Customer Id']]
            ){
                $customer_id = $cache_customer_id[$payment['Customer Id']];
            } else {
                $customer_id =  $this->Import_mapping_model->get_mapping_customer_id($payment['Customer Id']);
                $cache_customer_id[$payment['Customer Id']] = $customer_id;
            }


            if(
                isset($cache_booking_id[$payment['Booking Id']]) && 
                    $cache_booking_id[$payment['Booking Id']]
            ){
                $booking_id = $cache_booking_id[$payment['Booking Id']];
            } else {
                $booking_id =  $this->Import_mapping_model->get_mapping_booking_id($payment['Booking Id']);
                $cache_booking_id[$payment['Booking Id']] = $booking_id;
            }

            if(!empty($payment['Payment Id'])){

                if(
                    isset($cache_get_import_payment[$payment['Payment Id']]) && 
                        $cache_get_import_payment[$payment['Payment Id']]
                ){
                    $get_import_payment = $cache_get_import_payment[$payment['Payment Id']];
                } else {
                    $get_import_payment = $this->Import_mapping_model->get_mapping_payment_id($payment['Payment Id']);
                    $cache_get_import_payment[$payment['Payment Id']] = $get_import_payment;
                }
                
                if(empty($get_import_payment)){
                    $data = Array(
                        "description" => $payment['Description'],
                        "date_time" => $payment['Date Time'] == '' ? null : $payment['Date Time'],
                        "booking_id" => $booking_id['new_id'],
                        "payment_type_id" => $payment_id,
                        "amount" => $payment['Amount'] == '' ? null : $payment['Amount'],
                        "credit_card_id" => $payment['Credit Card Id'] == '' ? null : $payment['Credit Card Id'],
                        "selling_date" => $payment['Selling Date'] == '' ? null : $payment['Selling Date'],
                        "customer_id" => $customer_id['new_id'],
                        "payment_status" => $payment['Payment Status'] == '' ? null : $payment['Payment Status'],
                        "is_captured" => $payment['Payment Capture'] == 'true' ? 1 : 0,
                        "read_only" => $payment['Payment Read Only'] == 'true' ? 1 : 0
                    );

                    $payment_create_id = $this->Payment_model->insert_payment($data);

                    $data_import_mapping = Array(
                        "new_id" => $payment_create_id['payment_id'],
                        "old_id" => $payment['Payment Id'],
                        "company_id" => $this->company_id,
                        "type" => "payment"
                    );

                    $import_data = $this->Import_mapping_model->insert_import_mapping($data_import_mapping);
                }
            }
        }
    }

    function import_statements_csv($value){

        $cache_get_statements = array();
        $cache_booking_id = array();

        foreach($value as $statement){
            if($statement['Statment Id']){
                
                if(
                    isset($cache_get_statements[$statement['Statment Id']]) && 
                        $cache_get_statements[$statement['Statment Id']]
                ){
                    $statements = $cache_get_statements[$statement['Statment Id']];
                } else {
                    $statements = $this->Import_mapping_model->get_mapping_statement_id($statement['Statment Id']);
                    $cache_get_statements[$statement['Statment Id']] = $statements;
                }

                
                if(
                    isset($cache_booking_id[$statement['Booking Id']]) && 
                        $cache_booking_id[$statement['Booking Id']]
                ){
                    $booking_id = $cache_booking_id[$statement['Booking Id']];
                } else {
                    $booking_id =  $this->Import_mapping_model->get_mapping_booking_id($statement['Booking Id']);
                    $cache_booking_id[$statement['Booking Id']] = $booking_id;
                }

                if(empty($statements)){

                    $current_date = date('Y-m-d H:i:s');
                    $statement_date = date('Y-m-d', strtotime($current_date));
                    $data = array(
                        "statement_number" => $statement['Statement Number'] ,
                        "creation_date" => $statement['Creation Date'] ? $statement['Creation Date'] : date('Y-m-d H:i:s'),
                        "statement_name" => $statement['Statement Name'] ? $statement['Statement Name'] : "Statement of ".date('M Y', strtotime($statement_date) )
                    );

                    $statement_id =  $this->Statement_model->create_statement($data);

                    $booking_statement = array(
                        "booking_id" => $booking_id['new_id'],
                        "statement_id" => $statement_id
                    );

                    $this->Statement_model->create_statement_booking($booking_statement);

                    $data_import_mapping = Array(
                        "new_id" => $statement_id,
                        "old_id" => $statement['Statment Id'],
                        "company_id" => $this->company_id,
                        "type" => "statement"
                    );

                    $import_data = $this->Import_mapping_model->insert_import_mapping($data_import_mapping);
                }
            }
        }
    }

    function import_company_setting($values){

        $value = json_decode($values, true);

        $company_data = array(
            'is_total_balance_include_forecast' => isset($value['Feature settings']['Total Balance Include Forecast']) ? $value['Feature settings']['Total Balance Include Forecast'] : ""  ,
            'auto_no_show'  => isset($value['Feature settings']['Auto No Show']) ? $value['Feature settings']['Auto No Show'] : "",
            'book_over_unconfirmed_reservations'=> isset($value['Feature settings']['Book Over Unconfirmed Reservations']) ? $value['Feature settings']['Book Over Unconfirmed Reservations'] : "" ,
            'send_invoice_email_automatically' => isset($value['Feature settings']['Send Invoice Email Automatically']) ? $value['Feature settings']['Send Invoice Email Automatically']: "",
            'hide_decimal_places'=> isset($value['Feature settings']['Hide Decimal Places']) ? $value['Feature settings']['Hide Decimal Places']: "",
            'automatic_email_confirmation' => isset($value['Feature settings']['Automatic Email Confirmation']) ? $value['Feature settings']['Automatic Email Confirmation'] : "",
            'automatic_email_cancellation' => isset($value['Feature settings']['Automatic Email Cancellation']) ? $value['Feature settings']['Automatic Email Cancellation'] : "",
            'send_booking_notes' => isset($value['Feature settings']['Send Booking Notes']) ? $value['Feature settings']['Send Booking Notes']: "",
            'make_guest_field_mandatory' => isset($value['Feature settings']['Make Guest Field Mandatory']) ? $value['Feature settings']['Make Guest Field Mandatory'] : "",
            'include_cancelled_noshow_bookings' => isset($value['Feature settings']['Include Cancelled Noshow Bookings']) ? $value['Feature settings']['Include Cancelled Noshow Bookings']: "",
            'hide_forecast_charges' => isset($value['Feature settings']['Hide Forecast Charges']) ? $value['Feature settings']['Hide Forecast Charges']:"",
            'send_copy_to_additional_emails' => isset($value['Feature settings']['Send Copy To Additional Emails']) ? $value['Feature settings']['Send Copy To Additional Emails']:"",
            'additional_company_emails' => isset($value['Feature settings']['Additional Company Emails'])? $value['Feature settings']['Additional Company Emails']: "",
            'default_charge_name' => isset($value['Feature settings']['Default Charge Name']) ? $value['Feature settings']['Default Charge Name']:"",
            'default_room_singular' => isset($value['Feature settings']['Default Room Singular']) ? $value['Feature settings']['Default Room Singular'] : "",
            'default_room_plural' => isset($value['Feature settings']['Default Room Plural']) ? $value['Feature settings']['Default Room Plural'] : "",
            'default_room_type'=> isset($value['Feature settings']['Default Room Type'])? $value['Feature settings']['Default Room Type'] : "",
            'date_format' => isset($value['Feature settings']['Date Format']) ? $value['Feature settings']['Date Format'] : "",
            'default_checkin_time' => isset($value['Feature settings']['Default Checkin Time'])? $value['Feature settings']['Default Checkin Time'] : "",
            'default_checkout_time' => isset($value['Feature settings']['Default Checkout Time']) ? $value['Feature settings']['Default Checkout Time'] : "",
            'enable_hourly_booking' => isset($value['Feature settings']['Enable Hourly Booking']) ? $value['Feature settings']['Enable Hourly Booking'] : "",
            'enable_api_access'=> isset($value['Feature settings']['Enable Api Access']) ? $value['Feature settings']['Enable Api Access'] : "",
            'booking_cancelled_with_balance' => isset($value['Feature settings']['Booking Cancelled With Balance']) ? $value['Feature settings']['Booking Cancelled With Balance'] : "",
            'enable_new_calendar' => isset($value['Feature settings']['Enable New Calendar']) ? $value['Feature settings']['Enable New Calendar'] : "",
            'hide_room_name' => isset($value['Feature settings']['Hide Room Name']) ? $value['Feature settings']['Hide Room Name'] : "",
            'restrict_booking_dates_modification' => isset($value['Feature settings']['Restrict Booking Dates Modification']) ? $value['Feature settings']['Restrict Booking Dates Modification'] : "",
            'restrict_checkout_with_balance' => isset($value['Feature settings']['Restrict Checkout With Balance']) ? $value['Feature settings']['Restrict Checkout With Balance'] : "",
            'show_guest_group_invoice' => isset($value['Feature settings']['Show Guest Group Invoice']) ? $value['Feature settings']['Show Guest Group Invoice'] : "",
            'ui_theme' => isset($value['Feature settings']['Ui Theme']) ? $value['Feature settings']['Ui Theme'] : "",
            'is_display_tooltip' => isset($value['Feature settings']['Display Tooltip']) ? $value['Feature settings']['Display Tooltip'] : "",
            'ask_for_review_in_invoice_email' => isset($value['Feature settings']['Ask For Review In Invoice Email']) ? $value['Feature settings']['Ask For Review In Invoice Email'] : "",
            'redirect_to_trip_advisor' => isset($value['Feature settings']['Redirect To Trip Advisor']) ? $value['Feature settings']['Redirect To Trip Advisor'] : "",
            'email_confirmation_for_ota_reservations' => isset($value['Feature settings']['Email Confirmation For Ota Reservations']) ? $value['Feature settings']['Email Confirmation For Ota Reservations'] : "",
            'email_cancellation_for_ota_reservations' => isset($value['Feature settings']['Email Cancellation For Ota Reservations']) ? $value['Feature settings']['Email Cancellation For Ota Reservations'] : "",
            'allow_non_continuous_bookings' => isset($value['Feature settings']['Allow Non Continuous Bookings']) ? $value['Feature settings']['Allow Non Continuous Bookings'] : "",
            'maximum_no_of_blocks' => isset($value['Feature settings']['Maximum No Of Blocks']) ? $value['Feature settings']['Maximum No Of Blocks'] : "",
            'force_room_selection' => isset($value['Feature settings']['Force Room Selection']) ? $value['Feature settings']['Force Room Selection'] : "",
            'automatic_feedback_email' => isset($value['Feature settings']['Automatic Feedback Email']) ? $value['Feature settings']['Automatic Feedback Email'] : "",
            'avoid_dmarc_blocking' => isset($value['Feature settings']['Avoid Dmarc Blocking']) ? $value['Feature settings']['Avoid Dmarc Blocking'] : "",
            'allow_free_bookings' => isset($value['Feature settings']['Allow Free Bookings']) ? $value['Feature settings']['Allow Free Bookings'] : "",
            'customer_modify_booking' => isset($value['Feature settings']['Customer Modify Booking']) ? $value['Feature settings']['Customer Modify Booking'] : "",

            'housekeeping_auto_clean_is_enabled' => isset($value['Housekeepings']['Housekeeping Auto Clean is Enabled']) ? $value['Housekeepings']['Housekeeping Auto Clean is Enabled'] : "",
            'housekeeping_auto_clean_time' => isset($value['Housekeepings']['Housekeeping Auto Clean Time']) ? $value['Housekeepings']['Housekeeping Auto Clean Time'] : "",
            'housekeeping_day_interval_for_full_cleaning' => isset($value['Housekeepings']['Housekeeping Day Interval For Full Cleaning']) ? $value['Housekeepings']['Housekeeping Day Interval For Full Cleaning'] : "",
            'housekeeping_auto_dirty_is_enabled' => isset($value['Housekeepings']['Housekeeping Auto Dirty is Enabled']) ? $value['Housekeepings']['Housekeeping Auto Dirty is Enabled'] : "",
            'housekeeping_auto_dirty_time' => isset($value['Housekeepings']['Housekeeping Auto Dirty Time']) ? $value['Housekeepings']['Housekeeping Auto Dirty Time'] : "",

            'invoice_email_header' => isset($value['Email Templates']['Invoice Email Header']) ? $value['Email Templates']['Invoice Email Header'] : "",
            'booking_confirmation_email_header' => isset($value['Email Templates']['Booking Confirmation Email Header']) ? $value['Email Templates']['Booking Confirmation Email Header'] : "",

            'reservation_policies'=> isset($value['Policies']['Reservation Policies']) ? $value['Policies']['Reservation Policies'] : "",
            'check_in_policies'=> isset($value['Policies']['Check In Policies']) ? $value['Policies']['Check In Policies'] : "",
            'show_logo_on_registration_card'=> isset($value['Registration cards']['Show logo on registration card']) ? $value['Registration cards']['Show logo on registration card'] : "",
            'show_rate_on_registration_card'=> isset($value['Registration cards']['Show rate on registration card']) ? $value['Registration cards']['Show rate on registration card'] : "",

            'default_currency_id'=> isset($value['Company Details']['Default Currency']) ? $value['Company Details']['Default Currency'] : "",
            'default_language'=> isset($value['Company Details']['Default language']) ? $value['Company Details']['Default language'] : "",
            'address'=> isset($value['Company Details']['Address']) ? $value['Company Details']['Address'] : "",
            'city'=> isset($value['Company Details']['City']) ? $value['Company Details']['City'] : "",
            'region'=> isset($value['Company Details']['Region']) ? $value['Company Details']['Region'] : "",
            'country'=> isset($value['Company Details']['Country']) ? $value['Company Details']['Country'] : "",
            'postal_code'=> isset($value['Company Details']['Postal code']) ? $value['Company Details']['Postal code'] : "",
            'phone'=> isset($value['Company Details']['Phone']) ? $value['Company Details']['Phone'] : "",
            'fax'=> isset($value['Company Details']['Fax']) ? $value['Company Details']['Fax'] : "",
            'email'=> isset($value['Company Details']['Email']) ? $value['Company Details']['Email'] : "",
            'time_zone'=> isset($value['Company Details']['Time zone']) ? $value['Company Details']['Time zone'] : "",
            'number_of_rooms'=> isset($value['Company Details']['Rooms']) ? $value['Company Details']['Rooms'] : "",
            'website' => isset($value['Company Details']['Website']) ? $value['Company Details']['Website'] : "",


            'invoice_header'=> isset($value['Invoice headers']['Invoice Header']) ? $value['Invoice headers']['Invoice Header'] : "",
            'statement_number'=> isset($value['Invoice headers']['Statement Number']) ? $value['Invoice headers']['Statement Number'] : "",


            'allow_same_day_check_in'=> isset($value['Online Bookings']['Allow same day check in']) ? $value['Online Bookings']['Allow same day check in'] : "",
            'require_paypal_payment'=> isset($value['Online Bookings']['Require paypal payment']) ? $value['Online Bookings']['Require paypal payment'] : "",
            'paypal_account'=> isset($value['Online Bookings']['Paypal account']) ? $value['Online Bookings']['Paypal account'] : "",
            'percentage_of_required_paypal_payment'=> isset($value['Online Bookings']['Percentage of required paypal payment']) ? $value['Online Bookings']['Percentage of required paypal payment'] : "",
            'booking_engine_booking_status'=> isset($value['Online Bookings']['Booking engine booking status']) ? $value['Online Bookings']['Booking engine booking status'] : "",
            'email_confirmation_for_booking_engine'=> isset($value['Online Bookings']['Email confirmation for booking engine']) ? $value['Online Bookings']['Email confirmation for booking engine'] : "",
            'booking_engine_tracking_code'=> isset($value['Online Bookings']['Booking engine tracking code']) ? $value['Online Bookings']['Booking engine tracking code'] : "",

            'selling_date'=> isset($value['Night Audits']['Selling date']) ? $value['Night Audits']['Selling date'] : "",
            'night_audit_auto_run_is_enabled'=> isset($value['Night Audits']['Night audit auto run is enabled']) ? $value['Night Audits']['Night audit auto run is enabled'] : "",
            'night_audit_auto_run_time'=> isset($value['Night Audits']['Night audit auto run time']) ? $value['Night Audits']['Night audit auto run time'] : "",

            'night_audit_ignore_check_out_date'=> isset($value['Night Audits']['Night audit ignore check out date']) ? $value['Night Audits']['Night audit ignore check out date'] : "",
            'night_audit_charge_in_house_only'=> isset($value['Night Audits']['Night audit charge in house only']) ? $value['Night Audits']['Night audit charge in house only'] : "",
            'night_audit_force_check_out'=> isset($value['Night Audits']['Night audit force check out']) ? $value['Night Audits']['Night audit force check out'] : ""
            // 'default_charge_name' => $charge_type_id['new_id'] ? $charge_type_id['new_id'] : 0 

        );
        $this->Company_model->update_company($this->company_id, $company_data);

        // $teams = $value['Teams'];

        // foreach ($teams as $key => $team) {
        //     $data = array(
        //         'email'              => $team['Email'],
        //         'current_company_id' => $this->company_id,
        //         'first_name'         => $team['First Name'],
        //         'last_name'          => $team['Last Name'],
        //         'password'           => $team['Password']
        //     );

        //     $get_user = $this->User_model->get_user_by_email($team['Email']);

        //     if(!$get_user){

        //         $user =  $this->users->create_user($data, true);

        //         $this->User_model->add_teams($this->company_id, $user['user_id'],$team['permission']);
        //     }
        // }

        $booking_fields = $value['Booking Fields'];


        if($booking_fields != "" ){
            foreach ($booking_fields as $key => $fields) {
                $booking_field_id = $this->Booking_field_model->get_the_booking_fields_by_name($key,$this->company_id);

                if($booking_field_id){
                    $booking_fieldid = $booking_field_id[0]['id'];
                } else {
                    $booking_fieldid = $this->Booking_field_model->create_booking_field($this->company_id, $key);
                }

                $data = array(
                    'show_on_booking_form' => $fields['show_on_booking_form'],
                    'show_on_registration_card' => $fields['show_on_registration_card'],
                    'show_on_in_house_report' => $fields['show_on_in_house_report'],
                    'show_on_invoice' => $fields['show_on_invoice'],
                    'is_required' => $fields['is_required']
                );
                $this->Booking_field_model->update_booking_field($booking_fieldid,$data);
            }
        }

        $customer_fields = $value['Customer Fields'];

        if($customer_fields != "" ){
            foreach ($customer_fields as $key => $customer_field_data) {
                $customer_field_id = $this->Customer_field_model->get_customer_field_by_name($this->company_id, $key);

                if($customer_field_id){
                    $customer_fieldid = $customer_field_id[0]['id'];
                } else {
                    $customer_fieldid = $this->Customer_field_model->create_customer_field($this->company_id, $key);
                }

                $customer_data = array(
                    'show_on_customer_form' => $customer_field_data['show_on_customer_form'],
                    'show_on_registration_card' => $customer_field_data['show_on_registration_card'],
                    'show_on_in_house_report' => $customer_field_data['show_on_in_house_report'],
                    'show_on_invoice' => $customer_field_data['show_on_invoice'],
                    'is_required' => $customer_field_data['is_required']
                );

                $this->Customer_field_model->update_customer_field($customer_fieldid,$customer_data);
            }
        }

        $payment_types = $value['Payment Types'];

        if($payment_types){
            foreach ($payment_types as $payment_type) {
                $existing_payment_type = $this->Payment_model->get_payment_types_by_name($payment_type['payment_type'], $this->company_id);

                if(empty($existing_payment_type)){

                    $payment_type = $this->Payment_model->create_payment_type($this->company_id,$payment_type['payment_type']);
                }
            }
        }

        $charge_types = $value['Charge Types'];

        if($charge_types){
            foreach ($charge_types as $charge_type) {
                $existing_charge_type = $this->Charge_type_model->get_charge_type_by_name($charge_type['name'], $this->company_id);

                if(empty($existing_charge_type)){
                    $charge_type_id = $this->Charge_type_model->create_charge_type($this->company_id, $charge_type['name']);

                    $taxes = explode(',', $charge_type['taxes']);

                    foreach ($taxes as $tax_type) {
                        if($tax_type){
                            $tax_type_id = $this->Tax_model->get_tax_type_by_name($tax_type);
                            $charge_taxes = $this->Charge_type_model->get_charge_tax($charge_type_id, $tax_type_id);
                            if(!$charge_taxes){
                                $this->Charge_type_model->add_charge_type_tax($charge_type_id, $tax_type_id);
                            }
                        }
                    }

                    $data = array(
                        "is_default_room_charge_type" => $charge_type['is_default_room_charge_type'],
                        "is_room_charge_type" => $charge_type['is_room_charge_type']
                    );
                    $this->Charge_type_model->update_charge_type($charge_type_id, $data);
                }
            }
        }

        $room_types = $value['Room Types'];

        if($room_types){
            foreach ($room_types as $room_type) {
                $existing_room_type = $this->Room_type_model->get_room_type_name($room_type['name'], $this->company_id);

                if(empty($existing_room_type)){
                    $room_type = $this->Room_type_model->create_room_type($this->company_id, $room_type['name'],$room_type['acronym'],$room_type['max_adults'],$room_type['max_children']);
                }
            }
        }

        $customer_types = $value['Customer Types'];

        if($customer_types){
            foreach ($customer_types as $customer_type) {
                $existing_customer_type = $this->Customer_type_model->get_customer_type_by_name($this->company_id,$customer_type['name']);

                if(empty($existing_customer_type)){

                    $customer_type = $this->Customer_type_model->create_customer_type($this->company_id, $customer_type['name']);
                }
            }
        }

        $booking_sources = $value['Booking Source'];

        if($booking_sources){
            foreach ($booking_sources as $booking_source) {
                $existing_booking_source = $this->Booking_source_model->get_booking_source_by_company($this->company_id, $booking_source['name']);
                
                if(empty($existing_booking_source)){
                    $booking_source_id = $this->Booking_source_model->create_booking_source($this->company_id, $booking_source['name']);
                } else {
                    $booking_source_id = $existing_booking_source;
                }

                $data = array(
                    'commission_rate' => $booking_source['commission_rate'],
                    'is_hidden' => $booking_source['is_hidden']
                );

                $this->Booking_source_model->update_booking_source($booking_source_id,$data);
            }
        }

        $room_types = $value['Room Types'];
        if($room_types){
            foreach($room_types as $room_type){
                $room_type_id =  $this->Import_mapping_model->get_mapping_room_type_id($room_type['id']);
                if($room_type_id){
                    $data = array(
                        'description' => $room_type['description']
                    );
                    $this->Room_type_model->update_room_type($room_type_id['new_id'], $data);
                }
            }
        }

        $rate_plans = $value['Rate Plan'];
        if($rate_plans){
            foreach($rate_plans as $rate_plan){
                $rate_plan_id = $this->Import_mapping_model->get_rate_plan_mapping_id($rate_plan['rate_plan_id']);
                if($rate_plan_id){
                    $data = array(
                        'description' => $rate_plan['description']
                    );
                    $this->Rate_plan_model->update_rate_plan($data,$rate_plan_id['new_id']);

                }
            }
        }
    }

    function get_roomsy_data(){
        $export_company_id  = $this->input->post('company_id');

        if(!$this->Export_data_model->is_company_exist($export_company_id)){
            echo ("<script LANGUAGE='JavaScript'>
                    window.alert('Company ID is Invalid.');
                    window.location.href='".base_url()."settings/company/import';
                    </script>");
        }

        if($this->input->post('removd_old_data') == 1){

            $get_bookings = $this->Booking_model->get_bookings_company($this->company_id);

            if($get_bookings){
                foreach ($get_bookings as $key => $booking) {
                    $this->Charge_model->delete_charges($booking['booking_id']);
                    $this->Payment_model->delete_payments($booking['booking_id']);
                }
                $this->Booking_model->delete_bookings($this->company_id);
            }
            $this->Booking_source_model->delete_booking_sources($this->company_id);
            $this->Booking_field_model->delete_booking_fields($this->company_id);
            $this->Customer_type_model->delete_customer_types($this->company_id);
            $this->Customer_field_model->delete_customer_fields($this->company_id);
            $this->Customer_model->delete_customers($this->company_id);
            $this->Payment_model->delete_payment_types($this->company_id);
            $this->Charge_type_model->delete_charge_types($this->company_id);
            $this->Room_type_model->delete_room_types($this->company_id);
            $this->Room_model->delete_rooms($this->company_id);
            $this->Tax_model->delete_tax_types($this->company_id);
            $this->Rate_plan_model->delete_rate_plans($this->company_id);
            $this->Import_mapping_model->delete_mapping_field($this->company_id);
            $this->Extra_model->delete_extras($this->company_id);
        }

        $this->import_rooms_data($export_company_id);
        $this->import_taxes_data($export_company_id);
        $this->import_customers_data($export_company_id);
        $this->import_charges_data($export_company_id);
        $this->import_rates_data($export_company_id);
        $this->import_bookings_data($export_company_id);
        $this->import_extras_data($export_company_id);
        $this->import_payments_data($export_company_id);
        $this->import_statements_data($export_company_id);
        $this->import_company_settings_data($export_company_id);

        echo ("<script LANGUAGE='JavaScript'>
                window.alert('Succesfully Imported');
                window.location.href='".base_url()."';
            </script>");
    }

    function import_rooms_data($company_id){

        $rooms = $this->Export_data_model->get_export_room_types($company_id);
        $rooms_csv= array();

        foreach($rooms as $key => $data)
        {
            $rooms_row = array();
            $rooms_row['Room Type Name'] = $data['name'];
            $rooms_row['Room Type Id'] = $data['room_type_id'];
            $rooms_row['Room Id'] = $data['room_id'];
            $rooms_row['Room Name'] = $data['room_name'];
            $rooms_row['Acronym'] = $data['acronym'];
            $rooms_row['Max Occupancy'] = $data['max_occupancy'];
            $rooms_row['Min Occupancy'] = $data['min_occupancy'];
            $rooms_row['Max Adults'] = $data['max_adults'];
            $rooms_row['Max Children'] = $data['max_children'];
            $rooms_row['Room Type Can be Sold online'] = $data['room_type_sold_online'] == 1 ?'true':'false';
            $rooms_row['Room Can be Sold online'] = $data['room_sold_online'] == 1 ?'true':'false';
            $rooms_row['Sort Order'] = $data['sort_order'];
            $rooms_row['Floor'] = $data['floor_name'];
            $rooms_row['Location'] = $data['location_name'];
            $rooms_row['Room Charge'] = $data['default_room_charge'];
            $rooms_row['Description'] = "";
            $rooms_row['Status'] = $data['status'];
            $rooms_csv[] = $rooms_row;
        }

        $setting_res = $this->company_setting_csv_data($company_id);

        $csv_data['rooms'] = $rooms_csv;
        $csv_data['settings'] = json_encode($setting_res);

        if (isset($csv_data['rooms'])) {
            $this->import_rooms_csv($csv_data['rooms'], $csv_data['settings']);
        }
    }

    function company_setting_csv_data($company_id){

        $settings = $this->Export_data_model->get_company($company_id);
        $general_settings = array();

        $feature_settings = array(
            'Total Balance Include Forecast' => $settings['is_total_balance_include_forecast'],
            'Auto No Show' => $settings['auto_no_show'],
            'Book Over Unconfirmed Reservations' => $settings['book_over_unconfirmed_reservations'],
            'Send Invoice Email Automatically' => $settings['send_invoice_email_automatically'],
            'Hide Decimal Places' => $settings['hide_decimal_places'],
            'Automatic Email Confirmation' => $settings['automatic_email_confirmation'],
            'Automatic Email Cancellation' => $settings['automatic_email_cancellation'],
            'Send Booking Notes' => $settings['send_booking_notes'],
            'Make Guest Field Mandatory' => $settings['make_guest_field_mandatory'],
            'Include Cancelled Noshow Bookings' => $settings['include_cancelled_noshow_bookings'],
            'Hide Forecast Charges' => $settings['hide_forecast_charges'],
            'Send Copy To Additional Emails' => $settings['send_copy_to_additional_emails'],
            'Additional Company Emails' => $settings['additional_company_emails'],
            'Default Charge Name' => $settings['default_charge_name'],
            'Default Room Singular' => $settings['default_room_singular'],
            'Default Room Plural' => $settings['default_room_plural'],
            'Default Room Type' => $settings['default_room_type'],
            'Date Format' => $settings['date_format'],
            'Default Checkin Time' => $settings['default_checkin_time'],
            'Default Checkout Time' => $settings['default_checkout_time'],
            'Enable Hourly Booking' => $settings['enable_hourly_booking'],
            'Enable Api Access' => $settings['enable_api_access'],
            'Booking Cancelled With Balance' => $settings['booking_cancelled_with_balance'],
            'Enable New Calendar' => 1,
            'Hide Room Name' => $settings['hide_room_name'],
            'Restrict Booking Dates Modification' => $settings['restrict_booking_dates_modification'],
            'Restrict Checkout With Balance' => $settings['restrict_checkout_with_balance'],
            'Show Guest Group Invoice' => $settings['show_guest_group_invoice'],
            'Ui Theme' => $settings['ui_theme'],
            'Display Tooltip' => $settings['is_display_tooltip'],
            'Ask For Review In Invoice Email' => $settings['ask_for_review_in_invoice_email'],
            'Redirect To Trip Advisor' => $settings['redirect_to_trip_advisor'],
            'Email Confirmation For Ota Reservations' => $settings['email_confirmation_for_ota_reservations'],
            'Email Cancellation For Ota Reservations' => $settings['email_cancellation_for_ota_reservations'],
            'Allow Non Continuous Bookings' => $settings['allow_non_continuous_bookings'],
            'Maximum No Of Blocks' => $settings['maximum_no_of_blocks'],
            'Force Room Selection' => $settings['force_room_selection'],
            'Automatic Feedback Email' => $settings['automatic_feedback_email'],
            'Avoid Dmarc Blocking' => $settings['avoid_dmarc_blocking'],
            'Allow Free Bookings' => $settings['allow_free_bookings'],
            'Customer Modify Booking' => $settings['customer_modify_booking']

        );

        $housekeeping = array(
            'Housekeeping Auto Clean is Enabled' => $settings['housekeeping_auto_clean_is_enabled'],
            'Housekeeping Auto Clean Time' => $settings['housekeeping_auto_clean_time'],
            'Housekeeping Day Interval For Full Cleaning' => $settings['housekeeping_day_interval_for_full_cleaning'],
            'Housekeeping Auto Dirty is Enabled' => $settings['housekeeping_auto_dirty_is_enabled'],
            'Housekeeping Auto Dirty Time' => $settings['housekeeping_auto_dirty_time']
        );

        $night_audit = array(
            "Selling date" => $settings['selling_date'],
            "Night audit auto run is enabled" => $settings['night_audit_auto_run_is_enabled'],
            "Night audit auto run time" => $settings['night_audit_auto_run_time'],
            "Night audit ignore check out date" => $settings['night_audit_ignore_check_out_date'],
            "Night audit charge in house only" => $settings['night_audit_charge_in_house_only'],
            // "default_room_charge_type_id" => $settings['default_room_charge_type_id'],
            "Night audit force check out" => $settings['night_audit_force_check_out'],
            "room_charge_type_id" => $settings['default_charge_name']
        );

        $booking_engine = $this->Export_data_model->get_common_booking_engine_fields($company_id);

        $online_booking = array(
            'Allow same day check in' => $settings['allow_same_day_check_in'],
            'Require paypal payment' => $settings['require_paypal_payment'],
            'Paypal account' => $settings['paypal_account'],
            'Percentage of required paypal payment' => $settings['percentage_of_required_paypal_payment'],
            'Booking engine booking status' => $settings['booking_engine_booking_status'],
            'Email confirmation for booking engine' => $settings['email_confirmation_for_booking_engine'],
            'Booking engine tracking code' => $settings['booking_engine_tracking_code'],
        );

        $email_templates = array(
            "Invoice Email Header" => $settings['invoice_email_header'],
            "Booking Confirmation Email Header" => $settings['booking_confirmation_email_header']
        );

        $invoice_header = array(
            'Invoice Header' => $settings['invoice_header'],
            'Statement Number' => $settings['statement_number']
        );

        $policies = array(
            "Reservation Policies" => $settings['reservation_policies'],
            "Check In Policies" => $settings['check_in_policies']
        );

        $registration_card = array(
            "Show logo on registration card" => $settings['show_logo_on_registration_card'],
            "Show rate on registration card" => $settings['show_rate_on_registration_card']
        );

        $company_detail = array(
            "Default Currency"=>$settings['default_currency_id'],
            "Default language"=>$settings['default_language'],
            "Address"=>$settings['address'],
            "City"=>$settings['city'],
            "Region"=>$settings['region'],
            "Country"=>$settings['country'],
            "Postal code"=>$settings['postal_code'],
            "Phone"=>$settings['phone'],
            "Fax"=>$settings['fax'],
            "Email"=>$settings['email'],
            "Time zone" => $settings['time_zone'],
            "Rooms" => $settings['number_of_rooms'],
            "Website" => $settings['website']
        );

        $teams = $this->Export_data_model->get_user_list($company_id,true);
        $permissions = $this->Export_data_model->get_all_user_permissions($company_id, false);

        
        $new_team = array();
        foreach ($teams as $key => $team) {
            $permission_array = array();
            foreach ($permissions as $key1 => $permission) {
                if(($team['user_id'] == $permission['user_id'])){
                    if($team['permission'] == 'is_employee'){
                        $permission_array[] = $permission['permission'];
                        // $teams[$key]['permission'] = $permission_array;
                        $new_team[$key]['permission'] = $permission_array;
                    }else{
                        $new_team[$key]['permission'] = $team['permission'];
                    }
                    $new_team[$key]['First Name'] = $team['first_name'];
                    $new_team[$key]['User Id'] = $team['user_id'];
                    $new_team[$key]['Last Name'] = $team['last_name'];
                    $new_team[$key]['Email'] = $team['email'];
                    $new_team[$key]['Password'] = $team['password'];

                }
            }
        }

        $booking_fields = $this->Export_data_model->get_booking_fields($company_id);
        $booking_field = array();
        if($booking_fields){

            foreach ($booking_fields as $key => $value) {
                $booking_data = array(
                    'show_on_booking_form' => $value['show_on_booking_form'],
                    'show_on_registration_card' => $value['show_on_registration_card'],
                    'show_on_in_house_report' => $value['show_on_in_house_report'],
                    'show_on_invoice' => $value['show_on_invoice'],
                    'is_required' => $value['is_required']
                );

                $booking_field[$value['name']] = $booking_data;
            }
        }

        $customer_fields = $this->Export_data_model->get_customer_fields($company_id);
        $customer_field = array();
        if($customer_fields){
            foreach ($customer_fields as $key => $value) {
                $customer_data = array(
                    'show_on_customer_form' => $value['show_on_customer_form'],
                    'show_on_registration_card' => $value['show_on_registration_card'],
                    'show_on_in_house_report' => $value['show_on_in_house_report'],
                    'show_on_invoice' => $value['show_on_invoice'],
                    'is_required' => $value['is_required']
                );

                $customer_field[$value['name']] = $customer_data;
            }
        }

        $payment_types = $this->Export_data_model->get_payment_types($company_id);

        $charge_types = $this->Export_data_model->get_charge_types($company_id);

        foreach ($charge_types as $key => $value) {

            $taxes = $this->Export_data_model->get_taxes($value['id']);
            $taxes_value = array();
            $tax_string = "";
            if($taxes){

                foreach ($taxes as $key1 => $value) {
                    if(isset($value['tax_type'])){
                        $taxes_value[]= $value['tax_type'];
                    }
                }
                $tax_string = implode(",", $taxes_value);
            }
            $charge_types[$key]['taxes'] = $tax_string ;
        }

        $room_types = $this->Export_data_model->get_room_types($company_id);

        $customer_types = $this->Export_data_model->get_customer_types($company_id);

        $booking_sources = $this->Export_data_model->get_booking_source($company_id,false, true);

        $rate_plans = $this->Export_data_model->get_rate_plans($company_id);

        $general_settings['Feature settings'] = $feature_settings;
        $general_settings['Housekeepings'] = $housekeeping;
        $general_settings['Email Templates'] = $email_templates;
        $general_settings['Policies'] = $policies;
        $general_settings['Registration cards'] = $registration_card;
        $general_settings['Company Details'] = $company_detail;
        $general_settings['Night Audits'] = $night_audit;
        $general_settings['Online Bookings'] = $online_booking;
        $general_settings['Teams'] = $new_team;
        $general_settings['Invoice headers'] = $invoice_header;
        $general_settings['Booking Fields'] = $booking_field;
        $general_settings['Customer Fields'] = $customer_field;
        $general_settings['Payment Types'] = $payment_types;
        $general_settings['Charge Types'] = $charge_types;
        // $general_settings['Room Types'] = $room_types;
        $general_settings['Customer Types'] = $customer_types;
        $general_settings['Booking Source'] = $booking_sources;
        $general_settings['Rate Plan'] = $rate_plans;
        $general_settings['Room Types'] = $room_types;

        return $general_settings;
    }

    function import_taxes_data($company_id){

        $taxes = $this->Export_data_model->get_all_tax_types($company_id);
        $results = array();

        foreach($taxes as $row => $field) {

            //check if "id" is already set and store the index value it exist within
            $existing_index = NULL;
            if($field['is_brackets_active'] == true){
                foreach($results as $result_row => $result_field) {


                    if ($result_field['tax_type_id'] == $field['tax_type_id']) {
                        $existing_index = $result_row;
                        break;
                    }
                }

                if (isset($existing_index)) {
                    //the "id" already exist, so add to the existing id
                    $dataArrayIndex = count($results[$existing_index]['price_bracket']);
                    $results[$existing_index]['price_bracket'][$dataArrayIndex]['start'] = $field['start_range'];
                    $results[$existing_index]['price_bracket'][$dataArrayIndex]['end'] = $field['end_range'];
                    $results[$existing_index]['price_bracket'][$dataArrayIndex]['rate'] = $field['price_bracket_rate'];
                    $results[$existing_index]['price_bracket'][$dataArrayIndex]['is_percentage'] = $field['is_price_bracket_percentage'];
                }
                else {
                    //the "id" does not exist, create it
                    $key = count($results);
                    $results[$key]['id'] = $field['id'];
                    $results[$key]['tax_type'] = $field['tax_type'];
                    $results[$key]['tax_rate'] = $field['taxrate'] ;
                    $results[$key]['is_percentage'] = $field['tax_percentage'] == 1 ? 'true' : 'false';;
                    $results[$key]['is_brackets_active'] = $field['is_brackets_active'];
                    $results[$key]['is_tax_inclusive'] = $field['is_tax_inclusive'];
                    $results[$key]['tax_type_id'] = $field['taxtype_id'];
                    $results[$key]['price_bracket'][0]['start'] = $field['start_range'];
                    $results[$key]['price_bracket'][0]['end'] = $field['end_range'];
                    $results[$key]['price_bracket'][0]['rate'] = $field['price_bracket_rate'];
                    $results[$key]['price_bracket'][0]['is_percentage'] = $field['is_price_bracket_percentage'];
                }
            }
            else {

                $key = count($results);
                $results[$key]['id'] = $field['id'];
                $results[$key]['tax_type'] = $field['tax_type'];
                $results[$key]['tax_rate'] = $field['taxrate'];
                $results[$key]['is_percentage'] = $field['tax_percentage'] == 1 ? 'true' : 'false';;
                $results[$key]['is_brackets_active'] = $field['is_brackets_active'];
                $results[$key]['is_tax_inclusive'] = $field['is_tax_inclusive'];
                $results[$key]['tax_type_id'] = $field['taxtype_id'];
            }
        }

        foreach ($results as $result) {
            $start = array();
            $end = array();
            $price_brackets = array();

            if(isset($result['price_bracket']) && $result['price_bracket']){
                foreach ($result['price_bracket'] as $key1 => $value) {
                    $price_brackets[] = array(
                        'start' => $value['start'],
                        'end' => $value['end'],
                        'rate' => $value['rate'],
                        'is_percentage' => $value['is_percentage']
                    );
                }
            }

            $taxes_row = array();
            $taxes_row['Tax Type'] = $result['tax_type'];
            $taxes_row['Tax Rate'] = $result['tax_rate'];
            $taxes_row['Tax Type Id'] = $result['tax_type_id'];
            $taxes_row['Is Percentage'] = $result['is_percentage'];
            $taxes_row['Bracket Active'] = $result['is_brackets_active'] == 1 ? 'true' : 'false';
            $taxes_row['Is Tax Inclusive'] = $result['is_tax_inclusive'] == 1 ? 'true' : 'false';
            $taxes_row['Price Bracket'] = json_encode($price_brackets);
            $tax_keys[] = $taxes_row;
        }

        $csv_data['taxes'] = $tax_keys;

        if (isset($csv_data['taxes'])) {
            $this->import_taxes_csv($csv_data['taxes']);
        }
    }

    function import_customers_data($company_id){

        $customer = $this->Export_data_model->get_customer_card_details($company_id);

        $first_customer = $customer[0];

        if($first_customer['custom_fields_name']){
            $custom_fields_name = explode(',',$first_customer['custom_fields_name']);
        }

        
        foreach($customer as $data)
        {
            $custom_fields_values = array();

            if($data['custom_fields_value']){
                $custom_fields_values[] = explode(',',$data['custom_fields_value']);
            }

            $customer_row = array();
            $customer_row['Customer Id'] = $data['customers_id'];
            $customer_row['Customer Name'] = $data['name_customer'];
            $customer_row['Customer Type'] = $data['customer_type_name'];
            $customer_row['Address'] = $data['address'];
            $customer_row['City'] = $data['city'];
            $customer_row['Region'] = $data['region'];
            $customer_row['Country'] = $data['country'];
            $customer_row['Postal Code'] = $data['postal_code'];
            $customer_row['Phone'] = $data['phone'];
            $customer_row['Fax'] = $data['fax'];
            $customer_row['Email'] = $data['email'];
            $customer_row['Customer Notes'] = $data['customer_notes'];
            $customer_row['Address2'] = $data['address2'];
            $customer_row['Phone2'] = $data['phone2'];

            if(isset($custom_fields_name) && $custom_fields_name){
                foreach ($custom_fields_name as $key => $value) {
                    if($custom_fields_values){
                        foreach($custom_fields_values as $custom_fields_value) {
                            if(!empty($custom_fields_value)){
                                $customer_row[$value] = $custom_fields_value[$key];
                            }
                        }
                    }
                }
            }

            $customer_keys[] = $customer_row;
        }

        $csv_data['customers'] = $customer_keys;

        if (isset($csv_data['customers'])) {
            $this->import_customers_csv($csv_data['customers']);
        }
    }

    function import_charges_data($company_id){

        $charges = $this->Export_data_model->get_all_the_charge_types($company_id);
        $charge_type_ids = array();

        foreach ($charges as $key => $value) {
            $charge_type_ids[] = $value['id'];
        }

        $taxes = $this->Export_data_model->get_charge_taxes($charge_type_ids);

        foreach ($taxes as $t => $tax) {
            foreach ($charges as $c => $charge) {
                if($charge['id'] == $tax['charge_type_id']){
                    $charges[$c]['taxes'][] = $tax;
                }
            }
        }

        foreach($charges as $key => $data)
        {
            switch ($data['pay_period']) {
                case "0" : $pay_period = 'Daily'; break;
                case "1" : $pay_period = 'Weekly'; break;
                case "2" : $pay_period = 'Monthly'; break;
                case "3" : $pay_period = 'One time'; break;
            }

            $taxes_value = array();
            $tax_string = "";
            if(isset($data['taxes']) && $data['taxes']){
                foreach ($data['taxes'] as $key1 => $value) {
                    if(isset($value['tax_type'])){
                        $taxes_value[]= $value['tax_type'];
                    }
                }
                $tax_string = implode(",", $taxes_value);
            }

            $charge_row = array();
            $charge_row['Charge Type'] = $data['name'];
            $charge_row['Charge Type Id'] = $data['id'];
            $charge_row['Room Charge Type'] = $data['is_room_charge_type'] == 1 ? "true":"false";
            $charge_row['Tax Exempt'] = $data['is_tax_exempt'] == 1 ? "true":"false";
            $charge_row['Default Room Charge'] = $data['is_default_room_charge_type'] == 1 ? "true":"false";
            $charge_row['Charge Id'] = $data['charge_id'];
            $charge_row['Tax Type'] = $tax_string;
            $charge_row['Description'] = $data['description'];
            $charge_row['Date Time'] = $data['date_time'];
            $charge_row['Booking Id'] = $data['booking_id'];
            $charge_row['Amount'] = $data['amount'];
            $charge_row['Selling Date'] = $data['selling_date'];
            $charge_row['Customer Id'] = $data['customer_id'];
            $charge_row['Pay Period'] = $pay_period;
            $charge_row['Night Audit Charge'] = $data['is_night_audit_charge'] == 1 ? "true":"false";
            $charge_keys[] = $charge_row;
        }

        $csv_data['charges'] = $charge_keys;

        if (isset($csv_data['charges'])) {
            $this->import_charges_csv($csv_data['charges']);
        }
    }

    function import_rates_data($company_id){

        $rates = $this->Export_data_model->get_all_rate_plans($company_id);

        $rates_keys = array();
        $i = 0;
        
        foreach($rates as $data)
        {
           $i++;
           $rates_keys[$i] = array();

            $rates_keys[$i]['Name'] = $data['rate_plan_name'];
            $rates_keys[$i]['Rate Plan Id'] = $data['rate_planid'];
            $rates_keys[$i]['Charge Type'] = $data['charge_type_id'];
            $rates_keys[$i]['Room type Id'] = $data['roomtype_id'];
            $rates_keys[$i]['Room Type Name'] = $data['room_type_name'];
            $rates_keys[$i]['Rate Id'] = $data['rate_id'];
            $rates_keys[$i]['Base Rate'] = $data['base_rate'];
            $rates_keys[$i]['From Date'] = $data['date_start'];
            $rates_keys[$i]['To Date'] = $data['date_end'];
            $rates_keys[$i]['Description'] = "";
            $rates_keys[$i]['Currency'] = $data['currency_id'];
            $rates_keys[$i]['Monday'] = $data['monday'];
            $rates_keys[$i]['Tuesday'] = $data['tuesday'];
            $rates_keys[$i]['Wednesday'] = $data['wednesday'];
            $rates_keys[$i]['Thursday'] = $data['thursday'];
            $rates_keys[$i]['Friday'] = $data['friday'];
            $rates_keys[$i]['Saturday'] = $data['saturday'];
            $rates_keys[$i]['Sunday'] = $data['sunday'];
            $rates_keys[$i]['Adult Rate 1'] = $data['adult_1_rate'];
            $rates_keys[$i]['Adult Rate 2'] = $data['adult_2_rate'];
            $rates_keys[$i]['Adult Rate 3'] = $data['adult_3_rate'];
            $rates_keys[$i]['Adult Rate 4'] = $data['adult_4_rate'];
            $rates_keys[$i]['Additional Adult Rate'] = $data['additional_adult_rate'];
            $rates_keys[$i]['Aditional Child Rate'] = $data['additional_child_rate'];
            $rates_keys[$i]['Min Length of Stay'] = $data['minimumlength'];
            $rates_keys[$i]['Max Length of Stay'] = $data['maximumlength'];
            $rates_keys[$i]['Close to Departure'] = $data['cls_to_departure'] == 1 ? "true": 'false';
            $rates_keys[$i]['Close to Arrival'] = $data['cls_to_arrival'] == 1 ? "true": 'false';
            $rates_keys[$i]['Can be sold online'] = $data['can_sold_online'] == 1 ? "true": 'false';
            $rates_keys[$i]['Read Only'] = $data['is_selectable'] == "true" ? "true": 'false';
            $rates_keys[$i]['Rate Extras'] = $data['rate_plan_extras'] ?: "";//$imported_extra ;
        }

        $csv_data['rates'] = $rates_keys;

        if (isset($csv_data['rates'])) {
            $this->import_rates_csv($csv_data['rates']);
        }
    }

    function import_bookings_data($company_id){
        $booking = $this->Export_data_model->get_booking_details($company_id);

        $first_booking = $booking[0];

        if($first_booking['custom_fields_name']){
            $custom_fields_name = explode(',',$first_booking['custom_fields_name']);
        }

        if(isset($custom_fields_name) && $custom_fields_name){
            foreach($custom_fields_name as $custom_fields) {
                $csv_booking_keys[] = $custom_fields;
            }
        }

        $staying_booking = $this->Export_data_model->get_booking_staying_customers($company_id);
        foreach($booking as $key => $data)
        {
            $staying_customer = array();
            $staying_booking_customer = "";
            if(!empty($staying_booking)){
                foreach ($staying_booking as $customer) {
                    if($customer['booking_id'] == $data['bookingid']){
                        $staying_customer[] = $customer['customer_id'];
                    }
                }
                $staying_booking_customer = implode(",", $staying_customer);
            }

            switch ($data['state']) {
                case "0" : $state = 'Reservation'; break;
                case "1" : $state = 'Checked-in'; break;
                case "2" : $state = 'Checked-out'; break;
                case "3" : $state = 'Out-of-Order'; break;
                case "4" : $state = 'Cancelled'; break;
                case "5" : $state = 'No-show'; break;
                case "6" : $state = 'Delete'; break;
                case "7" : $state = 'Unconfirmed'; break;
                default: $state = 'All'; break;
            }

            switch ($data['pay_period']) {
                case "0" : $pay_period = 'Daily'; break;
                case "1" : $pay_period = 'Weekly'; break;
                case "2" : $pay_period = 'Monthly'; break;
                case "3" : $pay_period = 'One time'; break;
            }

            switch ($data['source']) {
                case "0" : $source = 'Walk-in / Telephone'; break;
                case "1" : $source = 'Online Widget'; break;
                case "2" : $source = 'Booking Dot Com'; break;
                case "3" : $source = 'Expedia'; break;
                case "4" : $source = 'Agoda'; break;
                case "5" : $source = 'Trip Connect'; break;
                case "6" : $source = 'Air BNB'; break;
                case "7" : $source = 'Hotel World'; break;
                case "8" : $source = 'Myallocator'; break;
                case "9" : $source = 'Company'; break;
                case "10" : $source = 'Guest Member'; break;
                case "11" : $source = 'Owner'; break;
                case "12" : $source = 'Returning Guest'; break;
                case "13" : $source = 'Apartment'; break;
                case "14" : $source = 'sitminder'; break;
                case "15" : $source = 'Seasonal'; break;
                case "20" : $source = 'Other taravel agency'; break;
            }

            if($data['custom_fields_value']) {
                $custom_fields_values = explode(',',$data['custom_fields_value']);
            } else {
                $custom_fields_values = null;
            }

            $booking_row = array();
            $booking_row['Booking Id'] = $data['bookingid'];
            $booking_row['Rate'] = $data['rate'];
            $booking_row['Adult Count'] = $data['adult_count'];
            $booking_row['Children Count'] = $data['children_count'];
            $booking_row['State'] = $state;
            $booking_row['Booking Customer Id'] = $data['booking_customer_id'];
            $booking_row['Booked By'] = $data['booked_by'];
            $booking_row['Balance'] = $data['balance'];
            $booking_row['Balance Without Forecast'] = $data['balance_without_forecast'];
            $booking_row['Use Rate Plan'] = $data['use_rate_plan'] == 1 ? 'true' :'false';
            $booking_row['Rate Plan Id'] = $data['rate_plan_id'];
            $booking_row['Color'] = $data['color'];
            $booking_row['Charge Type'] = $data['charge_type_id'];
            $booking_row['Check In Date'] = $data['check_in_date'];
            $booking_row['Check Out Date'] = $data['check_out_date'];
            $booking_row['Room'] = $data['room_id'];
            $booking_row['Room Type'] = $data['room_type_id'];
            $booking_row['Group Id'] = $data['booking_group_id'];
            $booking_row['Group Name'] = $data['name'];
            $booking_row['Daily Charges'] = $data['add_daily_charge'] == 1 ? 'true':'false';
            $booking_row['Pay Period'] = $pay_period;
            $booking_row['Source'] = $source;
            $booking_row['Custom Booking Source'] = $data['booking_source_name'];
            $booking_row['Booking Note'] = $data['booking_notes'];
            $booking_row['Booking Room History'] = $data['booking_room_history_id'];
            $booking_row['Staying Customers'] = isset($staying_booking_customer) && $staying_booking_customer ? $staying_booking_customer : "";

            if($custom_fields_values){
                foreach($custom_fields_values as $custom_fields_value) {
                    if(!empty($custom_fields_value)){
                        $booking_row[] = $custom_fields_value;
                    } else {
                        $booking_row[] = "";
                    }
                }
            } else {
                if(isset($custom_fields_name) && $custom_fields_name) {
                    foreach ($custom_fields_name as $key => $value) {
                        $booking_row[] = "";
                    }
                }
            }

            $booking_keys[] = $booking_row;
        }

        $csv_data['bookings'] = $booking_keys;
        
        if (isset($csv_data['bookings'])) {
            $this->import_bookings_csv($csv_data['bookings']);
        }
    }

    function import_extras_data($company_id){

        $extras = $this->Export_data_model->get_all_extras($company_id);

        if($extras && count($extras) > 0){
            foreach($extras as $key => $data)
            {
                $extra_row = array();
                $extra_row['Booking Id'] = $data['booking_id'];
                $extra_row['Start Date'] = $data['start_date'];
                $extra_row['End Date'] = $data['end_date'];
                $extra_row['Quantity'] = $data['quantity'];
                $extra_row['Default Rate'] = $data['defaultrate'];
                $extra_row['Booking Extra Id'] = $data['booking_extra_id'];
                $extra_row['Extra Id'] = $data['extraid'];
                $extra_row['Extra Type'] = $data['extra_type'];
                $extra_row['Extra Name'] = $data['extra_name'];
                $extra_row['Charge Type'] = $data['charge_type_id'];
                $extra_row['Charging Scheme'] = $data['charging_scheme'];
                $extra_row['Show on POS'] = $data['show_on_pos'];
                $extra_row['Extra Rate Id'] = $data['extra_rate_id'];
                $extra_row['Rate'] = $data['rate'];
                $extra_row['Curreny'] = $data['currency_id'];
                $extra_keys[] = $extra_row;
            }

            $csv_data['extras'] = $extra_keys;

            if (isset($csv_data['extras'])) {
                $this->import_extras_csv($csv_data['extras']);
            }
        }
    }

    function import_payments_data($company_id){

        $payment = $this->Export_data_model->get_payment_details($company_id);

        foreach($payment as $key => $data)
        {
            $payment_row = array();
            $payment_row['Payment Type'] = $data['payment_type'];
            $payment_row['Deleted'] = $data['is_deleted'] == 1 ?"true":"false";
            $payment_row['Read Only'] = $data['is_read_only']== 1 ?"true":"false";
            $payment_row['Payment Read Only'] = $data['read_only']== 1 ?"true":"false";
            $payment_row['Payment Id'] = $data['payment_id'];
            $payment_row['Description'] = $data['description'];
            $payment_row['Date Time'] = $data['date_time'];
            $payment_row['Booking Id'] = $data['booking_id'];
            $payment_row['Amount'] = $data['amount'];
            $payment_row['Credit Card Id'] = $data['credit_card_id'];
            $payment_row['Selling Date'] = $data['selling_date'];
            $payment_row['Customer Id'] = $data['customer_id'];
            $payment_row['Payment Status'] = $data['payment_status'];
            $payment_row['Payment Capture'] = $data['is_captured']== 1 ?"true":"false";
            $payment_keys[] = $payment_row;
        }

        $csv_data['payments'] = $payment_keys;

        if (isset($csv_data['payments'])) {
            $this->import_payments_csv($csv_data['payments']);
        }
    }

    function import_statements_data($company_id){

        $statment = $this->Export_data_model->get_statement($company_id);

        foreach($statment as $key => $data)
        {
            $statment_row = array();
            $statment_row['Booking Id'] = $data['booking_id'];
            $statment_row['Statment Id'] = $data['statement_id'];
            $statment_row['Statement Number'] = $data['statement_number'];
            $statment_row['Creation Date'] = $data['creation_date'];
            $statment_row['Statement Name'] = $data['statement_name'];
            $statment_keys[] = $statment_row;
        }

        $csv_data['statments'] = $statment_keys;

        if (isset($csv_data['statments'])) {
            $this->import_statements_csv($csv_data['statments']);
        }
    }

    function import_company_settings_data($company_id){

        $setting_res = $this->company_setting_csv_data($company_id);

        $csv_data['settings'] = json_encode($setting_res);

        if (isset($csv_data['settings'])) {
            $this->import_company_setting($csv_data['settings']);
        }
    }
}
