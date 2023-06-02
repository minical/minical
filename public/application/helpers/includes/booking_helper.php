<?php
/* add a new booking in booking table also add booking details in booking block table for check in 
  checkout dates.
* Supported hooks:
* before_add_booking: the filter executed before add booking
* should_add_booking: the filter executed to check add booking.
* pre.add.booking: the hook executed before add booking. 
* post.create.booking: the hook executed after added booking.
* post.add.booking: the hook executed after added booking.
* @param array $booking (Required) includes following attributes:
* $data['room_id'] : the room_id (integer) for specific booking block (must required).
* $data['check_in_date'] : the check_in_date for specific booking block (must required date in gmdate()
   format).
* $data['check_out_date'] : the check_out_date for specific booking block(must required date in gmdate()
   format).
* $data['booking_type'] : the booking_type of specific booking.
* $data['booking_from'] : the booking_from of specific booking.
* $data['rate'] : the rate (integer) of specific booking.
* $data['adult_count'] : the adult_count (integer) for specific booking (must required).
* $data['children_count'] : the children_count (integer) for specific booking.
* $data['company_id'] : the company_id (integer) for specific booking.
* $data['state'] : the state (integer) for specific booking (must required).
* $data['booking_notes'] : the booking_notes (text) for specific booking.
* $data['booking_customer_id'] : the booking_customer_id (integer) for specific booking.
* $data['booked_by'] : the booked_by for specific booking.
* $data['balance'] : the balance (integer) for specific booking.
* $data['use_rate_plan'] : the use_rate_plan (integer) for specific booking (must required).
* $data['rate_plan_id'] : the rate_plan_id (integer) for specific booking.
* $data['charge_type_id'] : the charge_type_id (integer) for specific booking.
* $data['source'] : the source for specific booking.
* $data['is_ota_booking'] : the is_ota_booking (integer) for specific booking.
* $data['pay_period'] : the pay_period (integer) for specific booking.
* $data['room_type_id'] : the room_type_id (integer) for specific booking block.
* @return $response: array value of the booking data. A value of any type may be returned, If there  
   is no booking in the database, boolean false is returned
* $response array includes following attributes:
* $response['key'] : the key of specific booking
*
*/
function add_booking($booking)
{

    $CI = & get_instance();
    $CI->load->model('Booking_model');
    $CI->load->model('Booking_room_history_model');
    $CI->load->model('Invoice_model');
    $CI->load->library('session');

    if(empty($booking)){
        return null;
    }
    if (isset($booking['company_id'])) {
       $company_id = $booking['company_id'];
    }else{
       $company_id = $CI->session->userdata('current_company_id');
    }
    $data = apply_filters( 'before_add_booking', $booking, $CI->input->post());
    $should_add_booking = apply_filters( 'should_add_booking', $booking, $CI->input->post());
    if (!$should_add_booking) {
        return;
    }

    $booking_data = Array(
        "rate" => isset($booking['rate']) ? $booking['rate'] : null,
        "adult_count" => isset($booking['adult_count']) ? $booking['adult_count'] : null ,
        "children_count" => isset($booking['children_count']) ? $booking['children_count'] : null,
        "state" => isset($booking['state']) ?  $booking['state'] : 0,
        "booking_notes" => isset($booking['booking_notes']) ? $booking['booking_notes'] : null,
        "company_id" => $company_id ,
        "booking_customer_id" => isset($booking['booking_customer_id']) ? $booking['booking_customer_id'] : null,
        "booked_by" => isset($booking['booked_by']) ? $booking['booked_by'] : 0,
        "balance" => isset($booking['balance']) ? $booking['balance'] : 0.00,
        "use_rate_plan" =>isset($booking['use_rate_plan']) ? $booking['use_rate_plan'] : 0,
        "rate_plan_id" => isset($booking['rate_plan_id']) ? $booking['rate_plan_id'] : 0,
        "housekeeping_notes" => isset($booking['housekeeping_notes']) ? $booking['housekeeping_notes'] : null,
        "charge_type_id" => isset($booking['charge_type_id']) ? $booking['charge_type_id'] : 0,
        "source" => isset($booking['source']) ? $booking['source'] : 0,
        "is_ota_booking" => isset($booking['is_ota_booking']) ? $booking['is_ota_booking'] : 0,
        "pay_period" => isset($booking['pay_period']) ? $booking['pay_period'] : 0,
        "revenue" => isset($booking['revenue']) ? $booking['revenue'] : 0,
        "add_daily_charge" => isset($booking['add_daily_charge']) ? $booking['add_daily_charge'] : 0,
        "residual_rate" => isset($booking['residual_rate']) ? $booking['residual_rate'] : null,
        "is_invoice_auto_sent" => isset($booking['is_invoice_auto_sent']) ? $booking['is_invoice_auto_sent'] : 0
    );
    
    do_action('pre.add.booking', $booking_data, $CI->input->post());
    
    $booking_id = $CI->Booking_model->create_booking($booking_data);

    $booking_data['booking_id'] = $booking_id;
    $booking_data['booking_type'] = isset($booking['booking_type']) ? $booking['booking_type'] : 0;
    $booking_data['booking_from'] =isset($booking['booking_from']) ? $booking['booking_from'] : 0;

    do_action('post.create.booking', $booking_data, $CI->input->post());

    do_action('post.add.booking', $booking_data, $CI->input->post());

    $CI->Invoice_model->create_invoice($booking_id);

    $booking_room_history = array(
            "booking_id" => $booking_id,
            "check_in_date" => isset($booking['check_in_date']) ? $booking['check_in_date'] : gmdate("Y-m-d H:i:s"),
            "check_out_date" => isset($booking['check_out_date']) ? $booking['check_out_date'] : gmdate("Y-m-d H:i:s", strtotime(gmdate('Y-m-d')."+2 day")),
            "room_id" => $booking['room_id'],
            "room_type_id" => isset($booking['room_type_id']) ? $booking['room_type_id'] : null
            );
    $CI->Booking_room_history_model->create_booking_room_history($booking_room_history);


    if(isset($booking_id)){
        return $booking_id;
    }  

    return null; 
}


