<?php 
class Usuarios extends CI_Model{
     
    public function __construct(){
        $this->load->library('session');
        $this->load->library('encrypt');
        $this->load->database();
    }
       
       
    public function getLogin($username, $password){
        //comprobamos que el nombre de usuario y contraseña coinciden
        $data = array(
            'username' => $username,
            'password' => $password
        );
       
        $query = $this->db->get_where('users', $data);
        return $query->result_array();
    }
       
    public function close(){
        //cerrar sesión
        return $this->session->sess_destroy();
    }
}