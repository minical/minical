<?php

/**
 * Class Report
 *
 * @property Room_model Room_model
 */
class Ledger extends MY_Controller {

    function __construct()
	{
		parent::__construct();
	
		$this->load->model('Company_model');				
		$this->load->model('Charge_model');				
		$this->load->model('Payment_model');
        $this->load->model('Customer_type_model');
		$this->load->model('Room_model');				
		$this->load->model('Tax_model');	
		$this->load->model('Payment_model');
		$this->load->model('User_model');
		$this->load->model('Booking_model');
        $this->load->model('Booking_sources_model');
        $this->load->model('Currency_model');
		
		$global_data['menu_on'] = true;
		$global_data['selected_menu'] = 'reports';		
		$global_data['submenu'] = 'reports/reports_submenu.php';
		$global_data['submenu_parent_url'] = base_url()."reports/";
        $global_data['sidebar_menu_url'] = base_url()."reports/ledger/";
        $global_data['menu_items'] = $this->Menu_model->get_menus(array('parent_id' => 4, 'wp_id' => 1));
        $global_data['sidebar_links'] = $this->Menu_model->get_menus(array('parent_id' => 9, 'wp_id' => 1));

		$this->load->vars($global_data);
		
	}
	
	function index() {
        if ($this->permission->check_access_to_function($this->user_id, $this->company_id, "ledger", "show_ledger_summary_report"))
        //user not authorized to use
        {
            $this->show_ledger_summary_report();
        }
        else
        {
            redirect(base_url()."reports/room/show_housekeeping_report");
        }
	}

    function show_ledger_summary_report($start_date = '', $end_date = '')
    {
        if ($start_date == '') {
            $parts      = explode('-', $this->selling_date);
            $year       = $parts[0];
            $month      = $parts[1];
            $start_date = "$year-$month-01";
        }

        if ($end_date == '') {
            $end_date = date('Y-m-t', strtotime($start_date));
        }

        $data['start_date'] = $start_date;
        $data['end_date']   = $end_date;
        $data['customer_types'] = $this->Customer_type_model->get_customer_types($this->company_id);
		$data['company_data'] = $this->Company_model->get_company($this->company_id);
        
        $data['js_files'] = array(
            base_url().auto_version('js/report/ledger_report.js')
        );

        $data['css_files'][] = base_url().auto_version('css/report/ledger.css');
  
        $data['selected_submenu'] = 'Ledger'; //for css
        $data['selected_sidebar_link'] = 'Summary';
        $data['main_content']     = 'reports/ledger/ledger_summary_report';

        $this->load->view('includes/bootstrapped_template', $data);
    }

    function show_daily_report($date)
    {
		
        //get user's shift information
        $data['date'] = $date;
        $data['row']['sales_detail']   = $this->Charge_model->get_charges_detail($date, $date);
        $data['row']['cancelled_noshow_bookings_sales_detail']   = $this->Charge_model->get_charges_detail($date, $date, null, null, null, true);
        
		$data['row']['payment_total']  = $this->Payment_model->get_payment_total_by_date_range($date, $date, null, null, false, true);
        $data['row']['cancelled_noshow_bookings_payment_total'] = $this->Payment_model->get_payment_total_by_date_range($date, $date, null, null, true);
        
		$data['row']['payment_detail'] = $this->Payment_model->get_payment_detail($date, $date, null, false, true);
        $data['row']['cancelled_noshow_bookings_payment_detail'] = $this->Payment_model->get_payment_detail($date, $date, null, true);
        // making dates for total ledger
        $temp_date_array = explode('-', $date);
        $date_mtd        = $temp_date_array[0].'-'.$temp_date_array[1].'-01';
        $date_ytd        = $temp_date_array[0].'-01-01';
        $next_date       = date('Y-m-d', strtotime($date." +1 days"));

        // occupancy rate

        // get all payment information

        $customer_types = $this->Customer_type_model->get_customer_types($this->company_id);
        
        $yesterday = Date("Y-m-d", strtotime($date." -1 days"));
        
        $customer_type_totals = Array();
        if (isset($customer_types))
        {
            foreach($customer_types as $customer_type)
            {
                $charge_total_to_date = $this->Charge_model->get_charge_total_by_date_range("2000-01-01", $yesterday, '', $customer_type['id']);
                $charge_total_to_date = (isset($charge_total_to_date[0]['charge']['total']))?$charge_total_to_date[0]['charge']['total']:0;
                $payment_total_to_date = $this->_get_payment_total($this->Payment_model->get_payment_total_by_date_range("2000-01-01", $yesterday, '', $customer_type['id']));
                
                $charge_total_today = $this->Charge_model->get_charge_total_by_date_range($date, $date, '', $customer_type['id']);
                $charge_total_today = (isset($charge_total_today[0]['charge']['total']))?$charge_total_today[0]['charge']['total']:0;
                $payment_total_today = $this->_get_payment_total($this->Payment_model->get_payment_total_by_date_range($date, $date, '', $customer_type['id']));

                $customer_type_totals[] = Array(
                    'name' => $customer_type['name'],
                    'to_date' => ($charge_total_to_date - $payment_total_to_date),
                    'today' => ($charge_total_today - $payment_total_today)
                );
            }
        }
        
        //print_r($this->Charge_model->get_charge_total_by_date_range($date, $date, '', -1));
                

        // Unassigned Customer TYpes
        $charge_total_to_date = $this->Charge_model->get_charge_total_by_date_range("2000-01-01", $yesterday, '', -1);
        $charge_total_to_date = (isset($charge_total_to_date[0]['charge']['total']))?$charge_total_to_date[0]['charge']['total']:0;
        $payment_total_to_date = $this->_get_payment_total($this->Payment_model->get_payment_total_by_date_range("2000-01-01", $yesterday, '', -1));
        $charge_total_today = $this->Charge_model->get_charge_total_by_date_range($date, $date, '', -1);
        $charge_total_today = (isset($charge_total_today[0]['charge']['total']))?$charge_total_today[0]['charge']['total']:0;
        $payment_total_today = $this->_get_payment_total($this->Payment_model->get_payment_total_by_date_range($date, $date, '', -1));
        
        $customer_type_totals[] = Array(
            'name' => 'Unassigned',
            'to_date' => ($charge_total_to_date - $payment_total_to_date),
            'today' => ($charge_total_today - $payment_total_today)
        );
        
        //echo "$charge_total_today - $payment_total_today";
        $total_rooms = $this->Room_model->get_number_of_rooms($this->company_id);

        // occupancy & average rate are derived from here
        $data['total_data']  = $this->Charge_model->get_year_total($date);
        $data['customer_type_totals'] = $customer_type_totals;
        $data['total_rooms'] = $total_rooms;
        $data['booking_count'] = $this->Booking_model->get_booking_count(Date("Y-01-01", strtotime($date)), $next_date);
        $data['room_charges_ytd'] = $this->Charge_model->get_all_charges(Date("Y-01-01", strtotime($date)), $next_date, $this->selling_date, true, false);
        $data['room_charges_mtd'] = $this->Charge_model->get_all_charges(Date("Y-m-01", strtotime($date)), $next_date, $this->selling_date, true, false);
        $data['room_charges'] = $data['room_charges_ytd'][$date];
        
        $data['js_files'][] = base_url().auto_version('js/report/daily_sale_report.js');
        //$this->output->enable_profiler(TRUE);
        $data['selected_submenu'] = 'Ledger'; //for css
        $data['selected_sidebar_link'] = 'General Ledger';
        $data['main_content'] = 'reports/ledger/daily_summary_report';
        $this->load->view('includes/bootstrapped_template', $data);
	}
	
