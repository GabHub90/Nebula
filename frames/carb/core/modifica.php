<?php
error_reporting(E_ERROR | E_PARSE);
//error_reporting(0);

include('../ammo_func.php');
include('../carb_ini.php');
include('maestro.php');

$connectionInfo = array( "Database"=>$db, "UID"=>$user, "PWD"=>$pw ,"CharacterSet" => "UTF-8");
$db_handler=sqlsrv_connect("srvdb",$connectionInfo);

date_default_timezone_set ("Europe/Rome");

$obj=json_decode($_POST[obj]);
?>

<div id="carb_modifica" class="carb_modifica">
    <?php
	echo'<div style="margin-top:5px;">';
		echo '<label style="margin-right:44.5px;">Nota</label>';
		echo '<input type="text" size="40" maxlength="35" value="'.$obj->nota.'">';
	echo'</div>';
	?>
    
    
</div>

<?php  
sqlsrv_close($db_handler);
?>