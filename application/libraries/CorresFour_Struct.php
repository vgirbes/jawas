<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class CorresFour_Struct extends DB_op{

    var $corresfour = array(
        'codeFour' => '',
        'nomFour' => '',
        'user_id' => ''
    );

    var $codes = array();
    var $corres = array();

    public function Load_Data($data, $i){
        $CI =& get_instance();
        foreach ($data as $clave => $valor){
            if (isset($this->corresfour[$clave])){
                $this->datos_corresfour[$i][$clave] = "$valor";
            }
        }

        return true;
    }

    public function Get_Codes($CI){
        $CI->db->select('codeFour');
        $CI->db->from('corres_four');
        $query = $CI->db->get();
        foreach($query->result_array() as $row){
            $this->corres[] = $row['codeFour']; 
        }

        return true;
    }

    public function Code_Four($CI, $item, $data){
        $codeFour = 20000 + rand(1, 999);
        
        $ok = false;
        while (!$ok){
            if (in_array($codeFour, $this->corres) || in_array($codeFour, $this->codes)){
                $codeFour = 20000 + rand(1, 999);
            }else{
                $this->codes[] = $codeFour;
                $ok = true;
                
            }
        }

        $insert_corres_four = array('nomFour' => "$data[11]", 'codeFour' => "$codeFour", 'user_id', $CI->session->userdata['id']);
        $this->Load_Data($insert_corres_four, $item);

        return $codeFour;
    }
}