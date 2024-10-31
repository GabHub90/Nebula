<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_qcheck.php');

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/concerto/concerto_odl.php');
include_once($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/infinity/infinity_odl.php");

//02.03.2021 per il momento non creo gli oggetti TABELLA per GALILEO ma faccio direttamente la query.

//setta le funzioni GALILEO necessarie
$nebulaDefault=array();

$obj=new galileoQcheck();
$nebulaDefault['qcheck']=array("gab500",$obj);

$obj=new galileoConcertoODL();
$nebulaDefault['odl']=array("maestro",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$nebulaParams=$_POST['param'];

//VERIFICA CHIAVE DUPLICATA
$wClause="chiave='".$nebulaParams['rif']."' AND controllo='1'";
$galileo->executeCount('qcheck','QCHECK_storico_controlli',$wClause);
$fetID=$galileo->preFetch('qcheck');
$numeroElementi=-1;
while ( $row=$galileo->getFetch('qcheck',$fetID) ) {
    $numeroElementi=$row['numero_elementi'];
}
if ($numeroElementi>0) {
    $res=array(
        "chiave"=>"",
        "intestazione"=>'Chiave duplicata!!',
        "tecnici"=>array(),
        "rc"=>array()
    );
    die( json_encode($res) );
}
////////////////////////////////////////////////////////////

$res=array(
    "chiave"=>"",
    "intestazione"=>"",
    "tecnici"=>array(),
    "rc"=>array()
);

$tempOp=array();

//executeGeneric($tipo,$funzione,$args,$order)
$flag=$galileo->executeGeneric('odl','getQcheck',array($nebulaParams['rif']),"");
if (!$flag) die (json_encode($galileo->getLog('query')));

$result=$galileo->getResult();

if ($result) {
    $fetID=$galileo->preFetch('odl');
    while ($row=$galileo->getFetch('odl',$fetID)) {
        $res['chiave']=$row['num_rif_movimento'];
        $res['intestazione']=$row['targa'].' - '.$row['des_ragsoc'];
        
        if ( !in_array($row['rc'],$res['rc']) ) {   
            $res['rc'][]=$row['rc'];
        }

        if ( !in_array($row['operaio'],$res['tecnici']) ) {           
            $res['tecnici'][]=$row['operaio'];
        }
    }

    //echo json_encode($galileo->getLog('query'));
    echo json_encode($res);
}




?>