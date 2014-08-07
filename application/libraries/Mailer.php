<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Mailer{

	var $from_name = 'Stock Application';
	var $from = 'no-reply@norauto.com';
	var $to = '';
	var $message = '';
	var $subject = '';

	public function __construct(){
		$CI =& get_instance();
        $CI->load->database();
        $CI->load->helper('url');
        $CI->load->helper('language');
        $CI->load->library('email');
	}

	public function send(){
		$CI =& get_instance();
		$CI->email->from($this->from, $this->from_name);
		$CI->email->to($this->to); 
		$CI->email->subject($this->subject);
		$CI->email->message($this->message);

		$CI->email->send();
	}

}