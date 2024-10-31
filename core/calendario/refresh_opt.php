<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/core/calendario/calnav.php");

$nebulaParams=$_POST['param'];

//function __construct($risoluzione,$today,$config,$css,$galileo)
$calnav=new calnav($nebulaParams['risoluzione'],$nebulaParams['today'],$nebulaParams['config'],array(),$galileo);

$calnav->drawOpt();

?>