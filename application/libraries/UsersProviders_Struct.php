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

    public function Process_UserProvider($CI, $codeFour, $insertar = false){
        if ($insertar){
            $users = $this->Get_Usuarios($CI);
            foreach ($users as $user){
                $insert_userprovider = array('SupplierKey' => "$codeFour", 'users_id' => $user['id']);
                $this->Load_Data($insert_userprovider, $this->item_up);
                $this->item_up++;
            }
            return true;
        }else{
            return false;
        }
    }
}