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

$param=$_POST[param];
		
//ricerca buoni
$buoni=array();

$query="SELECT
		t1.*,
		t2.sede
		FROM CARB_buoni as t1
		LEFT JOIN MAESTRO_reparti as t2 on t1.reparto = t2.tag
		WHERE t1.d_stampa<='".input2db($param[data_f])."' AND t1.veicolo!='0' AND t1.mov_open='0' AND t1.stato!='annullato' AND t2.sede ='".$param[sede]."'
		";

if ($param[data_i]!="") $query.=" AND t1.d_stampa>='".input2db($param[data_i])."' ";

$query.=" ORDER BY t1.tipo_carb,t1.ID";


if($result=sqlsrv_query($db_handler,$query)) {
	while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
		$buoni[$row[ID]]=$row;
		$buoni[$row[ID]][dv]=$maestro->carb_get_dv($row[veicolo]);
	}
}


?>

<div style="">
	
	<div style="position: relative;width:600px;">
		<button onclick="carb_annulla_st_query();">Annulla</button>
		<button style="position: relative;top: 0px;left:30px;" onclick="carb_stampa_fe_query();">Stampa</button>
		<button style="position: absolute;top: 0px;right:60px;" onclick="carb_genera_tracciato();">Esporta</button>
	</div>
	
	<div id="carb_st_lista_div" class="carb_st_lista_div" style="width: 550px;">
 	 	
		<table class="carb_st_lista_tt" style="width:500px;border-collapse: collapse;">
			<colgroup>
				<col span="1" width="70px"/>
				<col span="1" width="130px"/>
				<col span="1" width="200px"/>
				<col span="1" width="100px"/>
			</colgroup>
			
			<thead>
				<tr>
					<th></th>
					<th style="text-align:left;">
						<div>Data stampa</div>
					</th>
					<th style="text-align:left;">Veicolo</th>
					<th style="text-align:center;">Importo</th>
				</tr>
			</thead>
		
			<tbody>
				<?php
				
					$td=1;
					$num_buoni=array("D"=>0,"B"=>0,"TOT"=>0);
					$importo=array("D"=>0,"B"=>0,"TOT"=>0);
					$carb="";
					
					foreach ($buoni as $k=>$r) {
					
						if ($carb!=$r[tipo_carb]) {
							
							if ($carb!="") {
								//resoconto
								echo '<tr>';
									echo '<td colspan="4" style="font-weight:bold;">';
										echo 'Numero Buoni: '.$num_buoni[$carb];
										echo ' - Importo: '.number_format($importo[$carb],2,',','');
									echo '</td>';
								echo '</tr>';
							}
							
							//intestazione
							echo '<tr>';
								echo '<td colspan="4" style="font-weight:bold;background-color:moccasin;">';
									echo 'Vetture ';
									if ($r[tipo_carb]=='B') echo 'Benzina';
									if ($r[tipo_carb]=='D') echo 'Diesel';
								echo '</td>';
							echo '</tr>';
							
							$carb=$r[tipo_carb];
						}
					
						
						$num_buoni[$r[tipo_carb]]++;
						$importo[$r[tipo_carb]]+=$r[importo];
						$num_buoni["TOT"]++;
						$importo["TOT"]+=$r[importo];
						
						//scrivi riga
						echo '<tr class="carb_lista_tt_tr1">';
							
							echo '<td>';
								echo $r[ID];
							echo '</td>';
							
							echo '<td>';
								echo '<span>'.db_todata($r[d_stampa]).'</span>';
							echo '</td>';
							
							echo '<td>';	
								echo '<div id="carb_descr" class="carb_descr" style="font-size:13px">'.($r[dv][$r[veicolo]][targa]!=""?$r[dv][$r[veicolo]][targa]:$r[dv][$r[veicolo]][telaio]).'</div>';
							echo '</td>';
							
							echo '<td>';
								echo '<div>'.number_format($r[importo],2,",","").'</div>';
							echo '</td>';
						
						echo '</tr>';
						
					}
					
					//resoconto
					echo '<tr>';
						echo '<td colspan="4" style="font-weight:bold;">';
							echo 'Numero Buoni: '.$num_buoni[$carb];
							echo ' - Importo: '.number_format($importo[$carb],2,',','');
						echo '</td>';
					echo '</tr>';
					
					//totale globale
					echo '<tr>';
						echo '<td colspan="4" style="font-weight:bold;background-color:#EEE8AA;">';
							echo 'TOTALE Buoni: '.$num_buoni["TOT"];
							echo ' - TOTALE Importo: '.number_format($importo["TOT"],2,',','');
						echo '</td>';
					echo '</tr>';
					
				?>
				
			</tbody>
		</table>
	</div>
</div>

<?php

	/*echo '<div>';
		echo $query;
	echo '</div>';
	*/
	
	$t_buoni=array();
	foreach ($buoni as $kb=>$b) {
		$t_buoni[]=array("ID"=>$b['ID'],"d_stampa"=>$b[d_stampa],"veicolo"=>($b[dv][$b[veicolo]][targa]!=""?$b[dv][$b[veicolo]][targa]:$b[dv][$b[veicolo]][telaio]),"importo"=>number_format($b[importo],2,',',''),"tipo_carb"=>$b[tipo_carb]);
	}

	echo '<script type="text/javascript">';
			echo '_carb_fe_lista='.json_encode($t_buoni).';';
	echo '</script>';

	sqlsrv_close($db_handler);
?>