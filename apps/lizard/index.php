<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/nebula_id.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/app_base_class.php");
include('lizard_class.php');

if (!isset($_POST['param']['contesto']['mainLogged'])) die ('Accesso Negato !!!');

$nebulaParams=$_POST['param'];

$ens=new lizardApp($nebulaParams,$galileo);

$ens->draw();
?>