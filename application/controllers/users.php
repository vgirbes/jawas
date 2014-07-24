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
      
}