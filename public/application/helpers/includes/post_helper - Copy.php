<?php

function add_post ($data) {
    $CI = & get_instance();

    $CI->load->model('Post_model');
    $data = apply_filters( 'before_add_post', $data, $CI->input->post());
    $should_add_post = apply_filters( 'should_add_post', $data, $CI->input->post());

    if (!$should_add_post) {
        return;
    }

    do_action('pre.add.post', $data, $CI->input->post());

    if(isset($data['meta']) && $data['meta']){
        $meta = $data['meta'];
        unset($data['meta']);
    }

    $post_id = $CI->Post_model->create_post($data);

    if($meta){
        foreach ($meta as $key => $value) {
            add_post_meta($post_id, $key, $value);
        }
    }
    do_action('post.add.post', $post_id, $data, $CI->input->post());
}

/**
 * get post form posts table
 * 
 * @param mixed $post Int id of post or An Array of post data, this key will be added in where clause.
 * @return mixed Either array or null, if post data is available or no error occurs, then array else null.
 * 
 */
function get_post( $post )
{

    $post_data = null;
    $CI = & get_instance();
    $CI->load->model('Post_model');

    // fillters 
    $post = apply_filters( 'before_get_post', $post, $CI->input->post());
    $should_get_post = apply_filters( 'should_get_post', $post, $CI->input->post());
    if (!$should_get_post) {
        return;
    }

    if(empty($post)){
        return null;
    }else{

        //for action befor getting the post
        do_action('pre.get.post', $post, $CI->input->post()); 
        $post_data = $CI->Post_model->get_post($post);
    }
    
    if(!$post_data){
        return null;
    }

    //for action after getting the post
    do_action('post.get.post', $post_data, $post, $CI->input->post());
    return $post_data;
    
}

/**
 * edit a post data of posts table
 * 
 * @param array $post An Array of post data including post_id.
 * @return mixed Either post_id or null, if post data is updated or no error occurs, then id else null.
 */
function edit_post(array $post = null)
{
    $CI = & get_instance();
    $CI->load->model('Post_model');

    // fillters 
    $post = apply_filters( 'before_edit_post', $post, $CI->input->post());
    $should_edit_post = apply_filters( 'should_edit_post', $post, $CI->input->post());
    if (!$should_edit_post) {
        return;
    }

    if(empty($post)){
        return null;
    }
    if(!isset($post['post_id'])){
        return false;
    }

    //for action befor editing the post
    do_action('pre.edit.post', $post, $CI->input->post()); 

    $updated_flag = $CI->Post_model->edit_post($post);
    if(empty($updated_flag)){
        return null;
    }

    $post_id = $post['post_id'];

    //for action after editing the post
    do_action('post.edit.post', $post_id, $post, $CI->input->post());
     
    return $post_id;

}

/**
 * delete a post data.
 * 
 * @param int $post_id The id of the post.
 * @return mixed Either true or null, if postmeta data is delete then true else null.
 * 
 */
function delete_post(int $post_id = null, bool $force_delete = false )
{
    $CI = & get_instance();
    $CI->load->model('Post_model');

    // fillters 
    $post = apply_filters( 'before_delete_post', $post_id, $CI->input->post());
    $should_delete_post = apply_filters( 'should_delete_post', $post_id, $CI->input->post());
    if (!$should_delete_post) {
        return;
    }

    if(isset($post_id) && $post_id == null){
        return null;
    }

    //for action befor deleteting the post
    do_action('pre.delete.post', $post_id, $CI->input->post()); 
  
    $delete_flag = $CI->Post_model->delete_post($post_id, $force_delete);
    if(empty($delete_flag)){
        return null;
    }
    
    //for action after deleteting the post
    do_action('post.delete.post', $post_id, $CI->input->post());
  
    return true;
    
}