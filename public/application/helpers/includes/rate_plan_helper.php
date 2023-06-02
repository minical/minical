<?php

/* add a new rate_plan in rate_plan table.
* Supported hooks: 
* before_add_rate_plan: the filter executed before add rate plan.
* should_add_rate_plan: the filter executed to check add rate plan.
* pre.add.rate_plan: the hook executed before add rate_plan. 
* post.add.rate_plan: the hook executed after added rate_plan.
* @param array $rate_plan (Required) includes following attributes:
* $data['rate_plan_name'] : the rate_plan_name (character) of specific rate_plan.
* $data['room_type_id'] : the room_type_id (integer) for specific rate_plan.
* $data['charge_type_id'] : the charge_type_id (integer) for specific rate_plan.
* $data['description'] : the description (text) for specific rate_plan.
* $data['company_id'] : the company_id (integer) for specific rate_plan.
* $data['base_rate_id'] : the base_rate_id (integer) for specific rate_plan.
* $data['parent_rate_plan_id'] : the parent_rate_plan_id (integer) for specific rate_plan.
* $data['policy_code'] : the policy_code (character) for specific rate_plan.
* $data['date_time'] : the date_time (date_time )for specific rate_plan.
* $data['is_selectable'] : the is_selectable (integer) for specific rate_plan.
* $data['number_of_adults_included_for_base_rate'] : the number_of_adults_included_for_base_rate (integer) for specific rate_plan.
* and many more attributes for table rate plan.
* @return $response: array value of the rate_plan data. A value of any type may be returned, If there  
   is no rate_plan in the database, boolean false is returned
* $response array includes following attributes:
* $response['key'] : the key of specific rate_plan
*
*/

function add_rate_plan($rate_plan)
{

    $CI = & get_instance();
    $CI->load->model('Rate_plan_model');
    $company_id = $CI->session->userdata('current_company_id');
    //echo $company_id; die();
    $data = apply_filters( 'before_add_rate_plan', $rate_plan, $CI->input->post());
    $should_add_rate_plan = apply_filters( 'should_add_rate_plan', $rate_plan, $CI->input->post());
    if (!$should_add_rate_plan) {
        return;
    }
    if(empty($rate_plan)){
        return null;
    }
    $rate_plan_data = array(
        'rate_plan_name'=> $rate_plan['rate_plan_name'],

        'room_type_id'=> isset($rate_plan['room_type_id']) ? $rate_plan['room_type_id'] : '',

        'charge_type_id '=> isset($rate_plan['charge_type_id']) ? $rate_plan['charge_type_id'] : '',

        'description'=> isset($rate_plan['description']) ? $rate_plan['description'] : '',

        'number_of_adults_included_for_base_rate'=> isset($rate_plan['number_of_adults_included_for_base_rate']) ? $rate_plan['number_of_adults_included_for_base_rate'] : 4,

        'currency_id '=> isset($rate_plan['currency_id']) ? $rate_plan['currency_id'] : '',

        'base_rate_id'=> isset($rate_plan['base_rate_id']) ? $rate_plan['base_rate_id'] : '',

        'is_selectable'=> isset($rate_plan['is_selectable']) ? $rate_plan['is_selectable'] : 1,

        'is_shown_in_online_booking_engine'=> isset($rate_plan['is_shown_in_online_booking_engine']) ?
        $rate_plan['is_shown_in_online_booking_engine'] : 1,

        'company_id'=> isset($rate_plan['company_id']) ? $rate_plan['company_id'] : $company_id ,

        'parent_rate_plan_id'=> isset($rate_plan['parent_rate_plan_id']) ? $rate_plan['parent_rate_plan_id'] : '',

        'policy_code'=> isset($rate_plan['policy_code']) ? $rate_plan['policy_code'] : '',
        'date_time'=> gmdate('Y-m-d H:i:s')
    );

    // before add rate_plan
    do_action('pre.add.rate_plan', $rate_plan_data, $CI->input->post());
    
    $rate_plan_id = $CI->Rate_plan_model->create_rate_plan($rate_plan_data);
    //after added rate_plan
    do_action('post.add.rate_plan', $rate_plan_id, $rate_plan_data, $CI->input->post());

    if(isset($rate_plan_id)){

        return $rate_plan_id;
    }
    return null;
}


/* Retrieves a rate_plan value based on a rate_plan_id.
* Supported hooks:
* before_get_rate_plan: the filter executed before get rate plan
* should_get_rate_plan: the filter executed to check get rate plan
* pre.get.rate_plan: the hook executed before getting rate_plan. 
* post.get.rate_plan: the hook executed after getting rate_plan
* @param string $rate_plan_id (Required) The primary id for rate plan table
*
* @return $response: array value of the rate_plan data. A value of any type may be returned, If there  
   is no rate_plan in the database, boolean false is returned
* $response array includes following attributes:
* $response['key'] : the key of specific rate_plan
* $response['rate_plan_name'] : the rate_plan_name of specific rate_plan.
* $response['room_type_id'] : the room_type_id for specific rate_plan.
* $response['charge_type_id'] : the charge_type_id for specific rate_plan.
* $response['description'] : the description for specific rate_plan.
* $response['company_id'] : the company_id for specific rate_plan.
* $response['number_of_adults_included_for_base_rate'] : the number_of_adults_included_for_base_rate
   for specific rate_plan.
* and many more attributes for table rate plan and join with rate table.
*/

