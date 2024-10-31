<?php
error_reporting(E_ERROR | E_PARSE);
//error_reporting(0);

include('../carb_ini.php');
include('maestro.php');

$connectionInfo = array( "Database"=>$db, "UID"=>$user, "PWD"=>$pw ,"CharacterSet" => "UTF-8");
$db_handler=sqlsrv_connect("srvdb",$connectionInfo);

date_default_timezone_set ("Europe/Rome");

$maestro=new Maestro();

//lettura POST
$old=array("ID"=>0,"veicolo"=>0,"importo"=>0,"reparto"=>"","nota"=>"");
if ($_POST[old]!="") $old=(array)json_decode($_POST[old]);

//lettura dei parametri
$reparti=array();
$query="SELECT * FROM MAESTRO_reparti where tipo IN('S','V','D','N','X') order by descrizione";
if($result=sqlsrv_query($db_handler,$query)) {
	while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
		$reparti[$row[tag]]=$row;
	}
}

//lettura importi standard
$importi_std=array();
$query="SELECT t1.*,t2.importo 
		FROM CARB_importi_std as t1
		LEFT JOIN CARB_importi_fasce as t2 ON t1.fascia=t2.fascia";
		
if($result=sqlsrv_query($db_handler,$query)) {
	while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
		$importi_std[$row[modello].$row[causale]]=$row;
	}
}

//lettura causali collegate alle gestioni e tipo reparto
$gescas=array();
$query="SELECT t1.*,t2.causale AS testo FROM CARB_gescas AS t1
		INNER JOIN CARB_causali AS t2 on t1.causale=t2.codice
		";
if($result=sqlsrv_query($db_handler,$query)) {
	while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
		$gescas[]=$row;
	}
}
?>

<!--ID nel caso di update -->
	
<input id="carb_buono_id" type="hidden" value="<?php echo $old[ID]; ?>" />

