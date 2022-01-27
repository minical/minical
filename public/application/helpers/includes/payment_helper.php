<?php
/* add a new payment in payment table.
* Supported hooks:
* before_add_payment: the filter executed before add payment
* should_add_payment: the filter executed to check add payment.
* pre.add.payment: the hook executed before add payment. 
* post.add.payment: the hook executed after added payment.
* @param array $payment (Required) includes following attributes:
* $data['user_id'] : the user_id of specific payment.
* $data['booking_id'] : the booking_id for specific payment.
* $data['cvc'] : the cvc for specific payment.
* $data['selling_date'] : the selling_date for specific payment.
* $data['company_id'] : the company_id for specific payment.
* $data['amount'] : the amount for specific payment.
* $data['customer_id'] : the customer_id for specific payment.
* $data['payment_type_id'] : the payment_type_id for specific payment.
* $data['description'] : the description for specific payment.
* $data['is_captured'] : the is_captured for specific payment.
* $data['selected_gateway'] : the selected_gateway for specific payment.
* $data['capture_payment_type'] : the capture_payment_type for specific payment.
* $data['folio_id'] : the folio_id for specific payment.
* @return $response: array value of the payment data. A value of any type may be returned, If there  
   is no payment in the database, boolean false is returned
* $response array includes following attributes:
* $response['key'] : the key of specific payment
*
*/
function add_payment($payment)
{

    $CI = & get_instance();
    $CI->load->model('Payment_model');
    $CI->load->model('Card_model');
    $CI->load->model('Company_model');
    $CI->load->library('session');

    if(empty($payment)){
        return null;
    }
    if (isset($payment['company_id'])) {

        $company_id = $payment['company_id'];
    }else{
        $company_id = $CI->session->userdata('current_company_id'); 
    }
    $data = apply_filters( 'before_add_payment', $payment, $CI->input->post());
    $should_add_payment = apply_filters( 'should_add_payment', $payment, $CI->input->post());
    if (!$should_add_payment) {
        return;
    }

    $payment_data = Array(
        "user_id" => $payment['user_id'],
        "booking_id" => isset($payment['booking_id']) ? $payment['booking_id'] : null ,
        "selling_date" => isset($payment['payment_date']) ? date('Y-m-d', strtotime($payment['payment_date'])) : null,
        "amount" => isset($payment['payment_amount']) ?  $payment['payment_amount'] : 0.00 ,
        "customer_id" => isset($payment['customer_id']) ? $payment['customer_id'] : 0,
        "payment_type_id" => isset($payment['payment_type_id']) ? $payment['payment_type_id'] : 0 ,
        "description" => isset($payment['description']) ? $payment['description'] : null,
        "is_captured" => isset($payment['is_captured']) ? $payment['is_captured'] : 0,
        "date_time" => gmdate("Y-m-d H:i:s"),
        "selected_gateway" =>isset($payment['selected_gateway']) ? $payment['selected_gateway'] : null
            );
    
   $payment_data['credit_card_id'] = null;
   if($payment['customer_id']){
        $card_data = $CI->Card_model->get_active_card($payment['customer_id'], $company_id);

        if (isset($card_data) && $card_data) {
            $payment_data['credit_card_id'] = $card_data['id'];
        }
    }
    $company_data =  $CI->Company_model->get_company($company_id);
    $capture_payment_type = $company_data['manual_payment_capture'];
    $capture_payment_type = ($payment['capture_payment_type'] != 'authorize_only') ? false : true;
    $cvc = $payment['cvc'];
    unset($payment['capture_payment_type']);

    do_action('pre.add.payment', $payment_data, $CI->input->post());
    
    $payment_id = $CI->Payment_model->insert_payment($payment_data, $cvc, $capture_payment_type);

    $payment_data['payment_id'] = $payment_id;

    do_action('post.add.payment', $payment_data, $CI->input->post());

    if($payment_id){
        $folio = array(
            'folio_id' => isset($payment['folio_id']) ? $payment['folio_id'] : 0,
            'payment_id' => $payment_id
        );
           
        $CI->Payment_model->insert_payment_folio($folio);
    }

    if(isset($payment_id)){
        return $payment_id;
    }  

    return null; 
}

