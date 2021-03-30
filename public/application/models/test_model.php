<?php

class Test_model extends CI_Model {

	function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    function get_test_company_id()
    {
    	// DELETE EVERYTHING THAT IS RELATED TO SELENIUM TESTING
		// $company_id = 1000000; // arbitary value for test company_id (DEPRECATED)


		$this->db->where('name', 'selenium test company');
		$query = $this->db->get('company');		
		$result = $query->result_array();

		if (!isset($result[0]['company_id']))
		{
			echo "there's no more company with name \"selenium test company\"";
			return;
		}
	
		$company_id = $result[0]['company_id'];	
		
		return $company_id;

    }
	
	/*
	// set default room charge type for properties that don't have default charge type assigned
	function set_default_charge_types() {		

		$sql="
			SELECT ct2.id
			FROM
			(
				select c.company_id, count(ct.id) as default_room_charge_count
				FROM company as c
				LEFT JOIN charge_type as ct on ct.is_deleted = 0 and ct.company_id = c.company_id and ct.is_default_room_charge_type = 1
				group by c.company_id
			)cc, charge_type as ct2

			where
				default_room_charge_count = 0 and
				ct2.company_id = cc.company_id and ct2.name = 'Room Charge' and ct2.is_deleted = 0
		";

		$q = $this->db->query($sql);
		$array = $q->result_array();
		$charge_type_ids = [];
		foreach ($array as $x)
		{
			$charge_type_ids[] = $x['id'];
		}

		echo $this->db->last_query();
		
		print_r($charge_type_ids);

		if (sizeof($charge_type_ids) == 0)
			return;

		if ($this->db->_error_message())
			show_error($this->db->_error_message()); 
		
		$sql ="
			Update charge_type set is_default_room_charge_type = 1 where id IN (".implode(", ", $charge_type_ids).")
		";
		
		$this->db->query($sql);
		echo $this->db->last_query();

		if ($this->db->_error_message())
			show_error($this->db->_error_message()); 
		
	}

	function set_image_groups()
	{

		$this->load->model('Image_model');
        $this->load->model('Room_type_model');
        $this->load->model('Rate_plan_model');

        
		$sql="
			SELECT c.company_id
			FROM company as c
			WHERE c.logo_image_group_id IS NULL
		";

		$q = $this->db->query($sql);
		$companies_without_image_groups = $q->result_array();
		 
		foreach ($companies_without_image_groups as $company)
		{
			$image_type_groups = Array(
				'logo_image_group_id'      => $this->Image_model->create_image_group(LOGO_IMAGE_TYPE_ID),
	            'slideshow_image_group_id' => $this->Image_model->create_image_group(SLIDE_IMAGE_TYPE_ID),
	            'gallery_image_group_id'   => $this->Image_model->create_image_group(GALLERY_IMAGE_TYPE_ID)
			);
			$this->Company_model->update_company($company['company_id'], $image_type_groups);

			$sql="
				SELECT id
				FROM room_type
				WHERE company_id = '".$company['company_id']."'
			";

			$q = $this->db->query($sql);
			$room_types = $q->result_array();

			foreach($room_types as $room_type)
			{
				$this->Room_type_model->update_room_type(
					$room_type['id'],
					Array(
						"image_group_id" => $this->Image_model->create_image_group(ROOM_TYPE_IMAGE_TYPE_ID)
					)
				);
			}
			

			$sql="
				SELECT rp.rate_plan_id
				FROM rate_plan as rp, room_type as rt
				WHERE rp.room_type_id = rt.id AND rt.company_id = '".$company['company_id']."'
			";

			$q = $this->db->query($sql);
			$rate_plans = $q->result_array();

			foreach($rate_plans as $rate_plan)
			{
				$this->Rate_plan_model->update_rate_plan(
					Array(
						"image_group_id" => $this->Image_model->create_image_group(RATE_PLAN_IMAGE_TYPE_ID)
					),
					$rate_plan['rate_plan_id']
				);
			}

		}
	}
	*/

}