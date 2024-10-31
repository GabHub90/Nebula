<?php
include('../chime_ini.php');
include('calendario.php');
require('func_data.php');

//connessione al database
$db_handler=mysqli_connect($server_db,$user,$pw);

$param=(array) json_decode($_POST[param]);

$mesi=array(Gennaio,Febbraio,Marzo,Aprile,Maggio,Giugno,Luglio,Agosto,Settembre,Ottobre,Novembre,Dicembre);


//SE QUERY DI TIPO GIORNO

if ($param[tipo]=="GIORNO") {

	//recupero della tabella del calendario
	$cal=new calendario($param[anno]);
	$tabella=$cal->mese($param[mese]);
	
	echo '<table width="575">';
		echo "<colgroup>";
			echo '<col width="15"/>';
			echo '<col span="7" width="80"/>';
		echo "</colgroup>";
		echo "<thead>";
			echo "<tr>";
				echo "<th></th>";
				//COMMAND
				echo '<th class="cal_command" colspan="7">';
					echo $mesi[(int)$param[mese]-1]."  ".$param[anno];
					$rif=strtotime("-1 month",mktime(0,0,0,(int)$param[mese],1,(int)$param[anno]));
					echo '<img class="cal_arrow" style="position:absolute;left:25px;" src="img/left.png" onclick="d_cal('.date("\'m\',\'Y\'",$rif).');"/>';
					$rif=strtotime("+1 month",mktime(0,0,0,(int)$param[mese],1,(int)$param[anno]));
					echo '<img class="cal_arrow" style="position:absolute;left:100%;margin-left:-75px;" src="img/right.png" onclick="d_cal('.date("\'m\',\'Y\'",$rif).');"/>';
				echo '</th>';
			echo "</tr>";
			echo "<tr>";
				echo "<th></th>";
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
		
		//scrivi numero settimana
		echo "<td>";
			echo $tabella[$riga];
		echo "</td>";
		
		//scrivi giorni
		for($d=1;$d<8;$d++) {
			
			//calcola la differenza in timestamp tra il blocco attuale e la data di default
			$delta=mktime(0,0,0,(int)substr($tabella[$riga.$d][tag],4,2),(int)substr($tabella[$riga.$d][tag],6,2),(int)substr($tabella[$riga.$d][tag],0,4))-mktime(0,0,0,(int)date("m",$param[inizio]),(int)date("d",$param[inizio]),(int)date("Y",$param[inizio]));
			
			$modo="nuovo";
			
			echo "<td>";
				//DIV BLOCK
				$txt='<div class="cal_block" style="';
				if ($tabella[$riga.$d][wd]==0 or $tabella[$riga.$d][f]==1) $txt.='border-color: #ff0000;';
				//se il mese non è il corrente = sfondo grigio
				if (substr($tabella[$riga.$d][tag],4,2)!=$param[mese]) $txt.='background-color:#e8e8e3;';
				$txt.='">';
				echo $txt;
					//DIV DAY
					$txt='<div class="cal_day" ';
					if ($tabella[$riga.$d][wd]==0 or $tabella[$riga.$d][f]==1) $txt.='style="color:red;"';
					$txt.='>'.substr($tabella[$riga.$d][tag],6);
					//se il giorno è quello di default segnala con un punto
					if (substr($tabella[$riga.$d][tag],4,4)==date("md",$param[inizio])) $txt.='<img class="cal_default" src="img/fermaglio.png"/>';
					$txt.='</div>';
					echo $txt;
					//DIV FESTA
					$txt='<div class="cal_festa" ';
					if ($tabella[$riga.$d][c]==1 or $tabella[$riga.$d][f]==1) $txt.='style="color:red;"';
					$txt.='>'.$tabella[$riga.$d][nome].'</div>';
					echo $txt;
					
					//DIV STORIA
					$stato_indice="";
					if ($param[indice]>0) {
						$txt='<div class="cal_storico">';
							//seleziona il db di default
							mysqli_select_db($db, $db_handler);
							$query='SELECT * FROM indici WHERE tag="'.$tabella[$riga.$d][tag].'" AND report='.$param[rep_id];
							if($result=mysqli_query($query)) {
								if($row=mysqli_fetch_array($result)) {
									//seleziona icona in base al tipo di salvataggio
									if ($row[stato]=="saved") {
										$icon="img/saved.png";
										$stato_indice="saved";
									}
									if ($row[stato]=="done") {
										$icon="img/spunta.png";
										$stato_indice="done";
									}
									$txt.='<img class="cal_spunta" src="'.$icon.'"/><span style="margin-left:5px;">'.format_tohtml($row[data]).'</span>';
									//Se esiste lo storico MODO=storico
									$modo="storico";
								}
							}
						$txt.='</div>';
						echo $txt;
					}
					
					//DIV CLICK
					$txt='<div class="cal_click" ';
					// Se la DATA è selezionabile per l'elaborazione
					if ($delta==0 || ($param[back]==1 && $delta<0) || ($param[forw]==1 && $delta>0)) {
						//se esisteva lo storico (vedi sopra) ma la data è attuale allora il MODO diventa "attuale"
						if ($modo=='storico') $modo='attuale';
						$txt.='style="cursor:pointer;" onclick="set_data(\''.$tabella[$riga.$d][tag].'\',\''.$modo.'\',\''.$stato_indice.'\');"';
					}
					// Se DATA è solo visualizzabile come storico
					else {
						if ($modo=='storico') $txt.='style="cursor:pointer;" onclick="go_story(\''.$tabella[$riga.$d][tag].'\',\''.$modo.'\');"';
					}
					$txt.='></div>';
					//echo "<div>".$delta."</div>";
					//echo "<div>".$param[back]."/".$param[forw]."</div>";
					echo $txt;
					
				//CHIUSURA DIV BLOCK
				echo "</div>";
			echo "</td>";
		}
		
		echo "</tr>";
	}
	
	echo "</tbody>";
	echo "</table>";
	
	//DIV FORM DATA
	echo '<div class="cal_form">';
		echo '<div>Data Selezionata:<span id="form_data" class="cal_form_data">seleziona</span></div>';
		echo '<script type="text/javascript">';
			echo 'fill_query();';
		echo '</script>';
	echo '</div>';
}

