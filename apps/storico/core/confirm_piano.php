<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_odl.php');

$param=$_POST['param'];

$obj=new galileoODL();
$nebulaDefault['odl']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

///////////////////////////////////////////////////////////

$temp=explode("_",$param['indice']);

$wc="codice='".$temp[0]."' AND indice='".$temp[1]."'";

$galileo->executeUpdate('odl','OT2_gruppi',array("oggetti"=>json_encode($param['oggetti'])),$wc);


?>