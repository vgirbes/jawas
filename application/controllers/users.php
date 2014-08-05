<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Users extends CI_Controller{
     
        public function __construct(){
            parent:: __construct();
            $this->load->model('usuarios');
            $this->load->helper('form');
            $this->load->library('form_validation');
            $this->load->library('session');
            $this->load->library('encrypt');
            $this->load->helper('language');
            $this->lang->load('norauto');
        }
       
        public function login(){
            $data = '';
            $this->form_validation->set_rules('username','username','required|min_lenght[5]|max_lenght[20]');
            $this->form_validation->set_rules('password','password','required');
            $this->form_validation->run();
            $isValidLogin = $this->usuarios->getLogin($this->input->post('username'), md5($this->input->post('password'))); 
            if($isValidLogin){
                $lng = $this->usuarios->getCountry($isValidLogin[0]['countries_id']);
                $lang = strtolower($lng);
                $sesion_data = array(
                    'username' => $this->input->post('username'),
                    'password' => $this->input->post('password'),
                    'lang' => $lang,
                    'rol' => $isValidLogin[0]['rol'],
                    'token' => $isValidLogin[0]['token'],
                    'id' => $isValidLogin[0]['id']
                );
                $this->session->set_userdata($sesion_data);
                $data['username'] = $this->session->userdata['username'];
                $data['password'] = $this->session->userdata['password'];
                $data['error'] = '';
                redirect($lang.'/inicio');
            }else{
                $data['error'] = lang('users.login_error');
            }

            $this->load->view('principal', $data);
        }

        public function logout(){
            $this->usuarios->close();
            $this->load->view('principal');
        }

        public function adduser(){
            $user = $this->input->post('usuario');
            $pass = $this->input->post('password');
            $rpass = $this->input->post('rpassword');
            $email = $this->input->post('email');
            $name = $this->input->post('name');
            $rol = $this->input->post('rol');
            $country = $this->input->post('country');
            $lang = $this->session->userdata['lang'];
            $datos = array();
            $estado = $this->usuarios->can_create($user, $pass, $rpass);
            if (count($estado) <= 0){
                $res = $this->usuarios->insert_user($user, $pass, $email, $name, $rol, $country);
                if ($res) redirect($lang.'/administration/load/users');
            }
            $datos['estado'] = $estado;
            $this->load->view('adduser', $datos);
        }

        public function add(){
            $rol = $this->usuarios->rol_ok();
            if ($rol){
                $this->load->view('adduser');
            }else{
                $this->load->view('principal');
            }
        }
      
}