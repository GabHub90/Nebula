<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include(DROOT.'/nebula/apps/carb/classi/buono.php');

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_carb.php');

$param=$_POST['param'];

$obj=new galileoCarb();
$nebulaDefault['carb']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$causali=array();
$collaboratori=array();


$init=array(
    'trash'=>false
);

$galileo->executeSelect('carb','CARB_buoni',"ID='".$param['ID']."'","");
if ($galileo->getResult()) {
    $fid=$galileo->preFetch('carb');
    while ($row=$galileo->getFetch('carb',$fid)) {   
        $init['trash']=$row;
    }
}

if (!$init['trash']) die ('<div style="margin-left:10px;">Nessun buono trovato</div>');

if ($init['trash']['mov_open']==1 || ($init['trash']['stato']!='stampato' && $init['trash']['stato']!='completato' && $init['trash']['stato']!='risarcito') ) {
    //echo json_encode($init['trash']);
    die ('<div style="margin-left:10px;">Buono non valido</div>');
}

$init['trash']['id_esec']=$param['id_annullo'];

$galileo->clearQueryOggetto('default','carb');

/////////////////////////////////////////////////
$galileo->executeSelect('carb','CARB_causali',"","");
if ($galileo->getResult()) {
    $fid=$galileo->preFetch('carb');
    while ($row=$galileo->getFetch('carb',$fid)) {   
        $causali[$row['codice']]=$row;
    }
}

$galileo->getMaestroCollab('');
if ($galileo->getResult()) {
    $fid=$galileo->preFetchBase('maestro');
    while ($row=$galileo->getFetchBase('maestro',$fid)) {   
        $collaboratori[$row['ID']]=$row;
        $collaboratori[$row['ID']]['ID_coll']=$row['ID'];
    }
}

$galileo->clearQueryOggetto('base','maestro');

$buono=new carbBuono($galileo);
$buono->init($init['trash']);
$buono->setAmbito('trash');
$buono->loadCausali($causali);
$buono->loadCollab($collaboratori);

$buono->draw();

//echo json_encode($galileo->getLog('query'));

?>