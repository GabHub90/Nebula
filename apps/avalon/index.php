<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/nebula_id.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/app_base_class.php");
include('avalon_class.php');

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_tempo.php');
//include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/maestro_schemi.php');

if (!isset($_POST['param']['contesto']['mainLogged'])) die ('Accesso Negato !!!');

$obj=new galileoTempo();
$nebulaDefault['tempo']=array("gab500",$obj);
//$obj=new galileoMaestroSchemi();
//$nebulaDefault['schemi']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$nebulaParams=$_POST['param'];

$avalon=new avalonApp($nebulaParams,true,$galileo);

$avalon->draw();

?>