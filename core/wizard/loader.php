<?php
include($_SERVER['DOCUMENT_ROOT'].'/nebula/main/baseline.php');
include('loader_class.php');

//###########################################################

include ($_SERVER['DOCUMENT_ROOT']."/nebula/apps/qcheck/classi/qc_widget.php");
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_qcheck.php');

//setta le funzioni GALILEO necessarie
$nebulaDefault=array();
$obj=new galileoQcheck();
$nebulaDefault['qcheck']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

//#############################################################

$param=$_POST['param'];

$wl=new wizardLoader($param['funzione'],$param['contesto'],$param['args'],$param['mainParam'],$galileo);
$wl->draw();

?>