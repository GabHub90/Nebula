<?php
error_reporting(E_ERROR | E_PARSE);
//error_reporting(0);

include ('../carb_ini.php');
include ('../ammo_func.php');
include('maestro.php');

$connectionInfo = array( "Database"=>$db, "UID"=>$user, "PWD"=>$pw ,"CharacterSet" => "UTF-8");
$db_handler=sqlsrv_connect("srvdb",$connectionInfo);

date_default_timezone_set ("Europe/Rome");

$b1=(array)json_decode($_POST[b1]);
$b2=(array)json_decode($_POST[b2]);
$storico=(array)json_decode($_POST[storico]);

$maestro=new Maestro();
$dv=$maestro->carb_get_dv($b1[veicolo]);

//leggi collaboratori
$collab=array();
$query="SELECT * FROM MAESTRO_collaboratori";

if($result=sqlsrv_query($db_handler,$query)) {
	while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
		$collab[$row[ID]]=$row;
	}
}

//leggi reparti
$reparti=array();
$query="SELECT * FROM MAESTRO_reparti";

if($result=sqlsrv_query($db_handler,$query)) {
	while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
		$reparti[$row[tag]]=$row;
	}
}

//leggi causali
$causali=array();
$query="SELECT * FROM CARB_causali";

if($result=sqlsrv_query($db_handler,$query)) {
	while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
		$causali[$row[codice]]=$row;
	}
}


//echo json_encode($b1);
?>
<div style="margin-top:20px;font-size:30px;">
    <div>
        <p><label>Augusto gabellini S.R.L. - Strada della Romagna 119 - Pesaro(PU)</label></p>
    </div>
    <div>
        <label>Buono Carburante</label>
		<span style="font-weight:bold;">&nbsp;(<?php if ($b1[tipo_carb]=='B') echo "Benzina";if ($b1[tipo_carb]=='D') echo "Diesel";?>)&nbsp;</span>
        <span>N.<?php echo $b1[ID]; ?></span>
        <label>del</label>
        <?php echo db_todata($b1[d_creazione]);?>
        <label>di:</label>
        <b>€<?php echo ($b1[importo]==0?"":number_format($b1[importo],2,',','.'));?></b>
    </div>
    <div>
        <label>emesso per l'auto:</label>
        <?php echo ($b1[veicolo]==0?"TANICA":$dv[$b1[veicolo]][targa].' - '.$dv[$b1[veicolo]][telaio]);?>
    </div>
    
    <div style="margin-top:10px;">
    	<table id="carb_buono_stampa_tab" style="border-collapse: collapse;border: 0px solid #333;font-size:25px;width: 1000px;">
    		<colgroup>
    			<col span="2" width="500px"/>
    		</colgroup>
    		<tr>
    			<td>
    				<label>Operatore:</label>
    				<?php echo $b2[des_utente];?>
    			</td>
    			<td>
    				<label>Richiedente:</label>
    				<?php echo $collab[$b2[rich]][cognome]." ".$collab[$b2[rich]][nome];?>
    			</td>
    		</tr>
    		<tr>
    			<td>
    				<label>Reparto:</label>
    				<?php echo $reparti[$b1[reparto]][descrizione];?>
    			</td>
    			<td style="text-align: center;">
    				<label>Firma</label>
    			</td>
    		</tr>
    			<td>
    				<label>Causale:</label>
    				<?php echo $causali[$b1[causale]][causale];?>
    			</td>
    			<td style="text-align: center;">
    				<label>_____________</label>
    			</td>
    		<tr>
    			<td>
					<label>Nota:</label>
					<?php echo $b1[nota];?>
				</td>
    		</tr>
    	</table>
    </div>

    <!--<div style="margin-top:10px;float:left;">
        <label>Operatore:</label>
        <?php
        //echo $b2[des_utente] ?>
    </div>
    <div style="margin-top:10px;float:left;">
        <label style="margin-left:130px;">Richiedente:</label>
        <?php //echo $collab[$b2[rich]][cognome]." ".$collab[$b2[rich]][nome];?>
    </div>
    <div style="clear:both;"></div>
         
    <div style="margin-top:10px;float:left;">
        <label>Reparto:</label>
        <?php //echo $reparti[$b1[reparto]][descrizione]?>
    </div>
    <div style="margin-top:10px;float:left;">
        <label style="margin-left:400px;">Firma</label>
    </div>
	
    <div style="clear:both;"></div>
        
    <div style="margin-top:10px;float:left;">
        <label>Causale:</label>
        <?php //echo $causali[$b1[causale]][causale]?>
    </div>
    <div style="margin-top:10px;float:left;">
        <label style="margin-left:450px;">_____________</label>
    </div>
    <div style="clear:both;"></div>   
	
	<div style="margin-top:10px;">
	    <label>Nota:</label>
	    <span><?php //echo $b1[nota];?></span>
	</div>
	-->
</div>

<br><br>
<p>================================================================================================================================</p>

