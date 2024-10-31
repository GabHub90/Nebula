<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_dudu.php');

$obj=new galileoDudu();
$nebulaDefault['dudu']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$param=$_POST['param'];

$line=array(
    "ID"=>$param['rif'],
    "testo"=>$param['txt']
);

$galileo->executeGeneric('dudu','insertLine',$line,'');

//echo json_encode($galileo->getLog('query'));

$lines=array();

$galileo->executeGeneric('dudu','loadLines',array('ID'=>$param['rif']),'');
if ($galileo->getResult()) {
    $fid=$galileo->preFetch('dudu');
    while($row=$galileo->getFetch('dudu',$fid)) {
        $lines[$row['riga']]=$row;
    }
}

echo json_encode($lines);

?>