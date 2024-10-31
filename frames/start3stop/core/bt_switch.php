
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
//	mov 		= ordine di lavoro
//	lam 			= lamentato
//	coll			= marcatempo
//	speciale		= tipo marcatura speciale
//	stato_lam	= stato lamentato da chiudere (se esiste)
//	reparto		= reparto selezionato )per individuazione dell'odl fittizio

$odl_fittizio="";

//INDIVIDUAZIONE ODL FITTIZIO
$query="SELECT 
		t1.* 
		FROM CROOM_serv_rif as t1
		INNER JOIN MAESTRO_reparti as t2 on t1.rep=t2.concerto AND t2.tag='".$_POST['reparto']."'
		WHERE t1.stato='1'
		";
		
if($result=sqlsrv_query($db_handler,$query)) {
	while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
		$odl_fittizio=$row['rif'];
	}
}

$maestro->st_apri_marcatura($_POST['mov'],$_POST['lam'],$_POST['coll'],$_POST['speciale'],$_POST['stato'],$odl_fittizio);

sqlsrv_close($db_handler);
?>