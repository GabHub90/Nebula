<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_tempo.php');

//setta le funzioni GALILEO necessarie
$nebulaDefault=array();
$obj=new galileoTempo();
$nebulaDefault['tempo']=array("gab500",$obj);
$galileo->setFunzioniDefault($nebulaDefault);

$param=$_POST['param'];

$res=array(
    "stato"=>"KO",
    "error"=>"",
    "query"=>"",
    "intersezioni"=>0
);

////////////////////////////////
//verifica intersezione
$intersezioni=0;
///////////////////////////////

$wc="coll='".$param['coll']."' AND data='".$param['giorno']."' AND ora_i<='".$param['a']."' AND ora_f>='".$param['da']."' AND ID!='".$param['ID']."'";
$galileo->executeCount('tempo','TEMPO_extra',$wc);
$result=$galileo->getResult();
if ($result) {
    $fetID=$galileo->preFetch('tempo');
    while($row=$galileo->getFetch('tempo',$fetID)) {
        $res['intersezioni']+=$row['numero_elementi'];
    }
}

$wc="coll='".$param['coll']."' AND data='".$param['giorno']."' AND ora_i<='".$param['a']."' AND ora_f>='".$param['da']."' ";
$galileo->executeCount('tempo','TEMPO_permessi',$wc);
$result=$galileo->getResult();
if ($result) {
    $fetID=$galileo->preFetch('tempo');
    while($row=$galileo->getFetch('tempo',$fetID)) {
        $res['intersezioni']+=$row['numero_elementi'];
    }
}

if ($res['intersezioni']>0) {
    $res['stato']='KO';
    $res['error']='Il periodo interseca altri eventi';

    die( json_encode($res) );
}

$galileo->clearQueryOggetto('default','tempo');
///////////////////////////////

$obj=array(
    "coll"=>$param['coll'],
    "tipo"=>$param['tipo'],
    "ora_i"=>$param['da'],
    "ora_f"=>$param['a'],
    "data"=>$param['giorno']

);

if ($param['conferma']=='true') {
    $obj['utente_conferma']=$param['logged'];
    $obj['dat_conferma']=date('Ymd');
}
else {
    $obj['utente_conferma']='NULL';
    $obj['dat_conferma']='NULL';
}

if ($param['ID']!="") {
    //UPDATE
    $obj['utente_modifica']=$param['logged'];
    $obj['dat_modifica']=date('Ymd');

    $result=$galileo->executeUpdate('tempo','TEMPO_extra',$obj,"ID='".$param['ID']."'");
}
else {
    //INSERT
    $obj['utente_inserimento']=$param['logged'];
    $obj['dat_inserimento']=date('Ymd');

    $result=$galileo->executeInsert('tempo','TEMPO_extra',$obj);
}

$res['query']=$galileo->getLog('query');

if ($result) {
    $res['stato']='OK';
}
else {
    $res['stato']='KO';
    $res['error']='Errore scrittura DB';
}

echo json_encode($res);

?>