<?php

class Customer_model extends CI_Model {

	function __construct()
    {
        parent::__construct();
    }

	// check if the customer belongs to the company
	function customer_belongs_to_company($customer_id, $company_id) 
	{
		$this->db->where('company_id', $company_id);
        $this->db->where('customer_id', $customer_id);
        $query = $this->db->get('customer');
        
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
		$q = sizeof($query->result());
		return $q != 0;
	}

	function get_customer($customer_id)
	{
		$this->db->where('customer_id', $customer_id);
    	$this->db->from('customer');
        $query = $this->db->get();
        $result = $query->result_array();
		
		$customer = null;
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
		if ($query->num_rows >= 1)
		{
			$customer = $result[0];

			$customer['customer_fields'] = $this->get_customer_fields($customer_id);
		}

        return $customer;
	}
    function get_bookedby_customer($customer_id)
	{
            $this->db->where('customer_id', $customer_id);
            $this->db->from('customer');
            $query = $this->db->get();
            $result = $query->result_array();
            return $result;
	}

	function get_customer_by_name($name, $company_id)
	{
		$this->db->where('customer_name', $name);
		$this->db->where('company_id', $company_id);
        $this->db->where('is_deleted', 0);
    	$this->db->from('customer');
        $query = $this->db->get();
        $result = $query->result_array();
		
		$customer = null;
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
		if ($query->num_rows >= 1)
		{
			$customer = $result[0];
		}

        return $customer;
	}

	
	
	/* 	Search customer using MYSQL
		parameters:
	  	"string to search"
	  	"number of items per page"
	  	"offset"
	  	"order by"
	  	"order (ASC or DESC)"
	  	"customer_id to omit"
	  */
	function get_customers($filters, $is_csv = false)
    {
		
		if($filters && isset($filters['only_static_customer_info']) && $filters['only_static_customer_info']) {
			
			$where_conditions = $this->_get_where_conditions($filters);
			
			// set order by
			// set order
			$order = "";
			$order_by = "ORDER BY ";
			if (isset($filters['order_by'])) {
				// if both $star and $end are set, then return occupancies within range
				if ($filters['order_by'] != "") {
					$order_by = "ORDER BY ".$filters['order_by'];
					$order = "ASC,";
					if (isset($filters['order'])) {
						if ($filters['order'] != "") {
							if ($filters['order'] == 'DESC') {
								$order = "DESC,";
							}
						}
					}
				}
			}

			
			//set limit
			$limit = "";
			if (isset($filters['offset'])) {
				$limit = "LIMIT ".intval($filters['offset']);
				if (isset($filters['per_page'])) {
					$limit = $limit.", ".$filters['per_page'];
				}
			}
			$sql = "SELECT * FROM  customer as c 
					WHERE $where_conditions
					GROUP BY c.customer_id
					$order_by $order c.customer_name
					$limit";
		} else {
			$sql = $this->_get_filtered_query($filters);
		}
		
        $q = $this->db->query($sql);		
		
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
		$result = $is_csv ? $q : $q->result();
		//echo $this->db->last_query();
		return $result;
    }

    function search_customers($filters, $is_csv = false)
    {
		
		if($filters && isset($filters['only_static_customer_info']) && $filters['only_static_customer_info']) {
			
			// $where_conditions = $this->_customer_search_where_conditions($filters);
			
			// set order by
			// set order
			$order = "";
			$order_by = "ORDER BY ";
			if (isset($filters['order_by'])) {
				// if both $star and $end are set, then return occupancies within range
				if ($filters['order_by'] != "") {
					$order_by = "ORDER BY ".$filters['order_by'];
					$order = "ASC,";
					if (isset($filters['order'])) {
						if ($filters['order'] != "") {
							if ($filters['order'] == 'DESC') {
								$order = "DESC,";
							}
						}
					}
				}
			}

			
			//set limit
			$limit = "";
			if (isset($filters['offset'])) {
				$limit = "LIMIT ".intval($filters['offset']);
				if (isset($filters['per_page'])) {
					$limit = $limit.", ".$filters['per_page'];
				}
			}

			$search_query = $filters['search_query'];

			$company_id = $filters['company_id'];

			$sql = "SELECT * FROM customer as c 
					WHERE c.company_id = '$company_id'
					AND c.is_deleted != '1'
					AND customer_name like '%$search_query%'
					GROUP BY c.customer_id

					UNION

					SELECT * FROM customer as c 
					WHERE c.company_id = '$company_id'
					AND c.is_deleted != '1'
					AND customer_id = '$search_query'
					GROUP BY c.customer_id

					UNION

					SELECT * FROM customer as c 
					WHERE c.company_id = '$company_id'
					AND c.is_deleted != '1'
					AND phone like '%$search_query%'
					GROUP BY c.customer_id

					UNION

					SELECT * FROM customer as c 
					WHERE c.company_id = '$company_id'
					AND c.is_deleted != '1'
					AND email like '%$search_query%'
					GROUP BY c.customer_id
					
					$order_by $order customer_name
					$limit";
		} else {
			$sql = $this->_get_filtered_query($filters);
		}
		
        $q = $this->db->query($sql);
		
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
		$result = $is_csv ? $q : $q->result();
		//echo $this->db->last_query();
		return $result;
    }
	
