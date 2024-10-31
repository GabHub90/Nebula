<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_odl.php');

require_once($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/odl_func.php");
require_once($_SERVER['DOCUMENT_ROOT']."/nebula/apps/storico/classi/piano.php");

$param=$_POST['param'];

$obj=new galileoODL();
$nebulaDefault['odl']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$odlFunc=new nebulaOdlFunc($galileo);
$piano=new nebulaStoricoPiano($param['marcaDms'],"","",$odlFunc,$galileo);

///////////////////////////////////////////////////////////

$arg=array(
    "codice"=>$param['chksum'],
    "indice"=>"###(SELECT isnull(max(indice),0)+1 FROM OT2_gruppi WHERE codice='".$param['chksum']."')",
    "descrizione"=>$piano->buildDescrizione($param['chksum'],$param['suffix']),
    "oggetti"=>"",
    "marca"=>$param['marcaDms']
);

if (isset($param['copia']) && $param['copia']!="") {

    $temp=explode("_",$param['copia']);

    $arg['oggetti']="###(SELECT oggetti FROM OT2_gruppi WHERE codice='".$temp[0]."' AND indice='".$temp[1]."')";
}
else {

    $temp=array();

    $galileo->executeGeneric('odl','getOggettiDefault',array("marca"=>$param['marcaDms']),'');

    if ($galileo->getResult()) {

        $fid=$galileo->preFetch('odl');

        while ($row=$galileo->getFetch('odl',$fid)) {

            $temp[$row['codice']]=array(
                "dt"=>$row['dt'],
                "mint"=>$row['mint'],
                "maxt"=>$row['maxt'],
                "stet"=>$row['stet'],
                "dkm"=>$row['dkm'],
                "minkm"=>$row['minkm'],
                "maxkm"=>$row['maxkm'],
                "stekm"=>$row['stekm'],
                "pcx"=>$row['pcx'],
                "topt"=>$row['topt'],
                "topkm"=>$row['topkm'],
                "first_t"=>$row['first_t'],
                "first_km"=>$row['first_km']
            );
        }

        $arg['oggetti']=json_encode($temp);
    }
}

if (!$arg['oggetti'] || $arg['oggetti']=='') die ('Errore calcolo oggetti');

$galileo->clearQuery();
$galileo->clearQueryOggetto('default','odl');

$galileo->executeInsert('odl','OT2_gruppi',$arg);

//echo json_encode($galileo->getLog('query'));

?>