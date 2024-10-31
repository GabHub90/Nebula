<?php  
include('../chime_ini.php');
@include("http_post.php");

$db_handler=mysqli_connect($server_db,$user,$pw);
mysqli_select_db($db_handler,$db);

//acquisisci caratteristiche del report
$param=(array) json_decode($_POST[param]);
$report=(array) json_decode($param[report]);
//viene passato un elemento per volta
$elem=(array) json_decode($param[elemento]);
$modo=$param[modo];

//acquisisci MEDIA
if ($report[sms]==1) {
	$sms=array();
	$query='SELECT t2.ID, t2.testo, t2.tag, t1.def FROM rep_sms AS t1 INNER JOIN sms AS t2 ON t1.sms=t2.ID WHERE t1.report='.$report[rep_id].' AND t2.attivo=1';
	if($result=mysqli_query($query)) {
		while ($row=mysqli_fetch_assoc($result)) {
			$sms[$row[ID]]=$row;
		}
	}
}

$ret="";

//$elem=(array)json_decode($elemento);

$dati=(array)$elem[dati];

$txt=$sms[$elem[model_sms]][testo];
//sostituisci TAG <xxx> contenuti nel messaggio SMS
preg_match_all("/<([^>]*)>/",$txt,$matches,PREG_PATTERN_ORDER);
$c=count($matches[0]);
if($c>0) {
	foreach($matches[0] as $key => $match) {
		$txt=str_replace($match,$dati[$matches[1][$key]],$txt);
	}
}

//SPEDISCI SMS

//le impostazioni di base sono nel file CHIME.INI
$form_data = array('login' => $sms_login, 'password' => $sms_pw, 'tipo' => $sms_tipo, 'dest' => $elem[phone], 'testo' => $txt, 'mitt' => $sms_mitt, 'status' => $sms_status);
@$a = new http_post; 
@$a->set_action('http://www.nsgateway.net/smsscript/sendsms.php'); 
@$a->set_timeout(30);
@$a->set_element($form_data); 
@$risultato = $a->send();

//esito
$matchCount = preg_match_all('/OK/',$risultato,$matches);
if ($matchCount>1) $ret.='x'.$elem[chsum].'.set_sms("ok");';
else $ret.='x'.$elem[chsum].'.set_sms("error");';
	
echo $ret;

mysqli_close($db_handler);
?>