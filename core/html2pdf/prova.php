<?php
//error_reporting(E_ERROR | E_PARSE);
error_reporting(-1);

require ('autoload.php');

use Spipu\Html2Pdf\Html2Pdf;

$html2pdf = new Html2Pdf();
$html2pdf->writeHTML('<h1>HelloWorld</h1>This is my first test');
$html2pdf->output();

?>