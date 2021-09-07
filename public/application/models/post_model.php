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


    function get_post(){

        $this->db->from('posts as p');
        $this->db->where('company_id',$this->company_id);
        $this->db->where('post_type','finance_transaction');
        $this->db->or_where('post_type','charge_transaction');
        $this->db->where('is_deleted',0);


        $query = $this->db->get();

        if ($query->num_rows() > 0)
        {
            $result = $query->result_array();
            return $result;
        }
        else
        {
            return NULL;
        }

    }

    function get_total_income_meta_data(){
        $this->db->from('postmeta');
        $this->db->where('meta_key','transaction_type');
        $this->db->where('meta_value','income');

        $query = $this->db->get();

        if ($query->num_rows() > 0)
        {
            $result = $query->result_array();
            return $result;
        }
        else
        {
            return NULL;
        }
    }

    function get_total_expense_meta_data(){
        $this->db->from('postmeta');
        $this->db->where('meta_key','transaction_type');
        $this->db->where('meta_value','expense');

        $query = $this->db->get();

        if ($query->num_rows() > 0)
        {
            $result = $query->result_array();
            return $result;
        }
        else
        {
            return NULL;
        }
    }


    function get_post_meta($post_id, $is_viewed = false){
        $this->db->from('postmeta as pm');
        $this->db->where('post_id',$post_id);

        if($is_viewed){
            $this->db->where('meta_key','is_viewed');
            $this->db->where('meta_value',0);
        }

        $query = $this->db->get();

        if ($query->num_rows() > 0)
        {
            $result = $query->result_array();
            return $result;
        }
        else
        {
            return NULL;
        }
    }

    function get_category($company_id){
        $this->db->from('posts');
        $this->db->where('company_id',$this->company_id);
        $this->db->where('post_type','finance_category');
        $query = $this->db->get();

        if ($query->num_rows() > 0)
        {
            $result = $query->result_array();
            return $result;
        }
        else
        {
            return NULL;
        }

    }

    function get_expense_category($company_id){
        $this->db->from('posts');
        $this->db->where('company_id',$this->company_id);
        $this->db->where('post_type','finance_expense_cate');
        $query = $this->db->get();

        if ($query->num_rows() > 0)
        {
            $result = $query->result_array();
            return $result;
        }
        else
        {
            return NULL;
        }

    }

    function update_alert($booking_id){


        $this->db->from('postmeta');
        $this->db->where('meta_key','booking_id');
        $this->db->where_in('meta_value',$booking_id);
        // $this->db->update('is_viewed',1);
        $query = $this->db->get();

        $result = $query->result_array();

        if($result){
            $post_ids = array();
            foreach ($result as $key => $value) {
                $post_ids[] = $value['post_id'];
            }
        }

        $data = array(
            'meta_value' => 1
        );
        $this->db->where('meta_key','is_viewed');
        $this->db->where_in('post_id',$post_ids);
        $this->db->update('postmeta',$data);

    }




    function create_category($data){

        $this->db->insert_batch('posts', $data);

    }

    function delete_transaction ($post_id){

        $data = Array('is_deleted' => '1');
        $this->db->where('post_id',$post_id);
        $this->db->where('post_type','finance_transaction');
        $this->db->update("posts", $data);

        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
    }


    function get_finance_alert(){

        $this->db->from('posts');
        $this->db->where('company_id',$this->company_id);
        $this->db->where('post_type','finance_alert');
        $this->db->where('is_deleted', 0 );
        $query = $this->db->get();

        if ($query->num_rows() > 0)
        {
            $result = $query->result_array();
            return $result;
        }
        else
        {
            return NULL;
        }

    }
}