<?php

class Company_subscription_model extends CI_Model {
	
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
	
	function get_subscription_id($company_id) {
		$selling_date = null;
		$this->db->select("subscription_id");
		$this->db->where('company_id', $company_id);

		$q = $this->db->get("company_subscription");
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
		
		$subscription_id = null;

		foreach ($q->result() as $row) {        
			$subscription_id = $row->subscription_id;
		}		
		//echo $this->db->last_query();
		return $subscription_id;
	}

    function get_company_subscription($company_id = null, $payment_method = null, $is_full_data = false)
    {
        $this->db->select('`company_id`, `subscription_id`, `subscription_type`, `subscription_state`, `renewal_period`,`tax`,`cost_amount`, `renewal_cost`, `balance`, `region`, `payment_method`, `expiration_date`, `transition_after_expired`, `chargify_subscription_link`, `invoice_link`, `subscription_level`, `limit_feature`');
        $this->db->from('company_subscription');
		if($company_id)
			$this->db->where('company_id', $company_id);
		if($payment_method)
			$this->db->where('payment_method', $payment_method);

        $query = $this->db->get();

        if ($this->db->_error_message()) // error checking
        {   
            show_error($this->db->_error_message());
        }

        if ($query->num_rows() >= 1) {
            $result = $query->result_array();
            $result = !$is_full_data ? $result[0] : $result;
        } else {
            $this->db->insert('company_subscription', compact('company_id'));
            $result = $this->get_company_subscription($company_id);
        }

        return $result;
    }

    function update_company_balance($company_id)
    {
        $this->load->model('Company_charge_model');
        $this->load->model('Company_payment_model');
        $charge_total  = $this->Company_charge_model->get_company_charge_total($company_id);
        $payment_total = $this->Company_payment_model->get_company_payment_total($company_id);
        $balance       = $charge_total - $payment_total;
        $data          = array("balance" => $balance);
        $this->update_company_subscription($company_id, $data);
    }
	
	function update_company_subscription($company_id, $data)
	{
		$data = (object) $data;

		$this->db->where('company_id', $company_id);
		$this->db->update('company_subscription', $data);

		//echo $this->db->last_query();

		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

        return TRUE;
	}

    function insert_or_update_company_subscription($company_id, $data)
    {

        $company = $this->get_company_subscription($company_id);

        if(isset($data['api_key'])) { // junk
            unset($data['api_key']);
        }

        // prevent clearing up id
        if(isset($data['subscription_id']) and empty($data['subscription_id'])){
            unset($data['subscription_id']);
        }

        if($company){
            // update
            if(isset($data['company_id'])){ //conflicts
                unset($data['company_id']);
            }
            $this->update_company_subscription($company_id,$data);
        }else{
            // insert
            $this->db->insert('company_subscription', $data);
        }

    }
}
?>