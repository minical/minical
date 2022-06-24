<?php

class Partners extends MY_Controller {
	
	function __construct()
	{
		parent::__construct();		
		
		$this->load->model('Whitelabel_partner_model');			
		
		$view_data['menu_on'] = true;
		
		$this->load->vars($view_data);
    }
	
	function index()
	{		
		$this->show_partners();
	}
		
	function show_partners()
	{		
		$data['partners'] = $this->Whitelabel_partner_model->get_partners_detail($this->vendor_id);
		
		$data['main_content'] = 'partners/show_partners';
		$data['js_files']     = array(
									base_url().auto_version('js/partners.js')
								);
        
		$this->load->view('includes/bootstrapped_template',$data);
	}

	function get_partners_details()
	{		
		$partners['company_name'] = $this->company_name;
		$partners['property_manage'] = 1;
		$partners['name'] = $this->first_name.' '.$this->last_name;
		$partners['email'] = $this->user_email;
		$partners['location'] = $this->company_data['country'];

		echo json_encode(array('success' => true, 'partners' => $partners));
		
	}

	function send_email_to_partner(){
		$this->load->library('email_template');
		$data = $_POST;
		$data['company_id'] = $this->company_id;
        $result_array = $this->email_template->send_email_to_partner($data);
        
        echo json_encode($result_array);
	}


}

