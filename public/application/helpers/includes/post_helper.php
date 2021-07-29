<?php 

function add_post ($data) {
    $CI = & get_instance();
    
    $data = apply_filters( 'before_add_post', $data, $CI->input->post);
    $should_add_post = apply_filters( 'should_add_post', $data, $CI->input->post);
    
    if (!$should_add_post) {
        return;
    }

    do_action('pre.add.post', $data, $CI->input->post);
    
    if(isset($data['meta']) && $data['meta']){
	    $meta = $data['meta'];
	    unset($data['meta']);
	}

    $post_id = $CI->post_model->create_post($data);

    if($meta){
	    foreach ($meta as $key => $value) {
	        add_post_meta($post_id, $key, $value);
	    }
	}
    do_action('post.add.post', $post_id, $data, $CI->input->post);
}