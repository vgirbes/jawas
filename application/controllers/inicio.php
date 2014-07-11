<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Inicio extends CI_Controller {
	public function __construct()
    {
        parent:: __construct();
        $this->load->library('session');
        $this->load->helper('language');
        $this->load->helper('cookie');
    	$this->lang->load('norauto');
    }

    public function __output($output = null){
        var_dump($this->input->cookie('stock_lng'));
        $this->load->view('principal.php', $output);
    }

	public function index()
	{
		$this->__output((object)array('output' => '' , 'js_files' => array() , 'css_files' => array()));
	}

}