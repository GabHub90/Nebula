<?php
//error_reporting(E_ERROR | E_PARSE);
error_reporting(0);

include('ststop_ini.php');
include('ststop_func.php');
include('core/maestro.php');
include('core/st_reparto.php');
include('core/st_marcatura.php');
include('core/st_collaboratore.php');
include('core/st_environment.php');

include($_SERVER['DOCUMENT_ROOT'].'/apps/quartet/core/quartet_loader.php');

//connessione al database
$connectionInfo = array( "Database"=>$db, "UID"=>$user, "PWD"=>$pw ,"CharacterSet" => "UTF-8");
$db_handler=sqlsrv_connect("srvdb",$connectionInfo);

date_default_timezone_set ("Europe/Rome");

/*$agg_rep=(isset($_GET[reparto])?$_GET[reparto]:"");
$agg_coll=(isset($_GET[coll])?$_GET[coll]:"");
$edit=(isset($_GET[edit])?$_GET[edit]:0);*/

$maestro=new Maestro();

?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >
	<title>Start &amp; Stop</title>
	<link rel="stylesheet" type="text/css" href="ststop.css?v=<?php echo mktime();?>" media="screen" />
	<script type="text/javascript" src="jQuery.js"></script>
	<script type="text/javascript" src="ststop.js?v=<?php echo mktime();?>"></script>
	<script type="text/javascript">
		//permette il caricamento dinamico delle nuove versione dei file CSS e JS
		/*function loadjscssfile(filename, filetype, media){
			 
			 var versionUpdate = (new Date()).getTime();
			 
		    if (filetype=="js"){ //if filename is a external JavaScript file
		        var fileref=document.createElement('script');
		        fileref.setAttribute("type","text/javascript");
		        fileref.setAttribute("src", filename+"?v="+versionUpdate);
		    }
		    else if (filetype=="css"){ //if filename is an external CSS file
		        var fileref=document.createElement("link");
		        fileref.setAttribute("rel", "stylesheet");
		        fileref.setAttribute("type", "text/css");
		        fileref.setAttribute("href", filename+"?v="+versionUpdate);
				 fileref.setAttribute("media", media);
		    }
		    if (typeof fileref!="undefined")
		        document.getElementsByTagName("head")[0].appendChild(fileref);
		}
		
		loadjscssfile("ststop.js", "js","") //dynamically load and add this .js file
		loadjscssfile("ststop.css", "css","print,screen") ////dynamically load and add this .css file
		*/
			
		//===========================================================================================================
		
		<?php
		
			$reparto="";
			$selected_coll=0;
			
			//$today='20190712';
			$today=date("Ymd");
			$reparti=get_reparto_menu_lines($db_handler);
			echo 'var _reparti='.json_encode($reparti).';';
			echo "\n\t\t";
						
			if (isset($_GET['coll']) && $_GET['coll']!="") {
				$selected_coll=$_GET['coll'];
				echo 'var _selected_coll='.$_GET['coll'].';';
			}
			else {
				echo 'var _selected_coll=0;';
			}
			
			if (isset($_GET['reparto']) && $_GET['reparto']!="") {
				$reparto=new stReparto($db_handler,$maestro,$_GET['reparto'],$selected_coll);
				echo 'var _reparto="'.$_GET['reparto'].'";';
			}
			else {
				echo 'var _reparto="";';
			}
		?>
		
		//variabile che determina se il collaboratore è in stato EDIT
		var _edit=0;
			
	</script>
</head>
<!--<body onload="chg_rep_eff('<?php echo $agg_rep ?>','<?php echo $agg_coll ?>','<?php echo $edit ?>');">-->
<body style="display: none;" onload="ststop_visualizza();">
	
	
	<div class="st_container">
		
		<!--DIV PRINCIPALE-->
		<div id="main" class="main_div">
			<?php
				if($reparto instanceof stReparto) {
					$reparto->draw_bar();
					$reparto->draw_colls();
					/*echo '<div>';
						$reparto->draw_log();
					echo '</div>';*/
				}
			?>
		</div>
		
	</div>
	
	<div id="st_cover" class="cover"></div>
	
	<!--DIV NUOVO-->
	<div id="st_bt_nuovo" class="nuovo_div">
		<div id="st_bt_nuovo_main"></div>
		<div style="position:absolute;bottom: 10px;left: 0px;width: 100%;text-align:center;">
			<button style="background-color: orange;" onclick="st_chiudi_nuovo();">annulla</button>
		</div>
	</div>
		
</body>
</html>

<?php
	sqlsrv_close($db_handler);
?>