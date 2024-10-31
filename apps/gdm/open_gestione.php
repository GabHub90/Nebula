<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/nebula_id.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/app_base_class.php");

include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/gab500/galileo_gdm.php");
include('gdm_class.php');

$param=$_POST['param'];

if (!isset($param['telaio']) || !isset($param['dms']) || $param['telaio']=='' || $param['dms']=='' ) die ('dms e telaio non compatibili!!!');

$obj=new galileoGDM();
$nebulaDefault['gdm']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$param['ribbon']['gdm_dms']=$param['dms'];
$param['ribbon']['gdm_telaio']=$param['telaio'];

$gdm=new gdmApp($param,$galileo);

$gdm->drawGestioneSolo();

?>