<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_alan.php');

//setta le funzioni GALILEO necessarie
$nebulaDefault=array();
$obj=new galileoAlan();
$nebulaDefault['alan']=array("gab500",$obj);
$galileo->setFunzioniDefault($nebulaDefault);

$param=$_POST['param'];

$timbratura=false;

$galileo->executeSelect('alan','ALAN_timbrature',"IDTIMBRATURA='".$param['IDTIMBRATURA']."'","");
$result=$galileo->getResult();
if ($result) {

    $fetID=$galileo->preFetch('alan');
    while($row=$galileo->getFetch('alan',$fetID)) {
        $timbratura=$row;
    }
}
else die('errore DB');

$res=false;

if ($timbratura) {

    if ($timbratura['VERSOO']=='E') $timbratura['VERSOO']='U';
    elseif ($timbratura['VERSOO']=='U') $timbratura['VERSOO']='E';

    $timbratura['tag']=$param['tag'];
    $timbratura['prefix']=$param['prefix'];
    $timbratura['IDcoll']=$param['IDcoll'];

    $arr=array(
        'VERSOO'=>$timbratura['VERSOO']
    );

    $res=$galileo->executeUpdate('alan','ALAN_timbrature',$arr,"IDTIMBRATURA='".$timbratura['IDTIMBRATURA']."'");
}

if ($res) echo json_encode($timbratura);
else echo 'errore update';

?>