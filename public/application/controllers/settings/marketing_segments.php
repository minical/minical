<?php
class Marketing_segments extends MY_Controller
{

	function __construct()
	{

        parent::__construct();
        
        $this->load->model('User_model');
        $this->load->model('Image_model');
        
		$this->load->model('Company_model');
		$this->load->model('post_model');
        $this->load->library('ckeditor');
        $this->load->library('ckfinder');
        $this->load->helper('ckeditor_helper');
		
		$this->load->library('Form_validation');
		
		$view_data['menu_on'] = true;
		$view_data['selected_menu'] = 'Settings';
		$view_data['selected_submenu'] = 'Rates';
		$view_data['submenu'] = 'hotel_settings/hotel_settings_submenu.php';
        
        $view_data['submenu_parent_url'] = base_url()."settings/";
		$view_data['sidebar_menu_url'] = base_url()."settings/rates/";
        
        $view_data['menu_items'] = $this->Menu_model->get_menus(array('parent_id' => 5, 'wp_id' => 1));
        $view_data['sidebar_links'] = $this->Menu_model->get_menus(array('parent_id' => 33, 'wp_id' => 1));

		$view_data['css_files'] = array(
				base_url() . auto_version('css/croppic.css')
		);
			
		$view_data['js_files'] = array(
			//base_url() . auto_version('js/bootstrap.min.js'),
			base_url() . auto_version('js/croppic.js'),
			base_url() . auto_version('js/hotel-settings/image-settings.js'),
			base_url() . auto_version('js/hotel-settings/Marketing_segment.js'),
			
		);
		
		
		$this->load->vars($view_data);
	}	

	function index() {
		$this->segment_list();
	}


function segment_list() {
		$data['company_id'] = $this->company_id;
		$config['per_page'] = (sqli_clean($this->security->xss_clean($this->input->get('per_page'))) ? sqli_clean($this->security->xss_clean($this->input->get('per_page'))) : '30');
        $config['uri_segment'] = 3; // uri_segment is used to tell pagination which page we're on. it seems that default is 3.
        $config['base_url'] = base_url() . "debtor_list";
		$filters['company_id'] = $this->company_id;
		$filters['post_type'] = 'marketing_segment';
		$data['rows'] = $this->post_model->get_post($filters);
        $config['total_rows'] = $this->post_model->get_found_rows();
		$this->load->library('pagination');
        $this->pagination->initialize($config);
        $config['suffix'] = '?'.http_build_query($_GET, '', "&");
        $data['main_content'] = 'hotel_settings/Marketing_segments/list';
		$this->load->view('includes/bootstrapped_template', $data);
	}
	
	function add()
	{
		$data['company_id'] = $this->company_id;
        $data['segment'] = array();
       
		//$data['menu_on'] = true;
        $data['selected_menu'] = 'settings';
        $data['selected_submenu'] = 'marketing_segments';
        //print_r($data['debtor']);
        $data['main_content'] = 'hotel_settings/Marketing_segments/add';
		$this->load->view('includes/bootstrapped_template', $data);
	}
	function edit($segment_id)
	{
		$data['company_id'] = $this->company_id;
		
        $segmentdata = $this->post_model->get_post($segment_id);
		
        $data['segment'] = isset($segmentdata[0])?$segmentdata[0]:array();
       
		//$data['menu_on'] = true;
        $data['selected_menu'] = 'settings';
        $data['selected_submenu'] = 'marketing_segments';
        //print_r($data['debtor']);
        $data['main_content'] = 'hotel_settings/Marketing_segments/edit';
		$this->load->view('includes/bootstrapped_template', $data);
	}
	function save_segment(){
		$name = $this->input->post('name');
		$description = $this->input->post('description');
		$segment_id = $this->input->post('segment_id');

		$response = true;

		$is_valid_creds = false;
		if(isset($response) && $response){

			$meta['post_title'] = $name;
			$meta['post_content'] = $description;
			$meta['company_id'] = $this->company_id;
			$meta['post_type'] = 'marketing_segment';
			if($segment_id!=''){
			$segmentdata = $this->post_model->get_post($segment_id);
			}
            $segment_data = isset($segmentdata[0])?$segmentdata[0]:false;
			//print_r($segment_data);
			if($segment_data){
				$meta['post_id'] = $segment_id;
				$this->post_model->edit_post($meta);
				$segment_id = $segment_data['post_id'];
			} else {
				$segment_id = $this->post_model->create_post($meta);
			}
			
			$is_valid_creds = true;
		}

		if($is_valid_creds){
			$msg = l('Marketing Segment inserted/Updated successfully.', true);
			echo json_encode(array('success' => true, 'msg' => $msg, 'segment_id' => $segment_id));
		} else {
			$msg = l('Some Problem on insert/update.', true);
			echo json_encode(array('success' => false, 'msg' => $msg));
		}
	}
	
	function status_segment(){
		$is_hidden = $this->input->post('is_disable')=='disable'?1:0;
		$segment_id = $this->input->post('segment_id');

		$response = true;

		$is_valid_creds = false;
		if(isset($response) && $response){

			$meta['is_deleted'] = $is_hidden;
			$meta['company_id'] = $this->company_id;
			

			$segmentdata = $this->post_model->get_post($segment_id);
            $segment_data = isset($segmentdata[0])?$segmentdata[0]:false;
			if($segment_data){
				$meta['post_id'] = $segment_id;
				$this->post_model->edit_post($meta);
				$segment_id = $segment_data['post_id'];
			} 
			
			$is_valid_creds = true;
		}

		if($is_valid_creds){
			$msg = l('Segment status updated successfully.', true);
			echo json_encode(array('success' => true, 'msg' => $msg, 'segment_id' => $segment_id));
		} else {
			$msg = l('Some Problem on insert/update.', true);
			echo json_encode(array('success' => false, 'msg' => $msg));
		}
	}
	
	
}
