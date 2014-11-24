<?php 
class Products_Struct extends DB_op{
    var $products = array(
        'codeRegroupement' => '',
        'id_products' => '',
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
        'priceMin' => '',
        'priceMinPlusP' => '',
        'stockValue' => '',
        'stockValueB' => '',
        'stockVar' => '',
        'other_prov' => '',
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

    public function Product_Exist($CI, $codeReg, $codProv, $user_id = ''){
        $CI->db->select('*');
        $CI->db->from('products');
        $CI->db->where('supplierKey', "$codProv");
        $CI->db->where('supplierRef', "$codProv");
        $CI->db->where('codeRegroupement', "$codeReg");
        if ($user_id != ''){
            $CI->db->where('user_id', $user_id);
        }
        $query = $CI->db->get();
        return $query->num_rows();
    }
}