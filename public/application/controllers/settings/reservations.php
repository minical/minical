<?php 

class Reservations extends MY_Controller
{
    public $module_name;
	function __construct()
	{
        parent::__construct();
        $this->load->library('form_validation');    
        $this->module_name = $this->router->fetch_module();

        $this->load->model('Booking_field_model');
        $this->load->model('Employee_log_model');
        $this->load->model('Booking_source_model');
        $this->load->model('Customer_field_model');
        $this->load->model('Customer_type_model');
        $this->load->model('Room_type_model');
        $this->load->model('Rate_plan_model');
        $this->load->model('Option_model');

        $global_data['menu_on'] = true;
        $view_data['selected_menu'] = 'Settings';
        $view_data['submenu_parent_url'] = base_url()."settings/";

        // $data['js_files'] = array(
        //     base_url() . auto_version('js/hotel-settings/booking-source.js')
        // );      

        // $data['js_files'] = array(
        //     base_url() . auto_version('js/hotel-settings/booking-fields.js')
        // );      

        $this->load->vars($global_data);
	}

    function _create_reservation_log($log) {
        $log_detail =  array(
                    "user_id" => $this->user_id,
                    "selling_date" => $this->selling_date,
                    "date_time" => gmdate('Y-m-d H:i:s'),
                    "log" => $log,
                );   
        
        $this->Employee_log_model->insert_log($log_detail);     
    }



    function booking_source()
    {
            $this->form_validation->set_rules('booking_source', 'booking source', 'required|trim');

            if ($this->form_validation->run() == TRUE)
            {
                    $booking_source = $this->input->post('booking_source');

                    $result = $this->Booking_source_model->create_booking_source($this->company_id, $booking_source);
            }

            $data = array();

            if(!is_null($booking_sources = $this->Booking_source_model->get_booking_source($this->company_id, false, true)))
            {
                $data['booking_sources'] = $booking_sources;
            }
        
            $common_booking_sources = json_decode(COMMON_BOOKING_SOURCES, true);
            $common_sources_setting = $this->Booking_source_model->get_common_booking_sources_settings($this->company_id);
            
            $sort_orders = array();
            $common_sources = array();
            foreach($common_booking_sources as $id => $name)
            {
                if(isset($common_sources_setting[$id])){
                    $common_sources_setting[$id]['sort_order'] = is_numeric($common_sources_setting[$id]['sort_order']) ? $common_sources_setting[$id]['sort_order'] : (count($sort_orders) > 0 ? max($sort_orders) : 0);
                    if(in_array($common_sources_setting[$id]['sort_order'], $sort_orders)){
                        while(in_array($common_sources_setting[$id]['sort_order'], $sort_orders)){
                            $common_sources_setting[$id]['sort_order'] = $common_sources_setting[$id]['sort_order'] + 1;
                        }
                        $new_sort_order = $common_sources_setting[$id]['sort_order'];
                        $sort_orders[] = $new_sort_order;
                    }else{
                        $new_sort_order = $common_sources_setting[$id]['sort_order'];
                        $sort_orders[] = $new_sort_order;
                    }

                    $common_sources[$new_sort_order] = array(
                                                    'id' => $id,
                                                    'name' => $name,
                                                    'company_id' => $this->company_id,
                                                    'is_deleted' => 0,
                                                    'is_common_source' => true,
                                                    'is_hidden' => isset($common_sources_setting[$id]) ? $common_sources_setting[$id]['is_hidden'] : 0,
                                                    'sort_order' => $new_sort_order,
                                                    'commission_rate' => isset($common_sources_setting[$id]) ? $common_sources_setting[$id]['commission_rate'] : null
                                                );
                }else{
                    
                    $new_sort_order = (count($sort_orders) > 0 ? max($sort_orders) : 0) + 1;
                    $sort_orders[] = $new_sort_order;

                    $common_sources[$new_sort_order] = array(
                                                    'id' => $id,
                                                    'name' => $name,
                                                    'company_id' => $this->company_id,
                                                    'is_deleted' => 0,
                                                    'is_common_source' => true,
                                                    'is_hidden' => 0,
                                                    'sort_order' => $new_sort_order,
                                                    'commission_rate' => null
                                                );
                }
                    
            }
            
            $booking_sources = array();
            if(isset($data['booking_sources']) && count($data['booking_sources']) > 0){
                foreach($data['booking_sources'] as $key => $value){
                    $data['booking_sources'][$key]['sort_order'] = isset($data['booking_sources'][$key]['sort_order']) ? 
                            $data['booking_sources'][$key]['sort_order'] : (count($sort_orders) > 0 ? max($sort_orders) : 0);

                    if(in_array($data['booking_sources'][$key]['sort_order'], $sort_orders)){
                        while(in_array($data['booking_sources'][$key]['sort_order'], $sort_orders)){
                            $data['booking_sources'][$key]['sort_order'] = $data['booking_sources'][$key]['sort_order'] + 1;
                        }
                        $new_sort_order = $data['booking_sources'][$key]['sort_order'];
                        $sort_orders[] = $new_sort_order;
                    }else{
                        $new_sort_order = $data['booking_sources'][$key]['sort_order'];
                        $sort_orders[] = $new_sort_order;
                    }
                    $booking_sources[$new_sort_order] = $value;
                }
            }
            
            $data['booking_sources'] = $common_sources + $booking_sources;
            ksort($data['booking_sources']);
          
            $data['js_files'] = array(
                base_url() . auto_version('js/hotel-settings/booking-source.js')
           );
            $data['selected_sidebar_link'] = 'Booking Source';

        $data['main_content'] = 'hotel_settings/booking/booking_source_settings';

        $this->template->load('bootstrapped_template', null , $data['main_content'], $data);
    }
    
