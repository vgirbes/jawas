<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Providers extends CI_Controller{
	public function __construct(){
		parent:: __construct();
		$this->load->model('usuarios_proveedores');
        $this->load->model('usuarios');
        $this->load->model('providers_model');
        $this->load->library('form_validation');
		$this->load->library('DB_Op');
        $this->load->library('session');
        $this->load->database();
        $this->load->helper('url');
        $this->load->library('grocery_CRUD');
        $this->load->helper('language');
        $this->lang->load('norauto');
    }

    public function __output($output = null){
        $this->load->view('providers_list.php', $output);
    }

    public function index(){
        $this->__output((object)array('output' => '' , 'js_files' => array() , 'css_files' => array()));
    }

    public function view(){
        if(isset($this->session->userdata['username'])){
            $user_id = $this->session->userdata['id'];
            $crud = new grocery_CRUD();
            $crud->user_id = $user_id;
            $crud->set_theme('flexigrid');
            $crud->set_table('users_providers');
            $crud->display_as('SupplierKey','Id');
            $crud->where('users_id', $user_id);
            $crud->set_subject('Providers');
         
            $crud->set_relation('SupplierKey','providers','nom');
         
            $crud->unset_columns('users_id');
            $crud->edit_fields('SupplierKey', 'active', 'correctionstock', 'ecotaxe', 'CDS', 'transport', 'delay', 'RFAfixe', 'RFA_p', 'forceStock', 'stock', 'comments');
            $output = $crud->render();
            $this->__output($output);
        }else{
            $this->load->view('principal');
        }
    }

    private function get_data_for_prov($CI){
        $datos = array();
        $datos['countries'] = $this->db_op->get_countries($CI);
        $datos['fields'] = $this->providers_model->other_provider_fields();
        $datos['other_prov'] = $this->providers_model->get_all('other_providers');
        $datos['prov_files'] = $this->providers_model->get_all('files_providers');
        if ($datos['other_prov'] != false) $datos['fields_saved'] = $this->providers_model->fields_saved($datos['other_prov'], 'id_other_prov_type');
        if ($datos['other_prov'] != false) $datos['positions_saved'] = $this->providers_model->fields_saved($datos['other_prov'], 'position');

        return $datos;
    }

    private function render_other_prov($datos){
        $rol = $this->usuarios->rol_ok();
        if ($rol){
            $this->load->view('otherproviders', $datos);
        }else{
            $this->load->view('principal');
        }
    }

    public function other_providers(){
        $CI =& get_instance();
        $estado = array();
        $prov_id = '';
        if (isset($_POST['edit_prov_id'])) $prov_id = $this->input->post('edit_prov_id');

        if (isset($_POST['name']) || isset($_POST['edit_name'])){
            $res = $this->providers_model->save_other_provider($_POST, $prov_id);
            if ($res){
                $estado[0] = lang('other_provider.save_ok');
            }else{
                $estado[0] = lang('other_provider.save_error');
            }
        }
        
        $datos = $this->get_data_for_prov($CI);
        $datos['list_mch'] = $this->providers_model->get_list_tables();
        if (count($estado)>0) $datos['estado'] = $estado;
        $this->render_other_prov($datos);
        
    }

    public function delete_other_provider(){
        $CI =& get_instance();
        $estado = array();
        $id = $this->input->post('edit_prov_id');

        if ($id!='' && !is_null($id)){
            $res = $this->providers_model->delete_other_provider($id);
            if (!$res){
                $estado[0] = lang('other_provider.delete_error');
            }else{
                $estado[0] = lang('other_provider.delete_ok');    
            } 
        }else{
            $estado[0] = lang('other_provider.delete_error');
        }
        $datos = $this->get_data_for_prov($CI);
        if (count($estado)>0) $datos['estado'] = $estado;

        $this->render_other_prov($datos);
    }
}