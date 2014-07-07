<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MCH{
    var $Provider = 'MCH';

    function __construct(){
        $CI =& get_instance();
        $CI->load->database();
        $CI->load->helper('array');
        $CI->load->library('session');
        $CI->load->library('Mch_Struct');
    }

    function Procesar_Items(){
    	$CI =& get_instance();
        $user_id = $CI->session->userdata['id'];
        $query = $this->Groupe_Marchandise($CI);
        $count_line = $query->num_rows();
        $Conn = $this->Connect_MCH();

		$i = 0;
		if ($Conn){
			$row_n = $this->Get_CODGMA($Conn);
			$row_e = $this->Get_EAN($Conn);
			//---------------------------------------------------------------------------------------------création de la requete qui va recuperer tous les produits appartenant aux GM présent dans la bdd->table = groupe_marchandise

			$sql = $this->Get_Consulta($row_e);
			
			foreach ($query->result() as $ligne)
			{
				$sql = $sql .$ligne->groupe;
				if ($i < $count_line - 1)
					$sql = $sql . ", ";
				else
					$sql = $sql . ")  where  prd1.DATSUP is null;";
				$i++;
			}

			$stmt = sqlsrv_query($Conn, $sql);
			$j = 0;
			$item = 0;
			//---------------------------------------------------------------------------------------------transfert des données de la MCH vers la bdd referentiel_atyse
			while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))
			{
				$j++;
				$CI->db->select('*');
				$CI->db->from('ean');
				$CI->db->where('ean', $row['VALPRO']);
				$query = $CI->db->get();
				$ligne = $query->result();
				echo '<input type="hidden" name="MCH">';

				if ($query->num_rows()>0)
				{
					$ligne = $ligne[0];
					$data_mch = $this->Get_Data_Mch($row['IDEPRD'], $row['CODPRO'], $row['VALPRO'], $user_id);
					$CI->mch_struct->Load_Data($data_mch, $item);
					$item++;

					$data_mch = $this->Get_Data_Mch($row['IDEPRD'], 'codeRegroupement', $ligne->codeRegroupement, $user_id);
					$CI->mch_struct->Load_Data($data_mch, $item);
					$item++;
				}
			}

			$CI->mch_struct->Insert_Data($CI, 'data_mch', 'si');
			return true;
		}else{
			return false;
		}
    }

    public function Connect_MCH(){
    	$ServerName = HOST_MCH;
		$ConnectionOptions  = array("Database" => DB_MCH, "UID"=> USER_MCH, "PWD"=> PASS_MCH);

		$Conn = sqlsrv_connect($ServerName , $ConnectionOptions);

		if($Conn) 
		{
	    	return $Conn;
		}else{
	     	return false;
	     	die( print_r( sqlsrv_errors(), true));
		}
    }

    public function Get_CODGMA($Conn){
    	$sql_n = "SELECT NUMPRO from mch.NCOM_PROPRIETE where CODPRO LIKE 'CODGMA';";
		$stmt_n = sqlsrv_query($Conn, $sql_n);
		$row_n = sqlsrv_fetch_array($stmt_n, SQLSRV_FETCH_ASSOC);

		return $row_n;
    }

    public function Get_EAN($Conn){
    	$sql_e = "SELECT NUMPRO from mch.NCOM_PROPRIETE where CODPRO LIKE 'EAN';";
		$stmt_e = sqlsrv_query($Conn, $sql_e);
		$row_e = sqlsrv_fetch_array($stmt_e, SQLSRV_FETCH_ASSOC);

		return $row_e;
    }

    public function Get_Consulta($row_e){
    	$sql = " Set transaction isolation level READ UNCOMMITTED SELECT distinct(prd1.IDEPRD),  
			 mch.NCOM_PROPRIETE.CODPRO as CODPRO, prd1.VALPRO as VALPRO 
			 from mch.NCOM_PRD_PRO prd1 inner join mch.NCOM_PROPRIETE on prd1.NUMPRO = mch.NCOM_PROPRIETE.NUMPRO 
			 and mch.NCOM_PROPRIETE.NUMPRO = ".$row_e['NUMPRO']."
			 inner join mch.NCOM_PRD_PRO prd2 on prd1.IDEPRD=prd2.IDEPRD and prd2.NUMPRO=1 and prd2.VALPRO in (";

		return $sql;
    }

    public function Groupe_Marchandise($CI){
    	$CI->db->select('*');
        $CI->db->from('groupe_marchandise');
        $query = $CI->db->get();

        return $query;
    }

    public function Get_Data_Mch($IDEPRD, $CODPRO, $valor, $user_id){
    	$data_mch = array(
			'idProd' => $IDEPRD,
			'country' => '*',
			'numPro' => $CODPRO,
			'valPro' => strip_tags($valor),
			'user_id' => $user_id
		);

		return $data_mch;
    }
}