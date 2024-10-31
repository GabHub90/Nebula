<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/nebula_id.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/app_base_class.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_carb.php');

include('carb_class.php');

if (!isset($_POST['param']['contesto']['mainLogged'])) die ('Accesso Negato !!!');

$nebulaParams=$_POST['param'];

$obj=new galileoCarb();
$nebulaDefault['carb']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$carb=new carbApp($nebulaParams,$galileo);

$carb->draw();

?>