<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/comest/classi/comest.php");

$param=$_POST['param'];

//construct inizializza galileo per COMEST
$c=new nebulaComest($galileo);
$c->init(array());

//{"rif":"","versione":1,"targa":"GE567PD","telaio":"WVWZZZCDZMW352615","descrizione":"GOLF 8 1.4 E-HYBRID DSG 204CV MY 21","dms":"infinity","odl":"0","fornitore":[],"d_apertura":"","utente_apertura":"","d_annullo":"","utente_annullo":""}

if (trim($param['targa'].$param['telaio'])=="") die ('Telaio e Targa entrambi nulli. Non è possibile procedere'); 

$galileo->clearQuery();
$galileo->clearQueryOggetto('default','comest');

$com=array(
    "versione"=>$c->getVersione(),
    "targa"=>isset($param['targa'])?trim($param['targa']):'',
    "telaio"=>isset($param['telaio'])?trim($param['telaio']):'',
    "descrizione"=>isset($param['descrizione'])?trim($param['descrizione']):'',
    "dms"=>isset($param['dms'])?$param['dms']:'',
    "odl"=>isset($param['odl'])?$param['odl']:'0',
    "fornitore"=>"",
    "controllo"=>json_encode($c->getControllo())
);

if (isset($param['fornitore']) && count($param['fornitore'])>0) {
    $arr['fornitore']=json_encode($param['fornitore']);
}

$galileo->setTransaction(true);
$galileo->executeGeneric('comest','insertCommessa',$com,'');

//echo json_encode($galileo->getLog('query'));
//echo json_encode($galileo->getLog('errori'));

$resVar=$galileo->getResvar();

$indice=$resVar['indice'];

if (!$indice || $indice=="") die ('Errore creazione Commessa!!!!');

$rev=array(
    "commessa"=>$indice,
    "revisione"=>1,
    "d_creazione"=>date('Ymd'),
    "utente_creazione"=>isset($param['utente'])?$param['utente']:''
);

$galileo->setTransaction(false);
$galileo->executeInsert('comest','COMEST_revisioni',$rev);

//echo json_encode($galileo->getLog('query'));

///////////////////////////////////////////////////////////////

$c->init(array('rif'=>$indice));
$c->draw();

?>