<div style="margin-top:20px;font-size:30px;">
    <div>
        <p>Augusto gabellini S.R.L. - Strada della Romagna 119 - Pesaro(PU)</p>
    </div>
    <div>
        <label>Buono Carburante N.</label>
        <span><?php echo $b1[ID]; ?></span>
        <label>del</label>
        <?php echo db_todata($b1[d_creazione]);?>
        <label>di:</label>
        <b>€<?php echo ($b1[importo]==0?"":number_format($b1[importo],2,',','.'))?></b>
    </div>
    <div>
        <label>emesso per l'auto:</label>
        <?php echo ($b1[veicolo]==0?"TANICA":$dv[$b1[veicolo]][targa].' - '.$dv[$b1[veicolo]][telaio])?>
    </div>
    
    <div style="margin-top:10px;">
    	<table id="carb_buono_stampa_tab" style="border-collapse: collapse;border: 0px solid #333;font-size:25px;width: 1000px;">
    		<colgroup>
    			<col span="2" width="500px"/>
    		</colgroup>
    		<tr>
    			<td>
    				<label>Operatore:</label>
    				<?php echo $b2[des_utente];?>
    			</td>
    			<td>
    				<label>Richiedente:</label>
    				<?php echo $collab[$b2[rich]][cognome]." ".$collab[$b2[rich]][nome];?>
    			</td>
    		</tr>
    		<tr>
    			<td>
    				<label>Reparto:</label>
    				<?php echo $reparti[$b1[reparto]][descrizione];?>
    			</td>
    			<td style="text-align: center;">
    				<label>Firma</label>
    			</td>
    		</tr>
    			<td>
    				<label>Causale:</label>
    				<?php echo $causali[$b1[causale]][causale];?>
    			</td>
    			<td style="text-align: center;">
    				<label>_____________</label>
    			</td>
    		<tr>
				<td>
					<label>Nota:</label>
					<?php echo $b1[nota];?>
				</td>
    		</tr>
    	</table>
    </div>
    
    <!--
    <div style="margin-top:10px;float:left;">
        <label>Operatore:</label>
        <?php
        echo $b2[des_utente] ?>
    </div>
    <div style="margin-top:10px;float:left;">
        <label style="margin-left:130px;">Richiedente:</label>
        <?php echo $collab[$b2[rich]][cognome]." ".$collab[$b2[rich]][nome];?>
    </div>
    <div style="clear:both;"></div>
         
    <div style="margin-top:10px;float:left;">
        <label>Reparto:</label>
        <?php echo $reparti[$b1[reparto]][descrizione]?>
    </div>
    <div style="margin-top:10px;float:left;">
        <label style="margin-left:400px;">Firma</label>
    </div>
	
    <div style="clear:both;"></div>
        
    <div style="margin-top:10px;float:left;">
        <label>Causale:</label>
        <?php echo $causali[$b1[causale]][causale]?>
    </div>
    <div style="margin-top:10px;float:left;">
        <label style="margin-left:450px;">_____________</label>
    </div>
    <div style="clear:both;"></div>
       
	<div style="margin-top:10px;">
	    <label>Nota:</label>
	    <span><?php echo $b1[nota];?></span>
	</div>
	-->
</div>
<br>
<p>=====================================================================================================================================</p>

<!--scrivere storico-->

<div id="carb_storico" style="position: relative;top: 0px;width: 800px;text-align:left;">

	<div style="position: relative;top: 0px;left:0px;text-align:left;"><h2>Buoni stampati negli ultimi 30gg</h2></div>
	
	<table class="carb_lista_tt_storico" width="650px">
		<colgroup>
			<col span="1" width="250px"/>
			<col span="1" width="300px"/>
			<col span="1" width="100px"/>
		</colgroup>
		<tbody>
			<?php
				foreach ($storico as $s) {
				
					$s=(array)$s;
					
					echo '<tr>';

						//echo '<td style="text-align:center;font-size:10pt;cursor:pointer;" onclick="carb_tartel_sel(\''.$key.'\');">sel</td>';
						//echo '<td style="font-size:10pt;cursor:pointer;" onclick="bon_sel_anagra(\'var obj='.addslashes(json_encode($a)).'\');">'.$a[cod].'</td>';
						
						echo '<td style="border-bottom:1px solid black;">';
							echo '<div style="text-align:left;color:green;"><b>'.$reparti[$s[reparto]][descrizione].'</b></div>';
							echo '<div style="text-align:left">'.db_todata($s[d_creazione]).'</div>';
						echo '</td>';
						
						echo '<td style="border-bottom:1px solid black;">';
							echo '<div style="">Esecutore: <span>'.$collab[$s[id_esec]][cognome].' '.$collab[$s[id_esec]][nome].'</span></div>';
							echo '<div id="carb_descr" class="carb_descr" style="">'.$collab[$s[id_rich]][cognome].' '.$collab[$s[id_rich]][nome].' / '.$causali[$s[causale]][causale].'</div>';
						echo '</td>';
						
						echo '<td style="border-bottom:1px solid black;">';
							echo '<div style="text-align:center;">'.$s[importo].'</div>';
						echo '</td>';
						
					echo '</tr>';
	
				}
				
			?>
		</tbody>
	</table>
</div>


<!--aggiornare buono db-->

<?php
if ($b1[importo]==0) {
	$q_stato='dacompletare';
	$q_open=1;
}
else {
	if ($b1[gestione]=='_CLIENTE_') {
		$q_stato='daris';
		$q_open=1;
	}
	else {
		$q_stato='stampato';
		$q_open=0;
	}
}


$query="UPDATE CARB_buoni 
		SET id_rich='".$b2[rich]."',id_esec='".$b2[utente]."',d_stampa='".date('Ymd')."',stato='".$q_stato."',nota='".str_replace("'","''",$b1[nota])."', mov_open='".$q_open."'
		WHERE ID='".$b1[ID]."'
		";
$result=sqlsrv_query($db_handler,$query);
?>


<!--

<div>
	<?php echo json_encode($b2); ?>
</div>

<div>
	<?php echo json_encode($reparti); ?>
</div>

<div>
	<?php echo json_encode($collab); ?>
</div>

<div>
	<?php echo json_encode($storico); ?>
</div>

-->

<?php
sqlsrv_close($db_handler);
?>