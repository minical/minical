<?php

class Employee_log_model extends CI_Model {

    function __construct()
    {        
        parent::__construct();
    }		
	
	function insert_log($data)
    {
	   // $data = (object) $data;        
	    $this->db->insert("employee_log", $data);
		
	/*	if ($this->db->_error_message()) 
		{
			show_error($this->db->_error_message());
		}		
    
        $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
		$result = $query->result_array();
        if(isset($result[0]))
        {  
            return $result[0]['last_id'];
        }
		else
        {  
            return null;
        }	*/
    }	
    
    function update_log($data,$condition)
    {
        $this->db->where($condition);
        $this->db->update('employee_log',$data);
    }
    
    function get_log($condition = array())
    {
        if($condition)
        {
            $this->db->where($condition);
        }
        $query = $this->db->get('employee_log');
        return $query->result_array();
    }
    
    function get_logged_in_out_time($company_id, $from_date, $to_date, $userId, $room_deleted_log = null){
        $where = '';
        if($company_id)
            $where .= "AND up.current_company_id = '$company_id'";
        if($userId)
            $where .= "AND el.user_id = '$userId'";
        
        $date_sql = "AND el.selling_date >= '".$from_date."' AND el.selling_date <= '".$to_date."'";
        $room_log_not_included = "AND NOT( el.log LIKE '%Room Hidden%' OR  el.log LIKE  '%Room Restored%' OR el.log LIKE '%Room Deleted%' ) ";
        if($room_deleted_log && $from_date && $to_date)
        {
            $prev_date = date("Y-m-d", strtotime($from_date));
            $next_date = date("Y-m-d", strtotime($to_date));
            $date_sql = "AND DATE(el.selling_date) >= '$prev_date' AND DATE(el.selling_date) <= '$next_date' ";
            $room_log_not_included = "";
        }
        $sql = "SELECT
                el.*,
                up.first_name,
                up.last_name
              FROM
                employee_log as el,
                users AS u,
                user_profiles AS up
              WHERE
                el.user_id = u.id AND u.id = up.user_id AND
				u.id != '".SUPER_ADMIN_USER_ID."'
                $date_sql
                $room_log_not_included
                $where
              ORDER BY
                el.date_time ASC";
        $query = $this->db->query($sql);
        $result = $query->result();
        return $result;
    }
    function get_user_permission($company_id,$user_id)
    {
        $query = "SELECT * FROM user_permissions WHERE company_id='$company_id' AND user_id='$user_id'";
        $result = $this->db->query($query);
        $permission = array();
        foreach ($result->result_array() as $row)
        {
           array_push($permission,$row['permission']);
        }
        
        // company's whitelabel partner will have 'is_admin' permissions, this permission is not stored in user_permissions table 
        $sql = "SELECT 
                    'is_admin' as permission
                FROM user_permissions as up
                LEFT JOIN 
                    company as c on c.company_id = up.company_id
                LEFT JOIN 
                    whitelabel_partner as wp on wp.id = c.partner_id
                LEFT JOIN 
                    whitelabel_partner_x_admin as wpxa on wp.id = wpxa.partner_id
                WHERE 
                    up.permission = 'is_owner' AND
                    up.company_id = '$company_id' AND
                    (wpxa.admin_id = '$user_id' OR '".SUPER_ADMIN_USER_ID."' = '$user_id')
                GROUP BY up.company_id";
        
        $query = $this->db->query($sql);
		if($query->num_rows() >= 1)
		{
            $results = $query->result_array();
            array_push($permission, $results[0]['permission']);
		}
        
        return $permission;
        
    }
    
    function get_all_user_permission()
    {
        $query = "SELECT * FROM user_permissions ";
        $result = $this->db->query($query);
        $permission = array();
        foreach ($result->result_array() as $row)
        {
           array_push($permission[$row['user_id']],$row['permission']);
        }
        return $permission;
        
    }
}