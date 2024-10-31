<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_centavos.php');

//setta le funzioni GALILEO necessarie
$nebulaDefault=array();
$obj=new galileoCentavos();
$nebulaDefault['centavos']=array("gab500",$obj);

//prendi il riferimento dell'oggetto utenti da Galileo
$l=$galileo->getObjectBase("maestro");
//aggiungi il riferimento dell'oggetto a "centavos"
$obj->addLink('maestro',$l);

$galileo->setFunzioniDefault($nebulaDefault);

$nebulaParams=$_POST['param'];

?>