    function _get_charge_total($array)
	{
		$total = 0;
        foreach ($array as $charge) {
            $flat_tax_rate        = 0;
            $total_tax_percentage = 0;
            if (isset($charge['tax_type'])) {
                foreach ($charge['tax_type'] as $tax) {
                    if ($tax['is_percentage'] == 1) {
                        $total_tax_percentage += $tax['tax_rate'] * 0.01;
                    } else {
                        $flat_tax_rate += $tax['tax_rate'];
                    }
                }

            }
            echo "$total + ".$charge['charge']['amount']." + ".(($charge['charge']['amount'] * $total_tax_percentage))." + $flat_tax_rate";
            $total = $total + $charge['charge']['amount'] + (($charge['charge']['amount'] * $total_tax_percentage)) + $flat_tax_rate;
		}
		return round($total, 2);
	}
	
	// show list of daily sales that belong in a month.
	// $month parameter doesn't effect anything yet.

    function _get_payment_total($array)
    {
        $total = 0;
        foreach ($array as $i) {
            $total = $total + $i->amount;
		}

        return $total;
	}

    function get_ledger_report_AJAX()
	{
        $start_date = $this->input->post('dateStart');
        $end_date   = $this->input->post('dateEnd');
        $group_by   = $this->input->post('groupBy');
        $customer_type_id   = $this->input->post('customerTypeId');
		
		$company_data = $this->Company_model->get_company($this->company_id);
		$include_cancelled_noshow_bookings = (isset($company_data['include_cancelled_noshow_bookings']) && $company_data['include_cancelled_noshow_bookings']) ? true : false ;

        $booking_count   = $this->Booking_model->get_booking_count($start_date, $end_date, 'date_wise', $customer_type_id);
        $charges_booking_count   = $this->Booking_model->get_charges_booking_count($start_date, $end_date, 'date_wise', $customer_type_id);
        $charge_total    = $this->Charge_model->get_all_charges($start_date, $end_date, $this->selling_date, false, true, $customer_type_id, null, $include_cancelled_noshow_bookings);
        $room_charge_total = $this->Charge_model->get_all_charges($start_date, $end_date, $this->selling_date, true, false, $customer_type_id, null, $include_cancelled_noshow_bookings);
        $payment_total   = $this->Payment_model->get_all_payments($start_date, $end_date, $customer_type_id);
        $number_of_rooms = $this->Room_model->get_number_of_rooms($this->company_id);
		
        $last_month                 = date("m", strtotime($start_date));
        $monthly_booking_count      = 0;
        $monthly_charges_booking_count      = 0;
        $accumulated_occupancy_rate = 0;
        $monthly_revPAR             = 0;
        $monthly_ADR                = 0;
        $monthly_room_charge_total  = 0;
        $monthly_charge_total       = 0;
        $monthly_payment_total      = 0;
        $date_count                 = 0;

        for ($date = $start_date; $date <= $end_date; $date = date('Y-m-d', strtotime($date."+1 day")))
		{
            $current_month = date("m", strtotime($date));
            // once month changes, enter monthly sum to the monthly_data array
            if ($current_month != $last_month) {
                $monthly_index                = date("Y", strtotime($date."-1 day"))."-".$last_month; // only used for array indexing purpose
                $monthly_data[$monthly_index] = Array(
                    "booking_count"  => $monthly_booking_count,
                    "charges_booking_count"  => $monthly_charges_booking_count,
                    "occupancy_rate" => $accumulated_occupancy_rate / $date_count,
                    "revPAR" => $monthly_revPAR / $date_count,
                    //"ADR" => $monthly_ADR / $date_count,
                    "room_charge_total"   => $monthly_room_charge_total,
                    "charge_total"   => $monthly_charge_total,
                    "payment_total"  => $monthly_payment_total
                );

                $last_month                 = $current_month;
                $monthly_booking_count      = 0;
                $monthly_charges_booking_count      = 0;
                $accumulated_occupancy_rate = 0;
                $monthly_revPAR             = 0;
                $monthly_ADR                = 0;
                $monthly_room_charge_total  = 0;
                $monthly_charge_total       = 0;
                $monthly_payment_total      = 0;
                $date_count                 = 0;
            }

            $bc = isset($booking_count[$date]) ? $booking_count[$date] : 0;
            $cbc = isset($charges_booking_count[$date]) ? $charges_booking_count[$date] : 0;
            $ct = isset($charge_total[$date]) ? $charge_total[$date] : 0;
            $rct = isset($room_charge_total[$date]) ? $room_charge_total[$date] : 0;
            $pt = isset($payment_total[$date]) ? $payment_total[$date] : 0;
            $or = $bc / $number_of_rooms;
            $rp = $rct / $number_of_rooms;
            $adr = ($bc > 0)?($rct / $bc):0;

            $daily_data[$date] = Array(
                "booking_count"     => $bc,
                "charges_booking_count"     => $cbc,
                "occupancy_rate"    => $or,
                "revPAR"            => $rp,
                "ADR"               => $adr,
                "room_charge_total" => $rct,
                "charge_total"      => $ct,
                "payment_total"     => $pt
            );

            $monthly_booking_count += $bc;
            $monthly_charges_booking_count += $cbc;
            $accumulated_occupancy_rate += $or;
            $date_count++; // for averaging the occupancy rate for monthly data.
            $monthly_revPAR += $rp;
            $monthly_ADR += $adr;
            $monthly_room_charge_total += $rct;
            $monthly_charge_total += $ct;
            $monthly_payment_total += $pt;
		}

        // When the loop ends, there might be unused accumulated monthly report data.
        // If there is, append a new row to the monthly report array
        if ($date_count > 0)
		{
			$date = date('Y-m-d', strtotime($date."-1 day"));
            $monthly_index                = date("Y", strtotime($date))."-".$current_month; // only used for array indexing purpose
            $monthly_data[$monthly_index] = Array(
                "booking_count"  => $monthly_booking_count,
                "charges_booking_count"  => $monthly_charges_booking_count,
                "occupancy_rate" => $accumulated_occupancy_rate / $date_count,
                "revPAR" => $monthly_revPAR / $date_count,
                "ADR" => $monthly_ADR / $date_count,
                "room_charge_total"   => $monthly_room_charge_total,
                "charge_total"   => $monthly_charge_total,
                "payment_total"  => $monthly_payment_total
            );

            if ($group_by == "daily") {
                echo json_encode($daily_data);
            } elseif ($group_by = "monthly") {
                echo json_encode($monthly_data);
            }
		}
	
	}
	
