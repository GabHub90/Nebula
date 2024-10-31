<?php

include('default.php');

$wclause="ID='".$nebulaParams['modulo']."'";
$galileo->executeSelect('centavos','CENTAVOS_moduli',$wclause,"");
$result=$galileo->getResult();
$c=0;
if ($result) {
    $fetID=$galileo->preFetch('centavos');
    while ($row=$galileo->getFetch('centavos',$fetID)) {
        $modulo=$row;
        $c++;
    }
}

if ($c==0) die('Modulo non trovato');

//echo json_encode($sezione['moduli']);

if(!$parametri=json_decode($modulo[$nebulaParams['tipo']],true)) $parametri=array();

///////////////////////////////////////

$arr=array(
    "titolo"=>$nebulaParams['titolo'],
    "tipo"=>$nebulaParams['tipo']
);

$galileo->setTransaction(true);
$galileo->executeGeneric('centavos','addParametro',$arr,"");

//restituisce un array coin l'ID del parametro appena creato
//{"ID_modulo":"3"}
$resvar=$galileo->getResvar();

//echo json_encode($galileo->getLog('query'));
//echo json_encode($resvar);

if (!isset($resvar['ID_parametro'])) die ('Errore creazione parametro');

$galileo->executeClear('centavos');

///////////////////////////////////////

$parametri[]=$resvar['ID_parametro'];

$arr=array();
$arr[$nebulaParams['tipo']]=json_encode($parametri);


$galileo->setTransaction(false);
$galileo->executeUpdate('centavos','CENTAVOS_moduli',$arr,$wclause);
$result=$galileo->getResult();
//se l'update non è andato a buon fine cancella il modulo appena creato
if (!$result) {
    $wclause="ID='".$resvar['ID_parametro']."'";
    $galileo->executeDelete('centavos','CENTAVOS_parametri',$wclause);
}

echo json_encode($galileo->getOpLog('centavos','errori'));
echo json_encode($galileo->getLog('query'));
//echo json_encode($resvar);

?>