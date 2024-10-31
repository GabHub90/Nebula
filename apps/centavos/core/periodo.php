<?php
include('default.php');
include("../classi/analisi.php");

if (!isset($nebulaParams['piano']) || ($nebulaParams['piano']=='')) die ("Piano non definito");
if (!isset($nebulaParams['periodo']) || ($nebulaParams['periodo']=='')) die ("Periodo non definito");

$wclause="ID='".$nebulaParams['piano']."'";
$orderby="data_i DESC";
$galileo->executeSelect('centavos','CENTAVOS_piani',$wclause,"");
$result=$galileo->getResult();
if ($result) {
    $fetID=$galileo->preFetch('centavos');
    while ($row=$galileo->getFetch('centavos',$fetID)) {
        $analisi=new centavosAnalisi($row,$galileo);
    }
}
else die('Errore caricamento Piano');

$analisi->buildPeriodo($nebulaParams['periodo']);

$analisi->drawPeriodo($nebulaParams['logged']);

?>