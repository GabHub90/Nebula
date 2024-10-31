<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/nebula_id.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/app_base_class.php");
include('../brogliaccio_class.php');

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_tempo.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_alan.php');

if (!isset($_POST['param']['contesto']['mainLogged'])) die ('Accesso Negato !!!');

$nebulaParams=$_POST['param'];

$obj=new galileoTempo();
$nebulaDefault['tempo']=array("gab500",$obj);

$obj=new galileoAlan();
$nebulaDefault['alan']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$bgc=new brogliaccioApp($nebulaParams,$galileo);

$txt=$bgc->esporta();

///////////////////////////////////////////////////////

$res=array(
    "data"=>base64_encode($txt),
    "mimetype"=>"text",
    "filename"=>"brogliaccio.csv"
);

echo json_encode($res);


?>