<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include('classi/lizattach.php');

$param=$_POST['param'];

$ens=new lizardAttach($param,$galileo);

$ens->draw();
?>