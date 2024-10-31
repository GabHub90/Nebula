<?php
include("baseline.php");

foreach ($_REQUEST['param'] as $k=>$v) {
    $nebulaParams[$k]=$v;
}

include('nebula_system.php');

//echo json_encode($nebulaParams);

$sistema=new nebulaSystem('',$nebulaParams['nebulaContesto'],'',$galileo);

//da ajax a post gli array vuoti spariscono ??????
if (!isset($nebulaParams['args'])) $nebulaParams['args']=array();
if (!isset($nebulaParams['ribbon'])) $nebulaParams['ribbon']=array();

//CURL scrive il risultato nel documento di default (non serve ECHO)
$sistema->startFunction($nebulaParams['funzione'],$nebulaParams['ribbon'],$nebulaParams['args']);

?>