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
class Auth extends REST_Controller
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

        //$this->response(array('error' => 'Invalid API_key'), 404);
    }

    /**
    *   fetch slideshow images that exists in S3. 
    *   If it exists append it to the array and return the array
    */

}