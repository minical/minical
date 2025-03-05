<?php
class Rates extends MY_Controller
{

	function __construct()
	{

        parent::__construct();
        
        $this->load->model('User_model');
        $this->load->model('Image_model');
        $this->load->model('Company_model');
		$this->load->model('Date_range_model');
		$this->load->model('Extra_model');
		$this->load->model('Currency_model');
		$this->load->model('Rate_model');
		$this->load->model('Rate_plan_model');
		$this->load->model('Room_type_model');
		$this->load->model('Charge_type_model');
		$this->load->model('Booking_model');
		$this->load->model('Company_model');
		$this->load->model('Employee_log_model');
        $this->load->model('Option_model');
        $this->load->library('ckeditor');
        $this->load->library('ckfinder');
        $this->load->helper('ckeditor_helper');
		
		$this->load->library('Form_validation');
		
		$view_data['menu_on'] = true;
		$view_data['selected_menu'] = 'Settings';
		$view_data['selected_submenu'] = 'Rates';
		$view_data['submenu'] = 'hotel_settings/hotel_settings_submenu.php';
        
        $view_data['submenu_parent_url'] = base_url()."settings/";
		$view_data['sidebar_menu_url'] = base_url()."settings/rates/";
        
        $view_data['menu_items'] = $this->Menu_model->get_menus(array('parent_id' => 5, 'wp_id' => 1));
        $view_data['sidebar_links'] = $this->Menu_model->get_menus(array('parent_id' => 33, 'wp_id' => 1));

		$view_data['css_files'] = array(
				base_url() . auto_version('css/croppic.css')
		);
			
		$view_data['js_files'] = array(
			//base_url() . auto_version('js/bootstrap.min.js'),
			base_url() . auto_version('js/croppic.js'),
			base_url() . auto_version('js/hotel-settings/image-settings.js')
			
		);
		
		
		$this->load->vars($view_data);
	}	

	function index() {
		$this->rate_plans();
	}

	/**
		Rate Plan settings
		Browse rate plan
	*/
	function rate_plans()
	{
		//$rate_plans = $this->Rate_plan_model->get_rate_plans($this->company_id);
		// if (isset($rate_plans))
		// {
		// 	foreach ($rate_plans as $id=> $rate_plan)
		// 	{
		// 		$rate_plans[$id]['images'] = $this->Image_model->get_images($rate_plan['image_group_id']);

		// 	}
		// }
		
		$data['company_id'] = $this->company_id;
		//$data['room_types'] = $this->Room_type_model->get_room_types($this->company_id);
		//$data['rate_plans'] = $rate_plans;
		//$data['currencies'] = $this->Currency_model->get_available_currency_list($this->company_id);
		//$data['charge_types'] = $this->Charge_type_model->get_room_charge_types($this->company_id);
		//$data['extras'] = $this->Extra_model->get_extras($this->company_id);
		
		$data['selected_sidebar_link'] = 'Rate Plans';
		
		$data['css_files'] = array(
			base_url() . auto_version('css/croppic.css'),
			base_url() . auto_version('css/admin/select2.css')
		);
        /*ckeditor*/
        
        $this->ckeditor->basePath = base_url().'application/third_party/ckeditor/';
        $this->ckeditor->config['language'] = 'it';
        $this->ckeditor->config['width'] = '500px';
        $this->ckeditor->config['height'] = '300px';            

        //Add Ckfinder to Ckeditor
        $this->ckfinder->SetupCKEditor($this->ckeditor,'../../ckfinder/'); 
        
		$data['js_files'] = array(
               'https://cdn.ckeditor.com/4.9.1/full/ckeditor.js',
				base_url() . auto_version('js/hotel-settings/rate-plan-settings.js'),
				base_url() . auto_version('js/croppic.js'),
				base_url() . auto_version('js/hotel-settings/image-settings.js'),
				base_url() . auto_version('js/admin/select2.min.js')
		);		
				
		$data['main_content'] = 'hotel_settings/rate_plan_settings/rate_plan_settings';
		$this->load->view('includes/bootstrapped_template', $data);
	}
    
    function get_cusom_rates_AJAX()
    {
        $rate_plan_id = $this->input->post('rate_plan_id');
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        $rate_plan_name = $this->input->post('rate_plan_name');
        
        $previous_rates = $this->Rate_plan_model->get_rate_plan($rate_plan_id, $this->company_id);

        if($rate_plan_name != "Custom Rate Plan" && empty($previous_rates))
        {
            $rate_array = $this->Rate_model->get_date_range($start_date, $end_date);
        }
        else
        {
            $this->load->library('rate');
            $rate_array = $this->rate->get_rate_array($rate_plan_id, $start_date, $end_date);
        }
        echo json_encode($rate_array);
    }
    
    function create_custom_rate_AJAX()
    {
       	$rate_plan_id = $this->input->post("rate_plan_id");
        $room_type_id = $this->input->post("room_type_id");
		$charge_type_id = $this->input->post("charge_type_id");
        $date_start = $this->input->post("start_date");
        $date_end = $this->input->post("end_date");
        $booking_id = $this->input->post("booking_id");
        $rates_arr = $this->input->post("rates");
        //print_r($rates_arr);
        $rate_plan = null;
        $policy_code = 0;
        
		if (!$charge_type_id && $rate_plan_id) {
			$rate_plan = $this->Rate_plan_model->get_rate_plan($rate_plan_id);
			$charge_type_id = $rate_plan['charge_type_id'];
		} elseif (!$charge_type_id) {
			$charge_type_id = $this->Charge_type_model->get_default_room_charge_type($this->company_id)['id'];
		}
		
        //day array
        $days = array(
                "monday",
                "tuesday",
                "wednesday",
                "thursday",
                "friday",
                "saturday",
                "sunday"
        );
        
        if($rate_plan_id != null)
        {    
            $previous_rates = $this->Rate_plan_model->get_rate_plan($rate_plan_id); // get prevoius rate plan 
           // echo '<pre>';print_r($previous_rates);
            if(!empty($previous_rates) && count($previous_rates) > 0 && $previous_rates['rate_plan_name'] == "Custom Rate Plan")
            {
                $custom_rates = $this->Rate_model->get_rate_x_date_ranges($rate_plan_id);

                $this->Rate_plan_model->delete_custom_rate_plan($rate_plan_id); // delete previous rate plan 

                if(count($custom_rates) > 0)
                {
                    foreach($custom_rates as $custom_rate)
                    {
                        // delete previous rates and date range 
                        $this->Rate_model->delete_custom_rates_and_date_range($custom_rate['rate_id'], $rate_plan_id, $custom_rate['date_range_id'], $custom_rate['date_range_x_rate_id']);
                    }
                }
            }
        }
        $rate_plan_id = null;  // unset previous rate plan id 
        if($rate_plan_id == null)
        {   // create new custom rate plan
            $default_currency = $this->Currency_model->get_default_currency($this->company_id);

            if((isset($this->is_nestpay_enabled) && $this->is_nestpay_enabled == true) || 
                   (isset($this->is_nestpaymkd_enabled) && $this->is_nestpaymkd_enabled == true) ||
                   (isset($this->is_nestpayalb_enabled) && $this->is_nestpayalb_enabled == true) ||
                   (isset($this->is_nestpaysrb_enabled) && $this->is_nestpaysrb_enabled == true)
                   ) {

                $policies = $this->Option_model->get_option_by_company('payment_policy',$this->company_id);
               
               if(isset($policies) && $policies !=''){

                    foreach ($policies as $policy) {
                     
                        $policy_value = json_decode($policy['option_value'],true);
                       
                        if($policy_value['policy_type'] == 1){

                            $policy_code = $policy['option_id'];
                             break;
                        }else{
                        $policy_code = $policy['option_id'];
                        }
                    }
               
               }else{

                 $policy_code ='';
               }
                
            }

            if(($this->is_nestpay_enabled == true) || ($this->is_nestpaymkd_enabled == true) ||
             ($this->is_nestpayalb_enabled == true) || ($this->is_nestpaysrb_enabled == true)){
               
                
                $rate_plan_data = Array(
                "room_type_id" => $room_type_id,
                "currency_id" => $rate_plan['currency_id'],
                "rate_plan_name" => $rate_plan['rate_plan_name']."#".$booking_id,
                "charge_type_id" => $charge_type_id,
                "company_id" => $this->company_id,
                "policy_code" => $policy_code,
                "is_selectable" => 0
                );
            }else{
                $rate_plan_data = Array(
                "room_type_id" => $room_type_id,
                "currency_id" => $rate_plan['currency_id'],
                "rate_plan_name" => "Custom Rate Plan",
                "charge_type_id" => $charge_type_id,
                "company_id" => $this->company_id,
                "policy_code" => $policy_code,
                "is_selectable" => 0
                );
            }

            $new_rate_plan_id = $this->Rate_plan_model->create_rate_plan($rate_plan_data); // create new rate plan

            if($booking_id)
            {
                $this->Booking_model->update_booking($booking_id, array("use_rate_plan" => 1, "rate_plan_id" => $new_rate_plan_id, "rate" => $rates_arr[0]["rate"]));
            } 
            if(!empty($rates_arr))
            {                
                $rate_data = null;
                foreach($rates_arr as $rate_arr)
                {
                    $rate_data = array(
                        "rate_plan_id" => $new_rate_plan_id,
                        "base_rate" => $rate_arr['rate'],
                        "adult_1_rate" => $rate_arr['rate'],
						"adult_2_rate" => $rate_arr['rate'],
						"adult_3_rate" => $rate_arr['rate'],
						"adult_4_rate" => $rate_arr['rate']
                    );

                    $rate_id = $this->Rate_model->create_rate($rate_data); // create rate 

                    $date_data['date_start'] = $date_start;
                    $date_data['date_end'] = date('Y-m-d', strtotime('+1day', strtotime($date_start)));                    
                    

                    foreach ($days as $day) 
                    {
                        if (substr($day,0,2) == strtolower($rate_arr['day'])) 
                            $date_data[$day] = 1;
                        else
                            $date_data[$day] = 0;
                        
                    }

                    $date_range_id = $this->Date_range_model->create_date_range($date_data);

                    $this->Date_range_model->create_date_range_x_rate(
                        Array(
                            'date_range_id' => $date_range_id,
                            'rate_id' => $rate_id
                        )
                    );
                    $date_start = date('Y-m-d', strtotime('+1day', strtotime($date_start)));
                }

             }
            $response['status'] = "success";
            $response['new_rate_plan_id'] = $new_rate_plan_id;
            echo json_encode($response);
           
        }
           
    }
	
