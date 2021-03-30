<?php 
class Api_access extends MY_Controller
{
	function __construct()
    {

        parent::__construct();
        $this->load->model('Company_model');

        $view_data['menu_on'] = true;

        $view_data['selected_menu']    = 'Settings';
        $view_data['selected_submenu'] = 'Api Access';

        $view_data['submenu'] = 'hotel_settings/hotel_settings_submenu.php';

        $view_data['submenu_parent_url'] = base_url()."settings/";
        $view_data['sidebar_menu_url'] = base_url()."settings/company/";

        $view_data['menu_items'] = $this->Menu_model->get_menus(array('parent_id' => 5, 'wp_id' => 1));
        $view_data['sidebar_links'] = $this->Menu_model->get_menus(array('parent_id' => 29, 'wp_id' => 1));

        $this->load->vars($view_data);
    }

	function index(){
		$this->api_access();
	}
	
	// Called from booking_form.js
	function api_access()
	{
        $view_data['company_data'] = $this->Company_model->get_company($this->company_id);
        
        $view_data['js_files'] = array(
            base_url() . 'js/hotel-settings/email-settings.js',
            base_url() . auto_version('js/hotel-settings/online-settings.js')
        );

        $view_data['main_content'] = 'hotel_settings/company/api_access';
        $this->load->view('includes/bootstrapped_template', $view_data);
	}
}