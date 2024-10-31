<?php
//error_reporting(E_ERROR | E_PARSE);
error_reporting(0);

include('../ststop_ini.php');
include('../ststop_func.php');
include('maestro.php');

//connessione al database
$connectionInfo = array( "Database"=>$db, "UID"=>$user, "PWD"=>$pw ,"CharacterSet" => "UTF-8");
$db_handler=sqlsrv_connect("srvdb",$connectionInfo);

date_default_timezone_set ("Europe/Rome");

$collaboratori=(array)json_decode($_POST[param]);

$reparto=$_POST[reparto];

//Lettura dei turni e li mette in un array key=[marcatempo_giorno]
$query="SELECT * FROM MAESTRO_coll_tur AS t1 INNER JOIN MAESTRO_turni AS t2 ON t1.turno=t2.tag";
if($result=sqlsrv_query($db_handler,$query)) {
	while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
		$turni[$row[marcatempo].'_'.$row[giorno]]=$row;
	}
}

//lettura tipi addebiti
$addebiti=get_addebiti($db_handler);

//creazione intervallo di ricerca collaboratori attivi
$int_coll="(";
$i=0;
foreach ($collaboratori as $key=>$coll) {
	if ($i!=0) $int_coll.=",";
	$int_coll.="'".$key."'";
	$i++;
}
$int_coll.=")";

//acquisisci riferimenti ODL SERVIZIO
$servizio=get_odl_servizio($db_handler);

//lettura delle marcature APERTE da DB

$maestro= new Maestro();
$lines=$maestro->get_tempi_aperti($int_coll);
/*mysqli_select_db('ststop',$db_handler);
$query="SELECT * FROM LISTA_TEMPI_APERTI WHERE cod_operaio IN ".$int_coll." ORDER BY cod_operaio";
$result=mysqli_query($db_handler,$query);
while ($row=mysqli_fetch_assoc($result)) {
	$lines[]=$row;
}*/


//Verifica sovrapposizione delle marcature e crea l'array OPEN[cod_operaio]
$actual_op="";
foreach ($lines as $val) {
	if($val[cod_operaio]==$actual_op) {
		$open[$val[cod_operaio]][error]=1;
		$open[$val[cod_operaio]][error_txt]='Ci sono più marcature aperte per il tecnico.';	
	}
	else {
		$open[$val[cod_operaio]]=$val;
		$open[$val[cod_operaio]][error]=0;
		$open[$val[cod_operaio]][error_txt]='';
	}
	
	$actual_op=$val[cod_operaio];
}

//recupera i LAMENTATI degli ordini dove ci sono le marcature aperte (senza errori)
//INTERVALLO
$int_lam="(";
$i=0;
foreach ($open as $val) {
	if($val[error]==0) {
		if ($i!=0) $int_lam.=",";
		$int_lam.="'".$val[num_rif_movimento]."'";
		$i++;
	}
}
$int_lam.=")";

//QUERY
$lamentati=$maestro->get_lamentati($int_lam);
	
/*$query="SELECT * FROM LISTA_LAMENTATI WHERE mov IN ".$int_lam." ORDER BY mov,inc";
$result=mysqli_query($db_handler,$query);
while ($row=mysqli_fetch_assoc($result)) {
	$lamentati[$row[mov].$row[inc]]=$row;
	$lamentati[$row[mov].$row[inc]][val_aperte]=0.00;
}*/

