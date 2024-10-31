<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require_once(DROOT.'/nebula/apps/gdm/classi/materiale.php');
include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/gab500/galileo_gdm.php");

$param=$_POST['param'];

$obj=new galileoGDM();
$nebulaDefault['gdm']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

///////////////////////////////////////////////////////////

//PNEUMATICI

$info=false;

$galileo->executeSelect('gdm','GDM_materiali',"id='".$param['id']."'",'');
if ($galileo->getResult()) {

    $fid=$galileo->prefetch('gdm');

    while($row=$galileo->getFetch('gdm',$fid)) {
        $info=$row;
    }

}

if (!$info) die ('Materiale non trovato!!');

ob_start();

    echo '<div style="position:relative;height:15%;" >';

        echo '<div style="position:relative;width:100%;text-align:center;font-weight:bold;font-size:1.2em;" >';

            echo '<img style="width:25px;height:25px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/'.strtolower($info['proprietario']).'.png" />';
            echo '<span style="margin-left:10px;"> Modifica materiale: '.$info['id'].' - '.$info['proprietario'].'</span>';

            if ($info['isAnnullabile']=='True') {
                echo '<img style="position:absolute;top:0px;left:35px;width:25px;height:25px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/trash.png" onclick="window._nebulaGdm.annullaMateriale(\''.$info['id'].'\');" />';
            }

        echo '</div>';

        echo '<div style="text-align:center;margin-top:10px;">';
            echo '<button style="position:relative;margin-left:30px;" onclick="window._nebulaGdm.confermaEdit();">Conferma Modifica</button>';
            if ($info['proprietario']=='Deposito') {
                echo '<span style="margin-left:10px;"> - Locazione: </span>';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:150px;height:16px;border:1px solid black;padding:2px;text-align:center;font-weight:bold;font-size:1.1em;margin-left:15px;cursor:pointer;" onclick="window._nebulaGdm.cambiaLocazione(\''.$info['id'].'\');" >'.$info['locazione'].'</div>';
            }
        echo '</div>';

    echo '</div>';

    echo '<script type="text/javascript" >';
        echo 'window._ckmf=new chekkoMultiForm(\'0\');';
    echo '</script>';

    echo '<div id="gdm_gestione_creazione_body" style="position:relative;height:85%;overflow:scroll;overflow-x:hidden;" >';
        //echo $materiali[$default][0];
        $mat=new gdmMateriale($info,$galileo);
        $mat->drawForm(false,0);
    echo '</div>';

echo base64_encode(ob_get_clean());
?>