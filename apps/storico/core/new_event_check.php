<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_odl.php');

$param=$_POST['param'];

$obj=new galileoODL();
$nebulaDefault['odl']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

///////////////////////////////////////////////////////////

$arr=array(
    "dms"=>$param['dms'],
    "odl"=>$param['rif'],
    "codice"=>$param['str'],
    "new"=>$param['new']
);

$galileo->executeInsert('odl','OT2_eventi_chk',$arr);

?>