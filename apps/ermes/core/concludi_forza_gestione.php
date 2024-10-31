<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_ermes.php');

$obj=new galileoErmes();
$nebulaDefault['ermes']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$param=$_POST['param'];

$a=array(
    "ID"=>$param['ID'],
    "utente"=>$param['logged'],
    "gestore"=>$param['gestore']
);

$galileo->setTransaction(true);

$galileo->executeGeneric('ermes','concludiForzaGestione',$a,'');


?>