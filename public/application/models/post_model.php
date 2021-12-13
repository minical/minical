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

    function get_post($post)
    {
        $this->db->select('*');
       
        if(isset($post) && is_array($post) && count($post) > 0){
            foreach($post as $key=>$value){
             $this->db->where($key, $value);      
            }
        }elseif(is_int($post))
        {
            $this->db->where('post_id', $post);   
        }

        $this->db->where('is_deleted', 0); 
        $query = $this->db->get('posts');
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        //  echo $this->db->last_query();die;
        $result_array = $query->result_array();

        return $result_array;
    }

    function edit_post(array $post = null)
    {
        $this->db->where('post_id', $post['post_id']);
        $this->db->update('posts', $post);
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }else{
            if ($this->db->affected_rows() > 0){
                return TRUE;
            }else{
                return FALSE;
            }
        }
    }

    function delete_post(int $post_id = null, bool $force_delete = false )
    {
     
        $data = Array('is_deleted' => '1');
        $this->db->where('post_id', $post_id);
        if(isset($force_delete) && $force_delete == TRUE){
            $this->db->delete('posts');
        }else{
            $this->db->update('posts', $data);
        }
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }else{
            if ($this->db->affected_rows() > 0){
                $this->db->where('post_id', $post_id);
                $this->db->delete('postmeta');
                return TRUE;
            }else{
                return FALSE;
            }
        }
    }

    function get_post_meta(int $post_id = null, string $key = null, bool $single = false )
    {
        
        $this->db->select('*');
       
        if(isset($post_id) && $post_id !== null){
            $this->db->where('post_id', $post_id);   
        
        }
        if(isset($key) && $key !== null){
            $this->db->where('meta_key', $key);

        }
        if(isset($single) && $single == TRUE){
            $result_array = $this->db->get('postmeta')->row_array();

        }else{
            $query = $this->db->get('postmeta');
            $result_array = $query->result_array();
        }

        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }

        return $result_array;
    }

    function update_post_meta(array $post_meta = null, int $post_id= null)
    {
        $data = array();      
        foreach ($post_meta as $key => $value) {
            $data['meta_key']= $key;
            $data['meta_value'] = $value;
            $data['post_id'] = $post_id;       
            $post_meta_data = $this->db->select("*")->where('meta_key', $key)->get('postmeta')->num_rows();
            if(isset($post_meta_data) && $post_meta_data >0){
                $this->db->where('post_id', $post_id);
                $this->db->where('meta_key', $key); 
                $this->db->update('postmeta', $data);
            }else {
                $this->db->insert('postmeta', $data);
            }
        }
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }else{
            if ($this->db->affected_rows() > 0){
                return TRUE;
            }else{
                return FALSE;
            }
        }
    }

    function delete_post_meta(int $post_id = null, string $meta_key = null,  $meta_value = null)
    {
        $this->db->where('post_id', $post_id);
        $this->db->where('meta_key', $meta_key);
        if(isset($meta_value) && $meta_value !== null){
            $this->db->where('meta_value', $meta_value); 
        }
        $this->db->delete('postmeta');
        if ($this->db->affected_rows() > 0){
            return TRUE;
        }else{
            return FALSE;
        }   
    }
}