<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/core/fidel/fidel.php");

$params=$_POST['param'];

$f=new nebulaFidel($params['index'],$galileo);

$f->build($params['param']);

echo $f->print();

?>