<?php
/* add a new tax in tax type table also details of price bracket in tax price_bracket.
* Supported hooks:
* before_add_tax: the filter executed before add tax
* should_add_tax: the filter executed to check add tax.
* pre.add.tax: the hook executed before add tax. 
* post.add.tax: the hook executed after added tax.
* @param array $tax (Required) includes following attributes:
* $data['tax_type'] : the tax_type (character) of specific tax.
* $data['tax_rate'] : the tax_rate (decimal  integer) for specific tax .
* $data['is_percentage'] : the is_percentage ( integer) for specific tax.
* $data['company_id'] : the company_id (integer) for specific tax.
* $data['is_brackets_active'] : the is_brackets_active (integer) for specific tax.
* $data['is_tax_inclusive'] : the is_tax_inclusive (integer) for specific tax.
* $data['start_range'] : the start_range (integer) for specific tax price_bracket.
* $data['end_range'] : the end_range (integer) for specific tax price_bracket.
* $data['tax_rate_price_bracket'] : the tax_rate (integer) for specific tax price_bracket.
* $data['is_percentage_price_bracket'] : the is_percentage (integer) for specific tax price_bracket.
* @return $response: array value of the tax data. A value of any type may be returned, If there  
   is no tax in the database, boolean false is returned
* $response array includes following attributes:
* $response['key'] : the key of specific tax
*
*/
function add_tax($tax)
{

    $CI = & get_instance();
    $CI->load->model('Tax_model');
    $CI->load->model('Tax_price_bracket_model');
    $CI->load->library('session');

    if(empty($tax)){
        return null;
    }
    if (isset($tax['company_id'])) {
         $company_id = $tax['company_id'];
    }else{
       $company_id = $CI->session->userdata('current_company_id');
      
    }
    $data = apply_filters( 'before_add_tax', $tax, $CI->input->post());
    $should_add_tax = apply_filters( 'should_add_tax', $tax, $CI->input->post());
    if (!$should_add_tax) {
        return;
    }

    $tax_data = array(
            'tax_type' => $tax['tax_type'],

            'tax_rate' => isset($tax['tax_rate']) ? $tax['tax_rate'] : 0.00 ,

            'company_id' => $company_id,

            'is_percentage' => isset($tax['is_percentage']) ? $tax['is_percentage'] : 1 ,

            'is_brackets_active' => isset($tax['is_brackets_active']) ? $tax['is_brackets_active'] : 0 ,

            'is_tax_inclusive' => isset($tax['is_tax_inclusive']) ? $tax['is_tax_inclusive'] : 0 
        );

    do_action('pre.add.tax', $tax_data, $CI->input->post());

    $tax_id = $CI->Tax_model->create_new_tax_type($tax_data);

    do_action('post.add.tax', $tax_id, $tax_data, $CI->input->post());

    if(isset($tax['is_brackets_active']) && $tax['is_brackets_active'] == 1  && $tax_id){
    $price_bracket = array(
            'tax_type_id' => $tax_id,
            'start_range' => isset($tax['start_range']) ? $tax['start_range'] : null,
            'end_range' => isset($tax['end_range']) ? $tax['end_range'] : null,
            'tax_rate' => isset($tax['tax_rate_price_bracket']) ? $tax['tax_rate_price_bracket'] : '',
            'is_percentage' => isset($tax['is_percentage_price_bracket']) ? $tax['is_percentage_price_bracket'] : 1,

        );

     $CI->Tax_price_bracket_model->create_price_bracket($price_bracket);
    }

    if(isset($tax_id)){
        return $tax_id;
    }  

    return null; 
}

/* Retrieves a tax value based on a tax_type_id.
* Supported hooks:
* before_get_tax: the filter executed before get tax
* should_get_tax: the filter executed to check get tax.
* pre.get.tax: the hook executed before getting tax. 
* post.get.tax: the hook executed after getting tax.
* @param string $tax_type_id (Required) The primary id for tax table
*
* @return $response: array value of the tax data. A value of any type may be returned, If there  
   is no tax in the database, boolean false is returned
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
* and many more attributes for table tax and join with tax_price_bracket table.
*/

function get_tax(int $tax_type_id = null )
{
    $get_tax_data = null;
    $CI = & get_instance();
    $CI->load->model('Tax_model');

    // filters
    $data = apply_filters( 'before_get_tax', $tax_type_id, $CI->input->post());
    $should_get_tax = apply_filters( 'should_get_tax', $tax_type_id, $CI->input->post());

    if (!$should_get_tax) {
        return;
    }

    if(isset($tax_type_id) && $tax_type_id == null){
        return null;
    }

    // before getting tax 
    do_action('pre.get.tax', $tax_type_id, $CI->input->post());

    $get_tax_data = $CI->Tax_model->get_tax($tax_type_id);

    // after getting tax
    do_action('post.get.tax', $tax_type_id, $tax_type_id, $CI->input->post());
     
    return $get_tax_data;

}

/* Retrieves a tax value based on a filter.
* Supported hooks:
* before_get_taxes: the filter executed before get tax
* should_get_taxes: the filter executed to check get tax.
* pre.get.taxes: the hook executed before getting tax. 
* post.get.taxes: the hook executed after getting tax
* @param array $filter (Required) The data for tax table
* you can filter data base on tax type id, company id and tax type.
* @return $response: array value of the tax data. A value of any type may be returned, If there  
   is no tax in the database, boolean false is returned
* $response array includes following attributes:
* * $response['tax_type'] : the tax_type of specific tax.
* $response['tax_rate'] : the tax_rate for specific tax .
* $response['is_percentage'] : the is_percentage for specific tax.
* $response['company_id'] : the company_id for specific tax.
* $response['is_brackets_active'] : the is_brackets_active for specific tax.
* $response['is_tax_inclusive'] : the is_tax_inclusive for specific tax.
* $response['start_range'] : the start_range for specific tax price_bracket.
* $response['end_range'] : the end_range for specific tax price_bracket.
* $response['tax_rate_price_bracket'] : the tax_rate for specific tax price_bracket.
* $response['is_percentage_price_bracket'] : the is_percentage for specific tax price_bracket.
* and many more attributes for table tax and join with tax_price_bracket table.
*/

