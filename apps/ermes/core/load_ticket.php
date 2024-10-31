<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/odl_func.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/ermes/classi/ermes.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_ermes.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_dudu.php');

$obj=new galileoErmes();
$nebulaDefault['ermes']=array("gab500",$obj);

$obj=new galileoDudu();
$nebulaDefault['dudu']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$param=$_POST['param'];

$ermes=new ermes($galileo);
$tk=new ermesTicket($galileo);
$tk->setContesto($param['caller']);

$res=array(
    "stato"=>1,
    "txt"=>"",
    "log"=>""
);

//se si tratta di un nuovo ticket
if ($param['id']==0) {
    $ermes=new ermes($galileo);
    $rep=$ermes->buildRep();
    ob_start();
        $tk->drawNew($rep['reparti'],$param['logged'],$param['padre'],'','');
    $res['txt']=base64_encode(ob_get_clean());
}

else {

    $log=array();

    $galileo->getConfigUtente($param['logged'],date('Ymd'));
    if ($galileo->getResult()) {
        $fid=$galileo->preFetchBase('maestro');
        while ($row=$galileo->getFetchBase('maestro',$fid)) {
            $log[]=$row;
        }
    }

    $res['log']=base64_encode(json_encode($log));


    //$tk=new ermesTicket($galileo);
        
    $galileo->executeSelect('ermes','ERMES_ticket',"ID='".$param['id']."'","");

    if ($galileo->getResult()) {
        $fid=$galileo->preFetch('ermes');
        while ($row=$galileo->getFetch('ermes',$fid)) {
            $tk->build($row);
        }
    }

    //controllo spostato su JS
    //if ($tk->check($log)) {

        ob_start();
            $tk->drawTicket($param['logged']);
        $res['txt']=base64_encode(ob_get_clean());
    //}
    /*else {
        $res['stato']=0;
        $res['txt']=base64_encode('Utente non abilitato a vedere il Ticket.');
    }*/
}

echo json_encode($res);


?>