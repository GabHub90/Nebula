<?php

include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_baseline.php");
include('class.php');

//===========================================
//configurazione GALILEO per le funzioni NON di base
//===========================================

$app=new nebulaTempo($nebulaParams,$galileo);

$app->setClass();

$app->build();

echo $app->export();

?>
