<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require_once(DROOT.'/nebula/core/odl/odl_func.php');
require($_SERVER['DOCUMENT_ROOT']."/nebula/apps/storico/classi/piano.php");

$param=$_POST['param'];

$odlFunc=new nebulaOdlFunc($galileo);
$piano=new nebulaStoricoPiano($param['marca'],$param['modello'],$param['telaio'],$odlFunc,$galileo);

if (isset($param['piano'])) $piano->buildID($param['piano']);
$piano->drawPiano(isset($param['piano'])?$param['piano']:"",isset($param['actual'])?$param['actual']:"");


?>