<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/lizard/classi/lizexec.php');

/*$param=array(
    "liz_exec"=>"lucia"
);*/
$param=$_POST['param'];

$ex=new lizExec($param,$galileo);

$ex->check();

$ex->build();

foreach ($ex->getLista() as $k=>$l) {
    echo $l['nome'].','.$l['telefono'].PHP_EOL;
}

?>