	function _create_rate_plan_log($log) {
        $log_detail =  array(
                    "user_id" => $this->user_id,
                    "selling_date" => $this->selling_date,
                    "date_time" => gmdate('Y-m-d H:i:s'),
                    "log" => $log,
                );   
        
        $this->Employee_log_model->insert_log($log_detail);     
    }

    function create_rate_plan_AJAX(){
        global $unsanitized_post;
        $room_type_ids = $this->security->xss_clean($this->input->post('room_type_id'));
        $new_rate_plan_ids = [];

        if ($room_type_ids){
            $extras = $this->security->xss_clean($this->input->post('extras'));
            // rate plan data
            $rate_plan_data = array(
                'rate_plan_name' => base64_decode($this->security->xss_clean($this->input->post('rate_plan_name'))),
                'currency_id' => base64_decode($this->security->xss_clean($this->input->post('currency_id'))),
                'charge_type_id' => base64_decode($this->security->xss_clean($this->input->post('charge_type_id'))),
                'description' => isset($unsanitized_post['description']) ? $unsanitized_post['description'] : "",
                'is_shown_in_online_booking_engine' => base64_decode($this->security->xss_clean($this->input->post('is_shown_in_online_booking_engine'))),
                'policy_code'=> $this->input->post('policy_code') !="" ? $this->security->xss_clean($this->input->post('policy_code')) : null,
            );

            $derived_rate_enable =  $this->input->post('derivedrate_enable') != "" ? $this->security->xss_clean($this->input->post('derivedrate_enable')) : 0;
            $parent_room_type = $this->input->post('parentroom_type') !="" ? $this->security->xss_clean($this->input->post('parentroom_type')) : null;            
            $parent_rate_plan = $this->input->post('parentrate_plan') !="" ? $this->security->xss_clean($this->input->post('parentrate_plan')) : null;
            $rate_logic = $this->input->post('ratelogic') !="" ? $this->security->xss_clean($this->input->post('ratelogic')) : null;
            $rate_logic_amount = $this->input->post('ratelogic_amount') !="" ? $this->security->xss_clean($this->input->post('ratelogic_amount')) : null;        
            $checklist = $this->input->post('dataArray_check');

            if(
                $this->is_derived_rate_enabled == 1 && 
                $derived_rate_enable == 1
            ) {
                $mims_arrival = isset($checklist[0]['min_stay_arrival']) ? $checklist[0]['min_stay_arrival'] : 0 ;
                $mams_arrival = isset($checklist[1]['max_stay_arrival']) ? $checklist[1]['max_stay_arrival'] : 0 ;
                $closed_to_arrival = isset($checklist[2]['closed_to_arrival']) ? $checklist[2]['closed_to_arrival'] : 0;
                $closed_to_departure = isset($checklist[3]['closed_to_departure']) ? $checklist[3]['closed_to_departure'] : 0 ;
                $stop_sell = isset($checklist[4]['stop_sell']) ? $checklist[4]['stop_sell'] : 0; 

                foreach ($room_type_ids as $typeId){
                    $rate_plan_data['room_type_id'] = $typeId;
                    // Create date range
                
                    $date_range_id = $this->Date_range_model->create_date_range(
                        Array(
                            'date_start' => '2000-01-01',
                            'date_end' => '2030-01-01'
                        )
                    );

                    // Create Rate Plan
                    $new_rate_plan_id = $this->Rate_plan_model->create_rate_plan($rate_plan_data);

                    $meta = array(
                        "derived_rate_enable" => $derived_rate_enable,
                        "parent_room_type" => $parent_room_type,
                        "parent_rate_plan" => $parent_rate_plan,
                        "rate_logic" => $rate_logic,
                        "rate_logic_amount" => $rate_logic_amount,
                        "rate_plan_id" => $new_rate_plan_id,
                        "company_id" => $this->company_id,
                        "rate" => 1,
                        "mims_a" => $mims_arrival,
                        "mams_a" => $mams_arrival,
                        "cta" => $closed_to_arrival, 
                        "ctd" => $closed_to_departure,
                        "stop_sell" => $stop_sell
                    );

                    $data['option_value'] = json_encode($meta);
                    $data['option_name'] = 'derived_rate_'.$new_rate_plan_id;
                    $data['autoload'] = 0;
                    $data['company_id'] = $this->company_id;

                    $this->Option_model->add_option($data);

                    $deriveddata =  $this->Option_model->get_option_by_company('derived_rate_'.$new_rate_plan_id,$this->company_id);

                    $Ddetails =  json_decode($deriveddata[0]['option_value'],true);

                    $date_range_data['start_date'] = date("Y-m-d");
                    $date_range_data['end_date'] = date('Y-m-d', strtotime(date("Y-m-d") . " + 1 year"));
                    
                    $parents_data = $this->Rate_plan_model->get_parent_rateplan_data($Ddetails['parent_room_type'], $Ddetails['parent_rate_plan'], $date_range_data);
                    
                    foreach ($parents_data as $parent_data){
                        if(
                            isset($parent_data['date_start']) && 
                            isset($parent_data['date_end'])
                        ){
                            if ($parent_data['monday'] == 1) {
                                $derive_details = $this->derived_rate_plan($Ddetails['rate_logic'], $parent_data, $Ddetails['rate_logic_amount']);
                            }
                            if ($parent_data['tuesday'] == 1) {
                                $derive_details = $this->derived_rate_plan($Ddetails['rate_logic'], $parent_data, $Ddetails['rate_logic_amount']);
                            }
                            if ($parent_data['wednesday'] == 1) {
                                $derive_details = $this->derived_rate_plan($Ddetails['rate_logic'], $parent_data, $Ddetails['rate_logic_amount']);
                            }
                            if ($parent_data['thursday'] == 1) {
                                $derive_details = $this->derived_rate_plan($Ddetails['rate_logic'], $parent_data, $Ddetails['rate_logic_amount']);
                            }
                            if ($parent_data['friday'] == 1) {
                                $derive_details = $this->derived_rate_plan($Ddetails['rate_logic'], $parent_data, $Ddetails['rate_logic_amount']);
                            }
                            if ($parent_data['saturday'] == 1) {
                                $derive_details = $this->derived_rate_plan($Ddetails['rate_logic'], $parent_data, $Ddetails['rate_logic_amount']);
                            }
                            if ($parent_data['sunday'] == 1) {
                                $derive_details = $this->derived_rate_plan($Ddetails['rate_logic'], $parent_data, $Ddetails['rate_logic_amount']);
                            }

                            $adult_1_rate = $derive_details['adult_1_rate'];
                            $adult_2_rate = $derive_details['adult_2_rate'];
                            $adult_3_rate = $derive_details['adult_3_rate'];
                            $adult_4_rate = $derive_details['adult_4_rate'];

                            $minimum_length_of_stay = null;
                            if($Ddetails['mims_a'] == 1){
                                $minimum_length_of_stay = isset($parent_data['minimum_length_of_stay']) ? $parent_data['minimum_length_of_stay'] : null;
                            }
                    
                            $maximum_length_of_stay = null;
                            if($Ddetails['mams_a'] == 1){  
                                $maximum_length_of_stay = isset($parent_data['maximum_length_of_stay']) ? $parent_data['maximum_length_of_stay'] : null;
                            }

                            $closed_to_arrival = 0;
                            if($Ddetails['cta'] == 1){
                                $closed_to_arrival = isset($parent_data['closed_to_arrival']) ? $parent_data['closed_to_arrival'] : 0; 
                            }

                            $closed_to_departure = 0;
                            if($Ddetails['ctd'] == 1){
                                $closed_to_departure = isset($parent_data['closed_to_departure']) ? $parent_data['closed_to_departure'] : 0; 
                            }

                            $can_be_sold_online = 1;
                            if($Ddetails['stop_sell'] == 1){
                                $can_be_sold_online = isset($parent_data['can_be_sold_online']) ? $parent_data['can_be_sold_online'] : 0; 
                            }

                            $additional_adult_rate =  isset($parent_data['additional_adult_rate']) ? $parent_data['additional_adult_rate'] : null; 
                            $additional_child_rate =  isset($parent_data['additional_child_rate']) ? $parent_data['additional_child_rate'] : null;
                            $minimum_length_of_stay_arrival = isset($parent_data['minimum_length_of_stay_arrival']) ? $parent_data['minimum_length_of_stay_arrival'] : null;
                            $maximum_length_of_stay_arrival = isset($parent_data['maximum_length_of_stay_arrival']) ? $parent_data['maximum_length_of_stay_arrival'] : null;
                            
                            if(
                                strtotime(date('Y-m-d')) > strtotime($parent_data['date_start'])
                            ) {
                                $parent_data['date_start'] = date('Y-m-d');
                            }
                            
                            // $parent_data['date_end'] = date('Y-m-d', strtotime(date("Y-m-d") . " + 1 year"));

                            $new_date_range_id = $this->Date_range_model->create_date_range(
                                Array(
                                    'date_start' => date('Y-m-d', strtotime($parent_data['date_start'])),
                                    'date_end' =>  date('Y-m-d', strtotime($parent_data['date_end'])),
                                    'monday' =>  $parent_data['monday'] == 1 ? 1 : 0,
                                    'tuesday' =>  $parent_data['tuesday'] == 1 ? 1 : 0,
                                    'wednesday' =>  $parent_data['wednesday'] == 1 ? 1 : 0,
                                    'thursday' =>  $parent_data['thursday'] == 1 ? 1 : 0,
                                    'friday' =>  $parent_data['friday'] == 1 ? 1 : 0,
                                    'saturday' =>  $parent_data['saturday'] == 1 ? 1 : 0,
                                    'sunday' =>  $parent_data['sunday'] == 1 ? 1 : 0,
                                )
                            );

                            $rate_array = Array(
                                'rate_plan_id' => $new_rate_plan_id,
                                'base_rate' => $adult_1_rate,
                                'adult_1_rate' => $adult_1_rate,
                                'adult_2_rate' => $adult_2_rate,
                                'adult_3_rate' => $adult_3_rate,
                                'adult_4_rate' => $adult_4_rate,
                                'additional_adult_rate' => $additional_adult_rate,
                                'additional_child_rate' => $additional_child_rate,
                                'minimum_length_of_stay' => $minimum_length_of_stay,
                                'maximum_length_of_stay' => $maximum_length_of_stay,
                                'minimum_length_of_stay_arrival' => $minimum_length_of_stay_arrival,
                                'maximum_length_of_stay_arrival' => $maximum_length_of_stay_arrival,
                                'closed_to_arrival' => $closed_to_arrival,
                                'closed_to_departure' => $closed_to_departure,
                                'can_be_sold_online'=> $can_be_sold_online
                            );
                    
                            $new_rate_id = $this->Rate_model->create_rate($rate_array);

                            $this->Date_range_model->create_date_range_x_rate(
                                Array(
                                    'rate_id' => $new_rate_id,
                                    'date_range_id' => $new_date_range_id
                                )
                            );

                            // Assign the Base Rate into the newly created rate plan
                            $this->Rate_plan_model->update_rate_plan(
                                array('base_rate_id' => $new_rate_id),
                                $new_rate_plan_id
                            );

                            if ($extras) {
                                $old_extra_ids_array = $this->Extra_model->get_rate_plan_extras($new_rate_plan_id, $rate_plan_data['room_type_id']);

                                if (isset($old_extra_ids_array) && count($old_extra_ids_array) > 0) {
                                    foreach ($old_extra_ids_array as $key => $value) {
                                        $this->Extra_model->delete_rate_plan_etras($new_rate_plan_id, $rate_plan_data['room_type_id'], $value['extra_id']);
                                    }
                                }

                                foreach ($extras as $extra_id) {
                                    $this->Extra_model->create_rate_plan_extras($new_rate_plan_id, $extra_id, $rate_plan_data['room_type_id']);
                                }
                            }

                        }
                    }

                    array_push($new_rate_plan_ids, $new_rate_plan_id);
                    $this->_create_rate_plan_log("Create New Rate Plan ( [ID {$new_rate_plan_id}])");
                }                
            } else {
                // Store data for the room type
                foreach ($room_type_ids as $typeId){
                    $rate_plan_data['room_type_id'] = $typeId;
                    // Create date range
                    $date_range_id = $this->Date_range_model->create_date_range(
                        Array(
                            'date_start' => '2000-01-01',
                            'date_end' => '2030-01-01'
                        )
                    );

                    // Create Rate Plan
                    $new_rate_plan_id = $this->Rate_plan_model->create_rate_plan($rate_plan_data);

                    $rate_plan = $this->Rate_plan_model->get_rate_plan($new_rate_plan_id);

                    $rate_plan['rate_plan_id'] = $new_rate_plan_id; // for some reason, rate_plan_id is not returned

                    $rate_array = Array('rate_plan_id' => $new_rate_plan_id);
                    if ($this->allow_free_bookings) {
                        $rate_array['can_be_sold_online'] = 0;
                    } else {
                        $rate_array['can_be_sold_online'] = 1;
                    }
                    $new_rate_id = $this->Rate_model->create_rate($rate_array);

                    $this->Date_range_model->create_date_range_x_rate(
                        Array(
                            'rate_id' => $new_rate_id,
                            'date_range_id' => $date_range_id
                        )
                    );

                    // Assign the Base Rate into the newly created rate plan
                    $this->Rate_plan_model->update_rate_plan(
                        array('base_rate_id' => $new_rate_id),
                        $new_rate_plan_id
                    );

                    if ($extras) {
                        $old_extra_ids_array = $this->Extra_model->get_rate_plan_extras($new_rate_plan_id, $rate_plan_data['room_type_id']);

                        if (isset($old_extra_ids_array) && count($old_extra_ids_array) > 0) {
                            foreach ($old_extra_ids_array as $key => $value) {
                                $this->Extra_model->delete_rate_plan_etras($new_rate_plan_id, $rate_plan_data['room_type_id'], $value['extra_id']);
                            }
                        }

                        foreach ($extras as $extra_id) {
                            $this->Extra_model->create_rate_plan_extras($new_rate_plan_id, $extra_id, $rate_plan_data['room_type_id']);
                        }
                    }
                    array_push($new_rate_plan_ids, $new_rate_plan_id);
                    $this->_create_rate_plan_log("Create New Rate Plan ( [ID {$new_rate_plan_id}])");
                }
            }

            $value = 'Save Successful';
        } else {
            $value = 'An error occured. Please contact adminstrator if it continues.';
        }

        $data = array (
            'error' => '',
            'value' => $value,
            'new_rate_plan_ids' => $new_rate_plan_ids
        );

        echo json_encode($data);
    }
	

