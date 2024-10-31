<?php

include('default.php');

$res=array(
    "result"=>"KO"
);

$a=array(
    "piano"=>$nebulaParams['piano'],
    "d_inizio"=>$nebulaParams['inizio'],
    "d_fine"=>$nebulaParams['fine'],
    "stato"=>"actual",
    "hidden"=>"0"
);

$galileo->executeInsert('centavos','CENTAVOS_periodi',$a);

if ($result=$galileo->getResult()) $res['result']='OK';

echo json_encode($res);

?>