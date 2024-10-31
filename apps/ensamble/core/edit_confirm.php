<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

$param=$_POST['param'];

$arr=array(
    "panorama"=>$param['panorama'],
    "collaboratore"=>$param['coll'],
    "gruppo"=>$param['id_gruppo'],
    "data_f"=>$param['data_f']
);

$galileo->chiudiCollgru($arr);
$galileo->chiudiCollskOpen($arr);

?>