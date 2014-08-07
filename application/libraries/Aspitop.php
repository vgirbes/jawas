<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Aspitop extends DB_Op{
    var $Provider = 'Aspitop';

    function __construct(){
        $CI =& get_instance();
        $CI->load->database();
        $CI->load->helper('array');
        $CI->load->library('Time_Process');
        $CI->load->library('session');
    }

    public function Procesar_Items($archivo, $user_id = '', $all){
        $CI =& get_instance();
        $process = false;
        $row = 0;
        $Conn_wrk = $this->Connect_WRK();
        $CI->time_process->flag = 'aspitop';
        $users = $this->Get_Usuarios($CI, $user_id);
        $CI->time_process->user_id = $user_id;
        //$this->Truncate_Tables($CI, $users, 'code6alert');
        $this->Get_AIH_PRIARTWEB($Conn_wrk);
        log_message('error', 'Inicio ASPITOP');

        if (file_exists('assets/files/aspitop/'.$archivo) && $archivo != false) 
        {
            $handle = fopen('assets/files/aspitop/'.$archivo, "r");
            $this->user_id = $user_id;
            while ((($data = fgetcsv($handle, 3000, ';')) !== FALSE) && !$process) 
            {
                if ($row >= 1){
                    log_message('error', 'Procesando idProd '.$data[0]);
                    $our_price = $this->Get_PRIVENLOC($data[0]);
                    echo 'idProd '.$data[0].' con precio de competencia '.str_replace(',', '.', $data[1]).'<br/>';
                    $price = (double)str_replace(',', '.', $data[1]);
                    if ($price > $our_price){
                        echo 'El precio de la competencia es m&aacute;s alto. Nuestro precio es de '.$our_price.' euros<br/>';
                    }else{
                        echo 'Ojo! El precio de la competencia es m&aacute;s barato. Nuestro precio es de '.$our_price.' euros<br/>';
                    }
                    echo '<hr></hr>';
                }
                $row++;
            }

            log_message('error', 'Fin ASPITOP');
            return true;

        }else{
            $CI->time_process->end_process($CI, $users, $all, 'error', 'file');
            return false;
        }
    }

}