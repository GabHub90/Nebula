<?php

ob_start('ob_gzhandler');

include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/nebula_id.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/app_base_class.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/gab500/galileo_gdm.php");
include('gdm_class.php');

$nebulaParams=$_POST['param'];

// Imposta la funzione di gestione degli errori
//set_error_handler(['mainFunc','errorHandler']);

$obj=new galileoGDM();
$nebulaDefault['gdm']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$gdm=new gdmApp($nebulaParams,$galileo);

//echo json_encode($nebulaParams);

$gdm->draw();

?>