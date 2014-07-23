<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Generate_Files{
    var $item = 0;
    var $TMP_PRDNOEBU = array();
    var $TMP_PRDGMABU_PRIX_OK = array();
    var $TMP_PRDGMABU_MASQ_OK = array();
    var $AIH_PRIARTWEB = array();
    var $user_id = '';
    var $user_name = '';

    function __construct(){
        $CI =& get_instance();
        $CI->load->database();
        $CI->load->helper('array');
        $CI->load->library('DB_op');
        $CI->load->library('session');
        $CI->load->library('Lastdayacti_Struct');
    }

    public function do_it($user_id, $user_name){
        $CI =& get_instance();
        $this->user_id = $user_id;
        $this->user_name = $user_name;
        $Conn = $CI->db_op->Connect_MCH();
        $Conn_wrk = $CI->db_op->Connect_WRK();
        $nom_csv = $user_name."_validationProduit_csv_" . date('YmdHis');

        $stock_mini = $CI->db_op->Get_Default_Value($CI, 'stock_mini');
        $marge_e = $CI->db_op->Get_Default_Value($CI, 'marge_e');
        $marge_p = $CI->db_op->Get_Default_Value($CI, 'marge_p');
        $tva = $CI->db_op->Get_Default_Value($CI, 'TVA');
        $query = $this->Get_Data_To_File($CI, $user_id);

        $save = 0;
        $no_price = 0;
        $ret = 0;
        $stock_ret = 0;
        $i = 2;
        $false = 0;
        $count = 0;
        $this->TMP_PRDNOEBU = $this->check_art_tables($Conn, 'TMP_PRDNOEBU');
        $this->TMP_PRDGMABU_PRIX_OK = $this->check_art_tables($Conn, 'TMP_PRDGMABU_PRIX_OK');
        $this->TMP_PRDGMABU_MASQ_OK = $this->check_art_tables($Conn, 'TMP_PRDGMABU_MASQ_OK');
        $this->Get_AIH_PRIARTWEB($Conn_wrk);
        $name_file_csv = 'assets/files/'.$nom_csv .".csv";
        $f = @fopen($name_file_csv, 'w+');
        $line = "OA;material;TYPE;val;date_debut;heure_debut;date_fin;heure_fin;sup";
        @fputcsv($f, explode(',', $line));

        if ($query->num_rows()>0){
            foreach ($query->result() as $ligne)
            {
                echo '<input type="hidden" name="generate">';
                $count++;
                $row['PRIVENLOC'] = $this->Get_PRIVENLOC($ligne->idProd);
                if ($row['PRIVENLOC'] != false)
                {
                    if ($this->checkArt($ligne->idProd) == 1)
                    {
                        if ($ligne->stockValue >= $stock_mini)
                        {
                            if ($ligne->priceMin == -1)
                            {
                                $this->calcActi($CI, $ligne, $f, 'no_price');
                                $no_price++;
                            }
                            else
                            {
                                $result = (((double)$ligne->priceMin + (double)$marge_e) * (1 + ((double)$marge_p / 100))) * (1 + ((double)$tva / 100));

                                if ((double)$result <= (double)$row['PRIVENLOC'])
                                {
                                    $this->calcActi($CI, $ligne, $f, 'good', $row, $result);
                                    $save++;
                                }
                                else
                                {
                                    $this->calcActi($CI, $ligne, $f, 'too_exp', $row, $result);
                                    $ret++;
                                }
                                $row++;
                            }
                        }
                        else
                        {
                            $this->calcActi($CI, $ligne, $f, 'no_stock');
                            $stock_ret++;
                        }
                        $i++;
                        $this->item = $this->item + 1;
                    }else
                        $false++;
                }
            }
        }
        @fclose($f);
        $CI->lastdayacti_struct->Insert_Data($CI, 'lastdayacti', 'no');
        $csv = $this->Generate_Alert($CI, $user_id, $user_name);

        if ($csv){
            return true;
        }else{
            return false;
        }
    }

    public function Get_Data_To_File($CI, $user_id){
        $CI->db->distinct('dm.idProd, dm.valPro, dm.country, r.stockValue, r.priceMin');
        $CI->db->from('data_mch dm, regroupement r');
        $CI->db->where('r.codeRegroupement = dm.valPro');
        $CI->db->where('dm.numPro = "codeRegroupement"');
        $CI->db->where('dm.country != "NOES"');
        $CI->db->where('dm.user_id', $user_id);
        $CI->db->where('r.user_id', $user_id);
        $CI->db->group_by('dm.valPro');
        $CI->db->order_by('valPro', 'asc');

        return $CI->db->get();
    }

    public function Get_AIH_PRIARTWEB($Conn){
        $sql = "SELECT PRIVENLOC, CODART from src.aih.AIH_PRIARTWEB where CODCEN = '9901';";
        $stmt = sqlsrv_query($Conn, $sql);
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
            $this->AIH_PRIARTWEB[$row['CODART']] = $row['PRIVENLOC'];
        }
    }

    public function Get_PRIVENLOC($idProd){
        if (array_key_exists($idProd, $this->AIH_PRIARTWEB)){
            return $this->AIH_PRIARTWEB[$idProd];
        }else{
            return false;
        }
    }

    public function check_art_tables($connexion, $table = ''){
        $req = ("SELECT IDEPRD from mch.".$table." ".($table == 'TMP_PRDNOEBU' ? " WHERE CODBU = 'NOES'" : ''));
        $stmt = sqlsrv_query($connexion, $req);

        $tabla = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
            $key = $row['IDEPRD'];
            $tabla[$key] = '';
            
        }

        return $tabla;
    }

    public function checkArt($idProd)
    {
        if (array_key_exists($idProd, $this->TMP_PRDNOEBU) && array_key_exists($idProd, $this->TMP_PRDGMABU_PRIX_OK) && array_key_exists($idProd, $this->TMP_PRDGMABU_MASQ_OK)){
            return true;
        }else{
            return false;
        }
    }

    public function calcActi($CI, $ligne, $f, $type, $row = '', $result = ''){
        $statut = 'Y';

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
        $CI->lastdayacti_struct->Load_Data($reg, $this->item);
        $line = $ligne->country . ";" . $ligne->idProd . ";MASQUE;".$statut.";1/10/12;00:00:00;31/12/2099;23:59:00;";
        @fputcsv($f, explode(',', $line));

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
        $f = @fopen("assets/files/".$user_name."_test_alert.csv", 'w+');

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
                echo '<input type="hidden" name="generate">';
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
        $CI->db->from('regroupement r, lastdayacti l');
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