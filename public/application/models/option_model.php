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


    function delete_option($option)
    {
        $this->db->where('option_name', $option);
        $this->db->delete('options');
        if ($this->db->affected_rows() > 0){
            return TRUE;
        }else{
            return FALSE;
        }   
    }
}

?>