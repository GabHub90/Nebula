<?php
abstract class nebulaTimeline {

	//minuti tra un'orario e l'altro
	protected $res;
	//array dello sviluppo della timeline
	protected $tl=array();
	//limiti inferiore e superiore delle valorizzazioni (INDICI)
	protected $trim=array(
		"min"=>1440,
		"max"=>0
	);

	protected $range=array(
		"min"=>0,
		"max"=>1425
	);

	//numeo di blocchi contenuti nel range
	//valorizzato d setRange
	protected $blocks=0;

	protected $colors=array(
		"violet"=>"#f336ff",
		"red"=>"#ff3636",
		"orange"=>"#ffa536",
		"yellow"=>"#ffeb00",
		"ocra"=>"#e0d32f",
		"green"=>"#53e130",
		"gray"=>"#eeeeee"
	);

	//definisce le aree che vengono disegnate
	//titolo				SI - NO
	//legenda				SI - NO
	//orari					SI - NO
	//corpo					SI - NO
	//valore				SI (disponibilità) - NO
	//totale				NO - DELTA (disponibilità residua) - PAN (delta / totale)
	//totale_bk				SI - NO rappresentazione grafica occupazione
	//totale_tag			SI - NO totale diviso in due righe (sub - valore)
	//flagTot				SI - NO (quadrato colorato indicante la disponibilità)
	//sottotitolo			SI - NO
	//popup					SI - NO (segnalazione orario passando sul blocco)

	protected $sezioni=array(
		"titolo"=>"SI",
		"legenda"=>"SI",
		"orari"=>"SI",
		"mark"=>"NO",
		"corpo"=>"SI",
		"valore"=>"SI",
		"totale"=>"DELTA",
		"totale_bk"=>"SI",
		"totale_tag"=>"NO",
		"flagTot"=>"NO",
		"sottotitolo"=>"NO",
		"popup"=>"NO"
	);

	//definisce gli elementi grafici
	//color					colore del testo (no valore) e bordi
	//width					larghezza totale
	//line_h				altezza di ogni riga del corpo
	//font_size				font di riferimento (legenda)
	//legenda_w				larghezza legenda
	//corpo_w				larghezza corpo
	//totale_w				larghezza slot totale
	//titolo_align			left - right - center
	//sottotitolo_align		left - right - center

	protected $css=array(
		"color"=>"black",
		"width"=>"100%",
		"line_h"=>"16px",
		"title_font_size"=>"11pt",
		"font_size"=>"8.5pt",
		"legenda_w"=>"7%",
		"corpo_w"=>"78%",
		"totale_w"=>"15%",
		"titolo_align"=>"left",
		"sottotitolo_align"=>"left"
	);

	//definisce il modo di stampare il corpo
	//zeros				se il denominatore è 0 : GRAY - NO
	//scala				scala dei valori che si riferiscono ai colori (se>valore => colore)
	//limite			PERC || VAL identifica come interpretare i valori della scala
	//valore_decimal	numero di cifre dopo la virgola nelle rappresentazioni dei valori (totali) 
	//legenda_tag		cosa scrivere nella legenda
	//occupazione		TRUE | FALSE indica se occorre tenere conto dell'occupazione
	protected $default=array(
		"sub"=>"",
		"zeros"=>"GRAY",
		"scala"=>array(
			"min"=>"violet",
			"0"=>"red",
			"1"=>"orange",
			"25"=>"ocra",
			"50"=>"yellow",
			"75"=>"green"
		),
		"limite"=>"PERC",
		"valore_decimal"=>2,
		"legenda_tag"=>"",
		"occupazione"=>false
	);

	//attuale intervallo in esame misurato in minuti
	protected $interval=array(
		"i"=>0,
		"f"=>0
	);

	//array di occupazione per ogni subs necessario
	//serve nel caso di comparazione in DRAW
	protected  $occupazione=array();

