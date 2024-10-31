<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/concerto/concerto_odl.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/infinity/infinity_odl.php");

include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/gab500/galileo_croom.php");

include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/c2r/classi/class_fatturato_S.php");

$nebulaDefault=array();
$obj=new galileoConcertoODL();
$nebulaDefault['odl']=array("maestro",$obj);
$obj=new galileoCroom();
$nebulaDefault['croom']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$nebulaParams=$_POST['param'];

//echo json_encode($nebulaParams);

$obj=new c2rFatturato_S($nebulaParams,$galileo);

$obj->getLines();

$obj->draw();

?>