    // ADD NEW RATE PLAN
    function add_new_rate_plan_AJAX()
    {
        $data['room_types'] = $this->Room_type_model->get_room_types($this->company_id);
        $data['charge_types'] = $this->Charge_type_model->get_room_charge_types($this->company_id);
        $data['currencies'] = $this->Currency_model->get_available_currency_list($this->company_id);
        $data['extras'] = $this->Extra_model->get_extras($this->company_id);
        $data['polices'] = $this->Rate_plan_model->get_policy('payment_policy',$this->company_id);
        $data['allow_free_bookings'] = $this->allow_free_bookings;

        $default_currency = $this->Currency_model->get_default_currency($this->company_id);
        // rate plan data
        $rate_plan_data = Array(
            "room_type_id" => $this->input->post('room_type_id'),
            "currency_id" => isset($default_currency['default_currency_id']) ? $default_currency['default_currency_id'] : '',
            "rate_plan_name" => "New Rate Plan",
            "charge_type_id" => $this->Charge_type_model->get_default_room_charge_type($this->company_id)['id'],
            "rate_plan_id" => ""
        );
        $data['allow_free_bookings'] = $this->allow_free_bookings;
        $this->load->view('hotel_settings/rate_plan_settings/new_rate_plan', $rate_plan_data + $data);
    }

    function edit_rate_plan_AJAX(){
      
        $rate_plan_id = $this->input->post('rate_plan_id');
        $room_type_id = $this->input->post('room_type_id');
        
		$data['charge_types'] = $this->Charge_type_model->get_room_charge_types($this->company_id);
		$data['currencies'] = $this->Currency_model->get_available_currency_list($this->company_id);
		$data['room_types'] = $this->Room_type_model->get_room_types($this->company_id);
		$data['extras'] = $this->Extra_model->get_extras($this->company_id);
        $data['polices'] = $this->Rate_plan_model->get_policy('payment_policy',$this->company_id);
		$data['rate_plan'] = $this->Rate_plan_model->get_rate_plan($rate_plan_id);
        $data['company_id'] = $this->company_id;
        
        // echo '<pre>'; print_r($data['rate_plan']); die;

		if (isset($data['rate_plan']))
		{
			//foreach ($data['rate_plan'] as $id=> $rate_plan)
			//{
				$data['rate_plan']['images'] = $this->Image_model->get_images($data['rate_plan']['image_group_id']);

				if(isset($data['rate_plan']['images']) && $data['rate_plan']['images']) {
					$data['images'] = $data['rate_plan']['images'];
				}
			//}
		}
        if($this->is_derived_rate_enabled == 1 ){
            $deriveddata =  $this->Option_model->get_option_by_company('derived_rate_'.$rate_plan_id,$this->company_id);
             if(isset($deriveddata[0]['option_value'])){       
            $data['Ddetails'] =  json_decode($deriveddata[0]['option_value'],true);
             
            $data['Drate_plan'] = $this->Rate_plan_model->get_rate_plans_by_room_type_id($data['Ddetails']['parent_room_type']);
            }
         }
		$rate_plan_extras = array();
		if(isset($data['rate_plan']) && $data['rate_plan']) {
			//foreach ($data['rate_plan'] as $key => $value) {
				$data['rate_plan']['extras'] = $this->Extra_model->get_rate_plan_extras($rate_plan_id, $room_type_id);
			//}
		}
        
        $this->load->view('hotel_settings/rate_plan_settings/edit_rate_plan_modal', $data);
    }

