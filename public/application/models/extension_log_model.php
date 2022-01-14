<?php

class Extension_log_model extends CI_Model {

	function __construct()
    {
        parent::__construct();
    }

	function create_extension_log($data)
	{
		
		$this->db->insert('extensions_log', $data);		
		
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		else
		{
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
	}
	
    function get_extension_log($logdata){
        $this->db->from('extensions_log` as el');

        if($logdata['status']){
        $this->db->where('el.status', $logdata['status']);
        }
        
        if($logdata['extension_name']){
        $this->db->where('el.extension_name',$logdata['extension_name']);
        }
        if($logdata['vendor_id']){
        $this->db->where('el.vendor_id', $logdata['vendor_id']);
        }

        if($logdata['company_id']){
           $this->db->where('el.company_id',$logdata['company_id']);
        }  
         $query = $this->db->get();

        $result = $query->row_array();
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        
        if ($query->num_rows >= 1)
        {
            return $result;
        }
        return null;

    }	

    function get_install_hours_extension($extension ,$vendor_id,$start_date,$end_date ){
        if($vendor_id){
          $vendor = "and el.vendor_id ='$vendor_id' order by el.date_time ASC";  
        }
        else{
          $vendor = 'order by el.date_time ASC';  
        }
        $sql="SELECT * FROM `extensions_log` as el where (el.status ='installed' or el.status = 'uninstalled') and el.extension_name = '$extension' and el.date_time BETWEEN '$start_date' and '$end_date'".$vendor;

        $query = $this->db->query($sql);
       

        // $this->db->from('extensions_log` as el');
        // $this->db->where('el.status', 'Installed');
        // $this->db->or_where('el.status', 'Uninstalled');
        // $this->db->where('el.extension_name',$extension);
        // $this->db->where('el.date_time >=', $start_date);
        // $this->db->where('el.date_time <=', $end_date);

        // if($vendor_id){
        //    $this->db->where('el.vendor_id',$vendor_id);
        // }  
        // $query = $this->db->get(); 
        // echo $this->db->last_query();
        // die();


       //print_r($query);die('1234');
       if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());

        if($query->num_rows() >= 1)
        {
            $result = $query->result_array();
            return $result;
        }

        return array();

    }
    function get_current_vendor_install_status($extension ,$vendor_id,$start_date,$end_date){

        if($vendor_id){
          $vendor = " and el.vendor_id ='$vendor_id' ORDER by el.date_time DESC LIMIT 1";  
        }
        else{
          $vendor = "and el.vendor_id ='0' ORDER by el.date_time DESC LIMIT 1";  
        }
        $sql="SELECT * FROM `extensions_log` as el where (el.status ='installed' or el.status = 'uninstalled') and el.extension_name = '$extension' and el.date_time BETWEEN '$start_date' and '$end_date'".$vendor;

        $query = $this->db->query($sql);
        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());

        if($query->num_rows() >= 1)
        {
            $result = $query->result_array();
            return $result;
        }

