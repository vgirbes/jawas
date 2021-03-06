<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Time_Process{
	var $user_id = '';
	var $url = '';
	var $flag = '';
	var $f_start = '';
	var $state = '';

	public function __construct(){
		$CI =& get_instance();
        $CI->load->database();
        $CI->load->helper('url');
        $CI->load->helper('language');
        $CI->load->library('Mailer');
        $CI->load->library('DB_Op');
	}

	public function check(){
		$CI =& get_instance();
		$user_id = $CI->uri->segment(4);
		$token = $CI->uri->segment(5);
		$type = $CI->uri->segment(3);

		if ($user_id != '' && $token != ''){
			$auth = $this->check_auth($CI, $user_id, $token);
			if ($type == 'all' && $auth != false){
				$auth = '';
			}
			return $auth;
		}else{
			return false;
		}
	}

	public function check_auth($CI, $user_id, $token){
		$CI->db->select('*');
		$CI->db->from('users');
		$CI->db->where('id', $user_id);
		$CI->db->where('token', $token);
		$query = $CI->db->get();

		if ($query->num_rows()>0){
			return $user_id;
		}else{
			return false;
		}
	}

	public function is_ready($CI){
		$CI->db->select('*');
		$CI->db->from('process');
		$CI->db->where('id', $this->user_id);

		$query = $CI->db->get();
		if ($query->num_rows()>0){
			$ligne = $query->result();
			$row = $ligne[0];
			return $this->is_failed($CI, $row->id);
		}else{
			return true;
		}

	}

	public function is_failed($CI, $process_id){
		$CI->db->select('*');
		$CI->db->from('error_process');
		$CI->db->where('process_id', $process_id);
		$CI->db->where('user_id', $this->user_id);
		$query = $CI->db->get();

		if ($query->num_rows()>0){
			return true;
		}else{
			return false;
		}

	}

	public function send_request(){
        /*exec('curl '.$this->url);
        log_message('error', 'curl '.$this->url);*/
        $ch = curl_init();
 
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 100000000);
		 
		curl_exec($ch);
		curl_close($ch);
	}

	public function init_process($CI, $users){
		foreach($users as $user){
			$CI->db->delete('process', array('user_id' => $user['id']));
			$CI->db->delete('error_process', array('user_id' => $user['id']));
			$insert = array(
				'flag' => $this->flag,
				'state' => $this->state,
				'f_start' => $this->f_start,
				'user_id' => $user['id']
			);

			$CI->db->insert('process', $insert);
		}
	}

	public function get_process($CI, $user_id){
		$CI->db->select('*');
		$CI->db->from('process');
		if ($this->flag != ''){
			$CI->db->where('flag', $this->flag);
		}
		$CI->db->where('user_id', $this->user_id);
		$query = $CI->db->get();

		if ($query->num_rows<=0){
			return false;
		}else{
			$row = $query->result();

			return $row[0];
		}
	}

	public function end_process($CI, $users, $all, $status, $msg_error = ''){
		$process = $this->get_process($CI, $this->user_id);
		if ($all){
			switch ($status){
				case 'ok':
					$min = time() - strtotime($process->f_start);
					$CI->db->delete('process', array('user_id' => $this->user_id, 'flag' => $this->flag));
					$time = array(
						'minutes' => abs(round($min/60)),
						'type' => $this->flag
					);
					$usuario = $CI->db_op->Get_Usuarios($CI, $this->user_id);
					$this->send_mail($CI, $usuario, $process);
					$CI->db->insert('time_process', $time);
				break;
				case 'error':
					$this->insert_error($CI, $process->id, $msg_error, $this->user_id);
				break;
			}
		}else{
			if ($status == 'error'){
				foreach ($users as $user){
					$process = $this->get_process($CI, $user['id']);
					$this->insert_error($CI, $process->id, $msg_error, $user['id']);
				}
			}

		}

	}

	public function send_mail($CI, $usuario, $process){
		$txt = lang('time_process.line_1_ha_finalizado_con_exito').' '.strtoupper($process->flag).'.<br/>';
		$txt .= lang('time_process.line_2_puede_continuar_aplicacion');
		$CI->mailer->to = $usuario[0]['email'];
		$CI->mailer->subject = lang('time_process.asunto_accion_exito');
		$CI->mailer->message = $txt;
		$CI->mailer->send();
	}

	public function get_error($msg_error){
		$txt = '';
		switch ($msg_error){
			case 'db':
				$txt = 'Error '.strtoupper($this->flag).' '.lang('time_process.error_db').'.';
			break;

			case 'file':
				$txt = 'Error '.strtoupper($this->flag).' '.lang('time_process.error_file').'.';
			break;
		}

		return $txt;
	}

	public function insert_error($CI, $process_id, $msg_error = '', $user_id){
		$msg = $this->get_error($msg_error);
		$err = array(
			'msg' => $msg,
			'process_id' => $process_id,
			'user_id' => $user_id
		);

		$CI->db->insert('error_process', $err);
	}

	public function get_error_process($CI, $process_id){
		$CI->db->select('*');
		$CI->db->from('error_process');
		$CI->db->where('user_id', $this->user_id);
		$CI->db->where('process_id', $process_id);
		$query = $CI->db->get();

		if ($query->num_rows<=0){
			return false;
		}else{
			$row = $query->result();
			return $row[0];
		}
	}

	public function get_time_process($CI){
		$CI->db->select('AVG(minutes) AS minute');
		$CI->db->from('time_process');
		$CI->db->where('type', $this->flag);
		$query = $CI->db->get();

		if ($query->num_rows<=0){
			return false;
		}else{
			$row = $query->result();
			$row = $row[0];
			if ($row->minute < 59){
				return number_format($row->minute, 0).' '.lang('time_process.minutos').'.';
			}else{
				$tiempo = abs(round($row->minute/60));
				return number_format($tiempo, 0).' '.lang('time_process.horas').'.';
			}
		}
	}
}