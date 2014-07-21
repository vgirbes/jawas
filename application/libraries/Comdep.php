<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Comdep{
    var $Provider = 'Comdep';

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
        $CI->load->library('UsersProviders_Struct');
        $CI->load->library('session');
    }

    public function Procesar_Items($xml){
        if ($xml != false){
            $CI =& get_instance();
            $i = 0;
            $j = 0;
            $k = 0;
            $count_four = 0;
            $count_prod = 0;
            $count = 0;
            $ins = false;
            $CI->provider_struct->Get_Codes($CI);
            $users = $CI->db_op->Get_Usuarios($CI);
            while ($xml->RegroupementsMobiWheel->RegroupementMobiWheel[$i] != NULL){
                
                $res_price = 300000000000000;
                $res_stock = 0;
                
                // On parcourt les références et on stock les données.
                $curse = 0;

                foreach ($xml->RegroupementsMobiWheel->RegroupementMobiWheel[$i]->attributes() as $a => $b[$curse]){
                    $data_b[$a] = $b[$curse];
                    $curse++;
                }
                // On rentre les attribut de chaque "RelatedProduct" contenus dans chaque "RegroupementMobiWheel" dans la BDD.
                while ($xml->RegroupementsMobiWheel->RegroupementMobiWheel[$i]->RelatedProducts->RelatedProduct[$j] != NULL){
                    $curse = 0;
                    $ligne_four = '';
                    foreach ($xml->RegroupementsMobiWheel->RegroupementMobiWheel[$i]->RelatedProducts->RelatedProduct[$j]->attributes() as $c => $d[$curse]){
                        $data_d[$c] = $d[$curse];
                        $curse++;
                    }       

                    if (strlen($d[0]) > 6)
                        $d[0] = substr($d[0], 5);
                    
                    $query = $CI->db_op->Info_Provider($CI, 'p.SupplierKey', $d[0]);
                    
                    if ($query->num_rows()<=0){
                        $ins = true;
                        $ins_provider = $CI->provider_struct->Process_Provider($CI, $count, $d, $d[0], 'comdep');
                        $ins_userprovider = $CI->usersproviders_struct->Process_UserProvider($CI, $d[0], $ins_provider);
                    }
                        

                    foreach ($users as $user){
                        $count++;
                        $CI->db_op->user_id = $user['id'];
                        $q_users_prov = $CI->db_op->Info_Provider($CI, 'p.SupplierKey', $d[0], $user['id']);
                        $ligne_four = $q_users_prov->result();
                        $ligne_four = $ligne_four[0];
                        if (isset($ligne_four->active)){
                            echo '<input type="hidden" name="comdep">';
                            //si le fournisseur a été trouvé dans la BDD
                            if ($ligne_four->active == "1" || $ligne_four->active == 1){
                                if ((strcmp($b[1], "TOURISME") == 0 && (int)$b[11] <= 18) || (strcmp($b[1], "UTILITAIRE") == 0 && (int)$b[11] <= 16))
                                    $transport = (double)$ligne_four->transport / 2;
                                else
                                    $transport = (double)$ligne_four->transport;

                                $result_price = (((double)$d[21] + (double)$ligne_four->ecotaxe)- (double)$ligne_four->RFAfixe) * (1 - ((double)$ligne_four->RFA_p / 100)) + (double)$ligne_four->CDS + (double)$transport;
                                
                                if ($ligne_four->forceStock == 1){
                                    $ligne_prodForced = $ligne_four;

                                    if (!is_null($ligne_prodForced)){
                                        $stockValue = (int)$ligne_prodForced->stock - $ligne_four->correctionstock;
                                    }else
                                        $stockValue = (int)(isset($d[25]) ? $d[25] : 0) - $ligne_four->correctionstock;
                                }
                                else
                                    $stockValue = (int)(isset($d[25]) ? $d[25] : 0) - $ligne_four->correctionstock;

                                if ($stockValue < 0)
                                    $stockValue = 0;

                                $res_stock = $res_stock + $stockValue;
                                $count_four++;

                                $ligne_val_prix_min = $this->Load_PrixMin($CI);
                                $ligne_val_prix_min = $ligne_val_prix_min[0];

                                if ($result_price < $res_price && $res_price >= (int)$ligne_val_prix_min->val && (int)$stockValue > 4)
                                    $res_price = (double)$result_price;
                                $data_prod = array_merge($data_b, $data_d);
                                $CI->products_struct->Load_Data($data_prod, $count);
                                $CI->products_struct->datos_products[$count]['user_id'] = $user['id'];
                                $CI->products_struct->datos_products[$count]['supplierPrice'] = "$result_price";
                                $CI->products_struct->datos_products[$count]['supplierPrice'] = (int)$stockValue;

                                if ($res_price > 30000000000)
                                    $res_price = -1;
                                //on entre le regroupement dans la BDD avec les informations mises à jour.
                                $CI->regroupement_struct->Load_Data($data_b, $count);
                                $CI->regroupement_struct->datos_regroupement[$count]['user_id'] = $user['id'];
                                $CI->regroupement_struct->datos_regroupement[$count]['priceMin'] = "$res_price";
                                $CI->regroupement_struct->datos_regroupement[$count]['stockValue'] = "$res_stock";

                                $this->Process_Ean($xml, $CI, $i, $k, $b, $count, $user['id']);
                            }
                        }
                        $count_prod++;
                        $j++;
                    }
                }
                $j = 0;
                $k = 0;
                $i++;
            }

            $ins_prod = $CI->products_struct->Insert_Data($CI, 'products');
            $ins_regro = $CI->regroupement_struct->Insert_Data($CI, 'regroupement');
            $ins_ean = $CI->ean_struct->Insert_Data($CI, 'ean');
            if ($ins){
                $CI->corresfour_struct->Insert_Data($CI, 'corres_four', 'no');
                $CI->provider_struct->Insert_Data($CI, 'providers', 'no');
                $CI->usersproviders_struct->Insert_Data($CI, 'users_providers', 'no');
            }

            if ($ins_prod && $ins_regro && $ins_ean){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function Load_Users_UP($CI, $d){
        $CI->db->select('*');
        $CI->db->from('providers p, users_providers up');
        $CI->db->where('up.SupplierKey = p.SupplierKey');
        $CI->db->where('p.SupplierKey', "$d[0]");
        $query = $CI->db->get();
        return $query->result();
    }

    function Load_PrixMin($CI){
        $CI->db->select('val');
        $CI->db->from('info_defaut');
        $CI->db->where('nom_champ', "Prix_min");
        $query = $CI->db->get();
        return $query->result();
    }

    public function Process_Ean($xml, $CI, $i, $k, $b, $count, $user_id){
        while ($xml->RegroupementsMobiWheel->RegroupementMobiWheel[$i]->EANs->EAN[$k] != NULL){
            $ean = (string)$xml->RegroupementsMobiWheel->RegroupementMobiWheel[$i]->EANs->EAN[$k];
            $k++;
            $datos_ean = array('codeRegroupement' => "$b[0]", 'ean' => "$ean", 'user_id'  => $user_id);
            $CI->ean_struct->Load_Data($datos_ean, $count);
        }
    }
}