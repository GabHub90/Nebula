<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/odl_func.php");

$param=$_POST['param'];

$odlFunc=new nebulaOdlFunc($galileo);

$odlFunc->editLam($param['rif'],$param['lam'],$param['dms'],$param['pren'],$param);

?>