	//contiene per ogni SUB la disponibilità totale e l'occupazione totale
	protected $subTot=array();
	
	protected $log=array();

	function __construct($res) {		
		$this->res=$res;	
	}
	
	function tl_reset($subs) {
		//SUBS = sottogruppi legati agli orari (presenza,occupazione.....) ARRAY anche in più LIVELLI
		//vengono definiti dalla classe EXTEND in base alle esigenze specifiche
		//ovviamente i metodi di caricamento e di elaborazione saranno scritti di conseguenza

		//ogni sub= { "flag": 1/0 ....... altre informazioni}

		$index=0;
		
		while ($index<1440) {

			//start			minuti iniziali = index
			//end			minuti finali = indice successivo
			//tag			rappresentazione oraria dei minuti HH:mm
			//point			se è un'ora esatta (grafico)
			//half			se è una mezz'ora esatta (grafico)
			//flag			stato overall per l'operazione di trim (NON BOOLEANO ma somma delle occorrenze dei subrep)
			//subs			sottoinsiemi di raggruppamento delle informazioni

			$this->tl[$index]=array(
				"start"=>$index,
				"end"=>($index+$this->res),
				"tag"=>mainFunc::gab_mintostring($index),
				"point"=>0,
				"half"=>0,
				"mark"=>false,
				"flag"=>false,
				"subs"=>$subs
			);
			if (($index%30)==0) $this->tl[$index]['half']=1;
			if (($index%60)==0) $this->tl[$index]['point']=1;
			$index+=$this->res;
		}
	}

	function getTl() {
		return $this->tl;
	}

	function chkIndex($k) {
		//definisce se il blocco interseca a sufficienza l'intevallo in esame
		//questo è vero se:
		//se "end"-"inizio intervallo" > 50% della risoluzione
		//AND "fine intervallo" - "start" > 50% della risoluzione
		$a=$this->tl[$k]['end']-$this->interval['i'];
		$b=$this->interval['f']-$this->tl[$k]['start'];
		$rif=$this->res/2;

		if ($a>$rif && $b>$rif) return true;
		else return false;	
	}

	function checkFlag($min) {
		//restituisce i limiti del blocco che contiene i minuti passati se il flag è true
		$ret=false;

		foreach ($this->tl as $index=>$t) {

			if ($min>=$t['start'] && $min<$t['end']) {
				if ($t['flag']) {
					$ret=array('start'=>$t['start'],'end'=>$t['end']);
				}
				break;
			}
		}
		return $ret;
	}

	function setMark($min) {
		
		foreach ($this->tl as $index=>$t) {

			if ($min>=$t['start'] && $min<$t['end']) {
				$this->tl[$index]['mark']=true;
				$this->sezioni['mark']="SI";
				break;
			} 
		}
	}

	function chkTrueSub($sub) {
		//verifica se c'è almeno un blocco TRUE nell'intervallo per il sub
		//INTERVAL[i/f] VENGONO FISSATI PRIMA DI CHIAMARE IL METODO
		$res=false;

		foreach ($this->tl as $k=>$t) {
			if ( $this->chkIndex($k) ) {
				$res=$t['subs'][$sub]['flag'];
			}
			if ($res) break;
		}

		return $res;
	}

	function chkFalseSub($sub) {
		//verifica se c'è almeno un blocco FALSE nell'intervallo per il sub
		//INTERVAL[i/f] VEDNGONO FISSATI PRIMA DI CHIAMARE IL METODO
		$res=true;

		foreach ($this->tl as $k=>$t) {
			if ( $this->chkIndex($k) ) {
				$res=$t['subs'][$sub]['flag'];
			}
			if (!$res) break;
		}

		return $res;
	}

	function setTrim($index) {
		if ($index<$this->trim['min']) $this->trim['min']=$index;
		if ($index>$this->trim['max']) $this->trim['max']=$index;
	}

	function getTrim() {
		return $this->trim;
	}

