<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/concerto/concerto_odl.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/infinity/infinity_odl.php");

include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/gab500/galileo_croom.php");

include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/gab500/galileo_comest.php");
include($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_tempo.php');

include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/c2r/classi/class_budget_S.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/c2r/classi/class_fatturato_S.php");

$nebulaDefault=array();

//inizializzazione di Galileo con il DB di NEBULA

$obj=new galileoTempo();
$nebulaDefault['tempo']=array("gab500",$obj);
$obj=new galileoComest();
$nebulaDefault['comest']=array("gab500",$obj);
$obj=new galileoConcertoODL();
$nebulaDefault['odl']=array("maestro",$obj);
$obj=new galileoCroom();
$nebulaDefault['croom']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$nebulaParams=$_POST['param'];

$obj=new c2rBudget_S($nebulaParams,$galileo);

$obj->build();
$obj->draw();

?>