<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_qcheck.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/concerto/concerto_veicoli.php');

//02.03.2021 per il momento non creo gli oggetti TABELLA per GALILEO ma faccio direttamente la query.

//setta le funzioni GALILEO necessarie
$nebulaDefault=array();

$obj=new galileoQcheck();
$nebulaDefault['qcheck']=array("gab500",$obj);

$obj=new galileoConcertoVeicoli();
$nebulaDefault['veicoli']=array("maestro",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$nebulaParams=$_POST['param'];

//leggi veicolo nel DB
//executeGeneric($tipo,$funzione,$args,$order)
$galileo->executeGeneric('veicoli','getQcheck',array($nebulaParams['targa']),"");

$tempintest="Veicolo non presente";

$result=$galileo->getResult();

if ($result) {
    $fetID=$galileo->preFetch('veicoli');
    while ($row=$galileo->getFetch('veicoli',$fetID)) {
        $tempintest=substr($row['des_veicolo'],0,25);
    }
}

//GENERAZIONE CHIAVE
$wClause="chiave LIKE '".$nebulaParams['targa']."_' AND controllo='2'";
$galileo->executeSelect('qcheck','QCHECK_storico_controlli',$wClause,"");
$fetID=$galileo->preFetch('qcheck');
$indice=1;
while ( $row=$galileo->getFetch('qcheck',$fetID) ) {
    //echo json_encode($row);
    $rif=explode('_',$row['chiave']);
    if ((int)$rif[1]>=$indice) $indice=(int)$rif[1]+1;
}

////////////////////////////////////////////////////////////

$res=array(
    "chiave"=>strtoupper($nebulaParams['targa']).'_'.$indice,
    "intestazione"=>$tempintest,
    "reparto"=>$nebulaParams['reparto']
);


//echo json_encode($galileo->getLog('query'));
echo json_encode($res);


?>