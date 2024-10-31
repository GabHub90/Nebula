<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_odl.php');

$param=$_POST['param'];

$obj=new galileoODL();
$nebulaDefault['odl']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

///////////////////////////////////////////////////////////

if ($param['tipo']=='Modello') {

    $wc="marca='".$param['marca']."' AND modello='".$param['modello']."'";

    if (!isset($param['oggetti']) || $param['oggetti']=="") {
        $galileo->executeDelete('odl','OT2_criteri_mod',$wc);
    }
    else {
        $galileo->executeUpsert('odl','OT2_criteri_mod',array("marca"=>$param['marca'],"modello"=>$param['modello'],"edit"=>json_encode($param['oggetti'])),$wc);
    }
}

elseif ($param['tipo']=='Telaio') {

    $wc="telaio='".$param['telaio']."'";

    if (!isset($param['oggetti']) || $param['oggetti']=="") {
        $galileo->executeDelete('odl','OT2_criteri_tel',$wc);
    }
    else {
        $galileo->executeUpsert('odl','OT2_criteri_tel',array("telaio"=>$param['telaio'],"edit"=>json_encode($param['oggetti'])),$wc);
    }
}

?>