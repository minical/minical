<?php
/* add a  new statement in statement table.
* Supported hooks:
* before_add_statement: the filter executed before add statement
* should_add_statement: the filter executed to check add statement.
* pre.add.statement: the hook executed before add statement. 
* post.add.statement: the hook executed after added statement.
* @param array $statement (Required) includes following attributes:
* $data['statement_number'] : the statement_number (integer) of specific statement.
* $data['creation_date'] : the creation_date (date_time) for specific statement ( must provide date in gmdate() format) .
* $data['statement_name'] : the statement_name (character) for specific statement.
* $data['booking_id'] : the booking_id (integer) for specific statement.
* @return $response: array value of the statement data. A value of any type may be returned, If there  
   is no statement in the database, boolean false is returned
* $response array includes following attributes:
* $response['key'] : the key of specific statement
*
*/
function add_statement($statement)
{

    $CI = & get_instance();
    $CI->load->model('Statement_model');
    $CI->load->library('session');

    if(empty($statement)){
        return null;
    }

    $data = apply_filters( 'before_add_statement', $statement, $CI->input->post());
    $should_add_statement = apply_filters( 'should_add_statement', $statement, $CI->input->post());
    if (!$should_add_statement) {
        return;
    }

    $statement_data = array(
            'statement_name' => $statement['statement_name'],

            'creation_date' => isset($statement['creation_date']) ? $statement['creation_date'] : gmdate('Y-m-d H:i:s') ,

            'statement_number' => isset($statement['statement_number']) ? $statement['statement_number'] : 0
        );

    do_action('pre.add.statement', $statement_data, $CI->input->post());

    $statement_id = $CI->Statement_model->create_statement($statement_data);

    do_action('post.add.statement', $statement_id, $statement_data, $CI->input->post());

    if(isset($statement['booking_id']) && $statement_id){
    $booking_statement_data = array(
            'booking_id' => $statement['booking_id'],

            'statement_id' => $statement_id
        );

     $CI->Statement_model->create_statement_booking($booking_statement_data);
    }

    if(isset($statement_id)){
        return TRUE;
    }   
}

/* Retrieves a statement value based on a statement_id.
* Supported hooks:
* before_get_statement: the filter executed before get statement
* should_get_statement: the filter executed to check get statement.
* pre.get.statement: the hook executed before getting statement. 
* post.get.statement: the hook executed after getting statement.
* @param string $statement_id (Required) The primary id for statement table
*
* @return $response: array value of the statement data. A value of any type may be returned, If there  
   is no statement in the database, boolean false is returned
* $response array includes following attributes:
* $response['statement_number'] : the statement_number of specific statement.
* $response['creation_date'] : the creation_date for specific statement .
* $response['statement_name'] : the statement_name for specific statement.
* $response['booking_id'] : the booking_id for specific statement.
* and many more attributes for table statement and join with booking_x_statement table.
*/

function get_statement(int $statement_id = null )
{
    $get_statement_data = null;
    $CI = & get_instance();
    $CI->load->model('Statement_model');

    // filters
    $data = apply_filters( 'before_get_statement', $statement_id, $CI->input->post());
    $should_get_statement = apply_filters( 'should_get_statement', $statement_id, $CI->input->post());

    if (!$should_get_statement) {
        return;
    }

    if(isset($statement_id) && $statement_id == null){
        return null;
    }

    // before getting statement 
    do_action('pre.get.statement', $statement_id, $CI->input->post());

    $get_statement_data = $CI->Statement_model->get_bookings_by_statement_id($statement_id);

    // after getting statement
    do_action('post.get.statement', $statement_id, $CI->input->post());
     
    return $get_statement_data;

}

/* Retrieves a statement value based on a filter.
* Supported hooks:
* before_get_statements: the filter executed before get statement
* should_get_statements: the filter executed to check get statement.
* pre.get.statements: the hook executed before getting statement. 
* post.get.statements: the hook executed after getting statement
* @param array $filter (Required) The data for statement table
*
* @return $response: array value of the statement data. A value of any type may be returned, If there  
   is no statement in the database, boolean false is returned
* $response array includes following attributes:
* $response['statement_number'] : the statement_number of specific statement.
* $response['creation_date'] : the creation_date for specific statement .
* $response['statement_name'] : the statement_name for specific statement.
* $response['booking_id'] : the booking_id for specific statement.
* and many more attributes for table statement and join with booking_x_statement table.
*/

