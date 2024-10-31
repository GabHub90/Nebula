<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require_once(DROOT.'/nebula/core/panorama/intervallo.php');

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_tempo.php');

$obj=new galileoTempo();
$nebulaDefault['tempo']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$param=$_POST['param'];

//TEST

$reparti=array();

$galileo->getReparti('S','');
$fetID=$galileo->preFetchBase('reparti');

while($row=$galileo->getFetchBase('reparti',$fetID)) {
    $reparti[$row['reparto']]=$row;
}

$a=array(
    "contesto"=>"libero",
    "presenza"=>"totali",
    "agenda"=>true,
    "schemi"=>true,
    "data_i"=>'20220110',
    "data_f"=>'20220120',
    "actualReparto"=>'VWS'
);

//array('VWS'=>"VWS")
$intervallo=new quartetIntervallo($a,$reparti,$galileo);

//echo json_encode($intervallo->getIntRange());

$intervallo->calcola();
$intervallo->calcolaIntTot();

//echo json_encode($intervallo->getPresenzaCollAll());

$s=array('20220110','20220111','20220112','20220113','20220114','20220115','20220116','20220117','20220118','20220119','20220120');

$tempsez=array(
    "totale"=>"NO",
    "totale_bk"=>"NO",
    "valore"=>"NO"
);

$tempcss=array(
    "font_size"=>"7pt",
    "legenda_w"=>"12%",
	"corpo_w"=>"98%",
	"totale_w"=>"0%",
    "line_h"=>"10px"
);

$ret=array(
    "lav"=>"",
    "rit"=>"",
    "ric"=>""
);

ob_start();

foreach ($s as $t) {

    if (!$dts=$intervallo->getDayTotSub($t)) continue;

    $tempry=array(
        "titolo"=>htmlentities(mainFunc::gab_weektotag(date('w',mainFunc::gab_tots($t))).' - '.mainFunc::gab_todata($t)),
        "range"=>$intervallo->getGlobalTrim()
    );

    echo '<div style="margin-top:10px;margin-bottom:5px;width:95%;">';
        $dts->setSezioni($tempsez);
        $dts->drawSetup($tempcss);
        echo $dts->drawProprietario($tempry);
    echo '</div>';
}

$ret['lav']=base64_encode(ob_get_clean());

//RICEZIONE e RICONSEGNA

$tempcss['line_h']='8px';

if ($dts=$intervallo->getDayTotRic('20220110')) {

    $tempry=array(
        "titolo"=>htmlentities('Ricezione: '.mainFunc::gab_weektotag(date('w',mainFunc::gab_tots('20220110'))).' - '.mainFunc::gab_todata('20220110')),
        "range"=>$intervallo->getGlobalTrim()
    );

    ob_start();

    echo '<div style="margin-top:10px;margin-bottom:5px;width:95%;">';
        $dts->setSezioni($tempsez);
        $dts->drawSetup($tempcss);
        echo $dts->drawProprietario($tempry);
    echo '</div>';

    $ret['rit']=base64_encode(ob_get_clean());

}

///////////////////
$tempcss['titolo_align']='right';

if ($dts=$intervallo->getDayTotRic('20220111')) {

    $tempry=array(
        "titolo"=>htmlentities('Riconsegna: '.mainFunc::gab_weektotag(date('w',mainFunc::gab_tots('20220111'))).' - '.mainFunc::gab_todata('20220111')),
        "range"=>$intervallo->getGlobalTrim()
    );

    ob_start();

    echo '<div style="margin-top:10px;margin-bottom:5px;width:95%;">';
        $dts->setSezioni($tempsez);
        $dts->drawSetup($tempcss);
        echo $dts->drawProprietario($tempry);
    echo '</div>';

    $ret['ric']=base64_encode(ob_get_clean());

}

//END TEST

echo json_encode($ret);

?>