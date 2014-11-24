<?php 
class Providers_Model extends CI_Model{
     
    public function __construct(){
        $this->load->library('session');
        $this->load->library('encrypt');
        $this->load->library('DB_Op');
        $this->load->database();
    }
       
    public function other_provider_fields(){
        $query = $this->db->get('other_providers_fields_type');

        return $query;
    }

    public function save_other_provider($post, $prov_id){
        $edit = '';
        if ($prov_id != '') $edit = 'edit_';

        $datos = array(
            'sap_name' => $post[$edit.'name'], 
            'countries_id' => $post[$edit.'country'],
            'active' => (isset($post[$edit.'activo']) ? $post[$edit.'activo'] : 0),
            'force_stock' => (isset($post[$edit.'active_fstock']) ? $post[$edit.'active_fstock'] : 0),
            'stock_forced' => ((isset($post[$edit.'stock']) || $post[$edit.'stock'] > 0) ? $post[$edit.'stock'] : 0),
            'key_fields' => (isset($post[$edit.'link_position']) ? $post[$edit.'link_position'] : 1),
            'table_db' => $post[$edit.'mch_tables'],
            'query' => $post[$edit.'query'],
            'delay' => $post[$edit.'delay'],
            'correctionstock' => $post[$edit.'correction_stock'],
            'id_files_providers' => $post[$edit.'prov_files'],
            'provider_id' => $post[$edit.'atyse_prov']
        );

        if ($prov_id != ''){
            $res = $this->update_other_provider($post, $datos);
        }else{
            $this->db->insert('other_providers', $datos);
            $ins_id = $this->db->insert_id();
            $res = $this->save_type($post, $ins_id);
        }
        return $res;
    }

    private function update_other_provider($post, $datos){
        $this->db->where('id', $post['edit_prov_id']);
        $update = $this->db->update('other_providers', $datos);
        $res = $this->save_type($post, '', $post['edit_prov_id']);
        if ($update && $res){
            return true;
        }else{
            return false;
        }
    }

    private function save_type($post, $ins_id, $prov_id = ''){
        $edit = '';
        $res = true;
        if ($prov_id != '') {
            $edit = 'edit_';
            $this->db->delete('other_providers_fields', array('id_other_prov' => $prov_id));
            $ins_id = $prov_id;
        }
        $total = $post[$edit.'total_reg'];

        for ($i=1; $i<=$total; $i++){
            if (isset($post[$edit.'position_'.$i])){
                if ($post[$edit.'position_'.$i] != ''){
                    $datos = array(
                        'id_other_prov' => $ins_id,
                        'id_other_prov_type' => intval(str_replace('field_', '', $post[$edit.'position_'.$i])),
                        'position' => $i
                    );

                    $res = $this->db->insert('other_providers_fields', $datos);
                }
            }
        }
        return $res;
    }

    public function get_all($table){
        $this->db->select('op.*, c.id AS country_id, c.name AS country_name');
        $this->db->from($table.' op, countries c');
        $this->db->where('c.id = op.countries_id');
        $this->db->order_by('c.name');
        $query = $this->db->get();

        if ($query->num_rows()>0){
            return $query;
        }else{
            return false;
        }
    }

    public function delete_other_provider($id){
        $this->db->where('id', $id);
        $res_op = $this->db->delete('other_providers');
        $this->db->where('id_other_prov', $id);
        $res_opf = $this->db->delete('other_providers_fields');

        if ($res_op && $res_opf){
            return true;
        }else{
            return false;
        }
    }

    public function fields_saved($all_fields, $value){
        $res = array();
        if ($all_fields->num_rows()>0){
            foreach ($all_fields->result() as $provs){
                $res[$provs->id] = '';
                $this->db->select('opf.*, opft.value');
                $this->db->from('other_providers_fields opf, other_providers_fields_type opft');
                $this->db->where('id_other_prov', $provs->id);
                $this->db->where('opf.id_other_prov_type = opft.id');
                $query = $this->db->get();
                if ($query->num_rows()>0){
                    foreach($query->result() as $field){
                        $res[$provs->id][$field->$value]['name'] = $field->value;
                        $res[$provs->id][$field->$value]['id'] = $field->id_other_prov_type;
                    }
                }
            }
        }else{
            return false;
        }

        return $res;
    }

    public function get_list_tables(){
        $mch = $this->db_op->Connect_MCH();
        $req = ("SELECT * FROM sys.Tables ORDER BY name");
        $stmt = sqlsrv_query($mch, $req);
        $res = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
            $res[] = strtoupper($row['name']);    
        }

        return $res;
    }

    public function get_atyse_providers(){
        $res = array();
        $this->db->select('*');
        $this->db->from('providers');
        $query = $this->db->get();

        if ($query->num_rows() > 0){
            foreach ($query->result() as $provider){
                $res[$provider->SupplierKey] = $provider->nom;
            }

            return $res;
        }else{
            return false;
        }
    }
}