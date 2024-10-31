<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/fidel/fidel.php');

$fidel=new nebulaFidel('fidi',$galileo);

$fidel->initJS();

$param=json_decode(base64_decode($_POST['param']),true);

$fidel->drawInterface($param['width'],$param['height']);

?>