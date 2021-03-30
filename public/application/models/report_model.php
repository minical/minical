<?php

class Report_model extends CI_Model {

	function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }	

	function get_geographical_data($company_id, $start_date, $end_date)
	{
		$sql = "
					SELECT
						x.country as label,
					    SUM(
					    	DATEDIFF(
					    		LEAST('$end_date', x.check_out_date), 
					    		GREATEST('$start_date', x.check_in_date)
					    	)
					    ) as value
					FROM
						(
							SELECT 
								CASE
								WHEN c.country IS NULL OR c.country = ''
								THEN 'Unknown'
								ELSE c.country
								END as country,
					            brh.check_in_date,
					            brh.check_out_date
							FROM 
								booking as b,
								booking_block as brh,
								customer as c
							WHERE 
								b.company_id = '$company_id' AND
								b.booking_customer_id = c.customer_id AND
								b.booking_id = brh.booking_id AND
								brh.check_in_date < '$end_date' AND
								brh.check_out_date > '$start_date' AND
								b.is_deleted != '1' AND
								c.is_deleted != '1'
							GROUP BY brh.booking_room_history_id
						) as x
					GROUP BY
						x.country
					ORDER BY value DESC

				";

		$q = $this->db->query($sql);
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
	
		//echo $this->db->last_query();		
		return $q->result_array();
	}

	function get_booking_source_data($company_id, $start_date, $end_date)
	{
		$sql = "
					SELECT
						x.source,
					    SUM(
					    	DATEDIFF(
					    		LEAST('$end_date', x.check_out_date), 
					    		GREATEST('$start_date', x.check_in_date)
					    	)
					    ) as booking_count
					FROM
						(
							SELECT 
								b.source,
								brh.check_in_date,
					            brh.check_out_date
							FROM 
								booking as b,
								booking_block as brh,
								customer as c
							WHERE 
								b.company_id = '$company_id' AND
								b.booking_customer_id = c.customer_id AND
								b.booking_id = brh.booking_id AND
								brh.check_in_date < '$end_date' AND
								brh.check_out_date > '$start_date' AND
								b.is_deleted != '1' AND
                                                                b.state != '4' AND
								c.is_deleted != '1'
							GROUP BY brh.booking_room_history_id
						) as x
					GROUP BY
						x.source
				";

		$q = $this->db->query($sql);
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
	
		//echo $this->db->last_query();		
		return $q->result_array();
	}

    function get_charges_by_source($start_date, $end_date, $current_selling_date = '2099-01-01',  $get_room_charges_only = false, $include_taxes = true, $is_tax_exempt = '', $include_cancelled_noshow_bookings = true)
    {
        $get_room_charges_only_sql = $join_customer_type = $where_statement = "";
        if ($get_room_charges_only)
        {
            $get_room_charges_only_sql = "AND is_room_charge_type = '1'";
        }
        $past_and_present_charge = "c.amount";
        if ($include_taxes)
        {
            $past_and_present_charge = "
			                            c.amount + 
										SUM(IF(
											tt.is_brackets_active = 1,
											IF(
												tt.is_tax_inclusive = 1,
												0,
												(c.amount * tpb.tax_rate * 0.01)
											),
											IF(
												tt.is_tax_inclusive = 1,
												0,(c.amount * tt.tax_rate * 0.01)
											)
										))";
        }
        $booking_state_where_condition = " cb.state <= '5'";
        if(!$include_cancelled_noshow_bookings)
        {
            $booking_state_where_condition = " cb.state < '3'";
        }

        $company_id = $this->session->userdata('current_company_id');
        $data = null;
        $sql = "SELECT x.source, ROUND(SUM(x.past_and_present_charge), 2) as total_charge
                        FROM (SELECT cb.source,
                            IFNULL((
                            SELECT 
                                $past_and_present_charge as past_and_present_charge
                            FROM 
                                charge_type_tax_list AS cttl
                            LEFT JOIN charge_type as ct3 ON ct3.id = cttl.charge_type_id
                            LEFT JOIN tax_type AS tt ON tt.tax_type_id = cttl.tax_type_id
                            LEFT JOIN tax_price_bracket as tpb 
                                ON tpb.tax_type_id = tt.tax_type_id AND tt.is_brackets_active = 1
                            WHERE 
                                tt.is_deleted = '0' AND
                                ct3.is_deleted = '0' AND
                                cttl.charge_type_id = ct2.id AND 
                                ((tt.is_brackets_active = 1 AND c.amount BETWEEN tpb.start_range AND tpb.end_range) OR (tt.is_brackets_active != 1))
                            GROUP BY cttl.charge_type_id
                        ), c.amount) as past_and_present_charge
                    FROM 
                        charge_type as ct2, 
                        charge as c					
                    LEFT JOIN booking as cb ON cb.booking_id = c.booking_id
                    WHERE 
                            c.is_deleted = '0' AND
                            c.selling_date >= '$start_date' AND
                            c.selling_date <= '$end_date' AND
                            $booking_state_where_condition AND
                            cb.is_deleted != '1' AND
                            ct2.company_id = '$company_id' AND
                            cb.company_id = '$company_id' AND
                            ct2.id = c.charge_type_id
                            $get_room_charges_only_sql
                            $where_statement
                ) as x
                GROUP BY x.source";

        $query = $this->db->query($sql);

        $future_charges = $this->forecast_charges->_get_source_wise_forecast_charges($start_date, date("Y-m-d", strtotime($end_date." +1day")), $current_selling_date, $get_room_charges_only, $include_taxes, null, $is_tax_exempt);
        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());
        $result = array();
        if ($query->num_rows >= 1)
        {
            foreach ($query->result_array() as $row)
            {
                if($future_charges && isset($future_charges[$row['source']])){
                    $result[$row['source']] = $row['total_charge'] + $future_charges[$row['source']];
                    unset($future_charges[$row['source']]);
                }else{
                    $result[$row['source']] = $row['total_charge'];
                }
            }
        }
        if($future_charges && count($future_charges) > 0){
            foreach($future_charges as $source => $amount){
                $result[$source] = $amount;
            }
        }

        return $result;
    }

    function get_customer_type_data($company_id, $start_date, $end_date, $order_type = null)
    {
        $select = 'x.customer_type as label';
        $order = 'value DESC';
        if ($order_type == 'customer_type') {
            $select = 'x.customer_type, type_id';
            $order = 'x.customer_type';
        }
        $sql = "
					SELECT
						$select,
					    SUM(
					    	DATEDIFF(
					    		LEAST('$end_date', x.check_out_date), 
					    		GREATEST('$start_date', x.check_in_date)
					    	)
					    ) as value
					FROM
						(
							SELECT 
								CASE
								WHEN ct.name IS NULL OR ct.name = ''
								THEN 'Unknown'
								ELSE ct.name
								END as customer_type,
								brh.check_in_date,
					            brh.check_out_date,
					            ct.id as type_id
							FROM 
								booking as b,
								booking_block as brh,
								customer as c,
								customer_type as ct
							WHERE 
								b.company_id = '$company_id' AND
								b.booking_customer_id = c.customer_id AND
								b.booking_id = brh.booking_id AND
								brh.check_in_date < '$end_date' AND
								brh.check_out_date > '$start_date' AND
								b.is_deleted != '1' AND
								c.is_deleted != '1' AND
								c.customer_type_id = ct.id
							GROUP BY brh.booking_room_history_id
						) as x
					GROUP BY
						x.customer_type
					ORDER BY $order
				";

        $q = $this->db->query($sql);

        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());

        //echo $this->db->last_query();
        return $q->result_array();
    }
}