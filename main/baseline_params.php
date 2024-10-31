<?php
include("baseline.php");

$jsonStr = file_get_contents("php://input"); //read the HTTP body.
$nebulaParams = json_decode($jsonStr,true);

include("nebula_system.php");

?>