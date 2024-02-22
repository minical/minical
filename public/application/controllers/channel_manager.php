<?php
class Channel_manager extends MY_Controller
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
		
		$this->load->model('Customer_model');
		$this->load->model('Extra_model');
		$this->load->model('Booking_room_history_model');
		$this->load->model('Room_type_model');
		$this->load->model('Room_model');
		$this->load->model('Charge_type_model');
		$this->load->model('Rate_plan_model');
		

		$this->load->library('pagination');
		$this->load->library('permission');
		$this->load->library('rate');
		$this->load->library('PHPRequests');		

		$this->load->helper('timezone');
	
		$language = $this->session->userdata('language');
		$this->lang->load('booking', $language);
		
	} 
                                  
	function update_availabilities()
	{

		$start_date                = $this->input->post('start_date');
        $end_date                  = $this->input->post('end_date');

        $channel_id                = $this->input->post('channel_id');
        $room_type_id              = $this->input->post('room_type_id');
        
		$company_id = $this->input->post('company_id');
		$company_id = $company_id ? $company_id : $this->company_id;

		$data = array(
						'start_date' => $start_date,
						'end_date' => $end_date,
						'channel_id' => $channel_id,
						'room_type_id' => $room_type_id,
						'company_id' => $company_id,
						'update_from' => 'inventory'
					);
        
        do_action('update_availability', $data);      
        do_action('update_siteminder_availability', $data);     
        
        // echo $req; // debugging    
        
        // // if response empty aka no availability, ensure actual array is created anyway
        
        // echo json_decode($req, true);
	
	}

        
	function update_rates()
	{
		$start_date                = date('Y-m-d',strtotime($this->input->post('start_date')));
        $end_date                  = date('Y-m-d',strtotime($this->input->post('end_date') . " + 1 day"));
        $rate_plan_id              = $this->input->post('rate_plan_id');
        $company_id                = $this->company_id;
        
        if(!empty($start_date) && $start_date!='' && !empty($end_date) && $end_date!='')
        {
            $req = file_get_contents(
			$this->config->item('cm_url').'/sync/update_rates/'.$company_id.'/'.$start_date.'/'.$end_date.'/'.$rate_plan_id
				);  
        }
        else
        {
            $req = file_get_contents(
			$this->config->item('cm_url').'/sync/update_company/'.$company_id.'/'."update_rates_only"
				);
        }
		
		echo $req; // debugging

        // if response empty aka no availability, ensure actual array is created anyway
        echo json_decode($req, true);
	
	}

	function update_pricing_model()
	{
		$booking_dot_com_rate_type = $this->input->post('booking_dot_com_rate_type');
        $company_id                = $this->company_id;
        
        if(!empty($booking_dot_com_rate_type) && $booking_dot_com_rate_type!='')
        {
            $req = file_get_contents(
			$this->config->item('cm_url').'/sync/update_pricing_model/'.$company_id.'/'.$booking_dot_com_rate_type);  
        }
		
		echo $req; // debugging

        // if response empty aka no availability, ensure actual array is created anyway
        echo json_decode($req, true);
	
	}  
    
    function update_full_refresh()
	{   
        $company_id = $this->company_id;
        $req = file_get_contents(
                $this->config->item('cm_url').'/sync/update_company/'.$company_id
            );
        echo $req; // debugging    
        echo json_decode($req, true);
	
	}

}
