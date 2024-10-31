<?php

class nebulaCalendario {

	//codice reparto se ""=>"AZ"
	protected $reparto="AZ";
	//Record delle informazioni del reparto
	protected $repInfo=array();
	//Giorni di festa Annuali
	protected $festa= array();
	//giorni di chiusura annuali
	protected $chiusura= array();

	//anno corrente
	protected $anno;
	//mese corrente
	protected $mese;
	//array giorni mese corrente - viene valorizzato da MESE()
	//ogni giorno contiene anche l'array GRIGLIE per il caricamento degli eventi
	protected $giorni=array();

	protected $galileo;

	public $log=array();
	
	function __construct($anno,$galileo) {

		$this->galileo=$galileo;

		$this->anno=$anno;
		
		$this->galileo->getFeste($this->anno);
		$result=$this->galileo->getResult();
		if($result) {
			$fetID=$this->galileo->preFetchBase('calendario');
			while ($row=$this->galileo->getFetchBase('calendario',$fetID)) {
				$this->festa[$row['mese'].$row['giorno']]=$row;
			}
		}
			
		$pasqua=$this->pasqua($this->anno);
		$angelo=strtotime("+1 day",$pasqua);
		if (!array_key_exists(date("md",$pasqua),$this->festa)) {
			$this->festa[date("md",$pasqua)]=array('nome'=>'Pasqua','anno_i'=>$this->anno,'anno_f'=>$this->anno);
		}
		if (!array_key_exists(date("md",$angelo),$this->festa)) {
			$this->festa[date("md",$angelo)]=array('nome'=>'luned&igrave; di Pasqua','anno_i'=>$this->anno,'anno_f'=>$this->anno);
		}
		
	}

	function getGiorni() {
		return $this->giorni;
	}

	function getFeste() {
		return $this->festa;
	}

	function getChiusure() {
		return $this->chiusura;
	}
	
	function pasqua($year) {
	
		$aM = array(22, 22, 23, 23, 24, 24);
    	$aN = array(2, 2, 3, 4, 5, 5);
	
		$a = $year % 19;
        $b = $year % 4;
        $c = $year % 7;
 
        $aIndex= floor($year/100)-15;
 
        $d = (19 * $a + $aM[$aIndex]) % 30;
        $e = (2 * $b + 4 * $c + 6 * $d + $aN[$aIndex]) % 7;
 
        $day = 22 + $d + $e;
        $month = 3;
 
        if ($day > 31) {
            $month = 4;
            $day -= 31;
        }
 
        /**
         * Eccezioni:
         * - Se la data risultante dalla formula è il 26 aprile, 
         *   allora la Pasqua cadrà il giorno 19 aprile;
         * - Se la data risultante dalla formula è il 25 aprile 
         *   e contemporaneamente d = 28, e = 6 e a > 10, 
         *   allora la Pasqua cadrà il 18 aprile. 
         */
        if ($month == 4 && $day == 26) {
            $day = 19;
        } elseif ($month == 4 && $day == 26 && $d == 28 && $e == 6 && $a > 10) {
            $day = 18;
        } 
 
        //return array('day'=>$day,'month'=>$month);
        return mktime(0,0,0,$month,$day,$year);
	}

	function setReparto($reparto) {

		if ($reparto=="") $this->reparto='AZ';
		else $this->reparto=$reparto;

		$this->galileo->getReparto($this->reparto);
		$result=$this->galileo->getResult();
		if($result) {

			$fetID=$this->galileo->preFetchBase('reparti');
			while ($row=$this->galileo->getFetchBase('reparti',$fetID)) {
				$this->repInfo=$row;
			}

			$this->chiusura=array();

			$this->calcolaChiusure();
		}
	}

	function calcolaChiusure() {

		$this->galileo->getChiusure($this->anno);
		$result=$this->galileo->getResult();
		if($result) {
			$fetID=$this->galileo->preFetchBase('calendario');
			while ($row=$this->galileo->getFetchBase('calendario',$fetID)) {

				//if (is_null($row['turni'])) $row['turni']="";
				if (is_null($row['rep_exc'])) $row['rep_exc']="";
				
				//if ($this->mese!=$row['mese']) continue;

				//se la chiusura riguarda il reparto, o l'azienda intera
				//if ($row['reparto']=='AZ' || $row['reparto']==$this->reparto || $row['reparto']==$this->repInfo['tipo']) {
				if ($row['reparto']=='AZ' || $row['reparto']==$this->reparto) {

					//se il reparto non è tra quelli esclusi (DA NOTARE LA VIRGOLA PER EVITARE FALSI POSITIVI)
					if (preg_match('/'.$this->reparto.',/',$row['rep_exc'])===false || preg_match('/'.$this->reparto.',/',$row['rep_exc'])==0) {

						//SE ESISTE GIÀ UNA CHIUSURA PER QUEL GIORNO
						if (array_key_exists($row['mese'].$row['giorno'], $this->chiusura)) {

							//se reparto corrisponde sovrascrivi e passa oltre
							if ($row['reparto']==$this->reparto) {
								$this->chiusura[$row['mese'].$row['giorno']]=$row;
								$this->chiusura[$row['mese'].$row['giorno']]['parent']="";
								continue;
							}

							//se la registrazione esistente era AZ sovrascrivila sempre
							if ($this->chiusura[$row['mese'].$row['giorno']]['parent']=='AZ') {
								$this->chiusura[$row['mese'].$row['giorno']]=$row;
								$this->chiusura[$row['mese'].$row['giorno']]['parent']=$row['reparto'];
							}
							//se il record NON è di AZ (ed a questo punto parent è il macroreparto o "")
							elseif ($row['reparto']!='AZ' && $row['reparto']!='') {
								$this->chiusura[$row['mese'].$row['giorno']]=$row;
								$this->chiusura[$row['mese'].$row['giorno']]['parent']=$row['reparto'];
							}
						}
						else {
							$this->chiusura[$row['mese'].$row['giorno']]=$row;
							if ($row['reparto']=$this->reparto) $this->chiusura[$row['mese'].$row['giorno']]['parent']="";
							else $this->chiusura[$row['mese'].$row['giorno']]['parent']=$row['reparto'];
						}
					}
				}
			}
		}
	}

