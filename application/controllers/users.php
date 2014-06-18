<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Users extends CI_Controller{
     
        public function __construct(){
            parent:: __construct();
            $this->load->model('usuarios');
            $this->load->helper('form');
            $this->load->library('form_validation');
            $this->load->library('session');
            $this->load->library('encrypt');
        }
       
        public function login(){
            $data = '';
            //definimos las reglas de validación
            $this->form_validation->set_rules('username','username','required|min_lenght[5]|max_lenght[20]');
            $this->form_validation->set_rules('password','password','required');
            if($this->form_validation->run() == FALSE){
                $this->load->view('principal');
            }else{
                $isValidLogin = $this->usuarios->getLogin($this->input->post('username'), md5($this->input->post('password'))); //pasamos los valores al modelo para que compruebe si existe el usuario con ese password
                if($isValidLogin){
                    // si existe el usuario, registramos las variables de sesión y abrimos la página de exito
                    $sesion_data = array(
                            'username' => $this->input->post('username'),
                            'password' => $this->input->post('password')
                             );
                    $this->session->set_userdata($sesion_data);
                    $data['username'] = $this->session->userdata['username'];
                    $data['password'] = $this->session->userdata['password'];
                            
                    $data['error'] = '';
                }else{
                    $data['error'] = 'error';;
                }
            }

            $this->load->view('principal', $data);
        }

        public function logout(){
            //destruimos la sesión
            $this->usuarios->close();
            $this->load->view('principal');
        }
      
}