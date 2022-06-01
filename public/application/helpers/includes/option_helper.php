<?php

/**
 * Adds a new option.
 * 
 * @param string $option  (Required) Name of the option to add.
 * @param mixed  $value  (Optional) Option value.
 * @param bool   $autoload  (Optional) Whether to load the option when extension starts up. Default is enabled.
 * @return bool  True if the option was added, null otherwise.
 *  
 */
function add_option(string $option, $value = '', bool $autoload = true)
{

    $option = trim( $option );
    if ( empty( $option ) ) {
        return false;
    }

    $CI = & get_instance();
    $CI->load->model('Option_model');
    $CI->load->library('session');
    $compnay_id =$CI->session->userdata('current_company_id');

    $data = apply_filters( 'before_add_option', $option, $CI->input->post());
    $should_add_option = apply_filters( 'should_add_option', $option, $CI->input->post());
    if (!$should_add_option) {
        return;
    }

    $option_array = array(
        "company_id" => $compnay_id,
        "option_name" => $option,
        "option_value" => $value,
        "autoload" => $autoload
    );

    do_action('pre.add.option', $option, $CI->input->post());
    
    $option_id = $CI->Option_model->add_option($option_array);

    do_action('post.add.option', $option_id, $option, $CI->input->post());

    if(isset($option_id)){
        return TRUE;
    }
    
}



/**
 * Retrieves an option value based on an option name.
 * 
 * @param string $option  (Required) Name of the option to add.
 * @param bool $default (Optional) Default value to return if the option does not exist.
 * @return bool Value of the option. A value of any type may be returned, If there is no option in the database, boolean false is *               returned
 *  
 */
function get_option( string $option, bool $default = false)
{
    $option = trim( $option );
    if ( empty( $option ) ) {
        return false;
    }

    $CI = & get_instance();
    $CI->load->model('Option_model');

    $data = apply_filters( 'before_get_option', $option, $CI->input->post());
    $should_get_option = apply_filters( 'should_get_option', $option, $CI->input->post());
    if (!$should_get_option) {
        return;
    }
   
    do_action('pre.get.option', $option, $CI->input->post());
    
    $option_data = $CI->Option_model->get_option($option);

    do_action('post.get.option', $option_data, $option, $CI->input->post());

    if(!empty($option_data) && $option_data !== null){
        return $option_data;
    }

    return $default;

}


/**
 * Retrieves all option value.
 * @return bool Value of the option. A value of any type may be returned, If there is no option in the database, boolean false is *               returned
 *  
 */
function get_options()
{
    $CI = & get_instance();
    $CI->load->model('Option_model');

    $option = null;
    
    $data = apply_filters( 'before_get_options', $option, $CI->input->post());
    // $should_get_option = apply_filters( 'should_get_options', $option, $CI->input->post());
    // if (!$should_get_option) {
    //     return;
    // }
   
    do_action('pre.get.options', $option, $CI->input->post());
    
    $option_data = $CI->Option_model->get_options();

    do_action('post.get.options', $option_data, $option, $CI->input->post());

    if(!empty($option_data) && $option_data !== null){
        return $option_data;
    }

    return false;

}

/**
 * Updates the value of an option that was already added.
 * 
 * @param string $option  (Required) Name of the option to update.
 * @param mixed  $value  (Required) Option value.
 * @param bool   $autoload  (Optional) Whether to load the option when extension starts up. Default is enabled.
 * @return bool  True if the value was updated, false otherwise.
 *  
 */
function update_option( string $option, $value = '', bool $autoload = true )
{

    $option = trim( $option );
    if ( empty( $option ) ) {
        return false;
    }
    $CI = & get_instance();
    $CI->load->model('Option_model');

    $data = apply_filters( 'before_update_option', $option, $CI->input->post());
    $should_update_option = apply_filters( 'should_update_option', $option, $CI->input->post());
    if (!$should_update_option) {
        return;
    }

    do_action('pre.update.option', $option, $CI->input->post());
    
    $update_flag = $CI->Option_model->update_option($option, $value, $autoload);

    do_action('post.update.option', $option, $CI->input->post());

    if(!empty($update_flag) && $update_flag != null){
        return TRUE;
    }

    return false;
}



/**
 * Removes option by name.
 * 
 * @param string $option (Required) Name of the option to delete.
 * @return bool  True if the option was deleted, false otherwise.
 *  
 */
function delete_option( string $option )
{
    
    $option = trim( $option );
    if ( empty( $option ) ) {
        return false;
    }

    $CI = & get_instance();
    $CI->load->model('Option_model');

    $data = apply_filters( 'before_delete_option', $option, $CI->input->post());
    $should_delete_option = apply_filters( 'should_delete_option', $option, $CI->input->post());
    if (!$should_delete_option) {
        return;
    }

    do_action('pre.delete.option', $option, $CI->input->post());
    
    $delete_flag = $CI->Option_model->delete_option($option);

    do_action('post.delete.option', $option, $CI->input->post());

    if(!empty($delete_flag) && $delete_flag != null){
        return TRUE;
    }

    return false;

}

?>