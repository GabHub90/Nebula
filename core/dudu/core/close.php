<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_dudu.php');

$obj=new galileoDudu();
$nebulaDefault['dudu']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$param=$_POST['param'];

$line=array(
    "d_chiusura"=>date('Ymd:H:i')
);

$galileo->executeUpdate('dudu','DUDU_lines',$line,"ID='".$param['ID']."' AND riga='".$param['riga']."'");

//echo json_encode($galileo->getLog('query'));

$lines=array();

$galileo->executeGeneric('dudu','loadLines',array('ID'=>$param['ID']),'');
if ($galileo->getResult()) {
    $fid=$galileo->preFetch('dudu');
    while($row=$galileo->getFetch('dudu',$fid)) {
        $lines[$row['riga']]=$row;
    }
}

echo json_encode($lines);

?>