<?php

class Whitelabel_partner_model extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();		
    }
    
    function get_partners($condition = array(), $is_reference = false){
        if($condition)
        {
            if($is_reference){
                $this->db->like($condition);
            }else{
                $this->db->where($condition);
            }
            
        }
        $query = $this->db->get('whitelabel_partner');
        return $query->result_array();
    }

    function get_partner_by_username($username) {
        $sql = "SELECT *
                FROM whitelabel_partner 
                WHERE 
                    BINARY username = '$username'";
        
        $query = $this->db->query($sql);
        if($query->num_rows() > 0)
        {
            return $query->result_array();
        }
        return NULL;
    }
    
    function update_partner_detail($user_id, $data){
        $this->db->where('id', $user_id);
        $this->db->update('users', $data);
    }
    
    
    // fucntion for fetching all whitelabel partners 
    function get_whitelabel_partners()
    {
        $this->db->where('status', '1');
        $this->db->where('is_deleted', '0');
        $this->db->from('whitelabel_partner');
        $query = $this->db->get();
        if($query->num_rows() > 0)
        {
            return $query->result_array();
        }
        return NULL;
    }

    function add_whitelabel_partner($data = array())
    {
        $query = $this->db->insert('whitelabel_partner', $data);
        return $this->db->insert_id();
    }
    
    function add_whitelabel_partner_x_admin($data = array())
    {
        $query = $this->db->insert('whitelabel_partner_x_admin', $data);
        return $this->db->insert_id();
    }

    function update_whitelabel_partner($partner_id, $data)
    {
        $this->db->where('id', $partner_id);
        $this->db->update('whitelabel_partner', $data); 
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
    }
    
    function delete_whitelabel_partner_x_admin($partner_id)
    {
        $this->db->where('partner_id', $partner_id);
		$this->db->delete('whitelabel_partner_x_admin');
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
    }

    function delete_whitelabel_partner($partner_id)
    {
        $data = array (
                    'is_deleted' => '1'
            );
        $this->db->where('id', $partner_id);
        $this->db->update("whitelabel_partner", $data);  
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
    }
    
    // function to get whitelabel admin_id 
    function get_whitelabel_admin_ids($user_id = null, $partner_id = null)
    {
        $this->db->select('wpxa.admin_id');
        $this->db->from('whitelabel_partner as wp');
        $this->db->join('whitelabel_partner_x_admin AS wpxa', "wp.id = wpxa.partner_id", "left");
        
        if($user_id !== null){
            $this->db->join('users as u', 'u.partner_id = wp.id', 'left');
            $this->db->where('u.id', $user_id);
        }
        if($partner_id !== null){
            $this->db->where('wp.id', $partner_id);
        }
       
        $query = $this->db->get();
        $admins = array();
        if($query->num_rows() > 0)
        {
            foreach($query->result_array() as $admin){
                $admins[] = $admin['admin_id'];
            }
        }
        return $admins;
    }
    
    function get_partner_company_ids($partner_id)
    {
        $this->db->where('u.partner_id', $partner_id);
        $this->db->where('up.permission', 'is_owner');
        $this->db->select('up.company_id');
        $this->db->from('users as u');
        $this->db->join('user_permissions as up', 'up.user_id = u.id', 'left');
        $query = $this->db->get();
        if($query->num_rows() > 0)
        {
            return $query->result_array();
        }
        return NULL;
    }

    function delete_previous_permissions($admin_user_id)
    {
        $this->db->where('user_id', $admin_user_id);
        $this->db->where('permission', 'is_admin');
        $this->db->delete('user_permissions');            
    }

    function add_new_permissions($admin_user_id, $company_id) 
    {
        $data = array(
                'company_id' => $company_id ,
                'user_id' => $admin_user_id ,
                'permission' => 'is_admin'
             );
        $query = $this->db->insert('user_permissions', $data); 
        return $this->db->insert_id();
    }
    
    function get_whitelabel_partner_types()
    {
        $this->db->from('whitelabel_partner_type');
        $query = $this->db->get();
        if($query->num_rows() > 0)
        {
            return $query->result_array();
        }
        return NULL;
    }

    function get_partner_detail($partner_id = null)
    {
        $partner = null;
        if($partner_id)
        {
            $this->db->select('wp.*, wpxa.*, wpt.slug as partner_slug');
            $this->db->where('wp.id', $partner_id);
            $this->db->where('wp.status', '1');
            $this->db->from('whitelabel_partner AS wp');
            $this->db->join('whitelabel_partner_x_admin AS wpxa', "wp.id = wpxa.partner_id", "left");
            $this->db->join('whitelabel_partner_type AS wpt', "wp.type_id = wpt.id", "left");
            $this->db->group_by('wp.id');
            $query = $this->db->get();
            if($query->num_rows() > 0)
            {
                $partner = $query->result_array()[0];
            }
        }
        
        if(!$partner && $this->user_id){ // if current user is a admin of any whitelabel partner
            $this->db->select('wp.*, wpxa.*, wpt.slug as partner_slug');
            $this->db->where('wpxa.admin_id', $this->user_id);
            $this->db->where('wp.status', '1');
            $this->db->from('whitelabel_partner AS wp');
            $this->db->join('whitelabel_partner_x_admin AS wpxa', "wp.id = wpxa.partner_id", "left");
            $this->db->join('whitelabel_partner_type AS wpt', "wp.type_id = wpt.id", "left");
            $this->db->group_by('wp.id');
            $query = $this->db->get();
            if($query->num_rows() > 0)
            {
                $partner = $query->result_array()[0];
            }
        }
        
        if ($partner) { 
            $partner['admins'] = $this->get_whitelabel_admin_ids(null, $partner_id);
        }
        
        return $partner;
    }

    function get_whitelabel_partner_id($user_id)
    {
        $this->db->from('whitelabel_partner AS wp');
        $this->db->join('whitelabel_partner_x_admin AS wpxa', "wp.id = wpxa.partner_id", "left");
        $this->db->join('whitelabel_partner_type AS wpt', "wp.type_id = wpt.id", "left");
        $this->db->where('wpxa.admin_id', $user_id);
        $this->db->group_by('wp.id');
        $query = $this->db->get();
        if($query->num_rows() > 0)
        {
            return $query->row_array();
        }
        return NULL;
    }

    function get_partners_detail($partner_id){

        $this->db->from('whitelabel_partner AS wp');
        $this->db->join('users AS u', "u.id = wp.admin_user_id", "left");
        $this->db->where('wp.show_on_partners_page', 1);
        $this->db->where('wp.is_deleted', 0);

        if($this->is_self_hosted_domain == 1) {
            $this->db->where('wp.id', $partner_id);
        }
        
        $this->db->group_by('wp.id');
        $query = $this->db->get();
        if($query->num_rows() > 0)
        {
            return $query->result_array();
        }
        return NULL;
    }
}
