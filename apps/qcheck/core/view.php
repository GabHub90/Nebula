<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/qcheck/classi/qc_viewer.php');

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_qcheck.php');

//setta le funzioni GALILEO necessarie
$nebulaDefault=array();
$obj=new galileoQcheck();
$nebulaDefault['qcheck']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$nebulaParams=$_POST['param'];

$view=new qcheckViewer($nebulaParams['controllo'],$nebulaParams['modulo'],$galileo);

$view->view();

?>