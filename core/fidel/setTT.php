<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/fidel/fidel.php');

$fidel=new nebulaFidel('odl',$galileo);

$param=$_POST['param'];

$a=json_decode(base64_decode($param['tt']),true);
$a['utente']=$param['utente'];

//##########################
//$a['odl']='';
//##########################

$fidel->build($a);

$fidel->initJS();

$fidel->draw();

?>