<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/comest/classi/comest.php");

$param=$_POST['param'];

//construct inizializza galileo per COMEST
$c=new nebulaComest($galileo);

$a=array(
    "controllo"=>$param['commessa']['controllo'],
    "d_controllo"=>date('Ymd'),
    "utente_controllo"=>$param['utente']
);

$galileo->executeUpdate('comest','COMEST_commesse',$a,"rif='".$param['commessa']['rif']."'");

///////////////////////////////////////////////////////////////

$c->init(array('rif'=>$param['commessa']['rif']));
$c->draw();

?>