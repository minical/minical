<?php

class User extends MY_Controller 
{    
	
	function __construct()
	{
        parent::__construct();

		$this->load->model('User_model');
		$this->load->model('User_role_model');
		$this->load->model('Employee_log_model');
	}

	function _create_user_log($log) {
        $log_detail =  array(
                    "user_id" => $this->user_id,
                    "selling_date" => $this->selling_date,
                    "date_time" => gmdate('Y-m-d H:i:s'),
                    "log" => $log,
                );   
        
        $this->Employee_log_model->insert_log($log_detail);     
    }
	
	function agreed_to_tos_AJAX()
	{
        $this->session->unset_userdata('is_registration_page');
		$tos_agreed_date = $this->User_model->get_tos_agreed_date($this->user_id);
		if ($tos_agreed_date < TOS_PUBLISH_DATE)
		{
			echo json_encode(false);
		}
		else
		{
			echo json_encode(true);
		}
		
	}

	function change_permission() 
	{
		$user_id = $this->input->post('user_id');		
		$permission = $this->input->post('permission');		
		$is_checked = $this->input->post('is_checked');	
        
        $user_detail = $this->User_model->get_user_profile($user_id);
		if ($is_checked =="true")
		{
            if($permission == 'bookings_view_only') 
            {
                $this->User_model->remove_other_permissions($this->company_id, $user_id, $permission);
            }
			$this->User_model->add_user_permission($this->company_id, $user_id, $permission);
		}
		else
		{
			$this->User_model->remove_user_permission($this->company_id, $user_id, $permission);			
		}
		$this->_create_user_log("Change <b>$permission</b> permission for user '{$user_detail['first_name']}'");
		//TO DO: error checking
		$data = array (
			'message' => 'User permission changed'
		);
		
		echo json_encode($data);
	}

	function change_role_permission() 
	{
		$role_id = $this->input->post('role_id');		
		$permission = $this->input->post('permission');		
		$is_checked = $this->input->post('is_checked');	
        
		$all_assigned_user = $this->User_role_model->get_users_by_role($role_id, $this->company_id);

		if($all_assigned_user) {
	        $role_detail = $this->User_role_model->get_role_detail($role_id);
			if ($is_checked =="true")
			{
	            if($permission == 'bookings_view_only') 
	            {
	                $this->User_role_model->remove_other_permissions($this->company_id, $role_id, $permission);
	            }

	            if($all_assigned_user) {
		            foreach ($all_assigned_user as $key => $value) {
		            	$this->User_role_model->add_user_permission($this->company_id, $value['user_id'], $permission, $role_id);
		            }
		        }
			}
			else
			{
				$this->User_role_model->remove_user_permission($this->company_id, $role_id, $permission);			
			}
			$this->_create_user_log("Change <b>$permission</b> permission for user '{$role_detail['role']}'");
			//TO DO: error checking
			$data = array (
				'success' => true,
				'message' => 'Role permission changed'
			);
		} else {
			$data = array (
				'success' => false,
				'message' => l('Firstly assign any user with this role, then provide permissions', true)
			);
		}
		
		echo json_encode($data);
	}

	function accept_terms_of_service() {
		$tos_agreed_date = $this->User_model->set_tos_agreed_date($this->user_id, Date("Y-m-d"));
		echo json_encode($tos_agreed_date);
	}
	
//	function update_user_main_view()
//	{
//		if($this->input->post('view') == 'overview_calendar')
//		{
//			$data = array('is_overview_calendar' => 1);
//		}
//		else if($this->input->post('view') == 'calendar')
//		{
//			$data = array('is_overview_calendar' => 0);
//		}
//		$this->User_model->update_user($this->user_id, $data);
//		echo json_encode(array('success' => true));
//	}
}