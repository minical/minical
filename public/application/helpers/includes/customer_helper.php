<?php

/* add a new customer in customer table.
* Supported hooks:
* before_add_customer: the filter executed before add customer.
* should_add_customer: the filter executed to check add customer.
* pre.add.customer: the hook executed before add customer. 
* post.add.customer: the hook executed after added customer.
* @param array $customer (Required) includes following attributes:
* $data['customer_name'] : the customer_name (character) of specific customer.
* $data['address'] : the address (character) for specific customer .
* $data['company_id'] : the company_id (integer) for specific customer.
* $data['city'] : the city (character) for specific customer.
* $data['region'] : the region (character) for specific customer.
* $data['country'] : the country (character) for specific customer.
* $data['postal_code'] : the postal_code (character) for specific customer.
* $data['phone'] : the phone (character) for specific customer.
* $data['fax'] : the fax (character) for specific  customer.
* $data['email'] : the email (character) for specific customer.
* $data['customer_notes'] : the customer_notes (character) for specific customer.
* $data['customer_type'] : the customer_type (character) for specific customer.
* $data['address2'] : the customer_notes (character) for specific customer.
* $data['phone2'] : the customer_notes (character) for specific customer.
* $data['is_primary'] : the is_primary (integer) for specific customer card details.
* $data['card_name'] : the card_name (character) for specific customer card details.
* $data['cc_number'] : the cc_number (character) for specific customer card details.
* $data['cvc'] : the cvc for specific (character) customer card details.
* $data['cc_expiry_month'] : the cc_expiry_month (character) for specific customer card details.
* $data['cc_expiry_year'] : the cc_expiry_year for (character) specific customer card details.
* @return $response: array value of the customer data. A value of any type may be returned, If there  
   is no customer in the database, boolean false is returned
* $response array includes following attributes:
* $response['key'] : the key of specific customer
*
*/
function add_customer($customer)
{

    $CI = & get_instance();
    $CI->load->model('Customer_model');
    $CI->load->model('Card_model');
    $CI->load->library('session');
    $CI->load->library('encrypt');
    
    if(empty($customer)){
        return null;
    }
    if (isset($customer['company_id'])) {

        $company_id = $customer['company_id'];
    }else{
        $company_id = $CI->session->userdata('current_company_id'); 
    }
    $data = apply_filters( 'before_add_customer', $customer, $CI->input->post());
    $should_add_customer = apply_filters( 'should_add_customer', $customer, $CI->input->post());
    if (!$should_add_customer) {
        return;
    }
    
    $customer_data =array(
        'customer_name'=> $customer['customer_name'],
        'address'=> isset($customer['address']) ? $customer['address'] : null,
        'city'=> isset($customer['city']) ? $customer['city'] : null,
        'region'=> isset($customer['region']) ? $customer['region'] : null,
        'country'=> isset($customer['country']) ? $customer['country'] : null,
        'postal_code'=> isset($customer['postal_code']) ? $customer['postal_code'] : null,
        'phone'=> isset($customer['phone']) ? $customer['phone'] : null,
        'fax'=> isset($customer['fax']) ? $customer['fax'] : null,
        'email'=> isset($customer['email ']) ? $customer['email '] : null,
        'customer_notes'=> isset($customer['customer_notes']) ? $customer['customer_notes'] : null,
        'customer_type'=> isset($customer['customer_type']) ? $customer['customer_type'] : 'PERSON',
        'customer_type_id '=> isset($customer['customer_type_id']) ? $customer['customer_type_id'] : 1,
        'address2'=> isset($customer['address2']) ? $customer['address2'] : null,
        'phone2'=> isset($customer['phone2']) ? $customer['phone2'] : null  
    );

    // before add customer
    do_action('pre.add.customer', $customer, $CI->input->post());
    
    $customer_id = $CI->Customer_model->create_customer($customer_data);

    // after add customer
    do_action('post.add.customer', $customer_id, $customer, $CI->input->post());
    $cvc = isset($customer['cvc']) ? $customer['cvc'] : '';
    $cc_number = isset($customer['cc_number']) ? $customer['cc_number'] : '';
    $card_details = array(
       'is_primary' => 1,
       'customer_id' => $customer_id,
       'customer_name' => $customer['customer_name'],
       'card_name' => '',
       'company_id' => $company_id,
       // 'cc_number' => (isset($customer_data['cc_number'])? $customer_data['cc_number'] : NULL),
       'cc_expiry_month' => (isset($customer['cc_expiry_month']) ? $customer['cc_expiry_month'] : NULL),
       'cc_expiry_year' => (isset($customer['cc_expiry_year']) ? $customer['cc_expiry_year'] : NULL)
    );

    if(
        $cc_number && 
            is_numeric($cc_number) &&
            !strrpos($cc_number, 'X') && 
            $cvc && 
            is_numeric($cvc) &&
            !strrpos($cvc, '*')
        )
        {
            $card_data_array = array('card' =>
                array(
                    'card_number'       => $cc_number,
                    'card_type'         => "",
                    'cardholder_name'   => (isset($customer['customer_name']) ? $customer['customer_name'] : ""),
                    'service_code'      => $cvc,
                    'expiration_month'  => isset($customer['cc_expiry_month']) ? $customer['cc_expiry_month'] : null,
                    'expiration_year'   => isset($customer['cc_expiry_year']) ? $customer['cc_expiry_year'] : null
                )
            );
            $card_response = array();

            if($card_data_array && $card_data_array['card']['card_number']) {

                $card_data_array['customer_data'] = $customer;

                $card_response = apply_filters('post.add.customer', $card_data_array);

                unset($card_data_array['customer_data']);
            }
            if(
                $card_response &&
                isset($card_response['tokenization_response']["data"]) &&
                isset($card_response['tokenization_response']["data"]["attributes"]) &&
                isset($card_response['tokenization_response']["data"]["attributes"]["card_token"])
            ){
                $card_token = $card_response['tokenization_response']["data"]["attributes"]["card_token"];

                $cvc_encrypted = $CI->encrypt->encode($cvc, $card_token);

                $card_details['cc_cvc_encrypted'] = ($cvc_encrypted) ? $cvc_encrypted : "";
                $card_details['cc_number'] = 'XXXX XXXX XXXX '.substr($cc_number,-4);

                $meta['token'] = $card_token;
                $card_details['customer_meta_data'] = json_encode($meta);
            }
        }
        if(isset($cc_number)){
            $CI->Card_model->create_customer_card_info($card_details);
        }


    if(isset($customer_id)){
        return $customer_id;
    }
    return null;   
}


