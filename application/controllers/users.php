<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Users extends CI_Controller{
     
        public function __construct(){
            parent:: __construct();
            $this->load->model('usuarios');
            $this->load->helper('form');
            $this->load->library('form_validation');
            $this->load->library('session');
            $this->load->library('encrypt');
            $this->load->library('DB_Op');
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
                    'countries_id' => $isValidLogin[0]['countries_id'],
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
            $CI =& get_instance();
            $datos = array();
            $datos['countries'] = $this->db_op->get_countries($CI);
            $user = $this->input->post('usuario');
            $pass = $this->input->post('password');
            $rpass = $this->input->post('rpassword');
            $email = $this->input->post('email');
            $name = $this->input->post('name');
            $rol = $this->input->post('rol');
            $activo = ($this->input->post('activo')==1 ? 1 : 0);
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
            $this->form_validation->set_message('valid_email', lang('adduser.validmail'));

            $country = $this->input->post('country');
            $lang = $this->session->userdata['lang'];
            $estado = $this->usuarios->can_create($user, $pass, $rpass);
            if ($this->form_validation->run() == FALSE){
                $estado[] = validation_errors();
            }

            if (count($estado) <= 0){
                $res = $this->usuarios->insert_user($user, $pass, $email, $name, $rol, $country, $activo);
                if ($res) redirect($lang.'/administration/load/users');
            }

            $datos['estado'] = $estado;
            $this->load->view('adduser', $datos);
        }

        public function add(){
            $CI =& get_instance();
            $rol = $this->usuarios->rol_ok();
            $datos['countries'] = $this->db_op->get_countries($CI);
            if ($rol){
                $this->load->view('adduser', $datos);
            }else{
                $this->load->view('principal');
            }
        }
}