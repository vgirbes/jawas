<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class RequestProvider{
	var $items = '';
    var $filename = '';
    var $country_id = '';
    var $user_name = '';

    function __construct(){
        $CI =& get_instance();
        $CI->load->database();
        $CI->load->library('Time_Process');
    }

    public function Cargar_Comdep($CI){
        $archivo = $this->Request_Files('COMDEP', $CI);
        $this->filename = $archivo;
        if ($archivo != '' && $archivo){
            $name_export_xml = 'assets/files/comdep/'.$archivo;
        }else{
            $name_export_xml = false;
        }
        //$name_export_xml = 'assets/files/comdep/exportReferentiel_20140101024740.xml';
        $items = simplexml_load_file($name_export_xml);
        return $items;
    }

    public function Cargar_Atyse($CI, $country_id = '', $user_name = ''){
        $this->country_id = $country_id;
        $this->user_name = $user_name;
        if ($user_name != ''){
            $archivo = $this->Request_Files('ATYSE', $CI);
        }
        $archivo = 'maquette ZFOUR_ATYSE.csv';
        $this->filename = $archivo;
        if ($archivo != '' && $archivo){
    	   return $archivo;
        }else{
            return false;
        }
    }

    public function Cargar_Archivos($CI, $country_id = '', $user_name = '', $provider){
        $this->country_id = $country_id;
        $this->user_name = $user_name;
        if ($user_name != ''){
            $archivo = $this->Request_Files(strtoupper($provider), $CI);
        }
        //$archivo = 'maquette ZFOUR_ATYSE.csv';
        //$archivo = 'aspitop_vgirbes_test.csv';
        $this->filename = $archivo;
        if ($archivo != '' && $archivo){
           return $archivo;
        }else{
            return false;
        }
    }

    public function Cargar_Pirelli(){
    	return $items;
    }

    public function Request_Files($provider, $CI){
        $s_data = $this->Get_Provider_Info($provider, $CI);
        $result = (count($s_data)==0 ? false : $this->Get_File($s_data[0], $CI));
        return $result;
    }

    public function Get_Provider_Info($provider, $CI){
        $CI->db->select('*');
        $CI->db->from('files_providers');
        $CI->db->where('name', "$provider");
        if ($provider == 'ATYSE' || $provider == 'ASPITOP'){
            $CI->db->where('countries_id', $this->country_id);
        }
        $query = $CI->db->get();
        return $query->result();
    }

    public function Get_Last_File($provider_name){
        $directorio = opendir('assets/files/'.$provider_name);
        $fecha = date('Ymd');
        $f_aux = 0;
        $archivo_def = '';
        while ($archivo = readdir($directorio)) { 
            $f_archivo = filemtime('assets/files/'.$provider_name.'/'.$archivo);
            if (date("Ymd", $f_archivo)==$fecha && $archivo != '.' && $archivo!='..'){
                $fecha_exp = date('YmdHis', $f_archivo);
                if ($fecha_exp > $f_aux){
                    $archivo_def = $archivo;
                    $f_aux = $fecha_exp;
                }
            }
        }
        return $archivo_def;
    }

    public function Get_File($s_data, $CI, $archivo = ''){
        $username = $s_data->user;
        $password = $s_data->password;
        $name = ($this->user_name =! '' ? $this->user_name : 'all');
        $provider_name = strtolower($s_data->name);
        $provider = $provider_name.'_'.$name.'.'.$s_data->ext;
        $download = ($archivo != '' ? substr($archivo, 0, strlen($archivo) - 1) : '');
        $url = $s_data->server.'/'.$s_data->source.$download;
        $ftp_server = $s_data->protocol.'://' . $username . ':' . $password . '@' . $url;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ftp_server);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_FTP_SSL, CURLFTPSSL_TRY);
        curl_setopt($ch, CURLOPT_HEADER, true);

        if ($archivo != ''){
            $fp = fopen('assets/files/'.$provider, 'w');
            curl_setopt($ch, CURLOPT_FILE, $fp);
        }else{
            curl_setopt($ch, CURLOPT_FTPLISTONLY, TRUE);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);

        if ($archivo == ''){
            $files = explode("\n", $output);
            $cur = count($files)-2;
            $archivo = ($provider_name == 'aspitop' ? $s_data->file.'09082014.'.$s_data->ext.' ' : $files[0]);
            $result = ($archivo !='' && count($files)>0 ? $this->Get_File($s_data, $CI, $archivo) : $this->Get_Last_File($provider_name));
        }else{
            fwrite($fp, $output);
            fclose($fp);
            $size = $this->Get_File_Size($ftp_server, $CI);
            if (filesize('assets/files/'.$provider) == $size){
                if ($s_data->ext=='zip'){
                    $archivo = $this->Unzip($archivo, $s_data, $provider, $name);
                }else $archivo = $this->MoveCSV($archivo, $s_data, $provider);
            }

            $result = $this->Get_Last_File($provider_name);
        }
        return $result;
    }

    public function Get_File_Size($ftp_server, $CI){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ftp_server);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_FTP_SSL, CURLFTPSSL_TRY);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);

        return $size;
    }

    public function Unzip($archivo, $s_data, $provider, $name = ''){
        $zip = new ZipArchive;
        $folder = strtolower($s_data->name);

        if (file_exists('assets/files/'.$provider)){
            $res = $zip->open('assets/files/'.$provider);
            if ($res === TRUE){
                $path = 'assets/files/'.$folder.'/'; 
                $path = str_replace("\\","/", $path); 
                $zip->extractTo($path); 
                $zip->close(); 
                return true; 
            } else { 
                return false;
            } 
        }else{
            return false;
        }
    }

    public function MoveCSV($archivo, $s_data, $provider){
        $folder = strtolower($s_data->name);

        if (file_exists('assets/files/'.$provider)){
            copy('assets/files/'.$provider , 'assets/files/'.$folder.'/'.$provider);
            return true;
        }else{
            return false;
        }
    }

}



