<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CodeIgniter Night Audit
 *
 * Main goal is to run night audit
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Jaeyun Noh
 * @link		
 */
class Night_audit {
	
	public function __construct()
	{
		$this->ci =& get_instance();
		
		$this->ci->load->model('Charge_model');
		$this->ci->load->model('Charge_type_model');		
		$this->ci->load->model('Payment_model');
		$this->ci->load->model('Room_model');		
		$this->ci->load->model('Company_model');
		$this->ci->load->model('Booking_model');
		$this->ci->load->model('Booking_log_model');
        $this->ci->load->model('Booking_room_history_model');
		$this->ci->load->model('Rate_plan_model');
		$this->ci->load->model('Rate_model');
		$this->ci->load->model('Invoice_log_model');
		$this->ci->load->model('Folio_model');
		$this->ci->load->model('Card_model');
        $this->ci->load->model('Channex_model');
		
		log_message('debug', "Night audit initialized");
	}

	function run_night_audit($company_id)
	{		
		//Prevent people from accidently running night audit too far.				
		$this->ci->load->helper('timezone');
		// for automatic night audit
		$user_id = $this->ci->session->userdata('user_id');
		if (isset($user_id))
		{
			if ($user_id == "" || !$user_id)
			{
				$user_id = "0";
			}
		}

		$company_time_zone = $this->ci->Company_model->get_time_zone($company_id);
		if (!isset($company_time_zone))
		{
			$company_time_zone = "America/New_York";		
		}
		
		$actual_date = convert_to_local_time(new DateTime(), $company_time_zone)->format("Y-m-d");
		$selling_date = $this->ci->Company_model->get_selling_date($company_id);
		$company = $this->ci->Company_model->get_company($company_id);
		
		if ($selling_date > $actual_date)
		{
			return "Selling date cannot be 2 days ahead of real date ($actual_date)";
		}
		
		//Charge all guests and update their balances
		$this->ci->db->trans_begin();
		// Checked-in
		$charge_response = $this->_charge_bookings($company_id, $selling_date, $user_id);
		
        
        $bookings_to_be_charged = $charge_response['bookings_to_be_charged'];
        $response = $charge_response['response'];
        
        
		//Set all occupied rooms' status as 'dirty'
        if($company_id != 2637)
		  $this->ci->Room_model->set_occupied_rooms_dirty($company_id);		
		        
        //Increase selling date
        $this->ci->Company_model->increment_selling_date_by_one($company_id);
        
		// check transaction. If there's any error, roll back. Otherwise, commit.
		if ($this->ci->db->trans_status() === FALSE)
		{
			$this->ci->db->trans_rollback();
			return "Error has occured. Please try running Night Audit again.";
		}
		else
		{
			$this->ci->db->trans_commit();
            
            // update balance before incrementing date;
            $this->_calculate_bookings_balance($bookings_to_be_charged);
            
            // update balance for no show bookings
            //$this->_calculate_bookings_balance($no_show_bookings_to_be_charged);
            
			$new_selling_date = $this->ci->Company_model->get_selling_date($company_id);
            
			//If Auto check-out is enabled in Night Audit Settings, Auto check-out guests
			if ($company['night_audit_force_check_out'])
			{
				$this->ci->Booking_model->auto_check_out_guests($company_id, $new_selling_date);
			}
			
			//If Auto Night auditing multiple days is enabled in night-audit-settings,
			//Repeat night audit process until selling_date is equal to actual_date.
			if ($company['night_audit_multiple_days'] &&	$new_selling_date < $actual_date)
			{
				return $this->run_night_audit($company_id);
			}
			else
			{
				// log night audit
				$this->ci->load->model('Night_audit_model');
				$this->ci->Night_audit_model->log_night_audit($company_id, $selling_date, $user_id);
				return $response;
			}
		}
		
	}

