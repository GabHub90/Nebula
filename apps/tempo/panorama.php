<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/nebula_id.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/app_base_class.php");
include("classi/panorama/panorama.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_tempo.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_alan.php');

$nebulaDefault=array();

$obj=new galileoTempo();
$nebulaDefault['tempo']=array("gab500",$obj);

$obj=new galileoAlan();
$nebulaDefault['alan']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$nebulaParams=$_POST['param'];

$pan=new tempoPanorama($nebulaParams,$galileo);

$pan->build(array());

$pan->draw();

?>