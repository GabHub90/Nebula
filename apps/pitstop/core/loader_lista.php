<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/pitstop/classi/lista.php");

if (!isset($_POST['param'])) $params=array();
else $params=$_POST['param'];

/*$obj=new galileoGrent();
$nebulaDefault['grent']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);*/

$lista=new pitstopLista($params,$galileo);

$lista->draw();

?>