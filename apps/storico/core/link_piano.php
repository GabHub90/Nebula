<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_odl.php');

$param=$_POST['param'];

$obj=new galileoODL();
$nebulaDefault['odl']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

///////////////////////////////////////////////////////////

$temp=explode('_',$param['piano']);

$arr=array(
    "marca"=>$param['marca'],
    "modello"=>$param['modello'],
    "gruppo"=>$temp[0],
    "indice"=>$temp[1]
);

$galileo->executeUpsert('odl','OT2_link_modgru',$arr,"marca='".$param['marca']."' AND modello='".$param['modello']."'");

//echo json_encode($param);
//echo json_encode($arr);
echo json_encode($galileo->getLog('query'));
//echo json_encode($galileo->getLog('errori'));

?>