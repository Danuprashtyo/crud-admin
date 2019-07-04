 <?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller 
{
		public function __construct()
    {
        parent::__construct();
        $this->load->library('user_agent');
    }
	public function index()
	{
		$data['title'] = 'Dashboard';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

       $this->load->view('template/header', $data);
       $this->load->view('user/index', $data);
       $this->load->view('template/footer');
	}

	public function table()
	{
		$data['user'] = $this->db->get('exploit');
		
	}
} 