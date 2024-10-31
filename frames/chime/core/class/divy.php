<?php 

class DivyMan {

	private $lista=array();
	
	private $check="";
	private $def_chk=0;
	
	private $bck_arr=array("white","#B0E0E6");

	function __construct($arr,$check,$def_chk) {
		$this->lista=$arr;
		$this->check=$check;
		$this->def_chk=$def_chk;
	}
	
	function get_list() {
		return $this->lista;
	}
	
	function draw_code($codice) {
		if(is_callable(array($this, $codice))){
			$res=$this->$codice();
		}
	}
	
	function check_contatto($str) {
	
		if ($this->check=='sms') {
			//elimina tutto ciò che non è numerico
			$str=preg_replace("/[^0-9.]/", "", $str);
			//verifica il numero
			return preg_match("/^[1-9]{1}[0-9]{8,9}$/", $str);
		}
	}
	
	function line5($contatto,$d_invio,$note,$k) {
		echo '<div style="font-size:13pt;">';
			echo '<div class="chime_list_contatto" style="float:left;width:78%;">'.$contatto.'</div>';
			
			echo '<div style="float:left;width:21%;text-align:right;">';
				
				//decidi in base alla qualità del contatto ed a d_invio
				if (!$this->check_contatto($contatto)) {
					echo '<img class="chime_list_icon" src="img/stop.png" />';
					echo '<input id="chime_list_chk_'.$k.'" class="chime_list_chk" style="visibility:hidden;" type="checkbox" disabled="disabled" />';
					echo '<input id="chime_list_stato_'.$k.'" type="hidden" value="nogood" />';
				}
				else {
					if ($d_invio=="") {
						//echo '<img class="chime_list_icon" src="img/stop.png" />';
						echo '<input id="chime_list_chk_'.$k.'" class="chime_list_chk" type="checkbox" ';
							if ($this->def_chk==1) echo ' checked="checked"'; 
						echo '/>';
						echo '<input id="chime_list_stato_'.$k.'" type="hidden" value="good" />';
					}
					elseif ($d_invio=="-1") {
						if ($this->def_chk==1) echo '<img class="chime_list_icon" src="img/stop.png" />';
						echo '<input id="chime_list_chk_'.$k.'" class="chime_list_chk" type="checkbox" />';
						echo '<input id="chime_list_stato_'.$k.'" type="hidden" value="excluded" />';
					}
					elseif ($d_invio=="-2") {
						echo '<img class="chime_list_icon" src="img/error.png" />';
						echo '<input id="chime_list_chk_'.$k.'" class="chime_list_chk" type="checkbox" checked="checked" />';
						echo '<input id="chime_list_stato_'.$k.'" type="hidden" value="error" />';
					}
					else {
						echo '<img class="chime_list_icon" src="img/spunta.png" />';
						echo '<input id="chime_list_chk_'.$k.'" class="chime_list_chk" style="visibility:hidden;" type="checkbox" checked="checked" disabled="disabled" />';
						echo '<input id="chime_list_stato_'.$k.'" type="hidden" value="ok" />';
					}
				}
			
			echo '</div>';
			
			echo '<div style="clear:both;"></div>';
		echo '</div>';
		
		//LINE 6
		echo '<div id="chime_list_error_'.$k.'" class="chime_error">'.$note.'</div>';
	}
	
