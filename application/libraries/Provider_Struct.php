<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Provider_Struct extends DB_op{
    var $provider = array(
        'nom' => '',
        'SupplierKey' => '',
        'commentaires' => ''
    );

    public function Load_Data($data, $i){
        $CI =& get_instance();
        foreach ($data as $clave => $valor){
            if (isset($this->provider[$clave])){
                $this->datos_provider[$i][$clave] = "$valor";
            }
        }

        return true;
    }

    public function Process_Provider($CI, $item, $data, $codeFour){
        $CI->db->select('*');
        $CI->db->from('providers');
        $CI->db->where('SupplierKey', "$codeFour");
        $query = $CI->db->get();
        if ($query->num_rows()<=0){
            $insert_provider = array('nom' => utf8_encode($data[11]), 'SupplierKey' => "$codeFour", 'commentaires' => 'N');
            $this->Load_Data($insert_provider, $item);
            return true;
        }else{
            return false;
        }
    }
}