	function getSubtot() {
		return $this->subTot;
	}

	function includi($da,$a,$info) {
		//"da" e "a" sono orari HH:mm
		//"info" sono le informazioni da passare ad includi

		$this->interval['i']=mainFunc::gab_stringtomin($da);
		$this->interval['f']=mainFunc::gab_stringtomin($a);

		foreach ($this->tl as $k=>$t) {
			if ( $this->chkIndex($k) ) $this->includiProprietario($k,$info);
		}

	}

	function escludi($da,$a,$info) {
		//"da" e "a" sono orari HH:mm
		//"info" sono le informazioni da passare ad includi

		$this->interval['i']=mainFunc::gab_stringtomin($da);
		$this->interval['f']=mainFunc::gab_stringtomin($a);

		foreach ($this->tl as $k=>$t) {
			if ( $this->chkIndex($k) ) $this->escludiProprietario($k,$info);
		}

	}

	function includiDefault($index,$sub,$bool) {
		//può essere chiamato da INCLUDI_PROPRIETARIO quando i SUBS sono ad un solo livello e non necessitano di calcoli specifici
		//se $bool è true la quantità può essere 1 o 0
		if (!isset($this->tl[$index]['subs'][$sub]['flag'])) {
			$this->tl[$index]['subs'][$sub]['flag']=true;
			$this->tl[$index]['subs'][$sub]['qta']=1;
		}
		else {
			$this->tl[$index]['subs'][$sub]['flag']=true;
			if ($bool) $this->tl[$index]['subs'][$sub]['qta']=1;
			else $this->tl[$index]['subs'][$sub]['qta']++;
		}

		//settare flag=TRUE
		$this->tl[$index]['flag']=true;

		$this->setTrim($index);
	}

	function escludiDefault($index,$sub,$bool) {

		//può essere chiamato da ESCLUDI_PROPRIETARIO quando i SUBS sono ad un solo livello e non necessitano di calcoli specifici
		//se $bool è true la quantità può essere 1 o 0
		if (!isset($this->tl[$index]['subs'][$sub]['flag'])) {
			$this->tl[$index]['subs'][$sub]['flag']=false;
			$this->tl[$index]['subs'][$sub]['qta']=0;
		}
		else {
			$this->tl[$index]['subs'][$sub]['flag']=false;
			if ($bool) $this->tl[$index]['subs'][$sub]['qta']=0;
			else $this->tl[$index]['subs'][$sub]['qta']--;
		}

		//settare flag=FALSE se qta<1
		if ($this->tl[$index]['subs'][$sub]['qta']<=0) $this->tl[$index]['flag']=false;

		//NON AGGIORNA TRIM

	}

	function setRange($arr) {
		$this->range=$arr;

		$this->blocks=0;

		foreach ($this->tl as $index=>$t) {
			if ($index>$this->range['max']) break;
			if ($index<$this->range['min']) continue;
			$this->blocks++;
		}

		if ($this->blocks<1) $this->blocks=1;
	}

	function setSezioni($config) {
		//definisce le sezioni da considerare o meno
		foreach ($this->sezioni as $k=>$v) {
			if ( array_key_exists($k,$config) ) $this->sezioni[$k]=$config[$k];
		}
	}

	function drawSetup($config) {
		//aggiorna l'array $CSS
		foreach ($this->css as $k=>$v) {
			if ( array_key_exists($k,$config) ) $this->css[$k]=$config[$k];
		}
	}

