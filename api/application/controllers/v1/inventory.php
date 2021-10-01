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
class Inventory extends REST_Controller
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
        $this->load->model("Availability_model");
        $this->load->model("Rate_model");
        $this->load->helper('date');
        
        //$this->response(array('error' => 'Invalid API_key'), 404);
    }
    
    // Get net availability
	function availability_get()
    {
        //$company_id = $this->get('company_id');

        /*
            Room types are in an array form: {1232, 3212, 3211, 3242}
        */

        $room_types = $this->get('room_types');
        $start_date = $this->get('start_date');
        $end_date = $this->get('end_date');
        $adult_count = $this->get('max_adults');
        $get_inventory = $this->get('get_inventory');
        $adult_count = empty($adult_count) ? null : $adult_count;
        $children_count = $this->get('max_children');
        $children_count = empty($children_count) ? null : $children_count;
		$ota_id = $this->get('ota_id');
		$ota_id = empty($ota_id) ? SOURCE_BOOKING_DOT_COM : $ota_id; // default Booking.com
		$filter_can_be_sold_online = $this->get('filter_can_be_sold_online');
		$get_max_availability = $this->get('get_max_availability');
		$get_inventorysold = $this->get('get_inventorysold');
		$get_closeout_status = $this->get('get_closeout_status');
        $get_inventory = empty($get_inventory) ? false : $get_inventory;
        $company_id = $this->get('company_id');
        $ota_key = $this->get('ota_key');
        $availability = $this->Availability_model->get_availability(
																$start_date, 
																$end_date, 
																$room_types, 
																$ota_id,
																$filter_can_be_sold_online,
                                                                $adult_count,
                                                                $children_count,
                                                                $get_inventory,
                                                                $get_max_availability,
                                                                $get_inventorysold,
                                                                $get_closeout_status,
                                                                false,
                                                                $company_id,
                                                                $ota_key
        );
        
        $this->response($availability, 200); // 200 being the HTTP response code
    	
    }

    // Get max availability
	function max_availability_get()
    {
        $room_types = $this->get('room_types');
        $start_date = $this->get('start_date');
        $end_date = $this->get('end_date');
		$channel = $this->get('channel');
		$filter_can_be_sold_online = $this->get('filter_can_be_sold_online');

        $availability = $this->Availability_model->get_max_availability(
																$start_date, 
																$end_date, 
																$room_types, 
																$channel,
																$filter_can_be_sold_online);
     
        $this->response($availability, 200);
    }


    function rates_get()
    {
        $rate_plan_id = $this->get('rate_plan_id');
		$ota_id = $this->get('ota_id');
		$ota_id = empty($ota_id) ? 2 : $ota_id; // default Booking.com
        $start_date = $this->get('start_date');
        $end_date = $this->get('end_date');

        $ranged_rates = $this->Rate_model->get_rates(
                                            $rate_plan_id, 
											$ota_id,
                                            $start_date, 
                                            $end_date);
        
        $this->response($ranged_rates, 200); // 200 being the HTTP response code
        
    }

}
