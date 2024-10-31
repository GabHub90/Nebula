<?php

//error_reporting(-1);
//ini_set('display_errors', 'On');

include('../chime_ini.php');

$path = '/usr/lib/php/PEAR';
//$path = '/usr/lib/php/';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

include_once('Mail.php');
include_once('Mail_Mime/mime.php');


$db_handler=mysqli_connect($server_db,$user,$pw);
mysqli_select_db($db_handler,$db);

//acquisisci caratteristiche del report
$param=(array) json_decode($_POST[param]);
$report=(array) json_decode($param[report]);
//viene passato un elemento per volta
$elem=(array) json_decode($param[elemento]);
$modo=$param[modo];

//acquisisci MEDIA
if ($report[mail]==1) {
	$mail=array();
	$query='SELECT t2.ID, t2.codice, t2.tag, t2.descrizione, t2.oggetto, t2.from, t1.def FROM rep_mail AS t1 INNER JOIN mail AS t2 ON t1.mail=t2.ID WHERE t1.report='.$report[rep_id].' AND t2.attivo=1';
	if($result=mysqli_query($query)) {
		while ($row=mysqli_fetch_assoc($result)) {
			$mail[$row[ID]]=$row;
		}
	}
}

$ret="";

//$elem=(array)json_decode($elemento);

$dati=(array)$elem[dati];

$codice=$mail[$elem[model_mail]][codice];
//sostituisci TAG <#xxx#> contenuti nel codice HTML
preg_match_all("/\[([^\]]*)\]/",$codice,$matches,PREG_PATTERN_ORDER);
$c=count($matches[0]);
if($c>0) {
	foreach($matches[0] as $key => $match) {
		$codice=str_replace($match,$dati[$matches[1][$key]],$codice);
	}
}
//sostituisci TAG <#xxx#> contenuti nell'oggetto della mail
preg_match_all("/\[([^\]]*)\]/",$oggetto,$matches,PREG_PATTERN_ORDER);
$c=count($matches[0]);
if($c>0) {
	foreach($matches[0] as $key => $match) {
		$oggetto=str_replace($match,$dati[$matches[1][$key]],$oggetto);
	}
}

//SPEDISCI MAIL

$subject=$mail[$elem[model_mail]][oggetto];
// CORRETTO 
$from=$mail[$elem[model_mail]][from];
//TEST
//$from="tometa@me.com";
$to=$elem[address];

$headers = array ('From' => $from,'To' => $to,'Subject' => $subject,'Content-Type' => 'text/html;charset=utf-8');

// create a new instance of the Mail_Mime class
$mime = new Mail_Mime();
// set HTML content
$mime->setHtmlBody($codice);
// build email message and save it in $body
$body = $mime->get();
// build header
$hdrs = $mime->headers($headers);
// create Mail instance that will be used to send email later (CHIME.INI)
$smtp = Mail::factory('smtp',
  array ('host' => $mail_host,'auth' => $mail_auth, 'port' => $mail_port, 'username' => $mail_user,'password' => $mail_pw));
//INVIO MAIL
$mail = $smtp->send($to, $hdrs, $body);

if (PEAR::isError($mail)) $ret.='x'.$elem[chsum].'.set_sms("error");alert("'.$mail->getMessage().'");';
else $ret.='x'.$elem[chsum].'.set_mail("ok");';

echo $ret;

mysqli_close($db_handler);
?>