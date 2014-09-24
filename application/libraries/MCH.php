<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MCH{
    var $Provider = 'MCH';
    var $count = 0;
    var $diameter_list = array();
    var $typepneu_list = array();
    var $stockmini = '';
    var $ligne_val_prix_min = '';

    function __construct(){
        $CI =& get_instance();
        $CI->load->database();
        $CI->load->helper('array');
        $CI->load->library('session');
        $CI->load->library('Time_Process');
        $CI->load->library('Mch_Struct');
        $CI->load->library('Regroupement_Struct');
        $CI->load->library('Products_Struct');
        $CI->load->library('DB_op');
    }

    function Procesar_Items($check, $user_id = ''){
	  	$CI =& get_instance();
	    $query = $this->Groupe_Marchandise($CI);
	    $count_line = $query->num_rows();
	    $Conn = $CI->db_op->Connect_WRK();
	    $Conn_MCH = $CI->db_op->Connect_MCH();
	    $CI->time_process->flag = 'mch';
	    $CI->time_process->user_id = $user_id;
	    $users = $CI->db_op->Get_Usuarios($CI, $user_id);
	    $this->stock_mini = $CI->db_op->Get_Default_Value($CI, 'stock_mini');
	    $CI->db_op->Truncate_Tables($CI, $users, 'data_mch');
	    $all = ($user_id != '' ? true : false);
	    $this->ligne_val_prix_min = $CI->db_op->Get_Default_Value($CI, 'Prix_min');
	    $this->diameter_list = $CI->db_op->Get_Property_MCH_List($CI, 'diameter', $Conn_MCH);
	    $this->typepneu_list = $CI->db_op->Get_Property_MCH_List($CI, 'type_pneu', $Conn_MCH);
		$i = 0;
		log_message('error', 'Entra '.$user_id);
		if ($Conn){
			$row_n = $this->Get_CODGMA($Conn);
			$row_e = $this->Get_EAN($Conn);

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

			$stmt = sqlsrv_query($Conn_MCH, $sql);
			$j = 0;
			$item = 0;
			$ean_list = $this->List_EAN($CI);

			while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))
			{
				log_message('error', 'Entra MCH '.$row['IDEPRD']);
				foreach ($users as $user){
					$j++;
					$key = $user['id'].'_'.$row['VALPRO'];
					$is_in = array_key_exists($key, $ean_list);
					$codeReg = ($is_in ? $ean_list[$key] : '');
					if ($codeReg != '')
					{
						$data_mch = $this->Get_Data_Mch($row['IDEPRD'], $row['CODPRO'], $row['country'], $row['VALPRO'], $user['id']);
						$CI->mch_struct->Load_Data($data_mch, $item);
						$item++;
						
						log_message('error', 'Extrayendo index de '.$user['id'].'_'.$row['VALPRO'].' con valor '.$codeReg);
						$data_mch = $this->Get_Data_Mch($row['IDEPRD'], 'codeRegroupement', $row['country'], $codeReg, $user['id']);
						$CI->mch_struct->Load_Data($data_mch, $item);
						$item++;
					}
				}
			}
			log_message('error', 'Preinsert');
			$CI->mch_struct->Insert_Data($CI, 'data_mch', 'no');
			$res = $this->Calc_Stock_MCH($CI, $users, $Conn_MCH);
			$CI->time_process->end_process($CI, $users, $all, 'ok');
			log_message('error', 'Fin MCH');
			return $res;
		}else{
			$CI->time_process->end_process($CI, $users, $all, 'error', 'db');
			return false;
		}
    }

    public function List_EAN($CI){
    	$list = array();
    	$CI->db->select('*');
		$CI->db->from('ean');
		$query = $CI->db->get();

		foreach ($query->result() as $ligne){
			$list[$ligne->user_id.'_'.$ligne->ean] = $ligne->codeRegroupement;
		}

		return $list;

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
    	$sql = " Set transaction isolation level READ UNCOMMITTED SELECT distinct(prd1.IDEPRD), prd1.CODBU as country,  
			 mch.NCOM_PROPRIETE.CODPRO as CODPRO, prd1.VALPRO as VALPRO 
			 from mch.NCOM_PRD_PRO_BU prd1 inner join mch.NCOM_PROPRIETE on prd1.NUMPRO = mch.NCOM_PROPRIETE.NUMPRO 
			 and mch.NCOM_PROPRIETE.NUMPRO = ".$row_e['NUMPRO']."
			 inner join mch.NCOM_PRD_PRO_BU prd2 on prd1.IDEPRD=prd2.IDEPRD and prd2.NUMPRO=1 and prd2.VALPRO in (";

		return $sql;
    }

    public function Groupe_Marchandise($CI){
	    $CI->db->select('*');
	    $CI->db->from('groupe_marchandise');
	    $query = $CI->db->get();

	    return $query;
    }

    public function Get_Data_Mch($IDEPRD, $CODPRO, $country, $valor, $user_id){
    	$data_mch = array(
			'idProd' => $IDEPRD,
			'country' => $country,
			'numPro' => $CODPRO,
			'valPro' => strip_tags($valor),
			'user_id' => $user_id
		);

		return $data_mch;
    }

    public function Calc_Stock_MCH($CI, $users, $Conn_MCH){
    	$pourcent = $CI->db_op->Get_Default_Value($CI, 'p_stock');
    	foreach ($users as $user){
    		log_message('error', 'User id: '.$user['id']);
			$CI->db->select('idProd, valPro');
			$CI->db->from('data_mch');
			$CI->db->where('numPro = "codeRegroupement"');
			$CI->db->where('user_id', $user['id']);
			$query = $CI->db->get();
			$this->count = 0;
			if ($query->num_rows() > 0){
				foreach ($query->result() as $ligne)
				{
					$this->Calc($pourcent, $ligne->valPro, $ligne->idProd, $CI, $user['id'], $Conn_MCH);
				}

				//$CI->db->update_batch('products', $CI->products_struct->datos_products, 'id_products');
			}
    	}

    	return true;
    }

    public function Calc($pourcent, $valPro, $idProd, $CI, $user_id, $Conn_MCH){
  		$result_stock = 0;
  		$result = 0;
  		$res_price = 300000000000000;
  		
		$CI->db->select('id_products, supplierKey, supplierPrice, stockValue');
		$CI->db->from('products');
		$CI->db->where('codeRegroupement', $valPro);
		$CI->db->where('user_id', $user_id);
		$query = $CI->db->get();
		log_message('error', 'idProd '.$idProd);
		log_message('error', 'codeRegroupement '.$valPro);
		log_message('error', 'User id '.$user_id);
		
		if ($query->num_rows()>0){
			foreach ($query->result() as $prod)
			{
				$query_p = $CI->db_op->Info_Provider($CI, 'p.SupplierKey', $prod->supplierKey, $user_id);
				if ($query_p->num_rows()>0){
					$ligne_f = $query_p->result();
					$ligne_f = $ligne_f[0];
					$diameter = (isset($this->diameter_list[$idProd]) ? $this->diameter_list[$idProd] : 0);
					$type_pneu = (isset($this->typepneu_list[$idProd]) ? $this->typepneu_list[$idProd] : 0);
					$transport = $this->Calc_transport($type_pneu, $diameter, $ligne_f->transport);
					$result_price = (((double)$prod->supplierPrice + (double)$ligne_f->ecotaxe) - (double)$ligne_f->RFAfixe) * (1 - ((double)$ligne_f->RFA_p / 100)) + (double)$ligne_f->CDS + (double)$transport;
							
	                if ($result_price < $res_price && $res_price >= (int)$this->ligne_val_prix_min && (int)$prod->stockValue > $this->stock_mini)
	                    $res_price = (double)$result_price;

					$result = $res_price + ($res_price * $pourcent / 100);
					if ($prod->supplierPrice <= $result)
					{
						$result_stock = $result_stock + $prod->stockValue;
					}
				}else{
					return false;
				}

				$products = array(
					'stockValue' => $result_stock,
					'priceMin' => $res_price,
					'priceMinPlusP' => $result
				);

				log_message('error', 'stockValue '.$result_stock);
				log_message('error', 'priceMin '.$res_price);
				log_message('error', 'priceMinPlusP '.$result);
				log_message('error', 'id_products '.$prod->id_products);
				$CI->db->where('id_products', $prod->id_products);
				$CI->db->update('products', $products);

				$CI->products_struct->Load_Data($products, $this->count);
				$this->count++;

			}
		}
		return true;
    }

    public function Calc_transport($typeVehicule, $diameter, $l_transport){
        if ((strcmp($typeVehicule, "TOURISME") == 0 && (int)$diameter <= 18) || (strcmp($typeVehicule, "UTILITAIRE") == 0 && (int)$diameter <= 16))
            $transport = (double)$l_transport / 2;
        else
            $transport = (double)$l_transport;

        return $transport;
    }
}