<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Import extends CI_Controller{
	public function __construct(){
		parent:: __construct();
		$this->load->model('ficheros');
		$this->load->library('session');
        $this->load->helper('url');
        $this->load->library('grocery_CRUD');
    }

    public function __output($output = null){
        $this->load->view('comdep_list.php', $output);
    }

    public function comdep(){
        if(isset($this->session->userdata['username'])){
        	$result = $this->ficheros->process_comdep_aty('comdep');
            if ($result){
                redirect('import/view');
            }else{
                $datos['errores'] = 'Error: No se ha podido cargar el fichero de Comdep.';
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
                //redirect('import/view');
                $datos['import_state'] = $this->ficheros->import_state($user_id);
                $this->load->view('principal', $datos);
            }else{
                $datos['errores'] = 'Error: No se ha podido cargar el fichero de Atyse.';
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
            $crud->set_theme('twitter-bootstrap');
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