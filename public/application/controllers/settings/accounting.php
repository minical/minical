<?php

//use Omnipay\Omnipay;
use QuickBooksOnline\API\DataService\DataService;

class Accounting extends MY_Controller
{
	function __construct()
	{

        parent::__construct();
        
        $this->load->model('User_model');
        $this->load->model('Image_model');
        $this->load->model('Company_model');
		$this->load->model('Currency_model');	
		$this->load->model('Charge_type_model');		
		$this->load->model('Payment_model');
		$this->load->model('Payment_gateway_model');
		$this->load->model('Tax_model');
		$this->load->model('Tax_price_bracket_model');
		$this->load->model('Employee_log_model');
		
		$this->load->library('form_validation');	
		$this->load->library('PaymentGateway');
        $this->load->library('ckeditor');
        $this->load->library('ckfinder');
        $this->load->helper('ckeditor_helper');
        
		$this->load->helper('url'); // for redirect
		$this->load->helper("guid");
							
		$view_data['menu_on'] = TRUE;		
		
		$view_data['selected_menu'] = 'Settings';
		$view_data['selected_submenu'] = 'Accounting';

		$view_data['submenu'] = 'hotel_settings/hotel_settings_submenu.php';
        
        $view_data['submenu_parent_url'] = base_url()."settings/";
		$view_data['sidebar_menu_url'] = base_url()."settings/accounting/";
        
        $view_data['menu_items'] = $this->Menu_model->get_menus(array('parent_id' => 5, 'wp_id' => 1));
        $view_data['sidebar_links'] = $this->Menu_model->get_menus(array('parent_id' => 30, 'wp_id' => 1));
		
		$this->load->vars($view_data);
	}	

	function index() {
		$this->charge_types();
	}
    
	/* CHARGE TYPES */

	function _create_accounting_log($log) {
        $log_detail =  array(
                    "user_id" => $this->user_id,
                    "selling_date" => $this->selling_date,
                    "date_time" => gmdate('Y-m-d H:i:s'),
                    "log" => $log,
                );   
        
        $this->Employee_log_model->insert_log($log_detail);     
    }
	
	function charge_types()
	{
		//Create array to gather view data
		$view_data = array();
		
		//Load view data with tax types
		if(!is_null($tax_types = $this->Tax_model->get_tax_types($this->company_id)))
		{
			$view_data['tax_types'] = $tax_types;
		}
		
		//Load view data with charge types
		if(!is_null($charge_types = $this->Charge_type_model->get_charge_types($this->company_id)))
		{		
			//Built arrays to split up room charges and other charges
			/*
			$room_charge_types = array();
			$other_charge_types = array();
			$room_charge_type_tax_list = array();
			$other_charge_type_tax_list = array();
			*/
			$all_charge_types = array();
			$all_charge_type_tax_list = array();
			foreach ($charge_types as $charge_type)
			{	
				if(!is_null($charge_type_taxes = $this->Charge_type_model->get_charge_type_tax_list($charge_type['id'])))
				{
					//Create seperate lists for room charges
					/*if ($charge_type->is_room_charge_type == '1' )
					{
						array_push($room_charge_types, $charge_type);
						$room_charge_type_tax_list = array_merge($room_charge_type_tax_list, $charge_type_taxes);
					}
					else
					{													
						
						array_push($other_charge_types, $charge_type);
						$other_charge_type_tax_list = array_merge($other_charge_type_tax_list, $charge_type_taxes);							
					}*/
					array_push($all_charge_types, $charge_type);
					$all_charge_type_tax_list = array_merge($all_charge_type_tax_list, $charge_type_taxes);
				}
			}
			/*$view_data['room_charge_types'] = $room_charge_types;
			$view_data['other_charge_types'] = $other_charge_types;
			$view_data['room_charge_type_tax_list'] = $room_charge_type_tax_list;
			$view_data['other_charge_type_tax_list'] = $other_charge_type_tax_list;
			*/
			$view_data['all_charge_types'] = $all_charge_types;
			$view_data['all_charge_type_tax_list'] = $all_charge_type_tax_list;
		}


		$default_currency = $this->Currency_model->get_default_currency($this->company_id);
		$currency_code = $default_currency['currency_code'];
		$view_data['currency_code'] = $currency_code;

		//Load other view data
		$view_data['js_files'] = array(
				base_url() . 'js/jquery.jeditable.mini.js',
				base_url() . auto_version('js/hotel-settings/charge_types.js')
		);
		
		$view_data['selected_sidebar_link'] = 'Charge Types';
	
		$view_data['main_content'] = 'hotel_settings/accounting_settings/charge_type_settings';
		$this->load->view('includes/bootstrapped_template', $view_data);
		
	}	
	
