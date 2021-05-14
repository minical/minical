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
                // Load Translation Model for Language Translation
		$this->load->model('translation_model');
                
		$this->load->library('form_validation');
                // Load language Translation Helper
                $this->load->helper('language_translation');
		
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
		
		// updating the password happens here while checking for _incorrect_old_password
		$this->form_validation->set_rules('old_password', 'Old Password', 'trim|xss_clean');
		$this->form_validation->set_rules('new_password', 'New Password', 'required|trim|xss_clean');
		$this->form_validation->set_rules('confirm_new_password', 'Confirm new Password', 'required|trim|xss_clean|matches[new_password]');

		// this validation actually changes password
		$this->form_validation->set_rules('old_password', 'Old Password', 'callback__incorrect_old_password['.$new_password.']');

		if ($this->form_validation->run()) // validation ok
		{
			echo "<script>alert('successfully updated password!');</script>";
		}

		$data['selected_submenu'] = 'password';
		$data['main_content'] = 'account_settings/password';
		
		$this->load->view('includes/bootstrapped_template',$data);
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
			$this->form_validation->set_message('_incorrect_old_password', 'Incorrect old password');
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
}

