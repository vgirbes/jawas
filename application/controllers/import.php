<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Import extends CI_Controller{
	public function __construct(){
		parent:: __construct();
		$this->load->model('ficheros');
		$this->load->library('session');
        $this->load->library('RequestProvider');
        $this->load->library('DB_op');
        $this->load->helper('url');
        $this->load->library('grocery_CRUD');
        $this->load->helper('language');
        $this->lang->load('norauto');
        $this->load->library('encrypt');
        $this->load->library('Time_Process');
    }

    public function __output($output = null){
        $this->load->view('comdep_list.php', $output);
    }

    public function comdep(){
        $result = false;
        $user_id = $this->time_process->check();
        if ($user_id != false){
            $result = $this->ficheros->process_comdep_aty('comdep', $user_id);
        }
    	
        if ($result){
            $datos['import_state'] = $this->ficheros->import_state($user_id);
            $this->load->view('principal', $datos);
        }else{
            $datos['errores'] = lang('import.comdep_error');
            $this->load->view('principal', $datos);
        }   

    }

    public function atyse(){
        $result = false;
        $user_id = $this->time_process->check();
        if ($user_id != false){
            $result = $this->ficheros->process_comdep_aty('atyse', $user_id);
        }
        if ($result){
            $datos['import_state'] = $this->ficheros->import_state($user_id);
            $this->load->view('principal', $datos);
        }else{
            $datos['errores'] = lang('import.atyse_error');
            $this->load->view('principal', $datos);
        }   
    }

    public function all(){
        $ip = $_SERVER['REMOTE_ADDR'];

        if ($ip == '127.0.0.1'){
            $comdep = $this->ficheros->process_comdep_aty('comdep');
            $atyse = $this->ficheros->process_comdep_aty('atyse');
        }

        $this->load->view('principal', $datos);
    /*    $sesion_data = array(
                'username' => 'all',
                'password' => '',
                'lang' => 'es',
                'rol' => 1,
                'id' => 1
        );
        $this->session->set_userdata($sesion_data);
        $CI =& get_instance();
        $user_id = 1;
        $archivo = $this->requestprovider->Request_Files('COMDEP', $CI);*/

    }

    public function mch(){
        $result = false;
        $user_id = $this->time_process->check();
        if ($user_id != false){
            $result = $this->ficheros->process_comdep_aty('mch', $user_id);
        }
        
        if ($result){
            $datos['import_state'] = $this->ficheros->import_state($user_id);
            $this->load->view('principal', $datos);
        }else{
            $datos['errores'] = lang('import.mch_error');
            $this->load->view('principal', $datos);
        }   
    }

    public function generate(){
        $result = false;
        $user_id = $this->time_process->check();
        log_message('error', 'Usuario '.$user_id);
        $result = $this->ficheros->generate_files($user_id);
        if ($result){
            $datos['lista_ficheros'] = $this->ficheros->show_files();
            if (!$datos['lista_ficheros']) $datos['error'] = lang('import.files_error');
            $this->load->view('principal', $datos);
        }else{
            $datos['errores'] = lang('import.generate_error');
            $this->load->view('principal', $datos);
        }   
    }

    public function view(){
        $CI =& get_instance();
        if(isset($this->session->userdata['username'])){
            $user_id = $this->session->userdata['id']; 
            $datos['import_state'] = $this->ficheros->import_state($user_id);
            $this->time_process->user_id = $user_id;
            $process = $this->time_process->get_process($CI, $user_id);
            if ($process != false){
                $this->time_process->flag = $process->flag;
                if ($process->flag == 'all') $datos['process_all'] = true;
                $datos['process'] = $process;
                $datos['error_process'] = $this->time_process->get_error_process($CI, $process->id);
                $datos['time_process'] = $this->time_process->get_time_process($CI);
                if ($datos['error_process'] == false){
                    $datos['editable'] = false;
                }else{
                    $datos['editable'] = true;
                }
                $this->load->view('principal', $datos);
            }else{
                $datos['editable'] = true;
                $crud = new grocery_CRUD();
                $crud->set_theme('flexigrid');
                $crud->set_table('products');
                $crud->set_subject('Products');
                $crud->columns('codeRegroupement', 'name', 'description', 'supplierPrice', 'supplierPriceB', 'priceVar', 'stockValue', 'stockValueB', 'stockVar');
                $crud->where('user_id', $user_id);
                 
                $output = $crud->render();
                $this->__output($output);
            }
        }else{
            $this->load->view('principal');
        }
    }

    public function index(){
        $CI =& get_instance();
        mail('vgirbes@norauto.es', 'hola', 'hola');
        
        $this->load->library('email');
        
        //$this->email->initialize($config);
        $this->email->from('vgirbes@norauto.es', 'Stock Application');
        $this->email->to('vgirbes@norauto.es'); 
        $this->email->subject('Acción realizada con éxito');
        $this->email->message('hopla');
        if ($this->email->send()){
            echo 'OK';
        }else{
            echo $this->email->print_debugger();
        }

        if(isset($this->session->userdata['username'])){
            $user_id = $this->session->userdata['id'];
            $datos['import_state'] = $this->ficheros->import_state($user_id);
            $this->time_process->user_id = $user_id;
            $process = $this->time_process->get_process($CI, $user_id);
            if ($process != false){
                $this->time_process->flag = $process->flag;
                if ($process->flag == 'all') $datos['process_all'] = true;
                $datos['process'] = $process;
                $datos['error_process'] = $this->time_process->get_error_process($CI, $process->id);
                $datos['time_process'] = $this->time_process->get_time_process($CI);
                if ($datos['error_process'] == false){
                    $datos['editable'] = false;
                }else{
                    $datos['editable'] = true;
                }
            }else{
                $datos['editable'] = true;
            }
            
            $this->load->view('principal', $datos);
        }else{
            $this->load->view('principal');
        }
    }

    public function processtyres($provider, $user_id){
        $CI =& get_instance();
        $this->time_process->user_id = $user_id;
        $url = base_url().$this->session->userdata['lang'].'/import/'.$provider.'/'.$user_id.'/'.$this->session->userdata['token'];
        if ($this->time_process->is_ready($CI)){
            $this->time_process->url = $url;
            $this->time_process->flag = $provider;
            $this->time_process->f_start = date('Y-m-d H:i:s');
            $this->time_process->init_process($CI);
            $this->time_process->send_request();
        }
        $datos['import_state'] = $this->ficheros->import_state($user_id);
        $this->time_process->user_id = $user_id;
        $process = $this->time_process->get_process($CI, $user_id);
        if ($process != false){
            $this->time_process->flag = $process->flag;
            if ($process->flag == 'all') $datos['process_all'] = true;
            $datos['process'] = $process;
            $datos['error_process'] = $this->time_process->get_error_process($CI, $process->id);
            $datos['time_process'] = $this->time_process->get_time_process($CI);
            if ($datos['error_process'] == false){
                $datos['editable'] = false;
            }else{
                $datos['editable'] = true;
            }
        }else{
            $datos['editable'] = true;
        }
        $this->load->view('principal', $datos);
    }

    public function stockcomdep(){
        if(isset($this->session->userdata['username'])){
            $this->processtyres('comdep', $this->session->userdata['id']);        
        }
    }

    public function stockatyse(){
        if(isset($this->session->userdata['username'])){
            $this->processtyres('atyse', $this->session->userdata['id']);        
        }        
    }

    public function stockmch(){
        if(isset($this->session->userdata['username'])){
            $this->processtyres('mch', $this->session->userdata['id']);        
        }
    }

    public function stockfiles(){
        if(isset($this->session->userdata['username'])){
            $this->processtyres('generate', $this->session->userdata['id']);        
        }    
    }
}