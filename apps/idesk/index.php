<?php

ob_start('ob_gzhandler');

include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/nebula_id.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/app_base_class.php");
include('idesk_class.php');

if (!isset($_POST['param']['contesto']['mainLogged'])) die ('Accesso Negato !!!');

$param=$_POST['param'];

$idk=new ideskApp($param,$galileo);

$idk->draw();

ob_end_flush();

?>