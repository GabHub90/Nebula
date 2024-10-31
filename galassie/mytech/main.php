<?php

include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline_params.php");
include('rtech_system.php');
$nebulaVersion="0.5";

//echo json_encode($nebulaParams);

//INSTANZIARE OGGETTO nebulaSystem
//in base al CONTESTO e quindi all'utente, permette di definire il MENU della galassia
//con i sistemi e le funzioni degli stessi
//disegna la base html della galassia/sistema in abse alle funzioni abilitate

$sistema=new rtechSystem('R-tech',$nebulaParams['nebulaContesto'],$nebulaVersion,$galileo);

$sistema->drawNebumenu();

$sistema->drawSystem();

?>