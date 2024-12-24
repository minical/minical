<?php

class Payment_model extends CI_Model {

    /**
     * @var CI_Controller
     */
    private $ci;

	function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        $this->ci =& get_instance();
    }

	// called from invoice_model... and somewhere else...
	function update_payment($payment_id, $data, $company_id = null)
    {
        $data = (object) $data;		
		if ($company_id != null) {
			$this->db->where('company_id', $company_id);
	 }
		
        $this->db->where('payment_id', $payment_id);
        $this->db->update("payment", $data);
    }
	
	function get_payment_types($company_id)
    {       
        $this->db->where('company_id', $company_id);
		$this->db->where('is_deleted', 0);
		$this->db->where('is_read_only', 0); // read_only payment types are not shown
		
		$query = $this->db->get('payment_type');
		
	    if ($query->num_rows >= 1) 
		{	
			return $query->result();
		}
		
		return NULL;
    }
		
	function create_payment_type($company_id, $payment_type, $is_read_only = 0)
    {
        $data = array (
			'company_id' => $company_id,
			'payment_type' => $payment_type,
            'is_read_only' => $is_read_only
		);
		
		$this->db->insert('payment_type', $data);    
    
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
    // insert_id function won't work as it converts id(bigint) to int, results in incorrect value
//        return $this->db->insert_id();
    }
	
	function update_payment_type($payment_type_id, $data, $company_id = null)
	{
		$data = (object) $data;		
		if ($company_id != null) {
			$this->db->where('company_id', $company_id);
		}
		
		$this->db->where('payment_type_id', $payment_type_id);
		$this->db->update('payment_type', $data);
		
		//TO DO; Error if update fail.
		return TRUE;
	}
     
    function delete_payment_type($company_id = null)
    {
        $this->db->where('company_id', $company_id);
        $this->db->delete('payment_type');
    }

    function insert_payment($data, $cvc = null, $manual_payment_capture = false)
    {
        $payment_id = null;
        $error                         = null;
        $gateway_charge_id             = null;
        $payment_type_id               = &$data['payment_type_id'];
        $use_gateway                   = ($payment_type_id == 'gateway');
        $customer_id                   = isset($data['customer_id']) ? $data['customer_id'] : null;
        // make charge
        if ($use_gateway) {

            $payments_gateways = json_decode(PAYMENT_GATEWAYS, true);
            $selected_payment_gateway = $data['selected_gateway'];

            $new_payment_gateway = false;

            if(!in_array($selected_payment_gateway, $payments_gateways)){
                $new_payment_gateway = true;
            }

            if($new_payment_gateway){
                $this->ci->load->library('../extensions/'.$this->current_payment_gateway.'/libraries/ProcessPayment');
                $payment_gateway_credentials = $this->processpayment->getGatewayCredentials();

                $payment_type    = $this->ci->processpayment->getPaymentGatewayPaymentType($selected_payment_gateway);
                $payment_type_id = $payment_type['payment_type_id'];
                $capture_type = isset($manual_payment_capture) && $manual_payment_capture ? false : true;
                $gateway_charge_id = $this->ci->processpayment->createBookingCharge(
                    $data['booking_id'],
                    abs($data['amount']), // in cents, only positive
                    $customer_id,
                    $cvc,
                    $capture_type
                );
                $error = $this->ci->processpayment->getErrorMessage();
                
            } else {
                $this->ci->load->library('PaymentGateway');
                $payment_gateway_credentials = $this->paymentgateway->getGatewayCredentials();
                $selected_payment_gateway = "Stripe";
                if(isset($payment_gateway_credentials['selected_payment_gateway'])){
                    $selected_payment_gateway = $payment_gateway_credentials['selected_payment_gateway'];
                }
                
                $payment_type    = $this->ci->paymentgateway->getPaymentGatewayPaymentType($selected_payment_gateway);
                $payment_type_id = $payment_type['payment_type_id'];
                $capture_type = isset($manual_payment_capture) && $manual_payment_capture ? false : true;
                $gateway_charge_id = $this->ci->paymentgateway->createBookingCharge(
                    $data['booking_id'],
                    abs($data['amount']) * 100, // in cents, only positive
                    $customer_id,
                    $cvc,
                    $capture_type
                );
                $error = $this->ci->paymentgateway->getErrorMessage();
            }            
        }

        // mark payment as used gateway
        if ($use_gateway and !$error and $gateway_charge_id) {
            $data['payment_gateway_used'] = $this->ci->paymentgateway->getSelectedGateway();
            $data['gateway_charge_id'] = $gateway_charge_id;
            $data['is_captured'] = isset($manual_payment_capture) && $manual_payment_capture ? 0 : 1;

            $data['description'] = isset($data['description']) && $data['description'] ? $data['description'].'<br/>' : '';
            if(isset($data['payment_gateway_used']) && $data['payment_gateway_used'] == 'MonerisGateway') {
            	$desc = explode(';', $gateway_charge_id);
            	$data['description'] .= isset($desc[1]) && $desc[1] ? $desc[1] : '';
            }
            elseif(isset($data['payment_gateway_used']) && $data['payment_gateway_used'] == 'CieloGateway') {
            	$desc = explode('=', $gateway_charge_id);
            	$data['description'] .= isset($desc[0]) && $desc[0] ? $desc[0] : '';
            	if(isset($desc[1]) && $desc[1]){
            		$data['gateway_charge_id'] = $desc[1];
            	}
            }
        }

        // insert payment
        if (!$error) {
            $data['payment_status'] = 'charge';
            unset($data['selected_gateway']);
            $this->db->insert('payment', $data);            
            $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
            $result = $query->result_array();
            if(isset($result[0]))
            {
                $payment_id = $result[0]['last_id'];
            }
            $error = $this->db->_error_message();
        }
        
        // show error
        if (!empty($error)) {
            //show_error($error);
            if($error == "QuickBooks payments authorizations has been expired, please re-authorize your Quickbooks Payments. Click 'OK' to re-authorize.")
                return array("success" => false, "message" => $error, 'expire' => true, "payment_id" => $payment_id);
            else
                return array("success" => false, "message" => $error, "payment_id" => $payment_id);
        }
        return array("success" => true, "payment_id" => $payment_id ,"gateway_charge_id"=>$gateway_charge_id);
    }

    function get_payment_total_by_date_range($date_start, $date_end, $employee_id = '', $customer_type_id = null, $only_include_cancelled_bookings = false, $not_include_cancelled_bookings = false)
    {

        $company_id = $this->session->userdata('current_company_id');

        $employee_sql = "";
        if ($employee_id != '') {
            $employee_sql = "AND p.user_id = '$employee_id'";
		}


		$customer_type_sql = "";
		if ($customer_type_id == -1) {
			$customer_type_sql = "AND cut.id IS NULL";
		} elseif ($customer_type_id != "") {
			$customer_type_sql = "AND c.customer_type_id = '$customer_type_id'";
		}
		
		$where = "";
		if($only_include_cancelled_bookings)
		{
			$where = " AND (b.state = '4' OR b.state = '5')";
		}
		if($not_include_cancelled_bookings)
		{
			$where = "  AND b.state < '3'";
		}
        // get payment total
        $sql = "select
	        pt.payment_type as payment_type,
	        pt.payment_type_id,
	        SUM(p.amount) as amount
        FROM 
        	payment as p, 
        	payment_type as pt, 
        	booking as b
        LEFT JOIN customer as c ON b.booking_customer_id = c.customer_id AND c.is_deleted = '0'
        LEFT JOIN customer_type as cut ON c.customer_type_id = cut.id AND cut.is_deleted = '0'
        WHERE
			p.payment_type_id = pt.payment_type_id AND
			p.selling_date >= '$date_start' AND
			p.selling_date <= '$date_end' AND
			p.is_deleted = '0' AND
			pt.company_id = '$company_id' AND
			b.booking_id = p.booking_id AND
			b.is_deleted = '0' $where
			$employee_sql $customer_type_sql 
		GROUP BY pt.payment_type_id";

        $data = array();
        $q    = $this->db->query($sql);

        //echo $this->db->last_query();

        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
        }

        return $data;
	}

	function get_total_payments($start_date, $end_date, $selling_date, $type = 'room_wise')
    {

        $company_id = $this->session->userdata('current_company_id');

        $join = "";
        $select = 'brh.room_id';
        $outer_select = 'x.room_id';
        $key = "room_id";
        $group = 'x.room_id';

        if($type == 'roomtype_wise'){
            $key = "room_type_id";
            $group = 'x.room_type_id';
            $select = 'r.room_type_id';
            $outer_select = 'x.room_type_id';
            $join = 'LEFT JOIN room as r ON r.room_id = brh.room_id';
        }

        // get payment total
        $sql = "SELECT 
					$outer_select,
					SUM(x.amount) as total_payment
				FROM (
					SELECT
				        (p.amount) as amount,
				        $select
				        FROM 
				        	payment as p, 
				        	payment_type as pt, 
				        	booking as b
				        
				        LEFT JOIN booking_block as brh ON brh.booking_id = b.booking_id
				        $join
				        WHERE
							p.payment_type_id = pt.payment_type_id AND
							p.selling_date >= '$start_date' AND
							p.selling_date <= '$end_date' AND
							p.is_deleted = '0' AND
							pt.company_id = '$company_id' AND
							b.company_id = '$company_id' AND
							b.booking_id = p.booking_id AND
							b.is_deleted = '0'
					) as x
					GROUP BY $group";

        $data = array();
        $q    = $this->db->query($sql);

        //echo $this->db->last_query();

		if ($q->num_rows >= 1) 
		{	
			foreach ($q->result_array() as $row)
			{
				$data[$row[$key]] = $row['total_payment'];
			}
		}

        return $data;
	}
	
	/*	Returns payment data for Daily Report
		Param:
			employee_id is entered, return only payments total done by that employee
			customer_type is to distinguish between CORPORATE CUSTOMER and everyone else
	*/

    function get_all_payments($start_date, $end_date, $customer_type_id = null)
    {
		$company_id = $this->session->userdata('current_company_id');        
        $join_statement = $where_statement = "";        
        if($customer_type_id)
        {
            $join_statement = "LEFT JOIN customer as c ON pb.booking_customer_id = c.customer_id";
            $where_statement = "AND c.customer_type_id = '$customer_type_id'";
        }        
        $data  = null;
        $sql   = "

				SELECT
					date, report_table.*
				FROM
					date_interval as di
					LEFT JOIN (
						SELECT SUM(p.amount) as total_payment, p.selling_date
						FROM payment as p, payment_type as pt, booking as pb
                        $join_statement
						WHERE
								p.is_deleted = '0' AND
								pt.company_id = '$company_id' AND
								pt.payment_type_id = p.payment_type_id AND
								p.selling_date >= '$start_date' AND
								p.selling_date <= '$end_date' AND
								p.booking_id = pb.booking_id AND
								pb.state != '".DELETED."' AND
								pb.is_deleted != '1'
                                $where_statement
						GROUP BY p.selling_date
					) report_table ON report_table.selling_date = di.date
				WHERE
					di.date >= '$start_date' AND
					di.date <= '$end_date'

				";

        $query = $this->db->query($sql);

        if ($this->db->_error_message()) // error checking
        {
            show_error($this->db->_error_message());
        }

        $result = array();
        if ($query->num_rows >= 1) {
            foreach ($query->result_array() as $row) {
                $result[$row['date']] = $row['total_payment'];
            }
		}
        
        return $result;
	}
	
	// get payments sorted by date. used for monthly summary report
    
    function get_payments($booking_id, $customer_id = false, $folio_id = null, $is_first_folio = false)
    {
        $this->db->where("booking_id IN ($booking_id)");
        
		if($is_first_folio) {
            $this->db->where("(folio_id = '$folio_id' OR folio_id IS NULL OR folio_id = 0)");
		} elseif($folio_id) {
            $this->db->where('folio_id', $folio_id);
		}
		
        if ($customer_id != false)
        	$this->db->where('payment.customer_id', $customer_id);
        $this->db->where('payment.is_deleted', '0');
        $this->db->join('payment_folio' , 'payment.payment_id = payment_folio.payment_id', 'left');
        $this->db->join('payment_type', 'payment.payment_type_id = payment_type.payment_type_id', 'left');
        $this->db->join('user_profiles', 'payment.user_id = user_profiles.user_id', 'left');
        $this->db->join('customer', 'payment.customer_id = customer.customer_id', 'left');
        $this->db->select('payment.payment_id, payment.is_captured, description, customer_name, date_time, booking_id, amount, payment_status, payment.is_deleted, payment_type, payment.payment_type_id, payment.payment_gateway_used, gateway_charge_id, read_only, selling_date, CONCAT_WS(" ",first_name,  last_name ) as user_name,payment_folio.folio_id as folio_id, payment.payment_link_id');
        $this->db->order_by('selling_date', 'ASC');
        $this->db->order_by('date_time', 'ASC');
        $query = $this->db->get("payment");
		if ($this->db->_error_message())
			show_error($this->db->_error_message());
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
    }

    function get_payment_detail($date,$date_end, $employee_id = '', $only_include_cancelled_bookings = false, $not_include_cancelled_bookings = false)
    {

        $company_id   = $this->session->userdata('current_company_id');
        $employee_sql = "";
        if ($employee_id != '') {
           $employee_sql = "AND p.user_id = '$employee_id'";
        }
		
		$where = "";
		if($only_include_cancelled_bookings)
		{
			$where = " AND (b.state = '4' OR b.state = '5')";
		}
		if($not_include_cancelled_bookings)
		{
			$where = "  AND b.state < '3'";
		}
        // get payment total
        $sql = "
            select
                p.booking_id,
				r.room_name as room_name,
				cut.name as customer_type,
				pt.payment_type as payment_type,
				pt.payment_type_id, 
				p.description,
                IF(p.customer_id != 'null', c.customer_name, 
						(	SELECT customer_name FROM customer WHERE customer_id = b.booking_customer_id)
                ) as customer_name,
				p.amount
				FROM 
					payment as p
                LEFT JOIN booking as b ON b.booking_id = p.booking_id
				LEFT JOIN payment_type as pt ON pt.payment_type_id = p.payment_type_id
				LEFT JOIN booking_block as brh ON brh.booking_id = p.booking_id
                LEFT JOIN customer as c ON p.customer_id = c.customer_id
				LEFT JOIN booking_staying_customer_list as bscl ON bscl.customer_id = p.customer_id
                LEFT JOIN room as r ON brh.room_id = r.room_id
				LEFT JOIN customer_type as cut ON c.customer_type_id = cut.id
				WHERE
					p.payment_type_id = pt.payment_type_id AND
					pt.company_id = '$company_id'  AND
					p.is_deleted = '0' AND
					b.is_deleted = '0' AND
					b.booking_id = p.booking_id $employee_sql AND
					p.selling_date BETWEEN '$date' AND '$date_end'
					$where
				GROUP BY p.payment_id
				ORDER BY room_name ASC
				
		";

        $data = array();
        $q    = $this->db->query($sql);

		//echo $this->db->last_query();
        if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
        }

        return $data;
    }
	
	function get_accounting_payments ($start_date, $end_date){
		
        $company_id = $this->session->userdata('current_company_id');
        $sql = "
				SELECT DISTINCT pt.payment_type_id, pt.payment_type
				FROM payment_type as pt
				WHERE pt.company_id = '$company_id' AND
				pt.is_deleted = '0'";
        $q = $this->db->query($sql);
        $payment_type_array = $q->result();
        $str_array = $unique_payments = Array();
        foreach($payment_type_array as $row) {
            if(in_array($row->payment_type, $unique_payments)){
                continue;
            }
            $unique_payments[] = $row->payment_type;
            $str_array[] = "SUM(IF(payment_type_id='".$row->payment_type_id."', amount,' ')) AS ".$this->db->escape($row->payment_type);
			$str_array_sum[] = "SUM(IF(report_table.payment_type_id='".$row->payment_type_id."', amount,' ')) AS ".$this->db->escape($row->payment_type);
        }
        $payment_types_str = implode(", ", $str_array);
		$payment_types_str_sum = implode(", ", $str_array_sum);
        $sql = "
			SELECT date as 'Selling Date', $payment_types_str_sum
			FROM date_interval as di
			LEFT JOIN(
				SELECT payments.selling_date,payment_type_id, amount,$payment_types_str
				FROM (
					SELECT p.selling_date, pt.payment_type, p.payment_type_id as payment_type_id, SUM(p.amount) as amount
					FROM payment as p, payment_type as pt, booking as b
					WHERE
						p.is_deleted = '0' AND
						p.payment_type_id = pt.payment_type_id AND
						pt.company_id = '$company_id' AND
						p.booking_id = b.booking_id AND
						b.is_deleted = '0' ";
                        if($start_date && $end_date){
							$sql .=" AND p.selling_date >= '$start_date' AND p.selling_date <= '$end_date'";
						}
                    $sql .=	" GROUP BY payment_type_id) as payments GROUP BY payments.selling_date, payments.payment_type_id
			)report_table ON report_table.selling_date = di.date
            WHERE ";
            if($start_date && $end_date){
				$sql .= " di.date >= '$start_date' AND di.date <= '$end_date'";
			}
		$sql .= " ORDER BY date ASC";
        $q = $this->db->query($sql);
        if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
        $result = "";
        if ($q) {
            $result = $q->result_array()[0];
        }
        return $result;
	}

    function get_monthly_payment_report($date = NULL, $date_range = array(), $currency_code = null)
    {
        // extract month and year of $date
        //echo $date;
        if(empty($date_range))
        {
            if ($date == "") 
            {
                $date = date("Y-m-d");
            }

            $parts = explode('-', $date);
            $month = $parts[1];
            $year  = $parts[0];
        }
        else
        {
           $start_date = $date_range['from_date'];
           $end_date = $date_range['to_date'];
        }

		$company_id = $this->session->userdata('current_company_id');

        $sql = "
                SELECT DISTINCT pt.payment_type_id, pt.payment_type
                FROM payment_type as pt
                WHERE
                    pt.company_id = '$company_id' AND
                    pt.is_deleted = '0' AND payment_type IS NOT NULL
                ";

        $q = $this->db->query($sql);
        $payment_type_array = $q->result();

        $str_array = $unique_payments = Array();
        foreach ($payment_type_array as $row) {
            if(in_array($row->payment_type, $unique_payments)){
                continue;
            }
            $unique_payments[] = $row->payment_type;
            $str_array[] = "SUM(IF(payment_type_id='".$row->payment_type_id."', amount,' ')) AS ".$this->db->escape($row->payment_type);
        }
//        $str_array[] = "SUM(IF(payment_gateway_used='stripe', amount,' ')) AS 'Stripe'";
        $payment_types_str = implode(", ", $str_array);

        $where_currency = $currency_join = "";
        if($currency_code){
            $where_currency = " rp.rate_plan_id = b.rate_plan_id AND rp.currency_id = '$currency_code' AND ";
            $currency_join = ", rate_plan as rp ";
        }

        $sql = "
			SELECT date as 'Selling Date', report_table.*
			FROM date_interval as di
			LEFT JOIN
			(
				SELECT payments.selling_date, $payment_types_str
				FROM (
					SELECT p.selling_date, pt.payment_type, p.payment_type_id, p.amount
					FROM payment as p, payment_type as pt, booking as b $currency_join
					WHERE
						p.is_deleted = '0' AND
						p.payment_type_id = pt.payment_type_id AND
						pt.company_id = '$company_id' AND
						p.booking_id = b.booking_id AND
                        $where_currency
						b.is_deleted = '0' ";
                                        if(empty($date_range)){
                                            $sql .=" AND MONTH(p.selling_date) = '$month' AND
                                                    YEAR(p.selling_date) = '$year'";
                                        }else{
                                            $sql .=" AND p.selling_date >= '$start_date' AND
                                                    p.selling_date <= '$end_date'";
                                        }
						
                                            $sql .=	") as payments
                                    GROUP BY payments.selling_date
                                        )report_table ON report_table.selling_date = di.date
                                        WHERE ";
                                        if(empty($date_range)){
                                            $sql .= "MONTH(di.date) = '$month' AND YEAR(di.date) = '$year'";
                                        }else{
                                            $sql .= "di.date >= '$start_date' AND di.date <= '$end_date'";
                                        } 
                        $sql .= " ORDER BY date ASC";
        $q = $this->db->query($sql);

		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());


        //echo $this->db->last_query();
        $result = "";
        if ($q) {
            $result = $q->result_array();
        }

        return $result;
	}
	
	// returns array of daily total payments that belong in $date's month and year
	// NOT READY YET

	function get_booking_id_by_payment_id($payment_id) {
		$this->db->where('payment_id', $payment_id);
        $query = $this->db->get('payment');
        $q = $query->result();

		//echo $this->db->last_query();
		$result = $q[0]->booking_id;
        return $result;
	}

    /**
     * itodo error reporting
     * @param $payment_id
     */
    public function refund_payment($payment_id, $refund_amount, $payment_type, $booking_id = null, $folio_id = null)
    {
        $payment = $this->get_payment($payment_id);
        $amount = null;
        if($payment_type == 'partial' || $payment_type == 'remaining') {
            $amount = abs($refund_amount) * 100; // amount in cents
        } 
        $refund = array("success" => false, "message" => "some error occured!");
        if ($payment['payment_gateway_used'] and $payment['gateway_charge_id']) {
            $refund_payment = $payment;


            $payments_gateways = json_decode(PAYMENT_GATEWAYS, true);
            $new_payment_gateway = false;

            if(!in_array($this->selected_payment_gateway, $payments_gateways)){
                $new_payment_gateway = true;
            }

            if($new_payment_gateway){

                if($payment_type == 'partial' || $payment_type == 'remaining') {
                    $amount = abs($refund_amount); // amount in cents
                }

                $this->ci->load->library('../extensions/'.$this->current_payment_gateway.'/libraries/ProcessPayment');
                $refund = $this->ci->processpayment->refundBookingPayment($payment_id, $amount, $payment_type, $booking_id);
                
            } else {
           
                $this->ci->load->library('PaymentGateway');
                $refund = $this->ci->paymentgateway->refundBookingPayment($payment_id, $amount, $payment_type, $booking_id);
            }
            if(isset($refund['success']) && $refund['success'])
            {
                $refund_id = isset($refund['refund_id']) && $refund['refund_id'] ? $refund['refund_id'] : null;
                
                if(!$refund_id){
                    return $refund;
                }
                
                // update old payment
                $payment['read_only'] = 1;
                    if(isset($payment_type) && $payment_type == 'partial'){
                        $payment['read_only'] = 0; 
                    }
                $this->db->where('payment_id', $payment_id);
                $this->db->update("payment", $payment);

                // create refund payment
                unset($refund_payment['payment_id']);
                $refund_payment['read_only'] = 1;
                $refund_payment['gateway_charge_id'] = $refund_id;
                $refund_payment['parent_charge_id'] = $payment['gateway_charge_id'];          
                // $refund_payment['folio_id'] = $folio_id;
				
				$refund_payment['selling_date'] = $this->selling_date;
				$refund_payment['date_time'] = gmdate("Y-m-d H:i:s");
                
                if($payment_type == 'partial' || $payment_type == 'remaining')
                {
                    $refund_payment['amount'] = -$refund_amount;
                }
                elseif($payment_type == 'full')
                {
                    $refund_payment['amount'] *= -1;
                }

                if(isset($payment_type) && $payment_type == 'partial')
                {
                    $refund_payment['payment_status'] = 'partial';
                }
                else
                {
                    $refund_payment['payment_status'] = 'refund';
                }

                $this->db->insert('payment', $refund_payment);
                $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
	            $result = $query->result_array();
	            if(isset($result[0]))
	            {
	                $payment_id = $result[0]['last_id'];
					if($payment_id && $folio_id)
					{
						$this->insert_payment_folio(array('payment_id'=>$payment_id, 'folio_id'=>$folio_id));
					}
	            }
            }
        }
        return $refund;
    }


    public function void_payment($payment_id)
    {
        $folio_id = 0;
        $payment = $this->get_payment($payment_id);
        $amount = null;
        $void = array("success" => false, "message" => "some error occured!");
        if ($payment['payment_gateway_used'] and $payment['gateway_charge_id']) {
            $void_payment = $payment;
            $payments_gateways = json_decode(PAYMENT_GATEWAYS, true);
            $new_payment_gateway = false;

            if(!in_array($this->selected_payment_gateway, $payments_gateways)){
                $new_payment_gateway = true;
            }

            if($new_payment_gateway){
                $this->ci->load->library('../extensions/'.$this->current_payment_gateway.'/libraries/ProcessPayment');
                $void = $this->ci->processpayment->void_booking_payment($payment['gateway_charge_id']);
                
            } else {
           
            //     $this->ci->load->library('PaymentGateway');
            //     $void = $this->ci->paymentgateway->voidBookingPayment($payment_id, $amount, $payment_type, $booking_id);
            }

             if(isset($void['success']) && $void['success']){
                $payment_data = array(
                    'booking_id' => $payment['booking_id'],
                    'date_time' => gmdate("Y-m-d H:i:s"),
                    'description' => '',
                    'amount' => -$payment['amount'], 
                    'payment_type_id' => $payment['payment_type_id'], 
                    'credit_card_id' => $payment['credit_card_id'],
                    'selling_date' => $payment['selling_date'],
                    'is_deleted' => $payment['is_deleted'],
                    'user_id' => $payment['user_id'],
                    'customer_id' => $payment['customer_id'],
                    'payment_gateway_used' => $payment['payment_gateway_used'],
                    'gateway_charge_id' => $void['void_id'],
                    'read_only' => 1,
                    'payment_status' => 'void', 
                    'parent_charge_id' => $payment['gateway_charge_id'],
                    'is_captured' => 0,
                    'logs' => null,
                    'payment_link_id' => null
                );
                $this->db->set('read_only', 1);
                $this->db->where('payment_id', $payment['payment_id']);
                $this->db->update('payment');

                $this->db->insert('payment', $payment_data);
                $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
	            $result = $query->result_array();
	            if(isset($result[0]))
	            {
	                $payment_id = $result[0]['last_id'];
					if($payment_id && $folio_id)
					{
						$this->insert_payment_folio(array('payment_id'=>$payment_id, 'folio_id'=>$folio_id));
					}
	            }
            }
        }
        return $void;
        
    }

    function get_partial_refunds_by_charge_id($charge_id)
    {
        $this->db->where('parent_charge_id', $charge_id);
        $this->db->where('payment_status', 'partial');
        $query = $this->db->get('payment');
      
        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return null;
        }
    }
    
    function get_payment($payment_id = null, $filters = null, $gateway_charge_id = null)
    {
        if($filters)
        {
            $this->db->where($filters);
        }
        if($payment_id)
        {
            $this->db->where('payment_id', $payment_id);
        }
        if($gateway_charge_id)
        {
            $this->db->like('gateway_charge_id', $gateway_charge_id);
        }
        $query = $this->db->get('payment');

        if ($query->num_rows() > 0) {
            return $query->row_array();
        } else {
            return null;
        }
    }

    function delete_payment($payment_id)
    {
        $data['is_deleted'] = '1';
        $this->db->where('payment_id', $payment_id);
        $this->db->update("payment", $data);
    }
	
	// returns the payment_type_id of the payment that is read_only
	// used for PayPal payment types

    function get_read_only_payment_type_id_by_name($payment_type_name, $company_id)
	{
		$this->db->where('payment_type', $payment_type_name);
		$this->db->where('company_id', $company_id);
        $query = $this->db->get('payment_type');
        $q = $query->result();
		
		//echo $this->db->last_query();
		$result = $q[0]->payment_type_id;
        return $result;
	}
	
	
	// create the read_only payment type if it doesn't already exist.
	// otherwise, update the payment type's is_deleted state to 1	
	function enable_read_only_payment_type($payment_type_name, $company_id) 
	{
		$this->db->where('payment_type', $payment_type_name);
		$this->db->where('company_id', $company_id);
		$this->db->where('is_read_only = 1');
		$query = $this->db->get('payment_type');
		
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
		
		if ($query->num_rows() > 0)
		{
			// the payment type already exists. update it's is_deleted to 0
			$data['is_deleted'] = '0';
			$this->db->where('payment_type', $payment_type_name);
			$this->db->where('company_id', $company_id);
			$this->db->update("payment_type", $data);   
			
			if ($this->db->_error_message()) // error checking
				show_error($this->db->_error_message());
		}
		else
			{
			
			// the payment type doesn't exist. insert new			
			 $data = array (
				'payment_type' => $payment_type_name,
				'company_id' => $company_id,
				'is_read_only' => '1'
			);
			
			$this->db->insert('payment_type', $data);			
			if ($this->db->_error_message()) // error checking
				show_error($this->db->_error_message());
		}
	}
	
	// create the read_only payment type if it doesn't already exist.
	// otherwise, update the payment type's is_deleted state to 1	
	function disable_read_only_payment_type($payment_type_name, $company_id) 
	{
		// the payment type already exists. update it's is_deleted to 0
		$data['is_deleted'] = '1';
		$this->db->where('payment_type', $payment_type_name);
		$this->db->where('company_id', $company_id);
		$this->db->update("payment_type", $data);   
		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());
		
	}
	
    function insert_payment_folio ($data) {
		$data = (object) $data;
		$this->db->insert("payment_folio", $data);
		$query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
		$result = $query->result_array();
		if (isset($result[0])) {
			return $result[0]['last_id'];
		} else {
			return null;
		}
	}
	
	function get_payments_by_date($company_id = NULL, $date= NULL, $date_range = array(), $payment_type_id = null)
	{
		$result1 = $result2 = array();
		if(empty($date_range))
        {
            $start_date = $date;
            $end_date = $date;
        }
        else
        {
            $start_date = $date_range['from_date'];
            $end_date = $date_range['to_date'];
        }
		
		$get_payment_by_date = " p.selling_date >= '$start_date' AND p.selling_date <= '$end_date'";
		
		if($payment_type_id)
		{
			$query = "
					SELECT 
						SUM(p.amount) as payment_total, 
						count(*) as payment_count
					FROM payment as p 
					LEFT JOIN payment_type as pt ON (pt.payment_type_id = p.payment_type_id)
					LEFT JOIN booking as b ON (b.booking_id = p.booking_id)
					WHERE 
						$get_payment_by_date AND 
						p.payment_type_id = '$payment_type_id' AND 
						p.is_deleted = '0' AND 
						b.is_deleted = '0'";            
			
			$q = $this->db->query($query);
			// error checking
			if ($this->db->_error_message()) {
				show_error($this->db->_error_message());
			}

			if ($q->num_rows() > 0) {
				$result = $q->result_array();
				$arr = array();
				$arr['payment_count'] = $result[0]['payment_count'] ? $result[0]['payment_count'] : 0;
				$arr['payment_total'] = $result[0]['payment_total'] ? $result[0]['payment_total'] : 0;
				return $arr;
			}
			return null;
		} else {
			$query = "
					SELECT 
						pt.payment_type as payment_type, 
						r.room_name, 
						pf.folio_id, 
						p.amount as payment_amount, 
						pt.payment_type_id as payment_type_id
					FROM payment as p 
					LEFT JOIN payment_type as pt ON (pt.payment_type_id = p.payment_type_id)
					LEFT JOIN payment_folio as pf ON (pf.payment_id = p.payment_id)
					LEFT JOIN booking as b ON (b.booking_id = p.booking_id AND p.is_deleted != '1')
					LEFT JOIN booking_block as brh ON (brh.booking_id = b.booking_id)
					LEFT JOIN room as r ON (r.room_id = brh.room_id)
					WHERE 
						$get_payment_by_date AND
						b.company_id = '$company_id' AND 
						b.is_deleted != '1' 
					GROUP BY p.payment_id 
					ORDER BY pt.payment_type";            

			$q = $this->db->query($query);
			// error checking
			if ($this->db->_error_message()) {
				show_error($this->db->_error_message());
			}
			if ($q->num_rows() > 0) {
				$result1 = $q->result_array();
				return $result1;
			}
			return null;
		}
	}

    function get_payment_types_by_name($name, $company_id = null)
    {
        if($company_id){
            $this->db->where('company_id', $company_id); 
        }
        $this->db->where('payment_type', $name);
        $this->db->where('is_deleted', 0);
        $this->db->where('is_read_only', 0); // read_only payment types are not shown

        $query = $this->db->get('payment_type');

        if ($query->num_rows >= 1)
        {
            return $query->result();
        }

        return NULL;
    }

    function delete_payment_types($company_id)
    {
        $data = Array('is_deleted' => 1);

        $this->db->where('company_id', $company_id);
        $this->db->update("payment_type", $data);

        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }

    }


    function delete_payments($booking_id, $is_batch = false){
        $data = Array('is_deleted' => 1);

        if($is_batch)
            $this->db->where_in('booking_id', $booking_id);
        else
        $this->db->where('booking_id', $booking_id);

        $this->db->update("payment", $data);

        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
    }
}