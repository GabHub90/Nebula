<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/concerto/concerto_ricambi.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/infinity/infinity_ricambi.php");

include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/gab500/galileo_croom.php");

include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/c2r/classi/class_giacenza_M.php");

$nebulaDefault=array();
//$obj=new galileoConcertoRicambi();
//$nebulaDefault['ricambi']=array("maestro",$obj);
//$obj=new galileoInfinityRicambi();
//$nebulaDefault['ricambi']=array("rocket",$obj);
$obj=new galileoCroom();
$nebulaDefault['croom']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$nebulaParams=$_POST['param'];

//echo json_encode($nebulaParams);

$obj=new c2rGiacenza_M($nebulaParams,$galileo);

$obj->abilitaTot('gm19');

$obj->getLines();

$obj->draw();

?>