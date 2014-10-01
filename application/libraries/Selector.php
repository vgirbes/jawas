<?php
class Selector{
	private $current;
	public function __construct() {
		$this->current = 0;
	}

	public function Action($provider, $CI, $country_id, $user_name) {
		if ($provider == 'MCH' || $provider == 'Top'){
			$res = true;
			if ($provider == 'Top') $res = 'Top100.csv';
			return $res;
		}else{
			$items = $CI->requestprovider->Cargar_Archivos($CI, $country_id, $user_name, $provider);
			return $items;
		}
	}
}