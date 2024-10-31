<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require_once(DROOT.'/nebula/core/odl/odl_func.php');

$param=$_POST['param'];

$odlFunc=new nebulaOdlFunc($galileo);

$odlFunc->deletePassman($param);


?>