<?php

class Wizard_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }


    function get_current_guider($user_id)
    {
    	$this->db->from('user_x_guider as uxg, guider as g');
    	$this->db->where('uxg.user_id', $user_id);
    	$this->db->where('uxg.current_guider_id = g.guider_id');
        $query = $this->db->get();
		return $query->row_array(0);
    }

	function create_user_x_guider($user_id)
	{
		$data = array(
			'user_id' => $user_id,
			'current_guider_id' => 0,
			'completed_at' => NULL
		);
		$insert = $this->db->insert('user_x_guider', $data);
	}

	function get_user_x_guider($user_id)
	{
		$this->db->where('user_id', $user_id);
        $query = $this->db->get('user_x_guider');
		return $query->row_array(0);
	}

	function get_guider($guider_id)
	{
		$this->db->where('guider_id', $guider_id);
		$query = $this->db->get('guider');
		return $query->row_array(0);
	}

	function get_guiders_by_path($path)
	{
		$this->db->where('location', $path);
		$query = $this->db->get('guider');
		return $query->result_array();
	}

	function count_guiders()
	{
		$query = $this->db->from('guider');
		return $query->count_all_results();
	}

	function guiders_left_on_current_path($guider_id, $path)
	{
		$this->db->where('guider_id >', $guider_id);
		$this->db->where('location', $path);
		$query = $this->db->get('guider');
		return $query->result_array();
	}

	function get_all_guiders()
	{
		$query = $this->db->get('guider');
		return $query->result_array();
	}

	function next_guider($user_id)
	{
		$this->db->where('guider_id', $this->get_user_x_guider($user_id)['current_guider_id'] + 1);
		$query = $this->db->get('guider');
		return $query->row_array(0);
	}

	function advance_guider($user_id)
	{
		$this->db->where('user_id', $user_id);
		$this->db->set('current_guider_id', 'current_guider_id+1', FALSE);
		$this->db->update('user_x_guider');
	}

	function completed($user_id)
	{
		$this->db->where('user_id', $user_id);
		$this->db->set('completed_at', date('Y-m-d H:i:s'));
		$this->db->update('user_x_guider');
	}
}
