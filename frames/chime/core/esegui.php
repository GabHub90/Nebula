<?php
error_reporting(E_ERROR | E_PARSE);
//ini_set('display_errors', 'On');
//error_reporting(0);

require('../chime_func.php');
require('class/sandy.php');
//require('class/http_post.php');
include('class/maestro.php');

$maestro=new Maestro();

//-------STANDARD NOMI-------------------------------
//	azione				record descrittivo azione
//	giorno				AAAMMDD giorno di riferimento
//	lista				lista dei nominativi
////////////////////////////////////////////////////

$param=$_REQUEST['param'];

$azione=$param['azione'];

/*$connectionInfo = array( "Database"=>$db, "UID"=>$user, "PWD"=>$pw, "CharacterSet" => "UTF-8");
$db_handler=sqlsrv_connect("srvdb",$connectionInfo);
date_default_timezone_set ("Europe/Rome");*/

/////////////////////////////////////////////////////

$send=new SandyMan($azione,$param['lista'],$maestro);

$send->execute_akt($azione['send'],$param['giorno']);

//echo json_encode($send->get_log());

?>

<?php
//sqlsrv_close($db_handler);
?>