<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Files extends CI_Controller {

	public function __construct()
    {
        parent:: __construct();
        $this->load->library('session');
        $this->load->model('ficheros');
    }

	public function index()
	{
		if(isset($this->session->userdata['username'])){
			$datos['lista_ficheros'] = $this->ficheros->show_files();
			if (!$datos) $datos['error'] = 'No hay ficheros para mostrar.';
			$this->load->view('principal', $datos);
		}else{
			$this->load->view('principal');
		}
	}

}
