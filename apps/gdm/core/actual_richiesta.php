<?php

include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require_once(DROOT.'/nebula/apps/gdm/classi/richiesta.php');
require_once(DROOT.'/nebula/apps/gdm/classi/materiale.php');
include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/gab500/galileo_gdm.php");

$param=$_POST['param'];

$obj=new galileoGDM();
$nebulaDefault['gdm']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

///////////////////////////////////////////////////////////

$richiesta=false;

$galileo->clearQuery();
$galileo->clearQueryOggetto('default','gdm');

$galileo->executeSelect('gdm','GDM_richieste',"id='".$param['idRi']."'",'');
$result=$galileo->getResult();

if ($result) {
    $fid=$galileo->preFetch('gdm');

    while ($row=$galileo->getFetch('gdm',$fid)) {

        //imageSelect::imageSelectInit();

        $richiesta=new gdmRichiesta($param['ambito'],$row,$galileo);
        $richiesta->drawForm();
    }
   
}
else die('<div style="vertical-align:top;" >Richiesta non caricata</div>');

?>