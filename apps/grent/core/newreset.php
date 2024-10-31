<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/infinity/infinity_veicoli.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/gab500/galileo_grent.php");

$param=$_POST['param'];

$obj=new galileoInfinityVeicoli();
$nebulaDefault['veicoli']=array("rocket",$obj);

$obj=new galileoGrent();
$nebulaDefault['grent']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

//echo json_encode($param);
$param['d']=date('Ymd');

$galileo->executeInsert('grent','GRENT_reset',$param);
if ($galileo->getResult()) {
    //do per scontato che sia sempre INFINITY
    $galileo->executeUpdate('veicoli','OFF_VEICOLI',array('km'=>$param['km_reset']),"id_veicolo='".$param['rif_vei']."'");
}
//else echo json_encode($galileo->getLog('query'));
else echo 'ERROR GRENT';


?>