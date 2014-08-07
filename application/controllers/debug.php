<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Debug extends CI_Controller{
     
    public function index(){
        if (file_exists('application/logs/log-'.date('Y-m-d').'.php')) 
        {
            $fichero = 'application/logs/log-'.date('Y-m-d').'.php';
            $filas = file($fichero);
            $ultima_linea = count($filas);
            $debug = $filas[$ultima_linea-4].'<br/>';
            $debug .= $filas[$ultima_linea-3].'<br/>';
            $debug .= $filas[$ultima_linea-2].'<br/>';
            $debug .= $filas[$ultima_linea-1].'<br/>';
            $result['msg'] = $debug;
        }else{
            $result['msg'] = 'De momento nada';
        }

        print json_encode($result);
    }
      
}