<?php
class calendario {

	public $festa= array();
	public $chiusura= array();
	public $anno=0;
	
	function __construct($db,$anno) {
	
		$this->anno=$anno;
		
		//FESTIVITÀ
		//mysqli_select_db("calendario");
		
		$query="SELECT * FROM CALENDARIO_feste WHERE attivo=1 AND active_data>='".(int)$anno."' ORDER BY mese ASC, giorno ASC";
		if($result=sqlsrv_query($db,$query)) {
			while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$this->festa[$row[mese].$row[giorno]]=$row[nome];
			}
		}
		
		$pasqua=$this->pasqua($anno);
		$angelo=strtotime("+1 day",$pasqua);
		if (!array_key_exists(date("md",$angelo),$this->festa)) {
			$this->festa[date("md",$angelo)]='luned&igrave; di Pasqua';
		}
		
		//CHIUSURA
		$query="SELECT * FROM CALENDARIO_chiusure WHERE anno='".$anno."' ORDER BY mese ASC, giorno ASC";
		if($result=sqlsrv_query($db,$query)) {
			while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$this->chiusura[$row[mese].$row[giorno]]=array("nome" =>$row[nome],"tipo" => $row[tipo]);
			}
		}
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
	
	function mese($mese) {
	
		//deve essere fornito $mese con lo 0 davanti
	
		$res=array();
	
		//calcolo primo giorno del quadrante (TIMESTAMP)
		$time=mktime(0,0,0,(int)$mese,1,$this->anno);
		$weekday=date("w",$time);
		$index=strtotime("-".$weekday." day",$time);
	
		$am=$this->anno.$mese;
		$check=$am;
		$i=0;
	
		while ($check==$am) {
			$i++;
			switch ($i) {
				case 1: $riga="a"; break;
				case 2: $riga="b"; break;
				case 3: $riga="c"; break;
				case 4: $riga="d"; break;
				case 5: $riga="e"; break;
				case 6: $riga="f"; break;
			}
			$res[$riga]=date("W",$index);
			
			for($d=1;$d<8;$d++) {
				$day=array("tag"=>date('Ymd',$index),"wd"=>date('w',$index),"f"=>0,"c"=>"no","nome"=>"");
				
				
				$md=date("md",$index);
				if(array_key_exists($md,$this->festa)) {
					$day["f"]=1;
					$day["nome"]=$this->festa[$md];
				}
				else {
					if(array_key_exists($md, $this->chiusura)) {
						$day["nome"]=$this->chiusura[$md]["nome"];
						$day["c"]=$this->chiusura[$md]["tipo"];
					}
				}	
				
				$res[$riga.$d]=$day;
				$index=strtotime("+1 day",$index);
			}
			
			$check=date("Ym",$index);
		}
		
		//il risultato è un array che descrive la matrice del calendario del mese (righe da a-f e colonne da 1-7), con indici che vanno in base al mese da "a1" a "f7". L'indice con la sola lettera della riga contiene il numero della settimana. Ogni indice contine a sua volta un array che indica il TAG (Ymd) del giorno, il weekday in forma numerica (0=domenica - 6=sabato) se il giorno è festivo, se è attivo un tipo di chiusura ed il nome della festa/chiusura.
		
		return $res;
	}
	
	function lavorativi($from,$to) {
		$time_from=mktime(0,0,0,(int)substr($from,4,2),(int)substr($from,6,2),(int)substr($from,0,4));
		$time_to=mktime(0,0,0,(int)substr($to,4,2),(int)substr($to,6,2),(int)substr($to,0,4));
		for ($i=$time_from;$time_to>=$i;$i=strtotime("+1 day",$i)) {
			if (date('w',$i)>0 && date('w',$i)<6) {
				$md=date("md",$i);
				if (!array_key_exists($md,$this->festa) && !array_key_exists($md,$this->chiusura)) {
					$lavorativi++;
				}
			}
		}
		return $lavorativi;
	}
	
	function lavorativi_mese($mese,$anno) {
		$time_from=mktime(0,0,0,(int)$mese,1,(int)$anno);
		for ($i=$time_from;date('m',$i)==$mese;$i=strtotime("+1 day",$i)) {
			if (date('w',$i)>0 && date('w',$i)<6) {
				$md=date("md",$i);
				if (!array_key_exists($md,$this->festa) && !array_key_exists($md,$this->chiusura)) {
					$lavorativi++;
				}
				elseif ($this->chiusura[$md][tipo]=='M' || $this->chiusura[$md][tipo]=='P') {
					$lavorativi=$lavorativi+0.5;
				}
			}
		}
		return $lavorativi;
	}
	
	function days($mese) {
		//restituisce la descrizione dei giorni del mese
	
		$res=array();
		$time=mktime(0,0,0,(int)$mese,1,$this->anno);
		
		$index=date('m',$time);
		
		while (date('m',$time)==$index) {
			
			$day=array("tag"=>date('Ymd',$time),"wd"=>date('w',$time),"f"=>0,"c"=>"no","nome"=>"");
					
			$md=date("md",$time);
			if(array_key_exists($md,$this->festa)) {
				$day["f"]=1;
				$day["nome"]=$this->festa[$md];
			}
			else {
				if(array_key_exists($md, $this->chiusura)) {
					$day["nome"]=$this->chiusura[$md]["nome"];
					$day["c"]=$this->chiusura[$md]["tipo"];
				}
			}
			
			$res[$this->anno.$md]=$day;
			$time=strtotime("+1 day",$time);
		}
		return $res;
	}
	
	function days_def($mese) {
		$d=$this->days($mese);
		$res=array();
		
		foreach ($d as $key=>$val) {
			$res[$key]=1;
		}
		
		return $res;
	}
}

?>