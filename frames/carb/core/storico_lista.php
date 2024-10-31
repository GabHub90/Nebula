<?php
error_reporting(E_ERROR | E_PARSE);
//error_reporting(0);

include('../carb_func.php');
include('../carb_ini.php');
include('maestro.php');

$connectionInfo = array( "Database"=>$db, "UID"=>$user, "PWD"=>$pw ,"CharacterSet" => "UTF-8");
$db_handler=sqlsrv_connect("srvdb",$connectionInfo);

date_default_timezone_set ("Europe/Rome");

$maestro=new Maestro();

$val=(array)json_decode($_POST[val]);

$tartel_query="";

if($val[tartel]!="") {
	$tartel=$maestro->carb_tt($val[tartel]);
	foreach ($tartel as $t) {
		$tartel_query.="'".$t[rif]."',";
	}
}

//lettura gestioni
$gestioni=$maestro->carb_st_gestioni();
//aggiungi cliente
$gestioni[_CLIENTE_]=array("testo"=>"CLIENTE","flag"=>0);
$gestioni[TANICA]=array("testo"=>"TANICA","flag"=>0);

//lettura reparti
$reparti=array();
$query="SELECT * FROM MAESTRO_reparti where tipo IN('S','V','D','N') order by descrizione";
		if($result=sqlsrv_query($db_handler,$query)) {
			while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$reparti[$row[tag]]=array("testo"=>$row[descrizione],"flag"=>0);
			}
		}
		
//lettura causali
$causali=array();
$query="SELECT * FROM CARB_causali";
if($result=sqlsrv_query($db_handler,$query)) {
	while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
		$causali[$row[codice]]=array("testo"=>$row[causale],"flag"=>0);
	}
}

$coll=array();
$query="SELECT * 
		FROM MAESTRO_collaboratori
		WHERE stato='1'
		order by cognome
		";
		if($result=sqlsrv_query($db_handler,$query)) {
			while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$coll[$row[ID]]=$row;
			}
		}
		
//ricerca buoni
$buoni=array();
$query="SELECT
		t1.*
		FROM CARB_buoni as t1
		WHERE t1.d_stampa<='".input2db($val[data_f])."' ";

if ($val[data_i]!="") $query.="AND t1.d_stampa>='".input2db($val[data_i])."' ";
if ($tartel_query!="") $query.="AND t1.veicolo IN (".substr($tartel_query,0,-1).") ";
if ($val[reparto]!="") $query.="AND t1.reparto='".$val[reparto]."' ";
if ($val[operatore]!=0) $query.="AND t1.id_esec='".$val[operatore]."' ";
if ($val[richiedente]!=0) $query.="AND t1.id_rich='".$val[richiedente]."' ";
if ($val[verifica]!="T") $query.=" AND t1.verifica='".$val[verifica]."' ";

if ($val[tipo]=='tanica') $query.="AND t1.veicolo='0' ";
else $query.="AND t1.veicolo!='0' ";

$query.="AND mov_open='0' "; 

if($result=sqlsrv_query($db_handler,$query)) {
	while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
		$buoni[$row[ID]]=$row;
		//aggiungi flag per la stampa
		$buoni[$row[ID]][stampa]=1;
	}
}

/*echo '<div>';
	//echo $query;
echo '</div>';

echo '<div>';
	//echo json_encode($buoni);
echo '</div>';*/

//inizializzazione stati
$stati=array();

//inizializza array filtri
$tipi=array(array("testo"=>"normale","flag"=>1),array("testo"=>"pieno","flag"=>1));
$filters=array("Reparto"=>$reparti,"Stato"=>$stati,"Gestione"=>$gestioni,"Tipo"=>$tipi,"Causale"=>$causali);
?>

