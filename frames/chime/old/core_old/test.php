<?php

error_reporting(-1);
ini_set('display_errors', 'On');

$report='';
$input='';
$data="20140403";
$modo='';

include('reports/VWS_app_class.php');

echo $lista->tmp;

foreach ($lista->elementi as $key=>$obj) {
	$lista->d_elemento($key,'1');
}

//mysqli_close($db_handler);
?>
