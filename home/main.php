
<?php

include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline_params.php");
include('home_system.php');
$nebulaVersion="1.0";

$sistema=new homeSystem('Home',$nebulaParams['nebulaContesto'],$nebulaVersion,$galileo);

$sistema->drawNebumenu();

$sistema->drawSystem();

?>