//assegna ad ogni lamentato il valore delle marcature aperte
foreach ($open as $key=>$op) {
	//verifica se la marcatura non è in errore (più marcature per lo stesso tecnico)
	if($op[error]==0) {
		$wday=date("w",mktime(0,0,0,(int)substr($op[d],4,2),(int)substr($op[d],6,2),(int)substr($op[d],0,4)));
		//se esistono informazioni sul turno
		if(array_key_exists($op[cod_operaio].'_'.$wday,$turni)) {
			//$turno_marc=($op[t]<=$turni[$op[cod_operaio].'_'.$wday][mat_f])?'M':'P';
			//$turno_now=(date('H:i')<=$turni[$op[cod_operaio].'_'.$wday][mat_f])?'M':'P'; 
			
			//turni MM (mat_i) MM (mat_f) MX (pom_i) PP (pom_f) PX
			//$turno_marc=($op[t]<=$turni[$op[cod_operaio].'_'.$wday][mat_i])?'MM':($op[t]<$turni[$op[cod_operaio].'_'.$wday][mat_f])?'MM':($op[t]<$turni[$op[cod_operaio].'_'.$wday][pom_i])?'MX':($op[t]<$turni[$op[cod_operaio].'_'.$wday][pom_f])?'PP':'PX';
			//$turno_now=(date('H:i')<=$turni[$op[cod_operaio].'_'.$wday][mat_i])?'MM':(date('H:i')<$turni[$op[cod_operaio].'_'.$wday][mat_f])?'MM':(date('H:i')<$turni[$op[cod_operaio].'_'.$wday][pom_i])?'MX':(date('H:i')<$turni[$op[cod_operaio].'_'.$wday][pom_f])?'PP':'PX';
			
			if ($op[t]<=$turni[$op[cod_operaio].'_'.$wday][mat_i]) $turno_marc='MM';
			elseif ($op[t]<$turni[$op[cod_operaio].'_'.$wday][mat_f]) $turno_marc='MM';
			elseif ($op[t]<$turni[$op[cod_operaio].'_'.$wday][pom_i]) $turno_marc='MX';
			elseif ($op[t]<$turni[$op[cod_operaio].'_'.$wday][pom_f]) $turno_marc='PP';
			else $turno_marc='PX';
			
			if (date('H:i')<=$turni[$op[cod_operaio].'_'.$wday][mat_i]) $turno_now='MM';
			elseif (date('H:i')<$turni[$op[cod_operaio].'_'.$wday][mat_f]) $turno_now='MM';
			elseif (date('H:i')<$turni[$op[cod_operaio].'_'.$wday][pom_i]) $turno_now='MX';
			elseif (date('H:i')<$turni[$op[cod_operaio].'_'.$wday][pom_f]) $turno_now='PP';
			else $turno_now='PX';
			
			
			
			//Verifica se siamo nello stesso giorno
			if($op[d]==date('Ymd')) {
			
				//se inizio è MX e now è in un turno successivo(PP o PX), consideralo = PP
				if ($turno_marc=='MX' && substr($turno_now,0,1)=='P') $turno_marc='PP';
				
				// se siamo nello stesso turno (P=PX e M=MX)	
				if (substr($turno_marc,0,1)==substr($turno_now,0,1)) {
			
					//se le seconde lettere sono differenti = inizio nel turno e fine fuori dal turno = CONFERMA STRAORDINARIO
					if (substr($turno_now,1,1)!=substr($turno_marc,1,1)) {
						$open[$key][error]=4;
						$open[$key][error_txt]='Conferma straordinario';
						//creazione array per la gestione in JS degli errori $ERROR_JS[ERRORE][TECNICO][...]
						$error_js[$open[$key][error]][$key][cod_operaio]=$op[cod_operaio];
						$error_js[$open[$key][error]][$key][turno]=$turno_marc;
						$error_js[$open[$key][error]][$key][ora_i]=$op[t];
						$error_js[$open[$key][error]][$key][day]=$wday;
					}
					else {
						$lamentati[$op[num_rif_movimento].$op[cod_inconveniente]][val_aperte]+=delta_h(date("H:i"),$op[t]);
						//se siamo in un tempo X (fuori dal turno)
						if (substr($turno_now,1,1)=='X') $lamentati[$op[num_rif_movimento].$op[cod_inconveniente]][turno_X]=1;
					}
				}
				else {
					$open[$key][error]=2;
					$open[$key][error_txt]='Marcatura aperta da turno precedente [ '.convert_date_to_italian($op[d]).' '.$op[t].' ]';
					//creazione array per la gestione in JS degli errori $ERROR_JS[ERRORE][TECNICO][...]
					$error_js[$open[$key][error]][$key][cod_operaio]=$op[cod_operaio];
					$error_js[$open[$key][error]][$key][turno]=$turno_marc;
					$error_js[$open[$key][error]][$key][ora_i]=$op[t];
					$error_js[$open[$key][error]][$key][day]=$wday;
				}	
			}
			else {
				$open[$key][error]=2;
				$open[$key][error_txt]='Marcatura aperta da turno precedente [ '.convert_date_to_italian($op[d]).' '.$op[t].' ]';
				//creazione array per la gestione in JS degli errori $ERROR_JS[ERRORE][TECNICO][...]
				$error_js[$open[$key][error]][$key][cod_operaio]=$op[cod_operaio];
				$error_js[$open[$key][error]][$key][turno]=$turno_marc;
				$error_js[$open[$key][error]][$key][ora_i]=$op[t];
				$error_js[$open[$key][error]][$key][day]=$wday;
			}
		}
		else {
			$open[$key][error]=3;
			$open[$key][error_txt]='Turno non tabellato.';
		}
	}
}

//recupera le ultime marcature chiuse
$ultime=$maestro->get_ultime_timbrature();

/*$query="SELECT * FROM LISTA_ULTIME_TIMBRATURE";
$result=mysqli_query($db_handler,$query);
while ($row=mysqli_fetch_assoc($result)) {
	$ultime[$row[cod_operaio]]=$row;
}*/

//recupera i LAMENTATI degli ordini dove ci sono le ULTIME MARCATURE CHIUSE
//INTERVALLO
$int_lam="(";
$i=0;
foreach ($ultime as $val) {
	if ($i!=0) $int_lam.=",";
	$int_lam.="'".$val[mov]."'";
	$i++;
}
$int_lam.=")";

//QUERY
$c_lamentati=$maestro->get_lamentati($int_lam);
	
