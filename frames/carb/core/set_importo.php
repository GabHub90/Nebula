<?php
error_reporting(E_ERROR | E_PARSE);
//error_reporting(0);

include('../carb_ini.php');
//include('maestro.php');

$connectionInfo = array( "Database"=>$db, "UID"=>$user, "PWD"=>$pw ,"CharacterSet" => "UTF-8");
$db_handler=sqlsrv_connect("srvdb",$connectionInfo);

date_default_timezone_set ("Europe/Rome");

$obj=json_decode($_POST[obj]);

if ($obj->gestione=='_CLIENTE_') {
	$q_open=1;
	$q_stato="daris";
}
else {
	$q_open=0;
	$q_stato="completato";
}

$query="UPDATE CARB_buoni set importo='".$obj->importo."',stato='".$q_stato."',nota='".str_replace("'","''",$obj->nota)."',mov_open='".$q_open."' WHERE ID='".$_POST[id]."'";
$result=sqlsrv_query($db_handler,$query);

sqlsrv_close($db_handler);
?>