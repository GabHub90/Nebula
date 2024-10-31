<?php
//define the path to the .ttf files you want to use
define('FPDF_FONTPATH',$_SERVER['DOCUMENT_ROOT'].'/nebula/core/tfpdf/fonts/');

$pdf = new utf8graffaPDF();
$pdf->AddPage();

// Add Unicode fonts (.ttf files)
$fontName = 'DejaVu';
$pdf->AddFont($fontName,'','DejaVuSerif.ttf',true);
$pdf->AddFont($fontName,'B','DejaVuSans-Bold.ttf',true);

?>