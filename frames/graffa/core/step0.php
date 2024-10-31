<?php  
error_reporting(E_ERROR | E_PARSE);
//error_reporting(0);


include('../graffa_ini.php');
include('../graffa_func.php');
include('calendario.php');
include('draw_cal.php');
include('maestro.php');


$connectionInfo = array( "Database"=>$db, "UID"=>$user, "PWD"=>$pw ,"CharacterSet" => "UTF-8");
$db_handler=sqlsrv_connect("srvdb",$connectionInfo);

date_default_timezone_set ("Europe/Rome");

$maestro=new Maestro();

$reparto=$_POST['reparto'];
$annomese=substr($_POST['rifdata'],0,6);

$reparti=array();
$query="SELECT * FROM MAESTRO_reparti WHERE tag='".$reparto."'";
$result=sqlsrv_query($db_handler,$query);
$row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
$des_rep=$row['descrizione'];
$concerto=$row['concerto'];


//leggi prenotazioni
$temp_pren=$maestro->graffa_prenot($concerto,$_POST['rifdata']);
$pren=array();
while ($row=sqlsrv_fetch_array($temp_pren,SQLSRV_FETCH_ASSOC)) {
	$pren[]=$row;
}

?>

<div id="graffa_st0_left" class="graffa_st0_left">

	<div>
		<div style="left: 20px;font-size:16px;font-weight:bold;">
			<?php
				echo $des_rep;
			?>
		</div>
		<div id="graffa_st0_cal" class="graffa_st0_cal">
			<?php
				$cal=new Calendario($db_handler,substr($annomese,0,4));
				$tabella=$cal->mese(substr($annomese,4,2));

				draw_cal(substr($annomese,4,2),substr($annomese,0,4),$_POST[rifdata],$tabella);
				
				//echo '<div>'.$_POST[rifdata].'</div>';
			?>
		</div>
	</div>
	
	<div class="graffa_pdf">
		<div style="position: relative;width:300px;text-align: right;">
			<button style="" onclick="generaPDF();">Genera pdf</button>
		</div>
		
		<iframe id="graffa_pdf" style="height: 100%;"></iframe>
	</div>
	
</div>

<div id="graffa_st0_right" class="graffa_st0_right">
	<!--struttura tabella prenotazioni-->
	<table style="width:770px;font-size:10pt;text-align:center;border-collapse:collapse;">
		<colgroup>
			<col span="1" width="20px"/>
			<col span="3" width="70px"/>
			<col span="1" width="200px"/>
			<col span="1" width="160px"/>
			<col span="1" width="70px"/>
			<col span="1" width="50px"/>	
		</colgroup>
		<thead>
			<th></th>
			<th>Ora</th>
			<th>Riconsegna</th>
			<th>ODL</th>			
			<th>Veicolo</th>
			<th>Cliente</th>
			<th>Km</th>
			<th>UT</th>
		</thead>
		<tbody>
			<?php

				$hh=0;
								
				foreach ($pren as $kodl=>$odl) {
				
					//verifica se mattina o pomeriggio
					if ($odl[hh]>13 && $hh==0) {
						echo '<tr>';
							echo '<td colspan="8" style="height:5px;background-color:#5c80ea;">';
								//echo '<img style="width:650px;height:8px;" src="img/barra.png"/>';
							echo '</td>';
						echo '</tr>';
						$hh=1;
					}
				
					if ($odl[stato]=='RP') $cl_stato='g';
					else if ($odl[stato]=='RO') $cl_stato='r';
					else $cl_stato='w';
					
					
					echo '<tr class="graffa_riga_pren_'.$cl_stato.'">';
					
						//check
						echo '<td>';
							echo '<input id="graffa_chk_'.$kodl.'" type="checkbox" value="'.$kodl.'"/>';
						echo '</td>';
						
						//ora
						echo '<td style="text-align:center;font-weight:bold;">';
							echo ($odl[hh]<10?"0".$odl[hh]:$odl[hh]).":".($odl[mm]<10?"0".$odl[mm]:$odl[mm]);
						echo '</td>';
						
						//riconsegna
						//calcola riconsegna
						$delta=-1;
						if ($odl[ricon]!="") {
							$delta=delta_tempo ($odl[pren],$odl[ricon],"g");
						} 
						if ($delta==-1 || $delta>1) $temp=db_todata($odl[ricon]);
						else {
							if ($delta==0) $temp='OGGI';
							if ($delta==1) $temp='DOMANI';
						}
						
						echo '<td style="text-align:center;font-size:8pt;">';
							echo '<div style="';
								if ($odl[trasporto]=='ASPETTA') echo 'font-weight:bold;color:red;';
							echo '">';
								echo substr($odl[trasporto],0,8);
							echo '</div>';
							
							echo '<div style="font-size:10pt;">';
								//echo db_todata($odl[ricon]);
								echo $temp;
							echo '</div>';
						echo '</td>';
						
						//odl
						echo '<td style="text-align:center;font-weight:bold;">';
							echo $odl[odl];
						echo '</td>';
						
						//Veicolo
						echo '<td style="text-align:left;">';
							echo '<div style="font-weight:bold;">'.$odl[telaio].'</div>';
							echo '<div style="font-size:9pt;">'.substr($odl[des],0,25).'</div>';
						echo '</td>';
						
						//Cliente
						echo '<td style="text-align:left;">';
							echo '<div style="font-weight:bold;font-size:9pt;">'.substr($odl[util],0,21).'</div>';
							echo '<div style="font-size:9pt;">'.substr($odl[intest],0,25).'</div>';
						echo '</td>';
						
						//Km
						echo '<td style="text-align:center;font-size:8pt;">';
							echo number_format($odl[km],0,",",".");
						echo '</td>';
						
						//UT
						echo '<td style="text-align:center;">';
							echo number_format($odl[ut],2,",","");
						echo '</td>';
						
					echo '</tr>';
					
					//LAMS
					echo '<tr class="graffa_riga_lams_'.$cl_stato.'">';
						echo '<td colspan="8" style="color:black;text-align:left;padding-left:5px;font-size:11pt;">';
							echo strtolower(str_replace('<br/>',' - ',$odl[lams]));
						echo '</td>';
					echo '</tr>';

				}
			?>
		</tbody>
	</table>
	
	<?php
		/*echo '<div>';
			echo json_encode($pren);
		echo '</div>';*/
	?>
	
</div>



<?php
	echo '<script type="text/javascript">';
		echo '_graffa_pren='.json_encode($pren).';';
	echo '</script>';

	sqlsrv_close($db_handler);
?>
