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
$actualRichiesta=false;

$colori=['#dedcc4','#adc5ba'];

$galileo->clearQuery();
$galileo->clearQueryOggetto('default','gdm');

$galileo->executeSelect('gdm','GDM_richieste',"statoRi='Aperta'",'dataRi DESC');
$result=$galileo->getResult();

$col=1;

$count=0;

echo '<div style="position:relative;display:inline-block;width:47%;height:100%;overflow:scroll;overflow-x:hidden;vertical-align:top;" >';

    if ($result) {
        $fid=$galileo->preFetch('gdm');

        while($row=$galileo->getFetch('gdm',$fid)) {

            /*if ($count==0) {
                $count++;
                continue;
            }

            if ($count==10) break;*/

            try {

                //non scrivere la richiesta relativa al telaio attivo (perché viene scritta espansa a lato)
                if ($param['telaio'] && $row['idTelaio']==$param['telaio']) {
                    $actualRichiesta=new gdmRichiesta($param['gdm_ambito'],$row,$galileo);
                    continue;
                }

                $richiesta=new gdmRichiesta($param['gdm_ambito'],$row,$galileo);

            } catch (Exception $e) {
                // Gestisci l'eccezione catturata
                die('Si è verificato un errore: ' . $e->getMessage());
            }

            //se la richiesta ha operazioni "Pronto per Stoccaggio" salta
            //if ($richiesta->getPps()) continue;

            $col=($col==1)?0:1;

            //echo '<div style="position:relative;width:85%;border:1px solid black;margin-top:5px;background-color:'.$colori[$col].';cursor:pointer;" onclick="window._nebulaGdm.refreshRichiesta(\''.$row['id'].'\',\''.$param['gdm_ambito'].'\');">'; 
            //echo '<div style="position:relative;width:85%;border:1px solid black;margin-top:5px;background-color:'.$colori[$col].';cursor:pointer;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setVeicoloRichiesta(\''.$row['idTelaio'].'\',\''.$row['dms'].'\');">'; 
            echo '<div style="position:relative;width:75%;border:1px solid black;margin-top:5px;background-color:'.$colori[$col].';cursor:pointer;" data-lista="'.$count.'" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setVeicoloRichiesta(\''.$row['idTelaio'].'\',\'infinity\');">';
                $richiesta->draw(false);
            echo '</div>';

            $count++;
        } 
    }

echo '</div>'; 

echo '<div id="gdm_actual_richiesta_'.$param['gdm_ambito'].'" style="position:relative;display:inline-block;width:50%;height:99%;margin-left:3%;vertical-align:top;" >';

    if ($actualRichiesta) {
        //if (!$actualRichiesta->getPps()) {
            echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/chekko/multiform.js" ></script>';

            //imageSelect::imageSelectInit();
            $actualRichiesta->drawForm();
        //}
    }

echo '</div>';

?>