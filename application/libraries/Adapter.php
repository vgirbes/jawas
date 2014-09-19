<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once('Command.php');
require_once('CommandSelector.php');
require_once('Invoker.php');
require_once('Selector.php');

class Adapter{
    var $filename = '';

	function __construct(){
		$CI =& get_instance();
        $CI->load->library('RequestProvider');
    } 

    public function Load_Provider($provider, $country_id = '', $user_name = ''){
    	$CI =& get_instance();
        $invoker = new Invoker();
        $selector = new  Selector();
        $command = new CommandSelector($selector, $provider, $CI, $country_id, $user_name);
        $invoker->Select($command);
        $this->filename = $CI->requestprovider->filename;
        return $command->items;
    }

}