    function get_new_booking_source_div() {
        $new_booking_source_id = $this->Booking_source_model->create_booking_source($this->company_id, 'New Booking Source');
        $this->_create_reservation_log("Create New Booking Source ( [ID {$new_booking_source_id}])");
        $data = array (
            'id' => $new_booking_source_id,
            'name' => 'New Booking Source',
            'sort_order' => 0
        );
        

         $this->load->view('hotel_settings/booking/new_booking_source', $data);
        
    }

    function update_booking_sources()
    {
        $updated_booking_sources = $this->input->post('updated_booking_sources');
        $response = array(
            'error' => false,
            'success' => true
        );
        
        
        $common_booking_sources = json_decode(COMMON_BOOKING_SOURCES, true);
        
        foreach($updated_booking_sources as $updated_booking_source)
        {
            $booking_source_id = $this->security->xss_clean($updated_booking_source['id']);
            
            if(isset($common_booking_sources[$booking_source_id]))
            {
                $data = array(
                    'booking_source_id' => $booking_source_id,
                    'company_id' => $this->company_id,
                    'is_hidden' => $this->security->xss_clean($updated_booking_source['is_hidden']),
                    'sort_order' => $this->security->xss_clean($updated_booking_source['sort_order']),
                    'commission_rate' => $this->security->xss_clean($updated_booking_source['commission_rate'])
                );
                $this->Booking_source_model->update_common_booking_sources_settings($this->company_id, $booking_source_id, $data);
                continue;
            }
            
            
            $data = array(
                'name' => $this->security->xss_clean($updated_booking_source['name']),
                'commission_rate' => $this->security->xss_clean($updated_booking_source['commission_rate']),
                'is_hidden' => $this->security->xss_clean($updated_booking_source['is_hidden']),
                'sort_order' => $this->security->xss_clean($updated_booking_source['sort_order'])
            );
            if ($data['name'])
            {
                if (!$this->Booking_source_model->update_booking_source($booking_source_id, $data))
                {
                    $response = array (
                        'error' => 'An error occured. Please contact adminstrator if it continues.',
                        'success' => false
                    );
                    break;
                }
                $this->_create_reservation_log("Update Booking Source ( [ID {$booking_source_id}])");
            }
            else //Validation failed. Add error message
            {   
                $response = array (
                    'error' => 'An error occured with booking source '.form_error('booking-source-name'),
                    'success' => false
                );
                break;
            }
        }
        echo json_encode($response);
    }

    
    function delete_booking_source()
    {
        
        $id = $this->input->post('id');
        $data = array('is_deleted' => 1);
        
        if ($this->Booking_source_model->update_booking_source($id, $data))
        {
            $data = array ('isSuccess'=> TRUE, 'message' => 'Booking Source deleted');
            $this->_create_reservation_log("Delete Booking Source ( [ID {$id}])");
            echo json_encode($data);
        }
        else
        {
            $data = array ('isSuccess'=> FALSE, 'message' => 'Booking Source delete fail');
            echo json_encode($data);
        }       
    }
    


