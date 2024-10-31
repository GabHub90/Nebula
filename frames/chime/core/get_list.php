<?php
error_reporting(E_ERROR | E_PARSE);
//ini_set('display_errors', 'On');
//error_reporting(0);

require('../chime_func.php');
require('../chime_ini.php');
require('class/qry.php');
require('class/divy.php');
include('class/maestro.php');

$maestro=new Maestro();

//-------STANDARD NOMI-------------------------------
//	azione				record descrittivo azione
//	giorno				AAAMMDD giorno di riferimento
//	cover				0 / 1
////////////////////////////////////////////////////
/*array AZIONE
>array(
	"ID"=>1,
	"reparto"=>"VWS",
	"descrizione"=>"avviso appuntamento",
	"delta_i"=>"+1",
	"delta_f"=>"+3",
	"campi"=>"avv_app",
	"tipo_contatto"=>"sms",
	"qry"=>"avv_app",
	"sostituzioni"=>"ID,chime_concerto,chime_giorno",
	"stato"=>1
)
*/

$param=$_REQUEST['param'];

$azione=$param['azione'];

$connectionInfo = array( "Database"=>$db, "UID"=>$user, "PWD"=>$pw, "CharacterSet" => "UTF-8");
$db_handler=sqlsrv_connect("srvdb",$connectionInfo);
date_default_timezone_set ("Europe/Rome");

$officine=array();
$query="SELECT
		t1.*
		FROM maestro_reparti as t1
		WHERE isnull(t1.concerto,'')!=''
		";
if($result=sqlsrv_query($db_handler,$query)) {
	while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
		$officine[$row['tag']]=$row;
	}
}

/////////////////////////////////////////////////////

$azione['chime_giorno']=$param['giorno'];
$azione['chime_concerto']=$officine[$azione['reparto']]['concerto'];
$azione['chime_infinity']=$officine[$azione['reparto']]['infinity'];

$qry=new QueryMan($azione,$param['reparto']);

$query=$qry->get_query($azione['qry']);

$result=$maestro->chime_query($query);

$lista=array();
while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
	$lista[]=$row;
}

/////////////////////////////////////////////////////////////////

$d=new DivyMan($lista,$azione['tipo_contatto'],$azione['def_chk']);

echo '<div style="position:relative;">';

	echo '<div class="chime_list_div" style="color:blue;">';
		echo '<span style="cursor:pointer;" onclick="chime_sel_tutti();">tutti</span>';
		echo '<span style="margin-left:14px;cursor:pointer;" onclick="chime_sel_nessuno();">nessuno</span>';
	echo '</div>';

	echo '<div id="chime_list_cover" class="chime_list_cover" style="';
		if ($param['cover']==0) echo 'display:none;';
	echo '" ></div>';

	$d->draw_code($azione['campi']);
	
	if ($param['cover']==0 && count($d->get_list())>0) {
		echo '<div id="chime_list_esegui" style="text-align:right;margin-top:15px;margin-bottom:20px;">';
			echo '<input id="chime_list_giorno" type="hidden" value="'.$param['giorno'].'" />';
			echo '<button onclick="chime_esegui();">Esegui</button>';
		echo '</div>';
	}
	
	echo '<script type="text/javascript">';
		echo "_chime_lista=".json_encode($d->get_list()).";";
	echo '</script>';
	
echo '</div>';

/*echo '<div>';
	echo ($azione[qry]);
echo '</div>';*/

?>

<?php
sqlsrv_close($db_handler);
?>