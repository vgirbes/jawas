<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Generate_Files extends DB_Op{
    var $item = 0;
    var $user_id = '';
    var $user_name = '';
    var $providers_delay = array();
    var $other_providers_delay = array();
    var $codbu = '';
    var $delay_file = '';
    var $validation_file = '';
    var $stock_mini = 0;
    var $providers_reference = array();
    var $country_id = '';
    var $lastdayacti_products = array();
    var $days = 6;
    var $tva = 0;
    var $p_stock = 0;

    function __construct(){
        $CI =& get_instance();
        $CI->load->database();
        $CI->load->helper('array');
        $CI->load->library('session');
        $CI->load->library('Time_Process');
        $CI->load->library('Lastdayacti_Struct');
    }

    public function do_it($user_id, $user_name, $codbu, $codcen, $country_id, $all){
        $CI =& get_instance();
        $this->user_id = $user_id;
        $this->country_id = $country_id;
        $this->user_name = $user_name;
        $CI->time_process->flag = 'generate';
        $CI->time_process->user_id = $user_id;
        $Conn = $this->Connect_MCH();
        $Conn_wrk = $this->Connect_WRK();
        log_message('error', 'Entra files '.$user_id);
        $users = $this->Get_Usuarios($CI, $user_id);
        $this->stock_mini = $this->Get_Default_Value($CI, 'stock_mini', $users[0]['countries_id']);
        $marge_e = $this->Get_Default_Value($CI, 'marge_e', $users[0]['countries_id']);
        $marge_p = $this->Get_Default_Value($CI, 'marge_p', $users[0]['countries_id']);
        $this->tva = 1 + ($this->Get_Default_Value($CI, 'TVA', $users[0]['countries_id']) / 100);
        $this->p_stock = 1 + ($this->Get_Default_Value($CI, 'p_stock', $users[0]['countries_id']) / 100);
        $query = $this->Get_Data_To_File($CI, $user_id);
        $this->providers_reference = $this->Get_Providers_Reference($CI, $Conn, $codbu, $user_id);
        $this->codbu = $codbu;
        $this->providers_delay = $this->Get_Providers_Delay($CI, 'SupplierKey', 'delay', 'users_providers', $user_id);
        $this->other_providers_delay = $this->Get_Providers_Delay($CI, 'id', 'delay', 'other_providers');
        $this->Get_All_Lastdayacti($CI);
        $is_other_provider = false;
        $save = 0;
        $no_price = 0;
        $ret = 0;
        $stock_ret = 0;
        $i = 2;
        $false = 0;
        $count = 0;
        $name_file_csv = 'assets/files/countries/OBJNAT_MASSE_'.date('YmdHis').'.csv';
        $this->TMP_PRDNOEBU = $this->check_art_tables($Conn, 'TMP_PRDNOEBU', $codbu);
        $this->TMP_PRDGMABU_PRIX_OK = $this->check_art_tables($Conn, 'TMP_PRDGMABU_PRIX_OK', $codbu);
        $this->TMP_PRDGMABU_MASQ_OK = $this->check_art_tables($Conn, 'TMP_PRDGMABU_MASQ_OK', $codbu);
        $this->Get_AIH_PRIARTWEB($Conn_wrk, $codcen);
        $name_file_csv = 'assets/files/countries/OBJNAT_MASSE_'.date('YmdHis').'.csv';
        $delay_file_name = 'assets/files/countries/DELAI_FIA_'.date('YmdHis').'.csv';
        $this->delay_file = @fopen($delay_file_name, 'w+');
        $line_delay = 'CODBU;provider;id_prod;delay';
        @fputcsv($this->delay_file, explode(',', $line_delay));

        if ($query->num_rows()>0){
            foreach ($query->result() as $ligne)
            {
                log_message('error', 'Entra FILES idProd '.$ligne->idProd.' Usuario '.$user_id);
                $count++;
                $row['PRIVENLOC'] = $this->Get_PRIVENLOC($ligne->idProd);
                if ($row['PRIVENLOC'] != false)
                {
                    $is_other_provider = (!is_null($ligne->other_prov) && $ligne->other_prov == 1 ? true : false);
                    if ($is_other_provider){
                        log_message('error', 'Gestionando producto other provider '.$ligne->idProd);
                        $this->Process_Other_Provider($CI, $ligne, $user_id);
                    }else{
                        if ($this->checkArt($ligne->idProd) == 1)
                        {
                            log_message('error', 'Entra FILES idProd procesado '.$ligne->idProd);
                            if ($ligne->stockValue >= $this->stock_mini)
                            {
                                if ($ligne->priceMin == -1)
                                {
                                    $this->calcActi($CI, $ligne, 'no_price');
                                    $no_price++;
                                }
                                else
                                {
                                    $result = (((double)$ligne->priceMin + (double)$marge_e) * (1 + ((double)$marge_p / 100))) * ((double)$this->tva);

                                    if ((double)$result <= (double)$row['PRIVENLOC'])
                                    {
                                        $this->calcActi($CI, $ligne, 'good', $row, $result);
                                        $save++;
                                    }
                                    else
                                    {
                                        $this->calcActi($CI, $ligne, 'too_exp', $row, $result);
                                        $ret++;
                                    }
                                    $row++;
                                }
                            }
                            else
                            {
                                $this->calcActi($CI, $ligne, 'no_stock');
                                $stock_ret++;
                            }
                            $i++;
                            
                        }else{
                            $false++;
                        }
                        $this->item = $this->item + 1;
                    }
                }
            }
        }else{
            $CI->time_process->end_process($CI, $users, $all, 'error', 'db');
            return false;
        }
        @fclose($this->delay_file);
        $CI->lastdayacti_struct->Insert_Data($CI, 'lastdayacti', 'no');
        $masque = $this->Generate_MASQUE($CI, $name_file_csv);
        $csv = $this->Generate_Alert($CI, $user_id, $user_name);
        log_message('error', 'Fin');
        if ($csv && $masque){
            $CI->time_process->end_process($CI, $users, $all, 'ok');
            return true;
        }else{
            $CI->time_process->end_process($CI, $users, $all, 'error', 'file');
            return false;
        }
    }

    public function Compare_Dates($f_actual, $f_activo){
        $datetime1 = new DateTime($f_activo);
        $datetime2 = new DateTime($f_actual);
        $interval = $datetime1->diff($datetime2);
        $diff = $interval->format('%d');
        if ($diff >= $this->days){
            return true;
        }else{
            return false;
        }

    }

    private function Process_Other_Provider($CI, $ligne, $user_id){
        if ($ligne->stockValue >= $this->stock_mini){
            $row['PRIVENLOC'] = '';
            $this->calcActi($CI, $ligne, 'good', $row, 'other_provider');
        }else{
            $this->calcActi($CI, $ligne, 'no_stock', '', 'other_provider');
        }
    }

    private function Generate_MASQUE($CI, $name_file_csv){
        log_message('error', 'Entra en Generate_MASQUE');
        $processed = array();
        $products = array();
        $this->validation_file = @fopen($name_file_csv, 'w+');
        $f_actual = date('Y-m-d');
        $line = "OA;material;TYPE;val;date_debut;heure_debut;date_fin;heure_fin;sup";
        @fputcsv($this->validation_file, explode(',', $line));

        $query = $this->Get_Disabled_Lastdayacti($CI);
        if ($query->num_rows() > 0){
            foreach ($query->result() as $prod){
                log_message('error', 'idProd '.$prod->idProd);
                if (!$this->Is_New($prod->idProd)){
                    $statut = $this->Get_Active_Value($prod->idProd, 'statut');
                    $f_activo = $this->Get_Active_Value($prod->idProd, 'fecha');
                    $update_SAP = $this->Compare_Dates($f_actual, $f_activo);

                    if ($update_SAP){
                        $last_registry = $this->Get_Last_Registry($CI, $prod->idProd, $statut);
                        $new_statut = $last_registry->statut;
                        log_message('error', 'old_statut '.$statut.' new_statut '.$new_statut);
                        if ($new_statut != $statut && !isset($processed[$prod->idProd])){
                            $processed[$prod->idProd] = 1;
                            $this->Disable_Active($CI, $prod->idProd);
                            $id = $last_registry->id;
                        }else{
                            $update_SAP = false;
                        }
                    }
                }else{
                    $new_statut = $prod->statut;
                    $update_SAP = true;
                    $id = $prod->id;
                }

                if ($update_SAP){
                    log_message('error', 'Escribiendo en el fichero MASQUE '.$prod->idProd);
                    $line = $this->codbu . ";" . $prod->idProd . ";MASQUE;".$new_statut.";".date('Y-m-d', strtotime($f_actual. ' + 1 days')).";00:00:00;31/12/9999;23:59:00;";
                    @fputcsv($this->validation_file, explode(',', $line));
                    $this->Update_Active($CI, $id, 1);
                }
            }
        }

        @fclose($this->validation_file);
        return true;
    }

    private function Disable_Active($CI, $idProd){
        $CI->db->where('idProd', $idProd);
        $CI->db->where('user_id', $this->user_id);
        $CI->db->update('lastdayacti', array('active' => 0));
    }

    private function Update_Active($CI, $id, $active){
        $CI->db->where('id', $id);
        $CI->db->update('lastdayacti', array('active' => $active)); 
    }

    public function Get_Last_Registry($CI, $idProd){
        $CI->db->select('id, statut');
        $CI->db->from('lastdayacti');
        $CI->db->where('active', 0);
        $CI->db->where('countries_id', $this->country_id);
        $CI->db->where('user_id', $this->user_id);
        $CI->db->order_by('fecha', 'DESC');
        $CI->db->limit(1);
        $query = $CI->db->get();
        $row = $query->result();

        return $row[0];
    }

    private function Get_Active_Value($idProd, $type){
        $result = '';
        $result = (!isset($this->lastdayacti_products[$idProd]['Y']) ? 'N' : 'Y');
        if ($type == 'fecha'){
            $result = $this->lastdayacti_products[$idProd][$result];
        }

        return $result;
    }

    private function Get_Disabled_Lastdayacti($CI){
        $CI->db->select('*');
        $CI->db->from('lastdayacti');
        $CI->db->where('countries_id', $this->country_id);
        $CI->db->where('user_id', $this->user_id);
        $CI->db->where('active', 0);
        return $CI->db->get();
    }

    public function Get_All_lastdayacti($CI){
        $CI->db->select('idProd, statut, fecha');
        $CI->db->from('lastdayacti');
        $CI->db->where('countries_id', $this->country_id);
        $CI->db->where('active', 1);
        $CI->db->where('user_id', $this->user_id);
        $query = $CI->db->get();

        if ($query->num_rows() > 0){
            foreach ($query->result() as $products){
                $this->lastdayacti_products[$products->idProd][$products->statut] = $products->fecha;
            }
        }
    }

    public function Get_Data_To_File($CI, $user_id){
        $CI->db->distinct('dm.idProd, dm.valPro, dm.country, r.stockValue, r.priceMin, r.supplierKey, r.other_prov');
        $CI->db->from('data_mch dm, products r');
        $CI->db->where('r.codeRegroupement = dm.valPro');
        $CI->db->where('dm.numPro = "codeRegroupement"');
        $CI->db->where('dm.user_id', $user_id);
        $CI->db->where('r.user_id', $user_id);
        $CI->db->group_by('dm.valPro');
        $CI->db->order_by('valPro', 'asc');

        return $CI->db->get();
    }

    private function Get_Delay($ligne, $result){
        if ($result == 'other_provider'){
            log_message('error', 'Delay de other_provider '.$ligne->other_prov);
            return $this->other_providers_delay[$ligne->other_prov];
        }else{
            return (isset($this->providers_delay[$ligne->supplierKey]) ? $this->providers_delay[$ligne->supplierKey] : 99);
        }
    }

    public function Is_New($idProd){
         if (!isset($this->lastdayacti_products[$idProd]['Y']) && !isset($this->lastdayacti_products[$idProd]['N'])){
            return true;
        }else{
            return false;
        }
    }

    public function calcActi($CI, $ligne, $type, $row = '', $result = ''){
        $statut = 'Y';
        $provider_id = $ligne->supplierKey;
        $prov_delay = $this->Get_Delay($ligne, $result);
        if ($result == 'other_provider') $provider_id = $this->providers_reference[$ligne->idProd];

        switch($type){
            case 'no_price':
                $reg = $this->Get_Reg_noprice($ligne, $this->user_id);
            break;

            case 'good':
                $statut = 'N';
                $reg = $this->Get_Reg_good($ligne, $this->user_id, $result, $row);
            break;

            case 'too_exp':
                $reg = $this->Get_Reg_tooexp($ligne, $this->user_id, $result, $row);
            break;

            case 'no_stock':
                $reg = $this->Get_Reg_nostock($ligne, $this->user_id);
            break;
        }

        if ($type != 'too_exp'){
            if ($prov_delay > 0 && !is_null($prov_delay)){
                $line_delay = $ligne->idProd.';'.$this->codbu.';'.$provider_id.';'.($type == 'no_stock' ? 99 : $prov_delay);
                log_message('error', $line_delay);
                @fputcsv($this->delay_file, explode(',', $line_delay));
            }
        }

        if (!isset($this->lastdayacti_products[$ligne->idProd][$statut])){
            $CI->lastdayacti_struct->Load_Data($reg, $this->item);
        }

        return true;
    }

    public function Get_Reg_noprice($ligne, $user_id){
        $reg = array(
            'idProd' => $ligne->idProd,
            'codeRegroupement' => $ligne->valPro,
            'statut' => 'Y',
            'priceRec' => '',
            'reason' => 'no_price',
            'price_wrk' => '',
            'user_id' => $user_id,
            'fecha' => date('Y-m-d'),
            'active' => 0,
            'country_id' => $this->country_id
        );

        return $reg;
    }

    public function Get_Reg_good($ligne, $user_id, $result, $row){
        $reg = array(
            'idProd' => $ligne->idProd,
            'codeRegroupement' => $ligne->valPro,
            'statut' => 'N',
            'priceRec' => $result,
            'reason' => 'good',
            'price_wrk' => $row['PRIVENLOC'],
            'user_id' => $user_id,
            'fecha' => date('Y-m-d'),
            'active' => 0,
            'country_id' => $this->country_id
        );

        return $reg;
    }

    public function Get_Reg_tooexp($ligne, $user_id, $result, $row){
        $reg = array(
            'idProd' => $ligne->idProd,
            'codeRegroupement' => $ligne->valPro,
            'statut' => 'Y',
            'priceRec' => $result,
            'reason' => 'too_exp',
            'price_wrk' => $row['PRIVENLOC'],
            'user_id' => $user_id,
            'fecha' => date('Y-m-d'),
            'active' => 0,
            'country_id' => $this->country_id
        );

        return $reg;
    }

    public function Get_Reg_nostock($ligne, $user_id){
        $reg = array(
            'idProd' => $ligne->idProd,
            'codeRegroupement' => $ligne->valPro,
            'statut' => 'Y',
            'priceRec' => '',
            'reason' => 'no_stock',
            'price_wrk' => '',
            'user_id' => $user_id,
            'fecha' => date('Y-m-d'),
            'active' => 0,
            'country_id' => $this->country_id
        );

        return $reg;
    }

    public function Generate_Alert($CI, $user_id, $user_name){
        $f = @fopen("assets/files/countries/".$user_name."_test_alert.csv", 'w+');
        $line = "modele;";
        @fputcsv($f, explode(',', $line));
        $line = "codeRegroupement;référenceArticle;;stockValue;;priceMinPlusP;PrixMin;PrixRecalcul;prixVente;statut;reason";
        @fputcsv($f, explode(',', $line));
        $line = "CodeFournisseur;nom;StockValue;StockValueRec;;Price;PriceRec";
        @fputcsv($f, explode(',', $line));
        $line = ";";
        @fputcsv($f, explode(',', $line));
        $query = $this->Get_Data_To_Alert($CI, $user_id);
        if ($query){
            foreach ($query->result() as $ligne)
            {
                $line = $ligne->codeRegroupement . ";" . $ligne->idProd . ";;" . $ligne->stockValue . ";;" . $ligne->priceMinPlusP . ";" . $ligne->priceMin . ";" . $ligne->priceRec . ";" . $ligne->price_wrk . ";" . $ligne->statut . ";" . $ligne->reason;
                @fputcsv($f, explode(',', $line));
                $query_reg = $this->Get_Products_Provider($CI, $user_id, $ligne);

                if ($query_reg!=false){
                    foreach ($query_reg->result() as $ligne2)
                    {
                        if ($ligne2->supplierPrice == $ligne->priceMin)
                            $line = $ligne2->supplierKey . ";" . str_replace(' ', '_', $ligne2->nom) . ";" . $ligne2->stockValueB . ";" . $ligne2->stockValue . ";;" . $ligne2->supplierPriceB . ";" . str_replace('.', ',', $ligne2->supplierPrice) . ";" . ($ligne2->supplierPrice * $this->tva) . ";" . ($ligne2->supplierPrice * $this->p_stock) * ($this->tva);
                        else
                            $line = $ligne2->supplierKey . ";" . str_replace(' ', '_', $ligne2->nom) . ";" . $ligne2->stockValueB . ";" . $ligne2->stockValue . ";;" . $ligne2->supplierPriceB . ";" . str_replace('.', ',', $ligne2->supplierPrice);
                            @fputcsv($f, explode(',', $line));
                    }
                }
                $line = ";;;;";
                @fputcsv($f, explode(',', $line));
            }
        }
        @fclose($f);

        return true;
    }

    public function Get_Data_To_Alert($CI, $user_id){
        $CI->db->distinct('r.codeRegroupement, r.stockValue, r.priceMin, l.priceRec, l.idProd, l.price_wrk, l.statut, l.reason, r.priceMinPlusP');
        $CI->db->from('products r, lastdayacti l');
        $CI->db->where('r.codeRegroupement = l.codeRegroupement');
        $CI->db->where('l.user_id', $user_id);
        $CI->db->where('r.user_id', $user_id);
        $CI->db->order_by('r.codeRegroupement', 'asc');
        $query = $CI->db->get();

        if ($query->num_rows > 0){
            return $query;
        }else{
            return false;
        }
    }

    public function Get_Products_Provider($CI, $user_id, $ligne){
        $CI->db->select('p.supplierKey, pv.nom, p.supplierPrice, p.supplierPriceB, p.stockValue, p.stockValueB');
        $CI->db->from('products p, providers pv');
        $CI->db->where('p.supplierKey = pv.SupplierKey');
        $CI->db->where('p.codeRegroupement', $ligne->codeRegroupement);
        $CI->db->where('p.user_id', $user_id);
        $query = $CI->db->get();

        if ($query->num_rows > 0){
            return $query;
        }else{
            return false;
        }
    }
}