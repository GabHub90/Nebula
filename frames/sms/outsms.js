function validate(nome,telefono) {
	var attuale=parseInt(document.getElementById("numero").value);
	var error=false;
	//controllo numero
	if (attuale<10000000 || isNaN(attuale)) {
		document.getElementById("num").innerHTML="ERRORE NUMERO";
		document.getElementById("num").setAttribute("class","error");
		error=true;
	}
	else {
		document.getElementById("num").innerHTML="Numero di telefono";
		document.getElementById("num").setAttribute("class","num");
		error=false;
	}
	//controllo cliente
	if (document.getElementById("intest").checked && document.getElementById("cliente").value=="") {
		document.getElementById("clierror").style.visibility="visible";
		error=true;
	}
	else {
		document.getElementById("clierror").style.visibility="hidden";
		if (!error) error=false;
	}
	//controllo corpo
	if (document.getElementById("corpo").value.length<5) {
		document.getElementById("corpoerror").style.visibility="visible";
		error=true;
	}
	else {
		document.getElementById("corpoerror").style.visibility="hidden";
		if (!error) error=false;
	}
	
	//conferma
	if (!error)	sms(nome,telefono);
}

function sms(nome,telefono) {
	var msg = createMsg(nome,telefono);
	var destinatario = document.getElementById("numero").value;
	//destinatario
	var ds = document.getElementById("ds");
	ds.innerHTML = destinatario;
	//mittente
	var mxg = document.getElementById("mxg");
	mxg.innerHTML = msg;
	//lunghezza
	var len = document.getElementById("len");
	//attiva tasto CONFERMA
	if (msg.length>160) {
		len.innerHTML="<span style='color:#FF0000;'>IL MESSAGGIO E' TROPPO LUNGO ("+msg.length+")</span>";
	}
	else {
		len.innerHTML = msg.length;
		document.getElementById("ok").disabled=false;
	}
	//visualizza overlay
	document.getElementById("overlay").style.display="inline";
	//scrivi valori hidden
	document.getElementById("pmsg").value=msg;
	document.getElementById("pnumero").value=destinatario;	
	//cancella il div resp
	document.getElementById('resp').innerHTML="";
}

function createMsg(nome,telefono) {
	var msg="";
	//saluto e intestazione
	if (document.getElementById("saluto").checked) {
		msg=msg+document.getElementById("salutoval").value;
	}
	if (document.getElementById("intest").checked) {
		if (document.getElementById("sessoval").value!="")
			msg=msg+" "+document.getElementById("sessoval").value;
		if (document.getElementById("cliente").value!="")
			msg=msg+" "+document.getElementById("cliente").value;
	}
	//virgola
	if (msg.length>0) msg=msg+", ";
	// corpo
	msg=msg+document.getElementById("corpo").value;
	//data e ora
	if (document.getElementById("data").checked) {
		msg=msg+" "+document.getElementById("dataval").value;
	}
	if (document.getElementById("ora").checked) {
		msg=msg+" alle "+document.getElementById("oraval").value;
		msg=msg+":"+document.getElementById("minval").value;
	}
	//punto a capo
	msg=msg+".\n";
	//extra
	if (document.getElementById("extra").checked) {
		msg=msg+document.getElementById("exttext").value;
		if (document.getElementById("extattr").value!="") {
			msg=msg+" "+document.getElementById("extattr").value+" "+document.getElementById("extet").innerHTML;
		}
	msg=msg+".\n";
	}
	//mittente
	msg=msg+nome;
	telefono=telefono.substr(0,4)+"-"+telefono.substr(4,6);
	msg=msg+" "+telefono;
	
	return msg;
}

function conferma() {
	var wait = document.createElement("img");
		wait.setAttribute("class","waitimg");
		wait.setAttribute("src","wait.gif");
		wait.setAttribute("id","waitgif");
		var waiter = document.getElementById("waiter");
		waiter.appendChild(wait);
		//avvia il documento php
		$('#sender').on("load", function() {
		    $('#waitgif').remove();
		});
		document.getElementById("sender").setAttribute("src","link.php");
}
