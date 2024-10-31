<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/odl/odl_func.php');

$param=$_POST['param'];

$a=json_decode(base64_decode($param['obj']),true);

//echo json_encode($a);

$odlFunc=new nebulaOdlFunc($galileo);

$odlFunc->allineaDms($a);

?>