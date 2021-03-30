<?php

class Menu_model extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();		
    }
    
    function get_menus($filters = null){
        
        $where = "1";
        if($filters)
        {
            if (isset($filters['parent_id'])) {
                $where .= " AND parent_id = {$filters['parent_id']}";
            }
            if (isset($filters['partner_type_id'])) {
                $where .= " AND partner_type_id = {$filters['partner_type_id']}";
            }
            
            // $admins = array();
            // if(isset($filters['wp_id'])){
            //     $admins = $this->get_whitelabel_admin_ids(null, $filters['wp_id']);
            // }
            // if($this->user_id && in_array($this->user_id, $admins)){
            //     $where .= " AND (wp.id = ".($filters['wp_id'])." AND wpxa.admin_id = {$this->user_id})";
            // }elseif($this->user_id){
            //     $where .= " AND wp.id = 1 AND wpxa.admin_id != {$this->user_id}";
            // }else{
            //     $where .= " AND wp.id = 1";
            // }
        }
        $sql = "SELECT m.*
                FROM (menu AS m)
                LEFT JOIN
                    whitelabel_partner AS wp ON wp.type_id = m.partner_type_id
                LEFT JOIN 
                    whitelabel_partner_x_admin as wpxa on wp.id = wpxa.partner_id
                WHERE
                    $where
                GROUP BY
                    m.id";
        $query = $this->db->query($sql);
        return $query->result_array();
    }
    
    function get_whitelabel_admin_ids($user_id = null, $partner_id = null)
    {
        $this->db->select('wpxa.admin_id');
        $this->db->from('whitelabel_partner as wp');
        $this->db->join('whitelabel_partner_x_admin AS wpxa', "wp.id = wpxa.partner_id", "left");
        
        if($user_id){
            $this->db->join('users as u', 'u.partner_id = wp.id', 'left');
            $this->db->where('u.id', $user_id);
        }
        if($partner_id){
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
}
