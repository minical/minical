<?php

/* add a  new rate in rates table.
* Supported hooks: 
* before_add_rates: the filter executed before add rate.
* should_add_rates: the filter executed to check add rate.
* pre.add.rates: the hook executed before add rate. 
* post.add.rates: the hook executed after added rate.
* @param array $rates (Required) includes following attributes:
* $data['rate_plan_id'] : the rate_plan_id (integer) of specific rate.
* $data['base_rate'] : the base_rate (integer) for specific rate.
* $data['adult_1_rate'] : the adult_1_rate (decimal integer) for specific rate.
* $data['adult_2_rate'] : the adult_2_rate (decimal integer) for specific rate.
* $data['adult_3_rate'] : the adult_3_rate (decimal integer) for specific rate.
* $data['adult_4_rate'] : the adult_4_rate (decimal integer) for specific rate.
* $data['additional_adult_rate'] : the additional_adult_rate (decimal integer) for specific rate.
* $data['minimum_length_of_stay'] : the minimum_length_of_stay (integer) for specific rate.
* $data['closed_to_arrival'] : the closed_to_arrival (integer) for specific rate.
* $data['additional_child_rate'] : the additional_child_rate (decimal integer) for specific rate.
* $data['maximum_length_of_stay'] : the maximum_length_of_stay (integer) for specific rate.
* $data['closed_to_departure'] : the closed_to_departure (integer) for specific rate.
* $data['can_be_sold_online'] : the can_be_sold_online (integer) for specific rate.
* and many more attributes for table rate.
* @return $response: array value of the rate data. A value of any type may be returned, If there  
   is no rates in the database, boolean false is returned
* $response array includes following attributes:
* $response['key'] : the key of specific rate.
*
*/

function add_rates($rates)
{

    $CI = & get_instance();
    $CI->load->model('Rate_model');
    
    $data = apply_filters( 'before_add_rates', $rates, $CI->input->post());
    $should_add_rates = apply_filters( 'should_add_rates', $rates, $CI->input->post());
    if (!$should_add_rates) {
        return;
    }
    if(empty($rates)){
        return null;
    }
    $rate_data = array(
        'rate_plan_id'=> $rates['rate_plan_id'],
        'base_rate'=> isset($rates['base_rate']) ? $rates['base_rate'] : '',
        'adult_1_rate'=> isset($rates['adult_1_rate']) ? $rates['adult_1_rate'] : '',
        'adult_2_rate'=> isset($rates['adult_2_rate']) ? $rates['adult_2_rate'] : '',
        'adult_3_rate'=> isset($rates['adult_3_rate']) ? $rates['adult_3_rate'] : '',
        'adult_4_rate'=> isset($rates['adult_4_rate']) ? $rates['adult_4_rate'] : '',
        'additional_adult_rate'=> isset($rates['additional_adult_rate']) ? $rates['additional_adult_rate'] : '',
        'additional_child_rate'=> isset($rates['additional_child_rate']) ? $rates['additional_child_rate'] :'' ,
        'minimum_length_of_stay'=> isset($rates['minimum_length_of_stay']) ? $rates['minimum_length_of_stay'] : '',
        'maximum_length_of_stay'=> isset($rates['maximum_length_of_stay']) ? $rates['maximum_length_of_stay'] : '' ,
        'minimum_length_of_stay_arrival'=> isset($rates['minimum_length_of_stay_arrival']) ? $rates['minimum_length_of_stay_arrival'] : '',
        'maximum_length_of_stay_arrival'=> isset($rates['maximum_length_of_stay_arrival']) ? $rates['maximum_length_of_stay_arrival'] : '' ,
        'closed_to_arrival'=> isset($rates['closed_to_arrival']) ? $rates['closed_to_arrival'] : '' ,
        'closed_to_departure'=> isset($rates['closed_to_departure']) ? $rates['closed_to_departure'] : '',
        'can_be_sold_online'=> isset($rates['can_be_sold_online']) ? $rates['can_be_sold_online'] : ''
    );
    // before add rates
    do_action('pre.add.rates', $rate_data, $CI->input->post());
    
    $rate_id = $CI->Rate_model->create_rate($rate_data);
    //after added rates
    do_action('post.add.rates', $rate_id, $rate_data, $CI->input->post());

    if(isset($rate_id)){

        return $rate_id;
    }
    return null;
}


