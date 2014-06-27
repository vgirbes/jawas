<?php 
class Ficheros extends CI_Model{
     
    public function __construct(){
        $this->load->library('session');
        $this->load->library('encrypt');
        $this->load->library('Adapter');
        $this->load->library('Comdep');
        $this->load->database();
    }
       
    public function process_comdep_aty($provider){
        if(isset($this->session->userdata['username'])){
            $user_id = $this->session->userdata['id'];
            $res = $this->$provider->Procesar_Items($this->adapter->Load_Provider($this->$provider->Provider));
            if ($res) $this->update_state($user_id, strtoupper($provider), $this->adapter->filename);
            return $res;
        }
    }

    public function process_mch(){

    }

    public function process_competitors(){

    }

    public function send_SAP(){

    }

    public function generate_emails(){

    }

    public function update_state($user_id, $flag, $filename){
        $res = false;
        if ($user_id != ''){
            $exist = $this->state_exist($user_id);
            $data = array(
                'user_id' => $user_id,
                'fecha' => date('Y-m-d H:i:s'),
                'flag' => $flag,
                'filename' => $filename
            );

            if ($exist){
                $this->db->where('user_id', $user_id);
                $res = $this->db->update('import_state', $data); 
            }else{
                $res = $this->db->insert('import_state', $data); 
            }

            return $res;
        }else{
            return false;
        }
    }

    public function state_exist($user_id){
        $res = false;
        if ($user_id != ''){
            $this->db->select('id');
            $this->db->from('import_state');
            $this->db->where('user_id', $user_id);
            $count = $this->db->count_all_results();
            if ($count > 0) $res = true;
            return $res;
        }else{
            return false;
        }
    }

    public function import_state($user_id){
        $res = false;
        if ($user_id != ''){
            $this->db->select('*');
            $this->db->from('import_state');
            $this->db->where('user_id', $user_id);
            $query = $this->db->get();
            return $query->result_array();
        }else{
            return false;
        }
    }
}