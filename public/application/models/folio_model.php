<?php

class Folio_model extends CI_Model {
	
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
	
	function add_folio($data)
	{
		$data = (object) $data;        
        $this->db->insert("folio", $data);
		
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
    
    function get_folios($booking_id = null, $customer_id = null)
    {
		$where = "";
        if($booking_id) {
            $where .= " AND f.booking_id = '$booking_id'";
        }
		
		if($customer_id) {
            $where .= " AND f.customer_id = '$customer_id'";
        }
        $sql = "SELECT 
					f.*, 
					COUNT(c.charge_id) as charge_count,
					COUNT(p.payment_id) as payment_count
				FROM folio as f
                LEFT JOIN charge_folio AS cf ON cf.folio_id = f.id
				LEFT JOIN charge AS c ON c.charge_id = cf.charge_id AND c.is_deleted = '0'
                LEFT JOIN payment_folio AS pf ON pf.folio_id = f.id
                LEFT JOIN payment AS p ON p.payment_id = pf.payment_id AND p.is_deleted = '0'
                WHERE 
					f.is_deleted = '0'					
					$where
				GROUP BY f.id";
        
        
        
        $q = $this->db->query($sql);
		
		if ($this->db->_error_message())
        {
			show_error($this->db->_error_message());
		}
		
        //return result set as an associative array
        $result = $q->result_array();

        return $result;
    }

    function folios($folio_id,$booking_id,$customer_id) {
        if($folio_id == "Expedia EVC")
        {
            $where = 'folio_name = "'.$folio_id.'"';
        }
        else
        {
            $where = 'id != '.$folio_id;
        }
        
        $sql = "
                SELECT * 
                FROM folio 
                WHERE $where AND 
                booking_id = '$booking_id' AND 
                customer_id = '$customer_id' AND 
                is_deleted = '0'";
        $q = $this->db->query($sql);
        if ($this->db->_error_message()) {
            show_error($this->db->_error_message());
        }
        if ($q) {
            $result = $q->result_array();
            return $result;
        }
        return 0;
    }


    function remove_folio($folio_id) 
	{
        $data['is_deleted'] = '1';
        $this->db->where('id', $folio_id);
        $this->db->update("folio", $data);
    }

    function update_folio_charge_or_payment($data)
    {
        if(!empty($data['charge_id'])) {

            $this->db->select('*');
            $this->db->where("charge_id", $data['charge_id']);
            $query = $this->db->get('charge_folio');

            if($query->row_array()) {
                $this->db->where("charge_id", $data['charge_id']);
                $this->db->update("charge_folio", array("folio_id" => $data['folio_id']));
                if($this->db->affected_rows()) {
                    return true;
                }
            } else {
                $insert_data = array('charge_id' => $data['charge_id'], 'folio_id' => $data['folio_id']);
                $this->db->insert("charge_folio", $insert_data); 
                if($this->db->affected_rows()) {
                    return true;
                } 
            }
                
        }
        elseif (!empty($data['payment_id'])) {

            $this->db->select('*');
            $this->db->where("payment_id", $data['payment_id']);
            $query = $this->db->get('payment_folio');

            if($query->row_array()) {
                $this->db->where("payment_id", $data['payment_id']);
                $this->db->update("payment_folio", array("folio_id" => $data['folio_id']));
                if($this->db->affected_rows()) {
                    return true;
                }
            } else {
                $insert_data = array('payment_id' => $data['payment_id'], 'folio_id' => $data['folio_id']);
                $this->db->insert("payment_folio", $insert_data); 
                if($this->db->affected_rows()) {
                    return true;
                } 
            }
        }
    }

    function update_folio($folio_id, $data)
    {
        $this->db->where("id", $folio_id);
        $this->db->update("folio", $data);
        if($this->db->affected_rows()) {
            return true;
        }
    }

    function update_invoice_logs($data) 
    {
        if(!empty($data['charge_id'])) {
            $this->db->where("invoice_log_id", $data['charge_id']);
        }
        elseif (!empty($data['payment_id'])) {
            $this->db->where("invoice_log_id", $data['payment_id']);
        }
    }

    function get_folio_details($folio_id)
    {
        $this->db->where("id", $folio_id);
        $this->db->where("is_deleted", '0');
        $query = $this->db->get('folio');

        if ($query) {
            $result = $query->row_array();
            return $result;
        }
        return 0;
    }
}