/* Retrieves a rate value based on a rate_id.
* Supported hooks:
* before_get_rate: the filter executed before get rate.
* should_get_rate: the filter executed to check get rate.
* pre.get.rate: the hook executed before getting rate. 
* post.get.rate: the hook executed after getting rate.
* @param string $rate_id (Required) The primary id for rate table
*
* @return $response: array value of the rate data. A value of any type may be returned, If there  
   is no rate in the database, boolean false is returned
* $response array includes following attributes:
* $response['rate_plan_id'] : the rate_plan_id of specific rate.
* $response['base_rate'] : the base_rate for specific rate.
* $response['adult_1_rate'] : the adult_1_rate for specific rate.
* $response['adult_2_rate'] : the adult_2_rate for specific rate.
* $response['adult_3_rate'] : the adult_3_rate for specific rate.
* $response['adult_4_rate'] : the adult_4_rate for specific rate.
* $response['additional_adult_rate'] : the additional_adult_rate for specific rate.
* $response['minimum_length_of_stay'] : the minimum_length_of_stay for specific rate.
* $response['closed_to_arrival'] : the closed_to_arrival  for specific rate.
* $response['closed_to_departure'] : the closed_to_departure for specific rate.
* and many more attributes for table rate with rate table.
*/
function get_rate(int $rate_id = null )
{
    $get_rate_data = null;
    $CI = & get_instance();
    $CI->load->model('Rate_model');

    // filters
    $data = apply_filters( 'before_get_rate', $rate_id, $CI->input->post());
    $should_get_rate = apply_filters( 'should_get_rate', $rate_id, $CI->input->post());

    if (!$should_get_rate) {
        return;
    }

    if(isset($rate_id) && $rate_id == null){
        return null;
    }

    // before getting rate 
    do_action('pre.get.rate', $rate_id, $CI->input->post());

    $get_rate_data = $CI->Rate_model->get_rate_by_rate_id($rate_id);

    // after getting rate
    do_action('post.get.rate', $rate_id, $CI->input->post());
     
    return $get_rate_data;

}

/* Retrieves a rate value based on a filter.
* Supported hooks:
* before_get_rates: the filter executed before get rate.
* should_get_rates: the filter executed to check get rate.
* pre.get.rates: the hook executed before getting rate. 
* post.get.rates: the hook executed after getting rate.
* @param array $filter (Required) The data for rate table
* you can filter data base on rate id or rate plan id.
* $response array includes following attributes:
* $response['rate_plan_id'] : the rate_plan_id of specific rate.
* $response['base_rate'] : the base_rate for specific rate.
* $response['adult_1_rate'] : the adult_1_rate for specific rate.
* $response['adult_2_rate'] : the adult_2_rate for specific rate.
* $response['adult_3_rate'] : the adult_3_rate for specific rate.
* $response['adult_4_rate'] : the adult_4_rate for specific rate.
* $response['additional_adult_rate'] : the additional_adult_rate for specific rate.
* $response['minimum_length_of_stay'] : the minimum_length_of_stay for specific rate.
* $response['closed_to_arrival'] : the closed_to_arrival  for specific rate.
* $response['closed_to_departure'] : the closed_to_departure for specific rate.
* and many more attributes for table rate.
*/
function get_rates(array $filter = null )
{
    $get_rate_data = null;
    $CI = & get_instance();
    $CI->load->model('Rate_model');

    // filters
    $data = apply_filters( 'before_get_rates', $filter, $CI->input->post());
    $should_get_rate = apply_filters( 'should_get_rates', $filter, $CI->input->post());

    if (!$should_get_rate) {
        return;
    }

    if(empty($filter)){
        return null;
    }

    // before getting rate 
    do_action('pre.get.rates', $filter, $CI->input->post());

    $get_rate_data = $CI->Rate_model->get_rates($filter);

    // after getting rate
    do_action('post.get.rates', $filter, $CI->input->post());

    return $get_rate_data;

}


