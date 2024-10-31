<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_carb.php');

$param=$_POST['param'];

$obj=new galileoCarb();
$nebulaDefault['carb']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

if (!isset($param['ID']) || $param['ID']=='') die ('ID non definito');

//impostato mov_open per evitare che rimanga aperta la videata e si modifichi un buono già chiuso
$galileo->executeDelete('carb','CARB_buoni',"ID='".$param['ID']."' AND mov_open='1'");

?>