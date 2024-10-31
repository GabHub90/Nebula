<?php
require ('autoload.php');

use Spipu\Html2Pdf\Html2Pdf;

$jsonStr = file_get_contents("php://input"); //read the HTTP body.
$param = json_decode($jsonStr,true);

$html2pdf = new Html2Pdf();
$html2pdf->writeHTML($param['txt']);
//$html2pdf->writeHTML('<h1>HelloWorld</h1>This is my first test');

//il titolo viene ignorato
echo $html2pdf->output('prova.pdf','S');
//$html2pdf->output();
//echo json_encode($param);

?>