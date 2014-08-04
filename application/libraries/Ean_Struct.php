<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Ean_Struct extends DB_op{
    var $ean = array(
        'codeRegroupement' => '',
        'ean' => '',
        'user_id' => ''
    );

    public function Load_Data($data, $i){
        $CI =& get_instance();
        foreach ($data as $clave => $valor){
            if (isset($this->ean[$clave])){
                $this->datos_ean[$i][$clave] = "$valor";
            }
        }

        return true;
    }
}