<div id="carb_sel_main" class="carb_sel_main" style="margin-top:20px">
	<label>Targa/Telaio</label>
	<?php
		echo '<input type="radio" id="carb_sel1" name="carb_selezione" onclick="carb_check_sel(\'tartel\');" ';
			if ($old[ID]==0 || $old[veicolo]!=0) echo 'checked="checked" ';
			if ($old[ID]!=0) echo 'disabled="disabled" ';
		echo '/>';
	?>
	<label>Tanica</label>
	<?php
		echo '<input type="radio" id="carb_sel2" name="carb_selezione" onclick="carb_check_sel(\'tanica\');" ';
			if ($old[ID]!=0 && $old[veicolo]==0) echo 'checked="checked" ';
			if ($old[ID]!=0) echo 'disabled="disabled" ';
		echo '/>';
	?>
	<br/>
	<br/>
	
	<!--VETTURA-->
	<?php
	//leggi dati vettura se ID!=0
		$temp_vettura="";
		if ($old[ID]!=0) {
			$temp_dv=$maestro->carb_get_dv($old[veicolo]);
			$temp_vettura=$temp_dv[$old[veicolo]][telaio];
		}
	?>
	
	<label>Reparto</label>
	<select id="carb_reparto" onchange="write_opt_cau();">
		<option value="">Seleziona un reparto</option>
		<?php
		//menu a tendina con campi presi da concerto
			foreach($reparti as $k=>$r) {
				echo '<option value="'.$r[tag].'" ';
					if ($old[reparto]==$r[tag]) echo 'selected="selected"';
				echo '>'.$r[descrizione].'</option>';
			}
		?>
	</select>
	
	<?php
		echo '<div id="carb_tartel_main" style="margin-top:10px;height: 90px;';
			if ($old[gestione]=='TANICA') echo 'visibility:hidden;'; 
		echo '">';
	?>		
 
		<div id="carb_tartel_sel" style="margin-top: 20px;">
			<label>Targa/Telaio</label>
			<input type="text" id="carb_tt" name="carb_tt" style="margin-right:10px" onkeydown="if(event.keyCode==13) carb_lista();" value="<?php echo $temp_vettura; ?>"/>
			<button type="button" onclick="carb_lista('<?php echo $old[ID]; ?>');">Cerca</button>
		</div>
		<div id="carb_tartel_ok" style="display:none;margin-top: 15px;position: relative;">
			<div>
				<b><span id="carb_tt_telaio" name="carb_tt_telaio" style="font-size:14px;" ></span>&nbsp;-&nbsp;<span id="carb_tt_targa" style="font-size:14px;"></span></b>
			</div>
			<div>
				 <b><span id="carb_tt_des" name="carb_tt_des" style="font-size:14px;"></span></b>
			</div>
			<div>
				 <b><span id="carb_tt_ges" name="carb_tt_ges" style="font-size:12px;font-weight:normal;"></span></b>
				 <!--<button style="margin-left: 20px;background-color: lightgreen;" onclick="carb_force_ges();">Forza Gestione</button>-->
			</div>
			<div style="margin-top: 10px;">
				<button onclick="carb_tartel_switch('sel');">Cambia vettura</button>
			</div>
			
			<div style="margin-top: 10px;">
				<div style="margin-top:5px;">
					<label style="margin-right:22px">Causale</label>
					<!--viene compilato da JS-->
					<select id="carb_causale" onchange="write_importo_std();">					
					</select>
				</div>
			</div>
			
			<div style="margin-top: 10px;">
				<div id="carb_importo_std" style="margin-top:5px;height: 20px;font-weight:bold;color: green;"></div>
			</div>
		</div>
	</div>
	
	<div style="position: relative;margin-top: 50px;">
		<label>Importo</label>
		<input type="text" id="carb_importo" style="margin-right:5px;text-align:right;" value="<?php echo number_format($old[importo],0,',','.');?>"/>
		<?php
			echo '<input type="checkbox" id="carb_sel_pieno" name="carb_sel_pieno" onclick="carb_cancella_importo(this.checked);" ';
				if ($old[ID]!=0 && $old[importo]==0) echo 'checked="checked" ';
			echo '/>Pieno';
		?>
	</div>
	<br>
	
	<div style="position: relative;margin-top: 10px;margin-left: 20px;">';
		<div style="position: relative;float: left;">
			<input id="carb_urante" name="carb_urante" type="radio" value="D" <?php if ($old[tipo_carb]=='D') echo 'checked="checked"'; ?> />
			<span style="margin-left: 5px;">Diesel</span>
		</div>
		<div style="position: relative;float: left;margin-left: 30px;">
			<input id="carb_urante" name="carb_urante" type="radio" value="B" <?php if ($old[tipo_carb]=='B') echo 'checked="checked"'; ?> />
			<span style="margin-left: 5px;">Benzina</span>
		</div>
	</div>
	
	</br>
	
	<div style="margin-top:20px;position: relative;">
		<label>Note</label><br>
		<input type="text" id="carb_nota" size="30" maxlength="40" value="<?php echo $old[nota];?>"/>
 	</div>
	
	<div id="carb_main_error" class="carb_error" style="margin-top:10px;"></div>
		
	<div>
			<button style="top:20px;position: relative;" type="button" onclick="carb_confirm();"><?php echo ($old[ID]==0?"Crea":"Modifica"); ?></button>
			
			<?php 
				if ($old[ID]!=0) {
			?>
				<button style="top:20px;left:200px;position: relative;" type="button" onclick="carb_elimina('<?php echo $old[ID]; ?>');">Elimina</button>
			<?php
				}
			?>
		
	</div>
	
</div>

<div id="carb_cover_sx" class="carb_cover_sx"></div>

<?php

//echo '<div>'.json_encode($importi_std).'</div>';
//echo '<div>'.json_encode($reparti).'</div>';

echo '<script type="text/javascript">';
	echo '_carb_reparti='.json_encode($reparti).';';
	echo '_carb_gescas='.json_encode($gescas).';';
	echo '_carb_impstd='.json_encode($importi_std).';';
echo '</script>';

sqlsrv_close($db_handler);
?>