<?php

/**
 * Class Room_inventory
 * @property Room_model      Room_model
 * @property Room_type_model Room_type_model
 */
class Room_inventory extends MY_Controller {

    function __construct() {

        parent::__construct();

        $this->load->model('User_model');
        $this->load->model('Image_model');
        $this->load->model('Company_model');
        $this->load->model('Room_model');
        $this->load->model('Room_type_model');
        $this->load->model('Date_range_model');
        $this->load->model('Floor_model');
        $this->load->model('Feature_model');
        $this->load->model('Room_location_model');
        $this->load->model('Booking_source_model');
        $this->load->model('Employee_log_model');
        $this->load->model('Charge_type_model');
        $this->load->library('Form_validation');
        $this->load->library('ckeditor');
        $this->load->library('ckfinder');
        $this->load->helper('ckeditor_helper');

        $view_data['menu_on'] = true;
        $view_data['selected_menu'] = 'Settings';
        $view_data['selected_submenu'] = 'Room Inventory';
        $view_data['submenu'] = 'hotel_settings/hotel_settings_submenu.php';
        
        $view_data['submenu_parent_url'] = base_url()."settings/";
        $view_data['sidebar_menu_url'] = base_url()."settings/room_inventory/";
        
        $view_data['menu_items'] = $this->Menu_model->get_menus(array('parent_id' => 5, 'wp_id' => 1));
        $view_data['sidebar_links'] = $this->Menu_model->get_menus(array('parent_id' => 32, 'wp_id' => 1));

        $this->load->vars($view_data);
    }

    function index() {
        $this->rooms();
    }

    /**
     * Room settings page
     */
    function rooms() {
        
        $view_data = array();

        //get room_types
        if (!is_null($room_types = $this->Room_type_model->get_room_types($this->company_id))) {
            $view_data['room_types'] = $room_types;
        }

        //get rooms
        if (!is_null($rooms = $this->Room_model->get_rooms($this->company_id,'sort_order', '', true))) {
            $view_data['rooms'] = $rooms;
        }
        
        if (!is_null($locations = $this->Room_location_model->get_room_location($this->company_id))) {
            $view_data['location'] = $locations;
        }
        
        if (!is_null($floors = $this->Floor_model->get_floor($this->company_id))) {
            $view_data['floor'] = $floors;
        }
        $view_data['js_files'] = array(
            base_url() . auto_version('js/hotel-settings/room-settings.js')
        );
        if( isset($this->is_housekeeper_manage_enabled) && $this->is_housekeeper_manage_enabled == 1){
            $view_data['housekeeper_list'] = $this->User_model->get_company_mambers($this->company_id,'is_housekeeping');
            //prx($view_data['housekeeper_list']);
        }
        $view_data['selected_sidebar_link'] = 'Rooms';

        $view_data['main_content'] = 'hotel_settings/room_inventory_settings/room_settings';
        $this->load->view('includes/bootstrapped_template', $view_data);
    }

    function create_room_AJAX($room_type_id = null) {  
        $companyInfo = $this->Company_model->get_company($this->company_id);
        $numberOfRooms = $companyInfo['number_of_rooms'];
        $roomDetail = $this->Room_model->get_rooms($this->company_id);
        //echo $this->db->last_query();
        $actualUsedRoom = count($roomDetail);
        if ($room_type_id) {
            $view_data['room_type_id'] = $room_type_id;
        }
        $view_data['room_types'] = $this->Room_type_model->get_room_types($this->company_id);
        // if (!is_null($locations = $this->Room_location_model->get_room_location($this->company_id))) {
        //     $view_data['location'] = $locations;
        // }
        
        // if (!is_null($floors = $this->Floor_model->get_floor($this->company_id))) {
        //     $view_data['floor'] = $floors;
        // }
        
        if ($actualUsedRoom < $numberOfRooms) {
            $room_id = $this->Room_model->create_room($this->company_id, 'New', $room_type_id);
            $view_data['room'] = $this->Room_model->get_room($room_id);
            
            $this->_create_room_log("Room created ('New' [ID {$room_id}])");
        }else{
            $view_data['room_limit'] = "You have reached maximum number of rooms";
        }
        $this->load->view('hotel_settings/room_inventory_settings/new_room', $view_data);
    }

