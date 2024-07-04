<?php

class Tax_model extends CI_Model {

	function __construct()
    {        
        parent::__construct(); // Call the Model constructor
    }
	
	function get_tax_types($company_id)
	{
		$sql = "SELECT 
					tt.*, 
					IF((MIN(tpb.tax_rate) != MAX(tpb.tax_rate)), CONCAT(MIN(tpb.tax_rate), '-', MAX(tpb.tax_rate)), MAX(tpb.tax_rate)) as tax_rate_range 
				FROM (`tax_type` as tt) 
				LEFT JOIN `tax_price_bracket` as tpb ON `tpb`.`tax_type_id` = `tt`.`tax_type_id` 
				WHERE 
					`tt`.`company_id` = '$company_id' AND 
					`tt`.`is_deleted` = 0 
				GROUP BY `tt`.`tax_type_id` 
				ORDER BY `tt`.`tax_type_id` ASC";
		
		$query = $this->db->query($sql);
		
		if ($query->num_rows >= 1) return $query->result();
		return NULL;
	}
	
	function create_tax_type($company_id, $tax_type, $rate)
	{		
		$data = array(
			'company_id' => $company_id,
			'tax_type' => $tax_type,
			'tax_rate' => $rate
		);
		
		$this->db->insert('tax_type', $data);		
		//echo $this->db->last_query();
		//return $this->db->insert_id();
    $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
    $result = $query->result_array();
    if(isset($result[0]))
    {  
      return $result[0]['last_id'];
    }
    else
    {  
      return null;
    }
	}
	
	function update_tax_type($tax_type_id, $data, $company_id = null)
	{
		$data = (object) $data;
		
		if ($company_id != null) {
			$this->db->where('company_id', $company_id);
		}
		
		$this->db->where('tax_type_id', $tax_type_id);
		$this->db->update('tax_type', $data);
		return TRUE;
	}
	
	// return total percentage of tax for the corresponding booking
	function get_tax_totals($booking_id, $statement_date = NULL) {
		$company_id = $this->session->userdata('current_company_id');       
		$where_statement_date = '';
        if($statement_date){
            $where_statement_date = " AND c.selling_date <= '$statement_date'";
        }
		// Get detailed charge information
	    $sql = "SELECT 
					tax_type,amount, 
					c.selling_date, 
					tt.is_tax_inclusive, 
					tt.charge_type_id,
					IF(
						tt.is_brackets_active,
						tt.bracket_tax_rate,
						tt.tax_rate
					)as tax_rate,
					SUM(
						IF(
							tt.is_percentage, 
							IF(
								tt.is_tax_inclusive = 1, 
								((amount * IF(tt.is_brackets_active, bracket_tax_rate, tt.tax_rate) * 0.01) / (1 + IF(tt.is_brackets_active, bracket_tax_rate, tt.tax_rate) * 0.01)), 
								(amount * (IF(tt.is_brackets_active, bracket_tax_rate, tt.tax_rate)) * 0.01)
							), 
							(IF(tt.is_brackets_active, bracket_tax_rate, tt.tax_rate))
						)
					) AS tax_total
			    FROM charge as c
			    LEFT JOIN (
				    SELECT
					    ct.id AS charge_type_id,
						t.tax_type,
						t.tax_type_id,
						t.tax_rate,
						t.is_brackets_active,
						tpb.start_range,
						tpb.end_range,
						tpb.tax_rate AS bracket_tax_rate,
						t.is_percentage,
						t.is_tax_inclusive
					FROM 
						(tax_type as t, 
						charge_type_tax_list as cttl, 
						charge_type as ct)
					LEFT JOIN tax_price_bracket AS tpb ON t.tax_type_id = tpb.tax_type_id
					WHERE 
						cttl.charge_type_id = ct.id AND
						ct.company_id = '$company_id' AND
						cttl.tax_type_id = t.tax_type_id AND
						t.is_deleted != '1' AND
						ct.is_deleted != '1'
						
					)tt ON c.charge_type_id = tt.charge_type_id
				WHERE 
					c.booking_id = '$booking_id' AND 
					tax_type != 'null' AND
					c.is_deleted != '1' AND(tt.start_range IS NULL OR c.amount BETWEEN tt.start_range AND tt.end_range) 
                    $where_statement_date
				GROUP BY tax_type_id
		";
		$q = $this->db->query($sql);
		if ($q->num_rows() > 0)
        {
			return $q->result_array();
		}
		return null;
	}
    
