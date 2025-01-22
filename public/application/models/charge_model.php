<?php

class Charge_model extends CI_Model {

	function __construct()
    {
        // Call the Model constructor
        parent::__construct();
		$this->load->model('Company_model');		
		$this->load->model('Charge_type_model');	
        
        $this->load->library('Forecast_charges');
    }	

	//to change so you pass in company id
	function get_charge_total_by_booking_id($booking_id) {
		
		// get sales total
		// *IFNULL is used in case the charge type has no tax associated to it
		$sql = "
				SELECT 
					ROUND(SUM(c.amount * (1+IFNULL(percentage_total,0))), 2) as total
				FROM charge as c
				LEFT JOIN ( 
			                SELECT ct.name, ct.id, sum(percentage) as percentage_total 
			                FROM charge_type_tax_list AS cttl, charge_type as ct, tax_type AS tt, booking as b2
			                WHERE 
								ct.id = cttl.charge_type_id AND
								b2.booking_id = '$booking_id' AND
								ct.company_id = b2.company_id AND
								tt.tax_type_id = cttl.tax_type_id AND 
								tt.is_deleted = '0'
				
				            GROUP BY charge_type_id
		                )t 
		        ON c.charge_type_id = t.id		
		        WHERE 
			        c.is_deleted = '0' AND
					c.booking_id = '$booking_id';
			
		";		
		
		$query = $this->db->query($sql);
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
		
		if ($query) {
			$result = $query->row_array(0);		
			if ($result['total'])
				return $result['total'];
			else
				return 0;
		}
		return 0;	
	}
	
	//to change so you pass in company id
	function get_charge_total_by_date_range($date_start, $date_end, $employee_id='', $customer_type_id = '', $start_time = '', $end_time = '')
    {
		
		$company_id = $this->session->userdata('current_company_id');
		
		$employee_sql = "";
        if ($employee_id != '')
        {
			$employee_sql = "AND c.user_id = '$employee_id'";
		}
        $start_time_sql = "";
        if($start_time)
        {
            $start_time_sql = "AND DATE_FORMAT(c.date_time,'%H:%i') >= '$start_time'";
        }
        $end_time_sql = "";
        if($end_time)
        {
            $end_time_sql = "AND DATE_FORMAT(c.date_time,'%H:%i') <= '$end_time'";
        }
		$group_by = "GROUP BY x.customer_id";
		$customer_type_sql = "AND cu.customer_type_id = '$customer_type_id'";
		if ($customer_type_id == -1) 
        {
			$customer_type_sql = "AND cut.id IS NULL";
		} elseif ($customer_type_id == "") 
            {
                $customer_type_sql = "";
                $group_by = "GROUP BY x.id";
            }
		
		// get sales total
	    $sql = "
		SELECT 
			SUM(x.amount) as subtotal,
			count(x.charge_id) as charge_count,
			SUM(x.sub_total) as total,
			x.*
		FROM (
			SELECT
				ct.name as charge_type,
				ct.id,
				cut.id as customer_id,
				ct.is_room_charge_type,
				c.amount,
				ct.id as charge_type_id,
				c.charge_id,
				IFNULL((
					SELECT 
						c.amount + SUM(
							IF(tt.is_brackets_active = 1, IF(tpb.is_percentage, (c.amount * tpb.tax_rate * 0.01), tpb.tax_rate), IF(tt.is_percentage, (c.amount * tt.tax_rate * 0.01), tt.tax_rate)) 
						)
					FROM 
						charge_type_tax_list AS cttl
					LEFT JOIN charge_type as ct2 ON ct2.id = cttl.charge_type_id
					LEFT JOIN tax_type AS tt ON tt.tax_type_id = cttl.tax_type_id
					LEFT JOIN tax_price_bracket as tpb 
						ON tpb.tax_type_id = tt.tax_type_id AND tt.is_brackets_active = 1
					WHERE 
						tt.is_deleted = '0' AND
						ct2.is_deleted = '0' AND
						cttl.charge_type_id = ct.id AND 
						((tt.is_brackets_active = 1 AND c.amount BETWEEN tpb.start_range AND tpb.end_range) OR (tt.is_brackets_active != 1))
					#GROUP BY cttl.charge_type_id
				), c.amount) as sub_total
			FROM 
				(
					charge_type as ct, 
					charge as c,
					booking as b
				)

			LEFT JOIN customer as cu on b.booking_customer_id = cu.customer_id AND cu.is_deleted = '0'
			LEFT JOIN customer_type as cut on cu.customer_type_id = cut.id AND cut.is_deleted = '0'
			WHERE 
				c.charge_type_id = ct.id AND 
				c.selling_date >= '$date_start' AND 
				c.selling_date <= '$date_end' AND 
				c.is_deleted = '0' AND
				#ct.is_deleted = '0' AND #The reason ct.is_deleted is commented out is to prevent modifying settings affecting old transactions
				ct.company_id = '$company_id' AND
				b.company_id = '$company_id' AND
				b.booking_id = c.booking_id AND 
				b.state < '3' AND
				b.is_deleted = '0'

				$employee_sql $customer_type_sql $start_time_sql $end_time_sql
				GROUP BY c.charge_id
		) as x
		$group_by
		;";
		
		$data = array();
		
		
		$q = $this->db->query($sql);
        
		//echo $this->db->last_query();
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
			
		
        //$q = $this->db->query($sql);
        if ($q->num_rows() > 0)
        {
        	$result_array = $q->result_array();
            foreach($result_array as $row)
            {
                $data[] = array('charge' => $row);
            }
           
        }
		return $data;
	}

