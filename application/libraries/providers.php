<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class RequestProvider{

	var $items = '';

    public function Cargar_Atyse(){
    	return $items;
    }

    public function Cargar_Pirelli(){
    	return $items;
    }
}

class Adapter{
	function __construct(){
        $this->Request = new RequestProvider();
    } 

    public function Load_Provider($provider){
    	switch($provider){
    		case 'Atyse':
    			$items = $this->Request->Cargar_Atyse();
    			return $items;
    		break;
    	}

    }

}

class Atyse{
	var $Provider = 'Atyse';
	public function ProcesarItems($items){

	}

}
/*
$adapter = new Adapter();
$atyse = new Atyse();
$atyse->ProcesarItems($adapter->Load_Provider($atyse->Provider));*/