	// show list of daily sales that belong in a month.
	// $month parameter doesn't effect anything yet.

    function show_monthly_tax_report($date = '')
	{
		
		if ($date == '') {
			$date = $this->selling_date;
		}
        //get user's shift information
        $data['date'] = $date;

        $parts      = explode('-', $date);
        $year       = $parts[0];
        $month      = $parts[1];
        $start_date = "$year-$month-01";
        $end_date = date('Y-m-d', strtotime($start_date."+1 month"));
    

        $data['charges'] = $this->Charge_model->get_all_charges($start_date, $end_date, $this->selling_date, false, false, null, 0);
        $data['tax_exempt_charges'] = $this->Charge_model->get_all_tax_exempt_charges($start_date, $end_date, $this->selling_date, false, false, null, 1, true);
        $data['taxes'] = $this->Tax_model->get_monthly_taxes($date, true);
        // load content
		$data['js_files'] = array(
            base_url().auto_version('js/report/daily_sale_report.js')
		);

        $data['selected_submenu'] = 'Ledger'; //for css
        $data['selected_sidebar_link'] = 'Taxes';
        
        $data['main_content']     = 'reports/ledger/monthly_tax_report';
        $this->load->view('includes/bootstrapped_template', $data);
	}
	
	
	// show list of sources of bookings belong in a month.

    function show_monthly_charge_report($date = '')
    {
        
        $date_range = array();
        if ($date == '')
        {
            $date = $this->selling_date;
            $data['date'] = $date;
        }
        else
        {
            $date_check = explode('--', $date);
            if(count($date_check) > 1)
            {
                $date_range = array('from_date'=>$date_check[0], 'to_date'=>$date_check[1]);
            }
            else
            {
                $data['date'] = $date;
            }
        }
        //get user's shift information
        $data['dateRange'] = $date_range;
        if($date_range){
            $data['result']     = $this->Charge_model->get_monthly_sales('', $date_range, true);            
        }else{
            $data['result']     = $this->Charge_model->get_monthly_sales($date, '', true);
        }
        $data['tax_result'] = $this->Tax_model->get_monthly_taxes($date, true);

		// load content
		$data['js_files'] = array(
            base_url().auto_version('js/report/daily_sale_report.js'),
                     base_url().'js/moment.min.js',
            base_url().'js/daterangepicker.js',
        );
        $data['css_files'][] = base_url().auto_version('css/daterangepicker.css');
        $data['selected_submenu'] = 'Ledger'; //for css
        $data['selected_sidebar_link'] = 'Charges';
        
        $data['main_content']     = 'reports/ledger/monthly_charge_report';
		$this->load->view('includes/bootstrapped_template', $data);
	}
	

	// show list of sources of bookings belong in a month.

    function show_monthly_payment_report($date = '')
    {
        $date_range = array();
        if ($date == '')
        {
            $date = $this->selling_date;
            $data['date'] = $date;
        }
        else
        {
            $date_check = explode('--', $date);
            if(count($date_check) > 1)
            {
                $date_range = array('from_date'=>$date_check[0], 'to_date'=>$date_check[1]);
            }
            else
            {
                $data['date'] = $date;
            }
        }
        //get user's shift information
        $data['dateRange'] = $date_range;
        if($date_range)
        {
            $data['result'] = $this->Payment_model->get_monthly_payment_report('', $date_range);
        }
        else
        {
            $data['result'] = $this->Payment_model->get_monthly_payment_report($date);
        }
            
        $data['currencies'] = $this->Currency_model->get_available_currency_list($this->company_id);
        // load content
        $data['js_files'] = array(
            base_url().auto_version('js/report/daily_sale_report.js'),
            base_url().'js/jquery.jqprint.0.3.js',
            base_url().'js/moment.min.js',
            base_url().'js/daterangepicker.js',
        );
        $data['css_files'][] = base_url().auto_version('css/daterangepicker.css');
        $data['selected_submenu'] = 'Ledger'; //for css
        $data['selected_sidebar_link'] = 'Payments';
        $data['main_content']     = 'reports/ledger/monthly_payment_report';
        $this->load->view('includes/bootstrapped_template', $data);
    }

    function show_monthly_currency_payment_report($currency_code, $date = '')
    {
        $date_range = array();
        if ($date == '')
        {
            $date = $this->selling_date;
            $data['date'] = $date;
        }
        else
        {
            $date_check = explode('--', $date);
            if(count($date_check) > 1)
            {
                $date_range = array('from_date'=>$date_check[0], 'to_date'=>$date_check[1]);
            }
            else
            {
                $data['date'] = $date;
            }
        }
        //get user's shift information
        $data['dateRange'] = $date_range;
        if($date_range)
        {
            $data['result'] = $this->Payment_model->get_monthly_payment_report('', $date_range, $currency_code);
        }
        else
        {
            $data['result'] = $this->Payment_model->get_monthly_payment_report($date, null, $currency_code);
        }
            
        $data['currencies'] = $this->Currency_model->get_available_currency_list($this->company_id);
        // load content
        $data['js_files'] = array(
            base_url().auto_version('js/report/daily_sale_report.js'),
            base_url().'js/jquery.jqprint.0.3.js',
            base_url().'js/moment.min.js',
            base_url().'js/daterangepicker.js',
        );
        $data['css_files'][] = base_url().auto_version('css/daterangepicker.css');
        $data['selected_submenu'] = 'Ledger'; //for css
        $data['selected_sidebar_link'] = 'Payments';
        $data['main_content']     = 'reports/ledger/monthly_payment_report';
        $this->load->view('includes/bootstrapped_template', $data);
    }
    
