<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require_once(DROOT.'/nebula/apps/gdm/classi/materiale.php');
include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/gab500/galileo_gdm.php");

$param=$_POST['param'];

$materiali=array(
    "Pneumatici"=>array('8.0')
);

$default="";

$obj=new galileoGDM();
$nebulaDefault['gdm']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

///////////////////////////////////////////////////////////

//$mat=new gdmMateriale($param,$galileo);

ob_start();

    echo '<div style="position:relative;height:15%;" >';

        echo '<div style="position:relative;width:100%;text-align:center;font-weight:bold;font-size:1.2em;" >';

            echo '<img style="width:25px;height:25px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/'.strtolower($param['proprietario']).'.png" />';
            echo '<span style="margin-left:10px;"> Creazione materiale: '.$param['proprietario'].'</span>';

            echo '<img style="position:absolute;top:0px;left:35px;width:70px;height:25px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/fake.png" onclick="window._nebulaGdm.confermaFake();" />';

        echo '</div>';

        echo '<div style="width:100%;text-align:center;margin-top:5px;" >';
            echo '<span>Tipo materiale:</span>';
            echo '<select id="gdm_nuovo_form_tipologia" style="margin-left:10px;">';
                foreach ($materiali as $k=>$m) {
                    echo '<option value="'.$k.'">'.$k.'</option>';
                    if ($default=='')$default=$k;
                }
            echo '</select>';

            echo '<button style="position:relative;margin-left:30px;" onclick="window._nebulaGdm.confermaCrea();">Conferma</button>';

        echo '</div>';

    echo '</div>';

    echo '<script type="text/javascript" >';
        echo 'window._ckmf=new chekkoMultiForm(\'0\');';
    echo '</script>';

    echo '<div id="gdm_gestione_creazione_body" style="position:relative;height:85%;overflow:scroll;overflow-x:hidden;" >';
        //echo $materiali[$default][0];
        $param['tipologia']=$default;
        $mat=new gdmMateriale($param,$galileo);
        $mat->drawForm($materiali[$default][0],0);
    echo '</div>';

echo base64_encode(ob_get_clean());
?>