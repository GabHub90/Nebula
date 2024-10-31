<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/nebula_id.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/app_base_class.php");
include('../workshop_class.php');

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_tempo.php');
//include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_alan.php');

$obj=new galileoTempo();
$nebulaDefault['tempo']=array("gab500",$obj);

//$obj=new galileoAlan();
//$nebulaDefault['alan']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$nebulaParams=$_POST['param'];

$nebulaParams['ribbon']=array(
    "wsp_officina"=>$nebulaParams['wsp_officina']
);

$nebulaParams['nebulaFunzione']="";

$ws=new workshopApp($nebulaParams,$galileo);

//echo json_encode($nebulaParams);

//$IDcoll,$statoLamentato,$checkID
$ret=$ws->special_SER($nebulaParams['IDcoll'],$nebulaParams['statoLamentato'],$nebulaParams['checkID']);

if (!$ret) {
    echo '{"result":"KO"}';
}
else {
    $arr=array(
        "result"=>"OK"
    );
    //"query"=>$galileo->getLog('query')

    echo json_encode($arr);
}

?>