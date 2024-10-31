<?php
require_once($_SERVER['DOCUMENT_ROOT']."/nebula/core/fpdf/fpdf.php");

class comestPDF extends FPDF {

	function AcceptPageBreak() {
		$this->AddPage('P','A4');
		$this->Ln(5);
	}

	//Page header
	function Header() {		
	}
	
	function Footer() {
	}

	function drawCommessa($com,$rev,$bozza) {

		$this->SetAutoPageBreak(true, 5);

		$this->SetFont('Arial','B',15);

		$this->Cell(0,0,'Commissione lavori ad azienda esterna n. '.$com['rif'],0,2,'L',false);

		if ($bozza) {

			$this->SetFont('Arial','B',20);

			$this->SetX(150);

			$this->Cell(90,0,'BOZZA',0,2,'L',false);
		}

		$this->SetFont('Arial','B',12);

		$this->SetY(18);

		$this->Cell(50,0,'Service Partner',0,0,'L',false);

		$this->SetY(18);
		$this->SetX(65);

		$this->Cell(90,0,'Azienda Esterna',0,2,'L',false);

		$this->SetFont('Arial','',12);

		$this->SetY(22);

		$txt="Augusto Gabellini Srl\nStr. Romagna, 119\n61121 Pesaro (PU)";

		$this->MultiCell(50,6,$txt,'LRTB','L',0);

		$this->SetY(22);
		$this->SetX(65);

		if (isset($com['fornitore']['ragsoc'])) {
			$txt=$com['fornitore']['ragsoc'].PHP_EOL.$com['fornitore']['indirizzo'].PHP_EOL.$com['fornitore']['mail'].PHP_EOL;
			if ($com['fornitore']['tel1']!="") {
				$txt.=$com['fornitore']['tel1'];
				if ($com['fornitore']['nota1']!="") $txt.=' ('.$com['fornitore']['nota1'].')';
				$txt.=' - ';
			}
			if ($com['fornitore']['tel2']!="") {
				$txt.=$com['fornitore']['tel2'];
				if ($com['fornitore']['nota2']!="") $txt.=' ('.$com['fornitore']['nota2'].')';
			}
		}
		else $txt="";

		$this->MultiCell(130,6,$txt,'LRTB','L',0);

		$this->Ln(0);

		$this->SetY(50);
		//$this->SetX(0);

		$this->SetFont('Arial','B',12);

		$this->Cell(0,6,iconv('UTF-8', 'windows-1252', "Il Service Partner impartisce all’azienda commissionata il seguente intervento*"),0,2,'L',false);

		$this->SetFont('Arial','',10);

		$this->Cell(0,6,iconv('UTF-8', 'windows-1252', "* Ha validità quanto indicato nella lettera d’accordi in essere"),0,2,'L',false);

		$this->Ln(2);
		$this->SetFont('Arial','',12);

		$this->Cell(50,6,'Targa: '.$com['targa'],0,0,'L',false);
		$this->Cell(130,6,'Telaio: '.$com['telaio'],0,2,'L',false);

		$this->Ln(1);

		$this->Cell(130,6,'Modello: '.substr(iconv('UTF-8', 'windows-1252',$com['descrizione']),0,50),0,0,'L',false);
		$this->Cell(60,6,'Ordine: '.$com['odl'],0,2,'L',false);

		/*$this->Ln(1);
		$this->SetFont('Arial','B',12);

		$this->Cell(0,6,'Lavorazioni:',0,2,'L',false);
		$this->Ln(0);*/

		foreach ($rev['righe'] as $k=>$r) {
			$this->Ln(2);
			//$this->SetFont('Arial','B',12);
			//$this->Cell(0,6,$r['titolo'],0,2,'L',false);
			//$this->Ln(0);
			$this->SetFont('Arial','',12);
			$this->MultiCell(190,6,iconv('UTF-8', 'windows-1252',strtoupper($r['titolo']).' - '.strtolower($r['descrizione'])),'','L',0);
		}

		if (!$bozza) {

			//$this->MultiCell(190,6,json_encode($rev),'','L',0);
		
			$this->Ln(2);
			$this->Cell(0,6,'','T',2,'L',false);

			$this->SetFont('Arial','',12);
			$this->Cell(30,6,'Preventivo:',0,0,'L',false);
			$this->SetFont('Arial','B',13);
			$this->Cell(40,6,number_format($rev['preventivo'],2,',',''),0,0,'L',false);
			$this->SetFont('Arial','',12);
			$this->Cell(30,6,'Riconsegna:',0,0,'L',false);
			$this->SetFont('Arial','B',13);
			$this->Cell(40,6,mainFunc::gab_todata($rev['riconsegna']),0,2,'L',false);

			$this->Ln(2);

			$this->SetFont('Arial','',12);
			$this->Cell(30,6,'Data:',0,0,'L',false);
			$this->SetFont('Arial','B',13);
			$this->Cell(40,6,mainFunc::gab_todata($rev['d_chiusura']),0,0,'L',false);
			$this->SetFont('Arial','',12);
			$this->Cell(30,6,'Utente:',0,0,'L',false);
			$this->SetFont('Arial','B',13);
			$this->Cell(40,6,$rev['utente_chiusura'],0,2,'L',false);

			if ($rev['nota']!='') {
				$this->Ln(2);
				$this->SetFont('Arial','',12);
				$this->MultiCell(190,6,iconv('UTF-8', 'windows-1252','NOTA - '.$rev['nota']),'','L',0);
			}

			//////////////////////////////////////////////////////////////

			$x=75;
			$y=-54;

			$this->SetFont('Arial','',9);

			$this->SetY($y);
			$this->Ln(0);

			$txt="Azienda commissionata\nLa commissione è stata eseguita come da ordine. Controllo di qualità effettuato.\nData e Firma\n\n\n\n";

			$this->MultiCell(60,6,iconv('UTF-8', 'windows-1252',$txt),'LRTB','L',0);

			$this->SetFont('Arial','B',9);

			$this->SetY($y);
			$this->SetX($x);

			$this->Cell(0,6,'Controllo Finale Service Partner:',0,2,'L',false);

			$this->SetFont('Arial','',9);

			$y+=6;

			if (isset($com['controllo'])) {

				if ($a=json_decode($com['controllo'],true)) {

					foreach ($a as $k=>$c) {

						$this->SetY($y);
						$this->SetX($x);

						$this->Cell(70,6,$c['titolo'],0,0,'L',false);

						foreach ($c['opzioni'] as $ko=>$o) {

							$img=($o==$c['valore']?$_SERVER['DOCUMENT_ROOT'].'/nebula/apps/comest/img/si.jpeg':$_SERVER['DOCUMENT_ROOT'].'/nebula/apps/comest/img/no.jpeg');

							$this->Cell(5,6, $this->Image($img, $this->GetX(), $this->GetY()+1, 4), 0, 0, 'L', false );
							$this->Cell(8,6,$o,0,0,'L',false);
						}

						$y+=6;
					}
				}
			}

			$y+=6;

			$this->SetY($y);
			$this->SetX($x);

			$this->Cell(50,6,'Data: '.($com['d_controllo']!=""?mainFunc::gab_todata($com['d_controllo']):''),0,0,'L',false);

			$this->Cell(100,6,'Utente: '.($com['utente_controllo']!=""?$com['utente_controllo']:''),0,0,'L',false);
		}

	}

	function exportCommessa_b64() {
		return base64_encode($this->Output('pdf','S'));
	}

}

?>