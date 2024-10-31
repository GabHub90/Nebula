<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/infinity/infinity_veicoli.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/gab500/galileo_grent.php");

require($_SERVER['DOCUMENT_ROOT']."/nebula/apps/grent/classi/grent_manage.php");

$param=$_POST['param'];

$obj=new galileoInfinityVeicoli();
$nebulaDefault['veicoli']=array("rocket",$obj);

$obj=new galileoGrent();
$nebulaDefault['grent']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$grent=new grentManage($param['tipoRent'],$param['logged'],$galileo);
$grent->buildManage($param['grent_id']);

$grent->drawManage($param['marca'],$param['rif']);

?>