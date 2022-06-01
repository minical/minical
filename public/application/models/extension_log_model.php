<?php

class Extension_log_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    function create_extension_log($data)
    {

        $this->db->insert('extensions_log', $data);

        if ($this->db->_error_message()) {
            show_error($this->db->_error_message());
        } else {
            //return $this->db->insert_id();
            $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
            $result = $query->result_array();
            if (isset($result[0])) {
                return $result[0]['last_id'];
            } else {
                return null;
            }
        }
    }
}


/* End of file - Extension_log_model.php */