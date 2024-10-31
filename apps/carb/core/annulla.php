<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_carb.php');

$param=$_POST['param'];

$obj=new galileoCarb();
$nebulaDefault['carb']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$arr=array(
    "id_annullo"=>$param['id_annullo'],
    "stato"=>$param['stato'],
    "d_annullo"=>date('Ymd'),
    "mov_open"=>0,
    "nota_annullo"=>$param['nota_annullo']
);

//impostato mov_open per evitare che rimanga aperta la videata e si modifichi un buono già chiuso
$galileo->executeUpdate('carb','CARB_buoni',$arr,"ID='".$param['ID']."'");

echo json_encode($galileo->getLog('query'));

?>