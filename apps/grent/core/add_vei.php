<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/infinity/infinity_veicoli.php");

$param=$_POST['param'];

$obj=new galileoInfinityVeicoli();
$nebulaDefault['veicoli']=array("rocket",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

echo '<div style="position:relative;text-align:left;font-weight:bold;font-size:1.2em;height:12%;">';

    echo '<div style="margin-top:10px;">';
        echo '<img style=position:absolute;right:15px;top:0px;widht:20px;height:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/ensamble/img/annulla.png" onclick="window._nebulaApp.ribbonExecute();" />';
        echo 'Inserimento veicolo lista: '.$param['tipoRent'];
        echo '<input id="grent_form_rent" type="hidden" value="'.$param['tipoRent'].'" />';
    echo '</div>';

    echo '<div style="position:relative;margin-top:15px;" >';
        echo '<span style="font-size:0.9em;">Targa/Telaio:</span>';
        echo '<input id="grent_tt" style="postion:relarive;margin-left:10px;width:250px;text-align:center;" type="text" onkeydown="if(event.keyCode==13) window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].getTT();"/>';
        echo '<button style="margin-left:10px;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].getTT();">Cerca</button>';
    echo '</div>';

echo '</div>';

echo '<div id="grent_addvei_main" style="position:relative;text-align:left;margin-top:10px;font-size:1em;height:88%;">';
echo '</div>';



?>