<?php
class Menu extends MY_Controller
{
	function __construct()
	{
		parent::__construct();

		$this->load->model('User_model');
		$this->load->model('Company_model');
		$this->load->model('Employee_log_model');
        $this->load->model('Whitelabel_partner_model');
        $this->load->library('night_audit');
		
    }
	
	function select_hotel($company_id = null)
	{
		// if company isn't set, then set company_id as the first company that user has access to
		if (!isset($company_id))
		{
			$companies = $this->Company_model->get_companies($this->user_id);
			foreach ($companies as $key => $value) {
				$company_id = $value['company_id'];
				break;
			}
		}
                
                 
		//Verify user has permission to look at this company.
		if (!is_null($user_role = $this->User_model->get_user_role($this->user_id, $company_id)))
		{
                      
                        // Set last login date in company.
                     /*   if($user_role!='is_admin')
                        {
                            $updatedata = array('last_login' => date('Y-m-d'));
                            $this->Company_model->update_company($company_id, $updatedata);                                  }
                            */
                     
			//Store the latest hotel selected into the database so the next time the user logs in
			//they can continue using the same hotel
			$data = array('current_company_id' => $company_id);
			$this->User_model->update_user_profile($this->user_id, $data);
			
			$company = $this->Company_model->get_company($company_id);
			$data = array (
				'current_company_id' => $company_id,
				'current_selling_date' => $company['selling_date'],
				'is_paying_customer' => $this->Company_model->has_tag($company_id, "PAYING_CUSTOMER"),
				'user_role' => $user_role,
				'property_type' => $company['property_type']
			);
            $this->session->unset_userdata('permissions');
            $this->session->unset_userdata('current_company_id');
			$this->session->set_userdata($data);
            $employee_permission['permissions'] = $this->Employee_log_model->get_user_permission($this->session->userdata('current_company_id'), $this->session->userdata('user_id'));
			$this->session->set_userdata($employee_permission);
		}
		
        $partner = $this->Whitelabel_partner_model->get_partner_detail($company['partner_id']);
        if(
                isset($partner['partner_slug']) && 
                $partner['partner_slug'] == "channel_manager" &&
                in_array($this->user_id, $partner['admins'])
        ) {
			redirect('/room/inventory/');
        } 
		elseif($user_role=="is_housekeeping")	
		{
			redirect('/room');
		}
	 	else 
		{			
            redirect('/booking/');
        }
	}
	
	
	function run_night_audit()
	{
		$this->_create_employee_log("Manually ran Night Audit.");
		echo $this->night_audit->run_night_audit($this->company_id);
	}
	
	// returns the date which would be the resulting date after running night audit.
	// so, if current date is 2013-02-18, then this function will return 2013-02-19
	function get_night_audit_resulting_date() 
	{
		$company = $this->Company_model->get_company($this->company_id);
		
		//if processing multiple days at once is enabled in Night Audit Settings,
		//Display actual date time, otherwise, display current selling date's next day
		if (
			$company['night_audit_multiple_days'] &&
			strtotime($this->selling_date) < strtotime($actual_date)
		)
		{
			echo json_encode($actual_date);
		}
		else
		{
			$next_day = strtotime("+1 day", strtotime($this->selling_date));
			echo json_encode(Date("Y-M-d", $next_day));
		}
	}

	function _create_employee_log($log) {
        $log_detail =  array(
                    "user_id" => $this->user_id,
                    "selling_date" => $this->selling_date,
                    "date_time" => gmdate('Y-m-d H:i:s'),
                    "log" => $log,
                );  
        $this->Employee_log_model->insert_log($log_detail);     
    }
	
	//Loaded for employees when they try to access settings they don't have permission to
	function settings_unavailable()
	{
		//Load view
		$data['menu_on'] = TRUE;
		$data['main_content'] = 'hotel_settings/settings_unavailable';
		$this->load->view('includes/bootstrapped_template', $data);
	}
	

	//Loaded when company doesn't the feature included in their subscription package
	function feature_unavailable()
	{
		//Load view
		$data['menu_on'] = TRUE;
		$data['main_content'] = 'includes/feature_unavailable';
		$this->load->view('includes/bootstrapped_template', $data);
	}

	function automatic_alert() {
        $data = array();
    	do_action('post.set_automatic_reminder',$data);

    	$limit = 1000;
        $this->delete_ota_xml_logs($limit);
	}

	function delete_ota_xml_logs($limit = 1000){
        $this->load->model('Channex_model');

        $ota_xml_logs = $this->Channex_model->get_ota_xml_logs($limit);

        $xml_log_ids = array();

        if($ota_xml_logs){
            // foreach($ota_xml_logs as $log){

            //     $timestamp = strtotime($log['datetime']); //1373673600

            //     // getting current date 
            //     $cDate = strtotime(date('Y-m-d H:i:s'));

            //     // Getting the value of old date + 15 days
            //     $old_booking_retrieval_log_date = $timestamp + (86400 * 15);

            //     // Getting the value of old date + 3 days
            //     $old_other_log_date = $timestamp + (86400 * 3); // 86400 seconds in 24 hrs 

            //     // for delete booking retrieval logs
            //     if($old_booking_retrieval_log_date < $cDate && $log['request_type'] == 2)
            //     {
            //         $xml_log_ids[] = $log['xml_log_id'];
            //     }

            //     // for delete other logs
            //     if($old_other_log_date < $cDate && $log['request_type'] != 2)
            //     {
            //         $xml_log_ids[] = $log['xml_log_id'];
            //     }
            // }

            foreach($ota_xml_logs as $log) {

                $timestamp = strtotime($log['datetime']); //1373673600

                // getting current date 
                $cDate = strtotime(date('Y-m-d H:i:s'));

                // Getting the value of old date + 7 days
                $old_blank_response_log_date = $timestamp + (86400 * 7);

                // Getting the value of old date + 2 month
                $old_booking_retrieval_log_date = $timestamp + (86400 * 60); // 86400 seconds in 24 hrs 

                // for delete blank response logs
                if(
                	$old_blank_response_log_date < $cDate && 
                	$log['request_type'] != 2  && 
                	strpos($log['xml_out'], '"data":[]')
                )
                {
                    $xml_log_ids[] = $log['xml_log_id'];
                }

                // for delete booking retrieval logs
                if(
                	$old_booking_retrieval_log_date < $cDate && 
                	$log['request_type'] == 2 && 
                	strpos($log['xml_out'], '"data":[]')
                )
                {
                    $xml_log_ids[] = $log['xml_log_id'];
                }
            }

            // prx($xml_log_ids);

            $this->Channex_model->delete_old_xml_logs($xml_log_ids);
        }
    }
}
