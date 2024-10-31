<?php
error_reporting(E_ERROR | E_PARSE);
//error_reporting(0);

include('chime_ini.php');
require('chime_func.php');

//connessione al database
$connectionInfo = array( "Database"=>$db, "UID"=>$user, "PWD"=>$pw, "CharacterSet" => "UTF-8");
$db_handler=sqlsrv_connect("srvdb",$connectionInfo);
date_default_timezone_set ("Europe/Rome");

$reparto=(isset($_REQUEST['reparto'])?$_REQUEST['reparto']:'');

$azioni=array();
$query="SELECT
		*
		FROM CHIME_azioni
		WHERE stato='1'
		ORDER BY reparto,ID
		";
if($result=sqlsrv_query($db_handler,$query)) {
	while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
		$azioni[$row['reparto']][$row['ID']]=$row;
	}
}

//TEST
//"campi"=>"d_prenotazione,d_chiusura,ragsoc,telaio,targa,lamentati,tel1,d_invio"
/*$azioni=array(
	"S"=>array(
		"1"=>array(
			"ID"=>1,
			"reparto"=>"VWS",
			"descrizione"=>"avviso appuntamento",
			"delta_i"=>"+1",
			"delta_f"=>"+3",
			"campi"=>"avv_app",
			"tipo_contatto"=>"sms",
			"qry"=>"avv_app",
			"send"=>"avv_app",
			"sostituzioni"=>"ID,chime_concerto,chime_giorno",
			"stato"=>1
		),
		"2"=>array(
			"ID"=>2,
			"reparto"=>"VWS",
			"descrizione"=>"ringraziamento passaggio",
			"delta_i"=>"-3",
			"delta_f"=>"-1",
			"campi"=>"feedback_pass",
			"tipo_contatto"=>"sms",
			"qry"=>"feedback_pass",
			"send"=>"feedback_pass",
			"sostituzioni"=>"",
			"stato"=>1
		)
	)
);*/
//END TEST

?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >
	<title>Chime</title>
	<link rel="stylesheet" type="text/css" href="chime.css?v=<?php echo time();?>" media="screen" />
	<script type="text/javascript" src="jQuery.js"></script>
	<script type="text/javascript" src="chime.js?v=<?php echo time();?>"></script>
	<script type="text/javascript">
	
		var _chime_akt=0;
		var _chime_rep='';
		var _chime_offset='0';
		
		var _chime_lista={};
		
		var _chime_obj={};
		var _chime_flag='';
	
		<?php
		echo 'var _chime_today="'.date('Ymd').'";';
		echo 'var _chime_azioni='.json_encode($azioni).';';
		?>
		
	</script>
</head>
<body onload="chime_set_def();">
	<div id="chime_cover" class="chime_cover"></div>
	
	<div class="chime_left">
		
		<div class="chime_head">
			<div style="float:left;margin-left: 5px;width: 120px;">
				<img class="chime_title_img" src="img/chime.png" />
				<span style="margin-left: 5px;margin-top: -3px;">Chime</span>
			</div>
			
			<div style="float:left;width: 150px;">
				<span>Reparto:</span>
				<select id="chime_rep_sel" onchange="chime_set_rep();">
					<?php
					foreach ($azioni as $rep=>$a) {
						echo '<option value="'.$rep.'" ';
							if ($rep==$reparto) echo 'selected="selected"';
						echo '>'.$rep.'</option>';
					}
					?>
				</select>
			</div>
			
			<div style="float:left;width: 260px;">
				<span>Azione:</span>
				<select id="chime_akt_sel" onchange="chime_akt_set();"></select>
			</div>
			
			<div style="clear: both;"></div>
			
		</div>
		
		<div class="chime_cal"></div>
	
	</div>
	
	<div class="chime_right">
		<div class="chime_list"></div>
	</div>
	
</body>
</html>

<?php  
sqlsrv_close($db_handler);
?>