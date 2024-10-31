<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/nebula_id.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/app_base_class.php");
include('storico_class.php');

$nebulaParams=$_POST['param'];

$storico=new storicoApp($nebulaParams,$galileo);

$storico->draw();

?>