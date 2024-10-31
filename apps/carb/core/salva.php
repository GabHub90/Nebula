<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_carb.php');

$param=$_POST['param'];

$obj=new galileoCarb();
$nebulaDefault['carb']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

//{"ID":"0","dms":"infinity","veicolo":"9287","importo":"20","reparto":"RIT","id_rich":"1","id_esec":"1","nota":"prova","gestione":"NEBULA","causale":"VAR","pieno":"","flag_ris":"0","tipo_carb":"D","telaio":"WVWZZZ9NZ2D030404","targa":"BX725MF","des_veicolo":"POLO 1.4 TDI COMFORT","stato":""}

if ($param['d_creazione']=="") $param['d_creazione']=date('Ymd');

$param['importo']=number_format($param['importo'],2,'.','');

$param['mov_open']=0;

if ($param['stato']=='creato' || $param['stato']=='daris' || $param['stato']=='dacompletare') $param['mov_open']=1;

//impostato mov_open per evitare che rianga aperta la videata e si modifichi un buono già chiuso
/*$wc="ID='".$param['ID']."' ";
if ($param['stato']!='creato') $wc.="AND mov_open='1'";

$galileo->executeUpsert('carb','CARB_buoni',$param,$wc);*/

if ($param['stato']=='creato') {
    $galileo->executeInsert('carb','CARB_buoni',$param);
}
else {
    $galileo->executeUpdate('carb','CARB_buoni',$param,"ID='".$param['ID']."'");
}

?>