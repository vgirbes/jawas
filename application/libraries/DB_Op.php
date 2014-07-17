<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class DB_op{
	var $datos_products = array();
    var $datos_regroupement = array();
    var $datos_ean = array();
    var $datos_corresfour = array();
    var $datos_provider = array();
    var $datos_userprovider = array();
    var $datos_mch = array();
    var $datos_lastdayacti = array();

	function __construct(){
		$CI =& get_instance();
        $CI->load->database();
        $CI->load->helper('array');
        $CI->load->library('session');
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

            case 'data_mch':
                $data = $this->datos_mch;
            break;

            case 'lastdayacti':
                $data = $this->datos_lastdayacti;
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

    public function Get_Default_Value($CI, $field){
        $CI->db->select('*');
        $CI->db->from('info_defaut');
        $CI->db->where('nom_champ = "'.$field.'"');
        $query = $CI->db->get();

        if ($query->num_rows > 0){
            $def = $query->result();
            $def = $def[0];
            $val = $def->val;
        }else
            $val = 0;

        return $val;
    }

    public function Get_Regroupement($CI, $codeRegroupement){
        $CI->db->select('*');
        $CI->db->from('regroupement');
        $CI->db->where('codeRegroupement', "$codeRegroupement");

        return $CI->db->get();
    }

    public function Connect_WRK(){
        $ServerName = HOST_WRK;
        $ConnectionOptions  = array("Database" => DB_WRK, "UID"=> USER_WRK, "PWD"=> PASS_WRK);

        $Conn = sqlsrv_connect($ServerName , $ConnectionOptions);

        if($Conn) 
        {
            return $Conn;
        }else{
            return false;
            die( print_r( sqlsrv_errors(), true));
        }
    }

    public function Connect_MCH(){
        $ServerName = HOST_MCH;
        $ConnectionOptions  = array("Database" => DB_MCH, "UID"=> USER_MCH, "PWD"=> PASS_MCH);

        $Conn = sqlsrv_connect($ServerName , $ConnectionOptions);

        if($Conn) 
        {
            return $Conn;
        }else{
            return false;
            die( print_r( sqlsrv_errors(), true));
        }
    }
}