<?php

/**
 * More simplified date stuff
 * by Bob Sawyer / Pixels and Code
 *
 * @access    public
 * @param    string
 * @param    integer
 * @return    integer
 */    
if ( ! function_exists('setDate'))
{
    function setDate($datestr = '',$format = 'long')
    {
        if ($datestr == '')
            return '--';
    
        $time = strtotime($datestr);
        switch ($format) {
            case 'short': $fmt = 'm / d / Y - g:iA'; break;
            case 'long': $fmt = 'F j, Y - g:iA'; break;
            case 'notime': $fmt = 'Y-m-d'; break;
        }
        $newdate = date($fmt,$time);		
        return $newdate;
    }
}

if ( ! function_exists('get_array_with_range_of_dates_iso8601'))
{
   
    // Convert $change's date intervals of changes into a range of dates in ISO 8601 format
	function get_array_with_range_of_dates_iso8601($changes, $end_date_inclusive)
	{
		if (!isset($changes))
		{
			return null;
			
		} elseif (sizeof($changes) < 1)
		{
			return null;
		}
		
		$changes_indexed_by_date = array();	
		$date_start = null;
		$last_change = null;
		foreach ($changes as $change)
		{	
			
			if ($last_change != null)
			{
				$change_detected = false;
				foreach ($change as $key => $value)
				{
					if ($key != 'date')
					{
							// compare the actual number value to 2 decimal digits.
							$change_in_two_decimal_digits = number_format(floatval($change[$key]), 2, ".", "");
							$last_change_in_two_decimal_digits = number_format(floatval($last_change[$key]), 2, ".", "");
							if ($change_in_two_decimal_digits != $last_change_in_two_decimal_digits) 
							{
								$change_detected = true;
							}
						
					}
				}
				if (	!$change_detected	&&
						$change['date'] == Date('Y-m-d', strtotime("+1 day", strtotime($last_change['date'])))
				)
				{
					$last_change = $change;
					continue;
				}
			}
			
			if ($date_start == null)
			{
				$date_start = $change['date'];
				//echo "date start set\n";
			}
			else
			{
				$changes_indexed_by_date[] = array('date_start'=>$date_start, 'date_end'=> $change['date']) + $last_change;
				//	print_r(array('date_start'=>$date_start, 'date_end'=> $last_change['date']) + $last_change);
				$date_start = $change['date'];
				
			}
			$last_change = $change;		
			
		}
		$changes_indexed_by_date[] = array('date_start'=>$date_start, 'date_end'=> $last_change['date'])+$last_change ;
		//print_r(array('date_start'=>$date_start, 'date_end'=> $last_change['date']) + $last_change);



		/* Bullshit code
		if ($end_date_inclusive)
		{
			foreach ($changes_indexed_by_date as $i => $avail)
			{
				if($changes_indexed_by_date[$i]['date_start'] != $changes_indexed_by_date[$i]['date_end'])
				{
					$date_end = $changes_indexed_by_date[$i]['date_end'];
					$new_end = Date('Y-m-d', strtotime("-1 day", strtotime($date_end)));
					$changes_indexed_by_date[$i]['date_end'] = $new_end;
					$last_date = &$changes_indexed_by_date[$i]['date_end'];
				}
			}
			// I think I need to figure out how to add the last date missing.
		}
		
		*/

		return $changes_indexed_by_date;	
	}
}

if ( ! function_exists('get_array_with_range_of_dates'))
{
   
    // Convert $change's date intervals of changes into a range of dates in the correct format
	function get_array_with_range_of_dates($changes, $ota_id)
	{
        $date_ranges = array();
		switch ($ota_id) {
			case SOURCE_ONLINE_WIDGET: // Minical's Online Booking Engine
				$date_ranges = get_array_with_range_of_dates_iso8601($changes, FALSE);break;
			case SOURCE_BOOKING_DOT_COM: // Booking.com
				$date_ranges = get_array_with_range_of_dates_iso8601($changes, FALSE);break;
			case SOURCE_EXPEDIA: // Expedia
				$date_ranges = get_array_with_range_of_dates_iso8601($changes, TRUE);break;
            case SOURCE_MYALLOCATOR:
				$date_ranges = get_array_with_range_of_dates_iso8601($changes, FALSE);break;
            case SOURCE_AGODA:
				$date_ranges = get_array_with_range_of_dates_iso8601($changes, FALSE);break;
            case SOURCE_SITEMINDER:
				$date_ranges = get_array_with_range_of_dates_iso8601($changes, TRUE);break;
            default:
				$date_ranges = get_array_with_range_of_dates_iso8601($changes, FALSE);break;
		}
        return $date_ranges;
	}
}

?>