	// called from charge_type_settings.js
	function create_charge_type() 
	{
		$charge_type_id = $this->Charge_type_model->create_charge_type($this->company_id, 'New Charge Type');
		$this->_create_accounting_log("Create New Charge Type ( [ID {$charge_type_id}])");
		echo $charge_type_id;
	}
	
	function change_charge_type_name()
	{
		$charge_type_id = $this->input->post('charge_type_id');
		$charge_type_name = $this->input->post('charge_type_name');
		
		$data = array('name' => $charge_type_name);
		
		if($this->Charge_type_model->update_charge_type($charge_type_id, $data, $this->company_id))
		{
			$this->_create_accounting_log("Update Charge Type Name ( [ID {$charge_type_id}])");
			echo $charge_type_name;
		}
		else
		{
			echo l('An error occured changing the charge type name. Contact administrator if problems persist.',true);
		}
	}
	
	function change_charge_type_tax() //TO DO: Verify company permission
	{
		$charge_type_id = $this->input->post('charge_type_id');
		$tax_type_id = $this->input->post('tax_type_id');
		$is_checked = $this->input->post('is_checked');		
		
		if ($is_checked == "true")
		{
			$this->Charge_type_model->add_charge_type_tax($charge_type_id, $tax_type_id);
		}
		else
		{
			$this->Charge_type_model->delete_charge_type_tax($charge_type_id, $tax_type_id);
		}
		$this->_create_accounting_log("Update Charge Type Tax ( [ID {$charge_type_id}])");
		//TO DO: error checking
		$data = array (
			'message' => 'Charge type tax changed'
		);
		
		echo json_encode($data);
	}


	
	function change_room_charge_type()
	{
		$is_room_charge_type = 0;
		$charge_type_id = $this->input->post('charge_type_id');
		$is_checked = $this->input->post('is_checked');		
		
		if ($is_checked == "true")
		{
			$is_room_charge_type = 1;
		}
		else
		{
			$is_room_charge_type = 0;
		}
		$this->Charge_type_model->update_charge_type(
												$charge_type_id, 
												array(
													'is_room_charge_type' => $is_room_charge_type
												), 
												$this->company_id
											);
		$this->_create_accounting_log("Update Room Charge Type ( [ID {$charge_type_id}])");
		//TO DO: error checking
		$data = array (
			'message' => 'Charge type tax changed'
		);
		
		echo json_encode($data);
	}
        
        function change_tax_exempt()
	{
		$is_room_charge_type = 0;
		$charge_type_id = $this->input->post('charge_type_id');
		$is_checked = $this->input->post('is_checked');		
		
		if ($is_checked == "true")
		{
			$is_tax_exempt = 1;
		}
		else
		{
			$is_tax_exempt = 0;
		}
		$this->Charge_type_model->update_charge_type(
												$charge_type_id, 
												array(
													'is_tax_exempt' => $is_tax_exempt
												), 
												$this->company_id
											);
		//TO DO: error checking
		$data = array (
			'message' => 'Charge type tax changed'
		);
		
		echo json_encode($data);
	}
	
