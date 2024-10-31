<?php
error_reporting(E_ERROR | E_PARSE);
//error_reporting(0);

include('../ammo_func.php');
include('../carb_ini.php');
include('maestro.php');

$connectionInfo = array( "Database"=>$db, "UID"=>$user, "PWD"=>$pw ,"CharacterSet" => "UTF-8");
$db_handler=sqlsrv_connect("srvdb",$connectionInfo);

date_default_timezone_set ("Europe/Rome");

$obj=json_decode($_POST[obj]);

$maestro=new Maestro();
$dv=$maestro->carb_get_dv($obj->veicolo);


//lettura causali
/*$causali=array();
$query="SELECT t1.* FROM CARB_causali as t1
		inner join CARB_gescas as t2 on t1.codice=t2.causale
		left join MAESTRO_reparti as t3 on t3.tag='".$obj->reparto."'
		where t2.gestione='".$obj->gestione."' AND t2.tipo_rep=t3.tipo
		";
if($result=sqlsrv_query($db_handler,$query)) {
	while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
		$causali[]=$row;
	}
}
*/
//lettura di tutte le causali per lo storico
$causali_all=array();
$query="SELECT * FROM CARB_causali";
if($result=sqlsrv_query($db_handler,$query)) {
	while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
		$causali_all[$row[codice]]=$row;
	}
}
/*
//lettura collaboratori
$richiedenti=array();
$query="SELECT t1.* 
		FROM MAESTRO_collaboratori as t1
		inner join MAESTRO_coll_rep as t2 on t1.id=t2.collaboratore and t2.reparto='".$obj->reparto."' and t2.stato='1'
		order by t1.cognome
		";
		if($result=sqlsrv_query($db_handler,$query)) {
			while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$richiedenti[$row[ID]]=$row;
			}
		}*/

$psw=array();
$query="SELECT t1.* , t2.nome,t2.cognome
		FROM CARB_psw as t1
		left join MAESTRO_collaboratori as t2 on t1.n_collab=t2.ID
		";
		if($result=sqlsrv_query($db_handler,$query)) {
			while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$psw[$row[pass]]=$row;
			}
		}
		
//query storico buoni
//calcolo data di riferimento -30gg dalla data di creazione
/*$ts_rif=strtotime("-30 days", mktime(0,0,0,((int)substr($obj->d_creazione,4,2))-1,(int)substr($obj->d_creazione,6,2),(int)substr($obj->d_creazione,0,4)));

$storico=array();
$query="SELECT t1.*
		FROM CARB_buoni as t1
		WHERE t1.veicolo='".$obj->veicolo."' AND stato IN ('stampato','completato') AND d_creazione>'".date('Ymd',$ts_rif)."'
		ORDER BY d_creazione DESC";
		if($result=sqlsrv_query($db_handler,$query)) {
			while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$storico[$row[ID]]=$row;
			}
		}

$collab=array();
$query="SELECT * FROM MAESTRO_collaboratori";

		if($result=sqlsrv_query($db_handler,$query)) {
			while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$collab[$row[ID]]=$row;
			}
		}

$reparti=array();
$query="SELECT * FROM MAESTRO_reparti where tipo IN('S','V') order by descrizione";
	if($result=sqlsrv_query($db_handler,$query)) {
		while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$reparti[$row[tag]]=$row;
		}
	}*/

//lettura gestioni
$gestioni=$maestro->carb_gestioni();

?>
<div>
	<button onclick="carb_close_lista();">Annulla</button>
</div>
<br/>
<h2>Risarcimento buono:</h2>

<div>
	<table class="carb_lista_tt" width="500px">
			
		<tbody>
			<?php

				echo '<tr class="carb_lista_tt_tr1" >';
					//echo '<td style="text-align:center;font-size:10pt;cursor:pointer;" onclick="carb_tartel_sel(\''.$key.'\');">sel</td>';
					//echo '<td style="font-size:10pt;cursor:pointer;" onclick="bon_sel_anagra(\'var obj='.addslashes(json_encode($a)).'\');">'.$a[cod].'</td>';
					
					echo '<td>';
						echo '<div style="text-align:left;color:green;font-size:13px"><b>'.$obj->des_rep.'</b></div>';
						echo '<div style="text-align:left">'.db_todata($obj->d_creazione).'</div>';
						echo '<div style="text-align:left;color:violet;">'.$causali_all[$obj->causale][causale].'</div>';
					echo '</td>';
					
					echo '<td>';
						echo '<div style="font-size:12px;">Gestione: <span style="color:violet;">'.substr(($obj->gestione=="_CLIENTE_"?"CLIENTE":$gestioni[$obj->gestione]),0,20).'</span></div>';
						echo '<div id="carb_descr" class="carb_descr" style="font-size:13px">'.($obj->veicolo==0?"TANICA":$dv[$obj->veicolo][targa].' - '.$dv[$obj->veicolo][telaio]).'</div>';
						echo '<div style="font-size:11px">'.substr($dv[$obj->veicolo][des],0,35).'</div>';
					echo '</td>';
					
					echo '<td>';
						echo '<div style="text-align:center;font-size:10pt;">'.($obj->importo==0?"PIENO":number_format($obj->importo,2,",","")).'</div>';
						echo '<div><b>';
							if ($obj->tipo_carb=='B') echo 'Benzina';
							if ($obj->tipo_carb=='D') echo 'Diesel';
						echo '</b></div>';
					echo '</td>';
					
				echo '</tr>';	
			?>
		</tbody>
	</table>
	
	<br><br>
	
	<!--<label>Richiedente</label>
	<select id="carb_richiedente" class="carb_richiedente">
		<option value="0">seleziona un richiedente</option>
		<?php
		//menu a tendina con campi presi da concerto
			foreach($richiedenti as $r) {
				echo '<option value="'.$r[ID].'">'.$r[cognome].' '.$r[nome].'</option>';
			}
		?>
	</select>
		
	<div style="margin-top:5px;">
		<label style="margin-right:22px">Causale</label>
		<select id="carb_causale">
			<option value="">Seleziona ...</option>
			<?php
				foreach($causali as $c) {
					echo '<option value="'.$c[codice].'">'.$c[causale].'</option>';
				}
			?>
		</select>
	</div>-->
	
	<div>
		<input style="margin-left:5px;" type="radio" name="carb_ris_radio" id="carb_ris_radio" value="1"/>
		<label>Risarcito</label>
	</div>
	
	<div>
		<input style="margin-left:5px;" type="radio" name="carb_ris_radio" id="carb_ris_radio" value="0"/>
		<label>NON Risarcito</label>
	</div>
	
	<div style="margin-top:5px;">
		<label style="margin-right:40px">Nota mancato risarcimento</label>
		<br/>
		<input type="text" id="carb_ris_nota" maxlength="40" style="width:212px;" value=""/>
	</div>
	
	<div style="margin-top:10px;">
		<label>Password</label>
		<input style="margin-left:20px;" type="password" id="carb_ris_pw" onkeydown="if(event.keyCode==13) carb_close_ris();"/>
	</div>
	
	<div id="carb_ris_error" class="carb_error"></div>
	
	
	<div style="margin-left:300px;margin-top:10px;float:left;">
		<button onclick="carb_close_ris();"><img style="width:30px;height:30px;" src="img/cash.png"/></button>
	</div>
	
	
</div>

<?php
//echo '<div style="position:relative;top:100px;">'.json_encode($reparti).'</div>';
echo '<script type="text/javascript">';
	echo '_carb_psw='.json_encode($psw).';';
echo '</script>';

sqlsrv_close($db_handler);
?>