    function get_tax_rates_by_charge_type_id($charge_type_id, $company_id = null, $amount = null) {
        if(!$company_id)
            $company_id = $this->session->userdata('current_company_id');  
        $select_tax_rate = "t.tax_rate";
		$join_tax_brackets = "";
		// fetch tax rate from price brackets according to charge amount
		if ($amount) {
			$select_tax_rate = "IF(t.is_brackets_active, tpb.tax_rate, t.tax_rate) as tax_rate";
			$join_tax_brackets = "LEFT JOIN tax_price_bracket as tpb 
					ON tpb.tax_type_id = t.tax_type_id AND '$amount' BETWEEN tpb.start_range AND tpb.end_range";
		}
		$sql = "
                SELECT
                    ct.id as charge_type_id, t.tax_type, t.tax_type_id, t.is_percentage, t.is_tax_inclusive, $select_tax_rate
                FROM 
                    tax_type as t
                LEFT JOIN charge_type_tax_list as cttl ON cttl.tax_type_id = t.tax_type_id
                LEFT JOIN charge_type as ct ON cttl.charge_type_id = ct.id
				$join_tax_brackets
                WHERE 
                    cttl.charge_type_id = ct.id AND
                    ct.company_id = '$company_id' AND
                    cttl.tax_type_id = t.tax_type_id AND
                    t.is_deleted != '1' AND
                    ct.is_deleted != '1' AND 
                    t.tax_type != 'null' AND
                    ct.id = '$charge_type_id'
                GROUP BY t.tax_type_id
            ";
		$q = $this->db->query($sql);
		
        if ($q->num_rows() > 0)
        {
			return $q->result_array();
		}
		
		return null;
	}

	// return company's assigned nightly audit charge's tax rate. (i.e. 0.09)
	// it returns the TOTAL amount of all associated taxes
	function get_total_tax_percentage_by_charge_type_id($charge_type_id, $company_id = null, $amount = null, $is_tax_inclusive = false) 
	{
        if(!$charge_type_id) {
            return 0;
        }
		$select_tax_rate = "t.tax_rate";
		$join_tax_brackets = "";
		// fetch tax rate from price brackets according to charge amount
		if ($amount) {
			$select_tax_rate = "IF(t.is_brackets_active, tpb.tax_rate, t.tax_rate)";
			$join_tax_brackets = "LEFT JOIN tax_price_bracket as tpb 
					ON tpb.tax_type_id = t.tax_type_id AND '$amount' BETWEEN tpb.start_range AND tpb.end_range";
		}
        $where_company = $company_id ? "AND ct.company_id = '$company_id' AND t.company_id = '$company_id'" : "";
        
		$where_company .= $is_tax_inclusive ? " AND t.is_tax_inclusive = 1" : " AND t.is_tax_inclusive != 1";
		
		$sql = "
				SELECT SUM($select_tax_rate) as total_tax
				FROM charge_type_tax_list as cttl
				LEFT JOIN charge_type as ct ON ct.id = cttl.charge_type_id AND ct.is_deleted != '1'
				LEFT JOIN tax_type as t ON cttl.tax_type_id = t.tax_type_id	AND t.is_deleted != '1' AND t.is_percentage = '1'
				$join_tax_brackets
				WHERE
					'$charge_type_id' = cttl.charge_type_id 
					$where_company
				LIMIT 1;";

		$q = $this->db->query($sql);
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		$query = $q->row_array(0);
		
		$total_tax = $query['total_tax'];
		
		if ($total_tax == "")
			return 0;
		return $total_tax;
	}

