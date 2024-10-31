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
"qc1":"1","qc1n":"","qc2":"2","qc2n":"","qc3":"1","qc3n":"","qc4":"1","qc4n":"","qc5":"1","qc5n":"","qc6":"1","qc6n":"","qc7":"1","qc7n":"","qc8":"1","qc8n":"","qc9":"1","qc9n":"","qc10":"1","qc10n":"","qc11":"1","qc11n":"","qc12":"1","qc12n":"","qc13":"2","qc13n":"",
"stato":"salvato"
}
*/

$args=array(
    "risposte"=>"",
    "punteggio"=>"",
    "stato"=>"",
    "esecutore"=>"",
    "d_modulo"=>date('Ymd:H:i')
);

$temp=array();
$wtemp=array();

foreach ($nebulaParams as $k=>$v) {

    if ($k=='stato') {
        $args['stato']=$v;
        continue;
    }

    if ($k=='punteggio') {
        $args['punteggio']=json_encode($v,JSON_UNESCAPED_SLASHES);
        continue;
    }

    if ($k=='esecutore') {
        $args['esecutore']=$v;
        continue;
    }

    if ($k=='IDcontrollo' || $k=='modulo') {
        $wtemp[$k]=$v;
        continue;
    }

    $temp[$k]=$v;
}

$args["risposte"]=json_encode($temp,JSON_UNESCAPED_SLASHES);

//clausola WHERE
$wclause="ID_controllo='".$wtemp['IDcontrollo']."' AND modulo='".$wtemp['modulo']."'";

$galileo->setTransaction(true);
//executeUpdate($tipo,$tabella,$arr,$wClause)
$galileo->executeUpdate("qcheck","QCHECK_storico_moduli",$args,$wclause);

echo json_encode($galileo->getLog('query'));

//CHECK se tutti i moduli sono "CHIUSO"
//allora metti CHIIUSO anche il CONTROLLO

$wclause="ID_controllo='".$wtemp['IDcontrollo']."' AND stato IN ('aperto','salvato')";
$galileo->executeCount("qcheck","QCHECK_storico_moduli",$wclause);
$fetID=$galileo->preFetch("qcheck");

$numeroElementi=-1;
while ($row=$galileo->getFetch("qcheck",$fetID)) {
    $numeroElementi=$row['numero_elementi'];
}

if ($numeroElementi==0) {
    $args=array("stato"=>"chiuso");
    $wclause="ID='".$wtemp['IDcontrollo']."'";
    $galileo->executeUpdate("qcheck","QCHECK_storico_controlli",$args,$wclause);
}






?>