/*$query="SELECT * FROM LISTA_LAMENTATI WHERE mov IN ".$int_lam." ORDER BY mov,inc";
$result=mysqli_query($db_handler,$query);
while ($row=mysqli_fetch_assoc($result)) {
	$c_lamentati[$row[mov].$row[inc]]=$row;
	$c_lamentati[$row[mov].$row[inc]][val_aperte]=0.00;
}
mysqli_select_db('maestro',$db_handler);
*/

//includi i lamentati C_LAMENTATI nell'array LAMENTATI
foreach($c_lamentati as $keyl=>$lam) {
	if (!array_key_exists($keyl,$lamentati)) {
		$lamentati[$keyl]=$lam;
	}
}

//leggi tipi di chiusura
$query="SELECT * FROM STSTOP_stato_chiusura";
$result=sqlsrv_query($db_handler,$query);
while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
	$chiusura[$row[indice]]=$row[testo];
}

//leggi righe PITLANE_OPEN
$pitlane_open=$maestro->ststop_get_pitlane_open();


//=================================================================================

//scrivi i DIV dei collaboratori
foreach ($collaboratori as $key=>$coll) {

	echo '<div id="coll_div_'.$key.'" class="coll_div">';

		echo '<div class="coll_intest_div">';
			//intestazione
			echo '<div class="coll_intest_txt">';
				echo '['.$key.']'.' <b>'.$coll->nome.' '.$coll->cognome.'</b> ('.$coll->gruppo.')';
				
				//se il tecnico ha delle RICHIESTE PITLANE in piedi
				if (array_key_exists($key,$pitlane_open)) {
					echo '<img class="lam_icon_img" src="img/megafono.png" style="position:relative;left:20px;width:12px;height:12px;cursor:wait;" />';
				}
				
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
			echo '</div>';
		echo '</div>';
		
		//lamentati
		echo '<div class="coll_lam_block">';
		
		//se per il collaboratore NON ci sono marcature aperte crea la linea nell'array OPEN
		if (!array_key_exists($key,$open)) {
			$open[$key][error]=99;
			$open[$key][error_txt]='Non ci sono marcature aperte.';
			//se ci sono marcature CHIUSE
			if (array_key_exists($key,$ultime)) {
				$open[$key][num_rif_movimento]=$ultime[$key][mov];
				$open[$key][cod_inconveniente]=$ultime[$key][inc];
				$open[$key][cod_operaio]=$ultime[$key][cod_operaio];
				$open[$key][num_riga]=$ultime[$key][num_riga];
				$open[$key][d]=$ultime[$key][d];
				$open[$key][t]=$ultime[$key][t];
			}
		}
		
		if(array_key_exists($key,$open)) {
				//ERRORS (1=doppia marcatura / 2=marcatura rimasta aperta / 3=turno non tabellato / 4=autorizzazione straordinario)
				if($open[$key][error]>0) {
					echo '<div class="error">'.$open[$key][error_txt].'</div>';
					//se l'errore è di tipo 2
					if ($open[$key][error]==2) { 
						echo '<div id="error_b_'.$key.'" class="lam_icon_div" style="top:3px;left:920px;width:250px;" onclick="allinea_timbratura(\''.$key.'\',\''.$open[$key][error].'\');">';
							echo '<span class="error_button">Allinea alla chiusura del turno</span>';
						echo '</div>';
					}
					
					if ($open[$key][error]==4) { 
						echo '<div id="error_b_'.$key.'" class="lam_icon_div" style="top:3px;left:920px;width:250px;" onclick="conferma_straordinario(\''.$key.'\',\''.$open[$key][error].'\');">';
							echo '<span class="error_button">Conferma straordinario</span>';
						echo '</div>';
					}
					
				}
					
				//NUCLEO CENTRALE
				//se non ci sono errori oppure l'errore è la mancanza di marcatura
				if($open[$key][error]==0 || $open[$key][error]==99) {
					echo '<div class="lam_table_div">';
					echo '<table class="lam_table">';
					echo '<colgroup>';
						echo '<col span="1" width="10"/>';
						echo '<col span="1" width="80"/>';
						echo '<col span="1" width="300"/>';
						echo '<col span="1" width="100"/>';
						echo '<col span="1" width="120"/>';
						echo '<col span="1" width="70"/>';
						echo '<col span="1" width="30"/>';
						echo '<col span="1" width="130"/>';
						echo '<col span="1" width="70"/>';
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
							echo '<th>M</th>';
							echo '<th>Addebito</th>';
							echo '<th></th>';
							echo '<th></th>';
						echo '</tr>';
					echo '</thead>';
					echo '<tbody>';
					$lamline=0;

					foreach ($lamentati as $keylam=>$lam) {
						$line=($lamline==$lam[mov]?$line:1);
						$lamline=$lam[mov];
						//se il lamentato appartiene all'ordine dove c'è la marcatura aperta
						if($lam[mov]==$open[$key][num_rif_movimento]) {
							$focus=($lam[inc]==$open[$key][cod_inconveniente]?1:0);
							$serv=(array_key_exists($lam[mov],$servizio)?1:0);
							$nop=($open[$key][error]==99?1:0);
							$RT=($coll->gruppo=="RT"?1:0);
							//verifica se è una macatura speciale
							$special='';
							if ($open[$key][des_note]!='') {
								//alcune note non identificano più la marcatura come speciale
								//EXK - è una EXT elaborata correttamente
								if ($open[$key][des_note]!='EXK') {
									$special=substr($open[$key][des_note],0,3);
									$special_obj=json_decode(substr($open[$key][des_note],3));
								}
							}
							
							// non scrivere il lamentato se è di SERVIZIO e il collaboratore non è attivo su di esso oppure è una ultima marcatura chiusa.
							//if( !($focus==0 && ($serv==1 || $nop==1)) ) {
							if (($focus==1 && !($serv==1 && $nop==1)) || ($focus==0 && ($serv==0 && $nop==0))) {
								//se è la prima riga ma non è una marcatura di servizio scrivi la riga di riferimento dell'ordine di lavoro
								if($line==1 && $serv==0) {
									echo '<tr>';
										echo '<td></td>';
										echo '<td>'.$lam[mov].'</td>';
										echo '<td style="text-align:left;"><U>'.addslashes(substr(strtolower($lam[des_ragsoc]),0,35)).'</U></td>';
										echo '<td style="text-align:left;">'.$lam[nome].'</td>';
										echo '<td colspan="5"></td>';
									echo '</tr>';
									$line++;
								}
								echo '<tr>';
									//INDICATORE DI STATO
									echo '<td>';
										if ($focus==1 && $nop==0) {
											if ($special=='ANT') {
												echo '<img class="lam_icon_img" src="img/anticipata.png"/>';
											}
											elseif ($special=='ATT') {
												echo '<img class="lam_icon_img" src="img/attesa.png"/>';
											}
											elseif ($special=='PUL') {
												echo '<img class="lam_icon_img" src="img/pulizia.png"/>';
											}
											elseif ($special=='EXT') {
												echo '<img class="lam_icon_img" src="img/extra.png"/>';
											}
											elseif ($special=='SER') {
												echo '<img class="lam_icon_img" src="img/out.png"/>';
											}
											elseif ($special=='VRT') {
												echo '<img class="lam_icon_img" src="img/rt.png"/>';
											}
											elseif ($special=='PRV') {
												echo '<img class="lam_icon_img" src="img/prova.png"/>';
											}
											elseif ($special=='CIN') {
												echo '<img class="lam_icon_img" src="img/citnow.jpg"/>';
											}
											else {
												echo '<img class="lam_icon_img" src="img/icon_play.png"/>';
											}
										}
										if ($focus==1 && $nop==1) echo '<img class="lam_icon_img" src="img/icon_pause.png"/>';
										
									echo '</td>';
									
									//INFO
									$chiusura_txt=(array_key_exists($lam[ind_inc_stato],$chiusura)?$chiusura[$lam[ind_inc_stato]]:"");
									//se la marcatura è di servizio va evidenziata la riga
									if ($serv==1) {
										//se è una marcatura di NON PRESENZA
										if ($special=='SER' || $special=='VRT') $color_sp='#fc93eb';
										//se è prova
										elseif ($special=='PRV') $color_sp='#9ccefd';
										else $color_sp='#fdde9c';
										
										echo '<td style="background-color:'.$color_sp.'">'.$lam[inc].'</td>';
										echo '<td style="text-align:left;background-color:'.$color_sp.'">'.$lam[inc_testo];
										if ($special=="EXT") echo '<span style="font-size:9pt;"> ('.$special_obj->code.')</span>';
										echo '</td>';
									}
									else {
										echo '<td>'.$lam[inc].'</td>';
										if ($focus==1) {
											echo '<td style="text-align:left;">';
												echo '<div>'.substr(strtoupper($lam[inc_testo]),0,28).'</div>';
												if($nop==1) {
													echo '<div style="font-size:9pt;font-weight:bold;color:orange;">'.$chiusura_txt.'</div>';
												}
											echo '</td>';
										}
										else {
											echo '<td style="text-align:left;">';
												echo '<div>'.substr(strtolower($lam[inc_testo]),0,35).'</div>';
												echo '<div style="font-size:9pt;font-weight:bold;color:orange;">'.$chiusura_txt.'</div>';
											echo '</td>';
										}
									}
									
									//TEMPI
									//tempi fatturate
									if ($serv==1 && $special=='') {
										echo '<td></td>';
									}
									else {
										if ($special=='') {
											$lam_fatt=round($lam[inc_pos_lav],2);
											echo '<td>'.number_format($lam[inc_pos_lav],2,",",".").'</td>';
										}
										else echo '<td>'.number_format($special_obj->limite,2,",",".").'</td>';
									}
									//se è l'inconveniente con la marcatura APERTA
									if($focus==1 && $nop==0) {
										$marc=delta_h(date("H:i"),$open[$key][t]);
										//se marcatura di servizio non scrivi i totali
										if($serv==1) {
											//analisi se marcatura speciale ATT o PUL
											if ($special=='ATT' || $special=='PUL') {
												$allerta=((float)$lam[val_aperte]>(float)$special_obj->limite?'fuchsia':'black');
												echo '<td style="color:'.$allerta.';">'.number_format($marc,2,".","").'</td>';
											}
											else {
												echo '<td>'.number_format($marc,2,".","").'</td>';
											}
										}
										else {
											$marc_tot=round(($lam[inc_marc_chiuse]+$lam[val_aperte]),2);
											echo '<td>'.number_format(round($marc,2),2,".","").' ('.number_format($marc_tot,2,".","").')</td>';
										}
									}
									//se non è l'inconveniente con la marcatura aperta
									else {
										$marc_tot=round($lam[inc_marc_chiuse],2);
										echo '<td>'.$lam[inc_marc_chiuse].'</td>';
									}
									
									if ($serv!=1) {	
										//EFFICIENZA
										if ($marc_tot>0) $eff=round(($lam_fatt/$marc_tot),2)*100;
										if ($eff>=105) $col='#0bbf09';
										elseif ($eff>=100) $col='#edc935';
										else $col='red';
										
										echo '<td style="font-size:11pt;color:'.$col.';">';
											if 	($lam_fatt>0 && $marc_tot>0) echo $eff.' %';
										echo '</td>';
										
										//MULTIMARCATURA
										echo '<td style="font-size:9pt;">'.($lam[num_tecnici]>1?'<b style="color:red;">Si</b>':'No').'</td>';
										
										//VERIFICA ADDEBITO
										echo '<td style="font-size:10pt;">';
											$ta="";
											foreach ($addebiti as $tx) {
												if($tx[ca]==$lam[inc_ca]) {
													//la mancanza del codice di tipo garanzia viene tabellato con il simbolo *
													$tg=($lam[inc_cg]==""?'*':$lam[inc_cg]);
													$cm=$lam[inc_cm];
													
													if ($tx[cg]==$tg && $tx[cm]==$cm) {
														$ta=$tx[descrizione];
													}
												}
											}
											echo $ta;
										echo '</td>';
									}
									else echo '<td colspan="3"></td>';
									
									if ($serv!=1 || $special!='') {
										//START / STOP
										echo '<td><div id="ststop_b_'.$key.'" style="display:none;">';
											//se è una ULTIMA TIMBRATURA
											if ($nop==1) {
												echo '<img class="lam_button_img" src="img/PAUSE.png" onclick="riprendi_timbratura(\'PAUSE\',\''.$key.'\',\''.$keylam.'\');"/>';
											}
											//se è una timbratura APERTA
											else {
												if($focus==1) {
													if ($special=='') {
													
														//se straordinario
														if ($lam[turno_X]==1) {
															echo '<img class="lam_button_img" src="img/FINE.png" onclick="chiudi_nopresenza(\'\',\''.$key.'\',\'\')"/>';
														}
														
														else {
															if ($RT==0) {
																echo '<img class="lam_button_img" src="img/STOP.png" onclick="switch_timbratura(\'STOP\',\''.$key.'\',\''.$keylam.'\');"/>';
															}
															//Per l'RT la chiusura di una timbratura non prevede l'apertura di un tempo improduttivo
															else {
																echo '<img class="lam_button_img" src="img/STOP.png" onclick="chiudi_timbratura(\'STOP\',\''.$key.'\',\''.$keylam.'\');"/>';
															}
														}
													}
													//START-STOP per le marcature speciali
													if ($special!='') {
														if ($special=='ANT') {
															$sp_tipo=($RT==0?'STOP':'STOP_RT');
															//chiudi_anticipata(coll,marcatura fittizia)
															echo '<img class="lam_button_img" src="img/STOP.png" onclick="chiudi_speciale(\''.$sp_tipo.'\',\''.$key.'\',\''.$keylam.'\',\''.$special.'\');"/>';
														}
														if ($special=='PUL') {
															$sp_tipo=($RT==0?'STOP':'STOP_RT');
															echo '<img class="lam_button_img" src="img/STOP.png" onclick="chiudi_speciale(\''.$sp_tipo.'\',\''.$key.'\',\''.$keylam.'\',\''.$special.'\');"/>';
														}
														if ($special=='EXT') {
															$sp_tipo=($RT==0?'STOP':'STOP_RT');
															echo '<img class="lam_button_img" src="img/STOP.png" onclick="chiudi_speciale(\''.$sp_tipo.'\',\''.$key.'\',\''.$keylam.'\',\'PUL\');"/>';
														}
														if ($special=='MCQ') {
															$sp_tipo=($RT==0?'STOP':'STOP_RT');
															echo '<img class="lam_button_img" src="img/STOP.png" onclick="chiudi_speciale(\''.$sp_tipo.'\',\''.$key.'\',\''.$keylam.'\',\'PUL\');"/>';
														}
														if ($special=='SER') {
															$sp_tipo=($RT==0?'STOP':'STOP_RT');
															echo '<img class="lam_button_img" src="img/STOP.png" onclick="chiudi_nopresenza(\''.$sp_tipo.'\',\''.$key.'\',\''.$keylam.'\',\'SER\');"/>';
														}
														if ($special=='VRT') {
															$sp_tipo=($RT==0?'STOP':'STOP_RT');
															echo '<img class="lam_button_img" src="img/STOP.png" onclick="chiudi_nopresenza(\''.$sp_tipo.'\',\''.$key.'\',\''.$keylam.'\',\'VRT\');"/>';
														}
														if ($special=='PRV') {
															echo '<img class="lam_button_img" src="img/STOP.png" onclick="esito_prova(\''.$key.'\',\''.$special_obj->odl.'\');"/>';
														}
														if ($special=='CIN') {
															echo '<img class="lam_button_img" src="img/PAUSE.png" onclick="chiudi_CIN(\''.$key.'\',\''.$special_obj->rif.'\',\''.$special_obj->lam.'\');"/>';
														}
														if ($special=='PER') {
															echo '<img class="lam_button_img" src="img/PAUSE.png" onclick="chiudi_PER(\''.$key.'\',\''.$special_obj->rif.'\',\''.$special_obj->lam.'\');"/>';
														}
													}
												}
												else {
													echo '<img class="lam_button_img" src="img/START.png" onclick="switch_timbratura(\'START\',\''.$key.'\',\''.$keylam.'\');"/>';
												}
											}
	
										echo '</div></td>';
									}
									
									//BOTTONI
									if($focus==1 && $nop==0 && $serv!=1) {
										echo '<td>';
										
											echo '<div id="aiuto_b_'.$key.'" class="lam_icon_div">';
												//echo '<img class="lam_icon_img" src="img/aiuto.png"/>';
												
												if ($RT==0) {
												
													//solo se VWS (CITNOW)
													if ($reparto=='VWS') {
														echo '<img class="lam_icon_img" style="position:absolute;left:60px;top:0px;" src="img/CITNOW.jpg" onclick="switch_timbratura_special(\'STOP\',\'CIN\');"/>';
														}
														
														//solo se UPM (PERIZIA)
														if ($reparto=='UPM') {
															echo '<img class="lam_icon_img" style="position:absolute;left:60px;top:0px;" src="img/perizia.png" onclick="switch_timbratura_special(\'STOP\',\'PER\');"/>';
														}
														
														/*solo se marcatura X (
														if ($lam[turno_X]==1) {
															echo '<img class="lam_icon_img" style="position:absolute;left:60px;top:0px;" src="img/exit.png" onclick=""/>';
														}*/
														
														//echo $turno_marc.$turno_now;							
													
													echo '</div>';
													//se VRT
													if($coll->gruppo=="vRT") {
														echo '<div id="prova_b_'.$key.'" class="lam_icon_div" style="left:60px;"><img class="lam_icon_img" style="width:25px;" src="img/diagnosi.png"/></div>';
													}
													/*else {
														echo '<div class="lam_icon_div" style="left:100px;border-color:white;"></div>';
													}*/
													echo '<div id="extra_b_'.$key.'" class="lam_icon_div" style="left:130px;width:140px;text-align:left;">';
														//echo '<span style="position:relative;top:-5px;left:2px;">No ODL:</span>';
														//echo '<img class="lam_icon_img" src="img/extra.png" style="position:relative;left:10px;" onclick="open_extra(\'EXT\');"/>';
														//echo '<img class="lam_icon_img" src="img/1500.png" style="position:relative;left:20px;" onclick="open_extra(\'MCQ\');"/>';
														
														//se è un lamentato OOS non visionare l'icona
														//POTEVA BASTARE LA VARIABIL $SERV ???? NON MI RICORDO
													//if ($open[$key][tipo_commessa]!='OOS') {
													if ($open[$key][flag_pitlane]==0) {
														echo '<img class="lam_icon_img" src="img/megafono.png" style="position:relative;left:20px;" onclick="set_pitlane(\''.$key.'\');"/>';
													}
													else {
														echo '<img class="lam_icon_img" src="img/wait_pitlane.gif" style="position:relative;left:20px;cursor:wait;" />';
													}
														
												}
												//if $RT==1
												else {
													if ($open[$key][flag_pitlane]==0) {
														echo '<img class="lam_icon_img" src="img/megafono.png" style="position:relative;left:20px;" onclick="set_pitlane(\''.$key.'\');"/>';
													}
													else {
														echo '<img class="lam_icon_img" src="img/wait_pitlane.gif" style="position:relative;left:20px;cursor:wait;" />';
													}
												}
											echo '</div>';
											
										echo'</td>';
									}
									
									else {
										echo '<td></td>';
									}
									
								echo '</tr>';
							}
							//Se marcatura speciale eventualmente aggiungere una seconda riga
							if($special!='' && $focus==1) {
								if($special=='ANT') {
									$lam_special=$lamentati[$special_obj->rif.$special_obj->lam];
									echo '<tr>';
										echo '<td></td>';
											echo '<td>'.$lam_special[mov].'</td>';
											echo '<td style="text-align:left;"><U>'.addslashes(substr(strtolower($lam_special[des_ragsoc]),0,35)).'</U></td>';
											echo '<td style="text-align:left;">'.$lam_special[nome].'</td>';
									echo '</tr>';
									
									echo '<tr>';
										echo '<td></td>';
										echo '<td>'.$lam_special[inc].'</td>';
										$chiusura_txt=(array_key_exists($lam_special[ind_inc_stato],$chiusura)?$chiusura[$lam_special[ind_inc_stato]]:"");
										echo '<td style="text-align:left;">';
											echo '<div>'.substr(strtolower($lam_special[inc_testo]),0,35).'</div>';
											echo '<div style="font-size:9pt;font-weight:bold;color:orange;">'.$chiusura_txt.'</div>';
										echo '</td>';
										echo '<td colspan="5"></td>';
										echo '<td>';
											if ($lam_special[mov]!='') {
												echo '<div id="ststop_special_b_'.$key.'" style="display:none;">';
													//riprendi_anticipata(coll,marcatura fittizia)
													echo '<img class="lam_button_img" src="img/PAUSE.png" onclick="riapri_anticipata(\'PAUSE\',\''.$key.'\',\''.$keylam.'\');"/>';
												echo '</div>';
											}
										echo '</td>';
									echo '</tr>';
								}
								
								if($special=='ATT') {
									$lam_special=$lamentati[$special_obj->rif.$special_obj->lam];
									//SOLO se l'ultima timbratura NON è fittizia
									if (!array_key_exists($lam_special[mov],$servizio)) {
										echo '<tr>';
											echo '<td></td>';
												echo '<td>'.$lam_special[mov].'</td>';
												echo '<td style="text-align:left;"><U>'.addslashes(substr(strtolower($lam_special[des_ragsoc]),0,35)).'</U></td>';
												echo '<td style="text-align:left;">'.$lam_special[nome].'</td>';
										echo '</tr>';
										
										echo '<tr>';
											echo '<td></td>';
											echo '<td>'.$lam_special[inc].'</td>';
											$chiusura_txt=(array_key_exists($lam_special[ind_inc_stato],$chiusura)?$chiusura[$lam_special[ind_inc_stato]]:"");
											echo '<td style="text-align:left;">';
												echo '<div>'.substr(strtolower($lam_special[inc_testo]),0,35).'</div>';
												echo '<div style="font-size:9pt;font-weight:bold;color:orange;">'.$chiusura_txt.'</div>';
											echo '</td>';
											echo '<td colspan="5"></td>';
											echo '<td>';
												if ($lam_special[mov]!='') {
													echo '<div id="ststop_special_b_'.$key.'" style="display:none;">';
														//
														//echo '<img class="lam_button_img" src="img/PAUSE.png" onclick="riapri_anticipata(\'PAUSE\',\''.$key.'\',\''.$keylam.'\');"/>';
														echo '<img class="lam_button_img" src="img/PAUSE.png" onclick="riapri_extra(\'START\',\''.$key.'\',\''.$special_obj->rif.$special_obj->lam.'\');"/>';
													echo '</div>';
												}
											echo '</td>';
										echo '</tr>';
									}
								}
								
								
								if($special=='PUL') {
									$lam_special=$lamentati[$special_obj->rif.$special_obj->lam];
									//SOLO se l'ultima timbratura NON è fittizia
									if (!array_key_exists($lam_special[mov],$servizio)) {
										echo '<tr>';
											echo '<td></td>';
												echo '<td>'.$lam_special[mov].'</td>';
												echo '<td style="text-align:left;"><U>'.addslashes(substr(strtolower($lam_special[des_ragsoc]),0,35)).'</U></td>';
												echo '<td style="text-align:left;">'.$lam_special[nome].'</td>';
										echo '</tr>';
										
										echo '<tr>';
											echo '<td></td>';
											echo '<td>'.$lam_special[inc].'</td>';
											$chiusura_txt=(array_key_exists($lam_special[ind_inc_stato],$chiusura)?$chiusura[$lam_special[ind_inc_stato]]:"");
											echo '<td style="text-align:left;">';
												echo '<div>'.substr(strtolower($lam_special[inc_testo]),0,35).'</div>';
												echo '<div style="font-size:9pt;font-weight:bold;color:orange;">'.$chiusura_txt.'</div>';
											echo '</td>';
											echo '<td colspan="5"></td>';
											echo '<td>';
												if ($lam_special[mov]!='') {
													echo '<div id="ststop_special_b_'.$key.'" style="display:none;">';
														//riprendi_anticipata(coll,marcatura fittizia)
														echo '<img class="lam_button_img" src="img/PAUSE.png" onclick="riapri_anticipata(\'PAUSE\',\''.$key.'\',\''.$keylam.'\');"/>';
													echo '</div>';
												}
											echo '</td>';
										echo '</tr>';
									}
								}
								
								if($special=='EXT') {
									$lam_special=$lamentati[$special_obj->rif.$special_obj->lam];
									//SOLO se l'ultima timbratura NON è fittizia
									if (!array_key_exists($lam_special[mov],$servizio)) {
										echo '<tr>';
											echo '<td></td>';
												echo '<td>'.$lam_special[mov].'</td>';
												echo '<td style="text-align:left;"><U>'.addslashes(substr(strtolower($lam_special[des_ragsoc]),0,35)).'</U></td>';
												echo '<td style="text-align:left;">'.$lam_special[nome].'</td>';
										echo '</tr>';
										
										echo '<tr>';
											echo '<td></td>';
											echo '<td>'.$lam_special[inc].'</td>';
											$chiusura_txt=(array_key_exists($lam_special[ind_inc_stato],$chiusura)?$chiusura[$lam_special[ind_inc_stato]]:"");
											echo '<td style="text-align:left;">';
												echo '<div>'.substr(strtolower($lam_special[inc_testo]),0,35).'</div>';
												echo '<div style="font-size:9pt;font-weight:bold;color:orange;">'.$chiusura_txt.'</div>';
											echo '</td>';
											echo '<td colspan="5"></td>';
											echo '<td>';
												if ($lam_special[mov]!='') {
													echo '<div id="ststop_special_b_'.$key.'" style="display:none;">';
														//
														echo '<img class="lam_button_img" src="img/PAUSE.png" onclick="riapri_extra(\'START\',\''.$key.'\',\''.$special_obj->rif.$special_obj->lam.'\');"/>';
													echo '</div>';
												}
											echo '</td>';
										echo '</tr>';
									}
								}
								
								if($special=='MCQ') {
									$lam_special=$lamentati[$special_obj->rif.$special_obj->lam];
									//SOLO se l'ultima timbratura NON è fittizia
									if (!array_key_exists($lam_special[mov],$servizio)) {
										echo '<tr>';
											echo '<td></td>';
												echo '<td>'.$lam_special[mov].'</td>';
												echo '<td style="text-align:left;"><U>'.addslashes(substr(strtolower($lam_special[des_ragsoc]),0,35)).'</U></td>';
												echo '<td style="text-align:left;">'.$lam_special[nome].'</td>';
										echo '</tr>';
										
										echo '<tr>';
											echo '<td></td>';
											echo '<td>'.$lam_special[inc].'</td>';
											$chiusura_txt=(array_key_exists($lam_special[ind_inc_stato],$chiusura)?$chiusura[$lam_special[ind_inc_stato]]:"");
											echo '<td style="text-align:left;">';
												echo '<div>'.substr(strtolower($lam_special[inc_testo]),0,35).'</div>';
												echo '<div style="font-size:9pt;font-weight:bold;color:orange;">'.$chiusura_txt.'</div>';
											echo '</td>';
											echo '<td colspan="5"></td>';
											echo '<td>';
												if ($lam_special[mov]!='') {
													echo '<div id="ststop_special_b_'.$key.'" style="display:none;">';
														//
														echo '<img class="lam_button_img" src="img/PAUSE.png" onclick="riapri_extra(\'START\',\''.$key.'\',\''.$special_obj->rif.$special_obj->lam.'\');"/>';
													echo '</div>';
												}
											echo '</td>';
										echo '</tr>';
									}
								}
							}
						}						
					}
					//se RT o vRT aggiungere il lamentato PROVA
					/*if (($coll->gruppo=="vRT" || $coll->gruppo=="RT") && $special=='' && ($focus==1 && $nop==0)) {
						//echo '<tr><td colspan="10"></br></td></tr>';
						echo '<tr>';
							echo '<td></td>';
							echo '<td>X</td>';
							echo '<td class="prova_vett" style="text-align:left;">Prova vettura</td>';
							echo '<td colspan="5"></td>';
				
							//PROVA
							echo '<td>';
								echo '<div id="prova_b_'.$key.'" style="display:none;">';
									echo '<img class="lam_button_img" src="img/PROVA_b.png" onclick=""/>';
								echo '</div>';
							echo '</td>';
							echo '<td></td>';
						echo '</tr>';
					}*/
					echo '</tbody>';
					echo '</table>';
					echo '</div>';
				}
			}
			echo '</div>';
			
	echo '</div>';
	
	//echo 'reparto: '.$reparto;
	
}

// scrittura del codice JS per la creazione dell' ARRAY per la gestione degli errori
echo '<script type="text/javascript">';
	echo '_error_js=JSON.parse(\''.json_encode($error_js).'\');';
	echo '_lamentati=JSON.parse(\''.addslashes(json_encode($lamentati)).'\');';
	//echo '_lamentati=JSON.parse(\''.json_encode($lamentati).'\');';
	echo '_ststop_open='.json_encode($open).';';
echo '</script>';

sqlsrv_close($db_handler);
?>