/* Retrieves a payment value based on a payment_id.
* Supported hooks:
* before_get_payment: the filter executed before get payment
* should_get_payment: the filter executed to check get payment.
* pre.get.payment: the hook executed before getting payment. 
* post.get.payment: the hook executed after getting payment.
* @param string $payment_id (Required) The primary id for payment table
*
* @return $response: array value of the payment data. A value of any type may be returned, If there  
   is no payment in the database, boolean false is returned
* $response array includes following attributes:
* $data['user_id'] : the user_id of specific payment.
* $data['booking_id'] : the booking_id for specific payment .
* $data['selling_date'] : the selling_date for specific payment.
* $data['company_id'] : the company_id for specific payment.
* $data['amount'] : the amount for specific payment.
* $data['customer_id'] : the customer_id for specific payment.
* $data['payment_type_id'] : the payment_type_id for specific payment.
* $data['description'] : the description for specific payment.
* $data['selected_gateway'] : the selected_gateway for specific payment.
* $data['capture_payment_type'] : the capture_payment_type for specific payment.
* and many more attributes for table payment.
*/

function get_payment(int $payment_id = null )
{
    $get_payment_data = null;
    $CI = & get_instance();
    $CI->load->model('Payment_model');

    // filters
    $data = apply_filters( 'before_get_payment', $payment_id, $CI->input->post());
    $should_get_payment = apply_filters( 'should_get_payment', $payment_id, $CI->input->post());

    if (!$should_get_payment) {
        return;
    }

    if(isset($payment_id) && $payment_id == null){
        return null;
    }

    // before getting payment 
    do_action('pre.get.payment', $payment_id, $CI->input->post());

    $get_payment_data = $CI->Payment_model->get_payment($payment_id);

    // after getting payment
    do_action('post.get.payment', $payment_id, $payment_id, $CI->input->post());
     
    return $get_payment_data;

}

/* Retrieves a payment value based on a payment filter.
* Supported hooks:
* before_get_payment: the filter executed before get payment
* should_get_payment: the filter executed to check get payment.
* pre.get.payment: the hook executed before getting payment. 
* post.get.payment: the hook executed after getting payment
* @param array $filter (Required) The data for payment table
* you can filter data base on customer id ,booking id,payment id and folio id.
* @return $response: array value of the payment data. A value of any type may be returned, If there  
   is no payment in the database, boolean false is returned
* $response array includes following attributes:
* $response['tax_type'] : the tax_type of specific tax.
* $response['tax_rate'] : the tax_rate for specific tax .
* $response['is_percentage'] : the is_percentage for specific tax.
* $response['company_id'] : the company_id for specific tax.
* $response['is_brackets_active'] : the is_brackets_active for specific tax.
* $response['is_tax_inclusive'] : the is_tax_inclusive for specific tax.
* $response['start_range'] : the start_range for specific tax price_bracket.
* $response['end_range'] : the end_range for specific tax price_bracket.
* $response['tax_rate_price_bracket'] : the tax_rate for specific tax price_bracket.
* $response['is_percentage_price_bracket'] : the is_percentage for specific tax price_bracket.
* and many more attributes for table payment and join with customer , user_profiles table.
*/

function get_payments(array $filter = null)
{
    $get_payment_data = null;
    $CI = & get_instance();
    $CI->load->model('Payment_model');

    // filters
    $data = apply_filters( 'before_get_payment', $filter, $CI->input->post());
    $should_get_payment = apply_filters( 'should_get_payment', $filter, $CI->input->post());

    if (!$should_get_payment) {
        return;
    }

    if(empty($filter)){
        return null;
    }

    // before getting payment 
    do_action('pre.get.payment', $filter, $CI->input->post());

    $get_payment_data = $CI->Payment_model->get_payments_data($filter);

    // after getting payment
    do_action('post.get.payment',$filter,$filter, $CI->input->post());
     
    return $get_payment_data;

}


