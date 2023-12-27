<?php

class Admin_model extends CI_Model {
	
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
	
	function create_company($data)
	{
		$data = (object) $data;        
        $this->db->insert("company", $data);
		
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
	
	function get_company_admin_panel_info($company_id, $filters = null)
	{
        
         $sql = "SELECT 
                        capi.creation_date,
					    capi.conversion_date,
                        c.name, 
                        c.email, 
					    c.city,
					    c.country,
					    c.selling_date, 
					    c.is_setup, 
					    c.number_of_rooms, 
					    c.time_zone, 
					    cs.invoice_link,
					    cs.balance,
					    cs.chargify_subscription_link,
					    cs.subscription_state,
					    cs.renewal_cost,
					    cs.renewal_period,
					    cs.payment_method,
					    cs.subscription_id,
                        IFNULL(wp.username, 'Minical') as  partner,
					    capi.company_id,
					    capi.churn_date,
					    capi.utm_source,
						(DATEDIFF(NOW(), IF(c.last_login='0000-00-00', capi.creation_date, c.last_login))) as idle
                    FROM
						company_admin_panel_info as capi
                    LEFT JOIN company as c ON c.company_id = capi.company_id
                    LEFT JOIN company_subscription as cs ON cs.company_id = c.company_id
                    LEFT JOIN user_permissions as up ON c.company_id = up.company_id AND (up.permission = 'is_owner')
                    LEFT JOIN whitelabel_partner as wp ON c.partner_id = wp.id
                    LEFT JOIN whitelabel_partner_x_admin as wpxa ON wpxa.partner_id = wp.id
                    ";
            
        $where = "";
        if($filters['data_type'] == 'property_list')
        {
            $where .= " WHERE 1 ";
            if($filters['state'] == '')
            {
                $where .= " AND cs.subscription_state = 'active'";
            }
            
            if($filters['state'] != 'all' && $filters['state'] != '')
            {
                $where .= " AND cs.subscription_state = '".$filters['state']."'";
            }
            
            if($filters['country'] != '')
            {
                $where .= " AND c.country = '".$filters['country']."'";
            }
            
            if($filters['partner'] != '')
            {
                $where .= " AND c.partner_id = '".$filters['partner']."'";
            }
            
            if($filters['from_date'] != '' && $filters['to_date'] != '')
            {
                $where .= " AND capi.creation_date >= '".$filters['from_date']."' AND
                        capi.creation_date <= '".$filters['to_date']."'";
            }
        }
        else if($filters['data_type'] == 'sign_ups')
        {
            if($filters['report_type'])
            {
                if($filters['is_filtering'])
                {
                    $where .= " WHERE c.country = '".$filters['country']."' AND 
                            capi.creation_date >= '".$filters['sdate']."' AND
                            capi.creation_date <= '".$filters['edate']."'";
                }
                else
                {
                    $where .= " WHERE c.country = '".$filters['country']."'";
                }
            }
            else
            {
                if($filters['is_filtering'])
                {
                    $where .= " WHERE capi.creation_date >= '".$filters['sdate']."' AND
                            capi.creation_date <= '".$filters['edate']."'";
                }
                else
                {
                    $where .= " WHERE YEAR(capi.creation_date) = ".$filters['year']." AND
                            MONTH(capi.creation_date) = ".$filters['month'];
                }
            }
        }
        else if($filters['data_type'] == 'conversions')
        {     
            if($filters['report_type'])
            {
                if($filters['is_filtering'])
                {
                    $where .= " WHERE cs.subscription_state = 'active' AND 
                            c.country = '".$filters['country']."' AND 
                            capi.conversion_date >= '".$filters['sdate']."' AND
                            capi.conversion_date <= '".$filters['edate']."'";
                }
                else
                {
                    $where .= " WHERE cs.subscription_state = 'active' AND c.country = '".$filters['country']."'
                        ";
                }
            }
            else
            {
                if($filters['is_filtering'])
                {
                    $where .= " WHERE cs.subscription_state = 'active' AND
                            capi.conversion_date >= '".$filters['sdate']."' AND
                            capi.conversion_date <= '".$filters['edate']."'";
                }
                else
                {
                    $where .= " WHERE cs.subscription_state = 'active' AND YEAR(capi.conversion_date) = ".$filters['year']." AND MONTH(capi.conversion_date) = ".$filters['month']."
                        ";
                }
            }
        }
        elseif($filters['data_type'] == 'active_trials')
        {
            $where .= " INNER JOIN
                    (
                        SELECT tbl.comId as comId 
                        FROM 
                        (
                            SELECT count(bk.company_id) AS cont, bk.company_id as comId 
                            FROM booking as bk GROUP BY bk.company_id
                        ) as tbl 
                        WHERE tbl.cont >= 5
                    ) AS bk ON bk.comId = capi.company_id";
            
            if($filters['is_filtering'])
            {
                $where .= " WHERE cs.subscription_state = 'trialing' AND 
                        capi.creation_date >= '".$filters['sdate']."' AND 
                        capi.creation_date <= '".$filters['edate']."'";
            }
            else
            {
                $where .= " WHERE cs.subscription_state = 'trialing' AND
                        YEAR(capi.creation_date) = ".$filters['year']." AND
                        MONTH(capi.creation_date) = ".$filters['month'];
            }
        }
        else if($filters['data_type'] == 'churns')
        {       
            if($filters['report_type'])
            {
                if($filters['is_filtering'])
                {
                    $where .= " WHERE 
                            capi.conversion_date IS NOT NULL AND capi.conversion_date != '' AND capi.conversion_date != '0000-00-00' AND cs.subscription_state = 'canceled' AND 
                            c.country = '".$filters['country']."' AND 
                            capi.churn_date >= '".$filters['sdate']."' AND
                            capi.churn_date <= '".$filters['edate']."'";
                }
                else
                {
                    $where .= " WHERE capi.conversion_date IS NOT NULL AND capi.conversion_date != '' AND capi.conversion_date != '0000-00-00' AND cs.subscription_state = 'canceled' AND c.country = '".$filters['country']."' ";
                }
            }
            else
            {
                if($filters['is_filtering'])
                {
                    $where .= " WHERE 
                            capi.conversion_date IS NOT NULL AND capi.conversion_date != '' AND capi.conversion_date != '0000-00-00' AND cs.subscription_state = 'canceled' AND 
                            capi.churn_date >= '".$filters['sdate']."' AND
                            capi.churn_date <= '".$filters['edate']."'";
                }
                else
                {
                    $where .= " WHERE capi.conversion_date IS NOT NULL AND capi.conversion_date != '' AND capi.conversion_date != '0000-00-00' AND cs.subscription_state = 'canceled' AND YEAR(capi.churn_date) = ".$filters['year']." AND MONTH(capi.churn_date) = ".$filters['month']." ";
                }
            }
        }
        else if($filters['data_type'] == 'paying_customers')
        {    
            if($filters['report_type'])
            {
                if($filters['is_filtering'])
                {
                    $where .= " WHERE cs.subscription_state = 'active' AND
                            c.country = '".$filters['country']."' AND 
                            DATE(capi.conversion_date) <= '".$filters['edate']."'";
                }
                else
                {
                    $where .= " WHERE cs.subscription_state = 'active' AND c.country = '".$filters['country']."'
                        ";
                }
            }
            else
            {
                if($filters['is_filtering'])
                {
                    $where .= " WHERE cs.subscription_state = 'active' AND
                            DATE(capi.conversion_date) <= '".$filters['edate']."'";
                }
                else
                {
                    $where .= " WHERE cs.subscription_state = 'active' AND DATE(capi.conversion_date) <= '".$filters['year']."-".$filters['month']."-31' AND DATE(capi.conversion_date) != '0000-00-00'
                        ";
                }
            }
        }
        else if($filters['data_type'] == 'months')
        {                    
            if($filters['is_filtering'])
            {
                $where .= " WHERE 
                        capi.creation_date >= '".$filters['sdate']."' AND
                        capi.creation_date <= '".$filters['edate']."'";
            }
            else
            {
                $where .= " WHERE YEAR(capi.creation_date) = ".$filters['year']." AND MONTH(capi.creation_date) = ".$filters['month']."
					";
            }
        }
        else if($filters['data_type'] == 'country')
        {                    
            if($filters['is_filtering'])
            {
                $where .= " WHERE c.country = '".$filters['country']."' AND
                        capi.creation_date >= '".$filters['sdate']."' AND
                        capi.creation_date <= '".$filters['edate']."'";
            }
            else
            {
                $where .= " WHERE c.country = '".$filters['country']."'
					";
            }
        }
        
        $where = $where ? $where : "WHERE 1";
        
        $permissions = $this->session->userdata("permissions");
        $is_salesperson = in_array("is_salesperson", $permissions) ? 1 : 0;
		
		if ($this->user_id)
		{
			$where .= " AND (
                            (wpxa.admin_id = up.user_id AND wpxa.admin_id = '$this->user_id') OR
                            (wpxa.admin_id = '$this->user_id') OR
                            (up.user_id = '$this->user_id') OR
                            ('$this->user_id' = '".SUPER_ADMIN_USER_ID."') OR
                            ($is_salesperson = 1)
                        )";
						
		}
        $group_by = "GROUP BY capi.company_id";
        $sql .= $where;
        $sql .= $group_by;
        $query = $this->db->query($sql);
        if($query->num_rows() >= 1)
		{
			$result =  $query->result();
            return $result;
		}
        
		$this->db->from('company_admin_panel_info');
		$this->db->where('company_id', $company_id);
		
		$query = $this->db->get();
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		if($query->num_rows() >= 1)
		{
			$result = $query->result_array();
			return $result[0];
		}
		
		return array();
	}
    
