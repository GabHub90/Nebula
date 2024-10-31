<?php
class stMarcatura {

	private $maestro;
	
	private $now;

	private $marcatura=array();
	private $lamentato=array();
	private $cod_lamentato="";
	private $tipo="";
	
	private $special="";
	private $special_obj;
	
	private $now_turno=array();
	private $marc_turno=array();
	
	private $selected;
	private $odl_servizio=array();
	private $addebiti=array();
	private $stato_lam=array();
	//produttività standard collaboratore (RT=0)
	private $std_prod;
	//flag se pitlane attivo (1/0)
	private $pitlane_open;
	
	private $log;

	function __construct($maestro,$a,$marc_turno,$now_turno,$now,$selected,$odl_servizio,$addebiti,$pitlane_open,$stato_lam,$std_prod) {
	
		$this->maestro=$maestro;
		
		$this->marc_turno=$marc_turno;
		$this->now_turno=$now_turno;
		$this->now=$now;
		
		$this->selected=$selected;
		$this->odl_servizio=$odl_servizio;
		$this->addebiti=$addebiti;
		$this->stato_lam=$stato_lam;
		$this->std_prod=$std_prod;
		$this->pitlane_open=$pitlane_open;
				
		$this->marcatura=$a;
		if (array_key_exists("mov",$a)) {
			//$this->lamentato=$maestro->st_get_lamentato($a[mov],$a[inc]);
			$this->lamentato=$maestro->st_get_lamentati($a['mov']);
			$this->tipo="ultima";
			$this->cod_lamentato=$a['inc'];
			$this->calcola_addebito();
		}
		else {
			//$this->lamentato=$maestro->st_get_lamentati($a[num_rif_movimento],$a[cod_inconveniente]);
			$this->lamentato=$maestro->st_get_lamentati($a['num_rif_movimento']);
			$this->tipo="aperta";
			$this->cod_lamentato=$a['cod_inconveniente'];
			//$this->log=$a[num_rif_movimento];
			$this->calcola_addebito();
		}
		
		//PITLANE
		//se esiste quanlche segnalazione sull'operaio
		//if (array_key_exists($this->marcatura[cod_operaio],$this->pitlane_open)) {
			foreach ($this->lamentato as $klam=>$lam) {
				
				$this->lamentato[$klam]['flag_pitlane']=0;
				
				foreach ($this->pitlane_open[$this->marcatura['cod_operaio']] as $pl) {
					
					if ($lam['mov']==$pl['num_rif_movimento'] && $lam['inc']==$pl['cod_inconveniente']) {
						if ($pl['stato']=='OPEN') $this->lamentato[$klam]['flag_pitlane']=1;
						if ($pl['stato']=='WAIT') $this->lamentato[$klam]['flag_pitlane']=2;
						break;
					}
 				}
			}
		//}
		
		//SCRIVI NOTE = INFO SPECIAL
		$this->special=substr($a['des_note'],0,3);
		if ($this->special!="") {
			$this->special_obj=json_decode(substr($a['des_note'],3),true);
		}
	}
	
	function calcola_addebito() {
	
		foreach ($this->lamentato as $k_lam=>$l) {
		
			//se è il lamentato della marcatura
			//if ($k_lam==$this->cod_lamentato) {
		
				foreach ($this->addebiti as $tx) {
					if($tx['ca']==$l['inc_ca']) {
						//la mancanza del codice di tipo garanzia viene tabellato con il simbolo *
						$tg=($l['inc_cg']==""?'*':$l['inc_cg']);
						$cm=$l['inc_cm'];
						
						if ($tx['cg']==$tg && $tx['cm']==$cm) {
							//$ta=$tx[descrizione];
							$this->lamentato[$k_lam]['des_addebito']=$tx['descrizione'];
							$this->lamentato[$k_lam]['col_addebito']=$tx['colore'];
							break 1;
						}
					}
				}
			//}
		}
	}	
	
	function delta_h($rif) {
		//calcola la differenza in ore tra ADESSO e la datra di INIZIO marcatura 
		
		$now_min=((int)substr($this->now,0,2))*60+(int)substr($this->now,3,2);
		$rif_min=((int)substr($rif,0,2))*60+(int)substr($rif,3,2);
		
		$delta=($now_min-$rif_min)/60;
		
		return round($delta,2);
	}
	
