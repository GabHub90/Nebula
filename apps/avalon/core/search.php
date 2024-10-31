<?php

include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/avalon/classi/avalon_search.php");

$param=$_POST['param'];

$param['inizio']=date('Ymd',strtotime("-6 month",time()));
$param['fine']=date('21001231');
$param['chkFlag']=false;
$param['inarrivoFlag']=true;

$param['odlFlag']=1;

$lista=new avalonSearch($param,$galileo);

$lista->drawPren();

?>