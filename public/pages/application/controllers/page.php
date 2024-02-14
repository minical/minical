<?php
class Page extends CI_controller
{

    public $module_name;

	function __construct()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->load->library('email');
        $this->load->model("Company_model");

   		// $this->minical_homepage();

		$view_data['menu_on'] = true;
		$this->load->vars($view_data);
	}

	function index()
	{	
		$company_website = $this->uri->segment(2);

		$this->main($company_website);
	}	

    function minical_homepage()
	{
		if ($_SERVER['HTTP_HOST'] == 'localhost'||$_SERVER['HTTP_HOST'] == 'demo.minical.io'||$_SERVER['HTTP_HOST'] == 'app.minical.io') {
			$destination_uri = strtolower($this->uri->segment(2));
			$destination_uri_page = strtolower($this->uri->segment(3));
		    $company = $this->Company_model->get_company($destination_uri);
			
			if (
				strtolower($this->uri->segment(1))=='send_email'
			) {
            } else {
                header("Location: https://www.minical.io", true, 301);
                exit;
            }

			
		}
	}
	

	function main($company_website)
	{
		
        $this->load->model("Company_model");
		$company = $this->Company_model->get_company($company_website);

		$company_id = $company['company_id'];
		$data['company_data'] = $company;

		$this->load->model('Image_model');
		$data['gallery_image_group_id'] = $company['gallery_image_group_id'];
		
		$data['random_gallery_image'] = $this->Image_model->get_images($data['gallery_image_group_id']);

		$data['slideshow_image_group_id'] = $company['slideshow_image_group_id'];

		$data['slideshow_images'] = $this->Image_model->get_images($data['slideshow_image_group_id']);

		$data['logo_image_group_id'] = $company['logo_image_group_id'];

		$data['logo_images'] = $this->Image_model->get_images($data['logo_image_group_id']);
		$this->load->model('Feature_model');		
		$data['company_features'] = $this->Feature_model->get_features($company_id,NULL);


		$data['main_content'] = 'main';
		$this->load->view('includes/template', $data);	
	}


	function room_types()
	{
		
		$company_website = $this->uri->segment(2);
		$this->load->model("Company_model");

		$company = $this->Company_model->get_company($company_website);
		$data['company_data'] = $company;

		$data['slideshow_image_group_id'] = $company['slideshow_image_group_id'];
		$this->load->model('Image_model');

		$data['slideshow_images'] = $this->Image_model->get_images($data['slideshow_image_group_id']);
		
		$data['gallery_image_group_id'] = $company['gallery_image_group_id'];
		$data['random_gallery_image'] = $this->Image_model->get_images($data['gallery_image_group_id']);

		$data['logo_image_group_id'] = $company['logo_image_group_id'];

		$data['logo_images'] = $this->Image_model->get_images($data['logo_image_group_id']);

		$this->load->model('Image_model');
        $this->load->model('Room_type_model');
		$data['room_types'] = $this->Room_type_model->get_room_types($company['company_id']);

		foreach ($data['room_types'] as $key => $roomType) {
			$image = $this->Image_model->get_images($roomType['image_group_id']);
			if (!empty($image)) {
				$imageFilename = $image['0']['filename'];
				$data['room_types'][$key]['filename'] = $imageFilename;
			}  
			else {
				$data['room_types'][$key]['filename'] = '';
			}
		}

      
		$data['main_content'] = 'rooms';
		$this->load->view('includes/template', $data);	
	}

	function gallery()
	{
		$company_website = $this->uri->segment(2);
		$this->load->model("Company_model");

		$company = $this->Company_model->get_company($company_website);
		$data['company_data'] = $company;

		$data['slideshow_image_group_id'] = $company['slideshow_image_group_id'];
		$this->load->model('Image_model');

		$data['slideshow_images'] = $this->Image_model->get_images($data['slideshow_image_group_id']);
		
		$data['gallery_image_group_id'] = $company['gallery_image_group_id'];
		$data['random_gallery_image'] = $this->Image_model->get_images($data['gallery_image_group_id']);
		
		$data['logo_image_group_id'] = $company['logo_image_group_id'];

		$data['logo_images'] = $this->Image_model->get_images($data['logo_image_group_id']);

		$data['main_content'] = 'gallery';
		$this->load->view('includes/template', $data);	
	}


	function location()
	{
		$company_website = $this->uri->segment(2);
		$this->load->model("Company_model");

		$company = $this->Company_model->get_company($company_website);
		$data['company_data'] = $company;

		$data['slideshow_image_group_id'] = $company['slideshow_image_group_id'];
		$this->load->model('Image_model');

		$data['slideshow_images'] = $this->Image_model->get_images($data['slideshow_image_group_id']);
		
		$data['gallery_image_group_id'] = $company['gallery_image_group_id'];
		$data['random_gallery_image'] = $this->Image_model->get_images($data['gallery_image_group_id']);

		$data['logo_image_group_id'] = $company['logo_image_group_id'];

		$data['logo_images'] = $this->Image_model->get_images($data['logo_image_group_id']);


		$data['main_content'] = 'location';
		$this->load->view('includes/template', $data);	
	}


	function send_email()
	{
		$postdata = $this->input->post();
        // prx($postdata); die;
		$uri 		= $this->input->post('uri'); // only sent from hotel website
		$from_email = $this->input->post('from_email');
		$message 	= $this->input->post('message');
		$language 	= $this->input->post('language');
		
		// if company doesn't exist, then set "contact us" recipient as me
		// if ($uri == 'send_email' || $uri == 'pricing' || $uri == '')
		if ($uri == '')
		{
			$to_email = "support@minical.io";
		}
		else
		{
			$this->company_data = $this->Company_model->get_company($uri);
			$to_email = $this->company_data['email'];
		}
		
		// $recaptcha_response = $this->input->post('g-recaptcha-response');
		// $secret_key = "6LcUXv8SAAAAAMAjNdj2hVZnCZGY6miHCFDV4KBM";
		// $captcha_response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secret_key."&response=".$recaptcha_response);
        
        // $response_in_json = json_decode($captcha_response, true);
		// if( $response_in_json['success'])
        // {
        	$this->email->from($from_email);
			$this->email->reply_to($from_email);
			$this->email->to($to_email);

			$this->email->subject('New message from a website visitor!');
			$this->email->message($message);
			// $this->email->send();	
			if(!$this->email->send()) 
			{
				echo $this->email->print_debugger();
			   	$message = 'Oops! Something went wrong. Please contact us directly at '.$to_email;
			}
			else
			{
				$message = 'Thank you! We will get back to you shortly.';
			}
        // }
        // else
        // {
        // 	$message = 'Invalid recaptcha value';
        // }

        $json = json_encode(
	   					Array(
					   		"message" => $message
					   		)
   						);
		echo $json;
	}
}
