<?php

class Booking_sources_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

	function get_booking_sources_report($date)
    {
		// extract month and year of $date
		//echo $date;
		$parts = explode('-',$date);
		$month = $parts[1];
		$year = $parts[0];		
		
		$company_id = $this->session->userdata('current_company_id');
		
		$this->db->select('
			CONCAT_WS("-", YEAR(brh.check_in_date), MONTH(brh.check_in_date)) as month, 
			count(DISTINCT b.booking_id) as booking_count,
			');
		$this->db->from('
			booking as b,
			booking_block as brh
			');
		$this->db->where("b.booking_id = brh.booking_id AND b.company_id = '$company_id'");
		$this->db->group_by('CONCAT_WS("-", YEAR(brh.check_in_date), MONTH(brh.check_in_date))');
		
		$query = $this->db->get();
		//echo $this->db->last_query();
			
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		$result =  $query->result();
		$result_array = array();
		foreach($result as $row)
		{
			$result_array[$row->month] = Array('booking_count' =>$row->booking_count);
		}
		
		// get total online reservations
		
		$this->db->select('
			CONCAT_WS("-", YEAR(brh.check_in_date), MONTH(brh.check_in_date)) as month, 
			count(DISTINCT b.booking_id) as widget_count,
			');
		$this->db->from('
			booking as b,
			booking_block as brh,
			booking_log as bl
			');
		$this->db->where("b.booking_id = brh.booking_id AND b.company_id = '$company_id' AND b.booking_id = bl.booking_id AND bl.log = 'Online reservation submitted'");
		$this->db->group_by('CONCAT_WS("-", YEAR(brh.check_in_date), MONTH(brh.check_in_date))');
		
		$query = $this->db->get();
			
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
		
		$result =  $query->result();
		
		foreach($result as $row)
		{
			$result_array[$row->month] = $result_array[$row->month] + Array('widget_count' => $row->widget_count);
		}
		
		return $result_array;
		
    }

}