        return array();
    }

    function get_current_company_active_status($extension ,$company_id,$start_date,$end_date){
        if($company_id){
          $company = " and el.company_id ='$company_id' ORDER by el.date_time DESC LIMIT 1";  
        }
        else{
          $company = " and el.company_id ='$this->company_id' ORDER by el.date_time DESC LIMIT 1";  
        }
        $sql="SELECT * FROM `extensions_log` as el where (el.status ='Activated' or el.status = 'Deactivated') and el.extension_name = '$extension' and el.date_time BETWEEN '$start_date' and '$end_date'".$company;

        $query = $this->db->query($sql);
        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());

        if($query->num_rows() >= 1)
        {
            $result = $query->result_array();
            return $result;
        }

        return array();
    }


    function get_active_hours_extension($extension ,$company_id,$start_date,$end_date ){
        if($company_id){
          $company = "and el.company_id ='$company_id' order by el.date_time ASC";  
        }
        else{
          $company = 'order by el.date_time ASC';  
        }
        $sql="SELECT * FROM `extensions_log` as el where (el.status ='Activated' or el.status = 'Deactivated') and el.extension_name = '$extension' and el.date_time BETWEEN '$start_date' and '$end_date'".$company;

        $query = $this->db->query($sql);
       

        // $this->db->from('extensions_log` as el');
        // $this->db->where('el.status', 'Installed');
        // $this->db->or_where('el.status', 'Uninstalled');
        // $this->db->where('el.extension_name',$extension);
        // $this->db->where('el.date_time >=', $start_date);
        // $this->db->where('el.date_time <=', $end_date);

        // if($vendor_id){
        //    $this->db->where('el.company_id',$company_id);
        // }  
        // $query = $this->db->get(); 
        // echo $this->db->last_query();
        // die();


       //print_r($query);die('1234');
       if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());

        if($query->num_rows() >= 1)
        {
            $result = $query->result_array();
            return $result;
        }

        return array();

    }

    function get_vendor_company_active_extention_counts($extension,$vendor_id,$start_date , $end_date ){

        if($vendor_id){
            $vendor="and el.vendor_id = $vendor_id group by el.company_id ORDER by el.date_time DESC";
        }else{
            $vendor='group by el.company_id ORDER by el.date_time DESC';
        }
        $sql="SELECT exc.extension_name, exc.company_id,el.date_time FROM `extensions_x_company` as exc LEFT JOIN extensions_log as el on el.extension_name = exc.extension_name where el.company_id=exc.company_id and el.status='activated' and exc.is_active = 1 and exc.extension_name ='$extension' and (el.date_time < '$start_date'  OR el.date_time > '$start_date') and el.date_time < '$end_date'".$vendor;

        $query = $this->db->query($sql);
        
        $result = $query->result_array();
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        
        if ($query->num_rows >= 1)
        {
            return $result;
        }
        return array();

    }

    function vendor_company_active_extention_count_filter($vendor_id,$start_date , $end_date ){

        $sql="SELECT 
      r.extension_name,
        r.vendor_id,
      SUM(IF(DATE(r.last_active_date) > '$start_date' OR DATE(r.last_active_date) > DATE(r.last_deactive_date) OR (r.last_active_date AND IFNULL(r.last_deactive_date, 1) = 1), 1, 0)) as ActiveCount
    FROM (
      SELECT
       (
        SELECT MAX(el2.date_time)
        from extensions_log as el2 
        where 
          el2.company_id = el.company_id and 
                el2.vendor_id = el.vendor_id and 
                el2.extension_name = el.extension_name and 
          DATE(el2.date_time) < '$end_date' and
          el2.status = 'Activated'
        GROUP BY el2.company_id
        limit 1 
       ) as last_active_date,
        (
        SELECT MAX(el2.date_time)
        from extensions_log as el2 
        where 
          el2.company_id = el.company_id and 
                el2.vendor_id = el.vendor_id and 
                el2.extension_name = el.extension_name and 
          DATE(el2.date_time) < '$end_date' and
          el2.status = 'Deactivated'
        GROUP BY el2.company_id
        limit 1 
       ) as last_deactive_date,
       el.*
      from extensions_log as el 
      where el.vendor_id = $vendor_id AND DATE(el.date_time) < '$end_date'
      group by el.company_id, el.extension_name
    ) as r
    group by r.vendor_id, r.extension_name";

    $query = $this->db->query($sql);
        
        $result = $query->result_array();
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        
        if ($query->num_rows >= 1)
        {
            return $result;
        }
        return array();
    }

    function vendor_company_active_extention_count($start_date , $end_date ){

        $sql="SELECT 
          fr.extension_name,
            SUM(fr.ActiveCount) as ActiveCount
        FROM
        (
            SELECT 
            r.extension_name,
            r.vendor_id,
            SUM(IF(DATE(r.last_active_date) > '$start_date' OR DATE(r.last_active_date) > DATE(r.last_deactive_date) OR (r.last_active_date AND IFNULL(r.last_deactive_date, 1) = 1), 1, 0)) as ActiveCount
          FROM (
            SELECT
             (
              SELECT MAX(el2.date_time)
              from extensions_log as el2 
              where 
                el2.company_id = el.company_id and 
                el2.vendor_id = el.vendor_id and 
                el2.extension_name = el.extension_name and 
                DATE(el2.date_time) < '$end_date' and
                el2.status = 'Activated'
              GROUP BY el2.company_id
              limit 1 
             ) as last_active_date,
              (
              SELECT MAX(el2.date_time)
              from extensions_log as el2 
              where 
                el2.company_id = el.company_id and 
                el2.vendor_id = el.vendor_id and 
                el2.extension_name = el.extension_name and 
                DATE(el2.date_time) < '$end_date' and
                el2.status = 'Deactivated'
              GROUP BY el2.company_id
              limit 1 
             ) as last_deactive_date,
             el.*
            from extensions_log as el 
            where DATE(el.date_time) < '$end_date'
            group by el.company_id, el.extension_name
          ) as r
          group by r.vendor_id, r.extension_name
        ) as fr
        GROUP BY fr.extension_name";

        $query = $this->db->query($sql);
        
        $result = $query->result_array();
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        
        if ($query->num_rows >= 1)
        {
            return $result;
        }
        return array();
    }

    // function get_current_vendor_active_extension($extension,$vendor_id,$start_date, $end_date){
    //     if($vendor_id){
    //         $vendor="and el.vendor_id = $vendor_id group by el.company_id ORDER by el.date_time DESC";
    //     }else{
    //         $vendor='group by el.company_id ORDER by el.date_time DESC';
    //     }
    //     $sql="SELECT exc.extension_name, exc.company_id,el.date_time FROM `extensions_x_company` as exc LEFT JOIN extensions_log as el on el.extension_name = exc.extension_name where el.company_id=exc.company_id and el.status='activated' and exc.is_active = 1 and exc.extension_name ='channex_integration' and el.date_time < CAST('$start_date' AS date) ".$vendor;

    //     $query = $this->db->query($sql);
        
    //     $result = $query->result_array();
        
    //     if ($this->db->_error_message())
    //     {
    //         show_error($this->db->_error_message());
    //     }
        
    //     if ($query->num_rows >= 1)
    //     {
    //         return $result;
    //     }
    //     return array();
    // }