	// return row count for pagination

    function _get_filtered_query($filters)
    {
        $where_conditions = $this->_get_where_conditions($filters);

        $company_id = $filters['company_id'];

        // set order by
		// set order
        $order = "";
        $order_by = "ORDER BY ";
        if (isset($filters['order_by'])) {
            // if both $star and $end are set, then return occupancies within range
            if ($filters['order_by'] != "") {
                $order_by = "ORDER BY ".$filters['order_by'];
				$order = "ASC,";
				if (isset($filters['order'])) {
					if ($filters['order'] != "") {
						if ($filters['order'] == 'DESC') {
							$order = "DESC,";
						}
					}
				}
            }
        }

        //set limit
        $limit = "";
        if (isset($filters['offset'])) {
            $limit = "LIMIT ".intval($filters['offset']);
            if (isset($filters['per_page'])) {
                $limit = $limit.", ".$filters['per_page'];
            }
        }
        
		$charge_total_calculation_sql = "(IFNULL(balance,0) + IFNULL(SUM(payment_total), 0)) as charge_total";

        $balance_key = ($this->is_total_balance_include_forecast == 1) ? "balance" : "balance_without_forecast";

		$charge_total_calculation_join = "#Get balance
				(
					SELECT
						SUM(b1.{$balance_key}) as booking_balance_total
					FROM
						booking as b1
					LEFT JOIN booking_block AS brh ON brh.booking_room_history_id = (
						SELECT b2.booking_room_history_id 
                        FROM booking_block as b2 
                        WHERE b2.booking_id = b1.booking_id
                        LIMIT 1
                    ) 
					LEFT JOIN room as r ON brh.room_id = r.room_id 
					WHERE
						b1.is_deleted = '0' AND
						IF(brh.room_id, r.is_deleted, '0') != '1' AND
						b1.booking_customer_id = c.customer_id AND
						c.company_id = b1.company_id AND
						c.company_id = '$company_id'
				) as balance,
				";
		
        // set selling date
        $charge_selling_date_sql = $payment_selling_date_sql = "";
		if (isset($filters['selling_date']) && $filters['selling_date'])	
			{
		
			$charge_selling_date_sql = "ch.selling_date <= '{$filters['selling_date']}' AND";
			$payment_selling_date_sql = "p.selling_date <= '{$filters['selling_date']}' AND";

			$charge_total_calculation_sql = "(IFNULL(charge_total,0) - IFNULL(SUM(payment_total), 0)) as balance";
			$charge_total_calculation_join = "#Get charge total
				(
					SELECT
						(SUM((ch.amount * (1+IFNULL(percentage_total * 0.01, 0))) + IFNULL(flat_tax_total, 0))) as booking_charge_total
					FROM
						booking as b1,
						charge as ch
					LEFT JOIN (
						SELECT
							ch.charge_id,
							ct.name,
							ct.id,
							sum(IF(tt.is_percentage = 1, IF(tt.is_brackets_active, tpb.tax_rate, tt.tax_rate), 0)) as percentage_total,
							sum(IF(tt.is_percentage = 0, IF(tt.is_brackets_active, tpb.tax_rate, tt.tax_rate), 0)) as flat_tax_total
						FROM
							charge as ch
						LEFT JOIN booking as b ON b.booking_id = ch.booking_id
						LEFT JOIN charge_type as ct ON ch.charge_type_id = ct.id
						LEFT JOIN charge_type_tax_list AS cttl ON ct.id = cttl.charge_type_id 
						LEFT JOIN tax_type AS tt ON tt.tax_type_id = cttl.tax_type_id
						LEFT JOIN tax_price_bracket as tpb 
							ON tpb.tax_type_id = tt.tax_type_id AND ch.amount BETWEEN tpb.start_range AND tpb.end_range
						WHERE
							ch.charge_type_id = cttl.charge_type_id AND
							ct.id = cttl.charge_type_id AND
							tt.tax_type_id = cttl.tax_type_id AND
							tt.is_deleted = '0' AND
							ct.is_deleted = '0' AND
							b.company_id = '$company_id'
						GROUP BY ch.charge_id
					)t ON ch.charge_id = t.charge_id
					WHERE
						ch.is_deleted = '0' AND
                        $charge_selling_date_sql
						b1.is_deleted = '0' AND
						ch.booking_id = b1.booking_id AND
						b1.booking_customer_id = c.customer_id AND
						c.company_id = b1.company_id AND
						c.company_id = '$company_id'
				) as charge_total,
				";
		}
		
		

        $sql = "
		SELECT SQL_CALC_FOUND_ROWS
			*,
			$charge_total_calculation_sql
		FROM
		(
			SELECT
				*,
				$charge_total_calculation_join
				
				#Get payment total

                (
					SELECT
						SUM(p.amount)
					FROM
						booking as b2,
						payment as p
					WHERE
						p.is_deleted = '0' AND
                        $payment_selling_date_sql
						b2.is_deleted = '0' AND
						p.booking_id = b2.booking_id AND
						b2.booking_customer_id = c.customer_id AND
						c.company_id = b2.company_id AND
						c.company_id = '$company_id'

				) as payment_total,

				#Get last check out date
				
				(
					SELECT
						MAX(brh.check_out_date) as last_check_out_date
					FROM
						booking_block as brh,
						booking as b3
					WHERE
						b3.booking_id = brh.booking_id AND
						b3.is_deleted = '0' AND
						b3.booking_customer_id = c.customer_id AND
						c.company_id = b3.company_id AND
						c.company_id = '$company_id'
				)as last_check_out_date
                

			FROM
				customer as c
			WHERE
				$where_conditions


		) as c2

		GROUP BY customer_id

		$order_by $order c2.customer_name
		$limit
		";

        return $sql;
    }