/* Retrieves a customer value based on a customer_id.
* Supported hooks:
* before_get_customer: the filter executed before get customer
* should_get_customer: the filter executed to check get customer.
* pre.get.customer: the hook executed before getting customer. 
* post.get.customer: the hook executed after getting customer.
* @param string $customer_id (Required) The primary id for customer table
*
* @return $response: array value of the customer data. A value of any type may be returned, If there  
   is no customer in the database, boolean false is returned
* $response array includes following attributes:
* $response['customer_name'] : the customer_name of specific customer.
* $response['address'] : the address for specific customer .
* $response['city'] : the is_percentage for specific customer.
* $response['region'] : the region for specific customer.
* $response['country'] : the country for specific customer.
* $response['postal_code'] : the postal_code for specific customer.
* $response['phone'] : the phone for specific customer.
* $response['fax'] : the fax for specific  customer.
* $response['email'] : the email for specific customer.
* $response['customer_notes'] : the customer_notes for specific customer.
* $response['customer_type'] : the customer_type for specific customer.
* $response['address2'] : the customer_notes for specific customer.
* $response['phone2'] : the customer_notes for specific customer.
* and many more attributes for table customer.
*/
function get_customer(int $customer_id = null )
{
    $get_customer_data = null;
    $CI = & get_instance();
    $CI->load->model('Customer_model');

    // filters
    $data = apply_filters( 'before_get_customer', $customer_id, $CI->input->post());
    $should_get_customer = apply_filters( 'should_get_customer', $customer_id, $CI->input->post());

    if (!$should_get_customer) {
        return;
    }

    if(isset($customer_id) && $customer_id == null){
        return null;
    }

    // before getting customer 
    do_action('pre.get.customer', $customer_id, $CI->input->post());

    $get_customer_data = $CI->Customer_model->get_customer($customer_id);

    // after getting customer
    do_action('post.get.customer',$customer_id, $CI->input->post());
     
    return $get_customer_data;

}

