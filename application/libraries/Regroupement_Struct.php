<?php 
class Regroupement_Struct extends DB_op{
    var $regroupement = array(
        'codeRegroupement' => '',
        'typeVehicule' => '',
        'codeTypeVehicule' => '',
        'typeProduct' => '',
        'season' => '',
        'brand' => '',
        'codeBrand' => '',
        'profile' => '',
        'codeProfile' => '',
        'height' => '',
        'width' => '',
        'diameter' => '',
        'loadIndex' => '',
        'speedIndex' => '',
        'runFlat' => '',
        'consolidated' => '',
        'mountingSpecificity1' => '',
        'mountingSpecificity2' => '',
        'mountingSpecificity3' => '',
        'dot' => '',
        'color' => '',
        'radial' => '',
        'alesageCentral' => '',
        'deport' => '',
        'diametreImplant' => '',
        'nbTrous' => '',
        'wetGrip' => '',
        'fuelEfficiency' => '',
        'noiseClassType' => '',
        'noisedB' => '',
        'dateSuppression' => '',
        'poids' => '',
        'poidsUnite' => '',
        'volume' => '',
        'volumeUnite' => '',
        'manufacturerRef' => '',
        'priceMin' => '',
        'stockValue' =>  '',
        'user_id' => ''
    );

    public function Load_Data($data, $i){
        $CI =& get_instance();
        foreach ($data as $clave => $valor){
            if (isset($this->regroupement[$clave])){
                $this->datos_regroupement[$i][$clave] = "$valor";
            }
        }

        return true;
    }
}