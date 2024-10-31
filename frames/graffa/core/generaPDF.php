<?php
error_reporting(E_ERROR | E_PARSE);
//error_reporting(0);

include('../graffa_func.php');
require("fpdf/fpdf.php");

$val=(array)json_decode($_POST['obj']);

//cancella il vecchio PDF
unlink("temp/graffa.pdf");

class PDF extends FPDF {
	//Page header
	function Header() {
		//Va a 0.5 cm dalla cima della pagina
	    //$this->SetY(5);
	    //Seleziona Arial corsivo 8
	    //$this->SetFont('Arial','B',15);
	    //Stampa il numero di pagina centrato
	    //$this->Cell(0,10,"A. Gabellini Srl",0,0,'L');
	    
	    //disegna immagine di sfondo
	    //$this->Image('../img/bonifico.jpeg',5,5,200,287,"jpeg");
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

foreach ($val as $v) {


	$pdf->AddPage();
	$pdf->SetFont('Arial','B',15);
	
	$pdf->SetY(35);
	$pdf->SetX(30);
	$pdf->Cell(150,8,"Appuntamento: ".db_todata($v->pren)." - ".($v->hh<10?"0".$v->hh:$v->hh).":".($v->mm<10?"0".$v->mm:$v->mm),0,0,'L');
	$pdf->ln(10);
	$pdf->SetX(30);
	
	/*calcola riconsegna
	$delta=-1;
	if ($v->ricon!="") {
		$delta=delta_tempo ($v->pren,$v->ricon,"g");
	} 
	if ($delta==-1 || $delta>1) $temp=db_todata($v->ricon);
	else {
		if ($v->pren==date('Ymd')) {
			if ($delta==0) $temp='OGGI';
			if ($delta==1) $temp='DOMANI';
		}
		else {
			if ($delta==0) $temp='IN GIORNATA';
			if ($delta==1) $temp='GIORNO DOPO';
		}
	}*/	
	
	$pdf->Cell(150,8,"Riconsegna: ".db_todata($v->ricon)." - ".($v->hr<10?"0".$v->hr:$v->hr).":".($v->mr<10?"0".$v->mr:$v->mr)." (trasporto: ".$v->trasporto." )",0,0,'L');

	$pdf->ln(10);
	$pdf->SetX(30);
	$pdf->Cell(150,8,$v->odl." - ".$v->util,0,0,'L');
	$pdf->ln(10);
	$pdf->SetX(30);
	$pdf->Cell(150,8,$v->targa." - ".$v->telaio,0,0,'L');
	$pdf->ln(10);
	$pdf->SetX(30);
	$pdf->SetFont('Arial','',15);
	$pdf->Cell(150,8,$v->des,0,0,'L');
	$pdf->ln(20);
	$pdf->SetX(30);
	$pdf->MultiCell(150,8,iconv('UTF-8', 'windows-1252',str_replace('<br/>',"\n".'- ','- '.$v->lams)),0,'L');
	
	/*$pdf->SetY(170);
	$pdf->SetX(120);
	$pdf->MultiCell(70,8,json_encode($v),0,'L');*/
	
}



//$pdf->Output();

$pdf->Output("temp/graffa.pdf","F");

//echo json_encode($v);

?>