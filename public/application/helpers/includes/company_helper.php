<?php
/* Retrieves a company value based on a company_id.
* Supported hooks:
* before_get_company: the filter executed before add company.
* should_get_company: the filter executed to check add company.
* pre.get.company: the hook executed before getting company. 
* post.get.company: the hook executed after getting company.
* @param string $company_id (Required) The primary id for rate plan table
* @param array $data (Required) includes following attributes:
* $data['key'] : the key of specific company
*
* @return $response: array value of the company data. A value of any type may be returned, If there  
   is no company in the database, boolean false is returned
* $response array includes following attributes:
* $response['key'] : the key of specific company
* $data['name'] : the name of specific company.
* $data['number_of_rooms'] : the number_of_rooms for specific company.
* $data['property_type'] : the property_type for specific company.
* $data['email'] : the email for specific company.
* $data['country'] : the country for specific company.
* $data['web_site'] : the web_site count for specific company.
* and many more attributes from table company table.
*/

function get_company(int $company_id = null )
{
    $get_company_data = null;
    $CI = & get_instance();
    $CI->load->model('Company_model');

    // filters
    $data = apply_filters( 'before_get_company', $company_id, $CI->input->post());
    $should_get_company = apply_filters( 'should_get_company', $company_id, $CI->input->post());

    if (!$should_get_company) {
        return;
    }

    if(isset($company_id) && $company_id == null){
        return null;
    }

    // before getting company 
    do_action('pre.get.company', $company_id, $CI->input->post());

    $get_company_data = $CI->Company_model->get_company_detail($company_id);

    // after getting company
    do_action('post.get.company', $company_id, $company_id, $CI->input->post());
     
    return $get_company_data;

}


/* Retrieves a company value based on a company_id.
* Supported hooks:
* before_get_company: the filter executed before add company.
* should_get_company: the filter executed to check add company
* pre.get.company: the hook executed before getting company. 
* post.get.company: the hook executed after getting company.
* @param string $company_id (Required) The primary id for rate plan table
* @param array $data (Required) includes following attributes:
* $data['key'] : the key of specific company
*
* @return $response: array value of the company data. A value of any type may be returned, If there  
   is no company in the database, boolean false is returned
* $response array includes following attributes:
* $response['key'] : the key of specific company
* $data['name'] : the name of specific company.
* $data['number_of_rooms'] : the number_of_rooms for specific company.
* $data['property_type'] : the property_type for specific company.
* $data['email'] : the email for specific company.
* $data['country'] : the country for specific company.
* $data['web_site'] : the web_site count for specific company.
* $data['partner_id'] : the partner_id count for specific company.
* $data['subscription_level'] : the subscription_level count for specific company.
* $data['permission'] : the permission count for specific company.
* $data['balance'] : the balance count for specific company.
* and many more attributes from table company table and join with other tables.
*/

function get_company_data(int $company_id = null )
{
    $get_company_data = null;
    $CI = & get_instance();
    $CI->load->model('Company_model');

    // filters
    $data = apply_filters( 'before_get_company', $company_id, $CI->input->post());
    $should_get_company = apply_filters( 'should_get_company', $company_id, $CI->input->post());

    if (!$should_get_company) {
        return;
    }

    if(isset($company_id) && $company_id == null){
        return null;
    }

    // before getting company 
    do_action('pre.get.company', $company_id, $CI->input->post());

    $get_company_data = $CI->Company_model->get_company($company_id);

    // after getting company
    do_action('post.get.company', $company_id, $company_id, $CI->input->post());
     
    return $get_company_data;

}