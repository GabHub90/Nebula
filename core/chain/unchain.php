<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require('chain.php');

$param=$_POST['param'];

$chain=new nebulaChain($param['app'],$galileo);

$chain->unchain($param['chiave'],$param['utente']);

?>