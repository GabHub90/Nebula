<?php
require_once($_SERVER['DOCUMENT_ROOT']."/nebula/core/tfpdf/tfpdf.php");

class utf8graffaPDF extends tFPDF {

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

?>