	function delete_charge_type()
	{
		
		$charge_type_id = $this->input->post('charge_type_id');

		$data = array('is_deleted' => 1);
		
		if ($this->Charge_type_model->update_charge_type($charge_type_id, $data, $this->company_id))
		{
			$data = array ('isSuccess'=> TRUE, 'message' => 'Charge type deleted');
			$this->_create_accounting_log("Delete Charge Type ( [ID {$charge_type_id}])");
			echo json_encode($data);
		}
		else
		{
			$data = array ('isSuccess'=> FALSE, 'message' => 'Charge type delete fail');
			echo json_encode($data);
		}		
	}

	/* TAX TYPES */

	function tax_types()
	{
		$this->form_validation->set_rules('tax_type', 'Tax', 'required|trim');
		$this->form_validation->set_rules('rate', 'Rate', 'required|trim|numeric');
				
		if ($this->form_validation->run() == TRUE)
		{
			$tax_type = $this->input->post('tax_type');
			$rate = $this->input->post('rate');
			
			$result = $this->Tax_model->create_tax_type($this->company_id, $tax_type, $rate);
		}
		
		$data = array();
		
		//get tax_type		
		if(!is_null($tax_types = $this->Tax_model->get_tax_types($this->company_id)))
		{
			$data['tax_types'] = $tax_types;
		}
		$default_currency = $this->Currency_model->get_default_currency($this->company_id);
		$data['currency_symbol'] = $default_currency['currency_code'];
			
		$data['js_files'] = array(
				base_url() . auto_version('js/hotel-settings/tax_types.js')
		);
		$data['selected_sidebar_link'] = 'Tax Types';
		$data['main_content'] = 'hotel_settings/accounting_settings/tax_type_settings';
		$this->load->view('includes/bootstrapped_template', $data);
	}

	function add_price_brackets()
	{
		$start_range = $this->input->post('start-range');
		$end_range = $this->input->post('end-range');
		$tax_rate = $this->input->post('tax-rate');
		$is_percentage = $this->input->post('is-percentage');
		$tax_type_id = $this->input->post('tax_type_id');
		
	    $this->Tax_price_bracket_model->delete_price_brakets(null, $tax_type_id);
		
		$data = array();
		if($start_range && count($start_range) > 0) {
			foreach ($start_range as $key => $start) {
				$data[] = array(
					'start_range' => $start_range[$key],
					'end_range' => $end_range[$key],
					'tax_rate' => $tax_rate[$key],
					'is_percentage' => $is_percentage[$key],
					'tax_type_id' => $tax_type_id
				);
			}
			$this->Tax_price_bracket_model->add_price_brakets($data);
			$data = array('is_brackets_active' => 1);
			$this->Tax_model->update_tax_type($tax_type_id, $data);
		}
		else 
		{
			$data = array('is_brackets_active' => 0);
			$this->Tax_model->update_tax_type($tax_type_id, $data);
		}
		
		echo json_encode(array('success' => true));
	}

	function get_price_brackets()
	{
	    $tax_type_id = $this->input->post('tax_type_id');
		
		$tax_type_brackets = $this->Tax_price_bracket_model->get_price_brackets(null, $tax_type_id);
        echo json_encode($tax_type_brackets);
    }
	
	function create_tax_type() {			
		
		$tax_type_id = $this->Tax_model->create_tax_type($this->company_id, 'New Tax Type', 0);
		$this->_create_accounting_log("Create New Tax Type ( [ID {$tax_type_id}])");

		$default_currency = $this->Currency_model->get_default_currency($this->company_id);
		$currency_code = $default_currency['currency_code'];
	
		$data = array (
			'tax_type_id' => $tax_type_id,
			'tax_type' => 'New Tax Type',
			'currency_symbol' => $currency_code,
			'tax_rate' => 0
		);

		
		
		$this->load->view('hotel_settings/accounting_settings/new_tax_type', $data);
	}
	
