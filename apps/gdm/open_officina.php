<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require_once(DROOT.'/nebula/apps/gdm/classi/richiesta.php');
require_once(DROOT.'/nebula/apps/gdm/classi/materiale.php');
require_once(DROOT.'/nebula/apps/gdm/classi/gestione.php');
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/app_base_class.php");
require_once(DROOT.'/nebula/apps/gdm/gdm_class.php');
include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/gab500/galileo_gdm.php");

$param=$_POST['param'];

$obj=new galileoGDM();
$nebulaDefault['gdm']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

///////////////////////////////////////////////////////////

$actualRichiesta=false;

$galileo->executeSelect('gdm','GDM_richieste',"statoRi='Aperta' AND idTelaio='".$param['telaio']."'",'');
$result=$galileo->getResult();

if ($result) {
    $fid=$galileo->preFetch('gdm');

    while($row=$galileo->getFetch('gdm',$fid)) {
        $actualRichiesta=new gdmRichiesta($param['gdm_ambito'],$row,$galileo);

        $row['nomeCliente'] = mb_convert_encoding($row['nomeCliente'], 'UTF-8', 'UTF-8');

        $veicolo=array(
            "telaio"=>$row['idTelaio'],
            "nomeCliente"=>$row['nomeCliente'],
            "targa"=>$row['targa'],
            "des_veicolo"=>$row['tipoVeicolo'],
            "dms"=>$row['dms']
        );
    }
}

if (!$actualRichiesta) {
    //die('Richiesta non caricata');

    echo '<div style="position:relative;width:100%;height:89%;overflow:scroll;overflow-x:hidden;">';

        gdmApp::initJS();

        echo '<div>Storico GDM:</div>';

        $gestione=new gdmGestione($param['telaio'],$galileo);
        $gestione->drawStorico();

    echo '</div>';
}

else {

    echo '<div id="gdm_actual_richiesta_'.$param['gdm_ambito'].'" style="position:relative;display:inline-block;width:96%;height:99%;margin-left:3%;vertical-align:top;" >';

        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/chekko/multiform.js" ></script>';
        $actualRichiesta->drawForm();

    echo '</div>';

    gdmApp::initJS();

    echo '<script type="text/javascript" >';

        echo 'var temp='.json_encode($veicolo).';';
        echo 'window._nebulaGdm.loadVei(temp);';

        echo 'window._nebulaGdm.app="workshop";';
        
    echo '</script>';
}


?>