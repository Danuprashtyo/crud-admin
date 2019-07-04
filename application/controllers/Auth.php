<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller 
{
	public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->library('phpmailer_lib');
        $this->load->model('user_model');
        $this->load->library('ciqrcode');
    }

	public function index()
	{
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');

		if ($this->form_validation->run() == false){
        $data['title'] = 'Login page';
		$this->load->view('template/auth_header', $data);
		$this->load->view('auth/login');
		$this->load->view('template/auth_footer');
	
		}else{
			 // validasinya succes
              $this->_login();
		}
		
	}

 private function _login()
    {
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        
        // Select * From Table user
        $user = $this->db->get_where('user', ['email' => $email])->row_array();

          	//usernya ada
       if($user){
       	
       	//jikas usernya aktif
       	if($user['is_active'] == 1){

       		//cek password
       		if(password_verify($password, $user['password'])){
             $data = [
                        'email' => $user['email'],
                        'role_id' => $user['role_id']
                    ];
                     $this->session->set_userdata($data);
                    
                        redirect('user');

       		}else{

       			 $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Wrong password  </div>');
                  redirect('auth');
       		}
           
       	}else{
              $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">E mail not activated  </div>');
                  redirect('auth');


       	}
       }else{
       	     $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">This email is not registered  </div>');
                  redirect('auth');

       }
    }


	public function register()
	{
		 $this->form_validation->set_rules('name', 'Name', 'required|trim');
		 $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[user.email]' , [
      'is_unique' => 'Email already registered',
      'valid_email' => 'Please enter a valid Email'
     ]);

		 $this->form_validation->set_rules('password1', 'Password', 'required|trim|min_length[3]|matches[password2]', [
            'matches' => 'Password dont match!',
            'min_length' => 'Password too short!'
        ]);

		 $this->form_validation->set_rules('password2', 'Password', 'required|trim|matches[password1]');

		 if ($this->form_validation->run() == false)
		 {
		$data['title'] = 'Register';
		$this->load->view('template/auth_header', $data);
		$this->load->view('auth/register');
		$this->load->view('template/auth_footer');
	
	}else{
       $form_response=$this->input->post('g-recaptcha-response');
       $url="https://www.google.com/recaptcha/api/siteverify";
       $sitekey="6Lc8fKoUAAAAACau8XIFHYU9LGfqkoOaJbeHPXAA";
       $response = file_get_contents($url."?secret=".$sitekey."&response=".$form_response."&remoteip=".$_SERVER["REMOTE_ADDR"]);
       $data=json_decode($response);

        $email = $this->input->post('email');
      $set = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $code = substr(str_shuffle($set), 0, 10);
       //Validasi Captcha
       if(isset($data->success) && $data->success=="true"){
            $data = [
            'name' => htmlspecialchars($this->input->post('name', true)),
                'email' => htmlspecialchars($this->input->post('email', true)),
                'image' => 'default.jpg',
                'password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT),
                'is_active' => 0,
                'code' => $code
            ];

            $mail = $this->phpmailer_lib->load();
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'danu.burung55@gmail.com';
            $mail->Password = 'Anjing123';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('danu.burung55@gmail.com','Danu');

            $mail->addAddress($email);

            $mail->Subject = 'Your Email Verification';

            $mail->isHTML(true);
            $mailContent = "<h1>Dear,</h1>
            <p>Congratulations you have Successfully entered G-mail, Please (click here to activate)
               to verify your email and to activate your account that was previously created.

            </p>"."<a href=".base_url()."Auth/verify?email=".base64_encode($email).'&code='.$code.">Click Here To Activate</a>";
            $mail->Body = $mailContent;
            $mail->send();
              $this->db->insert('user', $data);
              $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">The account was successfully registered and I sent an email to verify.</div>');
              redirect('auth');
       }else{
              $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Fill in the Captcha!</div>');
             redirect('auth/register');
       }
  	}
}

  public function verify(){

    //untuk mengecek emailnya apakah benar 
    $id = $this->input->get('email');
    $id1 = base64_decode($id);
    $data = ['email' => $id1];
    $user = $this->user_model->checkUser($data);
          if($user){
              $token = $this->input->get('code');
              $check = ['code' => $token];
              $code = $this->user_model->checkUser($check);
            if($code){
              $this->user_model->changeActive($id1);
              $this->session->set_flashdata('message','<p class="alert alert-success label-material" role="alert">Activation Success</p>');
              redirect('Auth');
            }else{
            $this->session->set_flashdata('message','<p class="alert alert-danger label-material" role="alert">Activation Failed! Invalid Token</p>');
            redirect('Auth');
            }
          }else{
            $this->session->set_flashdata('message','<p class="alert alert-danger label-material" role="alert">Activation Failed! Invalid Email</p>');
            redirect('Auth');            
          }
    
  }

  public function QRcode($kodenya = '123456'){
    QRcode::png(
    $kodenya,
    $outfile = false,
    $level = QR_ECLEVEL_H,
    $size = 5,
    $margin = 2
      );
  }

	public function logout()
    {
        $this->session->unset_userdata('email');
        $this->session->unset_userdata('role_id');

        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">You have been logged out!</div>');
        redirect('auth');
    }

        //Ajax Controller Read Data
  public function showAllData(){
    $result = $this->user_model->getAllData();
    echo json_encode($result);
  }

  //Ajax Controller Add Data
  public function addNew(){
    $result = $this->user_model->addData();
    $msg['success'] = false;
    $msg['type'] = 'add';
    if($result){
      $msg['success'] = true;
    }
    echo json_encode($msg);
  }

  //Ajax Controller Edit Data
  public function editData(){
    $result = $this->user_model->editData();
    echo json_encode($result);
  }

  public function updateData(){
    $result = $this->user_model->updateData();
    $msg['success'] = false;
    $msg['type'] = 'update';
    if($result){
      $msg['success'] = true;
    }
    echo json_encode($msg);
  }

  public function deleteData(){
    $result = $this->user_model->deleteData();
    $msg['success'] = false;
    if($result){
      $msg['success'] = true;
    }
    echo json_encode($msg);
  }
}
