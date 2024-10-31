<?php
error_reporting(E_ERROR | E_PARSE);
//error_reporting(0);

include('../ammo_func.php');
include('../carb_ini.php');
include('maestro.php');

$connectionInfo = array( "Database"=>$db, "UID"=>$user, "PWD"=>$pw ,"CharacterSet" => "UTF-8");
$db_handler=sqlsrv_connect("srvdb",$connectionInfo);

date_default_timezone_set ("Europe/Rome");

$maestro=new Maestro();

//lettura di tutte le causali per lo storico
$causali=array();
$query="SELECT * FROM CARB_causali";
if($result=sqlsrv_query($db_handler,$query)) {
	while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
		$causali[$row[codice]]=$row[causale];
	}
}

//lettura dei buoni
$buoni=array();
$query="SELECT t1.*,t2.descrizione as des_rep
		FROM CARB_buoni as t1
		left join MAESTRO_reparti as t2 on t1.reparto=t2.tag
		WHERE t1.stato='creato'
		order by ID
		";
		if($result=sqlsrv_query($db_handler,$query)) {
			while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$buoni[$row[ID]]=$row;
				
			}
		}

//buoni da completare
$bdc=array();
$query="SELECT t1.*,t2.descrizione as des_rep
		FROM CARB_buoni as t1
		left join MAESTRO_reparti as t2 on t1.reparto=t2.tag
		WHERE t1.stato='dacompletare'
		order by ID
		";
		if($result=sqlsrv_query($db_handler,$query)) {
			while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$bdc[$row[ID]]=$row;
				
			}
		}
		
//buoni da risarcire
$bdr=array();
$query="SELECT t1.*,t2.descrizione as des_rep
		FROM CARB_buoni as t1
		left join MAESTRO_reparti as t2 on t1.reparto=t2.tag
		WHERE t1.stato='daris'
		order by ID
		";
		if($result=sqlsrv_query($db_handler,$query)) {
			while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$bdr[$row[ID]]=$row;
				
			}
		}

//lettura gestioni
$gestioni=$maestro->carb_gestioni();

?>

<div style="margin-top:20px;float:left">
  <button onclick="carb_new();">Nuovo</button>
</div>
<div style="margin-top:20px;margin-left:15px;float:left">
  <button onclick="carb_storico();">Storico buoni</button>
</div>
<div style="clear:both;"></div>
<div id="carb_cover_pw" class="carb_cover_pw"></div>

<div style="margin-top:10px;">

 	<h2>Buoni da stampare</h2>
 	
	<table class="carb_lista_tt" width="610px">
		<colgroup>
			<col span="1" width="140px"/>
			<col span="1" width="300px"/>
			<col span="1" width="90px"/>
			<col span="1" width="80px"/>
		</colgroup>
		
		<thead>
			<tr>
				<th style="text-align:center">Data</th>
				<th style="text-align:center">Veicolo</th>
				<th style="text-align:center">Importo</th>
				<th></th>
			</tr>
		</thead>
	
		<tbody>
			<?php
				$td=1;
				foreach ($buoni as $k=>$r) {
				
					//lettura dati veicolo
					$dv=$maestro->carb_get_dv($r['veicolo']);
					
					echo '<tr ';
					if ($td==1) { 
						echo 'class="carb_lista_tt_tr1"';
						$td=2;
					}
					else {
						echo 'class="carb_lista_tt_tr2"';
						$td=1;
					}
					echo '>';
						//echo '<td style="text-align:center;font-size:10pt;cursor:pointer;" onclick="carb_tartel_sel(\''.$key.'\');">sel</td>';
						//echo '<td style="font-size:10pt;cursor:pointer;" onclick="bon_sel_anagra(\'var obj='.addslashes(json_encode($a)).'\');">'.$a[cod].'</td>';
						
						echo '<td>';
							echo '<div style="text-align:left;color:green;font-size:13px"><b>'.$r['des_rep'].'</b></div>';
							echo '<div style="text-align:left">'.db_todata($r['d_creazione']).'</div>';
							echo '<div style="text-align:left;color:violet;">'.$causali[$r['causale']].'</div>';
						echo '</td>';
						
						echo '<td>';
							echo '<div style="font-size:12px;">Gestione: <span style="color:violet;">'.substr(($r[gestione]=="_CLIENTE_"?"CLIENTE":$gestioni[$r['gestione']]),0,20).'</span></div>';
							echo '<div id="carb_descr" class="carb_descr" style="font-size:13px">'.($r['veicolo']==0?"TANICA":$dv[$r['veicolo']]['targa'].' - '.$dv[$r['veicolo']]['telaio']).'</div>';
							echo '<div style="font-size:11px">'.substr($dv[$r['veicolo']]['des'],0,35).'</div>';
							
							//nota
							echo '<div style="text-align:center;color:red;font-size:15px;">'.$r['nota'].'</div>';
						echo '</td>';
						
						echo '<td>';
							echo '<div style="text-align:center;font-size:10pt;">';
								echo '<b>'.$r['tipo_carb'].'</b>&nbsp;'.($r['importo']==0?"PIENO":number_format($r['importo'],2,",",""));
							echo '</div>';
						echo '</td>';
					
						echo '<td style="text-align:center;font-size:10pt;">';
							echo '<button id="carb_stampa" onclick="carb_edit(\''.$k.'\');"><img style="width:20px;height:20px;cursor:pointer;" src="img/modifica.png"/></button>';
							echo '<button id="carb_stampa" onclick="carb_stampa(\''.$k.'\');"><img style="width:20px;height:20px;cursor:pointer;" src="img/icona_stampante.png"/></button>';
						echo '</td>';

					echo '</tr>';
					
					/*echo '<tr>';
						echo '<td colspan="4">';
							echo '<div style="text-align:left;color:red;font-size:15px;">'.$r[nota].'</div>';
						echo '</td>';
					echo '</tr>';*/
					
				}
			?>
		</tbody>
	</table>
 </div>
