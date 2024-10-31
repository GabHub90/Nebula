<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_tempo.php');

include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/c2r/classi/class_produttivita_S.php");

$nebulaDefault=array();

$obj=new galileoTempo();
$nebulaDefault['tempo']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$nebulaParams=$_POST['param'];

//echo json_encode($nebulaParams);

$obj=new c2rProduttivita_S($nebulaParams,$galileo);

$obj->getLines();

$obj->draw();

//echo json_encode($galileo->getLog('query'));

?>