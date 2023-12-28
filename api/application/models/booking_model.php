<?php

class Booking_model extends CI_Model {

    function __construct()
    {
        parent::__construct();

        $this->load->helper("guid");		
		$this->load->library("Forecast_charges");	
    }
    
    function get_booking_detail($booking_id)
    {
		$sql = "
			SELECT 
				c.customer_name as booking_customer_name,
				c.email as booking_customer_email,
				c.customer_type as booking_customer_type,
				b.state,
				b.is_deleted,
				b.source,
				b.booking_id,
				c.customer_id as booking_customer_id,
				b.adult_count,
				b.children_count,
				b.rate,
				b.booking_notes,
				b.company_id,
				b.use_rate_plan,
				b.rate_plan_id,
				b.color,
				b.charge_type_id,
				b.housekeeping_notes,
				b.invoice_hash,
				b.guest_review, 
        b.pay_period,
				(
					SELECT 
						MIN(brh1.check_in_date) as check_in_date
					FROM
						booking_block as brh1
					WHERE
						brh1.booking_id = b.booking_id
				) as check_in_date,
                (
					SELECT 
						MAX(brh2.check_out_date) as check_out_date
					FROM
						booking_block as brh2
					WHERE
						brh2.booking_id = b.booking_id
                ) as check_out_date
                
			FROM booking as b
			LEFT JOIN customer as c ON c.customer_id = b.booking_customer_id
			WHERE b.booking_id = '$booking_id' 

			";
		
		$query = $this->db->query($sql);	

		//echo $this->db->last_query();

		if ($this->db->_error_message()) 
		{
			show_error($this->db->_error_message());
		}
		
        $q = $query->row_array(0);
		
        return $q;
    }
	
    function get_selling_date($company_id) {
		$selling_date = null;
		$this->db->select("selling_date");
		$this->db->where('company_id', $company_id);

		$q = $this->db->get("company");

		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		foreach ($q->result() as $row) {
			$selling_date = $row->selling_date;
		}
		//echo $this->db->last_query();
		return $selling_date;
	}

    function get_company_by_booking($booking_id) {
        $selling_date = null;
        $this->db->from("company");
        $this->db->join("booking", "booking.company_id = company.company_id");
        $this->db->where('booking_id', $booking_id);

        $q = $this->db->get();

        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());

