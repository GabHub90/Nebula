<?php

ob_start('ob_gzhandler');

include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/nebula_id.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/app_base_class.php");
include('workshop_class.php');

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_tempo.php');
//include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_alan.php');

if (!isset($_POST['param']['contesto']['mainLogged'])) die ('Accesso Negato !!!');

$obj=new galileoTempo();
$nebulaDefault['tempo']=array("gab500",$obj);

//$obj=new galileoAlan();
//$nebulaDefault['alan']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$nebulaParams=$_POST['param'];

$ws=new workshopApp($nebulaParams,$galileo);

$ws->draw();

ob_end_flush();

?>