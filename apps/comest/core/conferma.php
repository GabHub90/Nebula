<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/comest/classi/comest.php");

$param=$_POST['param'];

//construct inizializza galileo per COMEST
$c=new nebulaComest($galileo);

$conferma=true;

include('saveCore.php');

///////////////////////////////////////////////////////////////

$c->init(array('rif'=>$param['commessa']['rif']));
$c->draw();

?>