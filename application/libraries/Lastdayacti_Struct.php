<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Lastdayacti_Struct extends DB_op{
    var $lastdayacti = array(
        'idProd' => '',
        'codeRegroupement' => '',
        'statut' => '',
        'reason' => '',
        'priceRec' => '',
        'price_wrk' => '',
        'user_id' => ''
    );

    public function Load_Data($data, $i){
        $CI =& get_instance();
        foreach ($data as $clave => $valor){
            if (isset($this->lastdayacti[$clave])){
                $this->datos_lastdayacti[$i][$clave] = "$valor";
            }
        }

        return true;
    }
}