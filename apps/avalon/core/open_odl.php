<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/nebula_id.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/app_slave_class.php");

include($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/odielle.php");
//include('../classi/wormhole.php');

$nebulaParams=$_POST['param'];

$odl=new nebulaOdielle ($nebulaParams,$galileo);

$odl->draw();

//echo json_encode($galileo->getLog('dberror'));
?>