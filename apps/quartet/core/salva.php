<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

$param=$_POST['param'];

if ($param['stato']=='crea') {

    $galileo->creaSchema($param);
}

if ($param['stato']=='salva') {
    $galileo->updateSchema($param);
}

$galileo->executeSchema();

?>