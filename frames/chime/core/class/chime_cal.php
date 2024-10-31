<?php

class ChimeCal extends calendario {

	public $griglia_chime;


	function __construct($db_handler,$anno_i,$reparto) {
        parent::__construct($db_handler,$anno_i,$reparto);
        
        //$this->griglia_chime=new griglia($reparto);
    }
    
    function chime_mese($mese,$oggi) {
    	//deve essere fornito $mese con lo 0 davanti
		$this->mese=$mese;
		$this->oggi=$oggi;
		
		$this->calcola_chiusure($mese);
		
		//---------------------definisci tabella -------------------------------------------------------
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
						
			//definizione giorno
			for($d=1;$d<8;$d++) {
				$day=array("tag"=>date('Ymd',$index),"wd"=>date('w',$index),"f"=>0,"c"=>"no","nome"=>"","chi"=>array());
				
				
				$md=date("md",$index);
				if(array_key_exists($md,$this->festa)) {
					$day["f"]=1;
					$day["nome"]=$this->festa[$md][nome];
				}
				else {
					if(array_key_exists($md, $this->chiusura)) {
						$day["nome"]=$this->chiusura[$md]["nome"];
						//$day["nome"]=json_encode($this->ore_giorno[date('Ymd',$index)]);
						//$day["nome"]=date('Ymd',$index);
						$day["c"]=$this->chiusura[$md]["tipo"];
						$day["chi"]=$this->chiusura[$md];
					}
				}	
				
				$res[$riga.$d]=$day;
				
				
				//array delle caratteristiche dei giorni
				$this->giorni[date('Ymd',$index)]=$day;
				
				$index=strtotime("+1 day",$index);
			}
			
			$check=date("Ym",$index);
		}
		
		//il risultato è un array che descrive la matrice del calendario del mese (righe da a-f e colonne da 1-7), con indici che vanno in base al mese da "a1" a "f7". L'indice con la sola lettera della riga contiene il numero della settimana. Ogni indice contine a sua volta un array che indica il TAG (Ymd) del giorno, il weekday in forma numerica (0=domenica - 6=sabato) se il giorno è festivo, se è attivo un tipo di chiusura ed il nome della festa/chiusura.
			
		$this->tabella=$res;
    }
    
	
	function draw_chime($inizio,$fine,$limite_i,$limite_f,$griglia) {
	
		$i=0;
		while ($i<6) {
			$i++;
			
			switch ($i) {
				case 1: $riga="a"; break;
				case 2: $riga="b"; break;
				case 3: $riga="c"; break;
				case 4: $riga="d"; break;
				case 5: $riga="e"; break;
				case 6: $riga="f"; break;
			}
			//se la riga non esiste interrompi il ciclo while
			if (!array_key_exists($riga,$this->tabella)) break;
			
			//se il giorno è >= di fine interrompi
			if ($this->tabella[$riga."1"][tag]>=$fine) break;
			
			//se il giorno è < di inizio continue
			if ($this->tabella[$riga."1"][tag]<$inizio) continue;
			
			//se il giorno != dal mese continua
			if (substr($this->tabella[$riga."1"][tag],4,2)!=$this->mese) continue;
						
			echo "<tr>";
						
				//scrivi numero settimana
				echo '<td style="font-weight:normal;font-size:9pt;">';
					echo '<div class="chime_cal_day">';
						echo $this->tabella[$riga];
					echo '</div>';	
				echo "</td>";
							
				//scrivi giorni
				for($d=1;$d<8;$d++) {
								
					echo '<td class="chime_cal_block_td" style="';
						if ($this->tabella[$riga.$d][wd]==0 or $this->tabella[$riga.$d][f]==1) {
							echo 'border-color: #ff0000;';
						}
					echo '">';
					
						//DIV DAY
						echo '<div class="chime_cal_day" style="';
							//se è un giorno di chiusura = sfondo immagine in base al tipo di chiusura
							if ($this->tabella[$riga.$d][c]!='no') {
								if ($this->tabella[$riga.$d][c]=='T') echo 'background-image:url(img/chiusura_T.png);background-size: 100% 100%;background-repeat: no-repeat;';
								elseif ($this->tabella[$riga.$d][c]=='M') echo 'background-image:url(img/chiusura_T.png);background-size: 100% 100%;background-repeat: no-repeat;';
								elseif ($this->tabella[$riga.$d][c]=='P') echo 'background-image:url(img/chiusura_T.png);background-size: 100% 100%;background-repeat: no-repeat;';
								elseif ($this->tabella[$riga.$d][c]=='C') echo 'background-image:url(img/chiusura_T.png);background-size: 100% 100%;background-repeat: no-repeat;';
								elseif ($this->tabella[$riga.$d][c]=='X') echo 'background-image:url(img/chiusura_T.png);background-size: cover;background-repeat: no-repeat;';
								//$txt.='background-repeat: no-repeat;';
								echo '';
							}
						echo '">';
						
							//numero
							$num_day=substr($this->tabella[$riga.$d][tag],6)."/".substr($this->tabella[$riga.$d][tag],4,2);
							echo '<div  ';
								if ($this->tabella[$riga.$d][wd]==0 || $this->tabella[$riga.$d][f]==1) echo 'style="color:red;"';
							echo '>';	
								echo $num_day;	
							echo '</div>';
							
							//OPERAZIONE GIORNO
							//dovrebe essere solo un record
							foreach ($griglia[$this->tabella[$riga.$d][tag]] as $g) {
								
								//azione in base a
								//$arr=("esclusi"=>0,"errori"=>0,"tot"=>0);
								echo '<div style="position:relative;width:80px;height:60px;text-align:center;cursor:pointer;vertical-align: middle;display: table-cell;" ';
								
								if (!($this->tabella[$riga.$d][tag]<$limite_i || $this->tabella[$riga.$d][tag]>$limite_f)) {
									echo 'onclick="chime_get_list(\''.$this->tabella[$riga.$d][tag].'\',0);"';
								}
								echo ' >';
									if ($g[esclusi]==0 && $g[errori]==0) {
										if ($g[tot]==0) {
											echo '<img style="width:35px;height:35px;" src="img/violet.png" />';
										}
										else {
											echo '<img style="width:35px;height:35px;" src="img/spunta.png"/>';
										}
									}
									else {
										echo '<div style="position:relative;margin-top:10px;height:35px;width:100%;">';
											echo '<span>'.$g[esclusi].'</span>';
											echo '<img class="chime_block_icon" src="img/stop.png"/>';
										echo '</div>';
										echo '<div style="position:relative;height:35px;width:100%;">';
											echo '<span>'.$g[errori].'</span>';
											echo '<img class="chime_block_icon" src="img/error.png"/>';
										echo '</div>';
									}
								echo '</div>';	
							}
							
							//cover giorno non selezionabile
							if ($this->tabella[$riga.$d][tag]<$limite_i || $this->tabella[$riga.$d][tag]>$limite_f) {
								echo '<div class="chime_cover2" onclick="chime_get_list(\''.$this->tabella[$riga.$d][tag].'\',1);"></div>';
							}
							
							//se il giorno è oggi segnala
							if ($this->tabella[$riga.$d][tag]==$this->oggi) echo '<img class="chime_cal_default" src="img/fermaglio.png"/>';	
							
						echo '</div>';
										
										
					echo '</td>';
				}			
					
			echo "</tr>";
		}
	}
	
	
	function chime_load_grid($grid) {
		$this->griglia_chime=$grid;
	}	
			    	
    
}
?>