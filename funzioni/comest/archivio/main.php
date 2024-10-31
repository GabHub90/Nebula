<?php

include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_baseline.php");
include('class.php');

//===========================================
//configurazione GALILEO per le funzioni NON di base
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_comest.php');
$obj=new galileoComest();
$nebulaDefault['comest']=array("gab500",$obj);
$galileo->setFunzioniDefault($nebulaDefault);
//===========================================

$app=new comest($nebulaParams,$galileo);

$app->setClass();

$app->build();

echo $app->export();

?>