//SE QUERY DI TIPO MESE
if ($param[tipo]=="MESE") {
	echo '<table width="575">';
		echo "<thead>";
			echo "<tr>";
				//COMMAND
				echo '<th class="cal_command">';
					echo $mesi[(int)$param[mese]-1]."  ".$param[anno];
					$rif=strtotime("-1 month",mktime(0,0,0,(int)$param[mese],1,(int)$param[anno]));
					echo '<img class="cal_arrow" style="position:absolute;left:2px;" src="img/left.png" onclick="d_cal('.date("\'m\',\'Y\'",$rif).');"/>';
					$rif=strtotime("+1 month",mktime(0,0,0,(int)$param[mese],1,(int)$param[anno]));
					echo '<img class="cal_arrow" style="position:absolute;left:100%;margin-left:-75px;" src="img/right.png" onclick="d_cal('.date("\'m\',\'Y\'",$rif).');"/>';
				echo '</th>';
			echo "</tr>";
		echo "</thead>";
		
		//calcola la differenza in timestamp tra il blocco attuale e la data di default
		$delta=mktime(0,0,0,(int)$param[mese],1,(int)$param[anno])-mktime(0,0,0,(int)date("m",$param[inizio]),1,(int)date("Y",$param[inizio]));
		
		$modo="nuovo";
		
		$tag=$param[anno].$param[mese]."01";
		
		echo "<tbody>";
			echo "<tr>";
				echo "<td>";
					//DIV BLOCK
					echo '<div class="cal_block">';
						//DIV STORIA
						$stato_indice="";
						if ($param[indice]>0) {
							$txt='<div class="cal_storico">';
								//seleziona il db di default
								mysqli_select_db($db, $db_handler);
								$query='SELECT * FROM indici WHERE tag="'.$tag.'" AND report='.$param[rep_id];
								if($result=mysqli_query($query)) {
									if($row=mysqli_fetch_array($result)) {
										//seleziona icona in base al tipo di salvataggio
										if ($row[stato]=="saved") {
											$icon="img/saved.png";
											$stato_indice="saved";
										}
										if ($row[stato]=="done") {
											$icon="img/spunta.png";
											$stato_indice="done";
										}
										$txt.='<div style="position:relative;top:15px;"><img class="cal_spunta" src="'.$icon.'"/></div><div style="position:relative;top:20px;">'.format_tohtml($row[data]).'</div>';
										//Se esiste lo storico MODO=storico
										$modo="storico";
									}
								}
							$txt.='</div>';
							echo $txt;
						}
						
						//DIV CLICK
						$txt='<div class="cal_click" ';
						// Se la DATA è selezionabile per l'elaborazione
						if ($delta==0 || ($param[back]==1 && $delta<0) || ($param[forw]==1 && $delta>0)) {
							//se esisteva lo storico (vedi sopra) ma la data è attuale allora il MODO diventa "attuale"
							if ($modo=='storico') $modo='attuale';
							$txt.='style="cursor:pointer;" onclick="set_data(\''.$tag.'\',\''.$modo.'\',\''.$stato_indice.'\');"';
						}
						// Se DATA è solo visualizzabile come storico
						else {
							if ($modo=='storico') $txt.='style="cursor:pointer;" onclick="go_story(\''.$tag.'\',\''.$modo.'\');"';
						}
						$txt.='></div>';
						//echo "<div>".$delta."</div>";
						//echo "<div>".$param[back]."/".$param[forw]."</div>";
						echo $txt;
					
					echo "</div>";
				echo "</td>";
			echo "</tr>";
			
		echo "</tbody>";
	echo '</table>';
	
	//DIV FORM DATA
	echo '<div class="cal_form">';
		echo '<div>Data Selezionata:<span id="form_data" class="cal_form_data">seleziona</span></div>';
		echo '<script type="text/javascript">';
			echo 'fill_query();';
		echo '</script>';
	echo '</div>';
}				

mysqli_close($db_handler);
?>