/* Retrieves a booking value based on a booking_id.
* Supported hooks:
* before_get_booking: the filter executed before get booking
* should_get_booking: the filter executed to check get booking.
* pre.get.booking: the hook executed before get booking. 
* post.get.booking: the hook executed after get booking.
* @param int $booking_id (Required) The primary id for booking table
*
* @return $response: array value of the booking data. A value of any type may be returned, If there  
   is no booking in the database, boolean false is returned
* $response array includes following attributes:
* $response['booking_type'] : the booking_type of specific booking.
* $response['booking_from'] : the booking_from of specific booking.
* $response['rate'] : the rate (integer) of specific booking.
* $response['adult_count'] : the adult_count (integer) for specific booking (must required).
* $response['children_count'] : the children_count (integer) for specific booking.
* $response['company_id'] : the company_id (integer) for specific booking.
* $response['state'] : the state (integer) for specific booking (must required).
* $response['booking_notes'] : the booking_notes for specific booking.
* $response['booking_customer_id'] : the booking_customer_id (integer) for specific booking.
* $response['booked_by'] : the booked_by for specific booking.
* $response['balance'] : the balance (integer) for specific booking.
* $response['use_rate_plan'] : the use_rate_plan (integer) for specific booking (must required).
* $response['rate_plan_id'] : the rate_plan_id (integer) for specific booking.
* $response['charge_type_id'] : the charge_type_id (integer) for specific booking.
* $response['source'] : the source for specific booking.
* $response['is_ota_booking'] : the is_ota_booking (integer) for specific booking.
* $response['pay_period'] : the pay_period (integer) for specific booking.
* $response['room_type_id'] : the room_type_id (integer) for specific booking block.
* and many more attributes from table booking and join with booking block and customer table.
*/

function get_booking(int $booking_id = null )
{
    $get_payment_data = null;
    $CI = & get_instance();
    $CI->load->model('Booking_model');

    // filters
    $data = apply_filters( 'before_get_booking', $booking_id, $CI->input->post());
    $should_get_booking = apply_filters( 'should_get_booking', $booking_id, $CI->input->post());

    if (!$should_get_booking) {
        return;
    }

    if(isset($booking_id) && $booking_id == null){
        return null;
    }

    // before getting booking 
    do_action('pre.get.booking', $booking_id, $CI->input->post());

    $get_booking_data = $CI->Booking_model->get_booking($booking_id);

    // after getting booking
    do_action('post.get.booking', $booking_id, $CI->input->post());
     
    return $get_booking_data;

}


