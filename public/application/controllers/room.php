<?php

class Room extends MY_Controller 
{       
	function __construct()
	{
		parent::__construct();
		
		$this->load->model('Company_model');
		$this->load->model('Room_model');
		$this->load->model('Room_type_model');
		$this->load->model('User_model');	
		$this->load->model('Channel_model');	
		$this->load->model('Date_range_model');
        $this->load->model('Date_color_model');
        $this->load->model('Employee_log_model');
        
		$global_data['menu_on'] = true;			
		$global_data['submenu'] = 'includes/submenu.php';
		$global_data['submenu_parent_url'] = base_url()."room/";
        $company_partner_id = isset($this->company_partner_id) && $this->company_partner_id ? $this->company_partner_id : 1;
        $global_data['menu_items'] = $this->Menu_model->get_menus(array('parent_id' => 3, 'wp_id' => $company_partner_id));
        
		$this->load->vars($global_data);
		
		$this->load->helper('url');
		
	}
	
	function index() {		

		$data['css_files']= array( 
			base_url() . auto_version('css/review_form/rateit.css')
		);

		$data['js_files'] = array(
			base_url() . auto_version('js/rooms.js'),
			base_url() . auto_version('js/booking/booking_main.js'),
			base_url() . auto_version('js/room_status.js'),
			base_url() . auto_version('js/review_form/jquery.rateit.min.js')
		);

		$room_ratings = $this->Room_model->get_room_rating($this->company_id);
		$data['date'] = $this->selling_date;
		$data['rows'] = $this->Room_model->get_room_inventory($data['date'], true);

		for($i = 0; $i < count($data['rows']); $i++)
		{
			for($j = 0; $j < count($room_ratings); $j++)
			{
				if($room_ratings[$j]['room_id'] == $data['rows'][$i]->room_id)
				{
					$data['rows'][$i]->rating = $room_ratings[$j]['average_rating'];
					$data['rows'][$i]->total_ratings = $room_ratings[$j]['total_ratings'];
					$data['rows'][$i]->total_reviews = $room_ratings[$j]['total_reviews'];
				}
			}
		}
		
		// load content
		$data['selected_menu'] = 'rooms';			
		$data['main_content'] = 'room/room_status';
		$data['selected_submenu'] = 'Status';
		$this->load->view('includes/bootstrapped_template', $data);			
	}

	function update_room_status()
    {	
		$room_status = sqli_clean($this->security->xss_clean($this->input->post('room_status', TRUE)));
		$room_id = sqli_clean($this->security->xss_clean($this->input->post('room_id', TRUE)));
		$this->Room_model->update_room_status($room_id, $room_status);                
        echo json_encode($room_status);
    }

    function update_room_score()
    {	
		$room_score = sqli_clean($this->security->xss_clean($this->input->post('room_score', TRUE)));
		$room_id = sqli_clean($this->security->xss_clean($this->input->post('room_id', TRUE)));
		$this->Room_model->update_room_score($room_id, $room_score);                
        echo json_encode(array('success' => true, 'score' => $room_score));
    }
	
	function set_rooms_clean()
	{
		$this->Room_model->set_rooms_clean($this->company_id);
	}	
			
	function view_room_note_form($room_id, $loading_for_first_time=0) {
		
		// if loading for the first time, load from DB, otherwise, use pre-submit data
		if ($loading_for_first_time) {
			$room_data = $this->Room_model->get_room($room_id);			
		} else {
			$room_data = $this->_parse_room_note_fields(); // this one doesn't actually get used in any models	
		}
		
		// form requires validation
		$input = $this->input->post('submit');
		switch($input) 
		{				
			case('save'):
				echo 	"<script type='text/javascript'>
							alert('Saved Successfully');					
						</script>";
				$this->Room_model->update_room($room_id, $room_data); // save booking detail information
                $this->_create_room_log("Room Updated (".json_encode($room_data)." [ID $room_id])");
		}
		$this->load->view('room/room_note_form', $room_data);
	}

    function _parse_room_note_fields()
    {
        $data['room_id'] = sqli_clean($this->security->xss_clean($this->input->post('room_id', TRUE)));
        $data['notes']   = sqli_clean($this->security->xss_clean($this->input->post('notes', TRUE)));

        return $data;
    }
	
