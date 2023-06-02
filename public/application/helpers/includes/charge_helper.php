<?php 
/* add a  new charge in charge table.
* Supported hooks:
* before_add_charge: the filter executed before add charge.
* should_add_charge: the filter executed to check add charge.
* pre.add.charge: the hook executed before add charge. 
* post.add.charge: the hook executed after added charge.
* @param array $charge (Required) includes following attributes:
* $data['description'] : the description (character) of specific charge.
* $data['date_time'] : the date_time (date_time) for specific charge( must provide date in gmdate()
   format).
* $data['booking_id'] : the booking_id (integer) for specific charge.
* $data['amount'] : the amount (decimal integer) for specific charge.
* $data['company_id'] : the company_id for specific charge.
* $data['quantity'] : the quantity (integer) for specific charge.
* $data['folio_id'] : the folio_id (integer) for specific charge.
* $data['user_id'] : the user_id (integer) for specific charge.
* $data['customer_id'] : the customer_id (integer) for specific charge.
* $data['charge_type_id'] : the charge_type_id (integer)  for specific charge.
* $data['selling_date'] : the selling_date (date) for specific charge ( must provide date in gmdate() format).
* $data['is_extra_pos'] : the is_extra_pos (integer)  for specific charge .
* $data['is_night_audit_charge'] : the is_night_audit_charge (integer) for specific charge.
* and many more attributes for table charge.
* @return $response: array value of the charge data. A value of any type may be returned, If there  
   is no charge in the database, boolean false is returned
* $response array includes following attributes:
* $response['key'] : the key of specific charge
*
*/
function add_charge($charge)
{

    $CI = & get_instance();
    $CI->load->model('Charge_model');
    $CI->load->library('session');

    if(empty($charge)){
        return null;
    }
    if (isset($charge['company_id'])) {
       $company_id = $charge['company_id'];
    }else{
       $company_id = $CI->session->userdata('current_company_id');
    }
    $data = apply_filters( 'before_add_charge', $charge, $CI->input->post());
    $should_add_charge = apply_filters( 'should_add_charge', $charge, $CI->input->post());

    if (!$should_add_charge) {
        return;
    }

    $charge_data = array(

        'description' => $charge['description'],

        'date_time' => isset($charge['date_time']) ? $charge['date_time'] : gmdate('Y-m-d H:i:s'),

        'booking_id' => $charge['booking_id'],

        'amount' => isset($charge['amount']) ? $charge['amount'] : 0 ,

        'charge_type_id' => isset($charge['charge_type_id']) ? $charge['charge_type_id'] : 0,

        'selling_date' => isset($charge['selling_date']) ? $charge['selling_date'] : gmdate('Y-m-d'),

        'user_id' => isset($charge['user_id']) ? $charge['user_id'] : 0,

        'customer_id' => isset($charge['customer_id']) ? $charge['customer_id'] : 0,

        'pay_period' => isset($charge['pay_period']) ? $charge['pay_period'] : 0,

        'is_night_audit_charge' => isset($charge['is_night_audit_charge']) ? $charge['is_night_audit_charge'] : 0 
        );

    do_action('pre.add.charge', $charge_data, $CI->input->post());

    $charge_id = $CI->Charge_model->insert_charge($charge_data);
       $charge_data['charge_id'] = $charge_id;
       $is_pos = true;
       if(!$charge['is_extra_pos'])
       {
         $is_pos = false;
       }
    do_action('post.create.charge', $charge_data,$CI->input->post());

    $charge_action_data = array('charge_id' => $charge_id, 'charge' => $charge_data, 'company_id'=> $company_id, 'is_pos' => $is_pos,'qty'=>$charge['quantity']);
    do_action('post.add.charge', $charge_action_data, $CI->input->post());

    if($charge_id){
        $folio = array(
            'folio_id' => isset($charge['folio_id']) ? $charge['folio_id'] : 0,
            'charge_id' => $charge_id
        );
        
        $CI->Charge_model->insert_charge_folio($folio['charge_id'],$folio['folio_id']);
    }
    if(isset($charge_id)){
        return $charge_id;
    } 
    return null;    
}


