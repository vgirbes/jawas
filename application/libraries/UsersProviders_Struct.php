<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class UsersProviders_Struct extends DB_op{
    var $userprovider = array(
        'SupplierKey' => '',
        'users_id' => ''
    );

    public function Load_Data($data, $i){
        $CI =& get_instance();
        foreach ($data as $clave => $valor){
            if (isset($this->userprovider[$clave])){
                $this->datos_userprovider[$i][$clave] = "$valor";
            }
        }

        return true;
    }

    public function Process_UserProvider($CI, $item, $codeFour, $insertar = false){
        if ($insertar){
            $insert_userprovider = array('SupplierKey' => "$codeFour", 'users_id' => $CI->session->userdata['id']);
            $this->Load_Data($insert_userprovider, $item);
            return true;
        }else{
            return false;
        }
    }
}