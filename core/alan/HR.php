<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_alan.php');

$obj=new galileoAlan();
$nebulaDefault['alan']=array("gab500",$obj);
$galileo->setFunzioniDefault($nebulaDefault);

$rif=false;
        
$galileo->executeSelect('alan','ALAN_parametri',"parametro='indiceHR'",'');
$result=$galileo->getResult();
if($result) {
    $fetID=$galileo->preFetch('alan');
    while ($row=$galileo->getFetch('alan',$fetID)) {
        $rif=$row['valore'];
    }
}

if (!$rif) return;

$galileo->clearQuery();

$newRif=$rif;

$galileo->getTimbratureSolariHR($rif);
$result=$galileo->getResult();

$txt="";

if($result) {
    $fetID=$galileo->preFetchBase('badge');
    while ($row=$galileo->getFetchBase('badge',$fetID)) {
        
        $txt.=$row['CODFISC'].$row['d'].$row['h'].$row['VERSOO'].PHP_EOL;

        $newRif=$row['IDTIMBRATURA'];
    }
}

$galileo->closeHandler('solari'); 

if ($txt!="") {

    $file='C:/HR/'.time().'.txt';

    if (file_put_contents($file, $txt)) {

        //aggiorna indice di riferimento
        if ($newRif>$rif) {
            $galileo->clearQuery();
            $arr=array(
                "valore"=>$newRif
            );
            $galileo->executeUpdate('alan','ALAN_parametri',$arr,"parametro='indiceHR'");
        }
    }
}

?>
