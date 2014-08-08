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
        $this->load->model('usuarios');
    }

    public function index(){
        $rol = $this->usuarios->rol_ok();
    	if ($rol){
    		$datos['admin'] = true;
    		$url = base_url().$this->session->userdata['lang'].'/';
    		$datos['list_admin'][0]['name'] = lang('admin.paises');
    		$datos['list_admin'][0]['url'] = $url.'administration/load/countries';
    		$datos['list_admin'][1]['name'] = lang('admin.lista_proveedores');
    		$datos['list_admin'][1]['url'] = $url.'administration/load/list_providers';
    		$datos['list_admin'][2]['name'] = lang('admin.usuarios');
    		$datos['list_admin'][2]['url'] = $url.'administration/load/users';
    		$datos['list_admin'][3]['name'] = lang('admin.proveedores');
    		$datos['list_admin'][3]['url'] = $url.'administration/load/providers';
    		$datos['list_admin'][4]['name'] = lang('admin.defaut');
    		$datos['list_admin'][4]['url'] = $url.'administration/load/defaut';
            $datos['list_admin'][5]['name'] = 'Mensajes';
            $datos['list_admin'][5]['url'] = $url.'administration/load/messages';
    		$this->load->view('principal', $datos);
    	}else{
    		$this->load->view('principal');
    	}

    }

    public function __output($output = null){
        $this->load->view('edit_admin.php', $output);
    }

    public function load(){
        $select = $this->uri->segment(4);
        $rol = $this->usuarios->rol_ok();
        if ($select!=''){
            if ($rol){
                $table = $this->select_table($select);
                $crud = new grocery_CRUD();
                $crud->set_theme('flexigrid');
                $crud->set_table($table);
             
                $output = $crud->render();
                $this->__output($output);
            }else{
                $this->load->view('principal');
            }
        }else{
            $this->load->view('principal');
        }
    }

    public function select_table($select){
        $table = $select;
        switch ($select){
            case 'list_providers':
                $table = 'files_providers';
            break;
            case 'defaut':
                $table = 'info_defaut';
            break;
        }

        return $table;
    }

}