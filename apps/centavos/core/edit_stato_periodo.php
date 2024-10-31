<?php

include('default.php');

if (!isset($nebulaParams['stato'])) $nebulaParams['stato']="";

$ck=true;

if ($nebulaParams['stato']=='freezed') {
    $ck=false;

    //##################################################
    //scrivi i dati CONGELATI
    //##################################################

}

if ($ck) {

    $arr=array(
        "hidden"=>$nebulaParams['hidden']
    );

    if($nebulaParams['stato']!="") $arr['stato']=$nebulaParams['stato'];

    $galileo->executeUpdate('centavos','CENTAVOS_periodi',$arr,"ID='".$nebulaParams['periodo']."'");
}

echo json_encode($galileo->getLog('query'));

?>