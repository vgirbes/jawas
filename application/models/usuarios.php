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

    public function can_create($user, $pass, $rpass){
        $err = array();
        $check_user = $this->check_user($user);
        if (!$check_user) $err[] = 'El usuario elegido ya existe, por favor, indique otro nombre de usuario';

        if ($rpass != $pass){
            $err[] = 'La contraseña no coincide';
        }else{
            if (strlen($pass) < 6){
                $err[] = 'La contraseña debe de tener almenos 6 caracteres '.$pass;
            }
        }

        return $err;

    }

    public function insert_user($user, $pass, $email, $name, $rol, $country){
        $ins = array(
            'username' => $user,
            'password' => md5($pass),
            'name' => $name,
            'rol' => $rol,
            'countries_id' => $country,
            'email' => $email,
            'token' =>  uniqid()
        );

        $res = $this->db->insert('users', $ins);

        return $res;
    }

    public function check_user($user){
        $this->db->select('id');
        $this->db->from('users');
        $this->db->where('username', $user);
        $query = $this->db->get();

        if ($query->num_rows() > 0){
            return false;
        }else{
            return true;
        }
    }

    public function rol_ok(){
        if (isset($this->session->userdata['rol'])&&($this->session->userdata['rol']==1)){
            return true;
        }else{
            return false;
        }
    }
}