/* Retrieves a charge value based on a charge_id.
* Supported hooks:
* before_get_charge: the filter executed before get charge
* should_get_charge: the filter executed to check get charge.
* pre.get.charge: the hook executed before getting charge. 
* post.get.charge: the hook executed after getting charge
* @param string $room_id (Required) The primary id for charge table
*
* @return $response: array value of the charge data. A value of any type may be returned, If there  
   is no charge in the database, boolean false is returned
* $response array includes following attributes:
* $response['description'] : the description of specific charge.
* $response['date_time'] : the date_time  for specific charge.
* $response['booking_id'] : the booking_id  for specific charge.
* $response['amount'] : the amount for specific charge.
* $response['charge_type_id'] : the charge_type_id for specific charge.
* $response['selling_date'] : the selling_date  for specific charge.
* $response['is_night_audit_charge'] : the is_night_audit_charge  for specific charge.
* and many more attributes for table charge.
*/

function get_charge(int $charge_id = null )
{
    $get_charge_data = null;
    $CI = & get_instance();
    $CI->load->model('Charge_model');

    // filters
    $data = apply_filters( 'before_get_charge', $charge_id, $CI->input->post());
    $should_get_charge = apply_filters( 'should_get_charge', $charge_id, $CI->input->post());

    if (!$should_get_charge) {
        return;
    }

    if(isset($charge_id) && $charge_id == null){
        return null;
    }

    // before getting charge 
    do_action('pre.get.charge', $charge_id, $CI->input->post());

    $get_charge_data = $CI->Charge_model->get_charge($charge_id);

    // after getting charge
    do_action('post.get.charge',$charge_id, $CI->input->post());
     
    return $get_charge_data;

}

/* Retrieves a charge value based on a filter.
* Supported hooks:
* before_get_charges: the filter executed before get charge
* should_get_charges: the filter executed to check get charge.
* pre.get.charges: the hook executed before getting charge. 
* post.get.charges: the hook executed after getting charge
* @param array $filter (Required) The data for charge table
* you can filter charges base on description , booking id ,customer id , user id charge type id. 
* @return $response: array value of the charge data. A value of any type may be returned, If there  
   is no charge in the database, boolean false is returned
* $response array includes following attributes:
* $data['description'] : the description of specific charge.
* $data['date_time'] : the date_time  for specific charge.
* $data['booking_id'] : the booking_id  for specific charge.
* $data['amount'] : the amount for specific charge.
* $data['charge_type_id'] : the charge_type_id for specific charge.
* $data['selling_date'] : the selling_date  for specific charge .
* $data['is_night_audit_charge'] : the is_night_audit_charge  for specific charge.
* and many more attributes for table charge join with other tables.
*/

function get_charges(array $filter = null)
{
    $get_charge_data = null;
    $CI = & get_instance();
    $CI->load->model('Charge_model');

    // filters
    $data = apply_filters( 'before_get_charges', $filter, $CI->input->post());
    $should_get_charge = apply_filters( 'should_get_charges', $filter, $CI->input->post());

    if (!$should_get_charge) {
        return;
    }

    if(empty($filter)){
        return null;
    }

    // before getting charge 
    do_action('pre.get.charges', $filter, $CI->input->post());

    $get_charge_data = $CI->Charge_model->get_charges_data($filter);

    // after getting charge
    do_action('post.get.charges',$filter, $CI->input->post());
     
    return $get_charge_data;

}

/* update a new charge in charge table.
* Supported hooks:
* before_update_charge: the filter executed before update charge
* should_update_charge: the filter executed to check update charge.
* pre.update.charge: the hook executed before update charge. 
* post.update.charge: the hook executed after update roochargem.
* @param array $charge (Required) includes following attributes:
* @param int $charge_id The id of the charge corresponds to the charge data.
* $data['description'] : the description (character) of specific charge.
* $data['date_time'] : the date_time (date_time) for specific charge( must provide date in gmdate()
   format).
* $data['booking_id'] : the booking_id (integer) for specific charge.
* $data['amount'] : the amount (decimal integer) for specific charge.
* $data['company_id'] : the company_id for specific charge.
* $data['quantity'] : the quantity (integer) for specific charge.
* $data['folio_id'] : the folio_id (integer) for specific charge.
* $data['user_id'] : the user_id (integer) for specific charge.
* $data['customer_id'] : the customer_id (integer) for specific charge.
* $data['charge_type_id'] : the charge_type_id (integer)  for specific charge.
* $data['selling_date'] : the selling_date (date) for specific charge ( must provide date in gmdate() format).
* $data['is_extra_pos'] : the is_extra_pos (integer)  for specific charge .
* $data['is_night_audit_charge'] : the is_night_audit_charge (integer) for specific charge.
* and many more attributes for table charge.
* @return mixed Either true or null, if charge data is updated then true else null.
*
*/

