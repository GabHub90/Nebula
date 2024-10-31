<?php
//error_reporting(E_ERROR | E_PARSE);
error_reporting(-1);

require ($_SERVER['DOCUMENT_ROOT'].'/nebula/core/html2pdf/autoload.php');

use Spipu\Html2Pdf\Html2Pdf;

$html2pdf = new Html2Pdf('P','A4');

$html2pdf->setTestTdInOnePage(false);

foreach ($_POST['blocchi'] as $b) {
    $html2pdf->writeHTML($b);
    //$html2pdf->writeHTML(base64_decode($b));
}

echo base64_encode($html2pdf->output('pdf','S'));

?>