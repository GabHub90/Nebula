<?php
include('default.php');
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/centavos/classi/centext.php");

$nebulaParams['d_inserimento']=date('Ymd');
$nebulaParams['d_validita']=mainFunc::gab_input_to_db($nebulaParams['d_validita']);

$galileo->executeInsert('centavos','CENTAVOS_esterni',$nebulaParams);

$ret=array(
    "tag"=>$nebulaParams['tag'],
    "stato"=>""
);

if (!$galileo->getResult()){ 
    $ret['stato']='Errore inserimento DB';
    $ret['query']=$galileo->getLog('query');
}

echo json_encode($ret);

?>