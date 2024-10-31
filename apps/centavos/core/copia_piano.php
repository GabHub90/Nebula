<?php

include('default.php');

$piano=array();

$galileo->executeSelect('centavos','CENTAVOS_piani',"ID='".$nebulaParams['piano']."'",'');

if ($galileo->getResult()) {

    $fid=$galileo->preFetch('centavos');

    while ($row=$galileo->getFetch('centavos',$fid)) {
        $piano=$row;
    }

}
else die('Piano non trovato.');

echo '<div id="ctv_copia_monitor" style="height:95%;overflow:scroll;">';

    echo '<div style="font-size:1.2em;font-weight:bold;" >Copia da: ('.$piano['reparto'].') '.$piano['descrizione'].'</div>';

    echo '<div style="margin-top:20px;" >';
        echo '<span>Descrizione:</span>';
        echo '<input id="ctv_desc_input" type="text" style="width:300px;margin-left:10px;" />';
        echo '<button style="margin-left:20px;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].ctvCopiaPianoExec(\''.$nebulaParams['piano'].'\');">Copia</button>';
    echo '</div>';

echo '</div>';

?>