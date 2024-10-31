<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_alan.php');

//setta le funzioni GALILEO necessarie
$nebulaDefault=array();
$obj=new galileoAlan();
$nebulaDefault['alan']=array("gab500",$obj);
$galileo->setFunzioniDefault($nebulaDefault);

$param=$_POST['param'];

$galileo->executeDelete('alan','ALAN_timbrature_k',"IDTIMBRATURA='".$param['ID']."'");

echo json_encode($param);

?>