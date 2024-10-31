<?php
error_reporting(E_ERROR | E_PARSE);
//ini_set('display_errors', 'On');
//error_reporting(0);

require('../chime_func.php');
require('../chime_ini.php');
include('class/maestro.php');
include($_SERVER['DOCUMENT_ROOT'].'/apps/tempo2/core/calendario.php');
include($_SERVER['DOCUMENT_ROOT'].'/apps/tempo2/core/griglia.php');

include ('class/chime_cal.php');

$maestro=new Maestro();

$param=$_REQUEST['param'];

$log=array();

$connectionInfo = array( "Database"=>$db, "UID"=>$user, "PWD"=>$pw, "CharacterSet" => "UTF-8");
$db_handler=sqlsrv_connect("srvdb",$connectionInfo);
date_default_timezone_set ("Europe/Rome");

$mesi=array('Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno','Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre');

$azione=$param['azione'];

//CALCOLO INTERVALLO DI TEMPO

$today=$param['today'];
$time_start=mktime(0,0,0,(int)substr($today,4,2),(int)substr($today,6,2),(int)substr($today,0,4));
$wd=date('w',$time_start);

$time_temp=strtotime($azione['delta_i'].' days',$time_start);
$limit_akt_i=date('Ymd',$time_temp);
$time_temp=strtotime($azione['delta_f'].' days',$time_start);
$limit_akt_f=date('Ymd',$time_temp);

//TROVA LA DOMENICA
while ($wd!=0) {
	$time_start=strtotime('-1 days',$time_start);
	$wd=date('w',$time_start);
}

//offset indica la posizione del calendario da cui iniziare se 0=>calcolo in base a today altrimenti in base ad offset
//OFFSET DEVE SEMPRE ESSERE UNA DOMENICA
if ((int)$param['offset']!=0) {
	$time_start=mktime(0,0,0,(int)substr($param['offset'],4,2),(int)substr($param['offset'],6,2),(int)substr($param['offset'],0,4));
}
else {
	$time_start=strtotime('-7 days',$time_start);
}
$inizio=date('Ymd',$time_start);
$time_end=strtotime('+21 days',$time_start);
$fine=date('Ymd',$time_end);

/*
$inizio			inizio calendario
$fine			fine calendario
$today			oggi (come passato come parametro)
$limit_akt_i		inizio validità azione
$limit_akt_f		fine validità azione
*/

$calendari=array();

//creazione calendari
$time_temp=mktime(0, 0, 0, (int)substr($inizio,4,2), 1, (int)substr($inizio,0,4));
$temp=date('Ymd',$time_temp);

while (substr($temp,0,6)<=substr($fine,0,6)) {
	$calendari[substr($temp,0,6)]=new ChimeCal($db_handler,substr($inizio,0,4),$param['reparto']);
	$calendari[substr($temp,0,6)]->chime_mese(substr($temp,4,2),$today);

	$time_temp=strtotime('+1 month',$time_temp);
	$temp=date('Ymd',$time_temp);
}

/////////////////////////////////////
//GRIGLIA
$grid=new griglia($param['reparto']);

$eventi=array();
$query="SELECT
		ID,
		rif,
		giorno,
		azione,
		contatto,
		isnull(d_invio,'') as d_invio
		FROM GN_GAB_CHIME_storico
		WHERE giorno>='".$inizio."' AND giorno<='".$fine."' AND azione='".$param[akt]."'
		ORDER BY giorno,rif
		";

$result=$maestro->chime_query($query);

if($result) {
	while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
	
		if (!array_key_exists($row['giorno'],$eventi)) {
			$eventi[$row['giorno']]['esclusi']=0;
			$eventi[$row['giorno']]['errori']=0;
			$eventi[$row['giorno']]['lista']=array();
		}
		
		if ($row['d_invio']==-1) $eventi[$row['giorno']]['esclusi']++;
		elseif ($row['d_invio']==-2) $eventi[$row['giorno']]['errori']++;
		
		$eventi[$row['giorno']]['lista'][]=$row;
	}
}

//DATA INVIO = -1 => escluso
//DATA INVIO = -2 => errore invio


//TEST
/*$eventi=array(
	"20190313"=>array(
		"esclusi"=>0,
		"errori"=>0,
		"lista"=>array(
			"1"=>array(
				"num_rif_movimento"=>1134321,
				"giorno"=>"20190313",
				"azione"=>1,
				"contatto"=>"3331323292",
				"data_invio"=>"20190311"
			)
		)
	)
);*/
//FINE TEST

$pointer=$time_start;
while ($pointer<=$time_end) {
	
	$tag=date('Ymd',$pointer);
	
	$arr=array("esclusi"=>0,"errori"=>0,"tot"=>0);
	
	//se il giorno ha degli eventi
	if (array_key_exists($tag,$eventi)) {
		$arr['esclusi']=$eventi[$tag]['esclusi'];
		$arr['errori']=$eventi[$tag]['errori'];
		$arr['tot']=count($eventi[$tag]['lista']);
		//$grid->insert_single($tag,$arr);
	}
	
	$log[]=$grid->insert_single($tag,$arr);
	
	$pointer=strtotime("+1 days",$pointer);
}

////////////////////////////////////

echo '<div class="chime_cal_head">';
	
	echo '<div style="position:absolute;left:-25px;top:3px;">';
		for ($x=3;$x>0;$x--) {
			$rif=strtotime("-".$x." weeks",$time_start);
			echo '<img class="chime_cal_arrow" src="img/left'.$x.'.png" onclick="chime_chg_rif(\''.date('Ymd',$rif).'\');"/>';
		}
	echo '</div>';
	
	echo '<div style="position:absolute;right:5px;top:3px;">';
		for ($x=1;$x<4;$x++) {
			$rif=strtotime("+".$x." weeks",$time_start);
			echo '<img class="chime_cal_arrow" src="img/right'.$x.'.png" onclick="chime_chg_rif(\''.date('Ymd',$rif).'\');"/>';
		}
	echo '</div>';		
	
echo '</div>';

	
//----- CHIME--------------------------------------------------------------------------------
echo '<table class="chime_cal_tab" width="570">';
	echo "<colgroup>";
		echo '<col span="1" width="10"/>';
		echo '<col span="1" width="80"/>';
		echo '<col span="6" width="80"/>';
	echo "</colgroup>";
	echo "<thead>";
		echo "<tr>";
			echo '<th style="font-size:11pt;position:relative;">';
				echo '<div style="position:absolute;top:3px;left:0px;">'.substr($inizio,0,4).'</div>';
			echo '</th>';
			echo "<th>Dom</th>";
			echo "<th>Lun</th>";
			echo "<th>Mar</th>";
			echo "<th>Mer</th>";
			echo "<th>Gio</th>";
			echo "<th>Ven</th>";
			echo "<th>Sab</th>";
		echo "</tr>";
	echo "</thead>";
	
	echo "<tbody>";

		foreach($calendari as $c) {
			//$c[cal]->draw_avalon($start,$end,$c[pan]);
			$c->draw_chime($inizio,$fine,$limit_akt_i,$limit_akt_f,$grid->get_griglia());
		}
	
	echo '</tbody>';
echo '</table>';

/*echo '<div>';
	echo json_encode($grid->get_griglia());
echo '</div>';*/

?>

<?php
sqlsrv_close($db_handler);
?>