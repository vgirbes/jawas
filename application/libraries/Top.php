<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Top extends DB_Op{
    var $Provider = 'Top';
    var $products = '';
    var $txt = '';

    function __construct(){
        $CI =& get_instance();
        $CI->load->database();
        $CI->load->helper('array');
        $CI->load->library('Time_Process');
        $CI->load->library('Mailer');
        $CI->load->library('session');
    }

    public function Procesar_Items($archivo, $user_id = '', $all = ''){
        $CI =& get_instance();
        $archivo = 'top100.csv';
        $txt = '';
        $users = $this->Get_Usuarios($CI, $user_id);
        $CI->time_process->user_id = $user_id;
        log_message('error', 'Inicio TOP100');
        $destinatarios = $this->Get_Destinatarios($CI, 'stocks', $users[0]['countries_id']);
        $this->Get_Top($CI, $archivo, $users, $all);
        $this->Generate_Report($CI, $users[0]['id']);
        $this->Send_Report($CI, $destinatarios);
        log_message('error', 'Fin TOP100');
        return true;
    }

    private function Get_Top($CI, $archivo, $users, $all = ''){
        $row = 0;
        if (file_exists('assets/files/top/'.$archivo) && $archivo != false) 
        {
            $handle = fopen('assets/files/top/'.$archivo, "r");
            $this->user_id = $users[0]['id'];
            while ((($data = fgetcsv($handle, 3000, ',')) !== FALSE)) 
            {
                if ($row >= 1){
                    if ($data[0] != ''){
                        $this->products[] = $data[0];
                    }else{
                        log_message('error', 'No se dispone de referencia del producto.');
                    }
                }
                $row++;
            }
        }else{
            $CI->time_process->end_process($CI, $users, $all, 'error', 'file');
            return false;
        }
    }

    private function Generate_Report($CI, $user_id){
        $CI->db->select('*');
        $CI->db->from('lastdayacti');
        $CI->db->where('user_id', $user_id);
        $CI->db->where('active', 1);
        $CI->db->where('reason = "no_stock"');
        $query = $CI->db->get();

        if ($query->num_rows() > 0){
            foreach ($query->result() as $no_stock){
                if (in_array($no_stock->idProd, $this->products)){
                    $this->txt = 'El producto '.$no_stock->idProd.' se ha quedado sin stock';
                    log_message('error', 'El producto '.$no_stock->idProd.' se ha quedado sin stock');
                }
            }
        }else{
            return false;
        }

        return true;
    }

    private function Send_Report($CI, $destinatarios){
        if ($this->txt != ''){
            if ($destinatarios != false){
                $CI->mailer->to = "$destinatarios";
                $CI->mailer->subject = lang('top.asunto');
                $CI->mailer->message = $this->txt;
                $result = $CI->mailer->send();
            }else{
                return false;
            }   
        }else{
            return false;
        }

        return $result;
    }
}