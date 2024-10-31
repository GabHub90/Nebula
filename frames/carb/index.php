<?php
error_reporting(E_ERROR | E_PARSE);
//error_reporting(0);

include('carb_ini.php');

/*$connectionInfo = array( "Database"=>$db, "UID"=>$user, "PWD"=>$pw ,"CharacterSet" => "UTF-8");
$db_handler=sqlsrv_connect("srvdb",$connectionInfo);

date_default_timezone_set ("Europe/Rome");

//lettura dei parametri
$reparti=array();
$query="SELECT * FROM MAESTRO_reparti where tipo IN('S','V') order by descrizione";
		if($result=sqlsrv_query($db_handler,$query)) {
			while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$reparti[$row[ID]]=$row;
			}
		}*/
?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html;charset=utf-8" >
	<meta http-equiv="Pragma" content="no-cache"> 
	<meta http-equiv="Expires" content="-1"> 
	<meta http-equiv="cache-control" content="no-store">
	<title>Buono Carburante</title>
	<link rel="shortcut icon" href="favicon.ico"/>
	<link rel="stylesheet" type="text/css" href="carb.css?v=<?php echo time();?>"/>	
	<script type="text/javascript" src="jquery-1.10.2.js"></script>
	<script type="text/javascript" src="carb.js?v=<?php echo time();?>"></script>
	<script type="text/javascript" src="jquery.jPrintArea.js"></script>
	<script type="text/javascript">
	</script>
</head> 
<body onload="carb_set_global();">
	
	<div id="carb_main" class="carb_main"></div>
	
	<div id="carb_right" class="carb_right" style="margin-top:20px;"></div>
	
	<div id="carb_cover" class="carb_cover"></div>
	
	<iframe id="download"></iframe>
	
</body>
</html>

<?php
//sqlsrv_close($db_handler);
?>