<?php

class Account_settings extends MY_Controller {
	
	function __construct()
	{
		parent::__construct();		
		
        $auth_required = true;
        
        if(
            $this->router->method == 'chargify_signup'
            OR $this->router->method == 'chargify_update'
        ){
            $auth_required = false;
        }
		
		$this->load->model('Company_model');
		$this->load->model('Company_subscription_model');
		$this->load->model('User_model');
		$this->load->model('Option_model');
        $this->load->model('Company_security_model');
                // Load Translation Model for Language Translation
		$this->load->model('translation_model');
                
		$this->load->library('form_validation');
                // Load language Translation Helper
        $this->load->helper('language_translation');


        $this->load->library('google_security');


		
		$view_data['menu_on'] = true;
		$view_data['selected_menu'] = 'my account';		
		$view_data['submenu'] = 'account_settings/account_settings_submenu.php';
		
		$this->load->vars($view_data);
    }
	
	function index()
	{		
		$this->password();
	}
	
	function password()
	{
		$old_password = $this->input->post('old_password');
		$new_password = $this->input->post('new_password');
		$confirm_new_password = $this->input->post('confirm_new_password');
		
		// updating the password happens here while checking for _incorrect_old_password
		$this->form_validation->set_rules('old_password', 'Old Password', 'trim|xss_clean');
		$this->form_validation->set_rules('new_password', 'New Password', 'trim|required|callback_password_check');
		$this->form_validation->set_rules('confirm_new_password', 'Confirm new Password', 'required|trim|xss_clean|matches[new_password]');

		if($new_password != $confirm_new_password){
			echo '<div class="alert alert-danger alert-dismissible" role="alert" style="
              position:fixed; 
              z-index:1000; 
              top:10%; 
              left:50%;
              width: 70%;
              margin-left: -35%;
              ">
            	<button type="button" class="close" data-dismiss="alert" aria-label="Close" fdprocessedid="krme3c"><span aria-hidden="true">×</span></button>
              <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
              <span class="sr-only">Error:</span>
              <strong>Please Correct The Below Error(s):</strong>
                        <p>The Confirm Password does not match the New Password.</p>
          	</div>';

          // return FALSE;
		}

		if ($this->form_validation->run()) // validation ok
		{
			// this validation actually changes password
			$this->form_validation->set_rules('old_password', 'Old Password', 'callback__incorrect_old_password['.$new_password.']');

			if ($this->form_validation->run()){
				echo "<script>alert('successfully updated password!');</script>";
				
				$userdata = $this->User_model->get_user_profile($this->user_id);
	            $this->User_model->delete_all_user_sessions($userdata['email']);
			}
		}

		$data['selected_submenu'] = 'password';
		$data['main_content'] = 'account_settings/password';
		
		$this->load->view('includes/bootstrapped_template',$data);
	}

	public function password_check($password)
	{
	    if (preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{6,20}$/', $password)) {
	        return TRUE;
	    } else {

	    	echo '<div class="alert alert-danger alert-dismissible" role="alert" style="
              position:fixed; 
              z-index:1000; 
              top:10%; 
              left:50%;
              width: 70%;
              margin-left: -35%;
              ">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close" fdprocessedid="krme3c"><span aria-hidden="true">×</span></button>
              <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
              <span class="sr-only">Error:</span>
              <strong>Please Correct The Below Error(s):</strong>
                        <p>The new password must contain at least one uppercase letter, one lowercase letter, one number, one special character, and be between 6 and 20 characters long.</p>
          </div>';

	        // $this->form_validation->set_message('password_check', 'The new password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.');
	        return FALSE;
	    }
	}

	/**
	 * Updates the password. 
	 * If the old password is incorrectly entered, then returns form validation error
	 *
	 * @access	public
	 * @param	string, string (from custom Form validation)
	 * @return	boolean
	 */
	function _incorrect_old_password($old_password, $new_password)
	{
		if ($this->tank_auth->change_password($old_password, $new_password)) 
		{
			return true;
		}
		else // update password failed
		{		
			echo '<div class="alert alert-danger alert-dismissible" role="alert" style="
              position:fixed; 
              z-index:1000; 
              top:10%; 
              left:50%;
              width: 70%;
              margin-left: -35%;
              ">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close" fdprocessedid="krme3c"><span aria-hidden="true">×</span></button>
              <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
              <span class="sr-only">Error:</span>
              <strong>Please Correct The Below Error(s):</strong>
                        <p>Incorrect old password</p>
          </div>';
			// $this->form_validation->set_message('_incorrect_old_password', 'Incorrect old password');
			return false;
		}
	}

	function language() {
		$this->form_validation->set_rules('language', 'Language', 'required');
		if ($this->form_validation->run()) 
		{
			// update language settings in database
			$explode = explode(',', $this->input->post('language'));
                        $language_id = $explode[0];
                        $new_language = $explode[1];
                        $this->User_model->update_user_profile($this->user_id, Array(
                            'language' => $new_language,
                            'language_id' => $language_id
                        ));
				
			// apply language change immediately by updating session variable
			$this->session->set_userdata(array( 'language' => $new_language ));
            $this->session->set_userdata(array( 'language_id' => $language_id ));
            // Call function to load translation of language
            load_translations($language_id);
            
            if($this->input->is_ajax_request())
            {
                echo l('success',true);
                return;
            }
            else
            {
                redirect('/account_settings/language');
            }
		}
		
		$data['selected_submenu'] = 'language';
		$data['current_language'] = $this->session->userdata('language');
		$data['main_content'] = 'account_settings/language';
		
		$this->load->view('includes/bootstrapped_template',$data);
		
	}

	function change_language() {
		// update language settings in database
		$explode = explode(',', $this->input->post('language'));
        $language_id = $explode[0];
        $new_language = $explode[1];
        $this->User_model->update_user_profile($this->user_id, Array(
            'language' => $new_language,
            'language_id' => $language_id
        ));
				
		// apply language change immediately by updating session variable
		$this->session->set_userdata(array( 'language' => $new_language ));
        $this->session->set_userdata(array( 'language_id' => $language_id ));
        // Call function to load translation of language
        load_translations($language_id);
        echo l('success',true);
	}

	function company_security()
    {
        $security_data =  $this->Option_model->get_option_by_company('company_security',$this->company_id);
        
        $data['company_security'] = isset($security_data[0]['option_value']) ? json_decode($security_data[0]['option_value'],true) :"";

        $data['security_data'] = $this->Company_security_model->get_deatils_by_company_user(null, $this->user_id);

        $email = $this->user_email;
        $security_name = 'security';

        $data['secure_data'] = $this->google_security->create_secret($email, $security_name);
        $data['security_name'] = $security_name;

        $company_id = $this->company_id;

		$data['main_content'] = 'account_settings/company_security';		
        $this->load->view('includes/bootstrapped_template', $data);

    }
}

