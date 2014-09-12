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
        $this->load->model('alerts');
        $this->load->library('form_validation');
    }

    public function index(){
        $rol = $this->usuarios->rol_ok();
        $datos = array();
        $this->session->unset_userdata('country');
    	if ($rol){
    		$datos['admin'] = true;
    		$url = base_url().$this->session->userdata['lang'].'/';
            $datos['list_admin'] = true;
            $datos['list_providers'][0]['name'] = lang('admin.proveedores');
            $datos['list_providers'][0]['url'] = $url.'administration/load/providers';
            $datos['list_providers'][1]['name'] = lang('admin.lista_proveedores');
            $datos['list_providers'][1]['url'] = $url.'administration/load/list_providers';

            $datos['list_users'][0]['name'] = lang('admin.usuarios');
            $datos['list_users'][0]['url'] = $url.'administration/load/users';
            $datos['list_users'][1]['name'] = lang('adduser.form_name');
            $datos['list_users'][1]['url'] = $url.'users/add';
            $datos['list_users'][2]['name'] = lang('general.mensajes');
            $datos['list_users'][2]['url'] = $url.'administration/load/messages';

            $datos['list_config'][0]['name'] = lang('admin.paises');
            $datos['list_config'][0]['url'] = $url.'administration/load/countries';
            $datos['list_config'][1]['name'] = lang('admin.defaut');
            $datos['list_config'][1]['url'] = $url.'administration/load/defaut';
            $datos['list_config'][2]['name'] = lang('admin.lista_alertas');
            $datos['list_config'][2]['url'] = 'javascript:show_alert()';
            
    	}

        $this->load->view('principal', $datos);
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

    public function gapps($auth_code){
        $result = array();
        $client_id = CLIENT_ID;
        $client_secret = CLIENT_SECRET;
        $redirect_uri = 'http://localhost/es/administration/alerts';
        $max_results = 50;

        $fields=array(
            'code'=>  urlencode($auth_code),
            'client_id'=>  urlencode($client_id),
            'client_secret'=>  urlencode($client_secret),
            'redirect_uri'=>  urlencode($redirect_uri),
            'grant_type'=>  urlencode('authorization_code')
        );
        $post = '';
        foreach($fields as $key=>$value) { $post .= $key.'='.$value.'&'; }
        $post = rtrim($post,'&');

        $accesstoken = $this->alerts->Get_Token($post);
        $contacts = $this->alerts->Get_Google_Contacts($max_results, $accesstoken);

        return $contacts;
    }

    public function alerts(){
        $datos = array();
        $estado = array();
        $rol = $this->usuarios->rol_ok();
        $type = $this->uri->segment(4);
        $pais = (isset($this->session->userdata['country']) ? $this->session->userdata['country'] : $this->uri->segment(5));
        log_message('error', 'id de pais '.$pais);
        
        if ($type == ''){
            $type = $this->session->userdata['type_list'];
        }else{
            $this->session->set_userdata('type_list', $type);
        }
        
        if ($rol && $type != ''){
            if (isset($_GET['code'])){
                $datos['contacts'] = $this->gapps($_GET['code']);
                log_message('error', 'entra gapps '.$_GET['code']);
            }
            $type_save = $this->input->post('type');
            if ($type_save != ''){
                $datos['errores'] = '';
                $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
                if ($this->form_validation->run() == FALSE){
                    $estado[] = validation_errors();
                }

                if (count($estado) <= 0){
                    $result = $this->alerts->Save_Contact($type_save, $this->input->post('email'), $pais);
                    if (!$result) $datos['errores'] = lang('alerts.contacto_existe');
                }else{
                    $datos['errores'] = $estado;
                }
                $datos['email'] = $this->input->post('email');
                print json_encode($datos);
            }else{
                $this->render_page($type, $pais, $datos);
            }
               
        }else{
            $this->load->view('principal');
        }
    }

    public function render_page($type, $pais, $datos){
        if ($pais == ''){
            $query = $this->db->get('countries');
            $datos['query'] = $query;
            $datos['lista_tipo'] = 'country';
        }else{
            $this->session->set_userdata('country', $pais);
            $datos['lista_emails'] = $this->alerts->Load_List($type, $pais);
            $datos['lista_tipo'] = $type;
            $datos['country'] = $pais;
        }
        $this->load->view('principal', $datos);
    }

    public function deletealerts(){
        $rol = $this->usuarios->rol_ok();
        $type = $this->uri->segment(4);
        $pais = ($this->uri->segment(5) == '' ? $this->input->post('country_id') : $this->uri->segment(5));
        log_message('error', 'pais delete '.$pais);
        $datos = array();
        $estado = '';
        if ($rol && $type != ''){
            $datos['errores'] = '';
            $result = $this->alerts->Delete_Contact($type, $this->input->post('email'), $pais);
            if (!$result) $datos['errores'] = lang('alerts.error_borrar');
            $datos['email'] = $this->input->post('email');
            print json_encode($datos);
        }else{
            $this->load->view('principal');
        }
    }

}