<?php
error_reporting(E_ERROR | E_PARSE);
//error_reporting(0);

//GENERA LA LISTA DEI LAMENTATI PER L'APERTURA DI UNA MARCATURA SU UN NUOVO ORDINE , riceve i parametri [coll]=marcatempo collaboratore - [id]=num_rif_movimento

include('../ststop_ini.php');
include('../ststop_func.php');
include('maestro.php');
include('st_reparto.php');
include('st_marcatura.php');
include('st_collaboratore.php');
include('st_environment.php');

include($_SERVER['DOCUMENT_ROOT'].'/apps/quartet/core/quartet_loader.php');

//connessione al database
$connectionInfo = array( "Database"=>$db, "UID"=>$user, "PWD"=>$pw ,"CharacterSet" => "UTF-8");
$db_handler=sqlsrv_connect("srvdb",$connectionInfo);

date_default_timezone_set ("Europe/Rome");

$maestro=new Maestro();

$today=date('Ymd');

//$_POST[coll]:		marcatempo
//$_POST[reparto]:	reparto (VWS,AUS)
//$_POST[tipo]:		NUOVO: 	bottone NUOVO
//					START:	bottone START lamentato interfaccia principale
//					STOP:	bottone STOP lamentato interfaccia principale
//					FINE:	bottone FINE lamentato interfaccia principale
//$_POST[info]:		eventuale informazione aggiuntiva

$reparto=new stReparto($db_handler,$maestro,$_POST['reparto'],$_POST['coll']);


?>

<div id="st_open_head" class="st_open_head" style="margin-top: 5px;">
	<?php
		//if ($_POST[tipo]=="NUOVO") {
		$reparto->draw_coll_open_head($_POST['coll'],$_POST['tipo'],$_POST['info']);
		//}
	?>
</div>

<div id="st_open_lamentati" class="st_open_lamentati">
</div>

<?php
sqlsrv_close($db_handler);
?>