function get_rate_plan(int $rate_plan_id = null )
{
    $get_rate_plan_data = null;
    $CI = & get_instance();
    $CI->load->model('Rate_plan_model');

    // filters
    $data = apply_filters( 'before_get_rate_plan', $rate_plan_id, $CI->input->post());
    $should_get_rate_plan = apply_filters( 'should_get_rate_plan', $rate_plan_id, $CI->input->post());

    if (!$should_get_rate_plan) {
        return;
    }

    if(isset($rate_plan_id) && $rate_plan_id == null){
        return null;
    }

    // before getting rate_plan 
    do_action('pre.get.rate_plan', $rate_plan_id, $CI->input->post());

    $get_rate_plan_data = $CI->Rate_plan_model->get_rate_plan($rate_plan_id);

    // after getting rate_plan
    do_action('post.get.rate_plan', $rate_plan_id, $CI->input->post());
     
    return $get_rate_plan_data;

}


/* Retrieves a rate_plan value based on a filter.
* Supported hooks:
* before_get_rate_plans: the filter executed before get rate_plan
* should_get_rate_plans: the filter executed to check get rate_plan.
* pre.get.rate_plans: the hook executed before getting rate_plan. 
* post.get.rate_plans: the hook executed after getting rate_plan
* @param array $filter (Required) The data for rate_plan table
*
* @return $response: array value of the rate_plan data. A value of any type may be returned, If there  
   is no rate_plan in the database, boolean false is returned
* $response array includes following attributes:
* $response['key'] : the key of specific rate_plan
* $response['rate_plan_name'] : the rate_plan_name of specific rate_plan.
* $response['room_type_id'] : the room_type_id for specific rate_plan.
* $response['charge_type_id'] : the charge_type_id for specific rate_plan.
* $response['description'] : the description for specific rate_plan.
* $response['company_id'] : the company_id for specific rate_plan.
* $response['number_of_adults_included_for_base_rate'] : the number_of_adults_included_for_base_rate for specific rate_plan.
* and many more attributes from table rate_plan.
*/

function get_rate_plans(array $filter = null )
{
    $get_rate_plan_data = null;
    $CI = & get_instance();
    $CI->load->model('Rate_plan_model');

    // filters
    $data = apply_filters( 'before_get_rate_plans', $filter, $CI->input->post());
    $should_get_rate_plan = apply_filters( 'should_get_rate_plans', $filter, $CI->input->post());

    if (!$should_get_rate_plan) {
        return;
    }

    if(empty($filter)){
        return null;
    }

    // before getting rate_plan 
    do_action('pre.get.rate_plans', $filter, $CI->input->post());

    $get_rate_plan_data = $CI->Rate_plan_model->get_rate_plans_data($filter);

    // after getting rate_plan
    do_action('post.get.rate_plans', $filter, $CI->input->post());

    return $get_rate_plan_data;

}

/* update a rate plan details in rate_plan table.
* Supported hooks:
* before_update_rate_plan: the filter executed before update rate_plan
* should_update_rate_plan: the filter executed to check update rate_plan.
* pre.update.rate_plan: the hook executed before update rate_plan. 
* post.update.rate_plan: the hook executed after update rate_plan.
* @param array $rate_plan (Required) includes following attributes:
* @param int $rate_plan_id The id of the rate plan corresponds to the rate_plan data.
* $data['rate_plan_name'] : the rate_plan_name (character) of specific rate_plan.
* $data['room_type_id'] : the room_type_id (integer) for specific rate_plan.
* $data['charge_type_id'] : the charge_type_id (integer) for specific rate_plan.
* $data['description'] : the description (text) for specific rate_plan.
* $data['company_id'] : the company_id (integer) for specific rate_plan.
* $data['base_rate_id'] : the base_rate_id (integer) for specific rate_plan.
* $data['parent_rate_plan_id'] : the parent_rate_plan_id (integer) for specific rate_plan.
* $data['policy_code'] : the policy_code (character) for specific rate_plan.
* $data['date_time'] : the date_time (date_time )for specific rate_plan.
* $data['is_selectable'] : the is_selectable (integer) for specific rate_plan.
* $data['number_of_adults_included_for_base_rate'] : the number_of_adults_included_for_base_rate (integer) for specific rate_plan.
* and many more attributes for table rate_plan.
* @return mixed Either true or null, if rate_plan data is updated then true else null.
*
*/

