<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class DB_op{
    var $user_id = '';
    var $country_id = '';
    var $user_name = '';
    var $item_up = 0;
    var $item_cf = 0;
	var $datos_products = array();
    var $datos_regroupement = array();
    var $datos_ean = array();
    var $datos_corresfour = array();
    var $datos_provider = array();
    var $datos_userprovider = array();
    var $datos_mch = array();
    var $datos_lastdayacti = array();
    var $AIH_PRIARTWEB = array();

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

    public function Info_Provider($CI, $campo, $valor, $user_id = ''){
        $CI->db->select('*');
        $CI->db->from('providers p, users_providers up');
        $CI->db->where("$campo", utf8_encode($valor));
        if ($user_id != ''){
            $CI->db->where('up.users_id', $user_id);
        }
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

    public function Get_AIH_PRIARTWEB($Conn){
        $sql = "SELECT PRIVENLOC, CODART from src.aih.AIH_PRIARTWEB where CODCEN = '9901';";
        $stmt = sqlsrv_query($Conn, $sql);
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
            $this->AIH_PRIARTWEB[$row['CODART']] = $row['PRIVENLOC'];
        }
    }

    public function Get_PRIVENLOC($idProd){
        if (array_key_exists($idProd, $this->AIH_PRIARTWEB)){
            return $this->AIH_PRIARTWEB[$idProd];
        }else{
            return false;
        }
    }

    public function Get_Regroupement($CI, $codeRegroupement, $user_id = ''){
        $CI->db->select('*');
        $CI->db->from('regroupement');
        $CI->db->where('codeRegroupement', "$codeRegroupement");
        if ($user_id != ''){
            $CI->db->where('user_id', $user_id);
        }

        return $CI->db->get();
    }

    public function Get_Usuarios($CI, $user_id = ''){
        $users = array();
        $CI->db->select('u.*, c.codbu');
        $CI->db->from('users u, countries c');
        if ($user_id != ''){
            $CI->db->where('u.id', $user_id);
        }
        $CI->db->where('u.countries_id = c.id');
        $query = $CI->db->get();
        $i = 0;

        if ($query->num_rows()>0){
            foreach ($query->result() as $ligne){
                $users[$i]['id'] = $ligne->id;
                $users[$i]['countries_id'] = $ligne->countries_id;
                $users[$i]['username'] = $ligne->username;
                $users[$i]['email'] = $ligne->email;
                $users[$i]['rol'] = $ligne->rol;
                $users[$i]['name'] = $ligne->name;
                $users[$i]['codbu'] = $ligne->codbu;
                $i++;
             }

            return $users;
        }else{
            return false;
        }
    }

    public function Get_Property_MCH($CI, $idProd, $valor, $Conn_MCH){
        $campo = $this->Get_Property_test($valor);
        $req = ("SELECT *
                FROM [REFMCH].[mch].[NCOM_PRD_PRO_BU]
                WHERE numpro = ".$campo." AND IDEPRD = ".$idProd." AND CODLANISO in ('*','fr')");
        $stmt = sqlsrv_query($Conn_MCH, $req);

        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        return strtoupper($row['VALPRO']);
    }

    public function Get_Property_MCH_List($CI, $valor, $Conn_MCH){
        $campo = $this->Get_Property_test($valor);
        $req = ("SELECT *
                FROM [REFMCH].[mch].[NCOM_PRD_PRO_BU]
                WHERE numpro = ".$campo." AND CODLANISO in ('*','fr')");
        $stmt = sqlsrv_query($Conn_MCH, $req);
        $res = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
            $res[$row['IDEPRD']] = strtoupper($row['VALPRO']);    
        }

        return $res;
    }

    public function Get_Property_test($valor){
        switch($valor){
            case 'type_pneu':
                $campo = 890;
            break;

            case 'diameter':
                $campo = 224;
            break;
        }
        
        return $campo;
    }

    public function Get_Property($valor){
        switch($valor){
            case 'type_pneu':
                $campo = 779;
            break;

            case 'diameter':
                $campo = 231;
            break;
        }
        
        return $campo;
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
            die(print_r(sqlsrv_errors(), true));
        }
    }

    public function Truncate_Tables($CI, $users, $tabla = ''){
        foreach ($users as $user){
            $CI->db->delete($tabla, array('user_id' => $user['id']));
        }

        return true;
    }

    public function Get_Destinatarios($CI, $type){
        $dest = '';
        $CI->db->select('email');
        $CI->db->from('alerts_list');
        $CI->db->where('type', $type);
        $query = $CI->db->get();

        if ($query->num_rows() > 0){
            foreach ($query->result() as $row){
                $dest .= $row->email.','; 
            }

            return rtrim($dest, ',');
        }else{
            return false;
        }
    }

    public function Get_Providers_Delay($CI, $user_id){
        $CI->db->select('SupplierKey, delay');
        $CI->db->from('users_providers');
        $CI->db->where('users_id', $user_id);
        $query = $CI->db->get();

        if ($query->num_rows() > 0){
            $res = array();
            foreach ($query->result() as $row){
                $res[$row->SupplierKey] = $row->delay;
            }

            return $res;
        }else{
            return false;
        }
    }
}