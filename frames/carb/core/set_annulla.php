<?php
error_reporting(E_ERROR | E_PARSE);
//error_reporting(0);

include('../carb_ini.php');
//include('maestro.php');

$connectionInfo = array( "Database"=>$db, "UID"=>$user, "PWD"=>$pw ,"CharacterSet" => "UTF-8");
$db_handler=sqlsrv_connect("srvdb",$connectionInfo);

date_default_timezone_set ("Europe/Rome");

$obj=json_decode($_POST[obj]);


$query="UPDATE CARB_buoni set d_annullo='".date('Ymd')."',id_annullo='".$obj->utente."',nota_annullo='".str_replace("'","''",$obj->nota)."',stato='annullato',mov_open='0' WHERE ID='".$_POST[id]."'";
$result=sqlsrv_query($db_handler,$query);

sqlsrv_close($db_handler);

//echo $query;
?>