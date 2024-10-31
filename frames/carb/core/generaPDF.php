<?php
error_reporting(E_ERROR | E_PARSE);
//error_reporting(0);

//importa le variabili $ditta , $conto , $filiale
include("carb_pdf_ini.php");
require("fpdf/fpdf.php");

$obj=(array)json_decode($_POST[values]);

//cancella il vecchio PDF
unlink("temp/buoni.pdf");

class PDF extends FPDF {
	//Page header
	function Header() {
		//Va a 0.5 cm dalla cima della pagina
	    //$this->SetY(5);
	    //Seleziona Arial corsivo 8
	    $this->SetFont('Arial','',8);
	    //Stampa il numero di pagina centrato
	    //$this->Cell(0,10,"A. Gabellini Srl",0,0,'L');
	}
	
	function Footer() {
	}
	
	function AcceptPageBreak() {
		$this->AddPage('P','A4');
		$this->Ln(10);
	}	
}

//------------------------------------------------------

$pdf=new PDF('P','mm','A4');

$pdf->AddPage();

//intestazione
$pdf->SetY(35);
$pdf->SetX(30);
$pdf->Cell(37,8,$ditta,0,0,'L');

//Operazione Richiesta
$pdf->SetY(53);
$pdf->SetX(25);
$pdf->Cell(75,8,$ditta,0,0,'L');

$pdf->SetY(76);
$pdf->SetX(25);
$pdf->Cell(8,8,$ditta,0,0,'L');

$pdf->SetY(59);
$pdf->SetX(30);
$pdf->Cell(15,8,$conto,0,0,'L');
$pdf->Cell(13);
$pdf->Cell(37,8,$filiale,0,0,'L');

$pdf->SetY(68.5);
$pdf->SetX(50);
$pdf->Cell(12,8,$conto,0,0,'L');
$pdf->Cell(10);
$pdf->Cell(23,8,iconv('UTF-8', 'windows-1252', '€ '.$obj[importo]),0,0,'R');
$pdf->SetY(138);
$pdf->SetX(109);
$pdf->Cell(20,8,$val[d],0,0,'L');

//modalità di regolamento
if ($val[sel]=='a') {
	$pdf->SetY(104);
	$pdf->SetX(162);
	$pdf->Cell(6,8,$val[num_ass],0,0,'L');
	$pdf->Cell(7);
	$pdf->Cell(23,8,iconv('UTF-8', 'windows-1252', '€ '.$val[importo]),0,0,'R');
}

if ($val[sel]=='c') {
	$pdf->SetY(100.5);
	$pdf->SetX(175);
	$pdf->Cell(23,8,iconv('UTF-8', 'windows-1252', '€ '.$val[importo]),0,0,'R');
}

//totale
$pdf->SetY(110);
$pdf->SetX(175);
$pdf->Cell(23,8,iconv('UTF-8', 'windows-1252', '€ '.$val[importo]),0,0,'R');



/*$pdf->SetY(170);
$pdf->SetX(120);
$pdf->MultiCell(70,8,json_encode($val),0,'L');*/



//$pdf->Output();

$pdf->Output("temp/buoni.pdf","F");

?>