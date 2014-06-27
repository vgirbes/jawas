<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Atyse{
    var $Provider = 'Atyse';

    function __construct(){
        $CI =& get_instance();
        $CI->load->database();
        $CI->load->helper('array');
        $CI->load->library('DB_op');
        $CI->load->library('Products_Struct');
        $CI->load->library('Regroupement_Struct');
        $CI->load->library('Ean_Struct');
        $CI->load->library('session');
    }

    public function Procesar_Items(){
    	
    }
}