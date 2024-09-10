<?php

class Company_security_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    function insert($data)
    {     
        $this->db->insert('company_security', $data);
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
    }

    function get_deatils_by_company_user($company_id = null, $user_id){

        $this->db->select('*');

        if($company_id)
            $this->db->where('company_id', $company_id);
        $this->db->where('user_id', $user_id);
        $query = $this->db->get('company_security');
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        $result_array = $query->row_array();

        return $result_array;
    }

    function update($company_id, $user_id, $data)
    {
        $this->db->where('company_id', $company_id);
        $this->db->where('user_id', $user_id);
        $this->db->update('company_security', $data);
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

    function get_first_property_partner($partner_id){
        $query = $this->db->query('select c.company_id ,c.name FROM whitelabel_partner AS wp INNER JOIN company AS c ON wp.id = c.partner_id INNER JOIN user_permissions AS up ON wp.admin_user_id = up.user_id AND up.company_id = c.company_id where c.is_deleted = 0 AND wp.id = '.$partner_id.' ORDER by up.permission_id ASC LIMIT 1');
            
        if ($this->db->_error_message()) // error checking
        show_error($this->db->_error_message());

        $result =  $query->row_array();
    
        return $result;
      
    }

    function get_vendor_companies($vendor_id){
        $sql = "SELECT
                    GROUP_CONCAT(c.company_id) as comp_ids
                FROM
                    `company` AS c
                LEFT JOIN company_subscription AS cs
                ON
                    cs.company_id = c.company_id
                WHERE
                    c.is_deleted = 0 AND cs.subscription_state IN('trialing', 'active') AND c.partner_id = $vendor_id";

        $query = $this->db->query($sql);

        $result =  $query->row_array();
    
        return $result;
    }
}

?>