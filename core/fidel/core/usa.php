<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/core/fidel/fidel.php");

$params=$_POST['param'];

$f=new nebulaFidel($params['index'],$galileo);

$a=array(
    "stato"=>"chiuso",
    "chiusura"=>date('Ymd'),
    "utente_chiusura"=>$params['utente'],
    "dms_chiusura"=>$params['dms'],
    "odl_chiusura"=>$params['odl']
);

$f->usa($params['voucher'],$a);

$f->build($params['param']);

$f->refresh();

?>