	function drawHead($info) {
		//stampa titolo ed orari

		if ($this->sezioni['titolo']=='SI') {

			echo '<div style="position:relative;font-weight:bold;';
				echo 'width:'.$this->css['width'].';';
				echo 'font-size:'.$this->css['title_font_size'].';';
				echo 'text-align:'.$this->css['titolo_align'].';';
				echo 'color:'.$this->css['color'].';';
			echo '">'.$info['titolo'].'</div>';

		}

		if ($this->sezioni['orari']=='SI') {

			echo '<table style="border-collapse:collapse;width:100%;font-size:'.$this->css['font_size'].';">';

				echo '<tr>';

					if ($this->sezioni['legenda']=='SI') {
						echo '<td style="width:'.$this->css['legenda_w'].';"></td>';
					}

					if ($this->sezioni['orari']=='SI') {
						echo '<td style="width:'.$this->css['corpo_w'].';">';
							$this->drawOrari();
						echo '</td>';
					}

					if ($this->sezioni['totale']!='NO') {
						echo '<td style="width:'.$this->css['totale_w'].';"></td>';
					}

				echo '</tr>';
			
			echo '</table>';

		}

	}

	function drawFoot($info) {
		//stampa il sottotitolo
		if ($this->sezioni['sottotitolo']=='SI') {

			echo '<div style="position:relative;';
				echo 'width:'.$this->css['width'].';';
				echo 'font-size:'.$this->css['font_size'].';';
				echo 'text-align:'.$this->css['sottotitolo_align'].';';
				echo 'color:'.$this->css['color'].';';
			echo '">'.$info['sottotitolo'].'</div>';

		}
	}

	function drawOrari() {
		//stampa la riga degli orari
		echo '<table style="width:100%;border-collapse:collapse;font-size:0.9em;color:'.$this->css['color'].';">';
		
			echo '<thead>';
				
				$w=round(100/($this->blocks),2);

				echo '<tr>';

					//echo '<th style="width:'.$w.'%;"></th>';
					
					foreach ($this->tl as $index=>$o) {

						if ($index>$this->range['max']) break;
						if ($index<$this->range['min']) continue;

						echo '<th style="position:relative;height:10px;width:'.$w.'%;">';
							if ($o['point']==1) {
								echo '<div style="position:absolute;left:0px;top:0px;transform: translate(-50%,-2px);">'.substr($o['tag'],0,2).'</div>';
							}
						echo '</th>';
					}

				echo '</tr>';

				echo '<tr>';

					//echo '<th style="height:4px;width:'.$w.'%;"></th>';
					
					foreach ($this->tl as $index=>$o) {

						if ($index>$this->range['max']) break;
						if ($index<$this->range['min']) continue;

						echo '<th style="position:relative;height:4px;width:'.$w.'%;';
							if ($o['point']==1) echo 'border-left:1px solid '.$this->css['color'].';';
						echo '">';
		
						echo '</th>';
					}

				echo '</tr>';

				echo '<tr>';

					//echo '<th style="height:4px;width:'.$w.'%;"></th>';
					
					foreach ($this->tl as $index=>$o) {

						if ($index>$this->range['max']) break;
						if ($index<$this->range['min']) continue;

						echo '<th style="position:relative;height:4px;width:'.$w.'%;';
							if ($o['half']==1) echo 'border-left:1px solid '.$this->css['color'].';';
						echo '">';
		
						echo '</th>';
					}

				echo '</tr>';

				echo '<tr>';

					//echo '<th style="height:4px;width:'.$w.'%;"></th>';
					
					foreach ($this->tl as $index=>$o) {

						if ($index>$this->range['max']) break;
						if ($index<$this->range['min']) continue;

						echo '<th style="position:relative;height:4px;width:'.$w.'%;border-bottom:1px solid '.$this->css['color'].';border-left:1px solid '.$this->css['color'].';';
						echo '">';

							if($this->sezioni['mark']=='SI' && $o['mark']) {
								echo '<img style="position:absolute;width:90%;height:230%;top:0px;left:0px;transform:translate(-53%,-80%);z-index:5;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/timeline/img/mark.png" />';
							}
		
						echo '</th>';
					}

				echo '</tr>';
		
			echo '</thead>';

		echo '</table>';
	}