	function _parse_room_edit_fields() {
		$data['room_id'] = sqli_clean($this->security->xss_clean($this->input->post('room_id', TRUE)));
		$data['room_name'] = sqli_clean($this->security->xss_clean($this->input->post('room_name', TRUE)));
		$data['room_type_id'] = sqli_clean($this->security->xss_clean($this->input->post('room_type_id', TRUE)));
		return $data;
	}

	function get_room_AJAX($room_id)
	{
		$response = $this->Room_model->get_room($room_id);
		echo json_encode($response);
	}

	function get_room_reviews()
	{
		$room_id = $this->input->post('room_id');
		$response = $this->Room_model->get_room_reviews($room_id);
		foreach($response as $key => $value)
		{
			if(isset($value['created']) && $value['created'])
				$response[$key]['created'] = date("F jS, Y", strtotime($value['created']));
			else
				$response[$key]['created'] = date("F jS, Y", strtotime($value['check_out_date']));
		}
		echo json_encode($response);
	}

	function update_room_AJAX()
	{
		$room_id = sqli_clean($this->security->xss_clean($this->input->post('room_id', TRUE)));
		
		// we no longer allow user to update room name from calendar page
		$data = Array(
			//'room_name' => $this->input->post('room_name'),
			//'room_type_id' => $this->input->post('room_type_id'),
			'status' => sqli_clean($this->security->xss_clean($this->input->post('status', TRUE)))
		);

		$this->Room_model->update_room($room_id, $data);
        //$this->_create_room_log("Room updated ({$data['room_name']} [{$room_id}])");

		$response = array(
		  'response'=>'success!'
		);

		echo json_encode($response);

	}	
    
	function _create_room_log($log) {
        $log_detail =  array(
                    "user_id" => $this->user_id,
                    "selling_date" => $this->selling_date,
                    "date_time" => gmdate('Y-m-d H:i:s'),
                    "log" => $log,
                );   
        
        $this->Employee_log_model->insert_log($log_detail);     
    }
    
	function get_notes_AJAX()
	{
		$room_id = sqli_clean($this->security->xss_clean($this->input->post('room_id')));
		$response = array(
		  'response'=>'success!',
		  'notes' => $this->Room_model->get_notes($room_id)
		);
		echo json_encode($response);

	}

	function update_notes_AJAX()
	{
		$room_id = $this->input->post('room_id');
		$notes = $this->input->post('notes');

		$data = Array(
			"notes" => $notes
			);

		$this->Room_model->update_room($room_id, $data);
        $this->_create_room_log("Room Note Updated ([ID $room_id])");
		$response = array(
		  'response'=>'success!'
		);

		echo json_encode($response);

	}

	// net availability
	function get_room_type_availability_AJAX()
	{
        $channel = sqli_clean($this->security->xss_clean($this->input->get('channel')));
        $channel_key = sqli_clean($this->security->xss_clean($this->input->get('channel_key')));
        $start_date = sqli_clean($this->security->xss_clean($this->input->get('start_date')));
        $end_date = sqli_clean($this->security->xss_clean($this->input->get('end_date')));
		$filter_can_be_sold_online = $this->input->get('filter_can_be_sold_online') == 'true' ? true : false;
		$res = $this->Room_type_model->get_room_type_availability($this->company_id, $channel, $start_date, $end_date, null, null, $filter_can_be_sold_online, null, true, true, true, true, null, $channel_key);
        $data_ar = array();


        $isThresholdEnabled = true;
		$isThresholdEnabled = apply_filters('is_threshold_enabled', array('ota_key' => $channel_key, 'isThresholdEnabled' => $isThresholdEnabled));

        if(!empty($res)){
            foreach($res as $key => $r){
                $result = $this->Room_model->get_room_count_by_room_type_id($r['id']); //  get rooms in particular room type
                $res[$key]['room_count'] = $result['room_count'];
                $res[$key]['is_threshold_enabled'] = $isThresholdEnabled;
            }
        }
		echo json_encode($res, true);
	}
       
