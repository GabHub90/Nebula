<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/odl_func.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/ermes/classi/ermes.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_ermes.php');

$obj=new galileoErmes();
$nebulaDefault['ermes']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$param=$_POST['param'];

$param['da']=mainFunc::gab_input_to_db($param['da']);
$param['a']=mainFunc::gab_input_to_db($param['a']);

$ermes=new ermes($galileo);

$ermes->loadMiei($param,false);

//echo json_encode($galileo->getLog('query'));

?>