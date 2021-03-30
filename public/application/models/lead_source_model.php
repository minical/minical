<?php

class Lead_source_model extends CI_Model {

	function __construct()
	{
		// Call the Model constructor
		parent::__construct();
	}

	// Function to get lead source id from lead_sources table
    function get_lead_source_id($lead_source_slug = null)
    {
        if($lead_source_slug)
        {
            $this->db->where('slug', $lead_source_slug);
            $this->db->select('id');
            $this->db->from('lead_sources');
            $result = $this->db->get()->result_array();
            if(isset($result[0]))
            {
                return $result[0]['id'];
            }
            else
            {
                $data = array();
                $data['name'] = ucwords($lead_source_slug);
                $data['slug'] = $lead_source_slug;
                $query = $this->db->insert('lead_sources', $data);
                return $this->db->insert_id();
            }
        }

        return 0;
    }     
    
    function get_lead_sources()
    {
        $this->db->select('company.*, lead_sources.name as lead_source_name');
        $this->db->from('company_admin_panel_info');
        $this->db->join('lead_sources', 'company_admin_panel_info.lead_source_id = lead_sources.id', 'left');
        $this->db->join('company', 'company_admin_panel_info.company_id = company.company_id', 'left');
        $this->db->order_by('company_id', 'desc');
        $query = $this->db->get();
        if($query->num_rows() > 0)
        {
            return $query->result_array();
        }
        return NULL;
    }
        
}