    function get_rooms_available_AJAX()
	{
		$channel = sqli_clean($this->security->xss_clean($this->input->get('channel', TRUE)));
        $channel_key = sqli_clean($this->security->xss_clean($this->input->get('channel_key', TRUE)));
		$start_date =  sqli_clean($this->security->xss_clean($this->input->get('start_date', TRUE)));
		$end_date =  sqli_clean($this->security->xss_clean($this->input->get('end_date', TRUE)));
		$company_id = sqli_clean($this->security->xss_clean($this->input->get('company_id', TRUE)));

        $res = null;
        if ($channel_key && $channel_key == 'obe') {

        	$company_key_data = $this->Company_model->get_company_api_permission($company_id);
            $company_access_key = isset($company_key_data[0]['key']) && $company_key_data[0]['key'] ? $company_key_data[0]['key'] : null;

            $channel = apply_filters('get_ota_id', 'obe');
            $channel = $channel ? $channel : SOURCE_ONLINE_WIDGET;

            $res = $this->Room_type_model->get_room_type_availability($company_id, $channel, $start_date, $end_date, null, null, true, null, true, true, true, true, $company_access_key, $channel_key);

        } else {
            $res = $this->Room_type_model->get_room_type_availability($company_id, $channel, $start_date, $end_date);
        }

		$data_ar = array();
		$result = null;
		if(!empty($res)){
			foreach($res as $key => $r){

				$result = $this->Room_model->get_room_count_by_room_type_id($r['id']); //  get rooms in particular room type
				$res[$key]['room_count'] = $result['room_count'];

				if($result['room_count'] > 0)
				{
					$data_ar[$key] = array();
					foreach($r['availability'] as $row)
					{
						if($row['availability'] == '0')
						{
							$check_in_date = ($row['date_start']);
							$check_in_date_object = date_create($row['date_start']);
							$check_out_date_object = date_create($row['date_end']);

							$interval = date_diff($check_in_date_object, $check_out_date_object);

							$days = $interval->format('%a');

							for($i = 0; $i <= ($days - 1)  ; $i++)
							{
								$data_ar[$key][] = $check_in_date;
								$check_in_date = date('Y-m-d',strtotime($check_in_date . "+1 days"));
							}
						}
					}                        
				}

			} 
			$result = null;
			foreach($data_ar as $data){
				if($result !== null){
					$result = array_intersect($data, $result);
				}else{
					$result = $data;
				}
			}
		}
		echo json_encode($result ? $result : array());
	}

	// max availability set by hotels
	function get_room_type_max_availability_AJAX()
	{
        $channel = sqli_clean($this->security->xss_clean($this->input->get('channel')));
        $start_date = sqli_clean($this->security->xss_clean($this->input->get('start_date')));
        $end_date = sqli_clean($this->security->xss_clean($this->input->get('end_date')));
		$res = $this->Room_type_model->get_room_type_max_availability($this->company_id, $channel, $start_date, $end_date);
        echo json_encode($res, true);
	}
	
    function get_instructions_AJAX()
    {
        $room_id = sqli_clean($this->security->xss_clean($this->input->post('room_id')));
        $response = array(
          'response'=>'success!',
          'instructions' => $this->Room_model->get_instructions($room_id)
        );
        echo json_encode($response);
    }

    function update_instructions_AJAX()
    {
        $room_id = $this->input->post('room_id');
        $instructions = $this->input->post('instructions');

        $data = Array(
            "instructions" => $instructions
            );

        $this->Room_model->update_room($room_id, $data);
        $this->_create_room_log("Room Instruction Updated ([ID $room_id])");
        $response = array(
          'response'=>'success!'
        );

        echo json_encode($response);
    }

    function inventory()
	{
		$data['js_files'] = array(
			base_url() . auto_version('js/mustache.min.js'),
			base_url() . auto_version('js/channel_manager/channel_manager.js'),
			base_url() . auto_version('js/room_inventory.js')
		);
		$data['css_files'] = array(
			base_url() . auto_version('css/room_inventory.css')
		);
		$data['selected_menu'] = 'rooms';			
		$data['main_content'] = 'room/room_inventory';		
		$data['selected_submenu'] = 'Inventory';
		
		$channels_keys = array();

        $channels_keys = apply_filters('get_inventory_channel_keys', $channels_keys);
        
        $channels = count($channels_keys) ? $this->Channel_model->get_all_channels($channels_keys) : array();

        $data['channels'] = array_merge(array(array("id" => -1, "name" => "Overview")), $channels);
        
		$this->load->view('includes/bootstrapped_template', $data);			
	}

