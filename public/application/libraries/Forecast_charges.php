<?php
class Forecast_charges
{
    
    public function __construct()
    {
        $this->ci =& get_instance();
        
		$this->ci->load->model('Company_model');				
		$this->ci->load->model('Rate_plan_model');
		$this->ci->load->model('Charge_model');
		$this->ci->load->model('Charge_type_model');
		$this->ci->load->model('Booking_model');
		$this->ci->load->model('Booking_extra_model');
        $this->ci->load->model('Tax_model');
        $this->ci->load->model('Channex_model');
        
        $this->ci->load->library('rate');
    }
    
    /* 
     * @var $get_amount_only get forecast charge/tax  in amount if true or in array if false
     */    
    function _get_forecast_charges($booking_id, $get_amount_only = false, $booking_details = false)
    {
        $booking_details = $booking_details ? $booking_details : $this->ci->Booking_model->get_booking_detail($booking_id);
        $company_details = isset($this->ci->company_data) && $this->ci->company_data ? $this->ci->company_data : $this->ci->Company_model->get_company($booking_details['company_id']);

        if (!$booking_details || $booking_details['state'] == CANCELLED || $booking_details['state'] == CHECKOUT || $booking_details['state'] == NO_SHOW)
		{
			if($get_amount_only) 
            {
                $response = array(
                        "total_charges" => 0,
                        "total_taxes" => array()
                    );
            }
            else
            {
                $response = array();
            }
            return $response;
		}
        $total_charges = 0;
        $total_taxes = array();
        $days = 1;
        $date_increment = "+1 day";
        $date_decrement = "-1 day";
        $description = "Daily ".$company_details['default_charge_name'];
        if($booking_details['pay_period'] == WEEKLY)
        {
            $days = 7;
            $date_increment = "+7 days";
            $date_decrement = "-7 days";
            $description = "Weekly ".$company_details['default_charge_name'];
        }
        elseif($booking_details['pay_period'] == MONTHLY)
        {
            $days = 30;
            $date_increment = "+1 month";
            $date_decrement = "-1 month";
            $description = "Monthly ".$company_details['default_charge_name'];
        }
        $company_id = $booking_details['company_id'];
        $room_name = $booking_details['room_name'];
        $room_type_name = $booking_details['room_type_name'];
        $customer_name = $booking_details['booking_customer_name'];
        
		//Load data used in calculations
		$current_selling_date = $this->ci->Company_model->get_selling_date($company_id);
		$check_in_date = date('Y-m-d', strtotime($booking_details['check_in_date']));
		$check_out_date = date('Y-m-d', strtotime($booking_details['check_out_date']));
        
        //$room_charge_type_id = $this->ci->Charge_type_model->get_default_room_charge_type($company_id)['id'];
        $room_charge_type_id = $this->ci->Charge_type_model->get_room_charge_type_id($booking_id);

        if ($company_details['allow_free_bookings'] && (!$room_charge_type_id || $room_charge_type_id == '0')) {
            if($get_amount_only)
            {
                $response = array(
                    "total_charges" => 0,
                    "total_taxes" => array()
                );
            }
            else
            {
                $response = array();
            }
            return $response;
        }
        
        $last_room_charge = $this->ci->Charge_model->get_last_applied_charge($booking_id, $room_charge_type_id, null, true);
        
        // echo "$booking_id, $room_charge_type_id last_room_charge";
        // print_r($last_room_charge);
        $next_room_charge_date = null;
        
        if($last_room_charge)
        {
            $next_room_charge_date = date('Y-m-d', strtotime($last_room_charge['selling_date'].$date_increment));
            if($last_room_charge['pay_period'] != $booking_details['pay_period'] && $last_room_charge['pay_period'] == DAILY)
            {
                $next_room_charge_date = date('Y-m-d', strtotime($last_room_charge['selling_date']."+1 day"));
            }
            elseif($last_room_charge['pay_period'] != $booking_details['pay_period'] && $last_room_charge['pay_period'] == WEEKLY)
            {
                $next_room_charge_date = date('Y-m-d', strtotime($last_room_charge['selling_date']."+7 day"));
            }
            elseif($last_room_charge['pay_period'] != $booking_details['pay_period'] && $last_room_charge['pay_period'] == MONTHLY)
            {
                $next_room_charge_date = date('Y-m-d', strtotime($last_room_charge['selling_date']."+1 month"));
            }
        }
        // echo "max (".$next_room_charge_date .", ". $last_room_charge['selling_date'].", ".$booking_details['check_in_date'].")";
		$date_start = max($next_room_charge_date, $current_selling_date, $check_in_date);
		$date_end = $check_out_date;
        
        
        if($booking_details['pay_period'] == ONE_TIME)
        {
            if($last_room_charge || $check_out_date < $current_selling_date){
                if($get_amount_only) 
                {
                    $response = array(
                            "total_charges" => 0,
                            "total_taxes" => array()
                        );
                }
                else
                {
                    $response = array();
                }
                return $response;
            }
            $date_start = $check_in_date;
        }
        
        // if booking is using rate_plan, return the array of dynamically changing rates.
        $rate_array = array();
        
        // only return forecasted rates for current/future bookings and same-day check-out bookings
        if ($current_selling_date < $check_out_date || $check_in_date == $check_out_date)
		{
			if ($booking_details['use_rate_plan']) 
            {
                if (!($date_start < $date_end || $check_in_date == $check_out_date))
                {
                    //forecast charges dates must be less than checkout date.
                    if($get_amount_only) 
                    {
                        $response = array(
                                "total_charges" => 0,
                                "total_taxes" => array()
                            );
                    }
                    else
                    {
                        $response = array();
                    }
                    return $response;
                }

                $rate_plan   = $this->ci->Rate_plan_model->get_rate_plan($booking_details['rate_plan_id']);
                
                if(
                    isset($booking_details['is_ota_booking']) && 
                    $booking_details['is_ota_booking']
                ) {

                    $oxc_data = $this->ci->Channex_model->get_channex_extra_charges($booking_details['company_id']);

                    if(
                        isset($oxc_data['is_extra_charge']) &&
                        $oxc_data['is_extra_charge']
                    ) {
                        $rate_plan['charge_type_id'] = SYSTEM_ROOM_NO_TAX;
                    }
                }

                $charge_type = $this->ci->Charge_type_model->get_charge_type_by_id($rate_plan['charge_type_id']);

				$rate_plan_id = $booking_details['rate_plan_id'];
				$adult_count = $booking_details['adult_count'];
				$children_count = $booking_details['children_count'];
				
				$rate_array = $this->ci->rate->get_rate_array($rate_plan_id, $date_start, $date_end, $adult_count, $children_count);
				
				$last_tax_rate_amount = $tax_rates = null;
				// change key names (i.e. date => selling_date, rate => amount
				foreach ($rate_array as $index => $rate)
				{
					$amount = $rate['rate'];
					if(!($last_tax_rate_amount && $last_tax_rate_amount == $amount)) {
						$tax_rates = $this->ci->Charge_type_model->get_taxes($rate_plan['charge_type_id'], $amount);
						$last_tax_rate_amount = $amount;
					}
					
					$rate_array[$index]['selling_date'] = $rate_array[$index]['date'];
					unset($rate_array[$index]['date']);
					$rate_array[$index]['amount'] = $rate_array[$index]['rate'];
					unset($rate_array[$index]['rate']);
					$rate_array[$index]['description'] = $rate_plan['rate_plan_name'];
					$rate_array[$index]['charge_type_id'] = $charge_type['id'];
					$rate_array[$index]['charge_type_name'] = $charge_type['name'];
                    $rate_array[$index]['pay_period'] = DAILY; // temporary fix as all rate plans are daily only for now. eventually this may allow weekly & monthly,
                    $rate_array[$index]['taxes'] = $tax_rates;
                    unset($rate_array[$index]['rate_plan_name']);
					unset($rate_array[$index]['is_deleted']); // you can't delete forecasted charges, but this variable is set from DB
                    $rate_array[$index]['booking_id'] = $booking_id;
                    $rate_array[$index]['room_name'] =  $room_name ;
                    $rate_array[$index]['customer_name'] = $customer_name;
                    $rate_array[$index]['room_type_name'] = $room_type_name;
					
                    if($get_amount_only)
                    {
                        $tax_total = 0;
                        if($tax_rates && count($tax_rates) > 0)
                        {
                            foreach($tax_rates as $tax){
                                if(!isset($total_taxes[$tax['tax_type']]))
                                    $total_taxes[$tax['tax_type']] = 0;
                                $total_taxes[$tax['tax_type']] += (($tax['is_percentage'] == '1') ? ($rate_array[$index]['amount'] * $tax['tax_rate'] / 100) : $tax['tax_rate']);
								if(!$tax['is_tax_inclusive']){
									$tax_total += (($tax['is_percentage'] == '1') ? ($rate_array[$index]['amount'] * $tax['tax_rate'] / 100) : $tax['tax_rate']);
								}
							}
                        }
                      $total_charges += $rate_array[$index]['amount'] + $tax_total;
                    }
				}
			}
			else // booking isn't using rate plan
			{
				
				$booking_id = $booking_details['booking_id'];
                
                $tax_rates = $this->ci->Charge_type_model->get_taxes($booking_details['charge_type_id'], $booking_details['rate']);
				
				if (isset($booking_details['charge_type_id']))
				{
					$charge_type = $this->ci->Charge_type_model->get_charge_type_by_id($booking_details['charge_type_id']);	
				} else 
				{
					$charge_type = $this->ci->Charge_type_model->get_default_room_charge_type($company_id);
				}
				
                if($booking_details['pay_period'] == ONE_TIME)
                {
                    if($date_start)
                    {
                        $rate_array[] = array(
                            "description"  => "One Time Charge",
                            "selling_date" => $date_start,
                            "amount" => $booking_details['rate'],
                            "charge_type_id" => $charge_type['id'],
                            "charge_type_name" => $charge_type['name'],
                            "pay_period"   => $booking_details['pay_period'],
                            "booking_id"   => $booking_id,
                            "taxes" => $tax_rates,
                            "room_name" =>  $room_name,
                            "customer_name" =>  $customer_name,
                            "room_type_name" =>  $room_type_name
                        );
                        
                        if($get_amount_only)
                        {
                            $tax_total = 0;
                            if($tax_rates && count($tax_rates) > 0)
                            {
                                foreach($tax_rates as $tax){
                                    if(!isset($total_taxes[$tax['tax_type']]))
                                        $total_taxes[$tax['tax_type']] = 0;
                                    $total_taxes[$tax['tax_type']] += (($tax['is_percentage'] == '1') ? ($booking_details['rate'] * $tax['tax_rate'] / 100) : $tax['tax_rate']);
									if(!$tax['is_tax_inclusive']){
										$tax_total += (($tax['is_percentage'] == '1') ? ($booking_details['rate'] * $tax['tax_rate'] / 100) : $tax['tax_rate']);									
									}
                                }
                            }
							$total_charges += $booking_details['rate'] + $tax_total;
                        }
                    }
                }
				elseif ($check_in_date == $check_out_date && $booking_details['state'] < CHECKOUT)
				{
                    // if customer is checking in & checking out on same date, still charge him for 1 day.
					$rate_array[] = array(
                        "description"  => "Daily ".$company_details['default_charge_name'],
                        "selling_date" => $date_start,
                        "amount" => $booking_details['rate'],
                        "charge_type_id" => $charge_type['id'],
                        "charge_type_name" => $charge_type['name'],
                        "pay_period"   => DAILY,
                        "booking_id"   => $booking_id,
                        "taxes" => $tax_rates,
                        "room_name" =>  $room_name ,
                        "customer_name" =>  $customer_name,
                        "room_type_name" =>  $room_type_name
					);
                    
                    if($get_amount_only)
                    {
                        $tax_total = 0;
                        if($tax_rates && count($tax_rates) > 0)
                        {
                            foreach($tax_rates as $tax){
                                if(!isset($total_taxes[$tax['tax_type']]))
                                    $total_taxes[$tax['tax_type']] = 0;
                                $total_taxes[$tax['tax_type']] += (($tax['is_percentage'] == '1') ? ($booking_details['rate'] * $tax['tax_rate'] / 100) : $tax['tax_rate']);
								if(!$tax['is_tax_inclusive']){
	                                $tax_total += (($tax['is_percentage'] == '1') ? ($booking_details['rate'] * $tax['tax_rate'] / 100) : $tax['tax_rate']);
								}
								
                            }
                        }
						$total_charges += $booking_details['rate'] + $tax_total;
                    }
				}
				else
                {
                    $charge_start_date = $date_start;
					for ($charge_end_date = Date("Y-m-d", strtotime($date_increment." -1 day", strtotime($charge_start_date))); 
                         $charge_end_date < $date_end; 
                         $charge_end_date = Date("Y-m-d", strtotime($date_increment, strtotime($charge_end_date)))
                    ) {
                        $rate_array[] = array(
                            "description"  => $description,
                            "selling_date" => $charge_start_date,
                            "amount"       => $booking_details['rate'],
                            "charge_type_id" => $charge_type['id'],
                            "charge_type_name" => $charge_type['name'],
                            "pay_period"   => $booking_details['pay_period'],
                            "booking_id"   => $booking_id,
                            "taxes" => $tax_rates,
                            "room_name" =>  $room_name,
                            "customer_name" =>  $customer_name, 
                            "room_type_name" =>  $room_type_name 
                        );
                        $charge_start_date = Date("Y-m-d", strtotime($date_increment, strtotime($charge_start_date)));
                        
                        if($get_amount_only)
                        {
                            $tax_total = 0;
                            if($tax_rates && count($tax_rates) > 0)
                            {
                                foreach($tax_rates as $tax){
                                    if(!isset($total_taxes[$tax['tax_type']]))
                                        $total_taxes[$tax['tax_type']] = 0;
                                    $total_taxes[$tax['tax_type']] += (($tax['is_percentage'] == '1') ? ($booking_details['rate'] * $tax['tax_rate'] / 100) : $tax['tax_rate']);
									
									if(!$tax['is_tax_inclusive']){
	                                    $tax_total += (($tax['is_percentage'] == '1') ? ($booking_details['rate'] * $tax['tax_rate'] / 100) : $tax['tax_rate']);
									}
									
                                }
                            }
							$total_charges += $booking_details['rate'] + $tax_total;
                        }
					}
                    $date = $charge_start_date;
                    if($date < $date_end && $booking_details['add_daily_charge'])
                    {   
                        $date_start = $date;
                        $daily_rate = (isset($booking_details['pay_period']) && ($booking_details['pay_period'] == WEEKLY || $booking_details['pay_period'] == MONTHLY) && $booking_details['residual_rate']) ? $booking_details['residual_rate'] : round(($booking_details['rate'] / $days), 2, PHP_ROUND_HALF_UP);
                        for ($date = $date_start; $date < $date_end; $date = Date("Y-m-d", strtotime("+1 day", strtotime($date))) )
                        {   
                            if($date >= $current_selling_date && $booking_details['add_daily_charge'])
                            {
                                $rate_array[] = array(
                                    "description"  => "Daily ".$company_details['default_charge_name'],
                                    "selling_date" => $date,
                                    "amount"       => $daily_rate,
                                    "charge_type_id" => $charge_type['id'],
                                    "charge_type_name" => $charge_type['name'],
                                    "pay_period"   => DAILY,
                                    "booking_id"   => $booking_id,
                                    "taxes" => $tax_rates,
                                    "room_name" =>  $room_name,
                                    "customer_name" =>  $customer_name, 
                                    "room_type_name" =>  $room_type_name
                                );
                                
                                if($get_amount_only)
                                {
                                    $tax_total = 0;
                                    if($tax_rates && count($tax_rates) > 0)
                                    {
                                        foreach($tax_rates as $tax){
                                            if(!isset($total_taxes[$tax['tax_type']]))
                                                $total_taxes[$tax['tax_type']] = 0;
                                            $total_taxes[$tax['tax_type']] += (($tax['is_percentage'] == '1') ? ($daily_rate * $tax['tax_rate'] / 100) : $tax['tax_rate']);
											if(!$tax['is_tax_inclusive']){
											    $tax_total += (($tax['is_percentage'] == '1') ? ($daily_rate * $tax['tax_rate'] / 100) : $tax['tax_rate']);
											}
									    }
                                    }
                                $total_charges += $daily_rate + $tax_total;
                                }
                            }
                        }
                    }
				}
            }
		}
        if($get_amount_only)     
        {
            $response = array(
                    "total_charges" => $total_charges,
                    "total_taxes" => $total_taxes
                );
        }
        else
        {
            $response = $rate_array;
        }
        return $response;
    }
	
    
    // get forecast extra charge array without taxes
    function _get_forecast_extra_charges($booking_id, $get_amount_only = false, $booking_details = false)
	{
		$extra_array = Array();
        $extra_charges = 0;
		$booking_details = $booking_details ? $booking_details : $this->ci->Booking_model->get_booking_detail($booking_id);
        $room_name = $booking_details['room_name'];
        $company_id = $booking_details['company_id'];
       
        if ($booking_details['state'] == CANCELLED || $booking_details['state'] == CHECKOUT)
		{
			return $get_amount_only ? $extra_charges : $extra_array;
		}
		if (($extras = $this->ci->Booking_extra_model->get_booking_extras($booking_id)))
		{
			foreach ($extras as $extra) {
                
				$current_selling_date = $this->ci->Company_model->get_selling_date($company_id);
                
                $tax_rates = $this->ci->Tax_model->get_tax_rates_by_charge_type_id($extra['charge_type_id'], $company_id, $extra['rate']);
				
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
                        $extra_array[] = array(
                            "amount"       => $extra['rate'] * $extra['quantity'],
                            "selling_date" => $date_start,
                            "pay_period" => ONE_TIME,
							"description" => $extra['extra_name']." (quantity: ".$extra['quantity'].")",
							"charge_type_id" => $extra['charge_type_id'],
							"charge_type_name" => $extra['name'],
                            "booking_id" => $booking_id,
                            "room_name" => $room_name
                        );
                        
                        if($get_amount_only)
                        {
                            $tax_total = 0;
                            if($tax_rates && count($tax_rates) > 0)
                            {
                                foreach($tax_rates as $tax){
									if(!$tax['is_tax_inclusive']){
										$tax_total += ($extra['rate'] * $tax['tax_rate'] / 100);
									}
									
                                }
                            }
                            $extra_charges += ($extra['rate'] + $tax_total) * $extra['quantity'];
                        }
                    }
                    else if($extra['charging_scheme'] == 'once_a_day' && $extra['extra_type'] == 'rental' && strtotime($date_start) < strtotime($date_end))
                    {
                        for ($date = $date_start; $date < $date_end; $date = Date("Y-m-d", strtotime("+1 day", strtotime($date))))
                        {
                            $extra_array[] = array(
                                "amount"       => $extra['rate'] * $extra['quantity'],
                                "selling_date" => $date,
                                "pay_period" => DAILY,
                                "description" => $extra['extra_name']." (quantity: ".$extra['quantity'].")",
                                "charge_type_id" => $extra['charge_type_id'],
                                "charge_type_name" => $extra['name'],
                                "booking_id" => $booking_id,
                                "room_name" => $room_name
                            );
                            
                            if($get_amount_only)
                            {
                                $tax_total = 0;
                                if($tax_rates && count($tax_rates) > 0)
                                {
                                    foreach ($tax_rates as $tax) {
										if (!$tax['is_tax_inclusive']) {
	                                        $tax_total += ($extra['rate'] * $tax['tax_rate'] / 100);
										}
                                    }
                                }
                                $extra_charges += ($extra['rate'] + $tax_total) * $extra['quantity'];
                            }
                        }
                    } else {
                        for ($date = $date_start; $date <= $date_end; $date = Date("Y-m-d", strtotime("+1 day", strtotime($date))))
                        {
                            $extra_array[] = array(
                                "amount"       => $extra['rate'] * $extra['quantity'],
                                "selling_date" => $date,
                                "pay_period" => DAILY,
                                "description" => $extra['extra_name']." (quantity: ".$extra['quantity'].")",
                                "charge_type_id" => $extra['charge_type_id'],
                                "charge_type_name" => $extra['name'],
                                "booking_id" => $booking_id,
                                "room_name" => $room_name
                            );
                            
                            if($get_amount_only)
                            {
                                $tax_total = 0;
                                if($tax_rates && count($tax_rates) > 0)
                                {
                                    foreach ($tax_rates as $tax) {
                                        if (!$tax['is_tax_inclusive']) {
                                            $tax_total += ($extra['rate'] * $tax['tax_rate'] / 100);
                                        }
                                    }
                                }
                                $extra_charges += ($extra['rate'] + $tax_total) * $extra['quantity'];
                            }
                        }
                    }
				}
			}
		}
		return $get_amount_only ? $extra_charges : $extra_array;
	}
    
    function _get_day_wise_forecast_charges($start_date, $end_date, $current_selling_date, $get_room_charges_only = false, $include_taxes = true, $customer_type_id = null, $is_tax_exempt = '')
    {
        $get_room_charges_only_sql = $join_statement = $where_statement = "";
		if ($get_room_charges_only)
		{
			$get_room_charges_only_sql = "AND is_room_charge_type = '1'";
		}
		$future_charges = "b.rate";
		if ($include_taxes)
		{
			$future_charges = "b.rate * (1+IFNULL(percentage_total * 0.01, 0)) + IFNULL(flat_tax_total, 0) as rate";
		}
        
        if($customer_type_id)
        {
            $join_statement = "LEFT JOIN customer as c ON b.booking_customer_id = c.customer_id";
            $where_statement = "AND c.customer_type_id = '$customer_type_id'";
        }
        $where_is_tax_exempt = "";
        if($is_tax_exempt === 1 || $is_tax_exempt === 0) {
            $where_is_tax_exempt = "AND t2.is_tax_exempt = '$is_tax_exempt' ";
        }
		$company_id = $this->ci->session->userdata('current_company_id');
		
        $sql = " 
                select
                    $future_charges,
                    DATE(brh.check_in_date) as check_in_date, 
                    DATE(brh.check_out_date) as check_out_date,
                    b.pay_period,
                    b.booking_id,
                    b.charge_type_id,
                    b.use_rate_plan,
                    b.rate_plan_id,
                    b.adult_count,
                    b.children_count,
                    b.residual_rate
                from 
                    booking_block as brh,
                    booking as b
                LEFT JOIN ( 
                    SELECT 
                        ct3.name, 
                        ct3.id, 
                        ct3.is_tax_exempt,
						sum(IF(tt.is_percentage = 1, IF(tt.is_tax_inclusive = 1,0,tt.tax_rate), 0)) as percentage_total, 
                        sum(IF(tt.is_percentage = 0, IF(tt.is_tax_inclusive = 1,0,tt.tax_rate), 0)) as flat_tax_total
                        
                    FROM 
                        charge_type AS ct3
                    LEFT JOIN charge_type_tax_list as cttl ON ct3.id = cttl.charge_type_id 				
                    LEFT JOIN tax_type AS tt ON tt.tax_type_id = cttl.tax_type_id  AND tt.is_deleted = '0'
					LEFT JOIN tax_price_bracket AS tpb ON tt.tax_type_id = tpb.tax_type_id
                    WHERE 	ct3.company_id = '$company_id' 
                        #AND ct.is_deleted = '0'
                        $get_room_charges_only_sql
                    GROUP BY charge_type_id
                )t2 ON b.charge_type_id = t2.id	
                $join_statement
                where 
                    b.company_id = '$company_id' AND
                    brh.booking_id = b.booking_id AND
                    DATE(brh.check_out_date) > '$start_date' AND
                    DATE(brh.check_in_date) < '$end_date' AND
                    b.is_deleted != '1' AND
                    b.state < 3 
                    $where_is_tax_exempt
                    $where_statement
                GROUP BY b.booking_id";
       
        $query = $this->ci->db->query($sql);
         
		if ($this->ci->db->_error_message()) // error checking
			show_error($this->ci->db->_error_message());
		$result = array();
		if ($query->num_rows >= 1) 
		{	
			foreach ($query->result_array() as $row)
			{
                
                
                if($row['check_out_date'] < $current_selling_date){
                    continue;
                }
                
                $date_start = max($row['check_in_date'], $current_selling_date);
                $last_room_charge = $this->ci->Charge_model->get_last_applied_charge($row['booking_id'], $row['charge_type_id'], null, true);
                if(isset($last_room_charge['selling_date']) && $last_room_charge['selling_date']){
                    if($row['pay_period'] == DAILY){
                        $date_start = date('Y-m-d', strtotime($last_room_charge['selling_date'].' +1 day'));
                    }
                    elseif($row['pay_period'] == WEEKLY){
                        $date_start = date('Y-m-d', strtotime($last_room_charge['selling_date'].' +7 day'));
                    }
                    elseif($row['pay_period'] == MONTHLY){
                        $date_start = date('Y-m-d', strtotime($last_room_charge['selling_date'].' +1 month'));
                    }
                    elseif($row['pay_period'] == ONE_TIME){
                        // skip this one time charge booking if we charged it alreday
                        continue;
                    }
                }
                
                $days = 1;
                $date_increment = "+1 day";
                $date_decrement = "-1 day";
                if($row['pay_period'] == WEEKLY)
                {
                    $days = 7;
                    $date_increment = "+7 days";
                    $date_decrement = "-7 days";
                }
                elseif($row['pay_period'] == MONTHLY)
                {
                    $days = 30;
                    $date_increment = "+1 month";
                    $date_decrement = "-1 month";
                }
                elseif($row['pay_period'] == ONE_TIME)
                {
                    if($row['check_in_date'] >= $current_selling_date){
                        if(!isset($result[$row['check_in_date']])){
                            $result[$row['check_in_date']] = 0;
                        }
                        $result[$row['check_in_date']] += $row['rate'];
                    }
                    continue;
                }
                
                
                if($row['use_rate_plan'])
                {
                    if (!($start_date < $end_date || $row['check_in_date'] == $row['check_out_date']))
                    {
                        //forecast charges dates must be less than checkout date.
                        return array();
                    }
                    $rate_plan_id = $row['rate_plan_id'];
                    $adult_count = $row['adult_count'];
                    $children_count = $row['children_count'];
                    
                    $rate_plan   = $this->ci->Rate_plan_model->get_rate_plan($rate_plan_id);
                    
                    if($row['check_out_date'] < $current_selling_date){
                        continue;
                    }
                    
                    $rate_array = $this->ci->rate->get_rate_array($rate_plan_id, $date_start, $row['check_out_date'], $adult_count, $children_count);
                    // change key names (i.e. date => selling_date, rate => amount
                    foreach ($rate_array as $index => $rate)
                    {
                        if(!isset($result[$rate_array[$index]['date']])){
                            $result[$rate_array[$index]['date']] = 0;
                        }
                        $result[$rate_array[$index]['date']] += $rate_array[$index]['rate'];
                    }
                }
                else
                {
                    $date_end = $row['check_out_date'];
                    for ($charge_start_date = $date_start; 
                        $charge_start_date < $date_end && Date("Y-m-d", strtotime($date_increment, strtotime($charge_start_date))) <= $row['check_out_date']; 
                        $charge_start_date = Date("Y-m-d", strtotime($date_increment, strtotime($charge_start_date)))
                    ) 
                    {
                        if($charge_start_date >= $start_date && $charge_start_date < $end_date)
                        {
                            if(!isset($result[$charge_start_date])){
                                $result[$charge_start_date] = 0;
                            }
                            $result[$charge_start_date] += $row['rate'];
                        }
                    }
                    $date = $charge_start_date;
                    if($date < $date_end)
                    {   
                        $date_start = $date;
                        $daily_rate = (isset($row['pay_period']) && ($row['pay_period'] == WEEKLY || $row['pay_period'] == MONTHLY) && $row['residual_rate']) ? $row['residual_rate'] :round(($row['rate'] / $days), 2, PHP_ROUND_HALF_UP);
                        for ($date = $date_start; $date < $date_end; $date = Date("Y-m-d", strtotime("+1 day", strtotime($date))) )
                        {   
                            if($date >= $start_date && $date < $end_date)
                            {
                                if(!isset($result[$date])){
                                    $result[$date] = 0;
                                }
                                $result[$date] += $daily_rate;
                            }
                        }
                    }
                }
			}
		}
        return $result;
    }

    // GET THE FORECAST CHARGES SOURCE WISE
    function _get_source_wise_forecast_charges($start_date, $end_date, $current_selling_date, $get_room_charges_only = false, $include_taxes = true, $is_tax_exempt = '')
    {
        $get_room_charges_only_sql = $join_statement = $where_statement = "";
        if ($get_room_charges_only)
        {
            $get_room_charges_only_sql = "AND is_room_charge_type = '1'";
        }
        $future_charges = "b.rate";
        if ($include_taxes)
        {
            $future_charges = "b.rate * (1+IFNULL(percentage_total * 0.01, 0)) + IFNULL(flat_tax_total, 0) as rate";
        }
        
        $where_is_tax_exempt = "";
        if($is_tax_exempt === 1 || $is_tax_exempt === 0) {
            $where_is_tax_exempt = "AND t2.is_tax_exempt = '$is_tax_exempt' ";
        }
        $company_id = $this->ci->session->userdata('current_company_id');

        $sql = " 
                select
                    $future_charges,
                    DATE(brh.check_in_date) as check_in_date, 
                    DATE(brh.check_out_date) as check_out_date,
                    b.pay_period,
                    b.booking_id,
                    b.charge_type_id,
                    b.use_rate_plan,
                    b.rate_plan_id,
                    b.adult_count,
                    b.children_count,
                    b.source
                from 
                    booking_block as brh,
                    booking as b
                LEFT JOIN ( 
                    SELECT 
                        ct3.name, 
                        ct3.id, 
                        ct3.is_tax_exempt,
						sum(IF(tt.is_percentage = 1, IF(tt.is_tax_inclusive = 1,0,tt.tax_rate), 0)) as percentage_total, 
                        sum(IF(tt.is_percentage = 0, IF(tt.is_tax_inclusive = 1,0,tt.tax_rate), 0)) as flat_tax_total
                        
                    FROM 
                        charge_type AS ct3
                    LEFT JOIN charge_type_tax_list as cttl ON ct3.id = cttl.charge_type_id 				
                    LEFT JOIN tax_type AS tt ON tt.tax_type_id = cttl.tax_type_id  AND tt.is_deleted = '0'
					LEFT JOIN tax_price_bracket AS tpb ON tt.tax_type_id = tpb.tax_type_id
                    WHERE 	ct3.company_id = '$company_id' 
                        #AND ct.is_deleted = '0'
                        $get_room_charges_only_sql
                    GROUP BY charge_type_id
                )t2 ON b.charge_type_id = t2.id	
                $join_statement
                where 
                    b.company_id = '$company_id' AND
                    brh.booking_id = b.booking_id AND
                    DATE(brh.check_out_date) > '$start_date' AND
                    DATE(brh.check_in_date) < '$end_date' AND
                    b.is_deleted != '1' AND
                    b.state < 3 
                    $where_is_tax_exempt
                    $where_statement
                GROUP BY b.booking_id";

        $query = $this->ci->db->query($sql);

        if ($this->ci->db->_error_message()) // error checking
            show_error($this->ci->db->_error_message());
        $result = array();
        if ($query->num_rows >= 1)
        {
            foreach ($query->result_array() as $row)
            {


                if($row['check_out_date'] < $current_selling_date){
                    continue;
                }

                $date_start = max($row['check_in_date'], $current_selling_date);
                $last_room_charge = $this->ci->Charge_model->get_last_applied_charge($row['booking_id'], $row['charge_type_id'], null, true);
                if(isset($last_room_charge['selling_date']) && $last_room_charge['selling_date']){
                    if($row['pay_period'] == DAILY){
                        $date_start = date('Y-m-d', strtotime($last_room_charge['selling_date'].' +1 day'));
                    }
                    elseif($row['pay_period'] == WEEKLY){
                        $date_start = date('Y-m-d', strtotime($last_room_charge['selling_date'].' +7 day'));
                    }
                    elseif($row['pay_period'] == MONTHLY){
                        $date_start = date('Y-m-d', strtotime($last_room_charge['selling_date'].' +1 month'));
                    }
                    elseif($row['pay_period'] == ONE_TIME){
                        // skip this one time charge booking if we charged it alreday
                        continue;
                    }
                }

                $days = 1;
                $date_increment = "+1 day";
                $date_decrement = "-1 day";
                if($row['pay_period'] == WEEKLY)
                {
                    $days = 7;
                    $date_increment = "+7 days";
                    $date_decrement = "-7 days";
                }
                elseif($row['pay_period'] == MONTHLY)
                {
                    $days = 30;
                    $date_increment = "+1 month";
                    $date_decrement = "-1 month";
                }
                elseif($row['pay_period'] == ONE_TIME)
                {
                    if($row['check_in_date'] >= $current_selling_date){
                        if(!isset($result[$row['source']])){
                            $result[$row['source']] = 0;
                        }
                        $result[$row['source']] += $row['rate'];
                    }
                    continue;
                }


                if($row['use_rate_plan'])
                {
                    if (!($start_date < $end_date || $row['check_in_date'] == $row['check_out_date']))
                    {
                        //forecast charges dates must be less than checkout date.
                        return array();
                    }
                    $rate_plan_id = $row['rate_plan_id'];
                    $adult_count = $row['adult_count'];
                    $children_count = $row['children_count'];

                    $rate_plan   = $this->ci->Rate_plan_model->get_rate_plan($rate_plan_id);

                    if($row['check_out_date'] < $current_selling_date){
                        continue;
                    }

                    $rate_array = $this->ci->rate->get_rate_array($rate_plan_id, $date_start, $row['check_out_date'], $adult_count, $children_count);
                    // change key names (i.e. date => selling_date, rate => amount
                    foreach ($rate_array as $index => $rate)
                    {
                        if(!isset($result[$row['source']])){
                            $result[$row['source']] = 0;
                        }
                        $result[$row['source']] += $rate_array[$index]['rate'];
                    }
                }
                else
                {
                    $date_end = $row['check_out_date'];
                    for ($charge_start_date = $date_start;
                         $charge_start_date < $date_end && Date("Y-m-d", strtotime($date_increment, strtotime($charge_start_date))) <= $row['check_out_date'];
                         $charge_start_date = Date("Y-m-d", strtotime($date_increment, strtotime($charge_start_date)))
                    )
                    {
                        if($charge_start_date >= $start_date && $charge_start_date < $end_date)
                        {
                            if(!isset($result[$row['source']])){
                                $result[$row['source']] = 0;
                            }
                            $result[$row['source']] += $row['rate'];
                        }
                    }
                    $date = $charge_start_date;
                    if($date < $date_end)
                    {
                        $date_start = $date;
                        $daily_rate = (isset($row['pay_period']) && ($row['pay_period'] == WEEKLY || $row['pay_period'] == MONTHLY) && $row['residual_rate']) ? $row['residual_rate'] :round(($row['rate'] / $days), 2, PHP_ROUND_HALF_UP);
                        for ($date = $date_start; $date < $date_end; $date = Date("Y-m-d", strtotime("+1 day", strtotime($date))) )
                        {
                            if($date >= $start_date && $date < $end_date)
                            {
                                if(!isset($result[$row['source']])){
                                    $result[$row['source']] = 0;
                                }
                                $result[$row['source']] += $daily_rate;
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }
    
    function _get_forecast_charges_with_next_charge_date($booking_id, $start_date, $end_date, $tax_rates, $total_payments)
    {
        $start_date = date('Y-m-d', strtotime($start_date));
        $end_date = date('Y-m-d', strtotime($end_date));
        
        $booking_details = $this->ci->Booking_model->get_booking_detail($booking_id);
		
		if(!$booking_details){
            return array("total_charges" => 0, "total_taxes" => array(), "next_expected_charge_date" => null);
        }
        
        $check_in_date = date('Y-m-d', strtotime($booking_details['check_in_date']));
        $check_out_date = date('Y-m-d', strtotime($booking_details['check_out_date']));
        $pay_period = $booking_details['pay_period'];
        $booking_state = $booking_details['state'];
        $rate = $booking_details['rate'];
        
        if($end_date < $this->ci->selling_date || $booking_state == CANCELLED || $booking_state == CHECKOUT || $booking_state == NO_SHOW){
            return array("total_charges" => 0, "total_taxes" => array(), "next_expected_charge_date" => null);
        }
        
        $next_expected_charge_date = null;
        $next_expected_charge_date_found = false;
        $total_charges = 0;
        $total_taxes = array();
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
            $last_room_charge = $this->ci->Charge_model->get_last_applied_charge($booking_id, $booking_details['charge_type_id'], null, true);
            
            if($start_date && !$last_room_charge && $check_out_date >= $this->ci->selling_date)
            {
				$tax_total = 0;
                if($tax_rates && count($tax_rates) > 0)
                {
					$is_tax_inclusive = array();
                    foreach($tax_rates as $tax){
                        if(!isset($total_taxes[$tax['tax_type']]))
                            $total_taxes[$tax['tax_type']] = 0;
                        $total_taxes[$tax['tax_type']] += ($rate * $tax['tax_rate'] / 100);
						if(!$tax['is_tax_inclusive']){
							$tax_total += $rate * $tax['tax_rate'] / 100;
						}
                        
                    }
                }
                $total_charges += $rate + $tax_total;
                $remaining_payments = $total_payments - ($rate + $tax_total);
                if(!$next_expected_charge_date_found && $remaining_payments < 0){                    
                    $next_expected_charge_date = null;
                    $next_expected_charge_date_found = true;
                }
            }
            return array(
                "total_charges" => $total_charges,
                "total_taxes" => $total_taxes,
                "next_expected_charge_date" => $next_expected_charge_date
            );
        }
        $start_date = max($start_date, $this->ci->selling_date);
        $charge_start_date = $start_date;
        
        if ($booking_details['use_rate_plan']) 
        {
            $rate_plan = $this->ci->Rate_plan_model->get_rate_plan($booking_details['rate_plan_id']);
            $charge_type_id = isset($rate_plan['charge_type_id']) && $rate_plan['charge_type_id'] ? $rate_plan['charge_type_id'] : $booking_details['charge_type_id'];
            
            if (!($start_date < $end_date || $check_in_date == $check_out_date))
            {
                //forecast charges dates must be less than checkout date.
                if($get_amount_only) 
                {
                    $response = array(
                            "total_charges" => 0,
                            "total_taxes" => array(),
                            "next_expected_charge_date" => null
                        );
                }
                else
                {
                    $response = array();
                }
                return $response;
            }
            
            $rate_plan_id = $booking_details['rate_plan_id'];
            $adult_count = $booking_details['adult_count'];
            $children_count = $booking_details['children_count'];
            $rate_array = $this->ci->rate->get_rate_array($rate_plan_id, $start_date, $end_date, $adult_count, $children_count);
			
			$last_tax_rate_amount = $rate_plan_tax_rates = null;
            // change key names (i.e. date => selling_date, rate => amount
            foreach ($rate_array as $index => $rate)
            {
				$amount = $rate['rate'];
				if(!($last_tax_rate_amount && $last_tax_rate_amount == $amount)) {
					$rate_plan_tax_rates = $this->ci->Tax_model->get_tax_rates_by_charge_type_id($charge_type_id, null, $amount);
					$last_tax_rate_amount = $amount;
				}
				
                $rate_array[$index]['selling_date'] = $rate_array[$index]['date'];
                unset($rate_array[$index]['date']);
                $rate_array[$index]['amount'] = $rate_array[$index]['rate'];
                unset($rate_array[$index]['rate']);
                $rate_array[$index]['pay_period'] = DAILY; // temporary fix as all rate plans are daily only for now. eventually this may allow weekly & monthly,
                $rate_array[$index]['taxes'] = $rate_plan_tax_rates;
                unset($rate_array[$index]['rate_plan_name']);
                unset($rate_array[$index]['is_deleted']); // you can't delete forecasted charges, but this variable is set from DB
                $tax_total = 0;
                if($rate_plan_tax_rates && count($rate_plan_tax_rates) > 0)
                {
                    foreach($rate_plan_tax_rates as $tax){
                        if(!isset($total_taxes[$tax['tax_type']]))
                            $total_taxes[$tax['tax_type']] = 0;
                        $total_taxes[$tax['tax_type']] += (($tax['is_percentage'] == '1') ? ($rate_array[$index]['amount'] * $tax['tax_rate'] / 100) : $tax['tax_rate']);
						if(!$tax['is_tax_inclusive']){
							$tax_total += ($amount * $tax['tax_rate'] / 100);
						}						
                    }
                }
                $total_charges += $rate_array[$index]['amount'] + $tax_total;
                $remaining_payments = $total_payments - ($rate_array[$index]['amount'] + $tax_total);
                if(!$next_expected_charge_date_found && $remaining_payments < 0){                    
                    $next_expected_charge_date = $rate_array[$index]['selling_date'];
                    $next_expected_charge_date_found = true;
                }
                $total_payments = $remaining_payments;
            }
            
        } else {
            
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
                        if($tax['is_tax_inclusive']){ 
							$total_taxes[$tax['tax_type']] += ($rate - ($rate / (1 + ((float) $tax['tax_rate'] * 0.01))));
						} else {
							$total_taxes[$tax['tax_type']] += ($rate * $tax['tax_rate'] / 100);
							$tax_total += ($rate * $tax['tax_rate'] / 100);
						}
					} 
				}
					
                $total_charges += $rate + $tax_total;

                $remaining_payments = $total_payments - ($rate + $tax_total);
                if(!$next_expected_charge_date_found && $remaining_payments < 0){                    
                    $next_expected_charge_date = $charge_start_date;
                    $next_expected_charge_date_found = true;
                }
                $total_payments = $remaining_payments;
            }
            $date = $charge_start_date;
            if($date < $end_date)
            {   
                $start_date = $date;
                $daily_rate = (isset($pay_period) && ($pay_period == WEEKLY || $pay_period == MONTHLY) && $booking_details['residual_rate']) ? $booking_details['residual_rate'] : round(($rate / $days), 2, PHP_ROUND_HALF_UP);
                for ($date = $start_date; $date < $end_date; $date = Date("Y-m-d", strtotime("+1 day", strtotime($date))) )
                {   
                    if($date >= $this->ci->selling_date)
                    {
                        $tax_total = 0;
                        if($tax_rates && count($tax_rates) > 0)
                        {
                            foreach($tax_rates as $tax){
								if(!isset($total_taxes[$tax['tax_type']]))
                                    $total_taxes[$tax['tax_type']] = 0;
                                    $total_taxes[$tax['tax_type']] += ($daily_rate * $tax['tax_rate'] / 100);
									//$tax_total += ($daily_rate * $tax['tax_rate'] / 100);
									//$total_taxes[]['is_tax_inclusive'] = $tax['is_tax_inclusive'];
								
									// $total_taxes['tax_total'] += $tax['tax_rate']; 
									$tax_total += ($daily_rate * $tax['tax_rate'] / 100);
							} 
                        }
                        $total_charges += $daily_rate + $tax_total;

                        $remaining_payments = $total_payments - ($daily_rate + $tax_total);
                        if(!$next_expected_charge_date_found && $remaining_payments < 0){                    
                            $next_expected_charge_date = $date;
                            $next_expected_charge_date_found = true;
                        }
                        $total_payments = $remaining_payments;
                    }
                }
            }
        }
		
        return array(
            "total_charges" => $total_charges,
            "total_taxes" => $total_taxes,
            "next_expected_charge_date" => $next_expected_charge_date
        );
    }
    
}