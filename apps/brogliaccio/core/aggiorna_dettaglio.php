<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_tempo.php');

//setta le funzioni GALILEO necessarie
$nebulaDefault=array();

$obj=new galileoTempo();
$nebulaDefault['tempo']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$param=$_POST['param'];

$param['obj']=json_encode($param['obj']);

/////////////////////////////////////////////////
//verifica esistenza record
$check=0;

$wc="reparto='".$param['reparto']."' AND coll='".$param['coll']."' AND tag='".$param['tag']."'";
$galileo->executeCount('tempo','TEMPO_dettaglio_bgc',$wc);
$result=$galileo->getResult();
if ($result) {
    $fetID=$galileo->preFetch('tempo');
    while($row=$galileo->getFetch('tempo',$fetID)) {
        $check=$row['numero_elementi'];
    }
}

$galileo->clearQuery();
$galileo->clearQueryOggetto('default','tempo');

if ($check>0) {
    $wc="reparto='".$param['reparto']."' AND coll='".$param['coll']."' AND tag='".$param['tag']."'";
    $arr=array(
        "obj"=>$param['obj']
    );

    $galileo->executeUpdate('tempo','TEMPO_dettaglio_bgc',$arr,$wc);
}
else {
    $galileo->executeInsert('tempo','TEMPO_dettaglio_bgc',$param);
}

$result=$galileo->getResult();

$res=array(
    "result"=>($result)?'OK':'KO'
);

echo json_encode($res);


?>