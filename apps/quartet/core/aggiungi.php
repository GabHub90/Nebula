<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

$param=$_POST['param'];

$r=false;

if ($param['stato']=='crea') {

    //verifica duplicazione
    //indicato il codice come UNIQUE nel DB

    $galileo->creaSchema($param);

}

//se stato==salva || elem==0
//significa che il FORM era editabile e quindi va aggiornato in QUERTET2_schemi


if ($param['stato']=='salva') {
    $galileo->updateSchema($param);
}

$arr=array(
    "skema"=>$param['codice'],
    "pan"=>$param['panorama'],
    "data_i"=>$param['data_i'],
    "blocco_inizio"=>$param['blocco_inizio']
);

$result=$galileo->insertPansk($arr);

if (!$result) die ('Inserimento non riuscito '.json_encode($galileo->getLog('query')));

?>