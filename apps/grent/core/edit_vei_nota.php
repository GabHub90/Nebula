<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/gab500/galileo_grent.php");

$param=$_POST['param'];

$obj=new galileoGrent();
$nebulaDefault['grent']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

if (!isset($param['txt'])) $param['txt']='';

$galileo->executeUpdate('grent','GRENT_veicoli',array('note'=>$param['txt']),"grent_id='".$param['grent_id']."'");

?>