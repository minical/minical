<?php 
class Extensions extends MY_Controller
{
	function __construct()
	{
        parent::__construct();

		$this->load->model('Extension_model');

        $view_data['menu_on']          = true;
       

        $this->load->vars($view_data);
	}

	function index(){
		$this->get_all_extensions();
	}
	
	// Called from booking_form.js
	function get_all_extensions()
	{
		$all_active_modules = $this->session->userdata('all_active_modules');

        if($this->is_super_admin != 1){
            foreach ($all_active_modules as $key => $value) {
                if(isset($value['is_admin_module']) && $value['is_admin_module']){
                    unset($all_active_modules[$key]);
                }
            }
        }

		$modules_name = array();
		foreach($all_active_modules as $module)
        {
        	$modules_name[] = $module['extension_folder_name'];
        }

        $extensions = $this->Extension_model->get_extensions($modules_name, $this->company_id);

		$final_modules = array();

		foreach($all_active_modules as $key => $module)
        {
        	$flag = true;
        	if($extensions){
	        	foreach ($extensions as $key1 => $extension) {
	        		if($module['extension_folder_name'] == $extension['extension_name'])
	        		{
	        			$extension['description'] = $module['description'];
                        $extension['extension_folder_name'] = $module['extension_folder_name'];
                        $extension['extension_name'] = $module['name'];
	        			$final_modules[] = $extension;
	        			$flag = false;
	        		}
	        	}
	        }
        	if($flag){
    			$module['is_active'] = 0;
    			$module['company_id'] = $this->company_id;
    			$module['extension_name'] = $module['name'];
    			unset($module['name']);
    			unset($module['is_default_active']);
    			$final_modules[] = $module;
        	}
	    }

        $data['extensions'] = $final_modules ? $final_modules : array();

        $activated_modules = array();
        if(count($data['extensions']) > 0){
        	foreach($data['extensions'] as $ext){
        		if($ext['is_active'] == 1){
        			$activated_modules[] = $ext['extension_folder_name'];
        		}
        	}
        }

        $this->session->set_userdata('activated_modules', $activated_modules);
        
        $data['js_files'] = array(
            base_url() . auto_version('js/hotel-settings/extension-settings.js'),
        );

        $data['selected_sidebar_link'] = 'Extensions';
        $data['main_content'] = 'hotel_settings/extension_settings/show_extensions';
        $this->load->view('includes/bootstrapped_template', $data);
	}

	function change_extension_status()
	{
		$data['extension_name'] = $this->input->post('extension_name');
		$extension_status = $this->input->post('extension_status');

		$data['is_active'] = $extension_status == 1 ? 0 : 1;
		$data['company_id'] = $this->company_id;

		$this->Extension_model->update_extension($data);
		echo json_encode(array('success' => true));
	}
	
}