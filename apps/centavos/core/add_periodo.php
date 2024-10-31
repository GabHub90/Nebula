<?php

include('default.php');

$a=array();
$cadenza=false;
$inizio=false;
$fine=false;

$galileo->executeGeneric('centavos','getPianoPeriodo',$nebulaParams,"");
$result=$galileo->getResult();
if ($result) {
    $fetID=$galileo->preFetch('centavos');
    while ($row=$galileo->getFetch('centavos',$fetID)) {

        if (count($a)==0) {

            switch($row['cadenza']) {
                case 'mese': $cadenza=0;break;
                case 'bimestre': $cadenza=1;break;
                case 'trimestre': $cadenza=2;break;
                case 'semestre': $cadenza=5;break;
                case 'anno': $cadenza=11;break;
            }
            
            if (!$cadenza) die('Cadenza non calcolabile');

            //d_fine è la data di fine del periodo
            //se non c'è significa che non c'è nessun periodo e quindi si inizia dalla data di inizio del piano
            if ($row['d_fine']=='') $inizio=$row['data_i'];
            else {
                $inizio=date('Ymd',strtotime('+1 month',mainFunc::gab_tots(substr($row['d_fine'],0,6).'01')));
            }

            $fine=date('Ymt',strtotime('+'.$cadenza.' month',mainFunc::gab_tots($inizio)));

            if (!$inizio || !$fine) die ('Intervallo periodo non calcolabile.');
        }

        $a[]=$row;
    }
}

if (count($a)==0) die('Piano non trovato');

echo '<div style="height:8%;font-weight:bold;font-size:15pt;" >Creazione nuovo periodo:</div>';

echo '<div style="width:100%;height:92%;overflow:scroll;overflow-x:hidden;" >';

    echo '<div style="position:relative;width:80%;" >';
        echo '<div style="position:relative;display:inline-block;width:20%;vertical-align:top;" >'.mainFunc::gab_todata($inizio).'</div>';
        echo '<div style="position:relative;display:inline-block;width:20%;vertical-align:top;" >'.mainFunc::gab_todata($fine).'</div>';
        echo '<div style="position:relative;display:inline-block;width:20%;vertical-align:top;" >';
            echo '<button data-inizio="'.$inizio.'" data-fine="'.$fine.'" data-piano="'.$nebulaParams['piano'].'" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].execAddPeriodo(this);" >Crea nuovo</button>';
        echo '</div>';
    echo '</div>';

    foreach ($a as $k=>$p) {

        echo '<div style="position:relative;width:80%;margin-top:20px;" >';
            echo '<div style="position:relative;display:inline-block;width:20%;vertical-align:top;" >'.mainFunc::gab_todata($p['d_inizio']).'</div>';
            echo '<div style="position:relative;display:inline-block;width:20%;vertical-align:top;" >'.mainFunc::gab_todata($p['d_fine']).'</div>';
            echo '<div style="position:relative;display:inline-block;width:20%;vertical-align:top;" >'.$p['stato'].'</div>';
        echo '</div>';

    }

echo '</div>';


//echo $cadenza.' '.$inizio.' '.$fine.' '.json_encode($a);

?>