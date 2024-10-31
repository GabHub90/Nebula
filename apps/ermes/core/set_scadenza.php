<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/odl_func.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_ermes.php');

$obj=new galileoErmes();
$nebulaDefault['ermes']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$param=$_POST['param'];

$a=array(
    "scadenza"=>mainFunc::gab_input_to_db($param['scadenza']).'00:00'
);

$galileo->executeUpdate('ermes','ERMES_ticket',$a,"ID='".$param['ticket']."'");


?>