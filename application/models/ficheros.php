<?php 
class Ficheros extends CI_Model{
     
    public function __construct(){
        $this->load->library('session');
        $this->load->library('encrypt');
        $this->load->library('Adapter');
        $this->load->library('Comdep');
        $this->load->library('Atyse');
        $this->load->library('MCH');
        $this->load->library('Generate_Files');
        $this->load->database();
    }
       
    public function process_comdep_aty($provider){
        if(isset($this->session->userdata['username'])){
            $res = true;
            $user_id = $this->session->userdata['id'];
            $importacion = $this->check_import_state($user_id, $provider);
            if ($importacion) $res = $this->$provider->Procesar_Items($this->adapter->Load_Provider($this->$provider->Provider));
            if ($res) $this->update_state($user_id, strtoupper($provider), $this->adapter->filename);
            return $res;
        }
    }

    public function generate_files(){
        $res = $this->generate_files->do_it();
        return $res;
    }

    public function show_files(){
        $dir = 'assets/files/';
        $user_name = $this->session->userdata['username'];
        $files = array();
        $item = 0;
        if (is_dir($dir))
        {
            $d=opendir($dir); 
            while( $archivo = readdir($d) )
            {
                if ( $archivo!="." AND $archivo!=".."  )
                {
                    $found = $this->file_exist($archivo, $user_name, $dir);
                    if ($found){
                        $f_archivo = filemtime($dir.$archivo);
                        $f_hoy = date('Ymd');
                        if (date("Ymd", $f_archivo) == $f_hoy){
                            $files[$item]['file'] = base_url().$dir.$archivo;
                            $files[$item]['file_name'] = $archivo;
                            $files[$item]['date'] = date('Y-m-d H:i:s', $f_archivo);
                            $item++;
                        }
                    }     
                }
            }

            return $files;

        }else
            return false; 
    }

    public function file_exist($archivo, $user_name, $dir){
        if (preg_match('/'.$user_name.'_validationProduit/i', $archivo) || preg_match('/'.$user_name.'_test_alert/i', $archivo)){
            return true;
        }else{
            return false;
        }
    }

    public function process_competitors(){

    }

    public function send_SAP(){

    }

    public function generate_emails(){

    }

    public function check_import_state($user_id, $provider){
        $import = $this->import_state($user_id);
        $import = $import[0];
        $time_fecha = strtotime($import['fecha']);
        $time_fecha_b = strtotime($import['fecha'])+300;
        $time_actual = time();
        $f_actual = date('Y-m-d');
        $f_import = date('Y-m-d', $time_fecha);
        $h_import = date('H:i', $time_fecha);
        if (($f_actual == $f_import) && (strtoupper($provider) == $import['flag'])){
            if ($time_actual <= $time_fecha_b){
                return false;
            }else{
                return true;
            }
        }else{
            return true;
        }
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