function update_charge(array $charge = null, int $charge_id = null)
{
    $updated_flag = null;
    $CI = & get_instance();
    $CI->load->model('Charge_model');
    $CI->load->model('Folio_model');

    //filters
    $data = apply_filters( 'before_update_charge', $charge_id, $CI->input->post());
    $should_update_charge = apply_filters( 'should_update_charge', $charge_id, $CI->input->post());

    if (!$should_update_charge) {
        return;
    }

    if(empty($charge) && $charge_id == null){
        return null;
    }

    $get_charge_data = $CI->Charge_model->get_charge($charge_id);

    $charge_data = array(

        'charge_id' => $charge_id,

        'description' => isset($charge['description']) ? $charge['description'] : $get_charge_data['description'],

        'date_time' => isset($charge['date_time']) ? $charge['date_time'] : $get_charge_data['date_time'],

        'booking_id' => $get_charge_data['booking_id'],

        'amount' => isset($charge['amount']) ? $charge['amount'] : $get_charge_data['amount'] ,

        'charge_type_id' => isset($charge['charge_type_id']) ? $charge['charge_type_id'] :$get_charge_data['charge_type_id'],

        'selling_date' => isset($charge['selling_date']) ? $charge['selling_date'] : $get_charge_data['selling_date'],

        'user_id' => isset($charge['user_id']) ? $charge['user_id'] : $get_charge_data['user_id'],

        'customer_id' => isset($charge['customer_id']) ? $charge['customer_id'] : $get_charge_data['customer_id'],

        'pay_period' => isset($charge['pay_period']) ? $charge['pay_period'] : $get_charge_data['pay_period'],

        'is_night_audit_charge' => isset($charge['is_night_audit_charge']) ? $charge['is_night_audit_charge'] : $get_charge_data['is_night_audit_charge']
    );
   
    // before updating charge 
    do_action('pre.update.charge', $charge_id, $CI->input->post());
    
    $updated_flag = $CI->Charge_model->update_charge($charge_data);
    if(empty($updated_flag)){
        return null;
    }
    // before updating charge 
    do_action('post.update.charge', $charge_id, $charge_data, $CI->input->post());
     
    if($charge_id){
        $folio = array(
            'folio_id' => isset($charge['folio_id']) ? $charge['folio_id'] : 0,
            'charge_id' => $charge_id
        );
           
        $CI->Folio_model->update_folio_charge_or_payment($folio);
    }
    return true;
}





/**
 * delete a charge data.
 * Supported hooks:
 * before_delete_charge: the filter executed before delete charge.
 * should_delete_charge: the filter executed to check delete charge.
 * pre.delete.charge: the hook executed before delete charge. 
 * post.delete.charge: the hook executed after delete charge.
 * @param int $charge_id The id of the charge corresponds to the charge table.
 * @return mixed Either true or null, if charge data is deleted then true else null.
 * 
 */
function delete_charge(int $charge_id = null)
{ 
    $CI = & get_instance();
    $CI->load->model('Charge_model');

    // filters 
    $data = apply_filters( 'before_delete_charge', $charge_id, $CI->input->post());
    $should_delete_charge = apply_filters( 'should_delete_charge', $charge_id, $CI->input->post());
    if (!$should_delete_charge) {
        return;
    }

    if(isset($charge_id) && $charge_id == null){
        return null;
    }

    //for action befor deleting the charge
    do_action('pre.delete.charge', $charge_id, $CI->input->post()); 
  
    $delete_flag = $CI->Charge_model->delete_charge($charge_id);
    if(empty($delete_flag)){
        return null;
    }
    
    //for action after deleting the charge
    do_action('post.delete.charge', $charge_id, $CI->input->post());
  
    return true;
}