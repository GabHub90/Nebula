<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/nebula_id.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/app_base_class.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/gab500/galileo_croom.php");
include('c2r_class.php');


if (!isset($_POST['param']['contesto']['mainLogged'])) die ('Accesso Negato !!!');

$nebulaDefault=array();
$obj=new galileoCroom();
$nebulaDefault['croom']=array("gab500",$obj);
$galileo->setFunzioniDefault($nebulaDefault);

$nebulaParams=$_POST['param'];

$ws=new c2rApp($nebulaParams,$galileo);

$ws->draw();

?>