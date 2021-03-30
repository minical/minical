<?php

class Rate_plan extends MY_Controller {

	function __construct()
	{
        parent::__construct();
		$this->load->library('form_validation');			
				
		$view_data['menu_on'] = true;
		$this->load->vars($view_data);
	}
	
	/**
	* @param bunch of POST data
	* @return JSON Array
	*
		both variables are used in booking_form.
		default_rate indicates default rate (i.e. 69.00), default_rate_array is an array of rate information (i.e. {[2011-01-01, 59, christmas season], [...],....}
	*
	*/
	function get_rate_array_JSON()
	{
		$rate_plan_id = $this->security->xss_clean($this->input->post('rate_plan_id'));
		$date_start = $this->security->xss_clean($this->input->post('date_start'));
		$date_end = $this->security->xss_clean($this->input->post('date_end'));
		$adult_count = $this->security->xss_clean($this->input->post('adult_count'));
		$children_count = $this->security->xss_clean($this->input->post('children_count'));
		
		$this->load->library('rate');
		$rate_array = $this->rate->get_rate_array($rate_plan_id, $date_start, $date_end, $adult_count, $children_count);

		echo json_encode($rate_array);
	}

	function get_tax_amount_from_room_charge_JSON() {
		$charge_type_id = $this->input->post('charge_type_id');
		$rate = $this->input->post('rate');
		$tax = $this->_get_tax_amount_from_charge_type_id($charge_type_id, null, $rate);
		echo json_encode($tax);
	}
	
	function get_tax_amount_from_rate_plan_JSON() {
		$rate_plan_id = $this->security->xss_clean($this->input->post('rate_plan_id'));
		$rate = $this->input->post('rate');
		$this->load->model('Rate_plan_model');
		$rate_plan = $this->Rate_plan_model->get_rate_plan($rate_plan_id);
		$tax = $this->_get_tax_amount_from_charge_type_id($rate_plan['charge_type_id'], $rate_plan['company_id'], $rate);
		echo json_encode($tax);
	}

	/**
	 * @return array
	 */

	function _get_tax_amount_from_charge_type_id($charge_type_id, $company_id = null, $rate = null)
	{
		$this->load->model('Tax_model');
		$tax = array();
		$tax['percentage'] = 0.01 * $this->Tax_model->get_total_tax_percentage_by_charge_type_id($charge_type_id, $company_id, $rate);
		$tax['inclusive_tax_percentage'] = 0.01 * $this->Tax_model->get_total_tax_percentage_by_charge_type_id($charge_type_id, $company_id, $rate, $is_tax_inclusive = true);
		$tax['flat_rate'] = $this->Tax_model->get_total_tax_flat_rate_by_charge_type_id($charge_type_id, $company_id, $rate);
		$tax['inclusive_tax_flat_rate'] = $this->Tax_model->get_total_tax_flat_rate_by_charge_type_id($charge_type_id, $company_id, $rate, $is_tax_inclusive = true);
		$tax['charge_type_id'] = $charge_type_id;
		return $tax;
	}
	
	function get_parent_rate_plan_id()
	{
		$this->load->model('Rate_plan_model');
		$rate_plan_id = $this->input->post('rate_plan_id');
		$rate_plan = $this->Rate_plan_model->get_rate_plan($rate_plan_id);
		echo json_encode(array("success" => true, 'id' => $rate_plan['parent_rate_plan_id']));

	}
	
}
