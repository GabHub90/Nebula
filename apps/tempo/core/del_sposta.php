<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_tempo.php');

//setta le funzioni GALILEO necessarie
$nebulaDefault=array();
$obj=new galileoTempo();
$nebulaDefault['tempo']=array("gab500",$obj);
$galileo->setFunzioniDefault($nebulaDefault);

$param=$_POST['param'];

$galileo->executeDelete('tempo','TEMPO_sposta',"ID='".$param['ID']."'");

?>