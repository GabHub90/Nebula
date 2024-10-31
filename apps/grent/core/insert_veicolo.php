<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
//include(DROOT.'/nebula/core/veicolo/classi/wormhole.php');

include (DROOT.'/nebula/galileo/gab500/galileo_grent.php');

$param=$_POST['param'];

//$wh=new veicoloWH('',$galileo);
//$wh->initGalileo($param['dms']);

$param=$_POST['param'];

$obj=new galileoGrent();
$nebulaDefault['grent']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$ret=array(
    "res"=>"ko",
    "error"=>"Errore Inserimento"
);

$dup=false;
//////////////////////////
//verifica esistenza già del veicolo in una lista qualsiasi in stato NON chiuso
$galileo->executeSelect('grent','GRENT_veicoli',"rif_id='".$param['rif']."' AND stato!='chiuso'",'');
if ($galileo->getResult()){
    $fid=$galileo->preFetch('grent');
    while($row=$galileo->getFetch('grent',$fid)) {
        $dup=$row['lista'];
    }
}
else {
    $ret['res']='ko';
    $ret['error']='Errore verifica duplicazione';

    die (json_encode($ret));
}

if ($dup) {
    $ret['res']='ko';
    $ret['error']='Vettura già presente nella lista '.$dup;

    die (json_encode($ret));
}

//inserimento del veicolo in GRENT_veicoli
$galileo->clearQuery();
$galileo->clearQueryOggetto("default","grent");

$arr=array(
    "dms"=>$param['dms'],
    "lista"=>$param['lista'],
    "rif_id"=>$param['rif'],
    "stato"=>"regolare",
    "marca"=>$param['marca'],
    "data_i"=>date('Ymd')
);

$galileo->executeInsert('grent','GRENT_veicoli',$arr);
if ($galileo->getResult()) {
    $ret['res']='ok';
    $ret['error']='';

    die (json_encode($ret));
}

echo json_encode($ret);

?>