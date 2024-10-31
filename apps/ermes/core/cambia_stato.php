<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/odl_func.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_ermes.php');

$obj=new galileoErmes();
$nebulaDefault['ermes']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$param=$_POST['param'];

$a=array(
    "stato"=>$param['stato']
);

if ($param['stato']=='sospeso') $a['scadenza']='';

$galileo->executeUpdate('ermes','ERMES_ticket',$a,"ID='".$param['ID']."'");


?>