<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Example
 *
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array.
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
 * @author		Phil Sturgeon
 * @link		http://philsturgeon.co.uk/code/
*/

// This can be removed if you use __autoload() in config.php OR use Modular Extensions

/**
 *
 * @SWG\Model(id="Booking")
 */
class Company extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model("Company_model");
    }

    function get_company_id_post()
    {
        $x_api_key = $this->post('x_api_key');

        if(!$x_api_key) {
            $this->response(array('status' => false, 'error' => 'API key is missing.'), 200);
        }

        $company_id = $this->Company_model->get_company_id_from_api_key($x_api_key);

        $this->response($company_id, 200);
    }

    public function install_extension_post()
    {
  
      $company_id = $this->post('company_id');
      $extension_name = (string) $this->post('extension_name');
  
      if($company_id != null and $extension_name != null){
          $data = array(
            'extension_name' => $extension_name,
            'company_id' => $company_id,
            'is_active' => '1'
          );
          $count = $this->db->where('company_id', $company_id)->where('extension_name', $extension_name)->from('extensions_x_company')->count_all_results();
          if($count>0){
            $data = array('is_active'=>'1');
              $extension_update = $this->db->where('company_id', $company_id)->where('extension_name', $extension_name)->update('extensions_x_company', $data);
              if($extension_update){
                $this->response(array('success'=>'Extension updated!'));
                
              }
          }else{
            $extension_data = $this->db->insert('extensions_x_company', $data);
            if($extension_data != null)
              $this->response(array('success'=>'Extension installed!'));
            else
              $this->response(array('error'=>'Something went wrong'));
          }
          
      }else{
        $this->response(array('error'=>'Something went wrong'));
      }
  
    }
}
