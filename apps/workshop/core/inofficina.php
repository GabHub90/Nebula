<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

require($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/idesk/classi/inofficina_ws.php');

$param=$_POST['param'];

$param['inizio']='20220201';
$param['fine']=date('Ymd');

$lista=new wsInofficina($param,$galileo);
$lista->getlines();

//esporta la lista in officina e sospesi
$ret=array(
    "sospesi"=>"",
    "officina"=>"",
    "attesa"=>""
);

ob_start();
$lista->drawInofficina(true);
$ret['officina']=base64_encode(ob_get_clean());

$ret['sospesi']=base64_encode($lista->drawSospeso());

echo json_encode($ret);

//echo json_encode($lista->exportPratiche());

?>