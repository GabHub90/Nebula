<?php
class stCollaboratore {

	private $maestro;

	private $coll=array();
	private $today;
	private $now;
	private $now_min;
	private $now_turno=array("tipo"=>"","numero"=>0);
	private $marc_turno=array("tipo"=>"","numero"=>0,"limite"=>"");
	
	private $turni_str="";
	
	//0:OK
	//10:doppia marcatura aperta
	private $stato=0;
	private $selected=0;
	
	private $marcatura_aperta;
	private $ultima_marcatura;
	
	private $odl_servizio;
	private $addebiti;
	private $stato_lam;
	private $pitlane_open;
	
	private $log;

	function __construct($maestro,$a,$today,$sel_coll) {
	
		$this->maestro=$maestro;
		
		$this->pitlane_open=$maestro->ststop_get_pitlane_open();

		$a['pitlane_open']=0;
		
		foreach($this->pitlane_open[$a['marcatempo']] as $p) {
			
			if ($p['stato']=='WAIT') {
				$a['pitlane_open']=2;
				break;
			}

			$a['pitlane_open']=1;
		}
		
		if ($a['marcatempo']==$sel_coll) $this->selected=1;

		$this->coll=$a;
		$this->today=$today;
		$this->now=date('H:i');
		$this->now_min= ((int)substr($this->now,0,2))*60+((int)substr($this->now,3,2));
		
		//===============================================
		//calcola turni NOW
		//===============================================
		$num_turno=0;
		
		foreach ($this->coll['griglia_turni'] as $gt) {
			
			foreach ($gt as $o) {
				//echo json_encode($o);
				//$obj=(array) $o;
				//echo gettype($o);
				$num_turno++;
				$i_min=((int)substr($o->i,0,2))*60+((int)substr($o->i,3,2));
				$f_min=((int)substr($o->f,0,2))*60+((int)substr($o->f,3,2));
				
				if ($this->now_min<$i_min) {	
					if ($this->now_turno['tipo']=="") {
						$this->now_turno['tipo']="extra";
						$this->now_turno['numero']=$num_turno;
					}
					$this->turni_str.='<span style="position:relative;margin-left:20px;">'.$o->i.' - '.$o->f.'</span>';
					
				}
				elseif ($this->now_min<=$f_min) {
				
					$this->now_turno['tipo']="standard";
					$this->now_turno['numero']=$num_turno;
					$this->turni_str.='<span style="position:relative;margin-left:20px;color:#001bff;font-weight:bold;">'.$o->i.' - '.$o->f.'</span>';
				}
				else {
					$this->turni_str.='<span style="position:relative;margin-left:20px;">'.$o->i.' - '.$o->f.'</span>';
				}
			}
		}
		
		if ($this->now_turno['tipo']=="") {
			$this->now_turno['tipo']="extra";
			$this->now_turno['numero']=$num_turno+1;
		}
		
	}
	