    function show_action_report($date = '', $user_id = '')
    {
        if ($date == '') 
        {
            $date = $this->selling_date;
        }
        //get user's shift information
        $this->load->helper('timezone');
        $data['time_zone'] = $this->Company_model->get_time_zone($this->company_id);

        $data['employees'] = array_merge(
            Array(Array('user_id' => '', 'first_name' => 'Everyone', 'last_name' => '')),
            $this->Company_model->get_user_list($this->company_id)
        );

        $data['current_employee_id'] = $user_id;

        $data['date'] = $date;
        $this->load->model('Booking_log_model');
        $data['rows'] = $this->Booking_log_model->get_action_report($this->company_id, $date, $user_id);

        // load content
        $data['js_files'] = array(
            base_url() . auto_version('js/channel_manager/channel_manager.js'),
            base_url().'js/moment.min.js',
            base_url().auto_version('js/booking/bookingModal.js'),
            base_url().auto_version('js/booking/booking_main.js'),
            base_url().'js/jquery.tablesorter.min.js',
            base_url().auto_version('js/report/action_report.js')
        );

        $data['selected_submenu'] = 'Action'; //for css
        $data['main_content']     = 'report/action_report';

        $this->load->view('includes/bootstrapped_template', $data);
    }

	function show_booking_report($date='')
    {

		if ($date == '') {
			$date = $this->selling_date;
		}
        //get user's shift information
        $data['date']   = $date;
        $charge_total_to_date = $this->Charge_model->get_charge_total_by_date_range("2000-01-01", $yesterday, '', -1);
        
		$data['row']    = $this->Booking_sources_model->get_booking_sources_report($date);

		// load content
		$data['js_files'] = array(
				base_url() . auto_version('js/report/monthly_summary_report.js'),
				base_url() . 'js/jquery.jqprint.0.3.js'
		);

        $data['selected_submenu'] = 'Booking'; //for css
        $data['main_content'] = 'report/booking_sources_report';

		$this->load->view('includes/bootstrapped_template', $data);
	}

    function show_customer_report($date = null, $state = INHOUSE)
	{
        //get user's shift information
		$date = (isset($date))?$date:$this->selling_date;
        $data['date'] = $date;

        
        $filters = array(
            'date_overlap' => $date    
            );
        
        $data['selected_state'] = $state;   
        if ($state == -1)
        {
            $filters['state'] = "active";
        }
        else
        {
            $filters['state'] = $state;
        }

        $data['bookings'] = $this->Booking_model->get_bookings($filters);

        $data['js_files'] = array(
                base_url().auto_version('js/report/housekeeping_report.js'),
                base_url().auto_version('js/report/in_house_report.js')
            );

        $data['selected_submenu'] = 'Customer';

        $data['main_content'] = 'report/in_house_report';
        $this->load->view('includes/bootstrapped_template', $data);

	}
    
    function download_charges_csv_export($date = null)
    {        
        $this->load->helper('download');
        $date_range = array();
        if ($date == '')
        {
            $date = $this->selling_date;
            $data['date'] = $date;
        }
        else
        {
            $date_check = explode('--', $date);
            if(count($date_check) > 1)
            {
                $date_range = array('from_date'=>$date_check[0], 'to_date'=>$date_check[1]);
            }
            else
            {
                $data['date'] = $date;
            }
        }
        //get user's shift information
        $data['dateRange'] = $date_range;
        if($date_range){
            $data['result']     = $this->Charge_model->get_monthly_sales('', $date_range);            
        }else{
            $data['result']     = $this->Charge_model->get_monthly_sales($date);
        }
        $data['tax_result'] = $this->Tax_model->get_monthly_taxes($date);
        
        foreach ($data['result'] as $key => $row)
        {
            unset($data['result'][$key]['selling_date']);
        }
        
        $total = array(); $ci = 0;
        $csv_keys = array();
        foreach ($data['result'][0] as $column_key => $value)
		{
			$csv_keys[] = $column_key;
            $total[$column_key] = 0;
							
		}
        $csv_keys[] = 'Total (without tax)';
               
       $bookings = array($csv_keys);
       $row_total = array();
       $ri = 0; // row index
       
       foreach($data['result'] as $r){
           $booking_row = array();
           $ci = 0; $row_total[$ri] = 0;
           foreach ($r as $ci => $c)  {
                
            if ($ci == 'Selling Date') { // ci represents column index
                    $booking_row[] = $c;
                } else { // not include date in total
                    $booking_row[] = number_format($c, 2, ".", ",");
                    $total[$ci] += $c;
                    $row_total[$ri] += $c;
                }
               
           }
           $booking_row[] = (number_format($row_total[$ri], 2, ".", ",")) ? number_format($row_total[$ri], 2, ".", ",") : 0;
           $ri++;
           
           $bookings[] = $booking_row;
       }
       
       force_download_csv($bookings, "charges_report.csv");
    }
    
    function download_payments_csv_export($date = null)
    {        
        $this->load->helper('download');
        $date_range = array();
        if ($date == '')
        {
            $date = $this->selling_date;
            $data['date'] = $date;
        }
        else
        {
            $date_check = explode('--', $date);
            if(count($date_check) > 1)
            {
                $date_range = array('from_date'=>$date_check[0], 'to_date'=>$date_check[1]);
            }
            else
            {
                $data['date'] = $date;
            }
        }
        //get user's shift information
        $data['dateRange'] = $date_range;
        if($date_range)
        {
            $data['result'] = $this->Payment_model->get_monthly_payment_report('', $date_range);
        }
        else
        {
            $data['result'] = $this->Payment_model->get_monthly_payment_report($date);
        }
        
        foreach ($data['result'] as $key => $row)
        {
            unset($data['result'][$key]['selling_date']);
        }
        
        $total = array(); $ci = 0;
        $csv_keys = array();
        foreach ($data['result'][0] as $column_key => $value)
		{
			$csv_keys[] = $column_key;
            $total[$column_key] = 0;
							
		}
        $csv_keys[] = 'Total';
               
       $bookings = array($csv_keys);
       $row_total = array();
       $ri = 0; // row index
       
       foreach($data['result'] as $r){
           $booking_row = array();
           $ci = 0; $row_total[$ri] = 0;
           foreach ($r as $ci => $c)  {
                
            if ($ci == 'Selling Date') { // ci represents column index
                    $booking_row[] = $c;
                } else { // not include date in total
                    $booking_row[] = number_format($c, 2, ".", ",");
                    $total[$ci] += $c;
                    $row_total[$ri] += $c;
                }
               
           }
           $booking_row[] = (number_format($row_total[$ri], 2, ".", ",")) ? number_format($row_total[$ri], 2, ".", ",") : 0;
           $ri++;
           
           $bookings[] = $booking_row;
       }

       force_download_csv($bookings, "payments_report.csv");
    }
    