        return $q->row_array();
    }
    
    function _get_forecast_charges($booking)
    {
        $this->load->model('Tax_model');
        $this->load->model('Rate_plan_model');
        $this->load->model('Charge_type_model');
        $this->load->model('Charge_model');
        $this->load->library('rate');
        
        $company_id = $booking['company_id'];
        $selling_date = $this->get_selling_date($company_id);
        
        $booking_id = $booking['booking_id'];
        $start_date = max($booking['check_in_date'], $selling_date);
        $end_date = $booking['check_out_date'];
        $check_in_date = $booking['check_in_date'];
        $check_out_date = $booking['check_out_date'];
        $pay_period = $booking['pay_period'];
        $rate = $booking['rate'];
        
        $next_expected_charge_date = null;
        $next_expected_charge_date_found = false;
        $total_charges = 0;
        $total_taxes = array();
            
        $rate_plan   = $this->Rate_plan_model->get_rate_plan($booking['rate_plan_id']);
        $charge_type = $this->Charge_type_model->get_charge_type_by_id($rate_plan['charge_type_id']);
        
        $room_charge_type_id = $this->Charge_type_model->get_room_charge_type_id($booking_id);
        
        $last_room_charge = $this->Charge_model->get_last_applied_charge($booking_id, $room_charge_type_id, null, true);

        $next_room_charge_date = null;
        $days = 1;
        $date_increment = "+1 day";
        $date_decrement = "-1 day";
        $description = "Daily Room Charge";

        if($booking['pay_period'] == WEEKLY)
        {
            $days = 7;
            $date_increment = "+7 days";
            $date_decrement = "-7 days";
            $description = "Weekly Room Charge";
        }
        elseif($booking['pay_period'] == MONTHLY)
        {
            $days = 30;
            $date_increment = "+1 month";
            $date_decrement = "-1 month";
            $description = "Monthly Room Charge";
        }
        
        if($last_room_charge)
        {
            $next_room_charge_date = date('Y-m-d', strtotime($last_room_charge['selling_date'].$date_increment));
            if($last_room_charge['pay_period'] != $booking['pay_period'] && $last_room_charge['pay_period'] == DAILY)
            {
                $next_room_charge_date = date('Y-m-d', strtotime($last_room_charge['selling_date']."+1 day"));
            }
            elseif($last_room_charge['pay_period'] != $booking['pay_period'] && $last_room_charge['pay_period'] == WEEKLY)
            {
                $next_room_charge_date = date('Y-m-d', strtotime($last_room_charge['selling_date']."+7 day"));
            }
            elseif($last_room_charge['pay_period'] != $booking['pay_period'] && $last_room_charge['pay_period'] == MONTHLY)
            {
                $next_room_charge_date = date('Y-m-d', strtotime($last_room_charge['selling_date']."+1 month"));
            }
        }

		$date_start = max($next_room_charge_date, $selling_date, $booking['check_in_date']);
		$date_end = $check_out_date;
        
        if($booking['pay_period'] == ONE_TIME)
        {
            if($last_room_charge || $check_out_date < $selling_date){
                return array("total_charges" => 0, "total_taxes" => array());
            }
            $date_start = $booking['check_in_date'];
        }
        
        if ($selling_date < $check_out_date || $check_in_date == $check_out_date)
        {
            
            if ($booking['use_rate_plan']) 
            {
                if (!($date_start < $date_end || $check_in_date == $check_out_date))
                {
                    return array("total_charges" => 0, "total_taxes" => array()); //forecast charges dates must be less than checkout date.
                }
                
                $tax_rates = $this->Tax_model->get_tax_rates_by_charge_type_id($rate_plan['charge_type_id'], $company_id);
                
				$rate_plan_id = $booking['rate_plan_id'];
				$adult_count = $booking['adult_count'];
				$children_count = $booking['children_count'];
				$this->load->library('rate');
				$rate_array = $this->rate->get_rate_array($rate_plan_id, $date_start, $date_end, $adult_count, $children_count);

				// change key names (i.e. date => selling_date, rate => amount
				foreach ($rate_array as $index => $rate)
				{
					$tax_total = 0;
                    if($tax_rates && count($tax_rates) > 0)
                    {
                        foreach($tax_rates as $tax){
                            if(!isset($total_taxes[$tax['tax_type']]))
                                $total_taxes[$tax['tax_type']] = 0;
                            $total_taxes[$tax['tax_type']] += ($rate['rate'] * $tax['tax_rate'] / 100);
                            $tax_total += ($rate['rate'] * $tax['tax_rate'] / 100);
                        }
                    }
                    $total_charges += $rate['rate'] + $tax_total;
				}
			}
			else // booking isn't using rate plan
			{
                if($end_date < $selling_date){
                    return array("total_charges" => 0, "total_taxes" => array());
                }
                
                $tax_rates = $this->Tax_model->get_tax_rates_by_charge_type_id($booking['charge_type_id'], $company_id);
                
                $days = 1;
                $date_increment = "+1 day";
                $date_decrement = "-1 day";
                if($pay_period == WEEKLY)
                {
                    $days = 7;
                    $date_increment = "+7 days";
                    $date_decrement = "-7 days";
                }
                elseif($pay_period == MONTHLY)
                {
                    $days = 30;
                    $date_increment = "+1 month";
                    $date_decrement = "-1 month";
                }
                elseif($pay_period == ONE_TIME)
                {
                    if($start_date)
                    {
                        $tax_total = 0;
                        if($tax_rates && count($tax_rates) > 0)
                        {
                            foreach($tax_rates as $tax){
                                if(!isset($total_taxes[$tax['tax_type']]))
                                    $total_taxes[$tax['tax_type']] = 0;
                                $total_taxes[$tax['tax_type']] += ($rate * $tax['tax_rate'] / 100);
                                $tax_total += ($rate * $tax['tax_rate'] / 100);
                            }
                        }
                        $total_charges += $rate + $tax_total;
                    }
                    return array(
                        "total_charges" => $total_charges,
                        "total_taxes" => $total_taxes
                    );
                }
                $start_date = max($start_date, $selling_date);
                $charge_start_date = $start_date;
                for ($charge_start_date = $start_date; 
                    $charge_start_date < $end_date && Date("Y-m-d", strtotime($date_increment, strtotime($charge_start_date))) <= $check_out_date; 
                    $charge_start_date = Date("Y-m-d", strtotime($date_increment, strtotime($charge_start_date)))
                ) {
                    $tax_total = 0;
                    if($tax_rates && count($tax_rates) > 0)
                    {
                        foreach($tax_rates as $tax){
                            if(!isset($total_taxes[$tax['tax_type']]))
                                $total_taxes[$tax['tax_type']] = 0;
                            $total_taxes[$tax['tax_type']] += ($rate * $tax['tax_rate'] / 100);
                            $tax_total += ($rate * $tax['tax_rate'] / 100);
                        }
                    }
                    $total_charges += $rate + $tax_total;

                }
                $date = $charge_start_date;
                if($date < $end_date)
                {   
                    $date_start = $date;
                    $daily_rate = round(($rate / $days), 2, PHP_ROUND_HALF_UP);
                    for ($date = $date_start; $date < $end_date; $date = Date("Y-m-d", strtotime("+1 day", strtotime($date))) )
                    {   
                        if($date >= $selling_date)
                        {
                            $tax_total = 0;
                            if($tax_rates && count($tax_rates) > 0)
                            {
                                foreach($tax_rates as $tax){
                                    if(!isset($total_taxes[$tax['tax_type']]))
                                        $total_taxes[$tax['tax_type']] = 0;
                                    $total_taxes[$tax['tax_type']] += ($daily_rate * $tax['tax_rate'] / 100);
                                    $tax_total += ($daily_rate * $tax['tax_rate'] / 100);
                                }
                            }
                            $total_charges += $daily_rate + $tax_total;

                        }
                    }
                }
            }
        }
        return array(
            "total_charges" => $total_charges,
            "total_taxes" => $total_taxes
        );
    }
    
    function _get_forecasted_extra_charges($booking)
	{
		$extra_charges = 0;
        
        $company_id = $booking['company_id'];
        $current_selling_date = $this->get_selling_date($company_id);
        
        $booking_id = $booking['booking_id'];
        
		if ($booking['state'] == CANCELLED)
		{
			return array();
		}

		if (($extras = $this->Booking_extra_model->get_booking_extras($booking_id)))
		{
			foreach ($extras as $extra) {
                
                $tax_rates = $this->Tax_model->get_tax_rates_by_charge_type_id($extra['charge_type_id'], $company_id);
				
                $date_start = Date('Y-m-d', max(strtotime($current_selling_date), strtotime($extra['start_date'])));
				$date_end = $extra['end_date'];

                if 	(
                    ($extra['charging_scheme'] == 'on_start_date' && strtotime($current_selling_date) <= strtotime($extra['start_date'])) ||
					($extra['charging_scheme'] == 'once_a_day' && $extra['extra_type'] == 'item' && strtotime($date_start) <= strtotime($date_end)) ||
					($extra['charging_scheme'] == 'once_a_day' && $extra['extra_type'] == 'rental' && strtotime($date_start) < strtotime($date_end))
				)
				{
                    if($extra['charging_scheme'] == 'on_start_date')
                    {
                        $tax_total = 0;
                        if($tax_rates && count($tax_rates) > 0)
                        {
                            foreach($tax_rates as $tax){
                                $tax_total += ($extra['rate'] * $tax['tax_rate'] / 100);
                            }
                        }
                        $extra_charges += ($extra['rate'] + $tax_total) * $extra['quantity'];
                    }
                    else
                    {
                        for ($date = $date_start; $date < $date_end; $date = Date("Y-m-d", strtotime("+1 day", strtotime($date))))
                        {
                            $tax_total = 0;
                            if($tax_rates && count($tax_rates) > 0)
                            {
                                foreach($tax_rates as $tax){
                                    $tax_total += ($extra['rate'] * $tax['tax_rate'] / 100);
                                }
                            }
                            $extra_charges += ($extra['rate'] + $tax_total) * $extra['quantity'];
                        }
                    }
				}
			}
		}

		return $extra_charges;
	}
    
    function delete_bookings($booking_ids_to_be_deleted)
    {
    	$data = Array('is_deleted' => '1');

        $this->db->where_in('booking_id', $booking_ids_to_be_deleted);
		$this->db->update("booking", $data);
        
		//echo $this->db->last_query();
		
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
    }

    function cancel_booking($booking_id, $cancellation_fee = "")
    {
    	$data = Array('state' => CANCELLED);

        if(is_numeric($cancellation_fee)){
            $data["booking_notes"] = "CONCAT('Cancellation fee: ".$cancellation_fee.",\n', booking_notes)";
        }
        
        $this->db->where('booking_id', $booking_id);
		$this->db->update("booking", $data);
		
		//echo $this->db->last_query();
		
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
    }

    function create_booking($data)
    {
		$data = $data;
		
        $this->db->insert("booking", $data);
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		//echo $this->db->last_query();
		//$new_booking_id = $this->db->insert_id();	
        
        $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
		$result = $query->result_array();
        if(isset($result[0]))
        {  
          $new_booking_id = $result[0]['last_id'];
        }
            else
        {  
          $new_booking_id = null;
        }
        
		
		// Generate new invoice_hash for the new booking (for guest to check the invoice later)
		$this->db->query("UPDATE booking SET invoice_hash = '".generate_guid()."' WHERE booking_id = '".$new_booking_id."'");
		
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		//echo $this->db->last_query();
		return $new_booking_id;
    }

    // Called from invoice too
    function get_booking($booking_id)
    {
		$sql="
			SELECT 
				c.customer_name as booking_customer_name,
				c.email as booking_customer_email,
				c.customer_type as booking_customer_type,
				b.state,
				b.is_deleted,
				b.source,
				b.booking_id,
				b.booking_customer_id,
				b.adult_count,
				b.children_count,
				b.rate,
				b.booking_notes,
				b.company_id,
				b.use_rate_plan,
				b.rate_plan_id,
				b.color,
				b.charge_type_id,
				b.housekeeping_notes,
				b.invoice_hash,
				b.guest_review,
                b.balance,
				(
					SELECT 
						MIN(brh1.check_in_date) as check_in_date
					FROM
						booking_block as brh1
					WHERE
						brh1.booking_id = b.booking_id
				) as check_in_date,
                (
					SELECT 
						MAX(brh2.check_out_date) as check_out_date
					FROM
						booking_block as brh2
					WHERE
						brh2.booking_id = b.booking_id
                ) as check_out_date
                
			FROM booking as b
			LEFT JOIN customer as c ON c.customer_id = b.booking_customer_id
			WHERE b.booking_id = '$booking_id'

			";
		
		$query = $this->db->query($sql);	

		//echo $this->db->last_query();

		if ($this->db->_error_message()) 
		{
			show_error($this->db->_error_message());
		}
		
        $q = $query->row_array(0);
		
        return $q;
    }

    function get_booking_staying_customers($booking_id, $company_id)
	{
		$this->db->select('booking_staying_customer_list.customer_id');
		$this->db->select('customer_name');
		$this->db->from('booking_staying_customer_list');
		$this->db->join('customer', 'customer.customer_id = booking_staying_customer_list.customer_id', 'left');
		$this->db->where('booking_staying_customer_list.booking_id', $booking_id);
		$this->db->where('booking_staying_customer_list.company_id', $company_id);
		$results = $this->db->get();
		
		if (empty($results))
		{
			return array();
		}
		else
		{
			return $results->result_array();
		}
	}
	
    function get_bookings_by_room_id($filters, $company_id = null)
    {   
   		$sql = "
                SELECT 
                    b.*,
                    brh.*,
                    r.room_id,
                    c.customer_name
                FROM    
                    booking as b,
                    booking_block as brh,
                    room as r,
                    customer as c 
                WHERE 
                    b.booking_id = brh.booking_id AND
                    brh.room_id = r.room_id  AND 
                    r.room_id = '".$filters['room_id']."' AND
                    b.company_id =  '".$company_id."'  AND 
                    b.is_deleted != '1' AND
                    brh.check_out_date >= '".$filters['start_date']."' AND 
                    brh.check_in_date <= '".$filters['end_date']."'  AND 
                    b.state != 7 AND
                    b.booking_customer_id = c.customer_id
                order BY 
                    brh.check_in_date ASC
            ";
        $q = $this->db->query($sql);	

        if ($this->db->_error_message()) 
		{
			show_error($this->db->_error_message());
		}
		$result = $q->result_array();		
		return $result;
    }
    
    function update_booking($data, $booking_id = null)
    {
		$data = $data;
		
        if($booking_id)
            $this->db->where('booking_id', $booking_id);
        else
            $this->db->where('booking_id', $data['booking_id']);
        $this->db->update("booking", $data);
		
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}	
    }
    
    function update_booking_balance($booking_id) 
    {		
    	$sql = "SELECT *,
                    IFNULL(
                    (
                        SELECT
                            ROUND(SUM((ch.amount * (1+IFNULL(percentage_total * 0.01, 0))) + IFNULL(flat_tax_total, 0)), 2) as charge_total
                        FROM
                            charge as ch
                        LEFT JOIN ( 
                            SELECT 
                                ct.name, 
                                ct.id, 
                                sum(IF(tt.is_percentage = 1, tax_rate, 0)) as percentage_total, 
                                sum(IF(tt.is_percentage = 0, tax_rate, 0)) as flat_tax_total
                            FROM charge_type_tax_list AS cttl, charge_type as ct, tax_type AS tt
                            WHERE 
                                ct.id = cttl.charge_type_id AND
                                tt.tax_type_id = cttl.tax_type_id AND 
                                tt.is_deleted = '0' AND
                                ct.is_deleted = '0'
                            GROUP BY ct.id
                        )t ON ch.charge_type_id = t.id
                        WHERE
                            ch.is_deleted = '0' AND
                            ch.booking_id = b.booking_id 
                        GROUP BY ch.booking_id
                    ), 0	
                ) as charge_total,
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
                ) as payment_total
            FROM booking as b
            LEFT JOIN booking_block as brh ON b.booking_id = brh.booking_id
            WHERE b.booking_id = '$booking_id'
        ";
        
        $query = $this->db->query($sql);
        $result = $query->result_array();
        $booking = null;
        if ($query->num_rows >= 1 && isset($result[0]))
		{
			$booking =  $result[0];
		}
        
        if($booking)
        {
            $forecast = $this->forecast_charges->_get_forecast_charges($booking_id, true);
            $forecast_extra = $this->forecast_charges->_get_forecast_extra_charges($booking_id, true);
            $booking_charge_total_with_forecast = (floatval($booking['charge_total']) + floatval($forecast['total_charges']) + floatval($forecast_extra));
            $data = array(
                'booking_id' => $booking_id,
                'balance' => round(floatval($booking_charge_total_with_forecast) - floatval($booking['payment_total']), 2),
                'balance_without_forecast' => round(floatval($booking['charge_total']) - floatval($booking['payment_total']), 2)
            );
            $this->update_booking($data);
            return $data['balance'];
        }
        return null;
    }
   //booking source querry
    function get_booking_source_detail($company_id)
    {
        
        $this->db->where('company_id', $company_id);
        
        $this->db->from('booking_source');
        $result = $this->db->get()->result_array();
        return $result;
    }
    function create_booking_source($company_id, $name, $is_hidden = NULL, $id = NULL, $sort_order = 0)
	{	
        if (!$is_hidden) 
        {
            $data = array(
                'name' => $name,
                'company_id' => $company_id,
                'sort_order' => $sort_order
            );
        }
		else
        {
            $data = array(
                'name' => $name,
                'company_id' => $company_id,
                'is_hidden' => $is_hidden,
                'id' => $id,
                'sort_order' => $sort_order
            );
        }
		
		$this->db->insert('booking_source', $data);		
		//echo $this->db->last_query();
		return $this->db->insert_id();
	} 

    function get_company_api_permission($company_id, $key)
    {
        $sql = "SELECT 
                    k.*, kxc.*
                FROM 
                    `key` as k
                LEFT JOIN 
                    key_x_company as kxc ON kxc.key_id = k.id
                WHERE 
                    k.key = '$key' AND
                    kxc.company_id = '$company_id' ";
        
        $query = $this->db->query($sql);
        $result = $query->result_array();
        
        return count($result) > 0 ? $result : NULL;
    }

    function get_bookings($filters, $company_id = 0, $group_by1 = NULL, $include_room_no_assigned = false, $include_customer_all_details = false)
    {

        $sql = $this->_build_get_bookings_sql_query_based_on_filters($filters, $company_id, $group_by1, $include_room_no_assigned, $include_customer_all_details);
        $q = $this->db->query($sql);    

        if ($this->db->_error_message()) 
        {
            show_error($this->db->_error_message());
        }
        

        $result = $q->result_array();       
        
        return $result;
    }

    function _build_get_bookings_sql_query_based_on_filters($filters, $company_id, $group_by1, $include_room_no_assigned = false, $include_customer_all_details = false) 
    {
        $filters['is_ota_booking'] = false;

        if(
            isset($filters['ota_booking_id']) &&
            $filters['ota_booking_id']
        ) {
            $ota_booking_data = $this->_get_ota_booking_data($filters['ota_booking_id']);
        }

        if($ota_booking_data){
            $filters['is_ota_booking'] = true;
        }

        $booking_fields_join = $customer_fields_join = '';
        $booking_fields_where_condition = $customer_fields_where_condition = '';

        if(isset($filters['search_query']) && $filters['search_query'])
        {
            $search_query = $filters['search_query'];

            $booking_fields_join = ' LEFT JOIN booking_x_booking_field AS bxbf ON bxbf.booking_id = b.booking_id';
            $booking_fields_where_condition = " OR bxbf.value like '%$search_query%'";
            

            $customer_fields_join = ' LEFT JOIN customer_x_customer_field AS pcxcf ON pcxcf.customer_id = c.customer_id
                                      LEFT JOIN customer_x_customer_field AS scxcf ON scxcf.customer_id = sg.customer_id';
            $customer_fields_where_condition = " OR pcxcf.value like '%$search_query%' OR scxcf.value like '%$search_query%'";
        }

        $where_conditions = $this->_get_where_conditions($filters, $company_id, $include_room_no_assigned, $booking_fields_where_condition, $customer_fields_where_condition);
                
        $where_condition_for_balance = "";
        $where_statement_date = '';
        if(isset($filters['statement_date']) && $filters['statement_date']){
            $where_statement_date = " AND ch.selling_date < '".$filters['statement_date']."' ";
        }
        
        $where_booking_ids = "";
        if(isset($filters['booking_ids']) && $filters['booking_ids']){
            $booking_ids = implode(',', $filters['booking_ids']);
            $where_booking_ids = " AND b.booking_id IN ($booking_ids) ";
        }
        
        if (isset($filters['show_paid'])) 
        {
            if($filters['show_paid'] === "paid_only") 
            {
                $where_condition_for_balance = 'WHERE balance <= 0';
            } 
            elseif ($filters['show_paid'] === "unpaid_only") 
            {
                $where_condition_for_balance = 'WHERE balance > 0';
            }
        }
        

        $order_by = "ORDER BY room_name";
        
        //if both $start and $end are set, then return occupancies within range
        if (isset($filters['order_by'])) 
        {
            if ($filters['order_by'] != "") 
            {
                $order_by = "ORDER BY ".$filters['order_by'];
            }
        }
                
        $group_by = "GROUP BY booking_id";
                if($group_by1){
                    $group_by = $group_by . ' , '.$group_by1;
                }
        if (isset($filters['group_by'])) 
        {
            if ($filters['group_by'] == 'booking_room_history_id')
            {
                $group_by = 'GROUP BY booking_room_history_id';
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
        
        $select_statements = $select_group_concat_statements = $join_statements = $where_statements = "";
        if (isset($filters['with_booking_statements']) && $filters['with_booking_statements']) 
        {
            $select_statements = ", x.booking_statements";
            $select_group_concat_statements = ", GROUP_CONCAT(DISTINCT CONCAT( s.statement_id, '_',  s.statement_number)) as booking_statements";
            $join_statements = "LEFT JOIN booking_x_statement AS bs ON bs.booking_id = b.booking_id 
                                LEFT JOIN statements AS s ON bs.statement_id = s.statement_id AND s.is_deleted = 0 ";
        
            if (isset($filters['in_statement']) && $filters['in_statement']) 
            {
                $where_statements .= ' WHERE x.booking_statements IS NOT NULL ';
            }
            elseif (isset($filters['in_statement']) && !$filters['in_statement']) 
            {
                $where_statements .= ' WHERE x.booking_statements IS NULL ';
            }
        }
        
        $include_charge_payment_total = ", IFNULL(
                        (
                            IFNULL(
                                (
                                    SELECT SUM(p.amount) as payment_total
                                    FROM payment as p, payment_type as pt
                                    WHERE
                                        p.is_deleted = '0' AND
                                        pt.is_deleted = '0' AND
                                        p.payment_type_id = pt.payment_type_id AND
                                        p.booking_id = x.booking_id

                                    GROUP BY p.booking_id
                                ), 0
                            ) + x.balance_without_forecast
                        ), 0    
                    ) as charge_total,
                    IFNULL(
                        (
                            SELECT SUM(p.amount) as payment_total
                            FROM payment as p, payment_type as pt
                            WHERE
                                p.is_deleted = '0' AND
                                pt.is_deleted = '0' AND
                                p.payment_type_id = pt.payment_type_id AND
                                p.booking_id = x.booking_id
                                
                            GROUP BY p.booking_id
                        ), 0
                    ) as payment_total";
        
        if (isset($filters['not_include_charge_payment_total']) && $filters['not_include_charge_payment_total']) 
        {
            $include_charge_payment_total = "";
        }
        
        $external_select_customer_type = $select_customer_type = $join_customer_type = "";
        if (isset($filters['include_customer_type']) && $filters['include_customer_type']) 
        {
            $external_select_customer_type = "x.customer_type_name, 
                                            x.adult_count, 
                                            x.children_count, 
                                            x.value,
                                            x.custom_field_value,
                                            x.staying_customer_id,";
            
            $select_customer_type = "ct.name as customer_type_name, 
                                    b.adult_count,
                                    b.children_count,
                                    cxcf.value,
                                    GROUP_CONCAT(DISTINCT cxcf2.value, ' ') as custom_field_value,
                                    GROUP_CONCAT(DISTINCT sg.customer_id, ' ') as staying_customer_id,";
            
            $join_customer_type = "LEFT JOIN customer_types as ct ON ct.id = c.customer_type_id
                    LEFT JOIN customer_x_customer_field as cxcf ON cxcf.customer_id = b.booking_customer_id AND b.booking_customer_id != '0'
                    LEFT JOIN customer_fields as cf ON cxcf.customer_field_id = cf.id
                    LEFT JOIN customer_x_customer_field as cxcf2 ON cxcf2.customer_field_id = cf.id AND bscl.customer_id = sg.customer_id AND cxcf2.customer_id = sg.customer_id
                    ";
            
        }
        
        $external_select_customer_details = $select_customer_details = "";
        if($include_customer_all_details)
        {
            $external_select_customer_details = "x.email,
                                                x.phone,
                                                x.phone2,
                                                x.fax,
                                                x.address,
                                                x.address2,
                                                x.city,
                                                x.region,
                                                x.country,
                                                x.postal_code,";

            $select_customer_details = "c.email,
                                        c.phone,
                                        c.phone2,
                                        c.fax,
                                        c.address,
                                        c.address2,
                                        c.city,
                                        c.region,
                                        c.country,
                                        c.postal_code,";
        }

        $select_room_name_combine = " GROUP_CONCAT(room_name) ";
        if (isset($filters['fetch_checkindate_with_room_name']) && $filters['fetch_checkindate_with_room_name'])
        {
            $select_room_name_combine = " GROUP_CONCAT(CONCAT(room_name, '|$|', check_in_date)) ";
        }

        // SQL_CALC_FOUND_ROWS is used to calculate # of rows ignoring LIMIT. SELECT FOUND_ROWS() Must be called right after this query.
        // Which is called from get_found_rows() function.
        $sql = "
            SELECT SQL_CALC_FOUND_ROWS 
                *
            FROM
                (SELECT
                    x.booking_id,
                    x.customer_type_id,
                    $external_select_customer_type
                    x.booking_customer_id,
                    x.group_id,
                    x.room_id, 
                    x.r_room_type_id,
                    x.rate_plan_id,
                    x.check_in_date,
                    x.check_out_date,
                    x.room_name,
                    x.groups_id,
                    x.is_group_booking,
                    x.group_name,
                    x.state,
                    x.color,
                    x.rate,
                    x.balance,
                    x.balance_without_forecast,
                    x.pay_period,
                    x.charge_type_id,
                    x.use_rate_plan,
                    x.booking_notes,
                    $external_select_customer_details
                    x.customer_notes,
                    x.source,
                    IF(x.customer_name IS NULL, '', x.customer_name) as customer_name,
                    x.guest_count,
                    x.staying_customers,
                    x.guest_name,
                    x.brh_room_type_id
                    $include_charge_payment_total
                    $select_statements
                FROM
                (
                    SELECT 
                        b.booking_id,
                        b.is_ota_booking,
                        b.rate_plan_id,
                        up.first_name,
                        up.last_name,
                        bxs.group_id AS groups_id,
                        blg.id AS is_group_booking,
                        blg.name AS group_name,
                        brh.room_id,
                        brh.room_type_id as brh_room_type_id,
                        r.room_type_id as r_room_type_id,
                        (
                            /* Room name combine */
                            SELECT $select_room_name_combine FROM room
                            LEFT JOIN booking_block on room.room_id = booking_block.room_id
                            WHERE booking_id = b.booking_id
                            ORDER BY booking_block.check_in_date DESC
                        ) as room_name,
                        
                        min(brh.check_in_date) as check_in_date,
                        max(brh.check_out_date) as check_out_date,
                        (
                            SELECT r.group_id 
                            FROM room as r 
                            JOIN room_type as rt ON r.group_id=rt.id 
                            WHERE rt.acronym='SN' 
                            GROUP BY r.group_id 
                            order by r.group_id DESC
                            LIMIT 0,1
                        ) as group_id,
                        b.state,
                        b.color,
                        b.rate,
                        b.balance,
                        b.balance_without_forecast,
                        IF(b.source > 20, (SELECT bs.name FROM booking_source as bs WHERE bs.id = b.source LIMIT 1), b.source) as source,
                        b.pay_period,
                        b.charge_type_id,
                        b.use_rate_plan,
                        b.booking_notes,
                        b.booking_customer_id,
                        c.customer_type_id,
                        $select_customer_type
                        c.customer_name,
                        $select_customer_details
                        c.customer_notes,
                        count(DISTINCT sg.customer_id) as guest_count,
                        GROUP_CONCAT(DISTINCT sg.customer_name, ' ') as staying_customers,
                        (   SELECT c2.customer_name
                                FROM customer as c2, booking_staying_customer_list as bscl2
                                WHERE 
                                        c2.customer_id = bscl2.customer_id AND 
                                        bscl2.booking_id = b.booking_id
                                LIMIT 0,1
                        ) as guest_name
                        
                        $select_group_concat_statements
                    FROM booking as b
                    $booking_fields_join
                    LEFT JOIN booking_log as bl ON bl.booking_id = b.booking_id
                    LEFT JOIN user_profiles as up ON up.user_id = bl.user_id
                    LEFT JOIN booking_staying_customer_list as bscl ON bscl.booking_id = b.booking_id
                    LEFT JOIN customer as sg ON bscl.customer_id = sg.customer_id 
                    LEFT JOIN booking_block as brh ON brh.booking_id = b.booking_id AND brh.check_out_date = check_out_date
                    LEFT JOIN room as r ON brh.room_id = r.room_id 
                    LEFT JOIN customer as c ON c.customer_id = b.booking_customer_id AND b.booking_customer_id != '0'
                    $customer_fields_join
                    $join_customer_type
                    LEFT JOIN booking_x_group AS bxs ON bxs.booking_id = brh.booking_id 
                    LEFT JOIN booking_x_booking_linked_group AS bxblg ON bxblg.booking_id = brh.booking_id 
                    LEFT JOIN booking_linked_group AS blg ON blg.id = bxblg.booking_group_id
                    LEFT JOIN ota_bookings as ob ON ob.pms_booking_id = b.booking_id
                    $join_statements
                    WHERE
                        $where_conditions
                        $where_booking_ids
                    $group_by

                )x
                $where_statements
            )y

            $where_condition_for_balance

            $order_by $order 
            
            $limit
        ";

        return $sql;
    }

    function _get_where_conditions($filters, $company_id, $include_room_no_assigned = false, $booking_fields_where_condition = "", $customer_fields_where_condition = "")
    {
        //Generate booking type portion of the SQL statement
        if (isset($filters['search_query']) && !isset($filters['state']) && !isset($filters['start_date']) && !isset($filters['end_date']))
        {
            $search_query = $filters['search_query'];
            $where_conditions = "
                c.customer_name like '%$search_query%' OR 
                sg.customer_name like '%$search_query%' OR 
                b.booking_notes like '%$search_query%' OR 
                b.booking_id like '%$search_query%' OR 
                c.phone like '%$search_query%' OR
                c.email like '%$search_query%' 
                $booking_fields_where_condition
                $customer_fields_where_condition
                ";
            return $where_conditions;
        }
        
        $state_sql = "";
        if (isset($filters['state'])) 
        {
            // SKIP THE CONDITION if filter applied for cancellation state
            if ($filters['state'] == 'active')  // Show all reservation, inhouse, and checkout guests. Called from calendar
            {
                if (!(isset($filters['reservation_type'])
                    && ($filters['reservation_type'] == CANCELLED || $filters['reservation_type'] == -1))) {
                    $state_sql = "AND (b.state < 4 OR b.state = '" . UNCONFIRMED_RESERVATION . "' OR b.state = '" . NO_SHOW . "')";
                }
            } 
            elseif ($filters['state'] == 'all' || $filters['state'] == '') 
            {
                $state_sql = "";
            } 
            else 
            {
                $state_sql = "AND b.state = '".$filters['state']."'";
            }
        }
        
        
                
        //Generate time portion of the SQL statement
        $time_sql = "";
        if(isset($filters['show_only_checked_in_and_checked_out']) && $filters['show_only_checked_in_and_checked_out']){
            if($filters['state'] == INHOUSE)
            {
                $time_sql = "AND (DATE(check_in_date) >= '".$filters['start_date']."' AND DATE(check_in_date) <= '".$filters['end_date']."')";
            }
            elseif ($filters['state'] == CHECKOUT)
            {
                $time_sql = "AND (DATE(check_out_date) >= '".$filters['start_date']."' AND DATE(check_out_date) <= '".$filters['end_date']."')";
            }
        }
        else
        {
            if(isset($filters['filter_booking_list']))
            {
                $start_date = isset($filters['filter_booking_list']['start_date']) ? $filters['filter_booking_list']['start_date'] : "";
                $end_date = isset($filters['filter_booking_list']['end_date']) ? $filters['filter_booking_list']['end_date'] : "";
                $date_overlap = isset($filters['filter_booking_list']['date_overlap']) ? $filters['filter_booking_list']['date_overlap'] : "";
                
                $time_sql = " AND ((DATE(check_in_date) = '".$start_date."' AND ".RESERVATION." = b.state) OR "
                                . " (DATE(check_out_date) = '".$end_date."' AND ".CHECKOUT." = b.state) OR "
                                . " (".UNCONFIRMED_RESERVATION." = b.state) OR "
                                . " (".INHOUSE." = b.state) OR "
                                . " (DATE(check_out_date) > '".$date_overlap."' AND DATE(check_in_date) <= '".$date_overlap."' AND ".OUT_OF_ORDER." = b.state))";
            }
            else
            {
                // if both start_date and end_date are set, then return occupancies within range
                if (isset($filters['start_date']) && isset($filters['end_date']))
                { 
                    if ($filters['start_date'] != "" && $filters['end_date'] != "" && isset($filters['unassigned_bookings']) && $filters['unassigned_bookings'])
                    {
                        $time_sql = "AND (DATE(check_out_date) > '".$filters['start_date']."' AND '".$filters['end_date']."' > DATE(check_in_date))";
                    }
                    else if ($filters['start_date'] != "" && $filters['end_date'] != "") 
                    {
                        if(isset($filters['booking_status']) && $filters['booking_status'] == 'checking_in')
                        {
                            $time_sql = "AND (DATE(check_in_date) >= '".$filters['start_date']."' AND '".$filters['end_date']."' >= DATE(check_in_date))";
                        }
                        else if(isset($filters['booking_status']) && $filters['booking_status'] == 'checking_out')
                        {
                            $time_sql = "AND (DATE(check_out_date) >= '".$filters['start_date']."' AND '".$filters['end_date']."' >= DATE(check_out_date))";
                        }
                        else
                        {
                            $time_sql = "AND (DATE(check_out_date) >= '".$filters['start_date']."' AND '".$filters['end_date']."' >= DATE(check_in_date))";
                        }
                    }
                // if only start_date is set, then return occupancies that starts on start_date
                }
                elseif (isset($filters['start_date']) && !isset($filters['end_date'])) 
                { 
                    $time_sql = "AND DATE(check_in_date) = '".$filters['start_date']."'";
                // if only end_date is set, then return occupancies that ends on end_date
                } 
                elseif (!isset($filters['start_date']) && isset($filters['end_date'])) 
                { 
                    $time_sql = "AND DATE(check_out_date) = '".$filters['end_date']."'";
                }
            }
        }
        
        $date_overlap_sql = "";
        if (isset($filters['date_overlap']))
        { 
            $date_overlap_sql = "AND (DATE(check_in_date) <= '".$filters['date_overlap']."' AND DATE(check_out_date) > '".$filters['date_overlap']."')";
        }

        // matching customer_id
        $booking_customer_sql = "";
        if (isset($filters['booking_customer_id']))
        {
            $booking_customer_sql = "AND b.booking_customer_id = '".$filters['booking_customer_id']."'";
        }

        // matching booking_id or ota_booking_id
        $booking_sql = "";

        if($filters['is_ota_booking']){
            $booking_sql = "AND ob.ota_booking_id = '".$filters['ota_booking_id']."'";
        } else if($filters['booking_id']) {
            $booking_sql = "AND b.booking_id = '".$filters['booking_id']."'";
        }

        // if(
        //     isset($filters['booking_id']) && 
        //     $filters['booking_id'] && 
        //     isset($filters['ota_booking_id']) &&
        //     $filters['ota_booking_id']
        // ) {
        //     $booking_sql = "AND (b.booking_id = '".$filters['booking_id']."' OR ob.ota_booking_id = '".$filters['ota_booking_id']."')";
        // }
        // else if (
        //     isset($filters['booking_id']) && 
        //     $filters['booking_id'] && 
        //     !isset($filters['ota_booking_id'])
        // ) {
        //     $booking_sql = "AND b.booking_id = '".$filters['booking_id']."'";
        // } 
        // else if(
        //     !isset($filters['booking_id']) && 
        //     isset($filters['ota_booking_id']) &&
        //     $filters['ota_booking_id']
        // ) {
        //     $booking_sql = "AND ob.ota_booking_id = '".$filters['ota_booking_id']."'";
        // } 
        
        // matching customer_id
        $staying_customer_sql = $staying_customer_name = "";
        if (isset($filters['staying_customer_id']))
        {
            $staying_customer_sql = "AND 
                                        b.booking_id = bscl.booking_id AND
                                        bscl.customer_id = '".$filters['staying_customer_id']."'";
        }
        if(isset($filters['staying_guest_name']) && $filters['staying_guest_name'])
        {
            $staying_customer_name = "AND
                                       sg.customer_name like '%".$filters['staying_guest_name']."%' ";
        }
        // set search query
        $search_sql = "";
        if (isset($filters['search_query'])) 
        {
            $search_query = $filters['search_query'];
            $search_sql = "AND (
                c.customer_name like '%$search_query%' OR 
                sg.customer_name like '%$search_query%' OR 
                b.booking_notes like '%$search_query%' OR 
                b.booking_id like '%$search_query%' OR 
                c.phone like '%$search_query%' OR
                c.email like '%$search_query%' 
                $booking_fields_where_condition
                $customer_fields_where_condition
                )";     
        }
        
        $paid_sql = "";
        $where_conditions = "
            b.company_id = '$company_id' AND
            b.is_deleted != '1' AND 
            ".($include_room_no_assigned ? "IF(brh.room_id, r.is_deleted, '0')" : "r.is_deleted")." != '1'
            $time_sql
            $state_sql 
            $booking_customer_sql
            $booking_sql
            $staying_customer_sql
            $staying_customer_name
            $search_sql
            $paid_sql
            $date_overlap_sql
        ";
        
        if (isset($filters['location_id']) && $filters['location_id']) 
        {
            $where_conditions .= ' AND r.location_id="'.$filters['location_id'].'"';
        }
        if (isset($filters['floor_id']) && $filters['floor_id']) 
        {
            $where_conditions .= ' AND r.floor_id="'.$filters['floor_id'].'"';
        }  
        if (isset($filters['room_type_id']) && $filters['room_type_id']) 
        {
            $where_conditions .= ' AND r.room_type_id="'.$filters['room_type_id'].'"';
        }
        if (isset($filters['group_id']) && $filters['group_id']) 
        {
            $where_conditions .= ' AND r.group_id="'.$filters['group_id'].'"';
        }    
        if (isset($filters['reservation_type']) && $filters['reservation_type'] != '' && $filters['reservation_type'] != -1)
        {
            $where_conditions .= ' AND b.state="'.$filters['reservation_type'].'"';
        }         
        if (isset($filters['booking_source']) && $filters['booking_source'] != '') 
        {
            $where_conditions .= ' AND b.source="'.$filters['booking_source'].'"';
        } 
        
        if(isset($filters['group']) && $filters['group']){
            if($filters['group'] == 'all'){
                $where_conditions .= " ";
            }elseif($filters['group'] == 'unassigned'){
                $where_conditions .= " AND (bxs.group_id IS NULL OR bxs.group_id = 0) ";
            }
            else {
                $where_conditions .= " AND bxs.group_id = '".$filters['group']."' ";
            }
            
        }
        if (isset($filters['statement_date']) && $filters['statement_date'] != '') 
        {
            $where_conditions .= ' AND DATE(check_in_date) <= "'.$filters['statement_date'].'"';
        }
        if (isset($filters['group_ids']) && $filters['group_ids'])
        {
            $group_ids = trim(trim($filters['group_ids'], ","), " ");
            $where_conditions .= " AND blg.id IN ($group_ids) ";
        }
        return $where_conditions;
    }

    function _get_ota_booking_data($ota_booking_id){
        $sql = "SELECT 
                    IF(ob.ota_booking_id IS NOT NULL, ob.ota_booking_id, NULL) as ota_booking_id
                FROM
                    ota_bookings as ob 
                WHERE 
                    ob.ota_booking_id = '$ota_booking_id'";

        $q = $this->db->query($sql);

        if ($this->db->_error_message()) 
        {
            show_error($this->db->_error_message());
        }

        $result = $q->row_array();       
        
        return $result;
    }

    function get_recent_bookings($company_id) {
        
        $sql = "SELECT 
                    bb.booking_id,
                    bb.check_in_date as arrival_date,
                    bb.check_out_date as departure_date,
                    bc.customer_name,
                    bc.email,
                    IF(bs.name IS NOT NULL, bs.name, IF(cbss.booking_source_id = 0, 'Walk-in / Telephone', cbss.booking_source_id)) as booking_source,
                    IF(ob.ota_booking_id IS NOT NULL, ob.ota_booking_id, NULL) as ota_booking_id,
                    b.rate,
                    bl.date_time as booking_date,
                    rt.name as room_type,
                    rp.rate_plan_name as rate_plan,
                    IF(ct.name IS NOT NULL, ct.name, ccts.customer_type_id)as customer_type
                FROM 
                    booking as b 
                LEFT JOIN 
                    booking_block as bb ON bb.booking_id = b.booking_id
                LEFT JOIN 
                    booking_log as bl ON bl.booking_id = b.booking_id
                LEFT JOIN 
                    customer as bc ON b.booking_customer_id = bc.customer_id
                LEFT JOIN 
                    booking_source as bs ON bs.id = b.source
                LEFT JOIN 
                    common_booking_source_setting as cbss ON cbss.booking_source_id = b.source
                LEFT JOIN 
                    room_type as rt ON rt.id = bb.room_type_id
                LEFT JOIN 
                    rate_plan as rp ON rp.rate_plan_id = b.rate_plan_id
                LEFT JOIN 
                    customer_type as ct ON ct.id = bc.customer_type_id
                LEFT JOIN 
                    common_customer_type_setting as ccts ON ccts.customer_type_id = bc.customer_type_id
                LEFT JOIN 
                    company as c ON c.company_id = b.company_id
                LEFT JOIN 
                    ota_bookings as ob ON ob.pms_booking_id = b.booking_id
                WHERE 
                    (bl.date_time) >= (NOW() - INTERVAL 15 MINUTE) AND
                    c.company_id = $company_id
                GROUP BY 
                    b.booking_id
                ORDER BY 
                    b.booking_id desc
                ";

        $q = $this->db->query($sql);

        if ($this->db->_error_message()) 
        {
            show_error($this->db->_error_message());
        }
        

        $result = $q->result_array();       
        
        return $result;
    }
}