/* Retrieves a booking value based on a booking filter.
* Supported hooks:
* before_get_booking: the filter executed before get booking
* should_get_booking: the filter executed to check get booking.
* pre.get.booking: the hook executed before getting booking. 
* post.get.booking: the hook executed after getting booking
* @param array $filter (Required) The data for booking table
* you can filter data base on customer id ,booking id ,company id and room id.
* @return $response: array value of the booking data. A value of any type may be returned, If there  
   is no booking in the database, boolean false is returned
* $response array includes following attributes:
* $response['room_id'] : the room_id for specific booking block.
* $response['check_in_date'] : the check_in_date for specific booking block.
* $response['check_out_date'] : the check_out_date for specific booking block.
* $response['booking_type'] : the booking_type of specific booking.
* $response['booking_from'] : the booking_from of specific booking.
* $response['rate'] : the rate of specific booking.
* $response['adult_count'] : the adult_count for specific booking .
* $response['children_count'] : the children_count for specific booking.
* $response['company_id'] : the company_id for specific booking.
* $response['state'] : the state for specific booking.
* $response['booking_notes'] : the booking_notes for specific booking.
* $response['booking_customer_id'] : the booking_customer_id for specific booking.
* $response['booked_by'] : the booked_by for specific booking.
* $response['balance'] : the balance for specific booking.
* $response['use_rate_plan'] : the use_rate_plan for specific booking.
* $response['rate_plan_id'] : the rate_plan_id for specific booking.
* $response['charge_type_id'] : the charge_type_id for specific booking.
* $response['source'] : the source for specific booking.
* $response['is_ota_booking'] : the is_ota_booking for specific booking.
* $response['pay_period'] : the pay_period for specific booking.
* $response['room_type_id'] : the room_type_id for specific booking block.
* and many more attributes for table booking and join with customer , booking block table.
*/

function get_bookings(array $filter = null)
{
    $get_booking_data = null;
    $CI = & get_instance();
    $CI->load->model('Booking_model');

    // filters
    $data = apply_filters( 'before_get_booking', $filter, $CI->input->post());
    $should_get_booking = apply_filters( 'should_get_booking', $filter, $CI->input->post());

    if (!$should_get_booking) {
        return;
    }

    if(empty($filter)){
        return null;
    }

    // before getting booking 
    do_action('pre.get.booking', $filter, $CI->input->post());

    $get_booking_data = $CI->Booking_model->get_bookings_data($filter);

    // after getting booking
    do_action('post.get.booking', $filter, $CI->input->post());
     
    return $get_booking_data;

}

/* update a booking in booking table.
* Supported hooks:
* before_update_booking: the filter executed before update booking
* should_update_booking: the filter executed to check update booking.
* pre.update.booking: the hook executed before update booking. 
* post.update.booking: the hook executed after update booking.
* @param array $booking (Required) includes following attributes:
* @param int $booking_id The id of the booking corresponds to the booking data.
* $data['room_id'] : the room_id (integer) for specific booking block (must required).
* $data['check_in_date'] : the check_in_date for specific booking block (must required date in gmdate()
   format).
* $data['check_out_date'] : the check_out_date for specific booking block(must required date in gmdate()
   format).
* $data['rate'] : the rate (integer) of specific booking.
* $data['adult_count'] : the adult_count (integer) for specific booking (must required).
* $data['children_count'] : the children_count (integer) for specific booking.
* $data['state'] : the state (integer) for specific booking (must required).
* $data['booking_notes'] : the booking_notes (text) for specific booking.
* $data['booking_customer_id'] : the booking_customer_id (integer) for specific booking.
* $data['use_rate_plan'] : the use_rate_plan (integer) for specific booking (must required).
* $data['rate_plan_id'] : the rate_plan_id (integer) for specific booking.
* $data['charge_type_id'] : the charge_type_id (integer) for specific booking.
* $data['source'] : the source for specific booking.
* $data['is_ota_booking'] : the is_ota_booking (integer) for specific booking.
* $data['pay_period'] : the pay_period (integer) for specific booking.
* $data['room_type_id'] : the room_type_id (integer) for specific booking block.

* and many more attributes for table booking.
* @return mixed Either true or null, if booking data is updated then true else null.
*
*/