    function save_rooms_AJAX() {
        // $strRequest = file_get_contents('php://input');
        $strRequest = $this->security->xss_clean($this->input->post('room_data'));
        $rooms = json_decode(json_encode($strRequest));
        $error_flag = false;
        foreach ($rooms as $room) {

            if( strpos($room->room_name, ',') !== false ) {
                $error_flag = true; 
                break;
            }

            $data = Array(
                'room_name' => $room->room_name,
                'room_type_id' => $room->room_type_id,
                'location_id' => isset($room->location_id) ? $room->location_id : 0,
                'floor_id' => isset($room->floor_id) ? $room->floor_id : 0,
                'can_be_sold_online' => $room->can_be_sold_online,
                'group_id' => $room->group_id,
                'sort_order' => isset($room->sort_order) ? (int) $room->sort_order : 0
            );
            $this->Room_model->update_room($room->room_id, $data);
            $this->_create_room_log("Room updated ({$room->room_name} [ID {$room->room_id}])");
        }

        if($error_flag) {
            echo "Please do not use comma (,) as a separator";
        } else {
            echo 1;
        }
    }

    //changes selected room's roomtype
    // not deleting actually, hiding them so they can be restored
    function delete_room_AJAX() {
        $room_id = $this->input->post('room_id');

        $data = array('is_deleted' => 1, 'is_hidden' => 1);
        $future_reservations = $this->Room_model->check_future_reservations_for_room($room_id, $this->selling_date);
        
        if($future_reservations){
            echo json_encode(array("future_reservation_available" => true));
        }
        else{
            if ($this->Room_model->update_room($room_id, $data)) {

                $room_info = $this->Room_model->get_room($room_id);
                $this->_create_room_log("Room Hidden ({$room_info['room_name']} [ID $room_id])");

                $data = array('isSuccess' => TRUE, 'message' => 'Room hidden successfully');
                echo json_encode($data);
            } else {
                $data = array('isSuccess' => FALSE, 'message' => 'Room hiding failed');
                echo json_encode($data);
            }
        }
    }
    
