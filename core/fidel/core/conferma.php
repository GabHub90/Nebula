<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/core/fidel/fidel.php");

$params=$_POST['param'];

$f=new nebulaFidel($params['index'],$galileo);

if (array_key_exists('scadenza',$params)) {
    $params['scadenza']=mainFunc::gab_input_to_db($params['scadenza']);
}
if (array_key_exists('ID',$params)) {
    $params['template']=$params['ID'];
}
$params['creazione']=date('Ymd');
$params['stato']='aperto';

$f->newVoucher($params);

$f->build($params['param']);

$f->refresh();

?>