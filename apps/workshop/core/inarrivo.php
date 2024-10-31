<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/avalon/classi/avalon_set_day.php");

$param=$_POST['param'];

$param['inizio']=mainFunc::gab_input_to_db($param['d']);
$param['fine']=$param['inizio'];
$param['chkFlag']=true;
$param['inarrivoFlag']=true;

$lista=new avalonSetday($param,$galileo);

$lista->drawPren();

?>