	function draw() {
		echo '<table class="lam_table">';
			echo '<colgroup>';
				echo '<col span="1" width="10"/>';
				echo '<col span="1" width="80"/>';
				echo '<col span="1" width="300"/>';
				echo '<col span="1" width="100"/>';
				echo '<col span="1" width="120"/>';
				echo '<col span="1" width="70"/>';
				echo '<col span="1" width="30"/>';
				echo '<col span="1" width="150"/>';
				echo '<col span="1" width="150"/>';
				echo '<col span="1" width="210"/>';
			echo '</colgroup>';
			echo '<thead>';
				echo '<tr>';
					echo '<th></th>';
					echo '<th>Riferimento</th>';
					echo '<th>Descrizione</th>';
					echo '<th>Pos.Lav.</th>';
					echo '<th>Marc.Coll/Tot</th>';
					echo '<th>Eff.</th>';
					echo '<th>Off</th>';
					echo '<th>Addebito</th>';
					echo '<th></th>';
					echo '<th></th>';
				echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
				call_user_func(array($this, "draw_".$this->tipo));
			echo '</tbody>';
		echo '</table>';
		
		//echo json_encode($this->marcatura);
		//echo json_encode($this->lamentato);
		//echo $this->tipo;
		//echo '-'.$this->log;
		//echo json_encode($this->addebiti);
	}
	
	function draw_marc_intest($k) {
		echo '<tr>';
			echo '<td></td>';
			echo '<td>'.$this->lamentato[$k]['mov'].'</td>';
			echo '<td style="text-align:left;"><U>'.substr(strtolower($this->lamentato[$k]['des_ragsoc']),0,35).'</U></td>';
			
			if (!array_key_exists($this->lamentato[$k]['mov'],$this->odl_servizio)) {
				echo '<td>'.$this->marcatura['targa'].'</td>';
				echo '<td colspan="4" style="font-size:10pt;text-align:left;">'.substr($this->marcatura['des_veicolo'],0,40).'</td>';
				echo '<td colspan="2"></td>';
			}
			else {
				echo '<td colspan="7"></td>';
			}
		echo '</tr>';
	}
	
