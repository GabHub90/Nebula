<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

require($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/idesk/classi/inofficina.php');

$param=$_POST['param'];

$param['inizio']=date('Ymd');
$param['fine']=$param['inizio'];

$lista=new ideskInofficina($param,$galileo);
$lista->getlines();

//esporta la lista in officina e sospesi
$ret=array(
    "sospesi"=>"",
    "esterni"=>"",
    "ricambi"=>"",
    "inofficina"=>"",
    "pronto"=>"",
    "timeline"=>""
);

ob_start();
$lista->drawInofficina(true);
$ret['inofficina']=base64_encode(ob_get_clean());

$ret['sospesi']=base64_encode($lista->drawSospeso());

$ret['esterni']=base64_encode($lista->drawEsterno());

$ret['ricambi']=base64_encode($lista->drawRicambio());

$ret['pronto']=base64_encode($lista->drawPronto());

$ret['timeline']=base64_encode("");

$tl=$lista->getTimeline();

ksort($tl);

$temp="";

foreach ($tl as $k=>$tk) {
    foreach ($tk as $k2=>$t) {
        $temp.=$t;
    }
}

if ($temp!="") $ret['timeline']=base64_encode($temp);

echo json_encode($ret);

//echo json_encode($lista->exportPratiche());

?>