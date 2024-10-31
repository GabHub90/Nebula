<?php

include('default.php');

$arr=array(
    "titolo"=>$nebulaParams['titolo'],
    "piano"=>$nebulaParams['piano'],
    "variante"=>$nebulaParams['variante']
);

$galileo->executeInsert('centavos','CENTAVOS_varianti',$arr);

//echo json_encode($galileo->getOpLog('centavos','errori'));
//echo json_encode($galileo->getLog('query'));

?>