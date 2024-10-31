<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require_once(DROOT.'/nebula/apps/gdm/classi/materiale.php');
include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/gab500/galileo_gdm.php");

$param=$_POST['param'];

$obj=new galileoGDM();
$nebulaDefault['gdm']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

///////////////////////////////////////////////////////////

$info=array(
    "materiale"=>false,
    "veicolo"=>$param['veicolo']
);

$galileo->executeSelect('gdm','GDM_materiali',"id='".$param['id']."'",'');
if ($galileo->getResult()) {
    $fid=$galileo->preFetch('gdm');
    while ($row=$galileo->getFetch('gdm',$fid)) {
        $info['materiale']=$row;
    }
}

if (!$info) die ('Materiale non trovato.');

$info['materiale']['locazione']=$param['locazione'];

$galileo->setTransaction(true);

$galileo->clearQuery();
$galileo->clearQueryOggetto('default','gdm');

$galileo->executeGeneric('gdm','cambiaLocazione',$info,'');

echo json_encode($galileo->getLog('query'));
?>