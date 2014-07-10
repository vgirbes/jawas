<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Administration extends CI_Controller{
	public function __construct(){
		parent:: __construct();
		$this->load->library('session');
		$this->load->database();
        $this->load->helper('url');
        $this->load->library('grocery_CRUD');
        $this->load->helper('language');
        $this->lang->load('norauto');
    }

    public function index(){
    	if ($this->session->userdata['rol']==1){
    		$datos['admin'] = true;
    		$url = base_url().$this->session->userdata['lang'].'/';
    		$datos['list_admin'][0]['name'] = lang('admin.paises');
    		$datos['list_admin'][0]['url'] = $url.'administration/countries';
    		$datos['list_admin'][1]['name'] = lang('admin.lista_proveedores');
    		$datos['list_admin'][1]['url'] = $url.'administration/list_providers';
    		$datos['list_admin'][2]['name'] = lang('admin.usuarios');
    		$datos['list_admin'][2]['url'] = $url.'administration/users';
    		$datos['list_admin'][3]['name'] = lang('admin.proveedores');
    		$datos['list_admin'][3]['url'] = $url.'administration/providers';
    		$datos['list_admin'][4]['name'] = lang('admin.defaut');
    		$datos['list_admin'][4]['url'] = $url.'administration/defaut';
    		$this->load->view('principal', $datos);
    	}else{
    		$this->load->view('principal');
    	}

    }

    public function __output($output = null){
        $this->load->view('edit_admin.php', $output);
    }

    public function countries(){
    	if ($this->session->userdata['rol']==1){
    		$crud = new grocery_CRUD();
	        $crud->set_theme('twitter-bootstrap');
	        $crud->set_table('countries');
	        $crud->set_subject('Countries');
	     
	        $output = $crud->render();
	        $this->__output($output);
    	}else{
    		$this->load->view('principal');
    	}
    }

    public function list_providers(){
    	if ($this->session->userdata['rol']==1){
    		$crud = new grocery_CRUD();
	        $crud->set_theme('twitter-bootstrap');
	        $crud->set_table('files_providers');
	        $crud->set_subject('Files providers');
	     
	        $output = $crud->render();
	        $this->__output($output);
    	}else{
    		$this->load->view('principal');
    	}
    }

    public function users(){
    	if ($this->session->userdata['rol']==1){
    		$crud = new grocery_CRUD();
	        $crud->set_theme('twitter-bootstrap');
	        $crud->set_table('users');
	        $crud->set_subject('Users');
	     
	        $output = $crud->render();
	        $this->__output($output);
    	}else{
    		$this->load->view('principal');
    	}
    }

    public function providers(){
    	if ($this->session->userdata['rol']==1){
    		$crud = new grocery_CRUD();
	        $crud->set_theme('twitter-bootstrap');
	        $crud->set_table('providers');
	        $crud->set_subject('Providers');
	     
	        $output = $crud->render();
	        $this->__output($output);
    	}else{
    		$this->load->view('principal');
    	}
    }

    public function defaut(){
    	if ($this->session->userdata['rol']==1){
    		$crud = new grocery_CRUD();
	        $crud->set_theme('twitter-bootstrap');
	        $crud->set_table('info_defaut');
	        $crud->set_subject('Global variables');
	     
	        $output = $crud->render();
	        $this->__output($output);
    	}else{
    		$this->load->view('principal');
    	}
    }
}