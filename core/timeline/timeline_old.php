<?php
class Timeline {

	public $res;
	public $orari=array();
	public $perc=1;
	public $width=0;
	public $margin=0;
	public $blocks=0;
	public $w=0;
	
	public $log=array();

	function __construct($res) {
		//res=minuti tra un'orario e l'altro
		$this->res=$res;
		
		$this->tl_reset();
	}
	
	function tl_reset() {
		$index=0;
		$this->perc=1;
		
		while ($index<1440) {
			//DEFAULT periodi interamente esclusi
			//la percentuale è del singolo blocco
			$this->orari[$index]=array("min"=>$index,"end"=>($index+$this->res),"tag"=>$this->mintostring($index),"point"=>0,"half"=>0,"stato"=>0,"perc"=>1);
			if (($index%30)==0) $this->orari[$index]['half']=1;
			if (($index%60)==0) $this->orari[$index]['point']=1;
			$index+=$this->res;
		}
	}
	
	function stringtomin($str) {
		return ((int)substr($str,0,2)*60)+(int)substr($str,3,2);
	}
	
	function mintostring($min) {
		$h=floor($min/60);
		$m=$min-($h*60);
		
		$str="";
		
		if ($h<10) $str.='0';
		$str.=$h.':';
		if ($m<10) $str.="0";
		$str.=$m;
		
		return $str;
	}
	
	function includi_generico($arr) {
		//$arr= ("08:15","12:15")
		$temp=$this->stringtomin($arr[0]);
		$pointer=(round($temp/$this->res))*$this->res;
		$temp=$this->stringtomin($arr[1]);
		$fine=(round($temp/$this->res))*$this->res;
		
		while ($pointer<$fine) {
			$this->orari[$pointer]['stato']=1;
			$pointer+=$this->res;
		}	
	}
	
	function escludi_generico($arr) {
		//$arr= ("08:15","12:15")
		$temp=$this->stringtomin($arr[0]);
		$pointer=(round($temp/$this->res))*$this->res;
		$temp=$this->stringtomin($arr[1]);
		$fine=(round($temp/$this->res))*$this->res;
		
		while ($pointer<$fine) {
			$this->orari[$pointer]['stato']=0;
			$pointer+=$this->res;
		}
	}
	
	
	//includi un periodo
	function includi($arr) {
		//$arr= [0] => Array ( [0] => 08:15 [1] => i ) [1] => Array ( [0] => 08:45 [1] => "")
		foreach ($arr as $a) {
			if ($a[1]!='f') {
				$this->orari[$this->stringtomin($a[0])]['stato']=1;
			}
		}
	}

	function occupa_and($arr) {
		//$arr= ("08:15","12:15")
		//una volta inclusi gli orari, mantiene in stato 1 solo le intersezioni con l'array passato
		$pi=$this->stringtomin($arr[0]);
		$pf=$this->stringtomin($arr[1]);
		
		foreach ($this->orari as $p=>$o) {
			//se è fuori dall'intervallo setta stato a 2
			if ($o['stato']==1) {
				if ($p<$pi || $p>=$pf) $this->orari[$p]['stato']=2;
			}
		}
	}

	function resetta_occupa_and() {

		foreach ($this->orari as $p=>$o) {
			//se è fuori dall'intervallo setta stato a 2
			if ($o['stato']==2) $this->orari[$p]['stato']=1;
		}
	}

	//escludi un periodo
	function escludi($arr) {
		//$arr= [0] => Array ( [0] => 08:15 [1] => i ) [1] => Array ( [0] => 08:45 [1] => "")
		foreach ($arr as $a) {
			if ($a[1]!='f') {
				$this->orari[$this->stringtomin($a[0])]['stato']=0;
			}
		}
	}
	
	function escludi_occupato($arr) {
		$pointer=$this->stringtomin($arr['ora_i']);
		$fine=$this->stringtomin($arr['ora_f']);
		
		while ($pointer<$fine) {
			$this->orari[$pointer]['stato']=2;
			$pointer+=$this->res;
		}
	}
	
	function escludi_timeline($arr) {
		//array è un errai ORARI
		foreach ($arr as $k=>$a) {
			if ($a['stato']==1) $this->orari[$k]['stato']=0;
		}
	}
	
	//STATO 0:NON VALIDO
	//STATO 1:VALIDO
	//STATO 2:OCCUPATO
	
