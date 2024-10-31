<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/odl_func.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/ermes/classi/ermes.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_ermes.php');

$obj=new galileoErmes();
$nebulaDefault['ermes']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$param=$_POST['param'];

$ermes=new ermes($galileo);

$log=array();

$galileo->getConfigUtente($param['logged'],date('Ymd'));
if ($galileo->getResult()) {
    $fid=$galileo->preFetchBase('maestro');
    while ($row=$galileo->getFetchBase('maestro',$fid)) {
        $log[]=$row;
    }
}

$ermes->loadGestione($param,$log,false);


?>