    function _get_where_conditions($filters)
    {
        // Handling customer type filter
        $query_customer_type = '';
        if (isset($filters['customer_type_id']))
		{
			if (!empty($filters['customer_type_id']))
			{
				$query_customer_type = "AND c.customer_type_id = '".$filters['customer_type_id']."'";
			}
				
		}

        // Handling "show deleted" filter
        $show_deleted = "AND c.is_deleted != '1'";
        if (isset($filters['show_deleted']))
		{
            if ($filters['show_deleted'] == 'checked') {
                $show_deleted = "AND c.is_deleted = '1'";
            }
		}

        // set search query
        $search_sql = "";
        if (isset($filters['search_query']))
		{
            $search_query = $filters['search_query'];
            
            if (!empty($search_query))
            {
            	$search_sql   = " AND (customer_name like '%".$search_query."%' OR customer_id = '".$search_query."' OR phone like '%".$search_query."%' OR email like '%".$search_query."%' )";
            }
            	
		}

        $company_id = $filters['company_id'];

        $where_conditions = "
				c.company_id = '$company_id'
				$show_deleted
				$search_sql
				$query_customer_type
		";

        return $where_conditions;
	}
	  
	// get customer id based on customer name.

    function get_found_rows()
    {
        $sql = "SELECT FOUND_ROWS() as count;";

        $q = $this->db->query($sql);

		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}

        if ($q)
		{
            $result = $q->row_array(0);

            return $result['count'];
		}

