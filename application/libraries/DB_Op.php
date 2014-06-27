<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class DB_op{
	var $datos_products = array();
    var $datos_regroupement = array();
    var $datos_ean = array();

	function __construct(){
		$CI =& get_instance();
        $CI->load->database();
        $CI->load->helper('array');
    } 

	public function Insert_Data($CI, $tabla = 'all'){ 
        $user_id = $CI->session->userdata['id'];
        $CI->db->delete($tabla, array('user_id' => $user_id)); 

        switch($tabla){
            case 'products':
                $data = $this->datos_products;
            break;

            case 'regroupement':
                $data = $this->datos_regroupement;
            break;

            case 'ean':
                $data = $this->datos_ean;
            break;
        }

        if (!is_null($data) && count($data)>0){
            if ($CI->db->insert_batch($tabla, $data)){
                return true;
            }else{
                return false;
            }
        }else{
            return true;
        }
    }
}