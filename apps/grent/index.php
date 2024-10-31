<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/nebula_id.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/app_base_class.php");

include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/infinity/infinity_veicoli.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/gab500/galileo_grent.php");

include('grent_class.php');

$nebulaParams=$_POST['param'];

$obj=new galileoInfinityVeicoli();
$nebulaDefault['veicoli']=array("rocket",$obj);

$obj=new galileoGrent();
$nebulaDefault['grent']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$grent=new grentApp($nebulaParams,$galileo);

$grent->draw();

?>