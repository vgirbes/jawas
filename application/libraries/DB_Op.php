<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class DB_op{
	var $datos_products = array();
    var $datos_regroupement = array();
    var $datos_ean = array();
    var $datos_corresfour = array();
    var $datos_provider = array();
    var $datos_userprovider = array();

	function __construct(){
		$CI =& get_instance();
        $CI->load->database();
        $CI->load->helper('array');
    } 

	public function Insert_Data($CI, $tabla = 'all', $truncate = 'si'){ 
        if ($truncate == 'si'){
            $user_id = $CI->session->userdata['id'];
            $CI->db->delete($tabla, array('user_id' => $user_id)); 
        }

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

            case 'corres_four':
                $data = $this->datos_corresfour;
            break;

            case 'providers':
                $data = $this->datos_provider;
            break;

            case 'users_providers':
                $data = $this->datos_userprovider;
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

    public function Info_Provider($CI, $campo, $valor){
        $CI->db->select('*');
        $CI->db->from('providers p, users_providers up');
        $CI->db->where("$campo", utf8_encode($valor));
        $CI->db->where('p.SupplierKey = up.SupplierKey');
        $query = $CI->db->get();
        return $query;
    }
}