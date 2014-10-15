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

    function __construct(){
        $CI =& get_instance();
        $CI->load->database();
        $CI->load->helper('array');
        $CI->load->library('session');
        $CI->load->library('Time_Process');
        $CI->load->library('Lastdayacti_Struct');
    }

    public function do_it($user_id, $user_name, $codbu, $codcen, $all){
        $CI =& get_instance();
        $this->user_id = $user_id;
        $this->user_name = $user_name;
        $CI->time_process->flag = 'generate';
        $CI->time_process->user_id = $user_id;
        $Conn = $this->Connect_MCH();
        $Conn_wrk = $this->Connect_WRK();
        log_message('error', 'Entra files '.$user_id);
        $this->stock_mini = $this->Get_Default_Value($CI, 'stock_mini');
        $marge_e = $this->Get_Default_Value($CI, 'marge_e');
        $marge_p = $this->Get_Default_Value($CI, 'marge_p');
        $tva = $this->Get_Default_Value($CI, 'TVA');
        $query = $this->Get_Data_To_File($CI, $user_id);
        $users = $this->Get_Usuarios($CI, $user_id);
        $this->providers_reference = $this->Get_Providers_Reference($CI, $Conn, $codbu, $user_id);
        $this->codbu = $codbu;
        $this->providers_delay = $this->Get_Providers_Delay($CI, 'SupplierKey', 'delay', 'users_providers', $user_id);
        $this->other_providers_delay = $this->Get_Providers_Delay($CI, 'id', 'delay', 'other_providers');
        $is_other_provider = false;
        $save = 0;
        $no_price = 0;
        $ret = 0;
        $stock_ret = 0;
        $i = 2;
        $false = 0;
        $count = 0;
        $this->TMP_PRDNOEBU = $this->check_art_tables($Conn, 'TMP_PRDNOEBU', $codbu);
        $this->TMP_PRDGMABU_PRIX_OK = $this->check_art_tables($Conn, 'TMP_PRDGMABU_PRIX_OK', $codbu);
        $this->TMP_PRDGMABU_MASQ_OK = $this->check_art_tables($Conn, 'TMP_PRDGMABU_MASQ_OK', $codbu);
        $this->Get_AIH_PRIARTWEB($Conn_wrk, $codcen);
        $name_file_csv = 'assets/files/countries/OBJNAT_MASSE_'.date('YmdHis').'.csv';
        $delay_file_name = 'assets/files/countries/DELAI_FIA_'.date('YmdHis').'.csv';
        $this->validation_file = @fopen($name_file_csv, 'w+');
        $this->delay_file = @fopen($delay_file_name, 'w+');
        $line_delay = 'CODBU;provider;id_prod;delay';
        @fputcsv($this->delay_file, explode(',', $line_delay));
        $line = "OA;material;TYPE;val;date_debut;heure_debut;date_fin;heure_fin;sup";
        @fputcsv($this->validation_file, explode(',', $line));

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
                                    $result = (((double)$ligne->priceMin + (double)$marge_e) * (1 + ((double)$marge_p / 100))) * (1 + ((double)$tva / 100));

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
        @fclose($this->validation_file);
        @fclose($this->delay_file);+
        $CI->lastdayacti_struct->Insert_Data($CI, 'lastdayacti', 'no');
        $csv = $this->Generate_Alert($CI, $user_id, $user_name);
        log_message('error', 'Fin');
        if ($csv){
            $CI->time_process->end_process($CI, $users, $all, 'ok');
            return true;
        }else{
            $CI->time_process->end_process($CI, $users, $all, 'error', 'file');
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
            return $this->providers_delay[$ligne->supplierKey];
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

        $CI->lastdayacti_struct->Load_Data($reg, $this->item);
        $line = $this->codbu . ";" . $ligne->idProd . ";MASQUE;".$statut.";".date("d/m/Y").";".date("H:i:s").";31/12/9999;23:59:00;";
        @fputcsv($this->validation_file, explode(',', $line));

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
            'user_id' => $user_id
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
            'user_id' => $user_id
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
            'user_id' => $user_id
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
            'user_id' => $user_id
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
                            $line = $ligne2->supplierKey . ";" . str_replace(' ', '_', $ligne2->nom) . ";" . $ligne2->stockValueB . ";" . $ligne2->stockValue . ";;" . $ligne2->supplierPriceB . ";" . str_replace('.', ',', $ligne2->supplierPrice) . ";" . ($ligne2->supplierPrice * 1.196) . ";" . (($ligne2->supplierPrice + 0) * (1 + (8 / 100))) * (1 + (19.6 / 100));
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