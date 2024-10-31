<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/timb_func.php");

$param=$_POST['param'];

$func=new nebulaTimbFunc($galileo);

$func->restartMarcatura($param['dms'],$param['ID']);

?>