/* update a new payment in payment table.
* Supported hooks:
* before_update_payment: the filter executed before update payment
* should_update_payment: the filter executed to check update payment.
* pre.update.payment: the hook executed before update payment. 
* post.update.payment: the hook executed after update payment.
* @param array $payment (Required) includes following attributes:
* @param int $payment_id The id of the tax corresponds to the tax data.
* $data['payment_type_id'] : the payment_type_id for specific payment.
* $data['description'] : the description for specific payment.
* $data['is_captured'] : the is_captured for specific payment.
* $data['selected_gateway'] : the selected_gateway for specific payment.
* @return mixed Either true or null, if tax data is updated then true else null.
*
*/

function update_payment(array $payment = null, int $payment_id = null)
{
    $updated_flag = null;
    $CI = & get_instance();
    $CI->load->model('Payment_model');
    $CI->load->model('Folio_model');
    $CI->load->library('session');

    //filters
    $data = apply_filters( 'before_update_payment', $payment_id, $CI->input->post());
    $should_update_payment = apply_filters( 'should_update_payment', $payment_id, $CI->input->post());

    if (!$should_update_payment) {
        return;
    }

    if(empty($payment) && $payment_id == null){
        return null;
    }
    // if (!$payment['company_id']) {
    //    $company_id = $CI->session->userdata('current_company_id');
    // }else{
    //    $company_id = $tax['company_id'];
    // }

    $get_payment_data = $CI->Payment_model->get_payment($payment_id);

    $payment_data = Array(
        
        "payment_type_id" => isset($payment['payment_type_id']) ? $payment['payment_type_id'] : $get_payment_data['payment_type_id'] ,
        "description" => isset($payment['description']) ? $payment['description'] : $get_payment_data['description'],
        "is_captured" => isset($payment['is_captured']) ? $payment['is_captured'] :$get_payment_data['is_captured'],
        "date_time" => gmdate("Y-m-d H:i:s"),
        "selected_gateway" =>isset($payment['selected_gateway']) ? $payment['selected_gateway'] : $get_payment_data['selected_gateway']
    );  
    // before updating payment 
    do_action('pre.update.payment', $payment_id, $CI->input->post());
    
    $updated_flag = $CI->Payment_model->update_payment($payment_data,$payment_id);
    if(empty($updated_flag)){
        return null;
    }
    // before updating payment 
    do_action('post.update.payment', $payment_id, $payment_data, $CI->input->post());
    if($payment_id){
    $folio = array(
            'folio_id' => $payment_data['folio_id'],
            'payment_id' => $payment_id
         );
       
     $CI->Folio_model->update_folio_charge_or_payment($folio);
     }
    return true;
}

/**
 * delete a payment data.
 * Supported hooks:
 * before_delete_payment: the filter executed before delete payment.
 * should_delete_payment: the filter executed to check delete payment.
 * pre.delete.payment: the hook executed before delete payment. 
 * post.delete.payment: the hook executed after delete payment.
 * @param int $payment_id The id of the payment corresponds to the payment table.
 * @return mixed Either true or null, if payment data is deleted then true else null.
 * 
 */
function delete_payment(int $payment_id = null)
{ 
    $CI = & get_instance();
    $CI->load->model('Payment_model');

    // filters 
    $data = apply_filters( 'before_delete_payment', $payment_id, $CI->input->post());
    $should_delete_payment = apply_filters( 'should_delete_payment', $payment_id, $CI->input->post());
    if (!$should_delete_payment) {
        return;
    }

    if(isset($payment_id) && $payment_id == null){
        return null;
    }

    //for action befor deleting the payment
    do_action('pre.delete.payment', $payment_id, $CI->input->post()); 
  
    $delete_flag = $CI->Payment_model->delete_payment($payment_id);
    if(empty($delete_flag)){
        return null;
    }
    
    //for action after deleting the payment
    do_action('post.delete.payment', $payment_id, $CI->input->post());
  
    return true;
}