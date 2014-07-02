<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Products_Struct extends DB_op{
    var $products = array(
        'codeRegroupement' => '',
        'supplierKey' => '',
        'supplierRef' => '',
        'attached' => '',
        'attachmentDate' => '',
        'name' => '',
        'description' => '',
        'manufacturerRef' => '',
        'status' => '',
        'lifeCycle' => '',
        'labelMobityre' => '',
        'orderable' => '',
        'dot' => '',
        'poidnet' => '',
        'poidnetunit' => '',
        'volume' => '',
        'volumeunit' => '',
        'radial' => '',
        'color' => '',
        'tractionGrade' => '',
        'treadwearGrade' => '',
        'temperatureResistanceGrade' => '',
        'supplierPrice' => '',
        'supplierPriceB' => '',
        'currency' => '',
        'withVAT' => '',
        'priceVar' => '',
        'stockValue' => '',
        'stockValueB' => '',
        'stockVar' => '',
        'user_id' => ''
    );

    public function Load_Data($data, $i){
        $CI =& get_instance();
        foreach ($data as $clave => $valor){
            if (isset($this->products[$clave])){
                $this->datos_products[$i][$clave] = "$valor";
            }
        }

        return true;
    }

    public function Product_Exist($CI, $codeReg, $codProv){
        $CI->db->select('*');
        $CI->db->from('products');
        $CI->db->where('supplierKey', "$codProv");
        $CI->db->where('supplierRef', "$codProv");
        $CI->db->where('codeRegroupement', "$codeReg");
        $query = $CI->db->get();
        return $query->num_rows();
    }
}