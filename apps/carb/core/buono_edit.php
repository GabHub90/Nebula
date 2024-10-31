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


$init=array();

if ($param['ambito']=='new') {
    $init['new']=array (
        'ID'=>0,
        'dms'=>$param['dms'],
        'reparto'=>$param['reparto'],
        'id_esec'=>""   
    );
}

else {
    $galileo->executeSelect('carb','CARB_buoni',"ID='".$param['ID']."'","");
    if ($galileo->getResult()) {
        $fid=$galileo->preFetch('carb');
        while ($row=$galileo->getFetch('carb',$fid)) {   
            $init[$param['ambito']]=$row;
        }
    }

    if ($init[$param['ambito']]['mov_open']==0 || ($param['ambito']=='fill' && $init[$param['ambito']]['stato']!='dacompletare') || ($param['ambito']=='cash' && $init[$param['ambito']]['stato']!='daris') || ($param['ambito']=='print' && $init[$param['ambito']]['stato']!='creato')) die ('<div style="margin-left:10px;">Buono non più attivo!!!</div>');
}

$init[$param['ambito']]['id_esec']=$param['id_esec'];

if ($param['ambito']=='fill') $init[$param['ambito']]['pieno']=0;

if (!isset($param['reparto'])) $param['reparto']=$init[$param['ambito']]['reparto'];

$galileo->clearQueryOggetto('default','carb');

/////////////////////////////////////////////////
$galileo->executeSelect('carb','CARB_causali',"","");
if ($galileo->getResult()) {
    $fid=$galileo->preFetch('carb');
    while ($row=$galileo->getFetch('carb',$fid)) {   
        $causali[$row['codice']]=$row;
    }
}

if ($param['ambito']=='new') {
    $galileo->getCollaboratori('reparto',$param['reparto'],date('Ymd'));
    if ($galileo->getResult()) {
        $fid=$galileo->preFetchBase('maestro');
        while ($row=$galileo->getFetchBase('maestro',$fid)) {   
            $collaboratori[$row['ID_coll']]=$row;
        }
    }
}
else {
    $galileo->getMaestroCollab('');
    if ($galileo->getResult()) {
        $fid=$galileo->preFetchBase('maestro');
        while ($row=$galileo->getFetchBase('maestro',$fid)) {   
            $collaboratori[$row['ID']]=$row;
            $collaboratori[$row['ID']]['ID_coll']=$row['ID'];
        }
    }
}
$galileo->clearQueryOggetto('base','maestro');

$buono=new carbBuono($galileo);
$buono->init($init[$param['ambito']]);
$buono->setAmbito($param['ambito']);
$buono->loadCausali($causali);
$buono->loadCollab($collaboratori);

$buono->draw();

//echo json_encode($galileo->getLog('query'));

?>