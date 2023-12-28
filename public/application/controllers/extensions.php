<?php 
class Extensions extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('Extension_model');
        $this->load->model('User_model');
        $this->load->helper('includes/extension');
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
        if($this->user_email !='support@minical.io'){
            foreach ($all_active_modules as $key => $value) {
                 if(
                    isset($value['is_super_admin_module']) &&
                    $value['is_super_admin_module']
                ){
                    unset($all_active_modules[$key]);
                }   
            }
        }

        $modules_name = array();

        $installed_extensions = $this->Extension_model->get_installed_extensions(null, $this->vendor_id);

        if($installed_extensions && count($installed_extensions) > 0) {
            foreach($installed_extensions as $module)
            {
                $modules_name[] = $module['extension_name'];
            }
        }

        $company_id = $this->company_id;

        $extensions = $this->Extension_model->get_extensions($modules_name, $company_id);

        // echo $this->user_id; echo '<br/>';
        // echo $company_id; echo '<br/>';
        // echo $this->vendor_id ? $this->vendor_id : 0; echo '<br/>';
        // echo 'extensions = '; prx($extensions, 1);
        // echo 'installed_extensions = '; prx($installed_extensions);

        $temp_ext = $temp_extension = array();

        $i = 0;
        $is_hosted_prod_service = getenv('IS_HOSTED_PROD_SERVICE');

        if($is_hosted_prod_service || $_SERVER['HTTP_HOST'] == "app.minical.io" || $_SERVER['HTTP_HOST'] == "demo.minical.io"){

            if($extensions && count($extensions) > 0) {
                foreach ($extensions as $e => $ext) {
                    if($installed_extensions && count($extensions) == count($installed_extensions)){
                        foreach ($installed_extensions as $ie => $in_ext) {
                            if($ext['extension_name'] == $in_ext['extension_name'])
                            {
                                $temp_ext[$ie] = $in_ext;
                                $temp_ext[$ie]['is_active'] = $ext['is_active'];
                                $temp_ext[$ie]['is_favourite'] = $ext['is_favourite'];
                            } 
                        } 
                    } else { 
                        if($installed_extensions && count($installed_extensions) > 0) {
                            foreach ($installed_extensions as $ie => $in_ext) {
                                if($ext['extension_name'] == $in_ext['extension_name'])
                                {
                                    $temp_ext[$ie] = $in_ext;
                                    $temp_ext[$ie]['is_active'] = $ext['is_active'];
                                    $temp_ext[$ie]['is_favourite'] = $ext['is_favourite'];
                                } 
                                else {
                                    $temp_ext[$ie] = $in_ext;
                                    $temp_ext[$ie]['is_active'] = 0;
                                    $temp_ext[$ie]['is_favourite'] = 0;
                                }

                            }
                        }
                            
                        // if($temp_ext)
                            $extensions = $temp_ext;
                    }
                }
            } else {
                if($installed_extensions && count($installed_extensions) > 0) {
                    foreach ($installed_extensions as $ie => $in_ext) {
                        $temp_ext[$ie] = $in_ext;
                        $temp_ext[$ie]['is_active'] = 0;
                        $temp_ext[$ie]['is_favourite'] = 0;
                    }
                }
            }

            if($temp_ext)
                $extensions = $temp_ext;
        }

        $final_modules = array();

        foreach($all_active_modules as $key => $module)
        {
            $flag = true;
            if($extensions && count($extensions) > 0) {
                foreach ($extensions as $key1 => $extension) {
                    if($module['extension_folder_name'] == $extension['extension_name'])
                    {
                        $extension['description'] = $module['description'];
                        $extension['extension_folder_name'] = $module['extension_folder_name'];
                        $extension['extension_name'] = $module['name'];

                        if(isset($module['image_name']) && $module['image_name']) {
                            $extension['image_name'] = $module['image_name']; 
                        } elseif(isset($module['logo']) && $module['logo']) {
                            $extension['logo'] = $module['logo']; 
                        } else {
                            $extension['image_name'] = "";
                            $extension['logo'] = "";
                        }

                        $extension['setting_link']= isset($module['setting_link'])?$module['setting_link']:"";
                        $extension['view_link']= isset($module['view_link'])?$module['view_link']:"";
                        $extension['marketplace_product_link']= isset($module['marketplace_product_link'])?$module['marketplace_product_link']:"";
                        $extension['is_vendor_module'] = isset($module['is_vendor_module']) && $module['is_vendor_module'] ? true : false;
                        $extension['is_admin_module'] = isset($module['is_admin_module']) && $module['is_admin_module'] ? true : false;
                        $extension['supported_in_minimal'] = isset($module['supported_in_minimal']) && $module['supported_in_minimal'] ? true : false;

                        $final_modules[] = $extension;
                        $flag = false;
                    }
                }
            }
            $is_hosted_prod_service = getenv('IS_HOSTED_PROD_SERVICE');

            $white_label_info = $this->session->userdata('white_label_information');

            if($is_hosted_prod_service && $_SERVER['HTTP_HOST'] != $white_label_info['domain'] && $_SERVER['HTTP_HOST'] != "app.minical.io" && $_SERVER['HTTP_HOST'] != "demo.minical.io"){
                if($flag){
                    $module['is_active'] = 0;
                    $module['company_id'] = $this->company_id;
                    $module['extension_name'] = $module['name'];
                    unset($module['name']);
                    unset($module['is_default_active']);
                    $final_modules[] = $module;
                }
            }
        }

        $keys = array_column($final_modules, 'extension_name');

        array_multisort($keys, SORT_ASC, $final_modules);
    
        $data['extensions'] = $final_modules ? $final_modules : array();

        $activated_modules = array();
        if(isset($data['extensions']) && count($data['extensions']) > 0){
            foreach($data['extensions'] as $ext){
                if($ext['is_active'] == 1){
                    $activated_modules[] = $ext['extension_folder_name'];
                }
            }
        }

        $this->session->set_userdata('activated_modules', $activated_modules);

        $data['is_vendor'] = $this->Whitelabel_partner_model->get_whitelabel_admin_ids($this->user_id);

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
        $data['vendor_id'] = $this->company_partner_id ? $this->company_partner_id : 0;

        if($data['is_active']== 1) {
            extension_activated_log($data);
          
        } else {
            extension_deactivated_log($data);
             
        }
        echo json_encode(array('success' => true));
    }
    
    function get_category_extension(){

        $category = $this->input->post('extension_category');
        $all_active_modules = $this->all_active_modules;

        $status = 1;
        $active_extension = $this->Extension_model->get_filter_extension($status,$this->company_id);

        $installed_extensions = $this->Extension_model->get_installed_extensions(null, $this->vendor_id);

        $active_extension_name = $modules_name = array();

        if($installed_extensions && count($installed_extensions) > 0) {
            foreach ($installed_extensions as $key => $value) {
                $modules_name[] = $value['extension_name'];
            }
        }

        if($active_extension && count($active_extension) > 0) {
            foreach ($active_extension as $key => $value) {
                $active_extension_name[] = $value['extension_name'];
            }
        }
        $active_extension_name;

        $final_modules = array();
        foreach ($all_active_modules as $key => $module) {
            if(isset($module['categories']) && $module['categories'] ){
                if(in_array($category,$module['categories']) && in_array($module['extension_folder_name'], $modules_name)){
                    $extension = array();
                    $extension['description'] = $module['description'];
                    $extension['extension_folder_name'] = $module['extension_folder_name'];
                    $extension['extension_name'] = $module['name'];
                    $extension['is_active'] = in_array($module['extension_folder_name'],$active_extension_name) ? 1: 0;
                    
                    if(isset($module['image_name']) && $module['image_name']) {
                        $extension['image_name'] = $module['image_name']; 
                    } elseif(isset($module['logo']) && $module['logo']) {
                        $extension['logo'] = $module['logo']; 
                    } else {
                        $extension['image_name'] = "";
                        $extension['logo'] = "";
                    }

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
        if(isset($data['extensions']) && count($data['extensions']) > 0){
            foreach($data['extensions'] as $ext){
                if($ext['is_active'] == 1){
                    $activated_modules[] = $ext['extension_folder_name'];
                }
            }
        }

        if($this->is_super_admin != 1){
            foreach ($data['extensions'] as $key => $value) {
                if(isset($value['is_admin_module']) && $value['is_admin_module']){
                    unset($data['extensions'][$key]);
                }
            }
        }

        $data['selected_sidebar_link'] = 'Extensions';
        $this->load->view('hotel_settings/extension_settings/extension_view', $data);
    }

    function search_extension(){
        $search = $this->input->post('item');
        $all_active_modules = $this->all_active_modules;

        $installed_extensions = $this->Extension_model->get_installed_extensions(null, $this->vendor_id);

        $modules_name = array();
        if($search != ""){
            if($installed_extensions && count($installed_extensions) > 0) {
                foreach($installed_extensions as $module)
                {
                    $name = ($module['extension_name']);
                    if(stripos($name,$search) !== false){
                        $modules_name[] = $module['extension_name'];

                    }
                }
            }
        } else {
            if($installed_extensions && count($installed_extensions) > 0) {
                foreach ($installed_extensions as $key => $value) {
                    $modules_name[] = $value['extension_name'];
                }
            }
        }

        $status = 1;
        $active_extension = $this->Extension_model->get_filter_extension($status,$this->company_id);

        $active_extension_name = array();
        if($active_extension && count($active_extension) > 0) {
            foreach ($active_extension as $key => $value) {
                $active_extension_name[] = $value['extension_name'];
            }
        }

        $fv_status = 1;
        $fv_extension = $this->Extension_model->get_favourite_extension($fv_status,$this->company_id);

        $fv_extension_name = array();
        if($fv_extension && count($fv_extension) > 0) {
            foreach ($fv_extension as $key => $value) {
                $fv_extension_name[] = $value['extension_name'];
            }
        }

        $final_modules = array();
        foreach ($all_active_modules as $key => $module) {
            if($modules_name){
                foreach ($modules_name as $key => $name) {
                    if($name == $module['extension_folder_name']){
                        $extension = array();
                        $extension['description'] = $module['description'];
                        $extension['extension_folder_name'] = $module['extension_folder_name'];
                        $extension['extension_name'] = $module['name'];
                        $extension['is_active'] = in_array($module['extension_folder_name'],$active_extension_name) ? 1: 0;
                        $extension['is_favourite'] = in_array($module['extension_folder_name'],$fv_extension_name) ? 1: 0;
                        
                        if(isset($module['image_name']) && $module['image_name']) {
                            $extension['image_name'] = $module['image_name']; 
                        } elseif(isset($module['logo']) && $module['logo']) {
                            $extension['logo'] = $module['logo']; 
                        } else {
                            $extension['image_name'] = "";
                            $extension['logo'] = "";
                        }

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
        if(isset($data['extensions']) && count($data['extensions']) > 0){
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

        if($this->user_email !='support@minical.io'){
            foreach ($all_active_modules as $key => $value) {
                 if(
                    isset($value['is_super_admin_module']) &&
                    $value['is_super_admin_module']
                ){
                    unset($all_active_modules[$key]);
                }   
            }
        }
        $status = 1;
        $active_extension = $this->Extension_model->get_filter_extension($status,$this->company_id);

        if($extension_status == "active"){
            $active_extension_name = array();

            if($active_extension && count($active_extension) > 0) {
                foreach ($active_extension as $key => $value) {
                    $active_extension_name[] = $value['extension_name'];
                }
            }
            
            $new_array = array_values($active_extension_name);
        } else {
            $active_extension_name = array();

            if($active_extension && count($active_extension) > 0) {
                foreach ($active_extension as $key => $value) {
                    $active_extension_name[] = $value['extension_name'];
                }
            }

            $module_name = array();
            foreach ($all_active_modules as $key => $value) {
                $module_name[] = $value['extension_folder_name'];
            }

            $new_array = array_diff($module_name,$active_extension_name);

            $new_array = array_values($new_array);
        }

        $installed_extensions = $this->Extension_model->get_installed_extensions(null, $this->vendor_id);

        $modules_name = $final_modules = array();

        if($installed_extensions && count($installed_extensions) > 0) {
            foreach ($installed_extensions as $key => $value) {
                if(in_array($value['extension_name'], $new_array))
                {
                    $modules_name[] = $value['extension_name'];
                }
            }
        }

        $new_array = $modules_name;

        foreach($all_active_modules as $key => $module){

            if($new_array && in_array($module['extension_folder_name'], $new_array)){
                $extension['description'] = $module['description'];
                $extension['extension_folder_name'] = $module['extension_folder_name'];
                $extension['extension_name'] = $module['name'];
                
                if(isset($module['image_name']) && $module['image_name']) {
                    $extension['image_name'] = $module['image_name']; 
                } elseif(isset($module['logo']) && $module['logo']) {
                    $extension['logo'] = $module['logo']; 
                } else {
                    $extension['image_name'] = "";
                    $extension['logo'] = "";
                }
                        
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
        if(isset($data['extensions']) && count($data['extensions']) > 0){
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

    function uninstall_extension()
    {
        $data['extension_name'] = $this->input->post('extension_name');
        $data['is_installed'] = 0;
        $data['company_id'] = $this->company_id;
        $data['vendor_id'] = $this->company_partner_id;

        $get_vendors_companies = $this->Company_model->get_partner_company_data($data['vendor_id']);
        $vendor_companies = $company_ids = array();

        if($get_vendors_companies && count($get_vendors_companies) > 0){
            foreach ($get_vendors_companies as $key => $value) {
                $vendor_companies[] = $value['company_id'];
            }
        }

        $data['vendor_companies'] = $vendor_companies;
        $activated_module_data = $this->Extension_model->update_extension_status($data);

        if(empty($activated_module_data)){
            echo json_encode(array('success' => true));
        } else {
            foreach($activated_module_data as $key => $module){
                $company_ids[] = $module['company_id'];
            }

            $company_data = $this->Company_model->get_company_data($company_ids);
            echo json_encode(array('success' => $company_data));
        }
    }

    function uninstall_extension_process()
    {
        $data['extension_name'] = $this->input->post('extension_name');
        $data['is_installed'] = 0;
        $data['company_id'] = $this->company_id;
        $data['vendor_id'] = $this->company_partner_id;

        
        $status = $this->Extension_model->update_extension_status($data);

        extension_uninstall_log($data);
        
        if($status){
            echo json_encode(array('success' => true));
        }
    }

    function install_extension()
    {
        $data['extension_name'] = $this->input->post('extension_name');
        $data['is_installed'] = 1;
        $data['company_id'] = $this->company_id;
        $data['vendor_id'] = $this->company_partner_id;
        
        $this->Extension_model->update_extension_status($data);

        extension_install_log($data);

        echo json_encode(array('success' => true));
    }

    function show_vendors_extensions(){

        $this->load->model('Admin_model');
        $company_data = $this->Admin_model->get_single_company_admin_panel_info($this->company_id);
        $data['company_data'] = $company_data;
        
        $all_active_modules = $this->all_active_modules;

        if($this->is_super_admin != 1){
            foreach ($all_active_modules as $key => $value) {
                if(isset($value['is_admin_module']) && $value['is_admin_module']){
                    unset($all_active_modules[$key]);
                }
            }
        }

        if($this->user_email !='support@minical.io'){
            foreach ($all_active_modules as $key => $value) {
                 if(
                    isset($value['is_super_admin_module']) &&
                    $value['is_super_admin_module']
                ){
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

        $installed_extensions = $this->Extension_model->get_installed_extensions(null, $this->vendor_id);


        $final_modules = array();

        foreach($all_active_modules as $key => $module)
        {
            $flag = true;
            if(isset($installed_extensions) && $installed_extensions && count($installed_extensions) > 0){
                foreach ($installed_extensions as $key1 => $extension) {
                    if($module['extension_folder_name'] == $extension['extension_name'])
                    {
                        $extension['description'] = $module['description'];
                        $extension['extension_folder_name'] = $module['extension_folder_name'];
                        $extension['extension_name'] = $module['name'];

                        if(isset($module['image_name']) && $module['image_name']) {
                            $extension['image_name'] = $module['image_name']; 
                        } elseif(isset($module['logo']) && $module['logo']) {
                            $extension['logo'] = $module['logo']; 
                        } else {
                            $extension['image_name'] = "";
                            $extension['logo'] = "";
                        }

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
        if(isset($data['extensions']) && count($data['extensions']) > 0){
            foreach($data['extensions'] as $ext){
                if(isset($ext['is_active']) && $ext['is_active'] == 1){
                    $activated_modules[] = $ext['extension_folder_name'];
                }
            }
        }

        if($data['extensions']){
            foreach ($data['extensions'] as $key => $value) {
                if(
                    !isset($data['extensions'][$key]['is_installed']) &&
                    !isset($data['extensions'][$key]['vendor_id'])
                ){
                    $data['extensions'][$key]['is_installed'] = 0;
                }
            }
        }

        $this->session->set_userdata('activated_modules', $activated_modules);
        
        $data['js_files'] = array(
            base_url() . auto_version('js/hotel-settings/extension-settings.js'),
        );

        $data['selected_sidebar_link'] = 'Extensions';
        $data['main_content'] = 'hotel_settings/extension_settings/show_vendors_extensions';
        $this->load->view('includes/bootstrapped_template', $data);
    }
}