	/**
	* Charges all in house guests with a daily room charge
	* @return void
	*/
	function _charge_bookings($company_id, $selling_date, $user_id)
	{

		$company = $this->ci->Company_model->get_company($company_id);
        
		$booking_states = array("1");
        if($company['auto_no_show'] == '1')
		{   
			$reservations = $this->ci->Booking_model->get_reservations($company_id);
			foreach($reservations as $row)
			{
				$data = array();
				$data['state'] = NO_SHOW;

				$booking_id = $row['booking_id'];
				$new_checkin_date = $row['check_in_date'];
				$new_checkout_date = date('Y-m-d', strtotime($new_checkin_date . ' +1 day'));
				// update booking for one day 
				$this->ci->Booking_room_history_model->update_noshow_booking($booking_id, $new_checkin_date, $new_checkout_date);
                
				$booking_log_data = array(
					array(
							"selling_date" => $selling_date,
							"booking_id" => $booking_id,
							"date_time" => gmdate('Y-m-d H:i:s'),
							"log_type" => USER_LOG,
							"log" => "Booking auto-updated to No-Show",
							"user_id" => $user_id
						),
					array(
							"selling_date" => $selling_date,
							"booking_id" => $booking_id,
							"date_time" => gmdate('Y-m-d H:i:s'),
							"log_type" => USER_LOG,
							"log" => "Check-out date auto-updated to $new_checkout_date",
							"user_id" => $user_id
						)
				);
                $this->ci->Booking_log_model->insert_logs($booking_log_data); 
				
				$this->ci->Booking_model->update_booking($booking_id, $data); 
				$this->ci->Booking_model->update_booking_balance($booking_id);
			}        
		}
		$bookings_to_be_charged = $this->ci->Booking_model->get_todays_bookings($company_id);
		
		//Get the current UTC datetime for the company and use it to log the time of the night audit
		$this->ci->load->helper('timezone');
		$current_date_time_unix_format = convert_to_UTC_time(new DateTime(date('Y-m-d H:i:s')));
		$current_date_time = $current_date_time_unix_format->format('Y-m-d H:i:s');

        $default_room_charge_type_id = $this->ci->Charge_type_model->get_default_room_charge_type($company_id)['id'];

		$charge_data = Array();

		foreach ($bookings_to_be_charged as $booking)
		{			
			//Load all possible room charge types
			$booking_id = $booking['booking_id'];
            $customer_id = $booking['booking_customer_id'];
            $folio_id = 0;

            /*
             * No longer active - new folio for evc_card
             *
            $card_data = isset($customer_id) ? $this->ci->Card_model->get_active_card($customer_id, $company_id) : null;
            if(isset($card_data['evc_card_status']) && $card_data['evc_card_status'])
            {
                $folios = $this->ci->Folio_model->folios('Expedia EVC', $booking_id, $customer_id);
                if(isset($folios) && $folios)
                {
                    $folio_id = $folios[0]['id'];
                }
                else
                {
                    $first_folio_id = $folio_id = null;

                    $first_folio['booking_id'] = $booking_id;
                    $first_folio['customer_id'] = $customer_id;
                    $first_folio['folio_name'] = 'Folio #1';

                    $folio['booking_id'] = $booking_id;
                    $folio['customer_id'] = $customer_id;
                    $folio['folio_name'] = 'Expedia EVC';
                    $existing_folios = $this->ci->Folio_model->get_folios($booking_id, $customer_id);

                    if(!empty($existing_folios))
                    {
                        $folio_id = $this->ci->Folio_model->add_folio($folio);  
                    }
                    else
                    {
                        $first_folio_id = $this->ci->Folio_model->add_folio($first_folio);
                        $folio_id = $this->ci->Folio_model->add_folio($folio);
                    }
                    $invoice_log_data = array();
                    $invoice_log_data['date_time'] = gmdate('Y-m-d h:i:s');
                    $invoice_log_data['booking_id'] = $booking_id;
                    $invoice_log_data['user_id'] = $user_id;
                    $invoice_log_data['action_id'] = ADD_FOLIO;
                    if (isset($first_folio_id) && $first_folio_id) {
                        $invoice_log_data['log'] = $first_folio['folio_name'].' Folio Added';
                        $this->ci->Invoice_log_model->insert_log($invoice_log_data);
                    }
                    if ($folio_id) {
                        $invoice_log_data['log'] = $folio['folio_name'].' Folio Added';
                        $this->ci->Invoice_log_model->insert_log($invoice_log_data);
                    }
                }
            }
            else
            {
                $folio_id = 0;
            }
            */

			// if the booking is using rate plan
			if ($booking['use_rate_plan'] && $booking['rate_plan_id'] != '0')
			{

				$this->ci->load->library('rate');
				$rate_array = $this->ci->rate->get_rate_array(
												$booking['rate_plan_id'], 
												$selling_date, 
												$selling_date, 
												$booking['adult_count'], 
												$booking['children_count']
												);
				if (isset($rate_array[0]))
				{
					$rate = $rate_array[0];

                    if ($company['allow_free_bookings'] == '1' && (!$rate['charge_type_id'] || $rate['charge_type_id'] == NULL || $rate['charge_type_id'] == '0')) {

                        // do nothing as allow_free_bookings is true and charge type is not set, no need to run night audit for this booking

                    } else {
                        // In case there's a bug where Booking is assigned with DELETED rate plan.
                        // That will halt the entire night audit.

                        if(
                            isset($booking['is_ota_booking']) && 
                            $booking['is_ota_booking']
                        ) {

                            $oxc_data = $this->ci->Channex_model->get_channex_extra_charges($booking['company_id']);

                            if(
                                isset($oxc_data['is_extra_charge']) &&
                                $oxc_data['is_extra_charge']
                            ) {
                                $rate['charge_type_id'] = SYSTEM_ROOM_NO_TAX;
                            }
                        }

                        
                        if (isset($rate['charge_type_id']))
                        {
                            $charge_data[] = Array(
                                'description' => $rate['rate_plan_name'],
                                'charge_type_id' => $rate['charge_type_id'],
                                'amount' => $rate['rate'],
                                'booking_id' => $booking_id,
                                'user_id' => $user_id,
                                'pay_period' => DAILY,
                                'is_night_audit_charge' => 1,
                                'folio_id' => $folio_id
                            );
                        }
                    }
				}
				
			}
			else
			{
				
				//$charge_type = $this->ci->Charge_type_model->get_room_charge_type($company_id);
				$charge_type_id = $this->ci->Charge_type_model->get_room_charge_type_id($booking_id);

                if ($company['allow_free_bookings'] == '1' && (!$charge_type_id || $charge_type_id == NULL || $charge_type_id == '0')) {

                    // do nothing as allow_free_bookings is true and charge type is not set, no need to run night audit for this booking

                } else {
                    // if the booking doesn't have charge_type_id associated to it for some reason, use default room charge type
                    if ($charge_type_id == NULL || $charge_type_id == '0')
                    {
                        $charge_type_id = $default_room_charge_type_id;
                    }
                    $amount = $booking['rate'];
                    if($booking['pay_period'] == DAILY) // charge daily
                    {
                        $last_room_charge = $this->ci->Charge_model->get_last_applied_charge($booking_id, $charge_type_id, null, true);
                        $new_charge_start_date = null;
                        if(!$last_room_charge)
                        {
                            $new_charge_start_date = date('Y-m-d', max(strtotime($selling_date), strtotime($booking['check_in_date'])));
                        }
                        else
                        {
                            $new_charge_start_date = date('Y-m-d', strtotime("+1 day", strtotime($last_room_charge['selling_date'])));
                            if($last_room_charge['pay_period'] != $booking['pay_period'] && $last_room_charge['pay_period'] == DAILY)
                            {
                                $new_charge_start_date = date('Y-m-d', strtotime($last_room_charge['selling_date']."+1 day"));
                            }
                            elseif($last_room_charge['pay_period'] != $booking['pay_period'] && $last_room_charge['pay_period'] == WEEKLY)
                            {
                                $new_charge_start_date = date('Y-m-d', strtotime($last_room_charge['selling_date']."+7 day"));
                            }
                            elseif($last_room_charge['pay_period'] != $booking['pay_period'] && $last_room_charge['pay_period'] == MONTHLY)
                            {
                                $new_charge_start_date = date('Y-m-d', strtotime($last_room_charge['selling_date']."+1 month"));
                            }
                        }

                        if($new_charge_start_date <= $selling_date)
                        {
                            $charge_data[] = Array(
                                'description' => "Daily ".$company['default_charge_name'],
                                'charge_type_id' => $charge_type_id,
                                'amount' => $amount,
                                'booking_id' => $booking_id,
                                'user_id' => $user_id,
                                'pay_period' => DAILY,
                                'is_night_audit_charge' => 1,
                                'folio_id' => $folio_id
                            );
                        }
                    }
                    elseif($booking['pay_period'] == WEEKLY) // charge weekly
                    {
                        $last_room_charge = $this->ci->Charge_model->get_last_applied_charge($booking_id, $charge_type_id, null, true);
                        $new_charge_start_date = null;
                        if(!$last_room_charge)
                        {
                            $new_charge_start_date = date('Y-m-d', max(strtotime($selling_date), strtotime($booking['check_in_date'])));
                        }
                        else
                        {
                            $new_charge_start_date = date('Y-m-d', strtotime("+7 day", strtotime($last_room_charge['selling_date'])));
                            if($last_room_charge['pay_period'] != $booking['pay_period'] && $last_room_charge['pay_period'] == DAILY)
                            {
                                $new_charge_start_date = date('Y-m-d', strtotime($last_room_charge['selling_date']."+1 day"));
                            }
                            elseif($last_room_charge['pay_period'] != $booking['pay_period'] && $last_room_charge['pay_period'] == WEEKLY)
                            {
                                $new_charge_start_date = date('Y-m-d', strtotime($last_room_charge['selling_date']."+7 day"));
                            }
                            elseif($last_room_charge['pay_period'] != $booking['pay_period'] && $last_room_charge['pay_period'] == MONTHLY)
                            {
                                $new_charge_start_date = date('Y-m-d', strtotime($last_room_charge['selling_date']."+1 month"));
                            }
                        }


                        if($new_charge_start_date <= $selling_date && date('Y-m-d', strtotime("+7 day", strtotime($new_charge_start_date))) <= $booking['check_out_date'])
                        {
                            $charge_data[] = Array(
                                'description' => "Weekly ".$company['default_charge_name'],
                                'charge_type_id' => $charge_type_id,
                                'amount' => $amount,
                                'booking_id' => $booking_id,
                                'user_id' => $user_id,
                                'pay_period' => WEEKLY,
                                'is_night_audit_charge' => 1,
                                'folio_id' => $folio_id
                            );
                        }
                        elseif((
                                ($last_room_charge && $new_charge_start_date <= $selling_date && date('Y-m-d', strtotime("+7 day", strtotime($new_charge_start_date))) > $booking['check_out_date']) ||
                                (!$last_room_charge && date('Y-m-d', strtotime("+7 day", strtotime($new_charge_start_date))) > $booking['check_out_date']) ||
                                ($last_room_charge && $last_room_charge['pay_period'] != $booking['pay_period'] && date('Y-m-d', strtotime("+7 day", strtotime($new_charge_start_date))) > $booking['check_out_date'])
                            ) && $booking['add_daily_charge'])
                        {
                            $daily_rate = (isset($booking['pay_period']) && ($booking['pay_period'] == WEEKLY || $booking['pay_period'] == MONTHLY) && $booking['residual_rate']) ? $booking['residual_rate'] : round(($amount / 7), 2, PHP_ROUND_HALF_UP);
                            $charge_data[] = Array(
                                'description' => "Daily ".$company['default_charge_name'],
                                'charge_type_id' => $charge_type_id,
                                'amount' => $daily_rate,
                                'booking_id' => $booking_id,
                                'user_id' => $user_id,
                                'pay_period' => DAILY,
                                'is_night_audit_charge' => 1,
                                'folio_id' => $folio_id
                            );
                        }
                    }
                    elseif($booking['pay_period'] == MONTHLY) // charge monthly
                    {
                        $last_room_charge = $this->ci->Charge_model->get_last_applied_charge($booking_id, $charge_type_id, null, true);

                        $new_charge_start_date = null;
                        if(!$last_room_charge)
                        {
                            $new_charge_start_date = date('Y-m-d', max(strtotime($selling_date), strtotime($booking['check_in_date'])));
                        }
                        else
                        {
                            $new_charge_start_date = date('Y-m-d', strtotime("+1 month", strtotime($last_room_charge['selling_date'])));
                            if($last_room_charge['pay_period'] != $booking['pay_period'] && $last_room_charge['pay_period'] == DAILY)
                            {
                                $new_charge_start_date = date('Y-m-d', strtotime($last_room_charge['selling_date']."+1 day"));
                            }
                            elseif($last_room_charge['pay_period'] != $booking['pay_period'] && $last_room_charge['pay_period'] == WEEKLY)
                            {
                                $new_charge_start_date = date('Y-m-d', strtotime($last_room_charge['selling_date']."+7 day"));
                            }
                            elseif($last_room_charge['pay_period'] != $booking['pay_period'] && $last_room_charge['pay_period'] == MONTHLY)
                            {
                                $new_charge_start_date = date('Y-m-d', strtotime($last_room_charge['selling_date']."+1 month"));
                            }
                        }

                        if($new_charge_start_date <= $selling_date && date('Y-m-d', strtotime("+1 month", strtotime($new_charge_start_date))) <= $booking['check_out_date'])
                        {
                            $charge_data[] = Array(
                                'description' => "Monthly ".$company['default_charge_name'],
                                'charge_type_id' => $charge_type_id,
                                'amount' => $amount,
                                'booking_id' => $booking_id,
                                'user_id' => $user_id,
                                'pay_period' => MONTHLY,
                                'is_night_audit_charge' => 1,
                                'folio_id' => $folio_id
                            );
                        }
                        elseif((
                                ($last_room_charge && $new_charge_start_date <= $selling_date && date('Y-m-d', strtotime("+1 month", strtotime($new_charge_start_date))) > $booking['check_out_date']) ||
                                (!$last_room_charge && date('Y-m-d', strtotime("+1 month", strtotime($new_charge_start_date))) > $booking['check_out_date']) ||
                                ($last_room_charge && $last_room_charge['pay_period'] != $booking['pay_period'] && date('Y-m-d', strtotime("+1 month", strtotime($new_charge_start_date))) > $booking['check_out_date'])
                            ) && $booking['add_daily_charge'])
                        {
                            $daily_rate = (isset($booking['pay_period']) && ($booking['pay_period'] == WEEKLY || $booking['pay_period'] == MONTHLY) && $booking['residual_rate']) ? $booking['residual_rate'] : round(($amount / 30), 2, PHP_ROUND_HALF_UP);
                            $charge_data[] = Array(
                                'description' => "Daily ".$company['default_charge_name'],
                                'charge_type_id' => $charge_type_id,
                                'amount' => $daily_rate,
                                'booking_id' => $booking_id,
                                'user_id' => $user_id,
                                'pay_period' => DAILY,
                                'is_night_audit_charge' => 1,
                                'folio_id' => $folio_id
                            );
                        }
                    }
                    elseif($booking['pay_period'] == ONE_TIME)
                    {
                        $last_room_charge = $this->ci->Charge_model->get_last_applied_charge($booking_id, $charge_type_id, null, true);
                        if(!$last_room_charge && $booking['check_in_date'] <= $selling_date)
                        {
                            $charge_data[] = Array(
                                'description' => "One Time Charge",
                                'charge_type_id' => $charge_type_id,
                                'amount' => $amount,
                                'booking_id' => $booking_id,
                                'user_id' => $user_id,
                                'pay_period' => ONE_TIME,
                                'is_night_audit_charge' => 1,
                                'folio_id' => $folio_id
                            );
                        }
                    }
                }
			}
			//Load remaining data used for each new night audit charge
			
			
			//Charge all extras
			$this->ci->load->model('Booking_extra_model');
			if ($booking_extras = $this->ci->Booking_extra_model->get_booking_extras($booking_id))
			{
				foreach ($booking_extras as $extra)
				{
					$extra_charge = array(
						'description' => $extra['extra_name'].(($extra['quantity'] > 1)?" (quantity: ".$extra['quantity'].")":""),
						'booking_id' => $booking_id,
						'charge_type_id' => $extra['charge_type_id'],
						'amount' => $extra['rate']*$extra['quantity'],
						'date_time' => $current_date_time,
						'selling_date' => $selling_date,
						'user_id' => $user_id,
                        'pay_period' => DAILY,
                        'is_night_audit_charge' => 1,
                        'folio_id' => $folio_id
					);
				
					if ($extra['charging_scheme'] == 'on_start_date' 
							&& $extra['start_date'] == $selling_date)							
					{
						$charge_data[] = $extra_charge;
					}
					elseif ($extra['charging_scheme'] == 'once_a_day')
					{						
						if ($extra['extra_type'] == 'item')								
						{
							if ($this->_check_if_in_range($extra['start_date'], $extra['end_date'], $selling_date))
							{								
								$charge_data[] = $extra_charge;
							}
						}
						elseif ($extra['extra_type'] == 'rental')									
						{
							if ($this->_check_if_in_range($extra['start_date'], $extra['end_date'], $selling_date, false))
							{
								$charge_data[] = $extra_charge;
							}
						}
					}
				}
			}            
		}
        
		// insert all charges in one transaction
		$response = $this->ci->Charge_model->insert_charges($company_id, $charge_data);

        $post_charge_data = $charge_data;
        $post_charge_data['company_id'] = $company_id;

        do_action('post.create.charge', $post_charge_data);
        //$this->ci->Charge_model->insert_charges($company_id, $charge_data);
        //invoice log
        foreach ($charge_data as $key => $charge) {
            $invoice_log_data = array();
            $invoice_log_data['date_time'] = gmdate('Y-m-d h:i:s');
            $invoice_log_data['booking_id'] = $charge['booking_id'];
            $invoice_log_data['user_id'] = $charge['user_id'];
            $invoice_log_data['action_id'] = ADD_CHARGE;
            $invoice_log_data['charge_or_payment_id'] = '0';
            $invoice_log_data['new_amount'] = isset($charge['amount']) ? $charge['amount'] : '';
            $invoice_log_data['log'] = 'Night Audit Charge Added';
            $this->ci->Invoice_log_model->insert_log($invoice_log_data);
        }
                
        return array('bookings_to_be_charged' => $bookings_to_be_charged, 'response' => $response);
	}
    
    function _calculate_bookings_balance($bookings_to_be_charged)
	{		
		foreach ($bookings_to_be_charged as $booking)
		{			
			//Load all possible room charge types
			$booking_id = $booking['booking_id'];
            $this->ci->Booking_model->update_booking_balance($booking_id);
        }
	}
	
	// check if $date_from_user is within the range between $start_date and $end_date
	function _check_if_in_range($start_date, $end_date, $date_from_user, $include_end_date = true)
	{
	  // Convert to timestamp
	  $start_ts = strtotime($start_date);
	  $end_ts = strtotime($end_date);
	  $user_ts = strtotime($date_from_user);

	  // Check that user date is between start & end
	  if ($include_end_date)
	  {
		return (($user_ts >= $start_ts) && ($user_ts <= $end_ts));
	  }
	  else	  
	  {
		return (($user_ts >= $start_ts) && ($user_ts < $end_ts));
	  }
	}
	
}