        return 0;
    }
	
	// get customer id based on customer name and phone

    function get_customer_info($customer_id)
	{
		if(is_array($customer_id))
    	{
    		$customer_ids_str = implode(",", $customer_id);
    		$where = " customer_id IN ($customer_ids_str) ";
    	}
    	else
    	{
    		$where = " customer_id = '$customer_id' ";
    	}
        $sql = "
			SELECT * FROM customer
			WHERE $where ;
		";
       
        $q = $this->db->query($sql);

		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}

        //return result set as an associative array
        if ($q)
		{
			if(is_array($customer_id))
            	$result = $q->result_array();
            else
            	$result = $q->row_array(0);

            return $result;
		}

        return 0;
	}
	
	// get customer id based on customer email

    function get_customer_id($customer_name, $company_id = 0)
    {
        if ($company_id == 0) {
            $company_id = $this->session->userdata('current_company_id');
        }

        $this->db->from("customer");
        $this->db->where('customer_name', $customer_name);
        $this->db->where('company_id', $company_id);
        $query = $this->db->get();

        if ($this->db->_error_message()) {
            show_error($this->db->_error_message());
        }

        $result = $query->row_array(0);
        if (isset($result['customer_id'])) {
            return $result['customer_id'];
        } else {
            return 0;
        }
    }

    function get_customer_id_by_name_and_phone($customer_name, $customer_phone, $company_id)
    {
        $this->db->from("customer");
        $this->db->where('customer_name', $customer_name);
        $this->db->where('phone', $customer_phone);
        $this->db->where('company_id', $company_id);
        $query = $this->db->get();

        //echo $this->db->last_query();
        if ($this->db->_error_message()) {
            show_error($this->db->_error_message());
        }

        $result = $query->row_array(0);
        if (isset($result['customer_id'])) {
            return $result['customer_id'];
        } else {
            return 0;
        }
    }

    function get_customer_id_by_email($email, $company_id)
    {
		$this->db->from("customer");
		$this->db->where('email', $email);
        $this->db->where('company_id', $company_id);
        $query = $this->db->get();

        //echo $this->db->last_query();
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}

		$result = $query->row_array(0);
		if (isset($result['customer_id']))
		{
			return $result['customer_id'];
		}
		else
		{
			return 0;
		}
	}

    function update_customer($customer_id, $data)
    {
		$data = (object) $data;
		$this->db->where('customer_id', $customer_id);
        $this->db->update("customer", $data);
    }
 
    function update_customer_type($id, $new_customer_type__id)
    {
        $sql = "UPDATE `customer` SET `customer_type_id` = '$new_customer_type__id' WHERE `customer`.`customer_type_id` = $id";
        $this->db->query($sql);
    }   

    function get_customer_fields($customer_id, $is_array = false)
    {
		$this->db->where('company_id', $this->company_id);
		$this->db->where('show_on_customer_form', 1);
		$this->db->where('is_deleted', 0);
    	$this->db->from('customer_field as cf');
    	if($is_array) {
    		$this->db->join('customer_x_customer_field as cxcf', "cxcf.customer_field_id = cf.id", 'left');
    		$this->db->where_in('cxcf.customer_id', $customer_id);
    	} else {
    	$this->db->join('customer_x_customer_field as cxcf', "cxcf.customer_field_id = cf.id and cxcf.customer_id = '$customer_id'", 'left');
    	}
        $query = $this->db->get();
        $customer_fields_result = $query->result_array();

        if($is_array) {
        	return $customer_fields_result;
	        }
        
        $customer_fields = array();
        foreach($customer_fields_result as $field)
        {
        	$customer_fields[$field['id']] = $field['value'];
        }

        return $customer_fields;
	}
    
    function update_customer_fields($customer_id, $customer_fields)
    {
    	$data = Array();
    	foreach ($customer_fields as $customer_field_id => $value)
    	{
    		$this->db->where('customer_id',$customer_id); 
    		$this->db->where('customer_field_id',$customer_field_id); 
    		$q = $this->db->get('customer_x_customer_field');  

    		
    		if ( $q->num_rows() > 0 )  
    		{
    			$this->db->where('customer_id', $customer_id); 
    			$this->db->where('customer_field_id', $customer_field_id); 
    			$this->db->update('customer_x_customer_field', array("value" => $value));
    		} 
    		else 
    		{ 
    			$data = Array(
    				"customer_id" => $customer_id,
    				"customer_field_id" => $customer_field_id,
    				"value" => $value
    			);

    			$this->db->insert('customer_x_customer_field', $data);
    		} 
    	}
    }

	function create_customer($data)
    {
        $data = (object)$data;
        $this->db->insert("customer", $data);

        if ($this->db->_error_message())
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
      }
      // insert_id function won't work as it converts id(bigint) to int, results in incorrect value
