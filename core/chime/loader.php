<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include ('chime_class.php');

$param=$_POST['param'];

$chime=new nebulaChime($param['reparto'],$param['pren'],$galileo);

$chime->drawHead($param['day'],$param['pratica'],$param['dms']);

?>