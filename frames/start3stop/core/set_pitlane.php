<?php
//error_reporting(E_ERROR | E_PARSE);
error_reporting(0);

include('maestro.php');

$a=$_POST[obj];

$txt=str_replace("'","''",$a[annotazioni]);

$maestro=new Maestro();

$query="INSERT INTO OF_GAB_PITLANE
		(num_rif_movimento,cod_inconveniente,collaboratore,cod_off,apertura,stato,annotazioni)
		values ('".$a[num_rif_movimento]."','".$a[cod_inconveniente]."','".$a[cod_operaio]."','".$a[cod_off]."','".date("d/m/Y H:i:s")."','OPEN','".$txt."')
		";
		
$maestro->ststop_query($query);

?>