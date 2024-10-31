<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

$param=$_POST['param'];

$param['data_f']='21001231';

$galileo->insertCollgru($param);

echo json_encode($galileo->getLog('query'));

?>