<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/nebula_id.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/app_base_class.php");
include("classi/panorama/panorama.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_tempo.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_alan.php');

$nebulaDefault=array();

$obj=new galileoTempo();
$nebulaDefault['tempo']=array("gab500",$obj);

$obj=new galileoAlan();
$nebulaDefault['alan']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$nebulaParams=$_POST['param'];

if (!$nebulaParams['ribbon']['tpo_today'] || $nebulaParams['ribbon']['tpo_today']=='') $nebulaParams['ribbon']['tpo_today']=date('Ymd');

$pan=new tempoPanorama($nebulaParams,$galileo);

$i=mainFunc::gab_tots($nebulaParams['ribbon']['tpo_today']);
while (date('w',$i)>0) {
    $i=strtotime("-1 day",$i);
}

$f=strtotime("+13 days",$i);

$arr=array(
    "intervallo"=>"libero",
    "data_i"=>date('Ymd',$i),
    "data_f"=>date('Ymd',$f),
    "tag"=>'d m Y',
    "steptag"=>"settimana",
    "step"=>"1",
    "fattore"=>"90",
    "telefono"=>true
);

$pan->build($arr);

$pan->draw();

?>