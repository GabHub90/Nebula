<?php

include('default.php');

$wclause="ID='".$nebulaParams['modulo']."'";
$galileo->executeSelect('centavos','CENTAVOS_moduli',$wclause,"");
$result=$galileo->getResult();
if ($result) {
    $c=0;
    $fetID=$galileo->preFetch('centavos');
    while ($row=$galileo->getFetch('centavos',$fetID)) {
        $modulo=$row;
        $c++;
    }
}

if ($c==0) die('Modulo non trovato');

//echo json_encode($sezione['moduli']);

if(!$parametri=json_decode($modulo[$nebulaParams['tipo']],true)) die ('Campo parametri non conforme');

///////////////////////////////////////

$wclause="ID='".$nebulaParams['parametro']."'";
$galileo->executeDelete('centavos','CENTAVOS_parametri',$wclause);
$result=$galileo->getResult();

if ($result) {
    $galileo->executeClear('centavos');

    $a=array();
    foreach($parametri as $m) {
        if ($m!=$nebulaParams['parametro']) $a[]=$m;
    }

    if (!$txt=json_encode($a)) $txt="[]";

    $arr=array();
    $arr[$nebulaParams['tipo']]=$txt;

    $wclause="ID='".$nebulaParams['modulo']."'";
    $galileo->executeUpdate('centavos','CENTAVOS_moduli',$arr,$wclause);
}

echo json_encode($galileo->getOpLog('centavos','errori'));
echo json_encode($galileo->getLog('query'));

?>