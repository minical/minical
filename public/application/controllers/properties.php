<?php

class Properties extends MY_Controller {
	
	function __construct()
	{
		parent::__construct();		
		
		$this->load->model('Company_model');
		$this->load->model('User_model');			
		
		$this->load->library('form_validation');
		
		$view_data['selected_menu'] = 'my properties';		
		$view_data['menu_on'] = true;
		
		$this->load->vars($view_data);
    }
	
	function index()
	{		
		$this->my_properties();
	}
		
	function my_properties()
	{		
		$data['current_selected_property_id'] = $this->company_id;
		$data['properties'] = $this->Company_model->get_companies($this->user_id);
		
		$data['main_content'] = 'properties/my_properties';
		$data['js_files']     = array(
									base_url().auto_version('js/my_properties.js')
								);
        
		$this->load->view('includes/bootstrapped_template',$data);
	}


}

