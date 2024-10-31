<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/odl_func.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/ermes/classi/ermes.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_ermes.php');

$obj=new galileoErmes();
$nebulaDefault['ermes']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$param=$_POST['param'];

$ermes=new ermes($galileo);
$tk=new ermesTicket($galileo);
$tk->setContesto($param['caller']);

$res=array(
    "stato"=>1,
    "txt"=>"",
    "log"=>""
);

$ermes=new ermes($galileo);
$rep=$ermes->buildRep();
ob_start();
    $tk->drawNew($rep['reparti'],$param['logged'],$param['padre'],$param['reparto'],$param['categoria']);
$res['txt']=base64_encode(ob_get_clean());


echo json_encode($res);


?>