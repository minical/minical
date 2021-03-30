<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {

	function __construct()
	{
		parent::__construct();
	}

	function index()
	{
		$this->load->helper('url');
		//$this->load->view('welcome_message');
	}

	public function company_users() 
	{
	  
		header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Methods: GET, OPTIONS, POST");
		$data = file_get_contents('php://input');
		
		$request_data = $this->input->post();
		$user_email = $request_data['user_email'];
		// get user id by email
		$data = $this->db->select('id')
						  ->from('users')
						  ->where('email', $user_email)
						  ->get()->result();
  
		if($data!=null)
		{
		  // get company
		  $result = $this->db->select('company_id')
									->from('user_permissions') 
									->where('user_id', $data[0]->id)
									->group_by('company_id')
									->get();
			  
		   $company_id_array = array();
			foreach($result->result() as $row)
			{
			   $company_id_array[] = $row->company_id; // add each user id to the array
			} 
		  $company_data = $this->db->select('c.company_id,c.name,k.key')
						->from('company as c')
						->join('key_x_company as kc', 'c.company_id=kc.company_id')
						->join('key as k', 'kc.key_id=k.id')
						->where_in('c.company_id',$company_id_array)
						->get()->result();
		  if($company_data==null){
			  echo json_encode(array("error"=>"Please enable api access for company!"));
			  return; 
		  }
		  echo json_encode(array("result"=>$company_data));
		 return;   
		}else
		{
		  echo json_encode(array('error'=>'No company registered with this user'));
		  return;  
	   }
	   
		
	}
}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */