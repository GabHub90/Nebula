<?php
error_reporting(E_ERROR | E_PARSE);
//error_reporting(0);

include('../carb_ini.php');
include('maestro.php');

$connectionInfo = array( "Database"=>$db, "UID"=>$user, "PWD"=>$pw ,"CharacterSet" => "UTF-8");
$db_handler=sqlsrv_connect("srvdb",$connectionInfo);

date_default_timezone_set ("Europe/Rome");

//lettura buoni aperti
$buoni=array();
$query="SELECT * FROM CARB_buoni WHERE mov_open='1' AND veicolo!='0' ";
if($result=sqlsrv_query($db_handler,$query)) {
	while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
		$buoni[$row[veicolo]]=$row;
	}
}
		
$maestro=new Maestro();

$lista=$maestro->carb_tt($_POST[txt]);

?>

<?php
	if ($_POST[flag]==0) {
?>
	<button style="position:absolute;top:-20px;left:5px;" onclick="carb_close_lista();">Annulla</button>
<?php
	}
	
	//echo $_POST[flag];
?>	

<table class="carb_lista_tt" width="640px">
	<colgroup>
		<col span="1" width="20px"/>
		<col span="1" width="110px" />
		<col span="1" width="110px"/>
		<col span="1" width="230px"/>
		<col span="1" width="170px"/>
	</colgroup>
	
	<thead>
		<tr>
			<th></th>
			<th>Telaio</th>
			<th>Targa</th>
			<th>Descrizione</th>
			<th>Gestione</th>
		</tr>
	</thead>
	
	<tbody>
		<?php
			$td=1;
			foreach ($lista as $key=>$a) {
				
				//verifica se c'è un buono aperto per la stessa vettura
				$open=0;
				if ($a[telaio]!=$_POST[txt]) {
					if (array_key_exists($key,$buoni)) {
						$open=1;
					}
				}
				
				//determina gestione
				$temp_ges=(array) json_decode($a[flag_cons]);
				/*if (!(($a[flag_cons]=='' || $a[flag_cons]=='1956') && $a[cod_natura]!='')) {
					$lista[$key][des_natura]="CLIENTE";
					$lista[$key][cod_natura]="_CLIENTE_";
				}*/
				if ($a[cod_natura]=='' || ($temp_ges[acq]!='1956' && $temp_ges[d_cons]!='')) {
					$lista[$key][des_natura]="CLIENTE";
					$lista[$key][cod_natura]="_CLIENTE_";
				}				
			
				echo '<tr ';
				
				if($open==1) {
					echo 'class="carb_lista_tt_tr3"';
					if ($td==1) $td=2;
					else $td=1;
				}
				elseif ($td==1) { 
					echo 'class="carb_lista_tt_tr1"';
					$td=2;
				}
				elseif ($td==2) {
					echo 'class="carb_lista_tt_tr2"';
					$td=1;
				}
				
				echo '>';
					
						echo '<td style="text-align:center;font-size:10pt;" >';
							if ($open==0) {
								echo '<img style="width:15px;height:15px;cursor:pointer;" src="img/sel.png" onclick="carb_tartel_sel(\''.$key.'\',\''.$a[modello].'\');"/>';
							}
						echo '</td>';
						//echo '<td style="font-size:10pt;cursor:pointer;" onclick="bon_sel_anagra(\'var obj='.addslashes(json_encode($a)).'\');">'.$a[cod].'</td>';
					echo '<td>';
						echo '<div style="font-size:9pt;">'.$a[telaio].'</div>';
					echo '</td>';
					echo '<td>';
						echo '<div style="font-size:9pt;">'.$a[targa].'</div>';
					echo '</td>';
					echo '<td>';
						echo '<div style="font-size:8pt;">'.substr($a[des],0,35).'</div>';
					echo '</td>';
					echo '<td>';
					
						
						echo '<div style="font-size:9pt;">'.substr($lista[$key][des_natura],0,35).'</div>';
						/*echo '<div>'.$a[rif].'</div>';
						echo '<div>'.$a[gestione].'</div>';
						echo '<div>'.$a[flag_cons].'</div>';
						echo '<div>'.$a[cod_natura].'</div>';
						echo '<div>'.$a[des_natura].'</div>';*/
					echo '</td>';
					
					/*
					echo '<td style="font-size:10pt;">';
						echo '<div style="text-align:left;position:relative;">c.fisc: ';
							echo '<span style="position:absolute;top:0px;left:40px;">'.$a[cfisc].'</span>';
						echo '</div>';
						echo '<div style="text-align:left;position:relative;">p.iva: ';
							echo '<span style="position:absolute;top:0px;left:40px;">'.$a[piva].'</span>';
						echo '</div>';
					echo '</td>';
					
					echo '<td style="font-size:10pt;">'.$a[iban].'</td>';*/
					
				echo '</tr>';
			}
		?>
	</tbody>
</table>

<?php

	//echo json_encode($lista);

	echo '<script type="text/javascript">';
		echo '_carb_lista='.json_encode($lista).';';
	echo '</script>';
	
	sqlsrv_close($db_handler);
?>