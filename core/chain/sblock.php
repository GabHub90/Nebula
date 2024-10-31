<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/core/chain/chain.php");

$param=$_POST['param'];

$c=new nebulaChain($param['app'],$galileo);

$c->sblock($param['chiave']);

?>