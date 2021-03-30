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

if ( ! function_exists('get_array_with_range_of_dates'))
{
   
   // Convert $change's date intervals of changes into a range of dates		
	function get_array_with_range_of_dates($changes)
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
						if (is_string($change[$key]) || is_numeric($change[$key]))
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
				$changes_indexed_by_date[] = array('date_start'=>$date_start, 'date_end'=> $last_change['date']) + $last_change;
				//	print_r(array('date_start'=>$date_start, 'date_end'=> $last_change['date']) + $last_change);
				$date_start = $change['date'];
				
			}
			$last_change = $change;		
			
		}
		$changes_indexed_by_date[] = array('date_start'=>$date_start, 'date_end'=> $last_change['date'])+$last_change ;
		//print_r(array('date_start'=>$date_start, 'date_end'=> $last_change['date']) + $last_change);
		
		return $changes_indexed_by_date;	
	}
}


?>