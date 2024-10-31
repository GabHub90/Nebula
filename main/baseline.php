<?php
//error_reporting(E_ERROR | E_PARSE);
error_reporting(-1);

//////////////////////////////////////////////////////////
//INIT
//////////////////////////////////////////////////////////

//C:.....
define("DROOT", $_SERVER['DOCUMENT_ROOT']);
//10.55.99.14
define("SADDR", $_SERVER['SERVER_ADDR']);

include_once("main_func.php");
include_once("nebula_universe.php");
include_once(DROOT.'/nebula/galileo/galileo_main.php');
//per il momento gli utenti sono su concerto
include_once(DROOT.'/nebula/galileo/concerto/concerto_utenti.php');
include_once(DROOT.'/nebula/galileo/gab500/maestro_utenti.php');
include_once(DROOT.'/nebula/galileo/gab500/maestro_reparti.php');
include_once(DROOT.'/nebula/galileo/gab500/maestro_applicazioni.php');
include_once(DROOT.'/nebula/galileo/gab500/galileo_calendario.php');
include_once(DROOT.'/nebula/galileo/gab500/maestro_schemi.php');
include_once(DROOT.'/nebula/galileo/gab500/nebula_avalon.php');

include_once(DROOT.'/nebula/galileo/solari/dbstart.php');

//setta le funzioni necessarie (BASE)
$nebulaBase=array();
$obj=new galileoConcertoUtenti();
$nebulaBase['utenti']=array("maestro",$obj);

$obj=new galileoMaestroApp();
$nebulaBase['applicazioni']=array("gab500",$obj);

$obj=new galileoMaestroReparti();
$nebulaBase['reparti']=array("gab500",$obj);
$obj2=new galileoMaestroUtenti($obj);
$nebulaBase['maestro']=array("gab500",$obj2);

$obj=new galileoCalendario();
$nebulaBase['calendario']=array("gab500",$obj);

$obj=new galileoMaestroSchemi();
$nebulaBase['schemi']=array("gab500",$obj);

$obj=new solariDBstart();
$nebulaBase['badge']=array("solari",$obj);

$obj=new nebulaAvalonDB();
$nebulaBase['avalon']=array("gab500",$obj);

////////////////////////////////////////////////////////

$galileo=new GalileoMain($nebulaBase);

$nebulaParams=array();