    function download_taxes_csv_export($date = null)
    {        
        $this->load->helper('download');
        if ($date == '') {
			$date = $this->selling_date;
		}
        //get user's shift information
        $data['date'] = $date;

        $parts      = explode('-', $date);
        $year       = $parts[0];
        $month      = $parts[1];
        $start_date = "$year-$month-01";
        $end_date = date('Y-m-d', strtotime($start_date."+1 month"));
    

        $data['charges'] = $this->Charge_model->get_all_charges($start_date, $end_date, $this->selling_date, false, false, null, 0);
        $data['tax_exempt_charges'] = $this->Charge_model->get_all_tax_exempt_charges($start_date, $end_date, $this->selling_date, false, false, null, 1);
        $data['taxes'] = $this->Tax_model->get_monthly_taxes($date);
        
        $total = array(); $ci = 0;
        $csv_keys = array('Date', 'Charge Total (before taxes)');
        foreach ($data['taxes'] as $date => $tax)
		{
            // construct the table's headers
            foreach ($data['taxes'][$date] as $column_key => $value)
            {
                if($column_key != 'is_tax_exempt' && $column_key != 'tax_rate')
                {
                    $csv_keys[] = $column_key;
                    $total[$column_key] = 0;
                }
            }
            break;
		}
        $csv_keys[] = 'Tax Total';
        $csv_keys[] = 'Tax Exempt Total';
               
        $bookings = array($csv_keys);
        $charge_total = 0;
        $tax_exempt_charge_total = 0;
       
        if(count($data['taxes']) > 0){
            $ri = 0; // row index
            foreach($data['taxes'] as $date => $r){
                $booking_row = array();
                if (isset($data['charges'][$date]))
                {
                    $charge = $data['charges'][$date];
                }
                else
                {
                    $charge = 0;
                }

                if (isset($data['tax_exempt_charges'][$date]))
                {
                    $tax_exempt_charge = $data['tax_exempt_charges'][$date];
                }
                else
                {
                    $tax_exempt_charge = 0;
                }
                $booking_row[] = $date;
                $booking_row[] = number_format(($charge), 2, ".", ",");
                $ci = 0; // keep track of column index. To prevent making date/shift_type_name into currency format
                $row_total[$ri] = 0;
                
                foreach ($r as $ci => $c)
                {
                    if($ci != 'is_tax_exempt'  && $ci != 'tax_rate'){ // ci represents column index
                        $booking_row[] = number_format($c, 2, ".", ",");
                        $total[$ci] += $c;
                        $row_total[$ri] += $c;
                    }
                }
                $charge_total += $charge;                                                        
                $tax_exempt_charge_total += $tax_exempt_charge;
                
                $booking_row[] = (number_format($row_total[$ri], 2, ".", ",")) ? number_format($row_total[$ri], 2, ".", ",") : 0;
                $booking_row[] = (number_format($tax_exempt_charge, 2, ".", ",")) ? number_format($tax_exempt_charge, 2, ".", ",") : 0;
                $ri++;

               $bookings[] = $booking_row;
            }
        }
        force_download_csv($bookings, "tax_report.csv");
    }
    
    function download_summary_csv_export($start_date = NULL, $end_date = NULL, $group_by = null, $customer_type_id = null)
	{   
        $this->load->helper('download');
        $arrayData = array();

        $booking_count   = $this->Booking_model->get_booking_count($start_date, $end_date, 'date_wise', $customer_type_id);
        $charge_total    = $this->Charge_model->get_all_charges($start_date, $end_date, $this->selling_date, false, true, $customer_type_id);
        $room_charge_total = $this->Charge_model->get_all_charges($start_date, $end_date, $this->selling_date, true, false, $customer_type_id);
        $payment_total   = $this->Payment_model->get_all_payments($start_date, $end_date, $customer_type_id);
        $number_of_rooms = $this->Room_model->get_number_of_rooms($this->company_id);

        $last_month                 = date("m", strtotime($start_date));
        $monthly_booking_count      = 0;
        $accumulated_occupancy_rate = 0;
        $monthly_revPAR             = 0;
        $monthly_ADR                = 0;
        $monthly_room_charge_total  = 0;
        $monthly_charge_total       = 0;
        $monthly_payment_total      = 0;
        $date_count                 = 0;

        for ($date = $start_date; $date <= $end_date; $date = date('Y-m-d', strtotime($date."+1 day")))
		{
            $current_month = date("m", strtotime($date));
            // once month changes, enter monthly sum to the monthly_data array
            if ($current_month != $last_month) {
                $monthly_index                = date("Y", strtotime($date))."-".$last_month; // only used for array indexing purpose
                $monthly_data[$monthly_index] = Array(
                    "booking_count"  => $monthly_booking_count,
                    "occupancy_rate" => $accumulated_occupancy_rate / $date_count,
                    "revPAR" => $monthly_revPAR / $date_count,
                    //"ADR" => $monthly_ADR / $date_count,
                    "room_charge_total"   => $monthly_room_charge_total,
                    "charge_total"   => $monthly_charge_total,
                    "payment_total"  => $monthly_payment_total
                );

                $last_month                 = $current_month;
                $monthly_booking_count      = 0;
                $accumulated_occupancy_rate = 0;
                $monthly_revPAR             = 0;
                $monthly_ADR                = 0;
                $monthly_room_charge_total  = 0;
                $monthly_charge_total       = 0;
                $monthly_payment_total      = 0;
                $date_count                 = 0;
            }

            $bc = isset($booking_count[$date]) ? $booking_count[$date] : 0;
            $ct = isset($charge_total[$date]) ? $charge_total[$date] : 0;
            $rct = isset($room_charge_total[$date]) ? $room_charge_total[$date] : 0;
            $pt = isset($payment_total[$date]) ? $payment_total[$date] : 0;
            $or = $bc / $number_of_rooms;
            $rp = $rct / $number_of_rooms;
            $adr = ($bc > 0)?($rct / $bc):0;

            $daily_data[$date] = Array(
                "booking_count"     => $bc,
                "occupancy_rate"    => $or,
                "revPAR"            => $rp,
                "ADR"               => $adr,
                "room_charge_total" => $rct,
                "charge_total"      => $ct,
                "payment_total"     => $pt
            );

            $monthly_booking_count += $bc;
            $accumulated_occupancy_rate += $or;
            $date_count++; // for averaging the occupancy rate for monthly data.
            $monthly_revPAR += $rp;
            $monthly_ADR += $adr;
            $monthly_room_charge_total += $rct;
            $monthly_charge_total += $ct;
            $monthly_payment_total += $pt;

		}

        // When the loop ends, there might be unused accumulated monthly report data.
        // If there is, append a new row to the monthly report array
        if ($date_count > 0)
		{
            $monthly_index                = date("Y", strtotime($date))."-".$current_month; // only used for array indexing purpose
            $monthly_data[$monthly_index] = Array(
                "booking_count"  => $monthly_booking_count,
                "occupancy_rate" => $accumulated_occupancy_rate / $date_count,
                "revPAR" => $monthly_revPAR / $date_count,
                "ADR" => $monthly_ADR / $date_count,
                "room_charge_total"   => $monthly_room_charge_total,
                "charge_total"   => $monthly_charge_total,
                "payment_total"  => $monthly_payment_total
            );
            
            if ($group_by == "daily") {
                $arrayData = $daily_data;
            } elseif ($group_by = "monthly") {
                $arrayData = $monthly_data;
            }
		}
        
        $csv_keys = array(
                            'Selling Date',
                            'Bookings (Occupancy Rate)',
                            'RevPAR',
                            'ADR',
                            'Room Charges (before taxes)',
                            'All Charges (including taxes)',
                            'All Payments',
                            'Balance'
                           );
        
        $bookings[] = $csv_keys;
        
        foreach($arrayData as $key => $data)
        {
            $booking_row = array();
            
            $booking_count = ($data['booking_count']) ? $data['booking_count'] : 0;
            $room_charge_total = ($data['room_charge_total']) ? $data['room_charge_total'] : 0;
            if($booking_count && $room_charge_total)
            {
                $ADR = number_format($data['room_charge_total']/$data['booking_count'], 2, ".", ",");;
            }
            elseif(!$booking_count && !$room_charge_total)
            {
                $ADR = 0.00;
            }
            elseif(!$room_charge_total && $booking_count)
            {
                $ADR = 0.00;
            }
            elseif($room_charge_total && !$booking_count)
            {
                $ADR = 'Infinity';
            }
            
            $booking_row[] = $key;
            $booking_row[] = ($data['booking_count']) ? $data['booking_count']." (".number_format($data['occupancy_rate']*100, 1, ".", ",")."%)" : '0 (0.00%)';
            $booking_row[] = ($data['revPAR']) ? number_format($data['revPAR'], 2, ".", ",") : '0.00';
            $booking_row[] = $ADR;
            $booking_row[] = ($data['room_charge_total']) ? number_format($data['room_charge_total'], 2, ".", ",") : '0.00';
            $booking_row[] = ($data['charge_total']) ? number_format($data['charge_total'], 2, ".", ",") : '0.00';
            $booking_row[] = ($data['payment_total']) ? number_format($data['payment_total'], 2, ".", ",") : '0.00';
            $booking_row[] = number_format($data['charge_total'] - $data['payment_total'], 2, ".", ",");
           
            $bookings[] = $booking_row;
        }
        force_download_csv($bookings, "summary_report.csv");
	}
    
