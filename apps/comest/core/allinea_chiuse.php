<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/comest/classi/comest.php");

//construct inizializza galileo per COMEST
$c=new nebulaComest($galileo);

$c->allineaChiuse();

echo json_encode($c->getLog());

?>