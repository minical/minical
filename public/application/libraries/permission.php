<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Permission {

	/**
	 * Create a new instance
	 */
	function __construct()
	{
        $this->ci =& get_instance();
        $this->ci->load->model('Permission_model');
	}


	public function check_access_to_function($user_id, $company_id, $controller_name, $function_name)
	{
		// If user doesn't have access to Class & Function combination, or to company, show forbidden
		return $this->ci->Permission_model->has_access_to_function(
										$user_id,
										$company_id, 
										$controller_name,
										$function_name
									);
	}

	public function check_access_to_company_id($user_id, $company_id)
	{
		if ($this->ci->Permission_model->has_access_to_company_id($user_id, $company_id)) 
		{
			return true;
		}
		else
			return false;
	}

	public function check_access_to_customer_id($user_id, $customer_id)
	{
		if (!$this->ci->Permission_model->has_access_to_customer_id($user_id, $customer_id)) 
		{
			show_404();
		}	
	}


	public function check_access_to_customer_type_id($user_id, $customer_type_id)
	{
		if (!$this->ci->Permission_model->has_access_to_customer_type_id($user_id, $customer_type_id)) 
		{
			show_404();
		}	
	}

	public function check_access_to_customer_field_id($user_id, $customer_field_id)
	{
		if (!$this->ci->Permission_model->has_access_to_customer_field_id($user_id, $customer_field_id)) 
		{
			show_404();
		}	
	}

	public function check_access_to_booking_id($user_id, $booking_id)
	{
		if (!$this->ci->Permission_model->has_access_to_booking_id($user_id, $booking_id)) 
		{
			show_404();
		}	
	}

	public function is_function_public($controller_name, $function_name)
	{
		if ($this->ci->Permission_model->is_public($controller_name, $function_name))
			return true;
		else
			return false;
	}

    public function is_route_public($route_name)
    {
        if ($this->ci->Permission_model->is_route_public($route_name))
            return true;
        else
            return false;
    }

	public function is_extension_active($extension_name, $company_id, $is_array = false)
	{
		if ($modules = $this->ci->Permission_model->is_extension_active($extension_name, $company_id, $is_array)) {
			if($is_array) {
				return $modules;
			} else {
				return true;
			}
		}
		else {
			if($is_array){
	            return null;
	        } else {
	            return false;
	        }
		}
	}
		
}