	function change_tax_type_name()
	{
		$tax_type_id = $this->input->post('tax_type_id');
		$tax_type_name = $this->input->post('tax_type_name');
		
		$data = array('tax_type' => $tax_type_name);
		
		//TO DO:
		//check if tax type has been used in any invoice
		//if it has been, create a new tax type
		//link new tax type to existing charge types using it
		if($this->Tax_model->update_tax_type($tax_type_id, $data, $this->company_id))
		{
			$this->_create_accounting_log("Update Tax Type Name( [ID {$tax_type_id}])");
			echo $tax_type_name;
		}
		else
		{
			echo l('An error occured changing the tax type name. Contact administrator if problems persist.',true);
		}
	}
	
	function change_tax_type_percentage()
	{
		$this->form_validation->set_rules('tax_type_percentage', 'Tax Rate', 'trim|numeric');
		
		$tax_type_id = $this->input->post('tax_type_id');
		$tax_type_percentage = $this->input->post('tax_type_percentage');
		
		//Convert percentage to decimal because values in db are stored as decimals
		$tax_type_decimal = $tax_type_percentage / 100;
		
		$data = array('percentage' => $tax_type_decimal);
		
		if ($this->form_validation->run() == TRUE)
		{			
			//TO DO:
			//check if tax type has been used in any invoice
			//if it has been, create a new tax type
			//link new tax type to existing charge types using it
			
			//update tax type
			if ($this->Tax_model->update_tax_type($tax_type_id, $data, $this->company_id))
			{
				$value = $tax_type_percentage;
				$this->_create_accounting_log("Update Tax Type Percentage( [ID {$tax_type_id}])");
			}
			else
			{
				$value = l('An error occured. Please contact adminstrator if it continues.', true);
			}
			
			$data = array (
				'error' => '',
				'value' => $value
			);	
		
			echo json_encode($data);
		}		
		else //Validation failed. Add error message
		{			
			$data = array (
				'error' => form_error('tax_type_percentage'),
				'value' => ''
			);

			echo json_encode($data);
		}
	}

	// updates a (single) tax type
	function update_tax_type()
	{
		//temp -- this needs to be changed
		$this->form_validation->set_rules('tax_type_name', 'Tax Type Name', 'trim');
		$this->form_validation->set_rules('tax_rate', 'Tax Rate', 'numeric');
		if ($this->input->post('is_tax_inclusive') == 'true') {
			$is_tax_inclusive = '1';
		} 
		elseif ($this->input->post('is_tax_inclusive') == 'false') {
			$is_tax_inclusive = '0';
		}
		$tax_type_id = $this->security->xss_clean($this->input->post('tax_type_id'));
		$data = array(
			'tax_type' => $this->security->xss_clean($this->input->post('tax_type')),
			'tax_rate' => $this->security->xss_clean($this->input->post('tax_rate')),
			'is_percentage' => $this->security->xss_clean($this->input->post('is_percentage')),
			'is_tax_inclusive' => $this->security->xss_clean($is_tax_inclusive)
		);
        if ($this->form_validation->run() == TRUE)
		{
			//update room type
			if ($this->Tax_model->update_tax_type($tax_type_id, $data))
			{
				$value = 'Save Successful';
				$this->_create_accounting_log("Update Tax Type ( [ID {$tax_type_id}])");
			}
			else
			{
				$value = l('An error occured. Please contact adminstrator if it continues.', true);
			}
			
			$data = array (
				'error' => '',
				'value' => $value
			);
			
			echo json_encode($data);
		} else //Validation failed. Add error message
		{			
			$data = array (
				'error' => form_error('tax-type-name'),
				'value' => ''
			);

			echo json_encode($data);
		}
	}

	
	function delete_tax_type()
	{
		
		$tax_type_id = $this->input->post('tax_type_id');

		$data = array('is_deleted' => 1);
		
		if ($this->Tax_model->update_tax_type($tax_type_id, $data, $this->company_id))
		{
			$data = array ('isSuccess'=> TRUE, 'message' => 'Tax type deleted');
			$this->_create_accounting_log("Delete Tax Type ( [ID {$tax_type_id}])");
			echo json_encode($data);
		}
		else
		{
			$data = array ('isSuccess'=> FALSE, 'message' => 'Tax type delete fail');
			echo json_encode($data);
		}		
	}

