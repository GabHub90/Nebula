<?php

include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/gab500/galileo_gdm.php");

$param=$_POST['param'];

$obj=new galileoGDM();
$nebulaDefault['gdm']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

///////////////////////////////////////////////////////////

$operazioni=array();

$galileo->executeSelect('gdm','GDM_operazioni',"idRi='".$param['idRi']."'",'');

$result=$galileo->getResult();

if ($result) {

    $fid=$galileo->preFetch('gdm');
    while ($row=$galileo->getFetch('gdm',$fid)) {
        $operazioni[]=$row;
    }
}
else die('operazioni non recuperate');

if (count($operazioni)==0) die('operazioni non recuperate');

$galileo->setTransaction(true);
$galileo->executeGeneric('gdm','delRichiesta',$operazioni,'');

echo json_encode($galileo->getLog('query'));

?>