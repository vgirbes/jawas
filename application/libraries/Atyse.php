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

    public function Procesar_Items($archivo, $user_id = ''){
        $CI =& get_instance();
        $row = 1;
        $good = 0;
        $item = 0;
        $prod_n = 0;
        $ins = false;
        $code_four = '';
        $process = false;
        
        if (file_exists('assets/files/atyse/'.$archivo)) 
        {
            
            $handle = fopen('assets/files/atyse/'.$archivo, "r");
            $CI->corresfour_struct->Get_Codes($CI);
            $CI->provider_struct->Get_Codes($CI);
            $CI->db_op->user_id = $user_id;
            while ((($data = fgetcsv($handle, 3000, ';')) !== FALSE) && !$process) 
            {
                if ($row > 1)
                {
                    if (!isset($data[70])) break;
                    switch($data[70]){
                        case 'ENT':
                            $brand = 'ATYSE';
                            $codProv = '111';
                            $query = $CI->db_op->Info_Provider($CI, 'p.SupplierKey', $codProv);
                            $ligne_f = $query->result();
                            if ($query->num_rows() > 0) $ligne_f = $ligne_f[0];
                        break;
                        case 'TPP':
                            $brand = $data[11];
                            $query = $CI->db_op->Info_Provider($CI, 'p.nom', $brand);
                            if ($query->num_rows() > 0){
                                $query = $CI->db_op->Info_Provider($CI, 'p.nom', $brand, $CI->db_op->user_id);
                                $ligne_f = $query->result();
                                $ligne_f = $ligne_f[0];
                                $codProv = $ligne_f->SupplierKey;
                            }else{
                                $ins = true;
                                $code_four = $CI->corresfour_struct->Code_Four($CI, $item, $data);
                                $ins_provider = $CI->provider_struct->Process_Provider($CI, $item, $data, $code_four, 'atyse');
                                $ins_userprovider = $CI->usersproviders_struct->Process_UserProvider($CI, $item, $code_four, $ins_provider);
                                $codProv = $code_four;
                            } 
                        break;
                    }

                    if ($ligne_f->active == 1 || $ligne_f->active == "1"){
                        $count = 0;
                        $codeReg = $data[1];
                        $CI->db->select('*');
                        $CI->db->from('regroupement');
                        $CI->db->where('codeRegroupement', "$codeReg");
                        $CI->db->where('user_id', $CI->db_op->user_id);
                        $query = $CI->db->get();
                        $count = $query->num_rows();
                        $ligne = $query->result();
                        echo '<input type="hidden" name="atyse">';
                        if ($count > 0) $ligne = $ligne[0];              
                
                        if ($count<=0)
                        {
                            $regroupement = $this->Get_Regroupement_array($codeReg, $data, $brand, $CI->db_op->user_id);
                            $CI->regroupement_struct->Load_Data($regroupement, $item);
                            $datos_ean = array('codeRegroupement' => "$codeReg", 'ean' => "$data[4]", 'user_id'  => $CI->db_op->user_id);
                            $CI->ean_struct->Load_Data($datos_ean, $item);
                        }

                        $transport = $this->Calc_transport($data[64], $data[17], $ligne_f->transport);
                        if ($ligne_f->forceStock == 1)
                        {
                            $ligne_prodForced = $ligne_f;

                            if (!is_null($ligne_prodForced))
                            {
                               $stockValue = (int)$ligne_prodForced->stock - $ligne_f->correctionstock;
                            }
                            else
                               $stockValue = (int)$data[68] - $ligne_f->correctionstock;
                        }
                        else
                            $stockValue = (int)$data[68] - $ligne_f->correctionstock;
                        if ($stockValue < 0)
                            $stockValue = 0;
                        $result_price = (((double)$data[60] + (double)$ligne_f->ecotaxe) - (double)$ligne_f->RFAfixe) * (1 - ((double)$ligne_f->RFA_p / 100)) + (double)$ligne_f->CDS + (double)$transport;
                        $prod_n = $CI->products_struct->Product_Exist($CI, $codeReg, $codProv, $CI->db_op->user_id);
                        if ($prod_n <= 0){
                            $products = $this->Get_Products_array($codeReg, $codProv, $data, $result_price, $stockValue, $CI->db_op->user_id);
                            $CI->products_struct->Load_Data($products, $item);
                        }

                        $priceMin = ($count > 0 ? $ligne->priceMin : '3000000');
                        $l_stockValue = ($count > 0 ? $ligne->stockValue : $stockValue);
                        $res_stockValue = $l_stockValue + $stockValue;
                        $stockupdate = array('stockValue' => "$res_stockValue", 'codeRegroupement' => "$codeReg");
                        if (($priceMin < 0 || $priceMin > $result_price) && $stockValue > 4){
                            $res_pricemin = array('priceMin' => "$result_price");
                            $stockupdate = array_merge($stockupdate, $res_pricemin);
                        }
                        $update_regroup[$item] = $stockupdate;
                        $item++;
                    }
                }
                $stockValue = 0;
                $row++;

            }
            fclose($handle);
            
        }else{
            return false;
        }

        $CI->products_struct->Insert_Data($CI, 'products', 'no');
        $CI->regroupement_struct->Insert_Data($CI, 'regroupement', 'no');
        $CI->ean_struct->Insert_Data($CI, 'ean', 'no');
        $CI->db->update_batch('regroupement', $update_regroup, 'codeRegroupement', ' AND user_id = '.$CI->db_op->user_id);
        if ($ins){
            $CI->corresfour_struct->Insert_Data($CI, 'corres_four', 'no');
            $CI->provider_struct->Insert_Data($CI, 'providers', 'no');
            $CI->usersproviders_struct->Insert_Data($CI, 'users_providers', 'no');
        }
        return true;
    }

    public function Get_Regroupement_array($codeReg, $data, $brand, $user_id){
        $regroupement = array(
            'codeRegroupement' => "$codeReg",
            'typeVehicule' => "$data[64]",
            'codeTypeVehicule' => '',
            'typeProduct' => "$data[64]",
            'season' => "$data[65]",
            'brand' => "$brand",
            'height' => "$data[15]",
            'width' => "$data[14]",
            'diameter' => "$data[17]",
            'radial' => "$data[16]",
            'poids' => "$data[50]",
            'poidsUnite' => "$data[51]",
            'volume' => "$data[52]",
            'volumeUnite' => "$data[53]",
            'manufacturerRef' => '',
            'priceMin' => '3000000',
            'user_id' => $user_id
        );

        return $regroupement;
    }

    public function Get_Products_array($codeReg, $codProv, $data, $result_price, $stockValue, $user_id){
        $products = array(
            'codeRegroupement' => "$codeReg",
            'supplierKey' => "$codProv",
            'supplierRef' => "$codProv",
            'attached' => 'true',
            'name' => "$data[13]",
            'poidnet' => "$data[50]",
            'poidnetunit' => "$data[51]",
            'volume' => "$data[52]",
            'volumeunit' => "$data[53]",
            'temperatureResistanceGrade' => '',
            'supplierPrice' => "$result_price",
            'supplierPriceB' => "$data[60]",
            'stockValue' => "$stockValue",
            'stockValueB' => "$data[68]",
            'user_id' => $user_id
        );

        return $products;
    }

    public function Calc_transport($typeVehicule, $diameter, $l_transport){
        if ((strcmp($typeVehicule, "TOURISME") == 0 && (int)$diameter <= 18) || (strcmp($typeVehicule, "UTILITAIRE") == 0 && (int)$diameter <= 16))
            $transport = (double)$l_transport / 2;
        else
            $transport = (double)$l_transport;

        return $transport;
    }
}