	function drawSubs($a,$config) {
		//è obbligatorio definire drawProprietario
		//che prepara e passa i valori a drawSub
		//$a=sub.sub  (massimo due livelli)

		$w=round(100/($this->blocks),2);

		$temprif=explode(".",$a,2);

		if (!$temprif) return;

		$info=$this->default;

		foreach ($info as $k=>$v) {
			if ( array_key_exists($k,$config) ) $info[$k]=$config[$k];
		}

		echo '<table style="border-collapse:collapse;width:100%;">';

			echo '<tr>';

				if ($this->sezioni['legenda']=='SI') {
					echo '<td style="width:'.$this->css['legenda_w'].';font-size:'.$this->css['font_size'].';">'.$info['legenda_tag'].'</td>';
				}

				if ($this->sezioni['corpo']=='SI') {
					echo '<td style="width:'.$this->css['corpo_w'].';">';

						echo '<table style="width:100%;border-collapse:collapse;font-size:0.7em;color:'.$this->css['color'].';">';

							$totDispo=0;
							$totOccu=0;
		
							foreach ($this->tl as $index=>$o) {

								if ($index>$this->range['max']) break;
								if ($index<$this->range['min']) continue;

								$blocco=&$o['subs'][$temprif[0]];
								if ( isset($temprif[1]) ) $blocco=&$blocco[$temprif[1]];

								echo '<td style="position:relative;height:'.$this->css['line_h'].';width:'.$w.'%;padding:1px;box-sizing:border-box;">';

									echo '<div style="border:1px solid transparent;height:100%;text-align:center;vertical_align:middle;';

										//verifica ZEROS se nesuna disponibilità nominale
										if ($blocco['qta']==0) {
											if ($info['zeros']=='GRAY') echo 'background-color:'.$this->colors['gray'].';';
										}
										else {
											//se occupazione è abilitato ed esiste l'array che la quantifica
											if ($info['occupazione'] && isset($this->occupazione[$info['sub']][$index]) ) {
												$dispo=$blocco['qta']-$this->occupazione[$info['sub']][$index];

												$totOccu+=$this->occupazione[$info['sub']][$index];
											}
											else $dispo=$blocco['qta'];

											$totDispo+=$blocco['qta'];

											if ($info['limite']=='PERC') $val=($dispo/$blocco['qta'])*100;
											else $val=$dispo;

											$col="";
											foreach ($info['scala'] as $kc=>$c) {
												if ($col=='') {
													$col=$c;
													continue;
												}

												if ($val>=$kc) $col=$c;
											}

											echo 'background-color:'.$this->colors[$col].';';

										}

									echo '" data-index="'.$index.'" >';

										if ($this->sezioni['valore']=='SI') {

											if ($blocco['qta']>0) echo '<div style="font-size:0.7em;position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);">'.$dispo.'</div>';
										}

									echo '</div>';
		
								echo '</td>';
								
							}

						echo '</table>';
						
					echo '</td>';
				}

				if ($this->sezioni['totale']!='NO' || $this->sezioni['totale_bk']!='NO') {

					//se i totali non sono stati calcolati
					if ($this->sezioni['corpo']=='NO') {
						$totDispo=0;
						$totOccu=0;
						foreach ($this->tl as $index=>$o) {

							if ($index>$this->range['max']) break;
							if ($index<$this->range['min']) continue;

							$blocco=&$o['subs'][$temprif[0]];
							if ( isset($temprif[1]) ) $blocco=&$blocco[$temprif[1]];

							//se occupazione è abilitato ed esiste l'array che la quantifica
							if ($info['occupazione'] && isset($this->occupazione[$info['sub']][$index]) ) {
								$totOccu+=$this->occupazione[$info['sub']][$index];
							}

							$totDispo+=$blocco['qta'];
						}
					}

					$totDispo=($totDispo*$this->res)/60;
					$totOccu=($totOccu*$this->res)/60;

					$this->subTot[$info['sub']]=array(
						"dispo"=>$totDispo,
						"occu"=>$totOccu
					);

					echo '<td style="width:'.$this->css['totale_w'].';">';

						echo '<div style="position:relative;height:'.$this->css['line_h'].';width:89%;margin-left:10%;background-size:cover;background-repeat:no-repeat;';
							
							//eventualmente background-image
							if ($this->sezioni['totale_bk']=='SI') {
								echo "background-image:url(http://".$_SERVER['SERVER_ADDR']."/nebula/apps/tempo/img/sfondo_perc.png);";
							}

						echo '">';

							//cover bianca
							if ($this->sezioni['totale_bk']=='SI') {
								echo '<div style="position:absolute;right:0px;top:0px;height:100%;background-color:#ffffff88;z-index:2;';
									if ($totDispo==0) $temperc=100;
									else $temperc=round((1-($totOccu/$totDispo))*100);
									echo 'width:'.($temperc>100?"100":$temperc).'%;';
								echo '">';
								echo '</div>';
							}

							echo '<div style="position:relative;width:100%;height:100%;font-size:'.$this->css['font_size'].';padding:2px;box-sizing:border-box;z-index:3;">';

								echo '<div style="text-align:center;font-weight:bold;font-size:0.9em;">';

									if ($this->sezioni['totale_tag']=='SI') {
										echo '<div>';
											echo $info['legenda_tag'];
										echo '</div>';
									}

									echo '<div>';
										if ($this->sezioni['totale']=='DELTA') echo number_format($totDispo-$totOccu,$info['valore_decimal'],".","");
										if ($this->sezioni['totale']=='PAN') echo number_format($totOccu,$info['valore_decimal'],".","")." / ".number_format($totDispo,$info['valore_decimal'],".","");
									echo '</div>';

								echo '</div>';

							echo '</div>';

						echo '</div>';
						
					echo '</td>';
				}

			echo '</tr>';
		
		echo '</table>';


	}