//        return $this->db->insert_id();
    }
	
	// returns an array of staying customers's names. eg. [customer1, customer2, customer3]

    function delete_customer($customer_id, $company_id = null)
    {
        $data['is_deleted'] = '1';

        if ($company_id != null) {
            $this->db->where('company_id', $company_id);
        }

        $this->db->where('customer_id', $customer_id);
        $this->db->update("customer", $data);
    }

    function get_customer_detail($customer_id)
    {
        $sql = "SELECT *
				FROM customer as c
				WHERE c.customer_id = '$customer_id'
			";

        $q = $this->db->query($sql);

        // return result set as an associative array
        $result = $q->row_array(0);

        return $result;
    }

	function get_staying_customers($booking_id)
    {
    	if(is_array($booking_id))
    	{
    		$select = " DISTINCT c.customer_id, ";
    		$booking_ids_str = implode(",", $booking_id);
    		$where = " b.booking_id IN ($booking_ids_str) AND ";
    	}
    	else
    	{
    		$select = "";
    		$where = " b.booking_id = '$booking_id' AND ";
    	}
        $sql = "SELECT $select c.*
				FROM customer as c, booking_staying_customer_list as bscl, booking as b
				WHERE
					$where
					b.booking_id = bscl.booking_id AND
					bscl.customer_id = c.customer_id
			";

        $q = $this->db->query($sql);

        // return result set as an associative array
        return $q->result_array();
    }

    function delete_all_staying_customers($booking_id)
    {
        $this->db->where('booking_id', $booking_id);
        $this->db->delete('booking_staying_customer_list');
        //echo $this->db->last_query();
        if ($this->db->_error_message())
		{
            show_error($this->db->_error_message());
		}

    }

    function create_staying_customers($booking_id, $staying_customer_ids)
    {
        if (isset($staying_customer_ids))
		{
            if ($staying_customer_ids != '')
			{
                foreach ($staying_customer_ids as $staying_customer_id) {
                    $link_data = array(
                        'booking_id'  => $booking_id,
                        'customer_id' => $staying_customer_id,
                        'company_id' => $this->company_id
                    );
                    
                    $this->db->insert('booking_staying_customer_list', $link_data);
                    //echo $this->db->last_query();
                    if ($this->db->_error_message()) {
                        show_error($this->db->_error_message());
                    }

                }
			}
		}
    }

    function create_staying_customers_batch ($booking_ids, $staying_customer_ids) {

	    $batch = array();

        if (isset($staying_customer_ids))
        {
            if ($staying_customer_ids != '')
            {
                foreach ($booking_ids as $booking_id) {
                    foreach ($staying_customer_ids as $staying_customer_id) {
                        $batch[] = array(
                            'booking_id'  => $booking_id,
                            'customer_id' => $staying_customer_id,
                            'company_id' => $this->company_id
                        );
                    }
                }

                $this->db->insert_batch('booking_staying_customer_list', $batch);
                //echo $this->db->last_query();
                if ($this->db->_error_message()) {
                    show_error($this->db->_error_message());
                }
            }
        }

    }

    function get_csv($company_id)
	{
		$this->db->select('customer_id,
		    customer_name,
		    address,
		    city,
		    region,
		    country,
		    postal_code,
		    phone,
		    fax,
		    email,
		    customer_notes,
		    is_deleted,
		    customer_type');
		$this->db->from('customer');
		$this->db->where("company_id", $company_id);
        $this->db->where("is_deleted", 0);
		
		$query = $this->db->get();
		return $query;
		
	}

    function delete_customers($company_id)
    {
        $data = Array('is_deleted' => 1);

        $this->db->where('company_id', $company_id);
        $this->db->update("customer", $data);

        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }

    }

        function delete_customer_permanently($customer_id){
            $this->db->where('customer_id',$customer_id);
            $this->db->delete('customer');
        }
}
