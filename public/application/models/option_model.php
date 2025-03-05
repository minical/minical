<?php

class Option_model extends CI_Model {

    function add_option($option){
        $this->db->insert('options', $option);
        return $this->db->insert_id();
    }


    function get_option($option){

        $this->db->select('*');
        $this->db->where('option_name', $option);
        $query = $this->db->get('options');
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        $result_array = $query->result_array();

        return $result_array;

    }
    
    function get_option_by_company($option, $company_id, $is_require = false){

        $this->db->select('*');
        $this->db->where('option_name', $option);

        if(is_array($company_id)){
            $this->db->where_in('company_id', $company_id);
        } else {
            $this->db->where('company_id', $company_id);
        }

        if($is_require){
            $where = "(JSON_EXTRACT(option_value, '$.security_status') = 1)";
            $this->db->where($where);
        }
        $query = $this->db->get('options');
     
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        $result_array = $query->result_array();

        return $result_array;
    }

    function get_option_by_user($option, $user_id){

        $this->db->select('*');
        $this->db->where('option_name', $option);
        // $this->db->like('option_value', $user_id, 'both');

        $where = "(JSON_EXTRACT(option_value, '$.user_id') = $user_id)";
        $this->db->where($where);
        
        $query = $this->db->get('options');
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        $result_array = $query->result_array();

        return $result_array;
    }

    function get_options(){

        $this->db->select('*');
        $query = $this->db->get('options');
        $result_array = $query->result_array();
        return $result_array;

    }

    
    function update_option($option, $value, $autoload)
    {
        $data = array(
            "option_name" => $option,
            "option_value" => $value,
            "autoload" => $autoload
        );
        $this->db->where('option_name', $option);
        $this->db->update('options', $data);
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

    function update_option_company($option, $value, $company_id)
    {
        $data = array(
            "option_name" => $option,
            "option_value" => $value,
            "autoload" => 0
        );
        $this->db->where('company_id', $company_id);
        $this->db->where('option_name', $option);
        $this->db->update('options', $data);
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

    function delete_option($option, $user_id = null)
    {
        $this->db->where('option_name', $option);

        if($user_id){
            $this->db->like('option_value', $user_id, 'both');
        }
        $this->db->delete('options');
        if ($this->db->affected_rows() > 0){
            return TRUE;
        }else{
            return FALSE;
        }   
    }

    function get_data_by_json($option_name, $company_id)
    {
        $sql = "SELECT * FROM `options`
                WHERE 
                    option_name = 'loyalty_customer' AND
                    company_id = '$company_id';
                ";
        
        $query = $this->db->query($sql);
        if($query->num_rows() >= 1)
        {
            return $query->result_array();
        }
        
        return NULL;
    }

    function get_option_by_json_data($option_name, $option_value, $company_id){

        $this->db->select('*');
        //$this->db->where('option_name', $option);
        $this->db->where('company_id', $company_id);
    
        if($option_value){
            $where = "(JSON_EXTRACT(option_value, '$.".$option_name."') = '".$option_value."')";
            $this->db->where($where);
        }
        $query = $this->db->get('options');
       //echo  $this->db->last_query();
      
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        $result_array = $query->result_array();

        return $result_array;
    }
}

?>