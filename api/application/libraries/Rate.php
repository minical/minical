<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rate {
	
	public function __construct()
	{
		$this->ci =& get_instance();
		
		$this->ci->load->model('Rate_plan_model');
		$this->ci->load->model('Rate_model');
	}

	function get_rate_array($rate_plan_id, $date_start, $date_end, $adult_count, $children_count)
	{
		$rate_array = $this->ci->Rate_model->get_daily_rates($rate_plan_id, $date_start, $date_end);
		
		foreach ($rate_array as $index => $rate)
		{
			if ($adult_count == '1')
			{
				$rate_array[$index]['rate'] = $rate['adult_1_rate'] + ($children_count * $rate['additional_child_rate']);
			} elseif ($adult_count == '2')
			{
				$rate_array[$index]['rate'] = $rate['adult_2_rate'] + ($children_count * $rate['additional_child_rate']);
			} elseif ($adult_count == '3')
			{
				$rate_array[$index]['rate'] = $rate['adult_3_rate'] + ($children_count * $rate['additional_child_rate']);
			} elseif ($adult_count == '4')
			{
				$rate_array[$index]['rate'] = $rate['adult_4_rate'] + ($children_count * $rate['additional_child_rate']);
			} else
			{
				$extra_adult_count = max(0, intval($adult_count) - 4);
				$rate_array[$index]['rate'] = $rate['adult_4_rate'] + ($extra_adult_count * $rate['additional_adult_rate']) + ($children_count * $rate['additional_child_rate']);
			}
		}

		return $rate_array;
	}
	
	function get_average_daily_rate($rates)
	{
		$rate_total = 0;
		foreach($rates as $rate)
		{
			$rate_total += $rate['rate'];
		}
		
		return $rate_total/count($rates);
	}
	
}
