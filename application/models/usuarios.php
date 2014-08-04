<?php 
class Usuarios extends CI_Model{
     
    public function __construct(){
        $this->load->library('session');
        $this->load->library('encrypt');
        $this->load->database();
    }
       
       
    public function getLogin($username, $password){
        $data = array(
            'username' => $username,
            'password' => $password
        );
       
        $query = $this->db->get_where('users', $data);
        return $query->result_array();
    }

    public function getCountry($id){
        $this->db->select('iso_code');
        $this->db->from('countries');
        $this->db->where('id', $id);
        $query = $this->db->get();

        if ($query->num_rows() > 0){
            $ligne = $query->result();
            $ligne = $ligne[0];

            return $ligne->iso_code;
        }else{
            return 'es';
        }
    }
       
    public function close(){
        return $this->session->sess_destroy();
    }
}