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

$lav=array(
    "commessa"=>$param['rif'],
    "revisione"=>$param['rev'],
    "zona"=>$param['zona'],
    "titolo"=>$param['titolo'],
    "ID"=>"###(SELECT isnull(max(ID),0)+1 FROM COMEST_lavorazioni WHERE commessa='".$param['rif']."' AND revisione='".$param['rev']."')"
);

$galileo->executeInsert('comest','COMEST_lavorazioni',$lav);

///////////////////////////////////////////////////////////////

$c->init(array('rif'=>$param['rif']));
$c->draw();

?>