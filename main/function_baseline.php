<?php
include("baseline.php");
include("function_class/basilare.php");

$jsonStr = file_get_contents("php://input"); //read the HTTP body.
$nebulaParams = json_decode($jsonStr,true);

echo '<style type="text/css">   @import url("http://'.SADDR.$nebulaParams['nebulaFunzione']['loc'].'/main.css"); </style>';

?>