	// get sales made by this employee in detail
	// called from employee report
	function get_charges_detail($date, $date_end, $employee_id='', $start_time = '', $end_time = '', $cancelled_bookings = false)
    {
		$company_id = $this->session->userdata('current_company_id');       
		$employee_sql = "";
        if ($employee_id != '')
        {
			$employee_sql = "AND ch.user_id = '$employee_id'";
		}
        
        $start_time_sql = "";
        if($start_time)
        {
            $start_time_sql = "AND DATE_FORMAT(ch.date_time,'%H:%i') >= '$start_time'";
        }
        $end_time_sql = "";
        if($end_time)
        {
            $end_time_sql = "AND DATE_FORMAT(ch.date_time,'%H:%i') <= '$end_time'";
        }
		$where = " AND b.state < '3'";
		if($cancelled_bookings)
		{
			$where = " AND (b.state = '4' OR b.state = '5')";
		}

		$is_charge_type_deleted = "";
		if(!is_deleted_chargetype_linked_with_charge($company_id)) {
			$is_charge_type_deleted = " ct.is_deleted = '0' AND ";
		}
		
		// Get detailed charge information
		$sql = "select 
					ch.booking_id,
					MIN(r.room_name) as room_name,  
					c.customer_name,
					cut.name as customer_type,
					ct.name as charge_type,
					ch.charge_type_id, 
					ct.is_room_charge_type,
					ch.amount,
					ch.description
				FROM charge as ch, charge_type as ct, booking as b
				LEFT JOIN customer as c ON b.booking_customer_id = c.customer_id AND c.is_deleted = '0'
				LEFT JOIN customer_type as cut ON c.customer_type_id = cut.id AND cut.is_deleted = '0'
				LEFT JOIN booking_block as brh ON brh.booking_id = b.booking_id
				LEFT JOIN room as r ON brh.room_id = r.room_id
				WHERE
					ch.charge_type_id = ct.id AND 
					ch.is_deleted = '0' AND 
					$is_charge_type_deleted
					#ct.is_deleted = '0' AND 
					b.company_id = '$company_id'  AND 					
					#ct.company_id = '$company_id'  AND 
					b.booking_id = ch.booking_id $employee_sql $start_time_sql $end_time_sql AND
					b.is_deleted = '0' 
					$where AND
					ch.selling_date BETWEEN '$date' AND '$date_end'
				GROUP BY ch.charge_id
				ORDER BY room_name ASC, charge_type
		";
		
		$data = array();
        $q = $this->db->query($sql);
        
		//echo $this->db->last_query();
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
			
		if ($q->num_rows() > 0)
        {
			foreach($q->result_array() as $row)
            {
				$data[] = array('charge' => $row, 'tax_type' => $this->Charge_type_model->get_taxes($row['charge_type_id'], $row['amount']));
			}
		}
		return $data;
    }
	
	// insert new charges
	// called from save invoice button or run_night_audit
	// the reason there's company_id in the parameter is because we need to know what the selling_date is
	function insert_charges($company_id, $data)
	{
		if (!$data) return "No charges have been made";
		
		$sql = array();
		$this->load->helper('timezone');
		$date = convert_to_UTC_time(new DateTime(date('Y-m-d H:i:s'))); // Apply time zone
		$date_time = $date->format('Y-m-d H:i:s');
		
		$charge_data = array();
		foreach( $data as $row ) {
			if (isset($row['selling_date']))
			{
				$selling_date = trim($row['selling_date']);
			}
			else
			{
				$selling_date = $this->Company_model->get_selling_date($company_id);
			}
			
			$charge_data[] = array(
				'date_time' => $date_time,
				'selling_date' => $selling_date,
				'description' => trim($row['description']),
				'customer_id' => trim(isset($row['customer_id']) ? $row['customer_id'] : 0),
				'charge_type_id' => trim($row['charge_type_id']),
				'amount' => trim($row['amount']),
				'booking_id' => $row['booking_id'],
				'user_id' => $row['user_id'],
                'pay_period' => isset($row['pay_period']) ? $row['pay_period'] : DAILY,
                'is_night_audit_charge' => isset($row['is_night_audit_charge']) ? $row['is_night_audit_charge'] : 0
				);
		}
        
        $folio_id = isset($row['folio_id']) ? $row['folio_id'] : 0;
        $this->db->insert_batch('charge', $charge_data);
        $number_of_charges = count($charge_data);
        $first_id = $this->db->insert_id(); 
        $charge_id = $first_id + ($number_of_charges - 1);
        
		$charge_folio_data = array("charge_id" => $charge_id, "folio_id" => $folio_id);
		$this->db->insert('charge_folio', $charge_folio_data);
	    $response = "Successfully made $number_of_charges charge(s)\n";
		// error checking
		if ($this->db->_error_message()) 
		{
			echo "error occured!";
			$response = $this->db->last_query()."\n\n<br/><br/>[ERROR MESSAGE]:".$this->db->_error_message();
			
			// email error report
			$this->load->library('email');

			$whitelabelinfo = $this->session->userdata('white_label_information');

            $from_email = isset($whitelabelinfo['support_email']) && $whitelabelinfo['support_email'] ? $whitelabelinfo['support_email'] : 'support@minical.io';
            
            $to_email = isset($whitelabelinfo['support_email']) && $whitelabelinfo['support_email'] ? $whitelabelinfo['support_email'] : 'support@minical.io';

			$this->email->from($from_email);
			$this->email->to($to_email);
			$this->email->subject("insert_charges error");
			$this->email->message($response);
			$this->email->send();
			
			show_error($this->db->_error_message());		
		}

		return $response;
	}
		
