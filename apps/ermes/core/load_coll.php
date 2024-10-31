<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/ermes/classi/ermes.php");

$param=$_POST['param'];

$tk=new ermesTicket($galileo);

$galileo->getCollaboratori('reparto',$param['reparto'],date('Ymd'));

if ($galileo->getResult()) {

    $fid=$galileo->preFetchBase('maestro');

    //{"ID_coll":24,"data_i":"20120501","data_f":"21001231","ID_gruppo":7,"gruppo":"TEC","des_gruppo":"Tecnico","posizione":1,"macrogruppo":"TES","des_macrogruppo":"Tecnici Service","posizione_macrogruppo":3,"sede":"PU","reparto":"AUS","macroreparto":"S","des_reparto":"Service Audi","rep_concerto":"PA","des_macroreparto":"Service","nome":"Ivan","cognome":"Bruscia","concerto":"i.bruscia","cod_operaio":"28","tel_interno":"","cellulare":"","mail":"","IDDIP":"68","IDMAT":"143"}

    while ($row=$galileo->getFetchBase('maestro',$fid)) {
        if ($tk->getPermesso($row['reparto'],$row['gruppo'])) {
            echo '<option value="'.$row['concerto'].'">'.$row['cognome'].' '.$row['nome'].'</option>';
        }
    }

}


?>