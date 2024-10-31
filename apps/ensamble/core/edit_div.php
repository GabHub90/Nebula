<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

$param=$_POST['param'];

$collaboratore=false;
$galileo->getMaestroCollab("id='".$param['coll']."'");
if ($galileo->getResult()) {
    $fid=$galileo->preFetchBase('maestro');
    while($row=$galileo->getFetchBase('maestro',$fid)) {
        $collaboratore=$row;
    }
}

if (!$collaboratore) die('Collaboratore non trovato!!!');

$gruppi=array();

$galileo->getCollaboratoriIntervallo("'".$param['reparto']."'",'20120501','21001231');
if ($galileo->getResult()) {
    $fid=$galileo->preFetchBase('maestro');
    while($row=$galileo->getFetchBase('maestro',$fid)) {
        if ($row['ID_coll']!=$param['coll']) continue;
        $gruppi[]=$row;
    }
}

/////////////////////////////////////////////////////////

echo '<div style="position:relative;text-align:left;margin-top:10px;font-weight:bold;font-size:1.2em;">';
    echo '<img style=position:absolute;right:15px;top:0px;widht:20px;height:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/ensamble/img/annulla.png" onclick="window._nebulaApp.ribbonExecute();" />';
    echo 'Modifica Collaboratore: '.$param['coll'];
echo '</div>';

echo '<div style="position:relative;margin-top:10px;font-size:1.2em;">';
    //echo json_encode($collaboratore);
    echo $collaboratore['cognome'].' '.$collaboratore['nome'];
echo '</div>';

echo '<div style="position:relative;margin-top:10px;">';
    //echo json_encode($gruppi);
    echo '<table style="font-size:1em;text-align:center;" >';
        echo '<tr>';
            echo '<th style="width:200px;">Reparto</th>';
            echo '<th style="width:250px;">Gruppo</th>';
            echo '<th style="width:120px;">Inizio</th>';
            echo '<th style="width:120px;">Fine</th>';
            echo '<th style="width:50px;"></th>';
        echo '</tr>';

        foreach ($gruppi as $k=>$g) {
            echo '<tr>';
                echo '<td style="text-align:center;height:30px;">'.$g['macroreparto'].' - '.$g['des_reparto'].'</td>';
                echo '<td style="text-align:center;">'.$g['macrogruppo'].' - '.$g['des_macrogruppo'].'</td>';
                echo '<td>'.mainFunc::gab_todata($g['data_i']).'</td>';
                echo '<td>'.($g['data_f']!='21001231'?mainFunc::gab_todata($g['data_f']):'').'</td>';
                if ($g['data_f']=='21001231' && $param['d']>$g['data_i']) {
                    echo '<td>';
                        echo '<button onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].confirmEdit(\''.$param['coll'].'\',\''.$g['ID_gruppo'].'\',\''.$param['panorama'].'\');">chiudi</button>';
                    echo '</td>';
                }
                else {
                    echo '<td></td>';
                }
            echo '</tr>';
        }

    echo '</table>';
echo '</div>';

?>