	// Used when saving invoice only
	function insert_charge($data) 
	{		
		$this->db->insert('charge', $data);
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
                return $this->db->insert_id();
	}

	function insert_charge_folio($charge_id, $folio_id) {
		$data = array("charge_id" => $charge_id, "folio_id" => $folio_id);
		$this->db->insert('charge_folio',$data);
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
                return $this->db->insert_id();
	}

	
	function get_charge($charge_id)
	{
		$this->db->where('charge_id', $charge_id);		
		$query = $this->db->get('charge');
		
		if ($query->num_rows() > 0)
		{
			return $query->row_array();
		}
		else
		{
			return null;
		}
	}
	
	/**
	* Returns all charges between start and end dates. Grouped by Date.
	* The past & present charges are fetched using the existing charges in the charge table
	* If current_selling_date is specified, then the future charges (charges made after current_selling_date)
	* are "calculated" using rate plans & rates entered. 
	* This is because these charges do not exist yet in the charge table.
	*
	* @access	public
	* @param	string, string, string
	* @return	array
	*/
	function get_all_charges($start_date, $end_date, $current_selling_date = '2099-01-01',  $get_room_charges_only = false, $include_taxes = true, $customer_type_id = null, $is_tax_exempt = '', $include_cancelled_noshow_bookings = true)
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
        if($customer_type_id)
        {
            $join_customer_type = 'LEFT JOIN customer as cst ON cb.booking_customer_id = cst.customer_id';
            $where_statement = "AND cst.customer_type_id = '$customer_type_id'";
        }
		$booking_state_where_condition = " cb.state <= '5'";
		if(!$include_cancelled_noshow_bookings)
		{
			$booking_state_where_condition = " cb.state < '3'";
		}
        
		$company_id = $this->session->userdata('current_company_id');		
		$data = null;        
		 $sql = "
				SELECT 
					date, 
					IF(date <= '$current_selling_date', 
						past_and_present_charge,
						0
					) as total_charge
				FROM 
					date_interval as di
					LEFT JOIN
					(
						SELECT 
							charge_table.selling_date,
							charge_table.past_and_present_charge
						FROM (
							SELECT 
								x.selling_date,
								ROUND(SUM(x.past_and_present_charge), 2) as past_and_present_charge
							FROM (
								SELECT 
									c.selling_date,
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
								$join_customer_type
								WHERE 
										c.is_deleted = '0' AND
										c.selling_date >= '$start_date' AND
										c.selling_date <= '$end_date' AND
										$booking_state_where_condition AND
										cb.is_deleted != '1' AND
										#ct2.company_id = '$company_id' AND
										#IF(cb.is_ota_booking = 1, ct2.company_id = '0', ct2.company_id = '$company_id') AND
										cb.company_id = '$company_id' AND
										ct2.id = c.charge_type_id
										$get_room_charges_only_sql
										$where_statement

								#GROUP BY c.selling_date
							) as x
							GROUP BY x.selling_date
						) charge_table				
					) report_table ON report_table.selling_date = di.date
				WHERE
					di.date >= '$start_date' AND
					di.date <= '$end_date'
				GROUP BY di.date
				";
		
		$query = $this->db->query($sql);
        
        $future_charges = $this->forecast_charges->_get_day_wise_forecast_charges($start_date, date("Y-m-d", strtotime($end_date." +1day")), $current_selling_date, $get_room_charges_only, $include_taxes, $customer_type_id, $is_tax_exempt);
        if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		$result = array();
		if ($query->num_rows >= 1) 
		{	
			foreach ($query->result_array() as $row)
			{
				if($future_charges && isset($future_charges[$row['date']])){					
                    $result[$row['date']] = $row['total_charge'] + $future_charges[$row['date']];
                    unset($future_charges[$row['date']]);
                }else{					
                    $result[$row['date']] = $row['total_charge'];
                }
			}
		}
        if($future_charges && count($future_charges) > 0){
            foreach($future_charges as $date => $amount){
                $result[$date] = $amount;
            }
        }

