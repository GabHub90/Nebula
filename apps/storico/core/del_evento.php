<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_odl.php');

$param=$_POST['param'];

$obj=new galileoODL();
$nebulaDefault['odl']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

///////////////////////////////////////////////////////////

$galileo->executeDelete('odl','OT2_eventi',"oggetto='".$param['oggetto']."' AND codice='".$param['codice']."' AND tipo='".$param['tipo']."'");

?>