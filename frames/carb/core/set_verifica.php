<?php
error_reporting(E_ERROR | E_PARSE);
//error_reporting(0);

include('../carb_ini.php');
//include('maestro.php');

$connectionInfo = array( "Database"=>$db, "UID"=>$user, "PWD"=>$pw ,"CharacterSet" => "UTF-8");
$db_handler=sqlsrv_connect("srvdb",$connectionInfo);

date_default_timezone_set ("Europe/Rome");

//echo $_POST[flag];

if ($_POST[flag]=="true") {
	$query="UPDATE CARB_buoni SET d_verifica='".date('Ymd')."',verifica='1' WHERE ID='".$_POST[id]."' ";
}
else {
	$query="UPDATE CARB_buoni SET d_verifica='".date('Ymd')."',verifica='0' WHERE ID='".$_POST[id]."' ";
}

$result=sqlsrv_query($db_handler,$query);

sqlsrv_close($db_handler);

//echo $_POST[flag];

?>