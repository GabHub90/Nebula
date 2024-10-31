<?php

include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_baseline.php");
include('class.php');

//===========================================
//configurazione GALILEO per le funzioni NON di base
//===========================================

$app=new nebulaSthor_presenza($nebulaParams,$galileo);

//iDesk EXTENDS basilare
//basilare instanzia "nebulaID" per interpretare "configUtente"
//basilare decide che tipo di applicazione caricare in base all'utente(classe ID) tramite il metodo setClass

$app->setClass();

$app->build();

echo $app->export();

?>
