<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Inicio extends CI_Controller {
	public function __construct()
    {
        parent:: __construct();
        $this->load->library('session');
        $this->load->helper('language');
    	$this->lang->load('norauto');
        $this->load->library('Time_Process');
        $this->load->model('ficheros');
    }

    public function __output($output = null){
        $this->load->view('principal.php', $output);
    }

	public function index()
	{
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
            }else{
                $datos['editable'] = true;
            }
            
            $this->load->view('principal', $datos);
        }else{
            $this->load->view('principal');
        }
	}

}