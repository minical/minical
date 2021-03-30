<?php

class Feature_model extends CI_Model {

    function __construct()
    {
        parent::__construct(); // Call the Model constructor

        $this->load->model('Image_model');
        $this->load->model('Room_model');
        $this->load->library('PHPRequests');
    }

    // return company's room_types
    function create_feature($company_id, $feature_name)
    {
        $data = array(
            'company_id' => $company_id,
            'feature_name' => $feature_name
        );

        $this->db->insert('feature', $data);
        //return $this->db->insert_id();
        $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
        $result = $query->result_array();
        if(isset($result[0]))
        {
            return $result[0]['last_id'];
        }
        else
        {
            return null;
        }
    }

    function get_features($company_id, $limit = NULL)
    {
        $this->db->select('*');
        $this->db->from('feature');
        $this->db->where('company_id',$company_id);
        $this->db->where('is_deleted','0');
        if($limit)
        {
            $this->db->order_by("id", "DESC");
            $this->db->limit($limit);
        }
        $query = $this->db->get();
        return $query->result_array();
    }

    function get_feature($feature_id) {
        $this->db->select('*');
        $this->db->from('feature');
        $this->db->where('feature_id',$feature_id);
        $query = $this->db->get();
        return $query->row_array();

    }

    function update_feature($feature_id, $data)
    {
        $data = (object) $data;
        $this->db->where('feature_id', $feature_id);
        $this->db->update("feature", $data);

        //TODO: Add error if update failed.
        return TRUE;
    }

}
