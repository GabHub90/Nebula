<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
//include($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/concerto/concerto_veicoli.php');

include("classi/veicolo_main.php");

/*$obj=new galileoConcertoVeicoli();
$nebulaDefault['veicoli']=array("maestro",$obj);

$galileo->setFunzioniDefault($nebulaDefault);*/

$param=$_POST['param'];

$vei=new nebulaVeicolo("",$galileo);

$ret=$vei->cercaLinker($param);

echo json_encode($ret);


?>