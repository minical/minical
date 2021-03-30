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
class Room_types extends REST_Controller
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
        $this->load->model('Company_model');
        $this->load->model('Room_model'); 
        $this->load->model('Room_type_model');           
        $this->load->model('Image_model');           
        $this->load->model('Charge_type_model');           
        $this->load->model('Rate_plan_model');           
        
        //$this->response(array('error' => 'Invalid API_key'), 404);
    }
    
    function index_get()
    {
        
        $company_id = $this->get('company_id');
        $room_types = $this->Room_type_model->get_room_types($company_id);

        foreach($room_types as $i => $room_type)
        {
            $image_group_id = $room_type['image_group_id'];
            $room_types[$i]['images'] = $this->Image_model->get_images($image_group_id);
        }
        
        $this->response($room_types, 200); // 200 being the HTTP response code   
    }

    function room_details_post()
    {
        $room_id = $this->post('room_id');
        $room_data = $this->Room_model->get_room($room_id);
        
        $this->response($room_data, 200); // 200 being the HTTP response code   
    }

    function rate_plan_details_post()
    {
        $rate_plan_id = $this->post('rate_plan_id');
        $rate_plan_data = $this->Rate_plan_model->get_rate_plan($rate_plan_id);
        
        $this->response($rate_plan_data, 200); // 200 being the HTTP response code   
    }

    function tax_details_post()
    {
        $charge_type_id = $this->post('charge_type_id');
        $tax_data = $this->Charge_type_model->get_taxes($charge_type_id);
        
        $this->response($tax_data, 200); // 200 being the HTTP response code   
    }
    
    function pms_max_room_type_occupancy_post()
    {
        $pms_room_type_id = $this->post('pms_room_type_id');
        $room_type = $this->Room_type_model->get_room_type($pms_room_type_id);
        $this->response($room_type, 200);
    }

    /**
    *   fetch slideshow images that exists in S3. 
    *   If it exists append it to the array and return the array
    */

}