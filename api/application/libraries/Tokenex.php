<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Tokenex {
	
    public $api_key, $tokenex_id, $api_tokenization_url, $api_payment_url, $api_iframe_url;

    public function __construct()
	{
		$this->ci =& get_instance();
       
        $this->api_key = isset($_SERVER["TOKENEX_API_KEY"]) ? $_SERVER["TOKENEX_API_KEY"] : "";
        $this->tokenex_id = isset($_SERVER["TOKENEX_ID"]) ? $_SERVER["TOKENEX_ID"] : "";
        
        $api_prefix = ($this->ci->config->item('app_environment') == "development") ? "test-" : "";
        $this->api_tokenization_url = "https://".$api_prefix."api.tokenex.com/TokenServices.svc/REST/";
      
    }
    
    public function tokenize($data){
       
        $data['TokenScheme']  = TOKEN_SCHEME_TOKEN;
        $response_xml = $this->call_api($this->api_tokenization_url, 'Tokenize', $data);
        $response = array();
        $response_obj = $this->xml_to_object($response_xml);
        if(isset($response_obj->Success) && $response_obj->Success == 'true')
        {
            $response['token'] = (string)$response_obj->Token;
        }
        elseif(isset($response_obj->Success) && $response_obj->Success == 'false')
        {
            $response['error'] = (string)$response_obj->Error;
        }
        return $response;
    }
      
    public function xml_to_object($response_xml){
       return simplexml_load_string($response_xml);
    }
    
    public function call_api($api_url, $method_name, $data = array()){
        $data['APIKey'] = $this->api_key;
        $data['TokenExID'] = $this->tokenex_id;

        $data = json_encode($data);
        
        if(!empty($data)){
            $content_length = strlen($data);
        }
        
        $url = $api_url.$method_name;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', "Content-Length:$content_length"));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_MAXCONNECTS, 1);
                   
        $output = curl_exec($ch);
        
        if(curl_error($ch))
            echo curl_error($ch);
         
        curl_close($ch);
        return $output;
        
    }
}