	function draw_aperta() {
		
		$counter=0;
		
		foreach($this->lamentato as $k_lam=>$lam) {
		
			$counter++;
			
			//se odl servizio ma il lamentato della marcatura non combacia, SALTA
			if (array_key_exists($lam['mov'],$this->odl_servizio) && $k_lam!=$this->cod_lamentato) continue;
			
			if ($counter==1) {
				$this->draw_marc_intest($k_lam);
			}
			
			echo '<tr>';
				echo '<td>';
					if ($k_lam==$this->cod_lamentato) {
					
						if ($this->special=='ANT') {
							echo '<img class="lam_icon_img" src="img/anticipata.png"/>';
						}
						elseif ($this->special=='ATT') {
							echo '<img class="lam_icon_img" src="img/attesa.png"/>';
						}
						elseif ($this->special=='PUL') {
							echo '<img class="lam_icon_img" src="img/pulizia.png"/>';
						}
						elseif ($this->special=='EXT') {
							echo '<img class="lam_icon_img" src="img/extra.png"/>';
						}
						elseif ($this->special=='SER') {
							echo '<img class="lam_icon_img" src="img/out.png"/>';
						}
						elseif ($this->special=='VRT') {
							echo '<img class="lam_icon_img" src="img/rt.png"/>';
						}
						elseif ($this->special=='PRV') {
							echo '<img class="lam_icon_img" src="img/prova.png"/>';
						}
						elseif ($this->special=='CIN') {
							echo '<img class="lam_icon_img" src="img/citnow.jpg"/>';
						}
						elseif ($this->special=='PER') {
							echo '<img class="lam_icon_img" src="img/perizia.png"/>';
						}
						else {
							echo '<img class="lam_icon_img" src="img/icon_play.png"/>';
						}
					}
				echo '</td>';
				
				echo '<td>';
					echo $lam['inc'];
				echo '</td>';
				
				echo '<td style="text-align:left;background-color:'.$lam['col_addebito'].';">';
					echo '<div>'.strtolower(substr($lam['inc_testo'],0,35)).'</div>';
					if ($this->special=='') {
						echo '<div style="font-size:9pt;font-weight:bold;color:black;">';
							echo (array_key_exists($lam['ind_inc_stato'],$this->stato_lam)?$this->stato_lam[$lam['ind_inc_stato']]:"");
						echo '</div>';
					}
				echo '</td>';
				
				$actual=($k_lam==$this->cod_lamentato?$this->delta_h($this->marcatura['t']):0);
				$marc_tot=$lam['inc_marc_chiuse']+$actual;
				
				echo '<td>';
					if ($this->special!="") {
						if (array_key_exists('limite',$this->special_obj)) {
							echo '<div style="color:fuchsia;font-weight:bold;">';
								echo number_format($this->special_obj['limite'],2,',','');
							echo '</div>';
							$perc=$this->special_obj['limite']/$actual;
							if (is_nan($perc)) $perc=0;
						}
						
						//se LIMITE non esiste la percentuale non viene scritta
					}
					else {
						echo number_format($lam['inc_pos_lav'],2,',','');
						$perc=$lam['inc_pos_lav']/$marc_tot;
					}
				echo '</td>';
		
				echo '<td>';
					echo number_format($actual,2,',','');
					if ($this->special=="") {
						echo ' ('.number_format($marc_tot,2,',','').')';
					}
				echo '</td>';
				
				echo '<td style="';
					if ($perc==0) echo 'color:black;';
					else if ($perc<1) echo 'color:red;font-weight:bold;';
					else if ($perc>1) echo 'color:#32CD32;font-weight:bold;';
				echo '">';
					if ($this->special=="" || array_key_exists('limite',$this->special_obj)) {
						echo number_format(($perc*100),1,',','');
					}
				echo '</td>';
				
				echo '<td>';
					if ($this->special=="") {
						echo $lam['inc_cod_off'];
					}
				echo '</td>';
				
				echo '<td>';
					echo $lam['des_addebito'];
				echo '</td>';
				
				if ($this->selected==1) {
				
					if ($k_lam!=$this->cod_lamentato) {
						if ($this->now_turno['tipo']!='extra') {
							echo '<td style="text-align:left;">';
								echo '<img class="lam_button_img" src="img/START.png" onclick="st_apri_nuovo(\'START\',\''.$k_lam.'\');"/>';
							echo '</td>';
						}
					}
					else {
						echo '<td style="text-align:left;">';
							echo '<span style="';
								echo ($this->marc_turno['tipo']=='extra'?"color:orange;font-weight:bold;":"");
							echo '">'.$this->marcatura['t'];
							echo '</span>';
							echo '<img style="width:12px;height:12px;margin-left:2px;" src="img/right.png"/>';
							
							//se siamo in un tempo EXTRA (fuori dai turni) scrivere il pulsante FINE
							//oppure se il collaboratore è STD_PROD=0
							if ($this->now_turno['tipo']=='extra' || $this->std_prod==0) {
								echo '<img class="lam_button_img" style="margin-left:5px;top:3px;position:relative;" src="img/FINE.png" onclick="st_apri_nuovo(\'FINE\',\'\');"/>';
							}
							//se non è una macatura speciale
							elseif (!$this->chk_servizio()) {
								echo '<img class="lam_button_img" style="margin-left:5px;top:3px;position:relative;" src="img/STOP.png" onclick="st_apri_nuovo(\'STOP\',\'\');"/>';
							}
							//se è una marcatura speciale
							else {
								if ($this->special=='PER') {
									echo '<img class="lam_button_img" src="img/PAUSE.png" onclick="st_switch(\''.$this->special_obj['rif'].'\',\''.$this->special_obj['lam'].'\',\'\',\'\');"/>';
								}
							}
						echo '</td>';
					}
					
					//OPERAZIONI POSSIBILI
					echo '<td>';
					
						echo '<div>';
					
							if ($lam['flag_pitlane']==0 && $lam['inc']==$this->cod_lamentato) {
								echo '<img class="lam_icon_img" src="img/megafono.png" style="position:relative;margin-left:20px;" onclick="st_set_pitlane(\''.$this->marcatura['cod_operaio'].'\',\''.$lam['mov'].'\',\''.$lam['inc'].'\',\''.$lam['inc_cod_off'].'\');"/>';
							}
							elseif ($lam['flag_pitlane']==1){
								echo '<img class="lam_icon_img" src="img/wait_pitlane.gif" style="position:relative;margin-left:20px;cursor:wait;" />';
							}
							elseif ($lam['flag_pitlane']==2){
								echo '<img class="lam_icon_img" src="img/thumbup.png" style="position:relative;margin-left:20px;cursor:wait;" />';
							}
							
							if ($lam['inc_cod_off']=='PU') {
								echo '<img class="lam_icon_img" src="img/perizia.png" style="position:relative;margin-left:20px;" onclick="st_apri_form_speciale(\'PER\');"/>';
							}
							
						echo '</div>';
						
						if ($lam['flag_pitlane']==0 && $lam['inc']==$this->cod_lamentato) {
							echo '<div>';
								echo '<textarea id="st_pitlane_text_'.$this->marcatura['cod_operaio'].'" style="width:100%;resize:none;" rows="2" ></textarea>';
							echo '</div>';
						}
						
					echo '</td>';
				}
				
				//se non è selected
				else {
					if ($k_lam==$this->cod_lamentato) {
						echo '<td style="text-align:left;">';
							echo '<span style="';
								echo ($this->marc_turno['tipo']=='extra'?"color:orange;font-weight:bold;":"");
							echo '">'.$this->marcatura['t'];
							echo '</span>';
							echo '<img style="width:12px;height:12px;margin-left:2px;" src="img/right.png"/>';
						echo '</td>';
					}
					else {
						echo '<td></td>';
					}
					
					echo '<td></td>';
				}
				
			echo '</tr>';
		}
		
		/*echo '<tr>';
			echo json_encode($this->lamentato);
		echo '</tr>';*/
	}
	
