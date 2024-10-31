<?php
error_reporting(E_ERROR | E_PARSE);
//error_reporting(0);

include('graffa_func.php');
include('graffa_ini.php');

/*$connectionInfo = array( "Database"=>$db, "UID"=>$user, "PWD"=>$pw ,"CharacterSet" => "UTF-8");
$db_handler=sqlsrv_connect("srvdb",$connectionInfo);

date_default_timezone_set ("Europe/Rome");

//lettura dei parametri
*/

?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >
	<meta http-equiv="Pragma" content="no-cache"> 
	<meta http-equiv="Expires" content="-1"> 
	<meta http-equiv="cache-control" content="no-store">
	<title>Appuntamenti</title>
	<link rel="shortcut icon" href="favicon.ico">
	<link rel="stylesheet" type="text/css" href="graffa.css" media="screen" />	
	<script type="text/javascript" src="jquery-1.10.2.js"></script>
	<script type="text/javascript" src="graffa.js"></script>
	<script type="text/javascript">
		//Variabili globali
		_graffa_rifdata="<?php echo date('Ymd');?>";
		_graffa_reparto='';
		_graffa_pren={};
		_graffa_temp=[];
	</script>
</head>

<body style="position:relative;margin: 0px;" onload="graffa_setrep('<?php echo $_GET['reparto'];?>');">
	
	<div id="graffa_main" class="graffa_main"></div>
	
</body>
</html>

<?php
	//sqlsrv_close($db_handler);
?>