function get_taxes(array $filter = null)
{
    $get_tax_data = null;
    $CI = & get_instance();
    $CI->load->model('Tax_model');

    // filters
    $data = apply_filters( 'before_get_taxes', $filter, $CI->input->post());
    $should_get_tax = apply_filters( 'should_get_taxes', $filter, $CI->input->post());

    if (!$should_get_tax) {
        return;
    }

    if(empty($filter)){
        return null;
    }

    // before getting tax 
    do_action('pre.get.taxes', $filter, $CI->input->post());

    $get_tax_data = $CI->Tax_model->get_taxes($filter);

    // after getting tax
    do_action('post.get.taxes',$filter,$filter, $CI->input->post());
     
    return $get_tax_data;

}

/* update a tax in tax table.
* Supported hooks:
* before_update_tax: the filter executed before update tax
* should_update_tax: the filter executed to check update tax.
* pre.update.tax: the hook executed before update tax. 
* post.update.tax: the hook executed after update tax.
* @param array $tax (Required) includes following attributes:
* @param int $tax_id The id of the tax corresponds to the tax data.
* $data['tax_type'] : the tax_type (character) of specific tax.
* $data['tax_rate'] : the tax_rate (decimal integer) for specific tax .
* $data['is_percentage'] : the is_percentage (integer) for specific tax.
* $data['company_id'] : the company_id (integer) for specific tax.
* $data['is_brackets_active'] : the is_brackets_active (integer) for specific tax.
* $data['is_tax_inclusive'] : the is_tax_inclusive (integer) for specific tax.
* @return mixed Either true or null, if tax data is updated then true else null.
*
*/

function update_tax(array $tax = null, int $tax_type_id = null)
{
    $updated_flag = null;
    $CI = & get_instance();
    $CI->load->model('Tax_model');
    $CI->load->library('session');

    //filters
    $data = apply_filters( 'before_update_tax', $tax_type_id, $CI->input->post());
    $should_update_tax = apply_filters( 'should_update_tax', $tax_type_id, $CI->input->post());

    if (!$should_update_tax) {
        return;
    }

    if(empty($tax) && $tax_type_id == null){
        return null;
    }
    if (!$tax['company_id']) {
       $company_id = $CI->session->userdata('current_company_id');
    }else{
       $company_id = $tax['company_id'];
    }

    $get_tax_data = $CI->Tax_model->get_tax($tax_type_id);

    $tax_data = array(
            'tax_type' => isset($tax['tax_type']) ? $tax['tax_type'] : $get_tax_data['tax_type'],

            'tax_rate' => isset($tax['tax_rate']) ? $tax['tax_rate'] : $get_tax_data['tax_rate'] ,

            'company_id' => $company_id,

            'is_percentage' => isset($tax['is_percentage']) ? $tax['is_percentage'] : $get_tax_data['is_percentage'] ,

            'is_brackets_active' => isset($tax['is_brackets_active']) ? $tax['is_brackets_active'] :$get_tax_data['is_brackets_active'] ,

            'is_tax_inclusive' => isset($tax['is_tax_inclusive']) ? $tax['is_tax_inclusive'] :$get_tax_data['is_tax_inclusive'] 
        );  
    // before updating tax 
    do_action('pre.update.tax', $tax_type_id, $CI->input->post());
    
    $updated_flag = $CI->Tax_model->update_tax_type($tax_data,$tax_type_id ,$company_id);
    if(empty($updated_flag)){
        return null;
    }
    // before updating tax 
    do_action('post.update.tax', $tax_type_id, $tax_data, $CI->input->post());
     
    return true;
}

/**
 * delete a tax data.
 * Supported hooks:
 * before_delete_tax: the filter executed before delete tax.
 * should_delete_tax: the filter executed to check delete tax.
 * pre.delete.tax: the hook executed before delete tax. 
 * post.delete.tax: the hook executed after delete tax.
 * @param int $tax_type_id The id of the tax corresponds to the tax table.
 * @return mixed Either true or null, if tax data is deleted then true else null.
 * 
 */
function delete_tax(int $tax_type_id = null)
{ 
    $CI = & get_instance();
    $CI->load->model('Tax_model');
    $CI->load->model('Tax_price_bracket_model');

    // filters 
    $data = apply_filters( 'before_delete_tax', $tax_type_id, $CI->input->post());
    $should_delete_tax = apply_filters( 'should_delete_tax', $tax_type_id, $CI->input->post());
    if (!$should_delete_tax) {
        return;
    }

    if(isset($tax_type_id) && $tax_type_id == null){
        return null;
    }

    //for action befor deleting the tax
    do_action('pre.delete.tax', $tax_type_id, $CI->input->post()); 
  
    $delete_flag = $CI->Tax_model->delete_tax_type($tax_type_id);
    if(empty($delete_flag)){
        return null;
    }
    
    //for action after deleting the tax
    do_action('post.delete.tax', $tax_type_id, $CI->input->post());

    $CI->Tax_model->delete_price_braket($tax_type_id);

  
    return true;
}