</div>

<hr/>

<div>
	<h2>Buoni da completare</h2>
	
	<table class="carb_lista_tt" width="610px">
		<colgroup>
			<col span="1" width="140px"/>
			<col span="1" width="300px"/>
			<col span="1" width="90px"/>
			<col span="1" width="80px"/>
		</colgroup>
		
		<thead>
			<tr>
				<th style="text-align:center">Data</th>
				<th style="text-align:center">Veicolo</th>
				<th style="text-align:center">Importo</th>
				<th></th>
			</tr>
		</thead>
	
		<tbody>
			<?php
				$td=1;
				foreach ($bdc as $k=>$r) {
				
					//lettura dati veicolo
					$dv=$maestro->carb_get_dv($r['veicolo']);
					
					echo '<tr ';
					if ($td==1) { 
						echo 'class="carb_lista_tt_tr1"';
						$td=2;
					}
					else {
						echo 'class="carb_lista_tt_tr2"';
						$td=1;
					}
					echo '>';
						//echo '<td style="text-align:center;font-size:10pt;cursor:pointer;" onclick="carb_tartel_sel(\''.$key.'\');">sel</td>';
						//echo '<td style="font-size:10pt;cursor:pointer;" onclick="bon_sel_anagra(\'var obj='.addslashes(json_encode($a)).'\');">'.$a[cod].'</td>';
						
						echo '<td>';
							echo '<div style="text-align:left;color:green;font-size:13px"><b>'.$r['des_rep'].'</b></div>';
							echo '<div style="text-align:left">'.db_todata($r['d_stampa']).'</div>';
							echo '<div style="text-align:left;color:violet;">'.$causali[$r['causale']].'</div>';
						echo '</td>';
						
						echo '<td>';
							echo '<div style="font-size:12px;">Gestione: <span style="color:violet;">'.substr(($r['gestione']=="_CLIENTE_"?"CLIENTE":$gestioni[$r['gestione']]),0,20).'</span></div>';
							echo '<div id="carb_descr" class="carb_descr" style="font-size:13px">'.($r['veicolo']==0?"TANICA":$dv[$r['veicolo']]['targa'].' - '.$dv[$r['veicolo']]['telaio']).'</div>';
							echo '<div style="font-size:11px">'.substr($dv[$r['veicolo']]['des'],0,35).'</div>';
							
							//nota
							echo '<div style="text-align:center;color:red;font-size:12px;">'.$r['nota'].'</div>';
						echo '</td>';
						
						echo '<td>';
							echo '<div style="text-align:center;font-size:10pt;">';
								echo '<b>'.$r['tipo_carb'].'</b>&nbsp;'.($r['importo']==0?"PIENO":number_format($r['importo'],2,",",""));
							echo '</div>';
						echo '</td>';
						
						echo '<td style="text-align:center;font-size:10pt;">';
							echo '<button onclick="carb_fill(\''.$k.'\');"><img style="width:20px;height:20px;cursor:pointer;" src="img/fill.png"/></button>';
							echo '<button onclick="carb_annulla(\''.$k.'\',\'fill\');"><img style="width:20px;height:20px;cursor:pointer;" src="img/annulla.png"/></button>';
						echo '</td>';
						
						/*echo '<td style="text-align:center;font-size:10pt;cursor:pointer;">';
							echo '<button id="carb_stampa" onclick="carb_stampa(\''.$k.'\');"><img src="img/icona_stampante.png"/></button>';
						echo '</td>';*/
					echo '</tr>';
					
					/*echo '<tr>';
						echo '<td colspan="4">';
							echo '<div style="text-align:left;color:red;font-size:15px;">'.$r[nota].'</div>';
						echo '</td>';
					echo '</tr>';*/
					
				}
			?>
		</tbody>
	</table>
	
