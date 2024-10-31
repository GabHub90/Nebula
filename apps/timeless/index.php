<?php

ob_start('ob_gzhandler');

include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/nebula_id.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/app_base_class.php");
include('timeless_class.php');

if (!isset($_POST['param']['contesto']['mainLogged'])) die ('Accesso Negato !!!');

//setta le funzioni GALILEO necessarie
//$nebulaDefault=array();
//$galileo->setFunzioniDefault($nebulaDefault);

$nebulaParams=$_POST['param'];

$time=new timelessApp($nebulaParams,$galileo);

$time->build();

$time->draw();

?>