	function avv_app() {
		
		/*
		t1.num_rif_movimento AS rif,
		t1.pren AS d_prenotazione,
		t1.num_rif_veicolo AS veicolo,
		isnull(t1.lams,'') AS lamentati,
		isnull(t4.des_ragsoc,'') AS ragsoc,
		t4.num_rif_anagrafica AS anagrafica,
		t4.tel1 AS contatto,
		t3.mat_telaio AS telaio,
		t3.mat_targa AS targa,
		t3.des_veicolo AS descrizione,
		isnull(t5.d_invio,'') AS d_invio
		t5.note AS note
		*/
		
		//DATA INVIO = -1 => escluso
		//DATA INVIO = -2 => errore invio
		
		$rif='';
		$t_contatto='';
		$bck=1;

		foreach ($this->lista as $k=>$l) {
		
			//escludi in base al contatto
			//se il secondo contatto è vuoto non scriverlo
			if ($rif==$l[rif]) {
				if ($l[contatto]=='' || $l[contatto]==$t_contatto) {
					unset($this->lista[$k]);
					continue;
				}
			}
			
			$t_contatto=$l[contatto];
		
			echo '<div class="chime_list_div" ';
				if ($rif!=$l[rif]) {
					$rif=$l[rif];
					if ($bck==1) $bck=0;
					else $bck=1;
				}

				echo 'style="background-color:'.$this->bck_arr[$bck].'" ';
			echo '>';
			
				//LINE 1
				echo '<div>';
					echo db_todata($l[d_prenotazione]);
					echo ' - <b>'.$l[rif].'</b>';
				echo '</div>';
				
				//LINE 2
				echo '<div>';
					echo $l[targa];
					echo ' - '.$l[telaio];
				echo '</div>';
				
				//LINE 3
				echo '<div>';
					echo '<b>'.strtoupper($l[ragsoc]).'</b>';
				echo '</div>';
				
				//LINE 4
				echo '<div style="font-size:12pt;">';
					echo strtolower(str_replace('<br/>','-',$l[lamentati]));
				echo '</div>';
				
				//LINE 5
				$this->line5($l[contatto],$l[d_invio],$l[note],$k);
				
			echo '</div>';
		}
	}
	
	function feedback_pass() {
			
		/*
		t1.num_rif_movimento as rif,
		CONVERT(VARCHAR(8),t3.dat_documento_i,112) AS d_fatt,
		t1.cod_officina,
		t1.cod_accettatore,
		t7.des_accettatore,
		t5.cod_movimento,
		t1.num_km as km,
		t5.cod_anagra AS anagra,
		t2.mat_targa as targa,
		t2.mat_telaio as telaio,
		t2.cod_marca,
		t3.val_imponibile_tot as imponibile,
		t3.val_totale as ivato,
		t4.ind_tipo_addebito,
		isnull(t6.des_ragsoc,'') AS ragsoc, 
		isnull(t6.contatto,'') AS contatto,
		dbo.fnOT_lams(t1.num_rif_movimento) as lamentati,
		isnull(t8.d_invio,'') AS d_invio, 
		t8.note AS note
		*/
		
		//DATA INVIO = -1 => escluso
		//DATA INVIO = -2 => errore invio
		
		$rif='';
		$t_contatto='';
		$bck=1;

		foreach ($this->lista as $k=>$l) {
		
			//escludi in base al contatto
			//se il secondo contatto è vuoto non scriverlo
			if ($rif==$l[rif]) {
				if ($l[contatto]=='' || $l[contatto]==$t_contatto) {
					unset($this->lista[$k]);
					continue;
				}
			}
			
			$t_contatto=$l[contatto];
		
			echo '<div class="chime_list_div" ';
				if ($rif!=$l[rif]) {
					$rif=$l[rif];
					if ($bck==1) $bck=0;
					else $bck=1;
				}

				echo 'style="background-color:'.$this->bck_arr[$bck].'" ';
			echo '>';
			
				//LINE 1
				echo '<div>';
					echo db_todata($l[d_fatt]);
					echo ' - <b>'.$l[rif].'</b>';
					echo ' -&nbsp;<span style="color:orange;font-weight:bold;">'.$l[des_accettatore].'</span>';
				echo '</div>';
				
				//LINE 2
				echo '<div>';
					echo $l[targa];
					echo ' - '.$l[telaio];
					echo ' -&nbsp;<b>'.number_format($l[ivato],2,',','').'&nbsp;€</b>';
				echo '</div>';
				
				//LINE 3
				echo '<div>';
					echo '<b>'.strtoupper($l[ragsoc]).'</b>';
				echo '</div>';
				
				//LINE 4
				echo '<div style="font-size:12pt;">';
					echo strtolower(str_replace('<br/>','-',$l[lamentati]));
				echo '</div>';
				
				//LINE 5
				$this->line5($l[contatto],$l[d_invio],$l[note],$k);
								
			echo '</div>';
		}
	}
	
}

?>