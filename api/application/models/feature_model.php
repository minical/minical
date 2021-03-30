<?php

class Feature_model extends CI_Model {

    function __construct()
    {
        parent::__construct(); // Call the Model constructor
    }

    function get_features($company_id, $limit = NULL)
    {
        $this->db->select('*');
        $this->db->from('feature');
        $this->db->where('company_id',$company_id);
        $this->db->where('show_on_website','1');
        $this->db->where('is_deleted','0');
        if($limit)
        {
            $this->db->order_by("id", "DESC");
            $this->db->limit($limit);
        }
        $query = $this->db->get();
        return $query->result_array();
    }

}