	function modify_availabilities() 
	{
		$view_data['date_start'] = sqli_clean($this->security->xss_clean($this->input->get('dateStart')));
		$view_data['room_type_ids'] = implode(',', $this->input->get('roomTypeIds'));
		$view_data['room_type_names'] = $this->input->get('roomTypeNames');
		$view_data['channel_id'] = sqli_clean($this->security->xss_clean($this->input->get('channelId')));
		//$view_data['room_type_ids'] = explode(',', $this->input->get('roomTypeIds'));

		$this->load->view('room/modify_availabilities', $view_data);
	}
    
	function modify_availabilities_POST()
	{
		$update_status_only = $this->input->post("update_status_only");
        
		$date_start = sqli_clean($this->security->xss_clean($this->input->post("date_start")));
		$date_end = sqli_clean($this->security->xss_clean($this->input->post("date_end")));
		$monday = ($this->input->post("monday") == 'true')?'1':'0';
		$tuesday = ($this->input->post("tuesday") == 'true')?'1':'0';
		$wednesday = ($this->input->post("wednesday") == 'true')?'1':'0';
		$thursday = ($this->input->post("thursday") == 'true')?'1':'0';
		$friday = ($this->input->post("friday") == 'true')?'1':'0';
		$saturday = ($this->input->post("saturday") == 'true')?'1':'0';
		$sunday = ($this->input->post("sunday") == 'true')?'1':'0';

		$channel_id = sqli_clean($this->security->xss_clean($this->input->post('channel_id')));
		$room_type_ids = explode(',', $this->input->post("room_type_ids"));
        
        if($update_status_only){
            $status = $this->input->post("status");
        }else{
            $availability = $this->input->post("availability");
        }
		$this->load->library('form_validation');			
		
		$this->form_validation->set_rules('date_start', 'Start Date', 'required|trim|callback_date_format_check');	
		$this->form_validation->set_rules('date_end', 'End Date', 'required|trim|callback_date_format_check');
		if(!$update_status_only){
            $this->form_validation->set_rules('availability', 'Availability for OTAs', 'trim|required|numeric');
        }
		$response = array();

		foreach($room_type_ids as $i => $room_type)
		{
            $response[$i] = array();

			if ($this->form_validation->run() == TRUE)
			{
				$response[$i]['status'] = "success";

				$date_range_id = $this->Date_range_model->create_date_range(
					Array(
						'date_start' => $date_start,
						'date_end' => $date_end,
						'monday' => $monday,
						'tuesday' => $tuesday,
						'wednesday' => $wednesday,
						'thursday' => $thursday,
						'friday' => $friday,
						'saturday' => $saturday,
						'sunday' => $sunday
						)
					);

				$date_range_x_room_type_id = $this->Date_range_model->create_date_range_x_room_type(
					Array(
						'room_type_id' => $room_type, 
						'date_range_id' => $date_range_id
						)
					);
                
                if ($update_status_only) {
                    $this->Date_range_model->create_date_range_x_room_type_x_status(
                        Array(
                            'date_range_x_room_type_id' => $date_range_x_room_type_id,
                            'channel_id' => $channel_id,
                            'can_be_sold_online' => $status
                        )
                    );
                } else {
                    $this->Date_range_model->create_date_range_x_room_type_x_channels(
                        Array(
                            'date_range_x_room_type_id' => $date_range_x_room_type_id,
                            'channel_id' => $channel_id,
                            'availability' => $availability
                        )
                    );
                }
			}
			else
			{
				$response[$i]['status'] = "error";

				if($date_start == ''){
					$response[$i]['message'][] = 'The Start Date field is required';
				}
				if($date_end == ''){
					$response[$i]['message'][] = 'The End Date field is required';
				}
				if($availability == ''){
					$response[$i]['message'][] = 'The Availability for OTAs field is required';
				}
				// $response[$i]['message'] = validation_errors();
			}
		}

		echo json_encode($response);
	}

	function set_room_type_ota_threshold(){
        $room_type_id = $this->input->post('room_type_id');
        $threshold_val = $this->input->post('threshold_val');
        $data = array();
        $room_type_id = isset($room_type_id)? $room_type_id : null;
        $data['ota_close_out_threshold'] = isset($threshold_val)? $threshold_val : null;
        $this->Room_type_model->update_room_type($room_type_id, $data);
        echo json_encode('successfully updated', true);
    }
}
