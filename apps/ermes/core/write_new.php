<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/ermes/classi/ermes.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_ermes.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_dudu.php');

$obj=new galileoErmes();
$nebulaDefault['ermes']=array("gab500",$obj);

$obj=new galileoDudu();
$nebulaDefault['dudu']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$param=$_POST['param'];

$ticket=array(
    "categoria"=>$param['categoria'],
    "reparto"=>$param['reparto'],
    "des_reparto"=>$param['des_reparto'],
    "creatore"=>$param['creatore'],
    "gestore"=>$param['gestore'],
    "d_creazione"=>date('Ymd:H:i'),
    "mittente"=>$param['mittente'],
    "urgenza"=>$param['urgenza'],
    "stato"=>"attesa",
    "scadenza"=>mainFunc::calcolaScadenza(time(),$param['scadenza']),
    "padre"=>$param['padre'],
    "nota"=>$param['nota'],
    "msg"=>$param['msg']
);

$galileo->setTransaction(true);

$galileo->executeGeneric('ermes','newTicket',$ticket,'');

//////////////////////////////////////////////////////////

$tk=new ermesTicket($galileo);
$tk->setContesto($param['caller']);

foreach ($galileo->getResvar() as $k=>$id) {
    
    $galileo->executeSelect('ermes','ERMES_ticket',"ID='".$id."'","");

    if ($galileo->getResult()) {
        $fid=$galileo->preFetch('ermes');
        while ($row=$galileo->getFetch('ermes',$fid)) {
            $tk->build($row);
        }
    }
}

$tk->drawTicket($param['creatore']);

?>