<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

$param=$_POST['param'];

$arr=array();

$chk=array(false,false,false,false,false,false,false);

foreach ($param['turno'] as $k=>$t) {
    $arr[$k]=$t;
    $arr[$k]['codice']=$param['codice'];
    $chk[$k]=true;
}

$chkFlag=true;

foreach ($chk as $c) {
    if (!$c) $chkFlag=false;
}

if ($chkFlag) {
    $result=$galileo->creaTurno($arr);
}
else {
    die('Turno incompleto');
}

?>