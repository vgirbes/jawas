<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Providers extends CI_Controller{
	public function __construct(){
		parent:: __construct();
		$this->load->model('usuarios_proveedores');
		$this->load->library('session');
        $this->load->database();
        $this->load->helper('url');
        $this->load->library('grocery_CRUD');
    }

    public function __output($output = null){
        $this->load->view('providers_list.php', $output);
    }

    public function index(){
        $this->__output((object)array('output' => '' , 'js_files' => array() , 'css_files' => array()));
    }

    public function view(){
        $user_id = $this->session->userdata['id'];
        $crud = new grocery_CRUD();
        $crud->set_theme('twitter-bootstrap');
        $crud->set_table('users_providers');
        $crud->display_as('SupplierKey','Id');
        $crud->where('users_id', $user_id);
        $crud->set_subject('Providers');
     
        $crud->set_relation('SupplierKey','providers','nom');
     
        $output = $crud->render();
        $this->__output($output);
    }
}