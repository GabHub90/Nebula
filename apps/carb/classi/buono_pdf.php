<?php
require_once($_SERVER['DOCUMENT_ROOT']."/nebula/core/fpdf/fpdf.php");

class carbBuonoPDF extends FPDF {

	//Page header
	function Header() {
		
	}
	
	function Footer() {
	}
	
	function AcceptPageBreak() {
		$this->AddPage('P','A4');
		$this->Ln(10);
	}

	function nebulaBuono($param,$collaboratori,$causali,$carburanti) {

		$count=0;

		while ($count<2) {

			$this->SetFont('Arial','',15);

			$this->Cell(0,0,'AugustoGabellini S.r.l.',0,2,'L',false);

			$this->Ln(5);

			$this->Cell(0,0,'Buono carburante N. '.$param['ID'].' del '.mainFunc::gab_todata($param['d_stampa']),0,2,'L',false);

			$this->Ln(5);

			$this->Cell(0,0,'Emesso per la vettura: '.$param['telaio'].' ('.$param['targa'].')',0,2,'L',false);

			$this->Ln(8);

			$this->Cell(0,0,'Nota: '.$param['nota'],0,2,'L',false);

			$this->Ln(8);

			$this->SetFontSize(12);
			$this->Cell(25,0,'Operatore:',0,0,'L',false);
			$this->SetFontSize(13);
			if (isset($collaboratori[$param['id_esec']])){
				$this->Cell(70,0,$collaboratori[$param['id_esec']]['cognome'].' '.$collaboratori[$param['id_esec']]['nome'],0,0,'L',false);
			}
			else {
				$this->Cell(70,0,'Errore Operatore',0,0,'L',false);
			}
			$this->SetFontSize(12);
			$this->Cell(25,0,'Richiedente:',0,0,'L',false);
			$this->SetFontSize(13);
			if (isset($collaboratori[$param['id_rich']])){
				$this->Cell(70,0,$collaboratori[$param['id_rich']]['cognome'].' '.$collaboratori[$param['id_rich']]['nome'],0,0,'L',false);
			}
			else {
				$this->Cell(70,0,'Errore Richiedente',0,0,'L',false);
			}

			$this->Ln(5);

			$this->SetFontSize(12);
			$this->Cell(25,0,'Reparto:',0,0,'L',false);
			$this->SetFontSize(13);
			$this->Cell(70,0,$param['reparto'],0,0,'L',false);
			$this->SetFontSize(12);
			$this->Cell(25,0,'Firma:',0,0,'L',false);

			$this->Ln(5);

			$this->SetFontSize(12);
			$this->Cell(25,0,'Causale:',0,0,'L',false);
			$this->SetFontSize(13);
			$this->Cell(70,0,(isset($causali[$param['causale']])?$causali[$param['causale']]['causale']:'Errore causale'),0,0,'L',false);
			$this->SetFontSize(12);
			$this->Cell(95,0,'________________________________',0,0,'L',false);
			
			$this->Ln(5);

			$this->SetFontSize(12);
			$this->Cell(25,0,'Carburante:',0,0,'L',false);
			$this->SetFontSize(13);
			$this->Cell(70,0,(isset($carburanti[$param['tipo_carb']])?$carburanti[$param['tipo_carb']]:"Errore Carburante"),0,0,'L',false);
			
			$this->Ln(5);

			if ($param['pieno']=="1") {

				$this->SetFontSize(12);
				$this->Cell(50,0,'Importo:',0,0,'L',false);
				$this->Cell(50,0,'______________ euro',0,0,'L',false);
				$this->Cell(20,0,'(pieno)',0,0,'L',false);
			}

			else {
				$this->SetFontSize(12);
				$this->Cell(50,0,'Importo:',0,0,'L',false);
				$this->SetFont('Arial','B',15);
				$this->Cell(50,0,number_format($param['importo'],2,',','').' euro',0,0,'L',false);
				$this->SetFont('Arial','',12);
			}

			$this->Ln(10);

			$this->Cell(0,0,'-------------------------------------------------------------------------------------------------------------------------------------',0,2,'L',false);

			$this->Ln(10);

			$count++;
		}

	}
}

?>