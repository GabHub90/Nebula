<?php
function draw_cal($mese,$anno,$rifdata,$tabella) {

	//echo '<div>'.$anno.$mese.$giorno.'</div>';

	//$grid_day=array();
	//$day_grid=array();
	
	$oggi=date('Ymd');
	
	$mesi=array(Gennaio,Febbraio,Marzo,Aprile,Maggio,Giugno,Luglio,Agosto,Settembre,Ottobre,Novembre,Dicembre);
	
	echo '<table class="cal_tab" width="310">';
		echo "<colgroup>";
			//echo '<col width="15"/>';
			echo '<col span="1" width="10"/>';
			echo '<col span="6" width="50"/>';
		echo "</colgroup>";
		echo "<thead>";
			echo "<tr>";
				//echo "<th></th>";
				//COMMAND
				echo '<th class="cal_command" colspan="7">';
					echo '<div style="position:relative;">';
						echo $mesi[(int)($mese)-1].'  '.$anno;
						//se sono avanti nel tempo
						if (((int)$mese>(int)date('m') && (int)$anno=(int)date('Y')) || (int)$anno>(int)date('Y')) {
							//se mese più uno
							if ($anno.$mese==date('Ym',strtotime('next month',mktime(0,0,0,(int)date('m'),1,(int)date('Y'))))) {
								$rif=mktime(0,0,0,(int)date('m'),(int)date('d'),(int)date('Y'));
							}
							else {
								$rif=strtotime("last day of last month",mktime(0,0,0,(int)$mese,1,(int)$anno));
							}
							echo '<img class="cal_arrow" style="position:absolute;left:5px;top:3px;" src="img/left.png" onclick="graffa_set_rifdata(\''.date("Ymd",$rif).'\');"/>';
						}
						$rif=strtotime("first day of next month",mktime(0,0,0,(int)$mese,1,(int)$anno));
						echo '<img class="cal_arrow" style="position:absolute;right:5px;top:3px;" src="img/right.png" onclick="graffa_set_rifdata(\''.date("Ymd",$rif).'\');"/>';
					echo '</div>';
				echo '</th>';
			echo "</tr>";
			echo "<tr>";
				//echo "<th></th>";
				echo "<th>Dom</th>";
				echo "<th>Lun</th>";
				echo "<th>Mar</th>";
				echo "<th>Mer</th>";
				echo "<th>Gio</th>";
				echo "<th>Ven</th>";
				echo "<th>Sab</th>";
			echo "</tr>";
		echo "</thead>";
		
		echo "<tbody>";
	
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
		if (!array_key_exists($riga,$tabella)) break;
		
		echo "<tr>";
		
		/*scrivi numero settimana
		echo "<td>";
			echo $tabella[$riga];
		echo "</td>";*/
		
		$passato=1;
		
		//scrivi giorni
		for($d=1;$d<8;$d++) {
			
			//segnala che il giorno appartiene al mese visualizzato
			$m_flag=1;
			//calcola la differenza in timestamp tra il blocco attuale e la data di default
			//$delta=mktime(0,0,0,(int)substr($tabella[$riga.$d][tag],4,2),(int)substr($tabella[$riga.$d][tag],6,2),(int)substr($tabella[$riga.$d][tag],0,4))-mktime(0,0,0,(int)date("m",$param[inizio]),(int)date("d",$param[inizio]),(int)date("Y",$param[inizio]));
			
			echo '<td class="cal_block_td" style="position:relative;';
				if ($tabella[$riga.$d][wd]==0 or $tabella[$riga.$d][f]==1) {
					echo 'border-color: #ff0000;';
				}
				
				//se il mese non è il corrente = sfondo grigio
				if (substr($tabella[$riga.$d][tag],4,2)!=$mese) {
					echo 'background-color:#e8e8e3;';
					$m_flag=0;
				}
				//se è un giorno di chiusura = sfondo immagine in base al tipo di chiusura
				elseif ($tabella[$riga.$d][c]!='no') {
					if ($tabella[$riga.$d][c]=='T') echo 'background-image:url(img/chiusura_T.png);';
					//if ($tabella[$riga.$d][c]=='M') echo 'background-image:url(img/chiusura_M.png);';
					//if ($tabella[$riga.$d][c]=='P') echo 'background-image:url(img/chiusura_P.png);';
					//$txt.='background-repeat: no-repeat;';
					echo 'background-size: 100%;';
				}
				echo '">';
				
				//DIV DAY
				//identificazione dei giorni selezionabili
				if ($tabella[$riga.$d][tag]>=$oggi) $passato=0;
				
				$num_day=substr($tabella[$riga.$d][tag],6);
				$txt='<div class="cal_day" ';
				if ($tabella[$riga.$d][wd]==0 or $tabella[$riga.$d][f]==1) $txt.='style="color:red;"';
				$txt.='><span class="cal_day_num" ';
				//attiva onclick sul numero se l'azienda è aperta
				if ($tabella[$riga.$d][wd]!=0 && $tabella[$riga.$d][f]!=1 && ($tabella[$riga.$d][c]=='no' || $tabella[$riga.$d][c]=='P' || $tabella[$riga.$d][c]=='M') && $m_flag==1 && $passato==0) {
					$txt.='onclick="graffa_set_rifdata(\''.$anno.$mese.$num_day.'\');"';
				}
				$txt.='>'.$num_day.'</span>';
				//se il giorno è quello di default segnala
				if ($tabella[$riga.$d][tag]==$rifdata && $tabella[$riga.$d][wd]!=0 && $tabella[$riga.$d][f]!=1 && ($tabella[$riga.$d][c]=='no' || $tabella[$riga.$d][c]=='P' || $tabella[$riga.$d][c]=='M') && $m_flag==1) $txt.='<img class="cal_default" src="img/fermaglio.png"/>';
				$txt.='</div>';
				echo $txt;
				/*DIV FESTA
				$txt='<div class="cal_festa" ';
				if ($tabella[$riga.$d][f]==1) $txt.='style="color:red;"';
				$txt.='>';
				if ($tabella[$riga.$d][wd]!=0) $txt.=$tabella[$riga.$d][nome];
				$txt.='</div>';
				echo $txt;*/
					
				//DIV INFORMAZIONI
				echo '<div id="d_'.$tabella[$riga.$d][tag].'" class="cal_info"></div>';
				
				/*DIV BLOCK
				echo '<div id="div_'.$riga.$d.'" class="cal_block">';
					//se giorno non abilitato
					if ($passato==1 && $tabella[$riga.$d][f]==0 && $tabella[$riga.$d][wd]!=0) {
						//echo '<img style="position:relative;width:25px;height:25px;top:10px;" src="img/jmp.png"';
					}
					
					//se giorno abilitato
					elseif ($tabella[$riga.$d][wd]!=0 && $tabella[$riga.$d][f]!=1 && ($tabella[$riga.$d][c]=='no' || $tabella[$riga.$d][c]=='P' || $tabella[$riga.$d][c]=='M') && $m_flag==1){
					
						//verifica dati statistici
						$tot_pren=$pren[$tabella[$riga.$d][tag]][rp]+$pren[$tabella[$riga.$d][tag]][ro]+$pren[$tabella[$riga.$d][tag]][nn];
						
						//se una categoria comprende il 100% dei casi
						if ($pren[$tabella[$riga.$d][tag]][rp]==$tot_pren) {
							echo '<div class="ot_stat_pren" style="background-color:#c1faba;"></div>';
							echo '<div class="ot_stat_pren" style="background-color:#c1faba;font-size:12pt;font-weight:bold;">'.$tot_pren.'</div>';
							echo '<div class="ot_stat_pren" style="background-color:#c1faba;"></div>';
						}
						elseif ($pren[$tabella[$riga.$d][tag]][ro]==$tot_pren) {
							echo '<div class="ot_stat_pren" style="background-color:#fdc7c7;"></div>';
							echo '<div class="ot_stat_pren" style="background-color:#fdc7c7;font-size:12pt;font-weight:bold;">'.$tot_pren.'</div>';
							echo '<div class="ot_stat_pren" style="background-color:#fdc7c7;"></div>';
						}
						elseif ($pren[$tabella[$riga.$d][tag]][nn]==$tot_pren) {
							echo '<div class="ot_stat_pren" style="background-color:white;"></div>';
							echo '<div class="ot_stat_pren" style="background-color:white;font-size:12pt;font-weight:bold;">'.$tot_pren.'</div>';
							echo '<div class="ot_stat_pren" style="background-color:white;"></div>';
						}
						else {
							echo '<div class="ot_stat_pren" style="background-color:#fdc7c7;">'.$pren[$tabella[$riga.$d][tag]][ro].'</div>';
							echo '<div class="ot_stat_pren" style="background-color:white;">'.$pren[$tabella[$riga.$d][tag]][nn].'</div>';
							echo '<div class="ot_stat_pren" style="background-color:#c1faba;">'.$pren[$tabella[$riga.$d][tag]][rp].'</div>';
						}
					}
				echo "</div>";*/
				
				//alimenta $grid_day e $day_grid
				//$day_grid[$tabella[$riga.$d][tag]]=$riga.$d;
				//$grid_day[$riga.$d]=$tabella[$riga.$d][tag];
				
			echo "</td>";
		}
		
		echo "</tr>";
	}
	
	echo "</tbody>";
	echo "</table>";
}
?>