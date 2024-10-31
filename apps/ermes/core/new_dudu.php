<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_dudu.php');

$obj=new galileoDudu();
$nebulaDefault['dudu']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$param=$_POST['param'];

$link=array(
    "testo"=>$param['txt'],
    "rif"=>$param['ticket']
);

$galileo->setTransaction(true);

$galileo->executeGeneric('dudu','newLink',$link,'');

//echo json_encode($galileo->getLog('query'));

?>