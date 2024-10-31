<?php 
include ("../msg.config.php");

$db = mysqli_connect($db_host, $db_user, $db_password);

if ($db == FALSE)
		die(" <div class=\"error\">Server Mysql non raggiungibile -".$db_host."-</div>");
		
mysqli_select_db($db,$db_name)
	or die (" <div class=\"error\">Errore apertura Database</div>");


$d=date("Y-m-d H:i:s");

$query="UPDATE messaggi SET attempts='".($_POST['num']+1)."',last='".$d."' WHERE ID='".$_POST['index']."'";

$result=mysqli_query($db,$query);

mysqli_close($db);
?>