/* Retrieves a customer value based on a filter.
* Supported hooks:
* before_get_customers: the filter executed before get customer.
* should_get_customers: the filter executed to check get customer.
* pre.get.customers: the hook executed before getting customer. 
* post.get.customers: the hook executed after getting customer.
* @param array $filter (Required) The data for customer table
* you can filter customer base on customer name  , customer email ,customer id , company id . 
* @return $response: array value of the customer data. A value of any type may be returned, If there  
   is no customer in the database, boolean false is returned.
* $response array includes following attributes:
* $response['customer_name'] : the customer_name of specific customer.
* $response['address'] : the address for specific customer .
* $response['city'] : the is_percentage for specific customer.
* $response['region'] : the region for specific customer.
* $response['country'] : the country for specific customer.
* $response['postal_code'] : the postal_code for specific customer.
* $response['phone'] : the phone for specific customer.
* $response['fax'] : the fax for specific  customer.
* $response['email'] : the email for specific customer.
* $response['customer_notes'] : the customer_notes for specific customer.
* $response['customer_type'] : the customer_type for specific customer.
* $response['address2'] : the customer_notes for specific customer.
* $response['phone2'] : the customer_notes for specific customer.
* $response['is_primary'] : the is_primary for specific customer card details.
* $response['card_name'] : the card_name for specific customer card details.
* $response['cc_number'] : the cc_number for specific customer card details.
* $response['cvc'] : the cvc for specific customer card details.
* $response['cc_expiry_month'] : the cc_expiry_month for specific customer card details.
* $response['cc_expiry_year'] : the cc_expiry_year for specific customer card details.
* and many more attributes for table customer and join with customer card details .
*/
function get_customers(array $filter = null )
{
    $get_customer_data = null;
    $CI = & get_instance();
    $CI->load->model('Customer_model');

    // filters
    $data = apply_filters( 'before_get_customers', $filter, $CI->input->post());
    $should_get_customer = apply_filters( 'should_get_customers', $filter, $CI->input->post());

    if (!$should_get_customer) {
        return;
    }

    if(empty($filter)){
        return null;
    }


    // before getting customer 
    do_action('pre.get.customers', $filter, $CI->input->post());

    $get_customer_data = $CI->Customer_model->get_customers_data($filter);

    // after getting customer
    do_action('post.get.customers', $filter, $CI->input->post());
     
    return $get_customer_data;

}

