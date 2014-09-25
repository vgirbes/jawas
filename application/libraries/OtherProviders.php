<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class OtherProviders{
    var $Provider = 'OtherProviders';
    var $mch_product = array();
    var $products = array();
    var $ean = '';
    var $table = '';
    var $query = '';
    var $key = 0;
    var $id = '';
    var $stock = 0;
    var $fstock = false;
    var $stock_forced = 0;
    var $correction_stock = 0;

    function __construct(){
        $CI =& get_instance();
        $CI->load->database();
        $CI->load->helper('array');
        $CI->load->library('DB_op');
        $CI->load->library('Products_Struct');
        $CI->load->library('Mch_Struct');
        $CI->load->library('Ean_Struct');
        $CI->load->library('Time_Process');
        $CI->load->library('session');
    }

    public function Procesar_Items($provider, $archivo, $user_id = '', $provider_id){
        $CI =& get_instance();
        $Conn_MCH = $CI->db_op->Connect_MCH();
        $row = 0;
        $item = 0;
        $users = $CI->db_op->Get_Usuarios($CI, $user_id);
        $this->get_other_provider($CI, $provider_id);
        $stock_id = $this->get_stock_id($CI);
        $stock_position = ($this->get_position($CI, $provider_id, $stock_id))-1;
        $key_position = ($this->get_position($CI, $provider_id, $this->key))-1;

        if ($key_position != '' && $stock_position != ''){
            if (file_exists('assets/files/'.$provider.'/'.$archivo) && $archivo != false){
                while ((($data = fgetcsv($handle, 3000, ';')) !== FALSE)){
                    if ($row > 1){
                        $id = $data[$key_position];
                        $this->stock = $this->calculate_stock($data[$stock_position]);
                        $exist = $this->get_mch_products($CI, $users[0]['codbu'], $id, $Conn_MCH);
                        if ($exist){
                            $this->products = $this->get_products_array($user_id);
                            $CI->products_struct->Load_Data($this->products, $item);
                            $datos_ean = array('codeRegroupement' => "", 'ean' => $this->ean, 'user_id'  => $user_id);
                            $CI->ean_struct->Load_Data($datos_ean, $item);
                            $data_mch = $this->get_mch_array($user_id);
                            $CI->mch_struct->Load_Data($data_mch, $item);
                            $item++;
                        }
                    }
                    $row++;
                }
                $CI->products_struct->Insert_Data($CI, 'products', 'no');
                $CI->ean_struct->Insert_Data($CI, 'ean', 'no');
                $CI->mch_struct->Insert_Data($CI, 'data_mch', 'no');
            }
        }
        
        log_message('error', 'Fin');
        $CI->time_process->end_process($CI, $users, $all, 'ok');
        return true;
    }

    public function get_mch_array($user_id){
        $result = array('idProd' => $this->mch_product['IDEPRD'],
            'country' => $thiS->mch_product['CODBU'],
            'numPro' => $this->mch_product['CODPRO'], 
            'valPro' => strip_tags($this->ean), 
            'user_id' => $user_id
        );

        return $result;
    }

    public function get_products_array($user_id){
        $products = array(
            'codeRegroupement' => $this->ean,
            'stockValue' => $this->stock,
            'other_prov' => 1,
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

    private function get_mch_products($CI, $codbu, $id, $Conn_MCH){
        $req = ("SELECT TOP 1 FROM [REFMCH].[mch].[$this->table] WHERE $this->query ".$id." AND (CODBU = '$codbu' OR CODBU = '*')");
        $stmt = sqlsrv_query($Conn_MCH, $req);

        if (sqlsrv_num_rows($stmt) > 0){
            $product = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            $this->mch_product = $product;
            $this->ean = $product['VALPRO'];
            return $true;
        }else{
            return false;
        }
    }

    private function get_position($CI, $provider_id, $value){
        $CI->db->select('position');
        $CI->db->from('other_providers_fields');
        $CI->db->where('id_other_prov', $provider_id);
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

    private function get_other_provider($CI, $provider_id){
        $CI->db->select('*');
        $CI->db->from('other_providers');
        $CI->db->where('id', $provider_id);
        $query = $CI->db->get();

        if ($query->num_rows() > 0){
            $result = $query->result();
            $this->query = $result[0]->query;
            $this->table = $result[0]->table_db;
            $this->key = $result[0]->key_fields;
            $this->correction_stock = $result[0]->correctionstock;
            $this->fstock = $result[0]->force_stock;
            $this->stock_forced = $result[0]->stock_forced;
        }
    }
}