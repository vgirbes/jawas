<?php 
class Usuarios extends CI_Model{
     
    public function __construct(){
        $this->load->library('session');
        $this->load->library('encrypt');
        $this->load->library('Mailer');
        $this->load->database();
    }
       
    public function getLogin($username, $password){
        $data = array(
            'username' => $username,
            'password' => $password,
            'active' => 1
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
        if (!$check_user) $err[] = lang('adduser.error_1');

        if ($rpass != $pass){
            $err[] = lang('adduser.error_2');
        }else{
            if (strlen($pass) < 6){
                $err[] = lang('adduser.error_3');
            }
        }

        return $err;

    }

    public function insert_user($user, $pass, $email, $name, $rol, $country, $activo){
        $user_prov = array();
        $ins = array(
            'username' => $user,
            'password' => md5($pass),
            'name' => $name,
            'rol' => $rol,
            'countries_id' => $country,
            'email' => $email,
            'active' => $activo,
            'token' =>  uniqid()
        );

        $res = $this->db->insert('users', $ins);
        $id = $this->db->insert_id();

        $this->db->select('*');
        $this->db->from('users_providers');
        $this->db->where('users_id', 1);
        $query = $this->db->get();

        foreach ($query->result() as $row){
            $user_prov = $this->insert_user_provider($row, $id);
            $this->db->insert('users_providers', $user_prov);
        }

        if ($res) $this->send_mail($user, $pass, $email, $name);
        return $res;
    }

    private function send_mail($user, $pass, $email, $name){
        $this->mailer->to = $email;
        $this->mailer->subject = lang('mail.alta_usuario');
        $this->mailer->message = lang('mail.welcome').'<br/><br/>';
        $this->mailer->message .= 'URL: <a href="http://10.250.16.20">http://10.250.16.20</a><br/>User: '.$user.'<br/>Pass: '.$pass.'<br/>';
        $this->mailer->message .= $name;
        $this->mailer->send();
    }

    public function insert_user_provider($row, $id){
        $res = array(
            'SupplierKey' => $row->SupplierKey, 
            'users_id' => $id, 
            'active' => $row->active, 
            'correctionstock' => $row->correctionstock, 
            'ecotaxe' => $row->ecotaxe, 
            'CDS' => $row->CDS, 
            'transport' => $row->transport, 
            'delay' => $row->delay, 
            'RFAfixe' => $row->RFAfixe, 
            'RFA_p' => $row->RFA_p, 
            'comments' => $row->comments, 
            'forceStock' => $row->forceStock, 
            'stock' => $row->stock
        );

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