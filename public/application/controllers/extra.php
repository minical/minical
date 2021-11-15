<?php 
class Extra extends MY_Controller
{
	function __construct()
	{
        parent::__construct();

		$this->load->model('Extra_model');
		$this->load->model('Booking_extra_model');
		$this->load->model('Rate_model');
        $this->load->model('Booking_log_model');
        $this->load->model('Booking_model');
	}
	
	// Called from booking_form.js
	function get_extra_JSON()
	{
		$extra_id = $this->security->xss_clean($this->input->post('extra_id'));
		
		if(!is_null($extra = $this->Extra_model->get_extra($extra_id)))
		{
			echo json_encode($extra);
		}
	}

	// Called from booking_form.js
	function get_all_extras_JSON()
	{
//		if(!is_null())
//		{
//			//echo json_encode($extras);
//		}
        $extras = $this->Extra_model->get_extras($this->company_id);
        $extras = $extras ? $extras : array();
        echo json_encode($extras);
		}
	
	// Called from booking_form.js
	function get_default_extra_rate_JSON()
	{
		$extra_id = $this->security->xss_clean($this->input->post('extra_id'));		
		$default_rate = $this->Rate_model->get_default_extra_rate($extra_id);		
		echo json_encode($default_rate);
	}

	function create_booking_extra_AJAX()
	{
		$booking_id = $this->input->post('booking_id');
		$extra_data = $this->input->post('extra_data');
		
		$extra_id = $extra_data['extra_id'];
		$start_date = $extra_data['start_date'];
		$end_date = $extra_data['end_date'];
		$quantity = $extra_data['quantity'];
		
		// get default rate
		$extra_default_rate = $this->Extra_model->get_extra_default_rate($extra_id);
		
		$booking_extra_id = $this->Booking_extra_model->create_booking_extra(
														$booking_id, 
														$extra_id, 
														$start_date, 
														$end_date, 
														$quantity, 
														$extra_default_rate
													);
        if($booking_extra_id) {
            $log = Array(
                "booking_id" => $booking_id,
                "date_time" => gmdate('Y-m-d H:i:s'),
                "log_type" => 24,
                "log" => $booking_extra_id,
                "user_id" => $this->user_id,
                "selling_date" => $this->selling_date
            );
            $this->Booking_log_model->insert_log($log);
        }

        $balance = $this->Booking_model->update_booking_balance($booking_id);

		echo json_encode(array('booking_extra_id' => $booking_extra_id, 'balance' => $balance));
	}

	function get_booking_extra_AJAX()
	{
		$booking_extra_id = $this->input->post('booking_extra_id');
		$booking_extra = $this->Booking_extra_model->get_booking_extra($booking_extra_id);
		echo json_encode($booking_extra);
	}

	function update_booking_extra_AJAX()
	{
		$booking_id = $this->input->post('booking_id');
		$booking_extra_id = $this->input->post('booking_extra_id');
		$booking_extra_data = $this->input->post('booking_extra_data');
		$this->Booking_extra_model->update_booking_extra($booking_extra_id, $booking_extra_data);
		$balance = $this->Booking_model->update_booking_balance($booking_id);
		echo json_encode(array('status' => "success!", 'balance' => $balance));
	}
}