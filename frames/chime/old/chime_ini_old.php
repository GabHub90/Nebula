<?php
	if ($_SERVER['SERVER_NAME']=='localhost') {
		$server_db='localhost';
		$db='chime';
		$user='';
		$pw='';
		//$dominio='localhost/check123/';
		$dominio='';
	}
	else {
		$server_db='localhost';
			$db='chime';
			$user='gabellini';
			$pw='';
			//$dominio='http://service/apps/check123/';
			$dominio='';
	}
	
	// parametri SMS
	$sms_login = 'sbuser804'; // login e password sono fornite all’attivazione 
	$sms_pw = 'xksyl17';
	//$password="";
	$sms_tipo = 1;	// tipo = 1 -> spedisce SMS – tipo = 2 -> Ritorna num crediti
	$sms_mitt = "Gabellini";
	$sms_status = 0;
	
	//parametri MAIL
	//SSL Required: Yes
	$mail_host = "relay.vwgroupvpn.it";
	$mail_port = "25";
	$mail_auth=false;
	$mail_user="";
	$mail_pw="";
?>