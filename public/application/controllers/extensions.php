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
		$all_active_modules = $this->all_active_modules;

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
                        $extension['image_name'] = isset($module['image_name'])? $module['image_name']:"";
                        $extension['setting_link']= isset($module['setting_link'])?$module['setting_link']:"";
						$extension['view_link']= isset($module['view_link'])?$module['view_link']:"";
                        $extension['marketplace_product_link']= isset($module['marketplace_product_link'])?$module['marketplace_product_link']:"";
						$extension['is_vendor_module'] = isset($module['is_vendor_module']) && $module['is_vendor_module'] ?true : false;
						$extension['is_admin_module'] = isset($module['is_admin_module']) && $module['is_admin_module'] ?true : false;

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

        $keys = array_column($final_modules, 'extension_name');

        array_multisort($keys, SORT_ASC, $final_modules);
    
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
	
    function get_category_extension(){

        $category = $this->input->post('extension_category');
        $all_active_modules = $this->all_active_modules;

        $status = 1;
        $active_extension = $this->Extension_model->get_filter_extension($status,$this->company_id);

        $active_extension_name = array();
        foreach ($active_extension as $key => $value) {
            $active_extension_name[] = $value['extension_name'];
        }
        $active_extension_name;

        $final_modules = array();
        foreach ($all_active_modules as $key => $module) {
            if(isset($module['categories']) && $module['categories'] ){
                if(in_array($category,$module['categories'])){
                    $extension = array();
                    $extension['description'] = $module['description'];
                    $extension['extension_folder_name'] = $module['extension_folder_name'];
                    $extension['extension_name'] = $module['name'];
                    $extension['is_active'] = in_array($module['extension_folder_name'],$active_extension_name) ? 1: 0;
                    $extension['image_name'] = isset($module['image_name'])? $module['image_name']:"";
                    $extension['setting_link']= isset($module['setting_link'])?$module['setting_link']:"";
                    $extension['view_link']= isset($module['view_link'])?$module['view_link']:"";
                    $extension['marketplace_product_link']= isset($module['marketplace_product_link'])?$module['marketplace_product_link']:"";
                    $extension['is_vendor_module'] = isset($module['is_vendor_module']) && $module['is_vendor_module'] ?true : false;
                    $extension['is_admin_module'] = isset($module['is_admin_module']) && $module['is_admin_module'] ?true : false;

                    $final_modules[] = $extension;
                }
            }
        }

        $keys = array_column($final_modules, 'extension_name');
        array_multisort($keys, SORT_ASC, $final_modules);

        $data['extensions'] = $final_modules ? $final_modules : array();

        $activated_modules = array();
        if(count($data['extensions']) > 0){
            foreach($data['extensions'] as $ext){
                if($ext['is_active'] == 1){
                    $activated_modules[] = $ext['extension_folder_name'];
                }
            }
        }

        $data['selected_sidebar_link'] = 'Extensions';
        $this->load->view('hotel_settings/extension_settings/extension_view', $data);
    }

    function search_extension(){
        $search = $this->input->post('item');
        $all_active_modules = $this->all_active_modules;
        $modules_name = array();
        if($search != ""){
            foreach($all_active_modules as $module)
            {
                $name = ($module['name']);
                if(stripos($name,$search) !== false){
                    $modules_name[] = $module['name'];

                }
            }
        } else {
            foreach ($all_active_modules as $key => $value) {
                $modules_name[] = $value['name'];
            }
        }

        $status = 1;
        $active_extension = $this->Extension_model->get_filter_extension($status,$this->company_id);

        $active_extension_name = array();
        foreach ($active_extension as $key => $value) {
            $active_extension_name[] = $value['extension_name'];
        }

        $fv_status = 1;
        $fv_extension = $this->Extension_model->get_favourite_extension($fv_status,$this->company_id);

        $fv_extension_name = array();
        foreach ($fv_extension as $key => $value) {
            $fv_extension_name[] = $value['extension_name'];
        }

        $final_modules = array();
        foreach ($all_active_modules as $key => $module) {
            if($modules_name){
                foreach ($modules_name as $key => $name) {
                    if($name == $module['name']){
                        $extension = array();
                        $extension['description'] = $module['description'];
                        $extension['extension_folder_name'] = $module['extension_folder_name'];
                        $extension['extension_name'] = $module['name'];
                        $extension['is_active'] = in_array($module['extension_folder_name'],$active_extension_name) ? 1: 0;
                        $extension['is_favourite'] = in_array($module['extension_folder_name'],$fv_extension_name) ? 1: 0;
                        $extension['image_name'] = isset($module['image_name'])? $module['image_name']:"";
                        $extension['setting_link']= isset($module['setting_link'])?$module['setting_link']:"";
                        $extension['view_link']= isset($module['view_link'])?$module['view_link']:"";
                        $extension['marketplace_product_link']= isset($module['marketplace_product_link'])?$module['marketplace_product_link']:"";
                        $extension['is_vendor_module'] = isset($module['is_vendor_module']) && $module['is_vendor_module'] ?true : false;
                        $extension['is_admin_module'] = isset($module['is_admin_module']) && $module['is_admin_module'] ?true : false;

                        $final_modules[] = $extension;
                    }
                }
            }
        }

        $keys = array_column($final_modules, 'extension_name');
        array_multisort($keys, SORT_ASC, $final_modules);

        $data['extensions'] = $final_modules ? $final_modules : array();

        $activated_modules = array();
        if(count($data['extensions']) > 0){
            foreach($data['extensions'] as $ext){
                if($ext['is_active'] == 1){
                    $activated_modules[] = $ext['extension_folder_name'];
                }
            }
        }

        $data['selected_sidebar_link'] = 'Extensions';
        $this->load->view('hotel_settings/extension_settings/extension_view', $data);
    }

    function get_filter_extension(){

        $extension_status = $this->input->post('extension_status');
        $all_active_modules = $this->all_active_modules;

        if($this->is_super_admin != 1){
            foreach ($all_active_modules as $key => $value) {
                if(isset($value['is_admin_module']) && $value['is_admin_module']){
                    unset($all_active_modules[$key]);
                }
            }
        }

        $status = 1;
        $active_extension = $this->Extension_model->get_filter_extension($status,$this->company_id);

        if($extension_status == "active"){
            $active_extension_name = array();
            foreach ($active_extension as $key => $value) {
                $active_extension_name[] = $value['extension_name'];
            }
            $new_array = array_values($active_extension_name);
        } else {
            $active_extension_name = array();
            foreach ($active_extension as $key => $value) {
                $active_extension_name[] = $value['extension_name'];
            }

            $module_name = array();
            foreach ($all_active_modules as $key => $value) {
                $module_name[] = $value['extension_folder_name'];
            }

            $new_array = array_diff($module_name,$active_extension_name);

            $new_array = array_values($new_array);
        }

        foreach($all_active_modules as $key => $module){

            if(in_array($module['extension_folder_name'],$new_array)){
                $extension['description'] = $module['description'];
                $extension['extension_folder_name'] = $module['extension_folder_name'];
                $extension['extension_name'] = $module['name'];
                $extension['image_name'] = isset($module['image_name'])? $module['image_name']:"";
                $extension['setting_link']= isset($module['setting_link'])?$module['setting_link']:"";
                $extension['view_link']= isset($module['view_link'])?$module['view_link']:"";
                $extension['marketplace_product_link']= isset($module['marketplace_product_link'])?$module['marketplace_product_link']:"";
                $extension['is_vendor_module'] = isset($module['is_vendor_module']) && $module['is_vendor_module'] ?true : false;
                $extension['is_admin_module'] = isset($module['is_admin_module']) && $module['is_admin_module'] ?true : false;
                $extension['is_active'] = $extension_status == "active" ? 1 : 0;

                $final_modules[] = $extension;
            }
        }

        $keys = array_column($final_modules, 'extension_name');
        array_multisort($keys, SORT_ASC, $final_modules);

        $data['extensions'] = $final_modules ? $final_modules : array();

        $activated_modules = array();
        if(count($data['extensions']) > 0){
            foreach($data['extensions'] as $ext){
                if($ext['is_active'] == 1){
                    $activated_modules[] = $ext['extension_folder_name'];
                }
            }
        }

        $data['selected_sidebar_link'] = 'Extensions';
        $this->load->view('hotel_settings/extension_settings/extension_view', $data);
    }

    function change_favourite_status(){
        $data['extension_name'] = $this->input->post('extension_name');
        $extension_status = $this->input->post('extension_status');
        $data['is_favourite'] = $extension_status;
        $data['company_id'] = $this->company_id;

        $extension = $this->Extension_model->get_extensions($data['extension_name'],$this->company_id);

        if(empty($extension)){
            $data['is_active'] = 0;
            $this->Extension_model->add_extension($data);

        } else {
            $this->Extension_model->update_extension($data);
        }

        echo json_encode(array('success' => true));
    }
}