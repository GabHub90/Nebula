<?php

include('default.php');

$d_fine=false;

//trova i periodi e quindi l'ultima data da attribuire a 9999
$arr=array(
    "piano"=>$nebulaParams['piano']
);

$galileo->executeGeneric('centavos','getLastFine',$arr,'');

if ($result=$galileo->getResult()) {

    $fid=$galileo->preFetch('centavos');

    while ($row=$galileo->getFetch('centavos',$fid)) {
        $d_fine=$row['d_fine'];
    }
}

if (!$d_fine) die ('Data non rilevata');

$arr=array(
    "data_f"=>$d_fine
);

$galileo->executeUpdate('centavos','CENTAVOS_link',$arr,"piano='".$nebulaParams['piano']."' AND periodo_fine='9999'");

?>