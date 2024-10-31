<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
require_once(DROOT.'/nebula/apps/gdm/classi/materiale.php');
include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/gab500/galileo_gdm.php");

$param=$_POST['param'];

$obj=new galileoGDM();
$nebulaDefault['gdm']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

///////////////////////////////////////////////////////////
//VALIDO SOLO PER PNAUMATICI

/*{
    "form":{"gdmForm_7132":{"flag":1,"chg":0,"expo":{"id":"7132","dimeASx":"205/55R16 91V","dimeADx":"205/55R16 91V","dimePSx":"205/55R16 91V","dimePDx":"205/55R16 91V","marcaASx":"uybiu","marcaADx":"uybiu","marcaPSx":"uybiu","marcaPDx":"uybiu","dotASx":"2345","dotADx":"2345","dotPSx":"2345","dotPDx":"2345","usuraASx":"5.5","usuraADx":"5.5","usuraPSx":"5.5","usuraPDx":"5.5","annotazioni":"","operazione":"0","destinazione":"Deposito","origine":"Deposito","compoGomme":"TRENO","tipoGomme":"4 STAGIONI"}}},
    "id":"7132"
}*/

$arr=array('ASx','ADx','PSx','PDx');

$info=array(
    "form"=>array(),
    "veicolo"=>array()
);

//è uno solo
foreach ($param['form'] as $k=>$f) {
    $info['form']=$f['expo'];
}

$info['form']['isFull']='True';
$info['form']['isAnnullato']='False';

foreach ($arr as $k=>$p) {
    if ((int)$info['form']['usura'.$p]<0) $info['form']['isFull']='False';
}

$info['veicolo']=$param['veicolo'];

$galileo->setTransaction(true);

$galileo->executeGeneric('gdm','editMateriale',$info,'');

echo json_encode($galileo->getLog('query'));


?>