<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/odl_func.php");

$param=$_POST['param'];

$odlFunc=new nebulaOdlFunc($galileo);
$odlFunc->setGalileo($param['dms']);
$galileo=$odlFunc->exportGalileo();

$arr=array(
    "pratica"=>base64_decode($param['pratica']),
    "d"=>str_replace('-','',$param['d']),
    "ora"=>$param['ora']
);

$galileo->executeGeneric('odl','setRiconPratica',$arr,'');

echo json_encode($galileo->getLog('query'));

?>