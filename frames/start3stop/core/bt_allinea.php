
<?php
error_reporting(E_ERROR | E_PARSE);
//error_reporting(0);

include('../ststop_ini.php');
include('../ststop_func.php');
include('maestro.php');

//connessione al database
$connectionInfo = array( "Database"=>$db, "UID"=>$user, "PWD"=>$pw ,"CharacterSet" => "UTF-8");
$db_handler=sqlsrv_connect("srvdb",$connectionInfo);

date_default_timezone_set ("Europe/Rome");

$maestro=new Maestro();

//$_POST:  
//	coll			= marcatempo
//	stato_lam	= stato lamentato da chiudere (se esiste)
//	ora			= ora allineamento


$maestro->st_chiudi_marcatura(date('Ymd').' '.$_POST[ora],$_POST[coll],$_POST[stato_lam]);

sqlsrv_close($db_handler);
?>