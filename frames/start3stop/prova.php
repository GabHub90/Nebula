<?php
error_reporting(E_ERROR | E_PARSE);
include('ststop_ini.php');

$connectionInfo = array( "Database"=>$db, "UID"=>$user, "PWD"=>$pw);
$db=sqlsrv_connect("srvdb",$connectionInfo);

$query="SELECT * FROM MAESTRO_reparti WHERE tipo='S' OR tipo='X' ORDER BY tipo,tag";
if($result=sqlsrv_query($db,$query)) {
	while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
		$reparti[$row[tag]]=$row;
	}
}

print_r($reparti);

sqlsrv_close($db);
?>