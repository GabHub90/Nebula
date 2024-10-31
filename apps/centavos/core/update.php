<?php
include('default.php');

$variante=$nebulaParams['form']['ctv_FS']['expo'];
$variante['gradi']=$nebulaParams['form']['ctv_FSG']['expo']['gradi'];

foreach ($nebulaParams['form'] as $k=>$f) {

    $arr=explode("_",$k);

    if ($arr[0]=='ctm') {
        $wclause="ID='".$arr[1]."'";
        $t=array(
            "funzione"=>$f['expo']['funzione'],
            "griglia"=>$f['expo']['griglia'],
        );
        unset($f['expo']['funzione']);
        unset($f['expo']['griglia']);
        $t['param']=json_encode($f['expo']);
        
        $galileo->executeUpdate("centavos","CENTAVOS_parametri",$t,$wclause);
        $galileo->executeClear("centavos");
    }
}

//update variazione
$wclause="ID='".$nebulaParams['ID']."'";
$galileo->executeUpdate("centavos","CENTAVOS_varianti",$variante,$wclause);

//echo preg_replace('/\\\"/',"\"", json_encode($nebulaParams));

echo json_encode($galileo->getLog('query'));

?>