</div>

<hr/>

<div>
	<h2>Buoni da risarcire</h2>
	
	<table class="carb_lista_tt" width="610px">
		<colgroup>
			<col span="1" width="140px"/>
			<col span="1" width="300px"/>
			<col span="1" width="90px"/>
			<col span="1" width="80px"/>
		</colgroup>
		
		<thead>
			<tr>
				<th style="text-align:center">Data</th>
				<th style="text-align:center">Veicolo</th>
				<th style="text-align:center">Importo</th>
				<th></th>
			</tr>
		</thead>
	
		<tbody>
			<?php
				$td=1;
				foreach ($bdr as $k=>$r) {
				
					//lettura dati veicolo
					$dv=$maestro->carb_get_dv($r['veicolo']);
					
					echo '<tr ';
					if ($td==1) { 
						echo 'class="carb_lista_tt_tr1"';
						$td=2;
					}
					else {
						echo 'class="carb_lista_tt_tr2"';
						$td=1;
					}
					echo '>';
						//echo '<td style="text-align:center;font-size:10pt;cursor:pointer;" onclick="carb_tartel_sel(\''.$key.'\');">sel</td>';
						//echo '<td style="font-size:10pt;cursor:pointer;" onclick="bon_sel_anagra(\'var obj='.addslashes(json_encode($a)).'\');">'.$a[cod].'</td>';
						
						echo '<td>';
							echo '<div style="text-align:left;color:green;font-size:13px"><b>'.$r['des_rep'].'</b></div>';
							echo '<div style="text-align:left">'.db_todata($r['d_stampa']).'</div>';
							echo '<div style="text-align:left;color:violet;">'.$causali[$r['causale']].'</div>';
						echo '</td>';
						
						echo '<td>';
							echo '<div style="font-size:12px;">Gestione: <span style="color:violet;">'.substr(($r['gestione']=="_CLIENTE_"?"CLIENTE":$gestioni[$r['gestione']]),0,20).'</span></div>';
							echo '<div id="carb_descr" class="carb_descr" style="font-size:13px">'.($r['veicolo']==0?"TANICA":$dv[$r['veicolo']]['targa'].' - '.$dv[$r['veicolo']]['telaio']).'</div>';
							echo '<div style="font-size:11px">'.substr($dv[$r['veicolo']]['des'],0,35).'</div>';
							
							//nota
							echo '<div style="text-align:center;color:red;font-size:12px;">'.$r['nota'].'</div>';
						echo '</td>';
						
						echo '<td>';
							echo '<div style="text-align:center;font-size:10pt;">';
								echo '<b>'.$r['tipo_carb'].'</b>&nbsp;'.($r['importo']==0?"PIENO":number_format($r['importo'],2,",",""));
							echo '</div>';
						echo '</td>';
						
						echo '<td style="text-align:center;font-size:10pt;">';
							echo '<button onclick="carb_ris(\''.$k.'\');"><img style="width:20px;height:20px;cursor:pointer;" src="img/cash.png"/></button>';
							if ($r['pieno']==0) {
								echo '<button onclick="carb_annulla(\''.$k.'\',\'ris\');"><img style="width:20px;height:20px;cursor:pointer;" src="img/annulla.png"/></button>';
							}
						echo '</td>';
						
					echo '</tr>';
					
					/*echo '<tr>';
						echo '<td colspan="4">';
							echo '<div style="text-align:left;color:red;font-size:15px;">'.$r[nota].'</div>';
						echo '</td>';
					echo '</tr>';*/
					
				}
			?>
		</tbody>
	</table>
	
</div>



<!--
<div><?php echo $_POST['key']; ?></div>
-->
<div id="carb_cover_sx" class="carb_cover_sx"></div>

<?php

//echo json_encode($gestioni);

//echo json_encode($buoni);

echo '<script type="text/javascript">';
		echo '_carb_creati='.json_encode($buoni).';';
		echo '_carb_tofill='.json_encode($bdc).';';
		echo '_carb_toris='.json_encode($bdr).';';
echo '</script>';

sqlsrv_close($db_handler);
?>