	/* Payment Type Settings */


	function payment_types()
	{		
		$this->form_validation->set_rules('payment_type', 'Payment Type', 'required|trim');		
		
		if ($this->form_validation->run() == TRUE)
		{
			$payment_type = $this->input->post('payment_type');	
			
			$result = $this->Payment_model->create_payment_type($this->company_id, $payment_type);
		}
		
		$data = array();
		
		//get payment types
		if(!is_null($payment_types = $this->Payment_model->get_payment_types($this->company_id)))
		{
			$data['payment_types'] = $payment_types;
		}		
		
		$data['js_files'] = array(
			base_url() . 'js/jquery.jeditable.mini.js',
			base_url() . auto_version('js/hotel-settings/payment_types.js')
		);
		$data['selected_sidebar_link'] = 'Payment Types';
	
		
		$data['main_content'] = 'hotel_settings/accounting_settings/payment_type_settings';
		$this->load->view('includes/bootstrapped_template', $data);
		
	}
	
	function create_payment_type() {
		$this->load->model('Payment_model');
		
		$payment_type_id = $this->Payment_model->create_payment_type($this->company_id, 'Click to edit');
		$this->_create_accounting_log("Create New Payment Type ( [ID {$payment_type_id}])");
		echo $payment_type_id;
	}
	
	function change_payment_type_name()
	{
		$payment_type_id = $this->input->post('payment_type_id');
		$payment_type_name = $this->input->post('payment_type_name');
		
		$data = array ('payment_type' => $payment_type_name);
		
		if ($this->Payment_model->update_payment_type($payment_type_id, $data, $this->company_id))
		{
			$this->_create_accounting_log("Update Payment Type Name( [ID {$payment_type_id}])");
			echo $payment_type_name; //TODO: verify update.
		}
		else
		{
			echo l('An occured changing the payment type name. Please contact adminstrator if this persists.',true);
		}
	}
	
	function delete_payment_type()
	{
		
		$payment_type_id = $this->input->post('payment_type_id');

		$data = array('is_deleted' => 1);
		
		if ($this->Payment_model->update_payment_type($payment_type_id, $data, $this->company_id))
		{
			$data = array ('isSuccess'=> TRUE, 'message' => 'Payment type deleted');
			$this->_create_accounting_log("Delete Payment Type( [ID {$payment_type_id}])");
			echo json_encode($data);
		}
		else
		{
			$data = array ('isSuccess'=> FALSE, 'message' => 'Payment type delete fail');
			echo json_encode($data);
		}		
	}

    function are_gateway_credentials_filled()
    {
        $data['are_gateway_credentials_filled'] = $this->paymentgateway->areGatewayCredentialsFilled();
        echo json_encode($data);
    }

    function cc_tokenization_status()
    {
    	$customer_id = $this->input->get('customer_id');
        $this->paymentgateway->setCustomerById($customer_id);
        $token_info       = $this->paymentgateway->getCustomerTokenInfo();
        $selected_gateway = $this->paymentgateway->getSelectedGateway();
        $gateways = array();
        if ($selected_gateway and isset($token_info[$selected_gateway])) {
            $gateways[] = PaymentGateway::getGatewayName($selected_gateway);
        }
        echo json_encode($gateways);
    }

    public function get_public_gateway_settings()
    {
        $data = $this->paymentgateway->getSelectedGatewayCredentials(true);
        $company = $this->Company_model->get_company($this->company_id);
        $enable_card_tokenization = array(
            "enable_card_tokenization" => $company['enable_card_tokenization'] == "1" ? true : false
		);
		$is_cc_visualization_enabled = array(
            "is_cc_visualization_enabled" => $company['is_cc_visualization_enabled'] == "1" ? true : false
        );
        $data = array_merge($data, $enable_card_tokenization,$is_cc_visualization_enabled);
        echo json_encode($data);
    }
}