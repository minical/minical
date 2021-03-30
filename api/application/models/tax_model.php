<?php

class Tax_model extends CI_Model {

	function __construct()
    {        
        parent::__construct(); // Call the Model constructor
    }
	
	
    function get_tax_rates_by_charge_type_id($charge_type_id, $company_id = null) {
        
		$sql = "
                SELECT
                    ct.id as charge_type_id, t.tax_type, t.tax_type_id, tax_rate, t.is_tax_inclusive
                FROM 
                    tax_type as t, 
                    charge_type_tax_list as cttl, 
                    charge_type as ct
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
                LEFT JOIN tax_type as t ON cttl.tax_type_id = t.tax_type_id AND t.is_deleted != '1' AND t.is_percentage = '1'
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
                LEFT JOIN tax_type as t ON cttl.tax_type_id = t.tax_type_id AND t.is_deleted != '1' AND t.is_percentage = '0'
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

}

/* End of file tax_model.php */