	function replicate_rate_plan_AJAX()
	{
		$source_rate_plan_id = $this->security->xss_clean($this->input->post('rate_plan_id'));

        $rate_plan = $this->Rate_plan_model->get_rate_plan($source_rate_plan_id);
		// replicate rate_plan_data
		$new_rate_plan_data = array(
			'rate_plan_name' => $rate_plan['rate_plan_name'] . " (Copy)",
			'room_type_id' => $rate_plan['room_type_id'],
			'currency_id' => $rate_plan['currency_id'],
			'charge_type_id' => $rate_plan['charge_type_id'],
            'description' => $rate_plan['description'],
            'is_shown_in_online_booking_engine' => $rate_plan['is_shown_in_online_booking_engine']
        );
		
		$data['room_types'] = $this->Room_type_model->get_room_types($this->company_id);
		$data['charge_types'] = $this->Charge_type_model->get_room_charge_types($this->company_id);
		$data['currencies'] = $this->Currency_model->get_available_currency_list($this->company_id);
		
		// Create Rate Plan
		$new_rate_plan_id = $this->Rate_plan_model->create_rate_plan($new_rate_plan_data);
	     
		$rate_plan['rate_plan_id'] = $new_rate_plan_id; // for some reason, rate_plan_id is not returned
		
        $source_rate_x_date_ranges = $this->Rate_model->get_rate_x_date_ranges($source_rate_plan_id);
        if(!empty($source_rate_x_date_ranges)){
            foreach ($source_rate_x_date_ranges as $rate_x_date_range)
            {
                $rate = $this->Rate_model->get_rate_by_rate_id($rate_x_date_range['rate_id']);
                $rate['rate_plan_id'] = $new_rate_plan_id;
                unset($rate['rate_id']);
                $new_rate_id = $this->Rate_model->create_rate($rate);
                
                // We can use the existing date_range, because the date_ranges are write-only anyways
                // Meaning, we never modify the date range. If there's change in rates, we simply overlap
                // the existing date range & rates with the new ones.
                // Hence, when replicating, we can use the existing date_ranges
                //
                // Going further, I should create a table rate_plan_x_rate, and remove rate_plan_id field in
                // rate table. this will further allow us to simplify the replication process.
                $this->Date_range_model->create_date_range_x_rate(
                Array(
                    'rate_id' => $new_rate_id, 
                    'date_range_id' => $rate_x_date_range['date_range_id']
                    )
                );
            }
        }
		
		
		// Assign the Base Rate into the newly created rate plan
		$this->Rate_plan_model->update_rate_plan(
				array('base_rate_id' => $source_rate_x_date_ranges[0]['rate_id']),
				$new_rate_plan_id
            );

        $data['rate_plan'] = $rate_plan;

            // $data = array ('isSuccess'=> TRUE, 'message' => 'Rate Plan replicateds');
			// echo json_encode($data);
		
		$this->load->view('hotel_settings/rate_plan_settings/replicate_rate_plan', $data);
	}


	function update_rate_plan_AJAX(){
		global $unsanitized_post;
		$rate_plan_id = $this->security->xss_clean($this->input->post('rate_plan_id'));
		$rate_plan_data = array(
			'rate_plan_name' => base64_decode($this->security->xss_clean($this->input->post('rate_plan_name'))),
            'room_type_id' => base64_decode($this->security->xss_clean($this->input->post('room_type_id'))),
            'currency_id' => base64_decode($this->security->xss_clean($this->input->post('currency_id'))),
            'charge_type_id' => base64_decode($this->security->xss_clean($this->input->post('charge_type_id'))),
            //'description' => $this->input->post('description'),
            'description' => isset($unsanitized_post['description']) ? $unsanitized_post['description'] : "" ,
			      'is_shown_in_online_booking_engine' => base64_decode($this->security->xss_clean($this->input->post('is_shown_in_online_booking_engine'))),
            'policy_code'=> $this->input->post('policy_code') !="" ? $this->security->xss_clean($this->input->post('policy_code')) : null, 
        );
        $derived_rate_enable =  $this->input->post('derivedrate_enable') != "" ? $this->security->xss_clean($this->input->post('derivedrate_enable')) : 0;
        $parent_room_type = $this->input->post('parentroom_type') !="" ? $this->security->xss_clean($this->input->post('parentroom_type')) : null;            
        $parent_rate_plan = $this->input->post('parentrate_plan') !="" ? $this->security->xss_clean($this->input->post('parentrate_plan')) : null;
        $rate_logic = $this->input->post('ratelogic') !="" ? $this->security->xss_clean($this->input->post('ratelogic')) : null;
        $rate_logic_amount = $this->input->post('ratelogic_amount') !="" ? $this->security->xss_clean($this->input->post('ratelogic_amount')) : null;        
        $checklist = $this->input->post('dataArray_check');

        if(($this->is_derived_rate_enabled == 1) && $derived_rate_enable == 1 ){

            $mims_arrival = isset($checklist[0]['min_stay_arrival']) ? $checklist[0]['min_stay_arrival'] : 0 ;
            $mams_arrival = isset($checklist[1]['max_stay_arrival']) ? $checklist[1]['max_stay_arrival'] : 0 ;
            $closed_to_arrival = isset($checklist[2]['closed_to_arrival']) ? $checklist[2]['closed_to_arrival'] : 0;
            $closed_to_departure = isset($checklist[3]['closed_to_departure']) ? $checklist[3]['closed_to_departure'] : 0 ;
            $stop_sell = isset($checklist[4]['stop_sell']) ? $checklist[4]['stop_sell'] : 0;

            $deriveddatapre =  $this->Option_model->get_option_by_company('derived_rate_'.$rate_plan_id,$this->company_id);
            //prx($deriveddata,1);
            $Ddetailspre =  json_decode($deriveddatapre[0]['option_value'],true);
           
            if(
                $Ddetailspre['rate_logic'] != $rate_logic || 
                $Ddetailspre['rate_logic_amount'] != $rate_logic_amount
            ){
                $meta = array(
                    "derived_rate_enable" => $derived_rate_enable,
                    "parent_room_type" => $parent_room_type,
                    "parent_rate_plan" => $parent_rate_plan,
                    "rate_logic" => $rate_logic,
                    "rate_logic_amount" => $rate_logic_amount,
                    "rate_plan_id" => $rate_plan_id,
                    "company_id" => $this->company_id,
                    "rate" => 1,
                    "mims_a" => $mims_arrival,
                    "mams_a" => $mams_arrival,
                    "cta" => $closed_to_arrival, 
                    "ctd" => $closed_to_departure,
                    "stop_sell" => $stop_sell
                );

                $this->Option_model->update_option_company('derived_rate_'.$rate_plan_id,json_encode($meta),$this->company_id);
            }
        } 
		//update room type
		if ($this->Rate_plan_model->update_rate_plan($rate_plan_data, $rate_plan_id))
		{
			$extras = $this->security->xss_clean($this->input->post('extras'));

            $old_extra_ids_array = $this->Extra_model->get_rate_plan_extras($rate_plan_id, $rate_plan_data['room_type_id']);

                if($old_extra_ids_array && count($old_extra_ids_array) > 0) {
                    foreach ($old_extra_ids_array as $key => $value) {
                        $this->Extra_model->delete_rate_plan_etras($rate_plan_id, $rate_plan_data['room_type_id'], $value['extra_id']);
                    }
                }

			if($extras)
			{
				foreach($extras as $extra_id)
				{
					$this->Extra_model->create_rate_plan_extras($rate_plan_id, $extra_id, $rate_plan_data['room_type_id']);
				}
			}

			$this->_create_rate_plan_log("Rate Plan updated ( [ID {$rate_plan_id}])");
			$value = 'Save Successful';
		}
		else
		{
			$value = 'An error occured. Please contact adminstrator if it continues.';
		}
	
		$data = array (
			'error' => '',
			'value' => $value
		);
		
		echo json_encode($data);
	}

	function get_rate_plans() {
		$room_type_id = $this->input->get('room_type_id');
		
		$data['charge_types'] = $this->Charge_type_model->get_room_charge_types($this->company_id);
		$data['currencies'] = $this->Currency_model->get_available_currency_list($this->company_id);
		$data['room_types'] = $this->Room_type_model->get_room_types($this->company_id);
		$data['extras'] = $this->Extra_model->get_extras($this->company_id);
		$data['rate_plans'] = $this->Rate_plan_model->get_rate_plans_by_room_type_id($room_type_id, null, false);
		$data['company_id'] = $this->company_id;

		if (isset($data['rate_plans']))
		{
			foreach ($data['rate_plans'] as $id=> $rate_plan)
			{
				$data['rate_plans'][$id]['images'] = $this->Image_model->get_images($rate_plan['image_group_id']);

				if(isset($data['rate_plans'][$id]['images']) && $data['rate_plans'][$id]['images']) {
					$data['images'] = $data['rate_plans'][$id]['images'];
				}
			}
		}

		$rate_plan_extras = array();
		if(isset($data['rate_plans']) && $data['rate_plans']) {
			foreach ($data['rate_plans'] as $key => $value) {
				$data['rate_plans'][$key]['extras'] = $this->Extra_model->get_rate_plan_extras($value['rate_plan_id'], $room_type_id);
			}
		}
		$this->load->view('hotel_settings/rate_plan_settings/edit_rate_plan', $data);
	}

