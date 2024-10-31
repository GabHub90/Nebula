<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/comest/classi/comest.php");

$param=$_POST['param'];

//construct inizializza galileo per COMEST
$c=new nebulaComest($galileo);

$conferma=false;

if (isset($param['commessa']) && isset($param['revisione'])) {
    include('saveCore.php');
}

///////////////////////////////////////////////////////////////

$galileo->clearQuery();
$galileo->clearQueryOggetto('default','comest');

$galileo->executeDelete('comest','COMEST_lavorazioni',"commessa='".$param['rif']."' AND revisione='".$param['rev']."' AND ID='".$param['riga']."'");

///////////////////////////////////////////////////////////////

//echo json_encode($galileo->getLog('query'));

$c->init(array('rif'=>$param['rif']));
$c->draw();

?>