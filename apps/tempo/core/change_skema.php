<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_tempo.php');

//setta le funzioni GALILEO necessarie
$nebulaDefault=array();
$obj=new galileoTempo();
$nebulaDefault['tempo']=array("gab500",$obj);
$galileo->setFunzioniDefault($nebulaDefault);

$param=$_POST['param'];

$res=array(
    "stato"=>"KO",
    "param"=>$param,
    "query"=>""
);

if ($param['operazione']=='delete') {

    $wc="collaboratore='".$param['collaboratore']."' AND panorama='".$param['panorama']."' AND tag='".$param['tag']."' AND skema='".$param['skema']."' AND turno='".$param['turno']."' AND azione='".$param['azione']."'";
    $galileo->executeDelete('tempo','TEMPO_sostituzioni',$wc);
    $result=$galileo->getResult();
}

if ($param['operazione']=='insert') {

    $result=$galileo->executeInsert('tempo','TEMPO_sostituzioni',$param,'');
}

if ($result) {
    $res['stato']='OK';
}

$res['query']=$galileo->getLog('query');

echo json_encode($res);

?>