	/**

		RATES

	*/
	function create_rate_AJAX()
	{
	    
	    $tab_identification = $this->input->post("tab_identification");
		$rate_plan_id = $this->input->post("rate_plan_id");
       		
		$date_start = $this->input->post("date_start");
		$date_end = $this->input->post("date_end");
        // Update the end date if it's empty
		$date_end = trim($date_end) != '' ? $date_end : date('Y-m-d', strtotime('+1 year'));
		// Fetch rate POST variables
		$rate_data_variables = array(
									"rate_plan_id",
									"adult_1_rate",
									"adult_2_rate",
									"adult_3_rate",
									"adult_4_rate",
									"additional_adult_rate",
									"additional_child_rate",
									'minimum_length_of_stay',
									'maximum_length_of_stay',
									'minimum_length_of_stay_arrival',
									'maximum_length_of_stay_arrival',
									'closed_to_arrival',
									'closed_to_departure',
									'can_be_sold_online'
			);


        //day array
        $days = array(
                    "monday",
                    "tuesday",
                    "wednesday",
                    "thursday",
                    "friday",
                    "saturday",
                    "sunday"
        );

        if($tab_identification == 1){
            $mon = $this->input->post('adult_1_rate');
            $rate_array = $this->format_general_rate_plan($rate_data_variables);
        }else{
            $mon = $this->input->post('adult_1_rate_mon');
            $rate_array = $this->format_rate_plan($rate_data_variables);
        }

		$this->load->library('form_validation');			
		
		$this->form_validation->set_rules('date_start', 'Start Date', 'required|trim|callback_date_format_check');	

		// Validates the end date:
        // if there is any plan except default plan then need to check the user has entered end date or not
  
		$response = array();

		if ($this->form_validation->run() == TRUE)
		{
			// detect if there's been a modification rates
			if (count($rate_array)!=0) {
                foreach ($rate_array as $key=>$value)
                {
                    $day_string = $value['days'];
                    unset($rate_array[$key]['days']);
                    $rate_data = array();
                    foreach ($rate_array[$key] as $k=>$val)
                    {
                        if($tab_identification == 1){
                            $apply_mon = $this->input->post($k . '_apply_change');
                            $apply_tue = $this->input->post($k . '_apply_change');
                            $apply_wed = $this->input->post($k . '_apply_change');
                            $apply_thu = $this->input->post($k . '_apply_change');
                            $apply_fri = $this->input->post($k . '_apply_change');
                            $apply_sat = $this->input->post($k . '_apply_change');
                            $apply_sun = $this->input->post($k . '_apply_change');
                        }else{
                            $apply_mon = $this->input->post($k . '_apply_change_mon');
                            $apply_tue = $this->input->post($k . '_apply_change_tue');
                            $apply_wed = $this->input->post($k . '_apply_change_wed');
                            $apply_thu = $this->input->post($k . '_apply_change_thu');
                            $apply_fri = $this->input->post($k . '_apply_change_fri');
                            $apply_sat = $this->input->post($k . '_apply_change_sat');
                            $apply_sun = $this->input->post($k . '_apply_change_sun');
                        }


                        if($apply_mon==1 && $day_string == 'monday'){
                            $rate_data[$k] = 'null';
                        }elseif($apply_mon!=2 && $day_string == 'monday'){

                            if($val != 'null')
                            {
                                $rate_data[$k] = $val;
                            }
                        }elseif($apply_tue==1 && $day_string == 'tuesday'){
                            $rate_data[$k] = 'null';
                        }elseif($apply_tue!=2 && $day_string == 'tuesday'){

                            if($val != 'null')
                            {
                                $rate_data[$k] = $val;
                            }
                        }elseif($apply_wed==1 && $day_string == 'wednesday'){
                            $rate_data[$k] = 'null';
                        }elseif($apply_wed!=2 && $day_string == 'wednesday'){

                            if($val != 'null')
                            {
                                $rate_data[$k] = $val;
                            }
                        }elseif($apply_thu==1 && $day_string == 'thursday'){
                            $rate_data[$k] = 'null';
                        }elseif($apply_thu!=2 && $day_string == 'thursday'){

                            if($val != 'null')
                            {
                                $rate_data[$k] = $val;
                            }
                        }elseif($apply_fri==1 && $day_string == 'friday'){
                            $rate_data[$k] = 'null';
                        }elseif($apply_fri!=2 && $day_string == 'friday'){

                            if($val != 'null')
                            {
                                $rate_data[$k] = $val;
                            }
                        }elseif($apply_sat==1 && $day_string == 'saturday'){
                            $rate_data[$k] = 'null';
                        }elseif($apply_sat!=2 && $day_string == 'saturday'){

                            if($val != 'null')
                            {
                                $rate_data[$k] = $val;
                            }
                        }elseif($apply_sun==1 && $day_string == 'sunday'){
                            $rate_data[$k] = 'null';
                        }elseif($apply_sun!=2 && $day_string == 'sunday'){

                            if($val != 'null')
                            {
                                $rate_data[$k] = $val;
                            }
                        }else{
                           /* if($val != 'null')
                            {
                                $rate_data[$k] = $val;
                            }
                           */
                        }
                    }

                    if(count($rate_data) == 0){
                        continue;
                    }
                    $rate_data['rate_plan_id'] = $rate_plan_id;
                    $rate_id = $this->Rate_model->create_rate($rate_data);
                    
                    $date_data['date_start'] = $date_start;
                    $date_data['date_end'] = $date_end;
                    foreach ($days as $day) {

                        if (strpos($day_string, $day) !== false) {
                            $date_data[$day] = 1;
                        }
                        else{
                            $date_data[$day] = 0;
                        }

                    }
                    $date_range_id = $this->Date_range_model->create_date_range($date_data);

                    $this->Date_range_model->create_date_range_x_rate(
                        Array(
                            'date_range_id' => $date_range_id,
                            'rate_id' => $rate_id
                        )
                    );
                }
                $this->_create_rate_plan_log("Rates created for ( Rate Plan [ID {$rate_plan_id}])");

                $rate_array_channex = array();

                if($this->is_derived_rate_enabled == 1){

                    $derived_data_previous =  $this->Option_model->get_option_by_json_data('parent_rate_plan', $rate_plan_id, $this->company_id);
                     
                    foreach ($derived_data_previous as  $derived_data){
                        $Ddetails =  json_decode($derived_data['option_value'],true);
                        
                        $parents_data = $this->Rate_plan_model->get_parent_rateplan_data($Ddetails['parent_room_type'], $Ddetails['parent_rate_plan']);
                        
                        // prx($parents_data);
                        foreach ($parents_data as  $parent_data) {
                        
                            if ($parent_data['monday'] == 1) {
                                $derive_details = $this->derived_rate_plan($Ddetails['rate_logic'], $parent_data, $Ddetails['rate_logic_amount']);
                            }
                            if ($parent_data['tuesday'] == 1) {
                                $derive_details = $this->derived_rate_plan($Ddetails['rate_logic'], $parent_data, $Ddetails['rate_logic_amount']);
                            }
                            if ($parent_data['wednesday'] == 1) {
                                $derive_details = $this->derived_rate_plan($Ddetails['rate_logic'], $parent_data, $Ddetails['rate_logic_amount']);
                            }
                            if ($parent_data['thursday'] == 1) {
                                $derive_details = $this->derived_rate_plan($Ddetails['rate_logic'], $parent_data, $Ddetails['rate_logic_amount']);
                            }
                            if ($parent_data['friday'] == 1) {
                                $derive_details = $this->derived_rate_plan($Ddetails['rate_logic'], $parent_data, $Ddetails['rate_logic_amount']);
                            }
                            if ($parent_data['saturday'] == 1) {
                                $derive_details = $this->derived_rate_plan($Ddetails['rate_logic'], $parent_data, $Ddetails['rate_logic_amount']);
                            }
                            if ($parent_data['sunday'] == 1) {
                                $derive_details = $this->derived_rate_plan($Ddetails['rate_logic'], $parent_data, $Ddetails['rate_logic_amount']);
                            }

                            $adult_1_rate = $derive_details['adult_1_rate'];
                            $adult_2_rate = $derive_details['adult_2_rate'];
                            $adult_3_rate = $derive_details['adult_3_rate'];
                            $adult_4_rate = $derive_details['adult_4_rate'];
                        
                    
                            $minimum_length_of_stay = null;
                            if($Ddetails['mims_a'] == 1){
                              $minimum_length_of_stay = isset($parent_data['minimum_length_of_stay']) ? $parent_data['minimum_length_of_stay'] : null;
                            }
                        
                            $maximum_length_of_stay = null;
                            if($Ddetails['mams_a'] == 1){  
                              $maximum_length_of_stay = isset($parent_data['maximum_length_of_stay']) ? $parent_data['maximum_length_of_stay'] : null;
                            }

                            $closed_to_arrival = 0;
                            if($Ddetails['cta'] == 1){
                                $closed_to_arrival = isset($parent_data['closed_to_arrival']) ? $parent_data['closed_to_arrival'] : 0; 
                            }

                            $closed_to_departure = 0;
                            if($Ddetails['ctd'] == 1){
                                $closed_to_departure = isset($parent_data['closed_to_departure']) ? $parent_data['closed_to_departure'] : 0; 
                            }

                            $can_be_sold_online = 1;
                            if($Ddetails['stop_sell'] == 1){
                                $can_be_sold_online = isset($parent_data['can_be_sold_online']) ? $parent_data['can_be_sold_online'] : 0; 
                            }

                            $additional_adult_rate =  isset($parent_data['additional_adult_rate']) ? $parent_data['additional_adult_rate'] : null; 
                            $additional_child_rate =  isset($parent_data['additional_child_rate']) ? $parent_data['additional_child_rate'] : null;
                            $minimum_length_of_stay_arrival = isset($parent_data['minimum_length_of_stay_arrival']) ? $parent_data['minimum_length_of_stay_arrival'] : null;
                            $maximum_length_of_stay_arrival = isset($parent_data['maximum_length_of_stay_arrival']) ? $parent_data['maximum_length_of_stay_arrival'] : null;

                            $new_date_range_id = $this->Date_range_model->create_date_range(
                                Array(
                                    'date_start' => date('Y-m-d', strtotime($parent_data['date_start'])),
                                    'date_end' =>  date('Y-m-d', strtotime($parent_data['date_end'])),
                                    'monday' =>  $parent_data['monday'] == 1 ? 1 : 0,
                                    'tuesday' =>  $parent_data['tuesday'] == 1 ? 1 : 0,
                                    'wednesday' =>  $parent_data['wednesday'] == 1 ? 1 : 0,
                                    'thursday' =>  $parent_data['thursday'] == 1 ? 1 : 0,
                                    'friday' =>  $parent_data['friday'] == 1 ? 1 : 0,
                                    'saturday' =>  $parent_data['saturday'] == 1 ? 1 : 0,
                                    'sunday' =>  $parent_data['sunday'] == 1 ? 1 : 0,
                                )
                            );

                            $rate_array = Array(
                                'rate_plan_id' => $Ddetails['rate_plan_id'],
                                'base_rate' => $adult_1_rate,
                                'adult_1_rate' => $adult_1_rate,
                                'adult_2_rate' => $adult_2_rate,
                                'adult_3_rate' => $adult_3_rate,
                                'adult_4_rate' => $adult_4_rate,
                                'additional_adult_rate' => $additional_adult_rate,
                                'additional_child_rate' => $additional_child_rate,
                                'minimum_length_of_stay' => $minimum_length_of_stay,
                                'maximum_length_of_stay' => $maximum_length_of_stay,
                                'minimum_length_of_stay_arrival' => $minimum_length_of_stay_arrival,
                                'maximum_length_of_stay_arrival' => $maximum_length_of_stay_arrival,
                                'closed_to_arrival' => $closed_to_arrival,
                                'closed_to_departure' => $closed_to_departure,
                                'can_be_sold_online'=> $can_be_sold_online
                            );

                            $rate_array_channex[] = Array(
                                'rate_plan_id' => $Ddetails['rate_plan_id'],
                                'date_start' => date('Y-m-d', strtotime($parent_data['date_start'])),
                                'date_end' =>  date('Y-m-d', strtotime($parent_data['date_end'])),
                                'adult_1_rate' => $adult_1_rate,
                                'adult_2_rate' => $adult_2_rate,
                                'adult_3_rate' => $adult_3_rate,
                                'adult_4_rate' => $adult_4_rate,
                                'additional_adult_rate' => $additional_adult_rate,
                                'minimum_length_of_stay' => $minimum_length_of_stay,
                                'maximum_length_of_stay' => $maximum_length_of_stay,
                                'closed_to_arrival' => $closed_to_arrival,
                                'closed_to_departure' => $closed_to_departure,
                                'can_be_sold_online'=> $can_be_sold_online
                            );

                            $new_rate_id = $this->Rate_model->create_rate($rate_array);

                            $this->Date_range_model->create_date_range_x_rate(
                                Array(
                                    'rate_id' => $new_rate_id,
                                    'date_range_id' => $new_date_range_id
                                )
                            );

                            // Assign the Base Rate into the newly created rate plan
                            $this->Rate_plan_model->update_rate_plan(
                                array('base_rate_id' => $new_rate_id),
                                $Ddetails['rate_plan_id']
                            );
                        }
                    }
                }

                $response['status'] = "success";
                $response['rate_array_channex'] = $rate_array_channex;
                echo json_encode($response);
                return;
			}

			$response['status'] = "error";
			$response['message'] = "No modification detected";
			echo json_encode($response);
			return;
		
		}
	
		$response['status'] = "error";
		$response['message'] = l("Please enter the valid start date and end date.", true);
		echo json_encode($response);
	}