function update_rate_plan(array $rate_plan = null, int $rate_plan_id = null)
{
    $updated_flag = null;
    $CI = & get_instance();
    $CI->load->model('Rate_plan_model');

    //filters
    $data = apply_filters( 'before_update_rate_plan', $rate_plan_id, $CI->input->post());
    $should_update_rate_plan = apply_filters( 'should_update_rate_plan', $rate_plan_id, $CI->input->post());

    if (!$should_update_rate_plan) {
        return;
    }

    if(empty($rate_plan) && $rate_plan_id == null){
        return null;
    }

    $get_rate_plan_data = $CI->Rate_plan_model->get_rate_plan($rate_plan_id);

    $rate_plan_data = array(
        'rate_plan_name'=> isset($rate_plan['rate_plan_name']) ? $rate_plan['rate_plan_name'] : $get_rate_plan_data['rate_plan_name'],

        'room_type_id'=> isset($rate_plan['room_type_id']) ? $rate_plan['room_type_id'] : $get_rate_plan_data['room_type_id'],

        'charge_type_id '=> isset($rate_plan['charge_type_id']) ? $rate_plan['charge_type_id'] : $get_rate_plan_data['charge_type_id'],

        'description'=> isset($rate_plan['description']) ? $rate_plan['description'] : $get_rate_plan_data['description'],

        'number_of_adults_included_for_base_rate'=> isset($rate_plan['number_of_adults_included_for_base_rate']) ? $rate_plan['number_of_adults_included_for_base_rate'] : $get_rate_plan_data['number_of_adults_included_for_base_rate'],

        'currency_id '=> isset($rate_plan['currency_id']) ? $rate_plan['currency_id'] : $get_rate_plan_data['currency_id'],

        'base_rate_id'=> isset($rate_plan['base_rate_id']) ? $rate_plan['base_rate_id'] : $get_rate_plan_data['base_rate_id'],

        'is_selectable'=> isset($rate_plan['is_selectable']) ? $rate_plan['is_selectable'] : $get_rate_plan_data['is_selectable'],

        'is_shown_in_online_booking_engine'=> isset($rate_plan['is_shown_in_online_booking_engine']) ?
        $rate_plan['is_shown_in_online_booking_engine'] : $get_rate_plan_data['is_shown_in_online_booking_engine'],

        'company_id'=> isset($rate_plan['company_id']) ? $rate_plan['company_id'] : $get_rate_plan_data['company_id'],

        'parent_rate_plan_id'=> isset($rate_plan['parent_rate_plan_id']) ? $rate_plan['parent_rate_plan_id'] : $get_rate_plan_data['parent_rate_plan_id'],
        
        'policy_code'=> isset($rate_plan['policy_code']) ? $rate_plan['policy_code'] : $get_rate_plan_data['policy_code'],

        'date_time'=> gmdate('Y-m-d H:i:s')
    );
   
    // before updating rate_plan 
    do_action('pre.update.rate_plan', $rate_plan_data, $CI->input->post());
    
    $updated_flag = $CI->Rate_plan_model->update_rate_plan($rate_plan_data,$rate_plan_id);
    if(empty($updated_flag)){
        return null;
    }
    // before updating rate_plan 
    do_action('post.update.rate_plan', $rate_plan_id, $rate_plan_data, $CI->input->post());
     
    return true;
}


/**
 * delete a rate plan data.
 * Supported hooks:
 * before_delete_rate_plan: the filter executed before delete rate_plan.
 * should_delete_rate_plan: the filter executed to check delete rate_plan.
 * pre.delete.rate_plan: the hook executed before delete rate_plan. 
 * post.delete.rate_plan: the hook executed after delete rate_plan.
 * @param int $rate_plan_id The id of the rate_plan corresponds to the rate_plan table.
 * @return mixed Either true or null, if rate_plan data is deleted then true else null.
 * 
 */
function delete_rate_plan(int $rate_plan_id = null)
{ 
    $CI = & get_instance();
    $CI->load->model('Rate_plan_model');

    // filters 
    $data = apply_filters( 'before_delete_rate_plan', $rate_plan_id, $CI->input->post());
    $should_delete_rate_plan = apply_filters( 'should_delete_rate_plan', $rate_plan_id, $CI->input->post());
    if (!$should_delete_rate_plan) {
        return;
    }

    if(isset($rate_plan_id) && $rate_plan_id == null){
        return null;
    }

    //for action befor deleting the rate_plan
    do_action('pre.delete.rate_plan', $rate_plan_id, $CI->input->post()); 
  
    $delete_flag = $CI->Rate_plan_model->delete_rate_plan($rate_plan_id);
    if(empty($delete_flag)){
        return null;
    }
    
    //for action after deleting the rate_plan
    do_action('post.delete.rate_plan', $rate_plan_id, $CI->input->post());
  
    return true;
}