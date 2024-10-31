<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

$param=$_POST['param'];

$gruppi=array();

$galileo->getGruppi("reparto='".$param['reparto']."'");
$result=$galileo->getResult();
if ($result) {
    $fetID=$galileo->preFetchBase('maestro');
    while ($row=$galileo->getFetchBase('maestro',$fetID)) {
        $gruppi[$row['gruppo']]=$row;
    }
}

$galileo->clearQuery();
$galileo->clearQueryOggetto('base','maestro');

$galileo->getAvalaibleColl($param['reparto'],$param['today']);
$result=$galileo->getResult();
$fetID=$galileo->preFetchBase('maestro');

echo '<div style="font-weight:bold;font-size:1.2em;" >Inserimento collaboratore dalla data: '.mainFunc::gab_todata($param['today']).'</div>';

echo '<input id="addcoll_today" type="hidden" value="'.$param['today'].'" />';

echo '<div style="position:relative;margin-top:15px;width:100%;font-size:1.2em;height:100px;" >';

    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:60%;" >';

        echo '<div style="font-weight:bold;">Collaboratore</div>';

        echo '<select id="addcoll_coll" style="font-size:1.1em;" >';

            echo '<option value="">Scegli ...</option>';

            while ($row=$galileo->getFetchBase('maestro',$fetID)) {
                echo '<option value="'.$row['ID'].'" >'.$row['cognome'].' '.$row['nome'].'  ('.$row['ID'].' - '.$row['concerto'].')</option>';
            }

        echo '</select>';

    echo '</div>';

    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:40%;" >';
            
        echo '<div style="font-weight:bold;">Gruppo</div>';

            echo '<select id="addcoll_gruppo" style="font-size:1.1em;">';

                echo '<option value="">Scegli ...</option>';

                foreach ($gruppi as $gruppo=>$g) {

                    echo '<option value="'.$g['ID_gruppo'].'" >'.$gruppo.' - '.$g['des_gruppo'].'</option>';
                }

            echo '</select>';
        echo '</div>';

echo '</div>';

echo '<div style="position:relative;" >';
    echo '<button style="font-size:1.2em;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].confirmAdd();" >Conferma</button>';
echo '</div>';

//echo json_encode($galileo->getLog('query'));

?>