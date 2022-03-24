<?php 

class Translation extends MY_Controller
{
    
	function __construct()
	{
        parent::__construct();

        $this->load->model('Translation_model');
        $this->load->library('pagination');
        $this->load->library('Form_validation');
        $this->load->helper('language_translation');
        $this->load->helper('timezone');
        $view_data['menu_on'] = true;
        
        $view_data['selected_menu']    = 'Settings';
        $view_data['selected_submenu'] = 'Translation';

        $view_data['submenu'] = 'hotel_settings/hotel_settings_submenu.php';

        $view_data['submenu_parent_url'] = base_url()."settings/";
        $view_data['sidebar_menu_url'] = base_url()."settings/translation/";

        $view_data['menu_items'] = $this->Menu_model->get_menus(array('parent_id' => 6, 'wp_id' => 1));
        $view_data['sidebar_links'] = $this->Menu_model->get_menus(array('parent_id' => 29, 'wp_id' => 1));   
    
        $this->load->vars($view_data);
	}

    function index($language_id = 0){
        $this->translation($language_id);
    }

    function translation($language_id = 0)
    {
        $languages = $this->translation_model->get_records('language');
        
        if($this->input->post())
        {
            
            $this->form_validation->set_rules('phrase_keyword[]', 'Phrase Keyword', 'trim|required');

            if ($this->form_validation->run()) 
            { 
                $insert_data = array();
                $insert_translation_data = array();
                $language_id = $this->input->post('language_id');
                $language_name = $this->input->post('language_name');
                $default_language_id = $this->input->post('default_language_id');
                $phrase_keyword = $this->input->post('phrase_keyword');
                $selected_language_phrase = $this->input->post('selected_language_phrase');
                
                foreach ($phrase_keyword as $key => $value) 
                {
                    $insert_data['phrase_keyword'] = $value;
                    $insert_translation_data['language_id'] = $language_id;
                    $insert_translation_data['phrase'] = $selected_language_phrase[$key];
                  
                    $check_if_exists = $this->translation_model->check_phrase_keyword_with_language($value, $language_id);
                    
                    if(empty($check_if_exists))
                    {
                        $phrase_id = $this->translation_model->insert_records('language_phrases', $insert_data); 
                        
                        if(!empty($phrase_id))
                        {
                            $insert_translation_data['phrase_id'] = $phrase_id;
                            $insert_translation_data_array = array();
                            
                            foreach ($languages as $language) 
                            {
                                $insert_translation_data['language_id'] = $language['id'];
                                if($language_id == '1')
                                {
                                    if ($language['id']==$language_id) 
                                    {
                                        $insert_translation_data['phrase'] = $selected_language_phrase[$key];
                                    }else{
                                        $insert_translation_data['phrase'] = $selected_language_phrase[$key];
                                    }
                                }
                                else
                                {
                                    if($language['id'] == $language_id)
                                    {
                                        $insert_translation_data['phrase'] = $selected_language_phrase[$key];
                                    }
                                    else
                                    {
                                        $insert_translation_data['phrase'] = '';
                                    }                                             
                                }
                                     
                                $insert_translation_data_array[] = $insert_translation_data;
                            }
                            $translation_id = $this->translation_model->insert_translation_records('language_translation', $insert_translation_data_array);
                        }
                    }else{
                        $this->session->set_flashdata('error_message', $value.' phrase kewyword already exists for '.$language_name.' language');
                        redirect('settings/translation');
                    }
                }
                
                if($translation_id > 0)
                {
                    $this->session->set_flashdata('success_message', 'Saved Successfully!!');
                    redirect('settings/translation/'.$language_id);
                }else{
                    $this->session->set_flashdata('error_message', 'Error in insertion');
                    redirect('settings/translation/'.$language_id);
                }
            }
            else
            {
                $this->session->set_flashdata('error_message', 'Please fill required Fields');
                redirect('settings/translation/'.$language_id);
            }

        }

        $view_data['non_translated_phrase'] = false;
        if(isset($_GET['non_translated_phrase'])) {
            $view_data['non_translated_phrase'] = true;
        }

        $view_data['translationRecords'] = $this->translation_model->get_all_phrases($language_id, $view_data['non_translated_phrase']);

        $language_status = $this->translation_model->get_record_where('language',array('id' => $language_id));
        $view_data['language_status'] = isset($language_status[0]['is_enable']) ? $language_status[0]['is_enable'] : '';
        
        $view_data['language_id'] = $language_id;
         
         $view_data['css_files'] = array(
            'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'
        );
         $view_data['js_files'] = array(
            'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
            base_url() . auto_version('js/hotel-settings/language_translation.js')
        );
        
        $view_data['main_content'] = 'hotel_settings/company/language_translation';
        $this->load->view('includes/bootstrapped_template', $view_data);
        
    }
    
    // Get translation data by language using ajax
    function get_translation_data() {

        $language_id = $this->input->post('language_id');
        $data['translationRecords'] = $this->translation_model->get_translation_data($language_id);
        
        $english_translations = $this->translation_model->get_translation_data("1");
        foreach($english_translations as $key => $translation){
            $data['translationRecords'][$key]['default_key'] = isset($translation['phrase']) && $translation['phrase'] != '' ? $translation['phrase'] : (isset($translation['phrase_keyword']) && $translation['phrase_keyword'] ? $translation['phrase_keyword'] : "");
        }

        foreach ($data['translationRecords'] as $key => $value) {
            if(isset($value['phrase']) && $value['phrase'] != '') {
                unset($data['translationRecords'][$key]);
            }
        }

        $data['language_id'] = $language_id;
        $this->load->view('admin/language_translation_ajax_view', $data);
    }
    
    // Change language status using ajax
    function change_language_status() {
        
        $language_id = $this->input->post('language_id');
        $language_value = $this->input->post('value');
        $where = array('id'=>$language_id);
        $data['is_enable'] = $language_value;
        $this->translation_model->update_records('language', $data, $where);
        $enabled_languages = $this->session->userdata('enabled_languages');
        $where = array('is_enable'=>1);
        $enabled_languages = $this->translation_model->get_record_where('language', $where);
        $this->session->set_userdata(array('enabled_languages' => $enabled_languages));
       
        echo l('Data Saved Successfully!', true);
    }

    function add_language_AJAX(){
        $data = $_POST;
         $data = array(
            'language_name' =>ucfirst($data['lang_name']),
            'is_default_lang' => 0,
            'flag' => strtolower($data['lang_name']),
        );
        $language_flag = $this->translation_model->get_record_where('language',array('language_name' => $data['language_name']));
        if(empty($language_flag)){ 
            $this->translation_model->insert_records('language', $data); 

            echo json_encode(array('success'=>l('Language added Successfully!', true))); 
        }else{

            echo json_encode(array('success'=>l('Language already exists!', true)));
        }
    }

    function update_translation_phrase() {
        $data = array();
        $tid = $this->input->post('tid');
        $pid = $this->input->post('pid');
        $language_id = $this->input->post('language_id');
        $phrase_value = $this->input->post('phrase_value');

        $where = array('id' => $tid);
        $data['phrase'] = $phrase_value;
        
        $is_exist_translation = $this->translation_model->get_record_where('language_translation', array('phrase_id' => $pid, 'language_id' => $language_id));
        
        if($is_exist_translation) {

           $this->translation_model->update_records('language_translation', $data, $where);
        
        } else {
            $data['phrase_id'] = $pid;
            $data['language_id'] = $language_id;
            $this->translation_model->insert_records('language_translation', $data);
        }
        
        echo l('success', true);
    }   
}