    function restore_hidden_room_AJAX()
    {
        $room_id = $this->input->post('room_id');
        $data = array(
                "is_deleted" => 0,
                "is_hidden" => 0
            );
        if ($this->Room_model->update_room($room_id, $data)) {
            $room_info = $this->Room_model->get_room($room_id);
            $this->_create_room_log("Room Restored ({$room_info['room_name']} [ID $room_id])");
            
            $data = array('isSuccess' => TRUE, 'message' => 'Room has been restored successfully');
            echo json_encode($data);
        } else {
            $data = array('isSuccess' => FALSE, 'message' => 'Room restoring failed');
            echo json_encode($data);
        }
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
    /**
      ROOM TYPES SETTINGS
     */
    function room_types() {

        $view_data = array();

        $view_data['company_id'] = $this->company_id;

        //get room types for view
        $this->load->model('Room_type_model');
        if (!is_null($room_types = $this->Room_type_model->get_room_types($this->company_id))) {
            foreach ($room_types as $index => $room_type) {

                // if the room type doesn't have image_group_id set, create one.
                // this is made to deal with MIGRATED room_types from Minical v1.
                if ($room_type['image_group_id'] == '') {
                    $data = array(
                        'image_group_id' => $this->Image_model->create_image_group(ROOM_TYPE_IMAGE_TYPE_ID)
                    );
                    $this->Room_type_model->update_room_type($room_type['id'], $data);
                }

                //$room_types[$index]['images'] = $this->Image_model->get_images($room_type['image_group_id']);
                
                $rate_plans[$room_type['id']] = $this->Rate_plan_model->get_rate_plans_by_room_type_id($room_type['id']);
            }

            $view_data['rate_plans'] = $rate_plans;

            $view_data['room_types'] = $room_types;
        }

        if (!is_null($charge_types = $this->Charge_type_model->get_charge_types($this->company_id)))
        {
            $view_data['charge_types'] = $charge_types;
        }

        $view_data['css_files'] = array(
            base_url() . auto_version('css/croppic.css')
        );
        /*ckeditor*/
        
        $this->ckeditor->basePath = base_url().'application/third_party/ckeditor/';
        // $this->ckeditor->config['toolbar'] = array(
        //         array( 'Source', '-', 'Bold', 'Italic', 'Underline', '-','Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo','-','NumberedList','BulletedList' )
        //                );
        $this->ckeditor->config['language'] = 'en';
        //$this->ckeditor->config['width'] = '730px';
        //$this->ckeditor->config['height'] = '300px';             

        //Add Ckfinder to Ckeditor
        $this->ckfinder->SetupCKEditor($this->ckeditor,'../../ckfinder/'); 
        //load view
       // $data['js_files']   = array(base_url().auto_version('/ckeditor/ckeditor.js'));
        
        $view_data['js_files'] = array(
            'https://cdn.ckeditor.com/4.9.1/full/ckeditor.js',
            base_url() . auto_version('js/hotel-settings/room-type-settings.js'),
            base_url() . auto_version('js/croppic.js'),
            base_url() . auto_version('js/hotel-settings/image-settings.js'),
            
        );
        $view_data['selected_sidebar_link'] = 'Room Types';

        $view_data['main_content'] = 'hotel_settings/room_inventory_settings/room_type_settings';
        $this->load->view('includes/bootstrapped_template', $view_data);
    }

    function add_room_type(){

        if (!is_null($charge_types = $this->Charge_type_model->get_charge_types($this->company_id)))
        {
            $data['charge_types'] = $charge_types;
        }

        $data['rate_plans'] = $this->Rate_plan_model->get_rate_plans($this->company_id);

        $this->load->view('hotel_settings/room_inventory_settings/new_room_type', $data);
    }

    function create_room_type() {
        global $unsanitized_post;
        $this->form_validation->set_rules('room_type_id', 'Room Type ID', 'trim');

        // $room_type_id = $this->security->xss_clean($this->input->post('room_type_id'));
        
        $data = array(
            'name' => base64_decode($this->security->xss_clean($this->input->post('room_type_name'))),
            'acronym' => base64_decode($this->security->xss_clean($this->input->post('acronym'))),
            'max_occupancy' => base64_decode($this->security->xss_clean($this->input->post('max_occupancy'))),
            'min_occupancy' => base64_decode($this->security->xss_clean($this->input->post('min_occupancy'))),
            'max_adults' => base64_decode($this->security->xss_clean($this->input->post('max_adults'))),
            'max_children' => base64_decode($this->security->xss_clean($this->input->post('max_children'))),
            'can_be_sold_online' => $this->security->xss_clean($this->input->post('can_be_sold_online')) ? base64_decode($this->security->xss_clean($this->input->post('can_be_sold_online'))) : 0,
            'default_room_charge' => $this->input->post('default_room_charge') ? base64_decode($this->input->post('default_room_charge')) : null,
            'prevent_inline_booking' => base64_decode($this->input->post('prevent_inline_booking')),
            'description' => isset($unsanitized_post['description']) ? $unsanitized_post['description'] : null,
            'company_id' => $this->company_id
        );
        $file_name = $this->security->xss_clean($this->input->post('file_name'));


        if ($this->form_validation->run() == TRUE) {
            
            $max_occupancy = $this->input->post('max_occupancy') ? base64_decode($this->input->post('max_occupancy')) : 4;
            $min_occupancy = $this->input->post('min_occupancy') ? base64_decode($this->input->post('min_occupancy')) : 4;
            $max_adults = base64_decode($this->input->post('max_adults'));
            $max_children = base64_decode($this->input->post('max_children'));
            $total = $max_adults + $max_children;
            
            if($min_occupancy > $max_occupancy)
            {
                $value = 'An error occured. Number of Min Occupancy should be equal or less than Max Occupancy.';
                $data = array(
                    'error' => 'error',
                    'value' => $value
                );
            }
            else if($max_occupancy && $max_adults > $max_occupancy)
            {
                $value = 'An error occured. Number of adults should be less than Max Occupancy.';
                $data = array(
                    'error' => 'error',
                    'value' => $value
                );
            }
            else if($max_adults < 1 && $min_occupancy)
            {
                $value = 'An error occured. Number of adults should be atleast 1.';
                $data = array(
                    'error' => 'error',
                    'value' => $value
                );
            }
            else if($total < $min_occupancy)
            {
                $value = 'An error occured. Number of guests should be equal or greater than Min Occupancy.';
                $data = array(
                    'error' => 'error',
                    'value' => $value
                );
            }
            else if($max_occupancy && $max_children >= $max_occupancy)
            {
                $value = 'An error occured. Number of childrens should be less than Max Occupancy.';
                $data = array(
                    'error' => 'error',
                    'value' => $value
                );
            }
            else
            {
                //add room type
                if ($room_type_id = $this->Room_type_model->add_new_room_type($data)) {
                    $this->_create_room_log("Create New Room Type ( [ID {$room_type_id}])");

                    $date_range_id = $this->Date_range_model->create_date_range(
                            Array(
                                'date_start' => '2000-01-01',
                                'date_end' => '2030-01-01',
                            )
                    );

                    $this->Date_range_model->create_date_range_x_room_type(
                            Array(
                                'room_type_id' => $room_type_id,
                                'date_range_id' => $date_range_id
                            )
                    );

                    $value = 'Save Successful';
                } else {
                    $value = 'An error occured. Please contact adminstrator if it continues.';
                }

                $data = array(
                    'error' => '',
                    'value' => $value
                );
            }

            echo json_encode($data);
        } else { //Validation failed. Add error message
            $data = array(
                'error' => form_error('room_type_name'),
                'value' => ''
            );

            echo json_encode($data);
        }
    }

    function edit_room_type(){
        $room_type_id = $this->input->post('room_type_id');
        $data['room_type'] = $this->Room_type_model->get_room_type($room_type_id);
        $data['room_type']['images'] = $this->Image_model->get_images($data['room_type']['image_group_id']);
        if (!is_null($charge_types = $this->Charge_type_model->get_charge_types($this->company_id)))
        {
            $data['charge_types'] = $charge_types;
        }
        $data['rate_plans'] = $this->Rate_plan_model->get_rate_plans_by_room_type_id($room_type_id);

        $data['company_id'] = $this->company_id;
        $this->load->view('hotel_settings/room_inventory_settings/edit_room_type_setting',$data);
    }


    function get_room_types_names_JSON() {
        $this->load->model('Room_type_model');

        if (!is_null($room_types = $this->Room_type_model->get_room_types($this->company_id))) {
            $data = array();

            foreach ($room_types as $room_type) {
                $data[$room_type['id']] = $room_type['name'];
            }
        } else {
            $data = array('message' => 'Failed to get room types. Please contact administrator.');
        }
        echo json_encode($data);
    }

    function get_room_types_JSON() {
        $this->load->model('Room_type_model');

        if (!is_null($room_types = $this->Room_type_model->get_room_types($this->company_id))) {
            $data = $room_types;
        } else {
            $data = array('message' => 'Failed to get room types. Please contact administrator.');
        }
        echo json_encode($data);
    }

    function update_room_type_AJAX() { 
        //temp -- this needs to be changed
        global $unsanitized_post;
        $this->form_validation->set_rules('room_type_id', 'Room Type ID', 'trim');

        $room_type_id = base64_decode($this->security->xss_clean($this->input->post('room_type_id')));
        
        $data = array(
             'name' => base64_decode($this->security->xss_clean($this->input->post('room_type_name'))),
            'acronym' => base64_decode($this->security->xss_clean($this->input->post('acronym'))),
            'max_occupancy' => base64_decode($this->security->xss_clean($this->input->post('max_occupancy'))),
            'min_occupancy' => base64_decode($this->security->xss_clean($this->input->post('min_occupancy'))),
            'max_adults' => base64_decode($this->security->xss_clean($this->input->post('max_adults'))),
            'max_children' => base64_decode($this->security->xss_clean($this->input->post('max_children'))),
            'can_be_sold_online' => $this->security->xss_clean($this->input->post('can_be_sold_online')) ? base64_decode($this->security->xss_clean($this->input->post('can_be_sold_online'))) : 0,
            'default_room_charge' => $this->input->post('default_room_charge') ? base64_decode($this->input->post('default_room_charge')) : null,
            'prevent_inline_booking' => base64_decode($this->input->post('prevent_inline_booking')),
            'description' => isset($unsanitized_post['description']) ? $unsanitized_post['description'] : null
        );
        $file_name = $this->security->xss_clean($this->input->post('file_name'));


        if ($this->form_validation->run() == TRUE) {
            
            $max_occupancy = $this->input->post('max_occupancy') ? base64_decode($this->input->post('max_occupancy')) : 4;
            $min_occupancy = $this->input->post('min_occupancy') ? base64_decode($this->input->post('min_occupancy')) : 4;
            $max_adults = base64_decode($this->input->post('max_adults'));
            $max_children = base64_decode($this->input->post('max_children'));
            $total = $max_adults + $max_children;
            
            if($min_occupancy > $max_occupancy)
            {
                $value = 'An error occured. Number of Min Occupancy should be equal or less than Max Occupancy.';
                $data = array(
                    'error' => 'error',
                    'value' => $value
                );
            }
            else if($max_occupancy && $max_adults > $max_occupancy)
            {
                $value = 'An error occured. Number of adults should be less than Max Occupancy.';
                $data = array(
                    'error' => 'error',
                    'value' => $value
                );
            }
            else if($max_adults < 1 && $min_occupancy)
            {
                $value = 'An error occured. Number of adults should be atleast 1.';
                $data = array(
                    'error' => 'error',
                    'value' => $value
                );
            }
            else if($total < $min_occupancy)
            {
                $value = 'An error occured. Number of guests should be equal or greater than Min Occupancy.';
                $data = array(
                    'error' => 'error',
                    'value' => $value
                );
            }
            else if($max_occupancy && $max_children >= $max_occupancy)
            {
                $value = 'An error occured. Number of childrens should be less than Max Occupancy.';
                $data = array(
                    'error' => 'error',
                    'value' => $value
                );
            }
            else
            {
                //update room type
                if ($this->Room_type_model->update_room_type($room_type_id, $data)) {
                    $this->_create_room_log("Update Room Type ( [ID {$room_type_id}])");
                    $value = 'Save Successful';
                } else {
                    $value = 'An error occured. Please contact adminstrator if it continues.';
                }

                $data = array(
                    'error' => '',
                    'value' => $value
                );
            }

            echo json_encode($data);
        } else { //Validation failed. Add error message
            $data = array(
                'error' => form_error('room_type_name'),
                'value' => ''
            );

            echo json_encode($data);
        }
    }

    function delete_room_type() {
        $this->load->model('Room_type_model');
        $this->load->model('Rate_model');

        $room_type_id = $this->security->xss_clean($this->input->post('room_type_id'));

        if (!$this->Room_type_model->delete_room_type($room_type_id)) {
            $data = array('isSuccess' => FALSE, 'message' => 'Room type delete fail');
            echo json_encode($data);
            return;
        }
        else
        {
            $this->_create_room_log("Delete Room Type ( [ID {$room_type_id}])");
            $data = array('isSuccess' => TRUE, 'message' => 'Room type deleted');
            echo json_encode($data);
        }
    }

    function change_room_type_name() {
        //to do: verify user has permission to change room -- tie to company permission
        $this->form_validation->set_rules('room_type_name', 'Room Type', 'trim|max_length[30]');

        $room_type_id = $this->security->xss_clean($this->input->post('room_type_id'));
        $room_type_name = $this->security->xss_clean($this->input->post('room_type_name'));

        $data = array('room_type' => $room_type_name);

        if ($this->form_validation->run() == TRUE) {
            //update room type
            if ($this->Room_type_model->update_room_type($room_type_id, $data)) {
                $value = $room_type_name;
                $this->_create_room_log("Update Room Type Name ( [ID {$room_type_id}])");
            } else {
                $value = 'An error occured. Please contact adminstrator if it continues.';
            }

            $data = array(
                'error' => '',
                'value' => $value
            );

            echo json_encode($data);
        } else { //Validation failed. Add error message
            $data = array(
                'error' => form_error('acronym'),
                'value' => ''
            );

            echo json_encode($data);
        }
    }

    function change_acronym() {
        //to do: verify user has permission to change room -- tie to company permission
        $this->form_validation->set_rules('acronym', 'Room Type Acronym', 'trim|max_length[6]');

        $room_type_id = $this->security->xss_clean($this->input->post('room_type_id'));
        $acronym = $this->security->xss_clean($this->input->post('acronym'));

        $data = array('acronym' => $acronym);

        if ($this->form_validation->run() == TRUE) {
            //update room type
            if ($this->Room_type_model->update_room_type($room_type_id, $data)) {
                $value = $acronym;
                $this->_create_room_log("Update Room Type Acronym ( [ID {$room_type_id}])");
            } else {
                $value = 'An error occured. Please contact adminstrator if it continues.';
            }

            $data = array(
                'error' => '',
                'value' => $value
            );

            echo json_encode($data);
        } else { //Validation failed. Add error message
            $data = array(
                'error' => form_error('acronym'),
                'value' => ''
            );

            echo json_encode($data);
        }
    }


     function location(){
        $view_data = array();
        if (!is_null($room_types = $this->Room_type_model->get_room_types($this->company_id))) {
            $view_data['room_types'] = $room_types;
        }
        if (!is_null($room_location = $this->Room_location_model->get_room_location($this->company_id))) {
            $view_data['room_location'] = $room_location;
        }
        if (!is_null($rooms = $this->Room_model->get_rooms($this->company_id,'room_name'))) {
            $view_data['rooms'] = $rooms;
        }
        $view_data['js_files'] = array(
            base_url() . auto_version('js/hotel-settings/room-settings.js')
        );
        $view_data['selected_sidebar_link'] = 'Location';
        $view_data['main_content'] = 'hotel_settings/room_inventory_settings/room_location_settings';
        $this->load->view('includes/bootstrapped_template', $view_data);
        
    }
    function create_room_location_AJAX() {
        $data = array (
                    'location_name' => 'New Location',
                    'company_id' => $this->company_id,
                    'is_deleted' => 0,
                );
        $this->Room_location_model->insert($data);
        $this->_create_room_log('New room loaction added');
        $room_location = $this->Room_location_model->get_room_location($this->company_id, '1');
        $view_data['room_location'] = $room_location[0];
        $this->load->view('hotel_settings/room_inventory_settings/new_room_location', $view_data);
    }
    
    function save_room_locations_AJAX() {
        $strRequest = file_get_contents('php://input');
        $roomLocations = json_decode($strRequest, true);
        for($i=0; $i < count($roomLocations); $i++){
            $data = Array();
            $location_id = '';
            $data = Array(
                'location_name' => $roomLocations[$i]['location_name']
            );
            $location_id = $roomLocations[$i]['location_id'];
            $this->Room_location_model->update_location($location_id, $data);
            $this->_create_room_log("Room loaction updated (ID: $location_id)");
        }
        echo 1;
    }
    function delete_room_location_AJAX() {
        $location_id = $this->input->post('location_id');
        $data = array('is_deleted' => 1);
        $this->Room_location_model->update_location($location_id, $data);
        $this->_create_room_log("Room loaction deleted (ID: $location_id)");
        $data = array('isSuccess' => TRUE, 'message' => 'Room Location deleted');
        echo json_encode($data);
    }


    function floor(){
        $view_data = array();
        if (!is_null($room_types = $this->Room_type_model->get_room_types($this->company_id))) {
            $view_data['room_types'] = $room_types;
        }
        if (!is_null($room_location = $this->Floor_model->get_floor($this->company_id))) {
            $view_data['room_location'] = $room_location;
        }
        if (!is_null($rooms = $this->Room_model->get_rooms($this->company_id,'room_name'))) {
            $view_data['rooms'] = $rooms;
        }
        $view_data['js_files'] = array(
            base_url() . auto_version('js/hotel-settings/room-settings.js')
        );
        $view_data['selected_sidebar_link'] = 'Floor';
        $view_data['main_content'] = 'hotel_settings/room_inventory_settings/floor_settings';
        $this->load->view('includes/bootstrapped_template', $view_data);
        
    }


    /*
        ceate floor by AJAX
    */
    function create_floor_AJAX() {
        $data = array (
                    'floor_name' => 'New Floor',
                    'company_id' => $this->company_id,
                    'is_deleted' => 0
                );
        $this->Floor_model->insert($data);
        $this->_create_room_log('New floor added');

        $room_floor = $this->Floor_model->get_floor($this->company_id, 1);
        $view_data['floor'] = $room_floor[0];
        $this->load->view('hotel_settings/room_inventory_settings/new_floor', $view_data);
    }


    /*
        Save floor by AJAX
    */
    function save_floor_AJAX() {
        $strRequest = file_get_contents('php://input');
        $floor = json_decode($strRequest,true);
        for($i=0;$i<count($floor);$i++){
            $data = Array();
            $floor_id = '';
            $data = Array(
                'floor_name' => $floor[$i]['floor_name']
            );
            $floor_id = $floor[$i]['floor_id'];
            $this->Floor_model->update_floor($floor_id,$data);
            $this->_create_room_log("Floor updated (ID: $floor_id)");
        }
        echo 1;
    }


    /*
        Delete floor by AJAX
    */
    function delete_floor_AJAX() {
        $floor_id = $this->input->post('floor_id');
        $data = array('is_deleted' => 1);
        $this->Floor_model->update_floor($floor_id, $data);
        $this->_create_room_log("Floor deleted (ID: $floor_id)");
        $data = array('isSuccess' => TRUE, 'message' => 'Floor deleted');
        echo json_encode($data);
    }


    /*
        Get feature list for view
    */
    function features() {
        $view_data = array();
        $features = $this->Feature_model->get_features($this->company_id);
        $view_data['features'] = $features;
        $view_data['js_files'] = array(
            base_url() . auto_version('js/hotel-settings/feature.js')
        );

        $view_data['selected_sidebar_link'] = 'Features';

        $view_data['main_content'] = 'hotel_settings/room_inventory_settings/features';
        $this->load->view('includes/bootstrapped_template', $view_data);
    }

    /*
        Return new feature creation form in HTML
    */
    function get_new_feature_form_AJAX() {

        $company_info = $this->Company_model->get_company($this->company_id);
        $number_of_rooms = $company_info['number_of_rooms'];
        $features = $this->Feature_model->get_features($this->company_id);
        $actual_used_feature = count($features);

        $room_id = $this->Feature_model->create_feature($this->company_id, 'New');
        $this->_create_room_log('New Room Feature added');
        $view_data['feature'] = $this->Feature_model->get_feature($room_id);

        $this->load->view('hotel_settings/room_inventory_settings/new_feature', $view_data);
    }

    /*
        Save features by AJAX
    */
    function save_features_AJAX() {
        $strRequest = file_get_contents('php://input');
        $features = json_decode($strRequest);
        foreach ($features as $feature) {
            $data = Array(
                'feature_name' => $feature->feature_name,
                'show_on_website' => $feature->show_on_website
            );
            $this->Feature_model->update_feature($feature->feature_id, $data);
            $this->_create_room_log("Room Feature updated (ID: $feature->feature_id)");
        }

        // todo report error
        echo 1;
    }

    /*
        Delete features by AJAX
    */
    function delete_feature_AJAX() {
        $feature_id = $this->input->post('feature_id');

        $data = array('is_deleted' => 1);

        if ($this->Feature_model->update_feature($feature_id, $data)) {
            $this->_create_room_log("Room Feature deleted (ID: $feature_id)");
            $data = array('isSuccess' => TRUE, 'message' => 'Feature deleted');
            echo json_encode($data);
        } else {
            $data = array('isSuccess' => FALSE, 'message' => 'Feature delete fail');
            echo json_encode($data);
        }
    }

    /**

      HOUSEKEEPING SETTINGS

     */
    function housekeeping() {
        $data = array();

        //Load the company's house keeping data
        if (!is_null($housekeeping_settings = $this->Company_model->get_housekeeping_settings($this->company_id))) {
            $data['settings'] = $housekeeping_settings;
            //change the TIME format from MYSQL to simple human legible format (i.e. 03:00:00 to 03:00 AM)
            $timestamp = strtotime($housekeeping_settings->housekeeping_auto_clean_time);
            $data['settings']->hour = date("h", $timestamp);
            $data['settings']->minute = date("i", $timestamp);
            $data['settings']->am_pm = date("A", $timestamp);
        }

        if (!is_null($dirty_room_housekeeping_settings = $this->Company_model->get_housekeeping_settings($this->company_id, true))) {
            $data['dirty_room_settings'] = $dirty_room_housekeeping_settings;
            //change the TIME format from MYSQL to simple human legible format (i.e. 03:00:00 to 03:00 AM)
            $dirty_room_timestamp = strtotime($dirty_room_housekeeping_settings->housekeeping_auto_dirty_time);
            $data['dirty_room_settings']->d_hour = date("h", $dirty_room_timestamp);
            $data['dirty_room_settings']->d_minute = date("i", $dirty_room_timestamp);
            $data['dirty_room_settings']->d_am_pm = date("A", $dirty_room_timestamp);
        }

        $this->form_validation->set_rules('housekeeping_auto_clean_is_enabled', 'Automatically clean all rooms', 'trim');
        $this->form_validation->set_rules('hour', 'Time(hour) of time to clearn all rooms', 'trim|greater_than[0]|less_than[13]');
        $this->form_validation->set_rules('minute', 'Time(hour) of time to clean all rooms', 'trim|greater_than[-1]|less_than[60]');
        $this->form_validation->set_rules('am_pm', 'Time(am/pm) of time to clean all rooms', 'trim|max_length[2]');
        $this->form_validation->set_rules('housekeeping_day_interval_for_full_cleaning', 'Number of days until full cleaning', 'trim');

        if ($this->form_validation->run() == TRUE) {
            $time = $this->input->post('hour') . ":" . $this->input->post('minute') . " " . $this->input->post('am_pm');
            $timestamp = strtotime($time);
            $mysql_time = Date("H:i:00", $timestamp);

            $data['settings']->housekeeping_auto_clean_is_enabled = ($this->input->post('housekeeping_auto_clean_is_enabled') == 'on') ? '1' : '0';
            $data['settings']->housekeeping_day_interval_for_full_cleaning = $this->input->post('housekeeping_day_interval_for_full_cleaning');

            $data['settings']->hour = date("h", $timestamp);
            $data['settings']->minute = date("i", $timestamp);
            $data['settings']->am_pm = date("A", $timestamp);

            $update_data = array(
                'housekeeping_auto_clean_is_enabled' => $data['settings']->housekeeping_auto_clean_is_enabled,
                'housekeeping_auto_clean_time' => $mysql_time,
                'housekeeping_day_interval_for_full_cleaning' => $data['settings']->housekeeping_day_interval_for_full_cleaning
            );

            // if($this->input->post('housekeeping_auto_dirty_is_enabled'))
            // {
                $data['dirty_room_settings']->housekeeping_auto_dirty_is_enabled = ($this->input->post('housekeeping_auto_dirty_is_enabled') == 'on') ? '1' : '0';

                $dirty_room_time = $this->input->post('d_hour') . ":" . $this->input->post('d_minute') . " " . $this->input->post('d_am_pm');
                $dirty_room_timestamp = strtotime($dirty_room_time);
                $dirty_room_mysql_time = Date("H:i:00", $dirty_room_timestamp);

                $data['dirty_room_settings']->d_hour = date("h", $dirty_room_timestamp);
                $data['dirty_room_settings']->d_minute = date("i", $dirty_room_timestamp);
                $data['dirty_room_settings']->d_am_pm = date("A", $dirty_room_timestamp);

                $update_data['housekeeping_auto_dirty_is_enabled'] = $data['dirty_room_settings']->housekeeping_auto_dirty_is_enabled; 
                $update_data['housekeeping_auto_dirty_time'] = $dirty_room_mysql_time; 
                
            // }
            // prx($update_data);
            //TO DO: This should be done in model.
            $this->Company_model->update_company($this->company_id, $update_data);
            $this->_create_room_log("Housekeeping setting updated");

            //Post Redirect Get practice
            redirect('/settings/room_inventory/housekeeping');
        }

        //load view
        $data['company_ID'] = $this->company_id;

        $data['submenu'] = 'hotel_settings/hotel_settings_submenu.php';
        $data['selected_submenu'] = 'room_inventory';

        $data['selected_sidebar_link'] = 'Housekeeping';

        $data['main_content'] = 'hotel_settings/room_inventory_settings/housekeeping_settings';

        //No Post Redirect Get here, because the validation error message must be shown
        $this->load->view('includes/bootstrapped_template', $data);
        return;
    }

     
}
