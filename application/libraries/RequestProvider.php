<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class RequestProvider{
	var $items = '';
    var $filename = '';

    function __construct(){
        $CI =& get_instance();
        $CI->load->database();
    }

    public function Cargar_Comdep($CI){
        $archivo = $this->Request_Files('COMDEP', $CI);
        //$archivo = false;
        $this->filename = $archivo;
        if ($archivo != '' && $archivo){
            $name_export_xml = 'assets/files/comdep/'.$archivo;
            if ($name_export_xml){
                $items = simplexml_load_file($name_export_xml);
                return $items;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function Cargar_Atyse(){
    	return $items;
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
        $name = $CI->session->userdata['username'];
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
            $archivo = $files[0];
            $result = ($archivo !='' && count($files)>0 ? $this->Get_File($s_data, $CI, $archivo) : $this->Get_Last_File($provider_name));
        }else{
            fwrite($fp, $output);
            fclose($fp);
            if ($s_data->ext=='zip') $archivo = $this->Unzip($archivo, $s_data, $provider, $name);
            $result = $this->Get_Last_File($provider_name);
        }

        return $result;
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

}



