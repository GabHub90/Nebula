<?php
	header('Content-type: text/html; charset=ISO-8859-1');
?>

<html>
<head>
<script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript">
	function link() {
		var temp=parent.document.getElementById("pmsg").value;
		//temp=decodeURIComponent(escape(temp));
		document.getElementById("msg").value=temp;
		document.getElementById("numero").value=parent.document.getElementById("pnumero").value;
		document.getElementById("msgstr").submit();
		
		//var temp={'login':'sbuser804', 'password':'dx8wosm', 'tipo':1, 'dest':parent.document.getElementById("pnumero").value, 'testo':parent.document.getElementById("pmsg").value , 'mitt':'Gabellini'};
		
		//alert(JSON.stringify(temp));
		
		/*$.ajax({"url":'http://www.nsgateway.net/smsscript/sendsms.php',"type":"POST","data":temp,"async":true,"cache":false,"headers":{"Access-Control-Request-Headers": "x-requested-with"}, "success":function(ret) {
			$('#sender').html('<html>'+ret+'</html>');
			parent.document.getElementById('waiter').innerHTML='';
			parent.document.getElementById('ok').disabled=true;
		}});*/
		
		/*$.ajax({"url":'http://www.nsgateway.net/smsscript/sendsms.php',"type":"POST","data":temp,"async":true,"cache":false, "success":function(ret) {
			$('#sender').html('<html>'+ret+'</html>');
			parent.document.getElementById('waiter').innerHTML='';
			parent.document.getElementById('ok').disabled=true;
		},
		"error":function(ret,status,error) {
			parent.document.getElementById('resp').innerHTML='<html><body><div class="overtxt" style="text-align:center;margin-top:15px;">In teoria il messaggio dovrebbe essere stato spedito.</div></body></html>';
			parent.document.getElementById('waiter').innerHTML='';
			parent.document.getElementById('ok').disabled=true;
		}});*/
			
	}
</script>
<body onload="link();">
	<!--<form name="msgstr" id="msgstr" method="post" action="spedisci.php">-->
	<!--<form name="msgstr" id="msgstr" method="post" action="" onsubmit="return false;">-->
	<form name="msgstr" id="msgstr" method="post" action="http://www.nsgateway.net/smsscript/sendsms.php">
		<input name="testo" id="msg" type="hidden" value=""/>
		<input name="dest" id="numero" type="hidden" value=""/>
		<input name="login" type="hidden" value="sbuser804"/>
		<input name="password" type="hidden" value="dx8wosm"/>
		<input name="mitt" type="hidden" value="Gabellini"/>
		<input name="tipo" type="hidden" value="1"/>
	</form>
</body>
</html>
