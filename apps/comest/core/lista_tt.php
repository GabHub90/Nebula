<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include(DROOT.'/nebula/core/veicolo/classi/wormhole.php');

$param=$_POST['param'];

$wh=new veicoloWH($param['reparto'],$galileo);

$map=$wh->getTT($param['dms'],$param['str']);

echo '<div style="position:relative;width:100%;height:100%;overflow:scroll;overflow-x:hidden;" >';

    if ($map['result']) {

        $fid=$galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

        while ($row=$galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {

            echo '<div style="position:relative;width:90%;margin-top:8px;margin-bottom:8px;" >';

                echo '<div style="position:relative;display:inline-block;width:15%;" >';
                    echo $row['targa'];
                echo '</div>';

                echo '<div id="carb_veicoloDiv_telaio" style="position:relative;display:inline-block;width:35%;" >';
                    echo $row['telaio'];
                echo '</div>';

                echo '<div id="carb_veicoloDiv_des_veicolo" style="position:relative;display:inline-block;width:45%;font-size:0.9em;" >';
                    echo substr($row['des_veicolo'],0,30);
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:5%;" >';
                    echo '<img style="width:20px;height:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/carb/img/get.png" onclick="window._nebulaCarb.getTT(\''.$row['rif'].'\',\''.$row['targa'].'\',\''.$row['telaio'].'\',\''.base64_encode($row['des_veicolo']).'\');" />';
                echo '</div>';

            echo '</div>';
        }
    }
    else echo 'Nessuna corrispondenza trovata.';

echo '</div>';

?>