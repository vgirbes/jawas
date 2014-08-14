<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Adapter{
    var $filename = '';

	function __construct(){
		$CI =& get_instance();
        $CI->load->library('RequestProvider');
    } 

    public function Load_Provider($provider, $country_id = '', $user_name = ''){
    	$CI =& get_instance();
    	switch($provider){
    		case 'Atyse':
    			$items = $CI->requestprovider->Cargar_Archivos($CI, $country_id, $user_name, $provider);
                $this->filename = $CI->requestprovider->filename;
    			return $items;
    		break;

            case 'Comdep':
                $items = $CI->requestprovider->Cargar_Comdep($CI);
                $this->filename = $CI->requestprovider->filename;
                return $items;
            break;

            case 'MCH':
                return true;
            break;

            case 'Aspitop':
                $items = $CI->requestprovider->Cargar_Archivos($CI, $country_id, $user_name, $provider);
                $this->filename = $CI->requestprovider->filename;
                return $items;
            break;
    	}

    }

}