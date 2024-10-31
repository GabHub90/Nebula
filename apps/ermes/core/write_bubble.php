<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/ermes/classi/ermes.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_ermes.php');

$obj=new galileoErmes();
$nebulaDefault['ermes']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$param=$_POST['param'];

$b=json_decode(base64_decode($param['info']),true);
$b['msg']=$param['msg'];

//#######################################################
//VERIFICA SE SI È ANCORA IN GESTIONE DEL TICKET
$chain=new nebulaChain('ermes',$galileo);
if (!$chain->check($b['ID'],$b['utente'])) {
    die('{"ok":0,"txt":"L\'utente non è più attivo sul Ticket"}');
}

//LETTURA DEL TICKET ED EVENTUALE CALCOLO DI REACT
$tk=false;

$galileo->executeSelect('ermes','ERMES_ticket',"ID='".$b['ID']."'",'');
if ($galileo->getResult()) {
    $fid=$galileo->preFetch('ermes');
    while ($row=$galileo->getFetch('ermes',$fid)) {
        $tk=$row;
    }

}
else die ('{"ok":0,"txt":"Ticket non trovato."}');

if ($tk['react']==0) {
    $b['react']=mainFunc::gab_delta_tempo_c($tk['d_creazione'],date('Ymd:H:i'),'m');
}

if ($b['tipo']=='Q') $b['stato']='attesa';
else $b['stato']='progress';

if ($b['stato']=='attesa') {
    $ticket= new ermesTicket($galileo);
    $b['scadenza']=$ticket->calcolaScadenza(time(),$tk);
}

//#######################################################

$galileo->clearQuery();
$galileo->clearQueryOggetto('default','ermes');

$galileo->setTransaction(true);

$galileo->executeGeneric('ermes','newBubble',$b,'');

//echo json_encode($galileo->getLog('query'));

echo '{"ok":1}';

?>