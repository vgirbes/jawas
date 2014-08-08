<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Status extends CI_Controller{
	public function __construct(){
		parent:: __construct();
		$this->load->library('session');
		$this->load->database();
		$this->load->model('webtools');
    }

	public function index(){
		$stat = $this->webtools->pingAddress("10.250.16.17");	
		$result['stat'] = $stat;
		print json_encode($result);
	}

	public function get_notify(){
		$user_id = $this->session->userdata['id'];
		if ($user_id != ''){
			$result['num'] = $this->webtools->n_notify($user_id);
			$result['msg'] = $this->webtools->show_notify($user_id);
		}else{
			$result['msg'] = 'error';
		}
		print json_encode($result);
	}

	public function close_notify(){
		$user_id = $this->session->userdata['id'];
		if ($user_id != ''){
			$result['msg'] = $this->webtools->delete_notify($user_id);
		}else{
			$result['msg'] = 'error';
		}
		print json_encode($result);
	}
	
}