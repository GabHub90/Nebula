<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require_once(DROOT.'/nebula/core/odl/odl_func.php');
require($_SERVER['DOCUMENT_ROOT']."/nebula/apps/storico/classi/piano.php");

$param=$_POST['param'];

$eventiBase=array();
$eventiDefault=array();

$odlFunc=new nebulaOdlFunc($galileo);

////////////////////////////////////////////////////

$map=$odlFunc->getOTDefault($param['marca']);

if (!$map['result']) die('Erroe lettura eventi default');

$fid=$galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

while ($row=$galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {

    $eventiDefault[$row['codice']]=$row;
    
}

$map=$odlFunc->getOTBase();

$fid=$galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

while ($row=$galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {

    $eventiBase[$row['codice']]=$row;
    
}

////////////////////////////////////////////////////

$old=$odlFunc->getOTevento($param['oggetto'],$param['codice'],$param['tipo']);

////////////////////////////////////////////////////

$tipi=array(
    'M'=>"Manodopera",
    'R'=>"Ricambi",
    'V'=>'Varie'
);

echo '<div style="font-weight:bold;font-size:1.2em;">Abbinamento riga - evento service</div>';

echo '<div style="position:relative;margin-top:20px;" >';

    echo '<div>';
        echo '<div style="position:relative;display:inline-block;width:10%;" >Marca:</div>';
        echo '<div style="position:relative;display:inline-block;width:30%;" >'.$param['marca'].'</div>';
    echo '</div>';

    echo '<div>';
        echo '<div style="position:relative;display:inline-block;width:10%;" >Tipo:</div>';
        echo '<div style="position:relative;display:inline-block;width:30%;" >'.$tipi[$param['tipo']].'</div>';
    echo '</div>';

    echo '<div>';
        echo '<div style="position:relative;display:inline-block;width:10%;" >Codice:</div>';
        echo '<div style="position:relative;display:inline-block;width:25%;" ><b>'.$param['codice'].'</b></div>';
        if ($old) {
            echo '<img style="width:25px;height:25px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/storico/img/trash.png" onclick="window._nebulaStorico.deleteEvento();" />';
        }
    echo '</div>';

echo '</div>';

echo '<div style="position:relative;margin-top:20px;" >';

    echo '<input id="storicoEditEvento_codice" type="hidden" value="'.$param['codice'].'" />';
    echo '<input id="storicoEditEvento_tipo" type="hidden" value="'.$param['tipo'].'" />';

    echo '<div style="position:relative;display:inline-block;width:50%;vertical-align:top;" >';
        echo '<div style="font-size:0.9em;font-weight:bold;" >Evento:</div>';
        echo '<div>';
            echo '<select id="storicoEditEvento_evento" style="width:90%;font-size:1.2em;" ';
                if ($old) echo 'disabled';
            echo ' >';
                foreach($eventiBase as $codice=>$b) {
                    if (array_key_exists($codice,$eventiDefault)) {
                        echo '<option value="'.$codice.'" ';
                            if ($codice==$param['oggetto']) echo 'selected';
                        echo ' >'.$codice.' - '.$b['descrizione'].'</option>';
                    }
                }
            echo '</select>';
        echo '</div>';
    echo '</div>';

    echo '<div style="position:relative;display:inline-block;width:15%;vertical-align:top;" >';
        echo '<div style="position:relative;font-size:0.9em;font-weight:bold;text-align:center;width:100%;" >Qtà minima</div>';
        echo '<input id="storicoEditEvento_qta" type="text" style="width:100%;text-align:center;font-size:1.2em;" value="'.($old?number_format($old['min_qta'],2,'.',''):'1.00').'" />';
    echo '</div>';

    echo '<div style="position:relative;display:inline-block;width:20%;vertical-align:top;" >';
    echo '<div style="font-size:0.9em;font-weight:bold;width:100%;text-align:center;" >Da confermare</div>';
        echo '<div style="width:100%;text-align:center;font-size:1.2em;" >';
            echo '<input id="storicoEditEvento_chk" type="checkbox" style="margin-top:5px;" ';
                if ($old) {
                    if ($old['chk']==1) echo 'checked';
                }
            echo ' />';
        echo '</div>';
    echo '</div>';

echo '</div>';

echo '<div style="position:relative;margin-top:20px;width:100%;text-align:right;" >';
    if ($old) echo '<button onclick="window._nebulaStorico.insertEvento(true);" >Modifica</button>';
    else echo '<button onclick="window._nebulaStorico.insertEvento(false);">Crea</button>';
echo '</div>';

?>