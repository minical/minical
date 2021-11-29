<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
if ( ! function_exists('get_languages'))
{
        // Function for fetching all language
	function get_languages()
	{
		$CI = & get_instance();
        $CI->load->model('translation_model');

        $result = $CI->translation_model->get_records('language');
        return $result;
	}
    
    // Function for fetching enable languages
    function get_enabled_languages()
	{
		$CI = & get_instance();
        $CI->load->model('translation_model');

        $enabled_languages = $CI->session->userdata('enabled_languages');
        if (!$enabled_languages) {
            $where = array('is_enable'=>1);
            $enabled_languages = $CI->translation_model->get_record_where('language', $where);
            $CI->session->set_userdata(array('enabled_languages' => $enabled_languages));
        }

        return $enabled_languages;
	}
        
        // Function for fetching default language
    function get_default_language()
	{
		$CI = & get_instance();
        $CI->load->model('translation_model');

        $where = array('is_default_lang'=>1);
        $result = $CI->translation_model->get_record_where('language',$where);
        if(!empty($result)) return $result[0];
        return FALSE;
	}
        
	// Function for get language id by language name
	function get_language_id($language_name) {
		$CI = & get_instance();
        $CI->load->model('translation_model');
        $CI->load->model('User_model');

		$where = array('language_name' => $language_name);
		$result = $CI->translation_model->get_record_where('language', $where);

		if (!empty($result)) {
			$CI->session->set_userdata(array('language_id' => $result[0]['id']));
			$CI->User_model->update_user_profile($CI->session->userdata('user_id'), Array(
				'language_id' => $result[0]['id']
			));
			return $result[0]['id'];
		} else {
			return FALSE;
		}
	}

	// Function to load translation of language
    function load_translations($language_id = 1)
    {
        $CI = & get_instance();
        $CI->load->model('translation_model');
        $data_arr = array();

        $language_name = $CI->session->userdata('language');
        
        //if($language_name == ''){
            $language_data = $CI->translation_model->get_record_where('language', array('id' => $language_id));
            $language_name = $language_data[0]['flag'];
        //}

        $modules_path = APPPATH.'extensions/';
        $modules = scandir($modules_path);
       
        foreach($modules as $module)
        {
            if($module === '.' || $module === '..') continue;
            if(is_dir($modules_path) . $module)
            {
                $config = array();
                $files_path = $modules_path . $module."/language/".$language_name."/index.php";
                if(file_exists($files_path))
                {
                    $lang = array();
                    require($files_path);
                    if(count($lang) > 0){
                        foreach ($lang as $m => $v) {
                            foreach($v as $key => $value)
                            {
                                $data_arr[strtolower($m.'/'.$key)] = $value;
                            }
                        }
                    }
                }
                else
                {
                    $files_path = $modules_path . $module."/language/english/index.php";
                    if(file_exists($files_path))
                    {
                        $lang = array();
                        require($files_path);
                        if(count($lang) > 0){
                            foreach ($lang as $m => $v) {
                                foreach($v as $key => $value)
                                {
                                    $data_arr[strtolower($m.'/'.$key)] = $value;
                                }
                            }
                        }
                    }
                }
            }
        }

        $result = $CI->translation_model->get_all_phrases_by_language($language_id);
       
        if(!empty($result))
        {
            foreach ($result as $key => $value)
            {
                $data_arr[strtolower($value['phrase_keyword'])]=$value['phrase'];
            }
            // language translation is too much data to be stored in session and sometimes corrupt session if it reaches max limit of DB
            //$CI->session->set_userdata(array('translation_data'=>$data_arr));
            $CI->all_translations_data = $data_arr;
            //$CI->all_translations = $data_arr;
        }
    }

    global $current_translation_version;
    // Function to return a value of phrase key
    function l($phrase_key, $return_plain_text = null)
    {
        $CI = & get_instance();   
        $CI->load->model('translation_model');

        $translation_version = $CI->session->userdata('translation_version');
        //$current_translation_version = $CI->config->item("current_translation_version");
        global $current_translation_version;
        if (!$current_translation_version) {
            $current_translation_version = $CI->translation_model->get_record_where('language', array('language_name' => $CI->session->userdata('language')));
            $current_translation_version = isset($current_translation_version[0]) ? $current_translation_version[0]['version'] : 1;
        }

        //!$CI->all_translations &&
        //if(!isset($CI->session->userdata['translation_data']) || ($translation_version != $current_translation_version))
        if(!isset($CI->all_translations_data) || ($translation_version != $current_translation_version))
        {
            $CI->session->set_userdata('translation_version', $current_translation_version);
            if(empty($CI->session->userdata('language_id')))
            {
                get_language_id($CI->session->userdata('language'));
            }
            $language_id = $CI->session->userdata('language_id');
            load_translations($language_id);
        }
        // $result = $CI->session->userdata['translation_data'];
        $result = $CI->all_translations_data;
        //$result = $CI->all_translations;

        if(!empty($result))
        {
            if(isset($result[strtolower($phrase_key)]))
            {
                if($return_plain_text)
                {
                    $line = $result[strtolower($phrase_key)];
                }
                else
                {
                    $line = '<span alt="'.$phrase_key.'" title="'.$phrase_key.'">'.$result[strtolower($phrase_key)].'</span>';
                }
                return $line;
            }
            else
            {
                // if((isset($CI->user_id) && $CI->user_id == SUPER_ADMIN_USER_ID) || (!(isset($result[strtolower($phrase_key)]) || $CI->lang->line($phrase_key)))) {
                //     if(is_null($CI->translation_model->check_phrase_keyword_with_language($phrase_key, null, true))) {
                //         $insert_data = array('phrase_keyword' => $phrase_key, 'created_at' => date('Y-m-d H:i:s'));
                //         $CI->translation_model->insert_records('language_phrase', $insert_data);
                //     }
                // }

                if($return_plain_text)
                {
                    $line = ($CI->lang->line($phrase_key)) ? $CI->lang->line($phrase_key) : $phrase_key;
                }
                else
                {
                    //$line = '<span alt="'.$phrase_key.'" title="'.$phrase_key.'">'.(($CI->lang->line($phrase_key))?$CI->lang->line($phrase_key):$phrase_key).'</span>';
                    $line = ucwords(str_replace('_', ' ', $phrase_key));
                }
                return $line;
            }
        }
        return $phrase_key;
    }
}