    function derived_rate_plan($rate_logic, $parent_data, $rate_logic_amount){
        switch ($rate_logic) {
                        
            case 'ASP':
                $adult_1_rate = ($parent_data['adult_1_rate'] != 0) ? $parent_data['adult_1_rate'] : null ;  
                $adult_2_rate = ($parent_data['adult_2_rate'] != 0) ? $parent_data['adult_2_rate'] : null ;
                $adult_3_rate = ($parent_data['adult_3_rate'] != 0) ? $parent_data['adult_3_rate'] : null ;
                $adult_4_rate = ($parent_data['adult_4_rate'] != 0) ? $parent_data['adult_4_rate'] : null ;

                break;

            case 'IBA':
                $adult_1_rate = ($parent_data['adult_1_rate'] != 0) ? $parent_data['adult_1_rate'] + $rate_logic_amount : $parent_data['adult_1_rate'] ;  
                $adult_2_rate = ($parent_data['adult_2_rate'] != 0) ? $parent_data['adult_2_rate'] + $rate_logic_amount : $parent_data['adult_2_rate'] ;
                $adult_3_rate = ($parent_data['adult_3_rate'] != 0) ? $parent_data['adult_3_rate'] + $rate_logic_amount : $parent_data['adult_3_rate'] ;
                $adult_4_rate = ($parent_data['adult_4_rate'] != 0) ? $parent_data['adult_4_rate'] + $rate_logic_amount : $parent_data['adult_4_rate'] ;

                break;

            case 'DBA':
                $adult_1_rate = ($parent_data['adult_1_rate'] != 0) ? $parent_data['adult_1_rate'] - $rate_logic_amount : $parent_data['adult_1_rate'];  
                $adult_2_rate = ($parent_data['adult_2_rate'] != 0) ? $parent_data['adult_2_rate'] - $rate_logic_amount : $parent_data['adult_2_rate'];
                $adult_3_rate = ($parent_data['adult_3_rate'] != 0) ? $parent_data['adult_3_rate'] - $rate_logic_amount : $parent_data['adult_3_rate'];
                $adult_4_rate = ($parent_data['adult_4_rate'] != 0) ? $parent_data['adult_4_rate'] - $rate_logic_amount : $parent_data['adult_4_rate'];

                break;

            case 'IBP':
                $perad1 = ($rate_logic_amount / 100) * $parent_data['adult_1_rate'];
                $perad2 = ($rate_logic_amount / 100) * $parent_data['adult_2_rate'];
                $perad3 = ($rate_logic_amount / 100) * $parent_data['adult_3_rate'];
                $perad4 = ($rate_logic_amount / 100) * $parent_data['adult_4_rate'];

                $adult_1_rate = ($parent_data['adult_1_rate'] != 0) ? $parent_data['adult_1_rate'] + $perad1 : $parent_data['adult_1_rate'];  
                $adult_2_rate = ($parent_data['adult_2_rate'] != 0) ? $parent_data['adult_2_rate'] + $perad2 : $parent_data['adult_2_rate'];
                $adult_3_rate = ($parent_data['adult_3_rate'] != 0) ? $parent_data['adult_3_rate'] + $perad3 : $parent_data['adult_3_rate'];
                $adult_4_rate = ($parent_data['adult_4_rate'] != 0) ? $parent_data['adult_4_rate'] + $perad4 : $parent_data['adult_4_rate'];

                break;
            case 'DBP':
                $perad1 = ($rate_logic_amount / 100) * $parent_data['adult_1_rate'];
                $perad2 = ($rate_logic_amount / 100) * $parent_data['adult_2_rate'];
                $perad3 = ($rate_logic_amount / 100) * $parent_data['adult_3_rate'];
                $perad4 = ($rate_logic_amount / 100) * $parent_data['adult_4_rate'];

                $adult_1_rate = ($parent_data['adult_1_rate'] != 0) ? $parent_data['adult_1_rate'] - $perad1 : $parent_data['adult_1_rate'];  
                $adult_2_rate = ($parent_data['adult_2_rate'] != 0) ? $parent_data['adult_2_rate'] - $perad2 : $parent_data['adult_2_rate']; 
                $adult_3_rate = ($parent_data['adult_3_rate'] != 0) ? $parent_data['adult_3_rate'] - $perad3 : $parent_data['adult_3_rate']; 
                $adult_4_rate = ($parent_data['adult_4_rate'] != 0) ? $parent_data['adult_4_rate'] - $perad4 : $parent_data['adult_4_rate']; 

                break;

            default:
                break;
        }

        return array(
            'adult_1_rate' => $adult_1_rate,
            'adult_2_rate' => $adult_2_rate,
            'adult_3_rate' => $adult_3_rate,
            'adult_4_rate' => $adult_4_rate
        );
    }


// Create Supplied rates
        function create_supplied_rate_AJAX()
	{
		$rate_plan_id = $this->input->post("rate_plan_id");
		$room_type_id = $this->input->post("room_type_id");
		$date_start = $this->input->post("date_start");
		$date_end = $this->input->post("date_end");

		// Fetch rate POST variables
		$rate_data_variables = array(
                                            "rate_plan_id",
                                            "room_type_id",
                                            "supplied_adult_1_rate",
                                            "supplied_adult_2_rate",
                                            "supplied_adult_3_rate",
                                            "supplied_adult_4_rate"
                                        );
                //day array
                $days = array(
                            "monday",
                            "tuesday",
                            "wednesday",
                            "thursday",
                            "friday",
                            "saturday",
                            "sunday"
                        );

                $mon = $this->input->post('supplied_adult_1_rate_mon');

                $rate_array = $this->format_rate_plan($rate_data_variables);
		
		$this->load->library('form_validation');			
		
		$this->form_validation->set_rules('date_start', 'Start Date', 'required|trim|callback_date_format_check');	
		$this->form_validation->set_rules('date_end', 'End Date', 'required|trim|callback_date_format_check');
		$response = array();

		if ($this->form_validation->run() == TRUE)
		{
			// detect if there's been a modification rates
			if (count($rate_array)!=0) {
                            
                            foreach ($rate_array as $key=>$value)
                            {
                                $day_string = $value['days'];
                                unset($rate_array[$key]['days']);
                                $rate_data = array();
                                foreach ($rate_array[$key] as $k=>$val) 
                                {
                                    if($val != 'null')
                                    {
                                        $rate_data[$k] = $val;
                                    }
                                }
                                if(count($rate_data) == 0){
                                    continue;
                                }
                                $rate_data['room_type_id'] = $room_type_id;
                                $rate_data['rate_plan_id'] = $rate_plan_id;
                                $rate_supplied_id = $this->Rate_model->create_supplied_rate($rate_data);

                                $date_data['date_start'] = $date_start;
                                $date_data['date_end'] = $date_end;
                                foreach ($days as $day) {
                                    if (strpos($day_string, $day) !== false) {
                                        $date_data[$day] = 1;
                                    }
                                    else{
                                        $date_data[$day] = 0;
                                    }
                                }
                                $date_range_id = $this->Date_range_model->create_date_range($date_data);

                                $this->Date_range_model->create_date_range_x_rate_supplied(
                                    Array(
                                        'date_range_id' => $date_range_id,
                                        'rate_supplied_id' => $rate_supplied_id
                                    )
                                );
                            }
							$this->_create_rate_plan_log("Supplied Rates created for ( Rate Plan [ID {$rate_plan_id}])");
                            $response['status'] = "success";
                            echo json_encode($response);
                            return;
			}

			$response['status'] = "error";
			$response['message'] = "No modification detected";
			echo json_encode($response);
			return;
		
		}
	
		$response['status'] = "error";
		$response['message'] = validation_errors();
		echo json_encode($response);
	}
        
