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
class Room_type extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model("Rate_plan_model");
        $this->load->model("Rate_model");
        $this->load->model("Room_type_model");
        $this->load->model("Room_model");
            
        $this->load->helper('timezone');
        
        //$this->response(array('error' => 'Invalid API_key'), 404);
    }

    function get_adr_post()
    {
        $date_start = $this->post('date_start');
        $date_end = $this->post('date_end');
        $room_type_id = $this->post('room_type_id');
        $rate_plan_id = $this->post('rate_plan_id');
        $company_id = $this->post('company_id');
        $response = array();

        if($date_start == '')
        {
            $this->response(array('status' => false, 'error' => 'Start date is missing.'), 200);
        }
        if($date_end == '')
        {
            $this->response(array('status' => false, 'error' => 'End date is missing.'), 200);
        }
        if($rate_plan_id == '')
        {
            $this->response(array('status' => false, 'error' => 'Rate plan id is missing.'), 200);
        }
        if($room_type_id == '')
        {
            $this->response(array('status' => false, 'error' => 'Room type id is missing.'), 200);
        }
        
        if (!empty($date_start) && !empty($date_end) && !empty($rate_plan_id))
        {   
            $daily_rates = $this->Rate_model->get_daily_rates($rate_plan_id, $date_start, $date_end, $company_id, $room_type_id);

            $adult_1_rate = $adult_2_rate = $adult_3_rate = $adult_4_rate = 0;

            if(isset($daily_rates) && $daily_rates)
            {
                foreach ($daily_rates as $key => $value) {
                    $adult_1_rate += $value['adult_1_rate'];
                    $adult_2_rate += $value['adult_2_rate'];
                    $adult_3_rate += $value['adult_3_rate'];
                    $adult_4_rate += $value['adult_4_rate'];
                }

                $adult_1_adr = $adult_1_rate / count($daily_rates);
                $adult_2_adr = $adult_2_rate / count($daily_rates);
                $adult_3_adr = $adult_3_rate / count($daily_rates);
                $adult_4_adr = $adult_4_rate / count($daily_rates);

                $adr = array(
                                'adult_1_adr' => number_format($adult_1_adr,2, ".", ","),
                                'adult_2_adr' => number_format($adult_2_adr,2, ".", ","),
                                'adult_3_adr' => number_format($adult_3_adr,2, ".", ","),
                                'adult_4_adr' => number_format($adult_4_adr,2, ".", ",")
                            );
                
                $response = $adr;
                $this->response($response, 200); // 200 being the HTTP response code
            }
            else
            {
                $this->response(array('status' => false, 'error' => 'Not authorized.'), 200);
            }
        }
        
        $this->response(null, 200); // 200 being the HTTP response code
    }

    function get_room_types_post()
    {
        $company_id = $this->post('company_id');

        if(!$company_id) {
            $this->response(array('status' => false, 'error' => 'Company ID is missing.'), 200);
        }

        $room_types = $this->Room_type_model->get_room_types($company_id);

        $this->response($room_types, 200);
    }

    function get_rooms_post()
    {
        $company_id = $this->post('company_id');

        if(!$company_id) {
            $this->response(array('status' => false, 'error' => 'Company ID is missing.'), 200);
        }

        $rooms = $this->Room_model->get_rooms($company_id);

        $this->response($rooms, 200);
    }
}
