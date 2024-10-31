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
    "query"=>""
);

$param['da']=mainFunc::gab_input_to_db($param['da']);
$param['a']=mainFunc::gab_input_to_db($param['a']);

////////////////////////////////
//verifica intersezione
$intersezioni=0;
///////////////////////////////

$wc="coll='".$param['coll']."' AND data_i<='".$param['a']."' AND data_f>='".$param['da']."' AND ID!='".$param['ID']."' ";
$galileo->executeCount('tempo','TEMPO_periodi',$wc);
$result=$galileo->getResult();
if ($result) {
    $fetID=$galileo->preFetch('tempo');
    while($row=$galileo->getFetch('tempo',$fetID)) {
        $intersezioni+=$row['numero_elementi'];
    }
}

$wc="coll='".$param['coll']."' AND data<='".$param['a']."' AND data>='".$param['da']."'";
$galileo->executeCount('tempo','TEMPO_permessi',$wc);
$result=$galileo->getResult();
if ($result) {
    $fetID=$galileo->preFetch('tempo');
    while($row=$galileo->getFetch('tempo',$fetID)) {
        $intersezioni+=$row['numero_elementi'];
    }
}

$wc="coll='".$param['coll']."' AND data<='".$param['a']."' AND data>='".$param['da']."'";
$galileo->executeCount('tempo','TEMPO_extra',$wc);
$result=$galileo->getResult();
if ($result) {
    $fetID=$galileo->preFetch('tempo');
    while($row=$galileo->getFetch('tempo',$fetID)) {
        $intersezioni+=$row['numero_elementi'];
    }
}

if ($intersezioni>0) {
    $res['stato']='KO';
    $res['error']='Il periodo interseca altri eventi';

    die( json_encode($res) );
}

$galileo->clearQueryOggetto('default','tempo');
///////////////////////////////

$obj=array(
    "coll"=>$param['coll'],
    "tipo"=>$param['tipo'],
    "data_i"=>$param['da'],
    "data_f"=>$param['a']
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

    $result=$galileo->executeUpdate('tempo','TEMPO_periodi',$obj,"ID='".$param['ID']."'");
}
else {
    //INSERT
    $obj['utente_inserimento']=$param['logged'];
    $obj['dat_inserimento']=date('Ymd');

    $result=$galileo->executeInsert('tempo','TEMPO_periodi',$obj);
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