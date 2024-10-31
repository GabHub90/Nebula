<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/nebula_id.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/app_base_class.php");

include($_SERVER['DOCUMENT_ROOT'].'/nebula/core/divo/divo.php');

include('centavos_class.php');
include('classi/ctv_chekko_sezione.php');
include('classi/ctv_chekko_gradi.php');
include('classi/ctv_chekko_modulo.php');

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_centavos.php');

if (!isset($_POST['param']['contesto']['mainLogged'])) die ('Accesso Negato !!!');

//setta le funzioni GALILEO necessarie
$nebulaDefault=array();
$obj=new galileoCentavos();

//prendi il riferimento dell'oggetto utenti da Galileo
$l=$galileo->getObjectBase("maestro");
//aggiungi il riferimento dell'oggetto a "centavos"
$obj->addLink('maestro',$l);

$nebulaDefault['centavos']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$nebulaParams=$_POST['param'];

$ws=new centavosApp($nebulaParams,$galileo);

$ws->drawViewer($nebulaParams['ribbon']['ctv_logged']);

?>