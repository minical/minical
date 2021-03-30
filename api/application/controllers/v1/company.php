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
//require APPPATH.'/libraries/REST_Controller.php';

/**
 *
 * @SWG\Model(id="Booking")
 */
class Company extends REST_Controller
{
    /**
     *
     * @SWG\Api(
     *   path="/booking.{format}/{bookingId}",
     *   description="Operations about Bookings",
     *   @SWG\Operation(...),
     *      @SWG\Parameter(...),
     *      @SWG\ResponseMessage(...),
     *      @SWG\ResponseMessage(...)
     *   )
     * )
     */

    function __construct()
    {
        parent::__construct();
        $this->load->model("Company_model");
        $this->load->model('Image_model');
        $this->load->model('Feature_model');
        $this->load->model('translation_model');

        //$this->response(array('error' => 'Invalid API_key'), 404);
    }
    
    function index_get()
    {
        $uri = $this->get('uri');
        $company = $this->Company_model->get_company($uri);

        if ($uri == "" || $company == null)
        {
            $this->response(array('error' => 'Invalid API_key'), 404);
        }

        $company_id = $company['company_id'];
        $company['logo_images'] = $this->Image_model->get_images($company['logo_image_group_id']);
        $company['slide_images'] = $this->Image_model->get_images($company['slideshow_image_group_id']);
        $company['gallery_images'] = $this->Image_model->get_images($company['gallery_image_group_id']);
        $company['company_features'] = $this->Feature_model->get_features($company_id);

        $this->response($company, 200); // 200 being the HTTP response code   
    }

	function company_data_get()
	{
		$company_id = $this->get('company_id');
        $company = $this->Company_model->get_company_data($company_id);
		$this->response($company, 200);
	}

	function room_types_and_rate_plans_get()
    {
        $company_id = $this->get('company_id');
        $room_types_and_rate_plans = $this->Company_model->get_rate_plans_grouped_by_room_type($company_id);
        $this->response($room_types_and_rate_plans, 200); // 200 being the HTTP response code	
    }

    function language_data_post()
    {
        $language_id = $this->post('language_id');
        $language_data = $this->translation_model->get_all_phrases_by_language($language_id);
        $this->response($language_data, 200);
    }

    /**
    *   fetch slideshow images that exists in S3. 
    *   If it exists append it to the array and return the array
    */

}
