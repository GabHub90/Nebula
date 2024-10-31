<?php
include('default.php');
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/centavos/classi/centext.php");

$galileo->executeDelete('centavos','CENTAVOS_rettifiche',"ID='".$nebulaParams['ID']."'");

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