	// return company's assigned nightly audit charge's tax rate. (i.e. 0.09)
	// it returns the TOTAL amount of all associated taxes
	function get_total_tax_flat_rate_by_charge_type_id($charge_type_id, $company_id = null, $amount = null, $is_tax_inclusive = false) 
	{
        if(!$charge_type_id) {
            return 0;
        }
		$select_tax_rate = "t.tax_rate";
		$join_tax_brackets = "";
		// fetch tax rate from price brackets according to charge amount
		if ($amount) {
			$select_tax_rate = "IF(t.is_brackets_active, tpb.tax_rate, t.tax_rate)";
			$join_tax_brackets = "LEFT JOIN tax_price_bracket as tpb 
					ON tpb.tax_type_id = t.tax_type_id AND '$amount' BETWEEN tpb.start_range AND tpb.end_range";
		}
        $where_company = $company_id ? "AND ct.company_id = '$company_id' AND t.company_id = '$company_id'" : "";
        
		$where_company .= $is_tax_inclusive ? " AND t.is_tax_inclusive = 1" : " AND t.is_tax_inclusive != 1";
		
		$sql = "
				SELECT SUM($select_tax_rate) as total_tax
				FROM charge_type_tax_list as cttl
				LEFT JOIN charge_type as ct ON ct.id = cttl.charge_type_id AND ct.is_deleted != '1'
				LEFT JOIN tax_type as t ON cttl.tax_type_id = t.tax_type_id	AND t.is_deleted != '1' AND t.is_percentage = '0'
				$join_tax_brackets
				WHERE
					'$charge_type_id' = cttl.charge_type_id 
					$where_company
				LIMIT 1;";

		$q = $this->db->query($sql);
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		$query = $q->row_array(0);
		
		$total_tax = $query['total_tax'];
		
		if ($total_tax == "")
			return 0;
		return $total_tax;
	}
	
	function get_accounting_taxes ($start_date, $end_date, $include_cancelled_bookings = false)
	{
		$where_condition = " b.state < '3' AND";
		if($include_cancelled_bookings)
		{
			$where_condition = " b.state <= '5' AND";
		}
		$company_id = $this->session->userdata('current_company_id');
		$sql = "
				SELECT DISTINCT tt.tax_type_id, tt.tax_type
				FROM tax_type as tt
				WHERE 
					tt.company_id = '$company_id' AND
					tt.is_deleted = '0'";
        $q = $this->db->query($sql);		
		$tax_type_array = $q->result();	
		if (count($tax_type_array) < 1) {
			return null;
		}
		$str_array = Array();		
		foreach($tax_type_array as $row) {
			$str_array[] = "SUM(IF(tax_type_id='".$row->tax_type_id."', taxed_amount,' ')) AS '".$this->db->escape_str($row->tax_type)."'";
		}	
        $tax_types_str = implode(", ", $str_array);
        $company_id = $this->session->userdata('current_company_id');
		$sql = "		
			SELECT 
			    #charges.selling_date as date, 
                charges.is_tax_exempt,
                #charges.tax_rate,
				$tax_types_str
			FROM (
				SELECT 
					c.selling_date, 
					tt.tax_type, 
					tt.tax_type_id, 
					sum(IF(tt.is_percentage = 1, CAST((c.amount * IF(tt.is_brackets_active = 1, tpb.tax_rate, tt.tax_rate) * 0.01) as DECIMAL(16, 5)), IF(tt.is_brackets_active = 1, tpb.tax_rate, tt.tax_rate))) as taxed_amount ,
                    ct2.is_tax_exempt,
                    IF(tt.is_brackets_active = 1, tpb.tax_rate, tt.tax_rate) as tax_rate
				FROM 
					booking as b,
					charge as c, 
					charge_type as ct2
				LEFT JOIN charge_type_tax_list as cttl ON ct2.id = cttl.charge_type_id
				LEFT JOIN tax_type AS tt ON tt.tax_type_id = cttl.tax_type_id
				LEFT JOIN tax_price_bracket as tpb 
					ON tpb.tax_type_id = tt.tax_type_id AND tt.is_brackets_active = 1
				WHERE 				
					ct2.company_id = '$company_id' AND
					ct2.id = c.charge_type_id AND
					c.is_deleted = '0'";
			
			if($start_date && $end_date){
				$sql .=" AND c.selling_date >= '$start_date' AND c.selling_date <= '$end_date'";
			}					
			
			$sql .="AND c.booking_id = b.booking_id AND
					b.is_deleted = '0' AND
					$where_condition
					((tt.is_brackets_active = 1 AND c.amount BETWEEN tpb.start_range AND tpb.end_range) OR (tt.is_brackets_active != 1))
					GROUP BY c.selling_date, tt.tax_type_id					
				) as charges
			#GROUP BY charges.selling_date";
			
		$q = $this->db->query($sql);
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
        $rows = "";
		if ($q)
			$rows = $q->result_array()[0];	
		
		return $rows;
	}
	
