<?php 
class Alerts extends CI_Model{
	public function __construct(){
        $this->load->database();
    }

    public function Load_List($type){
    	$this->db->select('id, email');
    	$this->db->from('alerts_list');
    	$this->db->where('type', $type);
    	$query = $this->db->get();
    	if ($query->num_rows()>0){
    		return $query;
    	}else{
    		return false;
    	}
    }

    public function Save_Contact($type, $email){
    	$exist = $this->Exist_Contact($type, $email);

    	if (!$exist){
    		$res = array(
    			'email' => $email,
    			'type' => $type
    		);
    		$result = $this->db->insert('alerts_list', $res);
    		return $result;
    	}else{
    		return false;
    	}
    }

    public function Exist_Contact($type, $email){
    	$this->db->select('email');
    	$this->db->from('alerts_list');
    	$this->db->where('type', $type);
    	$this->db->where('email', $email);

    	$query = $this->db->get();
    	if ($query->num_rows()>0){
    		return true;
    	}else{
    		return false;
    	}
    }

    public function Delete_Contact($type, $email){
    	$res = array(
    		'email' => $email,
    		'type' => $type
    	);

    	$result = $this->db->delete('alerts_list', $res);
    	return $result;
    }
}