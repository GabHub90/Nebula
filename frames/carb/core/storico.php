<?php
error_reporting(E_ERROR | E_PARSE);
//error_reporting(0);

include('../ammo_func.php');
include('../carb_ini.php');
include('maestro.php');

$connectionInfo = array( "Database"=>$db, "UID"=>$user, "PWD"=>$pw ,"CharacterSet" => "UTF-8");
$db_handler=sqlsrv_connect("srvdb",$connectionInfo);

date_default_timezone_set ("Europe/Rome");

//$obj=json_decode($_POST[obj]);

//$maestro=new Maestro();

$reparti=array();
$query="SELECT * FROM MAESTRO_reparti where tipo IN('S','V','D') order by descrizione";
		if($result=sqlsrv_query($db_handler,$query)) {
			while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$reparti[$row[ID]]=$row;
			}
		}

$richiedenti=array();
$query="SELECT *
		FROM MAESTRO_collaboratori
		WHERE stato='1'
		order by cognome
		";
		if($result=sqlsrv_query($db_handler,$query)) {
			while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$richiedenti[$row[ID]]=$row;
			}
		}
		
$operatori=array();
$query="SELECT t1.* , t2.nome,t2.cognome
		FROM CARB_psw as t1
		left join MAESTRO_collaboratori as t2 on t1.n_collab=t2.ID
		";
		if($result=sqlsrv_query($db_handler,$query)) {
			while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$operatori[$row[ID]]=$row;
			}
		}

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
		
$sede=array();
$query="SELECT
		sede
		FROM MAESTRO_reparti
		WHERE sede <> 'XX'
		GROUP BY sede";
		if($result=sqlsrv_query($db_handler,$query)) {
			while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$sede[]=$row;
			}
		}
		
?>

<div style="margin-top:15px"><h2>Storico Buoni</h2></div>

<div id="carb_st_query" style="position: relative;">
	<label>Da:</label>
	<input id="carb_st_data_i" type="date"/>
	<label>a:</label>
	<input id="carb_st_data_f" type="date" value="<?php echo date('Y-m-d');?>"/>
	<br><br>
	<div style="margin-top:10px;">
		<label>Targa/Telaio</label>
		<input type="text" id="carb_st_tartel"/> 
	</div>
	<br/>
	
	<div style="margin-top:-60px;margin-left: 350px;"> 
		<button style="" onclick="carb_st_cerca();"><img src="img/lente.png"/></button>
		<div style="margin-left:60px;margin-top: -40px;padding: 2px;border: 1px solid;"> 
		<button style="margin-left:20px;" onclick="carb_query_fe();"><img style="width:25px;height:25px;" src="img/carburante.png"/></button>
		<select id="carb_fe_sede">
			<option value="">seleziona sede</option>
			<?php
			//menu a tendina con campi presi da concerto
				foreach($sede as $s) {
					echo '<option value="'.$s[sede].'">'.$s[sede].'</option>';
				}
			?>
		</select>
		</div>
	</div>	
	<div style="margin-top: 20px;">	
		<select id="carb_st_reparto">
			<option value="">Tutti i reparti</option>
			<?php
			//menu a tendina con campi presi da concerto
				foreach($reparti as $k=>$r) {
					echo '<option value="'.$r[tag].'">'.$r[descrizione].'</option>';
				}
			?>
		</select>
			
		<select id="carb_st_richiedente" class="carb_st_richiedente">
			<option value="0">seleziona un richiedente</option>
				<?php
				//menu a tendina con campi presi da concerto
					foreach($richiedenti as $r) {
						echo '<option value="'.$r[ID].'">'.$r[cognome].' '.$r[nome].'</option>';
					}
				?>
		</select>
			
		<select id="carb_st_operatore" class="carb_st_operatore">
			<option value="0">seleziona un operatore</option>
				<?php
				//menu a tendina con campi presi da concerto
					foreach($operatori as $o) {
						echo'<option value="'.$o[ID].'">'.$o[cognome].' '.$o[nome].'</option>';
					}
				?>
		</select>
	</div>
		
	<br/>
	
	<div>
		<input type="radio" name="carb_st_tipo" value="normale" checked="checked"/>
		<label>Normale</label> 
		  
		<!--<input type="radio" name="carb_st_tipo" value="pieno"/>
		<label>Buoni dei pieni</label> -->
		
		<input type="radio" name="carb_st_tipo" value="tanica"/>
		<label>Buoni delle taniche</label>
		
		<input style="margin-left: 30px;" type="checkbox" name="carb_st_validate"/>
		<label style="color: red;">Abilita Verifica</label>
		
		<input style="margin-left:20px;" type="password" id="carb_cerca_pw" onkeydown="if(event.keyCode==13) carb_st_cerca();"/>
		<label>Password</label>
	</div>
	
	<div style="position:relative;margin-top: 5px;left: 235px;width:300px;">
		<input type="radio" name="carb_st_v" value="1"/>
		<label>Verificati</label> 
		  
		<input type="radio" name="carb_st_v" value="0"/>
		<label>Non verificati</label>
		
		<input type="radio" name="carb_st_v" value="T" checked="checked"/>
		<label>Tutti</label>
	</div>
		
	<div id="carb_st_error" class="carb_error" style="margin-top:10px;"></div>
		
	
	<div id="carb_st_query_cover" class="carb_st_query_cover"></div>
</div>

<hr style="width:95%;"/>

<!--filtri che vengono aggiornati in base alla ricerca-->
<div id="carb_st_query_filters"></div>

<!--pdf stampa
<iframe id="carb_pdf" name="carb_pdf" class="carb_pdf" style="display:none;" scrolling="no" src="core/buono.php"></iframe>-->

<!--div annullamento-->
<div id="carb_st_annulla" class="carb_st_annulla"></div>

<?php
echo '<script type="text/javascript">';
	echo '_carb_psw='.json_encode($psw).';';
echo '</script>';

sqlsrv_close($db_handler);
?>
	

