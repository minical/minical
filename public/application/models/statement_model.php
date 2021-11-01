<?php

class Statement_model extends CI_Model {

	function __construct()
    {        
        parent::__construct(); // Call the Model constructor
    }
	
    function get_statements($company_id = null, $customer_id = null, $filters = array())
    {
        if ($company_id)
		{
			$company_id = $this->session->userdata('current_company_id');
		}
		
		$sql = $this->_build_get_statements_sql_query_based_on_filters($company_id, $customer_id, $filters);

        $q = $this->db->query($sql);	

       	//echo $this->db->last_query();
		
		if ($this->db->_error_message()) 
		{
			show_error($this->db->_error_message());
		}
		

		$result = $q->result_array();		
		
		return $result;
    }
    
    function _build_get_statements_sql_query_based_on_filters($company_id, $customer_id, $filters) 
	{
        
        $order_by = "ORDER BY statement_number";
		
		//if both $start and $end are set, then return occupancies within range
		if (isset($filters['order_by'])) 
		{
			if ($filters['order_by'] != "") 
			{
				$order_by = "ORDER BY ".$filters['order_by'];
			}
		}
                
		// set order
		$order = "ASC";
		if (isset($filters['order'])) 
		{
			if ($filters['order'] == 'DESC')
			{
				$order = "DESC";
			}
		}
		
		// set limit
		$limit = "";
		if (isset($filters['offset'])) 
		{
			$limit = "LIMIT ".intval($filters['offset']);
			if (isset($filters['per_page'])) 
			{
				$limit = $limit.", ".$filters['per_page'];
			}
		}
        
        
		// SQL_CALC_FOUND_ROWS is used to calculate # of rows ignoring LIMIT. SELECT FOUND_ROWS() Must be called right after this query.
		// Which is called from get_found_rows() function.
        $sql = "
			SELECT SQL_CALC_FOUND_ROWS 
				*
			FROM
				(SELECT
					x.statement_id,
                    x.statement_number,
                    x.statement_name,
                    x.creation_date,
                    x.booking_ids,
                    x.balance,
                    x.balance_without_forecast,
                    x.charge_total,
                    x.payment_total
				FROM
				(
					SELECT 
                        s.statement_id,
                        s.statement_number,
                        s.statement_name,
                        s.creation_date,
						GROUP_CONCAT(b.booking_id) as booking_ids,
                        SUM(b.balance) as balance,
                        SUM(b.balance_without_forecast) as balance_without_forecast,
                        SUM(IFNULL(
                            (
                                IFNULL(
									(
										SELECT SUM(p.amount) as payment_total
										FROM payment as p, payment_type as pt
										WHERE
											p.is_deleted = '0' AND
											pt.is_deleted = '0' AND
											p.payment_type_id = pt.payment_type_id AND
											p.booking_id = b.booking_id
										GROUP BY p.booking_id
									), 0
								) + b.balance_without_forecast
                            ), 0	
                        )) as charge_total,
                        SUM(IFNULL(
                            (
                                SELECT SUM(p.amount) as payment_total
                                FROM payment as p, payment_type as pt
                                WHERE
                                    p.is_deleted = '0' AND
                                    pt.is_deleted = '0' AND
                                    p.payment_type_id = pt.payment_type_id AND
                                    p.booking_id = b.booking_id
                                GROUP BY p.booking_id
                            ), 0
                        )) as payment_total
                    FROM statement AS s 
					LEFT JOIN booking_x_statement AS bs ON bs.statement_id = s.statement_id AND s.is_deleted = 0
                    LEFT JOIN booking as b ON bs.booking_id = b.booking_id  
                    WHERE
                        b.company_id = '{$company_id}' 
                        AND b.is_deleted != '1' 
                        AND s.is_deleted = 0
                        AND b.booking_customer_id = '{$customer_id}'
                    GROUP BY s.statement_id
				)x
			)y
			$order_by $order 
			$limit
		";
		
		return $sql;
	}
	
    function get_last_statement($company_id){
        $this->db->select('s.statement_number');
        $this->db->where('b.company_id', $company_id);
        $this->db->where('s.is_deleted', 0);
        $this->db->from('statement as s');
        $this->db->join('booking_x_statement as bs', "bs.statement_id = s.statement_id", "left");
        $this->db->join('booking as b', "b.booking_id = bs.booking_id", "left");
        $this->db->order_by("s.statement_number DESC");
        $this->db->limit(1);
        
        $query = $this->db->get();
        if ($query->num_rows >= 1) {
            $result = $query->result_array();
            return $result[0];
        }
        return null;
    }
    
	function create_booking_statement($booking_ids, $statement_number)
	{		
        if($booking_ids && count($booking_ids) > 0){
            
            $current_date = date('Y-m-d H:i:s');
            $statement_date = date('Y-m-d', strtotime($current_date));
            $statement_name = "Statement of ".date('M Y', strtotime($statement_date) );
            
            $this->db->insert('statement', array("statement_number" => $statement_number, "creation_date" => $current_date, "statement_name" => $statement_name));		
            $statement_id = $this->db->insert_id();
            foreach($booking_ids as $booking_id){
                $this->db->insert('booking_x_statement', array("statement_id" => $statement_id, "booking_id" => $booking_id));	
            }
            return $statement_id;
        }
        return null;
	}
	
    function delete_booking_statements($statement_id)
    {
        $this->db->where("statement_id", $statement_id);
        $this->db->update('statement', array('is_deleted' => 1));
        
        if($this->db->affected_rows() > 0)
            return TRUE;
        else
            return FALSE;
    }
    
    function get_bookings_by_statement_id($statement_id)
    {
        $this->db->select('s.*, bs.booking_id');
        $this->db->where('s.statement_id', $statement_id);
        $this->db->from('booking_x_statement as bs');
        $this->db->join('statement as s', "bs.statement_id = s.statement_id", "left");
        $query = $this->db->get();
        
        if ($query->num_rows >= 1) {
            $result = $query->result_array();
            return $result;
        }
        return null;
    }
    
    function update_statement($statement_data, $statement_id)
    {
        $this->db->where("statement_id", $statement_id);
        $this->db->update('statement', $statement_data);  
        
        if($this->db->affected_rows() > 0)
            return true;
        else
            return false;
    }

    function create_statement($data){

        $this->db->insert("statement", $data);

        $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
        $result = $query->result_array();
        if(isset($result[0]))
        {
            $statement_id = $result[0]['last_id'];
        }
        else
        {
            $statement_id = null;
        }

        // if there's an error in query, show error message
        if ($this->db->_error_message())
            show_error($this->db->_error_message());
        else // otherwise, return insert_id;
            return $statement_id;

    }

     function create_statement_booking($data){
        
        $this->db->insert("booking_x_statement", $data);
     }
    
}

/* End of file customer_model.php */