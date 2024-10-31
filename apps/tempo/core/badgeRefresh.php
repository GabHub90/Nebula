<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

require_once(DROOT.'/nebula/core/panorama/intervallo.php');
require_once(DROOT.'/nebula/core/alan/alan.php');

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_tempo.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_alan.php');

//setta le funzioni GALILEO necessarie
$nebulaDefault=array();

$obj=new galileoTempo();
$nebulaDefault['tempo']=array("gab500",$obj);

$obj=new galileoAlan();
$nebulaDefault['alan']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$param=$_POST['param'];

/////////////////////////////////////////////////

$galileo->getReparti($param['macroreparto'],'');
$fetID=$galileo->preFetchBase('reparti');

while($row=$galileo->getFetchBase('reparti',$fetID)) {
    $reparti[$row['reparto']]=$row;
}

$a=array(
    "agenda"=>true,
    "brogliaccio"=>true,
    "intervallo"=>'libero',
    "data_i"=>$param['tag'],
    "data_f"=>$param['tag']
);

$intervallo=new quartetIntervallo($a,$reparti,$galileo);
$intervallo->calcola();

$alan=new nebulaAlan($param['macroreparto'],'tpo',null,$galileo);

$collaboratori=$intervallo->getCollaboratori();
$totCollDayTurni[$param['tag']]=$intervallo->getTurnoCollDay($param['reparto'],$param['IDcoll'],$param['tag']);

foreach ($collaboratori as $reparto=>$r) {
    foreach ($r as $collID=>$cl) {

        if ($collID!=$param['IDcoll']) continue;

        foreach ($cl as $c) {

            $alan->setCollaboratore($c,$totCollDayTurni);

            $alan->leggi($param['tag'],$param['tag']);

            $alan->build();

            break;
        }
    }
}

//$index=mainFunc::gab_tots($param['tag']);

$alan->drawContainer($param['tag']);

//echo json_encode($collaboratori);

?>