	abstract function includiProprietario($index,$info);
	abstract function escludiProprietario($index,$info);
	abstract function drawProprietario($info);

	
	
















	
	
	
	function draw_subrep($sub,$color,$def_edit,$w,$blocks,$margin) {
	
		//$rep=intval(60/$this->res);
		
		echo '<div style="position:relative;top:10px;">';
			echo '<table style="position:relative;border-collapse:collapse;font-size:8pt;color:'.$color.';" width="'.(($w*$blocks)+($margin*2)).'">';
			
				echo '<colgroup>';
					echo '<col span="1" width="'.$margin.'px"/>';
					echo '<col span="'.$blocks.'" width="'.$w.'px"/>';
					echo '<col span="1" width="'.$margin.'px"/>';
				echo '</colgroup>';
				
				echo '<tbody>';
					echo '<tr>';
						echo '<td></td>';
						//foreach ($dist as $k=>$d) {
						foreach ($this->orari as $kx=>$o) {
							//l'array sub misura la presenza oraria (di solito timeline h auna risoluzione a quarti d'ora
							//for ($x=0;$x<$rep;$x++) {
								echo '<td style="height:5px;';
									if ($o['stato']==1) {
										echo 'background-color:yellow;';
										//$this->orari[$k][stato]=1;
									}
									else {
										echo 'background-color:red;';
										//$this->orari[$k][stato]=0;
									}
								echo '"></td>';
							//}
						}
						echo '<td></td>';
					echo '</tr>';
				echo '</tbody>';		
			echo '</table>';
			
			echo '<div style="position:relative;color:'.$color.'">';
				echo '<div style="float:left;width:70px;">';
					echo '<div style="text-align:left;">'.$sub.'</div>';
				echo '</div>';
				echo '<div style="float:left;width:230px;font-size:9pt;">';
				
					foreach (array(0,25,50,75,100) as $p) {
						echo '<input name="cal_timeline_sub_'.$sub.'" type="radio" value="'.$p.'" onclick="tempo_edit_sposta_modifica();" ';
							if ($p==$def_edit) echo ' checked="checked"';
							if ($p!=0 && $p!=100) echo ' disabled="disabled"';
						echo '/>';
						echo '<span>'.$p.'</span>';
					}
				
				echo '</div>';
				
				echo '<div id="cal_timeline_sub_div_'.$sub.'"style="float:left;width:70px;text-align:right;"></div>';
				
				echo '<div style="clear:both;"></div>';
			echo '</div>';
			
		echo '</div>';
	}
	
	
	
	
	//calcola le ore valide nell'intervallo dato
	function calcola_ore($inizio,$fine) {
	
		//STATO 0:NON VALIDO
		//STATO 1:VALIDO
		//STATO 2:OCCUPATO
		
		//la percentule di ogni blocco serve per la distribuzione giornaliera della presenza
		//la percentuale generale viene impostata in base ai paramentri generali impostati per il collaboratore e per il giorno della settimana
	
		//$inizio e $fine sono in minuti
		$conta=0;
		
		foreach ($this->orari as $kx=>$o) {
		
			if ($kx==$fine) break;
			
			if ($kx>=$inizio) {
				
				if ($o['stato']==1) {
					$temp=$o['end']-$o['min'];
					$conta+=round(($temp*$o['perc']),3);
				}
			}
		}
		
		return round($conta*($this->perc),2);
			
		/*
		//OGGETTO TIMELINE
		//var _t2_temp_obj={"0":{"min":0,"end":15,"tag":"00:00","point":1,"half":1,"stato":0},"15":{"min":15,"end":30,"tag":"00:15","point":0,"half":0,"stato":0},"30":{"min":30,"end":45,"tag":"00:30","point":0,"half":1,"stato":0},"45":{"min":45,"end":60,"tag":"00:45","point":0,"half":0,"stato":0},"60":{"min":60,"end":75,"tag":"01:00","point":1,"half":1,"stato":0},"75":{"min":75,"end":90,"tag":"01:15","point":0,"half":0,"stato":0}...
		
		//$('#cal_coll_edit_ora_calcolo').html(Math.round(conta/60).toFixed(1)+' h');
		var temp=Math.round(((conta/60)*100))/100;
		$('#cal_coll_edit_ora_calcolo').html(temp+' h');
		*/
	}
	
	
	
