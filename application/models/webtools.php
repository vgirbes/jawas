<?php 
class Webtools extends CI_Model{
     
    public function __construct(){
        $this->load->library('session');
        $this->load->library('encrypt');
        $this->load->database();
    }
       
    function pingAddress($ip) {
        $pingresult = exec("ping  -n 3 $ip", $outcome, $status);
        if (0 == $status) {
            $status = $outcome[9];
        } else {
            $status = "";
        }

        return $status;

    }

    public function query_notify($user_id){
        $this->db->select('*');
        $this->db->from('messages m');
        $this->db->where('m.id not in (SELECT messages_id FROM messages_check mc WHERE mc.user_id = '.$user_id.')', NULL, FALSE);

        return $this->db->get();
    }

    public function n_notify($user_id){
        $query = $this->query_notify($user_id);

        return $query->num_rows();
    }

    public function show_notify($user_id){
        $text = '';
        $query = $this->query_notify($user_id);
        foreach ($query->result() as $row){
            $text .= $row->f_created.'<br/><br/>'.$row->text;
        }

        return $text;
    }

    public function delete_notify($user_id){
        $query = $this->query_notify($user_id);
        foreach ($query->result() as $row){
            $datos = array(
                'messages_id' => $row->id,
                'user_id' => $user_id
            );
            $this->db->insert('messages_check', $datos);
        }

        return true;
    }
}