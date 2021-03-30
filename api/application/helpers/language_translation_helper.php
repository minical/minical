<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
if ( ! function_exists('get_languages'))
{
        // Function for fetching all language
    function get_languages()
    {
        $CI = & get_instance();
                $result = $CI->translation_model->get_records('language');
                return $result;
    }
    
    // Function for fetching enable languages
    function get_enabled_languages()
    {
        $CI = & get_instance();

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
                $where = array('is_default_lang'=>1);
                $result = $CI->translation_model->get_record_where('language',$where);
                if(!empty($result)) return $result[0];
                return FALSE;
    }

    // Function to load translation of language
    function load_translations($language_id = 1)
    {
        $CI = & get_instance();

        if ($language_id == 1) {
            $CI->all_translations_data = array();
            return;
        }

        $result = $CI->translation_model->get_all_phrases_by_language($language_id);
        $data_arr = array();

        if(!empty($result))
        {
            foreach ($result as $key => $value)
            {
                $data_arr[strtolower($value['phrase_keyword'])]=$value['phrase'];
            }
            // language translation is too much data to be stored in session and sometimes corrupt session if it reaches max limit of DB
            $CI->all_translations_data = $data_arr;
        }
    }

    // Function to return a value of phrase key
    function l($phrase_key, $return_plain_text = null)
    {
        $CI = & get_instance();

        $result = $CI->all_translations_data;

        if(!empty($result))
        {
            if(isset($result[strtolower($phrase_key)]))
            {
                $response = $result[strtolower($phrase_key)];
            }else{
                $response = ($CI->lang->line($phrase_key)) ? $CI->lang->line($phrase_key) : $phrase_key;
            }
            return $response;
        }
        return $phrase_key;
    }
}