	function draw_ultima() {
	
		foreach($this->lamentato as $k_lam=>$lam) {
		
			if ($lam['ind_chiuso']=='S') break;
			if (array_key_exists($lam['mov'],$this->odl_servizio)) break;
	
			if ($k_lam!=$this->cod_lamentato) continue;
			
			$this->draw_marc_intest($k_lam);
			
			echo '<tr>';
				echo '<td>';
					echo '<img class="lam_icon_img" src="img/icon_pause.png"/>';
				echo '</td>';
				
				echo '<td>';
					echo $lam['inc'];
				echo '</td>';
				
				echo '<td style="text-align:left;border:3px solid;border-color:'.$lam['col_addebito'].';">';
					echo '<div>'.strtolower(substr($lam['inc_testo'],0,35)).'</div>';
					echo '<div style="font-size:9pt;font-weight:bold;color:black;">';
						echo (array_key_exists($lam['ind_inc_stato'],$this->stato_lam)?$this->stato_lam[$lam['ind_inc_stato']]:"");
					echo '</div>';
				echo '</td>';
				
				echo '<td>';
					echo number_format($lam['inc_pos_lav'],2,',','');
				echo '</td>';
				
				echo '<td>';
					echo number_format($lam['inc_marc_chiuse'],2,',','');
				echo '</td>';
				
				$perc=$lam['inc_pos_lav']/$lam['inc_marc_chiuse'];
				echo '<td style="';
					if ($perc==0) echo 'color:black;';
					else if ($perc<1) echo 'color:red;font-weight:bold;';
					else if ($perc<1.05) echo 'color:#DAA520;font-weight:bold;';
					else if ($perc>1) echo 'color:#32CD32;font-weight:bold;';
				echo '">';
					echo number_format(($perc*100),1,',','');
				echo '</td>';
				
				echo '<td>';
					echo $lam['inc_cod_off'];
				echo '</td>';
				
				echo '<td>';
					echo $lam['des_addebito'];
				echo '</td>';
				
				if ($this->selected==1) {
					echo '<td>';
						echo '<img class="lam_button_img" src="img/PAUSE.png" onclick="st_switch(\''.$lam['mov'].'\',\''.$lam['inc'].'\',\'\',\'\');"/>';
					echo '</td>';
					
					echo '<td></td>';
				}
				else {
					echo '<td></td>';
					echo '<td></td>';
				}
				
			echo '</tr>';
		}
		
		/*echo '<tr>';
			echo json_encode($this->lamentato);
		echo '</tr>';*/
	}
	
	function draw_lam_chiusura() {
		echo '<div style="text-align:left;margin-left:10px;background-color:'.$this->lamentato[$this->cod_lamentato]['col_addebito'].';">';
			echo '<div style="margin-left:5px;">'.strtolower(substr($this->lamentato[$this->cod_lamentato]['inc_testo'],0,30)).'</div>';
			if ($this->special=='') {
				echo '<div style="font-size:9pt;font-weight:bold;color:black;">';
					echo (array_key_exists($this->lamentato[$this->cod_lamentato]['ind_inc_stato'],$this->stato_lam)?$this->stato_lam[$this->lamentato[$this->cod_lamentato]['ind_inc_stato']]:"");
				echo '</div>';
			}
			//echo '<input id="st_open_form_odl_attuale" type="hidden" value="'.$this->lamentato[$this->cod_lamentato][mov].'"/>';
		echo '</div>';
	}
	
	function draw_lam_start($id) {
		echo '<div style="text-align:left;margin-left:10px;background-color:'.$this->lamentato[$id]['col_addebito'].';">';
			echo '<div style="margin-left:5px;">'.strtolower(substr($this->lamentato[$id]['inc_testo'],0,30)).'</div>';
			echo '<div style="font-size:9pt;font-weight:bold;color:black;">';
				echo (array_key_exists($this->lamentato[$id]['ind_inc_stato'],$this->stato_lam)?$this->stato_lam[$this->lamentato[$id]['ind_inc_stato']]:"");
			echo '</div>';
			//echo '<input id="st_open_form_odl_attuale" type="hidden" value="'.$this->lamentato[$this->cod_lamentato][mov].'"/>';
		echo '</div>';
	}
	
	function get_lam_aperto() {
		return $this->lamentato[$this->cod_lamentato]['mov'];
	}
	
	function chk_servizio() {
		if (array_key_exists($this->lamentato[$this->cod_lamentato]['mov'],$this->odl_servizio)) return true;
		else return false;
	}
	
	function get_marc_limite() {
		return $this->marc_turno['limite'];
	}
}
?>