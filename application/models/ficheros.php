<?php 
class Ficheros extends CI_Model{
     
    public function __construct(){
        $this->load->library('session');
        $this->load->library('encrypt');
        $this->load->library('Adapter');
        $this->load->library('Comdep');
        $this->load->library('Atyse');
        $this->load->library('Aspitop');
        $this->load->library('Top');
        $this->load->library('DB_Op');
        $this->load->library('MCH');
        $this->load->library('Generate_Files');
        $this->load->library('RequestProvider');
        $this->load->database();
    }
       
    public function process_comdep_aty($provider, $user_id = ''){
        $res = true;
        if ($provider == 'atyse' || $provider == 'aspitop' || $provider == 'top'){
            $CI =& get_instance();
            if ($provider == 'aspitop' || $provider == 'top') $user_id = 'admin';
            $users = $this->db_op->Get_Usuarios($CI, $user_id);
            if ($users != false){
                $all = ($user_id != '' ? true : false);
                if ($provider != 'aspitop' && $provider != 'top') $this->Reset_Tables($CI, $users);
                foreach ($users as $user){
                    $res = $this->$provider->Procesar_Items($this->adapter->Load_Provider($this->$provider->Provider, $user['countries_id'], $user['username']), $user['id'], $all);
                    if ($provider == 'Top') $this->adapter->filename = '';
                    if ($res) $this->update_state($user['id'], strtoupper($provider), $this->adapter->filename);
                }
            }else{
                return false;
            }
        }else{
            $res = $this->$provider->Procesar_Items($this->adapter->Load_Provider($this->$provider->Provider), $user_id);
            if ($res) $this->update_state($user_id, strtoupper($provider), $this->adapter->filename);
        }
        
        return $res;
    }

    public function other_providers($users){
        $CI =& get_instance();
        foreach ($users->result() as $user){
            $this->db->select('*');
            $this->db->from('other_providers');
            $this->db->where('countries_id', $user->countries_id);
            $query = $this->db->get();
        
            if ($query->num_rows() > 0){
                $provider = $query->result();
                $provider = $provider[0];
                $provider_name = $this->get_provider_name($provider->id_files_providers);
                if ($provider_name){
                    $archivo = $this->requestprovider->Cargar_Archivos($CI, $user->countries_id, $user->username, $provider_name);
                    $this->otherproviders->Procesar_Items($provider_name, $archivo, $user->id, $provider->id);
                }
            }
        }

        return true;
    }

    private function get_provider_name($provider_id){
        $this->db->select('name');
        $this->db->from('files_providers');
        $this->db->where('id', $provider_id);
        $query = $this->db->get();

        if ($query->num_rows() > 0){
            $provider = $query->result();
            $provider = $provider[0];
            return $provider->name;
        }else{
            return false;
        }

    }

    public function generate_files($user_id = ''){
        $CI =& get_instance();
        $users = $this->db_op->Get_Usuarios($CI, $user_id);
        $this->db_op->Truncate_Tables($CI, $users, 'lastdayacti');
        $all = ($user_id != '' ? true : false);
        foreach ($users as $user){
            $res = $this->generate_files->do_it($user['id'], $user['username'], $user['codbu'], $user['codcen'], $all);
            if (!$res) return false;
        }
        return $res;
    }

    public function show_files(){
        $CI =& get_instance();
        $users = $this->db_op->Get_Usuarios($CI, $this->session->userdata['id']);
        $codbu = $users[0]['codbu'];
        $dir = 'assets/files/countries/'.$codbu.'/';
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
        if (preg_match('/validationProduit/i', $archivo) || preg_match('/'.$user_name.'_test_alert/i', $archivo) || preg_match('/delay_file/i', $archivo)){
            return true;
        }else{
            return false;
        }
    }

    public function send_SAP(){

    }

    public function check_import_state($user_id, $provider){
        $import = $this->import_state($user_id);
        if (isset($import[0])){
            $import = $import[0];
            $time_fecha = strtotime($import['fecha']);
            $time_fecha_b = strtotime($import['fecha'])+30;
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

    public function Reset_Tables($CI, $users){
        $CI->db_op->Truncate_Tables($CI, $users, 'products');
        $CI->db_op->Truncate_Tables($CI, $users, 'ean');
    }
}