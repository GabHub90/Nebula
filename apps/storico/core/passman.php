<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require_once(DROOT.'/nebula/core/odl/odl_func.php');

$param=$_POST['param'];

$odlFunc=new nebulaOdlFunc($galileo);

$obj=array(
    "km"=>$param['km'],
    "data_fatt"=>mainFunc::gab_input_to_db($param['data']),
    "data_odl"=>mainFunc::gab_input_to_db($param['data']),
    "data_fine"=>mainFunc::gab_input_to_db($param['data'])
);

$arr=array(
    "telaio"=>$param['telaio'],
    "indice"=>$param['indice'],
    "obj"=>json_encode($obj),
    "note"=>str_replace(array("'",'"'),"",$param['note']),
    "righe"=>isset($param['righe'])?$param['righe']:array()
);

if ($param['indice']=="") {
    $odlFunc->insertPassman($arr);
}
else {
    $odlFunc->updatePassman($arr);
}


?>