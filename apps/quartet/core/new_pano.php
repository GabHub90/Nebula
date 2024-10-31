<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

$param=$_POST['param'];

if ($param['reparto']!="" && $param['am']!="" && $param['oa']!="") {
    $galileo->setNewPano($param);
}

?>