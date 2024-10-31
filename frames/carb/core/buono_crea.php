<?php
error_reporting(E_ERROR | E_PARSE);
//error_reporting(0);

include('../carb_ini.php');
include('maestro.php');

$connectionInfo = array( "Database"=>$db, "UID"=>$user, "PWD"=>$pw ,"CharacterSet" => "UTF-8");
$db_handler=sqlsrv_connect("srvdb",$connectionInfo);

date_default_timezone_set ("Europe/Rome");

//lettura dei parametri

//$maestro=new Maestro();

$v=(array)json_decode($_POST[values]);
$id=0;
$key="";

$query="SELECT isnull(max(ID),0) as id FROM CARB_buoni";

if($result=sqlsrv_query($db_handler,$query)) {
	while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
		$id=$row[id]+1;
	}
}


//se c'è stato un errore non eseguire altro codice
if ($id!=0) {

	$arrcas=explode(':',$v[causale]);

	if ($v[ID]==0) {
		$query="INSERT INTO CARB_buoni values('".$id."','".$v[vettura]."','".$v[importo]."','".$v[reparto]."',0,0,'".date("Ymd")."','','','','creato','".str_replace("'","''",$v[nota])."','".$v[gestione]."','".$arrcas[0]."','".$v[pieno]."','1','0','0','','',0,'','0','".$arrcas[1]."','','0','".$v[carb_tipo]."')";
		if($result=sqlsrv_query($db_handler,$query)) {
			if (sqlsrv_rows_affected($result)) {
				$key=''.$id;
			}
		}
		
		echo 'carb_home("'.$key.'");';
	}
	
	else {
		$query="UPDATE CARB_buoni set veicolo='".$v[vettura]."',importo='".$v[importo]."',reparto='".$v[reparto]."',d_creazione='".date("Ymd")."',nota='".str_replace("'","''",$v[nota])."',gestione='".$v[gestione]."',pieno='".$v[pieno]."',causale='".$arrcas[0]."',autz='".$arrcas[1]."',tipo_carb='".$v[carb_tipo]."' WHERE ID='".$v[ID]."'";
		$result=sqlsrv_query($db_handler,$query);
		
		echo 'carb_home(0);';
	}
}

//echo 'alert("'.$query.'");';

sqlsrv_close($db_handler);
?>