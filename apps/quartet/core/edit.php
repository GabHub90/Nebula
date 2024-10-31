<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include($_SERVER['DOCUMENT_ROOT']."/nebula/core/panorama/panedit.php");

$param=$_POST['param'];

$obj=new nebulaPanEdit($param['reparto'],$param['tipo'],$param['today'],$galileo);

$obj->drawEdit();

?>