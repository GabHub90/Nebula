<?php
include('default.php');
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/centavos/classi/centext.php");

$c=new centext($nebulaParams,$galileo);

$c->drawTag($nebulaParams['tag'],$nebulaParams['titolo']);

?>