	function newDay($index,$d) {

		$day=array("tag"=>date('Ymd',$index),"wd"=>$d,"festa"=>0,"chiusura"=>0,"chi"=>array(),"testo"=>"");
				
		$md=date("md",$index);
		if(array_key_exists($md,$this->festa)) {
			$day["festa"]=1;
			$day["testo"]=$this->festa[$md]['nome'];
		}
		else {
			if(array_key_exists($md, $this->chiusura)) {
				$day["testo"]=$this->chiusura[$md]["nome"];
				//$day["chiusura"]=$this->chiusura[$md]["tipo"];
				$day["chiusura"]=1;
				$day["chi"]=$this->chiusura[$md];
			}
		}
		
		return $day;
	}

	function mese($mese) {
		//fornisce la griglia di un mese
	
		//deve essere fornito $mese con lo 0 davanti
		$this->mese=$mese;
		
		//$this->calcola_chiusure($mese);
		
		//---------------------definisci tabella -------------------------------------------------------
		//$res=array();
	
		//calcolo primo giorno del quadrante (TIMESTAMP)
		$time=mktime(0,0,0,(int)$mese,1,$this->anno);
		$weekday=date("w",$time);
		$index=strtotime("-".$weekday." day",$time);
	
		$am=$this->anno.$mese;
		$check=$am;
		$sett=(int)date("W",$index);
		
		while ($check==$am) {
			
			//definizione giorno
			for($d=0;$d<=6;$d++) {

				$day=$this->newDay($index,$d);
							
				//array delle caratteristiche dei giorni
				$day['griglie']=array();
				$this->giorni[$sett][$d]=$day;
				
				$index=strtotime("+1 day",$index);
			}
			
			$check=date("Ym",$index);
			$sett++;
		}
		
		return $this->giorni;
	}

	function getLimitiPeriodo($d,$periodo) {
		//(settimana,quindicina,mese,bimestre,trimestre,quadrimestre,semestre,anno)

		$limiti=array();
		$ts=mainFunc::gab_tots($d);
		$w=date('w',$ts);
		$day=(int)substr($d,6,2);

		switch ($periodo) {

			case 'mese':
				$limiti=array(
					array (
						"inizio"=>substr($d,0,6)."01",
						"fine"=>date(substr($d,0,6)."t",$ts)
					)
				);
			break;

			case 'anno':
				$limiti=array(
					array (
						"inizio"=>$this->anno."0101",
						"fine"=>$this->anno."1231"
					)
				);
			break;

			case 'semestre':
				$limiti=array(
					array(
						"inizio"=>$this->anno."0101",
						"fine"=>$this->anno."0630"
					),
					array(
						"inizio"=>$this->anno."0701",
						"fine"=>$this->anno."1231"
					)
				);
			break;

			case 'quadrimestre':
				$limiti=array(
					array(
						"inizio"=>$this->anno."0101",
						"fine"=>$this->anno."0430"
					),
					array(
						"inizio"=>$this->anno."0501",
						"fine"=>$this->anno."0831"
					),
					array(
						"inizio"=>$this->anno."0701",
						"fine"=>$this->anno."1231"
					)
				);
			break;

			case 'trimestre':
				$limiti=array(
					array(
						"inizio"=>$this->anno."0101",
						"fine"=>$this->anno."0331"
					),
					array(
						"inizio"=>$this->anno."0401",
						"fine"=>$this->anno."0630"
					),
					array(
						"inizio"=>$this->anno."0701",
						"fine"=>$this->anno."0930"
					),
					array(
						"inizio"=>$this->anno."1001",
						"fine"=>$this->anno."1231"
					)
				);
			break;

			case 'bimestre':
				$limiti=array(
					array(
						"inizio"=>$this->anno."0101",
						"fine"=>date($this->anno."02t",mainFunc::gab_tots($this->anno."0201")),
					),
					array(
						"inizio"=>$this->anno."0301",
						"fine"=>$this->anno."0430"
					),
					array(
						"inizio"=>$this->anno."0501",
						"fine"=>$this->anno."0630"
					),
					array(
						"inizio"=>$this->anno."0701",
						"fine"=>$this->anno."0831"
					),
					array(
						"inizio"=>$this->anno."0901",
						"fine"=>$this->anno."1031"
					),
					array(
						"inizio"=>$this->anno."1101",
						"fine"=>$this->anno."1231"
					)
				);
			break;

			case 'quindicina':
				if ($day<=15) {
					$limiti=array(
						"inizio"=>substr($d,0,6)."01",
						"fine"=>substr($d,0,6)."15"
					);
				}
				else {
					$limiti=array(
						"inizio"=>substr($d,0,6)."16",
						"fine"=>date(substr($d,0,6)."t",$ts)
					);
				}
			break;

			case 'settimana':
					$limiti=array(
						"inizio"=>date("Ymd",strtotime("-".$w." day",$ts) ),
						"fine"=>date("Ymd",strtotime("+".(6-$w).' day',$ts) )
					);
			break;
		}

		////////////////////////////////////
		foreach ($limiti as $l) {
			if ($d>=$l['inizio'] && $d<=$l['fine']) {
				return $l;
			}
		}

		return array();
	}

