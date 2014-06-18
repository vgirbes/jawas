<?php 
class Action extends CI_Controller{
	public function __construct(){
            parent:: __construct();
            $this->load->model('ficheros');
    }

    public function processproviders(){
    	$data = $this->ficheros->process_suppliers();
    	$this->load->view('principal');
    }
}