        return $result;	
	}

	function get_all_tax_exempt_charges($start_date, $end_date, $current_selling_date = '2099-01-01',  $get_room_charges_only = false, $include_taxes = true, $customer_type_id = null, $is_tax_exempt = '', $include_cancelled_bookings = false)
	{
		$where_condition = " cb.state < '3' AND";
		if($include_cancelled_bookings)
		{
			$where_condition = " cb.state <= '5' AND";
		}
		$get_room_charges_only_sql = $join_customer_type = $where_statement = "";
		if ($get_room_charges_only)
		{
			$get_room_charges_only_sql = "AND is_room_charge_type = '1'";
		}
		$past_and_present_charge = "c.amount";
		if ($include_taxes)
		{
			$past_and_present_charge = "c.amount + SUM(
												IF(tt.is_brackets_active = 1, IF(tpb.is_percentage, (c.amount * tpb.tax_rate * 0.01), tpb.tax_rate), IF(tt.is_percentage, (c.amount * tt.tax_rate * 0.01), tt.tax_rate)) 
											)";
		}
        if($customer_type_id)
        {
            $join_customer_type = 'LEFT JOIN customer as cst ON cb.booking_customer_id = cst.customer_id';
            $where_statement = "AND cst.customer_type_id = '$customer_type_id'";
        }
    	$is_tax_exempt_sql = "";
		if($is_tax_exempt === 0 || $is_tax_exempt === 1){
			$is_tax_exempt_sql = " AND ct2.is_tax_exempt = $is_tax_exempt ";
		}
        
		$company_id = $this->session->userdata('current_company_id');
		
		$data = null;        
		$sql = "
				SELECT 
					date, 
					IF(date < '$current_selling_date', 
						past_and_present_charge,
						0
					) as total_charge
				FROM 
					date_interval as di
					LEFT JOIN
					(
						SELECT 
							charge_table.selling_date,
							charge_table.past_and_present_charge
						FROM (
							SELECT 
								x.selling_date,
								ROUND(SUM(x.past_and_present_charge), 2) as past_and_present_charge
							FROM (
								SELECT 
									c.selling_date,
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
									), c.amount)  as past_and_present_charge
								FROM 
									charge_type as ct2, 
									charge as c					
								LEFT JOIN booking as cb ON cb.booking_id = c.booking_id
								$join_customer_type
								WHERE 
										c.is_deleted = '0' AND
										c.selling_date >= '$start_date' AND
										c.selling_date <= '$end_date' AND
										$where_condition
										cb.is_deleted != '1' AND
										ct2.company_id = '$company_id' AND
										cb.company_id = '$company_id' AND
										ct2.id = c.charge_type_id
										$is_tax_exempt_sql
										$get_room_charges_only_sql
										$where_statement

								#GROUP BY c.selling_date
							) as x
							GROUP BY x.selling_date
						) charge_table				
					) report_table ON report_table.selling_date = di.date
				WHERE
					di.date >= '$start_date' AND
					di.date <= '$end_date'
				GROUP BY di.date
				";

		$query = $this->db->query($sql);
        
        $future_charges = $this->forecast_charges->_get_day_wise_forecast_charges($start_date, date("Y-m-d", strtotime($end_date." +1day")), $current_selling_date, $get_room_charges_only, $include_taxes, $customer_type_id, $is_tax_exempt);
        
        if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		$result = array();
		if ($query->num_rows >= 1) 
		{	
			foreach ($query->result_array() as $row)
			{
                if($future_charges && isset($future_charges[$row['date']])){
                    $result[$row['date']] = $row['total_charge'] + $future_charges[$row['date']];
                    unset($future_charges[$row['date']]);
                }else{
                    $result[$row['date']] = $row['total_charge'];
                }
			}
		}
        if($future_charges && count($future_charges) > 0){
            foreach($future_charges as $date => $amount){
                $result[$date] = $amount;
            }
        }

        return $result;	
	}
    
	function get_total_charges($start_date, $end_date, $selling_date, $get_room_charges_only = false, $include_taxes = true, $type = 'room_wise', $include_cancelled_bookings = false){
        
		$where_condition = " cb.state < '3' AND";
		if($include_cancelled_bookings)
		{
			$where_condition = " cb.state <= '5' AND";
		}
        $company_id = $this->session->userdata('current_company_id');
        
        $get_room_charges_only_sql = "";
		if ($get_room_charges_only)
		{
			$get_room_charges_only_sql = "AND is_room_charge_type = '1'";
			
		}

        $past_and_present_charge = "c.amount";
		if ($include_taxes)
		{
			$past_and_present_charge = "c.amount + 
											SUM(
												IF(
													tt.is_brackets_active = 1,
													IF(
														tt.is_tax_inclusive = 1,
														0,
														IF(
															tpb.is_percentage,
															(c.amount * tpb.tax_rate * 0.01),
															tpb.tax_rate
														)
													),
													IF(
														tt.is_tax_inclusive = 1,
														0,
														IF(
															tt.is_percentage,
															(c.amount * tt.tax_rate * 0.01),
															tt.tax_rate
														)
													)
												) 
											)";
		}
        
        $select = 'cbrh.room_id';
		$outer_select = 'x.room_id';
		
		$group = 'x.room_id';
        $join = "";
        $key = "room_id";
        if($type == 'roomtype_wise'){
            $group = 'x.room_type_id';
            $select = "r.room_type_id";
			$outer_select = 'x.room_type_id';
            $key = "room_type_id";
            $join = 'LEFT JOIN room as r ON r.room_id = cbrh.room_id';
        }
		$sql = "SELECT 
					$outer_select,
					SUM(x.total_charge) as total_charge
				FROM (
					SELECT 
						$select,
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
									ct3.company_id = '$company_id' AND
									((tt.is_brackets_active = 1 AND c.amount BETWEEN tpb.start_range AND tpb.end_range) OR (tt.is_brackets_active != 1))
									$get_room_charges_only_sql
								GROUP BY cttl.charge_type_id
						), c.amount) as total_charge
					FROM 
						charge_type as ct2, 
						charge as c
					LEFT JOIN booking as cb ON cb.booking_id = c.booking_id
					LEFT JOIN booking_block as cbrh ON cbrh.booking_room_history_id = (
							SELECT MIN(cbrh2.booking_room_history_id) as booking_room_history_id FROM 
                            booking_block as cbrh2 WHERE cbrh2.booking_id = cb.booking_id
                        )
					$join
					WHERE 
							c.is_deleted = '0' AND
							c.selling_date >= '$start_date' AND
							c.selling_date <= '$end_date' AND
							$where_condition
							cb.is_deleted != '1' AND
							ct2.company_id = '$company_id' AND
							cb.company_id = '$company_id' AND
							ct2.id = c.charge_type_id
							$get_room_charges_only_sql
				) as x
                GROUP BY $group";
		
        $query = $this->db->query($sql);
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

        $result = array();
		if ($query->num_rows >= 1) 
		{	
			foreach ($query->result_array() as $row)
			{
				$result[$row[$key]] = $row['total_charge'];
			}
		}
		
		return $result;	
    }
    
	function get_year_total($date) {
		// extract month and year of $date
		$parts = explode('-', $date);
		$month = $parts[1];
		$year = $parts[0];		
		
		$company_id = $this->session->userdata('current_company_id');
		
		$data = null;        
		$sql = "
				SELECT
				  SUM(b.rate) total_rate,
				  COUNT(b.booking_id) booking_count,
				  di2.date date
				FROM (SELECT
				        di.date
				      FROM date_interval di
				      WHERE YEAR(di.date) = '$year'
				          ) di2,
				  booking_block brh
				  LEFT JOIN booking b
				    ON brh.booking_id = b.booking_id
				WHERE 
					(
						(DATE(brh.check_in_date) <= di2.date AND DATE(brh.check_out_date) > di2.date) OR
						(DATE(brh.check_in_date) = di2.date AND DATE(brh.check_in_date) = DATE(brh.check_out_date))
					)AND
				    (b.state = '".INHOUSE."' OR b.state = '".CHECKOUT."') AND
				    b.company_id = '$company_id' 
				GROUP BY di2.date 
				";
		$result = $this->db->query($sql);
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		return $result->result();	
	}
	// returns a booking's list of charges + taxes associated with it as a sub-array
	function get_charges($booking_id, $customer_id = false, $folio_id = null, $is_first_folio = false)
    {
    	$this->db->where("charge.booking_id IN ($booking_id)");
        
        if ($customer_id)
        	$this->db->where('charge.customer_id', $customer_id);
		
		if($is_first_folio) {
            $this->db->where("(folio_id = '$folio_id' OR folio_id IS NULL OR folio_id = 0)");
		} elseif($folio_id) {
            $this->db->where('folio_id', $folio_id);
		}
		
		$this->db->where('charge.is_deleted', '0');
		$this->db->join('charge_folio as cf' , 'charge.charge_id = cf.charge_id', 'left');
        $this->db->join('charge_type as ct', 'charge.charge_type_id = ct.id', 'left');
		$this->db->join('customer as cu', 'charge.customer_id = cu.customer_id', 'left');
		$this->db->join('user_profiles', 'charge.user_id = user_profiles.user_id', 'left');
        $this->db->join('booking as b', 'b.booking_id = charge.booking_id', 'left');
		$this->db->join('booking_block as bb', 'bb.booking_id = b.booking_id', 'left');
		$this->db->join('room as r', 'r.room_id = bb.room_id', 'left');

		if($this->is_group_booking_features){
			$this->db->join('room_type as rt', 'r.room_type_id = rt.id', 'left');
			$this->db->select('charge.*, cu.*, ct.*, user_profiles.*, b.*, b.pay_period, ct.name as charge_type_name, ct.id as charge_type_id,`cf`.`folio_id` as folio_id, CONCAT_WS(" ",first_name,  last_name ) as user_name, r.room_name, rt.name as room_type_name');
		} else {
		$this->db->select('charge.*, cu.*, ct.*, user_profiles.*, b.*, b.pay_period, ct.name as charge_type_name, ct.id as charge_type_id,`cf`.`folio_id` as folio_id, CONCAT_WS(" ",first_name,  last_name ) as user_name, r.room_name');
		}
		
		$this->db->group_by('charge.charge_id');
        $this->db->order_by('selling_date', 'ASC');
        $query = $this->db->get("charge");
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		return $query->result_array();
    }
    
	function get_accounting_charges($start_date, $end_date, $include_cancelled_bookings = false){
		
		$where_condition = " b.state < '3' AND";
		if($include_cancelled_bookings)
		{
			$where_condition = " b.state <= '5' AND";
		}
		
		$company_id = $this->session->userdata('current_company_id');

		$is_charge_type_deleted = "";
		if(!is_deleted_chargetype_linked_with_charge($company_id)) {
			$is_charge_type_deleted = " AND ct.is_deleted = '0' ";
		}

		$sql = "
				SELECT DISTINCT ct.id, ct.name
				FROM charge_type as ct
				WHERE ct.company_id = '$company_id' $is_charge_type_deleted
				#AND ct.is_deleted = '0'";
		$q = $this->db->query($sql);		
		$charge_type_array = $q->result_array();		
		$str_array = $str_array_sum = $unique_charges = Array();		
		// foreach($charge_type_array as $row){
  //           if(in_array($row['name'], $unique_charges)){
  //               continue;
  //           }
  //           $unique_charges[] = $row['name'];
		// 	$str_array[] = "SUM(IF(charge_type_id='".$row['id']."', amount,' ')) AS ".$this->db->escape($row['name']);
		// 	$str_array_sum[] = "SUM(IF(report_table.charge_type_id='".$row['id']."', amount,' ')) AS ".$this->db->escape($row['name']);
		// }


		foreach (array_reverse($charge_type_array, true) as $key => $row) {
		    if (!in_array($row['name'], $unique_charges)) {
		        $unique_charges[] = $row['name'];
				$str_array[] = "SUM(IF(charge_type_id='".$row['id']."', amount,' ')) AS ".$this->db->escape($row['name']);
				$str_array_sum[] = "SUM(IF(report_table.charge_type_id='".$row['id']."', amount,' ')) AS ".$this->db->escape($row['name']);
		    }
		}

		$str_array = array_reverse($str_array);
		$str_array_sum = array_reverse($str_array_sum);
		$charge_types_str = implode(", ", $str_array);
		$charge_types_str_sum = implode(", ", $str_array_sum);
		$sql = "	
			SELECT date as 'Selling Date', 
			$charge_types_str_sum
			FROM date_interval as di
			LEFT JOIN (		
				SELECT charges.selling_date,charge_type_id,
                amount, $charge_types_str
				FROM (
					SELECT c.selling_date, c.charge_type_id as charge_type_id, SUM(c.amount) as amount
					FROM charge as c, booking as b
					WHERE 
						c.booking_id = b.booking_id AND
						$where_condition
						b.is_deleted = '0' AND
						c.is_deleted = '0' AND
						b.company_id = '$company_id' ";
						if($start_date && $end_date){
							$sql .=" AND c.selling_date >= '$start_date' AND c.selling_date <= '$end_date'";
						}
		            $sql .= " GROUP BY c.selling_date, charge_type_id
                ) as charges
                GROUP BY charges.selling_date, charges.charge_type_id
            )report_table ON report_table.selling_date = di.date
            WHERE";
			if($start_date && $end_date){
				$sql .= " di.date >= '$start_date' AND di.date <= '$end_date'";
			}			
			
        $q = $this->db->query($sql);
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
        $result = "";
		if ($q)
			$result = $q->result_array()[0];	
		return $result;
	}
	

	// Parameters: record_type_array, date (for finding current month)
	function get_monthly_sales($date= NULL, $date_range = array(), $include_cancelled_bookings = false)
	{
		$where_condition = " b.state < '3' AND";
		if($include_cancelled_bookings)
		{
			$where_condition = " b.state <= '5' AND";
		}
		if(empty($date_range))
        {
            if ($date == "")
            {
                $date = date("Y-m-d");
            }
                $parts = explode('-', $date);
                $month = $parts[1];
                $year  = $parts[0];
        }
        else
        {
            $start_date = $date_range['from_date'];
            $end_date = $date_range['to_date'];
        }	
		
		$company_id = $this->session->userdata('current_company_id');

		$is_charge_type_deleted = "";
		if(!is_deleted_chargetype_linked_with_charge($company_id)) {
			$is_charge_type_deleted = " AND ct.is_deleted = '0' ";
		}
		
		$sql = "
				SELECT DISTINCT ct.id, ct.name
				FROM charge_type as ct
				WHERE 
					(ct.company_id = '$company_id' OR ct.company_id = 0)
					$is_charge_type_deleted
					#AND ct.is_deleted = '0'
				";
				
		$q = $this->db->query($sql);		
		$charge_type_array = $q->result_array();		
		
		$str_array = $unique_charges = Array();		
		// foreach($charge_type_array as $row)
		// {
  //           if(in_array($row['name'], $unique_charges)){
  //               continue;
  //           }
  //           $unique_charges[] = $row['name'];
		// 	$str_array[] = "SUM(IF(charge_type_id='".$row['id']."', amount,' ')) AS ".$this->db->escape($row['name']);
		// }

		foreach (array_reverse($charge_type_array, true) as $key => $row) {
		    if (!in_array($row['name'], $unique_charges)) {
		        $unique_charges[] = $row['name'];
				$str_array[] = "SUM(IF(charge_type_id='".$row['id']."', amount,' ')) AS ".$this->db->escape($row['name']);
		    }
		}

		$str_array = array_reverse($str_array);
		
		$charge_types_str = implode(", ", $str_array);
		
		$sql = "	
			SELECT date as 'Selling Date', report_table.*
			FROM date_interval as di
			LEFT JOIN
			(		
				SELECT charges.selling_date, $charge_types_str
				FROM (
					SELECT c.selling_date, c.charge_type_id, sum(c.amount) as amount
					FROM charge as c, booking as b
					WHERE 
						c.booking_id = b.booking_id AND
						$where_condition
						b.is_deleted = '0' AND
						c.is_deleted = '0' AND
						b.company_id = '$company_id' ";
                        if(empty($date_range))
                        {
                            $sql .= " AND MONTH(c.selling_date) = '$month' AND 
                                      YEAR(c.selling_date) = '$year'";
                        }
                        else
                        {
                            $sql .=" AND c.selling_date >= '$start_date' AND
                                     c.selling_date <= '$end_date'";
                        }
					
					    $sql .= "GROUP BY c.selling_date, charge_type_id
                                ) as charges
                                GROUP BY charges.selling_date
                                )report_table ON report_table.selling_date = di.date
                                WHERE ";
                                if(empty($date_range))
                                {
                                    $sql .= "MONTH(di.date) = '$month' AND YEAR(di.date) = '$year'";
                                }
                                else
                                {
                                    $sql .= "di.date >= '$start_date' AND di.date <= '$end_date'";
                                }

                                $sql .= " ORDER by di.date" ;
		
		$q = $this->db->query($sql);
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

			
		//echo $this->db->last_query();		
		$result = "";
		if ($q)
			$result = $q->result_array();	
		return $result;
	}	
	
	function get_booking_id_by_charge_id($charge_id) {
		$this->db->where('charge_id', $charge_id);
        $query = $this->db->get('charge');
        $q = $query->result();
		
		//echo $this->db->last_query();
		$result = $q[0]->booking_id;
        return $result;
	}
	
        function get_amount_by_charge_id($charge_id) {
            $this->db->where('charge_id', $charge_id);
            $query = $this->db->get('charge');
            $q = $query->result();
            $result = $q[0]->amount;
            return $result;
	}
        
	function delete_charge($charge_id, $company_id = null) {
		//To be fixed. Right now charge doesn't have company id
		/*
		if ($company_id != null) {
			$this->db->where('company_id', $company_id);
		}	*/	
		
		$data['is_deleted'] = '1';
        $this->db->where('charge_id', $charge_id);
        $this->db->update("charge", $data);
		//echo $this->db->last_query();
	}
	
	function update_charge($data, $company_id = null) {
	
		if ($company_id != null) {
			$this->db->where('company_id', $company_id);
		}
		
		$this->db->where('charge_id', $data['charge_id']);
        $this->db->update("charge", $data);
		//echo $this->db->last_query();
	}

	
    function update_charge_booking($old_booking_id, $new_booking_id, $customer_id) {


        $sql = "UPDATE
				    `charge` AS c
				LEFT JOIN charge_type AS ct
				ON
				    ct.id = c.charge_type_id
				SET
				    `booking_id` = '$new_booking_id'
				  
				WHERE
				    `ct`.`company_id` = '$this->company_id' AND `ct`.`is_deleted` = 0 AND `c`.`booking_id` = '$old_booking_id'"
				;


        $q = $this->db->query($sql);

    }

    function update_booking_charges($old_booking_ids, $new_booking_ids) {

    	$i = 0; $data = array();
    	foreach ($old_booking_ids as $key => $value) {
    		if($value) {
    			$data[] = " WHEN booking_id = $value THEN $new_booking_ids[$i]";
    		}
			$i++;
    	}

		$sql = "UPDATE
				    `charge` AS c
				LEFT JOIN charge_type AS ct
				ON
				    ct.id = c.charge_type_id SET `booking_id` = CASE ";

		foreach ($data as $k => $val) {
    		$sql .= $val;
    	}

		$sql .= " END
				WHERE
				    `ct`.`company_id` = '$this->company_id' AND `ct`.`is_deleted` = 0";

		$q = $this->db->query($sql);
    }
  
    function get_last_applied_charge($booking_id, $charge_type_id, $end_date = null, $only_night_audit_charge = false)
	{
		$this->db->where('charge_type_id', $charge_type_id);		
        $this->db->where('booking_id', $booking_id);		
        $this->db->where('is_deleted', 0);	
        if($only_night_audit_charge) {
            $this->db->where('is_night_audit_charge', 1);	
        }
        
        if($end_date){
            $this->db->where('selling_date <', $end_date);	
        }
        
        $this->db->order_by('selling_date', 'DESC');		
        $this->db->limit(1);		
		$query = $this->db->get('charge');
		
		if ($query->num_rows() > 0)
		{
			return $query->result_array()[0];
		}
		else
		{
			return null;
		}
	}
    
    function get_applied_charges_and_dates($booking_id, $charge_type_id, $end_date = null, $company_id = null)
	{
        
        $sql = "SELECT c.selling_date, IFNULL(SUM(CAST((c.amount * tt.tax_rate / 100) as DECIMAL(16, 2))), 0) as tax_total, c.amount as charge_total
				FROM charge as c
				LEFT JOIN (
						SELECT
							ct.id as charge_type_id, t.tax_type, t.tax_type_id, tax_rate 
						FROM 
							tax_type as t, 
							charge_type_tax_list as cttl, 
							charge_type as ct
						WHERE 
							cttl.charge_type_id = ct.id AND
							ct.company_id = '$company_id' AND
							cttl.tax_type_id = t.tax_type_id AND
							t.is_deleted != '1' AND
							ct.is_deleted != '1'
						
					)tt ON c.charge_type_id = tt.charge_type_id
				WHERE 
					c.booking_id = '$booking_id' AND 
					c.is_deleted != '1' AND
                    c.selling_date < '$end_date' AND
                    c.charge_type_id = '$charge_type_id'
				GROUP BY c.charge_id
                ORDER BY c.selling_date ASC
		";
		$query = $this->db->query($sql);
		$charges = array();
		if ($query->num_rows() > 0)
		{
            $result = $query->result_array();
            foreach($result as $charge){
                $charges[$charge['selling_date']] = $charge['charge_total'] + $charge['tax_total'];
            }
			return $charges;
		}
		else
		{
			return null;
		}
	}
	
	function get_daily_charges($company_id = NULL, $date = NULL)
	{
		$query = "SELECT *, p.date_time as deposit_date
			FROM booking_block as brh 
			LEFT JOIN booking as b ON (brh.booking_id = b.booking_id)
			LEFT JOIN customer as c ON (c.customer_id = b.booking_customer_id)
			LEFT JOIN payment as p ON (b.booking_id = p.booking_id AND p.is_deleted != '1')
			LEFT JOIN booking_log as bl ON (bl.booking_id = b.booking_id)
			LEFT JOIN user_profiles as up ON (up.user_id = bl.user_id)
			WHERE DATE(brh.check_in_date) >= '$start_date' AND DATE(check_in_date) <= '$end_date' AND b.company_id = '$company_id' AND b.is_deleted != '1' AND bl.log_type = '1' $amount_condition GROUP BY b.booking_id";

		$q = $this->db->query($query);

		// error checking
		if ($this->db->_error_message()) {
			show_error($this->db->_error_message());
		}	

		if ($q->num_rows() > 0) {
			$result = $q->result_array();
			return $result;
		}
		return null;
	}
	
	function get_charges_by_date($company_id = NULL, $date= NULL, $date_range = array(), $charge_type_id = null)
	{
		$result1 = $result2 = array();
		if(empty($date_range))
        {
            $start_date = $date;
            $end_date = $date;
			$get_charge_by_date = " c.selling_date >= '$start_date' AND c.selling_date <= '$end_date'";
        }
        else
        {
            $start_date = $date_range['from_date'];
            $end_date = $date_range['to_date'];
			$get_charge_by_date = " c.selling_date >= '$start_date' AND c.selling_date <= '$end_date'";
        }
		
		if($charge_type_id)
		{
			$query = "SELECT SUM(c.amount) as charge_total, count(*) as charge_count
			FROM charge as c 
			LEFT JOIN charge_type as ct ON (ct.id = c.charge_type_id AND ct.is_deleted != '1')
			LEFT JOIN charge_folio as cf ON (cf.charge_id = c.charge_id)
			LEFT JOIN booking as b ON (b.booking_id = c.booking_id AND c.is_deleted != '1')
			LEFT JOIN booking_block as brh ON (brh.booking_id = b.booking_id)
			LEFT JOIN room as r ON (r.room_id = brh.room_id)
			WHERE $get_charge_by_date AND c.charge_type_id = '$charge_type_id' AND b.is_deleted != '1'";            

			$q = $this->db->query($query);
			// error checking
			if ($this->db->_error_message()) {
				show_error($this->db->_error_message());
			}

			if ($q->num_rows() > 0) {
				$result = $q->result_array();
				$arr = array();
				$arr['charge_count'] = $result[0]['charge_count'] ? $result[0]['charge_count'] : 0;
				$arr['charge_total'] = $result[0]['charge_total'] ? $result[0]['charge_total'] : 0;
				return $arr;
			}
			return null;
		} else {
			$query = "SELECT ct.name as charge_type, r.room_name, cf.folio_id, c.amount as charge_amount, ct.id as charge_type_id  
			FROM charge as c 
			LEFT JOIN charge_type as ct ON (ct.id = c.charge_type_id AND ct.is_deleted != '1')
			LEFT JOIN charge_folio as cf ON (cf.charge_id = c.charge_id)
			LEFT JOIN booking as b ON (b.booking_id = c.booking_id AND c.is_deleted != '1')
			LEFT JOIN booking_block as brh ON (brh.booking_id = b.booking_id)
			LEFT JOIN room as r ON (r.room_id = brh.room_id)
			WHERE $get_charge_by_date AND b.company_id = '$company_id' AND ct.company_id = '$company_id' AND b.is_deleted != '1' GROUP BY c.charge_id ORDER BY charge_type";            

			$q = $this->db->query($query);
			// error checking
			if ($this->db->_error_message()) {
				show_error($this->db->_error_message());
			}
			if ($q->num_rows() > 0) {
				$result1 = $q->result_array();
				return $result1;
			}
			return null;
		}
		
	}

    function delete_charges($booking_id, $is_batch = false){

        $data = Array('is_deleted' => 1);

        if($is_batch)
        	$this->db->where_in('booking_id', $booking_id);
        else
            $this->db->where('booking_id', $booking_id);
        
        $this->db->update("charge", $data);

        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
    }
}
