<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/pratica_func.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/odl_func.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_alert.php');

$obj=new galileoAlert();
$nebulaDefault['alert']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$param=$_POST['param'];

$tempAlert=json_decode(base64_decode($param['alert']),true);

foreach ($tempAlert as $k=>$a) {
    $tempAlert[$k]['utente']=$param['utente'];
    $tempAlert[$k]['dataora']=date('Ymd:H:i');

    if (isset($param['update']) && array_key_exists($k,$param['update'])) {
        $tempAlert[$k]['stato']=$param['update'][$k];
    }
}

/////////////////////////////////////////////////////
//scrivi o modifica il record stato_lam
$arr=array(
    "pratica"=>$param['pratica'],
    "dms"=>$param['dms'],
    "rif"=>$param['rif'],
    "pren"=>$param['pren'],
    "lam"=>isset($param['lam'])?$param['lam']:'',
    "stato"=>isset($param['stato'])?$param['stato']:'XX',
    "dataora"=>date('Ymd:H:i'),
    "utente"=>$param['utente'],
    "scadenza"=>(isset($param['scadenza']) && $param['scadenza']!="")?str_replace('-','',$param['scadenza']):'',
    "alert"=>json_encode($tempAlert),
    "nota"=>isset($param['nota'])?$param['nota']:'',
    "prevfine"=>"xxxxxxxx:xx:xx"
);

$galileo->executeGeneric('alert','updateAlert',$arr,'');

?>