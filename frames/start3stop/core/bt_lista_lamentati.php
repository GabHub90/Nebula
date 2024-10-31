<?php
//error_reporting(E_ERROR | E_PARSE);
error_reporting(0);

//GENERA LA LISTA DEI LAMENTATI PER L'APERTURA DI UNA MARCATURA SU UN NUOVO ORDINE , riceve i parametri [coll]=marcatempo collaboratore - [id]=num_rif_movimento

include('../ststop_ini.php');
include('../ststop_func.php');
include('maestro.php');
include('st_environment.php');

//connessione al database
$connectionInfo = array( "Database"=>$db, "UID"=>$user, "PWD"=>$pw ,"CharacterSet" => "UTF-8");
$db_handler=sqlsrv_connect("srvdb",$connectionInfo);
//$db_handler=mysqli_connect($server_db,$user,$pw);
//mysqli_select_db($db_handler,$db);

date_default_timezone_set ("Europe/Rome");

$maestro=new Maestro();

$today=date('Ymd');

//$_POST[odl]:		riferimento odl
//$_POST[prova]:	flag prova 1/0

$env=new stEnv($maestro,$db_handler,$today);
$odl_servizio=$env->get_servizio();
$addebiti=$env->get_addebiti();
$stato_lam=$env->get_stato_lam();

//VERIFICA PRESUPPOSTI
$error='';
if(array_key_exists($_POST[odl],$odl_servizio)) {
	$error.='<div class="error" style="text-align:center;">Non si pu&ograve; marcare su questo ordine</div>';
}


if ($error=='') {	

$lamentati=$maestro->st_get_lamentati($_POST[odl]);

	//TABELLA
	echo '<table class="nuovo_lam_table">';
		echo '<colgroup>';
			echo '<col span="1" width="10"/>';
			echo '<col span="1" width="80"/>';
			echo '<col span="1" width="300"/>';
			echo '<col span="1" width="100"/>';
			echo '<col span="1" width="120"/>';
			echo '<col span="1" width="90"/>';
			echo '<col span="1" width="90"/>';
			echo '<col span="1" width="150"/>';
			echo '<col span="1" width="150"/>';
			echo '<col span="1" width="10"/>';
		echo '</colgroup>';
		echo '<thead>';
			echo '<tr>';
				echo '<th></th>';
				echo '<th>Riferimento</th>';
				echo '<th>Descrizione</th>';
				echo '<th>Pos.Lav.</th>';
				echo '<th>Marc. Tot</th>';
				echo '<th>Eff.</th>';
				echo '<th>Off</th>';
				echo '<th>Addebito</th>';
				echo '<th></th>';
				echo '<th></th>';
			echo '</tr>';
		echo '</thead>';
		
		echo '<tbody>';
	
		$lamline=0;
		foreach ($lamentati as $keylam=>$lam) {
			//se è la prima riga scrivi il riferimento all'ordine di lavoro
			if($lamline==0) {
				echo '<tr>';
					echo '<td colspan="10" style="height:10px;"></td>';
				echo '</tr>';
				
				echo '<tr>';
					echo '<td></td>';
					echo '<td>'.$lam[mov].'</td>';
					echo '<td style="text-align:left;"><U>'.addslashes(substr(strtolower($lam[des_ragsoc]),0,35)).'</U></td>';
					echo '<td style="text-align:left;">'.$lam[nome].'</td>';
					echo '<td colspan="6"></td>';
				echo '</tr>';
				$lamline++;
			}
		
			$ta=array("addebito"=>"","colore"=>"");
			
			//calcola addebito del lamentato
			foreach ($addebiti as $tx) {
				if($tx[ca]==$lam[inc_ca]) {
					//la mancanza del codice di tipo garanzia viene tabellato con il simbolo *
					$tg=($lam[inc_cg]==""?'*':$lam[inc_cg]);
					$cm=$lam[inc_cm];
					
					if ($tx[cg]==$tg && $tx[cm]==$cm) {
						//$ta=$tx[descrizione];
						$ta[addebito]=$tx[descrizione];
						$ta[colore]=$tx[colore];
						break 1;
					}
				}
			}
		
			echo'<tr>';
				// LAMENTATO
				echo '<td></td>';
				echo '<td>'.$lam[inc].'</td>';
				echo '<td style="text-align:left;background-color:'.$ta[colore].';">'.substr(strtolower($lam[inc_testo]),0,25).'</td>';
				
				//tempi
				$lam_fatt=round($lam[inc_pos_lav],2);
				echo '<td>'.$lam[inc_pos_lav].'</td>';
				$marc_tot=round($lam[inc_marc_chiuse],2);
				echo '<td>'.$lam[inc_marc_chiuse].'</td>';
				
				//Efficienza
				if ($marc_tot>0) $eff=round(($lam_fatt/$marc_tot),2)*100;
				if ($eff>=105) $col='#0bbf09';
				elseif ($eff>=100) $col='#edc935';
				else $col='red';
				echo '<td style="color:'.$col.';">';
					if 	($lam_fatt>0 && $marc_tot>0) echo $eff.' %';
				echo '</td>';
				
				//Officina
				echo '<td style="">'.$lam[inc_cod_off].'</td>';
				
				//Addebito
				echo '<td style="font-size:10pt;">';
					echo $ta[addebito];
				echo '</td>';
			
				//START
				echo '<td>';
						echo '<img class="lam_button_img" src="img/START.png" onclick="st_apri_form_start(\''.$lam[mov].'\',\''.$lam[inc].'\');"/>';
				echo '</td>';
				
				echo '<td></td>';
			
			echo '</tr>';	
		}
	
		//PROVA
		if (($_POST[prova]=='1')) {

			echo '<tr>';
				echo '<td colspan="10" style="height:10px;"></td>';
			echo '</tr>';
			
			echo '<tr>';
				echo '<td></td>';
				echo '<td></td>';
				echo '<td style="text-align:left;">Prova vettura</td>';
				echo '<td colspan="5"></td>';
				
				//BOTTONE PROVA
				echo '<td>';
					echo '<img class="lam_button_img" src="img/PROVA_b.png" onclick="st_apri_form_prova(\''.$lam[mov].'\');"/>';
				echo '</td>';
				
				echo '<td></td>';
				
			echo '</tr>';
		}
	
	echo '</table>';
}

//se error
else {
	echo $error;
}

sqlsrv_close($db_handler);
?>