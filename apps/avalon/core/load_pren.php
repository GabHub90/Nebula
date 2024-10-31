<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/avalon/classi/avalon_set_day.php");

$nebulaParams=$_POST['param'];

$sd=new avalonSetDay($nebulaParams,$galileo);

$sd->drawPren();

?>