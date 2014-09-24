<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Atyse{
    var $Provider = 'Atyse';

    function __construct(){
        $CI =& get_instance();
        $CI->load->database();
        $CI->load->helper('array');
        $CI->load->library('DB_op');
        $CI->load->library('Products_Struct');
        $CI->load->library('Regroupement_Struct');
        $CI->load->library('Ean_Struct');
        $CI->load->library('CorresFour_Struct');
        $CI->load->library('Provider_Struct');
        $CI->load->library('Time_Process');
        $CI->load->library('UsersProviders_Struct');
        $CI->load->library('session');
    }

    public function Procesar_Items($archivo, $user_id = '', $all){
        $CI =& get_instance();
        $row = 1;
        $good = 0;
        $item = 0;
        $prod_n = 0;
        $ins = false;
        $code_four = '';
        $process = false;
        $CI->time_process->flag = 'atyse';
        $users = $CI->db_op->Get_Usuarios($CI, $user_id);
        $CI->time_process->user_id = $user_id;
        log_message('error', 'Atyse '.$archivo);
        if (file_exists('assets/files/atyse/'.$archivo) && $archivo != false) 
        {
            $handle = fopen('assets/files/atyse/'.$archivo, "r");
            $CI->corresfour_struct->Get_Codes($CI);
            $CI->provider_struct->Get_Codes($CI);
            $CI->db_op->user_id = $user_id;
            while ((($data = fgetcsv($handle, 3000, ';')) !== FALSE) && !$process) 
            {
                if ($row > 1)
                {
                    log_message('error', 'Entra ATYSE '.$data[1].' '.$user_id);
                    if (!isset($data[9])) break;
                    $codProv = $data[9];
                    $brand = $data[10];
                    $query = $CI->db_op->Info_Provider($CI, 'p.SupplierKey', $codProv, $CI->db_op->user_id);
                    if ($query->num_rows() > 0){
                        $ligne_f = $query->result();
                        $ligne_f = $ligne_f[0];
                        $codProv = $ligne_f->SupplierKey;
                    }else{
                        $ins = true;
                        $code_four = $CI->corresfour_struct->Code_Four($CI, $item, $data);
                        $ins_provider = $CI->provider_struct->Process_Provider($CI, $item, $data, $code_four, 'atyse');
                        $ins_userprovider = $CI->usersproviders_struct->Process_UserProvider($CI, $item, $code_four, $ins_provider);
                        $codProv = $code_four;
                        $ligne_f = null;
                    } 

                    if (!is_null($ligne_f) && ($ligne_f->active == 1 || $ligne_f->active == "1")){
                        $count = 0;
                        $codeReg = '00000000000'.$data[1];
                        log_message('error', 'Insertando proveedor '.$codProv);
                        log_message('error', 'Procesando '.$codeReg);
                        $datos_ean = array('codeRegroupement' => "$codeReg", 'ean' => "$data[3]", 'user_id'  => $CI->db_op->user_id);
                        $CI->ean_struct->Load_Data($datos_ean, $item);

                        if ($ligne_f->forceStock == 1)
                        {
                            $ligne_prodForced = $ligne_f;

                            if (!is_null($ligne_prodForced))
                            {
                               $stockValue = (int)$ligne_prodForced->stock - $ligne_f->correctionstock;
                            }
                            else
                               $stockValue = (int)$data[14] - $ligne_f->correctionstock;
                        }
                        else
                            $stockValue = (int)$data[14] - $ligne_f->correctionstock;
                        if ($stockValue < 0)
                            $stockValue = 0;
                        $result_price = (((double)$data[12] + (double)$ligne_f->ecotaxe) - (double)$ligne_f->RFAfixe) * (1 - ((double)$ligne_f->RFA_p / 100)) + (double)$ligne_f->CDS;
                        $prod_n = $CI->products_struct->Product_Exist($CI, $codeReg, $codProv, $CI->db_op->user_id);
                        if ($prod_n <= 0){
                            $products = $this->Get_Products_array($codeReg, $codProv, $data, $result_price, $stockValue, $CI->db_op->user_id);
                            $CI->products_struct->Load_Data($products, $item);
                        }
                        log_message('error', 'stock '.$data[14]);
                        $item++;
                    }
                }
                $stockValue = 0;
                $row++;

            }
            fclose($handle);
            
        }else{
            $CI->time_process->end_process($CI, $users, $all, 'error', 'file');
            return false;
        }
        log_message('error', 'Preinsert products');
        $CI->products_struct->Insert_Data($CI, 'products', 'no');
        log_message('error', 'Preinsert ean');
        $CI->ean_struct->Insert_Data($CI, 'ean', 'no');
        
        if ($ins){
            $CI->corresfour_struct->Insert_Data($CI, 'corres_four', 'no');
            $CI->provider_struct->Insert_Data($CI, 'providers', 'no');
            $CI->usersproviders_struct->Insert_Data($CI, 'users_providers', 'no');
        }
        log_message('error', 'Fin');
        $CI->time_process->end_process($CI, $users, $all, 'ok');
        return true;
    }

    public function Get_Products_array($codeReg, $codProv, $data, $result_price, $stockValue, $user_id){
        $products = array(
            'codeRegroupement' => "$codeReg",
            'supplierKey' => "$codProv",
            'supplierRef' => "00000$data[11]",
            'attached' => 'true',
            'name' => "$data[2]",
            'currency' => $data[13],
            'supplierPrice' => "$result_price",
            'stockValue' => "$stockValue",
            'user_id' => $user_id
        );

        return $products;
    }
}