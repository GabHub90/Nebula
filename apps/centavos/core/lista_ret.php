<?php
include('default.php');
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/centavos/classi/centret.php");

$c=new centret($nebulaParams,$galileo);

$c->drawTag($nebulaParams['tag'],$nebulaParams['titolo']);

?>