	function format_rate_plan($rate_data_variables){
        $rate_array = array();
        $mon_array = array();
        $tue_array = array();
        $wed_array = array();
        $thu_array = array();
        $fri_array = array();
        $sat_array = array();
        $sun_array = array();

        foreach ($rate_data_variables as $var)
        {
            if($var =='rate_plan_id') {
                $rate_data[$var] = $this->input->post($var);
            }
            else{
                $mon = $this->input->post($var . '_mon');
                $tue = $this->input->post($var . '_tue');
                $wed = $this->input->post($var . '_wed');
                $thu = $this->input->post($var . '_thu');
                $fri = $this->input->post($var . '_fri');
                $sat = $this->input->post($var . '_sat');
                $sun = $this->input->post($var . '_sun');

                $mon_array[$var] = $mon;
                $tue_array[$var] = $tue;
                $wed_array[$var] = $wed;
                $thu_array[$var] = $thu;
                $fri_array[$var] = $fri;
                $sat_array[$var] = $sat;
                $sun_array[$var] = $sun;
/*
                $apply_mon = $this->input->post($var . '_apply_change_mon');
                $apply_tue = $this->input->post($var . '_apply_change_tue');
                $apply_wed = $this->input->post($var . '_apply_change_wed');
                $apply_thu = $this->input->post($var . '_apply_change_thu');
                $apply_fri = $this->input->post($var . '_apply_change_fri');
                $apply_sat = $this->input->post($var . '_apply_change_sat');
                $apply_sun = $this->input->post($var . '_apply_change_sun');
                if($apply_mon == 1){
                    $mon_array[$var] = 'null';
                }elseif($apply_mon != 2){
                    $mon_array[$var] = $mon;
                }
                //$mon_array[$var] = $mon;
                if($apply_tue == 1){
                    $tue_array[$var] = 'null';
                }elseif($apply_tue != 2){
                    $tue_array[$var] = $tue;
                }
                if($apply_wed == 1){
                    $wed_array[$var] = 'null';
                }elseif($apply_wed != 2){
                    $wed_array[$var] = $wed;
                }
                if($apply_thu== 1){
                    $thu_array[$var] = 'null';
                }elseif($apply_thu!= 2){
                    $thu_array[$var] = $thu;
                }
                if($apply_fri == 1){
                    $fri_array[$var] = 'null';
                }elseif($apply_fri != 2){
                    $fri_array[$var] = $fri;
                }
                if($apply_sat == 1){
                    $sat_array[$var] = 'null';
                }elseif($apply_sat != 2){
                    $sat_array[$var] = $sat;
                }
                if($apply_sun == 1){
                    $sun_array[$var] = 'null';
                }elseif($apply_sun != 2){
                    $sun_array[$var] = $sun;
                }

*/


                foreach($rate_array as $key=>$rate){

                }
            }
        }
        $rate_array['monday'] = $mon_array;
        $rate_array['tuesday'] = $tue_array;
        $rate_array['wednesday'] = $wed_array;
        $rate_array['thursday'] = $thu_array;
        $rate_array['friday'] = $fri_array;
        $rate_array['saturday'] = $sat_array;
        $rate_array['sunday'] = $sun_array;

        $unique_array = array();
        foreach ($rate_array as $key=>$element)
        {
            if (in_array($element, $unique_array)) {
                //
            }
            else{
                $unique_array[$key] = $element;
                $unique_array[$key]['days'] = $key;
            }
        }
        return $unique_array;
    }

    function format_general_rate_plan($rate_data_variables){
       // print_r($rate_data_variables);die();
        $rate_array = array();
        $mon_array = array();
        $tue_array = array();
        $wed_array = array();
        $thu_array = array();
        $fri_array = array();
        $sat_array = array();
        $sun_array = array();

        foreach ($rate_data_variables as $var)
        {
            if($var =='rate_plan_id') {
                $rate_data[$var] = $this->input->post($var);
            }
            else{
                $mon = $this->input->post($var);
                $tue = $this->input->post($var);
                $wed = $this->input->post($var);
                $thu = $this->input->post($var);
                $fri = $this->input->post($var);
                $sat = $this->input->post($var);
                $sun = $this->input->post($var);

                $mon_array[$var] = $mon;
                $tue_array[$var] = $tue;
                $wed_array[$var] = $wed;
                $thu_array[$var] = $thu;
                $fri_array[$var] = $fri;
                $sat_array[$var] = $sat;
                $sun_array[$var] = $sun;
                /*
                                $apply_mon = $this->input->post($var . '_apply_change_mon');
                                $apply_tue = $this->input->post($var . '_apply_change_tue');
                                $apply_wed = $this->input->post($var . '_apply_change_wed');
                                $apply_thu = $this->input->post($var . '_apply_change_thu');
                                $apply_fri = $this->input->post($var . '_apply_change_fri');
                                $apply_sat = $this->input->post($var . '_apply_change_sat');
                                $apply_sun = $this->input->post($var . '_apply_change_sun');
                                if($apply_mon == 1){
                                    $mon_array[$var] = 'null';
                                }elseif($apply_mon != 2){
                                    $mon_array[$var] = $mon;
                                }
                                //$mon_array[$var] = $mon;
                                if($apply_tue == 1){
                                    $tue_array[$var] = 'null';
                                }elseif($apply_tue != 2){
                                    $tue_array[$var] = $tue;
                                }
                                if($apply_wed == 1){
                                    $wed_array[$var] = 'null';
                                }elseif($apply_wed != 2){
                                    $wed_array[$var] = $wed;
                                }
                                if($apply_thu== 1){
                                    $thu_array[$var] = 'null';
                                }elseif($apply_thu!= 2){
                                    $thu_array[$var] = $thu;
                                }
                                if($apply_fri == 1){
                                    $fri_array[$var] = 'null';
                                }elseif($apply_fri != 2){
                                    $fri_array[$var] = $fri;
                                }
                                if($apply_sat == 1){
                                    $sat_array[$var] = 'null';
                                }elseif($apply_sat != 2){
                                    $sat_array[$var] = $sat;
                                }
                                if($apply_sun == 1){
                                    $sun_array[$var] = 'null';
                                }elseif($apply_sun != 2){
                                    $sun_array[$var] = $sun;
                                }

                */


                foreach($rate_array as $key=>$rate){

                }
            }
        }

        $rate_array['monday'] = $mon_array;
        $rate_array['tuesday'] = $tue_array;
        $rate_array['wednesday'] = $wed_array;
        $rate_array['thursday'] = $thu_array;
        $rate_array['friday'] = $fri_array;
        $rate_array['saturday'] = $sat_array;
        $rate_array['sunday'] = $sun_array;

        $unique_array = array();
        foreach ($rate_array as $key=>$element)
        {
            if (in_array($element, $unique_array)) {
                //
            }
            else{
                $unique_array[$key] = $element;
                $unique_array[$key]['days'] = $key;
            }
        }
        return $unique_array;
    }
    
	function edit_rates($rate_plan_id="")
	{
		$data['company_id'] = $this->company_id;
		
		$rate_plan = $this->Rate_plan_model->get_rate_plan($rate_plan_id);
		$data['room_type'] = $this->Room_type_model->get_room_type($rate_plan['room_type_id']);
		$data['rate_plan_id'] = $rate_plan_id;
        $data['room_type_id'] = $rate_plan['room_type_id'];
		$data['rate_plan_name'] = $rate_plan['rate_plan_name'];      
		$data['today'] = $this->Company_model->get_selling_date($this->company_id);
		$data['next_year'] = Date("Y-m-d", strtotime("+1 year", strtotime($data['today'])));
        $data['has_rate_plan'] = $this->Rate_model->get_total_rates_by_rate_plan_id($rate_plan_id);

		$this->load->library('form_validation');			

		$data['selected_sidebar_link'] = 'Rate Plans';
        $data['css_files'] = array(
            base_url() . auto_version('css/rates/rates_modal.css')
        );
		$data['js_files'] = array(
				base_url() . auto_version('js/channel_manager/channel_manager.js'),
				base_url() . auto_version('js/hotel-settings/rate-settings.js'),
                base_url().'js/moment.min.js'
		);
		$data['Ddetails'] = array();
        if($this->is_derived_rate_enabled == 1 ){
            $deriveddata =  $this->Option_model->get_option_by_company('derived_rate_'.$rate_plan_id,$this->company_id);
             if(isset($deriveddata[0]['option_value'])){       
            $data['Ddetails'] =  json_decode($deriveddata[0]['option_value'],true);
             }
         }		
		$data['main_content'] = 'hotel_settings/rate_plan_settings/edit_rates';
		$this->load->view('includes/bootstrapped_template', $data);
	}