<div style="">
	
	<div style="position: relative;width:700px;">
		<button onclick="carb_annulla_st_query();">Annulla</button>
		<button style="position: relative;top: 0px;left:30px;" onclick="carb_stampa_st_query();">Stampa</button>
	</div>
	
	<div id="carb_st_lista_div" class="carb_st_lista_div">
 	 	
		<table class="carb_st_lista_tt" style="width:630px;border-collapse: collapse;">
			<colgroup>
				<col span="1" width="130px"/>
				<col span="1" width="270px"/>
				<col span="1" width="70px"/>
				<col span="1" width="120px"/>
				<col span="1" width="40px"/>
			</colgroup>
			
			<thead>
				<tr>
					<th style="text-align:left;">
						<div>Data stampa</div>
						<!--<div style="font-weight:normal;font-size:10pt;">stato</div>-->
					</th>
					<th style="text-align:left;">Veicolo</th>
					<th style="text-align:center;">Importo</th>
					<th style="text-align:center;">Causale</th>
					<th>V</th>
				</tr>
			</thead>
		
			<tbody>
				<?php
					$td=1;
					$num_buoni=0;
					foreach ($buoni as $k=>$r) {
						
						$num_buoni++;
					
						//lettura dati veicolo
						//$dv=$maestro->carb_get_dv($r[veicolo]);
						
						/*echo '<tr ';
						if ($td==1) { 
							echo 'class="carb_lista_tt_tr1"';
							$td=2;
						}
						else {
							echo 'class="carb_lista_tt_tr2"';
							$td=1;
						}
						echo '>';*/
						
						//aggiorna array stati
						if (!array_key_exists($r[stato],$filters[stato])) {
							$filters[Stato][$r[stato]]=array("testo"=>$r[stato],"flag"=>1);
						}
						
						//aggiorna filters
						$filters[Reparto][$r[reparto]][flag]=1;
						$filters[Gestione][$r[gestione]][flag]=1;
						$filters[Causale][$r[causale]][flag]=1;
						
						//scrivi riga
						echo '<tr class="carb_lista_tt_tr1" c_Reparto="'.$r[reparto].'" c_Stato="'.$r[stato].'" c_Gestione="'.$r[gestione].'" c_Tipo="'.$r[pieno].'" c_Causale="'.$r[causale].'" c_ID="'.$r[ID].'">';
						
							//echo '<td style="text-align:center;font-size:10pt;cursor:pointer;" onclick="carb_tartel_sel(\''.$key.'\');">sel</td>';
							//echo '<td style="font-size:10pt;cursor:pointer;" onclick="bon_sel_anagra(\'var obj='.addslashes(json_encode($a)).'\');">'.$a[cod].'</td>';
							
							echo '<td>';
								echo '<div style="text-align:left;color:green;font-size:11px;"><b>'.$reparti[$r[reparto]][testo].'</b></div>';
								echo '<div style="text-align:left;font-size:13px;">';
									echo '<span>'.db_todata($r[d_stampa]).'</span>';
									//
									echo '<span class="tooltip no-print" style="margin-left:15px;top:3px;">';
										echo '<img style="width:12px;height:12px;" src="img/info.png"/>';
										echo '<span class="tooltiptext">';
											echo 'Creazione: '.($r[d_creazione]==""?"<br/>":db_todata($r[d_creazione])."<br/>");
											//echo 'Stampa: '.($r[d_stampa]==""?"<br/>":db_todata($r[d_stampa])."<br/>");
											echo 'Risarcimento: '.($r[d_ris]==""?"<br/>":db_todata($r[d_ris])."<br/>");
											echo 'Annullamento: '.($r[d_annullo]==""?"<br/>":db_todata($r[d_annullo])."<br/>");
											echo 'Verifica: '.($r[d_verifica]==""?"<br/>":db_todata($r[d_verifica])."<br/>");
										echo '</span>';
									echo '</span>';
								echo '</div>';
								echo '<div style="text-align:left">';
									echo '<span style="font-size:11px;font-weight:bold;">'.$r[ID].'</span>';
									echo '<span style="position:relative;color:violet;font-size:12px;left:5px;">'.$r[stato].'</span>';
								echo '</div>';
							echo '</td>';
							
							echo '<td>';
								//dati veicolo
								if ($r[veicolo]!=0) $dv=$maestro->carb_get_dv($r[veicolo]);
								else $dv=array();
								
								echo '<div style="font-size:12px;text-align:left;position:relative;">';
									echo 'Gestione: <span style="color:violet;">'.substr($gestioni[$r[gestione]][testo],0,20).'</span>';
									echo '<span>&nbsp;(';
										if ($r[tipo_carb]=='B') echo "Benzina";
										if ($r[tipo_carb]=='D') echo "Diesel";
									echo '</span>)';
									//se ci sono delle note
									if ($r[nota]!="" || $r[nota_ris]!="" || $r[nota_annullo]!="") {
										echo '<div class="tooltip no-print" style="position:absolute;top:3px;right:5px;">';
											echo '<img style="width:15px;height:15px;" src="img/note.png"/>';
											echo '<div class="tooltiptext" style="width:300px;">';
												if ($r[nota]!="") {
													echo '<div>';
														echo '<label>Nota generale:</label>';
														echo '<div>'.$r[nota].'</div>';
													echo '</div>';
												}
												if ($r[nota_ris]!="") {
													echo '<div>';
														echo '<label>Nota mancato risarcimento:</label>';
														echo '<div>'.$r[nota_ris].'</div>';
													echo '</div>';
												}
												if ($r[nota_annullo]!="") {
													echo '<div>';
														echo '<label>Nota annullo:</label>';
														echo '<div>'.$r[nota_annullo].'</div>';
													echo '</div>';
												}
											echo '</div>';
										echo '</div>';
									}
								echo '</div>';
								echo '<div id="carb_descr" class="carb_descr" style="font-size:13px">'.($r[veicolo]==0?"TANICA":$dv[$r[veicolo]][targa].' - '.$dv[$r[veicolo]][telaio]).'</div>';
								echo '<div style="font-size:11px">'.substr($dv[$r[veicolo]][des],0,35).'</div>';
							echo '</td>';
							
							echo '<td ';
								if ($r[stato]=='annullato') echo 'style="background-color:#dddddd;"';
							echo '>';
								echo '<div style="text-align:center;font-size:12pt;">';
								if ($r[pieno]==1) echo '<div style="color:red;font-weight:bold;font-size:9pt;">PIENO</div>';
								echo '<div>'.number_format($r[importo],2,",","");
									//se è abilitata la verifica
									if ($val[v_flag]==1 && $r[stato]!='annullato') {
										echo '<img style="width:10px;height:10px;" src="img/annulla.png" onclick="carb_annulla(\''.$k.'\',\'storico\');"/>';
									}
								echo '</div>';
							echo '</td>';
							
							echo '<td style="text-align:center;">';
								echo '<div style="font-size:11pt;">'.$causali[$r[causale]][testo].'</div>';
								echo '<div class="tooltip no-print" style="">';
									echo '<span style="font-size:8pt;font-weight:bold;color:green;">autorizzazioni</span>';
									echo '<span class="tooltiptext" style="left:-400px;width:350px;font-size:11pt;">';
										echo 'Richiesta: '.$coll[$r[id_rich]][cognome]." ".$coll[$r[id_rich]][nome]."<br/>";
										echo 'Stampa: '.$coll[$r[id_esec]][cognome]." ".$coll[$r[id_esec]][nome]."<br/>";
										echo 'Risarcimento: '.($r[id_ris]=="0"?"<br/>":$coll[$r[id_ris]][cognome]." ".$coll[$r[id_ris]][nome]."<br/>");
										echo 'Annullamento: '.($r[id_annullo]=="0"?"<br/>":$coll[$r[id_annullo]][cognome]." ".$coll[$r[id_annullo]][nome]."<br/>");
									echo '</span>';
								echo '</div>';
							echo '</td>';
							
							echo '<td style="text-align:center;">';
								//se la verifica è abilitata
								if ($val[v_flag]==1) {
									echo '<input id="carb_v_'.$r[ID].'" class="st_chk_print" type="checkbox" value="'.$r[ID].'" onclick="carb_st_verifica(this.value,this.checked);"';
										if ($r[verifica]==1) echo 'checked="checked"';
									echo '/>';
									echo '<span id="carb_v_span_'.$r[ID].'" class="st_chkspan_print" style="display:none;">';
										if ($r[verifica]==1) echo 'V';
									echo '</span>';
								}
								//se la verifica NON è abilitata
								else {
									echo '<span class="st_chkspan_print">';
										if ($r[verifica]==1) echo 'V';
									echo '</span>';
								}
							echo '</td>';
						
						echo '</tr>';
					}
					
					echo '<tr>';
						echo '<td colspan="5" style="height:30px;">';
							echo '<label>Numero buoni:</label><span id="carb_stlista_num" style="margin-left:5px;"></span>';
							echo '<label style="margin-left:20px;">Importo totale:</label><span id="carb_stlista_tot" style="margin-left:5px;"></span>';
							echo '<span style="margin-left:20px;">( <label>Pagati:</label></span><span id="carb_stlista_tot_pag" style="margin-left:5px;"></span><span> / <label>Risarciti:</label></span><span id="carb_stlista_tot_ris" style="margin-left:5px;"></span><span> )</span>';
						echo '</td>';
					echo '</tr>';
					
				?>
			</tbody>
		</table>
	</div>
</div>

<div>
	<?php 
		/*foreach ($filters as $k=>$f) {
			echo '<div>';
				echo '<label>'.$k.'</label>'.json_encode($f);
			echo '</div>';
		}*/
	?>
</div>

<?php 

//echo '<input type="hidden" id="carb_st_num_buoni" value="'.$num_buoni.'"/>';

echo '<script type="text/javascript">';
		echo '_carb_st_lista='.json_encode($buoni).';';
		echo '_carb_st_filters='.json_encode($filters).';';
echo '</script>';

sqlsrv_close($db_handler);
?>