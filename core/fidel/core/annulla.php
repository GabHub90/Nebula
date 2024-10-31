<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/core/fidel/fidel.php");

$params=$_POST['param'];

$f=new nebulaFidel($params['index'],$galileo);

$a=array(
    "stato"=>"annullato",
    "chiusura"=>date('Ymd'),
    "utente_chiusura"=>$params['utente'],
    "dms_chiusura"=>"",
    "odl_chiusura"=>""
);

$f->annulla($params['voucher'],$a);

$f->build($params['param']);

$f->refresh();

?>