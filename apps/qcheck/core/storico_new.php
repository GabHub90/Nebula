<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_qcheck.php');

//setta le funzioni GALILEO necessarie
$nebulaDefault=array();
$obj=new galileoQcheck();
$nebulaDefault['qcheck']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$nebulaParams=$_POST['param'];

/*{
    "chiave":"1308722",
    "intestazione":"CCDK488 - EUROCAR ITALIA SRL",
    "controllo":"1",
    "reparto":"VWS",
    "versione":"1",
    "op_m1":"n.gjura",
    "va_m1":"1",
    "op_m2":"#m1",
    "va_m2":"1",
    "op_m3":"e.giannotti",
    "va_m3":"1"
}*/
/*
CONTROLLO
$this->default=array(
    "controllo"=>"",
    "versione"=>"",
    "reparto"=>"",
    "d_controllo"=>date('Ymd'),
    "chiave"=>"",
    "intestazione"=>"",
    "stato"=>"aperto"
);
*/

/*
MODULO
$this->default=array(
    "ID_controllo"=>"",
    "modulo"=>"",
    "variante"=>"",
    "esecutore"=>"",
    "operatore"=>"",
    "d_modulo"=>date('Ymd'),
    "risposte"=>"",
    "punteggio"=>"",
    "stato"=>"aperto"
);
*/

$args=array(
    "controllo"=>array(),
    "moduli"=>array()
);

/////////////////////////////////////////////////
//COMPLETAMENTO DI ARGS
/*
$args['controllo']['controllo']=$nebulaParams['controllo'];
$args['controllo']['versione']=$nebulaParams['versione'];
$args['controllo']['reparto']=$nebulaParams['reparto'];
$args['controllo']['chiave']=$nebulaParams['chiave'];
$args['controllo']['intestazione']=$nebulaParams['intestazione'];
*/

foreach ($nebulaParams as $k=>$v) {
    $prefix=substr($k,0,3);

    if ($prefix!='op_') {
        if ($prefix!='va_') $args['controllo'][$k]=$v;
        continue;
    }

    $m=substr($k,4);

    $args['moduli'][$k]['modulo']=$m;
    $args['moduli'][$k]['variante']=$nebulaParams['va_m'.$m];
    $args['moduli'][$k]['operatore']=$v;
}


/////////////////////////////////////////////////

$galileo->setTransaction(true);
//executeGeneric($tipo,$funzione,$args,$order)
$res=$galileo->executeGeneric("qcheck","insertNewCheck",$args,"");

//se ci sono stati degli errori nella COSTRUZIONE (non in result)
if (!$res) {
    echo 'ERROR';
    echo json_encode($galileo->getLog('query'));
}

//echo json_encode($galileo->getLog('query'));

echo json_encode($galileo->getResvar());



?>