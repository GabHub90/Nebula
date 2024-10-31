<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_scontrillo.php');

$param=$_POST['param'];

$obj=new galileoScontrillo();
$nebulaDefault['strillo']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$obj=json_decode(base64_decode($param['obj']),true);

if ($obj) {

    $a=array(
        "rif_dms"=>$param['rif_dms'],
        "dms"=>$obj['dms'],
        "d_fatt"=>$obj['d_fatt'],
        "cassa"=>$param['cassa'],
        "chiusura"=>$param['chiusura'],
        "utente"=>$param['utente'],
        "d_reg"=>date('Ymd:H:i'),
        "reparto"=>$obj['reparto'],
        "num_fatt"=>$obj['num_fatt'],
        "intest_ragsoc"=>substr($obj['intest_ragsoc'],0,50),
        "desc_movimento"=>substr($obj['desc_movimento'],0,50),
        "incassi"=>$param['incassi']
    );

    //echo json_encode($a);
    $galileo->setTransaction(true);
    $galileo->executeGeneric('strillo','registraMovimento',$a,'');
}
?>