	function get_tags($company_id)
	{
		$this->db->select('tag');
		$this->db->from('company_x_tag');
		$this->db->where('company_id', $company_id);
		
		$query = $this->db->get();
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		$result_array = $query->result_array();
		$result = array();
		foreach($result_array as $r => $tag)
		{
			$result[] = $tag['tag'];
		}
		return $result;
	}
	
	function get_mailing_list() {
	
		
		$selling_date = $this->session->userdata('current_selling_date');
		
		$month_before = Date("Y-m-d", strtotime("-1 month", strtotime($selling_date)));
					
		$this->db->select('			
			p.first_name,
			p.last_name,			
			u.email,
			c.name,
			c.company_id
			');
		$this->db->from('
			company as c, 
			user_permissions as up, 
			users as u, 
			user_profiles as p
			');
		$this->db->where("c.company_id = up.company_id AND up.user_id = u.id AND u.id = p.user_id ");
		
		$this->db->group_by("u.email");
		
		$query = $this->db->get();
		//echo $this->db->last_query();
			
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		if($query->num_rows() >= 1)
		{
			return $query->result();
		}
		
		return NULL;
	
	}
	
	
	function insert_company_x_tag($company_id, $tag)
	{
		$data = array("company_id" => $company_id, "tag" => $tag);
        $insert_query = $this->db->insert_string('company_x_tag', $data);
		$insert_query = str_replace('INSERT INTO','INSERT IGNORE INTO',$insert_query);
		$this->db->query($insert_query);
	}
	
		
	function delete_company_x_tag($company_id, $tag)
	{
		$this->db->where("company_id", $company_id);
		$this->db->where("tag", $tag);
		$this->db->delete("company_x_tag");
		if ($this->db->_error_message()) 
		{
			show_error($this->db->_error_message());
		}
	}
	
	function insert_company_admin_panel_info($data)
	{
		$data = (object) $data;
		
		$this->db->insert('company_admin_panel_info', $data);
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
		
	}
	
	function update_company_admin_panel_info($company_id, $data)
	{
		$data = (object) $data;
		
		$this->db->where('company_id', $company_id);
		$this->db->update('company_admin_panel_info', $data);
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
		
		return TRUE;
	}
	
	
	//Returns array of objects where each object represents a row from query
	function get_monthly_report($from_date = null, $to_date = null)
	{
        if($from_date && $to_date)
        {
            $sql1 = "SELECT year, month, SUM(creation_count) AS cre_count FROM (SELECT
                        COUNT(company_id) AS creation_count, YEAR(capi.creation_date) as year, MONTH(capi.creation_date) as month
                    FROM
                        company_admin_panel_info AS capi
                    LEFT JOIN
                        company_subscription AS cs ON cs.company_id = capi.company_id
                    WHERE
                        capi.creation_date >= DATE('$from_date') AND capi.creation_date <= DATE('$to_date') AND cs.subscription_state != 'deleted'
                    GROUP BY
                        YEAR(capi.creation_date),
                        MONTH(capi.creation_date)) AS creation_count1";
            $query1 = $this->db->query($sql1);
            $result1 =  $query1->result_array();

            $sql2 = "SELECT SUM(conversion_count) AS conv_count FROM (SELECT
                        COUNT(capi2.company_id) AS conversion_count
                    FROM
                        company_admin_panel_info AS capi2
                    LEFT JOIN
                        company_subscription AS cs ON cs.company_id = capi2.company_id
                    WHERE
                        cs.subscription_state = 'active' AND capi2.conversion_date <= DATE('$to_date')
                    GROUP BY
                        YEAR(capi2.conversion_date),
                        MONTH(capi2.conversion_date)) AS conversion_count1";
            $query2 = $this->db->query($sql2);
            $result2 =  $query2->result_array();
            
            $sql3 = "SELECT SUM(churn_count) AS chn_count FROM (SELECT
                        COUNT(capi3.company_id) AS churn_count
                    FROM
                        company_admin_panel_info AS capi3
                    LEFT JOIN
                        company_subscription AS cs ON cs.company_id = capi3.company_id
                    WHERE
                        capi3.conversion_date IS NOT NULL AND capi3.conversion_date != '' AND capi3.conversion_date != '0000-00-00' AND cs.subscription_state = 'canceled' AND 
                        capi3.churn_date >= DATE('$from_date') AND capi3.churn_date <= DATE('$to_date') AND cs.subscription_state != 'deleted'
                    GROUP BY
                        YEAR(capi3.churn_date),
                        MONTH(capi3.churn_date)) AS churn_count1";
            $query3 = $this->db->query($sql3);
            $result3 =  $query3->result_array();
            
            $sql4 = "SELECT year, month, SUM(conversion_count) AS bw_conv_count FROM (SELECT
                        COUNT(capi2.company_id) AS conversion_count, YEAR(capi2.conversion_date) as year, MONTH(capi2.conversion_date) as month
                    FROM
                        company_admin_panel_info AS capi2
                    LEFT JOIN
                        company_subscription AS cs ON cs.company_id = capi2.company_id
                    WHERE
                        cs.subscription_state = 'active' AND capi2.conversion_date >= DATE('$from_date') AND capi2.conversion_date <= DATE('$to_date')
                    GROUP BY
                        YEAR(capi2.conversion_date),
                        MONTH(capi2.conversion_date)) AS conversion_count4";
            $query4 = $this->db->query($sql4);
            $result4 =  $query4->result_array();
            
