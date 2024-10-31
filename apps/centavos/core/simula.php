<?php
include('default.php');

include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/centavos/classi/struttura.php");

/*
i parametri passati sono:
piano               è l'ID del piano di incentivazione
variante            la variante con cui effettuare la simulazione
sezione             la sezione di cui effetturare la simulazione
valori              valori delle SOERGENTI passati per la simulazione
*/

if (!isset($nebulaParams['piano']) || ($nebulaParams['piano']=='')) die ("Piano non definito");
if (!isset($nebulaParams['variante']) || ($nebulaParams['variante']=='')) die ("Variante non definita");
if (!isset($nebulaParams['sezione']) || ($nebulaParams['sezione']=='')) die ("Sezione non definita");
if (!isset($nebulaParams['sorgenti']) || ($nebulaParams['sorgenti']=='')) die ("Sorgenti non definite");

$wclause="ID='".$nebulaParams['piano']."'";
$orderby="data_i DESC";
$galileo->executeSelect('centavos','CENTAVOS_piani',$wclause,"");
$result=$galileo->getResult();
if ($result) {
    $fetID=$galileo->preFetch('centavos');
    while ($row=$galileo->getFetch('centavos',$fetID)) {
        $cs=new centaStruttura($row,$galileo);
    }
}
else die('Errore caricamento Piano');

//contiene i valori che saranno ritornati
$ret=array();

//non deve scrivere niente ma definire le sezioni
ob_start();
    $cs->drawSelectVar($nebulaParams['variante']);
    $cs->drawStructBody($nebulaParams['variante'],'simula');
ob_clean();

//////////////////////////////////////////////////////
//calcolo della sezione
$cs->setSimula($nebulaParams['sorgenti']);
$ret=$cs->simula($nebulaParams['sezione']);

//echo json_encode($nebulaParams);
echo json_encode($ret);


?>