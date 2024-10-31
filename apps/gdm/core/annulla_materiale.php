<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require_once(DROOT.'/nebula/apps/gdm/classi/materiale.php');
include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/gab500/galileo_gdm.php");

$param=$_POST['param'];

$obj=new galileoGDM();
$nebulaDefault['gdm']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

///////////////////////////////////////////////////////////

$galileo->setTransaction(true);

$galileo->executeGeneric('gdm','annullaMateriale',$param,'');

?>