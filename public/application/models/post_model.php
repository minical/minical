<?php
class Post_model extends CI_Model {

    function create_post($data)
    {
        $this->db->insert('posts', $data);
        return $this->db->insert_id();
    }

    function create_post_meta($data)
    {
        $this->db->insert('postmeta', $data);
        return $this->db->insert_id();
    }

}