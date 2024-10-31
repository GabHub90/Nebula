<?php
include('chime_ini.php');
require('chime_func.php');
//connessione al database
$db_handler=mysqli_connect($server_db,$user,$pw);
mysqli_select_db($db_handler,$db);

$reparto='AUS';
$color='#17c8f0';
?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >
	<title>Chime</title>
	<link rel="stylesheet" type="text/css" href="chime.css" media="screen" />
	<script type="text/javascript" src="jQuery.js"></script>
	<script type="text/javascript" src="chime.js"></script>
	<script type="text/javascript">
	
		var _menu_open=0;
		var _reparto="<?php echo $reparto; ?>";
		var _report;
		var _env_sms=0;
		var _env_mail=0;
		var _env_link=0;
		
		//Variabili globali standard per i parametri della query
		//DATA in formato Ymd e INPUT in formato JSON
		var _actual_mese="";
		var _actual_anno="";
		var _actual_giorno="";
		var _stato_indice="";
		var _form_data="";
		var _form_input={"form_data":""};
		var _modo="";
		
		//Variabili globali che contengono i messaggi inviabili
		var _sms=new Array();
		var _mail=new Array();
		var _link=new Array();
		
		//Variabili globali che contengono gli elementi della lista
		var _elementi=new Array();
		
		//variabili di supporto all'elaborazione
		var _chsum="";
		var _key=0;
		var _obj={};
		
	</script>
</head>
<body>
	<div id="cover" class="cover" onclick="close_menu();"></div>
	<div id="cover2" class="cover2"></div>
	
	<div class="titolo"><img class="title_img" src="img/chime.png" /><span style="margin-left:5px;">Chime</span><img id="wait" class="wait" src="img/waiting.gif" /></div>
	
	<!--DIV MENU -->
	<div class="menubar">
		<div id="reparto" class="reparto_m" onclick="open_menu('reparto_d');">Reparto: <span style="color:<?php echo $color; ?>;" id="tagrep"><?php echo $reparto; ?></span></div>
		<div id="report" class="report_m" onclick="open_menu('report_d');">Report: <span style="color:<?php echo $color; ?>;" id="tagreport"></span></div>
		<div id="sms" class="sms_m" onclick="open_menu('sms_d');">SMS: <span style="color:<?php echo $color; ?>;" id="tagsms">no</span></div>
		<div id="mail" class="mail_m" onclick="open_menu('mail_d');">MAIL: <span style="color:<?php echo $color; ?>;" id="tagmail">no</span></div>
		<div id="link" class="link_m" onclick="open_menu('link_d');">LINK: <span style="color:<?php echo $color; ?>;" id="taglink">no</span></div>
	</div>
	
	<div id="reparto_d" class="reparto_d">
		<div class="menuline" onclick="chg_reparto('VWS');">Volkswagen Service</div>
		<div class="menuline" onclick="chg_reparto('AUS');">Audi Service</div>
		<div class="menuline" onclick="chg_reparto('POS');">Porsche Service</div>
		<div class="menuline" onclick="chg_reparto('MAG');">Magazzino</div>
	</div>
				
	<div id="report_d" class="report_d">
		<?php
			get_report_menu_lines($reparto);
		?>
	</div>
	
	<div id="sms_d" class="sms_d">
	</div>
	
	<div id="mail_d" class="mail_d">
	</div>
	
	<div id="link_d" class="link_d">
	</div>
	
	<!--DIV QUERY -->
	<div class="query">
		<div class="desc_report" id="desc_report">Seleziona un report</div>
		<div class="query_report" id="query_report"></div>
	</div>
	
	<!--DIV LISTA -->
	<div id="lista" class="lista">
	</div>
	
</body>
</html>

<?php  
mysqli_close($db_handler);
?>