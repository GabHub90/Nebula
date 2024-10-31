<?php

//use FFI\CType;

include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
//define('FPDF_FONTPATH',$_SERVER['DOCUMENT_ROOT'].'/nebula/core/tfpdf/fonts/');
include($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/gab500/galileo_gdm.php");
//require($_SERVER['DOCUMENT_ROOT'].'/nebula/core/tfpdf/utf8graffa.php');
require_once($_SERVER['DOCUMENT_ROOT']."/nebula/core/fpdf/fpdf.php");

//inizializza l'oggetto $pdf
//include($_SERVER['DOCUMENT_ROOT'].'/nebula/core/tfpdf/init.php');
//$pdf = new utf8graffaPDF();
$pdf= new FPDF();
//$fontName = 'DejaVu';
//$pdf->AddFont($fontName,'','DejaVuSerif.ttf',true);
//$pdf->AddFont($fontName,'B','DejaVuSans-Bold.ttf',true);

$pdf->AddPage('P','A4');

//$pdf->SetFont($fontName,'',15);
$pdf->SetFont('Arial','B',15);

//$pdf->SetLineWidth(1);
 
$param=$_POST['param'];

//[{"magazzino":"01","precodice":"V","articolo":"ZTS205556VPZ00","descr_articolo":"205\/55R16 91V CINTURATO P","giacenza":"30.000","dispo":"30.000","listino":"159.0000000","qta":"4","des_marca":"Pirelli","scontoAC":"54","sconto":"0.41"}]
//{"qta":"4","pneumatici":"375.24000000000007","montaggio":"14.00","tasse":"2.50","totale":"441.24000000000007"}

$pdf->SetXY(10,10);

$delta=10;
$line=5;

$pdf->Cell(128,$line,"Pneumatici:",0,1,"L");

foreach ($param['gomme'] as$k=>$g) {

    $pdf->SetFont('Arial','',15);

    $y=(10+$line)+$delta*($k+1);

    $pdf->SetXY(10,$y);

    $pdf->Cell(10,$line,$g['qta'].' x',0,0,'L');

    $pdf->SetXY(30,$y);

    $pdf->Cell(100,$line,$g['articolo'].' - '.$g['des_marca'],0,0,'L');

    $pdf->SetXY(130,$y);

    $pdf->Cell(20,$line,number_format($g['qta']*$g['listino'],2,',',''),0,0,'R');

    $pdf->SetXY(160,$y);

    $pdf->SetFont('Arial','',12);
    
    $pdf->Cell(20,$line,'('.number_format($g['sconto']*100,0,'','').'%)',0,0,'L');

    $pdf->SetXY(30,$y+$line);
    
    $pdf->Cell(100,$line,substr($g['descr_articolo'],0,35),0,0,'L');

    $pdf->SetXY(130,$y+$line);

    $pdf->Cell(20,$line,'listino',0,0,'R');

    $pdf->SetXY(160,$y+$line);
    
    $pdf->Cell(20,$line,'sconto',0,0,'L');

}

$pos=$y+$line*3;

$pdf->SetFont('Arial','B',15);

$pdf->SetXY(10,$pos);

$pdf->Cell(10,$line,'Montaggio:',0,1,'L');

$pos+=$line*2;

$pdf->SetFont('Arial','',15);

$pdf->SetXY(10,$pos);

$pdf->Cell(10,$line,$param['importo']['qta'].' x',0,0,'L');

$pdf->SetXY(20,$pos);

$pdf->Cell(20,$line,number_format($param['importo']['montaggio'],2,',',''),0,0,'R');

$pdf->SetXY(130,$pos);

$pdf->Cell(20,$line,number_format($param['importo']['qta']*$param['importo']['montaggio'],2,',',''),0,0,'R');

$pos+=$line;

$pdf->SetXY(10,$pos);

$pdf->Cell(10,$line,$param['importo']['qta'].' x',0,0,'L');

$pdf->SetXY(20,$pos);

$pdf->Cell(20,$line,number_format($param['importo']['tasse'],2,',',''),0,0,'R');

$pdf->SetXY(130,$pos);

$pdf->Cell(20,$line,number_format($param['importo']['qta']*$param['importo']['tasse'],2,',',''),0,0,'R');

$pos+=$line*2;

$pdf->SetFillColor(230, 230, 230);  // Grigio chiaro

// Rettangolo riempito, senza bordo
$pdf->Rect(5, $pos-1, 180, ($line*2)+1, 'FD');

$pdf->SetXY(10,$pos);

$pdf->SetFont('Arial','B',15);

$pdf->Cell(10,$line,'Totale',0,0,'L');

$pdf->SetXY(120,$pos);

$pdf->SetFont('Arial','',15);

$tot=$param['importo']['pneumatici']+($param['importo']['qta']*($param['importo']['montaggio']+$param['importo']['tasse']));

$pdf->Cell(30,$line,number_format($tot,2,',',''),0,0,'R');

$pdf->SetXY(150,$pos);

$pdf->SetFont('Arial','B',15);

$pdf->Cell(30,$line,number_format($tot*1.22,2,',',''),0,0,'R');

$pos+=$line;

$pdf->SetXY(150,$pos);

$pdf->SetFont('Arial','B',12);

$pdf->Cell(30,$line,'iva comp.',0,0,'R');
        
/*$pdf->Cell(128,10,iconv('UTF-8', 'windows-1252',"gatto €"),0,1,"C");

$pdf->Cell(128,10,mb_convert_encoding("canè", "UTF-8", "ISO-8859-1"),0,1,"C");
*/

echo base64_encode($pdf->Output('pdf','S'));

//echo base64_encode(json_encode($param['gomme']));
//echo base64_encode(json_encode($param['importo']));

?>