<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Language_translation extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('Admin_model');
        $this->load->model('Test_model');
        $this->load->model('Company_model');
        $this->load->model('Company_subscription_model');
        $this->load->model('Whitelabel_partner_model');
        $this->load->model('translation_model');
        
        $this->load->library('form_validation');
        $this->load->library('pagination');

        $this->load->helper('language_translation');
    }

    function get_languages()
    {
        $languages = $this->translation_model->get_records('language');
        foreach($languages as $key => $lang)
        {
            $language = $this->session->userdata('language');
            $language = $language ? $language : "English";
            $languages[$key]['current_language'] = ucfirst($language);
        }
        echo json_encode($languages);
    }
    
    // Get translation data by language using ajax
    function get_translation_data() {
        $language_id = $this->input->post('lang_id');
        $current_language_id = $this->input->post('current_lang_id');
        $translation_key = trim($this->input->post('translation_key'));
        $translation_records = $this->translation_model->get_translation_data($language_id, $current_language_id, $translation_key);
        echo json_encode($translation_records);
    }
    
    function save_lang_translation_data()
    {
        $saveDataArray = $this->input->post('saveDataArray');
        $phraseCheckedKeys = $this->input->post('phraseCheckedKeysArray');
        $current_language_id = $this->input->post('current_lang_id');
        $translation_key = trim($this->input->post('translation_key'));
        $translation_key_id = $this->input->post('translation_key_id');
        $response['success'] = true;
        if(!empty($phraseCheckedKeys))
        {
            for($i = 0; $i < count($phraseCheckedKeys); $i++)
            {
                foreach($saveDataArray as $key => $val)
                {
                    foreach($saveDataArray[$key] as $key1 => $val1)
                    {
                        $translatedRecords = $this->translation_model->get_translation_data($key1, $current_language_id, $translation_key, $phraseCheckedKeys[$i]);
                        if(!empty($translatedRecords))
                        {
                            $data = array('phrase'=>$val1);
                            $where = array(
                                        'id'=>$translatedRecords['id'],
                                        'phrase_id'=>$translatedRecords['phrase_id'],
                                        'language_id'=>$key1,
                                        );
                            $this->translation_model->update_records('language_translation', $data, $where);
                            // for update current language version
                            $current_language_version = $this->translation_model->get_record_where('language', array('id' => $current_language_id))[0];
                            $this->translation_model->update_records('language', array('version' => ($current_language_version['version']+1)), array('id' => $current_language_id));
                            load_translations($current_language_id);
                        }
                        else
                        {
                            $lang_phrase_data = array('phrase_keyword'=>$translation_key);
                            $check_phrase_keyword = $this->translation_model->check_phrase_keyword_with_language($translation_key, $key1, true);
                            if(empty($check_phrase_keyword))
                            {
                                $phrase_id = $this->translation_model->get_existing_phrase_of_translation($translation_key);
                                if(!$phrase_id)
                                    $phrase_id = $this->translation_model->insert_records('language_phrase', $lang_phrase_data);
                            }
                            else
                            {
                                $phrase_id = $check_phrase_keyword['id'];
                            }
                            
                            if(isset($phrase_id) && $phrase_id)
                            {   
                                $lang_translation_data = array('phrase_id'=>$phrase_id, 'language_id'=>$key1, 'phrase'=>$val1);
                                $this->translation_model->insert_records('language_translation', $lang_translation_data);
                            }
                            // for update cuurent language version
                            $current_language_version = $this->translation_model->get_record_where('language', array('id' => $current_language_id))[0];
                            $this->translation_model->update_records('language', array('version' => ($current_language_version['version']+1)), array('id' => $current_language_id));
                            load_translations($current_language_id);
                        }
                    }
                }
            }
        }
        else
        {
            foreach($saveDataArray as $key => $val)
            {
                foreach($saveDataArray[$key] as $key1 => $val1)
                {
                    $translatedRecords = $this->translation_model->get_translation_data($key1, $current_language_id, $translation_key, $translation_key_id);
                    if(!empty($translatedRecords))
                    {
                        $data = array('phrase'=>$val1);
                        $where = array(
                                    'id' => $translatedRecords['id'],
                                    'phrase_id' => $translatedRecords['phrase_id'],
                                    'language_id' => $key1,
                                );
                        $this->translation_model->update_records('language_translation', $data, $where);
                        // for update cuurent language version
                        $current_language_version = $this->translation_model->get_record_where('language', array('id' => $current_language_id))[0];
                        $this->translation_model->update_records('language', array('version' => ($current_language_version['version'] + 1)), array('id' => $current_language_id));
                        load_translations($current_language_id);
                    }
                    else
                    {   
                        $lang_phrase_data = array('phrase_keyword' => $translation_key);
                        
                        $check_phrase_keyword = $this->translation_model->check_phrase_keyword_with_language($translation_key, $key1, true);
                        if(empty($check_phrase_keyword))
                        {
                            $phrase_id = $this->translation_model->get_existing_phrase_of_translation($translation_key);
                            if(!$phrase_id)
                                $phrase_id = $this->translation_model->insert_records('language_phrase', $lang_phrase_data);
                        }
                        else
                        {
                            $phrase_id = $check_phrase_keyword['id'];
                        }
                        
                        if(isset($phrase_id) && $phrase_id)
                        {   
                            $lang_translation_data = array('phrase_id' => $phrase_id, 'language_id' => $key1, 'phrase' => $val1);
                            $this->translation_model->insert_records('language_translation', $lang_translation_data);
                        }
                        // for update current language version
                        $current_language_version = $this->translation_model->get_record_where('language', array('id' => $current_language_id))[0];
                        $this->translation_model->update_records('language', array('version' => ($current_language_version['version'] + 1)), array('id' => $current_language_id));
                        load_translations($current_language_id);
                    }
                }
            }
        }
                
        echo json_encode($response);
    }

    function insert_non_translated_keys() {
        $insert_data = array();
        $non_translated_keys = $this->input->post('non_translated_keys');
        $is_module = true;
        $module_name = '';

        foreach($non_translated_keys as $key) {
            foreach ($this->all_active_modules as $module => $value) {
                
                $key1 = explode('/',$key);
                if($module == $key1[0]) {
                    $is_module = false;
                    $module_name = $module;
                }
            }
            
            if($is_module && $module_name == '') {
                if(is_null($this->translation_model->check_phrase_keyword_with_language($key, null, true))) {
                    $insert_data = array('phrase_keyword' => $key, 'created_at' => date('Y-m-d H:i:s'));
                    $insert_id = $this->translation_model->insert_records('language_phrase', $insert_data);
                }
            }
        }
    }

    function get_translated_phrase(){
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, OPTIONS, POST");
        $data = file_get_contents('php://input');

        $data = json_decode($data,true);
        $company_id = $data['company_id'];
        $labels = $data['label_array'];

        $company_data = $this->Company_model->get_company($company_id);

        $get_translated_phrase = array();
        
        foreach ($labels as $value) {
            $translation_records = $this->translation_model->check_phrase_keyword_with_language($value, $company_data['default_language']);

            $str = str_replace("-", "_", strtolower($value));
            $str = str_replace(" ", "_", strtolower($str));

            if($translation_records){
                $get_translated_phrase[$str] = $translation_records['phrase'];
            } else {
                $get_translated_phrase[$str] = $value;
            }
        }

        echo json_encode($get_translated_phrase, true);
    }
}
