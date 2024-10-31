<?php  
error_reporting(E_ERROR | E_PARSE);
//error_reporting(0);

include ('../carb_ini.php');

$connectionInfo = array( "Database"=>$db, "UID"=>$user, "PWD"=>$pw ,"CharacterSet" => "UTF-8");
$db_handler=sqlsrv_connect("srvdb",$connectionInfo);

date_default_timezone_set ("Europe/Rome");

$query="DELETE CARB_BUONI WHERE ID='".$_POST[id]."'";
$result=sqlsrv_query($db_handler,$query);

sqlsrv_close($db_handler);
?>