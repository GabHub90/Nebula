<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_qcheck.php');

//setta le funzioni GALILEO necessarie
$nebulaDefault=array();
$obj=new galileoQcheck();
$nebulaDefault['qcheck']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$nebulaParams=$_POST['param'];

$args=array("stato"=>"eliminato");

$galileo->setTransaction(true);
//executeUpdate($tipo,$tabella,$arr,$wclause)
$res=$galileo->executeUpdate("qcheck","QCHECK_storico_controlli",$args,"ID='".$nebulaParams['controllo']."'");

//se ci sono stati degli errori nella COSTRUZIONE (non in result)
if (!$res) {
    echo 'ERROR';
    echo json_encode($galileo->getLog('query'));
}

?>