<?php
include('default.php');
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/centavos/classi/centret.php");

$nebulaParams['d_inserimento']=date('Ymd');

$galileo->executeInsert('centavos','CENTAVOS_rettifiche',$nebulaParams);

$ret=array(
    "tag"=>$nebulaParams['parametro'],
    "stato"=>""
);

if (!$galileo->getResult()){ 
    $ret['stato']='Errore inserimento DB';
    $ret['query']=$galileo->getLog('query');
}

echo json_encode($ret);

?>