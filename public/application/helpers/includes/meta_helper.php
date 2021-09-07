<?php

function add_post_meta ($post_id, $key, $value) {

    $CI = & get_instance();
    $CI->load->model('Post_model');

    $data = array(
        'post_id' => $post_id,
        'meta_key' => $key,
        'meta_value' => $value
    );

    $data = apply_filters( 'before_add_post_meta', $data, $CI->input->post());
    $should_add_post_meta = apply_filters( 'should_add_post_meta', $data, $CI->input->post());

    if (!$should_add_post_meta) {
        return;
    }

    do_action('pre.add.post_meta', $data, $CI->input->post());

    $meta_id = $CI->Post_model->create_post_meta($data);

    do_action('post.add.post_meta', $post_id, $data, $CI->input->post());
}