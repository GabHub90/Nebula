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

//lettura gestioni
$gestioni=$maestro->carb_gestioni();

//lettura gestioni possibili in base alle causali possibili per il reparto
$temp=array();
$query="SELECT t1.* FROM CARB_gescas AS t1
		WHERE t1.tipo_rep='".$_POST[tipo]."'
		ORDER BY t1.gestione ASC 
		";
		
if($result=sqlsrv_query($db_handler,$query)) {
	while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
		$temp[]=$row;
	}
}

//lettura password autz
$psw=array();
$query="SELECT t1.* , t2.nome,t2.cognome
		FROM CARB_psw as t1
		left join MAESTRO_collaboratori as t2 on t1.n_collab=t2.ID
		WHERE t1.autz='1'
		";
		if($result=sqlsrv_query($db_handler,$query)) {
			while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$psw[$row[pass]]=$row;
			}
		}


//filtra le gestioni da $temp e $gestioni (gestione ripetuta una sola volta con la sua descrizione)
$ges=array();

foreach ($temp as $t) {
	if ($t[gestione]=='TANICA' || $t[gestione]=='_CLIENTE_') continue;
	
	if (!array_key_exists($t[gestione],$ges)) {
		$ges[$t[gestione]]=$gestioni[$t[gestione]]." (".$t[gestione].")";
	}
}

?>

<div>
	<button onclick="carb_close_forges();">Annulla</button>
</div>
<br/>
<h2>Forzatura gestione:</h2>

<div>
	
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
		<label>Nuova gestione</label>
		<select id="carb_forges_ges">
			<?php
				foreach ($ges as $kg=>$g) {
					echo '<option value="'.$kg.'">'.$g.'</option>';
				}
			?>
		</select>
	</div>
	
	<br/>
	
	<div style="margin-top:5px;">
		<label style="margin-right:65px">Nota</label>
		<input type="text" id="carb_forges_nota" size="30" maxlength="40" value=""/>
	</div>
	
	<div style="margin-top:10px;background-color: yellow;width: 250px;">
		<label>Password TDD</label>
		<input style="margin-left:20px;" type="password" id="carb_forges_pw" onkeydown="if(event.keyCode==13) carb_close_forges();"/>
	</div>
	
	<div id="carb_forges_error" class="carb_error"></div>
	
	
	<div style="margin-left:300px;margin-top:10px;float:left;">
		<button onclick="carb_close_forges();">OK</button>
	</div>
	
	
</div>

<?php
//echo '<div style="position:relative;top:100px;">'.json_encode($reparti).'</div>';
echo '<script type="text/javascript">';
	echo '_carb_psw='.json_encode($psw).';';
	echo '_carb_gestioni='.json_encode($gestioni).';';
echo '</script>';

sqlsrv_close($db_handler);
?>