<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Mch_Struct extends DB_op{
    var $mch = array(
        'idProd' => '',
        'country' => '',
        'numPro' => '',
        'valPro' => '',
        'user_id' => ''
    );

    public function Load_Data($data, $i){
        $CI =& get_instance();
        foreach ($data as $clave => $valor){
            if (isset($this->mch[$clave])){
                $this->datos_mch[$i][$clave] = "$valor";
            }
        }

        return true;
    }
}