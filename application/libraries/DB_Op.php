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
    var $TMP_PRDNOEBU = array();
    var $TMP_PRDGMABU_PRIX_OK = array();
    var $TMP_PRDGMABU_MASQ_OK = array();
    var $AIH_PRIARTWEB = array();
    var $stock_literals = array();

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

    public function Get_Default_Value($CI, $field, $countries_id = ''){
        $CI->db->select('*');
        $CI->db->from('info_defaut');
        $CI->db->where('nom_champ = "'.$field.'"');
        if ($countries_id != '') $CI->db->where('countries_id', $countries_id);
        $query = $CI->db->get();

        if ($query->num_rows > 0){
            $def = $query->result();
            $def = $def[0];
            $val = $def->val;
        }else
            $val = 0;

        return $val;
    }

    public function Get_AIH_PRIARTWEB($Conn, $codcen = '9901'){
        $sql = "SELECT PRIVENLOC, CODART from src.aih.AIH_PRIARTWEB where CODCEN = '$codcen';";
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
        $CI->db->select('u.*, c.codbu, c.codcen, c.lng, c.web');
        $CI->db->from('users u, countries c');
        if ($user_id != ''){
            if ($user_id == 'admin'){
                $CI->db->where('u.rol', 1);
            }else{
                $CI->db->where('u.id', $user_id);
            }
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
                $users[$i]['iso_code'] = $ligne->rol;
                $users[$i]['name'] = $ligne->name;
                $users[$i]['codbu'] = $ligne->codbu;
                $users[$i]['codcen'] = $ligne->codcen;
                $users[$i]['lng'] = $ligne->lng;
                $users[$i]['web'] = $ligne->web;
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

    public function Get_Providers_Reference($CI, $Conn_MCH, $codbu, $user_id){
        $result = array();
        $products = array();
        $ids = '';

        $CI->db->select('idProd');
        $CI->db->from('data_mch');
        $CI->db->where('user_id', $user_id);
        $CI->db->where('LENGTH(valPro) < 13');
        $query = $CI->db->get();

        if ($query->num_rows() > 0){
            foreach($query->result() as $prod){
                $products[$prod->idProd] = '';
                $ids .= $prod->idProd.',';
            }

            $result = $this->Get_Reference($products, rtrim($ids, ','), $codbu, $Conn_MCH);

            return $result;
        }else{
            return false;
        }
    }

    private function Get_Reference($products, $ids, $codbu, $Conn_MCH){
        log_message('error', 'Entra a Get Reference');
        $result = array();
        $req = "SELECT [IDEPRD], [VALPROSEC] FROM [REFMCH].[mch].[NCOM_PRD_PRO_BU] WHERE IDEPRD IN ($ids) AND NUMPRO = 8 AND NUMORD = 5 AND (CODBU = '$codbu' OR CODBU = '*') AND DATSUP IS NULL";
        log_message('error', $req);
        $stmt = sqlsrv_query($Conn_MCH, $req, array(), array( "Scrollable" => 'keyset' ));
        $has_rows = sqlsrv_has_rows($stmt);

        if ($has_rows){
            log_message('error', 'Entra en el bucle Get Reference');
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
                $products[$row['IDEPRD']] = $row['VALPROSEC'];
            }

            return $products;
        }else{
            return false;
        }
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

    public function Get_Destinatarios($CI, $type, $country_id){
        $dest = '';
        $CI->db->select('email');
        $CI->db->from('alerts_list');
        $CI->db->where('type', $type);
        $CI->db->where('countries_id', $country_id);
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

    public function Get_Providers_Delay($CI, $id, $delay, $table, $user_id = ''){
        $CI->db->select($id.', '.$delay);
        $CI->db->from($table);
        if ($user_id != '') $CI->db->where('users_id', $user_id);
        $query = $CI->db->get();

        if ($query->num_rows() > 0){
            $res = array();
            foreach ($query->result() as $row){
                $res[$row->$id] = $row->$delay;
            }

            return $res;
        }else{
            return false;
        }
    }

    public function get_countries($CI){
        $query = $CI->db->get('countries');

        return $query;
    }

    public function check_stock($stock, $provider_id){
        if (isset($this->stock_literals[$provider_id])){
            $stock_by_provider = $this->stock_literals[$provider_id];
            if (array_key_exists($stock, $stock_by_provider)){
                log_message('error', 'Existe stock');
                $stock = $stock_by_provider[$stock];
                log_message('error', 'Literal de stock valor: '.$stock);
            }

            return $stock;
        }else{
            return $stock;
        }
    }

    public function check_art_tables($connexion, $table = '', $codbu){
        $req = ("SELECT IDEPRD from mch.".$table." ".($table == 'TMP_PRDNOEBU' ? " WHERE CODBU = '".$codbu."' OR CODBU = '*'" : ''));
        $stmt = sqlsrv_query($connexion, $req);

        $tabla = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
            $key = $row['IDEPRD'];
            $tabla[$key] = '';
            
        }

        return $tabla;
    }

    public function get_stock_literals($CI, $provider){
        $result = array();
        $CI->db->select($provider.'_id AS prov_id, literal, value');
        $CI->db->from('stock_literals_'.$provider.'s');
        $query = $CI->db->get();

        if ($query->num_rows() > 0){
            foreach ($query->result() as $provider_row){
                $result[$provider_row->prov_id][$provider_row->literal] = $provider_row->value; 
            }

            return $result;
        }else{
            return false;
        }
    }

    public function checkArt($idProd)
    {
        if (array_key_exists($idProd, $this->TMP_PRDNOEBU) && array_key_exists($idProd, $this->TMP_PRDGMABU_PRIX_OK) && array_key_exists($idProd, $this->TMP_PRDGMABU_MASQ_OK)){
            return true;
        }else{
            return false;
        }
    }
}