	function load_m_aperte($a) {
	
		//l'operazione è valida solo se c'è una SOLA marcatura aperta
		if (count($a)>1) $this->stato=10;
		else {
			
			//calcola turno marcatura
			//tipo: extra / standard
			//numero
			//limite: tempo di chiusura se necessario
			//$marc_turno=array("tipo"=>"","numero"=>0,"limite"=>"");
			$marc_min=((int)substr($a[0]['t'],0,2))*60+((int)substr($a[0]['t'],3,2));
			$num_turno=0;
			
			foreach ($this->coll['griglia_turni'] as $gt) {
				
				foreach ($gt as $o) {
					//echo json_encode($o);
					//$obj=(array) $o;
					//echo gettype($o);
					$num_turno++;
					$i_min=((int)substr($o->i,0,2))*60+((int)substr($o->i,3,2));
					$f_min=((int)substr($o->f,0,2))*60+((int)substr($o->f,3,2));
					
					if ($marc_min<$i_min) {
					
						if ($this->marc_turno['tipo']=="") {
							$this->marc_turno['tipo']="extra";
							$this->marc_turno['numero']=$num_turno;
							$this->marc_turno['limite']=$o->i;
						}
					}
					elseif ($marc_min<=$f_min) {
						$this->marc_turno['tipo']="standard";
						$this->marc_turno['numero']=$num_turno;
						$this->marc_turno['limite']=$o->f;
					}
				}
			}
			
			if ($this->marc_turno['tipo']=="") {
				$this->marc_turno['tipo']="extra";
				$this->marc_turno['numero']=$num_turno+1;
				//niente limite perché se non viene chiusa cambia il giorno
			}
			
			//==================================================
			//DECIDI COSA FARE DELLA MARCATURA IN BASE ALLA DATA E ORA DI APERTURA
			//==================================================
			
			//se è una marcatura vecchia da chiudere FERMATI QUI
			//la chiusura avviene a livello reparto
			if ($this->today!=date('Ymd')) {
				return;
			}
			
			//se marcatura speciale e siamo in tempo extra chiudi la macatura
			if ($this->now_turno['tipo']=='extra' && $a[0]['des_note']!="") {
				
				//CHIUDI MARCATURA
				$this->maestro->st_chiudi_marcatura($this->today.' '.$this->marc_turno['limite'],$this->coll['marcatempo'],'T');
				
				//$this->log=$this->today.' '.$this->marc_turno[limite].' '.$this->coll[marcatempo];
				return;
			}
			
			//se TIPO è uguale ma NUMERO è diverso chiudi
			if ($this->marc_turno['tipo']==$this->now_turno['tipo'] && $this->marc_turno['numero']!=$this->now_turno['numero']) {
			
				//CHIUDI MARCATURA
				if ($this->marc_turno['tipo']=='standard') {
					$this->maestro->st_chiudi_marcatura($this->today.' '.$this->marc_turno['limite'],$this->coll['marcatempo'],'R');
					return;
				}
				if ($this->marc_turno['tipo']=='extra') {
					$this->maestro->st_chg_inizio($this->today.' '.$this->marc_turno['limite'],$this->coll['marcatempo']);
					$a[0]['t']=$this->marc_turno['limite'];
				}
			}
			
			//se marc[tipo]==extra e  numeri diversi chiudi
			elseif ($this->marc_turno['tipo']=='extra' && $this->marc_turno['numero']!=$this->now_turno['numero']) {
			
				//CHIUDI MARCATURA
				$this->maestro->st_chiudi_marcatura($this->today.' '.$this->marc_turno['limite'],$this->coll['marcatempo'],'R');
				return;
			}
			
			$this->marcatura_aperta=new stMarcatura($this->maestro,$a[0],$this->marc_turno,$this->now_turno,$this->now,$this->selected,$this->odl_servizio,$this->addebiti,$this->pitlane_open,$this->stato_lam,$this->coll['std_prod']);
		}
	}
	
	function load_ultima_marcatura($a) {
		$t=array();
		$this->ultima_marcatura=new stMarcatura($this->maestro,$a,$t,$t,$this->now,$this->selected,$this->odl_servizio,$this->addebiti,$this->pitlane_open,$this->stato_lam,$this->coll['std_prod']);
	}
	
	function load_odl_servizio($a) {
		$this->odl_servizio=$a;
	}
	
	function load_addebiti($a) {
		$this->addebiti=$a;
	}
	
