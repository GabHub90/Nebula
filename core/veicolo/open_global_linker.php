<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include('classi/global_linker.php');

$params=$_POST['param'];

$gl=new nebulaGlobalLinker($params,$galileo);

$gl->drawJS();

$gl->drawSearch();

?>