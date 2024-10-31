<?php

include('default.php');

$wclause="ID='".$nebulaParams['sezione']."'";
$galileo->executeSelect('centavos','CENTAVOS_varianti',$wclause,"");
$result=$galileo->getResult();
if ($result) {
    $c=0;
    $fetID=$galileo->preFetch('centavos');
    while ($row=$galileo->getFetch('centavos',$fetID)) {
        $sezione=$row;
        $c++;
    }
}
if($c==0) die('Sezione non trovata');

//echo json_encode($sezione['moduli']);

if(!$moduli=json_decode($sezione['moduli'],true)) die ('Campo moduli non conforme');

///////////////////////////////////////

$wclause="ID='".$nebulaParams['modulo']."'";
$galileo->executeDelete('centavos','CENTAVOS_moduli',$wclause);
$result=$galileo->getResult();

if ($result) {
    $galileo->executeClear('centavos');

    $a=array();
    foreach($moduli as $m) {
        if ($m!=$nebulaParams['modulo']) $a[]=$m;
    }

    if (!$txt=json_encode($a)) $txt="[]";

    $arr=array(
        "moduli"=>$txt
    );

    $wclause="ID='".$nebulaParams['sezione']."'";
    $galileo->executeUpdate('centavos','CENTAVOS_varianti',$arr,$wclause);
}

//############################################
//se OK cancella anche tutti i PARAMETRI collegati (forse)
//############################################

echo json_encode($galileo->getOpLog('centavos','errori'));
echo json_encode($galileo->getLog('query'));

?>