	// for monthly tax report
	function get_monthly_taxes($date="", $include_cancelled_bookings = false)
	{
		$where_condition = " b.state < '3' AND";
		if($include_cancelled_bookings)
		{
			$where_condition = " b.state <= '5' AND";
		}
		
		if ($date == "") 
			$date = date("Y-m-d");
		$parts = explode('-',$date);
		$month = $parts[1];
		$year = $parts[0];	
		
		$company_id = $this->session->userdata('current_company_id');
		
		$sql = "
				SELECT DISTINCT tt.tax_type_id, tt.tax_type
				FROM tax_type as tt
				WHERE 
					tt.company_id = '$company_id' AND
					tt.is_deleted = '0';
					
				";
                
		$q = $this->db->query($sql);		
		$tax_type_array = $q->result();	
		
		if (count($tax_type_array) < 1)
		{
			return null;
		}
		
		$str_array = Array();		
		foreach($tax_type_array as $row)
		{
			$str_array[] = "SUM(IF(tax_type_id='".$row->tax_type_id."', taxed_amount,' ')) AS '".$this->db->escape_str($row->tax_type)."'";
		}	

		$tax_types_str = implode(", ", $str_array);

		if($this->vendor_id == 21){
			$select_taxes = "SUM(IF(tt.is_percentage = 1, c.amount - (c.amount / (1 + (tt.tax_rate * 0.01))),  tt.tax_rate)) AS taxed_amount";
		} else {
			$select_taxes = "sum(IF(tt.is_percentage = 1, CAST((c.amount * IF(tt.is_brackets_active = 1, tpb.tax_rate, tt.tax_rate) * 0.01) as DECIMAL(16, 5)), IF(tt.is_brackets_active = 1, tpb.tax_rate, tt.tax_rate))) as taxed_amount";
		}

		$company_id = $this->session->userdata('current_company_id');
		$sql = "		
			SELECT 
				charges.selling_date as date, 
                charges.is_tax_exempt,
                charges.tax_rate,
				$tax_types_str
			FROM (
				SELECT 
					c.selling_date, 
					tt.tax_type, 
					tt.tax_type_id, 
					$select_taxes,
                    ct2.is_tax_exempt,
                    IF(tt.is_brackets_active = 1, tpb.tax_rate, tt.tax_rate) as tax_rate
				FROM 
					booking as b,
					charge as c, 
					charge_type as ct2
				LEFT JOIN charge_type_tax_list as cttl ON ct2.id = cttl.charge_type_id
				LEFT JOIN tax_type AS tt ON tt.tax_type_id = cttl.tax_type_id
				LEFT JOIN tax_price_bracket as tpb 
					ON tpb.tax_type_id = tt.tax_type_id AND tt.is_brackets_active = 1
				WHERE 				
					ct2.company_id = '$company_id' AND
					ct2.id = c.charge_type_id AND
					c.is_deleted = '0' AND
					MONTH(c.selling_date) = '$month' AND 
					YEAR(c.selling_date) = '$year' AND
					c.booking_id = b.booking_id AND
					b.is_deleted = '0' AND
					$where_condition
					((tt.is_brackets_active = 1 AND c.amount BETWEEN tpb.start_range AND tpb.end_range) OR (tt.is_brackets_active != 1))
				GROUP BY c.selling_date, tt.tax_type_id					
			
				) as charges
			GROUP BY charges.selling_date
				";
		
		$q = $this->db->query($sql);
				
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

			
		$rows = "";
		if ($q)
			$rows = $q->result_array();	
                
		$result = Array();
		foreach ($rows as $row)
		{
			$date = $row['date'];
			unset($row['date']);
			$result[$date] = $row;
		}
		return $result;
	}

    function create_new_tax_type($data){

        $this->db->insert('tax_type', $data);
        $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
        $result = $query->result_array();
        if(isset($result[0]))
        {
            return $result[0]['last_id'];
        }
        else
        {
            return null;
        }
    }

    function get_tax_type_by_name($name){

        $this->db->from('tax_type as tt');
        $this->db->where('tt.tax_type = ', $name);
        $this->db->where('tt.company_id', $this->company_id);
        $this->db->where('tt.is_deleted', 0);
        $query = $this->db->get();
        $result = $query->result_array();

        if(isset($result[0]))
        {
            return $result[0]['tax_type_id'];
        }
        else
        {
            return null;
        }
    }

    function delete_tax_types($company_id){

        $data = Array('is_deleted' => 1);

        $this->db->where('company_id', $company_id);
        $this->db->update("tax_type", $data);

        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
    }

}

/* End of file tax_model.php */
