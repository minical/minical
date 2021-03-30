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
        
        $this->ci->load->library('rate');
    }
    
    /* 
     * @var $get_amount_only get forecast charge/tax  in amount if true or in array if false
     */    
    function _get_forecast_charges($booking_id, $get_amount_only = false)
    {
		$booking_details = $this->ci->Booking_model->get_booking_detail($booking_id);
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
        $description = "Daily Room Charge";

        if($booking_details['pay_period'] == WEEKLY)
        {
            $days = 7;
            $date_increment = "+7 days";
            $date_decrement = "-7 days";
            $description = "Weekly Room Charge";
        }
        elseif($booking_details['pay_period'] == MONTHLY)
        {
            $days = 30;
            $date_increment = "+1 month";
            $date_decrement = "-1 month";
            $description = "Monthly Room Charge";
        }
        $company_id = $booking_details['company_id'];
        
		//Load data used in calculations
		$current_selling_date = $this->ci->Company_model->get_selling_date($company_id);
        $check_in_date = date('Y-m-d', strtotime($booking_details['check_in_date']));
        $check_out_date = date('Y-m-d', strtotime($booking_details['check_out_date']));
    
        $rate_plan   = $this->ci->Rate_plan_model->get_rate_plan($booking_details['rate_plan_id']);
        $charge_type = $this->ci->Charge_type_model->get_charge_type_by_id($rate_plan['charge_type_id']);
        
        //$room_charge_type_id = $this->ci->Charge_type_model->get_default_room_charge_type($company_id)['id'];
        $room_charge_type_id = $this->ci->Charge_type_model->get_room_charge_type_id($booking_id);
        
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
                
                $tax_rates = $this->ci->Charge_type_model->get_taxes($rate_plan['charge_type_id']);
                
				$rate_plan_id = $booking_details['rate_plan_id'];
				$adult_count = $booking_details['adult_count'];
				$children_count = $booking_details['children_count'];
				
				$rate_array = $this->ci->rate->get_rate_array($rate_plan_id, $date_start, $date_end, $adult_count, $children_count);

				// change key names (i.e. date => selling_date, rate => amount
				foreach ($rate_array as $index => $rate)
				{
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
                                $tax_total += (($tax['is_percentage'] == '1') ? ($rate_array[$index]['amount'] * $tax['tax_rate'] / 100) : $tax['tax_rate']);}
								else{
									$tax_total += '0';
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
                
                $tax_rates = $this->ci->Charge_type_model->get_taxes($booking_details['charge_type_id']);
				
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
                            "taxes" => $tax_rates
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
                        "description"  => "Daily Room Charge",
                        "selling_date" => $date_start,
                        "amount" => $booking_details['rate'],
                        "charge_type_id" => $charge_type['id'],
                        "charge_type_name" => $charge_type['name'],
                        "pay_period"   => DAILY,
                        "taxes" => $tax_rates
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
                            "taxes" => $tax_rates
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
                    if($date < $date_end)
                    {   
                        $date_start = $date;
                        $daily_rate = round(($booking_details['rate'] / $days), 2, PHP_ROUND_HALF_UP);
                        for ($date = $date_start; $date < $date_end; $date = Date("Y-m-d", strtotime("+1 day", strtotime($date))) )
                        {   
                            if($date >= $current_selling_date)
                            {
                                $rate_array[] = array(
                                    "description"  => "Daily Room Charge",
                                    "selling_date" => $date,
                                    "amount"       => $daily_rate,
                                    "charge_type_id" => $charge_type['id'],
                                    "charge_type_name" => $charge_type['name'],
                                    "pay_period"   => DAILY,
                                    "taxes" => $tax_rates
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
    function _get_forecast_extra_charges($booking_id, $get_amount_only = false)
	{
		$extra_array = Array();
        $extra_charges = 0;

		$booking_details = $this->ci->Booking_model->get_booking_detail($booking_id);
		$company_id = $booking_details['company_id'];
				
		if ($booking_details['state'] == CANCELLED || $booking_details['state'] == CHECKOUT)
		{
			return $get_amount_only ? $extra_charges : $extra_array;
		}

		if (($extras = $this->ci->Booking_extra_model->get_booking_extras($booking_id)))
		{
			foreach ($extras as $extra) {
                
				$current_selling_date = $this->ci->Company_model->get_selling_date($company_id);
                
                $tax_rates = $this->ci->Tax_model->get_tax_rates_by_charge_type_id($extra['charge_type_id'], $company_id);
                
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
							"charge_type_name" => $extra['name']
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
                    else
                    {
                        for ($date = $date_start; $date < $date_end; $date = Date("Y-m-d", strtotime("+1 day", strtotime($date))))
                        {
                            $extra_array[] = array(
                                "amount"       => $extra['rate'] * $extra['quantity'],
                                "selling_date" => $date,
                                "pay_period" => DAILY,
                                "description" => $extra['extra_name']." (quantity: ".$extra['quantity'].")",
                                "charge_type_id" => $extra['charge_type_id'],
                                "charge_type_name" => $extra['name']

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
                    }
				}
			}
		}

		return $get_amount_only ? $extra_charges : $extra_array;
	}
    
}