	function set_orari($o) {
		$this->orari=$o;
	}
	
	
	function set_perc($val) {
		if ($val>1) $val=1;
		if ($val<0) $val=0;
		$this->perc=round($val,3);
	}
	
	function set_block_perc($inizio,$fine,$perc) {
	
		if ($perc>1) $perc=1;
		if ($perc<0) $perc=0;
	
		foreach ($this->orari as $kx=>$o) {
		
			if ($kx>=$fine) break;
			
			if ($kx>=$inizio) {
				$this->orari[$kx]['perc']=$perc;
			}
		}
	}
	
	function reset_block_perc() {
		foreach($this->orari as $k=>$o) {
			$this->orari[$k]['perc']=1;
		}
	}
	
	function inverse_block_perc() {
		foreach($this->orari as $k=>$o) {
			$this->orari[$k]['perc']=1-$this->orari[$k]['perc'];
		}
	}
	
	function get_timeline() {
		return $this->orari;
	}

	function get_timeline_as_array() {
		//restituisce un array con indice e stato
		$a=array();
		foreach ($this->orari as $k=>$o) {
			$a[$k]=$o['stato'];
		}

		return $a;
	}
	
	function get_res() {
		return $this->res;
	}

	function get_dim() {
		return array("w"=>$this->w,"blocks"=>$this->blocks,"margin"=>$this->margin);
	}
	
	function get_stati() {
		$res=array();
		foreach ($this->orari as $k=>$o) {
			$res[$k]=$o['stato'];
		}
		
		return $res;
	}
}
?>