// function get_vendor_company_extention_hours_counts($extension,$vendor_id,$start_date , $end_date ){

//         if($vendor_id){
//             $vendor="and el.vendor_id = $vendor_id  ORDER by el.date_time ASC";
//         }else{
//             $vendor=' ORDER by el.date_time ASC';
//         }
//         $sql="SELECT el.* FROM `extensions_x_company` as exc LEFT JOIN extensions_log as el on el.extension_name = exc.extension_name where el.company_id = exc.company_id and (el.status='activated' OR el.status='deactivated') and exc.extension_name ='$extension' AND el.date_time BETWEEN '$start_date' and '$end_date'".$vendor;

//         $query = $this->db->query($sql);
        
//         $result = $query->result_array();
        
//         if ($this->db->_error_message())
//         {
//             show_error($this->db->_error_message());
//         }
        
//         if ($query->num_rows >= 1)
//         {
//             return $result;
//         }
//         return array();

//     }
    function vendor_company_extention_hours_count_filter($vendor_id,$start_date , $end_date ){

        $sql="SELECT
     (
        SELECT MAX(el2.date_time)
        from extensions_log as el2 
        where 
            el2.company_id = el.company_id and 
            el2.vendor_id = el.vendor_id and 
            el2.extension_name = el.extension_name and 
            DATE(el2.date_time) < '$end_date' and
            el2.status = 'Activated'
        GROUP BY el2.company_id
        limit 1 
     ) as last_active_date,
      (
        SELECT MAX(el2.date_time)
        from extensions_log as el2 
        where 
            el2.company_id = el.company_id and 
            el2.vendor_id = el.vendor_id and 
            el2.extension_name = el.extension_name and 
            DATE(el2.date_time) < '$end_date' and
            el2.status = 'Deactivated'
        GROUP BY el2.company_id
        limit 1 
     ) as last_deactive_date,
     
    GROUP_CONCAT(IF(el.status = 'Activated', CONCAT('Activated/', el.date_time), CONCAT('Deactivated/', el.date_time))) as times,
     el.extension_name,
     el.vendor_id,
     el.company_id
    from extensions_log as el 
    where 
    DATE(el.date_time) < '$end_date' and DATE(el.date_time) >= '$start_date'
    and vendor_id = $vendor_id and  el.company_id !=0
    group by el.company_id, el.extension_name";

    $query = $this->db->query($sql);
        
        $result = $query->result_array();
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        
        if ($query->num_rows >= 1)
        {
            return $result;
        }
        return array();
    }

    function vendor_company_extention_hours_count($start_date , $end_date ){

        $sql="SELECT
     (
        SELECT MAX(el2.date_time)
        from extensions_log as el2 
        where 
            el2.company_id = el.company_id and 
            el2.vendor_id = el.vendor_id and 
            el2.extension_name = el.extension_name and 
            DATE(el2.date_time) < '$end_date' and
            el2.status = 'Activated'
        GROUP BY el2.company_id
        limit 1 
     ) as last_active_date,
      (
        SELECT MAX(el2.date_time)
        from extensions_log as el2 
        where 
            el2.company_id = el.company_id and 
            el2.vendor_id = el.vendor_id and 
            el2.extension_name = el.extension_name and 
            DATE(el2.date_time) < '$end_date' and
            el2.status = 'Deactivated'
        GROUP BY el2.company_id
        limit 1 
     ) as last_deactive_date,
     
    GROUP_CONCAT(IF(el.status = 'Activated', CONCAT('Activated/', el.date_time), CONCAT('Deactivated/', el.date_time))) as times,
     el.extension_name,
     el.vendor_id,
     el.company_id
    from extensions_log as el 
    where 
    DATE(el.date_time) < '$end_date' and DATE(el.date_time) >= '$start_date'
    and el.company_id !=0
    group by el.company_id, el.extension_name";

    $query = $this->db->query($sql);
        
        $result = $query->result_array();
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        
        if ($query->num_rows >= 1)
        {
            return $result;
        }
        return array();
    }
    
    function get_vendor_extensions($vendor_id = null)
    {
        $this->db->from('extensions_x_vendor');
        if ($vendor_id) {
            $this->db->where('vendor_id', $vendor_id);
        }
        $this->db->group_by('extension_name');
        $query = $this->db->get();
        
        if($query->num_rows() >= 1)
        {
            return $query->result_array();
        }
        
        return NULL;
    }

    function get_vendor_install_extensions($vendor_id = null)
    {
        $this->db->from('extensions_x_vendor');
        $this->db->where('is_installed', 1);
        if ($vendor_id) {
            $this->db->where('vendor_id', $vendor_id);
        }
        $query = $this->db->get();
        
        if($query->num_rows() >= 1)
        {
            return $query->result_array();
        }
        
        return NULL;
    }

    function get_vendor_active_extensions()
    {   

        $this->db->select('exc.extension_name, exc.company_id , wp.id as vendor_admin_id, exc.is_active');
        $this->db->from('extensions_x_company` as exc');
        $this->db->join('user_permissions as up', 'exc.company_id =  up.company_id', 'left');
        $this->db->join('whitelabel_partner as wp', 'wp.admin_user_id = up.user_id', 'left');
        $this->db->where('up.permission', 'is_owner');
        $this->db->where('exc.is_active', 1);
        $this->db->order_by('up.user_id');
        
        $query = $this->db->get();
        
        if($query->num_rows() >= 1)
        {
            return $query->result_array();
        }
        
        return NULL;
    }
    
    function get_vendor_company_extensions($company_id =null)
    {   

        $this->db->select('exc.extension_name, exc.company_id , wp.id, exc.is_active');
        $this->db->from('extensions_x_company` as exc');
        $this->db->join('user_permissions as up', 'exc.company_id =  up.company_id', 'left');
        $this->db->join('whitelabel_partner as wp', 'wp.admin_user_id = up.user_id', 'left');
        $this->db->where('up.permission', 'is_owner');
        if ($company_id) {
        $this->db->where('exc.company_id',$company_id);
        }
        $this->db->group_by('exc.extension_name');
        $this->db->order_by('up.user_id');
        
        $query = $this->db->get();
        
        if($query->num_rows() >= 1)
        {
            return $query->result_array();
        }
        
        return NULL;
    }
}


/* End of file - Extension_log_model.php */