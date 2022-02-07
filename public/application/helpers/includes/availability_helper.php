<?php
/* Retrieves an availability value of room type based on a filter.
* Supported hooks:
* before_get_availability: the filter executed before get availability
* should_get_availability: the filter executed to check get availability.
* pre.get.availability: the hook executed before getting availability. 
* post.get.availability: the hook executed after getting availability
* @param array $filter (Required) The data for availability
* you can filter data base on company id ,ota_id ,start_date , end_date and company_access_key.
* $filter['company_id'] : the company_id of specific company(must required).
* $filter['ota_id']: the ota_id of any specific OTA type (must required).
* $filter['start_date']: the start_date of any specific availability (must required).
* $filter['end_date'] : the end_date of any specific availability (must required).
* $filter['adult_count']: the adult_count of any specific availability.
* $filter['children_count'] : the children_count of any specific availability.
* $filter['filter_can_be_sold_online']: the filter_can_be_sold_online of any specific availability.
* $filter['company_group_id']: the company_group_id of any specific availability.
* $filter['get_max_availability']:the get_max_availability of any specific availability.
* $filter['get_inventorysold']:the get_inventorysold of any specific availability.
* $filter['get_closeout_status']:the get_closeout_status of any specific availability.
* $filter['get_inventory']:the get_inventory of any specific availability.
* $filter['company_access_key']:the company_access_key of any specific availability.
* $filter['ota_key']:the ota_key of any specific availability.
* @return $response: array value of the availability data. A value of any type may be returned, If there
   is no availability in the database, boolean false is returned
* $response array includes multiple array of availability for room type have following attributes:
* $response['id'] : the id of specific room types.
* $response['company_id'] : the company_id for specific room types.
* $response['rate_plan_count'] : the rate_plan_count for specific room types.
* $response['max_occupancy'] : the max_occupancy for specific room types.
* $response['max_adult'] : the max_adult for specific room types.
* $response['max_children'] : the max_children for specific room types.
* $response['default_room_charge'] : the default_room_charge for specific room types.
* $response['date_start'] : the date_start for specific room availability.
* $response['date_end'] : the date_end for specific room availability.
* $response['availability'] : the availability for specific room availability.
* $response['max_availability'] : the max_availability for specific room availability.
* $response['max_availability'] : the name for specific room availability.
* $response['inventory_sold'] : the inventory_sold for specific room availability.
* and many more attributes from availability and join with room , room type table.
*/

function get_availability(array $filter = null)
{
    $get_availability_data = null;
    $CI = & get_instance();
    $CI->load->model('Room_type_model');
    $CI->load->model('Company_model');

    // filters
    $data = apply_filters( 'before_get_availability', $filter, $CI->input->post());
    $should_get_availability = apply_filters( 'should_availability', $filter, $CI->input->post());

    if (!$should_get_availability) {
        return;
    }

    if(empty($filter)){
        return null;
    }
    $company_id = isset($filter['company_id']) ? $filter['company_id'] : null ; 

    $ota_id = isset($filter['ota_id']) ? $filter['ota_id'] : null;

    $start_date = isset($filter['start_date']) ? $filter['start_date'] : null ;

    $end_date = isset($filter['end_date']) ? $filter['end_date'] : null ;

    $adult_count = isset($filter['adult_count']) ?  $filter['adult_count'] : null; 

    $children_count = isset($filter['children_count']) ? $filter['children_count'] : null;

    $filter_can_be_sold_online = isset($filter['filter_can_be_sold_online']) ? $filter['filter_can_be_sold_online'] : true;

    $company_group_id = isset($filter['company_group_id']) ? $filter['company_group_id'] : null; 

    $get_max_availability = isset($filter['get_max_availability']) ? $filter['get_max_availability'] : true; 
    $get_inventorysold = isset($filter['get_inventorysold']) ? $filter['get_inventorysold'] : true; 

    $get_closeout_status = isset($filter['get_closeout_status']) ? $filter['get_closeout_status'] : true; 
    $get_inventory = isset($filter['get_inventory']) ? $filter['get_inventory'] : true;

    $company_key_data = $CI->Company_model->get_company_api_permission($company_id);
    $company_access_key = isset($company_key_data[0]['key']) && $company_key_data[0]['key'] ? $company_key_data[0]['key'] : null;
 
    $ota_key = isset($filter['ota_key']) ? $filter['ota_key'] : null;

    // before getting availability 
    do_action('pre.get.availability', $filter, $CI->input->post());

    $get_availability_data = $CI->Room_type_model->get_room_type_availability($company_id, $ota_id, $start_date, $end_date, $adult_count , $children_count , $filter_can_be_sold_online , $company_group_id , $get_max_availability , $get_inventorysold , $get_closeout_status , $get_inventory, $company_access_key, $ota_key);

    // after getting availability
    do_action('post.get.availability',$filter, $CI->input->post());
     
    return $get_availability_data;

}