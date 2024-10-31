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

$galileo->clearQueryOggetto('default','tempo');
///////////////////////////////

$obj=array(
    "coll"=>$param['coll'],
    "ora_i"=>$param['da'],
    "ora_f"=>$param['a'],
    "data"=>$param['giorno'],
    "panorama"=>$param['panorama'],
    "sub_a"=>($param['suba']=="")?'NULL':$param['suba']
);

if ($param['ID']!="") {
    //UPDATE
    $result=$galileo->executeUpdate('tempo','TEMPO_sposta',$obj,"ID='".$param['ID']."'");
}
else {
    //INSERT
    $result=$galileo->executeInsert('tempo','TEMPO_sposta',$obj);
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