            $sql5 = "SELECT SUM(active_trial_count) AS act_trial_count
                    FROM (SELECT
                        COUNT(capi4.company_id) AS active_trial_count
                    FROM
                        company_admin_panel_info AS capi4
                    LEFT JOIN
                        company_subscription AS cs ON cs.company_id = capi4.company_id
                    INNER JOIN
                       (SELECT tbl.comId as comId FROM (SELECT count(bk.company_id) AS cont, bk.company_id as comId FROM booking as bk GROUP BY bk.company_id) as tbl WHERE tbl.cont >= 5) AS bk ON bk.comId = capi4.company_id
                    WHERE
                        cs.subscription_state = 'trialing' AND capi4.creation_date >= DATE('$from_date') AND capi4.creation_date <= DATE('$to_date')
                    GROUP BY
                        YEAR(capi4.creation_date),
                        MONTH(capi4.creation_date)) AS active_trial_count1";
            $query5 = $this->db->query($sql5);
            $result5 =  $query5->result_array();
            
            $final_result = array('active_trial_count' => $result5[0], 'creation_count' => $result1[0], 'conversion_count' => $result2[0], 'bw_conversion_count' => $result4[0], 'churn_count' => $result3[0]);
            return $final_result;
        }
        
		// get total sign ups
		$sql = "
			SELECT revenue.renewal_cost,revenue.renewal_period , created.year, created.month, creation_count, conversion_count, churn_count, active_trial_count

			from
				(
					select YEAR(capi.creation_date) as year, MONTH(capi.creation_date) as month, count(capi.company_id) as creation_count
			        from
						company_admin_panel_info as capi
                    LEFT JOIN company_subscription as cs ON cs.company_id = capi.company_id
                    WHERE cs.subscription_state != 'deleted'
					group by
						YEAR(capi.creation_date), MONTH(capi.creation_date)
				) as created
			left join
				(
					select YEAR(capi2.conversion_date) as year, MONTH(capi2.conversion_date) as month, count(capi2.company_id) as conversion_count
			        from
						company_admin_panel_info as capi2
                    LEFT JOIN company_subscription as cs ON cs.company_id = capi2.company_id
                    WHERE cs.subscription_state = 'active' || cs.subscription_state = 'canceled'
					group by
						YEAR(capi2.conversion_date), MONTH(capi2.conversion_date)
				) as converted ON converted.year = created.year AND converted.month = created.month 
			left join
				(
					select YEAR(capi3.churn_date) as year, MONTH(capi3.churn_date) as month, count(capi3.company_id) as churn_count
			        from
						company_admin_panel_info as capi3
                    LEFT JOIN company_subscription as cs ON cs.company_id = capi3.company_id
                    WHERE 
                        capi3.conversion_date IS NOT NULL AND capi3.conversion_date != '' AND capi3.conversion_date != '0000-00-00' AND cs.subscription_state = 'canceled'
					group by
						YEAR(capi3.churn_date), MONTH(capi3.churn_date)
				) as churned ON churned.year = created.year AND churned.month = created.month
            left join
				(
					SELECT
                        YEAR(capi4.creation_date) AS YEAR, MONTH(capi4.creation_date) AS MONTH, COUNT(bk.comId) AS active_trial_count
                    FROM
                      company_admin_panel_info AS capi4

                    LEFT JOIN
                      company_subscription AS cs ON cs.company_id = capi4.company_id
                    LEFT JOIN
                       (SELECT tbl.comId as comId FROM (SELECT count(bk.company_id) AS cont, bk.company_id as comId FROM booking as bk GROUP BY bk.company_id) as tbl WHERE tbl.cont >= 5) AS bk ON bk.comId = capi4.company_id
                      WHERE
                      cs.subscription_state = 'trialing'
                    GROUP BY
                      YEAR(capi4.creation_date), MONTH(capi4.creation_date)
				) as active_trial ON active_trial.year = created.year AND active_trial.month = created.month
            left join
				(
					SELECT 
						SUM(IF((cs.renewal_period = 'year' OR cs.renewal_period = '1 year' OR cs.renewal_period = '12 month' OR cs.renewal_period = '12months'  OR cs.renewal_period = '12 months'), 
							(cs.renewal_cost / 12), 
							(
								IF(cs.renewal_period = '6 month', 
								(cs.renewal_cost / 6), 
								(
									IF(cs.renewal_period = '3 month', 
									(cs.renewal_cost / 3), 
									cs.renewal_cost)
								))
							))
						) as renewal_cost, 
						cs.renewal_period,
						YEAR(capi5.conversion_date) as year, 
						MONTH(capi5.conversion_date) as month
			        from
						company_admin_panel_info as capi5
                    LEFT JOIN company_subscription as cs ON cs.company_id = capi5.company_id
                    WHERE cs.subscription_state = 'active'
					group by
						YEAR(capi5.conversion_date), MONTH(capi5.conversion_date)
				) as revenue ON revenue.year = created.year AND revenue.month = created.month 
		";
		
		$query = $this->db->query($sql);	
		//echo $this->db->last_query();
			
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		$result =  $query->result_array();
		
		return $result;
	}	
	
	function get_regional_report($from_date = null, $to_date = null)
	{
        if($from_date && $to_date)
        {
            $sql1 = "SELECT c.country,
                            COUNT(capi.company_id) AS creation_count
                        FROM
                            company_admin_panel_info AS capi
                        LEFT JOIN
                            company AS c ON c.company_id = capi.company_id
                        LEFT JOIN
                            company_subscription AS cs ON cs.company_id = capi.company_id
                        WHERE
                            capi.creation_date >= DATE('$from_date') AND capi.creation_date <= DATE('$to_date') AND c.country != '' AND cs.subscription_state != 'deleted'
                        GROUP BY c.country ORDER BY creation_count DESC";
            $query1 = $this->db->query($sql1);
            $result1 =  $query1->result_array();

            $sql2 = "SELECT c.country,
                            COUNT(capi2.company_id) AS conversion_count
                        FROM
                            company_admin_panel_info AS capi2
                        LEFT JOIN
                            company AS c ON c.company_id = capi2.company_id
                        LEFT JOIN
                            company_subscription AS cs ON cs.company_id = capi2.company_id
                        WHERE
                            cs.subscription_state = 'active' AND capi2.conversion_date >= DATE('$from_date') AND capi2.conversion_date <= DATE('$to_date')
                        GROUP BY c.country";
            $query2 = $this->db->query($sql2);
            $result2 =  $query2->result_array();
            
            $sql3 = "SELECT c.country,
                            COUNT(capi3.company_id) AS churn_count
                        FROM
                            company_admin_panel_info AS capi3
                        LEFT JOIN
                            company AS c ON c.company_id = capi3.company_id
                        LEFT JOIN
                            company_subscription AS cs ON cs.company_id = capi3.company_id
                        WHERE
                            capi3.conversion_date IS NOT NULL AND capi3.conversion_date != '' AND capi3.conversion_date != '0000-00-00' AND
                            cs.subscription_state = 'canceled' AND capi3.churn_date >= DATE('$from_date') AND capi3.churn_date <= DATE('$to_date')
                        GROUP BY c.country";
            $query3 = $this->db->query($sql3);
            $result3 =  $query3->result_array();
            
            $final = $final1 = array();            
            
            foreach($result2 as $r2 => $v2)
            {
                $final[$result2[$r2]['country']] = $v2;
            }
            
            foreach($result3 as $r3 => $v3)
            {
                $final1[$result3[$r3]['country']] = $v3;
            }

            foreach($result1 as $r1 => $v1)
            {
                if(array_key_exists($result1[$r1]['country'], $final)){
                    $result1[$r1]['conversion_count'] = $final[$result1[$r1]['country']]['conversion_count'];
                }
                else
                {
                    $result1[$r1]['conversion_count'] = '';
                }
                
                if(array_key_exists($result1[$r1]['country'], $final1)){
                    $result1[$r1]['churn_count'] = $final1[$result1[$r1]['country']]['churn_count'];
                }
                else{
                    $result1[$r1]['churn_count'] = '';
                }
            }
           
            $final_result = $result1;
            return $final_result;
        }
        
		// get total sign ups
		$sql = "
			SELECT created.country, created.year, created.month, creation_count, conversion_count, churn_count
			FROM
				(
					SELECT YEAR(capi.creation_date) as year, MONTH(capi.creation_date) as month, count(capi.company_id) as creation_count, c.country as country
			        FROM
						company_admin_panel_info as capi
                    LEFT JOIN company as c ON c.company_id = capi.company_id
                    LEFT JOIN company_subscription as cs ON cs.company_id = capi.company_id
                    WHERE c.country != '' and cs.subscription_state != 'deleted'
					group by
						c.country
				) as created
			LEFT JOIN
				(
					SELECT YEAR(capi2.conversion_date) as year, MONTH(capi2.conversion_date) as month, count(capi2.company_id) as conversion_count, c.country as country
			        FROM
						company_admin_panel_info as capi2
                    LEFT JOIN company_subscription as cs ON cs.company_id = capi2.company_id
                    LEFT JOIN company as c ON c.company_id = capi2.company_id
                    WHERE cs.subscription_state = 'active' AND c.country != ''
					group by
						c.country
				) as converted ON converted.country = created.country
            LEFT JOIN
				(
					SELECT YEAR(capi3.churn_date) as year, MONTH(capi3.churn_date) as month, count(capi3.company_id) as churn_count, c.country as country
			        FROM
						company_admin_panel_info as capi3
                    LEFT JOIN company_subscription as cs ON cs.company_id = capi3.company_id
					LEFT JOIN company as c ON c.company_id = capi3.company_id
                    WHERE 
                        capi3.conversion_date IS NOT NULL AND capi3.conversion_date != '' AND capi3.conversion_date != '0000-00-00' AND 
                        cs.subscription_state = 'canceled' AND c.country != ''
					group by
						c.country
				) as churned ON churned.country = created.country
                ORDER BY creation_count DESC
		";
		
		$query = $this->db->query($sql);	
		//echo $this->db->last_query();
			
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		$result =  $query->result_array();
		
		return $result;
	}	
    
	//Returns array of objects where each object represents a row from query
	function get_funnel_report()
	{
		// get total sign ups
		$sql = "
			SELECT 
				count(DISTINCT c.company_id) as total_sign_ups,
				DATE_FORMAT( capi.creation_date, '%Y-%m' ) as starting_date
			FROM
				company as c, 
				company_admin_panel_info as capi
			WHERE
				c.company_id = capi.company_id
			GROUP BY starting_date
			ORDER BY starting_date ASC
		";
		
		$query = $this->db->query($sql);	
		//echo $this->db->last_query();
			
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		$result =  $query->result();
		$result_array = array();
		foreach($result as $row)
		{
			$result_array[$row->starting_date] = Array('total_sign_ups' =>$row->total_sign_ups);;
		}
		
		$sql = "
			SELECT 
				count(DISTINCT c.company_id) as completed_setup_wizard,
				DATE_FORMAT( capi.creation_date, '%Y-%m' ) as starting_date
			FROM
				company as c, 
				company_admin_panel_info as capi
			WHERE
				c.company_id = capi.company_id AND
				c.is_setup = '1'
			GROUP BY starting_date
			ORDER BY starting_date ASC
		";
		
		$query = $this->db->query($sql);	

		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
		
		$result =  $query->result();
		
		foreach($result as $row)
		{
			$result_array[$row->starting_date]['completed_setup_wizard'] =  $row->completed_setup_wizard;
		}
		
		// get total JUNK subscriptions
		$sql = "
			SELECT 
				count(DISTINCT u.id) as new_users,
				DATE_FORMAT( u.created, '%Y-%m' ) as starting_date
			FROM
				users as u
			GROUP BY starting_date
			ORDER BY starting_date ASC
		";
		
		$query = $this->db->query($sql);	
	
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
		
		$result =  $query->result();
		
		foreach($result as $row)
		{
			if (isset($result_array[$row->starting_date]))
			{
				$result_array[$row->starting_date] = $result_array[$row->starting_date] + Array('new_users' => $row->new_users);
			}
			else
			{
				$result_array[$row->starting_date] = Array('new_users' => $row->new_users);
			}
		}
		
		// get total JUNK subscriptions
		$sql = "
			SELECT 
				count(DISTINCT u.id) as completed_tutorial,
				DATE_FORMAT( u.created, '%Y-%m' ) as starting_date
			FROM
				users as u
			WHERE
				u.completed_tutorial = 1
			GROUP BY starting_date
			ORDER BY starting_date ASC
		";
		
		$query = $this->db->query($sql);	
	
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
		
		$result =  $query->result();
		
		foreach($result as $row)
		{
			if (isset($result_array[$row->starting_date]))
			{
				$result_array[$row->starting_date] = $result_array[$row->starting_date] + Array('completed_tutorial' => $row->completed_tutorial);
			}
			else
			{
				$result_array[$row->starting_date] = Array('completed_tutorial' => $row->completed_tutorial);
			}
		}
		
		// get total revenue by month
		$sql = "
			SELECT 
			count(cb.company_id) as created_booking, cb.starting_date
			FROM
			(				
				SELECT 
					DISTINCT c.company_id, DATE_FORMAT( capi.creation_date, '%Y-%m' ) as starting_date
				FROM
					company as c,
					company_admin_panel_info as capi,
					booking as b
				WHERE
					capi.company_id = c.company_id AND
					b.company_id = c.company_id
			) as cb
			GROUP BY starting_date
			ORDER BY starting_date ASC
		";
		
		$query = $this->db->query($sql);	

		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
		
		$result =  $query->result();
		
		foreach($result as $row)
		{
			$result_array[$row->starting_date]['created_booking'] =  $row->created_booking;
		}
		
		$sql = "
			SELECT 
				count(DISTINCT c.company_id) as ran_night_audit,
				DATE_FORMAT( capi.creation_date, '%Y-%m' ) as starting_date
			FROM
				company as c, 
				company_admin_panel_info as capi
			WHERE
				c.company_id = capi.company_id AND
				c.selling_date != capi.creation_date
			GROUP BY starting_date
			ORDER BY starting_date ASC
		";
		
		$query = $this->db->query($sql);	

		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
		
		$result =  $query->result();
		
		foreach($result as $row)
		{
			$result_array[$row->starting_date]['ran_night_audit'] =  $row->ran_night_audit;
		}
		
		return $result_array;
	}	
	
	/*
	COUNT(DISTINCT c.company_id && (c.number_of_rooms >= 10 AND c.number_of_rooms <= 19)) as C,
			COUNT(DISTINCT c.company_id && (c.number_of_rooms >= 20 AND c.number_of_rooms <= 29)) as D,
			COUNT(DISTINCT c.company_id && (c.number_of_rooms >= 30 AND c.number_of_rooms <= 39)) as E,
			COUNT(DISTINCT c.company_id && (c.number_of_rooms >= 40 AND c.number_of_rooms <= 49)) as F,
			COUNT(DISTINCT c.company_id && (c.number_of_rooms >= 50 AND c.number_of_rooms <= 59)) as G,
			COUNT(DISTINCT c.company_id && (c.number_of_rooms >= 60 AND c.number_of_rooms <= 69)) as H,
		*/
		
	//Get number of companies per room size
	function get_number_of_subscriptions_by_room_size_and_by_month()
	{
		
		$room_count_ranges = Array(
			Array('start'=>1, 'end'=>4),
			Array('start'=>5, 'end'=>9)
		);
		for($i = 10; $i < 160; $i = $i +10)
		{
			$room_count_ranges[] = Array('start' => $i, 'end' => $i + 9);
		}

		$subscription_count_array = Array();
		
		foreach ($room_count_ranges as $range)
		{
			$sql = "
				SELECT 
					DATE_FORMAT( capi.creation_date, '%Y-%m' ) as starting_date, COUNT(c.company_id) as subscription_count
				FROM
					company as c, 
					company_admin_panel_info as capi
				WHERE
					c.company_id = capi.company_id AND
					c.number_of_rooms >= ".$range['start']." AND 
					c.number_of_rooms <= ".$range['end']."
				GROUP BY starting_date
				ORDER BY starting_date ASC
			";
			
			$query = $this->db->query($sql);	
			if ($this->db->_error_message()) // error checking
				show_error($this->db->_error_message());
			
			if($query->num_rows() >= 1)
			{
				$result = $query->result();
				foreach ($result as $r)
				{
					$subscription_count_array[$r->starting_date][(string)($range['start']."-".$range['end'])] = $r->subscription_count;
				}
				
			}
		}
		return $subscription_count_array;
	}
		
	function get_charges_by_room_size_and_by_month()
	{
		$room_count_ranges = Array(
			Array('start'=>1, 'end'=>4),
			Array('start'=>5, 'end'=>9)
		);
		
		for($i = 10; $i < 160; $i = $i +10)
		{
			$room_count_ranges[] = Array('start' => $i, 'end' => $i + 9);
		}

		$charge_array = Array();
		
		foreach ($room_count_ranges as $range)
		{
			$sql = "
				SELECT 
					DATE_FORMAT( cc.date, '%Y-%m' ) as starting_date, sum(cc.amount) as charge
				FROM
					company as c, company_charge as cc
				WHERE
					c.company_id = cc.company_id AND
					c.number_of_rooms >= ".$range['start']." AND 
					c.number_of_rooms <= ".$range['end']." AND
					cc.is_deleted = '0'
				GROUP BY starting_date
				ORDER BY starting_date ASC
			";
			
			$query = $this->db->query($sql);	
			
			if ($this->db->_error_message()) // error checking
				show_error($this->db->_error_message());
			
			if($query->num_rows() >= 1)
			{
				$result = $query->result();
				foreach ($result as $r)
				{
					$charge_array[$r->starting_date][(string)($range['start']."-".$range['end'])] = $r->charge;
				}
				
			}
		}
		
		return $charge_array;
	}
	
	function search($query)
	{
		//Returns array of objects where each object represents a row from query
		$this->db->select('
			up.company_id,
			pf.first_name,
			pf.last_name,
			u.email,
			');
		$this->db->from('
			users as u,
			user_permissions as up,
			user_profiles as pf
			');
		$this->db->where("up.user_id = u.id");
		$this->db->where("pf.user_id = u.id");
		$this->db->where("(u.email like '%$query%' OR pf.first_name like '%$query%' OR pf.last_name like '%$query%')");
		$this->db->group_by("u.email");
		$query = $this->db->get();
				
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		if($query->num_rows() >= 1)
		{
			return $query->result();
		}
		
		return NULL;		
	}
	
	
	function create_company_log($data)
    {
	    $data = (object) $data;        
        $this->db->insert("company_log", $data);
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
    
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
		
		echo $this->db->last_query();
    }
	
	// what he said
	// return row_limit number of rows. if row_limit is 0, then there is no cap.
	function get_company_log($company_id)	
    {		
        $sql = "
			SELECT * 
			FROM company_log as cl
			WHERE 
				cl.company_id = '$company_id'
			ORDER BY date_time DESC			
		";
		$q = $this->db->query($sql);
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
		
		$result = $q->result_array();
		if ($this->db->_error_message()) // error checking
				show_error($this->db->_error_message());
		
		//echo $this->db->last_query();
		
		return $result;
    }

    function import_company($data)
    {
    	//blah blah
    }
    
    function update_trialing_status($date)
    {
        $this->db->where("capi.creation_date <=  '$date'");
        $this->db->where("cs.subscription_state =  'trialing'");
        $this->db->update('company_admin_panel_info AS capi join company_subscription AS cs ON capi.company_id = cs.company_id',array('subscription_state' => 'trial_ended'));
    }
    
    function get_hotels_using_old_booking_modal()
    {
        $q = "SELECT * FROM (`company` as c) LEFT JOIN `company_subscription` as cs ON `c`.`company_id` = `cs`.`company_id` WHERE `cs`.`subscription_state` = 'trialing' OR `cs`.`subscription_state` = 'unpaid' OR `cs`.`subscription_state` = 'active'";
        $query = $this->db->query($q);
        
        return $query->result_array();
    }
	
	function create_company_group($data)
	{
		$data = (object) $data;        
        $this->db->insert("company_groups", $data);
		
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
	
	function create_company_groups_x_company($data)
	{
		$data = (object) $data;        
        $this->db->insert("company_groups_x_company", $data);
		
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
	
	function get_company_groups($company_group_id = null)
	{
		$where = "";
		if($company_group_id)
		{
			$where = " AND cg.id = $company_group_id ";
		}
		$sql = "SELECT * FROM company_groups as cg
				WHERE is_deleted = 0 $where GROUP BY cg.id ORDER BY cg.id";
		
		$query = $this->db->query($sql);
		$company_groups = $query->result_array();
		
		$sql1 = "SELECT c.company_id, c.name, cgxc.* FROM company_groups_x_company as cgxc 
				LEFT JOIN company as c ON cgxc.company_id = c.company_id";
		
		$query1 = $this->db->query($sql1);
		$company_groups_x_company = $query1->result_array();
		
		foreach($company_groups as $key => $groups)
		{
			foreach($company_groups_x_company as $groups_x_company)
			{
				if($groups['id'] == $groups_x_company['company_group_id'])
				{
					$company_groups[$key]['companies'][] = $groups_x_company;
				}
			}
		}
		
		return count($company_groups) > 0 ? $company_groups : NULL;
	}
	
	function update_company_group($company_group_id, $data)
	{
		$data = (object) $data;
		
		$this->db->where('id', $company_group_id);
		$this->db->update('company_groups', $data);
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
		
		return TRUE;
	}
	
	function delete_company_groups_x_company($company_group_id)
    {
        $this->db->where('company_group_id', $company_group_id);
		$this->db->delete('company_groups_x_company');
    }

    function get_single_company_admin_panel_info($company_id)
    {
        $this->db->from('company_admin_panel_info');
        $this->db->where('company_id', $company_id);
        
        $query = $this->db->get();
        
        if($query->num_rows() >= 1)
        {
            return $query->row_array();
        }
        
        return NULL;
    }
}
	
?>
