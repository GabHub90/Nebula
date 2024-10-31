<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/odl_linker.php");

$param=$_POST['param'];

//echo json_encode($_POST['param']);

//linker contiene l'informazione del DMS

$linker=new nebulaOdlLinker(isset($param['linker'])?$param['linker']:array(),isset($param['wormhole'])?$param['wormhole']:array(),$galileo);

$linker->drawBody();

?>