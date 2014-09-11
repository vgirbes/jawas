<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Aspitop extends DB_Op{
    var $Provider = 'Aspitop';

    function __construct(){
        $CI =& get_instance();
        $CI->load->database();
        $CI->load->helper('array');
        $CI->load->library('Time_Process');
        $CI->load->library('Mailer');
        $CI->load->library('session');
    }

    public function Procesar_Items($archivo, $user_id = '', $all){
        $CI =& get_instance();
        $txt = '';
        $process = false;
        $row = 0;
        $Conn_wrk = $this->Connect_WRK();
        $users = $this->Get_Usuarios($CI, $user_id);
        $CI->time_process->user_id = $user_id;
        $this->Get_AIH_PRIARTWEB($Conn_wrk, $users[0]['codcen']);
        log_message('error', 'Inicio ASPITOP');

        if (file_exists('assets/files/aspitop/'.$archivo) && $archivo != false) 
        {
            $handle = fopen('assets/files/aspitop/'.$archivo, "r");
            $this->user_id = $user_id;
            while ((($data = fgetcsv($handle, 3000, ';')) !== FALSE) && !$process) 
            {
                if ($row >= 1){
                    if ($data[0] != ''){
                        log_message('error', 'Procesando idProd '.$data[0]);
                        $our_price = $this->Get_PRIVENLOC($data[0]);
                        log_message('error', lang('aspitop.producto').': <a href="http://'.$users[0]['web'].'/INTERSHOP/web/WFS/NI-'.$users[0]['codbu'].'-Site/'.$users[0]['lng'].'/-/EUR/ViewParametricSearch-SimpleOfferSearch?SearchTerm='.$data[0].'" target="_blank">'.$data[0].'</a><br/>');
                        $price = (double)str_replace(',', '.', $data[1]);
                        if ((double)$price < $our_price){
                            $txt .= lang('aspitop.producto').': <a href="http://'.$users[0]['web'].'/INTERSHOP/web/WFS/NI-'.$users[0]['codbu'].'-Site/'.$users[0]['lng'].'/-/EUR/ViewParametricSearch-SimpleOfferSearch?SearchTerm='.$data[0].'" target="_blank">'.$data[0].'</a><br/>';
                            $txt .= lang('aspitop.precio_competencia').' '.$price.' '.lang('aspitop.mas_barato').' ('.$data[2].'). '.lang('aspitop.nuestro_precio').' '.$our_price.'.<br/><br/>';
                            log_message('error', lang('aspitop.precio_competencia').' '.$price.' '.lang('aspitop.mas_barato').' (<a href="http://'.$data[2].'" target="_blank">'.$data[2].'</a>). '.lang('aspitop.nuestro_precio').' '.$our_price.'.<br/>');
                        }
                    }else{
                        log_message('error', 'No se dispone de referencia del producto.');
                    }
                }
                $row++;
            }

            $destinatarios = $this->Get_Destinatarios($CI, 'prices', $users[0]['countries_id']);

            if ($txt != ''){
                if ($destinatarios != false){
                    $CI->mailer->to = "$destinatarios";
                    $CI->mailer->subject = lang('aspitop.asunto_informe');
                    $CI->mailer->message = $txt;
                    $CI->mailer->send();
                }   
            }

            log_message('error', 'Fin ASPITOP');
            return true;

        }else{
            $CI->time_process->end_process($CI, $users, $all, 'error', 'file');
            return false;
        }
    }

}