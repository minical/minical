<?php
class Date_range_model extends CI_Model {

	function __construct()
	{
		 parent::__construct();
	}
	
	function create_date_range($data)
	{
		
		$this->db->insert('date_range', $data);		
		//echo $this->db->last_query();
		if ($this->db->_error_message()) 
		{
			show_error($this->db->_error_message());
		}
		else 
		{
			return $this->db->insert_id();
      
		}
	}
	
	function create_date_range_x_room_type($data)
	{
		
		$this->db->insert('date_range_x_room_type', $data);		
		
		if ($this->db->_error_message()) 
		{
			show_error($this->db->_error_message());
		}
		else 
		{
			return $this->db->insert_id();
      
		}
	}

	function create_date_range_x_room_type_x_channels($data)
	{
		$this->db->insert('date_range_x_room_type_x_channel', $data);		
		
		if ($this->db->_error_message()) 
		{
			show_error($this->db->_error_message());
		}
		else 
		{
			return $this->db->insert_id();
		}
	}
    function create_date_range_x_room_type_x_status($data)
	{
		$this->db->insert('date_range_x_room_type_x_status', $data);		
		
		if ($this->db->_error_message()) 
		{
			show_error($this->db->_error_message());
		}
		else 
		{
			return $this->db->insert_id();
      
		}
	}
	
	function create_date_range_x_extra_rate($data)
	{
		
		$this->db->insert('date_range_x_extra_rate', $data);		
		
		if ($this->db->_error_message()) 
		{
			show_error($this->db->_error_message());
		}
		else 
		{
			return $this->db->insert_id();
      
		}
	}

	function create_date_range_x_rate($data)
	{
		
		$this->db->insert('date_range_x_rate', $data);		
		
		if ($this->db->_error_message()) 
		{
			show_error($this->db->_error_message());
		}
		else 
		{
			return true;;
			
		}
	}
        
        // Insert date-range-x for supplied rate
        function create_date_range_x_rate_supplied($data)
	{
		$this->db->insert('date_range_x_rate_supplied', $data);		
		
		if ($this->db->_error_message()) 
		{
			show_error($this->db->_error_message());
		}
		else 
		{
      return $this->db->insert_id();
                        
		}
	}
}

/* End of file - rate_model.php */
