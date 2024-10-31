<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/avalon/classi/avalon_set_day.php");

$param=$_POST['param'];

$param['inizio']=mainFunc::gab_input_to_db($param['da']);
$param['fine']=mainFunc::gab_input_to_db($param['a']);
$param['chkFlag']=false;
$param['inarrivoFlag']=true;

$param['odlFlag']=1;

$lista=new avalonSetday($param,$galileo);

$lista->drawPren();

?>