<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Generate_Files{
    var $item = 0;

    function __construct(){
        $CI =& get_instance();
        $CI->load->database();
        $CI->load->helper('array');
        $CI->load->library('DB_op');
        $CI->load->library('session');
        $CI->load->library('Lastdayacti_Struct');
    }

    public function do_it(){
        $CI =& get_instance();
        $user_id = $CI->session->userdata['id'];
        $user_name = $CI->session->userdata['username'];
        $Conn = $CI->db_op->Connect_MCH();

        $nom_csv = $user_name."_validationProduit_csv_" . date('YmdHis');
        $nom_xls = "validationProduit_xls_" . date('YmdHis');
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0); 
        $objPHPExcel->getActiveSheet()->setTitle('Feuille de test'); 

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

        $name_file_csv = 'assets/files/'.$nom_csv .".csv";
        $f = @fopen($name_file_csv, 'w+');
        $line = "OA;material;TYPE;val;date_debut;heure_debut;date_fin;heure_fin;sup";
        @fputcsv($f, explode(',', $line));

        foreach ($query->result() as $ligne)
        {
            $count++;
            $row = $this->Get_PRIVENLOC($Conn, $ligne->idProd);
            if (!is_null($row['PRIVENLOC']))
            {
                if ($this->checkArt($Conn_refmch, $ligne->idProd) == 1)
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
        @fclose($f);
        $CI->lastdayacti_struct->Insert_Data($CI, 'lastdayacti', 'si');
        $xls = $this->Generate_Validation_XLSX($name_file_csv, $objPHPExcel, $user_name, $nom_xls);
        return $xls;
    }

    public function Generate_Validation_XLSX($name_file_csv, $objPHPExcel, $user_name, $nom_xls){
        $row = 1;
        if (($handle = @fopen($name_file_csv, "r")) !== FALSE) 
        {
            while (($data = fgetcsv($handle, 3000, ';')) !== FALSE) 
            {
                $num = count($data);
                $col_A = 'A' . (string)$row;
                $col_B = 'B' . (string)$row;
                $col_C = 'C' . (string)$row;
                $col_D = 'D' . (string)$row;
                $col_E = 'E' . (string)$row;
                $col_F = 'F' . (string)$row;
                $col_G = 'G' . (string)$row;
                $col_H = 'H' . (string)$row;
                $col_I = 'I' . (string)$row;
                $objPHPExcel->getActiveSheet()->setCellValue($col_A, $data[0]) or die("error ecriture"); 
                $objPHPExcel->getActiveSheet()->setCellValue($col_B, $data[1]) or die("error ecriture"); 
                $objPHPExcel->getActiveSheet()->setCellValue($col_C, $data[2]) or die("error ecriture"); 
                $objPHPExcel->getActiveSheet()->setCellValue($col_D, $data[3]) or die("error ecriture");
                $objPHPExcel->getActiveSheet()->setCellValue($col_E, $data[4]) or die("error ecriture"); 
                $objPHPExcel->getActiveSheet()->setCellValue($col_F, $data[5]) or die("error ecriture"); 
                $objPHPExcel->getActiveSheet()->setCellValue($col_G, $data[6]) or die("error ecriture"); 
                $objPHPExcel->getActiveSheet()->setCellValue($col_H, $data[7]) or die("error ecriture");
                $objPHPExcel->getActiveSheet()->setCellValue($col_I, $data[8]) or die("error ecriture");
                $row++;
            }
            @fclose($handle);
        }else{
            return false;
        }

        $name_xls = 'assets/files/'.$nom_xls . ".xlsx";
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel); 
        $objWriter->save($name_xls);

        return $name_xls;
    }

    public function Get_Data_To_File($CI, $user_id){
        $CI->db->distinct('idProd, valPro, stockValue, country, priceMin');
        $CI->db->from('data_mch dm, regroupement r');
        $CI->db->where('valPro = r.codeRegroupement');
        $CI->db->where('numPro = "codeRegroupement"');
        $CI->db->where('country != "NOES"');
        $CI->db->where('dm.user_id', $user_id);
        $CI->db->where('r.user_id', $user_id);
        $CI->db->order_by('valPro', 'asc');

        return $CI->db->get();
    }

    public function Get_PRIVENLOC($Conn, $idProd){
        $sql = "SELECT PRIVENLOC from src.aih.AIH_PRIARTWEB where CODART = '".$idProd."' and CODCEN = '9901';";
        $stmt = sqlsrv_query($Conn, $sql);
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        return $row;
    }

    public function checkArt($connexion, $idProd)
    {
        $req = ("SELECT * 
            from mch.TMP_PRDNOEBU 
            where IDEPRD = '".$idProd."' 
            and CODBU = 'NOES' 
            and IDEPRD in (select IDEPRD from mch.TMP_PRDGMABU_PRIX_OK where IDEPRD = '".$idProd."') 
            and IDEPRD in (select IDEPRD from mch.TMP_PRDGMABU_MASQ_OK where IDEPRD = '".$idProd."');");
        $stmt = sqlsrv_query($connexion, $req);
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        if (!is_null($row['IDEPRD']))
        {
            return true;
        }
        else
            return false;
    }

    public function calcActi($CI, $ligne, $f, $type, $row, $result){
        $user_id = $CI->session->userdata['id'];
        $statut = 'Y';

        switch($type){
            case 'no_price':
                $reg = $this->Get_Reg_noprice($ligne, $user_id);
            break;

            case 'good':
                $statut = 'N';
                $reg = $this->Get_Reg_good($ligne, $user_id, $result, $row);
            break;

            case 'too_exp':
                $reg = $this->Get_Reg_tooexp($ligne, $user_id, $result, $row);
            break;

            case 'no_stock':
                $reg = $this->Get_Reg_nostock($ligne, $user_id);
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
            'reason' => 'no_price',
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
            'reason' => 'no_stock',
            'user_id' => $user_id
        );

        return $reg;
    }


}