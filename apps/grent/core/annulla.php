<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/gab500/galileo_grent.php");

$param=$_POST['param'];

$obj=new galileoGrent();
$nebulaDefault['grent']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$arr=array(
    "d_mod"=>date('Ymd'),
    "ute_mod"=>$param['logged'],
    "stato"=>'annullato'
);

$galileo->executeUpdate('grent','GRENT_pratiche',$arr,"nol_id='".$param['id']."'");

?>