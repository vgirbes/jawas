<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Import extends CI_Controller{
	public function __construct(){
		parent:: __construct();
		$this->load->model('ficheros');
		$this->load->library('session');
        $this->load->helper('url');
        $this->load->library('grocery_CRUD');
        $this->load->helper('language');
        $this->lang->load('norauto');
    }

    public function __output($output = null){
        $this->load->view('comdep_list.php', $output);
    }

    public function comdep(){
        if(isset($this->session->userdata['username'])){
            $user_id = $this->session->userdata['id'];
        	$result = $this->ficheros->process_comdep_aty('comdep');
            if ($result){
                $datos['import_state'] = $this->ficheros->import_state($user_id);
                $this->load->view('principal', $datos);
            }else{
                $datos['errores'] = lang('import.comdep_error');
                $this->load->view('principal', $datos);
            }   
        }else{
            $this->load->view('principal');
        }
    }

    public function atyse(){
        if(isset($this->session->userdata['username'])){
            $user_id = $this->session->userdata['id'];
            $result = $this->ficheros->process_comdep_aty('atyse');
            if ($result){
                $datos['import_state'] = $this->ficheros->import_state($user_id);
                $this->load->view('principal', $datos);
            }else{
                $datos['errores'] = lang('import.atyse_error');
                $this->load->view('principal', $datos);
            }   
        }else{
            $this->load->view('principal');
        }
    }

    public function mch(){
        if(isset($this->session->userdata['username'])){
            $user_id = $this->session->userdata['id'];
            $result = $this->ficheros->process_comdep_aty('mch');
            if ($result){
                $datos['import_state'] = $this->ficheros->import_state($user_id);
                $this->load->view('principal', $datos);
            }else{
                $datos['errores'] = lang('import.mch_error');
                $this->load->view('principal', $datos);
            }   
        }else{
            $this->load->view('principal');
        }
    }

    public function generate(){
        if(isset($this->session->userdata['username'])){
            $user_id = $this->session->userdata['id'];
            $result = $this->ficheros->generate_files();
            if ($result){
                $datos['lista_ficheros'] = $this->ficheros->show_files();
                if (!$datos['lista_ficheros']) $datos['error'] = lang('import.files_error');
                $this->load->view('principal', $datos);
            }else{
                $datos['errores'] = lang('import.generate_error');
                $this->load->view('principal', $datos);
            }   
        }else{
            $this->load->view('principal');
        }
    }

    public function view(){
        if(isset($this->session->userdata['username'])){
            $user_id = $this->session->userdata['id']; 
            $crud = new grocery_CRUD();
            $crud->set_theme('flexigrid');
            $crud->set_table('products');
            $crud->set_subject('Products');
            $crud->columns('codeRegroupement', 'name', 'description', 'supplierPrice', 'supplierPriceB', 'priceVar', 'stockValue', 'stockValueB', 'stockVar');
            $crud->where('user_id', $user_id);
             
            $output = $crud->render();
            $this->__output($output);
        }else{
            $this->load->view('principal');
        }
    }

    public function index(){
        if(isset($this->session->userdata['username'])){
            $user_id = $this->session->userdata['id'];
            $exist = $this->ficheros->state_exist($user_id);
            if ($exist){
                $datos['import_state'] = $this->ficheros->import_state($user_id);
                $this->load->view('principal', $datos);
            }else{
                $this->load->view('principal');
            }
        }else{
            $this->load->view('principal');
        }
    }
}