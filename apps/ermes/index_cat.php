<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/nebula_id.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/app_base_class.php");

include('ermes_class.php');
include('ermes_mono.php');

if (!isset($_POST['param']['contesto']['mainLogged'])) die ('Accesso Negato !!!');

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_ermes.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_dudu.php');

$obj=new galileoErmes();
$nebulaDefault['ermes']=array("gab500",$obj);

$obj=new galileoDudu();
$nebulaDefault['dudu']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$nebulaParams=$_POST['param'];

//echo json_encode($nebulaParams);

$ermes=new ermesMono($nebulaParams,$galileo);

$ermes->draw();

?>