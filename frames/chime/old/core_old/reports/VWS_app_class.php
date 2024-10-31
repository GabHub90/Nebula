<?php

include ("maestro.php");

class elemento {

	public $chsum;
	public $odl;
	public $data;
	public $intestazione;
	public $telaio;
	public $km;
	public $consegna;
	public $tel1="";
	public $tel2="";
	public $mail;
	
	public $lam=array();
	
	function __construct($odl,$data,$intestazione,$telaio,$km,$consegna,$tel1,$tel2,$mail,$datachk) {
		$this->chsum=$odl."_".$datachk;
		$this->odl=$odl;
		$this->data=$data;
		$this->intestazione=$intestazione;
		$this->telaio=$telaio;
		$this->km=$km;
		$this->consegna=$consegna;
		$this->tel1=$tel1;
		$this->tel2=$tel2;
		$this->mail=$mail;
	}
	
	function draw($ok) {
		echo '<table width="100%" style="border-collapse:collapse;">';
			echo '<colgroup>';
				echo '<col span="1" width="58%"/>';
				echo '<col span="1" width="16%"/>';
				echo '<col span="1" width="11%"/>';
				echo '<col span="1" width="15%"/>';
				
			echo '</colgroup>';
			echo '<tbody>';
				echo '<tr ';
				if ($ok==0) echo 'style="color:red;"';
				echo '>';
					echo '<td>'.$this->intestazione.'</td>';
					echo '<td style="font-size:10pt;">'.substr($this->telaio,6).'</td>';
					echo '<td style="font-size:10pt;">km:'.$this->km.'</td>';
					$cons=substr($this->consegna,6,2)."/".substr($this->consegna,4,2)."/".substr($this->consegna,0,4);
					echo '<td style="font-size:8pt;">C:'.$cons.'</td>';
				echo '</tr>';
				
				echo '<tr>';
					echo '<td colspan="4" style="font-size:8pt;">';
						if (count($this->lam)>0) {
							foreach ($this->lam as $l) {
								echo strtolower($l).' - ';
							}
						}
					echo '</td>';
				echo '</tr>';
				
			echo '</tbody>';
		echo '</table>';
		
		//restituisce i dati per una futura elaborazione JS e li attribuisce all'elemento CHIME_EL
		$datax=substr($this->data,6,2).'-'.substr($this->data,4,2).'-'.substr($this->data,0,4);
		//$orax=substr($this->dataora,11,5);
		echo '<script> x'.$this->chsum.'.dati={"intestazione":"'.$this->intestazione.'","telaio":"'.$this->telaio.'","km":"'.$this->km.'","consegna":"'.$this->consegna.'","data":"'.$datax.'","ora":"","odl":"'.$this->odl.'"}; </script>';
	}
}

class vw_app {

	private $maestro;

	public $elementi=array();
	public $data="";
	public $modo="";
	public $sms=array();
	
	public $tmp='';
	
	function __construct($report,$input,$data,$modo) {
		$this->data=$data;
		$this->modo=$modo;
		
		$this->maestro=new Maestro();
		
		//MYSQL
			/*trova elementi
			$q_data=substr($data,0,4)."-".substr($data,4,2)."-". substr($data,6,2);
			$query='SELECT odl,data,intestazione,telaio,km,consegna,tel1,tel2,mail FROM test_app WHERE DATE(data)="'.$q_data.'"';
			if($result=mysqli_query($query)) {
				while ($row=mysqli_fetch_array($result)) {
					$this->elementi[]=new elemento($row[odl],$row[data],$row[intestazione],$row[telaio],$row[km],$row[consegna],$row[tel1],$row[tel2],$row[mail],$data);
				}
			} */
			
		
		//MSSQL
			$q_data=substr($data,0,4)."-".substr($data,4,2)."-". substr($data,6,2);
			$lista=$this->maestro->get_prenotazioni($data,'PV');
			$lam='';
			if (count($lista)>0) {		
				$x=0;
				foreach ($lista as $row) {
					$this->elementi[$row[odl]]=new elemento($row[odl],$row[data],$row[intestazione],$row[telaio],$row[km],$row[consegna],$row[tel1],$row[tel2],$row[mail],$data);
					
					if ($x!=0) $lam.=",";
					$lam.="'".$row[odl]."'";
					$x++;
				}
			}
			
			//recupera i lamentati
			if ($lam!='') {
$this->tmp=$lam;
				$lista=$this->maestro->get_lamentati($lam);
				foreach ($lista as $key=>$l) {
					$this->elementi[$key]->lam=$l;
				}
			}
			
	}
	
	function intest() {
		echo '<div style="margin-bottom:10px;">';
			echo '<span style="position:relative;top:5px">';
			echo substr($this->data,6,2)."/".substr($this->data,4,2)."/".substr($this->data,0,4);
			echo '</span>';
		echo '</div>';
	}
	
	function numero_elementi() {
		//restituisce l'array delle chiavi degli elementi
		return array_keys($this->elementi);
	}
	
	function chsum($key) {
		return $this->elementi[$key]->chsum;
	}
	
	function d_elemento($key,$ok) {
		$this->elementi[$key]->draw($ok);
	}
	
	function telefoni($key) {
		return array($this->elementi[$key]->tel1,$this->elementi[$key]->tel2);
	}
	
	function mail($key) {
		return $this->elementi[$key]->mail;
	}
}

//inizializzaizone della variabile LISTA che punta all'oggetto
$lista=new vw_app($report,$input,$data,$modo);

?>