<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_odl.php');

$param=$_POST['param'];

$obj=new galileoODL();
$nebulaDefault['odl']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

///////////////////////////////////////////////////////////

$arr=array(
    "oggetto"=>$param['oggetto'],
    "codice"=>$param['codice'],
    "tipo"=>$param['tipo'],
    "min_qta"=>number_format($param['qta'],2,'.',''),
    "chk"=>$param['chk']
);

if ($param['old']==1) {
    $galileo->executeUpdate('odl','OT2_eventi',$arr,"oggetto='".$param['oggetto']."' AND codice='".$param['codice']."' AND tipo='".$param['tipo']."'");
}
else {
    $galileo->executeInsert('odl','OT2_eventi',$arr);
}

echo json_encode($galileo->getLog('query'));

?>