function get_statements(array $filter = null)
{
    $get_statement_data = null;
    $CI = & get_instance();
    $CI->load->model('Statement_model');

    // filters
    $data = apply_filters( 'before_get_statements', $filter, $CI->input->post());
    $should_get_statement = apply_filters( 'should_get_statements', $filter, $CI->input->post());

    if (!$should_get_statement) {
        return;
    }

    if(empty($filter)){
        return null;
    }

    // before getting statement 
    do_action('pre.get.statements', $filter, $CI->input->post());

    $get_statement_data = $CI->Statement_model->get_statements_data($filter);

    // after getting statement
    do_action('post.get.statements',$filter, $CI->input->post());
     
    return $get_statement_data;

}

/* update a new statement in statement table.
* Supported hooks:
* before_update_statement: the filter executed before update statement
* should_update_statement: the filter executed to check update statement.
* pre.update.statement: the hook executed before update statement. 
* post.update.statement: the hook executed after update statement.
* @param array $statement (Required) includes following attributes:
* @param int $statement_id The id of the statement corresponds to the statement data.
* $data['statement_number'] : the statement_number (integer) of specific statement.
* $data['creation_date'] : the creation_date (date_time) for specific statement ( must provide date in gmdate() format) .
* $data['statement_name'] : the statement_name (character) for specific statement.
* @return mixed Either true or null, if statement data is updated then true else null.
*
*/

function update_statement(array $statement = null, int $statement_id = null)
{
    $updated_flag = null;
    $CI = & get_instance();
    $CI->load->model('Statement_model');

    //filters
    $data = apply_filters( 'before_update_statement', $statement_id, $CI->input->post());
    $should_update_statement = apply_filters( 'should_update_statement', $statement_id, $CI->input->post());

    if (!$should_update_statement) {
        return;
    }

    if(empty($statement) && $statement_id == null){
        return null;
    }

    $get_statement_data = $CI->Statement_model->get_bookings_by_statement_id($statement_id);

    $statement_data = array(
        'statement_name' => isset($statement['statement_name']) ? $statement['statement_name'] : $get_statement_data['statement_name'],

        'creation_date' => isset($statement['creation_date']) ? $statement['creation_date'] : gmdate('Y-m-d H:i:s'),

        'statement_number' => isset($statement['statement_number']) ? $statement['statement_number'] : $get_statement_data['statement_number']
        );   
    // before updating statement 
    do_action('pre.update.statement', $statement_id, $CI->input->post());
    
    $updated_flag = $CI->Statement_model->update_statement($statement_data,$statement_id);
    if(empty($updated_flag)){
        return null;
    }
    // before updating statement 
    do_action('post.update.statement', $statement_id, $statement_data, $CI->input->post());
     
    return true;
}

/**
 * delete a statement data.
 * Supported hooks:
 * before_delete_statement: the filter executed before delete statement.
 * should_delete_statement: the filter executed to check delete statement.
 * pre.delete.statement: the hook executed before delete statement. 
 * post.delete.statement: the hook executed after delete statement.
 * @param int $statement_id The id of the statement corresponds to the statement table.
 * @return mixed Either true or null, if statement data is deleted then true else null.
 * 
 */
function delete_statement(int $statement_id = null)
{ 
    $CI = & get_instance();
    $CI->load->model('Statement_model');

    // filters 
    $data = apply_filters( 'before_delete_statement', $statement_id, $CI->input->post());
    $should_delete_statement = apply_filters( 'should_delete_statement', $statement_id, $CI->input->post());
    if (!$should_delete_statement) {
        return;
    }

    if(isset($statement_id) && $statement_id == null){
        return null;
    }

    //for action befor deleting the statement
    do_action('pre.delete.statement', $statement_id, $CI->input->post()); 
  
    $delete_flag = $CI->Statement_model->delete_booking_statements($statement_id);
    if(empty($delete_flag)){
        return null;
    }
    
    //for action after deleting the statement
    do_action('post.delete.statement', $statement_id, $CI->input->post());
  
    return true;
}