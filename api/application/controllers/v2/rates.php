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
class Rates extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model("Company_model");
        $this->load->model("Rate_plan_model");
        $this->load->model("Room_type_model");
        $this->load->model("Date_range_model");
            
        $this->load->helper('timezone');
        
        //$this->response(array('error' => 'Invalid API_key'), 404);
    }

    function update_rates_post()
    {
        $params = $this->post();

        if(!is_array($params)){
            $params = json_decode($params, true);
        }

        if(empty($params))
        {
            $this->response(array('status' => false, 'error' => 'Invalid json.'), 200);
        }
        
        $company_id = $this->post('company_id');

        if(!array_key_exists('rate_plan_id', $params))
        {
            $this->response(array('status' => false, 'error' => 'Rate plan id is missing.'), 200);
        }
        else if(array_key_exists('rate_plan_id', $params) && $params['rate_plan_id'] == '')
        {
            $this->response(array('status' => false, 'error' => 'Rate plan id is missing.'), 200);
        }

        $rate_plan_id = $params['rate_plan_id'];

        if(!array_key_exists('rates', $params))
        {
            $this->response(array('status' => false, 'error' => 'Rates data is missing.'), 200);
        }
        else if(array_key_exists('rates', $params) && $params['rate_plan_id'] == '')
        {
            $this->response(array('status' => false, 'error' => 'Rates data is missing.'), 200);
        }

        $dates = $params['rates'];
        $rate_plan = $this->Rate_plan_model->get_rate_plan($rate_plan_id, $company_id);
        if(isset($rate_plan) && $rate_plan)
        {
            $rate_data_variables = array(
                                    "rate_plan_id",
                                    "adult_1_rate",
                                    "adult_2_rate",
                                    "adult_3_rate",
                                    "adult_4_rate",
                                    "additional_adult_rate",
                                    "additional_child_rate",
                                    'minimum_length_of_stay',
                                    'maximum_length_of_stay',
                                    'minimum_length_of_stay_arrival',
                                    'maximum_length_of_stay_arrival',
                                    'closed_to_arrival',
                                    'closed_to_departure',
                                    'can_be_sold_online'
            );


            //day array
            $days = array(
                        "monday",
                        "tuesday",
                        "wednesday",
                        "thursday",
                        "friday",
                        "saturday",
                        "sunday"
            );
            
            if (count($dates)!=0) {
                foreach ($dates as $kd => $date)
                {
                    $date_start = $date['date_start'];
                    $date_end = $date['date_end'];
                    $mon = isset($date['adult_1_rate']) && $date['adult_1_rate'] ? $date['adult_1_rate'] : null;;
                    $date['rate_plan_id'] = $rate_plan_id;
                    $rate_array = $this->format_general_rate_plan($rate_data_variables, $date);

                    foreach ($rate_array as $key=>$value)
                    {
                        
                        $day_string = $value['days'];
                        unset($rate_array[$key]['days']);
                        $rate_data = array();
                        foreach ($rate_array[$key] as $k=>$val)
                        {
                            $apply_mon = $this->input->post($k . '_apply_change');
                            $apply_tue = $this->input->post($k . '_apply_change');
                            $apply_wed = $this->input->post($k . '_apply_change');
                            $apply_thu = $this->input->post($k . '_apply_change');
                            $apply_fri = $this->input->post($k . '_apply_change');
                            $apply_sat = $this->input->post($k . '_apply_change');
                            $apply_sun = $this->input->post($k . '_apply_change');


                            if($apply_mon==1 && $day_string == 'monday'){
                                $rate_data[$k] = 'null';
                            }elseif($apply_mon!=2 && $day_string == 'monday'){

                                if($val != 'null')
                                {
                                    $rate_data[$k] = $val;
                                }
                            }elseif($apply_tue==1 && $day_string == 'tuesday'){
                                $rate_data[$k] = 'null';
                            }elseif($apply_tue!=2 && $day_string == 'tuesday'){

                                if($val != 'null')
                                {
                                    $rate_data[$k] = $val;
                                }
                            }elseif($apply_wed==1 && $day_string == 'wednesday'){
                                $rate_data[$k] = 'null';
                            }elseif($apply_wed!=2 && $day_string == 'wednesday'){

                                if($val != 'null')
                                {
                                    $rate_data[$k] = $val;
                                }
                            }elseif($apply_thu==1 && $day_string == 'thursday'){
                                $rate_data[$k] = 'null';
                            }elseif($apply_thu!=2 && $day_string == 'thursday'){

                                if($val != 'null')
                                {
                                    $rate_data[$k] = $val;
                                }
                            }elseif($apply_fri==1 && $day_string == 'friday'){
                                $rate_data[$k] = 'null';
                            }elseif($apply_fri!=2 && $day_string == 'friday'){

                                if($val != 'null')
                                {
                                    $rate_data[$k] = $val;
                                }
                            }elseif($apply_sat==1 && $day_string == 'saturday'){
                                $rate_data[$k] = 'null';
                            }elseif($apply_sat!=2 && $day_string == 'saturday'){

                                if($val != 'null')
                                {
                                    $rate_data[$k] = $val;
                                }
                            }elseif($apply_sun==1 && $day_string == 'sunday'){
                                $rate_data[$k] = 'null';
                            }elseif($apply_sun!=2 && $day_string == 'sunday'){

                                if($val != 'null')
                                {
                                    $rate_data[$k] = $val;
                                }
                            }
                        }
                        if(count($rate_data) == 0){
                            continue;
                        }
                        $rate_data['rate_plan_id'] = $rate_plan_id;
                        $rate_id = $this->Rate_model->create_rate($rate_data);
                        
                        $date_data['date_start'] = $date_start;
                        $date_data['date_end'] = $date_end;
                        foreach ($days as $day) {

                            if (strpos($day_string, $day) !== false) {
                                $date_data[$day] = 1;
                            }
                            else{
                                $date_data[$day] = 0;
                            }

                        }
                        $date_range_id = $this->Date_range_model->create_date_range($date_data);

                        $this->Date_range_model->create_date_range_x_rate(
                            Array(
                                'date_range_id' => $date_range_id,
                                'rate_id' => $rate_id
                            )
                        );
                    }
                }
                // $this->_create_rate_plan_log("Rates created for ( Rate Plan [ID {$rate_plan_id}])");
                // $response['status'] = "success";
                // echo json_encode($response);
                // return;
                $this->response(array('status' => 'success'), 200);
            }
        }
        else
            $this->response(array('status' => false, 'error' => 'Rate plan id is not belongs to this company.'), 200);
    
    }
    function format_general_rate_plan($rate_data_variables, $rate){
        $rate_array = array();
        $mon_array = array();
        $tue_array = array();
        $wed_array = array();
        $thu_array = array();
        $fri_array = array();
        $sat_array = array();
        $sun_array = array();

        foreach ($rate_data_variables as $var)
        {
            if($var =='rate_plan_id')
            {
                $rate_data[$var] = $rate[$var];
            }
            else
            {
                $mon = isset($rate[$var]) && $rate[$var] ? $rate[$var] : null;
                $tue = isset($rate[$var]) && $rate[$var] ? $rate[$var] : null;
                $wed = isset($rate[$var]) && $rate[$var] ? $rate[$var] : null;
                $thu = isset($rate[$var]) && $rate[$var] ? $rate[$var] : null;
                $fri = isset($rate[$var]) && $rate[$var] ? $rate[$var] : null;
                $sat = isset($rate[$var]) && $rate[$var] ? $rate[$var] : null;
                $sun = isset($rate[$var]) && $rate[$var] ? $rate[$var] : null;

                $mon_array[$var] = $mon;
                $tue_array[$var] = $tue;
                $wed_array[$var] = $wed;
                $thu_array[$var] = $thu;
                $fri_array[$var] = $fri;
                $sat_array[$var] = $sat;
                $sun_array[$var] = $sun;
            }
        }

        $rate_array['monday'] = $mon_array;
        $rate_array['tuesday'] = $tue_array;
        $rate_array['wednesday'] = $wed_array;
        $rate_array['thursday'] = $thu_array;
        $rate_array['friday'] = $fri_array;
        $rate_array['saturday'] = $sat_array;
        $rate_array['sunday'] = $sun_array;

        $unique_array = array();
        foreach ($rate_array as $key=>$element)
        {
            if (in_array($element, $unique_array)) {
                //
            }
            else{
                $unique_array[$key] = $element;
                $unique_array[$key]['days'] = $key;
            }
        }
        return $unique_array;
    }

    function get_rate_plans_post()
    {
        $room_type_id = $this->post('room_type_id');
        $company_id = $this->post('company_id');

        if(!$room_type_id) {
            $this->response(array('status' => false, 'error' => 'Room type ID is missing.'), 200);
        }

        $rate_plans = $this->Rate_plan_model->get_rate_plans_by_room_type_id($room_type_id);

        $this->response($rate_plans, 200);
    }
}
