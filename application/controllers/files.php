<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Files extends CI_Controller {

	public function __construct()
    {
        parent:: __construct();
        $this->load->library('session');
        $this->load->model('ficheros');
        $this->load->helper('language');
    	$this->lang->load('norauto');
    }

	public function index()
	{
		if(isset($this->session->userdata['username'])){
			$datos['lista_ficheros'] = $this->ficheros->show_files();
			if (!$datos['lista_ficheros']) $datos['error'] = lang('files.error');
			$this->load->view('principal', $datos);
		}else{
			$this->load->view('principal');
		}
	}

}
