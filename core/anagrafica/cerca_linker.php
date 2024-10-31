<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
//include($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/concerto/concerto_anagrafiche.php');

include("classi/anagrafica_main.php");

/*$obj=new galileoConcertoAnagrafiche();
$nebulaDefault['anagra']=array("maestro",$obj);

$galileo->setFunzioniDefault($nebulaDefault);*/

$param=$_POST['param'];

$ana=new nebulaAnagrafica("",$galileo);

$ret=$ana->cercaLinker($param);

echo json_encode($ret);


?>