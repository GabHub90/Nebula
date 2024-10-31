<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include('classi/lizexec.php');

$param=$_POST['param'];

$ex=new lizExec($param,$galileo);

$ex->check();

$ex->build();

$ex->draw();

?>