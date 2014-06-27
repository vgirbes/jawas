<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Adapter{
    var $filename = '';

	function __construct(){
		$CI =& get_instance();
        $CI->load->library('RequestProvider');
    } 

    public function Load_Provider($provider){
    	$CI =& get_instance();
    	switch($provider){
    		case 'Atyse':
    			$items = $CI->requestprovider->Cargar_Atyse();
    			return $items;
    		break;

            case 'Comdep':
                $items = $CI->requestprovider->Cargar_Comdep($CI);
                $this->filename = $CI->requestprovider->filename;
                return $items;
            break;
    	}

    }

}