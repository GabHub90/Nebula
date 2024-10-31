<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/fidel/fidel.php');

$fidel=new nebulaFidel('fidi',$galileo);

$param=$_POST['param'];

$fidel->drawListaTelai($param['tt']);

?>