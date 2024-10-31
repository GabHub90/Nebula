<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/comest/classi/comest.php");

$param=$_POST['param'];

//construct inizializza galileo per COMEST
$c=new nebulaComest($galileo);

$galileo->setTransaction(true);
$galileo->executeDelete('comest','COMEST_revisioni',"commessa='".$param['commessa']."' AND revisione='".$param['revisione']."'");
$galileo->executeDelete('comest','COMEST_lavorazioni',"commessa='".$param['commessa']."' AND revisione='".$param['revisione']."'");

///////////////////////////////////////////////////////////////

$c->init(array('rif'=>$param['commessa']));
$c->draw();

?>