	function checkDay($tag,$reparto) {
		//ritorna l'array del giorno con in più il campo "chk"
		//  OK:     OK
    	//  FS:     Festivo
    	//  CH:     Chiusura totale
    	//  CHK:    Da verificare (chiusura parziale o con esclusione di reparti)

		$mg=substr($tag,4,4);

		$ts=mainFunc::gab_tots($tag);

		$ret=$this->newDay($ts,date('w',$ts));
		$ret['chk']='OK';

		if (array_key_exists( $mg , $this->festa)) {
			$ret['chk']="FS";
		}

		elseif (array_key_exists( $mg , $this->chiusura)) {
			//aggiunto 25/06/2024
			if ($this->chiusura[$mg]['reparto']=='AZ' || $this->chiusura[$mg]['reparto']==$reparto) {

				if ($this->chiusura[$mg]['tipo']=='T') $ret['chk']="CH";
				else $ret['chk']="CHK";
			}
		}

		return $ret;
	}

	function verificaLimitiChiusura($tag,$orario) {

		//leggere orario del giorno della settimana dal DB
		//confrontare se è interamente incluso nella chiusura
		//se si => CH
		//se no => OK

		$mg=substr($tag,4,4);
		//se la chiusura NON esiste ritorna OK
		if (!array_key_exists($mg,$this->chiusura)) return 'OK';

		$this->galileo->getOrario("'".$orario."'",date('w',mainFunc::gab_tots($tag)));
		$result=$this->galileo->getResult();
		if($result) {
			$fetID=$this->galileo->preFetchBase('schemi');
			while ($row=$this->galileo->getFetchBase('schemi',$fetID)) {
				try {
					$orobj=json_decode($row['orari'],true);
				}catch(Exception $e) {
					$orobj=array();
				}
			}
		}
		else return 'CH';

		$chk=true;

		foreach ($orobj as $o) {
			if ($o['i']=='00:00' && $o['f']=='00:00') $chk=false;
			elseif ($o['i']<$this->chiusura[$mg]['ora_i'] || $o['f']>$this->chiusura[$mg]['ora_f']) $chk=false;
		}

		if ($chk) return 'OK';
		else return 'CH';
	}

	function setGrid($indice) {
		foreach($this->giorni as $sett=>$s) {
			foreach ($s as $k=>$v) {
				$this->giorni[$sett][$k]['griglie'][$indice]=array();
			}
		}
	}

	function insertGroupInGrid($index,$da,$a,$arr,$esclusioni) {
		//esclusioni è un array di TAG che non devono essere considerati

		$pointer="";

		foreach ($this->giorni as $sett=>$s) {

			$line=0;
			$tempry=array();
			
			foreach ($s as $k=>$v) {
				$pointer=$v['tag'];
				if ($pointer>$a) break;
				if ($pointer<$da) continue;

				if ( in_array($v['tag'],$esclusioni) ) continue;

				while ( isset($this->giorni[$sett][$k]['griglie'][$index][$line]) ) {
					$line++;
				}

				$tempry[]=$k;
			}

			foreach ($tempry as $ky) {
				$this->giorni[$sett][$ky]['griglie'][$index][$line]=$arr;
			}

			if ($pointer>$a) break;
		}
	}

	function setDayLine($i,$f,$arr) {

		//$arr è l'array da rimpolpare

		$actual=mainFunc::gab_tots($i);
		$end=mainFunc::gab_tots($f);

		while ($actual<=$end) {

			$index=date('Ymd',$actual);
			$d=date('w',$actual);
			$arr[$index]=$this->newday($index,$d);
			$actual=strtotime("+1 day",$actual);
		}

		return $arr;

	}
	
}

?>