    function booking_fields() {

        $data = array();
        $data['booking_fields'] = array();

        if(!is_null($booking_fields = $this->Booking_field_model->get_booking_fields($this->company_id)))
        {
            $data['booking_fields'] = $booking_fields;
        }

       
        $data['selected_sidebar_link'] = 'Booking Fields';
        $data['js_files'] = array(
            base_url() . auto_version('js/hotel-settings/booking-fields.js')
       );

        $data['main_content'] = 'hotel_settings/booking/custom_booking_field_setting';

        $this->template->load('bootstrapped_template', null , $data['main_content'], $data);
    }

    function get_new_booking_field_div() {

        $new_booking_field_id = $this->Booking_field_model->create_booking_field($this->company_id, 'New Booking Field');
        $this->_create_reservation_log("Create New Booking Field ( [ID {$new_booking_field_id}])");
        $data = array (
            'id' => $new_booking_field_id,
            'name' => 'New Booking Field'
        );
       
         $this->load->view('hotel_settings/booking/new_custom_booking_field', $data);

    }

    function update_booking_field() {

        $updated_booking_fields = $this->input->post('updated_booking_fields');
        $response = array(
            'error' => false,
            'success' => true
        );

        foreach($updated_booking_fields as $updated_booking_field)
        {
            $booking_field_id = $this->security->xss_clean($updated_booking_field['id']);

            $data = array(
                'name' => $this->security->xss_clean($updated_booking_field['name']),
                'show_on_booking_form' => $this->security->xss_clean($updated_booking_field['show_on_booking_form']),
                'show_on_registration_card' => $this->security->xss_clean($updated_booking_field['show_on_registration_card']),
                'show_on_in_house_report' => $this->security->xss_clean($updated_booking_field['show_on_in_house_report']),
                'show_on_invoice' => $this->security->xss_clean($updated_booking_field['show_on_invoice']),
                'is_required' => $this->security->xss_clean($updated_booking_field['is_required'])
            );
            if ($data['name'])
            {
                if (!$this->Booking_field_model->update_booking_field($booking_field_id, $data))
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
                    'error' => 'An error occurred with booking field '.form_error('name'),
                    'success' => false
                );
                break;
            }
        }
        echo json_encode($response);
    }

    function delete_booking_field() {

        $id = $this->input->post('id');
        $data = array('is_deleted' => 1);

        if ($this->Booking_field_model->update_booking_field($id, $data))
        {
            $data = array ('isSuccess'=> TRUE, 'message' => 'booking field deleted');
            $this->_create_reservation_log("Delete Booking Field ( [ID {$id}])");
            echo json_encode($data);
        }
        else
        {
            $data = array ('isSuccess'=> FALSE, 'message' => 'booking field delete failed');
            echo json_encode($data);
        }
    }


    function customer_types()
    {
        $this->form_validation->set_rules('customer_type', 'customer type', 'required|trim');

        if ($this->form_validation->run() == TRUE)
        {
                $customer_type = $this->input->post('customer_type');

                $result = $this->Customer_type_model->create_customer_type($this->company_id, $customer_type);
        }

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

        if($this->is_loyalty_program){
            $data['room_types'] = $this->Room_type_model->get_room_types($this->company_id);
            $data['rate_plans'] = $this->Rate_plan_model->get_rate_plans($this->company_id);
            
            $data['room_rates'] = $this->Option_model->get_data_by_json('loyalty_customer', $this->company_id);
        }
        
        $data['js_files'] = array(
                base_url() . auto_version('js/hotel-settings/customer-types.js')
        );      
   
        $data['selected_sidebar_link'] = 'Customer Types';

        $data['main_content'] = 'hotel_settings/customer/customer_type_settings';

        $this->template->load('bootstrapped_template', null , $data['main_content'], $data);

    }
    
    function get_new_customer_type_div() {
        $new_customer_type_id = $this->Customer_type_model->create_customer_type($this->company_id, 'New Customer Type');
        $this->_create_reservation_log("Create New Customer Type ( [ID {$new_customer_type_id}])");
        $data = array (
            'id' => $new_customer_type_id,
            'name' => 'New Customer Type'
        );
        $this->load->view('hotel_settings/customer/new_customer_type', $data);
        
    }

    // updates a (single) customer type
    function update_customer_type()
    {
        $updated_customer_types = $this->input->post('updated_customer_types');
        $response = array(
            'error' => false,
            'success' => true
        );
        
        $common_customer_types = json_decode(COMMON_CUSTOMER_TYPES, true);
        
        foreach($updated_customer_types as $updated_customer_type)
        {
            $customer_type_id = $this->security->xss_clean($updated_customer_type['id']);
            
            if(isset($common_customer_types[$customer_type_id]))
            {
                $data = array(
                    'customer_type_id' => $customer_type_id,
                    'company_id' => $this->company_id,
                    'sort_order' => $this->security->xss_clean($updated_customer_type['sort_order']),
                );
                $this->Customer_type_model->update_common_customer_type_settings($this->company_id, $customer_type_id, $data);
                continue;
            }
            
            
            $data = array(
                'name' => $this->security->xss_clean($updated_customer_type['name']),
                'sort_order' => $this->security->xss_clean($updated_customer_type['sort_order'])
            );
            if ($data['name'])
            {
                if (!$this->Customer_type_model->update_customer_type($customer_type_id, $data))
                {
                    $response = array (
                        'error' => 'An error occured. Please contact adminstrator if it continues.',
                        'success' => false
                    );
                    break;
                }
                $this->_create_reservation_log("Update Customer Type ( [ID {$customer_type_id}])");
            }
            else //Validation failed. Add error message
            {   
                $response = array (
                    'error' => 'An error occured with customer type '.form_error('customer-type-name'),
                    'success' => false
                );
                break;
            }
        }
        echo json_encode($response);
    }

    
    function delete_customer_type()
    {
        
        $id = $this->input->post('id');
        $data = array('is_deleted' => 1);
        
        if ($this->Customer_type_model->update_customer_type($id, $data))
        {
            $data = array ('isSuccess'=> TRUE, 'message' => 'customer type deleted');
            $this->_create_reservation_log("Delete Customer Type ( [ID {$id}])");
            echo json_encode($data);
        }
        else
        {
            $data = array ('isSuccess'=> FALSE, 'message' => 'customer type delete fail');
            echo json_encode($data);
        }       
    }
    
    /* CUSTOMER FIELDS */

    function customer_fields()
    {
        $data['customer_fields'] = array();
        $this->form_validation->set_rules('customer_field', 'Customer field', 'required|trim');
                
        if ($this->form_validation->run() == TRUE)
        {
            $customer_field = $this->input->post('customer_field');
            $result = $this->Customer_field_model->create_customer_field($this->company_id, $customer_field);
        }
        
        $data = array();
        $data['customer_fields'] = array();
        
        $common_customer_fields = json_decode(COMMON_CUSTOMER_FIELDS, true);
        $get_common_customer_fields = $this->Customer_field_model->get_common_customer_fields_settings($this->company_id);

        foreach($common_customer_fields as $id => $name)
        {
            $data['customer_fields'][] = array(
                'id' => $id,
                'name' => $name,
                'company_id' => $this->company_id,
                'is_common_field'=>true,
                'show_on_customer_form'=> ($get_common_customer_fields && isset($get_common_customer_fields[$id]) && isset($get_common_customer_fields[$id]['show_on_customer_form'])) ? $get_common_customer_fields[$id]['show_on_customer_form'] : 1,
                'show_on_registration_card'=>  ($get_common_customer_fields && isset($get_common_customer_fields[$id]) && isset($get_common_customer_fields[$id]['show_on_registration_card'])) ? $get_common_customer_fields[$id]['show_on_registration_card'] : 0,
                'is_required' => ($id == FIELD_NAME) ? 1 : (($get_common_customer_fields && isset($get_common_customer_fields[$id]) && isset($get_common_customer_fields[$id]['is_required'])) ? $get_common_customer_fields[$id]['is_required'] : 0),
                'show_on_in_house_report' => ($get_common_customer_fields && isset($get_common_customer_fields[$id]) && isset($get_common_customer_fields[$id]['show_on_in_house_report'])) ? $get_common_customer_fields[$id]['show_on_in_house_report'] : 0,
                'show_on_invoice' => ($get_common_customer_fields && isset($get_common_customer_fields[$id]) && isset($get_common_customer_fields[$id]['show_on_invoice'])) ? $get_common_customer_fields[$id]['show_on_invoice'] : 0,
            );
        }
        //get customer_type     
        if(!is_null($customer_fields = $this->Customer_field_model->get_customer_fields($this->company_id)))
        {
            $data['customer_fields'] = array_merge($data['customer_fields'], $customer_fields);
        }
        
        $data['js_files'] = array(
             base_url() . auto_version('js/hotel-settings/customer-fields.js')
        );
        
        $data['selected_sidebar_link'] = 'Customer Fields';
        $data['main_content'] = 'hotel_settings/customer/customer_field_settings';
        $this->template->load('bootstrapped_template', null , $data['main_content'], $data);
        
    }
    
    function get_new_customer_field_div() {
        $new_customer_field_id = $this->Customer_field_model->create_customer_field($this->company_id, 'New Customer Field');
        $this->_create_reservation_log("Cretae New Customer Field ( [ID {$new_customer_field_id}])");
        $data = array (
            'id' => $new_customer_field_id,
            'name' => 'New Customer Field'
        );
        $this->load->view('hotel_settings/customer/new_customer_field', $data);

        // $this->load->view('hotel_settings/reservation_settings/new_customer_field', $data);
    }
    
    function update_customer_field()
    {
        $updated_customer_fields = $this->input->post('updated_customer_fields');
        $response = array(
            'error' => false,
            'success' => true
        );
        
        $common_customer_fields = json_decode(COMMON_CUSTOMER_FIELDS, true);
        
        foreach($updated_customer_fields as $updated_customer_field)
        {
            $customer_field_id = $this->security->xss_clean($updated_customer_field['id']);
            
            if(isset($common_customer_fields[$customer_field_id]))
            {
                $data = array(
                    'customer_field_id' => $customer_field_id,
                    'company_id' => $this->company_id,
                    'show_on_customer_form' => $this->security->xss_clean($updated_customer_field['show_on_customer_form']),
                    'show_on_registration_card' => $this->security->xss_clean($updated_customer_field['show_on_registration_card']),
                    'show_on_in_house_report' => $this->security->xss_clean($updated_customer_field['show_on_in_house_report']),
                    'show_on_invoice' => $this->security->xss_clean($updated_customer_field['show_on_invoice']),
                    'is_deleted' => 0,
                    'is_required' => $this->security->xss_clean($updated_customer_field['is_required'])
                );
                $this->Customer_field_model->update_common_customer_fields_settings($this->company_id, $customer_field_id, $data);
                continue;
            }
            
            $data = array(
                'name' => $this->security->xss_clean($updated_customer_field['name']),
                'show_on_customer_form' => $this->security->xss_clean($updated_customer_field['show_on_customer_form']),
                'show_on_registration_card' => $this->security->xss_clean($updated_customer_field['show_on_registration_card']),
                'show_on_in_house_report' => $this->security->xss_clean($updated_customer_field['show_on_in_house_report']),
                'show_on_invoice' => $this->security->xss_clean($updated_customer_field['show_on_invoice']),
                'is_required' => $this->security->xss_clean($updated_customer_field['is_required'])
            );
            if ($data['name'])
            {
                if (!$this->Customer_field_model->update_customer_field($customer_field_id, $data))
                {
                    $response = array (
                        'error' => 'An error occured. Please contact adminstrator if it continues.',
                        'success' => false
                    );
                    break;
                }
                $this->_create_reservation_log("Update Customer Field ( [ID {$customer_field_id}])");
            }
            else //Validation failed. Add error message
            {   
                $response = array (
                    'error' => 'An error occured with customer field '.form_error('name'),
                    'success' => false
                );
                break;
            }
        }
        echo json_encode($response);
    }
    
    function delete_customer_field()
    {
        
        $id = $this->input->post('id');
        $data = array('is_deleted' => 1);
        
        if ($this->Customer_field_model->update_customer_field($id, $data))
        {
            $data = array ('isSuccess'=> TRUE, 'message' => 'customer field deleted');
            $this->_create_reservation_log("Delete Customer Field ( [ID {$id}])");
            echo json_encode($data);
        }
        else
        {
            $data = array ('isSuccess'=> FALSE, 'message' => 'customer field delete fail');
            echo json_encode($data);
        }       
    }


    function unconfirmed_reservations() {
        $view_data['company_data'] = $this->Company_model->get_company($this->company_id);
        $view_data['js_files'] = array(base_url() . auto_version('js/hotel-settings/online-settings.js'));
        $view_data['selected_sidebar_link'] = 'Unconfirmed Reservations';
        $view_data['main_content'] = 'hotel_settings/channel_manager/unconfirmed_reservations';
        $this->load->view('includes/bootstrapped_template', $view_data);
    }



}

    