	function load_stato_lam($a) {
		$this->stato_lam=$a;
	}
	
	
	function draw_coll() {
	
		echo '<div id="ststop_colldiv_'.$this->coll['marcatempo'].'" class="coll_div">';
		
			echo '<div class="coll_intest_div">';
			
				//intestazione
				echo '<div class="coll_intest_txt">';
					echo '['.$this->coll['marcatempo'].']'.' <b>'.$this->coll['nome'].' '.$this->coll['cognome'].'</b> ('.$this->coll['ruolo'].')';
					
					
					//se il tecnico ha delle RICHIESTE PITLANE in piedi
					//if (array_key_exists($this->coll[marcatempo],$this->pitlane_open)) {
						if ($this->coll['pitlane_open']==1) {
							echo '<img class="lam_icon_img" src="img/megafono.png" style="position:relative;left:20px;width:20px;height:14px;cursor:wait;" />';
						}
						elseif ($this->coll['pitlane_open']==2) {
							echo '<img class="lam_icon_img" src="img/thumbup.png" style="position:relative;left:20px;width:20px;height:14px;cursor:wait;" />';
						}
					//}
					
					/*
					//scrivi il tasto NUOVO se la marcatura non è in errore
					if($open[$key][error]==0) {
						//se la marcatura è SPECIALE (no presenza) il tasto NUOVO non c'è
						if (substr($open[$key][des_note],0,3)!='SER' && substr($open[$key][des_note],0,3)!='VRT' && substr($open[$key][des_note],0,3)!='PRV') {
							$flagopen=(array_key_exists($key,$open)?1:0);
							echo '<div id="new_b_'.$key.'" style="position:absolute;top:0px;right:20px;display:none;">';
								echo '<img class="lam_button_img" src="img/NEW.png" onclick="open_nuovo(\''.$key.'\','.$flagopen.');"/>';
							echo '</div>';
						}
					}
					*/
				echo '</div>';
				
				echo '<div style="position:absolute;left:350px;top:0px;">';
					echo $this->turni_str;
					//echo json_encode($this->now_turno);
				echo '</div>';
				
				if ($this->selected==1) {
					echo '<div style="position:absolute;top:0px;right:20px;">';
						echo '<img class="lam_button_img" src="img/NEW.png" onclick="st_apri_nuovo(\'NUOVO\',\'\');"/>';
					echo '</div>';
				}
				
				
			echo '</div>';
			
			//====== CORPO COLLABORATORE =========
			
			echo '<div>';
			
				//echo json_encode($this->coll);
				//echo $this->log;
			
				/*if ($this->marcatura_aperta instanceof stMarcatura) {
					$this->marcatura_aperta->draw();
				}
				
				if ($this->ultima_marcatura instanceof stMarcatura) {
					$this->ultima_marcatura->draw();
				}*/
				
				$this->draw_corpo();
				
			echo '</div>';
			
		echo '</div>';
	}
	
	function draw_corpo() {
	
		//===== VERIFICA DOPPIA MARCATURA =======
		if ($this->stato==10) {
			echo '<div class="error">';
				echo '<div style="position:relative;left:5px;">Ci sono più marcature APERTE, correggere in CONCERTO</div>';
			echo '</div>';
			return;
		}
		
		//===== MARCATURA APERTA =======
		echo '<div>';
		
			if ($this->marcatura_aperta instanceof stMarcatura) {
				$this->marcatura_aperta->draw();
			}
			else {
				echo '<div class="error">';
					echo '<div style="position:relative;left:5px;">Non ci sono marcature aperte</div>';
				echo '</div>';
				//===== ULTIMA MARCATURA =======
				echo '<div>';
				
					if ($this->ultima_marcatura instanceof stMarcatura) {
						$this->ultima_marcatura->draw();
					}
					
				echo '</div>';
			}
			
		echo '</div>';
		
	}
	
