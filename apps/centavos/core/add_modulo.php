<?php

include('default.php');

$wclause="ID='".$nebulaParams['sezione']."'";
$galileo->executeSelect('centavos','CENTAVOS_varianti',$wclause,"");
$result=$galileo->getResult();
$c=0;
if ($result) {
    $fetID=$galileo->preFetch('centavos');
    while ($row=$galileo->getFetch('centavos',$fetID)) {
        $sezione=$row;
        $c++;
    }
}

if ($c==0) die('Sezione non trovata');

//echo json_encode($sezione['moduli']);

if(!$moduli=json_decode($sezione['moduli'],true)) $moduli=array();

///////////////////////////////////////

$arr=array(
    "titolo"=>$nebulaParams['titolo'],
);

$galileo->setTransaction(true);
$galileo->executeGeneric('centavos','addModulo',$arr,"");

//restituisce un array coin l'ID del modulo appena creato
//{"ID_modulo":"3"}
$resvar=$galileo->getResvar();

if (!isset($resvar['ID_modulo'])) die ('Errore creazione modulo');

$galileo->executeClear('centavos');

///////////////////////////////////////

$moduli[]=$resvar['ID_modulo'];

$arr=array(
    "moduli"=>json_encode($moduli)
);

$galileo->setTransaction(false);
$galileo->executeUpdate('centavos','CENTAVOS_varianti',$arr,$wclause);
$result=$galileo->getResult();
//se l'update non è andato a buon fine cancella il modulo appena creato
if (!$result) {
    $wclause="ID='".$resvar['ID_modulo']."'";
    $galileo->executeDelete('centavos','CENTAVOS_moduli',$wclause);
}

echo json_encode($galileo->getOpLog('centavos','errori'));
echo json_encode($galileo->getLog('query'));
//echo json_encode($resvar);

?>