	function draw_timeline($width,$color,$flag_solo_intest) {
		
		//larghezza blocco (il margine di 24 serve per le scritte)
		$this->width=$width;
		$this->margin=6;
		$this->blocks=count($this->orari);
		$w=floor(($width-($this->margin*2))/$this->blocks);
		if ($w<4) $w=4;
		$this->w=$w;
		
		echo '<table style="position:relative;top:15px;border-collapse:collapse;font-size:8pt;color:'.$color.';" width="'.(($this->w*$this->blocks)+($this->margin*2)).'">';
		
			echo '<colgroup>';
				echo '<col span="1" width="'.$this->margin.'px"/>';
				echo '<col span="'.$this->blocks.'" width="'.$this->w.'px"/>';
				echo '<col span="1" width="'.$this->margin.'px"/>';
			echo '</colgroup>';
		
			echo '<thead>';
				echo '<th></th>';
				
					foreach ($this->orari as $o) {
						echo '<th style="position:relative;height:5px;">';
							if ($o['point']==1) {
								echo '<span style="position:absolute;top:-10px;left:-5px;">'.substr($o['tag'],0,2).'</span>';
							}
						echo '</th>';
					}
				echo '<th style="position:relative;height:5px;">';
					echo '<span style="position:absolute;top:-10px;left:-5px;">00</span>';
				echo '</th>';
			echo '</thead>';
			
			echo '<tbody>';
			
				echo '<tr>';
					echo '<td></td>';
					
					foreach ($this->orari as $o) {
						echo '<td style="height:5px;';
							if ($o['point']==1) echo 'border-left:1px solid '.$color.';';
						echo '"></td>';
					}
					echo '<td style="border-left:1px solid '.$color.';"></td>';
				echo '</tr>';
				
				echo '<tr>';
					echo '<td></td>';
					foreach ($this->orari as $o) {
						echo '<td style="height:5px;';
							if ($o['half']==1) echo 'border-left:1px solid '.$color.';';
						echo '"></td>';
					}
					echo '<td style="border-left:1px solid '.$color.';"></td>';
				echo '</tr>';
				
				echo '<tr>';
					echo '<td></td>';
					foreach ($this->orari as $o) {
						echo '<td style="height:5px;border-left:1px solid '.$color.';border-bottom:1px solid '.$color.';"></td>';
					}
					echo '<td style="border-left:1px solid '.$color.';"></td>';
				echo '</tr>';
				
				if ($flag_solo_intest==0) {
				
					echo '<tr>';
						echo '<td></td>';
						foreach ($this->orari as $o) {
							echo '<td style="height:5px;';
								if ($o['stato']==0) echo 'background-color:red;';
								if ($o['stato']==1) echo 'background-color:#7FFF00;';
								if ($o['stato']==2) echo 'background-color:#8A2BE2;';
							echo '"></td>';
						}
						echo '<td></td>';
					echo '</tr>';
				}
				
			echo '</tbody>';
		
		echo '</table>';
			
	}
	
	function draw_timeline_option_da($val) {
	
		$stato=-1;
		
		foreach ($this->orari as $k=>$o) {
			if ($stato==-1) $stato=$o['stato'];
			else {
				if ($stato!=$o['stato']) {
					if ($o['stato']==1 ) $stato=1;
					elseif ($o['stato']==0 || $o['stato']==2) {
						$stato=$o['stato'];
						echo '<option value="" disabled="disabled">-----</option>';
					}
				}
			}
			
			if ($stato!=1) continue;
			
			$txt=$this->mintostring($k);
			echo '<option value="'.$k.'" ';
				if ($k==$val) echo 'selected="selected"';
			echo '>'.$txt.'</option>';
		}
		
	}
	
	function draw_timeline_option_a($val) {
	
		$stato=-1;
		$count=0;
		
		foreach ($this->orari as $k=>$o) {
			if ($stato==-1) $stato=$o['stato'];
			else {
				if ($stato!=$o['stato']) {
					if ($o['stato']==1 ) {
						$stato=1;
						$count=0;
					}
					elseif ($o['stato']==0 || $o['stato']==2) {
						$stato=$o['stato'];
						$txt=$this->mintostring($k);
						if ($o['stato']!=2) {
							echo '<option value="'.$k.'" ';
								if ($k==$val) echo 'selected="selected"';
							echo '>'.$txt.'</option>';
						}
						echo '<option value="" disabled="disabled">-----</option>';
					}
				}
			}
			
			if ($stato!=1) continue;
			
			$count++;
			if ($count==1) continue;
			
			$txt=$this->mintostring($k);
			echo '<option value="'.$k.'" ';
				if ($k==$val) echo 'selected="selected"';
			echo '>'.$txt.'</option>';
		}	
	}
	
	
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
	
	function update_avalon($av,$av15,$option_perc) {
		//option_perc serve per la suddivisione della presenza di un sub_rep in più sub_rep di destinazione
		
		foreach ($this->orari as $kx=>$o) {
			if ($o['stato']==1) {
				$index=intval($o['min']/60);
				$val=($o['end']-$o['min'])*($this->perc*$o['perc']*$option_perc);
				$vv=round($val/60,3);
				$av[$index]+=$vv;
				//$av[$index]=$this->perc;
				$av15[$kx]+=$vv;
			}
		}

		ksort($av);
		ksort($av15);
		
		//return $av;
		return array('av'=>$av,'av15'=>$av15);
	}
	
	function update_avalon_ricric($av,$av15,$qta) {
	
		//$qta=round($qta*($this->perc),2);
		
		foreach ($this->orari as $kx=>$o) {
			if ($o['stato']==1) {
				$index=intval($o['min']/60);
				$perc=($o['end']-$o['min'])/60;
				$av[$index]+=round(($perc*$qta),1);
				//$av[$index]=$this->perc;
				$av15[$kx]++;
			}
		}
		
		return array('ricric'=>$av,'ricric15'=>$av15);
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
