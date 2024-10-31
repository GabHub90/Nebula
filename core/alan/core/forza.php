<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_alan.php');

//setta le funzioni GALILEO necessarie
$nebulaDefault=array();
$obj=new galileoAlan();
$nebulaDefault['alan']=array("gab500",$obj);
$galileo->setFunzioniDefault($nebulaDefault);

$param=$_POST['param'];

$in="";
if ($param['IDa']!="") $in.="'".$param['IDa']."',";
if ($param['IDb']!="") $in.="'".$param['IDb']."',";

if ($in!="") {

    //valore qta in minuti
    $forza=(int) ($param['qta']*60);

    $arr=array(
        "forza_minuti"=>$forza
    );

    $res=$galileo->executeUpdate('alan','ALAN_timbrature',$arr,"IDTIMBRATURA IN(".substr($in,0,-1).")");
}

if ($res) echo json_encode($param);
else echo 'errore update';

?>