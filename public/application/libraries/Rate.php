<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rate {
	
	public function __construct()
	{
		$this->ci =& get_instance();
		
		$this->ci->load->model('Rate_plan_model');
		$this->ci->load->model('Rate_model');
	}

	function get_rate_array($rate_plan_id = null, $date_start, $date_end, $adult_count = null, $children_count = null, $rate_plan_ids = array(), $is_future_charges = false, $is_select_less_vars = false)
    {
        if(!$rate_plan_id && count($rate_plan_ids) > 0) {
            // new optimized function that accept multiple rate plans ids in an array
            $rate_array = $this->ci->Rate_model->get_daily_rates_optimized($rate_plan_ids, $date_start, $date_end, null);
        }
        else {

            if($is_future_charges){
                $rate_array = $this->ci->Rate_model->get_daily_rates_for_future_charges($rate_plan_id, $date_start, $date_end, null);
            } else {
                $rate_array = $this->ci->Rate_model->get_daily_rates($rate_plan_id, $date_start, $date_end, null, false, $is_select_less_vars);
            }
        }
        
        // Cast once
        $adult_count    = (int) $adult_count;
        $children_count = (int) $children_count;

        foreach ($rate_array as $index => $rate) {

            if ($adult_count <= 4) {
                // Adjust children just for this rate calculation
                $adjusted_children = $this->get_children_count_adjusted($adult_count, $children_count, $rate);

                $base_rate = $rate["adult_{$adult_count}_rate"];

                $rate_array[$index]['rate'] =
                    $base_rate + ($adjusted_children * $rate['additional_child_rate']);
            } else {
                // For more than 4 adults
                $extra_adult_count = $adult_count - 4;

                $rate_array[$index]['rate'] =
                    $rate['adult_4_rate']
                    + ($extra_adult_count * $rate['additional_adult_rate'])
                    + ($children_count * $rate['additional_child_rate']);
            }
        }

        return $rate_array;
    }
    
    /**
     * gets free slots
     * for ex adult rates are 100(1) 100(2) 150(3 adults)
     * if a 1 adult 1 child move in, child will occupy the left over adult slot since he paid 100 for 2 slots
     *
     * @param $rate
     * @param $adult_count
     * @return int
     */
    
    /**
     * Counts how many following slots (up to 4) have the same price.
     * Example: adult_1_rate = 89, adult_2_rate = 89 → 1 free slot.
     */
    private function get_positions_left(array $rate, int $adult_count): int
    {
        $positions = 0;
        $current_rate = $rate["adult_{$adult_count}_rate"];

        for ($i = $adult_count + 1; $i <= 4; $i++) {
            if ($rate["adult_{$i}_rate"] == $current_rate) {
                $positions++;
            } else {
                break;
            }
        }

        return $positions;
    }

	
	function get_average_daily_rate($rates)
	{
		$rate_total = 0;
		foreach($rates as $rate)
		{
			$rate_total += $rate['rate'];
		}
		
		return (count($rates) > 0) ? $rate_total/count($rates) : $rate_total;
	}

    /**
     * @param $adult_count
     * @param $children_count
     * @param $rate
     * @return mixed
     */

    /**
     * Only reduce child count if there are true "free child slots".
     * Free child slots exist only if adults < 2 and the next slot
     * has the same price as current.
     */
    private function get_children_count_adjusted(int $adult_count, int $children_count, array $rate): int
    {
        if ($adult_count < 2) {
            $free_positions = $this->get_positions_left($rate, $adult_count);
            $children_count = max(0, $children_count - $free_positions);
        }

        return $children_count;
    }

}