function update_booking(array $booking = null, int $booking_id = null)
{
    $updated_flag = null;
    $filter =array();
    $CI = & get_instance();
    $CI->load->model('Booking_model');
    $CI->load->model('Booking_room_history_model');
    $CI->load->model('Invoice_model');
    $CI->load->library('session');

    //filters
    $data = apply_filters( 'before_update_booking', $booking_id, $CI->input->post());
    $should_update_booking = apply_filters( 'should_update_booking', $booking_id, $CI->input->post());

    if (!$should_update_booking) {
        return;
    }

    if(empty($booking) && $booking_id == null){
        return null;
    }
     $filter['booking_id'] = $booking_id;
     
    $get_booking_data = $CI->Booking_model->get_bookings_data($filter);
    
    $booking_data = array(

        "rate" => isset($booking['rate']) ? $booking['rate'] : null,
        "adult_count" => isset($booking['adult_count']) ? $booking['adult_count'] : null ,
        "children_count" => isset($booking['children_count']) ? $booking['children_count'] : null,
        "state" => isset($booking['state']) ?  $booking['state'] : 0,
        "booking_notes" => isset($booking['booking_notes']) ? $booking['booking_notes'] : null,
        
        "booking_customer_id" => isset($booking['booking_customer_id']) ? $booking['booking_customer_id'] : null,
        
        "use_rate_plan" =>isset($booking['use_rate_plan']) ? $booking['use_rate_plan'] : 0,
        "rate_plan_id" => isset($booking['rate_plan_id']) ? $booking['rate_plan_id'] : 0,
        "housekeeping_notes" => isset($booking['housekeeping_notes']) ? $booking['housekeeping_notes'] : null,
        "charge_type_id" => isset($booking['charge_type_id']) ? $booking['charge_type_id'] : 0,
        "source" => isset($booking['source']) ? $booking['source'] : 0,
        "is_ota_booking" => isset($booking['is_ota_booking']) ? $booking['is_ota_booking'] : 0,
        "pay_period" => isset($booking['pay_period']) ? $booking['pay_period'] : 0,
        "revenue" => isset($booking['revenue']) ? $booking['revenue'] : 0,
        "add_daily_charge" => isset($booking['add_daily_charge']) ? $booking['add_daily_charge'] : 0,
        "residual_rate" => isset($booking['residual_rate']) ? $booking['residual_rate'] : null,
        "is_invoice_auto_sent" => isset($booking['is_invoice_auto_sent']) ? $booking['is_invoice_auto_sent'] : 0
    );
   
    // before updating booking 
    do_action('pre.update.booking', $booking_id, $CI->input->post());
    
    $updated_flag = $CI->Booking_model->update_booking($booking_data, $booking_id);
    if(empty($updated_flag)){
        return null;
    }
    // before updating booking 
    do_action('post.update.booking', $booking_id, $booking_data, $CI->input->post());
     
    
    return true;
}

/**
 * delete a booking data.
 * Supported hooks:
 * before_delete_booking: the filter executed before delete booking.
 * should_delete_booking: the filter executed to check delete booking.
 * pre.delete.booking: the hook executed before delete booking. 
 * post.delete.booking: the hook executed after delete booking.
 * @param int $booking_id The id of the booking corresponds to the booking table.
 * @return mixed Either true or null, if booking data is deleted then true else null.
 * 
 */
function delete_booking(int $booking_id = null)
{ 
    $CI = & get_instance();
    $CI->load->model('Booking_model');

    // filters 
    $data = apply_filters( 'before_delete_booking', $booking_id, $CI->input->post());
    $should_delete_booking = apply_filters( 'should_delete_booking', $booking_id, $CI->input->post());
    if (!$should_delete_booking) {
        return;
    }

    if(isset($booking_id) && $booking_id == null){
        return null;
    }

    //for action befor deleting the booking
    do_action('pre.delete.booking', $booking_id, $CI->input->post()); 
  
    $delete_flag = $CI->Booking_model->delete_booking($booking_id);
    if(empty($delete_flag)){
        return null;
    }
    
    //for action after deleting the booking
    do_action('post.delete.booking', $booking_id, $CI->input->post());
  
    return true;
}