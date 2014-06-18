<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Usuarios_proveedores extends CI_Model{
	public function __construct(){
        $this->load->database();
    }

    public function Load_Providers_List(){
    	$this->db->select('*');
    	$this->db->from('providers p, users_providers up');
    	$this->db->where('up.providers_id = p.SupplierKey');
        $query = $this->db->get();

    	return $query->result();
    }
}