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
        $installed_extensions = array();

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
        
        if($this->vendor_id != 0 && $this->user_id != SUPER_ADMIN_USER_ID)
        {
            $installed_extensions = $this->Extension_model->get_installed_extensions($this->company_id, $this->vendor_id);
        
            $uninstalled_extensions = $this->Extension_model->get_uninstalled_extensions($this->company_id, $this->vendor_id);
        } else {
            $installed_extensions = $this->Extension_model->get_installed_extensions(null, $this->vendor_id);
        
            $uninstalled_extensions = $this->Extension_model->get_uninstalled_extensions(null, $this->vendor_id);
        }

        // echo $this->company_id; echo '<br/>';
        // echo $this->vendor_id ? $this->vendor_id : 0; echo '<br/>';
        // echo 'extensions = '; prx($extensions, 1);
        // echo 'installed_extensions = '; prx($installed_extensions, 1);
        // echo 'uninstalled_extensions = '; prx($uninstalled_extensions);

        if($this->vendor_id){
            $get_installed_extensions = $this->Extension_model->get_installed_extensions($this->company_id, $this->vendor_id);

            if(empty($get_installed_extensions)){
                $active_extensions = $this->Extension_model->get_active_extensions($this->company_id);
                            
                if($active_extensions && count($active_extensions) > 0){
                    $new_extensions = array();
                    foreach ($active_extensions as $extension) {
                        $new_extensions[] = array(
                                                    'extension_name' => $extension['extension_name'],
                                                    'company_id' => $this->company_id,
                                                    'vendor_id' => $this->vendor_id,
                                                    'is_installed' => 1
                                                );
                    }

                    for ($i = 0, $total = count($new_extensions); $i < $total; $i = $i + 50)
                    {
                        $ext_batch = array_slice($new_extensions, $i, 50);

                        $this->db->insert_batch("extensions_x_vendor", $ext_batch);

                        if ($this->db->_error_message())
                        {
                            show_error($this->db->_error_message());
                        }
                    }
                }
            }
        }

        $temp_ext = $temp_extension = array();
        $is_ext_matched = false;
        
        if($extensions){
            foreach ($extensions as $key => $ext) {
                if($installed_extensions){
                    $is_ext_matched = false;
                    foreach ($installed_extensions as $key1 => $in_ext) {
                        if(
                            isset($in_ext['vendor_id']) && 
                            $ext['extension_name'] == $in_ext['extension_name'] && 
                            $ext['company_id'] == $in_ext['company_id']

                        ){
                            $is_ext_matched = true;
                            $temp_ext[$key] = $installed_extensions[$key1];
                            $temp_ext[$key]['is_active'] = $ext['is_active'];
                        }
                    }
                }
                if(!$is_ext_matched){
                    $temp_ext[$key] = $extensions[$key];
                }
            }

            if($installed_extensions && count($extensions) <= count($installed_extensions)){
                $i = 0;
                foreach ($installed_extensions as $key1 => $in_ext) {
                    foreach ($extensions as $key => $ext) {
                        
                        if(
                            $ext['extension_name'] == $in_ext['extension_name']
                        ){
                            $temp_ext[$i] = $in_ext;
                            $temp_ext[$i]['is_active'] = $ext['is_active'];
                            break;
                        } else {
                            $temp_ext[$i] = $in_ext;
                            $temp_ext[$i]['is_active'] = 0;
                        }
                    } $i++;
                }
            }

            $extensions = $temp_ext;
        }
        else {
            if($installed_extensions){
                foreach ($installed_extensions as $key1 => $in_ext) {
                    $installed_extensions[$key1]['is_active'] = 0;
                }
            }
            $extensions = $installed_extensions;
        }

        // prx($extensions);

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

        $data['extensions'] = $final_modules ? $final_modules : array();

        $activated_modules = array();
        if(count($data['extensions']) > 0){
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

                if(
                    isset($data['extensions'][$key]['is_admin_module']) &&
                    $data['extensions'][$key]['is_admin_module']
                ) {
                    $data['extensions'][$key]['is_installed'] = 1;
                }
            }
        }

        // prx($data['extensions']);

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

    function uninstall_extension()
    {
        $whitelabel_partner_detail = $this->Whitelabel_partner_model->get_whitelabel_partner_id($this->user_id);

        $data['extension_name'] = $this->input->post('extension_name');
        $data['is_installed'] = 0;
        $data['company_id'] = $this->company_id;
        $data['vendor_id'] = $whitelabel_partner_detail['partner_id'] ? $whitelabel_partner_detail['partner_id'] : 0;

        $this->Extension_model->update_extension_status($data);
        echo json_encode(array('success' => true));
    }

    function install_extension()
    {
        $whitelabel_partner_detail = $this->Whitelabel_partner_model->get_whitelabel_partner_id($this->user_id);

        $data['extension_name'] = $this->input->post('extension_name');
        $data['is_installed'] = 1;
        $data['company_id'] = $this->company_id;
        $data['vendor_id'] = $whitelabel_partner_detail['partner_id'] ? $whitelabel_partner_detail['partner_id'] : 0;

        $this->Extension_model->update_extension_status($data);
        echo json_encode(array('success' => true));
    }
    
}