/* update a rate in rates table.
* Supported hooks:
* before_update_rates: the filter executed before update rate.
* should_update_rates: the filter executed to check update rate.
* pre.update.rates: the hook executed before update rate. 
* post.update.rates: the hook executed after update rate.
* @param array $rate (Required) includes following attributes:
* @param int $rate_id The id of the rate  corresponds to the rate data.
* $data['rate_plan_id'] : the rate_plan_id (integer) of specific rate.
* $data['base_rate'] : the base_rate (integer) for specific rate.
* $data['adult_1_rate'] : the adult_1_rate (decimal integer) for specific rate.
* $data['adult_2_rate'] : the adult_2_rate (decimal integer) for specific rate.
* $data['adult_3_rate'] : the adult_3_rate (decimal integer) for specific rate.
* $data['adult_4_rate'] : the adult_4_rate (decimal integer) for specific rate.
* $data['additional_adult_rate'] : the additional_adult_rate (decimal integer) for specific rate.
* $data['minimum_length_of_stay'] : the minimum_length_of_stay (integer) for specific rate.
* $data['closed_to_arrival'] : the closed_to_arrival (integer) for specific rate.
* $data['additional_child_rate'] : the additional_child_rate (decimal integer) for specific rate.
* $data['maximum_length_of_stay'] : the maximum_length_of_stay (integer) for specific rate.
* $data['closed_to_departure'] : the closed_to_departure (integer) for specific rate.
* $data['can_be_sold_online'] : the can_be_sold_online (integer) for specific rate.
* and many more attributes for table rate.
* @return mixed Either true or null, if rate data is updated then true else null.
*
*/
function update_rates(array $rates = null, int $rate_id = null)
{
    $updated_flag = null;
    $get_rate_data = null;
    $CI = & get_instance();
    $CI->load->model('Rate_model');

    //filters
    $data = apply_filters( 'before_update_rates', $rate_id, $CI->input->post());
    $should_update_rates = apply_filters( 'should_update_rates', $rate_id, $CI->input->post());

    if (!$should_update_rates) {
        return;
    }
    if(empty($rates) && $rate_id == null){
        return null;
    }
    
    $get_rate_data = $CI->Rate_model->get_rate_by_rate_id($rate_id);

    $rate_data = array(
        'rate_plan_id'=> $get_rate_data['rate_plan_id'],

        'base_rate'=> isset($rates['base_rate']) ? $rates['base_rate'] : $get_rate_data['base_rate'],

        'adult_1_rate'=> isset($rates['adult_1_rate']) ? $rates['adult_1_rate'] : $get_rate_data['adult_1_rate'],

        'adult_2_rate'=> isset($rates['adult_2_rate']) ? $rates['adult_2_rate'] : $get_rate_data['adult_2_rate'],

        'adult_3_rate'=> isset($rates['adult_3_rate']) ? $rates['adult_3_rate'] : $get_rate_data['adult_3_rate'],

        'adult_4_rate'=> isset($rates['adult_4_rate']) ? $rates['adult_4_rate'] : $get_rate_data['adult_4_rate'],

        'additional_adult_rate'=> isset($rates['additional_adult_rate']) ? $rates['additional_adult_rate'] : $get_rate_data['additional_adult_rate'],

        'additional_child_rate'=> isset($rates['additional_child_rate']) ? $rates['additional_child_rate'] :$get_rate_data['additional_child_rate'] ,

        'minimum_length_of_stay'=> isset($rates['minimum_length_of_stay']) ? $rates['minimum_length_of_stay'] : $get_rate_data['minimum_length_of_stay'],

        'maximum_length_of_stay'=> isset($rates['maximum_length_of_stay']) ? $rates['maximum_length_of_stay'] : $get_rate_data['maximum_length_of_stay'] ,

        'minimum_length_of_stay_arrival'=> isset($rates['minimum_length_of_stay_arrival']) ? $rates['minimum_length_of_stay_arrival'] : $get_rate_data['minimum_length_of_stay_arrival'],

        'maximum_length_of_stay_arrival'=> isset($rates['maximum_length_of_stay_arrival']) ? $rates['maximum_length_of_stay_arrival'] : $get_rate_data['maximum_length_of_stay_arrival'] ,

        'closed_to_arrival'=> isset($rates['closed_to_arrival']) ? $rates['closed_to_arrival'] : $get_rate_data['closed_to_arrival'] ,

        'closed_to_departure'=> isset($rates['closed_to_departure']) ? $rates['closed_to_departure'] : $get_rate_data['closed_to_departure'],
        
        'can_be_sold_online'=> isset($rates['can_be_sold_online']) ? $rates['can_be_sold_online'] : $get_rate_data['can_be_sold_online']
    );
   
    // before updating rates 
    do_action('pre.update.rates', $rate_data, $CI->input->post());
    
    $updated_flag = $CI->Rate_model->update_rate($rate_data,$rate_id);
    if(empty($updated_flag)){
        return null;
    }
    // before updating rates 
    do_action('post.update.rates', $rate_id, $rate_data, $CI->input->post());
     
    return true;
}


/**
 * delete a rate data.
 * Supported hooks:
 * before_delete_rate: the filter executed before delete rate.
 * should_delete_rate: the filter executed to check delete rate.
 * pre.delete.rate: the hook executed before delete rate. 
 * post.delete.rate: the hook executed after delete rate.
 * @param int $rate_id The id of the rate corresponds to the rate table.
 * @return mixed Either true or null, if rate data is deleted then true else null.
 * 
 */
function delete_rate(int $rate_id = null)
{ 
    $CI = & get_instance();
    $CI->load->model('Rate_model');

    // filters 
    $data = apply_filters( 'before_delete_rate', $rate_id, $CI->input->post());
    $should_delete_rate = apply_filters( 'should_delete_rate', $rate_id, $CI->input->post());
    if (!$should_delete_rate) {
        return;
    }

    if(isset($rate_id) && $rate_id == null){
        return null;
    }

    //for action befor deleting the rate
    do_action('pre.delete.rate', $rate_id, $CI->input->post()); 
  
    $delete_flag = $CI->Rate_model->delete_rates($rate_id);
    if(empty($delete_flag)){
        return null;
    }
    
    //for action after deleting the rate
    do_action('post.delete.rate', $rate_id, $CI->input->post());
  
    return true;
}