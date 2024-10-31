<?php
	error_reporting(E_ERROR | E_PARSE);
	//error_reporting(0);
	
	include("http_post.php");
	include("config.php");
	$destinatari = $_REQUEST[numero]; // separati da virgola 
	$messaggio = $_REQUEST[msg];
	$mittente = "Gabellini";
	$status = 1;
	
	$form_data = array('login' => $login, 'password' => $password, 'tipo' => $tipo, 'dest' => $destinatari, 'testo' => $messaggio, 'mitt' => $mittente, 'status' => $status);
	
	//$form_data = array('login' => $login, 'password' => $password, 'tipo' => $tipo, 'dest' => $destinatari, 'testo' => $messaggio, 'mitt' => $mittente);
	
	$a = new http_post; 
	$a->set_action('http://www.nsgateway.net/smsscript/sendsms.php'); 
	$a->set_timeout(30);
	$a->set_element($form_data); 
	$risultato = $a->send();
?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=ISO-8859-1" >
	<style type="text/css">
		@import url(stile.css);
	</style>
</head>	
<body>
	<div class="overtxt" style="text-align:center;">

	<?php
		$matchCount = preg_match_all('/OK/',$risultato,$matches);
		if ($matchCount>1) {
			echo "Messaggio inviato correttamente.";
			echo "<script type=\"text/javascript\">";
			echo "parent.document.getElementById('waiter').innerHTML='';";
			echo "parent.document.getElementById('ok').disabled=true;";
			echo "</script>";
		}
		else echo $risultato;
		//echo $_REQUEST[numero];
		//echo "<br/>";
		//echo $_REQUEST[msg];
	?>
	</div>
</body>
</html>