    function download_daily_summary_report_csv_export($date)
    {        
        $this->load->helper('download');
        //get user's shift information
        $data['date'] = $date;

        $data['row']['sales_total']    = $this->Charge_model->get_charge_total_by_date_range($date, $date);
        $data['row']['sales_detail']   = $this->Charge_model->get_charges_detail($date, $date);
        $data['row']['payment_total']  = $this->Payment_model->get_payment_total_by_date_range($date, $date);
        $data['row']['payment_detail'] = $this->Payment_model->get_payment_detail($date, $date);

        // making dates for total ledger
        $temp_date_array = explode('-', $date);
        $date_mtd        = $temp_date_array[0].'-'.$temp_date_array[1].'-01';
        $date_ytd        = $temp_date_array[0].'-01-01';
        $next_date       = date('Y-m-d', strtotime($date." +1 days"));

        // occupancy rate

        // get all payment information

        $customer_types = $this->Customer_type_model->get_customer_types($this->company_id);
        
        $yesterday = Date("Y-m-d", strtotime($date." -1 days"));
        
        $customer_type_totals = Array();
        if (isset($customer_types))
        {
            foreach($customer_types as $customer_type)
            {
                $charge_total_to_date = $this->Charge_model->get_charge_total_by_date_range("2000-01-01", $yesterday, '', $customer_type['id']);
                $charge_total_to_date = (isset($charge_total_to_date[0]['charge']['total']))?$charge_total_to_date[0]['charge']['total']:0;
                $payment_total_to_date = $this->_get_payment_total($this->Payment_model->get_payment_total_by_date_range("2000-01-01", $yesterday, '', $customer_type['id']));
                
                $charge_total_today = $this->Charge_model->get_charge_total_by_date_range($date, $date, '', $customer_type['id']);
                $charge_total_today = (isset($charge_total_today[0]['charge']['total']))?$charge_total_today[0]['charge']['total']:0;
                $payment_total_today = $this->_get_payment_total($this->Payment_model->get_payment_total_by_date_range($date, $date, '', $customer_type['id']));

                $customer_type_totals[] = Array(
                    'name' => $customer_type['name'],
                    'to_date' => ($charge_total_to_date - $payment_total_to_date),
                    'today' => ($charge_total_today - $payment_total_today)
                );
            }
        }
        
        //print_r($this->Charge_model->get_charge_total_by_date_range($date, $date, '', -1));
                

        // Unassigned Customer TYpes
        $charge_total_to_date = $this->Charge_model->get_charge_total_by_date_range("2000-01-01", $yesterday, '', -1);
        $charge_total_to_date = (isset($charge_total_to_date[0]['charge']['total']))?$charge_total_to_date[0]['charge']['total']:0;
        $payment_total_to_date = $this->_get_payment_total($this->Payment_model->get_payment_total_by_date_range("2000-01-01", $yesterday, '', -1));
        $charge_total_today = $this->Charge_model->get_charge_total_by_date_range($date, $date, '', -1);
        $charge_total_today = (isset($charge_total_today[0]['charge']['total']))?$charge_total_today[0]['charge']['total']:0;
        $payment_total_today = $this->_get_payment_total($this->Payment_model->get_payment_total_by_date_range($date, $date, '', -1));
        
        $customer_type_totals[] = Array(
            'name' => 'Unassigned',
            'to_date' => ($charge_total_to_date - $payment_total_to_date),
            'today' => ($charge_total_today - $payment_total_today)
        );
        
        //echo "$charge_total_today - $payment_total_today";
        $total_rooms = $this->Room_model->get_number_of_rooms($this->company_id);

        // occupancy & average rate are derived from here
        $data['total_data']  = $this->Charge_model->get_year_total($date);
        $data['customer_type_totals'] = $customer_type_totals;
        $data['total_rooms'] = $total_rooms;
        $data['booking_count'] = $this->Booking_model->get_booking_count(Date("Y-01-01", strtotime($date)), $next_date);
        $data['room_charges_ytd'] = $this->Charge_model->get_all_charges(Date("Y-01-01", strtotime($date)), $next_date, $this->selling_date, true, false);
        $data['room_charges_mtd'] = $this->Charge_model->get_all_charges(Date("Y-m-01", strtotime($date)), $next_date, $this->selling_date, true, false);
        $data['room_charges'] = $data['room_charges_ytd'][$date];
        
        
        $bookings = array();
        $booking_row1 = array();
        $booking_row2 = array();
        $booking_row3 = array();
        $booking_row4 = array();
        $booking_row5 = array();
        $booking_row6 = array();
        
        $group_key  = array('charge_summary');
        $csv_keys = array('category', 'Room Charge', 'Sub-total', 'Tax', 'Total Charge');
        $bookings[] = $group_key;
        $bookings[] = $csv_keys;
        
        $totalBeforeTax = 0;		
		$totalAfterTax = 0;			
        if ($data['row']['sales_total'] != null) {
            foreach($data['row']['sales_total'] as $r){
                $booking_row1 = array();
                $totalTax = 0;
                $booking_row1[] = $r['charge']['charge_type'];
                if ($r['charge']['is_room_charge_type'] == '1')
                {
                    $booking_row1[] = "True";
                   // $booking_row1[] = "<span class='glyphicon glyphicon-ok' aria-hidden='true'></span>";
                }
                    // sale amount
                $subtotal=$r['charge']['subtotal']; 
                $booking_row1[] = number_format($subtotal, 2, ".", ",");										
                $totalBeforeTax += $subtotal;
                $tax_amount = $r['charge']['total'] - $r['charge']['subtotal'];
                $booking_row1[] = number_format($tax_amount, 2, ".", ",");
                $total = $r['charge']['total'];
                $totalAfterTax += $total;
                $booking_row1[] = number_format($total, 2, ".", ",");
                if($booking_row1){
                    $bookings[] = $booking_row1;
                }
            }
        }
        $booking_row1 = array();
        $booking_row1[] = " ";
        $booking_row1[] = 'Total:';
        $booking_row1[] = number_format($totalBeforeTax, 2, ".", ",");
        $booking_row1[] = number_format($totalAfterTax - $totalBeforeTax, 2, ".", ",");
        $booking_row1[] = number_format($totalAfterTax, 2, ".", ",");
        if($booking_row1){
            $bookings[] = $booking_row1;
        }

        $group_key  = array('Payment Summary');
        $csv_keys = array('Payment Type', 'Amount');
        $bookings[] = $group_key;
        $bookings[] = $csv_keys;
        
        $total_payment = 0;
			if ($data['row']['payment_total'] != null){
				foreach($data['row']['payment_total'] as $r){
                    $booking_row2 = array();
                    $booking_row2[] = $r->payment_type;
                    $booking_row2[] = number_format($amount=$r->amount, 2, ".", ",");
					$total_payment = $total_payment + $amount;
                    if($booking_row1){
                        $bookings[] = $booking_row2;
                    }
                }
            }
        $booking_row2 = array();
        $booking_row2[] = 'Total:';
        $booking_row2[] = number_format($total_payment, 2, ".", ",");
        $bookings[] = $booking_row2;
        
        $group_key  = array('Balance by Customer Types');
        $csv_keys = array("Customer Type", "Previous Balance", "Today's Balance Change", "Current");
        $bookings[] = $group_key;
        $bookings[] = $csv_keys;
        
        $total_to_date = 0;
		$total_today = 0;

        foreach ($data['customer_type_totals'] as $customer_type){
            $booking_row3 = array();
            $booking_row3[] = $customer_type['name'];
            $booking_row3[] = number_format($customer_type['to_date'], 2, ".", ",");
            $booking_row3[] = number_format($customer_type['today'], 2, ".", ",");
            $booking_row3[] = number_format($customer_type['to_date'] + $customer_type['today'], 2, ".", ",");
            $total_to_date += $customer_type['to_date'];
            $total_today += $customer_type['today'];
            if($booking_row1){
                $bookings[] = $booking_row3;
            }
        }
        $booking_row3 = array();
        $booking_row3[] = 'Total:';
        $booking_row3[] = number_format($total_to_date, 2, ".", ",");
        $booking_row3[] = number_format($total_today, 2, ".", ",");
        $booking_row3[] = number_format($total_to_date + $total_today, 2, ".", ",");
        $bookings[] = $booking_row3;
        
        $group_key  = array('Ocupancy & Average Rate');
        $csv_keys = array("Occupancy", "M.T.D", "Y.T.D", "ADR", "M.T.D", "Y.T.D");
        $bookings[] = $group_key;
        $bookings[] = $csv_keys;
        
        $total_count_mtd = array();
		$total_count_ytd = array();
		$total_rate_mtd = 0;
		$total_rate_ytd = 0;
		$today_count = isset($booking_count[$date])?$booking_count[$date]:0;
		$today_rate = 0;
		
		foreach($data['total_data'] as $t){
			if(strtotime($t->date) <= strtotime($date)){
				if(isset($t->total_rate)){
					$total_rate_ytd += $t->total_rate;
				}
				if(date('n', strtotime($t->date)) == date('n', strtotime($date))){
					if(isset($t->total_rate)){
						$total_rate_mtd += $t->total_rate;
					}
				}
			}
		}

		$mtd_days = 0;
		$ytd_days = 0;
		foreach ($data['booking_count'] as $d => $count)
		{
			if ($d >= Date("Y-01-01", strtotime($date)) && $d <= $date)
			{
				$total_count_ytd[] = $data['booking_count'][$d];
				$ytd_days++;
			}

			if ($d >= Date("Y-m-01", strtotime($date)) && $d <= $date)
			{
				$total_count_mtd[] = $data['booking_count'][$d];
				$mtd_days++;
			}
		}
        
        $booking_row4[] =  number_format($today_count / $total_rooms * 100, 2, ".", ",")."% (".$today_count.")";
        if ($mtd_days != 0)
            $booking_row4[] =  number_format(array_sum($total_count_mtd) / $total_rooms / $mtd_days * 100, 2, ".", ",").'% ('.array_sum($total_count_mtd).')';
        else
            $booking_row4[] = " ";
        if ($ytd_days != 0)
			$booking_row4[] =  number_format(array_sum($total_count_ytd) / $total_rooms / $ytd_days * 100, 2, ".", ",").'% ('.array_sum($total_count_ytd).')';
        else
            $booking_row4[] = " ";
        if($today_count > 0)
            $booking_row4[] =  number_format($room_charges / $today_count, 2, ".", ",");
        else
            $booking_row4[] = " ";
        $mtd_adr_array = array();
        foreach ($data['room_charges_mtd'] as $d => $room_charge)
        {
            if (isset($booking_count[$d]))
            {
                $mtd_adr_array[] = $room_charge / $booking_count[$d];
            }
        }
        //echo array_sum($room_charges_mtd)." / ".count($room_charges_mtd)." / ".$today_count."<br/>";
        if (count($mtd_adr_array) > 0)
            $booking_row4[] = number_format(array_sum($mtd_adr_array) / count($mtd_adr_array), 2, ".", ",");
        else
            $booking_row4[] = 0;
        $ytd_adr_array = array();
        foreach ($data['room_charges_ytd'] as $d => $room_charge)
        {
            if (isset($booking_count[$d]))
            {
                $ytd_adr_array[] = $room_charge / $booking_count[$d];
            }
        }
        if (count($ytd_adr_array) > 0)
            $booking_row4[] = number_format(array_sum($ytd_adr_array) / count($ytd_adr_array), 2, ".", ",");
        else
            $booking_row4[] = 0;
        if($booking_row4){
            $bookings[] = $booking_row4;
        }
        
        $group_key  = array('Charge Details');
        if($this->session->userdata('property_type')==0){
            $room_type = $this->lang->line('room');
        }else{
            $room_type = $this->lang->line('bed');
        }
        $csv_keys = array($room_type, "Booking Customer", "Customer Type", "Charge Type", "Sub-total", "Tax", "Total Sales");
        $bookings[] = $group_key;
        $bookings[] = $csv_keys;
        
        unset($taxArray); // renew tax array				
        $totalBeforeTax = 0;
        $totalAfterTax = 0;
					
        //print_r($row['detail']);
        if ($data['row']['sales_detail'] != null) {
            foreach($data['row']['sales_detail'] as $r){
                $booking_row5 =array();
                $totalTax = 0;
                //$booking_row5[] = base_url()."invoice/show_invoice/".$r['charge']['booking_id'];
                $booking_row5[] = $r['charge']['room_name'];
                $booking_row5[] = $r['charge']['customer_name'];
                $booking_row5[] = $r['charge']['customer_type'];
                $booking_row5[] = $r['charge']['charge_type'];
                // sale amount
                $amount=$r['charge']['amount'];
                $totalBeforeTax = $totalBeforeTax + $amount;
                $booking_row5[] = number_format($amount, 2, ".", ",");
                if($booking_row5){
                   // $bookings[] = $booking_row5;
                }
                if (isset($r['tax_type']))
                {
                    foreach ($r['tax_type'] as $tax) 
                    {
                        //$booking_row5 =array();
                        if ($tax['is_percentage'] == 1)
                        {
                            $taxAmount = (float)$tax['tax_rate']*0.01*$amount;
                        }
                        else
                        {
                            $taxAmount = (float)$tax['tax_rate'];
                        }
                        $totalTax += $taxAmount;
                        // accumulate tax totals
                        if (isset($taxArray[$tax['tax_type']])) {
                            $taxArray[$tax['tax_type']] = $taxArray[$tax['tax_type']] + round($taxAmount, 2);
                            }
                        else
                            $taxArray[$tax['tax_type']] = $taxAmount;

                        $booking_row5[] = $tax['tax_type']." ".number_format($taxAmount, 2, ".", ",");												
                        if($booking_row5){
                          //  $bookings[] = $booking_row5;
                        }
                        
                    }
                }
                $charge = round($totalTax + $amount, 2);
                $booking_row5[] = number_format($charge, 2, ".", ",");
                $totalAfterTax += $charge;
                
                if($booking_row5){
                    $bookings[] = $booking_row5;
                }
            }
        }
        $booking_row5 =array();
        $booking_row5[] = " ";
        $booking_row5[] = " ";
        $booking_row5[] = " ";
        $booking_row5[] = 'Total:';
        $booking_row5[] =  number_format($totalBeforeTax, 2, ".", ",");
        if (isset($taxArray))
        {
            foreach ($taxArray as $t => $value){
                $booking_row5[] = $t." ".number_format($value, 2, ".", ",");
               // $bookings[] = $booking_row5;
            }
        }else{
            $booking_row5[] = " ";
        }
        $booking_row5[] =  number_format($totalAfterTax, 2, ".", ",");
        if($booking_row5){
            $bookings[] = $booking_row5;
        }
        
        
        $group_key  = array('Payment Details');
        if($this->session->userdata('property_type')==0){
            $occ_type = $this->lang->line('room');
        }else{
            $occ_type = $this->lang->line('bed');
        }
        $csv_keys = array($occ_type, "Customer", "Customer Type", "Payment Type", "Description", "Amount");
        $bookings[] = $group_key;
        $bookings[] = $csv_keys;
        
        $total_payment = 0;
			
        if ($data['row']['payment_detail'] != null) {
            foreach($data['row']['payment_detail'] as $r){
                $booking_row6 = array();
                $totalTax = 0;
                //$booking_row6[] = base_url()."invoice/show_invoice/".$r->booking_id;
                $booking_row6[] = $r->room_name;
                $booking_row6[] = $r->customer_name;
                $booking_row6[] = $r->customer_type;
                $booking_row6[] = $r->payment_type;
                $booking_row6[] = $r->description;
                // sale amount
                $amount=$r->amount; 
                $total_payment = $total_payment + $amount;							
                $booking_row6[] = number_format($amount, 2, ".", ",");
                if($booking_row5){
                    $bookings[] = $booking_row6;
                }
            }
        }
        $booking_row6 = array();
        $booking_row6[] = " ";
        $booking_row6[] = " ";
        $booking_row6[] = " ";
        $booking_row6[] = " ";
        $booking_row6[] = 'Total:';
        $booking_row6[] = number_format($total_payment, 2, ".", ",");
        if($booking_row5){
            $bookings[] = $booking_row6;
        }
//        echo "<pre>"; print_r($bookings);    die();
        force_download_csv($bookings, "daily_summary_report.csv");
    }
}
