<?php
error_reporting(E_ERROR | E_PARSE);
//error_reporting(0);

include('../carb_ini.php');
//include('maestro.php');

$connectionInfo = array( "Database"=>$db, "UID"=>$user, "PWD"=>$pw ,"CharacterSet" => "UTF-8");
$db_handler=sqlsrv_connect("srvdb",$connectionInfo);

date_default_timezone_set ("Europe/Rome");

$obj=json_decode($_POST[obj]);

if ($obj->flag==0) {
	$q_stato="pagato";
}
else {
	$q_stato="risarcito";
}

$query="UPDATE CARB_buoni set d_ris='".date('Ymd')."',id_ris='".$obj->utente."',flag_ris='".$obj->flag."',nota_ris='".str_replace("'","''",$obj->nota)."',stato='".$q_stato."',mov_open='0' WHERE ID='".$_POST[id]."'";
$result=sqlsrv_query($db_handler,$query);

sqlsrv_close($db_handler);

//echo $query;
?>