/* update a customer in customer table.
* Supported hooks:
* before_update_customer: the filter executed before update customer
* should_update_customer: the filter executed to check update customer.
* pre.update.customer: the hook executed before update customer. 
* post.update.customer: the hook executed after update customer.
* @param array $customer (Required) includes following attributes:
* @param int $customer_id The id of the customer corresponds to the customer data.
* $data['customer_name'] : the customer_name (character) of specific customer.
* $data['address'] : the address (character) for specific customer .
* $data['company_id'] : the company_id (integer) for specific customer.
* $data['city'] : the city (character) for specific customer.
* $data['region'] : the region (character) for specific customer.
* $data['country'] : the country (character) for specific customer.
* $data['postal_code'] : the postal_code (character) for specific customer.
* $data['phone'] : the phone (character) for specific customer.
* $data['fax'] : the fax (character) for specific  customer.
* $data['email'] : the email (character) for specific customer.
* $data['customer_notes'] : the customer_notes (character) for specific customer.
* $data['customer_type'] : the customer_type (character) for specific customer.
* $data['address2'] : the customer_notes (character) for specific customer.
* $data['phone2'] : the customer_notes (character) for specific customer.
* @return mixed Either true or null, if customer data is updated then true else null.
*
*/
function update_customer(array $customer = null, int $customer_id = null)
{
    $updated_flag = null;
    $CI = & get_instance();
    $CI->load->model('Customer_model');

    //filters
    $data = apply_filters( 'before_update_customer', $customer_id, $CI->input->post());
    $should_update_customer = apply_filters( 'should_update_customer', $customer_id, $CI->input->post());

    if (!$should_update_customer) {
        return;
    }

    if(empty($customer) && $customer_id == null){
        return null;
    }

    $get_customer_data = $CI->Customer_model->get_customer($customer_id);

    $customer_data = array(
        'customer_name'=> isset($customer['customer_name']) ? $customer['customer_name'] :$get_customer_data['customer_name'] ,
        'address'=> isset($customer['address']) ? $customer['address'] : $get_customer_data['address'],
        'city'=> isset($customer['city']) ? $customer['city'] : $get_customer_data['city'],
        'region'=> isset($customer['region']) ? $customer['region'] : $get_customer_data['region'],
        'country'=> isset($customer['country']) ? $customer['country'] : $get_customer_data['country'],
        'postal_code'=> isset($customer['postal_code']) ? $customer['postal_code'] : $get_customer_data['postal_code'],
        'phone'=> isset($customer['phone']) ? $customer['phone'] : $get_customer_data['phone'],
        'fax'=> isset($customer['fax']) ? $customer['fax'] : $get_customer_data['fax'],
        'email'=> isset($customer['email ']) ? $customer['email '] : $get_customer_data['email'],
        'customer_notes'=> isset($customer['customer_notes']) ? $customer['customer_notes'] : $get_customer_data['customer_notes'],
        'customer_type'=> isset($customer['customer_type']) ? $customer['customer_type'] : $get_customer_data['customer_type'],
        'customer_type_id '=> isset($customer['customer_type_id']) ? $customer['customer_type_id'] : $get_customer_data['customer_type_id'],
        'address2'=> isset($customer['address2']) ? $customer['address2'] : $get_customer_data['address2'],
        'phone2'=> isset($customer['phone2']) ? $customer['phone2'] : $get_customer_data['phone2'] 
    );
   
    // before updating customer 
    do_action('pre.update.customer', $customer_id, $CI->input->post());
    
    $updated_flag = $CI->Customer_model->update_customer($customer_id,$customer_data);
    if(empty($updated_flag)){
        return null;
    }
    // before updating customer 
    do_action('post.update.customer', $customer_id, $customer_data, $CI->input->post());
     
    return true;
}

/**
 * delete a customer data.
 * Supported hooks:
 * before_delete_customer: the filter executed before delete customer.
 * should_delete_customer: the filter executed to check delete customer.
 * pre.delete.customer: the hook executed before delete customer. 
 * post.delete.customer: the hook executed after delete customer.
 * @param int $customer_id The id of the customer corresponds to the customer table.
 * @return mixed Either true or null, if customer data is deleted then true else null.
 * 
 */
function delete_customer(int $customer_id = null, string $company_id = null)
{ 
    $CI = & get_instance();
    $CI->load->model('Customer_model');

    // filters 
    $data = apply_filters( 'before_delete_customer', $customer_id, $CI->input->post());
    $should_delete_customer = apply_filters( 'should_delete_customer', $customer_id, $CI->input->post());
    if (!$should_delete_customer) {
        return;
    }

    if(isset($customer_id) && $customer_id == null){
        return null;
    }

    //for action befor deleteting the customer
    do_action('pre.delete.customer', $customer_id, $CI->input->post()); 
  
    $delete_flag = $CI->Customer_model->delete_customer($customer_id, $company_id);
    if(empty($delete_flag)){
        return null;
    }
    
    //for action after deleteting the customer
    do_action('post.delete.customer', $customer_id, $CI->input->post());
  
    return true;
}