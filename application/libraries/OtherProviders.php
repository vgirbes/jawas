<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class OtherProviders extends DB_op{
    var $Provider = 'OtherProviders';
    var $mch_product = array();
    var $products = array();
    var $products_file = array();
    var $ean = '';
    var $table = '';
    var $query = '';
    var $key = 0;
    var $id = '';
    var $stock = 0;
    var $fstock = false;
    var $stock_forced = 0;
    var $correction_stock = 0;
    var $ean_key = false;
    var $ideprd_key = false;
    var $provider_id = '';

    function __construct(){
        $CI =& get_instance();
        $CI->load->database();
        $CI->load->helper('array');
        $CI->load->library('Products_Struct');
        $CI->load->library('Mch_Struct');
        $CI->load->library('Ean_Struct');
        $CI->load->library('Time_Process');
        $CI->load->library('session');
    }

    public function Procesar_Items($provider, $archivo, $user_id = '', $provider_id){
        log_message('error', 'Entra procesar other provider');
        $CI =& get_instance();
        $Conn_MCH = $this->Connect_MCH();
        $this->truncate_other_providers($CI, $user_id);
        $this->provider_id = $provider_id;
        $row = 0;
        $ids = '';
        $all = ($user_id != '' ? true : false);
        $users = $this->Get_Usuarios($CI, $user_id);
        $this->stock_literals = $this->get_stock_literals($CI, 'other_provider');
        $this->get_other_provider($CI);
        $stock_id = $this->get_stock_id($CI);
        $stock_position = ($this->get_position($CI, $stock_id))-1;
        log_message('error', 'Key '.$this->key.' stock position: '.$stock_position);
        log_message('error', 'provider '.$provider.' archivo '.$archivo.' user_id '.$user_id.' provider_id '.$this->provider_id);
        if ($this->key >= 0 && $stock_position != ''){
            log_message('error', 'archivo: '.$archivo.' this->key: '.$this->key.' stock_position: '.$stock_position);
            if (file_exists('assets/files/'.$provider.'/'.$archivo) && $archivo != false){
                $handle = fopen('assets/files/'.$provider.'/'.$archivo, "r");
                
                while ((($data = fgetcsv($handle, 3000, ';')) !== FALSE)){
                    if ($row >= 1){
                        $id = $data[$this->key];
                        $ids .= $id.',';
                        log_message('error', 'Preinsert other provider de '.$id);
                        $stock = $this->check_stock($data[$stock_position], $provider_id);
                        $this->stock = $this->calculate_stock($stock);
                        $this->products_file[$id] = $this->stock;
                    }
                    $row++;
                }
                $this->get_mch_products($CI, $users[0]['codbu'], $user_id, rtrim($ids, ','), $Conn_MCH);

                log_message('error', 'Insertar datos other providers');
                $CI->products_struct->Insert_Data($CI, 'products', 'no');
                $CI->ean_struct->Insert_Data($CI, 'ean', 'no');
                $CI->mch_struct->Insert_Data($CI, 'data_mch', 'no');
            }
        }
        
        log_message('error', 'Fin other providers');
        $CI->time_process->flag = 'otherproviders';
        $CI->time_process->user_id = $users[0]['id'];
        $CI->time_process->end_process($CI, $users, $all, 'ok');
        return true;
    }

    private function truncate_other_providers($CI, $user_id){
        $CI->db->where('other_prov is not null');
        $CI->db->where('user_id', $user_id);
        $CI->db->delete('products');

        $CI->db->where('user_id', $user_id);
        $CI->db->where('LENGTH(codeRegroupement) <= 13');
        $CI->db->delete('ean');

        $CI->db->where('user_id', $user_id);
        $CI->db->where('LENGTH(valPro) <= 13');
        $CI->db->delete('data_mch');
    }

    private function get_mch_products($CI, $codbu, $user_id, $ids, $Conn_MCH){
        log_message('error', 'Iniciando consulta');
        $item = 0;
        $req = "SELECT [CODBU], [NUMPRO], [IDEPRD], [VALPRO] FROM [REFMCH].[mch].[$this->table] WHERE $this->query IN ($ids) AND NUMPRO = 8 AND NUMORD = 1 AND (CODBU = '$codbu' OR CODBU = '*') AND DATSUP IS NULL";
        log_message('error', "SELECT [CODBU], [NUMPRO], [IDEPRD], [VALPRO] FROM [REFMCH].[mch].[$this->table] WHERE $this->query IN ($ids) AND NUMPRO = 8 AND NUMORD = 1 AND (CODBU = '$codbu' OR CODBU = '*') AND DATSUP IS NULL");
        $stmt = sqlsrv_query($Conn_MCH, $req, array(), array( "Scrollable" => 'keyset' ));
        $has_rows = sqlsrv_has_rows($stmt);

        if ($has_rows){
            while ($product = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
                if ($this->exist_product($product)){
                    log_message('error', 'Introduciendo producto '.$product['IDEPRD'].' - '.$product['VALPRO']);
                    $this->mch_product[$product['IDEPRD']] = $product['VALPRO'];
                    $this->mch_product[$product['VALPRO']] = $product['IDEPRD'];
                    $key = ($this->ean_key ? $product['VALPRO'] : $product['IDEPRD']);
                    $stock = $this->products_file[$key];
                    $this->products = $this->get_products_array($user_id, $stock, $product);
                    $CI->products_struct->Load_Data($this->products, $item);
                    $datos_ean = array('codeRegroupement' => $product['IDEPRD'], 'ean' => $product['VALPRO'], 'user_id' => $user_id);
                    $CI->ean_struct->Load_Data($datos_ean, $item);
                    $data_mch = $this->get_mch_array($user_id, $product);
                    $CI->mch_struct->Load_Data($data_mch, $item);
                    $this->ean_key = false;
                    $this->ideprd_key = false;
                    $item++;
                }
            }
            return true;
        }else{
            return false;
        }
    }

    private function exist_product($product){
        if (array_key_exists($product['IDEPRD'], $this->products_file)){
            $this->ideprd_key = true;
        }

        if (array_key_exists($product['VALPRO'], $this->products_file)){
            $this->ean_key = true;
        }

        if ($this->ean_key || $this->ideprd_key){
            return true;
        }else{
            return false;
        }
    }

    public function get_mch_array($user_id, $product){
        $result = array('idProd' => $product['IDEPRD'],
            'country' => $product['CODBU'],
            'numPro' => 'codeRegroupement', 
            'valPro' => $product['IDEPRD'], 
            'user_id' => $user_id
        );

        return $result;
    }

    public function get_products_array($user_id, $stock, $product){
        $products = array(
            'codeRegroupement' => $product['IDEPRD'],
            'stockValue' => $stock,
            'other_prov' => $this->provider_id,
            'user_id' => $user_id
        );

        return $products;
    }

    private function calculate_stock($stock){
        if ($this->fstock){
            $result = $this->stock_forced - $this->correction_stock;
        }else{
            $result = $stock - $this->correction_stock;
        }

        return $result;
    }

    private function get_position($CI, $value){
        $CI->db->select('position');
        $CI->db->from('other_providers_fields');
        $CI->db->where('id_other_prov', $this->provider_id);
        $CI->db->where('id_other_prov_type', $value);
        $query = $CI->db->get();

        if ($query->num_rows() > 0){
            $position = $query->result();
            $position = $position[0];
            return $position->position;
        }else{
            return false;
        }
    }

    private function get_stock_id($CI){
        $CI->db->select('id');
        $CI->db->from('other_providers_fields_type');
        $CI->db->where('value', 'Stock');
        $query = $CI->db->get();

        if ($query->num_rows() > 0){
            $stock = $query->result();
            $stock = $stock[0];
            return $stock->id;
        }else{
            return false;
        }
    }

    private function get_other_provider($CI){
        $CI->db->select('*');
        $CI->db->from('other_providers');
        $CI->db->where('id', $this->provider_id);
        $query = $CI->db->get();

        if ($query->num_rows() > 0){
            $result = $query->result();
            $this->query = $result[0]->query;
            $this->table = $result[0]->table_db;
            $this->key = $result[0]->key_fields - 1;
            $this->correction_stock = $result[0]->correctionstock;
            $this->fstock = $result[0]->force_stock;
            $this->stock_forced = $result[0]->stock_forced;
        }
    }
}