	function draw_open_head($tipo,$info,$fore_col) {
	
		//$tipo=bottone che è stato schiacciato
		//$info=eventuale informazione aggiuntiva
			//START=codice lamentato
		
		//ordine attuale
		echo '<div class="st_open_subhead" style="border-right:1px solid black;">';
		
			echo '<div style="width: 100%;text-align:center;">Ordine attuale</div>';
			
			if ($this->marcatura_aperta instanceof stMarcatura) {
			
				echo '<div style="position:relative;margin-top:20px;width:95%;">';
					//$odl_attuale=$this->marcatura_aperta->get_lam_aperto();
					$this->marcatura_aperta->draw_lam_chiusura();
				echo '</div>';
				
				//////////////
				$t_odl="";
				$t_odl=$this->marcatura_aperta->get_lam_aperto();
				/////////////
				
				echo '<div style="position:relative;margin-top:20px;text-align:left;">';
					//se non è una macatura di servizio
					if (!$this->marcatura_aperta->chk_servizio()) {
						$i=0;
						foreach ($this->stato_lam as $k=>$chi) {
							echo '<div style="position:relative;left:15px;"><input name="st_open_form_chiusura_prec" type="radio" value="'.$k.'" '; 
							if ($i==0) echo 'checked="checked"';
							echo '>'.$chi.'</div>';
							$i++;
						}
					}
				echo '</div>';
			}
			
			//////////////////////////////////////////
			echo '<input id="st_open_form_odl_open" type="hidden" value="'.$t_odl.'"/>';
			//////////////////////////////////////////
			
		echo '</div>';
		
		//ordine da aprire
		echo '<div class="st_open_subhead">';
		
			echo '<div style="width: 100%;text-align:center;">Riferimento nuovo</div>';
			
			echo '<div style="position:relative;width:280px;margin-top:10px;left:50%;margin-left:-140px;text-align:center;background-color:black;color:'.$fore_col.'">';
				echo '<div>['.$this->coll['marcatempo'].']'.' <b>'.$this->coll['nome'].' '.$this->coll['cognome'].'</b></div>';
				echo '<div>('.$this->coll['ruolo'].')</div>';
			echo '</div>';
			
			if ($tipo=='NUOVO') {
			
				echo '<div style="position:absolute;bottom:10px;left:0px;text-align:center;width:100%;">';
					echo '<input id="st_open_form_odl_nuovo" type="text" maxlength="7" style="width:175px;height:50px;font-size:25pt;font-weight:bold;text-align:center;" onchange="st_view_lamentati(this.value);"/>';
				echo '</div>';
				
				//////////////
				echo '<input id="st_open_form_coll_prova" type="hidden" value="'.$this->chk_prova().'"/>';
				/////////////
			}
			
			if ($tipo=="START") {
			
				$this->marcatura_aperta->draw_lam_start($info);
				
				echo '<div style="position:relative;top:10px;left:0px;text-align:center;width:100%;font-size:25pt;font-weight:bold;">';
					echo $t_odl;
				echo '</div>';
				
				echo '<div style="position:relative;top:10px;left:0px;text-align:center;width:100%;font-size:16pt;font-weight:bold;">';
					echo '<img class="lam_button_img" src="img/START.png" onclick="st_apri_form_start(\''.$t_odl.'\',\''.$info.'\');"/>';
				echo '</div>';
				
			}
			
			if ($tipo=="FINE") {
				
				echo '<div style="position:relative;top:10px;left:0px;text-align:center;width:100%;font-size:16pt;font-weight:bold;">';
					echo '<img class="lam_button_img" src="img/FINE.png" onclick="st_apri_form_fine();"/>';
				echo '</div>';
				
				echo '<div style="position:relative;top:10px;left:0px;text-align:center;width:100%;font-size:16pt;font-weight:bold;">';
					echo date('d/m/Y H:i');
				echo '</div>';
				
			}
			
			
			//SE STOP NON SCRIVERE NIENTE
			
		echo '</div>';
		
		//apertura speciale
		echo '<div class="st_open_subhead" style="border-left:1px solid black;">';
		
			echo '<div style="width: 100%;text-align:center;">Speciale</div>';

			//password LIMPO ottobre 2018
			echo '<input id="st_open_form_password" type="hidden" value="limpo"/>';
			/////////////////////////////
			
			//special solo se PRODUTTIVO
			if (!$this->chk_improduttivo()) {
				if ($tipo=='NUOVO' && $this->now_turno['tipo']=="standard") {
				
					echo '<div id="st_div_open_speciale" style="margin-top:10px;">';
										
						echo '<div style="position:relative;height:50px;">';
							echo '<div style="float:left;width:40px;text-align:center;">';
								echo '<img class="nuovo_speciale_img" src="img/out.png" onclick="st_open_pw(\'SER\',\'out.png\');"/>';
							echo '</div>';
							echo '<div style="float:left;width:257px;position:relative;top:-5px;">';
								echo '<div style="">Servizio</div>';
								echo '<div style="font-size:11pt;">(marcatura improduttiva)</div>';
							echo '</div>';
						echo '</div>';
						
						if (!$this->marcatura_aperta instanceof stMarcatura) {
							echo '<div style="position:relative;height:50px;">';
								echo '<div style="float:left;width:40px;text-align:center;">';
									echo '<img class="nuovo_speciale_img" src="img/attesa.png" onclick="st_apri_form_speciale(\'ATT\');"/>';
								echo '</div>';
								echo '<div style="float:left;width:257px;position:relative;top:-5px;">';
									echo '<div style="">Attesa</div>';
									echo '<div style="font-size:11pt;">(attesa lavoro)</div>';
								echo '</div>';
							echo '</div>';
						}
						
						if ($this->marcatura_aperta instanceof stMarcatura) {
						
							echo '<div style="position:relative;height:50px;">';
								echo '<div style="float:left;width:40px;text-align:center;">';
									echo '<img class="nuovo_speciale_img" src="img/exit.png" onclick="st_open_pw(\'EXI\',\'exit.png\');"/>';
								echo '</div>';
								echo '<div style="float:left;width:257px;position:relative;top:-5px;">';
									echo '<div style="">Uscita</div>';
									echo '<div style="font-size:11pt;">(permesso accordato)</div>';
								echo '</div>';
							echo '</div>';
							
							//se non è una macatura di servizio
							if (!$this->marcatura_aperta->chk_servizio()) {
								echo '<div style="position:relative;height:50px;">';
									echo '<div style="float:left;width:40px;text-align:center;">';
										echo '<img class="nuovo_speciale_img" src="img/anticipata.png" onclick="st_open_pw(\'ANT\',\'anticipata.png\');"/>';
									echo '</div>';
									echo '<div style="float:left;width:257px;position:relative;top:-5px;">';
										echo '<div style="">Chiusura anticipata</div>';
										echo '<div style="font-size:11pt;">(esigenze di fatturazione)</div>';
									echo '</div>';
								echo '</div>';
							}
						}
		
					echo '</div>';
				}
			
				if ($tipo=='STOP') {
					
					if ($this->marcatura_aperta instanceof stMarcatura) {
					
						echo '<div style="position:relative;height:50px;">';
							echo '<div style="float:left;width:40px;text-align:center;">';
								echo '<img class="nuovo_speciale_img" src="img/attesa.png" onclick="st_apri_form_speciale(\'ATT\');"/>';
							echo '</div>';
							echo '<div style="float:left;width:257px;position:relative;top:-5px;">';
								echo '<div style="">Attesa</div>';
								echo '<div style="font-size:11pt;">(attesa lavoro)</div>';
							echo '</div>';
						echo '</div>';
						
						echo '<div style="position:relative;height:50px;">';
							echo '<div style="float:left;width:40px;text-align:center;">';
								echo '<img class="nuovo_speciale_img" src="img/pulizia.png" onclick="st_apri_form_speciale(\'PUL\');"/>';
							echo '</div>';
							echo '<div style="float:left;width:257px;position:relative;top:-5px;">';
								echo '<div style="">Pulizia</div>';
								echo '<div style="font-size:11pt;">(riordino postazione e attrezzi)</div>';
							echo '</div>';
						echo '</div>';
					}
				}
				
				if ($tipo=='FINE') {
					
					if ($this->marcatura_aperta instanceof stMarcatura) {
					
						echo '<div style="position:relative;height:50px;">';
							echo '<div style="float:left;width:40px;text-align:center;">';
								echo '<img class="nuovo_speciale_img" src="img/allinea.png" onclick="st_apri_form_allinea(\''.$this->marcatura_aperta->get_marc_limite().'\');"/>';
							echo '</div>';
							echo '<div style="float:left;width:257px;position:relative;top:-5px;">';
								echo '<div style="">Allinea</div>';
								echo '<div style="font-size:11pt;">(chiudi a '.$this->marcatura_aperta->get_marc_limite().')</div>';
							echo '</div>';
						echo '</div>';
					}
				}
				
				//SE START NON SCRIVERE NIENTE
			
			}
			
			//DIV PASSWORD
			echo '<div id="st_div_open_password" style="display:none;">';
			
				echo '<div id="st_div_open_password_img" style="position:relative;margin-top:10px;text-align:center;">';
				echo '</div>';
				//tipo marcatura speciale
				echo '<input id="st_open_form_speciale_tipo" type="hidden" value=""/>';
				
				echo '<div style="position:relative;margin-top:30px;text-align:center;width:100%;">';
					echo '<input id="st_open_form_speciale_password" type="password" maxlength="7" style="width:175px;height:50px;font-size:25pt;font-weight:bold;text-align:center;" onkeydown="if(event.keyCode==13) st_special_pw(this.value);"/>';
				echo '</div>';
				
				echo '<div style="position:relative;margin-top:15px;text-align:center;">';
					echo '<button style="background-color:orange;" onclick="st_close_pw();">annulla</button>';
				echo '</div>';
				
			echo '</div>';
			
		echo '</div>';
	}
	
	function chiudi_fine_turno() {
	
		return $this->marc_turno;
	}
	
	function chk_improduttivo() {
		//se $this->std_prod==0 significa che è un improduttivo
		if ($this->coll['std_prod']==0) return true;
		else return false;
	}
	
	function chk_prova() {
	
		if ($this->now_turno['tipo']=="extra") {
			return 0;
		}
		else return $this->coll['prova'];
	}
}
?>