	function get_rates_JSON()
	{
		$rate_plan_id = $this->input->post('rate_plan_id');
                $room_type_id = $this->input->post('room_type_id');
		$start_date = $this->input->post('start_date');
			
		$date_range = 28; // number of the days that will be displayed on calendar
		$end_date = Date("Y-m-d", strtotime("+$date_range days", strtotime($start_date)));
		
        $rates = array();
        
		$rates['rates'] = $this->Rate_model->get_daily_rates($rate_plan_id, $start_date, $end_date, $room_type_id);
        
        $rates['errors'] = array();
        foreach($rates['rates']  as $rate ){
            if(is_numeric($rate['maximum_length_of_stay']) && $rate['maximum_length_of_stay'] <= 0)
            {
                $rates['errors']['maximum_length_of_stay_0'] = 'maximum length of stay cannot be 0';
            }
            if(
                is_numeric($rate['minimum_length_of_stay']) && is_numeric($rate['maximum_length_of_stay']) && 
                (intval($rate['maximum_length_of_stay']) < intval($rate['minimum_length_of_stay']))
            )
            {
                $rates['errors']['minimum_length_of_stay_less'] = 'minimum length of stay has to be less than maximum length of stay';
            }
        }
        echo json_encode($rates);
	}
	
	function delete_rate_plan_AJAX()
	{
		$rate_plan_id = $this->security->xss_clean($this->input->post('rate_plan_id'));		
		
		//permission check
		if ($this->Rate_plan_model->check_if_rate_plan_belongs_to_company($rate_plan_id, $this->company_id))
		{
            //if($this->is_derived_rate_enabled == 1 ){

                $nesteddata = $this->Option_model->get_option_by_json_data('parent_rate_plan',$rate_plan_id,$this->company_id); 
                //prx($nesteddata);
                if(isset($nesteddata) && $nesteddata !=''){

                    foreach($nesteddata as $nestedrate) {
                        $Ddetails =  json_decode($nestedrate['option_value'],true);
                       
                       $this->Rate_plan_model->delete_rate_plan($Ddetails['rate_plan_id']);
                       $this->Option_model->delete_option('derived_rate_'.$Ddetails['rate_plan_id']);  
                    }
                }

                $deriveddata =  $this->Option_model->get_option_by_company('derived_rate_'.$rate_plan_id,$this->company_id);
                if(isset($deriveddata[0]['option_value'])){
                   $this->Option_model->delete_option('derived_rate_'.$rate_plan_id);         
                 }     
            //}
            
			if (!$this->Rate_plan_model->delete_rate_plan($rate_plan_id))
			{
				$data = array ('isSuccess'=> FALSE, 'message' => 'Rate Plan delete fail');
				echo json_encode($data);
				return;
			}
			$this->_create_rate_plan_log("Delete Rate Plan ( [ID {$rate_plan_id}])");
			$data = array ('isSuccess'=> TRUE, 'message' => 'Rate Plan deleted');
			echo json_encode($data);
		}
	}


	// Called by callback_date_format_check
	// Checks whether a valid date has been entered.
	function date_format_check($date)
	{
		//match the format of the date
		if (preg_match ("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts))
		{
		//check whether the date is valid of not
			if(checkdate($parts[2],$parts[3],$parts[1]))
			  return true;
			else
			 $this->form_validation->set_message('date_format_check', $this->lang->line('date_must_be_valid_format'));
		}
		else
			$this->form_validation->set_message('date_format_check', $this->lang->line('date_must_be_valid_format'));
		return false;
	}
		
	// generate GUID
	// prey that it do not collide (very low %, but it can happen, but maybe not before you die).
	// http://en.wikipedia.org/wiki/Universally_unique_identifier#Random_UUID_probability_of_duplicates
	// http://php.net/manual/ru/function.com-create-guid.php
	// http://php.net/manual/en/function.uniqid.php
	function _generate_guid()
	{
		return sprintf(
			'%04X%04X%04X%04X%04X%04X%04X%04X',
			mt_rand(0, 65535),
			mt_rand(0, 65535),
			mt_rand(0, 65535),
			mt_rand(16384, 20479),
			mt_rand(32768, 49151),
			mt_rand(0, 65535),
			mt_rand(0, 65535),
			mt_rand(0, 65535)
		);
	}

	/*Extra Settings */

	function products()
	{
		if(!is_null($extras = $this->Extra_model->get_extras($this->company_id)))
		{
			$view_data['extras'] = $extras;
			foreach ($extras as $key => $extra)
			{
				if(!is_null($extra_rates = $this->Rate_model->get_extra_rates($extra['extra_id'])))
				{
					$view_data['extras'][$key]['default_extra_rate'] = $extra_rates[0];
				}
			}
		}
		
		if(!is_null($charge_types = $this->Charge_type_model->get_charge_types($this->company_id)))
		{
			$view_data['charge_types'] = $charge_types;
		}
		
		$view_data['js_files'] = array(
				base_url() . 'js/jquery.jeditable.mini.js',
				base_url() . auto_version('js/hotel-settings/extra-settings.js')
		);
		
		//Load menu view data
		$view_data['selected_sidebar_link'] = 'Products';
		
		$view_data['main_content'] = 'hotel_settings/rate_plan_settings/extra_settings';
		$this->load->view('includes/bootstrapped_template', $view_data);
		
	}
	
	function create_extra()
	{
		
		$new_extra_name = 'New Product'; //Default name
		
		//For extra created,
		if ($new_extra_id = $this->Extra_model->create_extra($this->company_id, $new_extra_name))
		{
			$default_currency = $this->Currency_model->get_default_currency($this->company_id);	
			
			$date_range_id = $this->Date_range_model->create_date_range(Array('date_start'=>'2012-01-01', 'date_end'=>'2099-01-01'));
			$new_extra_rate_id = $this->Rate_model->create_extra_rate(Array('extra_id' => $new_extra_id, 'currency_id' => $default_currency['currency_id']));
			$this->Date_range_model->create_date_range_x_extra_rate(Array('extra_rate_id' => $new_extra_rate_id, 'date_range_id'=> $date_range_id));
			
			$this->_create_rate_plan_log("Create New Extra ( [ID {$new_extra_id}])");
		}
		
		$charge_types = $this->Charge_type_model->get_charge_types($this->company_id);		
			
		//Load data to get html code used by javascript to generate the new room type dynamically.		
		//Instead of making the page refresh to see the new room type.
		$data = array (
			'extra_id' => $new_extra_id,
			'extra_name' => $new_extra_name,
			'charge_types' => $charge_types
		);
		
		$this->load->view('hotel_settings/rate_plan_settings/new_extra', $data);
	}
	
	function delete_extra_AJAX()
	{		
		
		$extra_id = $this->security->xss_clean($this->input->post('extra_id'));
		
		
		if (!$this->Extra_model->update_extra($extra_id, array("is_deleted" => 1)))
		{
			$data = array ('isSuccess'=> FALSE, 'message' => 'Extra delete fail');
			echo json_encode($data);
			return;
		}
		$this->_create_rate_plan_log("Delete Extra ( [ID {$extra_id}])");
		$data = array ('isSuccess'=> TRUE, 'message' => 'Extra deleted');
		echo json_encode($data);
	}
	
	function update_extras()
	{
		$updated_extras = $this->input->post('updated_extras');
        $response = array(
            'error' => false,
            'success' => true
        );

        $default_currency = $this->Currency_model->get_default_currency($this->company_id);

        foreach($updated_extras as $updated_extra)
        {
            $extra_field_id = $this->security->xss_clean($updated_extra['extra_id']);

            $data = array(
                'extra_name' => $this->security->xss_clean($updated_extra['extra_name']),
                'charging_scheme' => $this->security->xss_clean($updated_extra['charging_scheme']),
                'extra_type' => $this->security->xss_clean($updated_extra['extra_type']),
                'charge_type_id' => $this->security->xss_clean($updated_extra['extra_charge_type_id']),
                'show_on_pos' => $this->security->xss_clean($updated_extra['show_on_pos'])
            );
            if ($data['extra_name'])
            {
                if (!$this->Extra_model->update_extra($extra_field_id, $data, $this->company_id))
                {
                    $response = array (
                        'error' => 'An error occurred. Please contact administrator if it continues.',
                        'success' => false
                    );
                    break;
                }
            }
            else //Validation failed. Add error message
            {
                $response = array (
                    'error' => 'An error occurred with booking field '.form_error('extra_name'),
                    'success' => false
                );
                break;
            }

            $extra_rate = $this->security->xss_clean($updated_extra['default_rate']);
			$rate_data = array(
				'rate' => ($extra_rate != '' && $extra_rate >= 0) ? trim(number_format($extra_rate, 2, ".", "")) : 0,
				'currency_id' => $default_currency['currency_id']
			);

			$this->Rate_model->update_extra_rate($extra_field_id, $rate_data);

        }
        echo json_encode($response);
	}
    
    function updated_room_types()
	{
        $updated_room_types = $this->input->post('updated_room_types');
        $response = array(
            'error' => false,
            'success' => true
        );
        
        foreach($updated_room_types as $updated_room_type)
        {
            $room_type_id = $this->security->xss_clean($updated_room_type['id']);
            
            $data = array(
                'sort' => $this->security->xss_clean($updated_room_type['sort_order'])
            );
            if ($room_type_id)
            {
                if (!$this->Room_type_model->update_room_type($room_type_id, $data))
                {
                    $response = array (
                        'error' => 'An error occured. Please contact adminstrator if it continues.',
                        'success' => false
                    );
                    break;
                }
				$this->_create_rate_plan_log("Update Room Type ( [ID {$room_type_id}])");
            }
        }
        echo json_encode($response);
	}
    
    function get_features_AJAX($feature_name = null)
	{
		$faetures = $this->Company_model->get_company($this->company_id);
        echo $feature_name && isset($faetures[$feature_name]) ? $faetures[$feature_name] : "";
	}

}
