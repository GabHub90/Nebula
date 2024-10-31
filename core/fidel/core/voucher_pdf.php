<?php
require_once($_SERVER['DOCUMENT_ROOT']."/nebula/core/fpdf/fpdf.php");

class fidelPDF extends FPDF {

	protected $param=array(
		"tag"=>"",
		"ben1"=>"",
		"ben2"=>""
	);

	function loadParam($param) {

		foreach ($this->param as $k=>$o) {
            if (array_key_exists($k,$param)) {
                $this->param[$k]=$param[$k];
            }
        }
	}

	function AcceptPageBreak() {
		$this->AddPage('P','A4');
		$this->Ln(5);
	}

	//Page header
	function Header() {
        $this->SetFont('Arial','B',15);
        $this->Cell(120,8,mb_convert_encoding('Gabellini per te', "ISO-8859-1", "UTF-8"),0,1,'L');
		$this->SetFont('Arial','',12);
		$this->Write(10,'Riferimento: ');
		$this->SetFont('Arial','B',12);
		$this->Write(10,$this->param['tag']);
		$this->SetFont('Arial','',12);
		$this->Write(10,' - ');
		$this->Write(10,substr($this->param['ben1'],0,35));
		$this->Ln(5);
		$this->Write(10,$this->param['ben2']);
        $this->Ln(10);
		$this->Write(10,'Voucher attivi al: '.date('d/m/Y'));
    }
	
	function Footer() {
	}

	function drawVoucher($v) {

		$this->Ln(5);
		$this->Cell(180,5,'','B',0,'L');
		$this->Ln(10);
		$this->SetFont('Arial','',12);
		$this->Cell(80,5,'Numero: '.$v['ID'].' del '.mainFunc::gab_todata($v['creazione']). ' ('.$v['utente'].')',0,0,'L');
		$this->Cell(80,5,'Scadenza: '.mainFunc::gab_todata($v['scadenza']),0,1,'R');
		$this->SetFont('Arial','B',15);
		$this->Write(12,$v['titolo']. ' - '.$v['offerta']);
		$this->Ln(8);
		$this->SetFont('Arial','',12);
		$this->Write(12,$v['nota']);
		$this->Ln(10);
		$this->Cell(180,5,'','T',0,'L');
		$this->Ln(5);
	}

	function exportCommessa_b64() {
		return base64_encode($this->Output('pdf','S'));
	}

}

?>