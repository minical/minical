<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CodeIgniter OTA Class
 *
 * Has various funcitons such as updating availability & rates and Get bookings
 * Utilizes various types of 
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Jaeyun Noh
 * @link		
 */
class Roomsy_channel_manager {
	
	/**
	 * Constructor - Sets Email Preferences
	 *
	 * The constructor can be passed an array of config values
	 */

	public function __construct()
	{
		$this->ci =& get_instance();
		$this->ci->load->library('Format');
		$this->ci->load->spark('restclient/2.2.1');	
        $this->ci->load->model('Company_model');

        $api_key = '';
        if(isset($this->company_api_key)){
            $api_key = $this->ci->company_api_key;
        }

		$config = array(
						'server'            => $this->ci->config->item('cm_url').'/api',
						'api_key'           => $api_key
		                );
        
		$this->ci->rest->initialize($config);
	}
    function get_otas($company_id)
    {
        return $this->get("otas", array('company_id' => $company_id));
    }
    
    function configure_ota($company, $data){
        $data['company_name'] = $company['name'];
        $data['time_zone'] = $company['time_zone'];
        $data['company_id'] = $company['company_id'];
        $api_key = $company['api_key'];
        if(!$company['api_key']){
            // generate new api key and create company on channel manager
            $company['api_key'] = md5(uniqid($company['company_id'], true));
            $this->ci->Company_model->update_company($company['company_id'], array('api_key' => $company['api_key']));
        }
        $data['inngrid_api_key'] = $company['api_key'];
        return $this->post("configure_ota", array('data' => $data));        
    }

    function configure_api_key($company, $api_key){

        if($api_key){

            if(!$company['api_key']){
                // get api key from key table
                $company['api_key'] = $api_key;
                $this->ci->Company_model->update_company($company['company_id'], array('api_key' => $company['api_key']));
            }

            $data['key'] = $api_key;
            $data['level'] = 10;
            $data['ignore_limits'] = 123;
            $data['date_created'] = date('Y-m-d');
        }

        return $this->post("configure_api_key", array('data' => $data));        
    }
    
    function deconfigure_ota($company_id, $ota_id){
        return $this->post("deconfigure_ota", array('company_id' => $company_id, 'ota_id' => $ota_id));        
    }
    
    function get_room_types_and_rate_plans($ota_id, $company_id)
    {
        return $this->get('room_types_and_rate_plans',array('ota_id' => $ota_id, 'company_id' => $company_id));
    }
    
    function get_link_data($company_id, $ota_id)
    {
        $link_data = $this->get('link_data', array('ota_id' => $ota_id, "company_id" => $company_id));
        return array('link_data' => $link_data);
    }
    
    function save_links($data)
    {
        return $this->post('save_links', $data);
    }
    
    
    // myallocator related methods
    function get_myallocator_user_token($company_id = null, $data = null)
    {
        return $this->get('myallocator_user_token', array("company_id" => $company_id, "data" => $data));
    }
    
    function get_myallocator_properties($company_id)
    {
        return $this->get('myallocator_properties', array('company_id' => $company_id));
    }
    function set_myallocator_property($company_id, $ota_id, $myallocator_property_id){
        return $this->post('set_myallocator_property', array(
                    "myallocator_property_id" => $myallocator_property_id,
                    "company_id" => $company_id,
                    "ota_id" => $ota_id
                ));
    }
    function get_myallocator_room_types_and_rate_plans($company_id, $ota_id){
        return $this->get('myallocator_room_types_and_rate_plans', array(
                    "company_id" => $company_id,
                    "ota_id" => $ota_id
                ));
    }
    function set_myallocator_room_types_and_rate_plans($company_id, $ota_id, $mapping_data){
        return $this->post('myallocator_room_types_and_rate_plans', array(
                    "company_id" => $company_id,
                    "ota_id" => $ota_id,
                    "mapping_data" => $mapping_data
                ));
    }
    
    // agoda related methods
    function get_agoda_user($company_id = null, $data = null)
    {
        return $this->get('agoda_user', array("company_id" => $company_id, "data" => $data));
    }
    
    function set_ical_import_urls_mapping($company_id, $ical_mapping_data)
    {
        return $this->post('ical_import_urls_mapping', array(
                    "company_id" => $company_id,
                    "ical_mapping_data" => $ical_mapping_data
                ));
    }
    
    function get_ical_pms_room_ids($company_id)
    {
        return $this->get('ical_pms_room_ids', array(
                    "company_id" => $company_id
                ));       
    }
    
	function get($url, $param = "", $format = NULL)
	{
		$val = $this->ci->rest->get($url, $param, $format);
		return $this->ci->format->factory($val)->to_array(); // convert to array
	}

	function post($url, $param = "", $format = NULL)
	{
		$val = $this->ci->rest->post($url, $param);
		return $this->ci->format->factory($val)->to_array(); // convert to array

	}

	function delete($url, $param = "", $format = NULL)
	{
		$val = $this->ci->rest->delete($url, $param);
		return $this->ci->format->factory($val)->to_array(); // convert to array
	}

	function change_api_server($new_api_server)
	{
		$config = array(
						'server'            => $this->ci->config->item('api_url'),
						'api_key'         => $this->ci->company_api_key
		                );

		$this->ci->rest->initialize($config);

	}

	function debug()
	{
		return $this->ci->rest->debug();
	}
}
