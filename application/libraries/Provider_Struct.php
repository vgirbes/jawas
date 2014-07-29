<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Provider_Struct extends DB_op{
    var $provider = array(
        'nom' => '',
        'SupplierKey' => '',
        'commentaires' => '',
        'statut' => ''
    );

    var $codes_prod = array();
    var $codes = array();

    public function Load_Data($data, $i){
        $CI =& get_instance();
        foreach ($data as $clave => $valor){
            if (isset($this->provider[$clave])){
                $this->datos_provider[$i][$clave] = "$valor";
            }
        }

        return true;
    }

    public function Get_Codes($CI){
        $CI->db->select('*');
        $CI->db->from('providers');
        $query = $CI->db->get();

        foreach($query->result_array() as $row){
            $this->codes_prod[] = $row['SupplierKey']; 
        }

        return true;
    }

    public function Process_Provider($CI, $item, $data, $codeFour, $origin = ''){
        if (!in_array($codeFour, $this->codes_prod) && !in_array((int)$codeFour, $this->codes)){
            $insert_provider = array('nom' => utf8_encode($data[10]), 'SupplierKey' => "$codeFour", 'commentaires' => 'N', 'statut' => "$origin");
            $this->codes[] = (int)$codeFour;
            $this->Load_Data($insert_provider, $item);
            return true;
        }else{
            return false;
        }
    }
}