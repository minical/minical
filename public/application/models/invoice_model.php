<?php

class Invoice_model extends CI_Model {

	function __construct()
    {
        // Call the Model constructor
        parent::__construct();
		$this->load->model('Company_model');		
		$this->load->model('Charge_model');		
		$this->load->model('Payment_model');		
		
    }	

	function update_charges($charge_changes) {
		
		$this->db->trans_start();		
		
		// update charges
		if ($charge_changes) {	
			foreach ($charge_changes as $changes) {				
				$this->Charge_model->update_charge($changes);				
			}
		}
		$this->db->trans_complete();
		
	}
			
	function get_invoice_log($booking_id)
    {		
        $sql = "
			SELECT * 
			FROM booking_log as bl
			LEFT JOIN users as u ON bl.user_id = u.id
			LEFT JOIN user_profiles as up ON  u.id = up.user_id
			WHERE 
				bl.booking_id IN ($booking_id) AND				
				bl.log_type = '".INVOICE_LOG."'
			ORDER BY date_time ASC
		";
		
		$q = $this->db->query($sql);
		
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
		$result = $q->result();
		
		return $result;
    }
	
	function create_invoice($booking_id)
	{
		// get the max invoice number
		$this->db->select("max(invoice_number) + 1 as number");
		$this->db->from("booking as b1, booking as b2, booking_x_invoice as bxi, invoice as i");
		$this->db->where("b1.booking_id = '$booking_id'");
		$this->db->where("b1.company_id = b2.company_id");
		$this->db->where("bxi.booking_id = b2.booking_id");
		$this->db->where("bxi.invoice_id = i.invoice_id");
		
		$query = $this->db->get();
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
		
		$result = $query->result();

		if (isset($result[0]->number))
			$new_invoice_number = $result[0]->number;
		else
			$new_invoice_number = 1; // first invoice

		$data = array (
			'invoice_number' => $new_invoice_number
		);
		$this->db->insert('invoice', $data);
		
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
		//$invoice_id = $this->db->insert_id();
        $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
        $result = $query->result_array();
        if(isset($result[0]))
        {
          $invoice_id = $result[0]['last_id'];
        }
        else
        {
          $invoice_id = null;
        }
		
		$data = array (
			'invoice_id' => $invoice_id,
			'booking_id' => $booking_id
		);
		$this->db->insert('booking_x_invoice', $data);
		
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
	}

	function create_batch_invoice ($booking_ids) {

        $booking_id = $booking_ids && count($booking_ids) > 0 ? $booking_ids[0] : null;
        if (!$booking_id) {
            return;
        }

        // get the max invoice number
        $this->db->select("max(invoice_number) + 1 as number");
        $this->db->from("booking as b1, booking as b2, booking_x_invoice as bxi, invoice as i");
        $this->db->where("b1.booking_id = '$booking_id'");
        $this->db->where("b1.company_id = b2.company_id");
        $this->db->where("bxi.booking_id = b2.booking_id");
        $this->db->where("bxi.invoice_id = i.invoice_id");

        $query = $this->db->get();
        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());

        $result = $query->result();

        if (isset($result[0]->number))
            $new_invoice_number = $result[0]->number;
        else
            $new_invoice_number = 1; // first invoice

        $data = array();
        foreach ($booking_ids as $booking_id) {
            $data[] = array (
                    'invoice_number' => $new_invoice_number
                );
            $new_invoice_number++;
        }

        $this->db->insert_batch('invoice', $data);

        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }

        //$invoice_id = $this->db->insert_id();
        $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
        $result = $query->result_array();
        if(isset($result[0]))
        {
            $invoice_id = $result[0]['last_id'];
        }
        else
        {
            $invoice_id = null;
        }

        $data = array();
        foreach ($booking_ids as $booking_id) {
            $data[] = array (
                'invoice_id' => $invoice_id,
                'booking_id' => $booking_id
            );
            $invoice_id++;
        }
        $this->db->insert_batch('booking_x_invoice', $data);

        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
    }
	
	function get_invoice_number($booking_id)
	{
		$this->db->select("i.invoice_number");
		$this->db->from('booking_x_invoice as bxi, invoice as i');
		$this->db->where('bxi.booking_id', $booking_id);
        $this->db->where('bxi.invoice_id = i.invoice_id');
		
		$query = $this->db->get();
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
		$result = $query->result();
		if ($query->num_rows >= 1)
		{
			return $result[0]->invoice_number;
		}
	}

    function get_booking_id_by_invoice_number($invoice_number, $company_id)
    {
        $this->db->select("bxi.booking_id");
        $this->db->from('booking_x_invoice as bxi, invoice as i, booking as b');
        $this->db->where('i.invoice_number', $invoice_number);
        $this->db->where('b.company_id', $company_id);
        $this->db->where('bxi.invoice_id = i.invoice_id');
        $this->db->where('bxi.booking_id = b.booking_id');
        
        $query = $this->db->get();
        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());
        $result = $query->result();
        if ($query->num_rows >= 1)
        {
            return $result[0]->booking_id;
        }
    }
	
	// returns true if the invoice has already been viewed
	// (log for viewing the invoice already exists)
	function has_invoice_been_viewed($booking_id)
	{

        $this->db->where('user_id', '0');
        $this->db->where('booking_id', $booking_id);
		$this->db->where('log_type', INVOICE_LOG);
        $query = $this->db->get('booking_log');
        
		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}
		
		$q = sizeof($query->result());
		return $q != 0;
	}
	function update_capture_payments($payment_id, $capture_updates)
    {
        $data = (object) $capture_updates;
        $this->db->where('payment_id', $payment_id);
        $this->db->update("payment", $capture_updates);
        //TODO: Add error if update failed.
        return TRUE;
    }

}